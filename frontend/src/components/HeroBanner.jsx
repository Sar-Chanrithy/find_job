import heroPortrait from '../assets/recruitment-hero-cutout.png';
import jobSearchBanner from '../assets/job-search-banner.png';
import { useEffect, useRef, useState } from 'react';
import SearchForm from './SearchForm';
import './HeroBanner.css';

function Icon({ type }) {
  const paths = {
    person: <><circle cx="12" cy="7" r="3"/><path d="M5 21v-3a7 7 0 0 1 14 0v3M16 11h6m-3-3v6"/></>,
    briefcase: <><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V4h8v3M3 12a20 20 0 0 0 18 0M10 12v3h4v-3"/></>,
    building: <><path d="M4 21V3h12v18M16 9h4v12M2 21h20M8 7h4M8 11h4M8 15h4M9 21v-3h2v3"/></>,
    check: <><path d="M12 2 3 6v6c0 5 9 10 9 10s9-5 9-10V6zM8 12l3 3 5-6"/></>,
  };
  return <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">{paths[type]}</svg>;
}

export default function HeroBanner({ total }) {
  const [slide, setSlide] = useState(0);
  const [playing, setPlaying] = useState(true);
  const [hovered, setHovered] = useState(false);
  const [focused, setFocused] = useState(false);
  const [hidden, setHidden] = useState(document.hidden);
  const [reducedMotion, setReducedMotion] = useState(() => window.matchMedia('(prefers-reduced-motion: reduce)').matches);
  const touchStart = useRef(null);
  const rotating = playing && !hovered && !focused && !hidden && !reducedMotion;

  useEffect(() => {
    const media = window.matchMedia('(prefers-reduced-motion: reduce)');
    const motionChange = () => setReducedMotion(media.matches);
    const visibilityChange = () => setHidden(document.hidden);
    media.addEventListener('change', motionChange);
    document.addEventListener('visibilitychange', visibilityChange);
    return () => {
      media.removeEventListener('change', motionChange);
      document.removeEventListener('visibilitychange', visibilityChange);
    };
  }, []);

  useEffect(() => {
    if (!rotating) return;
    const timer = window.setInterval(() => setSlide(current => (current + 1) % 2), 5500);
    return () => window.clearInterval(timer);
  }, [rotating]);

  function selectSlide(nex) {
    setSlide((next + 2) % 2);
    setPlaying(false);
  }

  return <section className={'recruitment-banner hero-carousel carousel-theme-' + slide}
    role="region" aria-roledescription="carousel" aria-label="Career highlights"
    onMouseEnter={() => setHovered(true)} onMouseLeave={() => setHovered(false)}
    onFocusCapture={() => setFocused(true)}
    onBlurCapture={event => { if (!event.currentTarget.contains(event.relatedTarget)) setFocused(false); }}>
    <div className="container recruitment-inner">
      <div className="hero-slide-window" aria-live={rotating ? 'off' : 'polite'}
        onPointerDown={event => { if (event.pointerType === 'touch') touchStart.current = { x: event.clientX, y: event.clientY }; }}
        onPointerCancel={() => { touchStart.current = null; }}
        onPointerUp={event => {
          if (!touchStart.current) return;
          const dx = event.clientX - touchStart.current.x;
          const dy = event.clientY - touchStart.current.y;
          touchStart.current = null;
          if (Math.abs(dx) > 55 && Math.abs(dx) > Math.abs(dy)) selectSlide(slide + (dx < 0 ? 1 : -1));
        }}>
      <div className={'hero-slide ' + (slide === 0 ? 'is-active' : '')} role="group" aria-roledescription="slide" aria-label="1 of 2: Skills and ambitions" aria-hidden={slide !== 0} inert={slide !== 0}>
      <div className="recruitment-copy">
        <p className="recruitment-eyebrow"><span/> Find the job that</p>
        <h1 id="hero-title">Matches Your Skills<br/><em>&amp; Ambitions.</em></h1>
        <p className="recruitment-description">Discover new opportunities with Cambodia's growing teams.<br className="desktop"/> Your next chapter starts with the right connection.</p>
        <div className="recruitment-actions">
          <a className="button" href="#/jobs"><Icon type="person"/>I'm looking for a job</a>
          <a className="button outline" href="#/register"><Icon type="briefcase"/>I'm hiring talent</a>
        </div>
      </div>
      <div className="recruitment-art">
        <div className="recruitment-disc" aria-hidden="true"/>
        <div className="recruitment-ring" aria-hidden="true"/>
        <svg className="recruitment-arrow" viewBox="0 0 110 80" fill="none" aria-hidden="true"><path d="M6 72C12 23 49 11 98 23m-13-15 15 15-19 9" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
        <span className="recruitment-dots dots-left" aria-hidden="true"/>
        <span className="recruitment-dots dots-right" aria-hidden="true"/>
        <img className="recruitment-person" src={heroPortrait} alt="Smiling professional in a purple blazer holding a tablet" fetchPriority="high" width="1024" height="1536"/>
        <div className="recruitment-cards">
          <a href="#/jobs" className="recruitment-stat"><Icon type="briefcase"/><span><strong>{total == null ? 'Explore' : total.toLocaleString()}</strong><small>{total == null ? 'Career opportunities' : 'Published sample jobs'}</small></span></a>
          <a href="#/jobs?location=Phnom%20Penh" className="recruitment-stat"><Icon type="building"/><span><strong>Local talent</strong><small>Opportunities in Cambodia</small></span></a>
          <a href="#/jobs" className="recruitment-stat"><Icon type="check"/><span><strong>Your next step</strong><small>A career that fits you</small></span></a>
        </div>
      </div>
      </div>
      <div className={'hero-slide job-search-slide ' + (slide === 1 ? 'is-active' : '')} role="group" aria-roledescription="slide" aria-label="2 of 2: Find your next opportunity" aria-hidden={slide !== 1} inert={slide !== 1}>
        <div className="recruitment-copy">
          <p className="recruitment-eyebrow"><span/> Your next chapter is waiting</p>
          <h2>Great Careers.<br/><em>Start Right Here.</em></h2>
          <p className="recruitment-description">Big ideas. Fresh opportunities. A place to grow.<br className="desktop"/> Find the role that brings out the best in you.</p>
          <div className="recruitment-actions">
            <a className="button" href="#/jobs"><Icon type="briefcase"/>Explore opportunities</a>
            <a className="button outline" href="#/register"><Icon type="person"/>Join DevHire</a>
          </div>
        </div>
        <div className="job-search-art"><img src={jobSearchBanner} alt="Job Search illustration with resumes, a briefcase, a globe and career icons" width="1536" height="1024"/><span className="job-search-caption">YOUR TALENT. YOUR FUTURE. YOUR MOVE.</span></div>
      </div>
      </div>
      <div className="recruitment-search">
        <SearchForm/>
        <div className="recruitment-popular"><strong>Popular searches:</strong>{['Designer', 'Developer', 'Marketing', 'Product Manager', 'Finance', 'Sales'].map(term => <a key={term} href={'#/jobs?q=' + encodeURIComponent(term)}>{term}</a>)}</div>
      </div>
      <div className="carousel-controls" aria-label="Banner controls">
        <button className="carousel-arrow" type="button" aria-label="Previous banner" onClick={() => selectSlide(slide - 1)}>‹</button>
        <div className="carousel-dots" role="group" aria-label="Choose a banner">
          {['Skills and ambitions', 'Job search'].map((label, index) => <button key={label} type="button" className={slide === index ? 'is-current' : ''} aria-label={`Show banner ${index + 1}: ${label}`} aria-pressed={slide === index} onClick={() => selectSlide(index)}><span/></button>)}
        </div>
        <button className="carousel-arrow" type="button" aria-label="Next banner" onClick={() => selectSlide(slide + 1)}>›</button>
        <span className="carousel-divider"/>
        <button className="carousel-play" type="button" disabled={reducedMotion} aria-label={playing ? 'Pause automatic banners' : 'Play automatic banners'} aria-pressed={playing && !reducedMotion} onClick={() => setPlaying(value => !value)}>{reducedMotion ? 'Reduced motion' : playing ? 'Ⅱ Pause' : '▷ Play'}</button>
        <span className="carousel-counter" aria-hidden="true">0{slide + 1} / 02</span>
      </div>
    </div>
  </section>;
}
