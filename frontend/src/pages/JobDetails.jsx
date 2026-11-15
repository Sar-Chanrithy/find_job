import { useState } from 'react';
import { useJob } from '../services/useJobs';
import RequestState from '../components/RequestState';

export default function JobDetails({ id, saved, toggle }) {
  const { job, loading, error, retry } = useJob(id);
  const [applied, setApplied] = useState(false);
  if (!job) return <section className="container empty"><RequestState loading={loading} error={error} retry={retry} /><a href="#/jobs">← Back to jobs</a></section>;

  return <>
    <section className="page-heading"><div className="container">
      <a href="#/jobs">← Back to jobs</a>
      <div className="detail-heading"><span className="logo" style={{ color: job.color }}>{job.logo}</span><div>
        <h1>{job.title}</h1><p>{job.company} · {job.location}, Cambodia</p>
        <div className="tags"><span>{job.type}</span><span>{job.mode}</span><span>{job.category}</span></div>
      </div></div>
    </div></section>
    <section className="container detail-layout">
      <article className="description">
        {job.is_demo && <span className="demo">Illustrative job listing</span>}
        <h2>Build something meaningful.</h2><p>{job.description}</p>
        <h2>What you'll do</h2><ul>{job.responsibilities.map((text, index) => <li key={index}>{text}</li>)}</ul>
        <h2>What you'll bring</h2><ul>{job.requirements.map((text, index) => <li key={index}>{text}</li>)}</ul>
        <h2>Room to grow</h2><p>{job.benefits}</p>
      </article>
      <aside className="job-summary">
        <h2>Job overview</h2>
        {[
          ['Monthly salary', job.salary], ['Location', job.location], ['Job type', job.type],
          ['Work arrangement', job.mode], ['Posted', `${job.days} days ago`],
        ].map(([label, value]) => <div className="summary-row" key={label}><small>{label}</small><strong>{value}</strong></div>)}
        <button className="button" onClick={() => setApplied(true)}>Apply for this job ↗</button>
        <button className="button light" onClick={() => toggle(job.id)}>{saved.includes(job.id) ? '♥ Saved to your jobs' : '♡ Save this job'}</button>
        {applied && <p className="notice" role="status">Applications are not open yet. No application was submitted. Account registration and secure applications are coming in the next phase.</p>}
      </aside>
    </section>
  </>;
}
