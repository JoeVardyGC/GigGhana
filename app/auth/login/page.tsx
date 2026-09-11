'use client';

import React, { useState, useMemo, Suspense, useEffect } from 'react';
import Image from 'next/image';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { SiteHeader } from '@/components/layout/SiteHeader';
import { SiteFooter } from '@/components/layout/SiteFooter';
import { useAuth } from '@/lib/context/AuthContext';
import { detectGhanaNetwork } from '@/components/ui/phone-input';
import { formatGhanaCardPin, validateGhanaCardPin } from '@/components/ui/ghana-card-input';
import { SpotlightCard } from '@/components/ui/spotlight-card';
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
  Clock,
  KeyRound,
  Fingerprint,
  RefreshCw,
  X,
  Send,
  HelpCircle,
} from 'lucide-react';

function LoginContent() {
  const router = useRouter();
  const { login, loginWithOtp, loginWithGhanaCard, requestPasswordReset, resetPassword, loginDemoUser } = useAuth();

  // Active Interface: 'provider' (Artisan Gateway) vs 'client' (Employer Console)
  const [activeInterface, setActiveInterface] = useState<'provider' | 'client'>('provider');

  // Sign-in Method: 'password' | 'otp' | 'ghanacard'
  const [authMethod, setAuthMethod] = useState<'password' | 'otp' | 'ghanacard'>('password');

  // Form State - Password Login
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(true);

  // Form State - OTP Login
  const [otpPhone, setOtpPhone] = useState('');
  const [otpStep, setOtpStep] = useState<'request' | 'verify'>('request');
  const [otpCode, setOtpCode] = useState(['', '', '', '', '', '']);
  const [resendTimer, setResendTimer] = useState(0);
  const [simulatedReceivedCode, setSimulatedReceivedCode] = useState('');

  // Form State - Ghana Card Login
  const [ghanaCardPin, setGhanaCardPin] = useState('');
  const [isScanningBiometric, setIsScanningBiometric] = useState(false);

  // Password Reset Modal
  const [isForgotModalOpen, setIsForgotModalOpen] = useState(false);
  const [resetIdentifier, setResetIdentifier] = useState('');
  const [resetStep, setResetStep] = useState<'request' | 'verify'>('request');
  const [resetCode, setResetCode] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [resetSuccessMsg, setResetSuccessMsg] = useState('');
  const [isResetting, setIsResetting] = useState(false);

  // General State
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  // Telecom auto-detection for Ghanaian phone numbers
  const detectedNetwork = useMemo(() => {
    const input = authMethod === 'otp' ? otpPhone : identifier;
    if (input.includes('@')) return 'email';
    return detectGhanaNetwork(input);
  }, [authMethod, otpPhone, identifier]);

  // Countdown timer for OTP
  useEffect(() => {
    let interval: NodeJS.Timeout;
    if (resendTimer > 0) {
      interval = setInterval(() => {
        setResendTimer((prev) => prev - 1);
      }, 1000);
    }
    return () => clearInterval(interval);
  }, [resendTimer]);

  // Handle Standard Password Login
  const handlePasswordSubmit = async (e: React.FormEvent) => {
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
        triggerSuccessCelebration();
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

  // Handle Requesting OTP Code
  const handleRequestOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');

    if (!otpPhone.trim() || otpPhone.replace(/\D/g, '').length < 9) {
      setErrorMsg('Please enter a valid 10-digit Ghanaian phone number (e.g. 024 XXX XXXX).');
      return;
    }

    setIsLoading(true);
    await new Promise((r) => setTimeout(r, 600));
    setIsLoading(false);

    // Generate random 6-digit verification code
    const generated = Math.floor(100000 + Math.random() * 900000).toString();
    setSimulatedReceivedCode(generated);
    setOtpStep('verify');
    setResendTimer(45);
  };

  // Handle Submitting OTP
  const handleVerifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');

    const fullCode = otpCode.join('');
    if (fullCode.length < 6) {
      setErrorMsg('Please enter the complete 6-digit SMS verification code.');
      return;
    }

    setIsLoading(true);
    try {
      const res = await loginWithOtp(otpPhone, fullCode);
      if (res.success) {
        triggerSuccessCelebration();
        setTimeout(() => {
          const userRole = res.user?.role || activeInterface;
          router.push(userRole === 'client' ? '/dashboard/client' : '/dashboard/provider');
        }, 800);
      } else {
        setErrorMsg(res.message || 'Invalid SMS code. Please check and re-enter.');
        setIsLoading(false);
      }
    } catch (err: any) {
      setErrorMsg(err?.message || 'OTP verification failed.');
      setIsLoading(false);
    }
  };

  // Handle Ghana Card Login
  const handleGhanaCardSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');

    if (!validateGhanaCardPin(ghanaCardPin)) {
      setErrorMsg('Invalid Ghana Card format. PIN must strictly follow GHA-XXXXXXXXX-X.');
      return;
    }

    setIsLoading(true);
    setIsScanningBiometric(true);
    await new Promise((r) => setTimeout(r, 1200)); // simulated biometric sensor match
    setIsScanningBiometric(false);

    try {
      const res = await loginWithGhanaCard(ghanaCardPin);
      if (res.success) {
        triggerSuccessCelebration();
        setTimeout(() => {
          const userRole = res.user?.role || activeInterface;
          router.push(userRole === 'client' ? '/dashboard/client' : '/dashboard/provider');
        }, 800);
      } else {
        setErrorMsg(res.message || 'National ID verification could not be matched.');
        setIsLoading(false);
      }
    } catch (err: any) {
      setErrorMsg(err?.message || 'Biometric authentication failed.');
      setIsLoading(false);
    }
  };

  // Handle OTP Input Change for 6 digits
  const handleOtpBoxChange = (idx: number, val: string) => {
    if (!/^\d*$/.test(val)) return;
    const updated = [...otpCode];
    updated[idx] = val.slice(-1);
    setOtpCode(updated);

    // Auto-focus next input
    if (val && idx < 5) {
      const nextInput = document.getElementById(`otp-input-${idx + 1}`);
      nextInput?.focus();
    }
  };

  // Handle 1-Click Quick Demo Login
  const handleQuickDemo = (demo: 'kwame_provider' | 'frimpong_client') => {
    loginDemoUser(demo);
    triggerSuccessCelebration();
    setTimeout(() => {
      router.push(demo === 'frimpong_client' ? '/dashboard/client' : '/dashboard/provider');
    }, 600);
  };

  // Forgot Password Request
  const handleRequestPasswordReset = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!resetIdentifier.trim()) return;

    setIsResetting(true);
    await requestPasswordReset(resetIdentifier);
    setIsResetting(false);
    setResetStep('verify');
  };

  // Forgot Password Complete
  const handleResetPasswordSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!resetCode || !newPassword) return;

    setIsResetting(true);
    const res = await resetPassword(resetIdentifier, resetCode, newPassword);
    setIsResetting(false);

    if (res.success) {
      setResetSuccessMsg('Your password has been successfully updated! You can now sign in.');
      setTimeout(() => {
        setIsForgotModalOpen(false);
        setResetStep('request');
        setResetSuccessMsg('');
        setPassword(newPassword);
        setIdentifier(resetIdentifier);
      }, 1500);
    }
  };

  const triggerSuccessCelebration = () => {
    try {
      confetti({
        particleCount: 85,
        spread: 70,
        origin: { y: 0.6 },
        colors: activeInterface === 'provider' ? ['#00D4C8', '#10B981', '#38BDF8'] : ['#F59E0B', '#D97706', '#10B981'],
      });
    } catch (_) {}
  };

  return (
    <div className="min-h-screen bg-[var(--bg)] text-[var(--tx)] flex flex-col font-body transition-colors relative selection:bg-[var(--cyan)] selection:text-black">
      <SiteHeader />

      {/* ══════ HERO EDITORIAL SECTION (ASYMMETRICAL 2-COLUMN SPLIT) ══════ */}
      <section className="hero" style={{ minHeight: 'auto', paddingTop: '115px', paddingBottom: '70px' }}>
        <div className="hero-container" style={{ alignItems: 'flex-start' }}>
          
          {/* ══════ LEFT COLUMN: BOLD EDITORIAL HEADLINE & TRUST SHOWCASE ══════ */}
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

            {/* ══════ HOMEPAGE-STYLE ARTISAN & CLIENT SHOWCASE CARDS ══════ */}
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

          {/* ══════ RIGHT COLUMN: AUTHENTICATION SUITE (HOMEPAGE LUXURY GLOWING CARD) ══════ */}
          <div className="hero-right-showcase w-full max-w-[620px]">
            <div className="showcase-outer-wrap">
              <SpotlightCard
                spotlightColor={
                  activeInterface === 'provider'
                    ? 'rgba(0, 212, 200, 0.22)'
                    : 'rgba(245, 158, 11, 0.22)'
                }
                className={`relative overflow-hidden rounded-3xl transition-all duration-500 p-6 sm:p-9 flex flex-col gap-5 ${
                  activeInterface === 'provider'
                    ? 'border-cyan-500/40 shadow-[0_24px_65px_rgba(0,0,0,0.5),0_0_35px_rgba(0,212,200,0.22),0_0_0_1px_rgba(0,212,200,0.35)]'
                    : 'border-amber-500/40 shadow-[0_24px_65px_rgba(0,0,0,0.5),0_0_35px_rgba(245,158,11,0.22),0_0_0_1px_rgba(245,158,11,0.35)]'
                }`}
                style={{
                  background:
                    activeInterface === 'provider'
                      ? 'linear-gradient(180deg, rgba(0, 212, 200, 0.05) 0%, var(--surface) 28%)'
                      : 'linear-gradient(180deg, rgba(245, 158, 11, 0.05) 0%, var(--surface) 28%)',
                  backdropFilter: 'blur(20px)',
                }}
              >
                {/* Top Neon Accent Beam with Glow (Signature Homepage Edge) */}
                <div
                  className="absolute -top-[1px] left-6 right-6 h-[2.5px] rounded-full transition-all duration-500 pointer-events-none z-20"
                  style={{
                    background:
                      activeInterface === 'provider'
                        ? 'linear-gradient(90deg, transparent, var(--cyan) 25%, #4DFFE8 50%, var(--cyan) 75%, transparent)'
                        : 'linear-gradient(90deg, transparent, var(--gold) 25%, #FDE68A 50%, var(--gold) 75%, transparent)',
                    boxShadow:
                      activeInterface === 'provider'
                        ? '0 0 16px var(--cyan), 0 0 32px var(--cyan)'
                        : '0 0 16px var(--gold), 0 0 32px var(--gold)',
                  }}
                />

                {/* Bottom Neon Accent Line */}
                <div
                  className="absolute -bottom-[1px] left-12 right-12 h-[1.5px] rounded-full transition-all duration-500 pointer-events-none opacity-70 z-20"
                  style={{
                    background:
                      activeInterface === 'provider'
                        ? 'linear-gradient(90deg, transparent, var(--cyan), transparent)'
                        : 'linear-gradient(90deg, transparent, var(--gold), transparent)',
                    boxShadow:
                      activeInterface === 'provider'
                        ? '0 0 10px var(--cyan)'
                        : '0 0 10px var(--gold)',
                  }}
                />

                {/* Ambient Corner Glow Filters (Matching Homepage .cta-glo) */}
                <div
                  className="absolute -top-16 -right-16 w-52 h-52 rounded-full pointer-events-none filter blur-2xl transition-all duration-500"
                  style={{
                    background:
                      activeInterface === 'provider'
                        ? 'radial-gradient(circle, rgba(0, 212, 200, 0.18) 0%, transparent 70%)'
                        : 'radial-gradient(circle, rgba(245, 158, 11, 0.18) 0%, transparent 70%)',
                  }}
                />
                <div
                  className="absolute -bottom-16 -left-16 w-52 h-52 rounded-full pointer-events-none filter blur-2xl transition-all duration-500"
                  style={{
                    background:
                      activeInterface === 'provider'
                        ? 'radial-gradient(circle, rgba(16, 185, 129, 0.14) 0%, transparent 70%)'
                        : 'radial-gradient(circle, rgba(217, 119, 6, 0.14) 0%, transparent 70%)',
                  }}
                />

                {/* ══════ HOMEPAGE BRAND LOGO ══════ */}
                <div className="flex flex-col items-center justify-center pt-1 pb-1 text-center relative z-10">
                  <Link href="/" className="logo group inline-flex items-center gap-2.5 transition-transform hover:scale-105">
                    <div className="logo-mark group-hover:scale-105 transition-transform">G</div>
                    <span className="logo-text text-2xl">
                      Gig<span>Ghana</span>
                    </span>
                  </Link>
                  <span className="text-[11px] text-[var(--tx-3)] font-mono mt-1.5 flex items-center gap-1.5">
                    <ShieldCheck className="w-3.5 h-3.5 text-[var(--cyan)]" />
                    <span>Escrow Protected &amp; Ghana Card Verified 🇬🇭</span>
                  </span>
                </div>

                {/* Top Floating Gateway Badge */}
                <div className="showcase-top-badge" style={{ position: 'static', transform: 'none', margin: '0 auto' }}>
                  <span className="live-pulse-dot" />
                  <span>
                    {activeInterface === 'provider' ? '🛠️ Master Artisan Gateway' : '🏢 Client & Employer Console'}
                  </span>
                </div>

                {/* ══════ DUAL-ROLE INTERFACE TOGGLE ══════ */}
                <div className="space-y-1.5 relative z-10">
                  <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)]">
                    <span>Select Interface Mode:</span>
                    <span className="text-[10px] text-[var(--tx-3)] font-mono">2 Dedicated Gateways</span>
                  </div>

                  <div className="p-1.5 rounded-xl bg-[var(--surface-2)] border border-[var(--bd)] grid grid-cols-2 gap-1.5 shadow-inner">
                    <button
                      type="button"
                      onClick={() => {
                        setActiveInterface('provider');
                        setErrorMsg('');
                      }}
                      className={`py-2.5 px-3 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-2 ${
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
                      className={`py-2.5 px-3 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-2 ${
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

                {/* ══════ AUTHENTICATION METHOD SELECTOR (3 TABS) ══════ */}
                <div className="border-b border-[var(--bd)] pb-2 flex items-center justify-between gap-1 text-xs relative z-10">
                  <button
                    type="button"
                    onClick={() => {
                      setAuthMethod('password');
                      setErrorMsg('');
                    }}
                    className={`flex-1 py-2 px-2.5 rounded-xl font-bold text-[11px] transition-all flex items-center justify-center gap-1.5 ${
                      authMethod === 'password'
                        ? 'bg-[var(--surface-2)] text-[var(--tx)] border border-[var(--bd2)] shadow-xs'
                        : 'text-[var(--tx-3)] hover:text-[var(--tx-2)]'
                    }`}
                  >
                    <KeyRound className="w-3 h-3 text-[var(--cyan)]" />
                    <span>Password</span>
                  </button>

                  <button
                    type="button"
                    onClick={() => {
                      setAuthMethod('otp');
                      setErrorMsg('');
                    }}
                    className={`flex-1 py-2 px-2.5 rounded-xl font-bold text-[11px] transition-all flex items-center justify-center gap-1.5 ${
                      authMethod === 'otp'
                        ? 'bg-[var(--surface-2)] text-[var(--tx)] border border-[var(--bd2)] shadow-xs'
                        : 'text-[var(--tx-3)] hover:text-[var(--tx-2)]'
                    }`}
                  >
                    <Smartphone className="w-3 h-3 text-emerald-400" />
                    <span>MoMo SMS OTP</span>
                  </button>

                  <button
                    type="button"
                    onClick={() => {
                      setAuthMethod('ghanacard');
                      setErrorMsg('');
                    }}
                    className={`flex-1 py-2 px-2.5 rounded-xl font-bold text-[11px] transition-all flex items-center justify-center gap-1.5 ${
                      authMethod === 'ghanacard'
                        ? 'bg-[var(--surface-2)] text-[var(--tx)] border border-[var(--bd2)] shadow-xs'
                        : 'text-[var(--tx-3)] hover:text-[var(--tx-2)]'
                    }`}
                  >
                    <ShieldCheck className="w-3 h-3 text-amber-400" />
                    <span>Ghana Card</span>
                  </button>
                </div>

                {/* ══════ METHOD 1: STANDARD PASSWORD FORM ══════ */}
                {authMethod === 'password' && (
                  <form onSubmit={handlePasswordSubmit} className="space-y-4 relative z-10">
                    {/* Identifier */}
                    <div className="space-y-1.5">
                      <div className="flex items-center justify-between text-xs font-bold text-[var(--tx)]">
                        <label htmlFor="login-identifier">Email or Ghanaian Phone Number</label>
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
                          className={`w-full h-12 pl-4 pr-11 bg-[var(--surface-2)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd)] ${
                            activeInterface === 'provider'
                              ? 'focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20'
                              : 'focus:border-[var(--gold)] focus:ring-2 focus:ring-[var(--gold)]/20'
                          } focus:outline-none transition-all shadow-inner`}
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

                    {/* Password */}
                    <div className="space-y-1.5">
                      <div className="flex items-center justify-between">
                        <label htmlFor="login-password" className="text-xs font-bold text-[var(--tx)]">
                          Password
                        </label>
                        <button
                          type="button"
                          onClick={() => {
                            setResetIdentifier(identifier);
                            setIsForgotModalOpen(true);
                          }}
                          className="text-[11px] font-semibold text-[var(--cyan)] hover:underline"
                        >
                          Forgot Password?
                        </button>
                      </div>

                      <div className="relative flex items-center">
                        <input
                          id="login-password"
                          type={showPassword ? 'text' : 'password'}
                          value={password}
                          onChange={(e) => setPassword(e.target.value)}
                          placeholder="••••••••••••"
                          className={`w-full h-12 pl-4 pr-12 bg-[var(--surface-2)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd)] ${
                            activeInterface === 'provider'
                              ? 'focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20'
                              : 'focus:border-[var(--gold)] focus:ring-2 focus:ring-[var(--gold)]/20'
                          } focus:outline-none transition-all shadow-inner`}
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

                    {/* Remember Me */}
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

                    {errorMsg && (
                      <div className="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
                        <AlertCircle className="w-4 h-4 shrink-0" />
                        <span>{errorMsg}</span>
                      </div>
                    )}

                    <button
                      type="submit"
                      disabled={isLoading}
                      className={`btn w-full h-12 font-bold text-sm shadow-xl flex items-center justify-center gap-2 transition-all ${
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
                          <span>Sign In to {activeInterface === 'provider' ? 'Artisan Portal' : 'Employer Console'}</span>
                          <ArrowRight className="w-4 h-4" />
                        </>
                      )}
                    </button>
                  </form>
                )}

                {/* ══════ METHOD 2: MOBILE MONEY SMS OTP FORM ══════ */}
                {authMethod === 'otp' && (
                  <div className="space-y-4 relative z-10">
                    {otpStep === 'request' ? (
                      <form onSubmit={handleRequestOtp} className="space-y-4">
                        <div className="space-y-1.5">
                          <div className="flex items-center justify-between text-xs font-bold text-[var(--tx)]">
                            <label htmlFor="otp-phone">Mobile Money Phone Number</label>
                            {detectedNetwork === 'mtn' && (
                              <span className="px-2 py-0.5 rounded-full bg-amber-500/15 text-amber-500 font-mono text-[10px] font-bold border border-amber-500/30">
                                🟡 MTN MoMo
                              </span>
                            )}
                            {detectedNetwork === 'telecel' && (
                              <span className="px-2 py-0.5 rounded-full bg-red-500/15 text-red-500 font-mono text-[10px] font-bold border border-red-500/30">
                                🔴 Telecel Cash
                              </span>
                            )}
                            {detectedNetwork === 'at' && (
                              <span className="px-2 py-0.5 rounded-full bg-blue-500/15 text-blue-400 font-mono text-[10px] font-bold border border-blue-500/30">
                                🔵 AT Money
                              </span>
                            )}
                          </div>

                          <div className="relative flex items-center">
                            <input
                              id="otp-phone"
                              type="tel"
                              value={otpPhone}
                              onChange={(e) => setOtpPhone(e.target.value)}
                              placeholder="024 XXX XXXX"
                              className="w-full h-12 pl-4 pr-11 bg-[var(--surface-2)] text-[var(--tx)] text-sm font-mono font-semibold rounded-xl border border-[var(--bd)] focus:border-emerald-500 focus:outline-none transition-all shadow-inner"
                              required
                            />
                            <Smartphone className="w-4 h-4 text-emerald-400 absolute right-4 pointer-events-none" />
                          </div>
                          <span className="text-[10px] text-[var(--tx-3)]">
                            We will send a 6-digit authentication token to this registered line.
                          </span>
                        </div>

                        {errorMsg && (
                          <div className="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
                            <AlertCircle className="w-4 h-4 shrink-0" />
                            <span>{errorMsg}</span>
                          </div>
                        )}

                        <button
                          type="submit"
                          disabled={isLoading}
                          className="btn btn-blue w-full h-12 font-bold text-sm shadow-xl flex items-center justify-center gap-2"
                        >
                          {isLoading ? (
                            <>
                              <div className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
                              <span>Dispatching SMS Code...</span>
                            </>
                          ) : (
                            <>
                              <Send className="w-4 h-4" />
                              <span>Send 6-Digit SMS Code</span>
                            </>
                          )}
                        </button>
                      </form>
                    ) : (
                      <form onSubmit={handleVerifyOtp} className="space-y-4 animate-in fade-in duration-200">
                        
                        {/* Simulated Incoming SMS Toast */}
                        <div className="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-xs text-emerald-500 flex items-start gap-2.5 shadow-sm">
                          <CheckCircle2 className="w-4 h-4 shrink-0 mt-0.5" />
                          <div className="flex-1">
                            <div className="font-bold font-mono">[SMS Simulation Received]</div>
                            <div className="text-[11px] text-[var(--tx)] mt-0.5">
                              GigGhana Security: Your one-time login code is <strong className="font-mono font-black text-emerald-400">{simulatedReceivedCode}</strong>.
                            </div>
                            <button
                              type="button"
                              onClick={() => {
                                const digits = simulatedReceivedCode.split('');
                                setOtpCode(digits);
                              }}
                              className="mt-1 text-[10px] font-bold text-[var(--cyan)] underline"
                            >
                              Auto-fill Code
                            </button>
                          </div>
                        </div>

                        <div className="space-y-2">
                          <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                            <span>Enter 6-Digit SMS Code</span>
                            <button
                              type="button"
                              onClick={() => setOtpStep('request')}
                              className="text-[10px] text-[var(--cyan)] hover:underline"
                            >
                              Change Phone
                            </button>
                          </label>

                          <div className="grid grid-cols-6 gap-2">
                            {otpCode.map((digit, idx) => (
                              <input
                                key={idx}
                                id={`otp-input-${idx}`}
                                type="text"
                                maxLength={1}
                                value={digit}
                                onChange={(e) => handleOtpBoxChange(idx, e.target.value)}
                                className="h-12 text-center text-lg font-mono font-black bg-[var(--surface-2)] text-[var(--tx)] rounded-xl border border-[var(--bd)] focus:border-emerald-500 focus:outline-none shadow-inner"
                              />
                            ))}
                          </div>

                          <div className="flex items-center justify-between text-[11px] text-[var(--tx-3)] pt-1">
                            <span>Didn&apos;t receive the code?</span>
                            {resendTimer > 0 ? (
                              <span className="font-mono text-amber-400">Resend in {resendTimer}s</span>
                            ) : (
                              <button
                                type="button"
                                onClick={() => setResendTimer(45)}
                                className="font-bold text-[var(--cyan)] hover:underline"
                              >
                                Resend Code
                              </button>
                            )}
                          </div>
                        </div>

                        {errorMsg && (
                          <div className="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
                            <AlertCircle className="w-4 h-4 shrink-0" />
                            <span>{errorMsg}</span>
                          </div>
                        )}

                        <button
                          type="submit"
                          disabled={isLoading}
                          className="btn btn-blue w-full h-12 font-bold text-sm shadow-xl flex items-center justify-center gap-2"
                        >
                          {isLoading ? (
                            <>
                              <div className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
                              <span>Authenticating Token...</span>
                            </>
                          ) : (
                            <>
                              <span>Verify &amp; Enter Workspace</span>
                              <ArrowRight className="w-4 h-4" />
                            </>
                          )}
                        </button>
                      </form>
                    )}
                  </div>
                )}

                {/* ══════ METHOD 3: GHANA CARD BIOMETRIC PIN FORM ══════ */}
                {authMethod === 'ghanacard' && (
                  <form onSubmit={handleGhanaCardSubmit} className="space-y-4 animate-in fade-in duration-200 relative z-10">
                    <div className="space-y-1.5">
                      <div className="flex items-center justify-between text-xs font-bold text-[var(--tx)]">
                        <label htmlFor="ghana-card-pin">National Identity PIN</label>
                        <span className="text-[10px] text-amber-500 font-mono font-bold">NIA Biometric</span>
                      </div>

                      <div className="relative flex items-center">
                        <input
                          id="ghana-card-pin"
                          type="text"
                          value={ghanaCardPin}
                          onChange={(e) => setGhanaCardPin(formatGhanaCardPin(e.target.value))}
                          placeholder="GHA-XXXXXXXXX-X"
                          className="w-full h-12 pl-4 pr-11 bg-[var(--surface-2)] text-[var(--tx)] text-sm font-mono font-bold tracking-wider rounded-xl border border-[var(--bd)] focus:border-amber-500 focus:outline-none transition-all shadow-inner uppercase"
                          required
                        />
                        <ShieldCheck className="w-4 h-4 text-amber-400 absolute right-4 pointer-events-none" />
                      </div>
                      <span className="text-[10px] text-[var(--tx-3)]">
                        Format: GHA-XXXXXXXXX-X (Issued by National Identification Authority)
                      </span>
                    </div>

                    {/* Biometric Sensor Simulation Banner */}
                    <div className="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/25 flex items-center gap-3">
                      <div className="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center shrink-0">
                        <Fingerprint className="w-6 h-6 animate-pulse" />
                      </div>
                      <div className="text-xs">
                        <div className="font-bold text-[var(--tx)]">Instant Biometric Verification</div>
                        <div className="text-[10px] text-[var(--tx-2)]">
                          Device fingerprint &amp; facial match against NIA repository.
                        </div>
                      </div>
                    </div>

                    {errorMsg && (
                      <div className="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
                        <AlertCircle className="w-4 h-4 shrink-0" />
                        <span>{errorMsg}</span>
                      </div>
                    )}

                    <button
                      type="submit"
                      disabled={isLoading}
                      className="btn btn-gold w-full h-12 font-bold text-sm shadow-xl flex items-center justify-center gap-2"
                    >
                      {isScanningBiometric ? (
                        <>
                          <Fingerprint className="w-4 h-4 animate-bounce" />
                          <span>Matching NIA Biometrics...</span>
                        </>
                      ) : (
                        <>
                          <ShieldCheck className="w-4 h-4" />
                          <span>Verify Ghana Card &amp; Sign In</span>
                        </>
                      )}
                    </button>
                  </form>
                )}

                {/* Device & Session Security Footnote */}
                <div className="pt-2 flex items-center justify-center gap-2 text-[10px] text-[var(--tx-3)] font-mono relative z-10">
                  <Lock className="w-3 h-3 text-emerald-400" />
                  <span>256-Bit SSL Encrypted · Accra, Ghana Session</span>
                </div>

                {/* Register Switcher */}
                <div className="pt-4 border-t border-[var(--bd)] text-center text-xs text-[var(--tx-2)] space-y-2 relative z-10">
                  <div>New to GigGhana? Register with your national ID:</div>
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

              </SpotlightCard>
            </div>
          </div>

        </div>
      </section>

      {/* ══════ INTERACTIVE FORGOT PASSWORD MODAL ══════ */}
      {isForgotModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/65 backdrop-blur-sm animate-in fade-in duration-200">
          <div className="w-full max-w-md rounded-3xl bg-[var(--surface)] border border-[var(--bd2)] shadow-2xl p-6 sm:p-8 relative">
            
            <button
              onClick={() => setIsForgotModalOpen(false)}
              className="absolute right-5 top-5 p-2 rounded-xl text-[var(--tx-3)] hover:text-[var(--tx)] hover:bg-[var(--surface-2)] transition-colors"
            >
              <X className="w-5 h-5" />
            </button>

            {resetSuccessMsg ? (
              <div className="py-6 text-center space-y-3">
                <div className="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 flex items-center justify-center mx-auto shadow-md">
                  <CheckCircle2 className="w-7 h-7" />
                </div>
                <h3 className="text-lg font-bold text-[var(--tx)]">Password Reset Complete</h3>
                <p className="text-xs text-[var(--tx-2)] max-w-xs mx-auto">
                  {resetSuccessMsg}
                </p>
              </div>
            ) : resetStep === 'request' ? (
              <form onSubmit={handleRequestPasswordReset} className="space-y-4">
                <div>
                  <div className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[var(--cyan-dim)] text-[var(--cyan)] text-[10px] font-bold uppercase tracking-wider mb-2">
                    <KeyRound className="w-3 h-3" />
                    <span>Self-Service Recovery</span>
                  </div>
                  <h3 className="text-lg font-bold text-[var(--tx)]">Reset Your Password</h3>
                  <p className="text-xs text-[var(--tx-2)] mt-0.5">
                    Enter your registered email address or Ghanaian phone number to receive a recovery token.
                  </p>
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">Email or Phone Number</label>
                  <input
                    type="text"
                    required
                    value={resetIdentifier}
                    onChange={(e) => setResetIdentifier(e.target.value)}
                    placeholder="024 XXX XXXX or your@email.com"
                    className="w-full h-11 px-3.5 bg-[var(--surface-2)] text-[var(--tx)] text-xs rounded-xl border border-[var(--bd)] focus:border-[var(--cyan)] focus:outline-none"
                  />
                </div>

                <div className="pt-2 flex items-center justify-end gap-2">
                  <button
                    type="button"
                    onClick={() => setIsForgotModalOpen(false)}
                    className="btn btn-ghost px-4 py-2 text-xs font-bold"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={isResetting}
                    className="btn btn-blue px-4 py-2 text-xs font-bold flex items-center gap-1.5"
                  >
                    {isResetting ? 'Sending...' : 'Send Recovery Code'}
                  </button>
                </div>
              </form>
            ) : (
              <form onSubmit={handleResetPasswordSubmit} className="space-y-4">
                <div>
                  <h3 className="text-lg font-bold text-[var(--tx)]">Enter Recovery Code</h3>
                  <p className="text-xs text-[var(--tx-2)] mt-0.5">
                    Code dispatched to <strong>{resetIdentifier}</strong>. Enter code &amp; set a new password.
                  </p>
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">6-Digit Recovery Token</label>
                  <input
                    type="text"
                    required
                    placeholder="123456"
                    value={resetCode}
                    onChange={(e) => setResetCode(e.target.value)}
                    className="w-full h-11 px-3.5 bg-[var(--surface-2)] text-[var(--tx)] text-xs font-mono font-bold tracking-widest rounded-xl border border-[var(--bd)] focus:border-[var(--cyan)] focus:outline-none"
                  />
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">New Secure Password</label>
                  <input
                    type="password"
                    required
                    placeholder="••••••••••••"
                    value={newPassword}
                    onChange={(e) => setNewPassword(e.target.value)}
                    className="w-full h-11 px-3.5 bg-[var(--surface-2)] text-[var(--tx)] text-xs rounded-xl border border-[var(--bd)] focus:border-[var(--cyan)] focus:outline-none"
                  />
                </div>

                <div className="pt-2 flex items-center justify-end gap-2">
                  <button
                    type="button"
                    onClick={() => setResetStep('request')}
                    className="btn btn-ghost px-3 py-2 text-xs font-bold"
                  >
                    Back
                  </button>
                  <button
                    type="submit"
                    disabled={isResetting}
                    className="btn btn-gold px-4 py-2 text-xs font-bold flex items-center gap-1.5"
                  >
                    {isResetting ? 'Updating...' : 'Set New Password'}
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
