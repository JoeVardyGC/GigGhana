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
import { LuxuryEstimator } from './ui/luxury-estimator';
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
  Gem,
  Compass,
  Check,
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

const guilds = [
  {
    title: 'Technology & Software Architecture',
    desc: 'Full-stack engineers, cloud architects, mobile developers, and AI engineers.',
    icon: '💻',
    count: '420+ Artisans',
    slug: 'web-development',
  },
  {
    title: 'Master Craftsmen & Skilled Trades',
    desc: 'Bespoke joinery, master carpentry, precision plumbing, and electrical engineering.',
    icon: '🪚',
    count: '680+ Artisans',
    slug: 'skilled-trades',
  },
  {
    title: 'Haute Culinary & Hospitality',
    desc: 'Executive private chefs, luxury caterers, sommeliers, and estate managers.',
    icon: '🍽️',
    count: '210+ Artisans',
    slug: 'hospitality',
  },
  {
    title: 'Health & Private Practice',
    desc: 'Private nursing, certified physiotherapists, and wellness practitioners.',
    icon: '🏥',
    count: '150+ Artisans',
    slug: 'health-wellness',
  },
  {
    title: 'Creative Direction & Editorial Media',
    desc: 'Art directors, fashion photographers, visual storytellers, and brand consultants.',
    icon: '🎨',
    count: '340+ Artisans',
    slug: 'graphic-design',
  },
];

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

