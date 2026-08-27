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
import { SpotlightCard } from './ui/spotlight-card';
import { BentoGrid, BentoCard } from './ui/bento-grid';
import { Marquee } from './ui/marquee';
import { GhanaCard } from './ui/ghana-card';
import { CommandSearchDialog } from './ui/command-dialog';
import {
  Search,
  ShieldCheck,
  Zap,
  Smartphone,
  Award,
  Sparkles,
  CheckCircle2,
  ArrowRight,
  Star,
  Lock,
  Briefcase,
  TrendingUp,
  Users,
  MapPin,
  Clock,
  Check,
  Calculator,
} from 'lucide-react';

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

const trendingSkills = [
  '⚡ React Developer',
  '🎨 UI/UX Designer',
  '🔧 Electrician',
  '🏥 Home Nurse',
  '🍽️ Private Chef',
  '📈 Digital Marketer',
  '🪚 Carpenter',
];

const disciplines = [
  { name: 'Full-Stack Developer', icon: '💻', hourly: 95 },
  { name: 'UI/UX Designer', icon: '🎨', hourly: 70 },
  { name: 'Electrician / Plumber', icon: '🔧', hourly: 55 },
  { name: 'Private Executive Chef', icon: '🍽️', hourly: 75 },
  { name: 'Home Care Nurse', icon: '🏥', hourly: 65 },
  { name: 'Photographer / Video', icon: '📷', hourly: 80 },
];

