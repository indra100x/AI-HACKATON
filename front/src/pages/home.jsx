import React, { useState } from "react";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { toast } from "react-toastify";
import { 
  faImage, faVideo, faLink, faCode, faTimes, faEye, faSave, faArrowLeft, 
  faQuestionCircle, faComments, faNewspaper, faFileAlt, faBook, faLock
} from "@fortawesome/free-solid-svg-icons";

const Home = () => {
    const [attachment, setAttachment] = useState({})
    const [pull, setPull] = useState(false)
    const [upload, setUpload] = useState(false)
    const handleUpload = () =>{
        setUpload(true)
        document.querySelector("#submit").disabled = true
        document.querySelector("#submit").style = "background: grey"
        console.log(attachment)
    }
    const remove  = () => {
        setPull(false)
        setAttachment({})
    }
  const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    files.forEach(file => {
      if (file.size >= 100 * 1024 * 1024) {
        toast.error('حجم الملف كبير جداً. الحد الأقصى 100 ميجابايت.');
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
                        <button id="submit" onClick={handleUpload} className="p-3 m-5 rounded-xl bg-blue-500 text-white hover:opacity-80 cursor-pointer">{!upload ? "upload" : "uploading..."}</button>
                        <button id="remove" onClick={remove} className="p-3 m-5 rounded-xl bg-red-500 text-white hover:opacity-80 cursor-pointer">remove</button>
                    </div>
                    
                    </>
                )}
            </div>
        </div>
    );
};
export default Home;