export default function LuxuryLandingPage({ initialData }: Props) {
  const { stats, categories, featured, matchedProviders, recentJobs, liveJobs, earningsData, earningsTotal, reviews } = initialData;

  // Perspective: 'client' (Commission Master Talent) vs 'artisan' (Join the Guild)
  const [perspective, setPerspective] = useState<'client' | 'artisan'>('client');

  // Theme state: dark by default, .lm for light mode
  const [isLight, setIsLight] = useState(false);

  // Language state
  const [lang, setLang] = useState<'en' | 'tw'>('en');

  // Command palette state
  const [isCommandOpen, setIsCommandOpen] = useState(false);

  // Mobile menu state
  const [isMobOpen, setIsMobOpen] = useState(false);

  // Search input
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('');

  // Toast notifications
  const [toasts, setToasts] = useState<ToastItem[]>([]);

  // Navbar scroll state
  const [scrolledNav, setScrolledNav] = useState(false);

  // Newsletter email
  const [nlEmail, setNlEmail] = useState('');

  // Animated stats
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

  const triggerGoldConfetti = () => {
    try {
      confetti({
        particleCount: 80,
        spread: 70,
        origin: { y: 0.7 },
        colors: ['#D4AF37', '#F3E5AB', '#10B981', '#FFFFFF', '#06B6D4'],
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

  const handleSubscribeNL = (e: React.FormEvent) => {
    e.preventDefault();
    if (!nlEmail || !nlEmail.includes('@')) {
      showToast('Notice', 'Please enter a valid email address.', 'error');
      return;
    }
    triggerGoldConfetti();
    showToast('Privilege Granted 🏛️', 'Welcome to the sovereign GigGhana dispatch.', 'success');
    setNlEmail('');
  };

  // Chart Config
  const chartData = {
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [
      {
        label: 'Escrow Volume Released (₵)',
        data: earningsData,
        borderColor: '#D4AF37',
        backgroundColor: 'rgba(212, 175, 55, 0.08)',
        borderWidth: 2.5,
        pointBackgroundColor: '#D4AF37',
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
        titleFont: { family: 'Playfair Display', weight: 700 as const },
        callbacks: {
          label: (context: any) =>
            ' ₵' + context.parsed.y.toLocaleString('en-GH', { minimumFractionDigits: 2 }),
        },
      },
    },
    scales: {
      x: {
        grid: { color: 'var(--border)' },
        ticks: { color: 'var(--tx-3)', font: { size: 11, family: 'Plus Jakarta Sans' } },
      },
      y: {
        grid: { color: 'var(--border)' },
        beginAtZero: true,
        ticks: {
          color: 'var(--tx-3)',
          font: { size: 11, family: 'Plus Jakarta Sans' },
          callback: (v: any) => '₵' + Number(v).toLocaleString(),
        },
      },
    },
  };

  return (
    <div className="min-h-screen bg-[var(--bg)] text-[var(--tx)] transition-colors duration-300 selection:bg-[var(--gold)] selection:text-[#090A0F]">
      {/* ══════ COMMAND SEARCH MODAL (Cmd + K) ══════ */}
      <CommandSearchDialog
        open={isCommandOpen}
        onOpenChange={setIsCommandOpen}
        categories={categories}
      />

      {/* ══════ FLOATING QUICK DOCK ISLAND ══════ */}
      <FloatingDock onOpenSearch={() => setIsCommandOpen(true)} />

      {/* ══════ HAUTE EDITORIAL HEADER ══════ */}
      <header
        className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
          scrolledNav
            ? 'bg-[var(--glass)] backdrop-blur-2xl border-b border-[var(--border-hi)] py-3.5 shadow-2xl'
            : 'bg-transparent py-5'
        }`}
      >
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
          <a href="/" className="flex items-center gap-3 group">
            <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-tr from-[var(--gold-light)] via-[var(--gold)] to-[#996515] p-[1px] shadow-md group-hover:scale-105 transition-transform">
              <div className="flex h-full w-full items-center justify-center rounded-[15px] bg-[var(--bg)] font-serif font-bold text-sm text-[var(--gold)]">
                GG
              </div>
            </div>
            <div>
              <span className="font-serif font-bold text-xl tracking-tight text-[var(--tx)]">
                Gig<span className="text-gold-gradient">Ghana</span>
              </span>
              <div className="text-[9px] uppercase tracking-widest text-[var(--tx-3)] font-sans font-bold -mt-1">
                Sovereign Edition
              </div>
            </div>
          </a>

          {/* Editorial Nav Links */}
          <nav className="hidden md:flex items-center gap-8 text-xs font-bold uppercase tracking-widest text-[var(--tx-2)]">
            <a href="#guilds" className="hover:text-[var(--gold)] transition-colors">
              The Guilds
            </a>
            <a href="#artisans" className="hover:text-[var(--gold)] transition-colors">
              Master Artisans
            </a>
            <a href="#sanctuary" className="hover:text-[var(--gold)] transition-colors">
              Escrow Sanctuary
            </a>
            <a href="#estimator" className="hover:text-[var(--gold)] transition-colors">
              Estimator
            </a>
            <a href="#commissions" className="hover:text-[var(--gold)] transition-colors">
              Commissions
            </a>
          </nav>

          {/* Nav Controls */}
          <div className="flex items-center gap-3">
            <button
              onClick={() => setIsCommandOpen(true)}
              className="hidden lg:flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold border border-[var(--border)] bg-[var(--surface-2)] text-[var(--tx-2)] hover:text-[var(--gold)] hover:border-[var(--border-hi)] transition-all cursor-pointer"
            >
              <Search className="w-3.5 h-3.5 text-[var(--gold)]" />
              <span>Search Concierge</span>
              <kbd className="text-[9px] font-mono px-1.5 py-0.5 rounded bg-[var(--surface)] text-[var(--tx-3)]">
                ⌘K
              </kbd>
            </button>

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
              className="hidden sm:inline-flex px-4 py-2 text-xs font-bold uppercase tracking-wider text-[var(--tx-2)] hover:text-[var(--tx)] transition-colors"
            >
              Sign In
            </a>

            <a
              href="/auth/register.php"
              onClick={triggerGoldConfetti}
              className="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-[#090A0F] bg-gradient-to-r from-[var(--gold-light)] via-[var(--gold)] to-[#B8860B] hover:brightness-110 shadow-md shadow-gold/20 transition-all font-sans"
            >
              Commission Talent
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
          <div className="md:hidden mt-3 px-6 py-6 bg-[var(--surface)] border-b border-[var(--border-hi)] space-y-4 text-xs font-bold uppercase tracking-wider">
            <a href="#guilds" className="block text-[var(--tx-2)] hover:text-[var(--gold)]">The Guilds</a>
            <a href="#artisans" className="block text-[var(--tx-2)] hover:text-[var(--gold)]">Master Artisans</a>
            <a href="#sanctuary" className="block text-[var(--tx-2)] hover:text-[var(--gold)]">Escrow Sanctuary</a>
            <a href="#estimator" className="block text-[var(--tx-2)] hover:text-[var(--gold)]">Estimator</a>
            <a href="/auth/login.php" className="block text-[var(--tx-2)] hover:text-[var(--gold)]">Sign In</a>
          </div>
        )}
      </header>

      {/* ══════ HAUTE EDITORIAL HERO ══════ */}
      <section className="relative pt-32 pb-24 md:pt-44 md:pb-32 overflow-hidden border-b border-[var(--border)]">
        {/* Subtle Ambient Gold Glow */}
        <div className="absolute top-1/4 left-1/2 -translate-x-1/2 w-[800px] h-[450px] rounded-full bg-[var(--gold)] opacity-[0.06] blur-[150px] pointer-events-none" />

        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            {/* Left Typographic Column */}
            <div className="lg:col-span-7 text-left space-y-6">
              {/* Sovereign Seal Pill */}
              <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[var(--gold-dim)] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-widest text-[var(--gold)]">
                <Gem className="w-3.5 h-3.5" />
                <span>SOVEREIGN AFRICAN COMMERCE</span>
              </div>

              {/* Perspective Selector */}
              <div className="flex items-center gap-3 pt-1">
                <button
                  onClick={() => setPerspective('client')}
                  className={`px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all ${
                    perspective === 'client'
                      ? 'bg-[var(--gold)] text-[#090A0F] shadow-lg shadow-gold/20'
                      : 'bg-[var(--surface-2)] text-[var(--tx-2)] border border-[var(--border)] hover:text-[var(--tx)]'
                  }`}
                >
                  Commission Master Talent
                </button>
                <button
                  onClick={() => setPerspective('artisan')}
                  className={`px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider transition-all ${
                    perspective === 'artisan'
                      ? 'bg-[var(--gold)] text-[#090A0F] shadow-lg shadow-gold/20'
                      : 'bg-[var(--surface-2)] text-[var(--tx-2)] border border-[var(--border)] hover:text-[var(--tx)]'
                  }`}
                >
                  Join the Sovereign Guild
                </button>
              </div>

              {/* Main Editorial Headline */}
              <h1 className="text-4xl sm:text-6xl lg:text-7xl font-serif font-bold text-[var(--tx)] leading-[1.08] tracking-tight">
                Where <span className="italic font-normal">Extraordinary</span> Ghanaian Talent Meets{' '}
                <span className="text-gold-gradient font-black">World-Class Commerce.</span>
              </h1>

              {/* Editorial Description */}
              <p className="text-base sm:text-lg text-[var(--tx-2)] leading-relaxed max-w-xl">
                {lang === 'en'
                  ? perspective === 'client'
                    ? 'Engage vetted software architects, master joiners, executive chefs, and clinical specialists across Ghana with 100% money-back escrow sanctuary.'
                    : 'Establish your independent practice in Ghana’s most prestigious marketplace. Enjoy 3 free commission openings, biometric verification, and instant MoMo payouts.'
                  : 'GigGhana de Ghanafoɔ nyinaa ho adwuma na wɔtua ka pɛ — IT, adwuma, yadeɛ, adesua, ahosiesie ne ebi.'}
              </p>

              {/* Concierge Search Capsule */}
              <form
                onSubmit={(e) => {
                  e.preventDefault();
                  window.location.href = `/search/providers.php?q=${encodeURIComponent(searchQuery)}&category=${encodeURIComponent(selectedCategory)}`;
                }}
                className="flex items-center rounded-2xl border border-[var(--border-hi)] bg-[var(--surface)] p-2 shadow-2xl focus-within:border-[var(--gold)] transition-all max-w-xl"
              >
                <Search className="w-5 h-5 ml-3 text-[var(--gold)] shrink-0" />
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Find a Senior Architect, Joiner, Executive Chef... (or ⌘K)"
                  className="w-full bg-transparent px-3 py-2 text-xs sm:text-sm text-[var(--tx)] placeholder-[var(--tx-3)] focus:outline-none font-sans"
                />

                <button
                  type="submit"
                  className="px-5 py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-[#090A0F] bg-gradient-to-r from-[var(--gold-light)] via-[var(--gold)] to-[#B8860B] hover:brightness-110 transition-all shrink-0 font-sans"
                >
                  Inquire
                </button>
              </form>

              {/* Guarantee hallmarks */}
              <div className="flex flex-wrap items-center gap-6 pt-2 text-xs text-[var(--tx-3)] font-sans">
                <span className="flex items-center gap-1.5">
                  <ShieldCheck className="w-4 h-4 text-[var(--gold)]" />
                  <span>Guaranteed Escrow Sanctuary</span>
                </span>
                <span className="flex items-center gap-1.5">
                  <Award className="w-4 h-4 text-[var(--gold)]" />
                  <span>Ghana Card Biometric Seal</span>
                </span>
                <span className="flex items-center gap-1.5">
                  <Smartphone className="w-4 h-4 text-[var(--gold)]" />
                  <span>Sub-Minute MoMo Settlement</span>
                </span>
              </div>
            </div>

            {/* Right Curated Visual Column */}
            <div className="lg:col-span-5">
              <div className="relative rounded-3xl border border-[var(--border-hi)] bg-[var(--card)] p-8 shadow-2xl backdrop-blur-2xl">
                <div className="flex items-center justify-between pb-6 border-b border-[var(--border)] mb-6">
                  <div className="flex items-center gap-3">
                    <div className="h-10 w-10 rounded-2xl bg-[var(--gold-dim)] border border-[var(--border-hi)] flex items-center justify-center font-serif font-bold text-sm text-[var(--gold)]">
                      🏛️
                    </div>
                    <div>
                      <div className="text-xs font-bold uppercase tracking-widest text-[var(--tx)] font-sans">
                        COMMISSION SEAL #GG-8942
                      </div>
                      <div className="text-[10px] text-[var(--tx-3)]">Cantonments Prime Enterprise</div>
                    </div>
                  </div>
                  <span className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[var(--emerald-dim)] text-[var(--emerald)] border border-[var(--emerald)]">
                    ESCROW PROTECTED
                  </span>
                </div>

                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <span className="text-xs text-[var(--tx-2)]">Contract Milestone</span>
                    <span className="text-base font-bold font-serif text-[var(--tx)]">
                      ₵4,500.00 GHS
                    </span>
                  </div>

                  <div className="p-4 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] space-y-3">
                    <div className="flex items-center justify-between text-xs">
                      <span className="text-[var(--tx-2)]">1. Escrow Deposit:</span>
                      <span className="font-bold text-[var(--emerald)]">✓ Secured in Vault</span>
                    </div>
                    <div className="flex items-center justify-between text-xs">
                      <span className="text-[var(--tx-2)]">2. Deliverable Sign-Off:</span>
                      <span className="font-bold text-[var(--gold)]">⏳ Client Review</span>
                    </div>
                    <div className="flex items-center justify-between text-xs">
                      <span className="text-[var(--tx-2)]">3. MoMo Disbursement:</span>
                      <span className="font-bold text-[var(--tx-3)]">&lt; 60s upon Approval</span>
                    </div>
                  </div>

                  <div className="pt-2 flex items-center justify-between">
                    <div className="flex items-center gap-2.5">
                      <div className="h-8 w-8 rounded-full bg-[var(--gold-dim)] border border-[var(--border-hi)] flex items-center justify-center font-serif font-bold text-xs text-[var(--gold)]">
                        KA
                      </div>
                      <div>
                        <div className="text-xs font-bold text-[var(--tx)]">Kwame Asante</div>
                        <div className="text-[10px] text-[var(--tx-3)]">Master Joiner &amp; Cabinet Maker</div>
                      </div>
                    </div>
                    <div className="text-xs font-bold text-[var(--gold)] flex items-center gap-1">
                      <Star className="w-3.5 h-3.5 fill-[var(--gold)]" />
                      <span>5.0 (48 reviews)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ══════ SOVEREIGN MARKETPLACE PULSE ══════ */}
      <section className="py-12 border-b border-[var(--border)] bg-[var(--bg-subtle)]" ref={statsRef}>
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
            <div className="space-y-1">
              <div className="text-3xl sm:text-4xl font-serif font-bold text-[var(--tx)]">
                {countProviders.toLocaleString()}
              </div>
              <div className="text-xs uppercase tracking-widest text-[var(--tx-3)] font-sans font-bold">
                Verified Master Artisans
              </div>
            </div>

            <div className="space-y-1">
              <div className="text-3xl sm:text-4xl font-serif font-bold text-[var(--gold)]">
                {countJobs.toLocaleString()}
              </div>
              <div className="text-xs uppercase tracking-widest text-[var(--tx-3)] font-sans font-bold">
                Active Commissions
              </div>
            </div>

            <div className="space-y-1">
              <div className="text-3xl sm:text-4xl font-serif font-bold text-[var(--emerald)]">
                {countCompleted.toLocaleString()}
              </div>
              <div className="text-xs uppercase tracking-widest text-[var(--tx-3)] font-sans font-bold">
                Milestones Completed
              </div>
            </div>

            <div className="space-y-1">
              <div className="text-3xl sm:text-4xl font-serif font-bold text-[var(--tx)]">
                ₵{countEarnings.toLocaleString()}K+
              </div>
              <div className="text-xs uppercase tracking-widest text-[var(--tx-3)] font-sans font-bold">
                Escrow Volume Released (GHS)
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ══════ THE SOVEREIGN GUILDS (CATEGORIES) ══════ */}
      <section className="py-24 relative" id="guilds">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--gold-dim)] text-[var(--gold)] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-widest font-sans mb-3">
              <Compass className="w-3.5 h-3.5" />
              <span>THE FIVE SOVEREIGN GUILDS</span>
            </div>
            <h2 className="text-3xl sm:text-5xl font-serif font-bold text-[var(--tx)] tracking-tight">
              Curated Disciplines of African Excellence
            </h2>
            <p className="text-sm text-[var(--tx-2)] mt-3">
              Explore verified practitioners organized into specialized professional guilds.
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {guilds.map((g, idx) => (
              <SpotlightCard
                key={idx}
                spotlightColor="rgba(212, 175, 55, 0.16)"
                className="p-8 flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center justify-between mb-4">
                    <div className="text-3xl">{g.icon}</div>
                    <span className="text-xs font-bold font-mono text-[var(--gold)]">
                      {g.count}
                    </span>
                  </div>
                  <h3 className="text-xl font-serif font-bold text-[var(--tx)] mb-2">
                    {g.title}
                  </h3>
                  <p className="text-xs text-[var(--tx-2)] leading-relaxed mb-6">
                    {g.desc}
                  </p>
                </div>

                <a
                  href={`/search/providers.php?category=${g.slug}`}
                  className="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[var(--gold)] hover:underline pt-4 border-t border-[var(--border)]"
                >
                  <span>Explore Guild Roster</span>
                  <ArrowRight className="w-3.5 h-3.5" />
                </a>
              </SpotlightCard>
            ))}
          </div>
        </div>
      </section>

      {/* ══════ THE ESCROW SANCTUARY (BENTO GRID) ══════ */}
      <section className="py-24 border-t border-[var(--border)] bg-[var(--bg-subtle)]" id="sanctuary">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--gold-dim)] text-[var(--gold)] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-widest font-sans mb-3">
              <ShieldCheck className="w-3.5 h-3.5" />
              <span>THE SOVEREIGN STANDARD</span>
            </div>
            <h2 className="text-3xl sm:text-5xl font-serif font-bold text-[var(--tx)] tracking-tight">
              The Sanctuary of Guaranteed Commerce
            </h2>
            <p className="text-sm text-[var(--tx-2)] mt-3">
              Eliminating financial uncertainty for discerning clients and independent masters alike.
            </p>
          </div>

          <BentoGrid>
            <BentoCard
              title="100% Escrow Vault Guarantee"
              description="Funds are placed into the sovereign Escrow Vault prior to project inception and released only upon explicit client sign-off."
              icon={<Lock className="w-6 h-6 text-[var(--gold)]" />}
              badge="Zero Financial Risk"
              spotlightColor="rgba(212, 175, 55, 0.18)"
              className="md:col-span-2"
              header={
                <div className="p-4 rounded-2xl bg-[var(--surface-2)] border border-[var(--border-hi)] flex items-center justify-between text-xs">
                  <span className="font-bold text-[var(--gold)] flex items-center gap-1.5">
                    <ShieldCheck className="w-4 h-4" /> Vault Active &amp; Insured
                  </span>
                  <span className="text-[var(--tx-3)]">100% Refundable</span>
                </div>
              }
            />

            <BentoCard
              title="Biometric Ghana Card Authentication"
              description="Every practitioner’s identity is validated through official Ghana Card biometrics, establishing an irreproachable foundation of trust."
              icon={<Award className="w-6 h-6 text-[var(--emerald)]" />}
              badge="Biometric Seal"
              spotlightColor="rgba(16, 185, 129, 0.18)"
            />

            <BentoCard
              title="Sub-60s Multi-Channel Settlement"
              description="Direct integration with MTN MoMo, Telecel Cash, AT Money, and domestic banks ensures lightning-fast disbursement."
              icon={<Smartphone className="w-6 h-6 text-[var(--gold)]" />}
              badge="Instant Settlement"
              spotlightColor="rgba(212, 175, 55, 0.18)"
            />

            <BentoCard
              title="Sovereign Membership Tiers"
              description="Transparent subscription models: Initiate (Free · 3 commissions), Guild Verified (₵49/mo · Unlimited), and Sovereign Master (₵99/mo · Priority placement)."
              icon={<Gem className="w-6 h-6 text-[var(--violet)]" />}
              badge="Transparent Dues"
              spotlightColor="rgba(139, 92, 246, 0.18)"
              className="md:col-span-2"
              header={
                <div className="grid grid-cols-3 gap-2 p-3 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] text-center text-xs">
                  <div className="p-2 rounded-xl bg-[var(--surface)]">
                    <div className="font-bold text-[var(--tx)]">Initiate</div>
                    <div className="text-[10px] text-[var(--tx-3)]">Free · 3 Gigs</div>
                  </div>
                  <div className="p-2 rounded-xl bg-[var(--gold-dim)] border border-[var(--border-hi)]">
                    <div className="font-bold text-[var(--gold)]">Verified ✓</div>
                    <div className="text-[10px] text-[var(--gold)]">₵49/mo</div>
                  </div>
                  <div className="p-2 rounded-xl bg-[var(--surface)]">
                    <div className="font-bold text-[var(--tx)]">Master ⭐</div>
                    <div className="text-[10px] text-[var(--tx-3)]">₵99/mo</div>
                  </div>
                </div>
              }
            />
          </BentoGrid>
        </div>
      </section>

      {/* ══════ PRIVATE PRACTICE ESTIMATOR ══════ */}
      <section className="py-24 relative border-t border-[var(--border)]" id="estimator">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <LuxuryEstimator />
        </div>
      </section>

      {/* ══════ THE MASTER ARTISAN DIRECTORY ══════ */}
      <section className="py-24 border-t border-[var(--border)] bg-[var(--bg-subtle)]" id="artisans">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16">
            <div>
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--gold-dim)] text-[var(--gold)] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-widest font-sans mb-3">
                <Users className="w-3.5 h-3.5" />
                <span>DISTINGUISHED PRACTITIONERS</span>
              </div>
              <h2 className="text-3xl sm:text-5xl font-serif font-bold text-[var(--tx)] tracking-tight">
                The Master Artisan Roster
              </h2>
            </div>
            <a
              href="/search/providers.php"
              className="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--gold)] hover:underline"
            >
              <span>View Full Roster</span>
              <ArrowRight className="w-4 h-4" />
            </a>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {(featured.length > 0 ? featured : matchedProviders).slice(0, 6).map((pv, idx) => {
              const skills = pv.skill_names ? pv.skill_names.split('|').filter(Boolean) : [];
              const init = initials(pv.first_name, pv.last_name);
              const jobs = Number(pv.completed_jobs || 0);
              const rating = Number(pv.rating_avg || 5);

              return (
                <SpotlightCard
                  key={idx}
                  spotlightColor="rgba(212, 175, 55, 0.18)"
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
                            <div className="h-14 w-14 rounded-2xl bg-gradient-to-tr from-[var(--gold-light)] via-[var(--gold)] to-[#996515] flex items-center justify-center font-serif font-bold text-base text-[#090A0F]">
                              {init}
                            </div>
                          )}
                          {pv.is_verified ? (
                            <span className="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-[var(--gold)] text-[9px] text-[#090A0F] font-bold">
                              ✓
                            </span>
                          ) : null}
                        </div>
                        <div>
                          <div className="text-lg font-serif font-bold text-[var(--tx)]">
                            {pv.first_name} {pv.last_name}
                          </div>
                          <div className="text-xs text-[var(--tx-3)] flex items-center gap-1">
                            <MapPin className="w-3 h-3 text-[var(--gold)]" />
                            <span>{pv.location || 'Accra Prime, Ghana'}</span>
                          </div>
                        </div>
                      </div>

                      <span className="px-2.5 py-1 rounded-full text-[10px] font-bold font-mono bg-[var(--gold-dim)] text-[var(--gold)] border border-[var(--border-hi)]">
                        {jobs >= 20 ? '⭐ Fellow' : 'Initiate'}
                      </span>
                    </div>

                    <p className="text-xs text-[var(--tx-2)] line-clamp-2 mb-4 leading-relaxed font-sans">
                      {pv.tagline || `${pv.experience_level ? pv.experience_level.charAt(0).toUpperCase() + pv.experience_level.slice(1) : 'Master'} practitioner available for selective commissions.`}
                    </p>

                    <div className="flex items-center gap-1.5 text-xs font-bold text-[var(--gold)] mb-4">
                      <Star className="w-3.5 h-3.5 fill-[var(--gold)]" />
                      <span>{rating.toFixed(1)}</span>
                      <span className="text-[var(--tx-3)] font-normal">({Number(pv.rating_count || 0)} endorsements)</span>
                      <span className="text-[var(--border)] mx-1">·</span>
                      <span className="text-[var(--tx-3)] font-normal">{jobs} commissions delivered</span>
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
                      <div className="text-[10px] uppercase tracking-wider text-[var(--tx-3)]">Retainer Rate</div>
                      <div className="text-base font-serif font-bold text-[var(--tx)]">
                        {pv.hourly_rate > 0 ? (
                          <>
                            {formatCurrency(pv.hourly_rate)}
                            <span className="text-xs text-[var(--tx-3)] font-sans font-normal">/hr</span>
                          </>
                        ) : (
                          'By Proposal'
                        )}
                      </div>
                    </div>

                    <a
                      href={`/profile.php?id=${pv.user_id}`}
                      className="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-[#090A0F] bg-gradient-to-r from-[var(--gold-light)] via-[var(--gold)] to-[#B8860B] hover:brightness-110 transition-all font-sans"
                    >
                      Commission
                    </a>
                  </div>
                </SpotlightCard>
              );
            })}
          </div>
        </div>
      </section>

      {/* ══════ COMMISSIONS BOARD (RECENT JOBS) ══════ */}
      <section className="py-24 relative border-t border-[var(--border)]" id="commissions">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-16">
            <div>
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--gold-dim)] text-[var(--gold)] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-widest font-sans mb-3">
                <Briefcase className="w-3.5 h-3.5" />
                <span>OPEN COMMISSIONS</span>
              </div>
              <h2 className="text-3xl sm:text-5xl font-serif font-bold text-[var(--tx)] tracking-tight">
                Distinguished Client Engagements
              </h2>
            </div>
            <a
              href="/jobs.php"
              className="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[var(--gold)] hover:underline"
            >
              <span>View All Commissions</span>
              <ArrowRight className="w-4 h-4" />
            </a>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            {recentJobs.slice(0, 6).map((job) => (
              <SpotlightCard
                key={job.id}
                spotlightColor="rgba(212, 175, 55, 0.16)"
                className="p-8 flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-start justify-between gap-3 mb-4">
                    <span className="text-xs font-bold text-[var(--gold)] flex items-center gap-1.5 uppercase tracking-wider">
                      <span>{iconMap[job.cat_icon] || '🏛️'}</span>
                      <span>{job.cat_name || 'General Guild'}</span>
                    </span>
                    <span
                      className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold ${
                        job.is_urgent
                          ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30'
                          : 'bg-[var(--emerald-dim)] text-[var(--emerald)] border border-[var(--emerald)]'
                      }`}
                    >
                      {job.is_urgent ? '🔥 Priority' : '● Open'}
                    </span>
                  </div>

                  <h3 className="text-lg font-serif font-bold text-[var(--tx)] mb-2 line-clamp-2 leading-snug">
                    {job.title}
                  </h3>

                  <p className="text-xs text-[var(--tx-2)] line-clamp-3 mb-6 leading-relaxed">
                    {job.description}
                  </p>

                  <div className="flex items-center gap-4 text-xs text-[var(--tx-3)] mb-6 font-mono">
                    <span className="flex items-center gap-1">
                      <Clock className="w-3 h-3 text-[var(--gold)]" />
                      <span>{timeAgo(job.created_at)}</span>
                    </span>
                    <span>{Number(job.proposal_count || 0)} submissions</span>
                  </div>
                </div>

                <div className="pt-6 border-t border-[var(--border)] flex items-center justify-between">
                  <div>
                    <div className="text-[10px] uppercase tracking-wider text-[var(--tx-3)]">Retainer Budget</div>
                    <div className="text-base font-serif font-bold text-[var(--emerald)]">
                      {formatCurrency(job.budget_min)}
                      {job.budget_max > job.budget_min ? ` - ${formatCurrency(job.budget_max)}` : ''}
                    </div>
                  </div>

                  <a
                    href={`/job-details.php?id=${job.id}`}
                    className="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-[var(--tx)] bg-[var(--surface-2)] border border-[var(--border)] hover:border-[var(--border-hi)] hover:text-[var(--gold)] transition-colors"
                  >
                    Submit Proposal
                  </a>
                </div>
              </SpotlightCard>
            ))}
          </div>
        </div>
      </section>

      {/* ══════ VOICES OF DISTINCTION (TESTIMONIALS) ══════ */}
      <section className="py-24 border-t border-[var(--border)] bg-[var(--bg-subtle)]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--gold-dim)] text-[var(--gold)] border border-[var(--border-hi)] text-xs font-bold uppercase tracking-widest font-sans mb-3">
              <Star className="w-3.5 h-3.5 fill-[var(--gold)]" />
              <span>TESTIMONIAL SALON</span>
            </div>
            <h2 className="text-3xl sm:text-5xl font-serif font-bold text-[var(--tx)] tracking-tight">
              Voices of the Sovereign Community
            </h2>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {reviews.map((rv, idx) => (
              <SpotlightCard
                key={idx}
                spotlightColor="rgba(212, 175, 55, 0.14)"
                className="p-7 flex flex-col justify-between"
              >
                <div>
                  <div className="flex items-center gap-1 text-[var(--gold)] mb-4">
                    {[1, 2, 3, 4, 5].map((s) => (
                      <Star key={s} className="w-3.5 h-3.5 fill-[var(--gold)]" />
                    ))}
                  </div>
                  <p className="text-xs text-[var(--tx)] italic font-serif leading-relaxed mb-6">
                    &ldquo;{rv.comment}&rdquo;
                  </p>
                </div>

                <div className="flex items-center gap-3 pt-4 border-t border-[var(--border)]">
                  <div className="h-9 w-9 rounded-full bg-[var(--gold-dim)] border border-[var(--border-hi)] flex items-center justify-center font-serif font-bold text-xs text-[var(--gold)]">
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
      <div className="py-12 border-t border-[var(--border)] bg-[var(--bg)]">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center text-xs font-bold uppercase tracking-widest text-[var(--tx-3)] mb-8 font-sans">
            Sovereign Settlement &amp; Technology Partners
          </div>

          <Marquee pauseOnHover className="[--duration:30s]">
            <div className="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] text-xs font-bold text-[var(--tx)]">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/MTN_Logo.svg/512px-MTN_Logo.svg.png"
                alt="MTN MoMo"
                className="h-6 w-auto object-contain"
              />
              <span>MTN Mobile Money</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] text-xs font-bold text-[var(--tx)]">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Vodafone_icon.svg/512px-Vodafone_icon.svg.png"
                alt="Telecel Cash"
                className="h-6 w-auto object-contain"
              />
              <span>Telecel Cash</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] text-xs font-bold text-[var(--coral)]">
              <span>📶 AirtelTigo Money</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] text-xs font-bold text-[var(--tx)]">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Paystack_logo.png"
                alt="Paystack"
                className="h-5 w-auto object-contain"
              />
              <span>Paystack Verified</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] text-xs font-bold text-[var(--tx)]">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/800px-Visa_Inc._logo.svg.png"
                alt="Visa"
                className="h-4 w-auto object-contain"
              />
              <span>Visa Premium</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-[var(--surface-2)] border border-[var(--border)] text-xs font-bold text-[var(--tx)]">
              <img
                src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/800px-Mastercard-logo.svg.png"
                alt="Mastercard"
                className="h-5 w-auto object-contain"
              />
              <span>Mastercard</span>
            </div>

            <div className="flex items-center gap-3 px-6 py-3.5 rounded-2xl bg-[var(--surface-2)] border border-[var(--border-hi)] text-xs font-bold text-[var(--gold)]">
              <ShieldCheck className="w-5 h-5" />
              <span>Smart Escrow Sanctuary</span>
            </div>
          </Marquee>
        </div>
      </div>

      {/* ══════ PRIVATE INVITATION CTA ══════ */}
      <section className="py-24 border-t border-[var(--border)] relative overflow-hidden bg-[var(--bg-subtle)]">
        <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="relative rounded-3xl border border-[var(--border-hi)] bg-[var(--surface)] p-12 sm:p-20 text-center shadow-2xl">
            <div className="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[var(--gold-dim)] border border-[var(--border-hi)] text-xs font-bold text-[var(--gold)] mb-6">
              <Gem className="w-4 h-4" />
              <span>THE SOVEREIGN INVITATION</span>
            </div>

            <h2 className="text-4xl sm:text-6xl font-serif font-bold text-[var(--tx)] tracking-tight max-w-2xl mx-auto leading-tight mb-6">
              Enter Ghana&apos;s Sovereign Economy
            </h2>

            <p className="text-sm sm:text-base text-[var(--tx-2)] max-w-xl mx-auto mb-10 leading-relaxed">
              Create your account in moments. 3 complimentary commission submissions for master artisans. 100% escrow peace of mind for clients.
            </p>

            <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
              <a
                href="/auth/register.php?role=provider"
                onClick={triggerGoldConfetti}
                className="w-full sm:w-auto px-8 py-4 rounded-2xl font-bold text-xs uppercase tracking-wider text-[#090A0F] bg-gradient-to-r from-[var(--gold-light)] via-[var(--gold)] to-[#B8860B] hover:brightness-110 shadow-xl shadow-gold/25 transition-all font-sans"
              >
                Join as a Master Artisan
              </a>

              <a
                href="/auth/register.php?role=client"
                onClick={triggerGoldConfetti}
                className="w-full sm:w-auto px-8 py-4 rounded-2xl font-bold text-xs uppercase tracking-wider text-[var(--tx)] bg-[var(--surface-2)] border border-[var(--border-hi)] hover:bg-[var(--gold-dim)] transition-all font-sans"
              >
                Commission Talent
              </a>
            </div>
          </div>
        </div>
      </section>

      {/* ══════ SOVEREIGN FOOTER ══════ */}
      <footer className="border-t border-[var(--border)] bg-[var(--bg)] pt-16 pb-12">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-1 md:grid-cols-5 gap-10 pb-12 border-b border-[var(--border)]">
            <div className="md:col-span-2 space-y-4">
              <a href="/" className="flex items-center gap-2.5">
                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-[var(--gold-light)] via-[var(--gold)] to-[#996515] font-serif font-bold text-xs text-[#090A0F]">
                  GG
                </div>
                <span className="font-serif font-bold text-lg text-[var(--tx)]">
                  Gig<span className="text-gold-gradient">Ghana</span>
                </span>
              </a>
              <p className="text-xs text-[var(--tx-2)] leading-relaxed max-w-sm">
                Ghana&apos;s sovereign marketplace connecting verified master artisans and practitioners with clients through biometric trust and guaranteed escrow sanctuary.
              </p>

              {/* Newsletter */}
              <form onSubmit={handleSubscribeNL} className="pt-2 flex max-w-sm gap-2">
                <input
                  type="email"
                  placeholder="Enter your email"
                  value={nlEmail}
                  onChange={(e) => setNlEmail(e.target.value)}
                  className="w-full px-4 py-2 rounded-xl bg-[var(--surface-2)] border border-[var(--border)] text-xs text-[var(--tx)] placeholder-[var(--tx-3)] focus:outline-none focus:border-[var(--gold)]"
                />
                <button
                  type="submit"
                  className="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-[#090A0F] bg-[var(--gold)] hover:bg-[var(--gold-light)] transition-colors shrink-0"
                >
                  Subscribe
                </button>
              </form>
            </div>

            <div>
              <div className="text-xs font-bold uppercase tracking-widest text-[var(--gold)] font-sans mb-4">
                The Guilds
              </div>
              <ul className="space-y-2.5 text-xs text-[var(--tx-2)]">
                <li><a href="/search/providers.php" className="hover:text-[var(--gold)] transition-colors">Technology Guild</a></li>
                <li><a href="/search/providers.php" className="hover:text-[var(--gold)] transition-colors">Craftsmen Guild</a></li>
                <li><a href="/search/providers.php" className="hover:text-[var(--gold)] transition-colors">Culinary Guild</a></li>
                <li><a href="/search/providers.php" className="hover:text-[var(--gold)] transition-colors">Health Guild</a></li>
              </ul>
            </div>

            <div>
              <div className="text-xs font-bold uppercase tracking-widest text-[var(--gold)] font-sans mb-4">
                Sanctuary
              </div>
              <ul className="space-y-2.5 text-xs text-[var(--tx-2)]">
                <li><a href="#sanctuary" className="hover:text-[var(--gold)] transition-colors">Escrow Vault</a></li>
                <li><a href="#sanctuary" className="hover:text-[var(--gold)] transition-colors">Biometric Trust</a></li>
                <li><a href="#estimator" className="hover:text-[var(--gold)] transition-colors">Estimator</a></li>
                <li><a href="#" className="hover:text-[var(--gold)] transition-colors">Membership Dues</a></li>
              </ul>
            </div>

            <div>
              <div className="text-xs font-bold uppercase tracking-widest text-[var(--gold)] font-sans mb-4">
                Trust &amp; Legal
              </div>
              <ul className="space-y-2.5 text-xs text-[var(--tx-2)]">
                <li><a href="/privacy.php" className="hover:text-[var(--gold)] transition-colors">Privacy Charter</a></li>
                <li><a href="/terms.php" className="hover:text-[var(--gold)] transition-colors">Terms of Service</a></li>
                <li><a href="#" className="hover:text-[var(--gold)] transition-colors">Dispute Resolution</a></li>
                <li><a href="#" className="hover:text-[var(--gold)] transition-colors">Ghana Registry</a></li>
              </ul>
            </div>
          </div>

          <div className="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[var(--tx-3)]">
            <div>
              © {new Date().getFullYear()} GigGhana Ltd. Sovereign Edition. Made with ❤️ in Ghana 🇬🇭
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
              {t.type === 'success' ? '🏛️' : t.type === 'error' ? '❌' : t.type === 'warning' ? '⚠️' : 'ℹ️'}
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
