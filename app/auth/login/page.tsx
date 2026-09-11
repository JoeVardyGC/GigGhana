'use client';

import React, { useState, Suspense } from 'react';
import { useRouter } from 'next/navigation';
import { AuthLayout } from '@/components/auth/AuthLayout';
import { useAuth } from '@/lib/context/AuthContext';
import confetti from 'canvas-confetti';
import { Lock, Mail, Phone, Eye, EyeOff, ArrowRight, Sparkles, ShieldCheck, BadgeCheck, CheckCircle2 } from 'lucide-react';

function LoginContent() {
  const router = useRouter();
  const { login, loginDemoUser } = useAuth();

  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberMe, setRememberMe] = useState(true);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg('');

    if (!identifier.trim()) {
      setErrorMsg('Please enter your email or Ghanaian telephone number.');
      return;
    }
    if (!password) {
      setErrorMsg('Please enter your password.');
      return;
    }

    setIsLoading(true);
    try {
      const res = await login(identifier, password);
      if (res.success) {
        try {
          confetti({
            particleCount: 70,
            spread: 60,
            origin: { y: 0.6 },
            colors: ['#00D4C8', '#F59E0B', '#10B981'],
          });
        } catch (_) {}

        setTimeout(() => {
          router.push('/');
        }, 800);
      } else {
        setErrorMsg(res.message || 'Invalid credentials. Please try again.');
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
        particleCount: 50,
        spread: 50,
        origin: { y: 0.6 },
        colors: ['#00D4C8', '#F59E0B'],
      });
    } catch (_) {}
    setTimeout(() => {
      router.push('/');
    }, 600);
  };

  return (
    <AuthLayout
      title="Welcome Back to GigGhana"
      subtitle="Sign in with your verified email or phone number to access your active escrow contracts and projects."
    >
      <form onSubmit={handleSubmit} className="space-y-4">
        {/* Email or Phone Input */}
        <div className="space-y-1.5">
          <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
            <span>Email or Phone Number</span>
            <span className="text-[10px] text-[var(--tx-3)] font-mono">024 XXX XXXX or email</span>
          </label>
          <div className="relative flex items-center">
            <input
              type="text"
              value={identifier}
              onChange={(e) => setIdentifier(e.target.value)}
              placeholder="e.g. 024 412 3456 or kwame@domain.com"
              className="w-full h-11 pl-3.5 pr-10 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
            />
            <div className="absolute right-3.5 text-[var(--tx-3)] pointer-events-none">
              {identifier.includes('@') ? <Mail className="w-4 h-4" /> : <Phone className="w-4 h-4" />}
            </div>
          </div>
        </div>

        {/* Password */}
        <div className="space-y-1.5">
          <div className="flex items-center justify-between">
            <label className="text-xs font-bold text-[var(--tx)]">Password</label>
            <a
              href="#"
              onClick={(e) => {
                e.preventDefault();
                alert('Password reset instructions will be sent via SMS or Email.');
              }}
              className="text-[11px] font-semibold text-[var(--cyan)] hover:underline"
            >
              Forgot Password?
            </a>
          </div>
          <div className="relative flex items-center">
            <input
              type={showPassword ? 'text' : 'password'}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="••••••••"
              className="w-full h-11 pl-3.5 pr-10 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
            />
            <button
              type="button"
              onClick={() => setShowPassword(!showPassword)}
              className="absolute right-3 text-[var(--tx-3)] hover:text-[var(--tx)] transition-colors p-1"
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
              className="w-4 h-4 rounded border-[var(--bd2)] text-[var(--cyan)] focus:ring-0 focus:ring-offset-0 bg-[var(--surface)]"
            />
            <span className="text-xs text-[var(--tx-2)] font-medium">Keep me signed in</span>
          </label>
        </div>

        {/* Error Alert */}
        {errorMsg && (
          <div className="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{errorMsg}</span>
          </div>
        )}

        {/* Sign In Button */}
        <button
          type="submit"
          disabled={isLoading}
          className="w-full h-11 rounded-xl bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] hover:from-[#00B4A9] hover:to-[#008B82] text-slate-950 font-black text-xs flex items-center justify-center gap-2 shadow-lg shadow-cyan-500/20 transition-all active:scale-[0.99]"
        >
          {isLoading ? (
            <>
              <span className="w-4 h-4 rounded-full border-2 border-slate-950 border-t-transparent animate-spin" />
              <span>Verifying Credentials...</span>
            </>
          ) : (
            <>
              <span>Sign In to Your Account</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </>
          )}
        </button>

        {/* Quick Demo Test Profiles */}
        <div className="pt-3">
          <div className="text-[11px] font-bold text-[var(--tx-3)] uppercase tracking-wider text-center mb-2 flex items-center justify-center gap-2">
            <span className="h-[1px] bg-[var(--bd2)] flex-1" />
            <span>⚡ Instant One-Click Demo Profiles</span>
            <span className="h-[1px] bg-[var(--bd2)] flex-1" />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
            <button
              type="button"
              onClick={() => handleQuickDemo('kwame_provider')}
              className="p-2.5 rounded-xl border border-[var(--bd2)] hover:border-[var(--cyan)] bg-[var(--s2)]/50 hover:bg-[var(--surface)] text-left flex items-center gap-2.5 transition-all group"
            >
              <div className="w-8 h-8 rounded-full bg-[var(--cyan)]/15 border border-[var(--cyan-border)] flex items-center justify-center text-xs font-bold text-[var(--cyan)]">
                KA
              </div>
              <div className="min-w-0 flex-1">
                <div className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                  <span>Kwame Asante</span>
                  <BadgeCheck className="w-3 h-3 text-[var(--cyan)] shrink-0" />
                </div>
                <div className="text-[10px] text-[var(--tx-3)] truncate">Verified POP Master (Artisan)</div>
              </div>
            </button>

            <button
              type="button"
              onClick={() => handleQuickDemo('frimpong_client')}
              className="p-2.5 rounded-xl border border-[var(--bd2)] hover:border-[#F59E0B] bg-[var(--s2)]/50 hover:bg-[var(--surface)] text-left flex items-center gap-2.5 transition-all group"
            >
              <div className="w-8 h-8 rounded-full bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-xs font-bold text-[#F59E0B]">
                KF
              </div>
              <div className="min-w-0 flex-1">
                <div className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                  <span>Dr. K. Frimpong</span>
                  <BadgeCheck className="w-3 h-3 text-[#F59E0B] shrink-0" />
                </div>
                <div className="text-[10px] text-[var(--tx-3)] truncate">Estate Client (Hiring)</div>
              </div>
            </button>
          </div>
        </div>

        {/* Link to Register */}
        <div className="pt-4 text-center border-t border-[var(--bd2)]/40 text-xs text-[var(--tx-2)]">
          Don&apos;t have a GigGhana account yet?{' '}
          <a href="/auth/register" className="font-extrabold text-[var(--cyan)] hover:underline">
            Register as an Artisan or Client
          </a>
        </div>
      </form>
    </AuthLayout>
  );
}

export default function LoginPage() {
  return (
    <Suspense fallback={<div className="min-h-screen bg-[var(--bg)] flex items-center justify-center text-xs text-[var(--tx-3)]">Loading GigGhana Sign-In...</div>}>
      <LoginContent />
    </Suspense>
  );
}
