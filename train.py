import os
import tensorflow as tf
from tensorflow import keras
from tensorflow.keras import layers
from tensorflow.keras.applications import MobileNetV2

img_size = (224, 224)
batch_size = 8
epochs = 40
data_dir = 'dataset'

os.environ['TF_FORCE_GPU_ALLOW_GROWTH'] = 'true'
try:
    tf.keras.mixed_precision.set_global_policy('mixed_float16')
except:
    pass

print("loading dataset...")
train_ds = keras.preprocessing.image_dataset_from_directory(
    data_dir,
    validation_split=0.2,
    subset='training',
    seed=42,
    image_size=img_size,
    batch_size=batch_size,
    label_mode='binary'
)

val_ds = keras.preprocessing.image_dataset_from_directory(
    data_dir,
    validation_split=0.2,
    subset='validation',
    seed=42,
    image_size=img_size,
    batch_size=batch_size,
    label_mode='binary'
)

train_ds = train_ds.take(800)
val_ds = val_ds.take(500)

print("creating model...")
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

print("training...")
history = model.fit(
    train_ds,
    validation_data=val_ds,
    epochs=epochs,
    verbose=1
)

print("saving model...")
model.save('deepfake_model.h5')
print("done")

print(f"train acc: {history.history['accuracy'][-1]:.4f}")
print(f"val acc: {history.history['val_accuracy'][-1]:.4f}")

