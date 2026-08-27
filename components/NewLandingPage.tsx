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
import { AuroraBackground } from './ui/aurora-background';
import { RoleSwitcher } from './ui/role-switcher';
import { TransactionVisualizerCard } from './ui/transaction-card';
import { EarningsCalculator } from './ui/earnings-calculator';
import { SpotlightCard } from './ui/spotlight-card';
import { BentoGrid, BentoCard } from './ui/bento-grid';
import { Marquee } from './ui/marquee';
import { FloatingDock } from './ui/floating-dock';
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
  '💻 Web Developer',
  '🎨 UI/UX Designer',
  '🔧 Electrician',
  '🏥 Home Nurse',
  '🍽️ Private Chef',
  '📈 Digital Marketer',
  '🪚 Carpenter',
];

function rankLabel(jobs: number) {
  if (jobs >= 50) return { i: '🏆', l: 'Elite Expert', c: 'text-amber-400 bg-amber-500/10 border-amber-500/20' };
  if (jobs >= 20) return { i: '⭐', l: 'Top Rated', c: 'text-cyan-400 bg-cyan-500/10 border-cyan-500/20' };
  if (jobs >= 5) return { i: '📈', l: 'Rising Talent', c: 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' };
  return { i: '🌱', l: 'New Freelancer', c: 'text-white/60 bg-white/5 border-white/10' };
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

export default function NewLandingPage({ initialData }: Props) {
  const { stats, categories, featured, matchedProviders, recentJobs, liveJobs, earningsData, earningsTotal, reviews } = initialData;

  // Perspective mode: 'hire' (for clients) vs 'work' (for freelancers)
  const [role, setRole] = useState<'hire' | 'work'>('hire');

  // Language state
  const [lang, setLang] = useState<'en' | 'tw'>('en');

  // Theme state
  const [isLight, setIsLight] = useState(false);

  // Command palette state
  const [isCommandOpen, setIsCommandOpen] = useState(false);

  // Mobile menu state
  const [isMobOpen, setIsMobOpen] = useState(false);

  // Search input & autocomplete
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');
  const [autocompleteOpen, setAutocompleteOpen] = useState(false);

  // Filtered talent tab
  const [talentFilter, setTalentFilter] = useState('all');

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
        particleCount: 90,
        spread: 70,
        origin: { y: 0.7 },
        colors: ['#06B6D4', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899'],
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
        label: 'Escrow Volume Released (₵)',
        data: earningsData,
        borderColor: '#06B6D4',
        backgroundColor: 'rgba(6, 182, 212, 0.08)',
        borderWidth: 3,
        pointBackgroundColor: '#06B6D4',
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
        backgroundColor: '#0C0E14',
        titleColor: '#F2F4F8',
        bodyColor: '#94A3B8',
        borderColor: 'rgba(6, 182, 212, 0.3)',
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
        grid: { color: 'rgba(255, 255, 255, 0.05)' },
        ticks: { color: '#64748B', font: { size: 11, family: 'DM Sans' } },
      },
      y: {
        grid: { color: 'rgba(255, 255, 255, 0.05)' },
        beginAtZero: true,
        ticks: {
          color: '#64748B',
          font: { size: 11, family: 'DM Sans' },
          callback: (v: any) => '₵' + Number(v).toLocaleString(),
        },
      },
    },
  };

  return (
    <div className="min-h-screen bg-[#07090E] text-[#F2F4F8] selection:bg-[#06B6D4] selection:text-[#07090E]">
      {/* ══════ COMMAND SEARCH MODAL (Cmd + K) ══════ */}
      <CommandSearchDialog
        open={isCommandOpen}
        onOpenChange={setIsCommandOpen}
        categories={categories}
      />

      {/* ══════ FLOATING QUICK DOCK ISLAND ══════ */}
      <FloatingDock onOpenSearch={() => setIsCommandOpen(true)} />

      {/* ══════ MODERN NAVBAR ══════ */}
      <header
        className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
          scrolledNav
            ? 'bg-[#07090E]/80 backdrop-blur-2xl border-b border-white/10 py-3.5 shadow-2xl shadow-black/50'
            : 'bg-transparent py-5'
        }`}
      >
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
          <a href="/" className="flex items-center gap-2.5 group">
            <div className="relative flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-tr from-[#06B6D4] via-[#10B981] to-[#F59E0B] p-[1.5px] shadow-lg shadow-cyan-500/20 group-hover:scale-105 transition-transform">
              <div className="flex h-full w-full items-center justify-center rounded-[14px] bg-[#07090E] font-heading font-extrabold text-lg text-white">
                G
              </div>
            </div>
            <span className="font-heading font-bold text-xl tracking-tight text-white">
              Gig<span className="text-[#06B6D4]">Ghana</span>
            </span>
          </a>

          {/* Desktop Nav Links */}
          <nav className="hidden md:flex items-center gap-8 text-xs font-semibold text-white/70">
            <a href="/search/providers.php" className="hover:text-white transition-colors">
              Find Talent
            </a>
            <a href="/jobs.php" className="hover:text-white transition-colors">
              Browse Jobs
            </a>
            <a href="#how" className="hover:text-white transition-colors">
              Why GigGhana
            </a>
            <a href="#calculator" className="hover:text-white transition-colors">
              Earnings Estimator
            </a>
            <a href="#categories" className="hover:text-white transition-colors">
              Categories
            </a>
          </nav>

          {/* Nav Actions */}
          <div className="flex items-center gap-3">
            <button
              onClick={() => setIsCommandOpen(true)}
              className="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border border-white/10 bg-white/[0.04] text-white/60 hover:text-white hover:border-[#06B6D4]/40 transition-all cursor-pointer"
            >
              <Search className="w-3.5 h-3.5 text-[#06B6D4]" />
              <span>Search...</span>
              <kbd className="text-[9px] font-mono px-1.5 py-0.5 rounded bg-white/10 text-white/40">
                ⌘K
              </kbd>
            </button>

            <button
              onClick={toggleLang}
              className="px-2.5 py-1.5 rounded-xl border border-white/10 bg-white/[0.03] text-xs font-bold text-white/80 hover:text-white hover:bg-white/[0.08] transition-all"
              title="Toggle Language"
            >
              🌍 {lang === 'en' ? 'EN' : 'TW'}
            </button>

            <button
              onClick={toggleTheme}
              className="p-2 rounded-xl border border-white/10 bg-white/[0.03] text-xs text-white/80 hover:text-white hover:bg-white/[0.08] transition-all"
              title="Toggle Theme"
            >
              {isLight ? '☀️' : '🌙'}
            </button>

            <a
              href="/auth/login.php"
              className="hidden sm:inline-flex px-4 py-2 rounded-xl text-xs font-bold text-white/80 hover:text-white transition-colors"
            >
              Sign In
            </a>

            <a
              href="/auth/register.php"
              onClick={triggerConfetti}
              className="px-4 py-2 rounded-xl text-xs font-bold text-[#07090E] bg-gradient-to-r from-[#06B6D4] to-[#10B981] hover:brightness-110 shadow-lg shadow-cyan-500/20 transition-all"
            >
              Get Started Free
            </a>

            {/* Mobile Hamburger */}
            <button
              onClick={() => setIsMobOpen(!isMobOpen)}
              className="md:hidden p-2 text-white/70 hover:text-white"
            >
              <div className="w-5 h-0.5 bg-white mb-1" />
              <div className="w-5 h-0.5 bg-white mb-1" />
              <div className="w-5 h-0.5 bg-white" />
            </button>
          </div>
        </div>

        {/* Mobile Dropdown */}
        {isMobOpen && (
          <div className="md:hidden mt-3 px-4 py-4 bg-[#0C0E14] border-b border-white/10 space-y-3 text-sm font-semibold">
            <a href="/search/providers.php" className="block text-white/80 hover:text-white">Find Talent</a>
            <a href="/jobs.php" className="block text-white/80 hover:text-white">Browse Jobs</a>
            <a href="#how" className="block text-white/80 hover:text-white">Why GigGhana</a>
            <a href="#calculator" className="block text-white/80 hover:text-white">Earnings Estimator</a>
            <a href="/auth/login.php" className="block text-white/80 hover:text-white">Sign In</a>
          </div>
        )}
      </header>

      {/* ══════ BESPOKE HERO SECTION ══════ */}
      <AuroraBackground className="pt-32 pb-20 md:pt-40 md:pb-28">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col items-center text-center max-w-3xl mx-auto mb-10">
            {/* Top Ghana Trust Pill */}
            <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/[0.04] border border-white/15 text-xs font-semibold text-white/80 backdrop-blur-xl mb-6 shadow-inner">
              <span className="flex h-2 w-2 rounded-full bg-[#10B981]" />
              <span>Ghana&apos;s #1 Escrow-Secured Freelance Marketplace</span>
            </div>

            {/* Dual Role Switcher */}
            <div className="mb-8">
              <RoleSwitcher role={role} onChange={setRole} />
            </div>

            {/* Dynamic Headline */}
            <h1 className="text-4xl sm:text-6xl font-extrabold font-heading tracking-tight text-white leading-[1.1] mb-6">
              {role === 'hire' ? (
                <>
                  Hire Verified Ghanaian Talent.
                  <br />
                  <span className="text-transparent bg-clip-text bg-gradient-to-r from-[#F59E0B] via-[#06B6D4] to-[#10B981]">
                    Zero Risk. Escrow-Secured.
                  </span>
                </>
              ) : (
                <>
                  Turn Your Skills Into Daily Income.
                  <br />
                  <span className="text-transparent bg-clip-text bg-gradient-to-r from-[#06B6D4] via-[#10B981] to-[#F59E0B]">
                    Instant Payouts via Mobile Money.
                  </span>
                </>
              )}
            </h1>

            {/* Subtitle with bilingual support */}
            <p className="text-base sm:text-lg text-white/70 leading-relaxed max-w-2xl mb-8">
              {lang === 'en'
                ? role === 'hire'
                  ? 'Connect with top-rated developers, designers, carpenters, electricians, nurses and private chefs across Ghana with 100% money-back escrow protection.'
                  : 'Join thousands of Ghanaian freelancers earning daily. Enjoy 3 free jobs, biometric trust badges, and instant payments directly to MTN MoMo, Telecel Cash, and AT Money.'
                : 'GigGhana de Ghanafoɔ nyinaa ho adwuma na wɔtua ka pɛ — IT, adwuma, yadeɛ, adesua, ahosiesie ne ebi.'}
            </p>

            {/* High-End Search Capsule */}
            <div className="w-full max-w-2xl relative mb-6">
              <form
                onSubmit={(e) => {
                  e.preventDefault();
                  window.location.href = `/search/providers.php?q=${encodeURIComponent(searchQuery)}&category=${encodeURIComponent(selectedCategory)}`;
                }}
                className="relative flex items-center rounded-2xl border border-white/20 bg-[#0C0E14]/90 p-2 shadow-2xl shadow-cyan-950/30 backdrop-blur-2xl focus-within:border-[#06B6D4] transition-all"
              >
                <Search className="w-5 h-5 ml-3 text-[#06B6D4] shrink-0" />
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => {
                    setSearchQuery(e.target.value);
                    setAutocompleteOpen(true);
                  }}
                  onFocus={() => setAutocompleteOpen(true)}
                  placeholder="e.g. React Developer, Carpenter, Nurse, Chef, Plumber..."
                  className="w-full bg-transparent px-3 py-2 text-sm text-white placeholder-white/40 focus:outline-none font-body"
                />

                <select
                  value={selectedCategory}
                  onChange={(e) => setSelectedCategory(e.target.value)}
                  className="hidden sm:block bg-white/[0.05] text-xs text-white/80 rounded-xl px-3 py-2 border border-white/10 focus:outline-none font-body mr-2 cursor-pointer"
                >
                  <option value="" className="bg-[#0C0E14]">All Categories</option>
                  {categories.map((c) => (
                    <option key={c.id} value={c.id} className="bg-[#0C0E14]">
                      {c.name}
                    </option>
                  ))}
                </select>

                <button
                  type="submit"
                  className="px-5 py-2.5 rounded-xl font-bold text-xs text-[#07090E] bg-gradient-to-r from-[#06B6D4] to-[#10B981] hover:brightness-110 transition-all shrink-0 font-heading"
                >
                  Search
                </button>
              </form>

              {/* Autocomplete Dropdown */}
              {autocompleteOpen && filteredSuggestions.length > 0 && (
                <div className="absolute left-0 right-0 top-full mt-2 rounded-2xl border border-white/15 bg-[#0C0E14] p-2 shadow-2xl z-30 text-left">
                  {filteredSuggestions.map((m, idx) => (
                    <div
                      key={idx}
                      onClick={() => {
                        setSearchQuery(m.text);
                        setAutocompleteOpen(false);
                        window.location.href = `/search/providers.php?q=${encodeURIComponent(m.text)}`;
                      }}
                      className="flex items-center justify-between px-3 py-2 rounded-xl text-xs text-white hover:bg-[#06B6D4]/15 hover:text-[#06B6D4] cursor-pointer transition-colors"
                    >
                      <div className="flex items-center gap-2.5">
                        <span>{m.icon}</span>
                        <span className="font-semibold">{m.text}</span>
                      </div>
                      <span className="text-[10px] text-white/40">{m.cat}</span>
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* Trending Quick Pills */}
            <div className="flex flex-wrap items-center justify-center gap-2 text-xs text-white/60">
              <span className="text-white/40">Trending searches:</span>
              {trendingSkills.map((ts, idx) => (
                <a
                  key={idx}
                  href={`/search/providers.php?q=${encodeURIComponent(ts.replace(/^[^\s]+ /, ''))}`}
                  className="px-2.5 py-1 rounded-full bg-white/[0.04] border border-white/10 hover:border-[#06B6D4]/40 hover:text-white transition-all text-[11px]"
                >
                  {ts}
                </a>
              ))}
            </div>
          </div>

          {/* Hero Visual Card: Live 3D Escrow Simulator */}
          <div className="mt-8">
            <TransactionVisualizerCard />
          </div>
        </div>
      </AuroraBackground>

      {/* ══════ LIVE MARKETPLACE PULSE BAR ══════ */}
      <section className="relative border-y border-white/10 bg-[#0C0E14]/80 backdrop-blur-xl py-8" ref={statsRef}>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div className="space-y-1">
              <div className="text-3xl sm:text-4xl font-extrabold font-heading text-white tracking-tight">
                {countProviders.toLocaleString()}
              </div>
              <div className="text-xs text-white/50 flex items-center justify-center gap-1">
                <Users className="w-3.5 h-3.5 text-[#06B6D4]" />
                <span>Verified Ghanaian Freelancers</span>
              </div>
            </div>

            <div className="space-y-1">
              <div className="text-3xl sm:text-4xl font-extrabold font-heading text-[#06B6D4] tracking-tight">
                {countJobs.toLocaleString()}
              </div>
              <div className="text-xs text-white/50 flex items-center justify-center gap-1">
                <Briefcase className="w-3.5 h-3.5 text-[#06B6D4]" />
                <span>Open Gigs &amp; Contracts</span>
              </div>
            </div>

            <div className="space-y-1">
              <div className="text-3xl sm:text-4xl font-extrabold font-heading text-[#10B981] tracking-tight">
                {countCompleted.toLocaleString()}
              </div>
              <div className="text-xs text-white/50 flex items-center justify-center gap-1">
                <CheckCircle2 className="w-3.5 h-3.5 text-[#10B981]" />
                <span>Completed Milestones</span>
              </div>
            </div>

            <div className="space-y-1">
              <div className="text-3xl sm:text-4xl font-extrabold font-heading text-amber-400 tracking-tight">
                ₵{countEarnings.toLocaleString()}K+
              </div>
              <div className="text-xs text-white/50 flex items-center justify-center gap-1">
                <TrendingUp className="w-3.5 h-3.5 text-amber-400" />
                <span>Released via Escrow (GHS)</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ══════ THE BENTO ECOSYSTEM ("WHY GIGHANA") ══════ */}
      <section className="py-24 relative" id="how">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-3xl mx-auto mb-16">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#06B6D4]/10 text-[#06B6D4] border border-[#06B6D4]/20 text-xs font-bold uppercase tracking-wider font-heading mb-3">
              <Sparkles className="w-3.5 h-3.5" />
              <span>THE GIGHANA STANDARD</span>
            </div>
            <h2 className="text-3xl sm:text-5xl font-extrabold font-heading tracking-tight text-white">
              Engineered for Trust, Speed, and Fair Work
            </h2>
            <p className="text-sm sm:text-base text-white/60 mt-4 leading-relaxed">
              We built GigGhana to eliminate the trust barriers in African freelancing. Every payment is held safely until milestones are signed off.
            </p>
          </div>

          <BentoGrid>
            <BentoCard
              title="100% Guaranteed Escrow Protection"
              description="Clients deposit funds into the GigGhana Escrow Vault before work begins. Funds are locked securely and released only when you approve the final deliverable."
              icon={<ShieldCheck className="w-6 h-6 text-[#06B6D4]" />}
              badge="Zero Scam Policy"
              spotlightColor="rgba(6, 182, 212, 0.2)"
              className="md:col-span-2"
              header={
                <div className="p-4 rounded-2xl bg-gradient-to-r from-cyan-950/40 via-emerald-950/20 to-transparent border border-cyan-500/20 flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <Lock className="w-5 h-5 text-[#06B6D4]" />
                    <span className="text-xs font-bold text-white">Funds Locked in Smart Vault</span>
                  </div>
                  <span className="text-xs font-bold text-[#10B981]">100% Refund Guarantee</span>
                </div>
              }
            />

            <BentoCard
              title="Ghana Card Biometric Verification"
              description="Identity verification tied directly to official Ghana Card records. Eliminates bots, impersonators, and fraudulent job postings."
              icon={<Award className="w-6 h-6 text-[#10B981]" />}
              badge="Verified Profiles"
              spotlightColor="rgba(16, 185, 129, 0.2)"
            />

            <BentoCard
              title="Instant MoMo &amp; Local Bank Payouts"
              description="Direct integration with MTN Mobile Money, Telecel Cash, AT Money, and local banks. Receive funds in your wallet within 60 seconds of client approval."
              icon={<Smartphone className="w-6 h-6 text-amber-400" />}
              badge="Under 60s"
              spotlightColor="rgba(245, 158, 11, 0.2)"
            />

            <BentoCard
              title="3 Free Jobs + Transparent Subscriptions"
              description="Every provider starts free with 3 job applications. Upgrade anytime to Verified (₵49/mo) or Premium (₵99/mo) for top search ranking and unlimited client invites."
              icon={<Zap className="w-6 h-6 text-[#8B5CF6]" />}
              badge="No Hidden Commission"
              spotlightColor="rgba(139, 92, 246, 0.2)"
              className="md:col-span-2"
              header={
                <div className="grid grid-cols-3 gap-2 p-3 rounded-2xl bg-white/[0.02] border border-white/5 text-center text-xs">
                  <div className="p-2 rounded-xl bg-white/[0.03]">
                    <div className="font-bold text-white">Beginner</div>
                    <div className="text-[10px] text-white/50">Free · 3 Jobs</div>
                  </div>
                  <div className="p-2 rounded-xl bg-[#06B6D4]/15 border border-[#06B6D4]/30">
                    <div className="font-bold text-[#06B6D4]">Verified ✓</div>
                    <div className="text-[10px] text-[#06B6D4]">₵49/mo</div>
                  </div>
                  <div className="p-2 rounded-xl bg-amber-500/10 border border-amber-500/20">
                    <div className="font-bold text-amber-400">Premium ⭐</div>
                    <div className="text-[10px] text-amber-400">₵99/mo</div>
                  </div>
                </div>
              }
            />
          </BentoGrid>
        </div>
      </section>

      {/* ══════ INTERACTIVE EARNINGS ESTIMATOR ══════ */}
      <section className="py-20 relative border-t border-white/10" id="calculator">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <EarningsCalculator />
        </div>
      </section>

      {/* ══════ INTERACTIVE TALENT EXPLORER ══════ */}
      <section className="py-24 relative" id="talent">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div>
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#10B981]/10 text-[#10B981] border border-[#10B981]/20 text-xs font-bold uppercase tracking-wider font-heading mb-2">
                <Users className="w-3.5 h-3.5" />
                <span>TOP GHANAIAN PERFORMERS</span>
              </div>
              <h2 className="text-3xl sm:text-4xl font-extrabold font-heading tracking-tight text-white">
                Featured Freelancers &amp; Tradespeople
              </h2>
            </div>
            <a
              href="/search/providers.php"
              className="flex items-center gap-2 text-xs font-bold text-[#06B6D4] hover:underline"
            >
              <span>Explore All Verified Talent</span>
              <ArrowRight className="w-4 h-4" />
            </a>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {(featured.length > 0 ? featured : matchedProviders).slice(0, 6).map((pv, idx) => {
              const skills = pv.skill_names ? pv.skill_names.split('|').filter(Boolean) : [];
              const rk = rankLabel(Number(pv.completed_jobs || 0));
              const init = initials(pv.first_name, pv.last_name);
              const jobs = Number(pv.completed_jobs || 0);
              const rating = Number(pv.rating_avg || 5);

              return (
                <SpotlightCard
                  key={idx}
                  spotlightColor="rgba(6, 182, 212, 0.18)"
                  className="p-6 flex flex-col justify-between"
                >
                  <div>
                    <div className="flex items-start justify-between gap-4 mb-4">
                      <div className="flex items-center gap-3">
                        <div className="relative">
                          {pv.avatar ? (
                            <img
                              src={pv.avatar}
                              alt={pv.first_name}
                              className="h-12 w-12 rounded-2xl object-cover border border-white/10"
                            />
                          ) : (
                            <div className="h-12 w-12 rounded-2xl bg-gradient-to-tr from-[#06B6D4] to-[#10B981] flex items-center justify-center font-bold text-sm text-[#07090E]">
                              {init}
                            </div>
                          )}
                          {pv.is_verified ? (
                            <span className="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[#06B6D4] text-[9px] text-[#07090E] font-bold">
                              ✓
                            </span>
                          ) : null}
                        </div>
                        <div>
                          <div className="text-base font-bold text-white font-heading">
                            {pv.first_name} {pv.last_name}
                          </div>
                          <div className="text-xs text-white/50 flex items-center gap-1">
                            <MapPin className="w-3 h-3 text-[#06B6D4]" />
                            <span>{pv.location || 'Accra, Ghana'}</span>
                          </div>
                        </div>
                      </div>
                      <span className={`px-2.5 py-1 rounded-full text-[10px] font-bold border ${rk.c}`}>
                        {rk.i} {rk.l}
                      </span>
                    </div>

                    <p className="text-xs text-white/70 line-clamp-2 mb-4 leading-relaxed">
                      {pv.tagline || `${pv.experience_level ? pv.experience_level.charAt(0).toUpperCase() + pv.experience_level.slice(1) : 'Professional'} specialist ready for top projects.`}
                    </p>

                    <div className="flex items-center gap-1.5 text-xs font-bold text-amber-400 mb-4">
                      <Star className="w-3.5 h-3.5 fill-amber-400" />
                      <span>{rating.toFixed(1)}</span>
                      <span className="text-white/40 font-normal font-mono">({Number(pv.rating_count || 0)} reviews)</span>
                      <span className="text-white/20 mx-1">·</span>
                      <span className="text-white/60 font-normal">{jobs} jobs completed</span>
                    </div>

                    <div className="flex flex-wrap gap-1.5 mb-6">
                      {skills.slice(0, 3).map((sk: string, sIdx: number) => (
                        <span
                          key={sIdx}
                          className="px-2 py-0.5 rounded-lg bg-white/[0.04] border border-white/5 text-[10px] font-medium text-white/80"
                        >
                          {sk}
                        </span>
                      ))}
                    </div>
                  </div>

                  <div className="pt-4 border-t border-white/5 flex items-center justify-between">
                    <div>
                      <div className="text-[10px] text-white/40">Hourly Rate</div>
                      <div className="text-sm font-bold text-white font-heading">
                        {pv.hourly_rate > 0 ? (
                          <>
                            {formatCurrency(pv.hourly_rate)}
                            <span className="text-xs text-white/50 font-normal">/hr</span>
                          </>
                        ) : (
                          'Negotiable'
                        )}
                      </div>
                    </div>

                    <a
                      href={`/profile.php?id=${pv.user_id}`}
                      className="px-4 py-2 rounded-xl text-xs font-bold text-[#07090E] bg-white hover:bg-[#06B6D4] transition-colors"
                    >
                      View Profile
                    </a>
                  </div>
                </SpotlightCard>
              );
            })}
          </div>
        </div>
      </section>

      {/* ══════ CATEGORIES GRID ══════ */}
      <section className="py-24 relative border-t border-white/10 bg-[#0C0E14]/40" id="categories">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-14">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-bold uppercase tracking-wider font-heading mb-3">
              <span>EXPLORE DISCIPLINES</span>
            </div>
            <h2 className="text-3xl sm:text-4xl font-extrabold font-heading tracking-tight text-white">
              Every Profession in Ghana
            </h2>
            <p className="text-sm text-white/60 mt-3">
              From high-tech engineering to physical trades, explore qualified talent in your region.
            </p>
          </div>

          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            {categories.map((cat) => (
              <SpotlightCard
                key={cat.id}
                spotlightColor="rgba(6, 182, 212, 0.14)"
                className="p-5 flex flex-col justify-between hover:translate-y-[-3px] transition-transform"
              >
                <a
                  href={`/search/providers.php?category=${cat.id}`}
                  className="flex flex-col h-full justify-between"
                >
                  <div>
                    <div className="text-3xl mb-3">{iconMap[cat.icon] || '🔧'}</div>
                    <div className="text-sm font-bold text-white font-heading mb-1">{cat.name}</div>
                    {cat.description && (
                      <p className="text-[11px] text-white/50 line-clamp-2 leading-relaxed">
                        {cat.description}
                      </p>
                    )}
                  </div>
                  <div className="mt-4 flex items-center gap-1 text-[11px] font-bold text-[#06B6D4]">
                    <span>Explore</span>
                    <ArrowRight className="w-3 h-3" />
                  </div>
                </a>
              </SpotlightCard>
            ))}
          </div>
        </div>
      </section>

      {/* ══════ RECENT JOBS OPPORTUNITIES ══════ */}
      <section className="py-24 relative border-t border-white/10">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div>
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#06B6D4]/10 text-[#06B6D4] border border-[#06B6D4]/20 text-xs font-bold uppercase tracking-wider font-heading mb-2">
                <Briefcase className="w-3.5 h-3.5" />
                <span>FRESH OPPORTUNITIES</span>
              </div>
              <h2 className="text-3xl sm:text-4xl font-extrabold font-heading tracking-tight text-white">
                Recently Posted Jobs in Ghana
              </h2>
            </div>
            <a
              href="/jobs.php"
              className="flex items-center gap-2 text-xs font-bold text-[#06B6D4] hover:underline"
            >
              <span>View All Open Jobs</span>
              <ArrowRight className="w-4 h-4" />
            </a>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {recentJobs.slice(0, 6).map((job) => (
              <SpotlightCard
                key={job.id}
                spotlightColor="rgba(245, 158, 11, 0.16)"
                className="p-6 flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-start justify-between gap-3 mb-3">
                    <span className="text-xs font-semibold text-[#06B6D4] flex items-center gap-1.5">
                      <span>{iconMap[job.cat_icon] || '📂'}</span>
                      <span>{job.cat_name || 'General'}</span>
                    </span>
                    <span
                      className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
                        job.is_urgent
                          ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30'
                          : 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30'
                      }`}
                    >
                      {job.is_urgent ? '🔥 Urgent' : '● Open'}
                    </span>
                  </div>

                  <h3 className="text-base font-bold text-white font-heading mb-2 line-clamp-2 leading-snug">
                    {job.title}
                  </h3>

                  <p className="text-xs text-white/60 line-clamp-3 mb-4 leading-relaxed">
                    {job.description}
                  </p>

                  <div className="flex items-center gap-4 text-[11px] text-white/50 mb-6 font-mono">
                    <span className="flex items-center gap-1">
                      <Clock className="w-3 h-3 text-[#06B6D4]" />
                      <span>{timeAgo(job.created_at)}</span>
                    </span>
                    <span>{Number(job.proposal_count || 0)} proposals</span>
                  </div>
                </div>

                <div className="pt-4 border-t border-white/5 flex items-center justify-between">
                  <div>
                    <div className="text-[10px] text-white/40">Budget</div>
                    <div className="text-sm font-bold text-[#10B981] font-heading">
                      {formatCurrency(job.budget_min)}
                      {job.budget_max > job.budget_min ? ` - ${formatCurrency(job.budget_max)}` : ''}
                    </div>
                  </div>

                  <a
                    href={`/job-details.php?id=${job.id}`}
                    className="px-4 py-2 rounded-xl text-xs font-bold text-white bg-white/10 hover:bg-[#06B6D4] hover:text-[#07090E] transition-colors"
                  >
                    Apply Now
                  </a>
                </div>
              </SpotlightCard>
            ))}
          </div>
        </div>
      </section>

      {/* ══════ TESTIMONIALS (WALL OF TRUST) ══════ */}
      <section className="py-24 relative border-t border-white/10 bg-[#0C0E14]/60">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#06B6D4]/10 text-[#06B6D4] border border-[#06B6D4]/20 text-xs font-bold uppercase tracking-wider font-heading mb-3">
              <Star className="w-3.5 h-3.5 fill-[#06B6D4]" />
              <span>COMMUNITY REPUTATION</span>
            </div>
            <h2 className="text-3xl sm:text-4xl font-extrabold font-heading tracking-tight text-white">
              Ghanaians Winning on GigGhana
            </h2>
            <p className="text-sm text-white/60 mt-3">
              Hear directly from tradespeople, developers, and businesses across Accra, Kumasi, and Takoradi.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {reviews.map((rv, idx) => (
              <SpotlightCard
                key={idx}
                spotlightColor="rgba(16, 185, 129, 0.16)"
                className="p-6 flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center gap-1 text-amber-400 mb-3">
                    {[1, 2, 3, 4, 5].map((s) => (
                      <Star key={s} className="w-3.5 h-3.5 fill-amber-400" />
                    ))}
                  </div>
                  <p className="text-xs text-white/80 italic leading-relaxed mb-6">
                    &ldquo;{rv.comment}&rdquo;
                  </p>
                </div>

                <div className="flex items-center gap-3 pt-4 border-t border-white/5">
                  <div className="h-9 w-9 rounded-full bg-gradient-to-tr from-[#06B6D4] to-[#10B981] flex items-center justify-center font-bold text-xs text-[#07090E]">
                    {initials(rv.first_name, rv.last_name)}
                  </div>
                  <div>
                    <div className="text-xs font-bold text-white">
                      {rv.first_name} {rv.last_name}
                    </div>
                    <div className="text-[10px] text-white/40">
                      {rv.role ? rv.role.charAt(0).toUpperCase() + rv.role.slice(1) : 'User'} · {rv.location || 'Ghana'}
                    </div>
                  </div>
                </div>
              </SpotlightCard>
            ))}
          </div>
        </div>
      </section>

      {/* ══════ TRANSPARENT EARNINGS ANALYTICS ══════ */}
      <section className="py-20 relative border-t border-white/10">
        <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="rounded-3xl border border-white/10 bg-[#0C0E14]/90 p-8 backdrop-blur-2xl shadow-2xl">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-6 border-b border-white/10">
              <div>
                <h3 className="text-lg font-bold text-white font-heading">
                  Platform Escrow Releases ({new Date().getFullYear()})
                </h3>
                <p className="text-xs text-white/50">
                  Total live volume released securely to Ghanaian freelancers
                </p>
              </div>
              <div className="text-right">
                <div className="text-2xl font-extrabold text-[#06B6D4] font-heading">
                  ₵{earningsTotal.toLocaleString('en-GH', { minimumFractionDigits: 2 })}
                </div>
                <div className="text-[10px] text-white/40">YTD Paid Out</div>
              </div>
            </div>
            <Line data={chartData} options={chartOptions} height={100} />
          </div>
        </div>
      </section>

      {/* ══════ INFINITE PARTNER MARQUEE ══════ */}
      <div className="py-12 border-t border-white/10 bg-[#07090E]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center text-xs font-semibold text-white/40 uppercase tracking-widest mb-6">
            Supported Payment Gateways &amp; Technology Partners
          </div>

          <Marquee pauseOnHover className="[--duration:28s]">
            <div className="flex items-center gap-3 px-6 py-3 rounded-2xl bg-white/[0.03] border border-white/5 text-xs font-bold text-white">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/MTN_Logo.svg/512px-MTN_Logo.svg.png"
                alt="MTN MoMo"
                className="h-6 w-auto object-contain"
              />
              <span>MTN Mobile Money</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3 rounded-2xl bg-white/[0.03] border border-white/5 text-xs font-bold text-white">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Vodafone_icon.svg/512px-Vodafone_icon.svg.png"
                alt="Telecel Cash"
                className="h-6 w-auto object-contain"
              />
              <span>Telecel / Vodafone Cash</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3 rounded-2xl bg-white/[0.03] border border-white/5 text-xs font-bold text-rose-400">
              <span>📶 AirtelTigo Money</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3 rounded-2xl bg-white/[0.03] border border-white/5 text-xs font-bold text-white">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Paystack_logo.png"
                alt="Paystack"
                className="h-5 w-auto object-contain"
              />
              <span>Paystack Verified</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3 rounded-2xl bg-white/[0.03] border border-white/5 text-xs font-bold text-white">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/800px-Visa_Inc._logo.svg.png"
                alt="Visa"
                className="h-4 w-auto object-contain"
              />
              <span>Visa Card</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3 rounded-2xl bg-white/[0.03] border border-white/5 text-xs font-bold text-white">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/800px-Mastercard-logo.svg.png"
                alt="Mastercard"
                className="h-5 w-auto object-contain"
              />
              <span>Mastercard</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3 rounded-2xl bg-white/[0.03] border border-white/5 text-xs font-bold text-[#10B981]">
              <ShieldCheck className="w-5 h-5 text-[#10B981]" />
              <span>Smart Escrow Vault</span>
            </div>
          </Marquee>
        </div>
      </div>

      {/* ══════ HIGH-CONVERTING CTA BANNER ══════ */}
      <section className="py-24 relative border-t border-white/10 overflow-hidden">
        <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative">
          <div className="relative rounded-3xl border border-white/15 bg-gradient-to-br from-[#06B6D4]/15 via-[#10B981]/10 to-[#8B5CF6]/15 p-10 sm:p-16 text-center backdrop-blur-2xl shadow-2xl shadow-cyan-950/40">
            <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/[0.08] border border-white/20 text-xs font-bold text-white mb-6">
              <Sparkles className="w-4 h-4 text-amber-400" />
              <span>Join Africa&apos;s Next-Gen Talent Economy</span>
            </div>

            <h2 className="text-3xl sm:text-5xl font-extrabold font-heading text-white tracking-tight max-w-2xl mx-auto leading-tight mb-6">
              Start Hiring or Earning in Ghana Today
            </h2>

            <p className="text-sm sm:text-base text-white/70 max-w-xl mx-auto mb-10 leading-relaxed">
              Create your account in under 2 minutes. Free 3 job applications for all freelancers. Zero deposit fees for clients.
            </p>

            <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
              <a
                href="/auth/register.php?role=provider"
                onClick={triggerConfetti}
                className="w-full sm:w-auto px-8 py-4 rounded-2xl font-bold text-sm text-[#07090E] bg-gradient-to-r from-[#06B6D4] to-[#10B981] hover:brightness-110 shadow-xl shadow-cyan-500/25 transition-all font-heading"
              >
                🚀 Sign Up as a Freelancer
              </a>

              <a
                href="/auth/register.php?role=client"
                onClick={triggerConfetti}
                className="w-full sm:w-auto px-8 py-4 rounded-2xl font-bold text-sm text-white bg-white/[0.08] border border-white/20 hover:bg-white/[0.15] transition-all font-heading"
              >
                🏢 Post a Job for Free
              </a>
            </div>
          </div>
        </div>
      </section>

      {/* ══════ MODERN FOOTER ══════ */}
      <footer className="border-t border-white/10 bg-[#07090E] pt-16 pb-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-5 gap-10 pb-12 border-b border-white/10">
            <div className="md:col-span-2 space-y-4">
              <a href="/" className="flex items-center gap-2">
                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-[#06B6D4] to-[#10B981] font-heading font-extrabold text-[#07090E] text-sm">
                  G
                </div>
                <span className="font-heading font-bold text-lg text-white">
                  Gig<span className="text-[#06B6D4]">Ghana</span>
                </span>
              </a>
              <p className="text-xs text-white/60 leading-relaxed max-w-sm">
                Ghana&apos;s premier freelance marketplace connecting verified talent across IT, creative arts, skilled trades, health, and hospitality with forward-thinking businesses.
              </p>

              {/* Newsletter */}
              <form onSubmit={handleSubscribeNL} className="pt-2 flex max-w-sm gap-2">
                <input
                  type="email"
                  placeholder="Enter your email"
                  value={nlEmail}
                  onChange={(e) => setNlEmail(e.target.value)}
                  className="w-full px-3.5 py-2 rounded-xl bg-white/[0.04] border border-white/10 text-xs text-white placeholder-white/40 focus:outline-none focus:border-[#06B6D4]"
                />
                <button
                  type="submit"
                  className="px-4 py-2 rounded-xl text-xs font-bold text-[#07090E] bg-[#06B6D4] hover:bg-[#10B981] transition-colors shrink-0"
                >
                  Subscribe
                </button>
              </form>
            </div>

            <div>
              <div className="text-xs font-bold text-white font-heading uppercase tracking-wider mb-4">
                Platform
              </div>
              <ul className="space-y-2.5 text-xs text-white/60">
                <li><a href="/search/providers.php" className="hover:text-white transition-colors">Find Talent</a></li>
                <li><a href="/jobs.php" className="hover:text-white transition-colors">Browse Open Jobs</a></li>
                <li><a href="/auth/register.php" className="hover:text-white transition-colors">Post a Job</a></li>
                <li><a href="#calculator" className="hover:text-white transition-colors">Earnings Estimator</a></li>
              </ul>
            </div>

            <div>
              <div className="text-xs font-bold text-white font-heading uppercase tracking-wider mb-4">
                Company
              </div>
              <ul className="space-y-2.5 text-xs text-white/60">
                <li><a href="#" className="hover:text-white transition-colors">About GigGhana</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Escrow Security</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Badge Tiers</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Careers</a></li>
              </ul>
            </div>

            <div>
              <div className="text-xs font-bold text-white font-heading uppercase tracking-wider mb-4">
                Support &amp; Trust
              </div>
              <ul className="space-y-2.5 text-xs text-white/60">
                <li><a href="#" className="hover:text-white transition-colors">Help Centre</a></li>
                <li><a href="/privacy.php" className="hover:text-white transition-colors">Privacy Policy</a></li>
                <li><a href="/terms.php" className="hover:text-white transition-colors">Terms of Service</a></li>
                <li><a href="#" className="hover:text-white transition-colors">Dispute Resolution</a></li>
              </ul>
            </div>
          </div>

          <div className="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-white/40">
            <div>
              © {new Date().getFullYear()} GigGhana Ltd. Made with ❤️ in Ghana 🇬🇭
            </div>
            <div className="flex items-center gap-6">
              <span>🔒 256-bit SSL Protected</span>
              <span>🇬🇭 Verified Ghana Registry</span>
              <span>⚡ Powered by Next.js 15</span>
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
    </div>
  );
}
