import { useState } from 'react'
import { BrowserRouter,Navigate, Routes, Route } from 'react-router-dom'
import reactLogo from './assets/react.svg'
import viteLogo from '/vite.svg'
import './App.css'
import Home from './pages/Home'
import { ToastContainer } from 'react-toastify'

function App() {

  return (
    <>
      <BrowserRouter>
        <Routes>
          <Route path='/' element={Home}/>
        </Routes>
      </BrowserRouter>
      <ToastContainer />
    </>
  )
}

export default App