function rankLabel(jobs: number) {
  if (jobs >= 50) return { i: '🏆', l: 'Elite Expert', c: 'text-amber-400 bg-amber-500/10 border-amber-500/20' };
  if (jobs >= 20) return { i: '⭐', l: 'Top Rated', c: 'text-blue-400 bg-blue-500/10 border-blue-500/20' };
  if (jobs >= 5) return { i: '📈', l: 'Rising Talent', c: 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' };
  return { i: '🌱', l: 'New Freelancer', c: 'text-slate-400 bg-slate-500/10 border-slate-500/20' };
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

export default function ModernBlueLandingPage({ initialData }: Props) {
  const { stats, categories, featured, matchedProviders, recentJobs, liveJobs, earningsData, earningsTotal, reviews } = initialData;

  // Perspective: 'hire' vs 'work'
  const [role, setRole] = useState<'hire' | 'work'>('hire');

  // Theme: light by default as primary, with dark mode toggle
  const [isLight, setIsLight] = useState(true);

  // Language state
  const [lang, setLang] = useState<'en' | 'tw'>('en');

  // Command palette state
  const [isCommandOpen, setIsCommandOpen] = useState(false);

  // Mobile menu state
  const [isMobOpen, setIsMobOpen] = useState(false);

  // Search input & autocomplete
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');
  const [selectedRegion, setSelectedRegion] = useState('');
  const [autocompleteOpen, setAutocompleteOpen] = useState(false);

  // Dynamic Keyword Ticker
  const tickerItems = [
    'React Developers',
    'Licensed Electricians',
    'Graphic Designers',
    'Home Care Nurses',
    'Master Carpenters',
    'Private Chefs',
    'Flutter Developers',
  ];
  const [tickerIndex, setTickerIndex] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setTickerIndex((prev) => (prev + 1) % tickerItems.length);
    }, 2500);
    return () => clearInterval(timer);
  }, [tickerItems.length]);

  // Earnings calculator state
  const [calcIndex, setCalcIndex] = useState(0);
  const [calcHours, setCalcHours] = useState(25);

  // Toast notifications
  const [toasts, setToasts] = useState<ToastItem[]>([]);

  // Navbar scroll state
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
        particleCount: 85,
        spread: 70,
        origin: { y: 0.7 },
        colors: ['#2563EB', '#3B82F6', '#60A5FA', '#06B6D4', '#10B981'],
      });
    } catch {
      // ignore
    }
  };

  // Initialize theme from localStorage / cookie (default: light)
  useEffect(() => {
    const savedTheme = localStorage.getItem('gg_theme');
    const lightMode = savedTheme === null ? true : savedTheme === 'light';
    setIsLight(lightMode);
    if (lightMode) {
      document.body.classList.add('lm');
      document.documentElement.classList.add('lm');
      document.body.classList.remove('dark');
      document.documentElement.classList.remove('dark');
      document.documentElement.setAttribute('data-theme', 'light');
    } else {
      document.body.classList.remove('lm');
      document.documentElement.classList.remove('lm');
      document.body.classList.add('dark');
      document.documentElement.classList.add('dark');
      document.documentElement.setAttribute('data-theme', 'dark');
    }
  }, []);

  const toggleTheme = () => {
    const nextLight = !isLight;
    setIsLight(nextLight);
    if (nextLight) {
      document.body.classList.add('lm');
      document.documentElement.classList.add('lm');
      document.body.classList.remove('dark');
      document.documentElement.classList.remove('dark');
      document.documentElement.setAttribute('data-theme', 'light');
      localStorage.setItem('gg_theme', 'light');
      document.cookie = 'gg_theme=light;path=/;max-age=31536000;SameSite=Lax';
      showToast('Theme', 'Switched to Light Mode', 'info');
    } else {
      document.body.classList.remove('lm');
      document.documentElement.classList.remove('lm');
      document.body.classList.add('dark');
      document.documentElement.classList.add('dark');
      document.documentElement.setAttribute('data-theme', 'dark');
      localStorage.setItem('gg_theme', 'dark');
      document.cookie = 'gg_theme=dark;path=/;max-age=31536000;SameSite=Lax';
      showToast('Theme', 'Switched to Dark Mode', 'info');
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

  // Stats Animation
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
      showToast('Notice', 'Please enter a valid email address.', 'error');
      return;
    }
    triggerConfetti();
    showToast('Subscribed! 🇬🇭', 'Welcome to the GigGhana dispatch.', 'success');
    setNlEmail('');
  };

  // Calculator calculations
  const curDiscipline = disciplines[calcIndex];
  const estMonthly = Math.round(curDiscipline.hourly * calcHours * 4.33);

  // Chart Config
  const chartData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [
      {
        label: 'Escrow Volume Released (₵)',
        data: earningsData,
        borderColor: '#3B82F6',
        backgroundColor: 'rgba(59, 130, 246, 0.08)',
        borderWidth: 3,
        pointBackgroundColor: '#2563EB',
        pointRadius: earningsData.some((v) => v > 0) ? 4 : 0,
        pointHoverRadius: 6,
        fill: true,
        tension: 0.4,
      },
    ],
  };

  const chartOptions = {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: 'var(--surface)',
        titleColor: 'var(--tx)',
        bodyColor: 'var(--tx-2)',
        borderColor: 'var(--border-hi)',
        borderWidth: 1,
        titleFont: { family: 'Plus Jakarta Sans', weight: 'bold' },
        callbacks: {
          label: (context: any) =>
            ' ₵' + context.parsed.y.toLocaleString('en-GH', { minimumFractionDigits: 2 }),
        },
      },
    },
    scales: {
      x: {
        grid: { color: 'var(--border)' },
        ticks: { color: 'var(--tx-3)', font: { size: 11, family: 'DM Sans' } },
      },
      y: {
        grid: { color: 'var(--border)' },
        beginAtZero: true,
        ticks: {
          color: 'var(--tx-3)',
          font: { size: 11, family: 'DM Sans' },
          callback: (v: any) => '₵' + Number(v).toLocaleString(),
        },
      },
    },
  };

  return (
    <div className="min-h-screen bg-[var(--bg)] text-[var(--tx)] selection:bg-[#2563EB] selection:text-white transition-colors duration-300">
      {/* ══════ COMMAND SEARCH MODAL (Cmd + K) ══════ */}
      <CommandSearchDialog
        open={isCommandOpen}
        onOpenChange={setIsCommandOpen}
        categories={categories}
      />


      {/* ══════ MODERN NAVBAR ══════ */}
      <header
        className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
          scrolledNav
            ? 'bg-[var(--glass)] backdrop-blur-2xl border-b border-[var(--border-hi)] py-3.5 shadow-2xl'
            : 'bg-transparent py-5'
        }`}
      >
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
          <a href="/" className="flex items-center gap-2.5 group">
            <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-tr from-[#2563EB] via-[#3B82F6] to-[#06B6D4] p-[1.5px] shadow-lg shadow-blue-500/25 group-hover:scale-105 transition-transform">
              <div className="flex h-full w-full items-center justify-center rounded-[14px] bg-[var(--bg)] font-heading font-extrabold text-lg text-white">
                G
              </div>
            </div>
            <span className="font-heading font-bold text-xl tracking-tight text-[var(--tx)]">
              Gig<span className="text-[#3B82F6]">Ghana</span>
            </span>
          </a>

          {/* Desktop Nav Links */}
          <nav className="hidden md:flex items-center gap-7 text-xs font-semibold text-[var(--tx-2)]">
            <a href="/search/providers.php" className="hover:text-[var(--tx)] transition-colors">
              Find Talent
            </a>
            <a href="/jobs.php" className="hover:text-[var(--tx)] transition-colors">
              Browse Jobs
            </a>
            <a href="#how" className="hover:text-[var(--tx)] transition-colors">
              How It Works
            </a>
            <a href="#categories" className="hover:text-[var(--tx)] transition-colors">
              Categories
            </a>
            <a href="#trending" className="hover:text-[var(--tx)] transition-colors">
              Trending
            </a>
          </nav>

          {/* Nav Controls */}
          <div className="flex items-center gap-3">
            <button
              onClick={toggleLang}
              className="px-2.5 py-1.5 rounded-xl border border-[var(--border)] bg-[var(--surface-2)] text-xs font-bold text-[var(--tx-2)] hover:text-[var(--tx)] transition-all"
              title="Toggle Language"
            >
              🌍 {lang === 'en' ? 'EN' : 'TW'}
            </button>

            <button
              onClick={toggleTheme}
              className="p-2 rounded-xl border border-[var(--border)] bg-[var(--surface-2)] text-xs text-[var(--tx-2)] hover:text-[var(--tx)] transition-all"
              title="Toggle Theme"
            >
              {isLight ? '☀️' : '🌙'}
            </button>

            <a
              href="/auth/login.php"
              className="hidden sm:inline-flex px-4 py-2 text-xs font-bold text-[var(--tx-2)] hover:text-[var(--tx)] transition-colors"
            >
              Sign In
            </a>

            <a
              href="/auth/register.php"
              onClick={triggerConfetti}
              className="px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#2563EB] to-[#3B82F6] hover:brightness-110 shadow-lg shadow-blue-500/25 transition-all font-heading"
            >
              Get Started Free
            </a>

            {/* Mobile Hamburger */}
            <button
              onClick={() => setIsMobOpen(!isMobOpen)}
              className="md:hidden p-2 text-[var(--tx-2)] hover:text-[var(--tx)]"
            >
              <div className="w-5 h-0.5 bg-[var(--tx)] mb-1" />
              <div className="w-5 h-0.5 bg-[var(--tx)] mb-1" />
              <div className="w-5 h-0.5 bg-[var(--tx)]" />
            </button>
          </div>
        </div>

        {/* Mobile Nav */}
        {isMobOpen && (
          <div className="md:hidden mt-3 px-6 py-6 bg-[var(--surface)] border-b border-[var(--border-hi)] space-y-4 text-xs font-bold">
            <a href="/search/providers.php" className="block text-[var(--tx-2)] hover:text-[var(--tx)]">Find Talent</a>
            <a href="/jobs.php" className="block text-[var(--tx-2)] hover:text-[var(--tx)]">Browse Jobs</a>
            <a href="#how" className="block text-[var(--tx-2)] hover:text-[var(--tx)]">Why GigGhana</a>
            <a href="#calculator" className="block text-[var(--tx-2)] hover:text-[var(--tx)]">Income Estimator</a>
            <a href="/auth/login.php" className="block text-[var(--tx-2)] hover:text-[var(--tx)]">Sign In</a>
          </div>
        )}
      </header>

      {/* ══════ WORLD-CLASS RESTRUCTURED HERO SECTION ══════ */}
      <section className="relative pt-32 pb-20 md:pt-36 md:pb-24 overflow-hidden border-b border-[var(--border)]">
        {/* Ambient Gradient Mesh */}
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] rounded-full bg-gradient-to-b from-[#2563EB]/10 via-[#3B82F6]/5 to-transparent blur-[140px] pointer-events-none" />
        <div className="absolute -top-24 -left-24 w-[400px] h-[400px] rounded-full bg-[#009E95]/5 blur-[120px] pointer-events-none" />

        {/* Subtle geometric dot matrix */}
        <div
          className="absolute inset-0 opacity-[0.025] pointer-events-none"
          style={{
            backgroundImage: `radial-gradient(var(--tx) 1px, transparent 1px)`,
            backgroundSize: '28px 28px',
          }}
        />

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
            {/* Left Column: Value Prop, Perspective Switcher & Search */}
            <div className="lg:col-span-7 text-left space-y-6">
              {/* Badge + Dynamic Ticker */}
              <div className="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-[var(--blue-dim)] border border-[var(--border-hi)] text-xs font-semibold text-[#2563EB] shadow-xs">
                <span className="flex h-2 w-2 rounded-full bg-[#2563EB] animate-pulse" />
                <span>Ghana&apos;s #1 Marketplace for</span>
                <span className="font-heading font-extrabold text-[#2563EB]">
                  {tickerItems[tickerIndex]}
                </span>
              </div>

              {/* Persona Switcher Tabs */}
              <div>
                <div className="inline-flex items-center p-1 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] shadow-xs">
                  <button
                    onClick={() => setRole('hire')}
                    className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                      role === 'hire'
                        ? 'bg-gradient-to-r from-[#2563EB] to-[#3B82F6] text-white shadow-md shadow-blue-500/20'
                        : 'text-[var(--tx-2)] hover:text-[var(--tx)]'
                    }`}
                  >
                    <span>🏢</span> I Want to Hire Talent
                  </button>
                  <button
                    onClick={() => setRole('work')}
                    className={`flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all ${
                      role === 'work'
                        ? 'bg-gradient-to-r from-[#2563EB] to-[#3B82F6] text-white shadow-md shadow-blue-500/20'
                        : 'text-[var(--tx-2)] hover:text-[var(--tx)]'
                    }`}
                  >
                    <span>💼</span> I Want to Find Gigs
                  </button>
                </div>
              </div>

              {/* Headline */}
              <h1 className="text-4xl sm:text-5xl lg:text-[56px] font-extrabold font-heading tracking-tight text-[var(--tx)] leading-[1.12]">
                {role === 'hire' ? (
                  <>
                    Hire Vetted Ghanaian Talent.
                    <br />
                    <span className="bg-gradient-to-r from-[#2563EB] via-[#3B82F6] to-[#0284C7] bg-clip-text text-transparent">
                      100% Escrow Protected.
                    </span>
                  </>
                ) : (
                  <>
                    Turn Your Skills Into Daily Income.
                    <br />
                    <span className="bg-gradient-to-r from-[#009E95] via-[#0DAF80] to-[#2563EB] bg-clip-text text-transparent">
                      Sub-60s Mobile Money Payouts.
                    </span>
                  </>
                )}
              </h1>

              {/* Subtitle */}
              <p className="text-base sm:text-lg text-[var(--tx-2)] leading-relaxed max-w-xl">
                {lang === 'en'
                  ? role === 'hire'
                    ? 'Connect with verified software engineers, carpenters, nurses, graphic designers, electricians, and private chefs across Ghana with 100% money-back escrow protection.'
                    : 'Join thousands of Ghanaian artisans and tech specialists earning daily. Enjoy 3 free job applications, Ghana Card biometric verification, and instant Mobile Money withdrawals.'
                  : 'GigGhana de Ghanafoɔ nyinaa ho adwuma na wɔtua ka pɛ — IT, adwuma, yadeɛ, adesua, ahosiesie ne ebi.'}
              </p>

              {/* Streamlined Super-Search Capsule */}
              <div className="relative max-w-2xl">
                <form
                  onSubmit={(e) => {
                    e.preventDefault();
                    window.location.href = `/search/providers.php?q=${encodeURIComponent(searchQuery)}&category=${encodeURIComponent(selectedCategory)}&region=${encodeURIComponent(selectedRegion)}`;
                  }}
                  className="relative flex flex-col sm:flex-row items-stretch sm:items-center rounded-2xl border border-[var(--border-hi)] bg-[var(--surface)] p-2 shadow-2xl shadow-blue-950/10 focus-within:border-[#2563EB] gap-2 transition-all"
                >
                  <div className="flex items-center flex-1 min-w-0 px-2">
                    <Search className="w-4 h-4 text-[#2563EB] shrink-0 mr-2.5" />
                    <input
                      type="text"
                      value={searchQuery}
                      onChange={(e) => {
                        setSearchQuery(e.target.value);
                        setAutocompleteOpen(true);
                      }}
                      onFocus={() => setAutocompleteOpen(true)}
                      placeholder={
                        role === 'hire'
                          ? 'Search talent: React Developer, Plumber, Nurse...'
                          : 'Search jobs: Mobile App, Electrical, Graphic Design...'
                      }
                      className="w-full bg-transparent py-2 text-xs sm:text-sm text-[var(--tx)] placeholder-[var(--tx-3)] focus:outline-none font-body"
                    />
                  </div>

                  {/* Region Filter */}
                  <select
                    value={selectedRegion}
                    onChange={(e) => setSelectedRegion(e.target.value)}
                    className="bg-[var(--surface-2)] text-xs text-[var(--tx-2)] rounded-xl px-3 py-2 border border-[var(--border)] focus:outline-none font-body cursor-pointer shrink-0"
                  >
                    <option value="">🇬🇭 All Ghana</option>
                    <option value="accra">Accra &amp; Tema</option>
                    <option value="kumasi">Kumasi &amp; Ashanti</option>
                    <option value="takoradi">Takoradi &amp; Western</option>
                    <option value="tamale">Tamale &amp; Northern</option>
                    <option value="remote">Remote / Online</option>
                  </select>

                  {/* Category Filter */}
                  <select
                    value={selectedCategory}
                    onChange={(e) => setSelectedCategory(e.target.value)}
                    className="hidden md:block bg-[var(--surface-2)] text-xs text-[var(--tx-2)] rounded-xl px-3 py-2 border border-[var(--border)] focus:outline-none font-body cursor-pointer shrink-0"
                  >
                    <option value="">All Categories</option>
                    {categories.map((c) => (
                      <option key={c.id} value={c.id}>
                        {c.name}
                      </option>
                    ))}
                  </select>

                  <button
                    type="submit"
                    className="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-gradient-to-r from-[#2563EB] to-[#3B82F6] hover:brightness-110 transition-all shrink-0 font-heading shadow-md shadow-blue-500/25 cursor-pointer"
                  >
                    {role === 'hire' ? 'Find Talent' : 'Search Jobs'}
                  </button>
                </form>

                {/* Autocomplete Dropdown */}
                {autocompleteOpen && filteredSuggestions.length > 0 && (
                  <div className="absolute left-0 right-0 top-full mt-2 rounded-2xl border border-[var(--border-hi)] bg-[var(--surface)] p-2 shadow-2xl z-30 text-left">
                    {filteredSuggestions.map((m, idx) => (
                      <div
                        key={idx}
                        onClick={() => {
                          setSearchQuery(m.text);
                          setAutocompleteOpen(false);
                          window.location.href = `/search/providers.php?q=${encodeURIComponent(m.text)}`;
                        }}
                        className="flex items-center justify-between px-3 py-2 rounded-xl text-xs text-[var(--tx)] hover:bg-[var(--blue-dim)] hover:text-[#2563EB] cursor-pointer transition-colors"
                      >
                        <div className="flex items-center gap-2.5">
                          <span>{m.icon}</span>
                          <span className="font-semibold">{m.text}</span>
                        </div>
                        <span className="text-[10px] text-[var(--tx-3)]">{m.cat}</span>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              {/* Trending Quick Search Badges */}
              <div className="flex flex-wrap items-center gap-2 text-xs text-[var(--tx-3)] pt-1">
                <span className="font-semibold">Trending:</span>
                {trendingSkills.map((ts, idx) => (
                  <a
                    key={idx}
                    href={`/search/providers.php?q=${encodeURIComponent(ts.replace(/^[^\s]+ /, ''))}`}
                    className="px-2.5 py-1 rounded-full bg-[var(--surface-2)] border border-[var(--border)] hover:border-[#2563EB] hover:text-[var(--tx)] transition-all text-[11px]"
                  >
                    {ts}
                  </a>
                ))}
              </div>
            </div>

            {/* Right Column: Interactive Stage Showcase */}
            <div className="lg:col-span-5 relative">
              {role === 'hire' ? (
                /* ── HIRE MODE STAGE ── */
                <div className="relative rounded-3xl border border-[var(--border-hi)] bg-[var(--card)] p-6 sm:p-7 shadow-2xl backdrop-blur-2xl transition-all">
                  {/* Card Top */}
                  <div className="flex items-start justify-between gap-4 pb-5 border-b border-[var(--border)] mb-5">
                    <div className="flex items-center gap-3.5">
                      <div className="relative">
                        <div className="h-14 w-14 rounded-2xl overflow-hidden border border-[var(--border-hi)] shadow-md bg-gradient-to-tr from-[#2563EB] to-[#0284C7] flex items-center justify-center">
                          <img
                            src="https://images.unsplash.com/photo-1522529599102-193c0d76b5b6?w=400&q=80&auto=format"
                            alt="Kwame Asante"
                            className="h-full w-full object-cover"
                          />
                        </div>
                        <span className="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#0DAF80] text-[9px] text-white font-bold ring-2 ring-[var(--card)]">
                          ✓
                        </span>
                      </div>
                      <div>
                        <div className="flex items-center gap-1.5 text-base font-bold text-[var(--tx)] font-heading">
                          <span>Kwame Asante</span>
                          <span className="text-[10px] px-2 py-0.5 rounded-md bg-[#2563EB]/15 text-[#2563EB] font-bold">
                            🇬🇭 NIA Verified
                          </span>
                        </div>
                        <div className="text-xs text-[var(--tx-3)] flex items-center gap-1 mt-0.5">
                          <MapPin className="w-3 h-3 text-[#2563EB]" />
                          <span>Accra Prime, Ghana</span>
                        </div>
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="text-xs text-[var(--tx-3)]">Hourly Rate</div>
                      <div className="text-base font-bold text-[var(--tx)] font-heading">
                        ₵85.00<span className="text-xs text-[var(--tx-3)] font-normal">/hr</span>
                      </div>
                    </div>
                  </div>

                  {/* Card Body: Live Contract Progress */}
                  <div className="p-4 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] space-y-3 mb-5">
                    <div className="flex items-center justify-between text-xs">
                      <span className="text-[var(--tx-2)]">Active Milestone: Custom Web Platform</span>
                      <span className="font-bold text-[#0DAF80] font-mono">₵2,500.00 GHS</span>
                    </div>
                    <div className="w-full bg-[var(--border)] h-2 rounded-full overflow-hidden">
                      <div className="bg-gradient-to-r from-[#2563EB] via-[#3B82F6] to-[#0DAF80] h-full w-[85%]" />
                    </div>
                    <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)]">
                      <span>⚡ 85% Completed</span>
                      <span className="text-[#2563EB] font-semibold">Funds Locked in Vault</span>
                    </div>
                  </div>

                  {/* Rating & Action Buttons */}
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-1.5 text-xs font-bold text-amber-500">
                      <Star className="w-4 h-4 fill-amber-400 text-amber-400" />
                      <span>4.98</span>
                      <span className="text-[var(--tx-3)] font-normal">(58 verified jobs)</span>
                    </div>

                    <a
                      href="/search/providers.php"
                      className="px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#2563EB] to-[#3B82F6] hover:brightness-110 shadow-md shadow-blue-500/20 transition-all font-heading"
                    >
                      Direct Hire
                    </a>
                  </div>
                </div>
              ) : (
                /* ── WORK MODE STAGE ── */
                <div className="relative rounded-3xl border border-[var(--border-hi)] bg-[var(--card)] p-6 sm:p-7 shadow-2xl backdrop-blur-2xl transition-all">
                  {/* Card Top */}
                  <div className="flex items-start justify-between gap-4 pb-5 border-b border-[var(--border)] mb-5">
                    <div>
                      <span className="inline-block text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md bg-[#0DAF80]/15 text-[#0DAF80] mb-1.5">
                        ⚡ 100% Escrow Funded
                      </span>
                      <h3 className="font-heading font-extrabold text-base text-[var(--tx)]">
                        Fintech Mobile App Redesign
                      </h3>
                      <div className="text-xs text-[var(--tx-3)] flex items-center gap-1.5 mt-0.5">
                        <span>GoldCoast Tech Ltd</span>
                        <span>•</span>
                        <span className="text-[#2563EB]">Accra Prime (Remote)</span>
                      </div>
                    </div>
                    <div className="text-right">
                      <div className="text-xs text-[var(--tx-3)]">Fixed Budget</div>
                      <div className="text-base font-bold text-[#0DAF80] font-mono">
                        ₵4,800.00
                      </div>
                    </div>
                  </div>

                  {/* Skill Tags */}
                  <div className="flex flex-wrap gap-1.5 mb-5">
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-[var(--surface-2)] text-[var(--tx-2)] border border-[var(--border)]">
                      React Native
                    </span>
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-[var(--surface-2)] text-[var(--tx-2)] border border-[var(--border)]">
                      Flutter
                    </span>
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-[var(--surface-2)] text-[var(--tx-2)] border border-[var(--border)]">
                      REST API
                    </span>
                    <span className="text-[10px] font-bold px-2 py-0.5 rounded bg-[var(--surface-2)] text-[var(--tx-2)] border border-[var(--border)]">
                      UI/UX
                    </span>
                  </div>

                  {/* Footer Action */}
                  <div className="flex items-center justify-between pt-3 border-t border-[var(--border)]">
                    <div className="text-xs text-[var(--tx-3)]">
                      <span>Posted 12m ago</span> · <span className="text-emerald-500 font-semibold">2 Proposals</span>
                    </div>

                    <a
                      href="/jobs.php"
                      className="px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#009E95] to-[#0DAF80] hover:brightness-110 shadow-md shadow-emerald-500/20 transition-all font-heading"
                    >
                      Apply with 1 Click
                    </a>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </section>



      {/* ══════ TOP TALENT EXPLORER ══════ */}
      <section className="py-24 border-t border-[var(--border)] bg-[var(--bg-subtle)]" id="talent">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16">
            <div>
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--blue-dim)] text-[#3B82F6] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-widest font-heading mb-3">
                <Users className="w-3.5 h-3.5" />
                <span>TOP PERFORMING TALENT</span>
              </div>
              <h2 className="text-3xl sm:text-5xl font-extrabold font-heading tracking-tight text-[var(--tx)]">
                Featured Verified Freelancers
              </h2>
            </div>
            <a
              href="/search/providers.php"
              className="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#3B82F6] hover:underline"
            >
              <span>View All Freelancers</span>
              <ArrowRight className="w-4 h-4" />
            </a>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {(featured.length > 0 ? featured : matchedProviders).slice(0, 6).map((pv, idx) => {
              const skills = pv.skill_names ? pv.skill_names.split('|').filter(Boolean) : [];
              const init = initials(pv.first_name, pv.last_name);
              const jobs = Number(pv.completed_jobs || 0);
              const rating = Number(pv.rating_avg || 5);
              const rk = rankLabel(jobs);

              return (
                <SpotlightCard
                  key={idx}
                  spotlightColor="rgba(59, 130, 246, 0.18)"
                  className="p-8 flex flex-col justify-between"
                >
                  <div>
                    <div className="flex items-start justify-between gap-4 mb-6">
                      <div className="flex items-center gap-3.5">
                        <div className="relative">
                          {pv.avatar ? (
                            <img
                              src={pv.avatar}
                              alt={pv.first_name}
                              className="h-14 w-14 rounded-2xl object-cover border border-[var(--border-hi)] shadow-md"
                            />
                          ) : (
                            <div className="h-14 w-14 rounded-2xl bg-gradient-to-tr from-[#2563EB] to-[#06B6D4] flex items-center justify-center font-heading font-extrabold text-base text-white">
                              {init}
                            </div>
                          )}
                          {pv.is_verified ? (
                            <span className="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#10B981] text-[9px] text-white font-bold">
                              ✓
                            </span>
                          ) : null}
                        </div>
                        <div>
                          <div className="text-lg font-bold text-[var(--tx)] font-heading">
                            {pv.first_name} {pv.last_name}
                          </div>
                          <div className="text-xs text-[var(--tx-3)] flex items-center gap-1">
                            <MapPin className="w-3 h-3 text-[#3B82F6]" />
                            <span>{pv.location || 'Accra, Ghana'}</span>
                          </div>
                        </div>
                      </div>

                      <span className={`px-2.5 py-1 rounded-full text-[10px] font-bold font-mono border ${rk.c}`}>
                        {rk.i} {rk.l}
                      </span>
                    </div>

                    <p className="text-xs text-[var(--tx-2)] line-clamp-2 mb-4 leading-relaxed font-body">
                      {pv.tagline || `${pv.experience_level ? pv.experience_level.charAt(0).toUpperCase() + pv.experience_level.slice(1) : 'Professional'} freelancer ready for high-impact gigs.`}
                    </p>

                    <div className="flex items-center gap-1.5 text-xs font-bold text-amber-400 mb-4">
                      <Star className="w-3.5 h-3.5 fill-amber-400" />
                      <span>{rating.toFixed(1)}</span>
                      <span className="text-[var(--tx-3)] font-normal">({Number(pv.rating_count || 0)} reviews)</span>
                      <span className="text-[var(--border)] mx-1">·</span>
                      <span className="text-[var(--tx-3)] font-normal">{jobs} jobs delivered</span>
                    </div>

                    <div className="flex flex-wrap gap-1.5 mb-6">
                      {skills.slice(0, 3).map((sk: string, sIdx: number) => (
                        <span
                          key={sIdx}
                          className="px-2.5 py-1 rounded-lg bg-[var(--surface-2)] border border-[var(--border)] text-[10px] font-semibold text-[var(--tx-2)]"
                        >
                          {sk}
                        </span>
                      ))}
                    </div>
                  </div>

                  <div className="pt-6 border-t border-[var(--border)] flex items-center justify-between">
                    <div>
                      <div className="text-[10px] uppercase tracking-wider text-[var(--tx-3)]">Hourly Rate</div>
                      <div className="text-base font-bold text-[var(--tx)] font-heading">
                        {pv.hourly_rate > 0 ? (
                          <>
                            {formatCurrency(pv.hourly_rate)}
                            <span className="text-xs text-[var(--tx-3)] font-normal">/hr</span>
                          </>
                        ) : (
                          'Negotiable'
                        )}
                      </div>
                    </div>

                    <a
                      href={`/profile.php?id=${pv.user_id}`}
                      className="px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-[#2563EB] to-[#3B82F6] hover:brightness-110 shadow-md shadow-blue-500/20 transition-all font-heading"
                    >
                      Invite
                    </a>
                  </div>
                </SpotlightCard>
              );
            })}
          </div>
        </div>
      </section>

      {/* ══════ CATEGORIES HUB ══════ */}
      <section className="py-24 relative border-t border-[var(--border)]" id="categories">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--blue-dim)] text-[#3B82F6] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-widest font-heading mb-3">
              <span>EVERY SKILL COVERED</span>
            </div>
            <h2 className="text-3xl sm:text-5xl font-extrabold font-heading tracking-tight text-[var(--tx)]">
              Explore All Categories
            </h2>
            <p className="text-sm text-[var(--tx-2)] mt-3">
              From software engineering to skilled physical trades, hire vetted professionals in every region.
            </p>
          </div>

          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            {categories.map((cat) => (
              <SpotlightCard
                key={cat.id}
                spotlightColor="rgba(59, 130, 246, 0.16)"
                className="p-5 flex flex-col justify-between hover:translate-y-[-3px] transition-transform"
              >
                <a
                  href={`/search/providers.php?category=${cat.id}`}
                  className="flex flex-col h-full justify-between"
                >
                  <div>
                    <div className="text-3xl mb-3">{iconMap[cat.icon] || '🔧'}</div>
                    <div className="text-sm font-bold text-[var(--tx)] font-heading mb-1">{cat.name}</div>
                    {cat.description && (
                      <p className="text-[11px] text-[var(--tx-3)] line-clamp-2 leading-relaxed">
                        {cat.description}
                      </p>
                    )}
                  </div>
                  <div className="mt-4 flex items-center gap-1 text-[11px] font-bold text-[#3B82F6]">
                    <span>Explore</span>
                    <ArrowRight className="w-3 h-3" />
                  </div>
                </a>
              </SpotlightCard>
            ))}
          </div>
        </div>
      </section>

      {/* ══════ RECENT JOBS ══════ */}
      <section className="py-24 relative border-t border-[var(--border)] bg-[var(--bg-subtle)]" id="jobs">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16">
            <div>
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--blue-dim)] text-[#3B82F6] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-widest font-heading mb-3">
                <Briefcase className="w-3.5 h-3.5" />
                <span>FRESH OPPORTUNITIES</span>
              </div>
              <h2 className="text-3xl sm:text-5xl font-extrabold font-heading tracking-tight text-[var(--tx)]">
                Recently Posted Jobs in Ghana
              </h2>
            </div>
            <a
              href="/jobs.php"
              className="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#3B82F6] hover:underline"
            >
              <span>View All Open Jobs</span>
              <ArrowRight className="w-4 h-4" />
            </a>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {recentJobs.slice(0, 6).map((job) => (
              <SpotlightCard
                key={job.id}
                spotlightColor="rgba(59, 130, 246, 0.16)"
                className="p-8 flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-start justify-between gap-3 mb-4">
                    <span className="text-xs font-bold text-[#3B82F6] flex items-center gap-1.5">
                      <span>{iconMap[job.cat_icon] || '📂'}</span>
                      <span>{job.cat_name || 'General'}</span>
                    </span>
                    <span
                      className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
                        job.is_urgent
                          ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30'
                          : 'bg-[#10B981]/15 text-[#10B981] border border-[#10B981]/30'
                      }`}
                    >
                      {job.is_urgent ? '🔥 Urgent' : '● Open'}
                    </span>
                  </div>

                  <h3 className="text-lg font-bold text-[var(--tx)] font-heading mb-2 line-clamp-2 leading-snug">
                    {job.title}
                  </h3>

                  <p className="text-xs text-[var(--tx-2)] line-clamp-3 mb-6 leading-relaxed">
                    {job.description}
                  </p>

                  <div className="flex items-center gap-4 text-xs text-[var(--tx-3)] mb-6 font-mono">
                    <span className="flex items-center gap-1">
                      <Clock className="w-3 h-3 text-[#3B82F6]" />
                      <span>{timeAgo(job.created_at)}</span>
                    </span>
                    <span>{Number(job.proposal_count || 0)} proposals</span>
                  </div>
                </div>

                <div className="pt-6 border-t border-[var(--border)] flex items-center justify-between">
                  <div>
                    <div className="text-[10px] uppercase tracking-wider text-[var(--tx-3)]">Budget</div>
                    <div className="text-base font-bold text-[#10B981] font-heading">
                      {formatCurrency(job.budget_min)}
                      {job.budget_max > job.budget_min ? ` - ${formatCurrency(job.budget_max)}` : ''}
                    </div>
                  </div>

                  <a
                    href={`/job-details.php?id=${job.id}`}
                    className="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-[var(--tx)] bg-[var(--surface-2)] border border-[var(--border)] hover:border-[#3B82F6] hover:text-[#3B82F6] transition-colors"
                  >
                    Apply Now
                  </a>
                </div>
              </SpotlightCard>
            ))}
          </div>
        </div>
      </section>

      {/* ══════ THE BENTO GRID ("WHY GIGHANA") ══════ */}
      <section className="py-24 relative border-t border-[var(--border)]" id="how">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--blue-dim)] text-[#3B82F6] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-wider font-heading mb-3">
              <Sparkles className="w-3.5 h-3.5" />
              <span>THE GIGHANA ADVANTAGE</span>
            </div>
            <h2 className="text-3xl sm:text-5xl font-extrabold font-heading tracking-tight text-[var(--tx)]">
              Built for Trust, Speed, and Safe Commerce
            </h2>
            <p className="text-sm text-[var(--tx-2)] mt-3">
              Eliminating financial uncertainty for businesses and independent talent across Ghana.
            </p>
          </div>

          <BentoGrid>
            <BentoCard
              title="100% Guaranteed Escrow Protection"
              description="Clients deposit funds into the GigGhana Escrow Vault before project kick-off. Funds are released only after deliverables are inspected and approved."
              icon={<ShieldCheck className="w-6 h-6 text-[#3B82F6]" />}
              badge="Zero Scam Policy"
              spotlightColor="rgba(59, 130, 246, 0.2)"
              className="md:col-span-2"
              header={
                <div className="p-4 rounded-2xl bg-[var(--surface-2)] border border-[var(--border-hi)] flex items-center justify-between text-xs">
                  <span className="font-bold text-[#3B82F6] flex items-center gap-1.5">
                    <Lock className="w-4 h-4" /> Vault Active &amp; Insured
                  </span>
                  <span className="text-[var(--emerald)] font-bold">100% Refundable</span>
                </div>
              }
            />

            <BentoCard
              title="Ghana Card Biometric Authentication"
              description="Every freelancer identity is validated against official National Identification Authority (NIA) database standards, ensuring 100% authentic identities."
              icon={<Award className="w-6 h-6 text-[#10B981]" />}
              badge="NIA Biometric Trust"
              spotlightColor="rgba(16, 185, 129, 0.2)"
              header={
                <div className="w-full flex items-center justify-center p-1">
                  <GhanaCard />
                </div>
              }
            />

            <BentoCard
              title="Sub-Minute Mobile Money Settlement"
              description="Direct integration with MTN MoMo, Telecel Cash, AT Money, and domestic banks ensures fast, reliable withdrawals in < 60 seconds."
              icon={<Smartphone className="w-6 h-6 text-amber-400" />}
              badge="Instant Settlement"
              spotlightColor="rgba(245, 158, 11, 0.2)"
            />

            <BentoCard
              title="3 Free Jobs + Flexible Badge Tiers"
              description="Every provider starts free with 3 job applications. Upgrade to Verified (₵49/mo) or Premium (₵99/mo) anytime for top search placement."
              icon={<Zap className="w-6 h-6 text-[#8B5CF6]" />}
              badge="Transparent Dues"
              spotlightColor="rgba(139, 92, 246, 0.2)"
              className="md:col-span-2"
              header={
                <div className="grid grid-cols-3 gap-2 p-3 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] text-center text-xs">
                  <div className="p-2 rounded-xl bg-[var(--surface)]">
                    <div className="font-bold text-[var(--tx)]">Beginner</div>
                    <div className="text-[10px] text-[var(--tx-3)]">Free · 3 Gigs</div>
                  </div>
                  <div className="p-2 rounded-xl bg-[var(--blue-dim)] border border-[var(--border-hi)]">
                    <div className="font-bold text-[#3B82F6]">Verified ✓</div>
                    <div className="text-[10px] text-[#3B82F6]">₵49/mo</div>
                  </div>
                  <div className="p-2 rounded-xl bg-[var(--surface)]">
                    <div className="font-bold text-[var(--tx)]">Premium ⭐</div>
                    <div className="text-[10px] text-[var(--tx-3)]">₵99/mo</div>
                  </div>
                </div>
              }
            />
          </BentoGrid>
        </div>
      </section>

      {/* ══════ INTERACTIVE EARNINGS ESTIMATOR ══════ */}
      <section className="py-20 relative border-t border-[var(--border)]" id="calculator">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="rounded-3xl border border-[var(--border-hi)] bg-[var(--card)] p-8 sm:p-12 shadow-2xl backdrop-blur-2xl">
            <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8 border-b border-[var(--border)] mb-8">
              <div>
                <div className="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#3B82F6] font-heading mb-2">
                  <Calculator className="w-4 h-4" />
                  <span>FREELANCE INCOME ESTIMATOR</span>
                </div>
                <h3 className="text-3xl sm:text-4xl font-extrabold font-heading text-[var(--tx)] tracking-tight">
                  Calculate Your Monthly Earning Potential
                </h3>
              </div>
              <div className="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[var(--blue-dim)] text-[#3B82F6] border border-[var(--border-hi)] text-xs font-bold w-fit">
                <TrendingUp className="w-4 h-4" />
                <span>Instant MoMo Payouts</span>
              </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
              <div className="lg:col-span-7 space-y-6">
                <div>
                  <label className="block text-xs font-bold uppercase tracking-wider text-[var(--tx-3)] mb-3">
                    1. Select Your Profession:
                  </label>
                  <div className="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    {disciplines.map((d, idx) => (
                      <button
                        key={idx}
                        onClick={() => setCalcIndex(idx)}
                        className={`p-3.5 rounded-2xl border text-left transition-all ${
                          calcIndex === idx
                            ? 'bg-[var(--blue-dim)] border-[#3B82F6] text-[var(--tx)] shadow-md shadow-blue-500/15'
                            : 'bg-[var(--surface-2)] border-[var(--border)] text-[var(--tx-2)] hover:border-[var(--border-hi)] hover:text-[var(--tx)]'
                        }`}
                      >
                        <div className="text-xl mb-1">{d.icon}</div>
                        <div className="text-xs font-bold leading-tight line-clamp-1">{d.name}</div>
                        <div className="text-[10px] text-[#3B82F6] mt-1 font-mono">₵{d.hourly}/hr avg</div>
                      </button>
                    ))}
                  </div>
                </div>

                <div>
                  <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)] mb-2">
                    <span>2. Weekly Hours:</span>
                    <span className="text-sm font-bold text-[#3B82F6] font-mono">{calcHours} Hours / Week</span>
                  </div>
                  <input
                    type="range"
                    min="5"
                    max="50"
                    step="5"
                    value={calcHours}
                    onChange={(e) => setCalcHours(Number(e.target.value))}
                    className="w-full h-2 bg-[var(--border)] rounded-lg appearance-none cursor-pointer accent-[#2563EB]"
                  />
                  <div className="flex justify-between text-[10px] text-[var(--tx-3)] mt-1.5 font-mono">
                    <span>5 hrs (Side Hustle)</span>
                    <span>25 hrs (Part-Time)</span>
                    <span>45+ hrs (Full-Time)</span>
                  </div>
                </div>
              </div>

              {/* Calculated Result */}
              <div className="lg:col-span-5 p-7 rounded-3xl bg-[var(--surface-2)] border border-[var(--border-hi)] text-center flex flex-col justify-between shadow-xl">
                <div>
                  <div className="text-xs font-bold uppercase tracking-widest text-[var(--tx-3)] mb-1">
                    Estimated Monthly Income
                  </div>
                  <div className="text-4xl sm:text-5xl font-extrabold text-[var(--tx)] my-2">
                    <span className="text-blue-gradient">₵{estMonthly.toLocaleString('en-GH')}</span>
                  </div>
                  <div className="text-xs text-[var(--tx-2)] mb-6">
                    Approx. <strong className="text-[var(--tx)]">₵{(estMonthly * 12).toLocaleString('en-GH')} GHS</strong> annually
                  </div>

                  <div className="p-3.5 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-left text-xs space-y-2 mb-6">
                    <div className="flex items-center justify-between text-[var(--tx-2)]">
                      <span>Hourly rate benchmark:</span>
                      <span className="font-bold text-[var(--tx)] font-mono">₵{curDiscipline.hourly}.00 / hr</span>
                    </div>
                    <div className="flex items-center justify-between text-[var(--tx-2)]">
                      <span>Escrow protection:</span>
                      <span className="font-bold text-[#10B981]">100% Guaranteed</span>
                    </div>
                  </div>
                </div>

                <a
                  href="/auth/register.php?role=provider"
                  onClick={triggerConfetti}
                  className="flex items-center justify-center gap-2 w-full py-4 px-6 rounded-2xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#2563EB] to-[#3B82F6] hover:brightness-110 shadow-lg shadow-blue-500/25 transition-all font-heading"
                >
                  <Sparkles className="w-4 h-4" />
                  <span>Start Earning on GigGhana</span>
                  <ArrowRight className="w-4 h-4" />
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ══════ TESTIMONIALS (WALL OF TRUST) ══════ */}
      <section className="py-24 relative border-t border-[var(--border)]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--blue-dim)] text-[#3B82F6] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-widest font-heading mb-3">
              <Star className="w-3.5 h-3.5 fill-[#3B82F6]" />
              <span>TESTIMONIALS</span>
            </div>
            <h2 className="text-3xl sm:text-5xl font-extrabold font-heading tracking-tight text-[var(--tx)]">
              Ghanaians Winning Every Day
            </h2>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {reviews.map((rv, idx) => (
              <SpotlightCard
                key={idx}
                spotlightColor="rgba(59, 130, 246, 0.14)"
                className="p-7 flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center gap-1 text-amber-400 mb-4">
                    {[1, 2, 3, 4, 5].map((s) => (
                      <Star key={s} className="w-3.5 h-3.5 fill-amber-400" />
                    ))}
                  </div>
                  <p className="text-xs text-[var(--tx)] italic leading-relaxed mb-6">
                    &ldquo;{rv.comment}&rdquo;
                  </p>
                </div>

                <div className="flex items-center gap-3 pt-4 border-t border-[var(--border)]">
                  <div className="h-9 w-9 rounded-full bg-gradient-to-tr from-[#2563EB] to-[#06B6D4] flex items-center justify-center font-heading font-bold text-xs text-white">
                    {initials(rv.first_name, rv.last_name)}
                  </div>
                  <div>
                    <div className="text-xs font-bold text-[var(--tx)]">
                      {rv.first_name} {rv.last_name}
                    </div>
                    <div className="text-[10px] text-[var(--tx-3)]">
                      {rv.role ? rv.role.charAt(0).toUpperCase() + rv.role.slice(1) : 'Member'} · {rv.location || 'Accra'}
                    </div>
                  </div>
                </div>
              </SpotlightCard>
            ))}
          </div>
        </div>
      </section>

      {/* ══════ PARTNER MARQUEE ══════ */}
      <div className="py-12 border-t border-[var(--border)] bg-[var(--bg-subtle)]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center text-xs font-bold uppercase tracking-widest text-[var(--tx-3)] mb-8 font-heading">
            Trusted Payment Gateways &amp; Technology Partners
          </div>

          <Marquee pauseOnHover className="[--duration:32s] py-2">
            {/* MTN Mobile Money */}
            <div className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-xs font-bold text-[var(--tx)] hover:border-[#FFCC00]/50 hover:shadow-lg transition-all">
              <div className="h-7 px-2.5 rounded-lg bg-[#FFCC00] flex items-center justify-center font-heading font-black text-[11px] text-black tracking-tighter">
                <span className="border-2 border-black rounded-full px-1.5 py-0.2 mr-1">MTN</span>
                <span>MoMo</span>
              </div>
              <span className="font-heading">MTN Mobile Money</span>
            </div>

            {/* Telecel Cash */}
            <div className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-xs font-bold text-[var(--tx)] hover:border-[#E60000]/50 hover:shadow-lg transition-all">
              <div className="h-7 w-7 rounded-full bg-[#E60000] flex items-center justify-center text-white font-extrabold text-xs shadow-sm">
                t
              </div>
              <span className="font-heading">Telecel Cash</span>
            </div>

            {/* AT Money (AirtelTigo) */}
            <div className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-xs font-bold text-[var(--tx)] hover:border-[#0085CA]/50 hover:shadow-lg transition-all">
              <div className="h-7 px-2 rounded-lg bg-[#001D4A] border border-[#0085CA]/40 flex items-center justify-center text-[#00A3E0] font-heading font-black text-xs">
                AT<span className="text-[#FF4A5A] ml-0.5">·</span>
              </div>
              <span className="font-heading">AT Money</span>
            </div>

            {/* Paystack */}
            <div className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-xs font-bold text-[var(--tx)] hover:border-[#0BA4DB]/50 hover:shadow-lg transition-all">
              <svg className="h-5 w-5 text-[#0BA4DB]" viewBox="0 0 24 24" fill="currentColor">
                <path d="M2 4h20v3H2V4zm0 6h14v3H2v-3zm0 6h20v3H2v-3z" />
              </svg>
              <span className="font-heading">Paystack</span>
            </div>

            {/* Flutterwave */}
            <div className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-xs font-bold text-[var(--tx)] hover:border-[#F5A623]/50 hover:shadow-lg transition-all">
              <svg className="h-6 w-6" viewBox="0 0 100 100" fill="none">
                <path d="M20 30C20 30 35 15 50 30C65 45 80 30 80 30" stroke="#F5A623" strokeWidth="12" strokeLinecap="round"/>
                <path d="M20 50C20 50 35 35 50 50C65 65 80 50 80 50" stroke="#F76B1C" strokeWidth="12" strokeLinecap="round"/>
                <path d="M20 70C20 70 35 55 50 70C65 85 80 70 80 70" stroke="#007AFF" strokeWidth="12" strokeLinecap="round"/>
              </svg>
              <span className="font-heading">Flutterwave</span>
            </div>

            {/* Hubtel */}
            <div className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-xs font-bold text-[var(--tx)] hover:border-[#00A3E0]/50 hover:shadow-lg transition-all">
              <div className="h-7 w-7 rounded-xl bg-[#00A3E0] flex items-center justify-center text-white font-black text-xs shadow-sm">
                H
              </div>
              <span className="font-heading">Hubtel</span>
            </div>

            {/* Visa */}
            <div className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-xs font-bold text-[var(--tx)] hover:border-[#1434CB]/50 hover:shadow-lg transition-all">
              <svg className="h-5 w-14" viewBox="0 0 100 32" fill="none">
                <path d="M37.5 2.5L25.3 29.5H17.4L10.6 8.3C10.2 6.6 9.8 6 8.5 5.3C6.3 4.1 3 3.1 0 2.5L0.2 1.5H13.6C15.3 1.5 16.9 2.7 17.2 4.6L20.5 21.8L28.6 1.5H37.5ZM70.4 20.3C70.5 12.6 59.7 12.1 59.8 8.6C59.8 7.6 60.8 6.4 63.2 6.1C64.4 6 67.7 5.8 71.3 7.5L72.7 1.4C70.8 0.7 68.3 0 65.2 0C57.4 0 51.8 4.2 51.8 10.2C51.7 14.6 55.7 17.1 58.7 18.6C61.8 20.1 62.8 21.1 62.8 22.5C62.7 24.6 60.2 25.5 57.9 25.5C53.8 25.6 51.5 24.4 49.6 23.5L48.1 29.8C50.1 30.7 53.7 31.5 57.4 31.5C65.7 31.5 70.4 27.4 70.4 20.3ZM91.4 29.5H98.4L92.3 1.5H85.9C84.3 1.5 83 2.4 82.4 3.9L70.4 29.5H78.4L80 25.1H89.8L91.4 29.5ZM82.2 19.3L86.2 8.3L88.5 19.3H82.2ZM49.8 1.5L43.6 29.5H36L42.2 1.5H49.8Z" fill="#1434CB"/>
              </svg>
              <span className="font-heading">Visa Card</span>
            </div>

            {/* Mastercard */}
            <div className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-xs font-bold text-[var(--tx)] hover:border-[#EB001B]/50 hover:shadow-lg transition-all">
              <svg className="h-6 w-9" viewBox="0 0 100 62" fill="none">
                <circle cx="34" cy="31" r="30" fill="#EB001B"/>
                <circle cx="66" cy="31" r="30" fill="#F79E1B"/>
                <path d="M50 10.5C57.1 16.1 61.5 24.8 61.5 31C61.5 37.2 57.1 45.9 50 51.5C42.9 45.9 38.5 37.2 38.5 31C38.5 24.8 42.9 16.1 50 10.5Z" fill="#FF5F00"/>
              </svg>
              <span className="font-heading">Mastercard</span>
            </div>

            {/* GhIPSS / GhQR */}
            <div className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-xs font-bold text-[var(--tx)] hover:border-[#006B3F]/50 hover:shadow-lg transition-all">
              <div className="h-7 px-2 rounded-lg bg-[#006B3F] text-white flex items-center justify-center font-heading font-black text-[11px] tracking-tight">
                Gh<span className="text-[#FCD116]">IP</span><span className="text-[#CE1126]">SS</span>
              </div>
              <span className="font-heading">GhQR / Bank Settlement</span>
            </div>

            {/* Smart Escrow Sanctuary */}
            <div className="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[var(--surface)] border border-[var(--border-hi)] text-xs font-bold text-[#3B82F6] shadow-sm">
              <ShieldCheck className="w-5 h-5 text-[#3B82F6]" />
              <span className="font-heading">Smart Escrow Vault</span>
            </div>
          </Marquee>
        </div>
      </div>

      {/* ══════ HIGH-CONVERTING CTA BANNER ══════ */}
      <section className="py-24 border-t border-[var(--border)] relative overflow-hidden">
        <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="relative rounded-3xl border border-[var(--border-hi)] bg-gradient-to-br from-[#2563EB]/20 via-[#3B82F6]/10 to-[#06B6D4]/20 p-12 sm:p-20 text-center shadow-2xl backdrop-blur-2xl">
            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[var(--blue-dim)] border border-[var(--border-hi)] text-xs font-bold text-[#3B82F6] mb-6">
              <Sparkles className="w-4 h-4" />
              <span>Join Africa&apos;s Premier Talent Marketplace</span>
            </div>

            <h2 className="text-4xl sm:text-6xl font-extrabold font-heading text-[var(--tx)] tracking-tight max-w-2xl mx-auto leading-tight mb-6">
              Start Hiring or Earning in Ghana Today
            </h2>

            <p className="text-sm sm:text-base text-[var(--tx-2)] max-w-xl mx-auto mb-10 leading-relaxed">
              Create your account in under 2 minutes. 3 free job applications for all freelancers. 100% money-back escrow guarantee.
            </p>

            <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
              <a
                href="/auth/register.php?role=provider"
                onClick={triggerConfetti}
                className="w-full sm:w-auto px-8 py-4 rounded-2xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#2563EB] to-[#3B82F6] hover:brightness-110 shadow-xl shadow-blue-500/30 transition-all font-heading"
              >
                Sign Up as a Freelancer
              </a>

              <a
                href="/auth/register.php?role=client"
                onClick={triggerConfetti}
                className="w-full sm:w-auto px-8 py-4 rounded-2xl font-bold text-xs uppercase tracking-wider text-[var(--tx)] bg-[var(--surface-2)] border border-[var(--border-hi)] hover:bg-[var(--blue-dim)] transition-all font-heading"
              >
                Post a Job for Free
              </a>
            </div>
          </div>
        </div>
      </section>

      {/* ══════ MODERN FOOTER ══════ */}
      <footer className="border-t border-[var(--border)] bg-[var(--bg)] pt-16 pb-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-5 gap-10 pb-12 border-b border-[var(--border)]">
            <div className="md:col-span-2 space-y-4">
              <a href="/" className="flex items-center gap-2.5">
                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-[#2563EB] to-[#06B6D4] font-heading font-extrabold text-xs text-white">
                  G
                </div>
                <span className="font-heading font-bold text-lg text-[var(--tx)]">
                  Gig<span className="text-[#3B82F6]">Ghana</span>
                </span>
              </a>
              <p className="text-xs text-[var(--tx-2)] leading-relaxed max-w-sm">
                Ghana&apos;s premier freelance marketplace connecting verified talent across IT, trades, creative arts, health, and hospitality with forward-thinking businesses.
              </p>

              {/* Newsletter */}
              <form onSubmit={handleSubscribeNL} className="pt-2 flex max-w-sm gap-2">
                <input
                  type="email"
                  placeholder="Enter your email"
                  value={nlEmail}
                  onChange={(e) => setNlEmail(e.target.value)}
                  className="w-full px-4 py-2 rounded-xl bg-[var(--surface-2)] border border-[var(--border)] text-xs text-[var(--tx)] placeholder-[var(--tx-3)] focus:outline-none focus:border-[#3B82F6]"
                />
                <button
                  type="submit"
                  className="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-[#2563EB] hover:bg-[#1D4ED8] transition-colors shrink-0"
                >
                  Subscribe
                </button>
              </form>
            </div>

            <div>
              <div className="text-xs font-bold uppercase tracking-widest text-[#3B82F6] font-heading mb-4">
                Platform
              </div>
              <ul className="space-y-2.5 text-xs text-[var(--tx-2)]">
                <li><a href="/search/providers.php" className="hover:text-[#3B82F6] transition-colors">Find Talent</a></li>
                <li><a href="/jobs.php" className="hover:text-[#3B82F6] transition-colors">Browse Open Jobs</a></li>
                <li><a href="/auth/register.php" className="hover:text-[#3B82F6] transition-colors">Post a Job</a></li>
                <li><a href="#calculator" className="hover:text-[#3B82F6] transition-colors">Income Estimator</a></li>
              </ul>
            </div>

            <div>
              <div className="text-xs font-bold uppercase tracking-widest text-[#3B82F6] font-heading mb-4">
                Company
              </div>
              <ul className="space-y-2.5 text-xs text-[var(--tx-2)]">
                <li><a href="#" className="hover:text-[#3B82F6] transition-colors">About GigGhana</a></li>
                <li><a href="#how" className="hover:text-[#3B82F6] transition-colors">Escrow Security</a></li>
                <li><a href="#" className="hover:text-[#3B82F6] transition-colors">Badge Tiers</a></li>
                <li><a href="#" className="hover:text-[#3B82F6] transition-colors">Careers</a></li>
              </ul>
            </div>

            <div>
              <div className="text-xs font-bold uppercase tracking-widest text-[#3B82F6] font-heading mb-4">
                Support &amp; Trust
              </div>
              <ul className="space-y-2.5 text-xs text-[var(--tx-2)]">
                <li><a href="/privacy.php" className="hover:text-[#3B82F6] transition-colors">Privacy Policy</a></li>
                <li><a href="/terms.php" className="hover:text-[#3B82F6] transition-colors">Terms of Service</a></li>
                <li><a href="#" className="hover:text-[#3B82F6] transition-colors">Dispute Resolution</a></li>
                <li><a href="#" className="hover:text-[#3B82F6] transition-colors">Ghana Registry</a></li>
              </ul>
            </div>
          </div>

          <div className="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[var(--tx-3)]">
            <div>
              © {new Date().getFullYear()} GigGhana Ltd. Made with ❤️ in Ghana 🇬🇭
            </div>
            <div className="flex items-center gap-6 font-mono text-[11px]">
              <span>🔒 256-bit SSL Vault</span>
              <span>🇬🇭 Ghana Biometrics</span>
              <span>⚡ Next.js 15 Engine</span>
            </div>
          </div>
        </div>
      </footer>

      {/* Toast Notifications Container */}
      <div id="toast-c">
        {toasts.map((t) => (
          <div key={t.id} className={`toast ${t.type}`}>
            <div className="t-ico">
              {t.type === 'success' ? '✅' : t.type === 'error' ? '❌' : t.type === 'warning' ? '⚠️' : 'ℹ️'}
            </div>
            <div className="t-bod">
              <div className="t-ttl font-bold">{t.title}</div>
              <div className="t-msg text-xs">{t.msg}</div>
            </div>
            <div
              className="t-cls cursor-pointer"
              onClick={() => setToasts((prev) => prev.filter((item) => item.id !== t.id))}
            >
              ×
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
