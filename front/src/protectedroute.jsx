import React from 'react';
import { Navigate,useLocation } from 'react-router-dom';

const ProtectedRoute = ({ children }) => {
  const token = localStorage.getItem('token');
  const navigate = useLocation();
  if (!token) {
    return <Navigate to="/landing" state={{ from: navigate }} replace />;
  }

  return children;
};

export default ProtectedRoute;