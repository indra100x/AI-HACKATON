import React from 'react';
import { Navigate,useLocation } from 'react-router-dom';
import { useAuth } from './AuthContext';

const ProtectedLogin = ({ children }) => {
  const { user, loading } = useAuth();
  const navigate = useLocation();

  if (loading) return null; // or spinner
  if (user) {
    return <Navigate to="/app" state={{ from: navigate }} replace />;
  }

  return children;
};

export default ProtectedLogin;