import os
import tensorflow as tf
from tensorflow import keras
from tensorflow.keras import layers
from tensorflow.keras.applications import MobileNetV2
import numpy as np

IMG_SIZE = (224, 224)
BATCH_SIZE = 64
EPOCHS = 50
MAX_TRAINING_TIME_MINUTES = 10
DATA_DIR = 'dataset'

os.environ['TF_FORCE_GPU_ALLOW_GROWTH'] = 'true'
try:
    tf.keras.mixed_precision.set_global_policy('mixed_float16')
    print("Mixed precision enabled")
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

train_samples = 20000
val_samples = 1000

train_batches = (train_samples + BATCH_SIZE - 1) // BATCH_SIZE
val_batches = (val_samples + BATCH_SIZE - 1) // BATCH_SIZE

train_ds = train_ds.take(train_batches)
val_ds = val_ds.take(val_batches)

print(f"Training samples: ~{train_batches * BATCH_SIZE} (limited to {train_samples})")
print(f"Validation samples: ~{val_batches * BATCH_SIZE} (limited to {val_samples})")

print("Adding data augmentation...")
data_augmentation = keras.Sequential([
    layers.RandomFlip('horizontal'),
    layers.RandomRotation(0.15),
    layers.RandomZoom(0.1),
    layers.RandomTranslation(0.1, 0.1),
    layers.RandomContrast(0.2),
    layers.RandomBrightness(0.2),
    layers.RandomWidth(0.1),
    layers.RandomHeight(0.1),
])

train_ds = train_ds.map(lambda x, y: (data_augmentation(x, training=True), y))
train_ds = train_ds.prefetch(tf.data.AUTOTUNE)
val_ds = val_ds.prefetch(tf.data.AUTOTUNE)

print("Creating model...")
base_model = MobileNetV2(
    input_shape=(224, 224, 3),
    include_top=False,
    weights='imagenet',
    alpha=1.0
)
base_model.trainable = False

model = keras.Sequential([
    layers.Rescaling(1./255),
    base_model,
    layers.GlobalAveragePooling2D(),
    layers.Dense(256, activation='relu'),
    layers.BatchNormalization(),
    layers.Dropout(0.5),
    layers.Dense(128, activation='relu'),
    layers.BatchNormalization(),
    layers.Dropout(0.3),
    layers.Dense(1, activation='sigmoid', dtype='float32')
])

initial_learning_rate = 0.001
lr_schedule = keras.optimizers.schedules.ExponentialDecay(
    initial_learning_rate,
    decay_steps=1000,
    decay_rate=0.96,
    staircase=True
)

model.compile(
    optimizer=keras.optimizers.Adam(learning_rate=lr_schedule),
    loss='binary_crossentropy',
    metrics=['accuracy', keras.metrics.Precision(), keras.metrics.Recall(), keras.metrics.AUC()]
)

print(f"Model parameters: {model.count_params():,}")

class TimeLimitCallback(keras.callbacks.Callback):
    def __init__(self, max_time_minutes):
        super().__init__()
        self.max_time_seconds = max_time_minutes * 60
        self.start_time = None
    
    def on_train_begin(self, logs=None):
        import time
        self.start_time = time.time()
    
    def on_epoch_end(self, epoch, logs=None):
        import time
        elapsed = time.time() - self.start_time
        if elapsed >= self.max_time_seconds:
            print(f"\nTime limit reached ({MAX_TRAINING_TIME_MINUTES} minutes). Stopping training...")
            self.model.stop_training = True

callbacks = [
    TimeLimitCallback(MAX_TRAINING_TIME_MINUTES),
    keras.callbacks.EarlyStopping(
        monitor='val_accuracy',
        patience=5,
        restore_best_weights=True,
        verbose=1,
        mode='max'
    ),
    keras.callbacks.ReduceLROnPlateau(
        monitor='val_loss',
        factor=0.5,
        patience=3,
        min_lr=1e-7,
        verbose=1,
        mode='min'
    ),
    keras.callbacks.ModelCheckpoint(
        'best_model.h5',
        save_best_only=True,
        monitor='val_accuracy',
        mode='max',
        verbose=1
    )
]

print("Training model...")
history = model.fit(
    train_ds,
    validation_data=val_ds,
    epochs=EPOCHS,
    callbacks=callbacks,
    verbose=1
)

print("Saving final model...")
model.save('deepfake_model.h5')
print("Done")

print("\n" + "="*60)
print("TRAINING RESULTS")
print("="*60)
print(f"Train Accuracy: {history.history['accuracy'][-1]:.4f} ({history.history['accuracy'][-1]*100:.2f}%)")
print(f"Val Accuracy: {history.history['val_accuracy'][-1]:.4f} ({history.history['val_accuracy'][-1]*100:.2f}%)")
if 'val_precision' in history.history:
    print(f"Val Precision: {history.history['val_precision'][-1]:.4f}")
    print(f"Val Recall: {history.history['val_recall'][-1]:.4f}")
    print(f"Val AUC: {history.history['val_auc'][-1]:.4f}")
print("="*60)

best_val_acc = max(history.history['val_accuracy'])
best_epoch = history.history['val_accuracy'].index(best_val_acc) + 1
print(f"\nBest validation accuracy: {best_val_acc:.4f} at epoch {best_epoch}")

