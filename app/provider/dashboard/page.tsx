'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useAuth } from '@/lib/context/AuthContext';
import { ProviderSidebar } from '@/components/provider/ProviderSidebar';
import { ProviderHeader } from '@/components/provider/ProviderHeader';
import confetti from 'canvas-confetti';
import {
  Wallet,
  ShieldCheck,
  CheckCircle2,
  Briefcase,
  Search,
  ArrowUpRight,
  Sparkles,
  Clock,
  Star,
  ChevronRight,
  TrendingUp,
  MapPin,
  Send,
  Eye,
  X,
  FileCheck,
  Smartphone,
  AlertCircle,
  Plus,
  ArrowRight,
  Check,
  Filter,
} from 'lucide-react';

interface ActiveContract {
  id: string;
  title: string;
  clientName: string;
  clientLocation: string;
  totalBudget: number;
  currentMilestone: string;
  milestoneBudget: number;
  milestoneStatus: 'in_progress' | 'submitted' | 'completed';
  progressPercent: number;
  dueDate: string;
}

interface RecommendedJob {
  id: string;
  title: string;
  category: string;
  location: string;
  budgetMin: number;
  budgetMax: number;
  isUrgent?: boolean;
  isEscrowFunded?: boolean;
  postedTime: string;
  proposalsCount: number;
  clientRating: number;
}

