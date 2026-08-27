'use client';

import React, { useState, useEffect, useRef } from 'react';
import type { LandingData } from '@/lib/types';
import { iconMap } from '@/lib/types';
import confetti from 'canvas-confetti';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';
import { Line } from 'react-chartjs-2';
import { Marquee } from './ui/marquee';
import { SpotlightCard } from './ui/spotlight-card';
import { BentoGrid, BentoCard } from './ui/bento-grid';
import { FloatingDock } from './ui/floating-dock';
import { CommandSearchDialog } from './ui/command-dialog';
import { Search, ShieldCheck, Zap, Smartphone, Award, Sparkles, CheckCircle2, ArrowRight } from 'lucide-react';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

interface Props {
  initialData: LandingData;
}

const acSuggestions = [
  { icon: '💻', text: 'Web Developer', cat: 'IT & Tech' },
  { icon: '📱', text: 'App Developer', cat: 'IT & Tech' },
  { icon: '📈', text: 'Digital Marketer', cat: 'IT & Tech' },
  { icon: '🎨', text: 'Graphic Designer', cat: 'Creative Arts' },
  { icon: '📷', text: 'Photographer', cat: 'Creative Arts' },
  { icon: '🎬', text: 'Video Editor', cat: 'Creative Arts' },
  { icon: '✍️', text: 'Content Writer', cat: 'Creative Arts' },
  { icon: '🔧', text: 'Plumber', cat: 'Skilled Trades' },
  { icon: '🪚', text: 'Carpenter', cat: 'Skilled Trades' },
  { icon: '🔌', text: 'Electrician', cat: 'Skilled Trades' },
  { icon: '🚗', text: 'Mechanic', cat: 'Skilled Trades' },
  { icon: '🏥', text: 'Home Nurse', cat: 'Health & Wellness' },
  { icon: '💪', text: 'Fitness Coach', cat: 'Health & Wellness' },
  { icon: '🏗️', text: 'Builder / Contractor', cat: 'Construction' },
  { icon: '🍽️', text: 'Private Chef', cat: 'Hospitality' },
  { icon: '🎉', text: 'Event Planner', cat: 'Hospitality' },
  { icon: '🚕', text: 'Driver', cat: 'Hospitality' },
  { icon: '📚', text: 'Math Tutor', cat: 'Education' },
  { icon: '🎵', text: 'Music Instructor', cat: 'Education' },
  { icon: '💼', text: 'Business Consultant', cat: 'Business Services' },
  { icon: '📊', text: 'Accountant', cat: 'Business Services' },
  { icon: '🌾', text: 'Farmer / Agri-tech', cat: 'Agriculture' },
  { icon: '📦', text: 'Delivery Rider', cat: 'Others' },
  { icon: '🌿', text: 'Landscaper / Gardener', cat: 'Others' },
  { icon: '🔐', text: 'Security Guard', cat: 'Others' },
];

const profs = [
  'Developers',
  'Carpenters',
  'Nurses',
  'Graphic Designers',
  'Chefs',
  'Electricians',
  'Teachers',
  'Photographers',
  'Mechanics',
  'Accountants',
  'Plumbers',
  'Event Planners',
];

const trends = [
  ['💻', 'Web Developer', '#1'],
  ['🎨', 'Graphic Designer', '#2'],
  ['🔧', 'Plumber', '#3'],
  ['🏥', 'Home Nurse', '#4'],
  ['🍽️', 'Private Chef', '#5'],
  ['📷', 'Photographer', '#6'],
  ['🔌', 'Electrician', '#7'],
  ['📱', 'App Developer', '#8'],
  ['🌿', 'Landscaper', '#9'],
  ['🎓', 'Math Tutor', '#10'],
];

const hotSlugs = ['it-tech', 'skilled-trades', 'hospitality', 'tech', 'trades', 'hosp', 'web-development', 'graphic-design'];
const avMap: Record<string, string> = {
  full_time: 'Full Time',
  part_time: 'Part Time',
  not_available: 'Unavailable',
};

function rankLabel(jobs: number) {
  if (jobs >= 50) return { i: '🏆', l: 'Elite Expert', c: 'rk-gold' };
  if (jobs >= 20) return { i: '⭐', l: 'Top Rated', c: 'rk-blue' };
  if (jobs >= 5) return { i: '📈', l: 'Rising Talent', c: 'rk-teal' };
  return { i: '🌱', l: 'New Freelancer', c: 'rk-dim' };
}

function initials(first: string = '', last: string = '') {
  return `${first.charAt(0)}${last.charAt(0)}`.toUpperCase() || 'GG';
}

