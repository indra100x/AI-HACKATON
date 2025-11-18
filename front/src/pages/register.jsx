import {React,useState} from "react";
import { useNavigate } from "react-router-dom";
import { toast } from "react-toastify";

const Register = () => {
    const navigate = useNavigate();
    const [loading,setLoading] = useState(false)
    const [user,setUser] = useState({
      name: "",
      email: "",
      password: "",
    })
    const reg = () =>{
        setLoading(true)
        fetch(`${import.meta.env.VITE_APP_API}api/auth/register`,{
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(user),
        }).then(res => res.json()).then(data => {
            if (data.message == "success") {
                localStorage.setItem("token",data.token)
                return navigate("/")
                console.log(data);
              }else{
                toast(data)
                setLoading(false)
              }
        })
    }
    return (
        <div className="flex flex-col items-center justify-center min-h-screen bg-blue-100 p-4">
            <div className="w-full max-w-md border-blue-600 border-t-6  bg-white rounded-xl shadow-md p-6">
            <h3 className="text-xl font-semibold text-center mb-4">create new account</h3>
            <hr className="w-1/5 mx-auto border-t-2 border-blue-600"/>

            <div className="space-y-4">
                <div>
                    <label className="block font-medium mb-1">full name</label>
                    <input
                    required
                    type="text"
                    onChange={(e) => setUser({...user,name:e.target.value})}
                    placeholder="enter your full name"
                    className="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label className="block font-medium mb-1">email</label>
                    <input
                    required
                    type="email"
                    onChange={(e) => setUser({...user,email:e.target.value})}
                    placeholder="enter your email"
                    className="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <div>
                    <label className="block font-medium mb-1">password</label>
                    <input
                    required
                    type="password"
                    onChange={(e) => setUser({...user,password:e.target.value})}
                    placeholder="enter your password"
                    className="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                </div>
                <button
                    type="submit"
                    className="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-md transition"
                    onClick={reg}
                    disabled={loading}
                >
                    {loading ? "registring" : "register"}
                </button>
                <p className="text-center text-sm text-gray-600 mt-4">
                already have account ?{" "}
                <a href="/login" className="text-blue-600 hover:underline">
                    login
                </a>
                </p>
            </div>
        </div>
        </div>
    )
}

export default Register;