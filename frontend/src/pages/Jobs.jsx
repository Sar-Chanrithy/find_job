import { useState } from 'react';
import { useJobs } from '../services/useJobs';
import SearchForm from '../components/SearchForm';
import JobCard from '../components/JobCard';
import RequestState from '../components/RequestState';

export default function Jobs({ route, saved, toggle }) {
  const initial = Object.fromEntries(new URLSearchParams(route.split('?')[1]));
  const [filters, setFilters] = useState(initial);
  const [type, setType] = useState('');
  const [remote, setRemote] = useState(false);
  const [sort, setSort] = useState('featured');
  const [page, setPage] = useState(1);
  const [resetKey, setResetKey] = useState(0);
  const savedOnly = initial.saved === 'true';
  const { jobs, meta, loading, error, retry } = useJobs({
    q: filters.q || '', category: filters.category || '', location: filters.location || '',
    type, remote: remote ? 1 : 0, sort, page, per_page: 9,
    ...(savedOnly ? { ids: saved.slice(0, 100) } : {}),
  }, !savedOnly || saved.length > 0);

  function reset() {
    setFilters({}); setType(''); setRemote(false); setPage(1); setResetKey(value => value + 1);
  }

  return <>
    <section className="page-heading"><div className="container">
      <span className="overline">FIND YOUR NEXT CHAPTER</span>
      <h1>{savedOnly ? 'Your saved opportunities' : 'A world of opportunity.'}</h1>
      <p>Find the work that brings out the best in you.</p>
      <SearchForm key={resetKey} initial={filters} onSearch={values => { setFilters(values); setPage(1); }} />
    </div></section>
    <section className="container listing-layout">
      <aside className="filter-panel">
        <h3>Refine your search</h3>
        <label>Job type<select value={type} onChange={event => { setType(event.target.value); setPage(1); }}>
          <option value="">All types</option><option>Full time</option><option>Part time</option><option>Internship</option>
        </select></label>
        <label className="check"><input type="checkbox" checked={remote} onChange={event => { setRemote(event.target.checked); setPage(1); }} />Remote only</label>
        <button className="text-button" onClick={reset}>Reset filters</button>
        <p>Explore published opportunities. Sample listings are labelled. Applications will be available in a future update.</p>
      </aside>
      <div>
        <div className="results-heading">
          <p>{loading ? 'Searching…' : <><strong>{meta?.total || 0}</strong> opportunities found</>}</p>
          <select aria-label="Sort jobs" value={sort} onChange={event => { setSort(event.target.value); setPage(1); }}>
            <option value="featured">Featured first</option><option value="newest">Newest first</option>
          </select>
        </div>
        <RequestState loading={loading} error={error} retry={retry} />
        <div className="results-grid">{jobs.map(job => <JobCard key={job.id} job={job} saved={saved} toggle={toggle} />)}</div>
        {!loading && !error && !jobs.length && <div className="empty">
          <h2>No matching opportunities yet</h2><p>{savedOnly ? 'Save a published job to see it here.' : 'Try another keyword or broaden your filters.'}</p>
          <button className="button" onClick={() => { reset(); location.hash = '/jobs'; }}>Browse all jobs</button>
        </div>}
        {meta?.last_page > 1 && <div className="pagination">
          <button className="button light" disabled={page <= 1 || loading} onClick={() => setPage(value => value - 1)}>← Previous</button>
          <span>Page {meta.current_page} of {meta.last_page}</span>
          <button className="button light" disabled={page >= meta.last_page || loading} onClick={() => setPage(value => value + 1)}>Next →</button>
        </div>}
      </div>
    </section>
  </>;
}
