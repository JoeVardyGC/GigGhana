'use client';

import React, { useState, useMemo, Suspense } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { useSearchParams } from 'next/navigation';
import { SiteHeader } from '@/components/layout/SiteHeader';
import { SiteFooter } from '@/components/layout/SiteFooter';
import { useAuth } from '@/lib/context/AuthContext';
import { fallbackFeaturedProviders } from '@/lib/types';
import confetti from 'canvas-confetti';
import {
  Search,
  MapPin,
  ShieldCheck,
  Star,
  Zap,
  SlidersHorizontal,
  ArrowRight,
  CheckCircle2,
  Phone,
  MessageSquare,
  Award,
  DollarSign,
  X,
  Sparkles,
  Send,
  Briefcase,
  Check,
} from 'lucide-react';
import { WhatsAppIcon } from '@/components/ui/social-icons';

interface ProviderItem {
  id: number;
  user_id: number;
  first_name: string;
  last_name: string;
  tagline: string;
  bio: string;
  cat_name: string;
  cat_icon: string;
  location: string;
  avatar: string;
  is_verified: number;
  badge: string;
  rating_avg: number;
  rating_count: number;
  completed_jobs: number;
  hourly_rate: number;
  skill_names: string;
  availability: string;
}

// Additional high-rated Ghanaian verified providers
const extendedProviders: ProviderItem[] = [
  ...fallbackFeaturedProviders,
  {
    id: 7,
    user_id: 7,
    first_name: 'Kweku',
    last_name: 'Mensah',
    tagline: 'Lead Architectural 3D Modeler & Municipality Permit Draftsman',
    bio: 'Over 10 years experience creating approved permit drawings, 3D structural renders, and quantity surveyor bills of quantities for luxury villas in Airport Residential and Kumasi.',
    cat_name: 'Construction',
    cat_icon: 'briefcase',
    location: 'Airport Residential, Accra',
    avatar: '/images/occupations/architect.jpg',
    is_verified: 1,
    badge: '⭐ Verified Master',
    rating_avg: 4.9,
    rating_count: 41,
    completed_jobs: 38,
    hourly_rate: 110,
    skill_names: 'Architectural Drawings|3D CAD Modeling|Permit Approvals',
    availability: 'full_time',
  },
  {
    id: 8,
    user_id: 8,
    first_name: 'Yaa',
    last_name: 'Asantewaa',
    tagline: 'Master Tailor & Traditional Kente Haute Couture Designer',
    bio: 'Specialist in custom hand-woven Bonwire Kente ceremonial garments, bespoke bridal gowns, and luxury African corporate fashion with international shipping experience.',
    cat_name: 'Creative Arts',
    cat_icon: 'pen-tool',
    location: 'Kumasi Central, Ashanti',
    avatar: '/images/avatars/avatar_female_2.jpg',
    is_verified: 1,
    badge: '👑 Elite Artisan',
    rating_avg: 5.0,
    rating_count: 57,
    completed_jobs: 52,
    hourly_rate: 90,
    skill_names: 'Bespoke Kente|Haute Couture|Bridal Wear',
    availability: 'full_time',
  },
  {
    id: 9,
    user_id: 9,
    first_name: 'Daniel',
    last_name: 'Quaye',
    tagline: 'Master Structural Mason & Precision Porcelain Tiler',
    bio: 'Expert in large-format porcelain tile laying, epoxy grouting, structural retaining walls, and waterproof swimming pool foundations across Greater Accra and Central Region.',
    cat_name: 'Construction',
    cat_icon: 'briefcase',
    location: 'Tema Comm. 6, Greater Accra',
    avatar: '/images/occupations/building_contractor.jpg',
    is_verified: 1,
    badge: '⭐ Premium Pro',
    rating_avg: 4.9,
    rating_count: 44,
    completed_jobs: 39,
    hourly_rate: 85,
    skill_names: 'Porcelain Tiling|Masonry|Epoxy Grouting',
    availability: 'full_time',
  },
  {
    id: 10,
    user_id: 10,
    first_name: 'Sena',
    last_name: 'Agbesi',
    tagline: 'Senior UI/UX & Web Interaction Designer',
    bio: 'Crafting responsive, high-converting digital products, design systems, and mobile wireframes tailored for African fintech and enterprise tech startups.',
    cat_name: 'IT & Tech',
    cat_icon: 'code',
    location: 'Osu, Accra',
    avatar: '/images/avatars/avatar_female_1.jpg',
    is_verified: 1,
    badge: '⭐ Verified Pro',
    rating_avg: 4.8,
    rating_count: 28,
    completed_jobs: 24,
    hourly_rate: 95,
    skill_names: 'Figma|UI/UX Systems|Web Design',
    availability: 'full_time',
  },
];

