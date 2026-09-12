'use client';

import React, { useState, Suspense } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { AuthLayout } from '@/components/auth/AuthLayout';
import { KeyRound, Mail, Lock, Check, AlertCircle, ArrowRight, Eye, EyeOff, Send } from 'lucide-react';

function ForgotPasswordContent() {
  const router = useRouter();

  const [step, setStep] = useState<1 | 2 | 3>(1);
  const [email, setEmail] = useState('');
  const [code, setCode] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [devCode, setDevCode] = useState('');

  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [successMsg, setSuccessMsg] = useState('');

  // Step 1: Send OTP
  const handleSendOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    setSuccessMsg('');

    if (!email.trim() || !email.includes('@')) {
      setErrorMsg('Please enter a valid registered email address.');
      return;
    }

    setIsLoading(true);
    try {
      const res = await fetch('/api/auth/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'send_otp', email: email.trim().toLowerCase() }),
      });

      const data = await res.json();
      setIsLoading(false);

      if (res.ok && data.success) {
        setSuccessMsg(data.message);
        if (data.otpCode) {
          setDevCode(data.otpCode);
          setCode(data.otpCode);
        }
        setStep(2);
      } else {
        setErrorMsg(data.message || 'Could not send verification code.');
      }
    } catch {
      setIsLoading(false);
      setErrorMsg('Could not send verification code. Please try again.');
    }
  };

  // Step 2: Verify OTP
  const handleVerifyOtp = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    setSuccessMsg('');

    if (!code.trim() || code.trim().length !== 6) {
      setErrorMsg('Please enter the complete 6-digit verification code.');
      return;
    }

    setIsLoading(true);
    try {
      const res = await fetch('/api/auth/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'verify_otp', email: email.trim().toLowerCase(), code: code.trim() }),
      });

      const data = await res.json();
      setIsLoading(false);

      if (res.ok && data.success) {
        setSuccessMsg('Code verified! Please set your new password.');
        setStep(3);
      } else {
        setErrorMsg(data.message || 'Invalid or expired verification code.');
      }
    } catch {
      setIsLoading(false);
      setErrorMsg('Verification failed.');
    }
  };

  // Step 3: Reset Password
  const handleResetPassword = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    setSuccessMsg('');

    if (!newPassword || newPassword.length < 6) {
      setErrorMsg('Password must be at least 6 characters.');
      return;
    }
    if (newPassword !== confirmPassword) {
      setErrorMsg('Passwords do not match.');
      return;
    }

    setIsLoading(true);
    try {
      const res = await fetch('/api/auth/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'reset_password',
          email: email.trim().toLowerCase(),
          code: code.trim(),
          new_password: newPassword,
        }),
      });

      const data = await res.json();
      setIsLoading(false);

      if (res.ok && data.success) {
        setSuccessMsg(data.message || 'Password reset successfully! Redirecting to sign in...');
        setTimeout(() => {
          router.push('/auth/login');
        }, 1500);
      } else {
        setErrorMsg(data.message || 'Password update failed.');
      }
    } catch {
      setIsLoading(false);
      setErrorMsg('Could not update password.');
    }
  };

  return (
    <AuthLayout
      title="Reset Your Password"
      subtitle="Follow the 3-step secure recovery process to restore access to your account."
      backHref="/auth/login"
      backLabel="Back to Sign In"
    >
      <div className="space-y-6">
        {/* Step Indicator */}
        <div className="flex items-center justify-between px-2 text-xs">
          <div className="flex items-center gap-2">
            <span className={`w-6 h-6 rounded-full flex items-center justify-center font-bold text-[11px] ${step >= 1 ? 'bg-[var(--cyan)] text-black' : 'bg-[var(--surface-elevated)] text-[var(--tx-3)]'}`}>
              1
            </span>
            <span className={`font-semibold ${step >= 1 ? 'text-[var(--tx)]' : 'text-[var(--tx-3)]'}`}>Email</span>
          </div>
          <div className="w-8 h-px bg-[var(--bd2)]" />
          <div className="flex items-center gap-2">
            <span className={`w-6 h-6 rounded-full flex items-center justify-center font-bold text-[11px] ${step >= 2 ? 'bg-[var(--cyan)] text-black' : 'bg-[var(--surface-elevated)] text-[var(--tx-3)]'}`}>
              2
            </span>
            <span className={`font-semibold ${step >= 2 ? 'text-[var(--tx)]' : 'text-[var(--tx-3)]'}`}>Code</span>
          </div>
          <div className="w-8 h-px bg-[var(--bd2)]" />
          <div className="flex items-center gap-2">
            <span className={`w-6 h-6 rounded-full flex items-center justify-center font-bold text-[11px] ${step >= 3 ? 'bg-[var(--cyan)] text-black' : 'bg-[var(--surface-elevated)] text-[var(--tx-3)]'}`}>
              3
            </span>
            <span className={`font-semibold ${step >= 3 ? 'text-[var(--tx)]' : 'text-[var(--tx-3)]'}`}>New Password</span>
          </div>
        </div>

        {/* Dev Code Helper */}
        {devCode && (
          <div className="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-mono text-center">
            Dev Code: <strong>{devCode}</strong>
          </div>
        )}

        {/* Alerts */}
        {successMsg && (
          <div className="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-500 text-xs font-semibold flex items-center gap-2">
            <Check className="w-4 h-4 shrink-0" />
            <span>{successMsg}</span>
          </div>
        )}
        {errorMsg && (
          <div className="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
            <AlertCircle className="w-4 h-4 shrink-0" />
            <span>{errorMsg}</span>
          </div>
        )}

        {/* ── STEP 1: Enter Email ── */}
        {step === 1 && (
          <form onSubmit={handleSendOtp} className="space-y-4">
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)]">
                Registered Email Address
              </label>
              <div className="relative flex items-center">
                <Mail className="w-4 h-4 text-[var(--tx-3)] absolute left-3.5 pointer-events-none" />
                <input
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="you@example.com"
                  className="w-full h-11 pl-10 pr-4 bg-[var(--surface-elevated)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none"
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={isLoading}
              className="w-full h-11 rounded-xl bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-md shadow-cyan-500/20"
            >
              <Send className="w-3.5 h-3.5" />
              <span>{isLoading ? 'Generating Code...' : 'Send Recovery Code'}</span>
            </button>
          </form>
        )}

        {/* ── STEP 2: Enter 6-Digit Code ── */}
        {step === 2 && (
          <form onSubmit={handleVerifyOtp} className="space-y-4">
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)]">
                6-Digit Recovery Code
              </label>
              <input
                type="text"
                required
                maxLength={6}
                value={code}
                onChange={(e) => setCode(e.target.value)}
                placeholder="123456"
                className="w-full h-12 px-4 text-center font-bold font-mono text-xl tracking-widest bg-[var(--surface-elevated)] text-[var(--tx)] rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
              />
            </div>

            <div className="flex items-center justify-between text-xs pt-1">
              <button
                type="button"
                onClick={() => setStep(1)}
                className="text-[var(--tx-3)] hover:text-[var(--tx)]"
              >
                Change Email
              </button>
            </div>

            <button
              type="submit"
              disabled={isLoading}
              className="w-full h-11 rounded-xl bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-md shadow-cyan-500/20"
            >
              <span>{isLoading ? 'Verifying...' : 'Verify Code'}</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </button>
          </form>
        )}

        {/* ── STEP 3: Enter New Password ── */}
        {step === 3 && (
          <form onSubmit={handleResetPassword} className="space-y-4">
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)]">New Password</label>
              <div className="relative flex items-center">
                <Lock className="w-4 h-4 text-[var(--tx-3)] absolute left-3.5 pointer-events-none" />
                <input
                  type={showPassword ? 'text' : 'password'}
                  required
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  placeholder="At least 6 characters"
                  className="w-full h-11 pl-10 pr-11 bg-[var(--surface-elevated)] text-[var(--tx)] text-sm rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3.5 text-[var(--tx-3)] hover:text-[var(--tx)] p-1"
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)]">Confirm New Password</label>
              <div className="relative flex items-center">
                <Lock className="w-4 h-4 text-[var(--tx-3)] absolute left-3.5 pointer-events-none" />
                <input
                  type={showPassword ? 'text' : 'password'}
                  required
                  value={confirmPassword}
                  onChange={(e) => setConfirmPassword(e.target.value)}
                  placeholder="Confirm matching password"
                  className="w-full h-11 pl-10 pr-4 bg-[var(--surface-elevated)] text-[var(--tx)] text-sm rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                />
              </div>
            </div>

            <button
              type="submit"
              disabled={isLoading}
              className="w-full h-12 rounded-xl bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-cyan-500/20"
            >
              <span>{isLoading ? 'Updating in MySQL...' : 'Save New Password & Sign In'}</span>
              <ArrowRight className="w-4 h-4" />
            </button>
          </form>
        )}

        {/* Back to Login */}
        <div className="pt-3 text-center border-t border-[var(--bd2)]/40 text-xs text-[var(--tx-2)]">
          Remembered your password?{' '}
          <Link href="/auth/login" className="font-extrabold text-[var(--cyan)] hover:underline">
            Sign In here →
          </Link>
        </div>
      </div>
    </AuthLayout>
  );
}

export default function ForgotPasswordPage() {
  return (
    <Suspense fallback={<div className="min-h-screen bg-[var(--bg)] flex items-center justify-center text-xs text-[var(--tx-3)]">Loading Password Recovery...</div>}>
      <ForgotPasswordContent />
    </Suspense>
  );
}
