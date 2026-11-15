export default function RequestState({ loading, error, retry }) {
  if (loading) return <div className="request-state" role="status">Loading opportunities…</div>;
  if (error) return <div className="request-state" role="alert"><p>{error}</p><button className="button" onClick={retry}>Try again</button></div>;
  return null;
}
