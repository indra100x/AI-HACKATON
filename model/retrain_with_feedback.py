import os
import json
import numpy as np
import tensorflow as tf
from tensorflow import keras
from tensorflow.keras import layers
from tensorflow.keras.applications import MobileNetV2
import cv2
from feedback import load_feedback, get_incorrect_predictions

IMG_SIZE = (224, 224)
BATCH_SIZE = 16
EPOCHS = 20
DATA_DIR = 'dataset'
FEEDBACK_FILE = 'feedback_data.json'

os.environ['TF_FORCE_GPU_ALLOW_GROWTH'] = 'true'
try:
    tf.keras.mixed_precision.set_global_policy('mixed_float16')
except:
    pass

def preprocess_image_for_training(image_path: str) -> np.ndarray:
    img = cv2.imread(image_path)
    if img is None:
        return None
    img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    img = cv2.resize(img, IMG_SIZE)
    img = img.astype(np.float32) / 255.0
    return img

def load_feedback_images():
    feedback_data = get_incorrect_predictions()
    
    if not feedback_data:
        print("No feedback data found. Training with original dataset only.")
        return None, None
    
    print(f"Found {len(feedback_data)} incorrect predictions in feedback")
    
    X_feedback = []
    y_feedback = []
    
    for entry in feedback_data:
        file_path = entry.get('file_path')
        if not file_path or not os.path.exists(file_path):
            continue
        
        user_feedback = entry.get('user_feedback', 'REAL')
        label = 0 if user_feedback == 'FAKE' else 1
        
        if entry.get('file_type') == 'image':
            img = preprocess_image_for_training(file_path)
            if img is not None:
                X_feedback.append(img)
                y_feedback.append(label)
    
    if len(X_feedback) == 0:
        print("No valid feedback images found")
        return None, None
    
    print(f"Loaded {len(X_feedback)} feedback images")
    return np.array(X_feedback), np.array(y_feedback)

print("Loading original dataset...")
train_ds = keras.preprocessing.image_dataset_from_directory(
    DATA_DIR,
    validation_split=0.2,
    subset='training',
    seed=42,
    image_size=IMG_SIZE,
    batch_size=BATCH_SIZE,
    label_mode='binary',
    shuffle=True
)

val_ds = keras.preprocessing.image_dataset_from_directory(
    DATA_DIR,
    validation_split=0.2,
    subset='validation',
    seed=42,
    image_size=IMG_SIZE,
    batch_size=BATCH_SIZE,
    label_mode='binary',
    shuffle=False
)

train_ds = train_ds.take(1500)
val_ds = val_ds.take(300)

print("Adding data augmentation...")
data_augmentation = keras.Sequential([
    layers.RandomFlip('horizontal'),
    layers.RandomRotation(0.1),
    layers.RandomZoom(0.1),
    layers.RandomContrast(0.1),
    layers.RandomBrightness(0.1),
])
train_ds = train_ds.map(lambda x, y: (data_augmentation(x, training=True), y))

X_feedback, y_feedback = load_feedback_images()

if X_feedback is not None and len(X_feedback) > 0:
    print("Combining feedback data with original dataset...")
    feedback_ds = tf.data.Dataset.from_tensor_slices((X_feedback, y_feedback))
    feedback_ds = feedback_ds.batch(BATCH_SIZE)
    feedback_ds = feedback_ds.map(lambda x, y: (data_augmentation(x, training=True), y))
    
    train_ds = train_ds.concatenate(feedback_ds)
    train_ds = train_ds.shuffle(10000)

print("Creating model...")
if os.path.exists('deepfake_model.h5'):
    print("Loading existing model...")
    model = keras.models.load_model('deepfake_model.h5')
    print("Model loaded. Continuing training with feedback data...")
else:
    print("Creating new model...")
    base_model = MobileNetV2(
        input_shape=(224, 224, 3),
        include_top=False,
        weights='imagenet'
    )
    base_model.trainable = False

    model = keras.Sequential([
        layers.Rescaling(1./255),
        base_model,
        layers.GlobalAveragePooling2D(),
        layers.Dense(128, activation='relu'),
        layers.Dropout(0.5),
        layers.Dense(1, activation='sigmoid', dtype='float32')
    ])

model.compile(
    optimizer=keras.optimizers.Adam(learning_rate=1e-4),
    loss='binary_crossentropy',
    metrics=['accuracy', keras.metrics.Precision(), keras.metrics.Recall(), keras.metrics.AUC()]
)

model.build(input_shape=(None, 224, 224, 3))
print(f"Model ready. Parameters: {model.count_params():,}")
print("Retraining model with feedback data...")

callbacks = [
    keras.callbacks.EarlyStopping(
        monitor='val_accuracy',
        patience=7,
        restore_best_weights=True,
        verbose=1
    ),
    keras.callbacks.ReduceLROnPlateau(
        monitor='val_loss',
        factor=0.5,
        patience=4,
        min_lr=1e-7,
        verbose=1
    ),
    keras.callbacks.ModelCheckpoint(
        'best_model_feedback.h5',
        save_best_only=True,
        monitor='val_accuracy',
        mode='max',
        verbose=1
    )
]

history = model.fit(
    train_ds,
    validation_data=val_ds,
    epochs=EPOCHS,
    callbacks=callbacks,
    verbose=1
)

print("Saving updated model...")
model.save('deepfake_model.h5')
print("Model updated and saved as 'deepfake_model.h5'")

print(f"Train Accuracy: {history.history['accuracy'][-1]:.4f} ({history.history['accuracy'][-1]*100:.2f}%)")
print(f"Val Accuracy: {history.history['val_accuracy'][-1]:.4f} ({history.history['val_accuracy'][-1]*100:.2f}%)")
if 'precision' in history.history:
    print(f"Val Precision: {history.history['val_precision'][-1]:.4f}")
    print(f"Val Recall: {history.history['val_recall'][-1]:.4f}")
    print(f"Val AUC: {history.history['val_auc'][-1]:.4f}")

