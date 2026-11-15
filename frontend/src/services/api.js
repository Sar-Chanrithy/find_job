import axios from 'axios';

// Vite forwards /api to Laravel in development. Only public configuration belongs here.
export const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  timeout: 10000,
  headers: { Accept: 'application/json' },
});

export function apiErrorMessage(error) {
  if (error.response?.status === 404) return 'This job is no longer available.';
  if (error.response?.status === 422) return 'Please check your search filters and try again.';
  if (error.response?.status === 429) return 'Too many requests. Please wait a minute and try again.';
  return 'We could not load jobs. Please check your connection and try again.';
}
