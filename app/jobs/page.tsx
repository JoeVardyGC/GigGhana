'use client';

import React, { useState, useMemo, Suspense } from 'react';
import Link from 'next/link';
import { useSearchParams } from 'next/navigation';
import { SiteHeader } from '@/components/layout/SiteHeader';
import { SiteFooter } from '@/components/layout/SiteFooter';
import { useAuth } from '@/lib/context/AuthContext';
import { fallbackRecentJobs, ghanaFallbackCats } from '@/lib/types';
import confetti from 'canvas-confetti';
import {
  Search,
  MapPin,
  Clock,
  ShieldCheck,
  Zap,
  Tag,
  SlidersHorizontal,
  ArrowRight,
  CheckCircle2,
  AlertCircle,
  Bookmark,
  Briefcase,
  Send,
  X,
  Sparkles,
  DollarSign,
  UserCheck,
  Building2,
  Check,
} from 'lucide-react';

interface JobItem {
  id: number;
  title: string;
  description: string;
  budget_min: number;
  budget_max: number;
  budget_type: string;
  is_urgent: number;
  is_featured: number;
  proposal_count: number;
  location: string;
  cat_name: string;
  cat_icon: string;
  created_at: string;
  first_name: string;
  last_name: string;
  is_verified: number;
}

// Additional realistic Ghanaian jobs
const extendedJobs: JobItem[] = [
  ...fallbackRecentJobs,
  {
    id: 107,
    title: 'Bespoke Hardwood Wardrobes & Kitchen Cabinets Installation',
    description: 'Looking for a master carpenter in East Legon to fabricate and install custom mahogany wardrobes and soft-close kitchen cabinets for a newly constructed duplex.',
    budget_min: 6500,
    budget_max: 9200,
    budget_type: 'fixed',
    is_urgent: 1,
    is_featured: 1,
    proposal_count: 5,
    location: 'East Legon, Accra',
    cat_name: 'Skilled Trades',
    cat_icon: 'tool',
    created_at: new Date(Date.now() - 50 * 60 * 1000).toISOString(),
    first_name: 'Abena',
    last_name: 'Mensah',
    is_verified: 1,
  },
  {
    id: 108,
    title: 'Commercial 3-Phase CCTV & Smart Biometric Access Control',
    description: 'Experienced security electrical technician required to wire, configure, and install a 24-channel IP surveillance system and biometric turnstiles in Tema Industrial Area.',
    budget_min: 7800,
    budget_max: 11500,
    budget_type: 'fixed',
    is_urgent: 0,
    is_featured: 0,
    proposal_count: 3,
    location: 'Tema, Greater Accra',
    cat_name: 'Skilled Trades',
    cat_icon: 'tool',
    created_at: new Date(Date.now() - 95 * 60 * 1000).toISOString(),
    first_name: 'Kofi',
    last_name: 'Boateng',
    is_verified: 1,
  },
  {
    id: 109,
    title: 'Flutter & Node.js Mobile Delivery Dispatch App for Courier Agency',
    description: 'Need a senior mobile app developer to design and test a cross-platform delivery app integrated with Google Maps Geocoding and Hubtel/Paystack SMS verification.',
    budget_min: 9500,
    budget_max: 15000,
    budget_type: 'fixed',
    is_urgent: 1,
    is_featured: 1,
    proposal_count: 8,
    location: 'Dzorwulu, Accra',
    cat_name: 'IT & Tech',
    cat_icon: 'code',
    created_at: new Date(Date.now() - 140 * 60 * 1000).toISOString(),
    first_name: 'Dr. Kwabena',
    last_name: 'Frimpong',
    is_verified: 1,
  },
  {
    id: 110,
    title: 'Compound Paving Blocks & Terrazzo Floor Polishing',
    description: 'Experienced paving contractor needed to lay 450 sqm interlock paving blocks with perimeter curbs and polish terrazzo corridors in Ahodwo, Kumasi.',
    budget_min: 5000,
    budget_max: 7200,
    budget_type: 'fixed',
    is_urgent: 0,
    is_featured: 0,
    proposal_count: 4,
    location: 'Ahodwo, Kumasi',
    cat_name: 'Construction',
    cat_icon: 'briefcase',
    created_at: new Date(Date.now() - 280 * 60 * 1000).toISOString(),
    first_name: 'Yaw',
    last_name: 'Appiah',
    is_verified: 1,
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
  'Volta',
  'Bono',
];

function JobsBoardContent() {
  const searchParams = useSearchParams();
  const { user, isAuthenticated, loginDemoUser } = useAuth();

  const initialCat = searchParams.get('category') || 'all';
  const initialQuery = searchParams.get('q') || '';
  const initialRegion = searchParams.get('region') || 'All Ghana';

  const [searchQuery, setSearchQuery] = useState(initialQuery);
  const [selectedCat, setSelectedCat] = useState(initialCat);
  const [selectedRegion, setSelectedRegion] = useState(initialRegion);
  const [budgetFilter, setBudgetFilter] = useState<'all' | 'under5k' | '5k_10k' | 'over10k'>('all');
  const [urgentOnly, setUrgentOnly] = useState(false);
  const [verifiedOnly, setVerifiedOnly] = useState(false);
  const [bookmarkedIds, setBookmarkedIds] = useState<number[]>([]);

  // Modal State
  const [activeJobForProposal, setActiveJobForProposal] = useState<JobItem | null>(null);
  const [bidAmount, setBidAmount] = useState<number>(5000);
  const [deliveryDays, setDeliveryDays] = useState<number>(7);
  const [coverLetter, setCoverLetter] = useState<string>('');
  const [isSubmittingProposal, setIsSubmittingProposal] = useState(false);
  const [proposalSubmittedSuccess, setProposalSubmittedSuccess] = useState(false);
  const [submittedJobIds, setSubmittedJobIds] = useState<number[]>([]);

  // Filter Jobs
  const filteredJobs = useMemo(() => {
    return extendedJobs.filter((job) => {
      // Query filter
      if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase();
        const matchesTitle = job.title.toLowerCase().includes(q);
        const matchesDesc = job.description.toLowerCase().includes(q);
        const matchesLoc = job.location.toLowerCase().includes(q);
        const matchesCat = job.cat_name.toLowerCase().includes(q);
        if (!matchesTitle && !matchesDesc && !matchesLoc && !matchesCat) {
          return false;
        }
      }

      // Category filter
      if (selectedCat !== 'all') {
        const cLower = selectedCat.toLowerCase();
        const jCat = job.cat_name.toLowerCase();
        if (cLower === 'trades' && !jCat.includes('trades')) return false;
        if (cLower === 'tech' && !jCat.includes('tech') && !jCat.includes('it')) return false;
        if (cLower === 'design' && !jCat.includes('creative') && !jCat.includes('art')) return false;
        if (cLower === 'build' && !jCat.includes('construction')) return false;
      }

      // Region filter
      if (selectedRegion !== 'All Ghana') {
        if (!job.location.toLowerCase().includes(selectedRegion.toLowerCase())) {
          return false;
        }
      }

      // Budget filter
      if (budgetFilter === 'under5k' && job.budget_min >= 5000) return false;
      if (budgetFilter === '5k_10k' && (job.budget_max < 5000 || job.budget_min > 10000)) return false;
      if (budgetFilter === 'over10k' && job.budget_max < 10000) return false;

      // Urgent filter
      if (urgentOnly && !job.is_urgent) return false;

      // Verified client filter
      if (verifiedOnly && !job.is_verified) return false;

      return true;
    });
  }, [searchQuery, selectedCat, selectedRegion, budgetFilter, urgentOnly, verifiedOnly]);

  const toggleBookmark = (id: number) => {
    setBookmarkedIds((prev) =>
      prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
    );
  };

  const handleOpenProposal = (job: JobItem) => {
    setActiveJobForProposal(job);
    setBidAmount(job.budget_min);
    setDeliveryDays(7);
    setCoverLetter(
      `Hello ${job.first_name}, I am a certified professional with verified experience handling projects like "${job.title}". All work will be guaranteed under the GigGhana Escrow Milestone vault with timely delivery.`
    );
    setProposalSubmittedSuccess(false);
  };

  const handleQuickLoginAsArtisan = () => {
    loginDemoUser('kwame_provider');
  };

  const handleSubmitProposal = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeJobForProposal) return;

    setIsSubmittingProposal(true);
    await new Promise((r) => setTimeout(r, 900)); // realistic network latency
    setIsSubmittingProposal(false);
    setProposalSubmittedSuccess(true);
    setSubmittedJobIds((prev) => [...prev, activeJobForProposal.id]);

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
      <SiteHeader activeTab="jobs" />

      {/* Main Container */}
      <main className="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20 w-full">
        
        {/* Hero Section */}
        <div className="relative mb-10 overflow-hidden rounded-3xl border border-[var(--cyan-border)] bg-gradient-to-br from-[var(--cyan-dim)] via-[var(--surface)] to-[var(--gold-dim)] p-8 sm:p-12 shadow-sm">
          <div className="max-w-3xl relative z-10">
            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[var(--cyan-dim)] border border-[var(--cyan-border)] text-[var(--cyan)] font-bold text-xs uppercase tracking-wider mb-4">
              <Sparkles className="w-3.5 h-3.5" />
              <span>Verified Escrow Job Board 🇬🇭</span>
            </div>
            <h1 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-[var(--tx)] font-heading tracking-tight leading-tight">
              Find Verified Contract Work & Gigs in Ghana
            </h1>
            <p className="mt-4 text-sm sm:text-base text-[var(--tx-2)] leading-relaxed">
              Every job listed here is backed by the GigGhana Escrow Vault. Client funds are locked before you begin work, with guaranteed sub-60 second settlements to your MTN MoMo, Telecel Cash, or AT Money wallet upon completion.
            </p>

            {/* Quick Metrics Bar */}
            <div className="mt-8 flex flex-wrap items-center gap-6 pt-6 border-t border-[var(--bd)] text-xs text-[var(--tx-2)] font-mono">
              <div className="flex items-center gap-2">
                <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" />
                <span className="font-bold text-[var(--tx)]">₵420,000+</span> In Escrow Vault
              </div>
              <div className="flex items-center gap-2">
                <ShieldCheck className="w-4 h-4 text-[var(--cyan)]" />
                <span className="font-bold text-[var(--tx)]">100%</span> Ghana Card Verified Clients
              </div>
              <div className="flex items-center gap-2">
                <Zap className="w-4 h-4 text-[var(--gold)]" />
                <span className="font-bold text-[var(--tx)]">&lt; 60s</span> Mobile Money Settlement
              </div>
            </div>
          </div>
        </div>

        {/* Search & Control Center */}
        <div className="mb-8 space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-12 gap-3">
            
            {/* Search Input */}
            <div className="md:col-span-6 relative">
              <Search className="w-4 h-4 text-[var(--tx-3)] absolute left-4 top-1/2 -translate-y-1/2" />
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search jobs by trade, skill, or keyword (e.g. POP ceiling, solar, Next.js)..."
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

            {/* Budget Range Filter */}
            <div className="md:col-span-3">
              <select
                value={budgetFilter}
                onChange={(e) => setBudgetFilter(e.target.value as any)}
                className="w-full px-4 py-3 rounded-2xl border border-[var(--bd)] bg-[var(--surface)] text-[var(--tx)] text-sm appearance-none focus:outline-none focus:border-[var(--cyan)] transition-all shadow-sm cursor-pointer"
              >
                <option value="all" className="bg-[var(--surface)]">All Budgets (Cedis ₵)</option>
                <option value="under5k" className="bg-[var(--surface)]">Under ₵5,000</option>
                <option value="5k_10k" className="bg-[var(--surface)]">₵5,000 - ₵10,000</option>
                <option value="over10k" className="bg-[var(--surface)]">₵10,000 and above</option>
              </select>
            </div>

          </div>

          {/* Filter Pills & Toggles */}
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
                All Categories
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
                onClick={() => setSelectedCat('tech')}
                className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 ${
                  selectedCat === 'tech'
                    ? 'bg-[var(--cyan)] text-black shadow-sm'
                    : 'bg-[var(--surface)] border border-[var(--bd)] text-[var(--tx-2)] hover:border-[var(--cyan)]'
                }`}
              >
                💻 IT & Tech
              </button>
              <button
                onClick={() => setSelectedCat('design')}
                className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 ${
                  selectedCat === 'design'
                    ? 'bg-violet-600 text-white shadow-sm'
                    : 'bg-[var(--surface)] border border-[var(--bd)] text-[var(--tx-2)] hover:border-violet-500'
                }`}
              >
                🎨 Creative Arts
              </button>
              <button
                onClick={() => setSelectedCat('build')}
                className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all shrink-0 ${
                  selectedCat === 'build'
                    ? 'bg-amber-600 text-white shadow-sm'
                    : 'bg-[var(--surface)] border border-[var(--bd)] text-[var(--tx-2)] hover:border-amber-500'
                }`}
              >
                🏗️ Construction
              </button>
            </div>

            {/* Checkbox Toggles */}
            <div className="flex items-center gap-4 text-xs font-medium text-[var(--tx-2)]">
              <label className="flex items-center gap-1.5 cursor-pointer select-none">
                <input
                  type="checkbox"
                  checked={urgentOnly}
                  onChange={(e) => setUrgentOnly(e.target.checked)}
                  className="rounded border-[var(--bd)] text-[var(--cyan)] focus:ring-[var(--cyan)]"
                />
                <span className="flex items-center gap-1">
                  <span className="w-2 h-2 rounded-full bg-red-500" />
                  Urgent Only
                </span>
              </label>

              <label className="flex items-center gap-1.5 cursor-pointer select-none">
                <input
                  type="checkbox"
                  checked={verifiedOnly}
                  onChange={(e) => setVerifiedOnly(e.target.checked)}
                  className="rounded border-[var(--bd)] text-[var(--cyan)] focus:ring-[var(--cyan)]"
                />
                <span className="flex items-center gap-1">
                  <ShieldCheck className="w-3.5 h-3.5 text-[var(--cyan)]" />
                  Verified Clients
                </span>
              </label>
            </div>

          </div>
        </div>

        {/* Results Count & Post Job CTA */}
        <div className="mb-6 flex items-center justify-between text-xs text-[var(--tx-3)]">
          <div>
            Showing <strong className="text-[var(--tx)] font-bold">{filteredJobs.length}</strong> verified job briefs
            {selectedRegion !== 'All Ghana' && ` in ${selectedRegion}`}
          </div>
          <Link
            href="/dashboard/client"
            className="flex items-center gap-1.5 text-xs font-bold text-[var(--cyan)] hover:underline"
          >
            <span>Post a Project Brief as Employer</span>
            <ArrowRight className="w-3.5 h-3.5" />
          </Link>
        </div>

        {/* Job Listings Grid */}
        <div className="space-y-4">
          {filteredJobs.length === 0 ? (
            <div className="py-16 text-center rounded-3xl border border-[var(--bd)] bg-[var(--surface)] p-8">
              <Briefcase className="w-12 h-12 text-[var(--tx-3)] mx-auto mb-3 opacity-40" />
              <h3 className="font-bold text-base text-[var(--tx)]">No matching jobs found</h3>
              <p className="text-xs text-[var(--tx-2)] mt-1 max-w-sm mx-auto">
                Try widening your search terms, changing the region filter, or resetting your category selections.
              </p>
              <button
                onClick={() => {
                  setSearchQuery('');
                  setSelectedCat('all');
                  setSelectedRegion('All Ghana');
                  setBudgetFilter('all');
                  setUrgentOnly(false);
                  setVerifiedOnly(false);
                }}
                className="mt-4 px-4 py-2 rounded-xl bg-[var(--surface-2)] text-xs font-bold hover:bg-[var(--bd2)] transition-colors"
              >
                Reset All Filters
              </button>
            </div>
          ) : (
            filteredJobs.map((job) => {
              const isBookmarked = bookmarkedIds.includes(job.id);
              const hasSubmitted = submittedJobIds.includes(job.id);

              return (
                <div
                  key={job.id}
                  className="group p-6 rounded-3xl border border-[var(--bd)] bg-[var(--surface)] hover:border-[var(--cyan-border)] hover:shadow-xl hover:shadow-cyan-500/5 transition-all flex flex-col justify-between gap-5 relative"
                >
                  {/* Top Row: Client Trust & Tags */}
                  <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                      <div className="w-8 h-8 rounded-full bg-[var(--surface-2)] flex items-center justify-center font-bold text-xs text-[var(--tx)]">
                        {job.first_name[0]}
                      </div>
                      <div>
                        <div className="flex items-center gap-1.5 text-xs font-bold text-[var(--tx)]">
                          <span>{job.first_name} {job.last_name[0]}.</span>
                          {job.is_verified ? (
                            <span className="flex items-center gap-0.5 text-[10px] text-[var(--cyan)] font-mono font-bold" title="Ghana Card KYC Verified Employer">
                              <ShieldCheck className="w-3.5 h-3.5 fill-[var(--cyan)]/20" />
                              <span>Verified Client</span>
                            </span>
                          ) : null}
                        </div>
                        <div className="flex items-center gap-2 text-[11px] text-[var(--tx-3)]">
                          <span className="flex items-center gap-1">
                            <MapPin className="w-3 h-3 text-[var(--tx-3)]" />
                            {job.location}
                          </span>
                          <span>•</span>
                          <span className="flex items-center gap-1">
                            <Clock className="w-3 h-3 text-[var(--tx-3)]" />
                            Posted recently
                          </span>
                        </div>
                      </div>
                    </div>

                    <div className="flex items-center gap-2">
                      {job.is_urgent ? (
                        <span className="px-2.5 py-0.5 rounded-full bg-red-500/10 text-red-500 border border-red-500/20 text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                          <span className="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse" />
                          Urgent Need
                        </span>
                      ) : null}
                      <span className="px-2.5 py-0.5 rounded-full bg-[var(--surface-2)] text-[var(--tx-2)] border border-[var(--bd)] text-[10px] font-semibold">
                        {job.cat_name}
                      </span>
                      <button
                        onClick={() => toggleBookmark(job.id)}
                        className={`p-1.5 rounded-lg border transition-colors ${
                          isBookmarked
                            ? 'border-amber-500 bg-amber-500/10 text-amber-500'
                            : 'border-[var(--bd)] text-[var(--tx-3)] hover:text-[var(--tx)]'
                        }`}
                        title={isBookmarked ? 'Remove bookmark' : 'Bookmark job'}
                      >
                        <Bookmark className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>

                  {/* Middle Row: Job Title & Description */}
                  <div className="space-y-2">
                    <h2 className="text-base sm:text-lg font-bold text-[var(--tx)] group-hover:text-[var(--cyan)] transition-colors font-heading leading-snug">
                      {job.title}
                    </h2>
                    <p className="text-xs sm:text-sm text-[var(--tx-2)] line-clamp-2 leading-relaxed">
                      {job.description}
                    </p>
                  </div>

                  {/* Bottom Row: Escrow Vault Budget & Submission CTA */}
                  <div className="pt-4 border-t border-[var(--bd)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div className="flex items-baseline gap-2">
                      <span className="text-lg sm:text-xl font-extrabold text-[var(--tx)] font-mono">
                        ₵{job.budget_min.toLocaleString()} - ₵{job.budget_max.toLocaleString()}
                      </span>
                      <span className="text-xs text-emerald-500 font-bold flex items-center gap-1">
                        <CheckCircle2 className="w-3.5 h-3.5" />
                        Escrow Funded
                      </span>
                    </div>

                    <div className="flex items-center gap-3">
                      <span className="text-xs text-[var(--tx-3)]">
                        {job.proposal_count + (hasSubmitted ? 1 : 0)} proposals
                      </span>

                      {hasSubmitted ? (
                        <span className="px-4 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 text-xs font-bold flex items-center gap-1.5">
                          <Check className="w-3.5 h-3.5" />
                          Bid Submitted
                        </span>
                      ) : (
                        <button
                          onClick={() => handleOpenProposal(job)}
                          className="px-4 py-2.5 rounded-xl bg-[var(--cyan)] text-black font-bold text-xs hover:bg-[var(--cyan-l)] transition-all shadow-md shadow-cyan-500/10 flex items-center gap-1.5 group/btn"
                        >
                          <span>Submit Proposal</span>
                          <ArrowRight className="w-3.5 h-3.5 group-hover/btn:translate-x-0.5 transition-transform" />
                        </button>
                      )}
                    </div>
                  </div>
                </div>
              );
            })
          )}
        </div>

      </main>

      {/* ══════ INTERACTIVE PROPOSAL SUBMISSION MODAL ══════ */}
      {activeJobForProposal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-in fade-in duration-200">
          <div className="w-full max-w-xl rounded-3xl bg-[var(--surface)] border border-[var(--bd2)] shadow-2xl p-6 sm:p-8 relative max-h-[90vh] overflow-y-auto">
            
            {/* Close Button */}
            <button
              onClick={() => setActiveJobForProposal(null)}
              className="absolute right-5 top-5 p-2 rounded-xl text-[var(--tx-3)] hover:text-[var(--tx)] hover:bg-[var(--surface-2)] transition-colors"
            >
              <X className="w-5 h-5" />
            </button>

            {proposalSubmittedSuccess ? (
              <div className="py-8 text-center space-y-4">
                <div className="w-16 h-16 rounded-3xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 flex items-center justify-center mx-auto shadow-xl">
                  <CheckCircle2 className="w-8 h-8" />
                </div>
                <h3 className="text-xl font-extrabold text-[var(--tx)] font-heading">
                  Proposal Submitted Successfully!
                </h3>
                <p className="text-xs sm:text-sm text-[var(--tx-2)] max-w-md mx-auto">
                  Your bid of <strong className="text-[var(--tx)] font-mono">₵{bidAmount.toLocaleString()}</strong> has been delivered to <strong>{activeJobForProposal.first_name}</strong>. Once approved, the funds will be locked into your Escrow Vault.
                </p>

                <div className="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                  <Link
                    href="/dashboard/provider"
                    className="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[var(--cyan)] text-black font-bold text-xs flex items-center justify-center gap-2 shadow-lg"
                  >
                    <span>View in Artisan Workspace</span>
                    <ArrowRight className="w-4 h-4" />
                  </Link>
                  <button
                    onClick={() => setActiveJobForProposal(null)}
                    className="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-[var(--bd)] text-[var(--tx)] font-semibold text-xs hover:bg-[var(--surface-2)]"
                  >
                    Close Window
                  </button>
                </div>
              </div>
            ) : (
              <form onSubmit={handleSubmitProposal} className="space-y-5">
                
                {/* Modal Header */}
                <div>
                  <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[var(--cyan-dim)] text-[var(--cyan)] text-[10px] font-bold uppercase tracking-wider mb-2">
                    <ShieldCheck className="w-3.5 h-3.5" />
                    <span>Protected Bid</span>
                  </div>
                  <h3 className="text-lg sm:text-xl font-bold text-[var(--tx)] font-heading leading-snug">
                    Submit Proposal for &ldquo;{activeJobForProposal.title}&rdquo;
                  </h3>
                  <p className="text-xs text-[var(--tx-3)] mt-1">
                    Client Budget: ₵{activeJobForProposal.budget_min.toLocaleString()} - ₵{activeJobForProposal.budget_max.toLocaleString()} • {activeJobForProposal.location}
                  </p>
                </div>

                {/* Artisan Profile Status Check */}
                {!isAuthenticated ? (
                  <div className="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between gap-3 text-xs">
                    <div className="space-y-0.5">
                      <div className="font-bold text-amber-500">Sign in to use your Ghana Card badge</div>
                      <div className="text-[11px] text-[var(--tx-2)]">Verified proposals receive 4x more employer callbacks.</div>
                    </div>
                    <button
                      type="button"
                      onClick={handleQuickLoginAsArtisan}
                      className="px-3 py-1.5 rounded-xl bg-amber-500 text-black font-bold text-[11px] shrink-0 shadow-sm"
                    >
                      Login as Kwame (Pro)
                    </button>
                  </div>
                ) : (
                  <div className="p-3.5 rounded-2xl bg-[var(--cyan-dim)] border border-[var(--cyan-border)] flex items-center gap-3 text-xs">
                    <div className="w-8 h-8 rounded-full bg-[var(--cyan)] text-black font-bold flex items-center justify-center text-xs">
                      {user?.first_name[0]}
                    </div>
                    <div className="flex-1">
                      <div className="font-bold text-[var(--tx)] flex items-center gap-1.5">
                        <span>{user?.first_name} {user?.last_name}</span>
                        <span className="text-[10px] font-mono text-[var(--cyan)]">✓ Ghana Card Verified</span>
                      </div>
                      <div className="text-[11px] text-[var(--tx-2)]">
                        Payout Wallet: {user?.payout_wallet?.toUpperCase()} MoMo ({user?.wallet_number || user?.phone})
                      </div>
                    </div>
                  </div>
                )}

                {/* Bid Amount & Completion Timeline */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-[var(--tx)]">Your Bid Amount (₵ Cedis)</label>
                    <div className="relative">
                      <span className="absolute left-3.5 top-1/2 -translate-y-1/2 font-mono font-bold text-sm text-[var(--cyan)]">
                        ₵
                      </span>
                      <input
                        type="number"
                        min="500"
                        step="100"
                        value={bidAmount}
                        onChange={(e) => setBidAmount(Number(e.target.value))}
                        required
                        className="w-full pl-8 pr-3 py-2.5 rounded-xl border border-[var(--bd)] bg-[var(--surface-2)] text-[var(--tx)] text-sm font-mono font-bold focus:border-[var(--cyan)] focus:outline-none"
                      />
                    </div>
                    <span className="text-[10px] text-[var(--tx-3)]">Includes platform escrow fee guarantee</span>
                  </div>

                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-[var(--tx)]">Estimated Delivery Timeline</label>
                    <div className="relative">
                      <input
                        type="number"
                        min="1"
                        max="90"
                        value={deliveryDays}
                        onChange={(e) => setDeliveryDays(Number(e.target.value))}
                        required
                        className="w-full px-3 py-2.5 rounded-xl border border-[var(--bd)] bg-[var(--surface-2)] text-[var(--tx)] text-sm font-mono focus:border-[var(--cyan)] focus:outline-none"
                      />
                      <span className="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-[var(--tx-3)]">
                        Days
                      </span>
                    </div>
                    <span className="text-[10px] text-[var(--tx-3)]">From milestone contract execution</span>
                  </div>
                </div>

                {/* Milestone Breakdown Preview */}
                <div className="p-4 rounded-2xl border border-[var(--bd)] bg-[var(--surface-2)] space-y-2">
                  <div className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                    <span>Proposed Escrow Milestones (2 Stages)</span>
                    <span className="font-mono text-[var(--cyan)]">Total: ₵{bidAmount.toLocaleString()}</span>
                  </div>
                  <div className="grid grid-cols-2 gap-2 text-xs">
                    <div className="p-2 rounded-xl bg-[var(--surface)] border border-[var(--bd)]">
                      <div className="text-[10px] text-[var(--tx-3)] uppercase font-mono">Phase 1 (50%)</div>
                      <div className="font-bold text-[var(--tx)] mt-0.5">₵{(bidAmount * 0.5).toLocaleString()}</div>
                      <div className="text-[10px] text-[var(--tx-2)]">Materials & Rough-in Inspection</div>
                    </div>
                    <div className="p-2 rounded-xl bg-[var(--surface)] border border-[var(--bd)]">
                      <div className="text-[10px] text-[var(--tx-3)] uppercase font-mono">Phase 2 (50%)</div>
                      <div className="font-bold text-[var(--tx)] mt-0.5">₵{(bidAmount * 0.5).toLocaleString()}</div>
                      <div className="text-[10px] text-[var(--tx-2)]">Final Handover & Client Approval</div>
                    </div>
                  </div>
                </div>

                {/* Cover Note */}
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Cover Letter & Approach</label>
                  <textarea
                    rows={4}
                    value={coverLetter}
                    onChange={(e) => setCoverLetter(e.target.value)}
                    required
                    placeholder="Describe your qualifications, past projects, and why the client should award you this contract..."
                    className="w-full p-3 rounded-xl border border-[var(--bd)] bg-[var(--surface-2)] text-[var(--tx)] text-xs focus:border-[var(--cyan)] focus:outline-none leading-relaxed"
                  />
                </div>

                {/* Submit Actions */}
                <div className="pt-2 flex items-center justify-end gap-3">
                  <button
                    type="button"
                    onClick={() => setActiveJobForProposal(null)}
                    className="px-4 py-2.5 rounded-xl border border-[var(--bd)] text-[var(--tx)] font-semibold text-xs hover:bg-[var(--surface-2)]"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={isSubmittingProposal}
                    className="px-5 py-2.5 rounded-xl bg-[var(--cyan)] text-black font-bold text-xs hover:bg-[var(--cyan-l)] transition-all shadow-md shadow-cyan-500/20 flex items-center gap-2 disabled:opacity-50"
                  >
                    {isSubmittingProposal ? (
                      <>
                        <div className="w-3.5 h-3.5 border-2 border-black border-t-transparent rounded-full animate-spin" />
                        <span>Locking Proposal in Vault...</span>
                      </>
                    ) : (
                      <>
                        <Send className="w-3.5 h-3.5" />
                        <span>Send Escrow Proposal</span>
                      </>
                    )}
                  </button>
                </div>

              </form>
            )}

          </div>
        </div>
      )}

      <SiteFooter />
    </div>
  );
}

export default function JobsPage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen bg-[var(--bg)] flex items-center justify-center">
        <div className="flex flex-col items-center gap-3 text-sm text-[var(--tx-2)]">
          <div className="w-8 h-8 border-2 border-[var(--cyan)] border-t-transparent rounded-full animate-spin" />
          <span>Loading verified jobs...</span>
        </div>
      </div>
    }>
      <JobsBoardContent />
    </Suspense>
  );
}
