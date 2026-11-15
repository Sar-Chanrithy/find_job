import { useEffect, useState } from 'react';
import axios from 'axios';
import { api, apiErrorMessage } from './api';

export function useJobs(params = {}, enabled = true) {
  const key = JSON.stringify(params);
  const [attempt, setAttempt] = useState(0);
  const [state, setState] = useState({ key: null, jobs: [], meta: null, loading: true, error: '' });

  useEffect(() => {
    if (!enabled) return;
    const controller = new AbortController();
    setState({ key, jobs: [], meta: null, loading: true, error: '' });
    api.get('/jobs', { params: JSON.parse(key), signal: controller.signal })
      .then(({ data }) => setState({ key, jobs: data.data, meta: data.meta, loading: false, error: '' }))
      .catch(error => {
        if (!axios.isCancel(error)) setState({ key, jobs: [], meta: null, loading: false, error: apiErrorMessage(error) });
      });
    return () => controller.abort();
  }, [key, enabled, attempt]);

  const current = !enabled
    ? { jobs: [], meta: null, loading: false, error: '' }
    : state.key !== key ? { jobs: [], meta: null, loading: true, error: '' } : state;
  return { ...current, retry: () => setAttempt(value => value + 1) };
}

export function useJob(id) {
  const [attempt, setAttempt] = useState(0);
  const [state, setState] = useState({ job: null, loading: true, error: '' });
  useEffect(() => {
    const controller = new AbortController();
    setState({ job: null, loading: true, error: '' });
    api.get(`/jobs/${encodeURIComponent(id)}`, { signal: controller.signal })
      .then(({ data }) => setState({ job: data.data, loading: false, error: '' }))
      .catch(error => {
        if (!axios.isCancel(error)) setState({ job: null, loading: false, error: apiErrorMessage(error) });
      });
    return () => controller.abort();
  }, [id, attempt]);
  return { ...state, retry: () => setAttempt(value => value + 1) };
}
