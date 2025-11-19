import React from 'react';
import { Navigate,useLocation } from 'react-router-dom';
import { useAuth } from './AuthContext';

const ProtectedRoute = ({ children }) => {
  const { user, loading } = useAuth();
  const navigate = useLocation();
  if (loading) return null;
  if (!user) {
    return <Navigate to="/landing" state={{ from: navigate }} replace />;
  }

  return children;
};

export default ProtectedRoute;