export default function ProviderDashboardPage() {
  const { user } = useAuth();
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  // Financial & Escrow Balances State
  const [availableBalance, setAvailableBalance] = useState(3250);
  const [pendingEscrowBalance, setPendingEscrowBalance] = useState(4600);
  const [totalCareerEarned, setTotalCareerEarned] = useState(42850);

  // Modals state
  const [isWithdrawModalOpen, setIsWithdrawModalOpen] = useState(false);
  const [isBidModalOpen, setIsBidModalOpen] = useState(false);
  const [isDeliverableModalOpen, setIsDeliverableModalOpen] = useState(false);

  // Modal active selections
  const [selectedJobForBid, setSelectedJobForBid] = useState<RecommendedJob | null>(null);
  const [bidAmount, setBidAmount] = useState('');
  const [bidDays, setBidDays] = useState('3');
  const [bidCoverLetter, setBidCoverLetter] = useState('');
  const [bidSubmittedSuccess, setBidSubmittedSuccess] = useState(false);

  // Cashout Modal State
  const [withdrawAmount, setWithdrawAmount] = useState('500');
  const [selectedWallet, setSelectedWallet] = useState<'mtn' | 'telecel' | 'at'>('mtn');
  const [walletPhoneInput, setWalletPhoneInput] = useState(user?.phone || '024 412 3456');
  const [isProcessingWithdrawal, setIsProcessingWithdrawal] = useState(false);
  const [withdrawSuccessMsg, setWithdrawSuccessMsg] = useState('');

  // Milestone deliverable state
  const [selectedContractForSubmit, setSelectedContractForSubmit] = useState<ActiveContract | null>(null);
  const [deliverableNotes, setDeliverableNotes] = useState('');
  const [deliverableSubmitted, setDeliverableSubmitted] = useState(false);

  // Filter for recommended gigs
  const [gigFilter, setGigFilter] = useState<'all' | 'urgent' | 'high_budget' | 'accra'>('all');

  // Active contracts data
  const [activeContracts, setActiveContracts] = useState<ActiveContract[]>([
    {
      id: 'cnt-1',
      title: 'Ridge Luxury Residence — 3-Bedroom POP Ceiling & Crown Molding',
      clientName: 'Dr. Kwabena Frimpong',
      clientLocation: 'East Legon, Accra',
      totalBudget: 5500,
      currentMilestone: 'Milestone 2: Master Bedroom & Hallway Decorative Finishing',
      milestoneBudget: 3000,
      milestoneStatus: 'in_progress',
      progressPercent: 65,
      dueDate: 'Due in 3 days',
    },
    {
      id: 'cnt-2',
      title: 'Showroom Ceramic & Porcelain Floor Tiling (280 sqm)',
      clientName: 'Ridge Commercial Holdings',
      clientLocation: 'Spintex Road, Accra',
      totalBudget: 3800,
      currentMilestone: 'Milestone 1: Sub-Floor Alignment & Tile Laying',
      milestoneBudget: 1600,
      milestoneStatus: 'in_progress',
      progressPercent: 40,
      dueDate: 'Due in 5 days',
    },
  ]);

  // Recommended gigs data
  const [recommendedJobs, setRecommendedJobs] = useState<RecommendedJob[]>([
    {
      id: 'job-1',
      title: 'Urgent POP Ceiling Repair & Repaint after Roof Leak',
      category: 'Finishing & Decor',
      location: 'Airport Residential, Accra',
      budgetMin: 1200,
      budgetMax: 1800,
      isUrgent: true,
      isEscrowFunded: true,
      postedTime: '24m ago',
      proposalsCount: 3,
      clientRating: 4.9,
    },
    {
      id: 'job-2',
      title: 'Complete Bio-Digester & Soak-Away Installation for 4-Unit Apartment',
      category: 'Construction',
      location: 'Spintex, Greater Accra',
      budgetMin: 6500,
      budgetMax: 8000,
      isUrgent: true,
      isEscrowFunded: true,
      postedTime: '1h ago',
      proposalsCount: 2,
      clientRating: 5.0,
    },
    {
      id: 'job-3',
      title: 'Porcelain Floor Tiling for 500sqm Warehouse & Office',
      category: 'Finishing',
      location: 'Tema Community 9, Greater Accra',
      budgetMin: 4500,
      budgetMax: 6000,
      isUrgent: false,
      isEscrowFunded: true,
      postedTime: '3h ago',
      proposalsCount: 5,
      clientRating: 4.8,
    },
    {
      id: 'job-4',
      title: 'Custom Solid Mahogany Wardrobes & Fitted Kitchen Joinery',
      category: 'Woodwork & Cabinetry',
      location: 'East Legon Hills, Accra',
      budgetMin: 9500,
      budgetMax: 13000,
      isUrgent: false,
      isEscrowFunded: true,
      postedTime: '5h ago',
      proposalsCount: 4,
      clientRating: 5.0,
    },
  ]);

  // Filtered jobs logic
  const filteredGigs = recommendedJobs.filter((job) => {
    if (gigFilter === 'urgent') return job.isUrgent;
    if (gigFilter === 'high_budget') return job.budgetMin >= 4000;
    if (gigFilter === 'accra') return job.location.includes('Accra');
    return true;
  });

  // Handle instant withdrawal submit
  const handleConfirmWithdrawal = () => {
    const amount = Number(withdrawAmount);
    if (!amount || amount <= 0 || amount > availableBalance) return;

    setIsProcessingWithdrawal(true);
    setTimeout(() => {
      setAvailableBalance((prev) => prev - amount);
      setIsProcessingWithdrawal(false);
      setWithdrawSuccessMsg(
        `₵${amount.toLocaleString()} has been sent to ${
          selectedWallet === 'mtn'
            ? 'MTN MoMo'
            : selectedWallet === 'telecel'
            ? 'Telecel Cash'
            : 'AT Money'
        } (${walletPhoneInput}). Sub-60s settlement complete!`
      );
      try {
        confetti({
          particleCount: 80,
          spread: 60,
          origin: { y: 0.6 },
          colors: ['#00D4C8', '#10B981', '#F59E0B'],
        });
      } catch (_) {}
    }, 1200);
  };

  // Handle submit bid
  const handleConfirmBid = () => {
    if (!bidAmount) return;
    setBidSubmittedSuccess(true);
    try {
      confetti({
        particleCount: 60,
        spread: 50,
        origin: { y: 0.6 },
        colors: ['#00D4C8', '#F59E0B'],
      });
    } catch (_) {}
    setTimeout(() => {
      setIsBidModalOpen(false);
      setBidSubmittedSuccess(false);
      setSelectedJobForBid(null);
      setBidAmount('');
      setBidCoverLetter('');
    }, 1500);
  };

  // Handle milestone submit
  const handleConfirmDeliverable = () => {
    if (!selectedContractForSubmit) return;
    setDeliverableSubmitted(true);
    try {
      confetti({
        particleCount: 70,
        spread: 55,
        origin: { y: 0.6 },
        colors: ['#10B981', '#00D4C8'],
      });
    } catch (_) {}
    setTimeout(() => {
      setActiveContracts((prev) =>
        prev.map((c) =>
          c.id === selectedContractForSubmit.id
            ? { ...c, milestoneStatus: 'submitted', progressPercent: 100 }
            : c
        )
      );
      setIsDeliverableModalOpen(false);
      setDeliverableSubmitted(false);
      setSelectedContractForSubmit(null);
      setDeliverableNotes('');
    }, 1500);
  };

  return (
    <div className="min-h-screen bg-[var(--bg)] text-[var(--tx)] flex">
      {/* ══════ DESKTOP SIDEBAR ══════ */}
      <div className="hidden md:block">
        <ProviderSidebar />
      </div>

      {/* ══════ MOBILE SIDEBAR DRAWER ══════ */}
      {mobileMenuOpen && (
        <div className="fixed inset-0 z-50 flex md:hidden animate-in fade-in duration-200">
          <div
            className="fixed inset-0 bg-slate-950/70 backdrop-blur-xs"
            onClick={() => setMobileMenuOpen(false)}
          />
          <div className="relative z-10 w-72 h-full bg-[var(--surface)] shadow-2xl">
            <ProviderSidebar onCloseMobile={() => setMobileMenuOpen(false)} />
          </div>
        </div>
      )}

      {/* ══════ MAIN CONTENT AREA ══════ */}
      <div className="flex-1 flex flex-col min-w-0 pb-20 md:pb-10">
        {/* Top Header */}
        <ProviderHeader
          onOpenMobileMenu={() => setMobileMenuOpen(true)}
          onOpenWithdrawModal={() => {
            setWithdrawSuccessMsg('');
            setIsWithdrawModalOpen(true);
          }}
        />

        {/* Dashboard Content Container */}
        <main className="p-4 sm:p-6 md:p-8 max-w-7xl w-full mx-auto space-y-6">
          {/* ══════ WELCOME & ARTISAN IDENTITY HERO BANNER ══════ */}
          <div className="relative overflow-hidden rounded-[24px] bg-gradient-to-r from-[var(--surface-elevated)] via-[var(--surface)] to-[var(--surface-elevated)] border border-[var(--cyan)]/25 p-6 md:p-8 shadow-sm">
            <div className="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-[var(--cyan)]/10 blur-3xl pointer-events-none" />
            <div className="absolute -bottom-24 -left-24 w-72 h-72 rounded-full bg-blue-500/10 blur-3xl pointer-events-none" />

            <div className="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
              <div className="flex items-start gap-4">
                <div className="w-16 h-16 rounded-[20px] bg-gradient-to-tr from-[var(--cyan)] via-[#00A89D] to-blue-500 text-slate-950 font-black text-2xl flex items-center justify-center shrink-0 shadow-lg shadow-cyan-500/20 ring-4 ring-[var(--surface)]">
                  {user?.first_name ? user.first_name[0] : 'K'}
                </div>
                <div>
                  <div className="flex flex-wrap items-center gap-2 mb-1">
                    <h2 className="text-xl md:text-2xl font-black text-[var(--tx)] tracking-tight">
                      {user?.first_name ? `${user.first_name} ${user.last_name}` : 'Kwame Asante'}
                    </h2>
                    <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-[#10B981]/15 text-[#10B981] border border-[#10B981]/30">
                      <ShieldCheck className="w-3.5 h-3.5 stroke-[3]" />
                      <span>Ghana Card Verified</span>
                    </span>
                  </div>
                  <p className="text-xs md:text-sm font-semibold text-[var(--cyan)] flex items-center gap-2 mb-2">
                    <span>{user?.trade || 'Master POP Ceilings & Masonry Artisan'}</span>
                    <span>&bull;</span>
                    <span className="text-[var(--tx-3)] font-normal flex items-center gap-1">
                      <MapPin className="w-3.5 h-3.5" />
                      <span>{user?.location || 'East Legon, Accra'}</span>
                    </span>
                  </p>

                  {/* Verification badges row */}
                  <div className="flex flex-wrap items-center gap-2 text-[11px]">
                    <span className="px-2.5 py-0.5 rounded-lg bg-[var(--surface)] border border-[var(--bd2)] text-[var(--tx-2)] font-mono">
                      GHA-722019482-1
                    </span>
                    <span className="px-2.5 py-0.5 rounded-lg bg-[var(--surface)] border border-[var(--bd2)] text-[var(--tx-2)] flex items-center gap-1">
                      <Smartphone className="w-3 h-3 text-[#10B981]" />
                      <span>SMS Verified</span>
                    </span>
                    <span className="px-2.5 py-0.5 rounded-lg bg-amber-500/10 border border-amber-500/25 text-amber-400 font-bold flex items-center gap-1">
                      <Star className="w-3 h-3 fill-current" />
                      <span>5.0 (24 Reviews)</span>
                    </span>
                  </div>
                </div>
              </div>

              {/* Profile completeness & Action buttons */}
              <div className="w-full lg:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div className="p-3.5 rounded-[18px] bg-[var(--surface)] border border-[var(--bd2)] min-w-[190px]">
                  <div className="flex items-center justify-between text-xs mb-1.5">
                    <span className="font-bold text-[var(--tx-2)]">Profile Strength</span>
                    <span className="font-extrabold text-[var(--cyan)]">92%</span>
                  </div>
                  <div className="w-full h-2 rounded-full bg-[var(--bd2)] overflow-hidden">
                    <div className="h-full bg-gradient-to-r from-[var(--cyan)] to-[#10B981] rounded-full w-[92%]" />
                  </div>
                  <div className="text-[10px] text-[var(--tx-3)] mt-1.5 flex items-center gap-1">
                    <Check className="w-3 h-3 text-[#10B981]" />
                    <span>Eligible for top-ranked gig matching</span>
                  </div>
                </div>

                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => {
                      setWithdrawSuccessMsg('');
                      setIsWithdrawModalOpen(true);
                    }}
                    className="flex-1 sm:flex-initial h-11 px-4 rounded-[16px] bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-md shadow-cyan-500/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
                  >
                    <Wallet className="w-4 h-4" />
                    <span>Cash Out</span>
                  </button>

                  <a
                    href="#recommended-jobs"
                    className="flex-1 sm:flex-initial h-11 px-4 rounded-[16px] border border-[var(--bd2)] hover:border-[var(--cyan)] bg-[var(--surface)] text-[var(--tx)] font-bold text-xs flex items-center justify-center gap-1.5 transition-colors"
                  >
                    <Search className="w-4 h-4 text-[var(--cyan)]" />
                    <span>Find Gigs</span>
                  </a>
                </div>
              </div>
            </div>
          </div>

          {/* ══════ KEY STATS CARDS (FINTECH & ESCROW) ══════ */}
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {/* 1. Available Escrow Wallet */}
            <div className="p-5 rounded-[22px] bg-[var(--surface)] border border-[var(--bd2)] hover:border-[var(--cyan)]/50 transition-all shadow-xs group">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-bold text-[var(--tx-2)]">Available Balance</span>
                <div className="w-9 h-9 rounded-[12px] bg-[var(--cyan)]/10 text-[var(--cyan)] flex items-center justify-center group-hover:scale-110 transition-transform">
                  <Wallet className="w-4 h-4 stroke-[2.5]" />
                </div>
              </div>
              <div className="text-2xl font-black text-[var(--tx)] tracking-tight">
                ₵{availableBalance.toLocaleString()}.00
              </div>
              <div className="mt-2.5 flex items-center justify-between">
                <span className="text-[11px] text-[#10B981] font-bold flex items-center gap-1">
                  <Sparkles className="w-3 h-3" />
                  <span>Ready for MoMo</span>
                </span>
                <button
                  type="button"
                  onClick={() => {
                    setWithdrawSuccessMsg('');
                    setIsWithdrawModalOpen(true);
                  }}
                  className="text-[11px] font-extrabold text-[var(--cyan)] hover:underline flex items-center gap-0.5 cursor-pointer"
                >
                  <span>Withdraw</span>
                  <ArrowUpRight className="w-3 h-3" />
                </button>
              </div>
            </div>

            {/* 2. Locked Escrow Balance */}
            <div className="p-5 rounded-[22px] bg-[var(--surface)] border border-[var(--bd2)] hover:border-amber-500/50 transition-all shadow-xs group">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-bold text-[var(--tx-2)]">Locked in Escrow</span>
                <div className="w-9 h-9 rounded-[12px] bg-amber-500/10 text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                  <ShieldCheck className="w-4 h-4 stroke-[2.5]" />
                </div>
              </div>
              <div className="text-2xl font-black text-[var(--tx)] tracking-tight">
                ₵{pendingEscrowBalance.toLocaleString()}.00
              </div>
              <div className="mt-2.5 flex items-center gap-1.5 text-[11px] text-[var(--tx-3)]">
                <Clock className="w-3 h-3 text-amber-400" />
                <span>2 Active milestones pending sign-off</span>
              </div>
            </div>

            {/* 3. Active Contracts */}
            <div className="p-5 rounded-[22px] bg-[var(--surface)] border border-[var(--bd2)] hover:border-blue-500/50 transition-all shadow-xs group">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-bold text-[var(--tx-2)]">Active Contracts</span>
                <div className="w-9 h-9 rounded-[12px] bg-blue-500/10 text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                  <Briefcase className="w-4 h-4 stroke-[2.5]" />
                </div>
              </div>
              <div className="text-2xl font-black text-[var(--tx)] tracking-tight">
                {activeContracts.length} Ongoing
              </div>
              <div className="mt-2.5 flex items-center justify-between text-[11px]">
                <span className="text-[var(--tx-3)]">1 Delivery due this week</span>
                <a href="#active-contracts" className="font-bold text-blue-400 hover:underline">
                  View &rarr;
                </a>
              </div>
            </div>

            {/* 4. Career Earnings & Success Rate */}
            <div className="p-5 rounded-[22px] bg-[var(--surface)] border border-[var(--bd2)] hover:border-[#10B981]/50 transition-all shadow-xs group">
              <div className="flex items-center justify-between mb-3">
                <span className="text-xs font-bold text-[var(--tx-2)]">Career Earnings</span>
                <div className="w-9 h-9 rounded-[12px] bg-[#10B981]/10 text-[#10B981] flex items-center justify-center group-hover:scale-110 transition-transform">
                  <TrendingUp className="w-4 h-4 stroke-[2.5]" />
                </div>
              </div>
              <div className="text-2xl font-black text-[var(--tx)] tracking-tight">
                ₵{totalCareerEarned.toLocaleString()}.00
              </div>
              <div className="mt-2.5 flex items-center justify-between text-[11px]">
                <span className="text-[#10B981] font-bold">99% Approval Rate</span>
                <span className="text-[var(--tx-3)]">18 Jobs Done</span>
              </div>
            </div>
          </div>

          {/* ══════ MAIN DASHBOARD DUAL COLUMN ══════ */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* ── LEFT COLUMN: WORKFLOWS (2 COLS) ── */}
            <div className="lg:col-span-2 space-y-6">
              {/* SECTION: ACTIVE CONTRACTS */}
              <section id="active-contracts" className="space-y-3">
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-2">
                    <Briefcase className="w-4 h-4 text-[var(--cyan)]" />
                    <h3 className="text-sm font-extrabold text-[var(--tx)] uppercase tracking-wider">
                      Active Milestone Contracts ({activeContracts.length})
                    </h3>
                  </div>
                  <span className="text-xs text-[var(--tx-3)] font-medium">Bank-grade Escrow Protected</span>
                </div>

                <div className="space-y-3">
                  {activeContracts.map((contract) => (
                    <div
                      key={contract.id}
                      className="p-5 rounded-[22px] bg-[var(--surface)] border border-[var(--bd2)] hover:border-[var(--bd)] transition-all space-y-3.5 shadow-xs"
                    >
                      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                          <h4 className="text-sm sm:text-base font-extrabold text-[var(--tx)]">
                            {contract.title}
                          </h4>
                          <div className="text-xs text-[var(--tx-3)] flex items-center gap-2 mt-0.5">
                            <span className="font-semibold text-[var(--tx-2)]">{contract.clientName}</span>
                            <span>&bull;</span>
                            <span className="flex items-center gap-1">
                              <MapPin className="w-3 h-3" />
                              <span>{contract.clientLocation}</span>
                            </span>
                          </div>
                        </div>
                        <div className="text-left sm:text-right">
                          <div className="text-xs font-bold text-[var(--tx-3)]">Contract Value</div>
                          <div className="text-base font-black text-[var(--cyan)]">
                            ₵{contract.totalBudget.toLocaleString()}.00
                          </div>
                        </div>
                      </div>

                      {/* Milestone details box */}
                      <div className="p-3.5 rounded-[16px] bg-[var(--surface-elevated)] border border-[var(--bd2)]/60 space-y-2">
                        <div className="flex items-center justify-between text-xs">
                          <span className="font-bold text-[var(--tx)] truncate max-w-[280px]">
                            {contract.currentMilestone}
                          </span>
                          <span className="font-extrabold text-[#10B981] ml-2 shrink-0">
                            ₵{contract.milestoneBudget.toLocaleString()}.00 Escrow Held
                          </span>
                        </div>

                        {/* Progress meter */}
                        <div className="space-y-1">
                          <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)]">
                            <span>{contract.dueDate}</span>
                            <span>{contract.progressPercent}% Completed</span>
                          </div>
                          <div className="w-full h-2 bg-[var(--bd2)] rounded-full overflow-hidden">
                            <div
                              className="h-full bg-gradient-to-r from-[var(--cyan)] to-[#10B981] rounded-full transition-all duration-500"
                              style={{ width: `${contract.progressPercent}%` }}
                            />
                          </div>
                        </div>
                      </div>

                      {/* Action buttons */}
                      <div className="flex flex-wrap items-center justify-between gap-2 pt-1">
                        <span className="text-[11px] text-[var(--tx-3)] flex items-center gap-1.5">
                          <CheckCircle2 className="w-3.5 h-3.5 text-[#10B981]" />
                          <span>Escrow deposited &amp; locked</span>
                        </span>

                        <div className="flex items-center gap-2 ml-auto">
                          <button
                            type="button"
                            onClick={() => {
                              setSelectedContractForSubmit(contract);
                              setIsDeliverableModalOpen(true);
                            }}
                            disabled={contract.milestoneStatus === 'submitted'}
                            className={`h-9 px-4 rounded-[12px] font-extrabold text-xs flex items-center gap-1.5 transition-all cursor-pointer ${
                              contract.milestoneStatus === 'submitted'
                                ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30 cursor-default'
                                : 'bg-[var(--cyan)] text-slate-950 hover:opacity-90 shadow-xs'
                            }`}
                          >
                            <FileCheck className="w-3.5 h-3.5" />
                            <span>
                              {contract.milestoneStatus === 'submitted'
                                ? 'Submitted for Review'
                                : 'Submit Deliverable'}
                            </span>
                          </button>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </section>

              {/* SECTION: RECOMMENDED GIGS */}
              <section id="recommended-jobs" className="space-y-3 pt-2">
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                  <div className="flex items-center gap-2">
                    <Search className="w-4 h-4 text-[var(--cyan)]" />
                    <h3 className="text-sm font-extrabold text-[var(--tx)] uppercase tracking-wider">
                      Recommended Ghanaian Gigs
                    </h3>
                  </div>

                  {/* Filter Pills */}
                  <div className="flex items-center gap-1 overflow-x-auto text-[11px]">
                    <button
                      type="button"
                      onClick={() => setGigFilter('all')}
                      className={`px-2.5 py-1 rounded-full font-bold transition-all cursor-pointer ${
                        gigFilter === 'all'
                          ? 'bg-[var(--cyan)] text-slate-950 font-black'
                          : 'bg-[var(--surface)] text-[var(--tx-2)] border border-[var(--bd2)]'
                      }`}
                    >
                      All Matches
                    </button>
                    <button
                      type="button"
                      onClick={() => setGigFilter('urgent')}
                      className={`px-2.5 py-1 rounded-full font-bold transition-all cursor-pointer ${
                        gigFilter === 'urgent'
                          ? 'bg-rose-500 text-white font-black'
                          : 'bg-[var(--surface)] text-[var(--tx-2)] border border-[var(--bd2)]'
                      }`}
                    >
                      Urgent (24h)
                    </button>
                    <button
                      type="button"
                      onClick={() => setGigFilter('high_budget')}
                      className={`px-2.5 py-1 rounded-full font-bold transition-all cursor-pointer ${
                        gigFilter === 'high_budget'
                          ? 'bg-amber-500 text-slate-950 font-black'
                          : 'bg-[var(--surface)] text-[var(--tx-2)] border border-[var(--bd2)]'
                      }`}
                    >
                      &gt; ₵4,000
                    </button>
                    <button
                      type="button"
                      onClick={() => setGigFilter('accra')}
                      className={`px-2.5 py-1 rounded-full font-bold transition-all cursor-pointer ${
                        gigFilter === 'accra'
                          ? 'bg-[var(--cyan)] text-slate-950 font-black'
                          : 'bg-[var(--surface)] text-[var(--tx-2)] border border-[var(--bd2)]'
                      }`}
                    >
                      Accra Hub
                    </button>
                  </div>
                </div>

                <div className="space-y-3">
                  {filteredGigs.map((job) => (
                    <div
                      key={job.id}
                      className="p-5 rounded-[22px] bg-[var(--surface)] border border-[var(--bd2)] hover:border-[var(--cyan)]/40 transition-all space-y-3 shadow-xs"
                    >
                      <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                        <div className="space-y-1">
                          <div className="flex flex-wrap items-center gap-1.5">
                            {job.isUrgent && (
                              <span className="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/15 text-rose-400 border border-rose-500/25 uppercase tracking-wider">
                                Urgent
                              </span>
                            )}
                            {job.isEscrowFunded && (
                              <span className="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-[#10B981]/15 text-[#10B981] border border-[#10B981]/25 flex items-center gap-1">
                                <ShieldCheck className="w-3 h-3" />
                                <span>Escrow Pre-Funded</span>
                              </span>
                            )}
                            <span className="text-[11px] text-[var(--tx-3)]">{job.postedTime}</span>
                          </div>

                          <h4 className="text-sm sm:text-base font-extrabold text-[var(--tx)]">
                            {job.title}
                          </h4>

                          <div className="text-xs text-[var(--tx-3)] flex items-center gap-2">
                            <span className="font-semibold text-[var(--cyan)]">{job.category}</span>
                            <span>&bull;</span>
                            <span className="flex items-center gap-1">
                              <MapPin className="w-3 h-3" />
                              <span>{job.location}</span>
                            </span>
                            <span>&bull;</span>
                            <span>{job.proposalsCount} proposals sent</span>
                          </div>
                        </div>

                        <div className="text-left sm:text-right shrink-0">
                          <div className="text-xs font-bold text-[var(--tx-3)]">Cedi Budget</div>
                          <div className="text-base sm:text-lg font-black text-[var(--tx)]">
                            ₵{job.budgetMin.toLocaleString()} - ₵{job.budgetMax.toLocaleString()}
                          </div>
                        </div>
                      </div>

                      <div className="pt-2 border-t border-[var(--bd2)]/40 flex items-center justify-between gap-2">
                        <div className="text-xs text-[var(--tx-3)] flex items-center gap-1">
                          <Star className="w-3.5 h-3.5 text-amber-400 fill-current" />
                          <span className="font-bold text-[var(--tx)]">{job.clientRating}</span>
                          <span>Verified Employer</span>
                        </div>

                        <button
                          type="button"
                          onClick={() => {
                            setSelectedJobForBid(job);
                            setBidAmount(String(job.budgetMin));
                            setIsBidModalOpen(true);
                          }}
                          className="h-9 px-4 rounded-[12px] bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-sm shadow-cyan-500/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
                        >
                          <span>Quick Bid</span>
                          <ArrowRight className="w-3.5 h-3.5" />
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              </section>
            </div>

            {/* ── RIGHT COLUMN: SIDEBAR WIDGETS (1 COL) ── */}
            <div className="space-y-6">
              {/* WIDGET 1: MOMO PAYOUT WALLET */}
              <div id="momo-wallet" className="p-5 rounded-[22px] bg-[var(--surface)] border border-[var(--bd2)] space-y-4 shadow-xs">
                <div className="flex items-center justify-between pb-2 border-b border-[var(--bd2)]">
                  <div className="flex items-center gap-2">
                    <Wallet className="w-4 h-4 text-[var(--cyan)]" />
                    <h3 className="text-xs font-black text-[var(--tx)] uppercase tracking-wider">
                      Mobile Money Payout
                    </h3>
                  </div>
                  <span className="text-[10px] font-bold text-[#10B981] bg-[#10B981]/10 px-2 py-0.5 rounded-full">
                    Active
                  </span>
                </div>

                <div className="space-y-2">
                  <div className="text-xs text-[var(--tx-3)]">Primary Settlement Account</div>
                  <div className="p-3.5 rounded-[16px] bg-[var(--surface-elevated)] border border-[var(--bd2)] flex items-center justify-between">
                    <div className="flex items-center gap-2.5">
                      <div className="w-8 h-8 rounded-[10px] bg-amber-400/15 border border-amber-400/30 text-amber-500 font-black text-xs flex items-center justify-center">
                        MTN
                      </div>
                      <div>
                        <div className="text-xs font-bold text-[var(--tx)]">MTN MoMo</div>
                        <div className="text-[11px] font-mono text-[var(--tx-2)]">
                          {user?.phone || '024 412 3456'}
                        </div>
                      </div>
                    </div>
                    <CheckCircle2 className="w-4 h-4 text-[#10B981]" />
                  </div>
                </div>

                <div className="space-y-1.5 pt-1">
                  <div className="flex items-center justify-between text-xs">
                    <span className="text-[var(--tx-3)]">Available for Withdrawal</span>
                    <span className="font-extrabold text-[var(--tx)]">
                      ₵{availableBalance.toLocaleString()}.00
                    </span>
                  </div>
                  <button
                    type="button"
                    onClick={() => {
                      setWithdrawSuccessMsg('');
                      setIsWithdrawModalOpen(true);
                    }}
                    className="w-full h-11 rounded-[14px] bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-md shadow-cyan-500/20 hover:scale-[1.01] active:scale-[0.99] transition-all cursor-pointer"
                  >
                    <Wallet className="w-3.5 h-3.5" />
                    <span>Instant Cash-Out (Sub-60s)</span>
                  </button>
                  <p className="text-[10.5px] text-[var(--tx-3)] text-center pt-1">
                    Direct automated settlement via Paystack MoMo rails
                  </p>
                </div>
              </div>

              {/* WIDGET 2: TRUST & COMPLIANCE SHIELD */}
              <div className="p-5 rounded-[22px] bg-[var(--surface)] border border-[var(--bd2)] space-y-3.5 shadow-xs">
                <div className="flex items-center gap-2 pb-2 border-b border-[var(--bd2)]">
                  <ShieldCheck className="w-4 h-4 text-[var(--cyan)]" />
                  <h3 className="text-xs font-black text-[var(--tx)] uppercase tracking-wider">
                    Trust &amp; Verification Shield
                  </h3>
                </div>

                <div className="space-y-2.5 text-xs">
                  <div className="flex items-center justify-between p-2.5 rounded-[14px] bg-[var(--surface-elevated)]">
                    <div className="flex items-center gap-2">
                      <CheckCircle2 className="w-4 h-4 text-[#10B981]" />
                      <span className="font-semibold text-[var(--tx)]">Ghana Card Biometrics</span>
                    </div>
                    <span className="text-[10px] font-extrabold text-[#10B981]">PASSED</span>
                  </div>

                  <div className="flex items-center justify-between p-2.5 rounded-[14px] bg-[var(--surface-elevated)]">
                    <div className="flex items-center gap-2">
                      <CheckCircle2 className="w-4 h-4 text-[#10B981]" />
                      <span className="font-semibold text-[var(--tx)]">SMS Phone Ownership</span>
                    </div>
                    <span className="text-[10px] font-extrabold text-[#10B981]">VERIFIED</span>
                  </div>

                  <div className="flex items-center justify-between p-2.5 rounded-[14px] bg-[var(--surface-elevated)]">
                    <div className="flex items-center gap-2">
                      <CheckCircle2 className="w-4 h-4 text-[#10B981]" />
                      <span className="font-semibold text-[var(--tx)]">MoMo Escrow Rail</span>
                    </div>
                    <span className="text-[10px] font-extrabold text-[#10B981]">CONNECTED</span>
                  </div>

                  <div className="flex items-center justify-between p-2.5 rounded-[14px] bg-[var(--surface-elevated)] opacity-75">
                    <div className="flex items-center gap-2">
                      <Clock className="w-4 h-4 text-amber-400" />
                      <span className="font-semibold text-[var(--tx)]">Police Criminal Clearance</span>
                    </div>
                    <span className="text-[10px] font-extrabold text-amber-400">OPTIONAL</span>
                  </div>
                </div>
              </div>

              {/* WIDGET 3: CLIENT REVIEWS & RATINGS */}
              <div className="p-5 rounded-[22px] bg-[var(--surface)] border border-[var(--bd2)] space-y-3.5 shadow-xs">
                <div className="flex items-center justify-between pb-2 border-b border-[var(--bd2)]">
                  <div className="flex items-center gap-2">
                    <Star className="w-4 h-4 text-amber-400 fill-current" />
                    <h3 className="text-xs font-black text-[var(--tx)] uppercase tracking-wider">
                      Client Feedback (5.0 / 5.0)
                    </h3>
                  </div>
                  <span className="text-[10px] text-[var(--cyan)] font-bold">24 reviews</span>
                </div>

                <div className="space-y-3">
                  <div className="p-3 rounded-[16px] bg-[var(--surface-elevated)] space-y-1.5 text-xs">
                    <div className="flex items-center justify-between">
                      <span className="font-bold text-[var(--tx)]">Dr. Kwabena Frimpong</span>
                      <div className="flex text-amber-400">
                        {'★'.repeat(5)}
                      </div>
                    </div>
                    <p className="text-[11px] text-[var(--tx-2)] leading-relaxed italic">
                      "Kwame’s POP work in East Legon was impeccable. Clean corners, very polite, and finished two days ahead of schedule."
                    </p>
                    <span className="text-[9.5px] text-[var(--tx-3)]">1 week ago &bull; East Legon</span>
                  </div>

                  <div className="p-3 rounded-[16px] bg-[var(--surface-elevated)] space-y-1.5 text-xs">
                    <div className="flex items-center justify-between">
                      <span className="font-bold text-[var(--tx)]">Akua Osei-Tutu</span>
                      <div className="flex text-amber-400">
                        {'★'.repeat(5)}
                      </div>
                    </div>
                    <p className="text-[11px] text-[var(--tx-2)] leading-relaxed italic">
                      "Extremely reliable master craftsman. Delivered the tiles exactly as negotiated under escrow."
                    </p>
                    <span className="text-[9.5px] text-[var(--tx-3)]">3 weeks ago &bull; Cantonments</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>

      {/* ══════ MODAL 1: INSTANT MOMO CASHOUT MODAL ══════ */}
      {isWithdrawModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm animate-in fade-in duration-150">
          <div className="relative w-full max-w-md rounded-[24px] bg-[var(--surface)] border border-[var(--bd2)] shadow-2xl p-6 space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-[var(--bd2)]">
              <div className="flex items-center gap-2.5">
                <div className="w-8 h-8 rounded-[10px] bg-[var(--cyan)]/10 text-[var(--cyan)] flex items-center justify-center">
                  <Wallet className="w-4 h-4" />
                </div>
                <div>
                  <h3 className="text-sm font-extrabold text-[var(--tx)]">
                    Instant MoMo Withdrawal
                  </h3>
                  <p className="text-[10.5px] text-[var(--tx-3)]">Sub-60s Automated Settlement</p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => setIsWithdrawModalOpen(false)}
                className="p-1 rounded-lg text-[var(--tx-3)] hover:text-[var(--tx)] cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {withdrawSuccessMsg ? (
              <div className="py-6 text-center space-y-3">
                <div className="w-12 h-12 rounded-full bg-[#10B981]/15 text-[#10B981] flex items-center justify-center mx-auto">
                  <CheckCircle2 className="w-6 h-6 stroke-[2.5]" />
                </div>
                <h4 className="text-base font-black text-[var(--tx)]">Cash-Out Successful!</h4>
                <p className="text-xs text-[var(--tx-2)] leading-relaxed px-4">
                  {withdrawSuccessMsg}
                </p>
                <button
                  type="button"
                  onClick={() => setIsWithdrawModalOpen(false)}
                  className="w-full h-11 rounded-[16px] bg-[var(--cyan)] text-slate-950 font-black text-xs mt-2 cursor-pointer shadow-sm"
                >
                  Done
                </button>
              </div>
            ) : (
              <div className="space-y-4">
                {/* Available balance reminder */}
                <div className="p-3.5 rounded-[16px] bg-[var(--surface-elevated)] border border-[var(--bd2)] flex items-center justify-between">
                  <span className="text-xs text-[var(--tx-2)] font-semibold">Available Balance:</span>
                  <span className="text-sm font-black text-[var(--cyan)]">
                    ₵{availableBalance.toLocaleString()}.00
                  </span>
                </div>

                {/* Amount input */}
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Amount to Withdraw (Cedis)</label>
                  <input
                    type="number"
                    value={withdrawAmount}
                    onChange={(e) => setWithdrawAmount(e.target.value)}
                    placeholder="500"
                    max={availableBalance}
                    className="w-full h-12 px-4 rounded-[16px] bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] text-base font-mono font-bold focus:border-[var(--cyan)] focus:outline-none"
                  />
                </div>

                {/* Quick amount chips */}
                <div className="grid grid-cols-4 gap-2">
                  {['250', '500', '1000', String(availableBalance)].map((amt, idx) => (
                    <button
                      key={amt}
                      type="button"
                      onClick={() => setWithdrawAmount(amt)}
                      className="py-1.5 px-2 rounded-[12px] bg-[var(--surface-elevated)] border border-[var(--bd2)] hover:border-[var(--cyan)] text-xs font-bold text-[var(--tx-2)] hover:text-[var(--tx)] transition-all cursor-pointer text-center"
                    >
                      {idx === 3 ? 'All' : `₵${amt}`}
                    </button>
                  ))}
                </div>

                {/* Wallet selector */}
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Settlement Wallet</label>
                  <div className="grid grid-cols-3 gap-2">
                    {[
                      { id: 'mtn', label: 'MTN MoMo' },
                      { id: 'telecel', label: 'Telecel Cash' },
                      { id: 'at', label: 'AT Money' },
                    ].map((w) => (
                      <button
                        key={w.id}
                        type="button"
                        onClick={() => setSelectedWallet(w.id as any)}
                        className={`py-2 px-2 rounded-[14px] text-xs font-bold border transition-all cursor-pointer ${
                          selectedWallet === w.id
                            ? 'border-[var(--cyan)] bg-[var(--cyan)]/10 text-[var(--cyan)] font-black'
                            : 'border-[var(--bd2)] bg-[var(--surface-elevated)] text-[var(--tx-2)]'
                        }`}
                      >
                        {w.label}
                      </button>
                    ))}
                  </div>
                </div>

                {/* Phone confirmation */}
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Recipient Mobile Number</label>
                  <input
                    type="tel"
                    value={walletPhoneInput}
                    onChange={(e) => setWalletPhoneInput(e.target.value)}
                    className="w-full h-11 px-4 rounded-[16px] bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] text-xs font-mono font-bold focus:border-[var(--cyan)] focus:outline-none"
                  />
                </div>

                {/* CTA button */}
                <button
                  type="button"
                  onClick={handleConfirmWithdrawal}
                  disabled={isProcessingWithdrawal || Number(withdrawAmount) <= 0 || Number(withdrawAmount) > availableBalance}
                  className="w-full h-12 rounded-[18px] bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-cyan-500/25 hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed transition-all cursor-pointer"
                >
                  {isProcessingWithdrawal ? (
                    <>
                      <span className="w-4 h-4 rounded-full border-2 border-slate-950 border-t-transparent animate-spin" />
                      <span>Settling via MoMo Rail...</span>
                    </>
                  ) : (
                    <>
                      <span>Confirm Instant Cash-Out</span>
                      <ArrowRight className="w-4 h-4 stroke-[2.5]" />
                    </>
                  )}
                </button>
              </div>
            )}
          </div>
        </div>
      )}

      {/* ══════ MODAL 2: QUICK BID / PROPOSAL MODAL ══════ */}
      {isBidModalOpen && selectedJobForBid && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm animate-in fade-in duration-150">
          <div className="relative w-full max-w-lg rounded-[24px] bg-[var(--surface)] border border-[var(--bd2)] shadow-2xl p-6 space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-[var(--bd2)]">
              <div>
                <h3 className="text-sm font-extrabold text-[var(--tx)]">
                  Submit Proposal &amp; Bid
                </h3>
                <p className="text-[11px] text-[var(--cyan)] font-semibold truncate max-w-xs">
                  {selectedJobForBid.title}
                </p>
              </div>
              <button
                type="button"
                onClick={() => setIsBidModalOpen(false)}
                className="p-1 rounded-lg text-[var(--tx-3)] hover:text-[var(--tx)] cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {bidSubmittedSuccess ? (
              <div className="py-6 text-center space-y-2">
                <div className="w-12 h-12 rounded-full bg-[#10B981]/15 text-[#10B981] flex items-center justify-center mx-auto">
                  <CheckCircle2 className="w-6 h-6 stroke-[2.5]" />
                </div>
                <h4 className="text-base font-black text-[var(--tx)]">Proposal Sent to Client!</h4>
                <p className="text-xs text-[var(--tx-2)]">
                  Your Ghana Card verified quote of ₵{Number(bidAmount).toLocaleString()} has been dispatched.
                </p>
              </div>
            ) : (
              <div className="space-y-4">
                <div className="grid grid-cols-2 gap-3">
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-[var(--tx)]">Proposed Bid (Cedis)</label>
                    <input
                      type="number"
                      value={bidAmount}
                      onChange={(e) => setBidAmount(e.target.value)}
                      placeholder="1500"
                      className="w-full h-11 px-4 rounded-[14px] bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] text-sm font-bold font-mono focus:border-[var(--cyan)] focus:outline-none"
                    />
                  </div>
                  <div className="space-y-1.5">
                    <label className="text-xs font-bold text-[var(--tx)]">Completion Days</label>
                    <input
                      type="number"
                      value={bidDays}
                      onChange={(e) => setBidDays(e.target.value)}
                      placeholder="3"
                      className="w-full h-11 px-4 rounded-[14px] bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] text-sm font-bold font-mono focus:border-[var(--cyan)] focus:outline-none"
                    />
                  </div>
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Artisan Pitch / Cover Note</label>
                  <textarea
                    rows={3}
                    value={bidCoverLetter}
                    onChange={(e) => setBidCoverLetter(e.target.value)}
                    placeholder="Hello! I am a verified master craftsman with 8+ years experience in Accra. I have specialized tools and can complete this work seamlessly..."
                    className="w-full p-3 rounded-[14px] bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] text-xs focus:border-[var(--cyan)] focus:outline-none"
                  />
                </div>

                <div className="p-3 rounded-[14px] bg-[var(--cyan)]/10 border border-[var(--cyan)]/25 text-[11px] text-[var(--tx-2)] flex items-center gap-2">
                  <ShieldCheck className="w-4 h-4 text-[var(--cyan)] shrink-0" />
                  <span>
                    Your bid will highlight your <strong>Ghana Card Verified</strong> badge and <strong>5.0 rating</strong>.
                  </span>
                </div>

                <button
                  type="button"
                  onClick={handleConfirmBid}
                  disabled={!bidAmount}
                  className="w-full h-12 rounded-[18px] bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-cyan-500/25 hover:scale-[1.01] active:scale-[0.99] transition-all cursor-pointer"
                >
                  <Send className="w-4 h-4" />
                  <span>Send Proposal to Client</span>
                </button>
              </div>
            )}
          </div>
        </div>
      )}

      {/* ══════ MODAL 3: SUBMIT DELIVERABLE MODAL ══════ */}
      {isDeliverableModalOpen && selectedContractForSubmit && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-sm animate-in fade-in duration-150">
          <div className="relative w-full max-w-md rounded-[24px] bg-[var(--surface)] border border-[var(--bd2)] shadow-2xl p-6 space-y-4">
            <div className="flex items-center justify-between pb-3 border-b border-[var(--bd2)]">
              <div>
                <h3 className="text-sm font-extrabold text-[var(--tx)]">
                  Submit Milestone Deliverable
                </h3>
                <p className="text-[11px] text-[var(--tx-3)] truncate max-w-xs">
                  {selectedContractForSubmit.currentMilestone}
                </p>
              </div>
              <button
                type="button"
                onClick={() => setIsDeliverableModalOpen(false)}
                className="p-1 rounded-lg text-[var(--tx-3)] hover:text-[var(--tx)] cursor-pointer"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {deliverableSubmitted ? (
              <div className="py-6 text-center space-y-2">
                <div className="w-12 h-12 rounded-full bg-[#10B981]/15 text-[#10B981] flex items-center justify-center mx-auto">
                  <CheckCircle2 className="w-6 h-6 stroke-[2.5]" />
                </div>
                <h4 className="text-base font-black text-[var(--tx)]">Milestone Submitted!</h4>
                <p className="text-xs text-[var(--tx-2)]">
                  Client has been notified to inspect work and approve ₵{selectedContractForSubmit.milestoneBudget.toLocaleString()} escrow release.
                </p>
              </div>
            ) : (
              <div className="space-y-4">
                <div className="p-3.5 rounded-[16px] bg-[var(--surface-elevated)] border border-[var(--bd2)] flex items-center justify-between">
                  <span className="text-xs text-[var(--tx-2)] font-semibold">Milestone Escrow Value:</span>
                  <span className="text-sm font-black text-[#10B981]">
                    ₵{selectedContractForSubmit.milestoneBudget.toLocaleString()}.00
                  </span>
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Completion Summary &amp; Notes</label>
                  <textarea
                    rows={4}
                    value={deliverableNotes}
                    onChange={(e) => setDeliverableNotes(e.target.value)}
                    placeholder="Describe completed work (e.g., Completed living room framework, jointing, sanding, and smooth primer application ready for inspection)..."
                    className="w-full p-3 rounded-[14px] bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] text-xs focus:border-[var(--cyan)] focus:outline-none"
                  />
                </div>

                <button
                  type="button"
                  onClick={handleConfirmDeliverable}
                  className="w-full h-12 rounded-[18px] bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-cyan-500/25 hover:scale-[1.01] active:scale-[0.99] transition-all cursor-pointer"
                >
                  <FileCheck className="w-4 h-4" />
                  <span>Notify Client &amp; Request Release</span>
                </button>
              </div>
            )}
          </div>
        </div>
      )}

      {/* ══════ MOBILE BOTTOM NAVIGATION BAR ══════ */}
      <nav className="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-[var(--surface)]/95 backdrop-blur-md border-t border-[var(--bd2)] z-40 flex items-center justify-around px-2">
        <a
          href="/provider/dashboard"
          className="flex flex-col items-center gap-1 text-[var(--cyan)] font-extrabold text-[10px]"
        >
          <Briefcase className="w-4 h-4" />
          <span>Overview</span>
        </a>
        <a
          href="#active-contracts"
          className="flex flex-col items-center gap-1 text-[var(--tx-3)] hover:text-[var(--tx)] font-semibold text-[10px]"
        >
          <FileCheck className="w-4 h-4" />
          <span>Contracts</span>
        </a>
        <a
          href="#recommended-jobs"
          className="flex flex-col items-center gap-1 text-[var(--tx-3)] hover:text-[var(--tx)] font-semibold text-[10px]"
        >
          <Search className="w-4 h-4" />
          <span>Gigs</span>
        </a>
        <button
          type="button"
          onClick={() => {
            setWithdrawSuccessMsg('');
            setIsWithdrawModalOpen(true);
          }}
          className="flex flex-col items-center gap-1 text-[var(--tx-3)] hover:text-[var(--tx)] font-semibold text-[10px] cursor-pointer"
        >
          <Wallet className="w-4 h-4" />
          <span>MoMo</span>
        </button>
        <Link
          href="/"
          className="flex flex-col items-center gap-1 text-[var(--tx-3)] hover:text-[var(--tx)] font-semibold text-[10px]"
        >
          <ArrowRight className="w-4 h-4" />
          <span>Home</span>
        </Link>
      </nav>
    </div>
  );
}