function formatCurrency(amount: number) {
  return '₵' + Number(amount).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function timeAgo(dateString: string) {
  if (!dateString) return 'recently';
  const diff = (new Date().getTime() - new Date(dateString).getTime()) / 1000;
  if (diff < 60) return 'just now';
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  return `${Math.floor(diff / 86400)}d ago`;
}

interface ToastItem {
  id: number;
  title: string;
  msg: string;
  type: 'success' | 'error' | 'warning' | 'info';
}

export default function LandingPage({ initialData }: Props) {
  const { stats, categories, featured, matchedProviders, recentJobs, liveJobs, earningsData, earningsTotal, reviews } = initialData;

  // Language state
  const [lang, setLang] = useState<'en' | 'tw'>('en');

  // Theme state
  const [isLight, setIsLight] = useState(false);

  // Mobile menu state
  const [isMobOpen, setIsMobOpen] = useState(false);

  // Command palette state
  const [isCommandOpen, setIsCommandOpen] = useState(false);

  // Hero carousel
  const [heroSlide, setHeroSlide] = useState(0);

  // Hero panel
  const [panelSlide, setPanelSlide] = useState(0);

  // Profession ticker
  const [tickerIndex, setTickerIndex] = useState(0);
  const [tickerFade, setTickerFade] = useState(false);

  // Search input & autocomplete
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');
  const [autocompleteOpen, setAutocompleteOpen] = useState(false);

  // Reviews carousel
  const [rvPos, setRvPos] = useState(0);

  // Toast notifications
  const [toasts, setToasts] = useState<ToastItem[]>([]);

  // Back to top & Navbar scroll
  const [scrolledNav, setScrolledNav] = useState(false);

  // Newsletter email
  const [nlEmail, setNlEmail] = useState('');

  // Animated counters
  const [countProviders, setCountProviders] = useState(0);
  const [countJobs, setCountJobs] = useState(0);
  const [countCompleted, setCountCompleted] = useState(0);
  const [countEarnings, setCountEarnings] = useState(0);
  const statsRef = useRef<HTMLDivElement>(null);
  const [animatedStats, setAnimatedStats] = useState(false);

  const showToast = (title: string, msg: string, type: 'success' | 'error' | 'warning' | 'info' = 'info') => {
    const id = Date.now() + Math.random();
    setToasts((prev) => [...prev, { id, title, msg, type }]);
    setTimeout(() => {
      setToasts((prev) => prev.filter((t) => t.id !== id));
    }, 4200);
  };

  const triggerConfetti = () => {
    try {
      confetti({
        particleCount: 80,
        spread: 70,
        origin: { y: 0.7 },
        colors: ['#00D4C8', '#FF6B4A', '#7C6FF7', '#1FD9A0', '#F59E0B'],
      });
    } catch {
      // ignore
    }
  };

  // Initialize theme from localStorage
  useEffect(() => {
    const savedTheme = localStorage.getItem('gg_theme');
    if (savedTheme === 'light') {
      setIsLight(true);
      document.body.classList.add('lm');
    }
  }, []);

  const toggleTheme = () => {
    const nextTheme = !isLight;
    setIsLight(nextTheme);
    if (nextTheme) {
      document.body.classList.add('lm');
      localStorage.setItem('gg_theme', 'light');
    } else {
      document.body.classList.remove('lm');
      localStorage.setItem('gg_theme', 'dark');
    }
  };

  const toggleLang = () => {
    const nextLang = lang === 'en' ? 'tw' : 'en';
    setLang(nextLang);
    showToast('Language', `Switched to ${nextLang === 'en' ? 'English' : 'Twi'}`, 'info');
  };

  // Scroll listeners
  useEffect(() => {
    const handleScroll = () => {
      setScrolledNav(window.scrollY > 40);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  // Hero carousel interval
  useEffect(() => {
    const timer = setInterval(() => {
      setHeroSlide((prev) => (prev + 1) % 6);
    }, 5500);
    return () => clearInterval(timer);
  }, []);

  // Hero panel interval
  useEffect(() => {
    const timer = setInterval(() => {
      setPanelSlide((prev) => (prev + 1) % 3);
    }, 5200);
    return () => clearInterval(timer);
  }, []);

  // Profession ticker interval
  useEffect(() => {
    const timer = setInterval(() => {
      setTickerFade(true);
      setTimeout(() => {
        setTickerIndex((prev) => (prev + 1) % profs.length);
        setTickerFade(false);
      }, 280);
    }, 2600);
    return () => clearInterval(timer);
  }, []);

  // Reviews carousel auto-advance
  useEffect(() => {
    const timer = setInterval(() => {
      setRvPos((prev) => {
        const visible = window.innerWidth < 768 ? 1 : 2;
        const max = Math.max(0, reviews.length - visible);
        return prev >= max ? 0 : prev + 1;
      });
    }, 5800);
    return () => clearInterval(timer);
  }, [reviews.length]);

  // Stats Intersection Observer Animation
  useEffect(() => {
    if (!statsRef.current) return;
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !animatedStats) {
          setAnimatedStats(true);
          const duration = 1800;
          const steps = 60;
          const stepTime = duration / steps;

          let step = 0;
          const pTarget = stats.providers || 0;
          const jTarget = stats.jobs || 0;
          const cTarget = stats.completed || 0;
          const eTarget = Math.round((stats.earnings || 0) / 1000);

          const interval = setInterval(() => {
            step++;
            const progress = step / steps;
            setCountProviders(Math.floor(pTarget * progress));
            setCountJobs(Math.floor(jTarget * progress));
            setCountCompleted(Math.floor(cTarget * progress));
            setCountEarnings(Math.floor(eTarget * progress));
            if (step >= steps) {
              setCountProviders(pTarget);
              setCountJobs(jTarget);
              setCountCompleted(cTarget);
              setCountEarnings(eTarget);
              clearInterval(interval);
            }
          }, stepTime);
        }
      },
      { threshold: 0.3 }
    );
    observer.observe(statsRef.current);
    return () => observer.disconnect();
  }, [animatedStats, stats]);

  // Autocomplete filter
  const filteredSuggestions = searchQuery.trim()
    ? acSuggestions
        .filter(
          (d) =>
            d.text.toLowerCase().includes(searchQuery.toLowerCase()) ||
            d.cat.toLowerCase().includes(searchQuery.toLowerCase())
        )
        .slice(0, 6)
    : [];

  const handleSubscribeNL = (e: React.FormEvent) => {
    e.preventDefault();
    if (!nlEmail || !nlEmail.includes('@')) {
      showToast('Oops!', 'Please enter a valid email address.', 'error');
      return;
    }
    triggerConfetti();
    showToast('Subscribed! 🇬🇭', 'Thank you for joining GigGhana updates.', 'success');
    setNlEmail('');
  };

  // Chart Config
  const chartData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [
      {
        label: 'Earnings Released (₵)',
        data: earningsData,
        borderColor: '#00D4C8',
        backgroundColor: 'rgba(0,212,200,0.07)',
        borderWidth: 2.5,
        pointBackgroundColor: '#00D4C8',
        pointRadius: earningsData.some((v) => v > 0) ? 4 : 0,
        pointHoverRadius: 6,
        fill: true,
        tension: 0.42,
      },
    ],
  };

  const chartOptions = {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#13161E',
        titleColor: '#F2F4F8',
        bodyColor: '#4E5A6E',
        borderColor: 'rgba(0,212,200,0.15)',
        borderWidth: 1,
        titleFont: { family: 'Plus Jakarta Sans', weight: 700 as const },
        callbacks: {
          label: (context: any) =>
            ' ₵' + context.parsed.y.toLocaleString('en-GH', { minimumFractionDigits: 2 }),
        },
      },
    },
    scales: {
      x: {
        grid: { color: 'rgba(78,90,110,0.08)' },
        ticks: { color: '#4E5A6E', font: { size: 11, family: 'DM Sans' } },
      },
      y: {
        grid: { color: 'rgba(78,90,110,0.08)' },
        beginAtZero: true,
        ticks: {
          color: '#4E5A6E',
          font: { size: 11, family: 'DM Sans' },
          callback: (v: any) => '₵' + Number(v).toLocaleString(),
        },
      },
    },
  };

  return (
    <>
      {/* ══════ COMMAND SEARCH DIALOG (Cmd + K) ══════ */}
      <CommandSearchDialog
        open={isCommandOpen}
        onOpenChange={setIsCommandOpen}
        categories={categories}
      />

      {/* ══════ FLOATING QUICK DOCK ISLAND ══════ */}
      <FloatingDock onOpenSearch={() => setIsCommandOpen(true)} />

      {/* ══════ NAVBAR ══════ */}
      <nav className={`navbar ${scrolledNav ? 'on' : ''}`} id="nav">
        <a href="/" className="logo">
          <div className="logo-mark">G</div>
          <span className="logo-text">
            Gig<span>Ghana</span>
          </span>
        </a>
        <div className="nav-links">
          <a href="/search/providers.php">Find Talent</a>
          <a href="/jobs.php">Browse Jobs</a>
          <a href="#how">How It Works</a>
          <a href="#categories">Categories</a>
          <a href="#trending">Trending</a>
        </div>
        <div className="nav-acts">
          <button
            onClick={() => setIsCommandOpen(true)}
            className="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border border-[var(--border)] bg-[var(--surface)] text-[var(--tx-2)] hover:text-[var(--cyan)] hover:border-[rgba(0,212,200,0.3)] transition-all cursor-pointer"
            title="Search (Cmd + K)"
          >
            <Search className="w-3.5 h-3.5 text-[var(--cyan)]" />
            <span className="hidden md:inline">Quick Search</span>
            <kbd className="hidden md:inline-block text-[9px] font-mono px-1.5 py-0.5 rounded bg-[var(--surface-2)] text-[var(--tx-3)]">
              ⌘K
            </kbd>
          </button>

          <div className="lang-pill" onClick={toggleLang} title="Switch language">
            🌍 <span>{lang === 'en' ? 'EN' : 'TW'}</span>
            <div className="lang-inner">{lang === 'en' ? 'TW' : 'EN'}</div>
          </div>
          <button onClick={toggleTheme} className="btn-theme" title="Toggle theme">
            {isLight ? '☀️' : '🌙'}
          </button>
          <a href="/auth/login.php" className="btn btn-ghost">
            Sign In
          </a>
          <a
            href="/auth/register.php"
            className="btn btn-gold"
            onClick={triggerConfetti}
          >
            Get Started Free
          </a>
        </div>
        <div className="ham" onClick={() => setIsMobOpen(!isMobOpen)}>
          <span
            style={
              isMobOpen
                ? { transform: 'rotate(45deg) translate(5px, 5px)' }
                : undefined
            }
          />
          <span style={isMobOpen ? { opacity: 0 } : undefined} />
          <span
            style={
              isMobOpen
                ? { transform: 'rotate(-45deg) translate(5px, -5px)' }
                : undefined
            }
          />
        </div>
      </nav>

      <div className={`mobile-nav ${isMobOpen ? 'open' : ''}`}>
        <a href="/search/providers.php">Find Talent</a>
        <a href="/jobs.php">Browse Jobs</a>
        <a href="#how">How It Works</a>
        <a href="#categories">Categories</a>
        <a href="#trending">Trending</a>
        <a href="/auth/login.php">Sign In</a>
        <a href="/auth/register.php">Get Started Free</a>
      </div>

      {/* ══════ HERO ══════ */}
      <section className="hero">
        <div className="hero-slides">
          <div className={`hero-slide hs1 ${heroSlide === 0 ? 'active' : ''}`} />
          <div className={`hero-slide hs2 ${heroSlide === 1 ? 'active' : ''}`} />
          <div className={`hero-slide hs3 ${heroSlide === 2 ? 'active' : ''}`} />
          <div className={`hero-slide hs4 ${heroSlide === 3 ? 'active' : ''}`} />
          <div className={`hero-slide hs5 ${heroSlide === 4 ? 'active' : ''}`} />
          <div className={`hero-slide hs6 ${heroSlide === 5 ? 'active' : ''}`} />
        </div>

        {/* Hero Right Panel */}
        <div className="hero-panel">
          <div className={`panel-slide ${panelSlide === 0 ? 'active' : ''}`}>
            <div className="p-icon">🚀</div>
            <h3>Hire Elite African Talent</h3>
            <p>Vetted developers, designers, carpenters, nurses and more — ready to deliver world-class results.</p>
            <div className="panel-sub-badge">
              <div className="panel-sub-title">🆓 3 Jobs Free for Every Provider</div>
              <div className="panel-sub-text">Upgrade to Verified or Premium to unlock unlimited jobs &amp; top placement.</div>
            </div>
          </div>
          <div className={`panel-slide ${panelSlide === 1 ? 'active' : ''}`}>
            <div className="p-icon">🔒</div>
            <h3>Work &amp; Get Paid Securely</h3>
            <p>Escrow holds funds until you approve. Instant MoMo &amp; bank payouts for freelancers.</p>
          </div>
          <div className={`panel-slide ${panelSlide === 2 ? 'active' : ''}`}>
            <div className="p-icon">🌍</div>
            <h3>Every Ghanaian Skill Counts</h3>
            <p>From software engineers to skilled tradespeople — GigGhana connects every talent to paying opportunities.</p>
          </div>
          <div className="panel-dots">
            <div className={`p-dot ${panelSlide === 0 ? 'active' : ''}`} onClick={() => setPanelSlide(0)} />
            <div className={`p-dot ${panelSlide === 1 ? 'active' : ''}`} onClick={() => setPanelSlide(1)} />
            <div className={`p-dot ${panelSlide === 2 ? 'active' : ''}`} onClick={() => setPanelSlide(2)} />
          </div>
        </div>

        <div className="hero-content">
          <div className="hero-inner">
            <div className="hero-badge">
              <span>Ghana&apos;s #1 Marketplace for </span>
              <span className="ticker-wrap">
                <span
                  className="ticker-text"
                  style={{
                    opacity: tickerFade ? 0 : 1,
                    transform: tickerFade ? 'translateY(-8px)' : 'translateY(0)',
                  }}
                >
                  {profs[tickerIndex]}
                </span>
              </span>
            </div>
            <h1 className="hero-title">
              Your Skill. Your Success.
              <br />
              <span className="gold">Your Ghana.</span>
            </h1>
            <p className="hero-sub">
              {lang === 'en'
                ? 'Connecting every Ghanaian talent to opportunities that pay — across IT, trades, health, education, hospitality & more.'
                : 'GigGhana de Ghanafoɔ nyinaa ho adwuma na wɔtua ka pɛ — IT, adwuma, yadeɛ, adesua, ahosiesie ne ebi.'}
            </p>

            <div className="search-outer">
              <form
                className="search-wrap"
                onSubmit={(e) => {
                  e.preventDefault();
                  const target = `/search/providers.php?q=${encodeURIComponent(searchQuery)}&category=${encodeURIComponent(selectedCategory)}`;
                  window.location.href = target;
                }}
              >
                <svg
                  width="15"
                  height="15"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  style={{ color: 'rgba(255,255,255,0.45)', flexShrink: 0 }}
                >
                  <path
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => {
                    setSearchQuery(e.target.value);
                    setAutocompleteOpen(true);
                  }}
                  onFocus={() => setAutocompleteOpen(true)}
                  placeholder="e.g. Carpenter, Nurse, React Developer, Chef… (or ⌘K)"
                  autoComplete="off"
                />
                <div className="search-div" />
                <select
                  value={selectedCategory}
                  onChange={(e) => setSelectedCategory(e.target.value)}
                >
                  <option value="">All Categories</option>
                  {categories.map((cat) => (
                    <option key={cat.id} value={cat.id}>
                      {cat.name}
                    </option>
                  ))}
                </select>
                <button type="submit" className="btn btn-gold">
                  Search
                </button>
              </form>

              {autocompleteOpen && filteredSuggestions.length > 0 && (
                <div className="autocomplete-drop open">
                  {filteredSuggestions.map((m, idx) => (
                    <div
                      key={idx}
                      className="auto-item"
                      onClick={() => {
                        setSearchQuery(m.text);
                        setAutocompleteOpen(false);
                        window.location.href = `/search/providers.php?q=${encodeURIComponent(m.text)}`;
                      }}
                    >
                      <div className="auto-icon">{m.icon}</div>
                      <div>
                        <div className="auto-text">{m.text}</div>
                        <div className="auto-cat">{m.cat}</div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>

            <div className="hero-acts">
              <a href="/auth/register.php?role=client" className="btn btn-gold btn-lg">
                🏢 I Need Talent
              </a>
              <a href="/auth/register.php?role=provider" className="btn btn-blue btn-lg">
                💼 I Have Skills
              </a>
            </div>

            <div className="hero-trust">
              <div className="trust-i">
                <div className="dot dot-g" />
                Secure Escrow
              </div>
              <div className="trust-i">
                <div className="dot dot-b" />
                Ghana Card Verified
              </div>
              <div className="trust-i">
                <div className="dot dot-gr" />
                MoMo &amp; Card
              </div>
              <div className="trust-i">
                <div className="dot dot-i" />3 Jobs Free
              </div>
            </div>
          </div>
        </div>

        <div className="hero-dots">
          {[0, 1, 2, 3, 4, 5].map((idx) => (
            <div
              key={idx}
              className={`hero-dot ${heroSlide === idx ? 'active' : ''}`}
              onClick={() => setHeroSlide(idx)}
            />
          ))}
        </div>
      </section>

      {/* ══════ STATS ══════ */}
      <section className="stats-bar" ref={statsRef}>
        <div className="stats-grid">
          <div className="stat-card">
            <div className="stat-icon">👷</div>
            <div className="stat-number">{countProviders.toLocaleString()}</div>
            <div className="stat-label">Verified Freelancers</div>
          </div>
          <div className="stat-card">
            <div className="stat-icon">💼</div>
            <div className="stat-number">{countJobs.toLocaleString()}</div>
            <div className="stat-label">Open Jobs</div>
          </div>
          <div className="stat-card">
            <div className="stat-icon">✅</div>
            <div className="stat-number">{countCompleted.toLocaleString()}</div>
            <div className="stat-label">Jobs Completed</div>
          </div>
          <div className="stat-card">
            <div className="stat-icon">💰</div>
            <div className="stat-number">{countEarnings.toLocaleString()}K+</div>
            <div className="stat-label">GHS Paid to Talent</div>
          </div>
        </div>
      </section>

      {/* ══════ LIVE FEED ══════ */}
      {liveJobs.length > 0 && (
        <section className="section" style={{ paddingTop: '28px', paddingBottom: '28px' }}>
          <div style={{ maxWidth: '1160px', margin: '0 auto' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: '9px', marginBottom: '16px' }}>
              <div className="live-dot" />
              <h3 style={{ fontSize: '15px', fontFamily: 'var(--fm)', fontWeight: 700 }}>
                🔥 Live Job Feed
              </h3>
              <span style={{ fontSize: '11px', color: 'var(--tx-3)', marginLeft: 'auto' }}>
                Real-time updates
              </span>
            </div>
            {liveJobs.map((lj, idx) => (
              <a
                key={idx}
                href="/jobs.php"
                className="feed-item"
                style={{ textDecoration: 'none', color: 'var(--tx)' }}
              >
                <div>
                  <div className="feed-ttl">{lj.title}</div>
                  <div className="feed-meta">
                    {lj.cat_name || 'General'} · Posted {timeAgo(lj.created_at)}
                  </div>
                </div>
                <div
                  style={{
                    fontFamily: 'var(--fm)',
                    fontWeight: 700,
                    color: 'var(--cyan)',
                    whiteSpace: 'nowrap',
                    fontSize: '13px',
                  }}
                >
                  {formatCurrency(lj.budget_min)}
                  {lj.budget_type === 'hourly' ? '/hr' : ''}
                </div>
              </a>
            ))}
            <div style={{ textAlign: 'center', marginTop: '14px' }}>
              <a href="/jobs.php" className="btn btn-ghost">
                View All Jobs →
              </a>
            </div>
          </div>
        </section>
      )}

      {/* ══════ CATEGORIES WITH SPOTLIGHT CARDS ══════ */}
      <section className="section" id="categories">
        <div className="s-head">
          <div className="s-badge">Categories</div>
          <h2 className="s-title">Every Skill. Every Profession.</h2>
          <p className="s-sub">
            From cutting-edge tech to skilled trades — GigGhana covers every Ghanaian profession.
          </p>
        </div>
        <div className="cat-grid">
          {categories.map((cat) => {
            const isHot = hotSlugs.includes(cat.slug || String(cat.id));
            return (
              <SpotlightCard
                key={cat.id}
                spotlightColor="rgba(0, 212, 200, 0.16)"
                className="cat-card p-0"
              >
                <a
                  href={`/search/providers.php?category=${cat.id}`}
                  style={{ textDecoration: 'none', color: 'inherit', display: 'block', width: '100%', height: '100%' }}
                >
                  {isHot && <div className="cat-hot">🔥 HOT</div>}
                  <div className="cat-icon">{iconMap[cat.icon] || '🔧'}</div>
                  <div className="cat-name">{cat.name}</div>
                  {cat.description && <div className="cat-desc">{cat.description}</div>}
                  <div className="cat-arrow">Explore →</div>
                </a>
              </SpotlightCard>
            );
          })}
        </div>
      </section>

      {/* ══════ TRENDING ══════ */}
      <section className="section" id="trending" style={{ paddingTop: '10px', paddingBottom: '56px' }}>
        <div className="s-head">
          <div className="s-badge">Trending Now</div>
          <h2 className="s-title">Most In-Demand Skills</h2>
          <p className="s-sub">What Ghanaian businesses are searching for this week.</p>
        </div>
        <div className="trending-wrap">
          {trends.map(([ic, lb, nm], idx) => (
            <a
              key={idx}
              href={`/search/providers.php?q=${encodeURIComponent(lb)}`}
              className="trend-pill"
            >
              <span>{ic}</span>
              <span>{lb}</span>
              <span className="trend-num">{nm}</span>
            </a>
          ))}
        </div>
      </section>

      {/* ══════ AI MATCHED PROVIDERS WITH SPOTLIGHT ══════ */}
      {matchedProviders.length > 0 && (
        <section
          className="section"
          style={{ background: 'linear-gradient(180deg,transparent,rgba(0,212,200,0.02),transparent)' }}
        >
          <div className="s-head">
            <div className="s-badge">AI Matching</div>
            <h2 className="s-title">Recommended Freelancers</h2>
            <p className="s-sub">Smart suggestions based on platform ratings and activity.</p>
          </div>
          <div className="prov-grid">
            {matchedProviders.map((p, idx) => {
              const skills = p.skill_names ? p.skill_names.split('|').filter(Boolean) : [];
              const rk = rankLabel(Number(p.completed_jobs || 0));
              const init = initials(p.first_name, p.last_name);
              const jobs = Number(p.completed_jobs || 0);
              const bt = jobs >= 20 ? 'premium' : jobs >= 5 ? 'verified' : 'free';
              const rating = Number(p.rating_avg || 5);

              return (
                <SpotlightCard
                  key={idx}
                  spotlightColor="rgba(0, 212, 200, 0.18)"
                  className="prov-card p-0"
                >
                  <div className="prov-img-wrap">
                    {p.avatar ? (
                      <img src={p.avatar} alt={p.first_name} loading="lazy" />
                    ) : (
                      <div className="prov-initials">{init}</div>
                    )}
                    {p.is_verified ? <div className="prov-verified-badge">✓ Verified</div> : null}
                  </div>
                  <div className="prov-body">
                    <div className="prov-name">{`${p.first_name} ${p.last_name}`}</div>
                    <div className="prov-tag">
                      {p.tagline || `${p.experience_level ? p.experience_level.charAt(0).toUpperCase() + p.experience_level.slice(1) : ''} Freelancer`}
                    </div>
                    <div className="badge-row">
                      {bt === 'premium' ? (
                        <span className="badge-premium">⭐ Premium</span>
                      ) : bt === 'verified' ? (
                        <span className="badge-verified">✓ Verified</span>
                      ) : (
                        <span className="badge-free">🌱 Beginner</span>
                      )}
                    </div>
                    <div className="prov-stars">
                      {[1, 2, 3, 4, 5].map((s) => (
                        <span key={s}>{rating >= s ? '★' : rating >= s - 0.5 ? '✦' : '☆'}</span>
                      ))}
                    </div>
                    <div className="prov-rc">
                      {rating.toFixed(1)} · {Number(p.rating_count || 0)} reviews
                    </div>
                    <div className="prov-pills">
                      {skills.slice(0, 2).map((skill: string, sIdx: number) => (
                        <span key={sIdx} className="skill-pill">
                          {skill}
                        </span>
                      ))}
                      <span className={rk.c}>{`${rk.i} ${rk.l}`}</span>
                    </div>
                    <div className="prov-foot">
                      <div className="prov-rate">
                        {p.hourly_rate > 0 ? (
                          <>
                            {formatCurrency(p.hourly_rate)}
                            <small>/hr</small>
                          </>
                        ) : (
                          'Negotiable'
                        )}
                      </div>
                      <a
                        href={`/profile.php?id=${p.user_id}`}
                        className="btn btn-indigo"
                        style={{ padding: '6px 14px', fontSize: '11.5px' }}
                      >
                        Invite
                      </a>
                    </div>
                  </div>
                </SpotlightCard>
              );
            })}
          </div>
        </section>
      )}

      {/* ══════ FEATURED PROVIDERS ══════ */}
      <section
        className="section"
        style={{ background: 'linear-gradient(180deg,transparent,rgba(19,22,30,0.3),transparent)' }}
      >
        <div className="s-head">
          <div className="s-badge">Top Talent</div>
          <h2 className="s-title">Featured Freelancers</h2>
          <p className="s-sub">Handpicked performers ready to bring your vision to life.</p>
        </div>
        {featured.length > 0 ? (
          <div className="prov-grid">
            {featured.map((pv, idx) => {
              const skills = pv.skill_names ? pv.skill_names.split('|').filter(Boolean) : [];
              const rk = rankLabel(Number(pv.completed_jobs || 0));
              const init = initials(pv.first_name, pv.last_name);
              const jobs = Number(pv.completed_jobs || 0);
              const bt = jobs >= 20 ? 'premium' : jobs >= 5 ? 'verified' : 'free';
              const rating = Number(pv.rating_avg || 5);

              return (
                <SpotlightCard
                  key={idx}
                  spotlightColor="rgba(255, 107, 74, 0.16)"
                  className="prov-card p-0"
                >
                  <div className="prov-img-wrap">
                    {pv.avatar ? (
                      <img src={pv.avatar} alt={pv.first_name} loading="lazy" />
                    ) : (
                      <div className="prov-initials">{init}</div>
                    )}
                    {pv.is_verified ? <div className="prov-verified-badge">✓ Verified</div> : null}
                  </div>
                  <div className="prov-body">
                    <div className="prov-name">{`${pv.first_name} ${pv.last_name}`}</div>
                    {pv.location && <div className="prov-loc">📍 {pv.location}</div>}
                    <div className="prov-tag">
                      {pv.tagline || `${pv.experience_level ? pv.experience_level.charAt(0).toUpperCase() + pv.experience_level.slice(1) : ''} Freelancer`}
                    </div>
                    <div className="badge-row">
                      {bt === 'premium' ? (
                        <span className="badge-premium">⭐ Premium</span>
                      ) : bt === 'verified' ? (
                        <span className="badge-verified">✓ Verified</span>
                      ) : (
                        <span className="badge-free">🌱 Beginner</span>
                      )}
                    </div>
                    <div className="prov-stars">
                      {[1, 2, 3, 4, 5].map((s) => (
                        <span key={s}>{rating >= s ? '★' : rating >= s - 0.5 ? '✦' : '☆'}</span>
                      ))}
                    </div>
                    <div className="prov-rc">
                      {rating.toFixed(1)} ({Number(pv.rating_count || 0)} reviews) · {jobs} jobs done
                    </div>
                    <div className="prov-pills">
                      {skills.slice(0, 2).map((skill: string, sIdx: number) => (
                        <span key={sIdx} className="skill-pill">
                          {skill}
                        </span>
                      ))}
                      {pv.availability && (
                        <span className="skill-pill">{avMap[pv.availability] || 'Available'}</span>
                      )}
                      <span className={rk.c}>{`${rk.i} ${rk.l}`}</span>
                    </div>
                    <div className="prov-foot">
                      <div className="prov-rate">
                        {pv.hourly_rate > 0 ? (
                          <>
                            {formatCurrency(pv.hourly_rate)}
                            <small>/hr</small>
                          </>
                        ) : (
                          'Negotiable'
                        )}
                      </div>
                      <a
                        href={`/profile.php?id=${pv.user_id}`}
                        className="btn btn-indigo"
                        style={{ padding: '6px 14px', fontSize: '11.5px' }}
                      >
                        View Profile
                      </a>
                    </div>
                  </div>
                </SpotlightCard>
              );
            })}
          </div>
        ) : (
          <div className="empty">
            <span>👤</span>No freelancers yet.{' '}
            <a href="/auth/register.php?role=provider" style={{ color: 'var(--cyan)' }}>
              Be the first!
            </a>
          </div>
        )}
        <div style={{ textAlign: 'center', marginTop: '32px' }}>
          <a href="/search/providers.php" className="btn btn-ghost btn-lg">
            View All Freelancers →
          </a>
        </div>
      </section>

      {/* ══════ MODERN BENTO GRID: WHY GIGHANA & PROCESS ══════ */}
      <section className="section" id="how">
        <div className="s-head">
          <div className="s-badge">Platform Highlights</div>
          <h2 className="s-title">Why Ghanaian Talent &amp; Businesses Choose GigGhana</h2>
          <p className="s-sub">Built from the ground up for safety, speed, and real-world African commerce.</p>
        </div>

        <BentoGrid>
          <BentoCard
            title="1. Create &amp; Verify Your Profile"
            description="Sign up free with your Ghana Card. Choose Beginner, Verified, or Premium badge tiers to immediately establish trust with prospective clients."
            icon={<ShieldCheck className="w-6 h-6 text-[var(--cyan)]" />}
            badge="Trust &amp; Safety"
            spotlightColor="rgba(0, 212, 200, 0.18)"
            className="md:col-span-2"
            header={
              <div className="flex items-center gap-2 p-3 rounded-xl bg-[var(--surface-2)] border border-[var(--border)] text-xs text-[var(--tx-2)]">
                <CheckCircle2 className="w-4 h-4 text-[var(--green)]" />
                <span>Instant Ghana Card verification and skill endorsement verification</span>
              </div>
            }
          />

          <BentoCard
            title="2. Smart Proposals &amp; Match"
            description="Use AI-assisted proposal starters to land jobs 3x faster with transparent milestone negotiations."
            icon={<Zap className="w-6 h-6 text-[var(--coral)]" />}
            badge="AI Powered"
            spotlightColor="rgba(255, 107, 74, 0.18)"
          />

          <BentoCard
            title="3. Instant MoMo &amp; Bank Payouts"
            description="Direct integration with MTN Mobile Money, Vodafone Cash, AirtelTigo, and local banks for fast, reliable withdrawals."
            icon={<Smartphone className="w-6 h-6 text-[var(--green)]" />}
            badge="Zero Delay"
            spotlightColor="rgba(31, 217, 160, 0.18)"
          />

          <BentoCard
            title="Guaranteed Escrow Protection"
            description="Funds are securely locked in escrow before project kick-off and released only when deliverables are approved by the client."
            icon={<Award className="w-6 h-6 text-[var(--gold)]" />}
            badge="100% Secure"
            spotlightColor="rgba(245, 158, 11, 0.18)"
            className="md:col-span-2"
            header={
              <div className="flex items-center justify-between p-3 rounded-xl bg-gradient-to-r from-emerald-950/30 to-cyan-950/20 border border-emerald-500/20 text-xs">
                <span className="text-[var(--green)] font-bold">🔒 Escrow Active</span>
                <span className="text-[var(--tx-3)]">Milestone released upon sign-off</span>
              </div>
            }
          />
        </BentoGrid>

        <div className="badge-tiers mt-10">
          <div className="badge-tier-card">
            <div className="bt-icon">🌱</div>
            <div className="bt-name">Beginner</div>
            <div className="bt-price">Free · 3 jobs included</div>
            <div className="bt-perks">Get started at no cost. Basic profile listing and access to open jobs.</div>
          </div>
          <div className="badge-tier-card featured">
            <div className="bt-icon">✓</div>
            <div className="bt-name">Verified</div>
            <div className="bt-price">₵49/mo · Unlimited jobs</div>
            <div className="bt-perks">Verified badge, unlimited applications, higher search ranking &amp; client trust.</div>
          </div>
          <div className="badge-tier-card">
            <div className="bt-icon">⭐</div>
            <div className="bt-name">Premium</div>
            <div className="bt-price">₵99/mo · Top placement</div>
            <div className="bt-perks">Featured listing, top search placement, priority support &amp; exclusive jobs.</div>
          </div>
        </div>
      </section>

      {/* ══════ RECENT JOBS ══════ */}
      <section className="section">
        <div className="s-head">
          <div className="s-badge">Latest Opportunities</div>
          <h2 className="s-title">Recently Posted Jobs</h2>
          <p className="s-sub">Businesses across Ghana are actively looking for professionals like you.</p>
        </div>
        {recentJobs.length > 0 ? (
          <div className="jobs-grid">
            {recentJobs.map((job) => (
              <SpotlightCard
                key={job.id}
                spotlightColor="rgba(0, 212, 200, 0.14)"
                className="job-card p-0"
              >
                <a
                  href={`/job-details.php?id=${job.id}`}
                  style={{ textDecoration: 'none', color: 'inherit', display: 'block', width: '100%', height: '100%' }}
                >
                  <div className="job-top">
                    <div className="job-ttl">{job.title}</div>
                    <span
                      className={`jb ${
                        job.is_urgent ? 'jb-urgent' : job.is_featured ? 'jb-feat' : 'jb-open'
                      }`}
                    >
                      {job.is_urgent ? '🔥 Urgent' : job.is_featured ? '⭐ Featured' : '● Open'}
                    </span>
                  </div>
                  <div className="job-desc">
                    {job.description ? job.description.slice(0, 145) + '…' : ''}
                  </div>
                  <div className="job-meta">
                    <span>
                      {iconMap[job.cat_icon] || '📂'} {job.cat_name || 'General'}
                    </span>
                    <span>🕒 {timeAgo(job.created_at)}</span>
                    <span>📝 {Number(job.proposal_count || 0)} proposals</span>
                    <span className="job-budget">
                      {formatCurrency(job.budget_min)}
                      {job.budget_max > job.budget_min ? ` – ${formatCurrency(job.budget_max)}` : ''}
                      {job.budget_type === 'hourly' ? '/hr' : ''}
                    </span>
                  </div>
                  <div className="job-poster">
                    <div className="client-av">
                      {job.client_avatar ? (
                        <img src={job.client_avatar} alt="" loading="lazy" />
                      ) : (
                        (job.first_name || 'C').charAt(0).toUpperCase()
                      )}
                    </div>
                    <div className="client-info">
                      <div className="client-name">{`${job.first_name} ${job.last_name}`}</div>
                      <div className="client-lbl">Verified Client</div>
                    </div>
                  </div>
                </a>
              </SpotlightCard>
            ))}
          </div>
        ) : (
          <div className="empty">
            <span>💼</span>No open jobs yet.{' '}
            <a href="/auth/register.php?role=client" style={{ color: 'var(--cyan)' }}>
              Post the first job!
            </a>
          </div>
        )}
        <div style={{ textAlign: 'center', marginTop: '32px' }}>
          <a href="/jobs.php" className="btn btn-ghost btn-lg">
            Browse All Jobs →
          </a>
        </div>
      </section>

      {/* ══════ TESTIMONIALS ══════ */}
      <section className="section" style={{ paddingTop: '32px' }}>
        <div className="s-head">
          <div className="s-badge">Reviews</div>
          <h2 className="s-title">Ghanaians Winning on GigGhana</h2>
          <p className="s-sub">Real feedback from painters, nurses, carpenters, chefs and more across Ghana.</p>
        </div>
        <div className="rv-carousel-outer">
          <div
            className="rv-track"
            style={{
              transform: `translateX(-${rvPos * 100}%)`,
            }}
          >
            {reviews.map((rv, idx) => {
              const init = initials(rv.first_name, rv.last_name);
              const rating = Number(rv.rating_overall || 5);
              const profIcons: Record<string, string> = { provider: '💼', client: '🏢' };

              return (
                <div key={idx} className="rv-card">
                  <div className="rv-stars">
                    {[1, 2, 3, 4, 5].map((s) => (
                      <span key={s}>{rating >= s ? '★' : '☆'}</span>
                    ))}
                  </div>
                  <div className="rv-text">&ldquo;{rv.comment}&rdquo;</div>
                  <div className="rv-author">
                    <div className="rv-av">
                      {rv.avatar ? <img src={rv.avatar} alt="" loading="lazy" /> : init}
                    </div>
                    <div>
                      <div className="rv-name">
                        {`${profIcons[rv.role] || '👤'} ${rv.first_name} ${rv.last_name}`}
                      </div>
                      <div className="rv-role">
                        {rv.role ? rv.role.charAt(0).toUpperCase() + rv.role.slice(1) : ''}
                        {rv.location ? ` · ${rv.location}` : ''}
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
        <div className="rv-nav">
          <button
            className="rv-nav-btn"
            onClick={() => setRvPos((prev) => Math.max(0, prev - 1))}
          >
            ←
          </button>
          <button
            className="rv-nav-btn"
            onClick={() => setRvPos((prev) => Math.min(reviews.length - 1, prev + 1))}
          >
            →
          </button>
        </div>
      </section>

      {/* ══════ CTA ══════ */}
      <div className="cta-wrap">
        <div className="cta-glo" />
        <div className="cta-glo2" />
        <div className="cta-inner">
          <h2 className="cta-title">Join Thousands of Ghanaians Winning Every Day</h2>
          <p className="cta-sub">
            Join {stats.providers.toLocaleString()} verified freelancers and {stats.clients.toLocaleString()} businesses already on GigGhana. Africa&apos;s talent economy starts here.
          </p>
          <div className="cta-btns">
            <a
              href="/auth/register.php?role=provider"
              className="btn btn-gold btn-lg"
              onClick={triggerConfetti}
            >
              Sign Up as Provider
            </a>
            <a
              href="/auth/register.php?role=client"
              className="btn btn-blue btn-lg"
              onClick={triggerConfetti}
            >
              Hire Talent
            </a>
          </div>
        </div>
      </div>

      {/* ══════ EARNINGS CHART ══════ */}
      <section className="section" style={{ paddingTop: '32px' }}>
        <div className="s-head">
          <div className="s-badge">Analytics</div>
          <h2 className="s-title">Track Your Earnings</h2>
          <p className="s-sub">Real earnings data from the platform — your personal dashboard updates after login.</p>
        </div>
        <div className="chart-box">
          <div className="chart-hd">
            <div>
              <div className="chart-ttl">Monthly Earnings Overview ({new Date().getFullYear()})</div>
              <div style={{ fontSize: '11.5px', color: 'var(--tx-3)', marginTop: '3px' }}>
                {earningsTotal > 0
                  ? 'Live from escrow release transactions'
                  : 'No completed transactions yet this year'}
              </div>
            </div>
            <div>
              <div className="chart-kpi-val">₵{earningsTotal.toLocaleString('en-GH', { minimumFractionDigits: 2 })}</div>
              <div className="chart-kpi-lbl">Total paid out ({new Date().getFullYear()})</div>
            </div>
          </div>
          <Line data={chartData} options={chartOptions} height={105} />
        </div>
      </section>

      {/* ══════ INFINITE PARTNER MARQUEE ══════ */}
      <div className="pay-section">
        <div className="pay-inner">
          <div className="pay-ttl">Trusted Payment &amp; Technology Partners</div>
          
          <Marquee pauseOnHover className="[--duration:25s] mt-3">
            <div className="pay-logo" title="MTN MoMo">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/MTN_Logo.svg/512px-MTN_Logo.svg.png"
                alt="MTN MoMo"
              />
              <span className="pay-txt">MTN MoMo</span>
            </div>
            <div className="pay-logo" title="Vodafone Cash">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Vodafone_icon.svg/512px-Vodafone_icon.svg.png"
                alt="Vodafone"
              />
              <span className="pay-txt">Vodafone Cash</span>
            </div>
            <div
              className="pay-logo"
              title="AirtelTigo"
              style={{ background: 'linear-gradient(135deg,var(--coral-dim),rgba(239,68,68,0.04))' }}
            >
              <span
                style={{
                  fontFamily: 'var(--fm)',
                  fontWeight: 800,
                  fontSize: '12px',
                  color: 'var(--coral)',
                  whiteSpace: 'nowrap',
                }}
              >
                AirtelTigo
              </span>
            </div>
            <div className="pay-logo" title="Paystack">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Paystack_logo.png"
                alt="Paystack"
              />
              <span className="pay-txt">Paystack</span>
            </div>
            <div className="pay-logo" title="Visa">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/800px-Visa_Inc._logo.svg.png"
                alt="Visa"
              />
              <span className="pay-txt">Visa</span>
            </div>
            <div className="pay-logo" title="Mastercard">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/800px-Mastercard-logo.svg.png"
                alt="Mastercard"
                style={{ filter: 'none', opacity: 1 }}
              />
              <span className="pay-txt">Mastercard</span>
            </div>
            <div className="pay-logo" title="Telesoft" style={{ background: 'var(--violet-dim)' }}>
              <span
                style={{
                  fontFamily: 'var(--fm)',
                  fontWeight: 800,
                  fontSize: '12px',
                  color: 'var(--violet)',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '5px',
                }}
              >
                🧠 Telesoft
              </span>
            </div>
            <div className="pay-logo" title="Secure Escrow" style={{ background: 'var(--green-dim)' }}>
              <span
                style={{
                  fontFamily: 'var(--fm)',
                  fontWeight: 700,
                  fontSize: '11.5px',
                  color: 'var(--green)',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '5px',
                }}
              >
                🔒 Secure Escrow
              </span>
            </div>
          </Marquee>
        </div>
      </div>

      {/* ══════ FOOTER ══════ */}
      <footer className="footer-wrap">
        <div className="footer-top">
          <div>
            <a href="/" className="logo">
              <div className="logo-mark">G</div>
              <span className="logo-text">
                Gig<span>Ghana</span>
              </span>
            </a>
            <p className="footer-brand">
              Africa&apos;s premier freelance marketplace connecting every Ghanaian talent — from IT and design to trades, health and education — with forward-thinking businesses.
            </p>
            <div className="footer-nl">
              <div style={{ fontSize: '12.5px', color: 'var(--tx-2)', fontWeight: 600, fontFamily: 'var(--fm)' }}>
                Stay in the Loop
              </div>
              <form className="nl-form" onSubmit={handleSubscribeNL}>
                <input
                  className="nl-input"
                  type="email"
                  placeholder="your@email.com"
                  value={nlEmail}
                  onChange={(e) => setNlEmail(e.target.value)}
                />
                <button type="submit" className="btn btn-gold" style={{ padding: '8px 16px', fontSize: '12px' }}>
                  Subscribe
                </button>
              </form>
            </div>
            <div className="footer-badges">
              <div className="f-badge">🔒 SSL Secured</div>
              <div className="f-badge">🇬🇭 Ghana Registered</div>
              <div className="f-badge">✓ Escrow Protected</div>
              <div className="f-badge">🌍 Africa-wide</div>
            </div>
          </div>
          <div>
            <div className="footer-ttl">Platform</div>
            <ul className="footer-links">
              <li><a href="/search/providers.php">Find Talent</a></li>
              <li><a href="/jobs.php">Browse Jobs</a></li>
              <li><a href="/auth/register.php">Post a Job</a></li>
              <li><a href="#">Enterprise</a></li>
              <li><a href="#">Pricing</a></li>
              <li><a href="#">Upgrade Badge</a></li>
            </ul>
          </div>
          <div>
            <div className="footer-ttl">Company</div>
            <ul className="footer-links">
              <li><a href="#">About Us</a></li>
              <li><a href="#">Blog</a></li>
              <li><a href="#">Careers</a></li>
              <li><a href="#">Press</a></li>
              <li><a href="#">Partners</a></li>
            </ul>
          </div>
          <div>
            <div className="footer-ttl">Support</div>
            <ul className="footer-links">
              <li><a href="#">Help Centre</a></li>
              <li><a href="/privacy.php">Privacy Policy</a></li>
              <li><a href="/terms.php">Terms of Service</a></li>
              <li><a href="#">Contact Us</a></li>
              <li><a href="#">Dispute Resolution</a></li>
            </ul>
          </div>
        </div>
        <div className="footer-bar">
          <span className="footer-copy">
            © {new Date().getFullYear()} GigGhana Ltd. Made with ❤️ in Ghana 🇬🇭
          </span>
          <div className="footer-socials">
            <a className="soc-btn" href="#" title="Twitter / X">𝕏</a>
            <a className="soc-btn" href="#" title="LinkedIn">in</a>
            <a className="soc-btn" href="#" title="Instagram">ig</a>
            <a className="soc-btn" href="#" title="Facebook">fb</a>
            <a className="soc-btn" href="#" title="TikTok" style={{ fontSize: '11px' }}>TT</a>
          </div>
        </div>
      </footer>

      {/* Toast Notifications */}
      <div id="toast-c">
        {toasts.map((t) => (
          <div key={t.id} className={`toast ${t.type}`}>
            <div className="t-ico">
              {t.type === 'success' ? '✅' : t.type === 'error' ? '❌' : t.type === 'warning' ? '⚠️' : 'ℹ️'}
            </div>
            <div className="t-bod">
              <div className="t-ttl">{t.title}</div>
              <div className="t-msg">{t.msg}</div>
            </div>
            <div
              className="t-cls"
              onClick={() => setToasts((prev) => prev.filter((item) => item.id !== t.id))}
            >
              ×
            </div>
          </div>
        ))}
      </div>
    </>
  );
}
