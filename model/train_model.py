import os
import tensorflow as tf
from tensorflow import keras
from tensorflow.keras import layers
from tensorflow.keras.applications import MobileNetV2


IMG_SIZE = (224, 224)
BATCH_SIZE = 8
EPOCHS = 20
DATA_DIR = 'dataset'
MAX_TRAIN_BATCHES = 300  
MAX_VAL_BATCHES = 80      

os.environ['TF_FORCE_GPU_ALLOW_GROWTH'] = 'true'
try:
    tf.keras.mixed_precision.set_global_policy('mixed_float16')
except:
    pass

train_ds = keras.preprocessing.image_dataset_from_directory(
    DATA_DIR,
    validation_split=0.2,
    subset='training',
    seed=42,
    image_size=IMG_SIZE,
    batch_size=BATCH_SIZE,
    label_mode='binary'
).take(MAX_TRAIN_BATCHES)

val_ds = keras.preprocessing.image_dataset_from_directory(
    DATA_DIR,
    validation_split=0.2,
    subset='validation',
    seed=42,
    image_size=IMG_SIZE,
    batch_size=BATCH_SIZE,
    label_mode='binary'
).take(MAX_VAL_BATCHES)

train_ds = train_ds.prefetch(tf.data.AUTOTUNE)
val_ds = val_ds.prefetch(tf.data.AUTOTUNE)

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
    layers.Dense(256, activation='relu'),
    layers.Dropout(0.4),
    layers.Dense(1, activation='sigmoid', dtype='float32')
])

model.compile(
    optimizer=keras.optimizers.Adam(1e-3),
    loss='binary_crossentropy',
    metrics=['accuracy']
)

print("Training (Stage 1 – Frozen)…")
history1 = model.fit(
    train_ds,
    validation_data=val_ds,
    epochs=EPOCHS
)

base_model.trainable = True
for layer in base_model.layers[:-30]:
    layer.trainable = False

model.compile(
    optimizer=keras.optimizers.Adam(1e-5),
    loss='binary_crossentropy',
    metrics=['accuracy']
)

print("Training (Stage 2 – Fine Tuning)…")
history2 = model.fit(
    train_ds,
    validation_data=val_ds,
    epochs=30
)

model.save("deepfake_model.h5")
print("Done.")
