'use client';

import React, { useState, useEffect, useRef } from 'react';
import type { LandingData } from '@/lib/types';
import { iconMap, fallbackRecentJobs, fallbackFeaturedProviders } from '@/lib/types';
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
import { CommandSearchDialog } from './ui/command-dialog';
import { LuxuryEstimator } from './ui/luxury-estimator';
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
  { icon: '🎨', text: 'Painter / Decorator', cat: 'Skilled Trades' },
  { icon: '🏗️', text: 'Building Contractor', cat: 'Construction' },
  { icon: '🛋️', text: 'Interior Designer', cat: 'Creative Arts' },
  { icon: '🪚', text: 'Carpenter / Joiner', cat: 'Skilled Trades' },
  { icon: '💻', text: 'Web Developer', cat: 'IT & Tech' },
  { icon: '🏥', text: 'Home Nurse', cat: 'Health & Wellness' },
  { icon: '🔌', text: 'Electrician', cat: 'Skilled Trades' },
  { icon: '🍽️', text: 'Private Chef', cat: 'Hospitality' },
  { icon: '🔧', text: 'Plumber', cat: 'Skilled Trades' },
  { icon: '🚗', text: 'Mechanic', cat: 'Skilled Trades' },
  { icon: '📈', text: 'Digital Marketer', cat: 'IT & Tech' },
  { icon: '📊', text: 'Accountant', cat: 'Business Services' },
  { icon: '🌾', text: 'Farmer / Agri-tech', cat: 'Agriculture' },
  { icon: '📚', text: 'Math Tutor', cat: 'Education' },
  { icon: '📷', text: 'Photographer', cat: 'Creative Arts' },
  { icon: '📦', text: 'Delivery Rider', cat: 'Others' },
];

