import json
import os
from datetime import datetime

feedback_file = 'feedback_data.json'

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

