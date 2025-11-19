import React, { createContext, useContext, useEffect, useState } from 'react';
import { apiFetch, apiBase } from './utils/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const init = async () => {
      const token = localStorage.getItem('token');
      if (token) {
        const { ok, body } = await apiFetch('/api/auth/user');
        if (ok && body.user) setUser(body.user);
        else {
          localStorage.removeItem('token');
          setUser(null);
        }
      }
      setLoading(false);
    };
    init();
  }, []);

  const login = async (credentials) => {
    const res = await fetch(`${apiBase}/api/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(credentials),
    });
    const data = await res.json();
    if (res.ok && (data.token || data.message === 'Login successful' || data.message === 'success')) {
      if (data.token) localStorage.setItem('token', data.token);
      // fetch user
      const { ok, body } = await apiFetch('/api/auth/user');
      if (ok && body.user) setUser(body.user);
      return { ok: true, data };
    }
    return { ok: false, data };
  };

  const register = async (payload) => {
    const res = await fetch(`${apiBase}/api/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await res.json();
    if (res.ok && data.token) {
      localStorage.setItem('token', data.token);
      const { ok, body } = await apiFetch('/api/auth/user');
      if (ok && body.user) setUser(body.user);
      return { ok: true, data };
    }
    return { ok: false, data };
  };

  const logout = async () => {
    try {
      await apiFetch('/api/auth/logout', { method: 'POST' });
    } catch (e) {
      // ignore
    }
    localStorage.removeItem('token');
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}

export default AuthContext;
