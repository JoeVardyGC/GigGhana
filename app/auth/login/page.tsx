'use client';

import React, { useState, useMemo, Suspense } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { SiteHeader } from '@/components/layout/SiteHeader';
import { SiteFooter } from '@/components/layout/SiteFooter';
import { useAuth } from '@/lib/context/AuthContext';
import { detectGhanaNetwork } from '@/components/ui/phone-input';
import confetti from 'canvas-confetti';
import {
  ShieldCheck,
  Lock,
  Smartphone,
  Eye,
  EyeOff,
  ArrowRight,
  Sparkles,
  CheckCircle2,
  Star,
  Briefcase,
  Mail,
  Phone,
  Building2,
  Wrench,
  Zap,
  Check,
  AlertCircle,
  HelpCircle,
  Clock,
  ArrowLeft,
} from 'lucide-react';

function LoginContent() {
  const router = useRouter();
  const { login, loginDemoUser } = useAuth();

  // Active Interface Tab: 'provider' (Artisan Gateway) vs 'client' (Employer Console)
  const [activeInterface, setActiveInterface] = useState<'provider' | 'client'>('provider');

  // Form State
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(true);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  // Telecom auto-detection for Ghanaian phone numbers
  const detectedNetwork = useMemo(() => {
    if (identifier.includes('@')) return 'email';
    return detectGhanaNetwork(identifier);
  }, [identifier]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');

    if (!identifier.trim()) {
      setErrorMsg('Please enter your registered email address or Ghanaian telephone number.');
      return;
    }
    if (!password) {
      setErrorMsg('Please enter your secure password.');
      return;
    }

    setIsLoading(true);
    try {
      const res = await login(identifier, password);
      if (res.success) {
        try {
          confetti({
            particleCount: 85,
            spread: 70,
            origin: { y: 0.6 },
            colors: activeInterface === 'provider' ? ['#00D4C8', '#10B981', '#38BDF8'] : ['#F59E0B', '#D97706', '#10B981'],
          });
        } catch (_) {}

        setTimeout(() => {
          const userRole = res.user?.role || activeInterface;
          router.push(userRole === 'client' ? '/dashboard/client' : '/dashboard/provider');
        }, 800);
      } else {
        setErrorMsg(res.message || 'Invalid credentials. Please verify your details and try again.');
        setIsLoading(false);
      }
    } catch (err: any) {
      setErrorMsg(err?.message || 'Login failed. Please try again.');
      setIsLoading(false);
    }
  };

  const handleQuickDemo = (demo: 'kwame_provider' | 'frimpong_client') => {
    loginDemoUser(demo);
    try {
      confetti({
        particleCount: 70,
        spread: 60,
        origin: { y: 0.6 },
        colors: demo === 'kwame_provider' ? ['#00D4C8', '#10B981'] : ['#F59E0B', '#FBBF24'],
      });
    } catch (_) {}
    setTimeout(() => {
      router.push(demo === 'frimpong_client' ? '/dashboard/client' : '/dashboard/provider');
    }, 600);
  };

  return (
    <div className="min-h-screen bg-[var(--bg)] text-[var(--tx)] flex flex-col font-body transition-colors relative selection:bg-[var(--cyan)] selection:text-black">
      <SiteHeader />

      {/* ══════ HERO EDITORIAL SECTION (IDENTICAL TO HOMEPAGE ASYMMETRICAL SPLIT) ══════ */}
      <section className="hero" style={{ minHeight: 'auto', paddingTop: '115px', paddingBottom: '70px' }}>
        <div className="hero-container" style={{ alignItems: 'flex-start' }}>
          
          {/* ══════ LEFT COLUMN: BOLD EDITORIAL HEADLINE & HOMEPAGE-STYLE CARDS ══════ */}
          <div className="hero-left space-y-6">
            
            {/* Top Micro Badge */}
            <div className="hero-badge">
              <span>Ghana&apos;s #1 Marketplace · </span>
              <span className="ticker-wrap">
                <span className="ticker-text text-[var(--cyan)] font-bold">
                  Escrow Protected Gateway 🇬🇭
                </span>
              </span>
            </div>

            {/* Bold Headline */}
            <h1 className="hero-title" style={{ fontSize: 'clamp(2.5rem, 4.2vw, 3.8rem)', lineHeight: 1.15 }}>
              Secure Access.
              <br />
              Protected Cedis.
              <br />
              <span className="gold">Your Workspace.</span>
            </h1>

            {/* Editorial Subtitle */}
            <p className="hero-sub" style={{ marginBottom: '20px' }}>
              Sign in to your verified GigGhana account. Manage active milestone contracts, submit deliverables with proof of work, release escrow funds, or withdraw directly to your Mobile Money wallet.
            </p>

            {/* Homepage-Style Trust Indicators */}
            <div className="hero-trust" style={{ marginBottom: '28px' }}>
              <div className="trust-i">
                <div className="dot dot-g" />
                Bank-Grade Escrow
              </div>
              <div className="trust-i">
                <div className="dot dot-b" />
                Ghana Card Verified
              </div>
              <div className="trust-i">
                <div className="dot dot-gr" />
                MoMo Sub-60s
              </div>
              <div className="trust-i">
                <div className="dot dot-i" />
                No Disputes
              </div>
            </div>

            {/* ══════ HOMEPAGE-STYLE ARTISAN & CLIENT PROFILE CARDS ══════ */}
            <div className="space-y-4 pt-2">
              <div className="flex items-center justify-between">
                <div className="text-xs font-bold uppercase tracking-wider text-[var(--tx-2)] flex items-center gap-1.5">
                  <Zap className="w-3.5 h-3.5 text-amber-400" />
                  <span>Instant 1-Click Demo Profiles</span>
                </div>
                <span className="text-[10px] font-mono text-[var(--cyan)] font-bold px-2 py-0.5 rounded-full bg-[var(--cyan-dim)] border border-[var(--cyan-border)]">
                  Live Preview
                </span>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                {/* Card 1: Kwame Asante (Artisan Showcase Card) */}
                <div className="artisan-studio-card group transition-all hover:shadow-2xl">
                  <div className="artisan-cover-wrap" style={{ height: '140px' }}>
                    <Image
                      src="/images/occupations/interior_designer.jpg"
                      alt="Kwame Asante - Master Artisan"
                      fill
                      className="artisan-cover-img"
                    />
                    <div className="artisan-cover-live-dot" title="Available for immediate work">
                      <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" />
                    </div>
                  </div>

                  <div className="artisan-studio-body" style={{ padding: '14px' }}>
                    <div className="artisan-name-row">
                      <div className="artisan-name-title">
                        <span>Kwame Asante</span>
                        <ShieldCheck className="w-3.5 h-3.5 text-[var(--cyan)] shrink-0" />
                      </div>
                      <div className="artisan-rate-capsule">
                        <span className="artisan-rate-val">₵95</span>
                        <span className="artisan-rate-unit">/hr</span>
                      </div>
                    </div>

                    <div className="text-[11px] text-[var(--cyan)] font-bold font-mono">
                      ✓ Ghana Card Biometric Verified
                    </div>

                    <p className="text-[11px] text-[var(--tx-2)] line-clamp-1">
                      Master POP Ceilings &amp; Luxury Interior Plasterer
                    </p>

                    {/* Stats Strip */}
                    <div className="artisan-stats-strip">
                      <div className="artisan-strip-pill">
                        <div className="artisan-strip-val">
                          <Star className="w-3 h-3 fill-amber-400 text-amber-400 shrink-0" />
                          <span>5.0</span>
                        </div>
                        <span className="artisan-strip-lbl">48 reviews</span>
                      </div>
                      <div className="artisan-strip-divider" />
                      <div className="artisan-strip-pill">
                        <div className="artisan-strip-val">
                          <Briefcase className="w-3 h-3 text-[var(--cyan)] shrink-0" />
                          <span>42</span>
                        </div>
                        <span className="artisan-strip-lbl">Jobs Done</span>
                      </div>
                    </div>

                    {/* Footer Action */}
                    <div className="pt-2">
                      <button
                        type="button"
                        onClick={() => handleQuickDemo('kwame_provider')}
                        className="btn btn-blue w-full text-xs py-2 shadow-sm font-bold flex items-center justify-center gap-1.5"
                      >
                        <span>Launch Artisan Portal</span>
                        <ArrowRight className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </div>

                {/* Card 2: Dr. Kwabena Frimpong (Client Showcase Card) */}
                <div className="artisan-studio-card group transition-all hover:shadow-2xl">
                  <div className="artisan-cover-wrap" style={{ height: '140px' }}>
                    <Image
                      src="/images/occupations/executive.jpg"
                      alt="Dr. Kwabena Frimpong - Employer"
                      fill
                      className="artisan-cover-img"
                    />
                    <div className="artisan-cover-live-dot" title="Active Hiring Account">
                      <span className="w-2 h-2 rounded-full bg-amber-400 animate-pulse" />
                    </div>
                  </div>

                  <div className="artisan-studio-body" style={{ padding: '14px' }}>
                    <div className="artisan-name-row">
                      <div className="artisan-name-title">
                        <span>Dr. K. Frimpong</span>
                        <ShieldCheck className="w-3.5 h-3.5 text-amber-500 shrink-0" />
                      </div>
                      <div className="artisan-rate-capsule" style={{ borderColor: 'rgba(245, 158, 11, 0.3)' }}>
                        <span className="artisan-rate-val text-amber-500">₵4.5k</span>
                        <span className="artisan-rate-unit">Vault</span>
                      </div>
                    </div>

                    <div className="text-[11px] text-amber-500 font-bold font-mono">
                      ✓ Ghana Card Verified Client
                    </div>

                    <p className="text-[11px] text-[var(--tx-2)] line-clamp-1">
                      Real Estate Developer &amp; Villa Investor
                    </p>

                    {/* Stats Strip */}
                    <div className="artisan-stats-strip">
                      <div className="artisan-strip-pill">
                        <div className="artisan-strip-val">
                          <Star className="w-3 h-3 fill-amber-400 text-amber-400 shrink-0" />
                          <span>5.0</span>
                        </div>
                        <span className="artisan-strip-lbl">62 ratings</span>
                      </div>
                      <div className="artisan-strip-divider" />
                      <div className="artisan-strip-pill">
                        <div className="artisan-strip-val">
                          <Lock className="w-3 h-3 text-emerald-400 shrink-0" />
                          <span>3</span>
                        </div>
                        <span className="artisan-strip-lbl">Contracts</span>
                      </div>
                    </div>

                    {/* Footer Action */}
                    <div className="pt-2">
                      <button
                        type="button"
                        onClick={() => handleQuickDemo('frimpong_client')}
                        className="btn btn-gold w-full text-xs py-2 shadow-sm font-bold flex items-center justify-center gap-1.5"
                      >
                        <span>Launch Client Console</span>
                        <ArrowRight className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </div>

              </div>
            </div>

          </div>

          {/* ══════ RIGHT COLUMN: DUAL LOGIN INTERFACES (HOMEPAGE LUXURY CARD) ══════ */}
          <div className="hero-right-showcase w-full">
            <div className="showcase-outer-wrap">
              <div
                className="showcase-card"
                style={{
                  height: 'auto',
                  padding: '32px 28px',
                  background: 'var(--surface)',
                  display: 'flex',
                  flexDirection: 'column',
                  gap: '22px',
                  boxShadow: '0 24px 60px rgba(0, 0, 0, 0.25), 0 0 0 1px var(--bd)',
                }}
              >
                {/* Top Floating Badge */}
                <div className="showcase-top-badge" style={{ position: 'static', transform: 'none', margin: '0 auto' }}>
                  <span className="live-pulse-dot" />
                  <span>
                    {activeInterface === 'provider' ? '🛠️ Master Artisan Gateway' : '🏢 Client & Employer Console'}
                  </span>
                </div>

                {/* ══════ INTERFACE SWITCHER TABS ══════ */}
                <div className="space-y-2">
                  <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)]">
                    <span>Select Interface Mode:</span>
                    <span className="text-[10px] text-[var(--tx-3)] font-mono">2 Dedicated Gateways</span>
                  </div>

                  <div className="p-1.5 rounded-2xl bg-[var(--surface-2)] border border-[var(--bd)] grid grid-cols-2 gap-1.5 shadow-inner">
                    <button
                      type="button"
                      onClick={() => {
                        setActiveInterface('provider');
                        setErrorMsg('');
                      }}
                      className={`py-3 px-3 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-2 ${
                        activeInterface === 'provider'
                          ? 'bg-gradient-to-r from-[#00D4C8] to-[#009E95] text-black shadow-lg shadow-cyan-500/25 scale-[1.01]'
                          : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface)]'
                      }`}
                    >
                      <Wrench className="w-3.5 h-3.5" />
                      <span>Artisan Interface</span>
                    </button>

                    <button
                      type="button"
                      onClick={() => {
                        setActiveInterface('client');
                        setErrorMsg('');
                      }}
                      className={`py-3 px-3 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-2 ${
                        activeInterface === 'client'
                          ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] text-black shadow-lg shadow-amber-500/25 scale-[1.01]'
                          : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface)]'
                      }`}
                    >
                      <Building2 className="w-3.5 h-3.5" />
                      <span>Client Interface</span>
                    </button>
                  </div>
                </div>

                {/* Interface Context Headline */}
                <div className="p-3.5 rounded-2xl border border-[var(--bd2)] bg-[var(--surface-2)]/50 flex items-start gap-3">
                  <div
                    className={`w-9 h-9 rounded-xl flex items-center justify-center text-black font-black shrink-0 ${
                      activeInterface === 'provider' ? 'bg-[var(--cyan)]' : 'bg-[var(--gold)]'
                    }`}
                  >
                    {activeInterface === 'provider' ? <Wrench className="w-4 h-4" /> : <Building2 className="w-4 h-4" />}
                  </div>
                  <div className="text-xs">
                    <div className="font-bold text-[var(--tx)]">
                      {activeInterface === 'provider'
                        ? 'Sign In as a Certified Artisan'
                        : 'Sign In as an Employer / Client'}
                    </div>
                    <div className="text-[11px] text-[var(--tx-2)] mt-0.5">
                      {activeInterface === 'provider'
                        ? 'Withdraw to MTN MoMo, Telecel Cash, or AT Money. Submit phase deliverables.'
                        : 'Inspect active milestones, deposit Cedis into Escrow, and release funds upon satisfaction.'}
                    </div>
                  </div>
                </div>

                {/* ══════ MAIN SIGN IN FORM ══════ */}
                <form onSubmit={handleSubmit} className="space-y-4">
                  
                  {/* Email or Ghanaian Phone Input */}
                  <div className="space-y-1.5">
                    <div className="flex items-center justify-between text-xs font-bold text-[var(--tx)]">
                      <label htmlFor="login-identifier">
                        Email Address or Ghanaian Phone Number
                      </label>
                      
                      {/* Telecom Network Auto-Detection Badge */}
                      {detectedNetwork === 'mtn' && (
                        <span className="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-500 font-mono text-[10px] font-bold border border-amber-500/30 flex items-center gap-1">
                          <span className="w-1.5 h-1.5 rounded-full bg-amber-400" />
                          🟡 MTN MoMo
                        </span>
                      )}
                      {detectedNetwork === 'telecel' && (
                        <span className="px-2 py-0.5 rounded-full bg-red-500/15 text-red-500 font-mono text-[10px] font-bold border border-red-500/30 flex items-center gap-1">
                          <span className="w-1.5 h-1.5 rounded-full bg-red-400" />
                          🔴 Telecel Cash
                        </span>
                      )}
                      {detectedNetwork === 'at' && (
                        <span className="px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-400 font-mono text-[10px] font-bold border border-blue-500/30 flex items-center gap-1">
                          <span className="w-1.5 h-1.5 rounded-full bg-blue-400" />
                          🔵 AT Money
                        </span>
                      )}
                      {detectedNetwork === 'email' && (
                        <span className="px-2 py-0.5 rounded-full bg-[var(--cyan-dim)] text-[var(--cyan)] font-mono text-[10px] font-bold border border-[var(--cyan-border)] flex items-center gap-1">
                          <Mail className="w-3 h-3" />
                          Verified Email
                        </span>
                      )}
                    </div>

                    <div className="relative flex items-center">
                      <input
                        id="login-identifier"
                        type="text"
                        value={identifier}
                        onChange={(e) => setIdentifier(e.target.value)}
                        placeholder={
                          activeInterface === 'provider'
                            ? 'e.g. 024 412 3456 or kwame@gigghana.com'
                            : 'e.g. 020 899 1234 or dr.frimpong@estate.com'
                        }
                        className="w-full h-12 pl-4 pr-11 bg-[var(--surface-2)] text-[var(--tx)] text-sm font-medium rounded-2xl border border-[var(--bd)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all shadow-inner"
                        required
                      />
                      <div className="absolute right-4 text-[var(--tx-3)] pointer-events-none">
                        {identifier.includes('@') ? (
                          <Mail className="w-4 h-4 text-[var(--cyan)]" />
                        ) : (
                          <Phone className="w-4 h-4 text-[var(--cyan)]" />
                        )}
                      </div>
                    </div>
                  </div>

                  {/* Password Input */}
                  <div className="space-y-1.5">
                    <div className="flex items-center justify-between">
                      <label htmlFor="login-password" className="text-xs font-bold text-[var(--tx)]">
                        Password
                      </label>
                      <a
                        href="#"
                        onClick={(e) => {
                          e.preventDefault();
                          alert('Password reset link will be dispatched via SMS / Email to your registered account.');
                        }}
                        className="text-[11px] font-semibold text-[var(--cyan)] hover:underline"
                      >
                        Forgot Password?
                      </a>
                    </div>

                    <div className="relative flex items-center">
                      <input
                        id="login-password"
                        type={showPassword ? 'text' : 'password'}
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        placeholder="••••••••••••"
                        className="w-full h-12 pl-4 pr-12 bg-[var(--surface-2)] text-[var(--tx)] text-sm font-medium rounded-2xl border border-[var(--bd)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all shadow-inner"
                        required
                      />
                      <button
                        type="button"
                        onClick={() => setShowPassword(!showPassword)}
                        className="absolute right-4 text-[var(--tx-3)] hover:text-[var(--tx)] transition-colors p-1"
                        aria-label={showPassword ? 'Hide password' : 'Show password'}
                      >
                        {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                      </button>
                    </div>
                  </div>

                  {/* Remember Me Checkbox */}
                  <div className="flex items-center justify-between pt-1">
                    <label className="flex items-center gap-2 cursor-pointer select-none">
                      <input
                        type="checkbox"
                        checked={rememberMe}
                        onChange={(e) => setRememberMe(e.target.checked)}
                        className="w-4 h-4 rounded border-[var(--bd)] text-[var(--cyan)] focus:ring-0 focus:ring-offset-0 bg-[var(--surface-2)]"
                      />
                      <span className="text-xs text-[var(--tx-2)] font-medium">Keep me signed in on this device</span>
                    </label>
                  </div>

                  {/* Error Alert */}
                  {errorMsg && (
                    <div className="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
                      <AlertCircle className="w-4 h-4 shrink-0" />
                      <span>{errorMsg}</span>
                    </div>
                  )}

                  {/* Submit Button (Styled with Homepage .btn Classes) */}
                  <button
                    type="submit"
                    disabled={isLoading}
                    className={`btn w-full h-12 rounded-2xl font-black text-sm shadow-xl flex items-center justify-center gap-2 transition-all ${
                      activeInterface === 'provider' ? 'btn-blue' : 'btn-gold'
                    }`}
                  >
                    {isLoading ? (
                      <>
                        <div className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
                        <span>Verifying Credentials...</span>
                      </>
                    ) : (
                      <>
                        <span>
                          Sign In to {activeInterface === 'provider' ? 'Artisan Portal' : 'Employer Console'}
                        </span>
                        <ArrowRight className="w-4 h-4" />
                      </>
                    )}
                  </button>

                  {/* Register Switcher */}
                  <div className="pt-4 border-t border-[var(--bd)] text-center text-xs text-[var(--tx-2)] space-y-2">
                    <div>New to GigGhana? Choose your path:</div>
                    <div className="flex items-center justify-center gap-3">
                      <Link
                        href="/auth/register?role=provider"
                        className="font-bold text-[var(--cyan)] hover:underline flex items-center gap-1"
                      >
                        <span>Join as Artisan</span>
                        <ArrowRight className="w-3 h-3" />
                      </Link>
                      <span>•</span>
                      <Link
                        href="/auth/register?role=client"
                        className="font-bold text-amber-500 hover:underline flex items-center gap-1"
                      >
                        <span>Hire Verified Talent</span>
                        <ArrowRight className="w-3 h-3" />
                      </Link>
                    </div>
                  </div>

                </form>

              </div>
            </div>
          </div>

        </div>
      </section>

      <SiteFooter />
    </div>
  );
}

export default function LoginPage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen bg-[var(--bg)] flex items-center justify-center text-xs text-[var(--tx-3)]">
        <div className="w-8 h-8 border-2 border-[var(--cyan)] border-t-transparent rounded-full animate-spin" />
      </div>
    }>
      <LoginContent />
    </Suspense>
  );
}
