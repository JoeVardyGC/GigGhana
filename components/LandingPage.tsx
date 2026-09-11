'use client';

import React, { useState, useEffect, useRef } from 'react';
import type { LandingData } from '@/lib/types';
import { iconMap, fallbackRecentJobs, fallbackFeaturedProviders, testimonialFallbacks } from '@/lib/types';
import confetti from 'canvas-confetti';
import { Marquee } from './ui/marquee';
import { SpotlightCard } from './ui/spotlight-card';
import { BentoGrid, BentoCard } from './ui/bento-grid';
import { GhanaCard } from './ui/ghana-card';
import { CommandSearchDialog } from './ui/command-dialog';
import { WhatsAppIcon, FacebookIcon, LinkedInIcon, InstagramIcon, TwitterXIcon } from './ui/social-icons';
import { Search, ShieldCheck, Zap, Smartphone, Award, Sparkles, Sprout, CheckCircle2, ArrowRight, BadgeCheck, Star, Briefcase, Clock, Wrench, Palette, Code, Building2, MessageSquare, Check, Phone, Mail, Layers } from 'lucide-react';

const getCategoryTheme = (cat: any) => {
  const name = (cat.name || cat.slug || cat.icon || '').toLowerCase();
  const emo = (e: string) => <span style={{ fontSize: '22px', lineHeight: 1 }}>{e}</span>;
  
  if (name.includes('tech') || name.includes('it') || name.includes('code')) {
    return {
      icon: emo('💻'),
      themeColor: '#00D4C8',
      themeDim: 'rgba(0, 212, 200, 0.12)',
      themeBorder: 'rgba(0, 212, 200, 0.35)',
      tags: ['Web & Apps', 'Solar & IT', 'UI/UX Design'],
      count: '180+ Pros',
    };
  }
  if (name.includes('trade') || name.includes('tool') || name.includes('carpenter') || name.includes('plumb') || name.includes('electric')) {
    return {
      icon: emo('🔧'),
      themeColor: '#F59E0B',
      themeDim: 'rgba(245, 158, 11, 0.12)',
      themeBorder: 'rgba(245, 158, 11, 0.35)',
      tags: ['POP Ceilings', 'Solar Wiring', 'Plumbing & Pipe'],
      count: '420+ Artisans',
    };
  }
  if (name.includes('construct') || name.includes('build')) {
    return {
      icon: emo('🏗️'),
      themeColor: '#3B82F6',
      themeDim: 'rgba(59, 130, 246, 0.12)',
      themeBorder: 'rgba(59, 130, 246, 0.35)',
      tags: ['Masonry Blocks', 'Building Plans', 'Tile Laying'],
      count: '260+ Pros',
    };
  }
  if (name.includes('creative') || name.includes('art') || name.includes('design') || name.includes('pen')) {
    return {
      icon: emo('🎨'),
      themeColor: '#A78BFA',
      themeDim: 'rgba(167, 139, 250, 0.12)',
      themeBorder: 'rgba(167, 139, 250, 0.35)',
      tags: ['Haute Couture', 'Photo & Film', 'Branding & Logo'],
      count: '310+ Creatives',
    };
  }
  if (name.includes('health') || name.includes('wellness') || name.includes('nurse')) {
    return {
      icon: emo('🏥'),
      themeColor: '#10B981',
      themeDim: 'rgba(16, 185, 129, 0.12)',
      themeBorder: 'rgba(16, 185, 129, 0.35)',
      tags: ['Home Nursing', 'Physiotherapy', 'Wellness Care'],
      count: '150+ Specialists',
    };
  }
  if (name.includes('biz') || name.includes('business') || name.includes('consult')) {
    return {
      icon: emo('💼'),
      themeColor: '#EC4899',
      themeDim: 'rgba(236, 72, 153, 0.12)',
      themeBorder: 'rgba(236, 72, 153, 0.35)',
      tags: ['GRA Tax Filing', 'Legal Advisory', 'Admin Support'],
      count: '190+ Experts',
    };
  }
  if (name.includes('hosp') || name.includes('food') || name.includes('chef')) {
    return {
      icon: emo('🍽️'),
      themeColor: '#FB923C',
      themeDim: 'rgba(251, 146, 60, 0.12)',
      themeBorder: 'rgba(251, 146, 60, 0.35)',
      tags: ['Private Chefs', 'Event Decor', 'Catering'],
      count: '210+ Pros',
    };
  }
  if (name.includes('edu') || name.includes('teach')) {
    return {
      icon: emo('📚'),
      themeColor: '#38BDF8',
      themeDim: 'rgba(56, 189, 248, 0.12)',
      themeBorder: 'rgba(56, 189, 248, 0.35)',
      tags: ['STEM Tutors', 'Languages', 'Music Lessons'],
      count: '140+ Tutors',
    };
  }
  if (name.includes('farm') || name.includes('agri')) {
    return {
      icon: emo('🌾'),
      themeColor: '#84CC16',
      themeDim: 'rgba(132, 204, 22, 0.12)',
      themeBorder: 'rgba(132, 204, 22, 0.35)',
      tags: ['Agri-Tech', 'Poultry Farming', 'Crop Science'],
      count: '95+ Specialists',
    };
  }
  return {
    icon: emo('🛠️'),
    themeColor: '#00D4C8',
    themeDim: 'rgba(0, 212, 200, 0.12)',
    themeBorder: 'rgba(0, 212, 200, 0.35)',
    tags: ['Delivery', 'Security', 'Handyman'],
    count: '100+ Pros',
  };
};

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

const paymentPartners = [
  {
    name: 'MTN Mobile Money',
    badge: 'Sub-60s MoMo STK Payout',
    icon: '/images/payments/mtn_momo.svg',
  },
  {
    name: 'Telecel Cash',
    badge: 'Instant Wallet Settlement',
    icon: '/images/payments/telecel_cash.svg',
  },
  {
    name: 'AT Money',
    badge: 'Direct Cedi Transfer',
    icon: '/images/payments/at_money.svg',
  },
  {
    name: 'Paystack Gateway',
    badge: 'PCI-DSS Level 1 Secure',
    icon: '/images/payments/paystack.svg',
  },
  {
    name: 'Visa Debit/Credit',
    badge: 'Verified by Visa 3DS',
    icon: '/images/payments/visa.svg',
  },
  {
    name: 'Mastercard SecureCode',
    badge: 'Global Bank Settlement',
    icon: '/images/payments/mastercard.svg',
  },
  {
    name: '100% Escrow Vault Guarantee',
    badge: 'Funds Protected Until Approval',
    icon: null,
  },
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
  return { i: '🌱', l: 'New Provider', c: 'rk-dim' };
}