const REGIONS = [
  'All Ghana',
  'Greater Accra',
  'Ashanti',
  'Western',
  'Central',
  'Eastern',
  'Northern',
  'Tema',
];

function ProvidersDirectoryContent() {
  const searchParams = useSearchParams();
  const { user, isAuthenticated, loginDemoUser } = useAuth();

  const initialCat = searchParams.get('category') || searchParams.get('cat') || 'all';
  const initialQuery = searchParams.get('q') || '';
  const initialRegion = searchParams.get('region') || searchParams.get('loc') || 'All Ghana';

  const [searchQuery, setSearchQuery] = useState(initialQuery);
  const [selectedCat, setSelectedCat] = useState(initialCat);
  const [selectedRegion, setSelectedRegion] = useState(initialRegion);
  const [verifiedOnly, setVerifiedOnly] = useState(true);
  const [minRating, setMinRating] = useState<number>(0);
  const [sortBy, setSortBy] = useState<'rating' | 'jobs' | 'rate_low' | 'rate_high'>('rating');

  // Modal State for Direct Hire / Milestone Contract
  const [activeProviderForHire, setActiveProviderForHire] = useState<ProviderItem | null>(null);
  const [projectTitle, setProjectTitle] = useState('');
  const [projectBudget, setProjectBudget] = useState(3500);
  const [projectNotes, setProjectNotes] = useState('');
  const [isSubmittingHire, setIsSubmittingHire] = useState(false);
  const [hireSuccess, setHireSuccess] = useState(false);

  // Chat Simulation State
  const [chatProvider, setChatProvider] = useState<ProviderItem | null>(null);
  const [chatMessage, setChatMessage] = useState('');
  const [chatHistory, setChatHistory] = useState<Array<{ sender: 'client' | 'provider'; text: string; time: string }>>([]);

  // Filter & Sort Providers
  const filteredProviders = useMemo(() => {
    return extendedProviders
      .filter((prov) => {
        // Query filter
        if (searchQuery.trim()) {
          const q = searchQuery.toLowerCase();
          const fullName = `${prov.first_name} ${prov.last_name}`.toLowerCase();
          const matchesName = fullName.includes(q);
          const matchesTagline = prov.tagline.toLowerCase().includes(q);
          const matchesBio = prov.bio.toLowerCase().includes(q);
          const matchesSkills = prov.skill_names.toLowerCase().includes(q);
          const matchesLoc = prov.location.toLowerCase().includes(q);
          if (!matchesName && !matchesTagline && !matchesBio && !matchesSkills && !matchesLoc) {
            return false;
          }
        }

        // Category filter
        if (selectedCat !== 'all') {
          const cLower = selectedCat.toLowerCase();
          const pCat = prov.cat_name.toLowerCase();
          if (cLower === 'trades' && !pCat.includes('trades')) return false;
          if (cLower === 'tech' && !pCat.includes('tech') && !pCat.includes('it')) return false;
          if (cLower === 'design' && !pCat.includes('creative') && !pCat.includes('art')) return false;
          if (cLower === 'build' && !pCat.includes('construction')) return false;
        }

        // Region filter
        if (selectedRegion !== 'All Ghana') {
          if (!prov.location.toLowerCase().includes(selectedRegion.toLowerCase())) {
            return false;
          }
        }

        // Ghana Card Verified Only
        if (verifiedOnly && !prov.is_verified) return false;

        // Min Rating
        if (minRating > 0 && prov.rating_avg < minRating) return false;

        return true;
      })
      .sort((a, b) => {
        if (sortBy === 'rating') return b.rating_avg - a.rating_avg;
        if (sortBy === 'jobs') return b.completed_jobs - a.completed_jobs;
        if (sortBy === 'rate_low') return a.hourly_rate - b.hourly_rate;
        if (sortBy === 'rate_high') return b.hourly_rate - a.hourly_rate;
        return 0;
      });
  }, [searchQuery, selectedCat, selectedRegion, verifiedOnly, minRating, sortBy]);

  const handleOpenHire = (prov: ProviderItem) => {
    setActiveProviderForHire(prov);
    setProjectTitle(`Contract with ${prov.first_name} ${prov.last_name}`);
    setProjectBudget(prov.hourly_rate * 40); // 40h typical milestone baseline
    setProjectNotes(
      `Hello ${prov.first_name}, I would like to hire your verified services for our upcoming project. Funds will be deposited in the GigGhana Escrow Vault before commencement.`
    );
    setHireSuccess(false);
  };

  const handleOpenChat = (prov: ProviderItem) => {
    setChatProvider(prov);
    setChatHistory([
      {
        sender: 'provider',
        text: `Hello! I am ${prov.first_name}, a verified ${prov.tagline.split('&')[0]}. How can I assist you with your project today?`,
        time: 'Just now',
      },
    ]);
  };

  const handleSendChat = (e: React.FormEvent) => {
    e.preventDefault();
    if (!chatMessage.trim() || !chatProvider) return;

    const userText = chatMessage;
    setChatMessage('');
    setChatHistory((prev) => [
      ...prev,
      { sender: 'client', text: userText, time: 'Just now' },
    ]);

    setTimeout(() => {
      setChatHistory((prev) => [
        ...prev,
        {
          sender: 'provider',
          text: `Thank you for reaching out! I am available for immediate dispatch in ${chatProvider.location}. Please send your project brief or submit a formal escrow milestone contract so we can get started right away!`,
          time: 'Just now',
        },
      ]);
    }, 1000);
  };

  const handleHireSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeProviderForHire) return;

    setIsSubmittingHire(true);
    await new Promise((r) => setTimeout(r, 900));
    setIsSubmittingHire(false);
    setHireSuccess(true);

    try {
      confetti({
        particleCount: 80,
        spread: 60,
        origin: { y: 0.6 },
        colors: ['#00D4C8', '#F59E0B', '#10B981'],
      });
    } catch (_) {}
  };

  return (
    <div className="min-h-screen bg-[var(--bg)] text-[var(--tx)] flex flex-col font-body transition-colors">
      <SiteHeader activeTab="providers" />

      {/* Main Container */}
      <main className="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20 w-full">
        
        {/* Hero Section */}
        <div className="relative mb-10 overflow-hidden rounded-3xl border border-[var(--cyan-border)] bg-gradient-to-br from-[var(--cyan-dim)] via-[var(--surface)] to-[var(--gold-dim)] p-8 sm:p-12 shadow-sm">
          <div className="max-w-3xl relative z-10">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--cyan-dim)] border border-[var(--cyan-border)] text-[var(--cyan)] font-bold text-xs uppercase tracking-wider mb-4">
              <ShieldCheck className="w-3.5 h-3.5" />
              <span>National Identity Verified Talent Directory 🇬🇭</span>
            </div>
            <h1 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[var(--tx)] font-heading tracking-tight leading-tight">
              Hire Ghana Card Verified Master Artisans & Pros
            </h1>
            <p className="mt-4 text-sm sm:text-base text-[var(--tx-2)] leading-relaxed">
              Eliminate artisan risk with verified biometric identity credentials and guaranteed escrow milestones. Every tradesman and contractor listed below has been verified with standard National ID checks (`GHA-XXXXXXXXX-X`).
            </p>

            {/* Quick Metrics Bar */}
            <div className="mt-8 flex flex-wrap items-center gap-6 pt-6 border-t border-[var(--bd)] text-xs text-[var(--tx-2)] font-mono">
              <div className="flex items-center gap-2">
                <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" />
                <span className="font-bold text-[var(--tx)]">2,840+</span> Verified Artisans Active
              </div>
              <div className="flex items-center gap-2">
                <Star className="w-4 h-4 text-amber-400 fill-amber-400" />
                <span className="font-bold text-[var(--tx)]">4.9/5</span> Average Client Rating
              </div>
              <div className="flex items-center gap-2">
                <Zap className="w-4 h-4 text-[var(--gold)]" />
                <span className="font-bold text-[var(--tx)]">Sub-60s</span> MoMo Milestone Settlement
              </div>
            </div>
          </div>
        </div>

        {/* Search & Filter Control Bar */}
        <div className="mb-8 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-12 gap-3">
            
            {/* Search Input */}
            <div className="md:col-span-5 relative">
              <Search className="w-4 h-4 text-[var(--tx-3)] absolute left-4 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search by name, trade, or skill (e.g. POP ceiling, solar, plumber)..."
                className="w-full pl-11 pr-4 py-3 rounded-2xl border border-[var(--bd)] bg-[var(--surface)] text-[var(--tx)] placeholder:text-[var(--tx-3)] text-sm focus:outline-none focus:border-[var(--cyan)] focus:ring-1 focus:ring-[var(--cyan)] transition-all shadow-sm"
              />
              {searchQuery && (
                <button
                  onClick={() => setSearchQuery('')}
                  className="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-[var(--tx-3)] hover:text-[var(--tx)]"
                >
                  ✕
                </button>
              )}
            </div>

            {/* Region Selector */}
            <div className="md:col-span-3">
              <div className="relative">
                <MapPin className="w-4 h-4 text-[var(--tx-3)] absolute left-4 top-1/2 -translate-y-1/2" />
                <select
                  value={selectedRegion}
                  onChange={(e) => setSelectedRegion(e.target.value)}
                  className="w-full pl-11 pr-8 py-3 rounded-2xl border border-[var(--bd)] bg-[var(--surface)] text-[var(--tx)] text-sm appearance-none focus:outline-none focus:border-[var(--cyan)] transition-all shadow-sm cursor-pointer"
                >
                  {REGIONS.map((reg) => (
                    <option key={reg} value={reg} className="bg-[var(--surface)]">
                      {reg}
                    </option>
                  ))}
                </select>
                <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-xs text-[var(--tx-3)]">
                  ▼
                </div>
              </div>
            </div>

            {/* Sort Order */}
            <div className="md:col-span-4">
              <select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value as any)}
                className="w-full px-4 py-3 rounded-2xl border border-[var(--bd)] bg-[var(--surface)] text-[var(--tx)] text-sm appearance-none focus:outline-none focus:border-[var(--cyan)] transition-all shadow-sm cursor-pointer"
              >
                <option value="rating" className="bg-[var(--surface)]">Sort: Highest Rated First</option>
                <option value="jobs" className="bg-[var(--surface)]">Sort: Most Projects Completed</option>
                <option value="rate_low" className="bg-[var(--surface)]">Sort: Rate (Low to High)</option>
                <option value="rate_high" className="bg-[var(--surface)]">Sort: Rate (High to Low)</option>
              </select>
            </div>

          </div>

          {/* Category Pills & Ghana Card Verified Filter */}
          <div className="flex flex-wrap items-center justify-between gap-3 pt-2">
            
            {/* Category Pills */}
            <div className="flex flex-wrap items-center gap-1.5 overflow-x-auto pb-1 max-w-full">
              <button
                onClick={() => setSelectedCat('all')}
                className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 ${
                  selectedCat === 'all'
                    ? 'bg-[var(--tx)] text-[var(--bg)] shadow-sm'
                    : 'bg-[var(--surface)] border border-[var(--bd)] text-[var(--tx-2)] hover:border-[var(--cyan)]'
                }`}
              >
                All Trades
              </button>
              <button
                onClick={() => setSelectedCat('trades')}
                className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 ${
                  selectedCat === 'trades'
                    ? 'bg-[var(--gold)] text-black shadow-sm'
                    : 'bg-[var(--surface)] border border-[var(--bd)] text-[var(--tx-2)] hover:border-[var(--gold)]'
                }`}
              >
                🔧 Skilled Trades
              </button>
              <button
                onClick={() => setSelectedCat('build')}
                className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 ${
                  selectedCat === 'build'
                    ? 'bg-amber-600 text-white shadow-sm'
                    : 'bg-[var(--surface)] border border-[var(--bd)] text-[var(--tx-2)] hover:border-amber-500'
                }`}
              >
                🏗️ Building & Masonry
              </button>
              <button
                onClick={() => setSelectedCat('tech')}
                className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 ${
                  selectedCat === 'tech'
                    ? 'bg-[var(--cyan)] text-black shadow-sm'
                    : 'bg-[var(--surface)] border border-[var(--bd)] text-[var(--tx-2)] hover:border-[var(--cyan)]'
                }`}
              >
                💻 IT & Software
              </button>
              <button
                onClick={() => setSelectedCat('design')}
                className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 ${
                  selectedCat === 'design'
                    ? 'bg-violet-600 text-white shadow-sm'
                    : 'bg-[var(--surface)] border border-[var(--bd)] text-[var(--tx-2)] hover:border-violet-500'
                }`}
              >
                🎨 Creative & Fashion
              </button>
            </div>

            {/* Toggles */}
            <div className="flex items-center gap-4 text-xs font-medium text-[var(--tx-2)]">
              <label className="flex items-center gap-1.5 cursor-pointer select-none">
                <input
                  type="checkbox"
                  checked={verifiedOnly}
                  onChange={(e) => setVerifiedOnly(e.target.checked)}
                  className="rounded border-[var(--bd)] text-[var(--cyan)] focus:ring-[var(--cyan)]"
                />
                <span className="flex items-center gap-1 font-bold text-[var(--cyan)]">
                  <ShieldCheck className="w-3.5 h-3.5" />
                  Ghana Card Verified Only
                </span>
              </label>

              <select
                value={minRating}
                onChange={(e) => setMinRating(Number(e.target.value))}
                className="px-2.5 py-1 rounded-xl border border-[var(--bd)] bg-[var(--surface)] text-xs text-[var(--tx)]"
              >
                <option value={0}>All Ratings</option>
                <option value={4.8}>⭐ 4.8 & Above</option>
                <option value={4.5}>⭐ 4.5 & Above</option>
              </select>
            </div>

          </div>
        </div>

        {/* Directory Count */}
        <div className="mb-6 flex items-center justify-between text-xs text-[var(--tx-3)]">
          <div>
            Showing <strong className="text-[var(--tx)] font-bold">{filteredProviders.length}</strong> verified master artisans
            {selectedRegion !== 'All Ghana' && ` located in ${selectedRegion}`}
          </div>
          <Link
            href="/auth/register?role=provider"
            className="flex items-center gap-1.5 text-xs font-bold text-[var(--gold)] hover:underline"
          >
            <span>Register as an Artisan</span>
            <ArrowRight className="w-3.5 h-3.5" />
          </Link>
        </div>

        {/* Providers Cards Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredProviders.length === 0 ? (
            <div className="col-span-full py-16 text-center rounded-3xl border border-[var(--bd)] bg-[var(--surface)] p-8">
              <ShieldCheck className="w-12 h-12 text-[var(--tx-3)] mx-auto mb-3 opacity-40" />
              <h3 className="font-bold text-base text-[var(--tx)]">No matching verified artisans found</h3>
              <p className="text-xs text-[var(--tx-2)] mt-1 max-w-sm mx-auto">
                Try widening your search terms or toggling the location filter to All Ghana.
              </p>
              <button
                onClick={() => {
                  setSearchQuery('');
                  setSelectedCat('all');
                  setSelectedRegion('All Ghana');
                  setVerifiedOnly(false);
                  setMinRating(0);
                }}
                className="mt-4 px-4 py-2 rounded-xl bg-[var(--surface-2)] text-xs font-bold hover:bg-[var(--bd2)] transition-colors"
              >
                Reset All Filters
              </button>
            </div>
          ) : (
            filteredProviders.map((prov) => {
              const skills = prov.skill_names.split('|').filter(Boolean);

              return (
                <div
                  key={prov.id}
                  className="group rounded-3xl border border-[var(--bd)] bg-[var(--surface)] hover:border-[var(--cyan-border)] hover:shadow-2xl hover:shadow-cyan-500/5 transition-all p-6 flex flex-col justify-between gap-5 relative overflow-hidden"
                >
                  {/* Top Profile Header */}
                  <div className="flex items-start gap-4">
                    <div className="relative w-16 h-16 rounded-2xl overflow-hidden bg-[var(--surface-2)] shrink-0 border border-[var(--bd)] shadow-md">
                      <Image
                        src={prov.avatar || '/images/occupations/interior_designer.jpg'}
                        alt={`${prov.first_name} ${prov.last_name}`}
                        fill
                        sizes="64px"
                        className="object-cover group-hover:scale-105 transition-transform duration-300"
                      />
                    </div>

                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-1.5">
                        <h3 className="text-base font-bold text-[var(--tx)] truncate font-heading group-hover:text-[var(--cyan)] transition-colors">
                          {prov.first_name} {prov.last_name}
                        </h3>
                      </div>

                      {/* Ghana Card Verified Shield */}
                      {prov.is_verified ? (
                        <div className="mt-0.5 inline-flex items-center gap-1 text-[10px] font-bold text-[var(--cyan)] font-mono">
                          <ShieldCheck className="w-3.5 h-3.5 fill-[var(--cyan)]/20" />
                          <span>Ghana Card Verified</span>
                        </div>
                      ) : (
                        <div className="mt-0.5 text-[10px] text-[var(--tx-3)]">KYC In Review</div>
                      )}

                      <div className="mt-1 flex items-center gap-2 text-xs text-[var(--tx-3)]">
                        <span className="flex items-center gap-1">
                          <MapPin className="w-3 h-3 text-[var(--tx-3)] shrink-0" />
                          <span className="truncate">{prov.location}</span>
                        </span>
                      </div>
                    </div>
                  </div>

                  {/* Specialty & Bio */}
                  <div className="space-y-2">
                    <div className="text-xs font-bold text-[var(--tx)] line-clamp-1">
                      {prov.tagline}
                    </div>
                    <p className="text-xs text-[var(--tx-2)] line-clamp-2 leading-relaxed">
                      {prov.bio}
                    </p>
                  </div>

                  {/* Skill Badges */}
                  <div className="flex flex-wrap gap-1">
                    {skills.slice(0, 3).map((s, idx) => (
                      <span
                        key={idx}
                        className="px-2 py-0.5 rounded-md bg-[var(--surface-2)] text-[10px] font-medium text-[var(--tx-2)] border border-[var(--bd)]"
                      >
                        {s}
                      </span>
                    ))}
                  </div>

                  {/* Rating & Rate Metrics */}
                  <div className="pt-3 border-t border-[var(--bd)] flex items-center justify-between text-xs">
                    <div className="flex items-center gap-1.5">
                      <div className="flex items-center gap-1 text-amber-400 font-bold font-mono">
                        <Star className="w-3.5 h-3.5 fill-amber-400" />
                        <span>{prov.rating_avg.toFixed(1)}</span>
                      </div>
                      <span className="text-[var(--tx-3)] text-[11px]">
                        ({prov.rating_count} reviews)
                      </span>
                    </div>

                    <div className="text-right">
                      <span className="font-extrabold text-sm text-[var(--tx)] font-mono">
                        ₵{prov.hourly_rate}
                      </span>
                      <span className="text-[10px] text-[var(--tx-3)]">/hr</span>
                    </div>
                  </div>

                  {/* Action Buttons */}
                  <div className="pt-2 flex items-center gap-2">
                    <button
                      onClick={() => handleOpenHire(prov)}
                      className="flex-1 py-2.5 rounded-xl bg-[var(--cyan)] text-black font-bold text-xs hover:bg-[var(--cyan-l)] transition-all shadow-md shadow-cyan-500/10 flex items-center justify-center gap-1.5"
                    >
                      <Briefcase className="w-3.5 h-3.5" />
                      <span>Hire in Escrow</span>
                    </button>
                    <button
                      onClick={() => handleOpenChat(prov)}
                      className="p-2.5 rounded-xl border border-[var(--bd)] text-[var(--tx-2)] hover:text-emerald-500 hover:border-emerald-500/30 transition-colors"
                      title="Direct WhatsApp / Chat Consultation"
                    >
                      <WhatsAppIcon size={16} />
                    </button>
                  </div>

                </div>
              );
            })
          )}
        </div>

      </main>

      {/* ══════ INTERACTIVE DIRECT HIRE / ESCROW CONTRACT MODAL ══════ */}
      {activeProviderForHire && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200">
          <div className="w-full max-w-lg rounded-3xl bg-[var(--surface)] border border-[var(--bd2)] shadow-2xl p-6 sm:p-8 relative max-h-[90vh] overflow-y-auto">
            
            <button
              onClick={() => setActiveProviderForHire(null)}
              className="absolute right-5 top-5 p-2 rounded-xl text-[var(--tx-3)] hover:text-[var(--tx)] hover:bg-[var(--surface-2)] transition-colors"
            >
              <X className="w-5 h-5" />
            </button>

            {hireSuccess ? (
              <div className="py-8 text-center space-y-4">
                <div className="w-16 h-16 rounded-3xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 flex items-center justify-center mx-auto shadow-xl">
                  <CheckCircle2 className="w-8 h-8" />
                </div>
                <h3 className="text-xl font-extrabold text-[var(--tx)] font-heading">
                  Escrow Contract Offer Sent!
                </h3>
                <p className="text-xs sm:text-sm text-[var(--tx-2)] max-w-md mx-auto">
                  Your project brief and milestone budget of <strong className="text-[var(--tx)] font-mono">₵{projectBudget.toLocaleString()}</strong> has been dispatched to <strong>{activeProviderForHire.first_name} {activeProviderForHire.last_name}</strong>.
                </p>

                <div className="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                  <Link
                    href="/dashboard/client"
                    className="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[var(--cyan)] text-black font-bold text-xs flex items-center justify-center gap-2 shadow-lg"
                  >
                    <span>Go to Client Workspace</span>
                    <ArrowRight className="w-4 h-4" />
                  </Link>
                  <button
                    onClick={() => setActiveProviderForHire(null)}
                    className="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-[var(--bd)] text-[var(--tx)] font-semibold text-xs hover:bg-[var(--surface-2)]"
                  >
                    Done
                  </button>
                </div>
              </div>
            ) : (
              <form onSubmit={handleHireSubmit} className="space-y-5">
                
                <div>
                  <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[var(--cyan-dim)] text-[var(--cyan)] text-[10px] font-bold uppercase tracking-wider mb-2">
                    <ShieldCheck className="w-3.5 h-3.5" />
                    <span>Protected Milestone Contract</span>
                  </div>
                  <h3 className="text-lg sm:text-xl font-bold text-[var(--tx)] font-heading leading-snug">
                    Hire {activeProviderForHire.first_name} {activeProviderForHire.last_name}
                  </h3>
                  <p className="text-xs text-[var(--tx-3)] mt-0.5">
                    {activeProviderForHire.tagline} • Rate: ₵{activeProviderForHire.hourly_rate}/hr
                  </p>
                </div>

                <div className="p-3.5 rounded-2xl bg-[var(--cyan-dim)] border border-[var(--cyan-border)] flex items-center gap-3 text-xs">
                  <ShieldCheck className="w-6 h-6 text-[var(--cyan)] shrink-0" />
                  <div className="text-[11px] text-[var(--tx-2)] leading-relaxed">
                    <strong>100% Escrow Vault Guarantee:</strong> Your milestone deposit is held safely. The artisan is paid via Mobile Money only after you inspect and release the milestone.
                  </div>
                </div>

                {/* Project Title */}
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Project Title</label>
                  <input
                    type="text"
                    required
                    value={projectTitle}
                    onChange={(e) => setProjectTitle(e.target.value)}
                    className="w-full px-3 py-2.5 rounded-xl border border-[var(--bd)] bg-[var(--surface-2)] text-[var(--tx)] text-xs focus:border-[var(--cyan)] focus:outline-none font-medium"
                  />
                </div>

                {/* Milestone Budget */}
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Milestone Budget (₵ Cedis)</label>
                  <div className="relative">
                    <span className="absolute left-3.5 top-1/2 -translate-y-1/2 font-mono font-bold text-sm text-[var(--cyan)]">
                      ₵
                    </span>
                    <input
                      type="number"
                      min="200"
                      step="50"
                      required
                      value={projectBudget}
                      onChange={(e) => setProjectBudget(Number(e.target.value))}
                      className="w-full pl-8 pr-3 py-2.5 rounded-xl border border-[var(--bd)] bg-[var(--surface-2)] text-[var(--tx)] text-sm font-mono font-bold focus:border-[var(--cyan)] focus:outline-none"
                    />
                  </div>
                </div>

                {/* Project Details */}
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Project Scope & Location Instructions</label>
                  <textarea
                    rows={4}
                    required
                    value={projectNotes}
                    onChange={(e) => setProjectNotes(e.target.value)}
                    className="w-full p-3 rounded-xl border border-[var(--bd)] bg-[var(--surface-2)] text-[var(--tx)] text-xs focus:border-[var(--cyan)] focus:outline-none leading-relaxed"
                  />
                </div>

                {/* Submit Actions */}
                <div className="pt-2 flex items-center justify-end gap-3">
                  <button
                    type="button"
                    onClick={() => setActiveProviderForHire(null)}
                    className="px-4 py-2.5 rounded-xl border border-[var(--bd)] text-[var(--tx)] font-semibold text-xs hover:bg-[var(--surface-2)]"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={isSubmittingHire}
                    className="px-5 py-2.5 rounded-xl bg-[var(--cyan)] text-black font-bold text-xs hover:bg-[var(--cyan-l)] transition-all shadow-md shadow-cyan-500/20 flex items-center gap-2 disabled:opacity-50"
                  >
                    {isSubmittingHire ? (
                      <>
                        <div className="w-3.5 h-3.5 border-2 border-black border-t-transparent rounded-full animate-spin" />
                        <span>Creating Escrow Vault...</span>
                      </>
                    ) : (
                      <>
                        <Send className="w-3.5 h-3.5" />
                        <span>Deploy Escrow Offer</span>
                      </>
                    )}
                  </button>
                </div>

              </form>
            )}

          </div>
        </div>
      )}

      {/* ══════ CHAT / WHATSAPP MODAL ══════ */}
      {chatProvider && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200">
          <div className="w-full max-w-md rounded-3xl bg-[var(--surface)] border border-[var(--bd2)] shadow-2xl overflow-hidden flex flex-col h-[520px]">
            
            {/* Chat Top Header */}
            <div className="p-4 bg-[var(--surface-2)] border-b border-[var(--bd)] flex items-center justify-between">
              <div className="flex items-center gap-3">
                <div className="relative w-10 h-10 rounded-full overflow-hidden border border-[var(--bd)]">
                  <Image
                    src={chatProvider.avatar || '/images/occupations/interior_designer.jpg'}
                    alt={chatProvider.first_name}
                    fill
                    className="object-cover"
                  />
                </div>
                <div>
                  <div className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                    <span>{chatProvider.first_name} {chatProvider.last_name}</span>
                    <ShieldCheck className="w-3.5 h-3.5 text-[var(--cyan)]" />
                  </div>
                  <div className="text-[10px] text-emerald-500 flex items-center gap-1 font-mono">
                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
                    <span>Online for Instant Response</span>
                  </div>
                </div>
              </div>
              <button
                onClick={() => setChatProvider(null)}
                className="p-1.5 rounded-lg text-[var(--tx-3)] hover:text-[var(--tx)]"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {/* Chat Messages Body */}
            <div className="flex-1 p-4 overflow-y-auto space-y-3 bg-[var(--bg)] text-xs">
              {chatHistory.map((m, idx) => (
                <div
                  key={idx}
                  className={`flex ${m.sender === 'client' ? 'justify-end' : 'justify-start'}`}
                >
                  <div
                    className={`max-w-[80%] p-3 rounded-2xl ${
                      m.sender === 'client'
                        ? 'bg-[var(--cyan)] text-black rounded-tr-sm font-medium'
                        : 'bg-[var(--surface)] border border-[var(--bd)] text-[var(--tx)] rounded-tl-sm'
                    }`}
                  >
                    <p className="leading-relaxed">{m.text}</p>
                    <span className="text-[9px] opacity-60 block text-right mt-1 font-mono">
                      {m.time}
                    </span>
                  </div>
                </div>
              ))}
            </div>

            {/* Chat Input Bar */}
            <form onSubmit={handleSendChat} className="p-3 bg-[var(--surface-2)] border-t border-[var(--bd)] flex items-center gap-2">
              <input
                type="text"
                value={chatMessage}
                onChange={(e) => setChatMessage(e.target.value)}
                placeholder={`Ask ${chatProvider.first_name} about availability or rates...`}
                className="flex-1 px-3.5 py-2.5 rounded-xl border border-[var(--bd)] bg-[var(--surface)] text-[var(--tx)] text-xs focus:border-[var(--cyan)] focus:outline-none"
              />
              <button
                type="submit"
                className="p-2.5 rounded-xl bg-[var(--cyan)] text-black hover:bg-[var(--cyan-l)] transition-colors"
                title="Send Message"
              >
                <Send className="w-4 h-4" />
              </button>
            </form>

          </div>
        </div>
      )}

      <SiteFooter />
    </div>
  );
}

export default function ProvidersPage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen bg-[var(--bg)] flex items-center justify-center">
        <div className="flex flex-col items-center gap-3 text-sm text-[var(--tx-2)]">
          <div className="w-8 h-8 border-2 border-[var(--cyan)] border-t-transparent rounded-full animate-spin" />
          <span>Loading verified artisans...</span>
        </div>
      </div>
    }>
      <ProvidersDirectoryContent />
    </Suspense>
  );
}
