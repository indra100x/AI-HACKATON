import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { toast } from "react-toastify";
import { 
  faImage, faVideo, faLink, faCode, faTimes, faEye, faSave, faArrowLeft, 
  faQuestionCircle, faComments, faNewspaper, faFileAlt, faBook, faLock
} from "@fortawesome/free-solid-svg-icons";

const Home = () => {
    const navigate = useNavigate();
    const [attachment, setAttachment] = useState({})
    const [pull, setPull] = useState(false)
    const [upload, setUpload] = useState(false)
    const handleUpload = () =>{
        setUpload(true)
        
        const formData = new FormData();
        formData.append('file', attachment.file);
        
        const token = localStorage.getItem('token');
        if (!token) {
            toast.error('Please login first');
            setUpload(false);
            return;
        }
        
        const uploadUrl = `${import.meta.env.VITE_APP_API.replace(/\/$/, '')}/api/documents/upload`;

        fetch(uploadUrl, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
          },
          body: formData,
        })
        .then(async (res) => {
          const contentType = res.headers.get('content-type') || '';
          let body = null;
          try {
            if (contentType.includes('application/json')) {
              body = await res.json();
            } else {
              body = await res.text();
            }
          } catch (e) {
            body = `Unable to parse response: ${e.message}`;
          }
          console.log('Upload response', { status: res.status, ok: res.ok, body });
          if (!res.ok) {
            const message = (body && body.message) ? body.message : (typeof body === 'string' ? body : 'Upload failed');
            toast.error(message || 'Upload failed');
            setUpload(false);
            return;
          }

          if ((body && body.message === 'success') || (body && body.document && body.document.id)) {
            toast.success('Document uploaded successfully');
            setAttachment({});
            setPull(false);
            setUpload(false);
            setTimeout(() => navigate('/'), 1000);
          } else {
            toast.error((body && body.message) || 'Upload failed');
            setUpload(false);
          }
        })
        .catch(err => {
          console.error('Upload error:', err);
          toast.error('Upload error: ' + (err.message || err));
          setUpload(false);
        });
    }
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
                        <button id="submit" type="button" onClick={handleUpload} className="p-3 m-5 rounded-xl bg-blue-500 text-white hover:opacity-80 cursor-pointer">{!upload ? "upload" : "uploading..."}</button>
                        <button id="remove" type="button" onClick={remove} className="p-3 m-5 rounded-xl bg-red-500 text-white hover:opacity-80 cursor-pointer">remove</button>
                    </div>
                    
                    </>
                )}
            </div>
        </div>
    );
};
export default Home;