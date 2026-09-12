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
  Sparkles,
  Mail,
  Building2,
  Wrench,
  Check,
  AlertCircle,
  KeyRound,
  Fingerprint,
  RefreshCw,
  X,
  Send,
  ShieldCheck,
} from 'lucide-react';

function LoginContent() {
  const router = useRouter();
  const { login, loginWithOtp, loginWithGhanaCard, requestPasswordReset, resetPassword } = useAuth();

  // Active Gateway: 'provider' (Artisan) vs 'client' (Employer)
  const [activeInterface, setActiveInterface] = useState<'provider' | 'client'>('provider');

  // Sign-in Method: 'password' | 'otp' | 'ghanacard'
  const [authMethod, setAuthMethod] = useState<'password' | 'otp' | 'ghanacard'>('password');

  // Form State - Password Login (PHP structure)
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

  // Handle Standard Password Login (PHP auth/login.php flow)
  const handlePasswordSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    setSuccessMsg('');

    if (!identifier.trim()) {
      setErrorMsg('Please enter your registered email address or Ghanaian phone number.');
      return;
    }
    if (!password) {
      setErrorMsg('Password is required.');
      return;
    }

    setIsLoading(true);
    try {
      const res = await login(identifier, password, rememberMe);
      if (res.success) {
        setSuccessMsg('Login successful! Redirecting to your dashboard...');
        triggerSuccessCelebration();
        setTimeout(() => {
          const userRole = res.user?.role || activeInterface;
          router.push(userRole === 'client' ? '/dashboard/client' : '/dashboard/provider');
        }, 800);
      } else {
        setErrorMsg(res.message || 'Invalid email/phone or password. Please try again.');
        setIsLoading(false);
      }
    } catch (err: any) {
      setErrorMsg(err?.message || 'Login failed. Please verify database connection.');
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
    try {
      const res = await fetch('/api/auth/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'resend', identifier: otpPhone }),
      });
      const data = await res.json();
      setIsLoading(false);

      const generated = data.otpCode || Math.floor(100000 + Math.random() * 900000).toString();
      setSimulatedReceivedCode(generated);
      setOtpStep('verify');
      setResendTimer(60);
    } catch {
      setIsLoading(false);
      const generated = Math.floor(100000 + Math.random() * 900000).toString();
      setSimulatedReceivedCode(generated);
      setOtpStep('verify');
      setResendTimer(60);
    }
  };

  // Handle Verifying OTP Code
  const handleVerifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');

    const fullCode = otpCode.join('');
    if (fullCode.length !== 6) {
      setErrorMsg('Please enter the complete 6-digit verification code.');
      return;
    }

    setIsLoading(true);
    try {
      const res = await loginWithOtp(otpPhone, fullCode);
      if (res.success) {
        setSuccessMsg('Phone verified! Loading dashboard...');
        triggerSuccessCelebration();
        setTimeout(() => {
          const userRole = res.user?.role || activeInterface;
          router.push(userRole === 'client' ? '/dashboard/client' : '/dashboard/provider');
        }, 800);
      } else {
        setErrorMsg(res.message || 'Invalid code. Please check your SMS and try again.');
        setIsLoading(false);
      }
    } catch (err: any) {
      setErrorMsg(err?.message || 'OTP verification failed.');
      setIsLoading(false);
    }
  };

  // Handle Ghana Card PIN Login
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
      const res = await loginWithGhanaCard(cleanPin);
      if (res.success) {
        setSuccessMsg('Ghana Card verified! Welcome back.');
        triggerSuccessCelebration();
        setTimeout(() => {
          const userRole = res.user?.role || activeInterface;
          router.push(userRole === 'client' ? '/dashboard/client' : '/dashboard/provider');
        }, 900);
      } else {
        setErrorMsg(res.message || 'Ghana Card not found in registry.');
        setIsScanningBiometric(false);
        setIsLoading(false);
      }
    } catch (err: any) {
      setErrorMsg(err?.message || 'Verification failed.');
      setIsScanningBiometric(false);
      setIsLoading(false);
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
      subtitle="Sign in to access your dashboard, active contracts, and secure escrow vault."
      backHref="/"
      backLabel="Back to Home"
    >
      <div className="space-y-6">

        {/* ══════ DUAL-ROLE GATEWAY SELECTOR (Artisan vs Client) ══════ */}
        <div className="space-y-1.5">
          <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)]">
            <span>Workspace Gateway:</span>
            <span className="text-[10px] text-[var(--tx-3)] font-mono">2 Dedicated Consoles</span>
          </div>

          <div className="p-1.5 rounded-2xl bg-[var(--surface-elevated)] border border-[var(--bd2)] grid grid-cols-2 gap-1.5 shadow-inner">
            <button
              type="button"
              onClick={() => {
                setActiveInterface('provider');
                setErrorMsg('');
              }}
              className={`py-2.5 px-3 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-2 ${
                activeInterface === 'provider'
                  ? 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 shadow-md shadow-cyan-500/20 scale-[1.01]'
                  : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface)]'
              }`}
            >
              <Wrench className="w-3.5 h-3.5" />
              <span>Artisan Gateway</span>
            </button>

            <button
              type="button"
              onClick={() => {
                setActiveInterface('client');
                setErrorMsg('');
              }}
              className={`py-2.5 px-3 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-2 ${
                activeInterface === 'client'
                  ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] text-slate-950 shadow-md shadow-amber-500/20 scale-[1.01]'
                  : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface)]'
              }`}
            >
              <Building2 className="w-3.5 h-3.5" />
              <span>Client Console</span>
            </button>
          </div>
        </div>

        {/* ══════ AUTHENTICATION METHOD TABS (Password, OTP, Ghana Card) ══════ */}
        <div className="border-b border-[var(--bd2)] pb-2 flex items-center justify-between gap-1 text-xs">
          <button
            type="button"
            onClick={() => {
              setAuthMethod('password');
              setErrorMsg('');
            }}
            className={`flex-1 py-1.5 rounded-lg font-bold text-[11px] transition-all flex items-center justify-center gap-1.5 ${
              authMethod === 'password'
                ? 'bg-[var(--surface-elevated)] text-[var(--tx)] border border-[var(--bd2)] shadow-xs'
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
            className={`flex-1 py-1.5 rounded-lg font-bold text-[11px] transition-all flex items-center justify-center gap-1.5 ${
              authMethod === 'otp'
                ? 'bg-[var(--surface-elevated)] text-[var(--tx)] border border-[var(--bd2)] shadow-xs'
                : 'text-[var(--tx-3)] hover:text-[var(--tx-2)]'
            }`}
          >
            <Smartphone className="w-3 h-3 text-emerald-400" />
            <span>SMS OTP</span>
          </button>

          <button
            type="button"
            onClick={() => {
              setAuthMethod('ghanacard');
              setErrorMsg('');
            }}
            className={`flex-1 py-1.5 rounded-lg font-bold text-[11px] transition-all flex items-center justify-center gap-1.5 ${
              authMethod === 'ghanacard'
                ? 'bg-[var(--surface-elevated)] text-[var(--tx)] border border-[var(--bd2)] shadow-xs'
                : 'text-[var(--tx-3)] hover:text-[var(--tx-2)]'
            }`}
          >
            <Fingerprint className="w-3 h-3 text-[#F59E0B]" />
            <span>Ghana Card</span>
          </button>
        </div>

        {/* Success Alert */}
        {successMsg && (
          <div className="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 text-xs font-semibold flex items-center gap-2">
            <Check className="w-4 h-4 shrink-0" />
            <span>{successMsg}</span>
          </div>
        )}

        {/* Error Alert */}
        {errorMsg && (
          <div className="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
            <AlertCircle className="w-4 h-4 shrink-0" />
            <span>{errorMsg}</span>
          </div>
        )}

        {/* ══════ 1. STANDARD PASSWORD LOGIN FORM (PHP auth/login.php) ══════ */}
        {authMethod === 'password' && (
          <form onSubmit={handlePasswordSubmit} className="space-y-4">
            {/* Email Address / Phone */}
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                <span>Email Address or Phone Number</span>
                {detectedNetwork && detectedNetwork !== 'unknown' && detectedNetwork !== 'email' && (
                  <span className="text-[10px] font-mono px-2 py-0.5 rounded bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--cyan)] font-bold uppercase">
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
                  className="w-full h-11 pl-10 pr-4 bg-[var(--surface-elevated)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all placeholder:text-[var(--tx-3)]"
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
                  className="w-full h-11 pl-10 pr-11 bg-[var(--surface-elevated)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all placeholder:text-[var(--tx-3)]"
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

            {/* Remember Me & Forgot Password Row (PHP layout) */}
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
                className={`w-full h-12 rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg transition-all ${
                  activeInterface === 'client'
                    ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] hover:from-[#D97706] hover:to-[#B45309] text-slate-950 shadow-amber-500/20'
                    : 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] hover:from-[#00B4A9] hover:to-[#008B82] text-slate-950 shadow-cyan-500/20'
                }`}
              >
                {isLoading ? (
                  <>
                    <span className="w-4 h-4 rounded-full border-2 border-slate-950 border-t-transparent animate-spin" />
                    <span>Signing in to MySQL...</span>
                  </>
                ) : (
                  <>
                    <span>Login to My Account</span>
                    <ArrowRight className="w-4 h-4" />
                  </>
                )}
              </button>
            </div>
          </form>
        )}

        {/* ══════ 2. OTP SIGN-IN FORM ══════ */}
        {authMethod === 'otp' && (
          <div>
            {otpStep === 'request' ? (
              <form onSubmit={handleRequestOtp} className="space-y-4">
                <div className="space-y-1.5">
                  <label className="text-xs font-bold text-[var(--tx)]">
                    Ghana Mobile Money Number
                  </label>
                  <input
                    type="tel"
                    required
                    value={otpPhone}
                    onChange={(e) => setOtpPhone(e.target.value)}
                    placeholder="024 123 4567"
                    className="w-full h-11 px-3.5 bg-[var(--surface-elevated)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                  />
                  <p className="text-[11px] text-[var(--tx-3)]">
                    We will send a 6-digit verification code to this phone number.
                  </p>
                </div>

                <button
                  type="submit"
                  disabled={isLoading}
                  className="w-full h-11 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-slate-950 font-bold text-xs flex items-center justify-center gap-2 shadow-md"
                >
                  {isLoading ? 'Dispatching Code...' : 'Send SMS Verification Code'}
                </button>
              </form>
            ) : (
              <form onSubmit={handleVerifyOtp} className="space-y-4">
                <div className="space-y-2 text-center">
                  <span className="text-xs text-[var(--tx-2)]">
                    Enter the 6-digit code sent to <strong className="text-[var(--tx)]">{otpPhone}</strong>
                  </span>

                  {simulatedReceivedCode && (
                    <div className="p-2 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-mono">
                      Dev Code: <strong>{simulatedReceivedCode}</strong>
                    </div>
                  )}

                  <div className="flex items-center justify-center gap-2 pt-2">
                    {otpCode.map((digit, idx) => (
                      <input
                        key={idx}
                        id={`otp-box-${idx}`}
                        type="text"
                        maxLength={1}
                        value={digit}
                        onChange={(e) => {
                          const val = e.target.value.replace(/\D/g, '');
                          const newCode = [...otpCode];
                          newCode[idx] = val;
                          setOtpCode(newCode);
                          if (val && idx < 5) {
                            document.getElementById(`otp-box-${idx + 1}`)?.focus();
                          }
                        }}
                        onKeyDown={(e) => {
                          if (e.key === 'Backspace' && !otpCode[idx] && idx > 0) {
                            document.getElementById(`otp-box-${idx - 1}`)?.focus();
                          }
                        }}
                        className="w-10 h-12 text-center font-bold text-lg rounded-xl bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] focus:border-[var(--cyan)] focus:outline-none"
                      />
                    ))}
                  </div>
                </div>

                <div className="flex items-center justify-between text-xs pt-1">
                  <button
                    type="button"
                    onClick={() => setOtpStep('request')}
                    className="text-[var(--tx-3)] hover:text-[var(--tx)]"
                  >
                    Change Number
                  </button>

                  <button
                    type="button"
                    disabled={resendTimer > 0}
                    onClick={handleRequestOtp}
                    className={`font-semibold ${resendTimer > 0 ? 'text-[var(--tx-3)] cursor-not-allowed' : 'text-[var(--cyan)] hover:underline'}`}
                  >
                    {resendTimer > 0 ? `Resend in ${resendTimer}s` : 'Resend Code'}
                  </button>
                </div>

                <button
                  type="submit"
                  disabled={isLoading}
                  className="w-full h-11 rounded-xl bg-[var(--cyan)] text-slate-950 font-bold text-xs flex items-center justify-center gap-2 shadow-md"
                >
                  {isLoading ? 'Verifying...' : 'Verify & Enter Workspace'}
                </button>
              </form>
            )}
          </div>
        )}

        {/* ══════ 3. GHANA CARD PIN LOGIN FORM ══════ */}
        {authMethod === 'ghanacard' && (
          <form onSubmit={handleGhanaCardSubmit} className="space-y-4">
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
                  className="w-full h-11 pl-10 pr-4 bg-[var(--surface-elevated)] text-[var(--tx)] text-sm font-mono font-bold rounded-xl border border-[var(--bd2)] focus:border-[#F59E0B] focus:outline-none"
                />
              </div>
              <p className="text-[11px] text-[var(--tx-3)]">
                Direct biometric instant authentication linked to your verified national profile.
              </p>
            </div>

            <button
              type="submit"
              disabled={isLoading || isScanningBiometric}
              className="w-full h-11 rounded-xl bg-gradient-to-r from-[#F59E0B] to-[#D97706] text-slate-950 font-bold text-xs flex items-center justify-center gap-2 shadow-md"
            >
              {isScanningBiometric ? (
                <>
                  <RefreshCw className="w-4 h-4 animate-spin" />
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

        {/* Trust Badges Strip (matching PHP auth/login.php) */}
        <div className="flex items-center justify-center gap-4 pt-2 text-[10.5px] text-[var(--tx-3)]">
          <div className="flex items-center gap-1">
            <Lock className="w-3 h-3 text-emerald-400" />
            <span>SSL secured</span>
          </div>
          <div className="w-px h-3 bg-[var(--bd2)]" />
          <div className="flex items-center gap-1">
            <span>🇬🇭</span>
            <span>Ghana only</span>
          </div>
          <div className="w-px h-3 bg-[var(--bd2)]" />
          <div className="flex items-center gap-1">
            <ShieldCheck className="w-3 h-3 text-[var(--cyan)]" />
            <span>Verified platform</span>
          </div>
        </div>

        {/* Switch to Register (matching PHP layout) */}
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
          <div className="w-full max-w-md rounded-2xl bg-[var(--surface)] border border-[var(--bd)] p-6 shadow-2xl space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-[var(--bd2)]">
              <div className="flex items-center gap-2">
                <KeyRound className="w-4 h-4 text-[var(--cyan)]" />
                <h3 className="font-heading font-black text-base text-[var(--tx)]">Password Recovery</h3>
              </div>
              <button
                type="button"
                onClick={() => setIsForgotModalOpen(false)}
                className="text-[var(--tx-3)] hover:text-[var(--tx)] p-1"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            {resetSuccessMsg && (
              <div className="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-semibold">
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
                  className="w-full h-10 px-3 bg-[var(--surface-elevated)] text-[var(--tx)] text-xs rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                />
                <button
                  type="submit"
                  disabled={isResetting}
                  className="w-full h-10 rounded-xl bg-[var(--cyan)] text-slate-950 font-bold text-xs flex items-center justify-center gap-1.5"
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
                  className="w-full h-10 px-3 bg-[var(--surface-elevated)] text-[var(--tx)] text-xs font-mono font-bold rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                />
                <input
                  type="password"
                  required
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  placeholder="New password (min 6 characters)"
                  className="w-full h-10 px-3 bg-[var(--surface-elevated)] text-[var(--tx)] text-xs rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                />
                <button
                  type="submit"
                  disabled={isResetting}
                  className="w-full h-10 rounded-xl bg-[var(--cyan)] text-slate-950 font-bold text-xs flex items-center justify-center gap-1.5"
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
