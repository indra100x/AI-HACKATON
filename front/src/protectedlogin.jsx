import React from 'react';
import { Navigate,useLocation } from 'react-router-dom';

const ProtectedLogin = ({ children }) => {
  const token = localStorage.getItem('token');
  const navigate = useLocation();
  if (token != null) {
    return <Navigate to="/" state={{ from: navigate }} replace />;
  }

  return children;
};

export default ProtectedLogin;