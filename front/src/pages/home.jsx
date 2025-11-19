import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { toast } from "react-toastify";
import { useAuth } from '../AuthContext';
import { 
  faImage, faVideo, faLink, faCode, faTimes, faEye, faSave, faArrowLeft, 
  faQuestionCircle, faComments, faNewspaper, faFileAlt, faBook, faLock
} from "@fortawesome/free-solid-svg-icons";

const Home = () => {
    const navigate = useNavigate();
    const [attachment, setAttachment] = useState({})
    const [pull, setPull] = useState(false)
    const [upload, setUpload] = useState(false)
    const [decisionResult, setDecisionResult] = useState(null)
    const [sendingToServer, setSendingToServer] = useState(false)
    const [isLoggingOut, setIsLoggingOut] = useState(false)

    const auth = useAuth();
    const handleLogout = async () => {
        setIsLoggingOut(true);
        try {
            await auth.logout();
            localStorage.removeItem('token');
            toast.success('Logged out successfully');
            navigate('/login');
        } catch (err) {
            console.error('Logout error:', err);
            toast.error('Logout error: ' + (err.message || err));
            setIsLoggingOut(false);
        }
    }
    // Step 1: upload file once to Laravel analyze endpoint which will store file and forward to FastAPI
    const handleUpload = async () => {
        if (!attachment.file) return;
        setUpload(true);

        const token = localStorage.getItem('token');
        if (!token) {
            toast.error('Please login first');
            setUpload(false);
            return;
        }

        const form = new FormData();
        form.append('file', attachment.file);

        const analyzeUrl = `${import.meta.env.VITE_APP_API.replace(/\/$/, '')}/api/documents/analyze`;

        try {
            const res = await fetch(analyzeUrl, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                },
                body: form,
            });

            const contentType = res.headers.get('content-type') || '';
            const body = contentType.includes('application/json') ? await res.json() : { message: await res.text() };

            if (!res.ok) {
                toast.error((body && body.detail) || (body && body.message) || 'Analyze failed');
                setUpload(false);
                return;
            }

            // show decision popup with prediction details and document id
            setDecisionResult({
                document: body.document,
                prediction_label: body.prediction && (body.prediction.prediction_label || body.prediction_label) || 'UNKNOWN',
                prediction: body.prediction || null,
                raw: body,
            });
            setUpload(false);

        } catch (err) {
            console.error('Analyze error:', err);
            toast.error('Analyze error: ' + (err.message || err));
            setUpload(false);
        }
    }

    // Step 2: after user chooses feedback, send rating to Laravel feedback endpoint (no file upload)
    const handleSendToLaravel = async (userFeedback) => {
    if (!decisionResult || !decisionResult.document) return;
    setSendingToServer(true);

    const token = localStorage.getItem('token');
    if (!token) {
        toast.error('Please login first');
        setSendingToServer(false);
        return;
    }

    const rating = userFeedback === decisionResult.prediction_label ? "positive" : "negative";

    const feedbackUrl = `${import.meta.env.VITE_APP_API.replace(/\/$/, '')}/api/documents/${decisionResult.document.id}/feedback`;

    try {
        const res = await fetch(feedbackUrl, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ rating }),
        });

        const contentType = res.headers.get('content-type') || '';
        const body = contentType.includes('application/json') ? await res.json() : { message: await res.text() };

        if (!res.ok) {
            toast.error((body && body.message) || (body && body.detail) || 'Submitting feedback failed');
            setSendingToServer(false);
            return;
        }

        toast.success('Feedback submitted successfully');
        setAttachment({});
        setPull(false);
        setDecisionResult(null);
        setSendingToServer(false);
        setTimeout(() => navigate('/'), 800);

    } catch (err) {
        toast.error('Feedback submit error: ' + (err.message || err));
        setSendingToServer(false);
    }
};


    const remove  = () => {
        setPull(false)
        setAttachment({})
    }
  const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
      if (file.size >= 50 * 1024 * 1024) {
        toast.error('File size too large. Maximum 50MB.');
      } else {
        setPull(true)
        setAttachment({
            id: Date.now() + Math.random(),
            file,
            name: file.name,
            type: file.type,
            preview: URL.createObjectURL(file) 
          });
      }
    });
  };
  console.log(attachment)
    return (
        <div className="flex flex-col items-center justify-center min-h-screen bg-blue-100 p-4">
            <div className="absolute top-5 right-5">
                <button 
                    type="button" 
                    onClick={handleLogout} 
                    disabled={isLoggingOut}
                    className="px-4 py-2 rounded-lg bg-red-500 text-white hover:opacity-80 cursor-pointer disabled:opacity-50"
                >
                    {isLoggingOut ? "logging out..." : "logout"}
                </button>
            </div>
            <div className="bg-white w-[90%] min-h-96 rounded-4xl p-10">
                <h1 className="font-bold">UPLOAD FILES</h1>
                {!pull && (
                <div className="border-2 bg-white border-dashed border-gray-300 rounded-lg p-6 mt-10 text-center">
                  <input
                    type="file"
                    onChange={handleFileUpload}
                    className="hidden"
                    id="file-upload"
                    accept="image/*,video/*"
                  />
                  <label htmlFor="file-upload" className="cursor-pointer">
                    <div className="text-gray-500 mb-2">
                      <FontAwesomeIcon icon={faImage} className="text-3xl mb-2" />
                      <p>اسحب الملفات هنا أو انقر لاختيارها</p>
                      <p className="text-sm">الحد الأقصى 10 ميجابايت لكل ملف</p>
                    </div>
                  </label>
                </div>
                )}
                {pull && (
                    <>
                    {attachment.type.startsWith("image/") &&(
                    <img className="w-[80%] m-auto mt-10" src={attachment.preview} alt="" />
                    )}
                    {attachment.type.startsWith("video/") &&(
                    <video className="w-[80%] m-auto mt-10" src={attachment.preview} controls alt="" />
                    )}
                    <div className="block w-fit mx-auto mt-5">
                        <button id="submit" type="button" onClick={handleUpload} className="p-3 m-5 rounded-xl bg-blue-500 text-white hover:opacity-80 cursor-pointer">{!upload ? "Analyze" : "analyzing..."}</button>
                        <button id="remove" type="button" onClick={remove} className="p-3 m-5 rounded-xl bg-red-500 text-white hover:opacity-80 cursor-pointer">remove</button>
                    </div>

                    {decisionResult && (
    <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
        <div className="bg-white p-6 rounded-lg w-[90%] max-w-md">
            <h2 className="font-bold mb-2">Model Decision</h2>

            <p className="mb-4">
                Prediction: <strong>{decisionResult.prediction_label}</strong>
            </p>

            <p className="mb-4">
                Message: <strong>{decisionResult.prediction.message}</strong>
            </p>

            {decisionResult?.prediction?.confidence !== undefined && (
                <p className="mb-4">
                    Confidence: {(decisionResult.prediction.confidence * 100).toFixed(2)}%
                </p>
            )}

            <p className="mb-4 text-sm text-gray-600">
                If the prediction is incorrect, please choose the correct label to help improve the model.
            </p>
            

            <div className="flex justify-between">
                <button 
                    onClick={() => handleSendToLaravel('REAL')} 
                    disabled={sendingToServer} 
                    className="px-4 py-2 rounded bg-green-500 text-white"
                >
                    REAL
                </button>

                <button 
                    onClick={() => handleSendToLaravel('FAKE')} 
                    disabled={sendingToServer} 
                    className="px-4 py-2 rounded bg-red-500 text-white"
                >
                    FAKE
                </button>

                <button 
                    onClick={() => { setDecisionResult(null); setUpload(false); }} 
                    disabled={sendingToServer} 
                    className="px-4 py-2 rounded bg-gray-300"
                >
                    Cancel
                </button>
            </div>
        </div>
    </div>
)}
                    
                    </>
                )}
            </div>
        </div>
    );
};
export default Home;