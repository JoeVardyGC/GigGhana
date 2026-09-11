'use client';

import React, { useState } from 'react';
import { useAuth } from '@/lib/context/AuthContext';
import confetti from 'canvas-confetti';
import {
  ShieldCheck,
  Building2,
  CheckCircle2,
  Lock,
  ArrowRight,
  Plus,
  Search,
  Bell,
  LogOut,
  Clock,
  Star,
  Smartphone,
  Check,
  X,
  Send,
  MessageSquare,
  AlertCircle,
  Briefcase,
  FileText,
  BadgeCheck,
} from 'lucide-react';

export default function ClientDashboardPage() {
  const { user, logout } = useAuth();

  // Escrow Vault State
  const [vaultBalance, setVaultBalance] = useState(4500);
  const [releasedTotal, setReleasedTotal] = useState(12000);
  const [phase2Status, setPhase2Status] = useState<'locked' | 'released'>('locked');

  // Modals
  const [isReleaseModalOpen, setIsReleaseModalOpen] = useState(false);
  const [isPostJobModalOpen, setIsPostJobModalOpen] = useState(false);
  const [isReleasing, setIsReleasing] = useState(false);
  const [releaseSuccess, setReleaseSuccess] = useState(false);

  // New Job Form State
  const [newJobTitle, setNewJobTitle] = useState('');
  const [newJobCat, setNewJobCat] = useState('Skilled Trades');
  const [newJobBudget, setNewJobBudget] = useState('4500');
  const [newJobDesc, setNewJobDesc] = useState('');
  const [jobPostedSuccess, setJobPostedSuccess] = useState(false);

  const handleApproveAndRelease = async () => {
    setIsReleasing(true);
    await new Promise((res) => setTimeout(res, 1200));

    setVaultBalance((prev) => prev - 3000);
    setReleasedTotal((prev) => prev + 3000);
    setPhase2Status('released');
    setIsReleasing(false);
    setReleaseSuccess(true);

    try {
      confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 },
        colors: ['#00D4C8', '#F59E0B', '#10B981', '#1877F2'],
      });
    } catch (_) {}

    setTimeout(() => {
      setReleaseSuccess(false);
      setIsReleaseModalOpen(false);
    }, 2200);
  };

  const handlePostJob = async (e: React.FormEvent) => {
    e.preventDefault();
    setJobPostedSuccess(true);
    try {
      confetti({
        particleCount: 70,
        spread: 60,
        origin: { y: 0.6 },
        colors: ['#F59E0B', '#00D4C8'],
      });
    } catch (_) {}
    setTimeout(() => {
      setIsPostJobModalOpen(false);
      setJobPostedSuccess(false);
      setNewJobTitle('');
      setNewJobDesc('');
    }, 1500);
  };

  const displayName = user ? `${user.first_name} ${user.last_name}` : 'Dr. Kwabena Frimpong';
  const displayLocation = user?.location || 'East Legon, Accra';

  return (
    <div className="min-h-screen bg-[var(--bg)] text-[var(--tx)] font-sans selection:bg-[var(--cyan)] selection:text-black">
      {/* ══════ CLIENT NAVBAR ══════ */}
      <header className="sticky top-0 z-40 w-full border-b border-[var(--bd2)] bg-[var(--surface)]/90 backdrop-blur-xl px-4 sm:px-8 py-3.5">
        <div className="max-w-7xl mx-auto flex items-center justify-between gap-4">
          <div className="flex items-center gap-6">
            <a href="/" className="flex items-center gap-2.5">
              <div className="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#F59E0B] via-[#D97706] to-[#00D4C8] flex items-center justify-center text-slate-950 font-black text-base shadow-xs">
                G
              </div>
              <div className="text-lg font-black tracking-tight text-[var(--tx)]">
                Gig<span className="text-[var(--cyan)]">Ghana</span>
              </div>
            </a>

            <nav className="hidden md:flex items-center gap-5 text-xs font-bold text-[var(--tx-2)]">
              <a href="/dashboard/client" className="text-[#F59E0B] flex items-center gap-1.5">
                <Building2 className="w-3.5 h-3.5" />
                <span>Client Projects</span>
              </a>
              <a href="/search/providers" className="hover:text-[var(--tx)] transition-colors flex items-center gap-1.5">
                <Search className="w-3.5 h-3.5" />
                <span>Find Master Artisans</span>
              </a>
              <a href="/jobs" className="hover:text-[var(--tx)] transition-colors">
                Public Job Feed
              </a>
            </nav>
          </div>

          <div className="flex items-center gap-3">
            {/* Post Job button */}
            <button
              onClick={() => setIsPostJobModalOpen(true)}
              className="hidden sm:inline-flex items-center gap-1.5 text-xs font-black px-4 py-2 rounded-xl bg-gradient-to-r from-[#F59E0B] to-[#D97706] text-slate-950 shadow-md shadow-amber-500/20 hover:brightness-105 transition-all"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>Post New Project</span>
            </button>

            {/* Notification bell */}
            <button className="w-9 h-9 rounded-xl border border-[var(--bd2)] bg-[var(--surface)] flex items-center justify-center text-[var(--tx-2)] hover:text-[var(--tx)] relative">
              <Bell className="w-4 h-4" />
              <span className="absolute top-2 right-2 w-2 h-2 rounded-full bg-[#F59E0B]" />
            </button>

            {/* User Profile */}
            <div className="flex items-center gap-2 pl-2 border-l border-[var(--bd2)]">
              <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-[#F59E0B] to-amber-600 p-0.5 relative">
                <img
                  src={user?.avatar || '/images/occupations/tech_hub.jpg'}
                  alt={displayName}
                  className="w-full h-full object-cover rounded-full"
                />
                <span className="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-[var(--surface)]" />
              </div>

              <div className="hidden lg:block text-left leading-tight">
                <div className="text-xs font-bold text-[var(--tx)] truncate max-w-[120px]">{displayName}</div>
                <div className="text-[10px] text-[#F59E0B] font-semibold">Verified Client</div>
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

      {/* ══════ MAIN WORKSPACE ══════ */}
      <main className="max-w-7xl mx-auto px-4 sm:px-8 py-8 space-y-8">
        {/* Welcome Banner */}
        <div className="rounded-2xl p-6 bg-gradient-to-r from-[var(--surface)] via-[var(--s2)] to-[var(--surface)] border border-[#F59E0B]/30 shadow-xl relative overflow-hidden">
          <div className="absolute right-0 top-0 bottom-0 w-80 bg-gradient-to-l from-amber-500/10 to-transparent pointer-events-none" />

          <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <div className="flex items-center gap-2 mb-1.5">
                <span className="text-xs font-extrabold uppercase tracking-wider text-[#F59E0B] font-mono">
                  Client &amp; Employer Console
                </span>
                <span className="text-[11px] px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-500 border border-emerald-500/30 font-bold">
                  ✓ Bank-Grade Escrow Vault Protected
                </span>
              </div>
              <h1 className="text-2xl sm:text-3xl font-extrabold text-[var(--tx)] tracking-tight">
                Akwaaba, {displayName}
              </h1>
              <p className="text-xs sm:text-sm text-[var(--tx-2)] mt-1 max-w-xl">
                {displayLocation} · You hold full milestone release authority. Artisans are paid only when you inspect and approve their completed deliverables.
              </p>
            </div>

            <div className="flex items-center gap-3 shrink-0">
              <button
                onClick={() => setIsPostJobModalOpen(true)}
                className="h-11 px-5 rounded-xl bg-gradient-to-r from-[#F59E0B] to-[#D97706] hover:from-[#D97706] hover:to-[#B45309] text-slate-950 font-black text-xs flex items-center gap-2 shadow-lg shadow-amber-500/20 transition-all active:scale-[0.99]"
              >
                <Plus className="w-4 h-4" />
                <span>Post New Job Brief</span>
              </button>

              <a
                href="/search/providers"
                className="h-11 px-5 rounded-xl bg-[var(--surface)] hover:bg-[var(--s2)] border border-[var(--bd2)] text-[var(--tx)] font-bold text-xs flex items-center gap-2 transition-all shadow-xs"
              >
                <span>Browse Masters</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </a>
            </div>
          </div>
        </div>

        {/* ══════ ESCROW & PROJECT METRICS ══════ */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Card 1: Protected in Escrow Vault */}
          <div className="p-5 rounded-2xl bg-[var(--surface)] border border-amber-500/30 shadow-sm">
            <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)] mb-2">
              <span>Currently in Escrow Vault</span>
              <div className="w-7 h-7 rounded-lg bg-amber-500/10 text-[#F59E0B] flex items-center justify-center">
                <Lock className="w-4 h-4" />
              </div>
            </div>
            <div className="text-2xl sm:text-3xl font-black text-[#F59E0B] font-display">
              ₵{vaultBalance.toLocaleString()}.00
            </div>
            <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)] mt-2 pt-2 border-t border-[var(--bd2)]">
              <span>Locked for safety</span>
              <span className="text-emerald-500 font-bold">100% Guaranteed</span>
            </div>
          </div>

          {/* Card 2: Released to Date */}
          <div className="p-5 rounded-2xl bg-[var(--surface)] border border-emerald-500/30 shadow-sm">
            <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)] mb-2">
              <span>Released to Artisans</span>
              <div className="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                <CheckCircle2 className="w-4 h-4" />
              </div>
            </div>
            <div className="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 font-display">
              ₵{releasedTotal.toLocaleString()}.00
            </div>
            <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)] mt-2 pt-2 border-t border-[var(--bd2)]">
              <span>3 Milestones Completed</span>
              <span className="text-emerald-500 font-semibold">Sub-60s MoMo</span>
            </div>
          </div>

          {/* Card 3: Active Contracts */}
          <div className="p-5 rounded-2xl bg-[var(--surface)] border border-[var(--bd2)] shadow-sm">
            <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)] mb-2">
              <span>Active Contracts</span>
              <div className="w-7 h-7 rounded-lg bg-[var(--cyan)]/10 text-[var(--cyan)] flex items-center justify-center">
                <Briefcase className="w-4 h-4" />
              </div>
            </div>
            <div className="text-2xl sm:text-3xl font-black text-[var(--tx)] font-display">
              2 Contracts
            </div>
            <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)] mt-2 pt-2 border-t border-[var(--bd2)]">
              <span>1 POP Ceiling · 1 Solar PV</span>
              <span className="text-[var(--cyan)] font-semibold">On Schedule</span>
            </div>
          </div>

          {/* Card 4: Open Bids Under Review */}
          <div className="p-5 rounded-2xl bg-[var(--surface)] border border-[var(--bd2)] shadow-sm">
            <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)] mb-2">
              <span>Open Job Proposals</span>
              <div className="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
                <FileText className="w-4 h-4" />
              </div>
            </div>
            <div className="text-2xl sm:text-3xl font-black text-[var(--tx)] font-display">
              6 Proposals
            </div>
            <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)] mt-2 pt-2 border-t border-[var(--bd2)]">
              <span>Borehole Drilling Project</span>
              <span className="text-blue-500 font-bold">Review Bids &rarr;</span>
            </div>
          </div>
        </div>

        {/* ══════ ACTIVE CONTRACT: POP CEILING ══════ */}
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <div>
              <h2 className="text-lg font-black text-[var(--tx)] tracking-tight">Active Project Management</h2>
              <p className="text-xs text-[var(--tx-2)]">Review deliverables and release milestone funds to your hired master artisan.</p>
            </div>
            <span className="text-xs font-mono text-[#F59E0B] font-bold">Escrow Ref: #GG-8849</span>
          </div>

          <div className="rounded-2xl bg-[var(--surface)] border border-[var(--bd2)] p-6 shadow-sm space-y-6">
            {/* Project Header */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[var(--bd2)]">
              <div className="flex items-start gap-3.5">
                <img
                  src="/images/occupations/interior_designer.jpg"
                  alt="Kwame Asante"
                  className="w-12 h-12 rounded-xl object-cover ring-2 ring-[var(--cyan-border)] shrink-0"
                />
                <div>
                  <div className="flex items-center gap-2 mb-1">
                    <h3 className="text-base font-extrabold text-[var(--tx)]">
                      Luxury POP Ceiling &amp; Concealed LED Cove
                    </h3>
                    <span className="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-500 border border-emerald-500/30">
                      In Progress
                    </span>
                  </div>
                  <div className="text-xs text-[var(--tx-2)] flex items-center gap-1.5">
                    <span>Hired Master: <strong className="text-[var(--tx)]">Kwame Asante</strong></span>
                    <BadgeCheck className="w-3.5 h-3.5 text-[var(--cyan)]" />
                    <span>(Ghana Card Biometric Verified)</span>
                  </div>
                </div>
              </div>

              <div className="text-right shrink-0">
                <div className="text-xs text-[var(--tx-3)]">Total Escrow Vault Commitment</div>
                <div className="text-xl font-black text-[var(--tx)] font-display">₵6,500.00 GHS</div>
              </div>
            </div>

            {/* Milestones Schedule */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
              {/* Phase 1: Released */}
              <div className="p-4 rounded-xl border border-emerald-500/40 bg-emerald-500/[0.04] space-y-1.5">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    <span>Phase 1: Framing</span>
                  </span>
                  <span className="text-[10px] font-extrabold text-emerald-500 bg-emerald-500/15 px-1.5 py-0.5 rounded">
                    Released
                  </span>
                </div>
                <div className="text-base font-black text-[var(--tx)]">₵2,000.00</div>
                <div className="text-[11px] text-[var(--tx-3)]">Transferred to Kwame's MTN MoMo (Approved)</div>
              </div>

              {/* Phase 2: Actionable */}
              <div className={`p-4 rounded-xl border space-y-1.5 transition-all ${
                phase2Status === 'released'
                  ? 'border-emerald-500/40 bg-emerald-500/[0.04]'
                  : 'border-amber-500/50 bg-amber-500/[0.05] ring-1 ring-amber-500/20'
              }`}>
                <div className="flex items-center justify-between">
                  <span className={`text-xs font-bold flex items-center gap-1 ${
                    phase2Status === 'released' ? 'text-emerald-500' : 'text-[#F59E0B]'
                  }`}>
                    {phase2Status === 'released' ? <CheckCircle2 className="w-3.5 h-3.5" /> : <Lock className="w-3.5 h-3.5" />}
                    <span>Phase 2: Plaster &amp; Moulding</span>
                  </span>
                  <span className={`text-[10px] font-extrabold px-1.5 py-0.5 rounded ${
                    phase2Status === 'released' ? 'bg-emerald-500/15 text-emerald-500' : 'bg-amber-500/15 text-amber-500'
                  }`}>
                    {phase2Status === 'released' ? 'Released' : 'Deliverable Ready'}
                  </span>
                </div>
                <div className="text-base font-black text-[var(--tx)]">₵3,000.00</div>
                <div className="text-[11px] text-[var(--tx-2)]">
                  {phase2Status === 'released'
                    ? 'Transferred to MTN MoMo (42s settlement)'
                    : 'Kwame submitted photos & completion notes.'}
                </div>
              </div>

              {/* Phase 3: Locked */}
              <div className="p-4 rounded-xl border border-[var(--bd2)] bg-[var(--surface)] space-y-1.5 opacity-70">
                <div className="flex items-center justify-between">
                  <span className="text-xs font-bold text-[var(--tx-2)] flex items-center gap-1">
                    <Clock className="w-3.5 h-3.5" />
                    <span>Phase 3: Sanding &amp; Paint</span>
                  </span>
                  <span className="text-[10px] font-bold text-[var(--tx-3)]">Locked</span>
                </div>
                <div className="text-base font-black text-[var(--tx)]">₵1,500.00</div>
                <div className="text-[11px] text-[var(--tx-3)]">Awaiting completion of Phase 2</div>
              </div>
            </div>

            {/* Approval Action Bar */}
            {phase2Status === 'locked' ? (
              <div className="p-4 rounded-xl bg-gradient-to-r from-amber-500/10 via-[var(--surface)] to-transparent border border-amber-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div className="text-xs text-[var(--tx-2)] flex items-center gap-2">
                  <span className="w-2.5 h-2.5 rounded-full bg-amber-400 animate-ping" />
                  <span>
                    <strong>Action Required:</strong> Kwame Asante submitted Phase 2 completion. Verify work and release milestone.
                  </span>
                </div>

                <div className="flex items-center gap-2.5 shrink-0">
                  <button
                    onClick={() => alert('Revision request sent to Kwame via SMS/Chat.')}
                    className="h-10 px-4 rounded-xl border border-[var(--bd2)] hover:border-[var(--bd)] text-xs font-bold text-[var(--tx-2)]"
                  >
                    Request Revision
                  </button>
                  <button
                    onClick={() => setIsReleaseModalOpen(true)}
                    className="h-10 px-5 rounded-xl bg-gradient-to-r from-[#10B981] to-[#059669] text-white font-extrabold text-xs flex items-center gap-1.5 shadow-md shadow-emerald-500/20 hover:brightness-105"
                  >
                    <Check className="w-3.5 h-3.5" />
                    <span>Approve &amp; Release ₵3,000 to MoMo</span>
                  </button>
                </div>
              </div>
            ) : (
              <div className="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0" />
                <span>Phase 2 milestone approved! ₵3,000.00 successfully settled to Kwame Asante&apos;s MTN Mobile Money wallet.</span>
              </div>
            )}
          </div>
        </div>
      </main>

      {/* ══════ APPROVE & RELEASE MODAL ══════ */}
      {isReleaseModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
          <div className="w-full max-w-md bg-[var(--surface)] border border-[var(--bd2)] rounded-2xl p-6 shadow-2xl relative space-y-4">
            <button
              onClick={() => setIsReleaseModalOpen(false)}
              className="absolute top-4 right-4 text-[var(--tx-3)] hover:text-[var(--tx)]"
            >
              <X className="w-5 h-5" />
            </button>

            <div>
              <div className="text-xs font-bold text-emerald-500 flex items-center gap-1 mb-1">
                <ShieldCheck className="w-4 h-4" />
                <span>Bank-Grade Escrow Vault Settlement</span>
              </div>
              <h3 className="text-xl font-extrabold text-[var(--tx)]">Authorize Milestone Release</h3>
              <p className="text-xs text-[var(--tx-2)] mt-0.5">
                Release <strong>₵3,000.00 GHS</strong> from Escrow Vault to artisan Kwame Asante.
              </p>
            </div>

            {releaseSuccess ? (
              <div className="p-6 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-center space-y-2">
                <CheckCircle2 className="w-12 h-12 text-emerald-500 mx-auto" />
                <h4 className="text-base font-extrabold text-emerald-600 dark:text-emerald-400">
                  Funds Released in Sub-60s!
                </h4>
                <p className="text-xs text-[var(--tx-2)]">
                  ₵3,000.00 has been transferred directly to Kwame&apos;s MTN Mobile Money wallet (024 412 3456).
                </p>
              </div>
            ) : (
              <div className="space-y-4">
                <div className="p-3.5 rounded-xl bg-[var(--s2)] border border-[var(--bd2)] space-y-2 text-xs">
                  <div className="flex justify-between text-[var(--tx-2)]">
                    <span>Recipient Artisan:</span>
                    <strong className="text-[var(--tx)]">Kwame Asante (Verified Pro)</strong>
                  </div>
                  <div className="flex justify-between text-[var(--tx-2)]">
                    <span>Payout Destination:</span>
                    <strong className="text-[var(--tx)]">MTN MoMo (024 412 3456)</strong>
                  </div>
                  <div className="flex justify-between text-[var(--tx-2)]">
                    <span>Milestone:</span>
                    <strong className="text-[var(--tx)]">Phase 2: Plaster &amp; Moulding</strong>
                  </div>
                  <div className="flex justify-between pt-2 border-t border-[var(--bd2)] text-sm font-bold">
                    <span>Total Transfer:</span>
                    <span className="text-emerald-500 font-black">₵3,000.00 GHS</span>
                  </div>
                </div>

                <button
                  type="button"
                  onClick={handleApproveAndRelease}
                  disabled={isReleasing}
                  className="w-full h-11 rounded-xl bg-gradient-to-r from-[#10B981] to-[#059669] hover:from-[#059669] hover:to-[#047857] text-white font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/20"
                >
                  {isReleasing ? (
                    <>
                      <span className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
                      <span>Settling Sub-60s MoMo Transfer...</span>
                    </>
                  ) : (
                    <>
                      <span>Confirm &amp; Release ₵3,000 to MoMo</span>
                      <ArrowRight className="w-3.5 h-3.5" />
                    </>
                  )}
                </button>
              </div>
            )}
          </div>
        </div>
      )}

      {/* ══════ POST NEW JOB MODAL ══════ */}
      {isPostJobModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
          <div className="w-full max-w-lg bg-[var(--surface)] border border-[var(--bd2)] rounded-2xl p-6 shadow-2xl relative space-y-4">
            <button
              onClick={() => setIsPostJobModalOpen(false)}
              className="absolute top-4 right-4 text-[var(--tx-3)] hover:text-[var(--tx)]"
            >
              <X className="w-5 h-5" />
            </button>

            <div>
              <div className="text-xs font-bold text-[#F59E0B] flex items-center gap-1 mb-1">
                <Plus className="w-3.5 h-3.5" />
                <span>New Project Brief</span>
              </div>
              <h3 className="text-xl font-extrabold text-[var(--tx)]">Post a Project Requirement</h3>
              <p className="text-xs text-[var(--tx-2)] mt-0.5">
                Verified Ghanaian artisans will review your brief and submit competitive Cedi quotes within 15 minutes.
              </p>
            </div>

            {jobPostedSuccess ? (
              <div className="p-6 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-center space-y-2">
                <CheckCircle2 className="w-12 h-12 text-emerald-500 mx-auto" />
                <h4 className="text-base font-extrabold text-emerald-600 dark:text-emerald-400">
                  Project Brief Published!
                </h4>
                <p className="text-xs text-[var(--tx-2)]">
                  Your project is now live on the GigGhana Live Job Feed. Verified artisans have been notified.
                </p>
              </div>
            ) : (
              <form onSubmit={handlePostJob} className="space-y-3">
                <div className="space-y-1">
                  <label className="text-xs font-bold text-[var(--tx)]">Project Title</label>
                  <input
                    type="text"
                    value={newJobTitle}
                    onChange={(e) => setNewJobTitle(e.target.value)}
                    placeholder="e.g. 3-Phase Solar Inverter Installation for Estate Villa"
                    className="w-full h-11 px-3.5 bg-[var(--bg)] text-[var(--tx)] text-xs font-medium rounded-xl border border-[var(--bd2)] focus:border-[#F59E0B] focus:outline-none"
                    required
                  />
                </div>

                <div className="grid grid-cols-2 gap-3">
                  <div className="space-y-1">
                    <label className="text-xs font-bold text-[var(--tx)]">Category</label>
                    <select
                      value={newJobCat}
                      onChange={(e) => setNewJobCat(e.target.value)}
                      className="w-full h-11 px-3 bg-[var(--bg)] text-[var(--tx)] text-xs font-medium rounded-xl border border-[var(--bd2)] focus:outline-none"
                    >
                      <option value="Skilled Trades">Skilled Trades (POP, Solar, Electric)</option>
                      <option value="Construction">Construction &amp; Masonry</option>
                      <option value="IT & Tech">IT &amp; Software Development</option>
                      <option value="Creative Arts">Creative Arts &amp; Fashion</option>
                    </select>
                  </div>

                  <div className="space-y-1">
                    <label className="text-xs font-bold text-[var(--tx)]">Estimated Budget (Cedis)</label>
                    <div className="relative flex items-center">
                      <span className="absolute left-3 text-xs font-bold text-[var(--tx-2)]">₵</span>
                      <input
                        type="number"
                        value={newJobBudget}
                        onChange={(e) => setNewJobBudget(e.target.value)}
                        className="w-full h-11 pl-7 pr-3 bg-[var(--bg)] text-[var(--tx)] text-xs font-bold rounded-xl border border-[var(--bd2)] focus:outline-none"
                        required
                      />
                    </div>
                  </div>
                </div>

                <div className="space-y-1">
                  <label className="text-xs font-bold text-[var(--tx)]">Project Scope Details</label>
                  <textarea
                    rows={3}
                    value={newJobDesc}
                    onChange={(e) => setNewJobDesc(e.target.value)}
                    placeholder="Describe specific deliverables, site location, timeline, and required materials..."
                    className="w-full p-3 bg-[var(--bg)] text-[var(--tx)] text-xs rounded-xl border border-[var(--bd2)] focus:border-[#F59E0B] focus:outline-none"
                    required
                  />
                </div>

                <button
                  type="submit"
                  className="w-full h-11 rounded-xl bg-gradient-to-r from-[#F59E0B] to-[#D97706] hover:from-[#D97706] hover:to-[#B45309] text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-amber-500/20"
                >
                  <span>Publish Job Brief to Live Feed</span>
                  <ArrowRight className="w-3.5 h-3.5" />
                </button>
              </form>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
