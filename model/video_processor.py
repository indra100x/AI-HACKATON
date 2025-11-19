import cv2
import numpy as np
import os
from typing import List, Tuple, Optional

IMG_SIZE = (224, 224)  

def extract_frames_from_video(video_path: str, max_frames: Optional[int] = None, frame_interval: int = 1) -> List[np.ndarray]:
    if not os.path.exists(video_path):
        raise FileNotFoundError(f"Video file not found: {video_path}")
    
    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        raise ValueError(f"Could not open video file: {video_path}")
    
    frames = []
    frame_count = 0
    extracted_count = 0
    
    while True:
        ret, frame = cap.read()
        if not ret:
            break
        
        if frame_count % frame_interval == 0:
            processed_frame = preprocess_frame(frame)
            if processed_frame is not None:
                frames.append(processed_frame)
                extracted_count += 1
                
                if max_frames and extracted_count >= max_frames:
                    break
        
        frame_count += 1
    
    cap.release()
    return frames

def preprocess_frame(frame: np.ndarray) -> Optional[np.ndarray]:
    if frame is None:
        return None
    frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
    frame_resized = cv2.resize(frame_rgb, IMG_SIZE)
    return frame_resized

def extract_frames_uniform(video_path: str, num_frames: int = 10) -> List[np.ndarray]:
    if not os.path.exists(video_path):
        raise FileNotFoundError(f"Video file not found: {video_path}")
    
    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        raise ValueError(f"Could not open video file: {video_path}")
    
    total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
    fps = cap.get(cv2.CAP_PROP_FPS)
    duration = total_frames / fps if fps > 0 else 0
    
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
            processed_frame = preprocess_frame(frame)
            if processed_frame is not None:
                frames.append(processed_frame)
    
    cap.release()
    return frames

def get_video_info(video_path: str) -> dict:
    if not os.path.exists(video_path):
        raise FileNotFoundError(f"Video file not found: {video_path}")
    
    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        raise ValueError(f"Could not open video file: {video_path}")
    
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

def process_video_for_prediction(video_path: str, num_frames: int = 10) -> Tuple[List[np.ndarray], dict]:
    video_info = get_video_info(video_path)
    frames = extract_frames_uniform(video_path, num_frames=num_frames)
    
    return frames, video_info

