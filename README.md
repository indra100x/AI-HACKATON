# AI-HACKATON: Fake Detector 🔍

An intelligent fake detection system built using fine-tuned AI models. This full-stack application combines machine learning with a modern web interface to identify and classify fake content across various media types.

## 📋 Project Overview

**AI-HACKATON Fake Detector** is a hackathon submission that leverages fine-tuned deep learning models to detect fake content. The system is designed to analyze input data and determine authenticity with high accuracy. This project demonstrates the complete pipeline from model fine-tuning to production deployment.

### Key Features
- 🤖 Fine-tuned AI models for fake content detection
- 🎯 High accuracy classification
- 💻 User-friendly web interface
- ⚡ Fast inference and results
- 🔄 Real-time analysis
- 📊 Detailed reports and confidence scores

## 🏗️ Project Structure

```
AI-HACKATON/
├── back-end/           # Python backend API and fine-tuned models
│   ├── models/         # Fine-tuned model files
│   ├── api/            # RESTful API endpoints
│   └── README.md       # Backend documentation
├── front/              # React + Vite frontend interface
│   ├── src/            # React components
│   ├── public/         # Static assets
│   └── README.md       # Frontend documentation
├── model/              # ML model training and fine-tuning
│   ├── train/          # Training scripts
│   ├── data/           # Training datasets
│   └── checkpoints/    # Fine-tuned model weights
└── README.md           # This file
```

### Backend (back-end/)
- Python-based RESTful API
- Model inference engine
- Fine-tuned model loading and management
- Request processing and response generation
- Integration with pre-trained and fine-tuned models

### Frontend (front/)
- React-based user interface
- Vite for fast development and builds
- File upload functionality
- Real-time result display
- Confidence score visualization
- Responsive design

### Machine Learning (model/)
- Model fine-tuning scripts
- Training dataset management
- Transfer learning implementation
- Model evaluation and testing
- Checkpoint management
- Performance metrics

## 🛠️ Technology Stack

### Languages
- **Python** - 100% of the codebase (backend, models, fine-tuning)

### Backend
- **Python** - Core language
- **FastAPI / Flask** - Web framework
- **PyTorch / TensorFlow** - Deep learning framework
- **Transformers** - Pre-trained model library (for fine-tuning)
- **SQLAlchemy** - Database ORM

### Frontend
- **React** - UI framework
- **Vite** - Build tool and dev server
- **JavaScript/TypeScript** - Frontend scripting
- **Axios** - HTTP client

### Machine Learning
- **PyTorch / TensorFlow** - Deep learning framework
- **Hugging Face Transformers** - Pre-trained models
- **scikit-learn** - Machine learning utilities
- **Pandas / NumPy** - Data processing
- **Matplotlib / Seaborn** - Visualization

### Data
- Custom labeled dataset for fine-tuning
- Fake vs. Authentic content samples

## 🚀 Getting Started

### Prerequisites
- Python 3.8+
- Node.js and npm
- Git
- GPU support (recommended for faster inference)

### Backend Setup

1. Navigate to the backend directory:
   ```bash
   cd back-end
   ```

2. Create a virtual environment:
   ```bash
   python -m venv venv
   source venv/bin/activate  # On Windows: venv\Scripts\activate
   ```

3. Install Python dependencies:
   ```bash
   pip install -r requirements.txt
   ```

4. Configure environment variables:
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

5. Start the backend server:
   ```bash
   python app.py
   # or
   uvicorn app:app --reload
   ```

   The API will be available at `http://localhost:8000`

### Frontend Setup

1. Navigate to the frontend directory:
   ```bash
   cd front
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start the development server:
   ```bash
   npm run dev
   ```

4. Open your browser to `http://localhost:5173`

### Model Setup & Fine-Tuning

1. Navigate to the model directory:
   ```bash
   cd model
   ```

2. Install model dependencies:
   ```bash
   pip install -r requirements.txt
   ```

3. Prepare your dataset:
   ```bash
   # Place your training data in data/
   python scripts/prepare_dataset.py
   ```

4. Fine-tune the model:
   ```bash
   python scripts/finetune.py --model base-model-name --epochs 10
   ```

5. Evaluate the model:
   ```bash
   python scripts/evaluate.py --model checkpoints/best-model
   ```

## 🔬 How Fake Detection Works

1. **Input**: User uploads content (image, text, audio, video, etc.)
2. **Preprocessing**: Content is preprocessed and normalized
3. **Model Inference**: Fine-tuned model analyzes the content
4. **Classification**: Model outputs authenticity score and prediction
5. **Results**: Frontend displays confidence level and detailed report
6. **Output**: User receives verdict - Fake, Authentic, or Uncertain

## 📊 Model Details

### Base Model
- Pre-trained transformer model (e.g., BERT, Vision Transformer, etc.)
- Transfer learning approach for improved performance

### Fine-Tuning Process
- Trained on labeled dataset of fake and authentic content
- Custom loss functions optimized for classification
- Data augmentation techniques applied
- Cross-validation for robust evaluation

### Performance Metrics
- Accuracy
- Precision & Recall
- F1-Score
- ROC-AUC

## 📚 Documentation

- See [back-end/README.md](./back-end/README.md) for backend API documentation
- See [front/README.md](./front/README.md) for frontend usage guide
- See [model/README.md](./model/README.md) for model fine-tuning details (if exists)

## 🔄 API Endpoints

### Detect Fake Content
```
POST /api/detect
Content-Type: multipart/form-data

Body:
  - file: <uploaded file>
  - content_type: "image" | "text" | "audio" | "video"

Response:
  {
    "prediction": "fake" | "authentic" | "uncertain",
    "confidence": 0.95,
    "scores": {
      "fake": 0.95,
      "authentic": 0.05
    },
    "processing_time": 1.23
  }
```

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

### Development Workflow
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Commit with clear messages (`git commit -m 'Add amazing feature'`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request

### Code Standards
- Follow PEP 8 for Python code
- Use ESLint for JavaScript/React code
- Write unit tests for new features
- Document your changes

## 🧪 Testing

### Backend Tests
```bash
cd back-end
pytest tests/
```

### Model Tests
```bash
cd model
python -m pytest tests/
```

### Frontend Tests
```bash
cd front
npm test
```

## 📝 License

This project is open source and available under the MIT License.

## 👤 Author

**indra100x** - Created for AI Hackathon 2025

## 🎯 Future Enhancements

- [ ] Multi-modal fake detection (image + text + audio)
- [ ] Real-time video stream analysis
- [ ] Browser extension for content verification
- [ ] API rate limiting and authentication
- [ ] Advanced analytics dashboard
- [ ] Model ensemble for improved accuracy
- [ ] Deployment to cloud platforms (AWS, GCP, Azure)

## 📞 Support

For issues, questions, or suggestions, please open an issue on the GitHub repository.

---

**Detect Fakes, Build Trust! 🛡️**

Last Updated: September 2026
