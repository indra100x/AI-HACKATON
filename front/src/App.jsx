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
          <Route path='/' element={<ProtectedRoute><Home/></ProtectedRoute>}/>
          <Route path='/landing' element={<ProtectedLogin><Landing/></ProtectedLogin>}/>
          <Route path='/register' element={<ProtectedLogin><Register/></ProtectedLogin>}/>
          <Route path='/login' element={<ProtectedLogin><Login/></ProtectedLogin>}/>
        </Routes>
      </BrowserRouter>
      <ToastContainer />
    </>
  )
}

export default App