const profs = [
  'Painters',
  'Building Contractors',
  'Interior Designers',
  'Carpenters',
  'Developers',
  'Nurses',
  'Electricians',
  'Chefs',
  'Plumbers',
  'Mechanics',
  'Graphic Designers',
  'Accountants',
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
  const [isHeroPaused, setIsHeroPaused] = useState(false);

  // Hero panel
  const [panelSlide, setPanelSlide] = useState(0);

  // Category filters for talent and jobs
  const [talentCatFilter, setTalentCatFilter] = useState('all');
  const [jobCatFilter, setJobCatFilter] = useState('all');
  const [activePreviewJobId, setActivePreviewJobId] = useState<number | null>(null);

  // Search input & autocomplete & region
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');
  const [selectedRegion, setSelectedRegion] = useState('');
  const [autocompleteOpen, setAutocompleteOpen] = useState(false);

  // Profession ticker
  const [tickerIndex, setTickerIndex] = useState(0);
  const [tickerFade, setTickerFade] = useState(false);

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
    const id = Date.now();
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

  // Initialize theme from localStorage & cookie
  useEffect(() => {
    const savedTheme = localStorage.getItem('gg_theme');
    if (savedTheme === 'light' || (!savedTheme && document.documentElement.classList.contains('lm'))) {
      setIsLight(true);
      document.documentElement.classList.add('lm');
      document.body.classList.add('lm');
      document.documentElement.setAttribute('data-theme', 'light');
      document.documentElement.classList.remove('dark');
      document.body.classList.remove('dark');
    } else if (savedTheme === 'dark') {
      setIsLight(false);
      document.documentElement.classList.add('dark');
      document.body.classList.add('dark');
      document.documentElement.setAttribute('data-theme', 'dark');
      document.documentElement.classList.remove('lm');
      document.body.classList.remove('lm');
    }
  }, []);

  const toggleTheme = () => {
    const nextTheme = !isLight;
    setIsLight(nextTheme);
    const themeStr = nextTheme ? 'light' : 'dark';
    localStorage.setItem('gg_theme', themeStr);
    document.cookie = `gg_theme=${themeStr};path=/;max-age=31536000;SameSite=Lax`;
    if (nextTheme) {
      document.documentElement.classList.add('lm');
      document.body.classList.add('lm');
      document.documentElement.setAttribute('data-theme', 'light');
      document.documentElement.classList.remove('dark');
      document.body.classList.remove('dark');
    } else {
      document.documentElement.classList.remove('lm');
      document.body.classList.remove('lm');
      document.documentElement.classList.add('dark');
      document.body.classList.add('dark');
      document.documentElement.setAttribute('data-theme', 'dark');
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

const occupationSlides = [
  { id: 'hs1', label: '🎨 Painter', title: '🎨 Master Decorative Painter', location: 'Accra', rate: '₵65/hr', exp: '8 yrs exp' },
  { id: 'hs2', label: '🏗️ Contractor', title: '🏗️ Certified Building Contractor', location: 'Airport City', rate: '₵120/hr', exp: '12 yrs exp' },
  { id: 'hs3', label: '🛋️ Designer', title: '🛋️ Luxury Interior Designer', location: 'East Legon', rate: '₵95/hr', exp: '6 yrs exp' },
  { id: 'hs4', label: '🪚 Carpenter', title: '🪚 Master Carpenter & Joiner', location: 'Kumasi', rate: '₵70/hr', exp: '10 yrs exp' },
  { id: 'hs5', label: '🔌 Electrician', title: '🔌 Certified Solar & Electrical Pro', location: 'Takoradi', rate: '₵80/hr', exp: '7 yrs exp' },
  { id: 'hs6', label: '🔧 Plumber', title: '🔧 Licensed Master Plumber', location: 'Tema & Accra', rate: '₵60/hr', exp: '9 yrs exp' },
  { id: 'hs7', label: '🏛️ Architect', title: '🏛️ Architectural Wood Sculptor', location: 'Cape Coast', rate: '₵85/hr', exp: '14 yrs exp' },
  { id: 'hs8', label: '💼 Executive', title: '💼 Creative Agency Director', location: 'Cantonments', rate: '₵150/hr', exp: '11 yrs exp' },
  { id: 'hs9', label: '💻 Developer', title: '💻 Senior Full-Stack Developer', location: 'Accra Tech Hub', rate: '₵110/hr', exp: '5 yrs exp' },
  { id: 'hs10', label: '🚀 FinTech', title: '🚀 FinTech & Cloud Architect', location: 'Airport Residential', rate: '₵180/hr', exp: '8 yrs exp' },
];

  // Hero carousel interval (10 custom Ghanaian occupation 8K slides)
  useEffect(() => {
    if (isHeroPaused) return;
    const timer = setInterval(() => {
      setHeroSlide((prev) => (prev + 1) % 10);
    }, 5000);
    return () => clearInterval(timer);
  }, [isHeroPaused]);

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

      {/* ══════ HERO SECTION (STRATEGY 1: ASYMMETRICAL 2-COLUMN SPLIT) ══════ */}
      <section className="hero">
        <div className="hero-container">
          
          {/* Left Column: Razor-Sharp Editorial & Search */}
          <div className="hero-left">
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
                ? 'Connecting every Ghanaian talent to opportunities that pay — across painting, building construction, interior design, health, IT & more.'
                : 'GigGhana de Ghanafoɔ nyinaa ho adwuma na wɔtua ka pɛ — adwuma, ahosiesie, yadeɛ, IT ne ebi.'}
            </p>

            {/* Super Search Capsule */}
            <div className="search-outer">
              <form
                className="search-wrap"
                onSubmit={(e) => {
                  e.preventDefault();
                  const target = `/search/providers.php?q=${encodeURIComponent(searchQuery)}&category=${encodeURIComponent(selectedCategory)}&region=${encodeURIComponent(selectedRegion)}`;
                  window.location.href = target;
                }}
              >
                <div className="search-input-row">
                  <Search className="w-4 h-4 text-[var(--cyan)]" style={{ flexShrink: 0 }} />
                  <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => {
                      setSearchQuery(e.target.value);
                      setAutocompleteOpen(true);
                    }}
                    onFocus={() => setAutocompleteOpen(true)}
                    placeholder="e.g. Painter, Contractor, Interior Designer, React Dev…"
                    autoComplete="off"
                  />
                </div>
                <div className="search-div" />
                <div className="search-selects-group">
                  <select
                    value={selectedRegion}
                    onChange={(e) => setSelectedRegion(e.target.value)}
                  >
                    <option value="">🇬🇭 All Ghana</option>
                    <option value="accra">Accra &amp; Tema</option>
                    <option value="kumasi">Kumasi &amp; Ashanti</option>
                    <option value="takoradi">Takoradi &amp; Western</option>
                    <option value="tamale">Tamale &amp; Northern</option>
                    <option value="remote">Remote / Online</option>
                  </select>
                  <div className="search-div-inner" />
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
                </div>
                <button type="submit" className="btn btn-gold hero-search-btn">
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

            {/* Action Buttons */}
            <div className="hero-acts">
              <a href="/auth/register.php?role=client" className="btn btn-gold btn-lg">
                I Need Talent
              </a>
              <a href="/auth/register.php?role=provider" className="btn btn-blue btn-lg">
                I Have Skills
              </a>
            </div>

            {/* Social Proof Talent Cluster */}
            <div className="hero-social-proof">
              <div className="avatar-cluster">
                <div className="cluster-avatar av-1">👨🏾‍🎨</div>
                <div className="cluster-avatar av-2">👩🏾‍💼</div>
                <div className="cluster-avatar av-3">👨🏾‍🔧</div>
                <div className="cluster-avatar av-4">👩🏾‍⚕️</div>
                <div className="cluster-avatar av-count">+14k</div>
              </div>
              <div className="social-proof-text">
                <div className="sp-stars">★★★★★</div>
                <div className="sp-desc">
                  <strong>14,250+ Verified Ghanaian Pros</strong> across all 16 regions
                </div>
              </div>
            </div>

            {/* Trust Indicators */}
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
                <div className="dot dot-i" />
                3 Jobs Free
              </div>
            </div>
          </div>

          {/* Right Column: 8K Ghanaian Occupation Visual Showcase */}
          <div className="hero-right-showcase">
            <div className="showcase-outer-wrap">
              <div
                className="showcase-card"
                onMouseEnter={() => setIsHeroPaused(true)}
                onMouseLeave={() => setIsHeroPaused(false)}
              >
                {/* The 10 8K Occupation Slides */}
                <div className="showcase-slides">
                  {occupationSlides.map((s, idx) => (
                    <div
                      key={s.id}
                      className={`showcase-slide ${s.id} ${heroSlide === idx ? 'active' : ''}`}
                    />
                  ))}
                </div>

                {/* Top Floating Badge with Dynamic Location */}
                <div className="showcase-top-badge">
                  <span className="live-pulse-dot" />
                  <span>
                    {occupationSlides[heroSlide]?.title} · {occupationSlides[heroSlide]?.location}
                  </span>
                </div>

                {/* Floating Rotator Pagination */}
                <div className="showcase-dots">
                  {occupationSlides.map((_, idx) => (
                    <div
                      key={idx}
                      className={`sc-dot ${heroSlide === idx ? 'active' : ''}`}
                      onClick={() => setHeroSlide(idx)}
                    />
                  ))}
                </div>
              </div>
            </div>
          </div>

        </div>
      </section>

      {/* ══════ FEATURED & VERIFIED FREELANCERS (EXACT SAME UI STYLE AS JOBS) ══════ */}
      {(() => {
        const allTalentList = featured && featured.length > 0 ? featured : fallbackFeaturedProviders;
        const filteredTalent = talentCatFilter === 'all'
          ? allTalentList
          : allTalentList.filter((p: any) => {
              if (talentCatFilter === 'tech') return p.cat_name?.toLowerCase().includes('tech') || p.cat_name?.toLowerCase().includes('it');
              if (talentCatFilter === 'trades') return p.cat_name?.toLowerCase().includes('trades') || p.cat_name?.toLowerCase().includes('skilled');
              if (talentCatFilter === 'design') return p.cat_name?.toLowerCase().includes('creative') || p.cat_name?.toLowerCase().includes('arts') || p.cat_name?.toLowerCase().includes('design');
              if (talentCatFilter === 'build') return p.cat_name?.toLowerCase().includes('construct') || p.cat_name?.toLowerCase().includes('build');
              if (talentCatFilter === 'health') return p.cat_name?.toLowerCase().includes('health') || p.cat_name?.toLowerCase().includes('wellness');
              return true;
            });
        const latestTalent = filteredTalent.slice(0, 7);

        return (
          <section className="section live-job-feed-section" id="talent" style={{ paddingTop: '56px', paddingBottom: '32px' }}>
            <div className="ljf-container">
              {/* Header */}
              <div className="ljf-header">
                <div className="ljf-header-left">
                  <div className="hero-badge" style={{ marginBottom: '12px' }}>
                    <span className="live-pulse-dot" />
                    <span>⭐ Top Verified Talent · Ghana&apos;s Master Artisans &amp; Specialists</span>
                  </div>
                  <h2 className="ljf-title">Featured Freelancers &amp; Master Artisans</h2>
                  <p className="ljf-sub">
                    Directly hire handpicked Ghanaian specialists. Every contract is backed by Ghana Card NIA Biometrics, verified client ratings, and 100% Escrow Vault protection.
                  </p>
                </div>

                {/* Header Action */}
                <div className="ljf-header-right">
                  <a href="/auth/register.php?role=provider" className="btn btn-gold btn-lg">
                    + Join as a Pro (Free)
                  </a>
                </div>
              </div>

              {/* Category Filter Tabs */}
              <div className="rjh-filter-tabs">
                {[
                  { id: 'all', label: '⚡ All Specialists', count: allTalentList.length },
                  { id: 'tech', label: '💻 IT & Tech', count: allTalentList.filter((p: any) => p.cat_name?.toLowerCase().includes('tech') || p.cat_name?.toLowerCase().includes('it')).length || 1 },
                  { id: 'trades', label: '🔧 Skilled Trades', count: allTalentList.filter((p: any) => p.cat_name?.toLowerCase().includes('trades') || p.cat_name?.toLowerCase().includes('skilled')).length || 3 },
                  { id: 'design', label: '🎨 Creative & Arts', count: allTalentList.filter((p: any) => p.cat_name?.toLowerCase().includes('creative') || p.cat_name?.toLowerCase().includes('arts')).length || 2 },
                  { id: 'build', label: '🏗️ Construction', count: allTalentList.filter((p: any) => p.cat_name?.toLowerCase().includes('construct')).length || 1 },
                  { id: 'health', label: '🏥 Health & Wellness', count: allTalentList.filter((p: any) => p.cat_name?.toLowerCase().includes('health')).length || 1 },
                ].map((tab) => (
                  <button
                    key={tab.id}
                    type="button"
                    className={`rjh-filter-btn ${talentCatFilter === tab.id ? 'active' : ''}`}
                    onClick={() => setTalentCatFilter(tab.id)}
                  >
                    <span>{tab.label}</span>
                    <span className="rjh-filter-badge">{tab.count}</span>
                  </button>
                ))}
              </div>

              {/* 7-Card Grid (Matching Jobs UI Style) */}
              <div className="ljf-grid">
                {latestTalent.map((p: any, idx: number) => {
                  const isFeaturedHeroCard = idx === 0;
                  const skills = p.skill_names ? (Array.isArray(p.skill_names) ? p.skill_names : p.skill_names.split('|').filter(Boolean)) : [];
                  const rating = Number(p.rating_avg || 5.0);
                  const jobsCount = Number(p.completed_jobs || 15);
                  const init = initials(p.first_name, p.last_name);

                  return (
                    <SpotlightCard
                      key={p.id || idx}
                      spotlightColor="rgba(0, 212, 200, 0.16)"
                      className={`ljf-card p-0 ${isFeaturedHeroCard ? 'ljf-card-lead' : ''}`}
                    >
                      <div className="ljf-card-inner">
                        {/* Card Top: Category & Availability */}
                        <div className="rjh-jc-top">
                          <div className="rjh-jc-cat">
                            <span>{iconMap[p.cat_icon] || '💼'}</span>
                            <span>{p.cat_name || 'Verified Pro'}</span>
                          </div>
                          <div className="rjh-jc-time">
                            <span className="live-pulse-dot" />
                            <span>● Available Now</span>
                          </div>
                        </div>

                        {/* Talent Profile Row */}
                        <div className="rjh-jc-client">
                          <div className="rjh-jc-avatar" style={{ overflow: 'hidden' }}>
                            {p.avatar ? (
                              <img src={p.avatar} alt={p.first_name} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                            ) : (
                              init
                            )}
                          </div>
                          <div>
                            <div className="rjh-jc-client-name">
                              {p.first_name} {p.last_name}
                              <span className="rjh-jc-kyc-tag">🇬🇭 NIA Verified</span>
                            </div>
                            <div className="rjh-jc-client-loc">📍 {p.location || 'Accra, Ghana'}</div>
                          </div>
                          {p.badge && (
                            <span className="rjh-jc-urgent" style={{ background: 'rgba(245, 158, 11, 0.14)', color: '#F59E0B', borderColor: 'rgba(245, 158, 11, 0.35)' }}>
                              {p.badge}
                            </span>
                          )}
                          {isFeaturedHeroCard && <span className="ljf-spotlight-pill">⭐ Master Spotlight</span>}
                        </div>

                        {/* Specialty / Headline Title */}
                        <h3 className="rjh-jc-title">
                          <a href={`/profile.php?id=${p.user_id || p.id}`}>
                            {p.tagline || `${p.first_name} ${p.last_name} - Verified Ghanaian Specialist`}
                          </a>
                        </h3>

                        {/* Bio / Scope Excerpt */}
                        <p className="rjh-jc-desc">
                          {p.bio ? p.bio.slice(0, isFeaturedHeroCard ? 200 : 115) + (p.bio.length > (isFeaturedHeroCard ? 200 : 115) ? '...' : '') : 'Master artisan & verified Ghanaian practitioner with 100% escrow protection.'}
                        </p>

                        {/* Chips / Trust metrics */}
                        <div className="rjh-jc-chips">
                          <span className="rjh-chip">⭐ {rating.toFixed(1)} ({Number(p.rating_count || 32)} reviews)</span>
                          <span className="rjh-chip">🏆 {jobsCount} jobs completed</span>
                          {skills.slice(0, 2).map((s: string, sIdx: number) => (
                            <span key={sIdx} className="rjh-chip">{s}</span>
                          ))}
                        </div>

                        {/* Card Footer: Hourly Rate Benchmark & Action */}
                        <div className="rjh-jc-footer">
                          <div className="rjh-jc-budget">
                            <div className="rjh-jc-budget-val">
                              {formatCurrency(p.hourly_rate || 85)}
                              <small style={{ fontSize: '12px', fontWeight: 600, color: 'var(--tx-3)', marginLeft: '3px' }}>/hr</small>
                            </div>
                            <div className="rjh-jc-budget-lbl">Direct Hire Benchmark</div>
                          </div>
                          <a href={`/profile.php?id=${p.user_id || p.id}`} className="btn btn-blue rjh-jc-btn">
                            View Profile →
                          </a>
                        </div>
                      </div>
                    </SpotlightCard>
                  );
                })}
              </div>

              {/* View More Talent CTA */}
              <div className="ljf-footer-actions">
                <a href="/search/providers.php" className="btn btn-ghost btn-xl ljf-view-more-btn">
                  <span>Browse All 14,250+ Verified Talent in Ghana</span>
                  <span className="ljf-btn-arrow">→</span>
                </a>
                <a href="/auth/register.php?role=provider" className="btn btn-gold btn-xl">
                  + Join as a Pro (Free)
                </a>
              </div>
            </div>
          </section>
        );
      })()}

      {/* ══════ LIVE JOB FEED (LATEST 7 JOBS + VIEW MORE JOBS) ══════ */}
      {(() => {
        const allJobsList = recentJobs && recentJobs.length > 0 ? recentJobs : fallbackRecentJobs;
        const filteredJobs = jobCatFilter === 'all'
          ? allJobsList
          : allJobsList.filter((j: any) => {
              if (jobCatFilter === 'tech') return j.cat_name?.toLowerCase().includes('tech') || j.cat_name?.toLowerCase().includes('it');
              if (jobCatFilter === 'trades') return j.cat_name?.toLowerCase().includes('trades') || j.cat_name?.toLowerCase().includes('skilled');
              if (jobCatFilter === 'design') return j.cat_name?.toLowerCase().includes('creative') || j.cat_name?.toLowerCase().includes('arts') || j.cat_name?.toLowerCase().includes('design');
              if (jobCatFilter === 'build') return j.cat_name?.toLowerCase().includes('construct') || j.cat_name?.toLowerCase().includes('build');
              return true;
            });
        const latest7Jobs = filteredJobs.slice(0, 7);

        return (
          <section className="section live-job-feed-section" id="live-jobs" ref={statsRef}>
            <div className="ljf-container">
              {/* Feed Header */}
              <div className="ljf-header">
                <div className="ljf-header-left">
                  <div className="hero-badge" style={{ marginBottom: '12px' }}>
                    <span className="live-pulse-dot" />
                    <span>🔥 Live Job Stream · Real-Time Opportunities in Ghana</span>
                  </div>
                  <h2 className="ljf-title">Live Job Feed</h2>
                  <p className="ljf-sub">
                    Direct assignments posted by verified businesses across Ghana. Every milestone is protected with Bank of Ghana Escrow Vault &amp; Sub-60s MoMo payouts.
                  </p>
                </div>

                {/* Quick Action */}
                <div className="ljf-header-right">
                  <a href="/post-job.php" className="btn btn-gold btn-lg">
                    + Post a Job (Free)
                  </a>
                </div>
              </div>

              {/* Category Filter Pills */}
              <div className="rjh-filter-tabs">
                {[
                  { id: 'all', label: '⚡ All Live Gigs', count: allJobsList.length },
                  { id: 'tech', label: '💻 IT & Tech', count: allJobsList.filter((j: any) => j.cat_name?.toLowerCase().includes('tech') || j.cat_name?.toLowerCase().includes('it')).length || 2 },
                  { id: 'trades', label: '🔧 Skilled Trades', count: allJobsList.filter((j: any) => j.cat_name?.toLowerCase().includes('trades') || j.cat_name?.toLowerCase().includes('skilled')).length || 3 },
                  { id: 'design', label: '🎨 Creative & Arts', count: allJobsList.filter((j: any) => j.cat_name?.toLowerCase().includes('creative') || j.cat_name?.toLowerCase().includes('arts')).length || 2 },
                  { id: 'build', label: '🏗️ Construction', count: allJobsList.filter((j: any) => j.cat_name?.toLowerCase().includes('construct')).length || 1 },
                ].map((tab) => (
                  <button
                    key={tab.id}
                    type="button"
                    className={`rjh-filter-btn ${jobCatFilter === tab.id ? 'active' : ''}`}
                    onClick={() => setJobCatFilter(tab.id)}
                  >
                    <span>{tab.label}</span>
                    <span className="rjh-filter-badge">{tab.count}</span>
                  </button>
                ))}
              </div>

              {/* Latest 7 Jobs Showcase Grid */}
              <div className="ljf-grid">
                {latest7Jobs.map((j: any, idx: number) => {
                  const isFeaturedHeroCard = idx === 0;
                  return (
                    <SpotlightCard
                      key={j.id || idx}
                      spotlightColor="rgba(0, 212, 200, 0.16)"
                      className={`ljf-card p-0 ${isFeaturedHeroCard ? 'ljf-card-lead' : ''}`}
                    >
                      <div className="ljf-card-inner">
                        {/* Card Top: Category & Real-time posted tag */}
                        <div className="rjh-jc-top">
                          <div className="rjh-jc-cat">
                            <span>{iconMap[j.cat_icon] || '💼'}</span>
                            <span>{j.cat_name || 'General Gig'}</span>
                          </div>
                          <div className="rjh-jc-time">
                            <span className="live-pulse-dot" />
                            <span>{timeAgo(j.created_at)}</span>
                          </div>
                        </div>

                        {/* Client details */}
                        <div className="rjh-jc-client">
                          <div className="rjh-jc-avatar">
                            {initials(j.first_name, j.last_name)}
                          </div>
                          <div>
                            <div className="rjh-jc-client-name">
                              {j.first_name} {j.last_name ? j.last_name[0] + '.' : ''}
                              <span className="rjh-jc-kyc-tag">🇬🇭 NIA Verified</span>
                            </div>
                            <div className="rjh-jc-client-loc">📍 {j.location || 'Accra, Ghana'}</div>
                          </div>
                          {j.is_urgent === 1 && <span className="rjh-jc-urgent">⚡ Urgent</span>}
                          {isFeaturedHeroCard && <span className="ljf-spotlight-pill">⭐ Featured Lead</span>}
                        </div>

                        {/* Card Title */}
                        <h3 className="rjh-jc-title">
                          <a href={`/jobs.php?id=${j.id}`}>{j.title}</a>
                        </h3>

                        {/* Description Excerpt */}
                        <p className="rjh-jc-desc">
                          {j.description ? j.description.slice(0, isFeaturedHeroCard ? 200 : 115) + (j.description.length > (isFeaturedHeroCard ? 200 : 115) ? '...' : '') : 'Looking for a verified skilled professional in Ghana.'}
                        </p>

                        {/* Chips */}
                        <div className="rjh-jc-chips">
                          <span className="rjh-chip">🔒 100% Escrow Funded</span>
                          <span className="rjh-chip">⚡ Sub-60s MoMo</span>
                          <span className="rjh-chip">💬 {j.proposal_count || 0} proposals</span>
                        </div>

                        {/* Card Footer: Budget & Apply CTA */}
                        <div className="rjh-jc-footer">
                          <div className="rjh-jc-budget">
                            <div className="rjh-jc-budget-val">
                              {formatCurrency(j.budget_min || 1000)}
                              {j.budget_max && j.budget_max > j.budget_min ? ` - ${formatCurrency(j.budget_max)}` : ''}
                            </div>
                            <div className="rjh-jc-budget-lbl">
                              {j.budget_type === 'hourly' ? 'Hourly Rate' : 'Fixed Escrow'}
                            </div>
                          </div>
                          <a href={`/jobs.php?id=${j.id}`} className="btn btn-blue rjh-jc-btn">
                            Apply Now →
                          </a>
                        </div>
                      </div>
                    </SpotlightCard>
                  );
                })}
              </div>

              {/* View More Jobs CTA under the 7 jobs */}
              <div className="ljf-footer-actions">
                <a href="/jobs.php" className="btn btn-ghost btn-xl ljf-view-more-btn">
                  <span>View More Jobs (840+ Open in Ghana)</span>
                  <span className="ljf-btn-arrow">→</span>
                </a>
                <a href="/post-job.php" className="btn btn-gold btn-xl">
                  + Post a Job (Free)
                </a>
              </div>

              {/* Bottom Trust Ribbon */}
              <div className="rjh-trust-ribbon">
                <div className="rjh-tr-item">
                  <span className="rjh-tr-icon">🛡️</span>
                  <div>
                    <div className="rjh-tr-title">Bank of Ghana Escrow Vault</div>
                    <div className="rjh-tr-desc">Client funds locked safely before work starts</div>
                  </div>
                </div>
                <div className="rjh-tr-sep" />
                <div className="rjh-tr-item">
                  <span className="rjh-tr-icon">🇬🇭</span>
                  <div>
                    <div className="rjh-tr-title">100% NIA Biometric KYC</div>
                    <div className="rjh-tr-desc">Verified Ghana Card employers &amp; providers</div>
                  </div>
                </div>
                <div className="rjh-tr-sep" />
                <div className="rjh-tr-item">
                  <span className="rjh-tr-icon">⚡</span>
                  <div>
                    <div className="rjh-tr-title">Sub-60s MoMo Settlement</div>
                    <div className="rjh-tr-desc">Instant payout to MTN MoMo, Telecel &amp; AT</div>
                  </div>
                </div>
              </div>
            </div>
          </section>
        );
      })()}

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

      {/* ══════ PROFESSIONAL INCOME ESTIMATOR ══════ */}
      <section className="section" id="calculator" style={{ paddingTop: '20px', paddingBottom: '32px' }}>
        <div style={{ maxWidth: '1160px', margin: '0 auto' }}>
          <LuxuryEstimator />
        </div>
      </section>

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
