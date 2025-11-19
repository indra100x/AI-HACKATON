import {React, useState} from "react";
import { useNavigate } from "react-router-dom";
import { toast } from "react-toastify";
import { useAuth } from '../AuthContext';

const Login = () => {
    const navigate = useNavigate();
    const [loading,setLoading] = useState(false)
    const [user,setUser] = useState({
        email: "",
        password: "",
    })
    const auth = useAuth();
    const login = async () =>{
        setLoading(true)
        try {
            const res = await auth.login(user);
            if (res.ok) {
                const data = res.data;
                const apiBase = import.meta.env.VITE_APP_API.replace(/\/$/, '');
                if (data.redirectUrl === '/admin') {
                    window.location.href = `${apiBase}/admin`;
                    return;
                }
                navigate('/app');
                return;
            }
            toast.error((res.data && res.data.message) || 'Login failed');
        } catch (err) {
            console.error('Login error:', err);
            toast.error('Login error: ' + (err.message || err));
        } finally {
            setLoading(false);
        }
    }
    return (
        <div className="flex flex-col items-center justify-center min-h-screen bg-blue-100 p-4">
            <div className="w-full max-w-md border-blue-600 border-t-6  bg-white rounded-xl shadow-md p-6">
            <h3 className="text-xl font-semibold text-center mb-4">login</h3>
            <hr className="w-1/5 mx-auto border-t-2 border-blue-600"/>

            <form className="space-y-4">
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
                    type="button"
                    className="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-md transition"
                    onClick={login}
                    disabled={loading}
                >
                    {loading ? "connecting..." : "login"}
                </button>
                <p className="text-center text-sm text-gray-600 mt-4">
                don't have account ?{" "}
                <a href="/register" className="text-blue-600 hover:underline">
                    register
                </a>
                </p>
            </form>
        </div>
        </div>
    )
}
export default Login;