function renderCatIcon(catName?: string, iconKey?: string) {
  const c = (catName || iconKey || '').toLowerCase();
  if (c.includes('trade') || c.includes('tool') || c.includes('plumb') || c.includes('elect') || c.includes('carp')) {
    return <Wrench className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />;
  }
  if (c.includes('creative') || c.includes('design') || c.includes('art') || c.includes('pen') || c.includes('paint')) {
    return <Palette className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />;
  }
  if (c.includes('code') || c.includes('tech') || c.includes('it') || c.includes('soft') || c.includes('web')) {
    return <Code className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />;
  }
  if (c.includes('construct') || c.includes('build') || c.includes('architect')) {
    return <Building2 className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />;
  }
  return <Sparkles className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />;
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

  // Category filters for talent and jobs
  const [talentCatFilter, setTalentCatFilter] = useState('all');
  const [jobCatFilter, setJobCatFilter] = useState('all');

  // Search input & autocomplete & region
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');
  const [selectedRegion, setSelectedRegion] = useState('');
  const [autocompleteOpen, setAutocompleteOpen] = useState(false);

  // Profession ticker
  const [tickerIndex, setTickerIndex] = useState(0);
  const [tickerFade, setTickerFade] = useState(false);

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
  { id: 'hs1', label: 'Painter', title: 'Master Decorative Painter', location: 'Accra', rate: '₵65/hr', exp: '8 yrs exp' },
  { id: 'hs2', label: 'Contractor', title: 'Certified Building Contractor', location: 'Airport City', rate: '₵120/hr', exp: '12 yrs exp' },
  { id: 'hs3', label: 'Designer', title: 'Luxury Interior Designer', location: 'East Legon', rate: '₵95/hr', exp: '6 yrs exp' },
  { id: 'hs4', label: 'Carpenter', title: 'Master Carpenter & Joiner', location: 'Kumasi', rate: '₵70/hr', exp: '10 yrs exp' },
  { id: 'hs5', label: 'Electrician', title: 'Certified Solar & Electrical Pro', location: 'Takoradi', rate: '₵80/hr', exp: '7 yrs exp' },
  { id: 'hs6', label: 'Plumber', title: 'Licensed Master Plumber', location: 'Tema & Accra', rate: '₵60/hr', exp: '9 yrs exp' },
  { id: 'hs7', label: 'Architect', title: 'Architectural Wood Sculptor', location: 'Cape Coast', rate: '₵85/hr', exp: '14 yrs exp' },
  { id: 'hs8', label: 'Executive', title: 'Creative Agency Director', location: 'Cantonments', rate: '₵150/hr', exp: '11 yrs exp' },
  { id: 'hs9', label: 'Developer', title: 'Senior Full-Stack Developer', location: 'Accra Tech Hub', rate: '₵110/hr', exp: '5 yrs exp' },
  { id: 'hs10', label: 'FinTech', title: 'FinTech & Cloud Architect', location: 'Airport Residential', rate: '₵180/hr', exp: '8 yrs exp' },
];

  // Hero carousel interval (10 custom Ghanaian occupation 8K slides)
  useEffect(() => {
    if (isHeroPaused) return;
    const timer = setInterval(() => {
      setHeroSlide((prev) => (prev + 1) % 10);
    }, 5000);
    return () => clearInterval(timer);
  }, [isHeroPaused]);

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
              Your Skill.
              <br />
              Your Success.
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
                    placeholder="e.g. Painter, Developer, Plumber…"
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
                Hire a Provider
              </a>
              <a href="/auth/register.php?role=provider" className="btn btn-blue btn-lg">
                Find Jobs &amp; Work
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

      {/* ══════ FEATURED SERVICE PROVIDERS & MASTER ARTISANS (ELEVATED ARTISAN SHOWCASE) ══════ */}
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
        const latestTalent = filteredTalent.slice(0, 6);

        return (
          <section className="section artisan-section" id="talent">
            <div className="artisan-container">
              {/* Header */}
              <div className="s-head">
                <div className="s-badge">
                  <span className="live-pulse-dot" />
                  <span>Top Verified Talent</span>
                </div>
                <h2 className="s-title">Featured Service Providers &amp; Master Artisans</h2>
                <p className="s-sub">
                  Directly hire handpicked Ghanaian specialists. Every contract is backed by Ghana Card NIA Biometrics, verified client ratings, and 100% Escrow Vault protection.
                </p>
              </div>

              {/* 7-Card Studio Showcase Grid */}
              <div className="artisan-grid">
                {latestTalent.map((p: any, idx: number) => {
                  const isLeadSpotlight = idx === 0;
                  const skills = p.skill_names ? (Array.isArray(p.skill_names) ? p.skill_names : p.skill_names.split('|').filter(Boolean)) : [];
                  const rating = Number(p.rating_avg || 5.0);
                  const jobsCount = Number(p.completed_jobs || (idx === 0 ? 42 : 25));
                  const isVerified = Boolean(p.is_verified === 1 || p.is_verified === true || p.is_kyc_verified || p.ghana_card_verified);
                  const tier = p.membership_tier || p.tier || (
                    (p.badge?.toLowerCase().includes('premium') || p.badge?.toLowerCase().includes('elite') || jobsCount >= 30) && isVerified
                      ? 'premium'
                      : (p.badge?.toLowerCase().includes('verified') || jobsCount >= 5) && isVerified
                        ? 'verified'
                        : 'beginner'
                  );
                  const init = initials(p.first_name, p.last_name);
                  const coverImg = p.avatar || '/images/occupations/interior_designer.jpg';

                  return (
                    <div
                      key={p.id || idx}
                      className="artisan-studio-card p-0"
                    >
                      {/* Visual Studio Cover Banner (Clean Editorial Photography) */}
                      <div className="artisan-cover-wrap">
                        <img
                          src={coverImg}
                          alt={p.tagline || p.first_name}
                          className="artisan-cover-img"
                          loading="lazy"
                        />
                        <div className="artisan-cover-live-dot" title="Available for hire">
                          <span className="live-pulse-dot" />
                        </div>
                      </div>

                      {/* Studio Body Content (Direct clean layout under expanded visual box) */}
                      <div className="artisan-studio-body">
                        {/* Name + Rate Row */}
                        <div className="artisan-name-row">
                          <div className="artisan-name-title">
                            <span>{p.first_name} {p.last_name}</span>
                            {isVerified && (
                              <BadgeCheck className="w-[22px] h-[22px] text-[#00D4C8] fill-[#00D4C8]/15 shrink-0" strokeWidth={2.2} aria-label="Verified Ghana Card Provider" />
                            )}
                          </div>
                          <div className="artisan-rate-capsule">
                            <span className="artisan-rate-val">{formatCurrency(p.hourly_rate || 85)}</span>
                            <span className="artisan-rate-unit">/hr</span>
                          </div>
                        </div>

                        {/* Location Subline */}
                        <div className="artisan-location-txt">
                          <span>📍</span>
                          <span>{p.location || 'Accra, Ghana'}</span>
                        </div>

                        <h3 className="artisan-studio-headline">
                          <a href={`/profile.php?id=${p.user_id || p.id}`}>
                            {p.tagline || `${p.first_name} ${p.last_name} - Certified Ghanaian Specialist`}
                          </a>
                        </h3>

                        <p className="artisan-studio-bio">
                          {p.bio || 'Specialist providing reliable, escrow-protected services across Ghana.'}
                        </p>

                        {/* Performance Stats Strip (Modern 2-Pillar Metric Capsule) */}
                        <div className="artisan-stats-strip">
                          <div className="artisan-strip-pill">
                            <div className="artisan-strip-val">
                              <Star className="w-3.5 h-3.5 fill-[#F59E0B] text-[#F59E0B] shrink-0" />
                              <span>{rating.toFixed(1)}</span>
                            </div>
                            <span className="artisan-strip-lbl">{Number(p.rating_count || 32)} reviews</span>
                          </div>
                          <div className="artisan-strip-divider" />
                          <div className="artisan-strip-pill">
                            <div className="artisan-strip-val">
                              <Briefcase className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />
                              <span>{jobsCount}</span>
                            </div>
                            <span className="artisan-strip-lbl">Jobs Done</span>
                          </div>
                        </div>

                        {/* Capability Skills Tags (Modern Micro-Pills) */}
                        <div className="artisan-skills-cloud">
                          {skills.slice(0, 3).map((skill: string, sIdx: number) => (
                            <span key={sIdx} className="artisan-skill-tag">
                              <span className="skill-tag-dot" />
                              <span>{skill}</span>
                            </span>
                          ))}
                        </div>

                        {/* Card Action Footer */}
                        <div className="artisan-studio-footer">
                          {tier === 'premium' && isVerified ? (
                            <div className="artisan-footer-tier artisan-tier-premium">
                              ⭐ Premium
                            </div>
                          ) : tier === 'verified' && isVerified ? (
                            <div className="artisan-footer-tier artisan-tier-verified">
                              ✓ Verified
                            </div>
                          ) : <div />}
                          <a
                            href={`/profile.php?id=${p.user_id || p.id}`}
                            className={`btn ${isLeadSpotlight ? 'btn-gold' : 'btn-blue'} artisan-footer-btn`}
                          >
                            View Profile &amp; Hire →
                          </a>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* View More Providers Action Footer */}
              <div className="ljf-footer-actions">
                <a href="/search/providers.php" className="btn btn-ghost btn-xl ljf-view-more-btn group">
                  <Search className="w-4 h-4 text-[#00D4C8] shrink-0" />
                  <span>Browse All Providers</span>
                  <ArrowRight className="w-4 h-4 text-current transition-transform duration-200 group-hover:translate-x-1 shrink-0" />
                </a>
                <a href="/auth/register.php?role=provider" className="btn btn-gold btn-xl">
                  + Join as a Provider (Free)
                </a>
              </div>
            </div>
          </section>
        );
      })()}

      {/* ══════ LIVE JOB FEED (LATEST 6 JOBS + VIEW MORE JOBS) ══════ */}
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
        const latestJobs = filteredJobs.slice(0, 7);

        return (
          <section className="section live-job-feed-section" id="live-jobs" ref={statsRef}>
            <div className="ljf-container">
              {/* Feed Header */}
              <div className="s-head">
                <div className="s-badge">
                  <span className="live-pulse-dot" />
                  <span>Live Job Opportunities in Ghana</span>
                </div>
                <h2 className="s-title">Live Job Feed</h2>
                <p className="s-sub">
                  Find real jobs posted by verified employers across Ghana. Your money is secured safely before you start, and paid directly to your MoMo the moment the work is done.
                </p>
              </div>

              {/* Category Filter Pills */}
              <div className="rjh-filter-tabs">
                {[
                  { id: 'all', label: 'All Open Gigs', count: allJobsList.length },
                  { id: 'tech', label: 'IT & Tech', count: allJobsList.filter((j: any) => j.cat_name?.toLowerCase().includes('tech') || j.cat_name?.toLowerCase().includes('it')).length || 2 },
                  { id: 'trades', label: 'Skilled Trades', count: allJobsList.filter((j: any) => j.cat_name?.toLowerCase().includes('trades') || j.cat_name?.toLowerCase().includes('skilled')).length || 3 },
                  { id: 'design', label: 'Creative & Arts', count: allJobsList.filter((j: any) => j.cat_name?.toLowerCase().includes('creative') || j.cat_name?.toLowerCase().includes('arts')).length || 2 },
                  { id: 'build', label: 'Construction', count: allJobsList.filter((j: any) => j.cat_name?.toLowerCase().includes('construct')).length || 1 },
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

              {/* Latest 6 Jobs Showcase Grid */}
              <div className="ljf-grid">
                {latestJobs.map((j: any, idx: number) => {
                  const isFeaturedHeroCard = idx === 0;
                  const isClientVerified = Boolean(j.is_verified === 1 || j.is_verified === true || j.is_client_verified === 1 || j.client_verified || j.is_kyc_verified || j.ghana_card_verified);
                  return (
                    <div
                      key={j.id || idx}
                      className={`ljf-card p-0 ${isFeaturedHeroCard ? 'ljf-card-lead' : ''}`}
                    >
                      <div className="ljf-card-inner">
                        {/* Client details with integrated live posted tag */}
                        <div className="rjh-jc-client">
                          <div className="rjh-jc-avatar">
                            {initials(j.first_name, j.last_name)}
                          </div>
                          <div className="rjh-jc-client-meta">
                            <div className="rjh-jc-client-name">
                              <span>{j.first_name} {j.last_name ? j.last_name[0] + '.' : ''}</span>
                              {isClientVerified && (
                                <span className="rjh-jc-kyc-tag">
                                  <BadgeCheck className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />
                                  <span>Verified Employer</span>
                                </span>
                              )}
                            </div>
                            <div className="rjh-jc-client-loc">
                              <span>📍 {j.location || 'Accra, Ghana'}</span>
                              <span className="rjh-jc-dot-sep">·</span>
                              <span className="rjh-jc-time-inline">
                                <span className="live-pulse-dot" />
                                <span>{timeAgo(j.created_at)}</span>
                              </span>
                            </div>
                          </div>
                          <div className="rjh-jc-badges-right">
                            {j.is_urgent === 1 && (
                              <span className="rjh-jc-urgent">
                                <Zap className="w-3 h-3 fill-[#D97706] text-[#D97706]" />
                                <span>Urgent</span>
                              </span>
                            )}
                            {isFeaturedHeroCard && (
                              <span className="ljf-spotlight-pill">
                                <Star className="w-3.5 h-3.5 fill-[#F59E0B] text-[#F59E0B] shrink-0" />
                                <span>Featured Lead</span>
                              </span>
                            )}
                          </div>
                        </div>

                        {/* Card Title */}
                        <h3 className="rjh-jc-title">
                          <a href={`/jobs.php?id=${j.id}`}>{j.title}</a>
                        </h3>

                        {/* Description Excerpt */}
                        <p className="rjh-jc-desc">
                          {j.description || 'Looking for a verified skilled professional in Ghana.'}
                        </p>

                        {/* Chips */}
                        <div className="rjh-jc-chips">
                          <span className="rjh-chip">
                            <ShieldCheck className="w-3.5 h-3.5 text-[#10B981] shrink-0" />
                            <span>Escrow Funded</span>
                          </span>
                          <span className="rjh-chip">
                            <Zap className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />
                            <span>Sub-60s MoMo</span>
                          </span>
                          <span className="rjh-chip">
                            <MessageSquare className="w-3.5 h-3.5 text-slate-400 shrink-0" />
                            <span>{j.proposal_count || 0} proposals</span>
                          </span>
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
                    </div>
                  );
                })}
              </div>

              {/* View More Jobs Action Footer */}
              <div className="ljf-footer-actions">
                <a href="/jobs.php" className="btn btn-ghost btn-xl ljf-view-more-btn group">
                  <Search className="w-4 h-4 text-[#00D4C8] shrink-0" />
                  <span>Browse All Jobs</span>
                  <ArrowRight className="w-4 h-4 text-current transition-transform duration-200 group-hover:translate-x-1 shrink-0" />
                </a>
                <a href="/post-job.php" className="btn btn-gold btn-xl">
                  + Post a Job (Free)
                </a>
              </div>
            </div>
          </section>
        );
      })()}

      {/* ══════ CATEGORIES WITH SPOTLIGHT CARDS ══════ */}
      <section className="section" id="categories">
        <div className="s-head">
          <div className="s-badge">
            <span className="live-pulse-dot" />
            <span>Explore Categories</span>
          </div>
          <h2 className="s-title">Every Skill. Every Master Craft in Ghana.</h2>
          <p className="s-sub">
            Direct access to verified Ghanaian artisans, tech engineers, creative directors, and building contractors.
          </p>
        </div>
        <div className="cat-grid">
          {categories.map((cat) => {
            const isHot = hotSlugs.includes(cat.slug || String(cat.id));
            const meta = getCategoryTheme(cat);
            return (
              <SpotlightCard
                key={cat.id}
                spotlightColor={meta.themeDim}
                className="cat-card p-0"
              >
                <a
                  href={`/search/providers.php?category=${cat.id}`}
                  className="cat-card-inner group"
                >
                  <div className="cat-card-top">
                    <div
                      className="cat-icon-box"
                      style={{
                        background: meta.themeDim,
                        borderColor: meta.themeBorder,
                      }}
                    >
                      {meta.icon}
                    </div>
                    {isHot ? (
                      <span className="cat-hot-pill">
                        <Zap className="w-3 h-3 fill-[#D97706] text-[#D97706]" />
                        <span>High Demand</span>
                      </span>
                    ) : (
                      <span
                        className="cat-active-pill"
                        style={{
                          background: meta.themeDim,
                          borderColor: meta.themeBorder,
                          color: meta.themeColor,
                        }}
                      >
                        <span className="live-pulse-dot" style={{ background: meta.themeColor }} />
                        <span>{meta.count}</span>
                      </span>
                    )}
                  </div>
                  
                  <div className="cat-content-block">
                    <div className="cat-name">{cat.name}</div>
                    <p className="cat-desc">
                      {cat.description || meta.tags.join(' · ')}
                    </p>
                  </div>

                  <div className="cat-chips-row">
                    {meta.tags.slice(0, 2).map((t: string, tIdx: number) => (
                      <span key={tIdx} className="cat-micro-chip">
                        {t}
                      </span>
                    ))}
                  </div>

                  <div className="cat-arrow-row">
                    <span>Explore Specialists</span>
                    <ArrowRight className="w-3.5 h-3.5 text-[#00D4C8] transition-transform duration-200 group-hover:translate-x-1" />
                  </div>
                </a>
              </SpotlightCard>
            );
          })}
        </div>
      </section>

      {/* ══════ TRENDING SKILLS SEARCH PILLS ══════ */}
      <section className="section" id="trending" style={{ paddingTop: '4px', paddingBottom: '28px' }}>
        <div className="s-head">
          <div className="s-badge">
            <span className="live-pulse-dot" />
            <span>Trending Searches</span>
          </div>
          <h2 className="s-title">Most In-Demand Skills This Week</h2>
          <p className="s-sub">Real-time keyword searches by homeowners, developers, and businesses across Accra, Kumasi, and Takoradi.</p>
        </div>
        <div className="trending-wrap">
          {trends.map(([ic, lb, nm], idx) => (
            <a
              key={idx}
              href={`/search/providers.php?q=${encodeURIComponent(lb)}`}
              className="trend-pill group"
            >
              <Search className="w-3.5 h-3.5 text-[#00D4C8] opacity-75 group-hover:opacity-100 transition-opacity" />
              <span className="trend-label">{lb}</span>
              <span className="trend-num">{nm}</span>
            </a>
          ))}
        </div>
      </section>

      {/* ══════ MODERN BENTO GRID: WHY GIGHANA & PROCESS ══════ */}
      <section className="section" id="how">
        <div className="s-head">
          <div className="s-badge">
            <span className="live-pulse-dot" />
            <span>Platform Highlights</span>
          </div>
          <h2 className="s-title">Why Ghanaian Talent &amp; Businesses Choose GigGhana</h2>
          <p className="s-sub">Built from the ground up for safety, speed, and real-world African commerce.</p>
        </div>

        <BentoGrid>
          {/* Card 1: 100% Ghana Card Biometric Verification */}
          <BentoCard
            title="1. 100% Ghana Card Biometric Verification"
            description="Every artisan, freelancer, and contractor is strictly authenticated with the National Identification Authority (NIA) database. Zero fake accounts, zero impostors."
            icon={<ShieldCheck className="w-5 h-5 text-[var(--cyan)]" />}
            badge="National Trust"
            spotlightColor="rgba(0, 212, 200, 0.18)"
            className="md:col-span-2"
            header={
              <div className="hidden md:block mt-3.5 w-full">
                <GhanaCard />
              </div>
            }
          />

          {/* Card 2: AI Scope Generator & Transparent Cedi Milestones */}
          <BentoCard
            title="2. AI Scopes &amp; Cedi Milestones"
            description="Our Ghanaian AI analyzes project requirements, generates market-rate Cedi (₵) estimates, and auto-matches verified artisans in your town within 15 minutes."
            icon={<Zap className="w-5 h-5 text-[#F59E0B]" />}
            badge="AI Powered"
            spotlightColor="rgba(245, 158, 11, 0.18)"
            header={
              <div className="ai-scope-sim">
                <div className="ai-scope-prompt">
                  <Sparkles className="w-3.5 h-3.5 text-[#F59E0B] shrink-0" />
                  <span className="truncate">&ldquo;POP Ceiling for 3-Bedroom Hall in Kumasi&rdquo;</span>
                </div>
                <div className="ai-scope-result">
                  <span className="font-semibold text-[var(--tx)]">Est. ₵3,800 – ₵4,400</span>
                  <span className="ai-scope-badge">⚡ 3 Pros Ready</span>
                </div>
              </div>
            }
          />

          {/* Card 3: Sub-60s Mobile Money Payouts with Realistic Logos */}
          <BentoCard
            title="3. Sub-60s Mobile Money Settlements"
            description="Direct integration with MTN MoMo, Telecel Cash, and AT Money. Deliverables signed off? Funds transfer to your wallet with zero withdrawal wait."
            icon={<Smartphone className="w-5 h-5 text-[#10B981]" />}
            badge="Instant Payouts"
            spotlightColor="rgba(16, 185, 129, 0.18)"
            header={
              <div className="momo-sim">
                <div className="momo-logos-row">
                  <span className="momo-brand-badge momo-brand-mtn">MTN MoMo</span>
                  <span className="momo-brand-badge momo-brand-telecel">Telecel Cash</span>
                  <span className="momo-brand-badge momo-brand-at">AT Money</span>
                </div>
                <div className="momo-alert-box">
                  <div className="flex items-center justify-between text-[10px] text-[var(--tx-3)] mb-1">
                    <span>🔔 Mobile Money Alert</span>
                    <span className="text-[#10B981] font-bold">42s Settlement</span>
                  </div>
                  <div className="font-semibold text-[var(--tx)] text-[11px]">
                    Received: ₵2,500.00 from Escrow Vault
                  </div>
                </div>
              </div>
            }
          />

          {/* Card 4: Bank-Grade Regulated Escrow Vault */}
          <BentoCard
            title="4. Bank-Grade Regulated Escrow Vault"
            description="Clients never risk paying upfront for shoddy work, and artisans never risk non-payment after completion. Funds stay safe in escrow until you inspect and approve."
            icon={<Award className="w-5 h-5 text-[var(--cyan)]" />}
            badge="100% Zero-Fraud"
            spotlightColor="rgba(0, 212, 200, 0.18)"
            className="md:col-span-2"
            header={
              <div className="escrow-vault-sim">
                <div className="flex items-center justify-between text-xs">
                  <div className="flex items-center gap-2">
                    <span className="text-[var(--cyan)] font-bold">🔒 Bank-Grade Regulated Escrow Vault</span>
                  </div>
                  <span className="text-[11px] font-mono text-[var(--tx-3)]">Contract #GG-8849 · ₵6,500</span>
                </div>
                <div className="escrow-milestones-track">
                  <div className="escrow-milestone-step completed">
                    <div className="font-bold text-[#10B981] flex items-center gap-1">
                      <CheckCircle2 className="w-3 h-3" />
                      <span>Phase 1: Framing</span>
                    </div>
                    <div className="text-[10px] text-[var(--tx-2)] mt-0.5">₵2,000 Released to MoMo</div>
                  </div>
                  <div className="escrow-milestone-step locked">
                    <div className="font-bold text-[#D97706] flex items-center gap-1">
                      <span>🔒 Phase 2: Wiring</span>
                    </div>
                    <div className="text-[10px] text-[var(--tx-2)] mt-0.5">₵3,000 Locked in Vault</div>
                  </div>
                  <div className="escrow-milestone-step">
                    <div className="font-bold text-[var(--tx-3)] flex items-center gap-1">
                      <span>⏳ Phase 3: Paint</span>
                    </div>
                    <div className="text-[10px] text-[var(--tx-2)] mt-0.5">₵1,500 Pending Approval</div>
                  </div>
                </div>
              </div>
            }
          />
        </BentoGrid>

        {/* ══════ MEMBERSHIP TIERS (COMPACT & SQUEEZED) ══════ */}
        <div className="badge-tiers mt-10">
          {/* Beginner Tier */}
          <div className="badge-tier-card tier-beginner">
            <div className="bt-top-row">
              <div className="bt-icon-box beginner-icon">
                <Sprout className="w-4 h-4 text-[#10B981]" />
              </div>
              <span className="bt-tier-pill beginner-pill">🌱 Starter</span>
            </div>
            <div className="bt-name">Beginner</div>
            <div className="bt-price">
              <span className="bt-price-val">Free</span>
              <span className="bt-price-sub">/ forever</span>
            </div>
            <p className="bt-desc">Plant your roots at zero cost. Set up your profile and start bidding.</p>
            <div className="bt-perks-list">
              <div className="bt-perk-item">
                <Check className="w-3.5 h-3.5 text-[#10B981] shrink-0" />
                <span>3 proposals per month</span>
              </div>
              <div className="bt-perk-item">
                <Check className="w-3.5 h-3.5 text-[#10B981] shrink-0" />
                <span>Standard directory listing</span>
              </div>
              <div className="bt-perk-item">
                <Check className="w-3.5 h-3.5 text-[#10B981] shrink-0" />
                <span>MoMo escrow payouts</span>
              </div>
            </div>
            <a href="/auth/register.php?role=provider" className="btn btn-ghost bt-action-btn bt-btn-beginner">
              Get Started Free
            </a>
          </div>

          {/* Verified Tier (Highlighted) */}
          <div className="badge-tier-card featured">
            <div className="bt-pop-badge">👑 Most Popular</div>
            <div className="bt-top-row">
              <div className="bt-icon-box featured-icon">
                <BadgeCheck className="w-4 h-4 text-[#00D4C8]" />
              </div>
              <span className="bt-tier-pill featured-pill">Verified Pro</span>
            </div>
            <div className="bt-name">Verified Pro</div>
            <div className="bt-price">
              <span className="bt-price-val">₵49</span>
              <span className="bt-price-sub">/ month</span>
            </div>
            <p className="bt-desc">Build high client trust and win verified jobs faster across Ghana.</p>
            <div className="bt-perks-list">
              <div className="bt-perk-item">
                <Check className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />
                <span><strong>✓ Verified Ghana Card badge</strong></span>
              </div>
              <div className="bt-perk-item">
                <Check className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />
                <span><strong>Unlimited</strong> proposals &amp; gigs</span>
              </div>
              <div className="bt-perk-item">
                <Check className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />
                <span><strong>3x Higher</strong> search visibility</span>
              </div>
            </div>
            <a href="/auth/register.php?role=provider&tier=verified" className="btn btn-gold bt-action-btn">
              Get Verified Status
            </a>
          </div>

          {/* Premium Tier */}
          <div className="badge-tier-card tier-premium">
            <div className="bt-top-row">
              <div className="bt-icon-box premium-icon">
                <Star className="w-4 h-4 text-[#F59E0B] fill-[#F59E0B]" />
              </div>
              <span className="bt-tier-pill premium-pill">⭐ Elite</span>
            </div>
            <div className="bt-name">Premium Master</div>
            <div className="bt-price">
              <span className="bt-price-val">₵99</span>
              <span className="bt-price-sub">/ month</span>
            </div>
            <p className="bt-desc">For top contractors and specialists seeking direct client contracts.</p>
            <div className="bt-perks-list">
              <div className="bt-perk-item">
                <Check className="w-3.5 h-3.5 text-[#F59E0B] shrink-0" />
                <span><strong>⭐ Featured Top Listing</strong> on home</span>
              </div>
              <div className="bt-perk-item">
                <Check className="w-3.5 h-3.5 text-[#F59E0B] shrink-0" />
                <span>Direct client invitations</span>
              </div>
              <div className="bt-perk-item">
                <Check className="w-3.5 h-3.5 text-[#F59E0B] shrink-0" />
                <span>Priority 24/7 dedicated support</span>
              </div>
            </div>
            <a href="/auth/register.php?role=provider&tier=premium" className="btn bt-action-btn bt-btn-premium">
              Upgrade to Premium
            </a>
          </div>
        </div>

        {/* ══════ TRUSTED PAYMENT & ESCROW SETTLEMENT PARTNERS MARQUEE ══════ */}
        <div className="pay-partners-marquee-wrap mt-12">
          <div className="pay-partners-head">
            <div className="pay-partners-badge">
              <span className="live-pulse-dot" />
              <span>Instant MoMo &amp; Escrow Infrastructure</span>
            </div>
            <h3 className="pay-partners-title">Trusted Payment &amp; Escrow Settlement Partners</h3>
            <p className="pay-partners-sub">
              Sub-60s payouts directly to all Ghanaian mobile wallets and bank cards with 100% bank-grade escrow protection.
            </p>
          </div>

          <div className="pay-marquee-outer relative overflow-hidden mt-6">
            <div className="pointer-events-none absolute inset-y-0 left-0 w-16 md:w-28 bg-gradient-to-r from-[var(--bg)] to-transparent z-10" />
            <div className="pointer-events-none absolute inset-y-0 right-0 w-16 md:w-28 bg-gradient-to-l from-[var(--bg)] to-transparent z-10" />

            <Marquee pauseOnHover className="[--duration:26s] py-2">
              {paymentPartners.map((item, idx) => (
                <div key={idx} className="pay-partner-card">
                  {item.icon ? (
                    <img src={item.icon} alt={item.name} className="pay-partner-logo" />
                  ) : (
                    <div className="pay-partner-icon-box">
                      <ShieldCheck className="w-5 h-5 text-[var(--cyan)]" />
                    </div>
                  )}
                  <div className="pay-partner-meta">
                    <span className="pay-partner-name">{item.name}</span>
                    <span className="pay-partner-badge-text">{item.badge}</span>
                  </div>
                </div>
              ))}
            </Marquee>
          </div>
        </div>
      </section>

      {/* ══════ REVIEWS & TESTIMONIALS ══════ */}
      <section className="section section-reviews" id="reviews" style={{ paddingTop: '20px', paddingBottom: '36px' }}>
        <div className="s-head">
          <div className="s-badge">
            <span className="live-pulse-dot" />
            <span>Real Stories from the Field</span>
          </div>
          <h2 className="s-title">Real Talk from Verified Masters &amp; Clients</h2>
          <p className="s-sub">
            No stories. No chasing clients for money. See how Ghana Card verification, secure escrow, and instant Mobile Money settlements transformed work across Ghana.
          </p>
        </div>

        {/* Sliding Carousel Swiping Left to Right */}
        <div className="rv-carousel-wrap relative overflow-hidden">
          <div className="pointer-events-none absolute inset-y-0 left-0 w-16 md:w-32 bg-gradient-to-r from-[var(--bg)] to-transparent z-10" />
          <div className="pointer-events-none absolute inset-y-0 right-0 w-16 md:w-32 bg-gradient-to-l from-[var(--bg)] to-transparent z-10" />

          <Marquee reverse pauseOnHover className="[--duration:28s] py-3">
            {testimonialFallbacks.map((rv, idx) => {
              const avatarFallbacks = [
                '/images/avatars/avatar_male_1.jpg',
                '/images/avatars/avatar_female_1.jpg',
                '/images/avatars/avatar_male_2.jpg',
                '/images/avatars/avatar_female_3.jpg',
                '/images/avatars/avatar_female_2.jpg',
                '/images/avatars/avatar_male_3.jpg',
              ];
              const avatarSrc = rv.avatar || avatarFallbacks[idx % avatarFallbacks.length];

              return (
                <div key={idx} className="rv-card">
                  <div className="rv-card-header">
                    <div className="rv-av-box">
                      <img
                        src={avatarSrc}
                        alt={`${rv.first_name} ${rv.last_name}`}
                        className="rv-avatar-img"
                        loading="eager"
                      />
                      <span className="rv-online-dot" />
                    </div>
                    <div className="rv-meta">
                      <div className="rv-name-row">
                        <span className="rv-name">{rv.first_name} {rv.last_name}</span>
                        <span className="rv-verified-check" title="Biometric Ghana Card Verified">
                          <BadgeCheck className="w-3.5 h-3.5 text-[#00D4C8]" />
                        </span>
                      </div>
                      <span className="rv-trade">{rv.trade} · {rv.location}</span>
                    </div>
                    <div className="rv-stars flex items-center gap-0.5 shrink-0" aria-label="5 stars">
                      {[...Array(5)].map((_, sIdx) => (
                        <Star key={sIdx} className="w-3 h-3 text-[#F59E0B] fill-[#F59E0B]" />
                      ))}
                    </div>
                  </div>
                  <p className="rv-text">&ldquo;{rv.comment}&rdquo;</p>
                  <div className="rv-card-footer">
                    <span className="rv-tag-pill">
                      {rv.role === 'provider' ? '🛠️ Verified Master' : '🏢 Verified Client'}
                    </span>
                    <div className="rv-proof-pill">
                      <CheckCircle2 className="w-3 h-3 text-[#10B981] shrink-0" />
                      <span>{rv.payout_proof || 'Escrow Released'}</span>
                    </div>
                  </div>
                </div>
              );
            })}
          </Marquee>
        </div>
      </section>

      {/* ══════ REVERTED FINAL CTA ══════ */}
      <div className="cta-wrap">
        <div className="cta-glo" />
        <div className="cta-glo2" />
        <div className="cta-inner">
          <h2 className="cta-title">
            Join Thousands of Ghanaians<br />Winning Every Day
          </h2>
          <p className="cta-sub">
            Join {stats.providers.toLocaleString()} verified service providers, master artisans, and {stats.clients.toLocaleString()} businesses already on GigGhana.<br className="hidden md:inline" /> Africa&apos;s talent economy starts here.
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
              Hire a Provider
            </a>
          </div>
        </div>
      </div>

      {/* ══════ EXECUTIVE FOOTER (DEVELOPED BY TECHROOM GHANA) ══════ */}
      <footer className="footer-wrap">
        <div className="footer-container">
          {/* Main 4-Column Directory Grid */}
          <div className="footer-grid">
            {/* Column 1: Brand & Live WhatsApp Concierge */}
            <div className="footer-brand-col">
              <a href="/" className="logo">
                <div className="logo-mark">G</div>
                <span className="logo-text">
                  Gig<span>Ghana</span>
                </span>
              </a>
              <p className="footer-brand-desc">
                Ghana&apos;s verified on-demand talent ecosystem connecting authenticated master artisans, skilled technicians, and digital pros with clients nationwide.
              </p>

              {/* Dedicated WhatsApp Concierge Pill with Official WhatsApp Logo */}
              <a
                href="https://wa.me/233200000000"
                target="_blank"
                rel="noopener noreferrer"
                className="footer-whatsapp-btn"
                title="Live 24/7 WhatsApp Concierge"
              >
                <WhatsAppIcon className="w-5 h-5 shrink-0" />
                <div className="f-wa-text">
                  <span className="f-wa-title">WhatsApp Concierge</span>
                  <span className="f-wa-sub">Instant Support · 24/7 Online</span>
                </div>
              </a>

              {/* Direct Contacts */}
              <div className="footer-contacts">
                <a href="mailto:support@gigghana.com" className="footer-contact-link">
                  <Mail className="w-3.5 h-3.5 text-[#00D4C8] shrink-0" />
                  <span>support@gigghana.com</span>
                </a>
                <a href="tel:+233501234567" className="footer-contact-link">
                  <Phone className="w-3.5 h-3.5 text-[#10B981] shrink-0" />
                  <span>+233 (0) 50 123 4567</span>
                </a>
                <div className="footer-contact-link text-muted">
                  <span>📍 Accra Digital Center, Ring Road West</span>
                </div>
              </div>
            </div>

            {/* Column 2: Master Trades */}
            <div className="footer-col">
              <div className="footer-col-ttl">Master Trades</div>
              <ul className="footer-col-links">
                <li><a href="/search/providers.php?cat=trades">Building &amp; Masonry</a></li>
                <li><a href="/search/providers.php?cat=trades">POP Ceilings &amp; Painting</a></li>
                <li><a href="/search/providers.php?cat=trades">Electrical &amp; Solar Inverters</a></li>
                <li><a href="/search/providers.php?cat=trades">Plumbing &amp; Water Systems</a></li>
                <li><a href="/search/providers.php?cat=trades">Bespoke Joinery &amp; Furniture</a></li>
                <li><a href="/search/providers.php?cat=tech">Software &amp; Web Apps</a></li>
                <li><a href="/search/providers.php?cat=creative">Fashion &amp; Haute Couture</a></li>
              </ul>
            </div>

            {/* Column 3: For Clients */}
            <div className="footer-col">
              <div className="footer-col-ttl">For Clients</div>
              <ul className="footer-col-links">
                <li><a href="/search/providers.php">Find Verified Talent</a></li>
                <li><a href="/jobs.php">Browse Live Job Feed</a></li>
                <li><a href="/auth/register.php?role=client">Post a Project Requirement</a></li>
                <li><a href="#how">How Escrow Protects You</a></li>
                <li><a href="/search/providers.php?loc=Accra">Artisans in Accra</a></li>
                <li><a href="/search/providers.php?loc=Kumasi">Contractors in Kumasi</a></li>
                <li><a href="#how">Milestone Inspection Guide</a></li>
              </ul>
            </div>

            {/* Column 4: For Artisans & Company */}
            <div className="footer-col">
              <div className="footer-col-ttl">For Artisans &amp; Trust</div>
              <ul className="footer-col-links">
                <li><a href="/auth/register.php?role=provider">Join as an Artisan</a></li>
                <li><a href="#how">Membership Tiers (₵0 - ₵99)</a></li>
                <li><a href="/auth/register.php?role=provider&tier=verified">Get Verified Pro Badge</a></li>
                <li><a href="#how">Ghana Card Biometric Guide</a></li>
                <li><a href="#how">Instant MoMo Withdrawal FAQ</a></li>
                <li><a href="/terms.php">Terms of Service</a></li>
                <li><a href="/privacy.php">Privacy Policy (Act 843)</a></li>
              </ul>
            </div>
          </div>

          {/* Newsletter & Rate Alert Strip */}
          <div className="footer-nl-strip">
            <div className="footer-nl-info">
              <span className="footer-nl-title">Get Weekly Artisan Market Rates &amp; Job Alerts</span>
              <span className="footer-nl-desc">Stay ahead with verified trade rates, pricing trends, and project briefs in Ghana.</span>
            </div>
            <form className="nl-form" onSubmit={handleSubscribeNL}>
              <input
                className="nl-input"
                type="email"
                placeholder="Enter your email address"
                value={nlEmail}
                onChange={(e) => setNlEmail(e.target.value)}
              />
              <button type="submit" className="btn btn-gold nl-btn">
                Subscribe
              </button>
            </form>
          </div>

          {/* Regional Hubs Ribbon */}
          <div className="footer-regions-bar">
            <span className="footer-regions-title">Serving All 16 Regions:</span>
            <div className="footer-regions-list">
              <a href="/search/providers.php?loc=Accra">Greater Accra</a>
              <span className="dot-sep">·</span>
              <a href="/search/providers.php?loc=Kumasi">Ashanti (Kumasi)</a>
              <span className="dot-sep">·</span>
              <a href="/search/providers.php?loc=Takoradi">Western (Takoradi)</a>
              <span className="dot-sep">·</span>
              <a href="/search/providers.php?loc=Tamale">Northern (Tamale)</a>
              <span className="dot-sep">·</span>
              <a href="/search/providers.php?loc=CapeCoast">Central (Cape Coast)</a>
              <span className="dot-sep">·</span>
              <a href="/search/providers.php?loc=Sunyani">Bono (Sunyani)</a>
              <span className="dot-sep">·</span>
              <a href="/search/providers.php?loc=Tema">Tema Industrial</a>
              <span className="dot-sep">·</span>
              <a href="/search/providers.php?loc=Ho">Volta (Ho)</a>
            </div>
          </div>

          {/* Bottom Row: TechRoom Ghana Attribution, Copyright & Realistic Social Icons */}
          <div className="footer-bottom-bar">
            <div className="footer-copy">
              <span>© {new Date().getFullYear()} GigGhana Ltd. All rights reserved.</span>
              <span className="hidden sm:inline"> · </span>
              <span className="text-muted">Empowering Ghanaian Talent 🇬🇭</span>
            </div>

            {/* Developed by TechRoom Ghana Badge */}
            <div className="footer-credit">
              <span>Developed by</span>
              <a
                href="https://techroomghana.com"
                target="_blank"
                rel="noopener noreferrer"
                className="techroom-badge"
                title="TechRoom Ghana · Enterprise Software & Engineering"
              >
                <span className="techroom-pulse" />
                <strong>TechRoom Ghana</strong>
              </a>
            </div>

            {/* Realistic Social Media Icons */}
            <div className="footer-socials-realistic">
              <a
                className="soc-btn-realistic"
                href="https://wa.me/233200000000"
                target="_blank"
                rel="noopener noreferrer"
                title="WhatsApp"
                aria-label="WhatsApp"
              >
                <WhatsAppIcon className="w-5 h-5" />
              </a>
              <a
                className="soc-btn-realistic"
                href="#"
                title="X / Twitter"
                aria-label="Twitter"
              >
                <TwitterXIcon className="w-5 h-5" />
              </a>
              <a
                className="soc-btn-realistic"
                href="#"
                title="LinkedIn"
                aria-label="LinkedIn"
              >
                <LinkedInIcon className="w-5 h-5" />
              </a>
              <a
                className="soc-btn-realistic"
                href="#"
                title="Instagram"
                aria-label="Instagram"
              >
                <InstagramIcon className="w-5 h-5" />
              </a>
              <a
                className="soc-btn-realistic"
                href="#"
                title="Facebook"
                aria-label="Facebook"
              >
                <FacebookIcon className="w-5 h-5" />
              </a>
            </div>
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
