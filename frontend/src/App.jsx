import {useEffect,useState} from 'react';
import MainLayout from './layouts/MainLayout';
import Home from './pages/Home';
import Jobs from './pages/Jobs';
import JobDetails from './pages/JobDetails';
import Auth from './pages/Auth';
import './App.css';
export default function App(){const[route,setRoute]=useState(location.hash.slice(1)||'/');const[saved,setSaved]=useState(()=>{try{const data=JSON.parse(localStorage.getItem('devhire-saved')||'[]');return Array.isArray(data)?data:[]}catch{return[]}});useEffect(()=>{const change=()=>{setRoute(location.hash.slice(1)||'/');window.scrollTo(0,0)};window.addEventListener('hashchange',change);return()=>window.removeEventListener('hashchange',change)},[]);function toggle(id){setSaved(previous=>{const next=previous.includes(id)?previous.filter(x=>x!==id):[...previous,id];try{localStorage.setItem('devhire-saved',JSON.stringify(next))}catch{/* Session-only fallback. */}return next})}const props={saved,toggle};let page=<Home {...props}/>;if(route.startsWith('/jobs/'))page=<JobDetails key={route} id={route.split('/')[2]} {...props}/>;else if(route.startsWith('/jobs'))page=<Jobs key={route} route={route} {...props}/>;else if(route.startsWith('/login')||route.startsWith('/register'))page=<Auth key={route} register={route.startsWith('/register')}/>;else if(route!=='/')page=<section className="container empty"><h1>Page not found</h1><a href="#/">Return home</a></section>;return <MainLayout route={route}>{page}</MainLayout>}
