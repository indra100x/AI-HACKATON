import os
import tensorflow as tf

model = None

def load_model(model_path: str = "deepfake_model.h5"):
    global model
    if model is None:
        if not os.path.exists(model_path):
            raise FileNotFoundError(f"Model file not found: {model_path}")
        print(f"Loading model from {model_path}...")
        model = tf.keras.models.load_model(model_path)
        print("Model loaded successfully!")
    return model