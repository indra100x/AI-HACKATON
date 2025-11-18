import os
import tensorflow as tf
from tensorflow import keras
from tensorflow.keras import layers
from tensorflow.keras.applications import MobileNetV2

IMG_SIZE = (224, 224)
BATCH_SIZE = 8
EPOCHS = 40
DATA_DIR = 'dataset'

os.environ['TF_FORCE_GPU_ALLOW_GROWTH'] = 'true'
try:
    tf.keras.mixed_precision.set_global_policy('mixed_float16')
except:
    pass

print("Loading dataset...")
train_ds = keras.preprocessing.image_dataset_from_directory(
    DATA_DIR,
    validation_split=0.2,
    subset='training',
    seed=42,
    image_size=IMG_SIZE,
    batch_size=BATCH_SIZE,
    label_mode='binary'
)

val_ds = keras.preprocessing.image_dataset_from_directory(
    DATA_DIR,
    validation_split=0.2,
    subset='validation',
    seed=42,
    image_size=IMG_SIZE,
    batch_size=BATCH_SIZE,
    label_mode='binary'
)

train_ds = train_ds.take(1500)  
val_ds = val_ds.take(500)

print("Creating model...")
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
    optimizer='adam',
    loss='binary_crossentropy',
    metrics=['accuracy']
)

print("Training model...")
history = model.fit(
    train_ds,
    validation_data=val_ds,
    epochs=EPOCHS,
    verbose=1
)

print("Saving model...")
model.save('deepfake_model.h5')
print("Done")

print(f"Train Accuracy: {history.history['accuracy'][-1]:.4f}")
print(f"Val Accuracy: {history.history['val_accuracy'][-1]:.4f}")

