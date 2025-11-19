const apiBase = (import.meta.env.VITE_APP_API || '').replace(/\/$/, '') || '';

async function apiFetch(path, options = {}) {
  const token = typeof window !== 'undefined' ? localStorage.getItem('token') : null;
  const headers = (options.headers = options.headers || {});

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const res = await fetch(`${apiBase}${path}`, options);
  const contentType = res.headers.get('content-type') || '';
  const body = contentType.includes('application/json') ? await res.json() : { text: await res.text() };
  return { ok: res.ok, status: res.status, body };
}

export { apiFetch, apiBase };
