# AI-HACKATON 🚀

A full-stack AI application developed for a hackathon, combining a Python-based backend with modern frontend technologies to create an intelligent solution.

## 📋 Project Overview

**AI-HACKATON** is a hackathon submission that demonstrates the integration of AI/ML capabilities with a complete web application stack. The project is organized into three main components:

- **Backend** - Python-powered API and machine learning models
- **Frontend** - React + Vite interface for user interaction
- **Model** - AI/Machine Learning components

## 🏗️ Project Structure

```
AI-HACKATON/
├── back-end/           # Backend API and services
│   └── README.md       # Backend documentation
├── front/              # Frontend application
│   └── README.md       # Frontend documentation
├── model/              # ML models and algorithms
└── README.md           # This file
```

### Backend (back-end/)
- Built with Python
- RESTful API implementation
- Integration with Laravel framework
- API endpoints for model inference and data management
- Database connectivity and session management

### Frontend (front/)
- React-based UI built with Vite
- Fast development and build experience with HMR (Hot Module Replacement)
- Modern JavaScript framework
- Responsive user interface for interacting with AI features

### Machine Learning (model/)
- Python-based ML models
- Model training and inference
- Data processing pipelines
- Integration with backend API

## 🛠️ Technology Stack

### Languages
- **Python** - 100% of the codebase (backend, models, and utilities)

### Frontend
- **React** - UI framework
- **Vite** - Build tool and dev server
- **JavaScript/TypeScript** - Frontend scripting

### Backend
- **Python** - Core language
- **Laravel** - Web framework
- **RESTful APIs** - Communication protocol

### Machine Learning
- Python ML libraries (TensorFlow, PyTorch, scikit-learn, etc.)
- Model persistence and serving

## 🚀 Getting Started

### Prerequisites
- Python 3.8+
- Node.js and npm
- Git

### Backend Setup

1. Navigate to the backend directory:
   ```bash
   cd back-end
   ```

2. Install Python dependencies:
   ```bash
   pip install -r requirements.txt
   ```

3. Configure environment variables (create `.env` file):
   ```bash
   cp .env.example .env
   ```

4. Run the development server:
   ```bash
   python manage.py runserver
   ```

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

### Model Setup

1. Navigate to the model directory:
   ```bash
   cd model
   ```

2. Install model dependencies:
   ```bash
   pip install -r requirements.txt
   ```

3. Train or load pre-trained models as needed

## 📚 Documentation

- See [back-end/README.md](./back-end/README.md) for backend-specific documentation
- See [front/README.md](./front/README.md) for frontend-specific documentation

## 🔄 Workflow

1. **Frontend** (React/Vite) sends requests to the **Backend** API
2. **Backend** (Python/Laravel) processes requests and coordinates with ML models
3. **Model** (Python ML) performs inference or training as requested
4. **Backend** returns results to the **Frontend**
5. **Frontend** displays results to the user

## 🤝 Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues for bug reports and feature requests.

### Development Guidelines

- Follow PEP 8 for Python code
- Use ESLint for JavaScript/React code
- Write clear commit messages
- Test your changes before submitting PRs

## 📝 License

This project is open source and available under the MIT License.

## 👤 Author

**indra100x** - Created for AI Hackathon 2025

## 📞 Support

For issues, questions, or suggestions, please open an issue on the GitHub repository.

---

**Happy Hacking! 🎉**

Last Updated: November 2025
