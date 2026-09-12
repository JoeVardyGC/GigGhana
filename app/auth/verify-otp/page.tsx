'use client';

import React, { useState, useEffect, Suspense } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import { AuthLayout } from '@/components/auth/AuthLayout';
import confetti from 'canvas-confetti';
import { ShieldCheck, Check, AlertCircle, ArrowRight, RefreshCw, Mail } from 'lucide-react';

function VerifyOtpContent() {
  const searchParams = useSearchParams();
  const router = useRouter();

  const userId = searchParams.get('userId') || '';
  const email = searchParams.get('email') || '';

  const [digits, setDigits] = useState(['', '', '', '', '', '']);
  const [resendTimer, setResendTimer] = useState(60);
  const [isLoading, setIsLoading] = useState(false);
  const [isResending, setIsResending] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');
  const [successMsg, setSuccessMsg] = useState('');
  const [devCode, setDevCode] = useState('');

  // Countdown timer for resending OTP
  useEffect(() => {
    let interval: NodeJS.Timeout;
    if (resendTimer > 0) {
      interval = setInterval(() => {
        setResendTimer((prev) => prev - 1);
      }, 1000);
    }
    return () => clearInterval(interval);
  }, [resendTimer]);

  const handleDigitChange = (index: number, val: string) => {
    const cleaned = val.replace(/\D/g, '');
    const newDigits = [...digits];
    newDigits[index] = cleaned;
    setDigits(newDigits);

    if (cleaned && index < 5) {
      document.getElementById(`otp-${index + 1}`)?.focus();
    }
  };

  const handleKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Backspace' && !digits[index] && index > 0) {
      document.getElementById(`otp-${index - 1}`)?.focus();
    }
  };

  const handleVerify = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');
    setSuccessMsg('');

    const code = digits.join('');
    if (code.length !== 6) {
      setErrorMsg('Please enter all 6 digits of your verification code.');
      return;
    }

    setIsLoading(true);
    try {
      const res = await fetch('/api/auth/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'verify',
          userId,
          identifier: email,
          code,
        }),
      });

      const data = await res.json();
      setIsLoading(false);

      if (res.ok && data.success) {
        setSuccessMsg(data.message || 'Account verified! Redirecting to workspace...');
        try {
          confetti({
            particleCount: 80,
            spread: 70,
            origin: { y: 0.6 },
            colors: ['#00D4C8', '#F59E0B', '#10B981', '#ffffff'],
          });
        } catch {}

        setTimeout(() => {
          router.push(data.redirectTo || '/dashboard/provider');
        }, 1200);
      } else {
        setErrorMsg(data.message || 'Invalid or expired verification code.');
      }
    } catch {
      setIsLoading(false);
      setErrorMsg('Verification failed. Please try again.');
    }
  };

  const handleResend = async () => {
    if (resendTimer > 0) return;
    setErrorMsg('');
    setIsResending(true);

    try {
      const res = await fetch('/api/auth/verify-otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'resend',
          userId,
          identifier: email,
        }),
      });

      const data = await res.json();
      setIsResending(false);

      if (res.ok && data.success) {
        setSuccessMsg(data.message || 'A new 6-digit code has been generated.');
        if (data.otpCode) {
          setDevCode(data.otpCode);
        }
        setResendTimer(60);
      } else {
        setErrorMsg(data.message || 'Could not resend code. Please try again.');
      }
    } catch {
      setIsResending(false);
      setErrorMsg('Could not resend code. Please check connection.');
    }
  };

  return (
    <AuthLayout
      title="Verify Your Account"
      subtitle={`Enter the 6-digit verification code sent to ${email || 'your registered account'}.`}
      backHref="/auth/login"
      backLabel="Back to Sign In"
    >
      <form onSubmit={handleVerify} className="space-y-6">
        {/* Verification Icon Card */}
        <div className="flex flex-col items-center justify-center text-center space-y-2">
          <div className="w-14 h-14 rounded-2xl bg-[var(--cyan-dim)] border border-[var(--cyan-border)] flex items-center justify-center text-[var(--cyan)] shadow-lg shadow-cyan-500/10">
            <Mail className="w-7 h-7" />
          </div>
          <h3 className="font-heading font-black text-lg text-[var(--tx)]">
            6-Digit Security Code
          </h3>
          <p className="text-xs text-[var(--tx-2)] max-w-sm">
            Check your email inbox or SMS. Code is valid for 15 minutes.
          </p>
        </div>

        {/* Dev hint badge if present */}
        {devCode && (
          <div className="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-mono text-center">
            New Generated Code: <strong>{devCode}</strong>
          </div>
        )}

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

        {/* 6-Digit Inputs (matching PHP auth/verify-otp.php) */}
        <div className="flex items-center justify-center gap-2 sm:gap-3 py-2">
          {digits.map((val, idx) => (
            <input
              key={idx}
              id={`otp-${idx}`}
              type="text"
              maxLength={1}
              value={val}
              onChange={(e) => handleDigitChange(idx, e.target.value)}
              onKeyDown={(e) => handleKeyDown(idx, e)}
              className="w-11 h-14 sm:w-12 sm:h-14 text-center font-bold text-xl sm:text-2xl rounded-xl bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all shadow-inner"
            />
          ))}
        </div>

        {/* Resend Action */}
        <div className="flex items-center justify-between text-xs pt-1">
          <span className="text-[var(--tx-3)]">Didn&apos;t receive the code?</span>
          <button
            type="button"
            disabled={resendTimer > 0 || isResending}
            onClick={handleResend}
            className={`font-semibold flex items-center gap-1.5 ${
              resendTimer > 0 || isResending
                ? 'text-[var(--tx-3)] cursor-not-allowed'
                : 'text-[var(--cyan)] hover:underline'
            }`}
          >
            {isResending ? (
              <RefreshCw className="w-3.5 h-3.5 animate-spin" />
            ) : resendTimer > 0 ? (
              <span>Resend in {resendTimer}s</span>
            ) : (
              <span>Resend Code</span>
            )}
          </button>
        </div>

        {/* Submit Verification Button */}
        <div className="pt-2">
          <button
            type="submit"
            disabled={isLoading}
            className="w-full h-12 rounded-xl bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] hover:from-[#00B4A9] hover:to-[#008B82] text-slate-950 font-black text-sm flex items-center justify-center gap-2 shadow-lg shadow-cyan-500/20 transition-all"
          >
            {isLoading ? (
              <>
                <span className="w-4 h-4 rounded-full border-2 border-slate-950 border-t-transparent animate-spin" />
                <span>Verifying in MySQL...</span>
              </>
            ) : (
              <>
                <span>Complete Verification</span>
                <ArrowRight className="w-4 h-4" />
              </>
            )}
          </button>
        </div>

        {/* Switch to Login */}
        <div className="pt-3 text-center border-t border-[var(--bd2)]/40 text-xs text-[var(--tx-2)]">
          Already verified?{' '}
          <Link href="/auth/login" className="font-extrabold text-[var(--cyan)] hover:underline">
            Sign In here →
          </Link>
        </div>
      </form>
    </AuthLayout>
  );
}

export default function VerifyOtpPage() {
  return (
    <Suspense fallback={<div className="min-h-screen bg-[var(--bg)] flex items-center justify-center text-xs text-[var(--tx-3)]">Loading OTP Verification...</div>}>
      <VerifyOtpContent />
    </Suspense>
  );
}
