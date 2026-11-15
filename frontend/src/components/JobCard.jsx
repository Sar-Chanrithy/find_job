export default function JobCard({ job, saved, toggle }) {
  return <article className="job-card">
    <div className="card-top">
      <span className="logo" style={{ color: job.color, background: job.color + '12' }}>{job.logo}</span>
      <button className="save" aria-label={`${saved.includes(job.id) ? 'Unsave' : 'Save'} ${job.title}`} aria-pressed={saved.includes(job.id)} onClick={() => toggle(job.id)}>{saved.includes(job.id) ? '♥' : '♡'}</button>
    </div>
    {job.is_demo && <span className="demo">Sample listing</span>}
    <h3><a href={`#/jobs/${job.id}`}>{job.title}</a></h3><p>{job.company}</p>
    <small>⌖ {job.location}, Cambodia</small>
    <div className="tags"><span>{job.type}</span><span>{job.mode}</span></div>
    <p className="salary">{job.salary} <small>/ month</small></p>
    <div className="card-bottom"><small>{job.days} days ago</small><a href={`#/jobs/${job.id}`}>View job ↗</a></div>
  </article>;
}
