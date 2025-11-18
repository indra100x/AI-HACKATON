import os
import numpy as np
import tensorflow as tf
from tensorflow import keras
from tensorflow.keras import layers
import cv2
from feedback import get_incorrect_predictions

img_size = (224, 224)
batch_size = 16
epochs = 20

os.environ['TF_FORCE_GPU_ALLOW_GROWTH'] = 'true'
try:
    tf.keras.mixed_precision.set_global_policy('mixed_float16')
except:
    pass

def preprocess_img(img_path):
    img = cv2.imread(img_path)
    if img is None:
        return None
    img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    img = cv2.resize(img, img_size)
    img = img.astype(np.float32) / 255.0
    return img

def load_feedback_images():
    feedback_data = get_incorrect_predictions()
    
    if not feedback_data:
        print("no feedback found")
        return None, None
    
    print(f"found {len(feedback_data)} incorrect predictions")
    
    x_feedback = []
    y_feedback = []
    
    for entry in feedback_data:
        file_path = entry.get('file_path')
        if not file_path or not os.path.exists(file_path):
            continue
        
        user_feedback = entry.get('user_feedback', 'REAL')
        label = 0 if user_feedback == 'FAKE' else 1
        
        if entry.get('file_type') == 'image':
            img = preprocess_img(file_path)
            if img is not None:
                x_feedback.append(img)
                y_feedback.append(label)
    
    if len(x_feedback) == 0:
        print("no valid feedback images")
        return None, None
    
    print(f"loaded {len(x_feedback)} feedback images")
    return np.array(x_feedback), np.array(y_feedback)

x_feedback, y_feedback = load_feedback_images()

if x_feedback is None or len(x_feedback) == 0:
    print("nothing to retrain")
    exit(0)

print(f"found {len(x_feedback)} feedback images to retrain")

if not os.path.exists('deepfake_model.h5'):
    print("no model found, train first")
    exit(1)

print("loading model...")
model = keras.models.load_model('deepfake_model.h5')
print("model loaded, training on feedback only")

data_aug = keras.Sequential([
    layers.RandomFlip('horizontal'),
    layers.RandomRotation(0.1),
    layers.RandomZoom(0.1),
    layers.RandomContrast(0.1),
    layers.RandomBrightness(0.1),
])

feedback_ds = tf.data.Dataset.from_tensor_slices((x_feedback, y_feedback))
feedback_ds = feedback_ds.batch(batch_size)
feedback_ds = feedback_ds.map(lambda x, y: (data_aug(x, training=True), y))
feedback_ds = feedback_ds.shuffle(1000).repeat(3)

feedback_size = len(x_feedback)
val_size = max(1, feedback_size // 5)
train_feedback_ds = feedback_ds.skip(val_size)
val_feedback_ds = feedback_ds.take(val_size)

model.compile(
    optimizer=keras.optimizers.Adam(learning_rate=1e-5),
    loss='binary_crossentropy',
    metrics=['accuracy']
)

print(f"training on {len(x_feedback)} samples")
print(f"train: {feedback_size - val_size}, val: {val_size}")

callbacks = [
    keras.callbacks.EarlyStopping(monitor='val_accuracy', patience=5, restore_best_weights=True, verbose=1),
    keras.callbacks.ReduceLROnPlateau(monitor='val_loss', factor=0.5, patience=3, min_lr=1e-7, verbose=1),
    keras.callbacks.ModelCheckpoint('best_model_feedback.h5', save_best_only=True, monitor='val_accuracy', mode='max', verbose=1)
]

history = model.fit(
    train_feedback_ds,
    validation_data=val_feedback_ds,
    epochs=epochs,
    steps_per_epoch=max(10, (feedback_size - val_size) // batch_size),
    validation_steps=max(1, val_size // batch_size),
    callbacks=callbacks,
    verbose=1
)

print("saving model...")
model.save('deepfake_model.h5')
print("done")

print(f"train acc: {history.history['accuracy'][-1]:.4f}")
print(f"val acc: {history.history['val_accuracy'][-1]:.4f}")

