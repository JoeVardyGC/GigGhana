'use client';

import React, { useState } from 'react';
import { useAuth } from '@/lib/context/AuthContext';
import confetti from 'canvas-confetti';
import {
  ShieldCheck,
  Smartphone,
  CheckCircle2,
  Clock,
  Lock,
  ArrowRight,
  Sparkles,
  Search,
  MessageSquare,
  Bell,
  LogOut,
  ChevronRight,
  TrendingUp,
  FileText,
  Briefcase,
  BadgeCheck,
  Star,
  ExternalLink,
  Plus,
  Send,
  X,
  Check,
} from 'lucide-react';

export default function ProviderDashboardPage() {
  const { user, logout } = useAuth();

  // Active state
  const [availableBalance, setAvailableBalance] = useState(2450);
  const [escrowBalance, setEscrowBalance] = useState(4500);
  const [isWithdrawModalOpen, setIsWithdrawModalOpen] = useState(false);
  const [withdrawAmount, setWithdrawAmount] = useState('1000');
  const [withdrawNetwork, setWithdrawNetwork] = useState<'mtn' | 'telecel' | 'at'>('mtn');
  const [isWithdrawing, setIsWithdrawing] = useState(false);
  const [withdrawSuccess, setWithdrawSuccess] = useState(false);

  // Milestone deliverable submit state
  const [isDeliverableModalOpen, setIsDeliverableModalOpen] = useState(false);
  const [deliverableNotes, setDeliverableNotes] = useState('');
  const [deliverableSubmitted, setDeliverableSubmitted] = useState(false);

  const handleWithdraw = async (e: React.FormEvent) => {
    e.preventDefault();
    const amount = Number(withdrawAmount);
    if (amount <= 0 || amount > availableBalance) {
      alert('Please enter a valid amount within your available balance.');
      return;
    }

    setIsWithdrawing(true);
    await new Promise((res) => setTimeout(res, 1200));

    setAvailableBalance((prev) => prev - amount);
    setIsWithdrawing(false);
    setWithdrawSuccess(true);

    try {
      confetti({
        particleCount: 80,
        spread: 60,
        origin: { y: 0.6 },
        colors: ['#00D4C8', '#F59E0B', '#10B981'],
      });
    } catch (_) {}

    setTimeout(() => {
      setWithdrawSuccess(false);
      setIsWithdrawModalOpen(false);
    }, 2000);
  };

  const handleDeliverableSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setDeliverableSubmitted(true);
    try {
      confetti({
        particleCount: 60,
        spread: 50,
        origin: { y: 0.6 },
        colors: ['#00D4C8', '#10B981'],
      });
    } catch (_) {}
    setTimeout(() => {
      setIsDeliverableModalOpen(false);
      setDeliverableSubmitted(false);
      setDeliverableNotes('');
    }, 1500);
  };

  const displayName = user ? `${user.first_name} ${user.last_name}` : 'Kwame Asante';
  const displayTrade = user?.trade || 'Master POP Ceiling Designer & Decorative Plasterer';
  const displayPhone = user?.phone || '024 412 3456';
  const displayTier = user?.membership_tier === 'premium' ? '⭐ Premium Master' : '👑 Verified Pro';

  return (
    <div className="min-h-screen bg-[var(--bg)] text-[var(--tx)] font-sans selection:bg-[var(--cyan)] selection:text-black">
      {/* ══════ DASHBOARD NAVBAR ══════ */}
      <header className="sticky top-0 z-40 w-full border-b border-[var(--bd2)] bg-[var(--surface)]/90 backdrop-blur-xl px-4 sm:px-8 py-3.5">
        <div className="max-w-7xl mx-auto flex items-center justify-between gap-4">
          <div className="flex items-center gap-6">
            <a href="/" className="flex items-center gap-2.5">
              <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#00D4C8] via-[#008B82] to-[#F59E0B] flex items-center justify-center text-slate-950 font-black text-base shadow-xs">
                G
              </div>
              <div className="text-lg font-black tracking-tight text-[var(--tx)]">
                Gig<span className="text-[var(--cyan)]">Ghana</span>
              </div>
            </a>

            <nav className="hidden md:flex items-center gap-5 text-xs font-bold text-[var(--tx-2)]">
              <a href="/dashboard/provider" className="text-[var(--cyan)] flex items-center gap-1.5">
                <Briefcase className="w-3.5 h-3.5" />
                <span>My Workspace</span>
              </a>
              <a href="/jobs" className="hover:text-[var(--tx)] transition-colors flex items-center gap-1.5">
                <Search className="w-3.5 h-3.5" />
                <span>Find Open Gigs</span>
              </a>
              <a href="/search/providers" className="hover:text-[var(--tx)] transition-colors">
                Artisans Directory
              </a>
            </nav>
          </div>

          <div className="flex items-center gap-3">
            {/* Ghana Card Verified Chip */}
            <div className="hidden sm:inline-flex items-center gap-1 text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border border-emerald-500/30 px-2.5 py-1 rounded-full">
              <BadgeCheck className="w-3.5 h-3.5" />
              <span>Ghana Card Verified</span>
            </div>

            {/* Notification Icon */}
            <button className="w-9 h-9 rounded-xl border border-[var(--bd2)] bg-[var(--surface)] flex items-center justify-center text-[var(--tx-2)] hover:text-[var(--tx)] relative">
              <Bell className="w-4 h-4" />
              <span className="absolute top-2 right-2 w-2 h-2 rounded-full bg-[var(--cyan)]" />
            </button>

            {/* User Profile dropdown / Logout */}
            <div className="flex items-center gap-2 pl-2 border-l border-[var(--bd2)]">
              <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-[var(--cyan)] to-blue-500 p-0.5 relative">
                <img
                  src={user?.avatar || '/images/occupations/interior_designer.jpg'}
                  alt={displayName}
                  className="w-full h-full object-cover rounded-full"
                />
                <span className="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-[var(--surface)]" />
              </div>

              <div className="hidden lg:block text-left leading-tight">
                <div className="text-xs font-bold text-[var(--tx)] truncate max-w-[120px]">{displayName}</div>
                <div className="text-[10px] text-[var(--cyan)] font-semibold">{displayTier}</div>
              </div>

              <button
                onClick={() => {
                  logout();
                  window.location.href = '/';
                }}
                className="text-[var(--tx-3)] hover:text-rose-500 p-1.5 transition-colors"
                title="Sign Out"
              >
                <LogOut className="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </header>

      {/* ══════ MAIN WORKSPACE CONTAINER ══════ */}
      <main className="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">
        {/* Welcome Banner */}
        <div className="rounded-2xl p-6 bg-gradient-to-r from-[var(--surface)] via-[var(--s2)] to-[var(--surface)] border border-[var(--cyan-border)] shadow-xl relative overflow-hidden">
          <div className="absolute right-0 top-0 bottom-0 w-80 bg-gradient-to-l from-[var(--cyan)]/10 to-transparent pointer-events-none" />
          
          <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <div className="flex items-center gap-2 mb-1.5">
                <span className="text-xs font-extrabold uppercase tracking-wider text-[var(--cyan)] font-mono">
                  Verified Artisan Workspace
                </span>
                <span className="text-[11px] px-2 py-0.5 rounded-full bg-amber-500/15 text-[#F59E0B] border border-amber-500/30 font-bold">
                  {displayTier}
                </span>
              </div>
              <h1 className="text-2xl sm:text-3xl font-extrabold text-[var(--tx)] tracking-tight">
                Akwaaba, {displayName} 🇬🇭
              </h1>
              <p className="text-xs sm:text-sm text-[var(--tx-2)] mt-1 max-w-xl">
                {displayTrade} · Your milestone earnings are protected by Bank-Grade Escrow and settle in under 60 seconds to your Mobile Money wallet.
              </p>
            </div>

            <div className="flex items-center gap-3 shrink-0">
              <button
                onClick={() => setIsWithdrawModalOpen(true)}
                className="h-11 px-5 rounded-xl bg-gradient-to-r from-[#10B981] to-[#059669] hover:from-[#059669] hover:to-[#047857] text-white font-extrabold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition-all active:scale-[0.99]"
              >
                <Smartphone className="w-4 h-4" />
                <span>Withdraw to MoMo</span>
              </button>

              <a
                href="/jobs"
                className="h-11 px-5 rounded-xl bg-[var(--surface)] hover:bg-[var(--s2)] border border-[var(--bd2)] text-[var(--tx)] font-bold text-xs flex items-center gap-2 transition-all shadow-xs"
              >
                <span>Browse New Jobs</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </a>
            </div>
          </div>
        </div>

        {/* ══════ FINANCIAL & ESCROW METRICS GRID ══════ */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Card 1: Available MoMo Balance */}
          <div className="p-5 rounded-2xl bg-[var(--surface)] border border-emerald-500/30 shadow-sm relative overflow-hidden">
            <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)] mb-2">
              <span>Available for Payout</span>
              <div className="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                <CheckCircle2 className="w-4 h-4" />
              </div>
            </div>
            <div className="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-display">
              ₵{availableBalance.toLocaleString()}.00
            </div>
            <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)] mt-2 pt-2 border-t border-[var(--bd2)]">
              <span>Ready for withdrawal</span>
              <button
                onClick={() => setIsWithdrawModalOpen(true)}
                className="text-[var(--cyan)] font-extrabold hover:underline"
              >
                Cash Out &rarr;
              </button>
            </div>
          </div>

          {/* Card 2: Locked in Escrow Vault */}
          <div className="p-5 rounded-2xl bg-[var(--surface)] border border-amber-500/30 shadow-sm relative overflow-hidden">
            <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)] mb-2">
              <span>Locked in Escrow Vault</span>
              <div className="w-7 h-7 rounded-lg bg-amber-500/10 text-[#F59E0B] flex items-center justify-center">
                <Lock className="w-4 h-4" />
              </div>
            </div>
            <div className="text-2xl sm:text-3xl font-black text-[#F59E0B] font-display">
              ₵{escrowBalance.toLocaleString()}.00
            </div>
            <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)] mt-2 pt-2 border-t border-[var(--bd2)]">
              <span>2 Milestone Contracts</span>
              <span className="text-amber-500 font-semibold">100% Protected</span>
            </div>
          </div>

          {/* Card 3: Total Lifetime Earnings */}
          <div className="p-5 rounded-2xl bg-[var(--surface)] border border-[var(--bd2)] shadow-sm">
            <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)] mb-2">
              <span>Lifetime Earnings</span>
              <div className="w-7 h-7 rounded-lg bg-[var(--cyan)]/10 text-[var(--cyan)] flex items-center justify-center">
                <TrendingUp className="w-4 h-4" />
              </div>
            </div>
            <div className="text-2xl sm:text-3xl font-black text-[var(--tx)] font-display">
              ₵28,900.00
            </div>
            <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)] mt-2 pt-2 border-t border-[var(--bd2)]">
              <span>42 Completed Gigs</span>
              <span className="text-[var(--cyan)] font-semibold">+18% this month</span>
            </div>
          </div>

          {/* Card 4: Rating & Trust Badge */}
          <div className="p-5 rounded-2xl bg-[var(--surface)] border border-[var(--bd2)] shadow-sm">
            <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)] mb-2">
              <span>Client Review Score</span>
              <div className="w-7 h-7 rounded-lg bg-amber-400/10 text-amber-500 flex items-center justify-center">
                <Star className="w-4 h-4 fill-amber-400" />
              </div>
            </div>
            <div className="text-2xl sm:text-3xl font-black text-[var(--tx)] flex items-center gap-1.5 font-display">
              <span>5.0</span>
              <div className="flex items-center text-[#F59E0B] text-sm">
                <Star className="w-3.5 h-3.5 fill-current" />
                <Star className="w-3.5 h-3.5 fill-current" />
                <Star className="w-3.5 h-3.5 fill-current" />
                <Star className="w-3.5 h-3.5 fill-current" />
                <Star className="w-3.5 h-3.5 fill-current" />
              </div>
            </div>
            <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)] mt-2 pt-2 border-t border-[var(--bd2)]">
              <span>48 Verified Reviews</span>
              <span className="text-emerald-500 font-bold">100% On-Time</span>
            </div>
          </div>
        </div>

        {/* ══════ ACTIVE MILESTONE CONTRACTS ══════ */}
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-lg font-black text-[var(--tx)] tracking-tight">Active Escrow Contracts</h2>
              <p className="text-xs text-[var(--tx-2)]">Milestone deliverables currently in progress or awaiting inspection.</p>
            </div>
            <span className="text-xs font-mono text-[var(--cyan)] font-bold">Contract #GG-8849</span>
          </div>

          <div className="rounded-2xl bg-[var(--surface)] border border-[var(--bd2)] p-6 shadow-sm space-y-6">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[var(--bd2)]">
              <div>
                <div className="flex items-center gap-2 mb-1">
                  <span className="px-2 py-0.5 rounded-full bg-[var(--cyan)]/10 text-[var(--cyan)] text-[10.5px] font-extrabold border border-[var(--cyan)]/30">
                    Active Project
                  </span>
                  <span className="text-xs text-[var(--tx-3)] font-medium">Started 4 days ago</span>
                </div>
                <h3 className="text-base font-extrabold text-[var(--tx)]">
                  Luxury POP Ceiling &amp; Concealed LED Cove for 3-Bedroom Hall in Kumasi
                </h3>
                <div className="text-xs text-[var(--tx-2)] mt-0.5">
                  Client: <strong className="text-[var(--tx)]">Dr. Kwabena Frimpong</strong> · Location: Bantama, Kumasi
                </div>
              </div>

              <div className="text-right shrink-0">
                <div className="text-xs text-[var(--tx-3)]">Total Contract Value</div>
                <div className="text-xl font-black text-[var(--tx)] font-display">₵6,500.00 GHS</div>
              </div>
            </div>

            {/* Milestones Breakdown */}
            <div className="space-y-3">
              <div className="text-xs font-bold text-[var(--tx)]">Project Milestone Schedule</div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
                {/* Milestone 1: Done */}
                <div className="p-3.5 rounded-xl border border-emerald-500/40 bg-emerald-500/[0.04] space-y-1.5">
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                      <CheckCircle2 className="w-3.5 h-3.5" />
                      <span>Phase 1: Framing &amp; Leveling</span>
                    </span>
                    <span className="text-[10px] font-extrabold text-emerald-500 bg-emerald-500/15 px-1.5 py-0.5 rounded">
                      Paid
                    </span>
                  </div>
                  <div className="text-sm font-black text-[var(--tx)]">₵2,000.00</div>
                  <div className="text-[10.5px] text-[var(--tx-3)]">Released to MTN MoMo (42s settlement)</div>
                </div>

                {/* Milestone 2: Active Locked in Escrow */}
                <div className="p-3.5 rounded-xl border border-amber-500/40 bg-amber-500/[0.04] space-y-1.5 relative">
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-bold text-[#F59E0B] flex items-center gap-1.5">
                      <Lock className="w-3.5 h-3.5" />
                      <span>Phase 2: Plaster &amp; Moulding</span>
                    </span>
                    <span className="text-[10px] font-extrabold text-amber-500 bg-amber-500/15 px-1.5 py-0.5 rounded">
                      In Escrow
                    </span>
                  </div>
                  <div className="text-sm font-black text-[var(--tx)]">₵3,000.00</div>
                  <div className="text-[10.5px] text-[var(--tx-3)]">Funds securely held in Escrow Vault</div>
                </div>

                {/* Milestone 3: Pending */}
                <div className="p-3.5 rounded-xl border border-[var(--bd2)] bg-[var(--surface)] space-y-1.5 opacity-80">
                  <div className="flex items-center justify-between">
                    <span className="text-xs font-bold text-[var(--tx-2)] flex items-center gap-1.5">
                      <Clock className="w-3.5 h-3.5" />
                      <span>Phase 3: Sanding &amp; Paint</span>
                    </span>
                    <span className="text-[10px] font-bold text-[var(--tx-3)]">Pending</span>
                  </div>
                  <div className="text-sm font-black text-[var(--tx)]">₵1,500.00</div>
                  <div className="text-[10.5px] text-[var(--tx-3)]">Deposit pending Phase 2 sign-off</div>
                </div>
              </div>
            </div>

            {/* Action Bar for Milestone 2 */}
            <div className="pt-2 flex flex-wrap items-center justify-between gap-3 border-t border-[var(--bd2)]">
              <div className="text-xs text-[var(--tx-2)] flex items-center gap-2">
                <span className="w-2 h-2 rounded-full bg-amber-400 animate-pulse" />
                <span>Phase 2 is ready for inspection. Submit photos or receipt to trigger escrow release.</span>
              </div>

              <div className="flex items-center gap-2.5">
                <button
                  onClick={() => setIsDeliverableModalOpen(true)}
                  className="h-10 px-4 rounded-xl bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-extrabold text-xs flex items-center gap-1.5 shadow-sm"
                >
                  <Send className="w-3.5 h-3.5" />
                  <span>Submit Deliverable for Approval</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* ══════ RECOMMENDED LIVE OPPORTUNITIES FOR KWAME ══════ */}
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-lg font-black text-[var(--tx)] tracking-tight">Recommended Jobs for You</h2>
              <p className="text-xs text-[var(--tx-2)]">High-budget opportunities matching your verified master skill.</p>
            </div>
            <a href="/jobs" className="text-xs font-bold text-[var(--cyan)] hover:underline flex items-center gap-1">
              <span>View All Gigs</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </a>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {[
              {
                title: 'Acoustic POP Ceilings for Corporate HQ in Ridge, Accra',
                client: 'Ridge Properties Ltd (✓ Verified Client)',
                budget: '₵5,400 – ₵7,200 GHS',
                location: 'Ridge, Accra',
                bids: '4 Proposals',
                urgent: true,
              },
              {
                title: 'Interior Decorative Wall Mouldings for Luxury Villa',
                client: 'Nana Prempeh Estate',
                budget: '₵3,800 – ₵4,500 GHS',
                location: 'Cantonments, Accra',
                bids: '2 Proposals',
                urgent: false,
              },
            ].map((job, idx) => (
              <div key={idx} className="p-5 rounded-2xl bg-[var(--surface)] border border-[var(--bd2)] hover:border-[var(--cyan-border)] transition-all space-y-3">
                <div className="flex items-start justify-between gap-2">
                  <h4 className="text-sm font-extrabold text-[var(--tx)] leading-snug">{job.title}</h4>
                  {job.urgent && (
                    <span className="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-500/15 text-[#F59E0B] border border-amber-500/30 shrink-0">
                      ⚡ Urgent
                    </span>
                  )}
                </div>
                <div className="text-xs text-[var(--tx-2)]">{job.client} · {job.location}</div>
                <div className="flex items-center justify-between pt-2 border-t border-[var(--bd2)] text-xs">
                  <span className="font-black text-[var(--cyan)] font-display">{job.budget}</span>
                  <a href="/jobs" className="font-bold text-[var(--tx)] hover:text-[var(--cyan)] flex items-center gap-1">
                    <span>Submit Proposal</span>
                    <ArrowRight className="w-3 h-3" />
                  </a>
                </div>
              </div>
            ))}
          </div>
        </div>
      </main>

      {/* ══════ WITHDRAW TO MOMO MODAL ══════ */}
      {isWithdrawModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
          <div className="w-full max-w-md bg-[var(--surface)] border border-[var(--bd2)] rounded-2xl p-6 shadow-2xl relative space-y-5">
            <button
              onClick={() => setIsWithdrawModalOpen(false)}
              className="absolute top-4 right-4 text-[var(--tx-3)] hover:text-[var(--tx)]"
            >
              <X className="w-5 h-5" />
            </button>

            <div>
              <div className="flex items-center gap-2 text-[var(--cyan)] text-xs font-mono font-bold mb-1">
                <Smartphone className="w-4 h-4" />
                <span>Instant Mobile Money Payout</span>
              </div>
              <h3 className="text-xl font-extrabold text-[var(--tx)]">Withdraw Escrow Balance</h3>
              <p className="text-xs text-[var(--tx-2)] mt-0.5">
                Funds transfer directly to your registered wallet within 60 seconds.
              </p>
            </div>

            {withdrawSuccess ? (
              <div className="p-6 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-center space-y-2">
                <div className="w-12 h-12 rounded-full bg-emerald-500/20 text-emerald-500 flex items-center justify-center mx-auto">
                  <Check className="w-6 h-6 stroke-[3]" />
                </div>
                <h4 className="text-base font-extrabold text-emerald-600 dark:text-emerald-400">
                  Payout Sent to {displayPhone}!
                </h4>
                <p className="text-xs text-[var(--tx-2)]">
                  ₵{Number(withdrawAmount).toLocaleString()}.00 successfully transferred to your MoMo wallet. Check your phone alert.
                </p>
              </div>
            ) : (
              <form onSubmit={handleWithdraw} className="space-y-4">
                {/* Available balance indicator */}
                <div className="p-3 rounded-xl bg-[var(--s2)] flex items-center justify-between text-xs">
                  <span className="text-[var(--tx-2)]">Available to Cash Out:</span>
                  <strong className="text-emerald-500 font-bold text-sm">₵{availableBalance.toLocaleString()}.00</strong>
                </div>

                {/* Amount to withdraw */}
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Withdrawal Amount (Cedis)</label>
                  <div className="relative flex items-center">
                    <span className="absolute left-3.5 text-xs font-bold text-[var(--tx-2)]">₵</span>
                    <input
                      type="number"
                      value={withdrawAmount}
                      onChange={(e) => setWithdrawAmount(e.target.value)}
                      max={availableBalance}
                      min={50}
                      className="w-full h-11 pl-8 pr-16 bg-[var(--bg)] text-[var(--tx)] text-base font-bold rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                    />
                    <button
                      type="button"
                      onClick={() => setWithdrawAmount(String(availableBalance))}
                      className="absolute right-3 text-[10.5px] font-bold text-[var(--cyan)] hover:underline"
                    >
                      Max
                    </button>
                  </div>
                </div>

                {/* Network picker */}
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Registered Wallet Destination</label>
                  <div className="p-3 rounded-xl border border-amber-400/40 bg-amber-400/5 flex items-center justify-between">
                    <div className="flex items-center gap-2.5">
                      <img src="/images/payments/mtn_momo.svg" alt="MTN" className="h-5 w-auto" />
                      <div>
                        <div className="text-xs font-extrabold text-[var(--tx)]">MTN Mobile Money</div>
                        <div className="text-[11px] font-mono text-[var(--tx-2)]">{displayPhone} (Kwame Asante)</div>
                      </div>
                    </div>
                    <span className="text-[10px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-0.5 rounded">
                      Active
                    </span>
                  </div>
                </div>

                <button
                  type="submit"
                  disabled={isWithdrawing}
                  className="w-full h-11 rounded-xl bg-gradient-to-r from-[#10B981] to-[#059669] hover:from-[#059669] hover:to-[#047857] text-white font-extrabold text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20 transition-all"
                >
                  {isWithdrawing ? (
                    <>
                      <span className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
                      <span>Settling Sub-60s Transfer...</span>
                    </>
                  ) : (
                    <>
                      <span>Confirm ₵{Number(withdrawAmount || 0).toLocaleString()}.00 MoMo Transfer</span>
                      <ArrowRight className="w-3.5 h-3.5" />
                    </>
                  )}
                </button>
              </form>
            )}
          </div>
        </div>
      )}

      {/* ══════ SUBMIT DELIVERABLE MODAL ══════ */}
      {isDeliverableModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
          <div className="w-full max-w-md bg-[var(--surface)] border border-[var(--bd2)] rounded-2xl p-6 shadow-2xl relative space-y-4">
            <button
              onClick={() => setIsDeliverableModalOpen(false)}
              className="absolute top-4 right-4 text-[var(--tx-3)] hover:text-[var(--tx)]"
            >
              <X className="w-5 h-5" />
            </button>

            <div>
              <div className="text-xs font-bold text-[var(--cyan)] mb-1">Phase 2 Deliverable</div>
              <h3 className="text-lg font-extrabold text-[var(--tx)]">Submit Plaster &amp; Moulding Work</h3>
              <p className="text-xs text-[var(--tx-2)]">
                Client Dr. Kwabena Frimpong will be notified immediately to inspect and release the ₵3,000.00 escrow milestone.
              </p>
            </div>

            {deliverableSubmitted ? (
              <div className="p-6 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-center space-y-2">
                <CheckCircle2 className="w-10 h-10 text-emerald-500 mx-auto" />
                <h4 className="text-sm font-bold text-emerald-500">Deliverable Submitted!</h4>
                <p className="text-xs text-[var(--tx-2)]">Notification and photos dispatched to client.</p>
              </div>
            ) : (
              <form onSubmit={handleDeliverableSubmit} className="space-y-3">
                <div className="space-y-1">
                  <label className="text-xs font-bold text-[var(--tx)]">Completion Notes for Client</label>
                  <textarea
                    rows={3}
                    value={deliverableNotes}
                    onChange={(e) => setDeliverableNotes(e.target.value)}
                    placeholder="e.g. All acoustic plaster moulding and cove LED channels in the main hall are complete and dried ready for your review."
                    className="w-full p-3 bg-[var(--bg)] text-[var(--tx)] text-xs rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                    required
                  />
                </div>

                <div className="border border-dashed border-[var(--bd2)] rounded-xl p-4 text-center text-xs text-[var(--tx-3)] cursor-pointer hover:border-[var(--cyan)]">
                  📸 3 Site Completion Photos Attached (Simulated)
                </div>

                <button
                  type="submit"
                  className="w-full h-11 rounded-xl bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-xs flex items-center justify-center gap-2"
                >
                  <span>Submit &amp; Request Milestone Release</span>
                  <Send className="w-3.5 h-3.5" />
                </button>
              </form>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
