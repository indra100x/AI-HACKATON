import os
import numpy as np
import tensorflow as tf
from fastapi import FastAPI, File, UploadFile, HTTPException, Form
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
import cv2
import tempfile
import shutil
import json
from datetime import datetime
from load_model import load_model

app = FastAPI(title="deepfake api", version="1.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

model = load_model()
img_size = (96, 96)
prediction_threshold = 0.5
invert_predictions = False
feedback_file = 'feedback_data.json'

def load_model(model_path="deepfake_model.h5"):
    global model
    if model is None:
        if not os.path.exists(model_path):
            raise FileNotFoundError(f"model not found: {model_path}")
        print(f"loading model from {model_path}...")
        model = tf.keras.models.load_model(model_path)
        print("model loaded")
    return model

def preprocess_image(image_path):
    img = cv2.imread(image_path)
    if img is None:
        return None
    img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    img = cv2.resize(img, img_size)
    return img.astype(np.float32)

def preprocess_frame(frame):
    if frame is None:
        return None
    frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
    frame_resized = cv2.resize(frame_rgb, img_size)
    return frame_resized

def get_video_info(video_path):
    if not os.path.exists(video_path):
        raise FileNotFoundError(f"video not found: {video_path}")
    
    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        raise ValueError(f"cant open video: {video_path}")
    
    fps = cap.get(cv2.CAP_PROP_FPS)
    frame_count = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
    width = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
    height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
    duration = frame_count / fps if fps > 0 else 0
    
    cap.release()
    
    return {
        'fps': fps,
        'frame_count': frame_count,
        'duration': duration,
        'width': width,
        'height': height
    }

def extract_frames_uniform(video_path, num_frames=10):
    if not os.path.exists(video_path):
        raise FileNotFoundError(f"video not found: {video_path}")
    
    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        raise ValueError(f"cant open video: {video_path}")
    
    total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
    
    if total_frames == 0:
        cap.release()
        return []
    
    if num_frames >= total_frames:
        frame_indices = list(range(total_frames))
    else:
        frame_indices = [int(i * total_frames / num_frames) for i in range(num_frames)]
    
    frames = []
    for idx in frame_indices:
        cap.set(cv2.CAP_PROP_POS_FRAMES, idx)
        ret, frame = cap.read()
        if ret:
            processed = preprocess_frame(frame)
            if processed is not None:
                frames.append(processed)
    
    cap.release()
    return frames

def process_video_for_prediction(video_path, num_frames=10):
    video_info = get_video_info(video_path)
    frames = extract_frames_uniform(video_path, num_frames=num_frames)
    return frames, video_info

def predict_image(image_array):
    global model
    if model is None:
        raise RuntimeError("model not loaded")
    
    img_batch = np.expand_dims(image_array, axis=0)
    prediction = model.predict(img_batch, verbose=0)[0][0]
    
    if invert_predictions:
        prediction = 1.0 - prediction
    
    is_fake = prediction < prediction_threshold
    
    if is_fake:
        confidence = 1.0 - prediction
    else:
        confidence = prediction
    
    return {
        "is_fake": bool(is_fake),
        "confidence": float(confidence),
        "raw_score": float(prediction)
    }

def predict_video_frames(frames):
    global model
    if model is None:
        raise RuntimeError("model not loaded")
    
    if not frames:
        raise ValueError("no frames provided")
    
    frames_normalized = [frame.astype(np.float32) for frame in frames]
    frames_batch = np.array(frames_normalized)
    raw_predictions = model.predict(frames_batch, verbose=0)
    
    if invert_predictions:
        raw_predictions = 1.0 - raw_predictions
    
    predictions = [float(p[0]) for p in raw_predictions]
    
    avg_prediction = np.mean(predictions)
    fake_count = np.sum([p < prediction_threshold for p in predictions])
    real_count = len(predictions) - fake_count
    agreement = max(fake_count, real_count) / len(predictions)
    
    is_fake = avg_prediction < prediction_threshold
    confidence = (1.0 - avg_prediction) if is_fake else avg_prediction
    
    return {
        "is_fake": bool(is_fake),
        "confidence": float(confidence),
        "raw_score": float(avg_prediction),
        "frames_analyzed": len(frames),
        "fake_frames": int(fake_count),
        "real_frames": int(real_count),
        "agreement": float(agreement),
        "frame_predictions": predictions
    }

def load_feedback():
    if os.path.exists(feedback_file):
        with open(feedback_file, 'r') as f:
            return json.load(f)
    return []

def save_feedback(feedback_data):
    with open(feedback_file, 'w') as f:
        json.dump(feedback_data, f, indent=2)

def add_feedback(file_type, file_path, model_prediction, model_confidence, user_feedback, raw_score=None, additional_info=None):
    feedback_data = load_feedback()
    
    feedback_entry = {
        'id': len(feedback_data) + 1,
        'timestamp': datetime.now().isoformat(),
        'file_type': file_type,
        'file_path': file_path,
        'model_prediction': model_prediction,
        'model_confidence': model_confidence,
        'model_raw_score': raw_score,
        'user_feedback': user_feedback,
        'is_correct': model_prediction == user_feedback,
        'additional_info': additional_info or {}
    }
    
    feedback_data.append(feedback_entry)
    save_feedback(feedback_data)
    
    return feedback_entry

def get_feedback_stats():
    feedback_data = load_feedback()
    
    if not feedback_data:
        return {
            'total_feedback': 0,
            'correct_predictions': 0,
            'incorrect_predictions': 0,
            'accuracy': 0.0
        }
    
    correct = sum(1 for f in feedback_data if f.get('is_correct', False))
    total = len(feedback_data)
    
    return {
        'total_feedback': total,
        'correct_predictions': correct,
        'incorrect_predictions': total - correct,
        'accuracy': correct / total if total > 0 else 0.0
    }

def get_incorrect_predictions():
    feedback_data = load_feedback()
    return [f for f in feedback_data if not f.get('is_correct', True)]

def preprocess_img_for_training(img_path):
    img = cv2.imread(img_path)
    if img is None:
        return None
    img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    img = cv2.resize(img, img_size)
    return img.astype(np.float32)

def retrain_model_with_feedback():
    global model
    feedback_data = get_incorrect_predictions()
    
    if not feedback_data:
        print("no incorrect predictions to retrain on")
        return False
    
    print(f"retraining model with {len(feedback_data)} feedback samples...")
    
    x_feedback = []
    y_feedback = []
    
    for entry in feedback_data:
        file_path = entry.get('file_path')
        if not file_path or not os.path.exists(file_path):
            continue
        
        user_feedback = entry.get('user_feedback', 'REAL')
        label = 0 if user_feedback == 'FAKE' else 1
        
        if entry.get('file_type') == 'image':
            img = preprocess_img_for_training(file_path)
            if img is not None:
                x_feedback.append(img)
                y_feedback.append(label)
    
    if len(x_feedback) == 0:
        print("no valid feedback images found")
        return False
    
    if model is None:
        if not os.path.exists('deepfake_model.h5'):
            print("no model found, cannot retrain")
            return False
        load_model()
    
    x_feedback = np.array(x_feedback)
    y_feedback = np.array(y_feedback)
    
    data_aug = tf.keras.Sequential([
        tf.keras.layers.RandomFlip('horizontal'),
        tf.keras.layers.RandomRotation(0.1),
        tf.keras.layers.RandomZoom(0.1),
        tf.keras.layers.RandomContrast(0.1),
        tf.keras.layers.RandomBrightness(0.1),
    ])
    
    feedback_ds = tf.data.Dataset.from_tensor_slices((x_feedback, y_feedback))
    feedback_ds = feedback_ds.batch(8)
    feedback_ds = feedback_ds.map(lambda x, y: (data_aug(x, training=True), y))
    feedback_ds = feedback_ds.shuffle(1000).repeat(2)
    
    feedback_size = len(x_feedback)
    val_size = max(1, feedback_size // 5)
    train_feedback_ds = feedback_ds.skip(val_size)
    val_feedback_ds = feedback_ds.take(val_size)
    
    model.compile(
        optimizer=tf.keras.optimizers.Adam(learning_rate=1e-5),
        loss='binary_crossentropy',
        metrics=['accuracy']
    )
    
    try:
        history = model.fit(
            train_feedback_ds,
            validation_data=val_feedback_ds,
            epochs=5,
            steps_per_epoch=max(5, (feedback_size - val_size) // 8),
            validation_steps=max(1, val_size // 8),
            verbose=0
        )
        
        model.save('deepfake_model.h5')
        print(f"model updated successfully (val acc: {history.history['val_accuracy'][-1]:.4f})")
        return True
    except Exception as e:
        print(f"error during retraining: {str(e)}")
        return False


@app.get("/")
async def root():
    return {
        "status": "running",
        "model_loaded": model is not None
    }

@app.post("/predict/image")
async def predict_image_endpoint(file: UploadFile = File(...)):
    if not file.content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="file must be an image")
    
    with tempfile.NamedTemporaryFile(delete=False, suffix=os.path.splitext(file.filename)[1]) as tmp_file:
        shutil.copyfileobj(file.file, tmp_file)
        tmp_path = tmp_file.name
    
    try:
        image_array = preprocess_image(tmp_path)
        if image_array is None:
            raise HTTPException(status_code=400, detail="could not process image")
        
        result = predict_image(image_array)
        prediction_label = 'FAKE' if result['is_fake'] else 'REAL'
        
        return JSONResponse(content={
            "success": True,
            "filename": file.filename,
            "prediction": result,
            "prediction_label": prediction_label,
            "message": f"predicted: {prediction_label} ({result['confidence']:.2%})",
            "feedback_info": {
                "note": "if prediction is wrong, submit feedback with correct label",
                "feedback_endpoint": "/feedback",
                "model_prediction": prediction_label,
                "model_confidence": result['confidence'],
                "raw_score": result['raw_score']
            }
        })
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"error: {str(e)}")
    
    finally:
        if os.path.exists(tmp_path):
            os.unlink(tmp_path)

@app.post("/predict/video")
async def predict_video_endpoint(file: UploadFile = File(...), num_frames: int = 10):
    if not file.content_type.startswith("video/"):
        raise HTTPException(status_code=400, detail="file must be a video")
    
    if num_frames < 1 or num_frames > 100:
        raise HTTPException(status_code=400, detail="num_frames must be 1-100")
    
    suffix = os.path.splitext(file.filename)[1] or ".mp4"
    with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as tmp_file:
        shutil.copyfileobj(file.file, tmp_file)
        tmp_path = tmp_file.name
    
    try:
        video_info = get_video_info(tmp_path)
        frames, _ = process_video_for_prediction(tmp_path, num_frames=num_frames)
        
        if not frames:
            raise HTTPException(status_code=400, detail="could not extract frames")
        
        result = predict_video_frames(frames)
        
        prediction_label = 'FAKE' if result['is_fake'] else 'REAL'
        
        return JSONResponse(content={
            "success": True,
            "filename": file.filename,
            "video_info": {
                "duration": float(video_info["duration"]),
                "fps": float(video_info["fps"]),
                "frame_count": int(video_info["frame_count"]),
                "resolution": f"{video_info['width']}x{video_info['height']}"
            },
            "prediction": result,
            "prediction_label": prediction_label,
            "message": f"predicted: {prediction_label} ({result['confidence']:.2%})",
            "feedback_info": {
                "note": "if prediction is wrong, submit feedback with correct label",
                "feedback_endpoint": "/feedback",
                "model_prediction": prediction_label,
                "model_confidence": result['confidence'],
                "raw_score": result['raw_score']
            }
        })
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"error: {str(e)}")
    
    finally:
        if os.path.exists(tmp_path):
            os.unlink(tmp_path)

@app.post("/feedback")
async def submit_feedback(
    file: UploadFile = File(...),
    user_feedback: str = Form(...)
):
    
    if user_feedback not in ['FAKE', 'REAL']:
        raise HTTPException(status_code=400, detail="user_feedback must be FAKE or REAL")
    
    feedback_dir = 'feedback_files'
    os.makedirs(feedback_dir, exist_ok=True)
    
    file_ext = os.path.splitext(file.filename)[1]
    saved_file_path = os.path.join(feedback_dir, f"feedback_{len(load_feedback()) + 1}{file_ext}")
    
    with open(saved_file_path, 'wb') as f:
        shutil.copyfileobj(file.file, f)
    
    if file.content_type.startswith('image/'):
        file_type = 'image'
    elif file.content_type.startswith('video/'):
        file_type = 'video'
    else:
        raise HTTPException(status_code=400, detail="file must be an image or video")
    
    try:
        if file_type == 'image':
            image_array = preprocess_image(saved_file_path)
            if image_array is None:
                raise HTTPException(status_code=400, detail="could not process image")
            result = predict_image(image_array)
        else:
            frames, _ = process_video_for_prediction(saved_file_path, num_frames=10)
            if not frames:
                raise HTTPException(status_code=400, detail="could not extract frames from video")
            result = predict_video_frames(frames)
        
        model_prediction = 'FAKE' if result['is_fake'] else 'REAL'
        model_confidence = result['confidence']
        raw_score = result['raw_score']
        
        feedback_entry = add_feedback(
            file_type=file_type,
            file_path=saved_file_path,
            model_prediction=model_prediction,
            model_confidence=model_confidence,
            user_feedback=user_feedback,
            raw_score=raw_score,
            additional_info={}
        )
        
        retrained = False
        if not feedback_entry['is_correct']:
            retrained = retrain_model_with_feedback()
        
        return JSONResponse(content={
            "success": True,
            "message": "feedback submitted",
            "feedback_id": feedback_entry['id'],
            "is_correct": feedback_entry['is_correct'],
            "model_retrained": retrained
        })
    except Exception as e:
        if os.path.exists(saved_file_path):
            os.unlink(saved_file_path)
        raise HTTPException(status_code=500, detail=f"error: {str(e)}")

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
