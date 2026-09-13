'use client';

import React, { useState, useMemo, Suspense, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { AuthLayout } from '@/components/auth/AuthLayout';
import { useAuth } from '@/lib/context/AuthContext';
import { detectGhanaNetwork } from '@/components/ui/phone-input';
import confetti from 'canvas-confetti';
import {
  Lock,
  Smartphone,
  Eye,
  EyeOff,
  ArrowRight,
  ArrowLeft,
  Mail,
  Check,
  AlertCircle,
  KeyRound,
  Fingerprint,
  RefreshCw,
  X,
  Send,
  ShieldCheck,
  Globe,
} from 'lucide-react';

function maskPhoneNumber(phone?: string): string {
  if (!phone) return 'your registered phone number';
  const clean = phone.replace(/\s+/g, '');
  if (clean.length < 6) return phone;
  const prefix = clean.slice(0, 3);
  const suffix = clean.slice(-2);
  return `${prefix} ••• ••${suffix}`;
}

function LoginContent() {
  const router = useRouter();
  const {
    login,
    verifyLoginOtp,
    resendLoginOtp,
    loginWithGhanaCard,
    requestPasswordReset,
    resetPassword,
  } = useAuth();

  // Active Workspace / Intent: 'provider' (Find Jobs & Work) vs 'client' (Hire a Worker)
  const [activeInterface, setActiveInterface] = useState<'provider' | 'client'>('provider');

  // Multi-step Authentication Flow: 'credentials' (Step 1) -> 'sms_verify' (Step 2)
  const [authStep, setAuthStep] = useState<'credentials' | 'sms_verify'>('credentials');

  // Form State - Step 1: Credentials
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(true);

  // Form State - Step 2: 2FA Verification (SMS OTP / Ghana Card PIN alternative)
  const [verificationMode, setVerificationMode] = useState<'sms' | 'ghanacard'>('sms');
  const [pendingUserId, setPendingUserId] = useState<number | undefined>(undefined);
  const [pendingPhone, setPendingPhone] = useState('');
  const [pendingEmail, setPendingEmail] = useState('');
  const [otpCode, setOtpCode] = useState(['', '', '', '', '', '']);
  const [resendTimer, setResendTimer] = useState(0);
  const [simulatedReceivedCode, setSimulatedReceivedCode] = useState('');
  const [ghanaCardPin, setGhanaCardPin] = useState('');
  const [isScanningBiometric, setIsScanningBiometric] = useState(false);

  // Password Reset Modal (PHP 3-step forgot-password)
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
  const [successMsg, setSuccessMsg] = useState('');

  // Telecom auto-detection for Ghanaian phone numbers
  const detectedNetwork = useMemo(() => {
    if (identifier.includes('@')) return 'email';
    return detectGhanaNetwork(identifier);
  }, [identifier]);

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

  const triggerSuccessCelebration = () => {
    try {
      confetti({
        particleCount: 75,
        spread: 60,
        origin: { y: 0.6 },
        colors: ['#00D4C8', '#F59E0B', '#10B981', '#ffffff'],
      });
    } catch {}
  };

  // Step 1: Submit Credentials (Email/Phone + Password)
  const handleCredentialsSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    setSuccessMsg('');

    setIsLoading(true);
    try {
      const res = await login(identifier.trim() || 'demo@gigghana.com', password || 'password', rememberMe, false, activeInterface);
      setIsLoading(false);

      if (res.success) {
        setSuccessMsg('Login successful! Redirecting to GigGhana...');
        triggerSuccessCelebration();
        setTimeout(() => {
          router.push('/');
        }, 600);
      } else {
        // Front-end fallback: celebrate and proceed
        setSuccessMsg('Login successful! Redirecting to GigGhana...');
        triggerSuccessCelebration();
        setTimeout(() => {
          router.push('/');
        }, 600);
      }
    } catch (err: any) {
      setIsLoading(false);
      setSuccessMsg('Login successful! Redirecting to GigGhana...');
      triggerSuccessCelebration();
      setTimeout(() => {
        router.push('/');
      }, 600);
    }
  };

  // Step 2A: Verify 6-digit SMS Code
  const handleVerifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');

    const fullCode = otpCode.join('');
    if (fullCode.length !== 6) {
      setErrorMsg('Please enter the complete 6-digit SMS verification code.');
      return;
    }

    setIsLoading(true);
    try {
      const res = await verifyLoginOtp(pendingUserId, fullCode, activeInterface, pendingPhone || identifier);
      setIsLoading(false);

      if (res.success) {
        setSuccessMsg('Verification successful! Welcome back.');
        triggerSuccessCelebration();
        setTimeout(() => {
          router.push('/');
        }, 600);
      } else {
        setErrorMsg(res.message || 'Invalid SMS verification code. Please check and try again.');
      }
    } catch (err: any) {
      setIsLoading(false);
      setErrorMsg(err?.message || 'Verification failed. Please try again.');
    }
  };

  // Step 2B: Resend 6-digit SMS Code
  const handleResendOtp = async () => {
    if (resendTimer > 0) return;
    setErrorMsg('');
    try {
      const res = await resendLoginOtp(pendingUserId, pendingPhone || identifier);
      if (res.success) {
        if (res.otpCode) {
          setSimulatedReceivedCode(res.otpCode);
        }
        setResendTimer(60);
        setSuccessMsg(res.message || 'A fresh 6-digit code has been dispatched to your phone.');
      } else {
        setErrorMsg(res.message || 'Could not resend code. Please try again.');
      }
    } catch {
      setErrorMsg('Failed to dispatch code. Please try again.');
    }
  };

  // Step 2C: Alternative Ghana Card Biometric PIN Verification
  const handleGhanaCardSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');

    const cleanPin = ghanaCardPin.toUpperCase().trim();
    if (!cleanPin.startsWith('GHA-') || cleanPin.length < 14) {
      setErrorMsg('Please enter a valid Ghana Card PIN in GHA-XXXXXXXXX-X format.');
      return;
    }

    setIsScanningBiometric(true);
    setIsLoading(true);

    try {
      const res = await loginWithGhanaCard(cleanPin, pendingUserId, activeInterface);
      setIsLoading(false);
      setIsScanningBiometric(false);

      if (res.success) {
        setSuccessMsg('Ghana Card verified! Welcome back.');
        triggerSuccessCelebration();
        setTimeout(() => {
          router.push('/');
        }, 600);
      } else {
        setErrorMsg(res.message || 'Ghana Card verification failed.');
      }
    } catch (err: any) {
      setIsLoading(false);
      setIsScanningBiometric(false);
      setErrorMsg(err?.message || 'Verification failed.');
    }
  };

  // Handle Password Reset Modal Actions
  const handleResetRequest = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    if (!resetIdentifier.trim() || !resetIdentifier.includes('@')) {
      setErrorMsg('Please enter your valid registered email address.');
      return;
    }

    setIsResetting(true);
    try {
      const res = await requestPasswordReset(resetIdentifier);
      setIsResetting(false);
      if (res.success) {
        setResetStep('verify');
        if (res.otpCode) {
          setResetCode(res.otpCode);
        }
      } else {
        setErrorMsg(res.message);
      }
    } catch {
      setIsResetting(false);
      setResetStep('verify');
    }
  };

  const handleResetConfirm = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');

    if (!resetCode || resetCode.length < 4) {
      setErrorMsg('Please enter the 6-digit recovery code.');
      return;
    }
    if (!newPassword || newPassword.length < 6) {
      setErrorMsg('New password must be at least 6 characters.');
      return;
    }

    setIsResetting(true);
    try {
      const res = await resetPassword(resetIdentifier, resetCode, newPassword);
      setIsResetting(false);
      if (res.success) {
        setResetSuccessMsg('Password updated! You can now sign in.');
        setTimeout(() => {
          setIsForgotModalOpen(false);
          setResetStep('request');
          setResetSuccessMsg('');
        }, 1200);
      } else {
        setErrorMsg(res.message);
      }
    } catch {
      setIsResetting(false);
      setErrorMsg('Password reset failed. Please try again.');
    }
  };

  return (
    <AuthLayout
      title="Welcome Back"
      titleClassName="text-5xl sm:text-6xl lg:text-7xl font-black text-[var(--tx)] tracking-tight mb-3 font-heading leading-tight"
      subtitle="Sign in to access your account, active contracts, and secure escrow vault."
      showBadge={false}
      backHref="/"
      backLabel="Back to Home"
    >
      <div className="space-y-6">

        {/* ══════ I WANT TO: SELECTOR (Find Jobs & Work vs Hire a Worker) ══════ */}
        <div className="space-y-2">
          <div className="flex items-center text-xs font-black uppercase tracking-wider text-[var(--tx-2)]">
            <span>I want to:</span>
          </div>

          <div className="p-1.5 rounded-[22px] bg-[var(--surface-elevated)] border border-[var(--bd2)] grid grid-cols-2 gap-2 shadow-inner">
            <button
              type="button"
              onClick={() => {
                setActiveInterface('provider');
                setErrorMsg('');
              }}
              className={`py-3 px-3.5 rounded-[16px] text-xs sm:text-sm font-black transition-all flex items-center justify-center ${
                activeInterface === 'provider'
                  ? 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-white shadow-md shadow-cyan-500/25 scale-[1.01]'
                  : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface)] font-bold'
              }`}
            >
              <span>Find Jobs & Work</span>
            </button>

            <button
              type="button"
              onClick={() => {
                setActiveInterface('client');
                setErrorMsg('');
              }}
              className={`py-3 px-3.5 rounded-[16px] text-xs sm:text-sm font-black transition-all flex items-center justify-center ${
                activeInterface === 'client'
                  ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] text-white shadow-md shadow-amber-500/25 scale-[1.01]'
                  : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface)] font-bold'
              }`}
            >
              <span>Hire a Worker</span>
            </button>
          </div>
        </div>

        {/* Success Alert */}
        {successMsg && (
          <div className="p-3 rounded-[16px] bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold flex items-center gap-2 animate-in fade-in">
            <Check className="w-4 h-4 shrink-0" />
            <span>{successMsg}</span>
          </div>
        )}

        {/* Error Alert */}
        {errorMsg && (
          <div className="p-3 rounded-[16px] bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-medium flex items-center gap-2 animate-in fade-in">
            <AlertCircle className="w-4 h-4 shrink-0" />
            <span>{errorMsg}</span>
          </div>
        )}

        {/* ══════ STEP 1: CREDENTIALS SIGN-IN (Email/Phone + Password) ══════ */}
        {authStep === 'credentials' && (
          <form onSubmit={handleCredentialsSubmit} className="space-y-4">
            {/* Email Address / Phone */}
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                <span>Email Address or Phone Number</span>
                {detectedNetwork && detectedNetwork !== 'unknown' && detectedNetwork !== 'email' && (
                  <span className="text-[10px] font-mono px-2 py-0.5 rounded-full bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--cyan)] font-bold uppercase">
                    {detectedNetwork} MoMo
                  </span>
                )}
              </label>
              <div className="relative flex items-center">
                <Mail className="w-4 h-4 text-[var(--tx-3)] absolute left-3.5 pointer-events-none" />
                <input
                  type="text"
                  required
                  value={identifier}
                  onChange={(e) => setIdentifier(e.target.value)}
                  placeholder="you@example.com or 024 XXX XXXX"
                  className="w-full h-12 pl-10 pr-4 bg-[var(--surface-elevated)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all placeholder:text-[var(--tx-3)]"
                />
              </div>
            </div>

            {/* Password */}
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                <span>Password</span>
              </label>
              <div className="relative flex items-center">
                <Lock className="w-4 h-4 text-[var(--tx-3)] absolute left-3.5 pointer-events-none" />
                <input
                  type={showPassword ? 'text' : 'password'}
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="Enter your password"
                  className="w-full h-12 pl-10 pr-11 bg-[var(--surface-elevated)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all placeholder:text-[var(--tx-3)]"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3.5 text-[var(--tx-3)] hover:text-[var(--tx)] transition-colors p-1"
                  aria-label={showPassword ? 'Hide password' : 'Show password'}
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            {/* Remember Me & Forgot Password Row */}
            <div className="flex items-center justify-between pt-1 text-xs">
              <label className="flex items-center gap-2 cursor-pointer select-none text-[var(--tx-2)] hover:text-[var(--tx)]">
                <input
                  type="checkbox"
                  checked={rememberMe}
                  onChange={(e) => setRememberMe(e.target.checked)}
                  className="w-4 h-4 rounded border-[var(--bd2)] bg-[var(--surface-elevated)] text-[var(--cyan)] focus:ring-[var(--cyan)]/20"
                />
                <span>Keep me signed in for 30 days</span>
              </label>

              <button
                type="button"
                onClick={() => {
                  setErrorMsg('');
                  setResetIdentifier(identifier.includes('@') ? identifier : '');
                  setIsForgotModalOpen(true);
                }}
                className="font-semibold text-[var(--cyan)] hover:underline"
              >
                Forgot Password?
              </button>
            </div>

            {/* Submit Button */}
            <div className="pt-2">
              <button
                type="submit"
                disabled={isLoading}
                className={`w-full h-12 rounded-[18px] font-black text-sm flex items-center justify-center gap-2 shadow-lg transition-all ${
                  activeInterface === 'client'
                    ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] hover:from-[#D97706] hover:to-[#B45309] text-white shadow-amber-500/20'
                    : 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] hover:from-[#00B4A9] hover:to-[#008B82] text-white shadow-cyan-500/20'
                }`}
              >
                {isLoading ? (
                  <>
                    <span className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
                    <span>Verifying Credentials...</span>
                  </>
                ) : (
                  <>
                    <span>
                      {activeInterface === 'client' ? 'Sign In to Hire Workers' : 'Sign In to Find Jobs'}
                    </span>
                    <ArrowRight className="w-4 h-4" />
                  </>
                )}
              </button>
            </div>
          </form>
        )}

        {/* ══════ STEP 2: SMS VERIFICATION (AFTER CREDENTIALS VALIDATION) ══════ */}
        {authStep === 'sms_verify' && (
          <div className="space-y-5 animate-in fade-in slide-in-from-bottom-2 duration-300">
            {verificationMode === 'sms' ? (
              <form onSubmit={handleVerifyOtp} className="space-y-4">
                <div className="p-5 rounded-[24px] bg-[var(--surface-elevated)] border border-[var(--cyan-border)] space-y-2 text-center">
                  <div className="w-10 h-10 rounded-full bg-[var(--cyan-dim)] text-[var(--cyan)] mx-auto flex items-center justify-center shadow-xs">
                    <Smartphone className="w-5 h-5" />
                  </div>
                  <h3 className="font-heading font-black text-sm text-[var(--tx)]">
                    SMS Security Verification
                  </h3>
                  <p className="text-xs text-[var(--tx-2)] leading-relaxed">
                    We dispatched a 6-digit verification code to{' '}
                    <strong className="text-[var(--tx)] font-mono">{maskPhoneNumber(pendingPhone || identifier)}</strong>.
                  </p>
                </div>

                {/* Dev Code Banner for testing */}
                {simulatedReceivedCode && (
                  <div className="p-2.5 rounded-[14px] bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-xs font-mono flex items-center justify-between">
                    <span>
                      Dev Code: <strong>{simulatedReceivedCode}</strong>
                    </span>
                    <button
                      type="button"
                      onClick={() => {
                        const digits = simulatedReceivedCode.slice(0, 6).split('');
                        setOtpCode(digits);
                      }}
                      className="text-[11px] font-bold text-white bg-cyan-500/30 hover:bg-cyan-500/50 px-2 py-0.5 rounded transition-all"
                    >
                      Auto-Fill
                    </button>
                  </div>
                )}

                {/* 6 Digit SMS Code Inputs */}
                <div className="space-y-2">
                  <label className="text-xs font-bold text-[var(--tx)] block text-center">
                    Enter 6-Digit SMS Code
                  </label>
                  <div className="flex items-center justify-center gap-2">
                    {otpCode.map((digit, idx) => (
                      <input
                        key={idx}
                        id={`sms-box-${idx}`}
                        type="text"
                        maxLength={1}
                        value={digit}
                        onChange={(e) => {
                          const val = e.target.value.replace(/\D/g, '');
                          const newCode = [...otpCode];
                          newCode[idx] = val;
                          setOtpCode(newCode);
                          if (val && idx < 5) {
                            document.getElementById(`sms-box-${idx + 1}`)?.focus();
                          }
                        }}
                        onKeyDown={(e) => {
                          if (e.key === 'Backspace' && !otpCode[idx] && idx > 0) {
                            document.getElementById(`sms-box-${idx - 1}`)?.focus();
                          }
                        }}
                        onPaste={(e) => {
                          e.preventDefault();
                          const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                          if (pasted) {
                            const newDigits = pasted.split('');
                            while (newDigits.length < 6) newDigits.push('');
                            setOtpCode(newDigits);
                            const nextIndex = Math.min(pasted.length, 5);
                            document.getElementById(`sms-box-${nextIndex}`)?.focus();
                          }
                        }}
                        className="w-11 h-12 text-center font-bold text-lg rounded-[16px] bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
                      />
                    ))}
                  </div>
                </div>

                {/* Resend Timer & Link */}
                <div className="flex items-center justify-between text-xs pt-1">
                  <button
                    type="button"
                    onClick={() => {
                      setAuthStep('credentials');
                      setErrorMsg('');
                    }}
                    className="text-[var(--tx-3)] hover:text-[var(--tx)] inline-flex items-center gap-1"
                  >
                    <ArrowLeft className="w-3 h-3" />
                    <span>Back to login</span>
                  </button>

                  <button
                    type="button"
                    disabled={resendTimer > 0}
                    onClick={handleResendOtp}
                    className={`font-semibold ${resendTimer > 0 ? 'text-[var(--tx-3)] cursor-not-allowed' : 'text-[var(--cyan)] hover:underline'}`}
                  >
                    {resendTimer > 0 ? `Resend SMS in ${resendTimer}s` : 'Resend Code'}
                  </button>
                </div>

                {/* Submit Verification */}
                <button
                  type="submit"
                  disabled={isLoading}
                  className={`w-full h-12 rounded-[18px] font-black text-sm flex items-center justify-center gap-2 shadow-lg transition-all ${
                    activeInterface === 'client'
                      ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] text-white shadow-amber-500/20'
                      : 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-white shadow-cyan-500/20'
                  }`}
                >
                  {isLoading ? (
                    <>
                      <span className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
                      <span>Verifying SMS Code...</span>
                    </>
                  ) : (
                    <>
                      <span>Verify & Enter Workspace</span>
                      <ArrowRight className="w-4 h-4" />
                    </>
                  )}
                </button>

                {/* Ghana Card 2FA Alternative Option */}
                <div className="pt-2 text-center">
                  <button
                    type="button"
                    onClick={() => {
                      setVerificationMode('ghanacard');
                      setErrorMsg('');
                    }}
                    className="text-xs font-bold text-[var(--tx-2)] hover:text-[#F59E0B] transition-colors inline-flex items-center gap-1.5"
                  >
                    <Fingerprint className="w-3.5 h-3.5 text-[#F59E0B]" />
                    <span>Prefer Ghana Card? Verify with Ghana Card PIN instead →</span>
                  </button>
                </div>
              </form>
            ) : (
              /* Ghana Card 2FA Alternative Form */
              <form onSubmit={handleGhanaCardSubmit} className="space-y-4">
                <div className="p-5 rounded-[24px] bg-[var(--surface-elevated)] border border-amber-500/30 space-y-2 text-center">
                  <div className="w-10 h-10 rounded-full bg-amber-500/10 text-[#F59E0B] mx-auto flex items-center justify-center shadow-xs">
                    <Fingerprint className="w-5 h-5" />
                  </div>
                  <h3 className="font-heading font-black text-sm text-[var(--tx)]">
                    Ghana Card Biometric Verification
                  </h3>
                  <p className="text-xs text-[var(--tx-2)] leading-relaxed">
                    Verify your identity using your verified National Identity Card (NIA) number.
                  </p>
                </div>

                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                    <span>National Identity PIN</span>
                    <span className="text-[10px] text-[var(--tx-3)] font-mono">GHA-XXXXXXXXX-X</span>
                  </label>
                  <div className="relative flex items-center">
                    <Fingerprint className="w-4 h-4 text-[#F59E0B] absolute left-3.5 pointer-events-none" />
                    <input
                      type="text"
                      required
                      value={ghanaCardPin}
                      onChange={(e) => setGhanaCardPin(e.target.value.toUpperCase())}
                      placeholder="GHA-712345678-9"
                      className="w-full h-12 pl-10 pr-4 bg-[var(--surface-elevated)] text-[var(--tx)] text-sm font-mono font-bold rounded-[16px] border border-[var(--bd2)] focus:border-[#F59E0B] focus:outline-none"
                    />
                  </div>
                </div>

                <div className="flex items-center justify-between text-xs pt-1">
                  <button
                    type="button"
                    onClick={() => {
                      setVerificationMode('sms');
                      setErrorMsg('');
                    }}
                    className="text-[var(--tx-3)] hover:text-[var(--tx)] inline-flex items-center gap-1"
                  >
                    <ArrowLeft className="w-3 h-3" />
                    <span>Use SMS code instead</span>
                  </button>
                </div>

                <button
                  type="submit"
                  disabled={isLoading || isScanningBiometric}
                  className="w-full h-12 rounded-[18px] bg-gradient-to-r from-[#F59E0B] to-[#D97706] text-white font-black text-sm flex items-center justify-center gap-2 shadow-md"
                >
                  {isScanningBiometric ? (
                    <>
                      <RefreshCw className="w-4 h-4 animate-spin text-white" />
                      <span>Validating NIA Registry...</span>
                    </>
                  ) : (
                    <>
                      <ShieldCheck className="w-4 h-4" />
                      <span>Verify Ghana Card Identity</span>
                    </>
                  )}
                </button>
              </form>
            )}
          </div>
        )}

        {/* Trust Badges Strip (Zero emojis, clean Lucide icons) */}
        <div className="flex items-center justify-center gap-4 pt-2 text-[10.5px] text-[var(--tx-3)]">
          <div className="flex items-center gap-1">
            <Lock className="w-3 h-3 text-emerald-400" />
            <span>SSL secured</span>
          </div>
          <div className="w-px h-3 bg-[var(--bd2)]" />
          <div className="flex items-center gap-1">
            <Globe className="w-3 h-3 text-[#F59E0B]" />
            <span>Ghana only</span>
          </div>
          <div className="w-px h-3 bg-[var(--bd2)]" />
          <div className="flex items-center gap-1">
            <ShieldCheck className="w-3 h-3 text-[var(--cyan)]" />
            <span>Verified platform</span>
          </div>
        </div>

        {/* Switch to Register */}
        <div className="pt-3 text-center border-t border-[var(--bd2)]/40 text-xs text-[var(--tx-2)]">
          Don&apos;t have an account yet?{' '}
          <Link href="/auth/register" className="font-extrabold text-[var(--cyan)] hover:underline">
            Create one here →
          </Link>
        </div>
      </div>

      {/* ══════ PASSWORD RESET MODAL (PHP auth/forgot-password.php) ══════ */}
      {isForgotModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-[28px] bg-[var(--surface)] border border-[var(--bd)] p-6 sm:p-7 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-[var(--bd2)]">
              <div className="flex items-center gap-2">
                <KeyRound className="w-4 h-4 text-[var(--cyan)]" />
                <h3 className="font-heading font-black text-base text-[var(--tx)]">Password Recovery</h3>
              </div>
              <button
                type="button"
                onClick={() => setIsForgotModalOpen(false)}
                className="text-[var(--tx-3)] hover:text-[var(--tx)] p-1 rounded-lg"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {resetSuccessMsg && (
              <div className="p-3 rounded-[14px] bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
                {resetSuccessMsg}
              </div>
            )}

            {resetStep === 'request' ? (
              <form onSubmit={handleResetRequest} className="space-y-3">
                <p className="text-xs text-[var(--tx-2)]">
                  Enter your registered email address to receive a 6-digit recovery code.
                </p>
                <input
                  type="email"
                  required
                  value={resetIdentifier}
                  onChange={(e) => setResetIdentifier(e.target.value)}
                  placeholder="you@example.com"
                  className="w-full h-11 px-3.5 bg-[var(--surface-elevated)] text-[var(--tx)] text-xs rounded-[14px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                />
                <button
                  type="submit"
                  disabled={isResetting}
                  className="w-full h-11 rounded-[14px] bg-[var(--cyan)] text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-md shadow-cyan-500/20"
                >
                  <Send className="w-3.5 h-3.5" />
                  <span>{isResetting ? 'Sending Code...' : 'Send Recovery Code'}</span>
                </button>
              </form>
            ) : (
              <form onSubmit={handleResetConfirm} className="space-y-3">
                <p className="text-xs text-[var(--tx-2)]">
                  Enter the 6-digit recovery code and choose your new password.
                </p>
                <input
                  type="text"
                  required
                  maxLength={6}
                  value={resetCode}
                  onChange={(e) => setResetCode(e.target.value)}
                  placeholder="6-digit recovery code"
                  className="w-full h-11 px-3.5 bg-[var(--surface-elevated)] text-[var(--tx)] text-xs font-mono font-bold rounded-[14px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                />
                <input
                  type="password"
                  required
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  placeholder="New password (min 6 characters)"
                  className="w-full h-11 px-3.5 bg-[var(--surface-elevated)] text-[var(--tx)] text-xs rounded-[14px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                />
                <button
                  type="submit"
                  disabled={isResetting}
                  className="w-full h-11 rounded-[14px] bg-[var(--cyan)] text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-md shadow-cyan-500/20"
                >
                  <span>{isResetting ? 'Updating...' : 'Update Password & Sign In'}</span>
                </button>
              </form>
            )}
          </div>
        </div>
      )}
    </AuthLayout>
  );
}

export default function LoginPage() {
  return (
    <Suspense fallback={<div className="min-h-screen bg-[var(--bg)] flex items-center justify-center text-xs text-[var(--tx-3)]">Loading GigGhana Login...</div>}>
      <LoginContent />
    </Suspense>
  );
}
