import { useState } from 'react'
import { BrowserRouter,Navigate, Routes, Route } from 'react-router-dom'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'
import Home from './pages/home'
import Landing from './pages/landing'
import Register from './pages/register'
import Login from './pages/login'
import { ToastContainer } from 'react-toastify'
import ProtectedLogin from './protectedlogin'
import ProtectedRoute from './protectedroute'

function App() {

  return (
    <>
      <BrowserRouter>
        <Routes>
          {/* Public landing at root */}
          <Route path='/' element={<Landing/>} />
          {/* Keep explicit landing/login/register protected for auth flow */}
          <Route path='/landing' element={<Landing/>} />
          <Route path='/register' element={<ProtectedLogin><Register/></ProtectedLogin>} />
          <Route path='/login' element={<ProtectedLogin><Login/></ProtectedLogin>} />
          {/* Authenticated app under /app */}
          <Route path='/app' element={<ProtectedRoute><Home/></ProtectedRoute>} />
        </Routes>
      </BrowserRouter>
      <ToastContainer />
    </>
  )
}

export default App
