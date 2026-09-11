'use client';

import React from 'react';
import { ShieldCheck, Smartphone, Award, Lock, ArrowLeft } from 'lucide-react';

interface AuthLayoutProps {
  children: React.ReactNode;
  title: string;
  subtitle: string;
  backHref?: string;
  backLabel?: string;
}

export function AuthLayout({
  children,
  title,
  subtitle,
  backHref = '/',
  backLabel = 'Back to Home',
}: AuthLayoutProps) {
  return (
    <div className="min-h-screen w-full relative flex flex-col justify-between bg-[var(--bg)] text-[var(--tx)] selection:bg-[var(--cyan)] selection:text-black overflow-x-hidden font-sans">
      {/* Background Ambient Glows matching Homepage */}
      <div className="pointer-events-none fixed inset-0 overflow-hidden z-0">
        <div className="absolute -top-[25%] left-1/2 -translate-x-1/2 w-[900px] h-[500px] bg-radial from-[#00D4C8]/15 via-[#3B82F6]/10 to-transparent blur-3xl opacity-70" />
        <div className="absolute top-[60%] -left-[10%] w-[600px] h-[600px] bg-radial from-[#F59E0B]/10 via-[#10B981]/5 to-transparent blur-3xl opacity-60" />
        <div className="absolute top-[40%] -right-[15%] w-[650px] h-[650px] bg-radial from-[#00D4C8]/10 via-transparent to-transparent blur-3xl opacity-50" />
        <div className="absolute inset-0 bg-[linear-gradient(to_right,var(--bd2)_1px,transparent_1px),linear-gradient(to_bottom,var(--bd2)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] opacity-25" />
      </div>

      {/* Top Brand Navigation Header */}
      <header className="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 py-6 flex items-center justify-between">
        <a href="/" className="flex items-center gap-2.5 group">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#00D4C8] via-[#008B82] to-[#F59E0B] flex items-center justify-center text-slate-950 font-black text-lg shadow-md group-hover:scale-105 transition-transform">
            G
          </div>
          <div className="text-xl font-black tracking-tight text-[var(--tx)]">
            Gig<span className="text-[var(--cyan)]">Ghana</span>
          </div>
        </a>

        <a
          href={backHref}
          className="inline-flex items-center gap-1.5 text-xs font-bold text-[var(--tx-2)] hover:text-[var(--cyan)] px-3.5 py-1.5 rounded-full border border-[var(--bd2)] hover:border-[var(--cyan-border)] bg-[var(--surface)]/80 backdrop-blur-md transition-all shadow-xs"
        >
          <ArrowLeft className="w-3.5 h-3.5" />
          <span>{backLabel}</span>
        </a>
      </header>

      {/* Main Container */}
      <main className="relative z-10 flex-1 flex flex-col items-center justify-center px-4 sm:px-6 py-6 sm:py-10">
        <div className="w-full max-w-xl mx-auto">
          {/* Header titles */}
          <div className="text-center mb-6 sm:mb-8">
            <h1 className="text-2xl sm:text-3xl font-extrabold text-[var(--tx)] tracking-tight mb-2 font-display">
              {title}
            </h1>
            <p className="text-xs sm:text-sm text-[var(--tx-2)] max-w-md mx-auto leading-relaxed">
              {subtitle}
            </p>
          </div>

          {/* Form Card */}
          <div className="relative rounded-2xl bg-[var(--surface)]/90 backdrop-blur-xl border border-[var(--bd2)] p-5 sm:p-8 shadow-2xl shadow-black/5 dark:shadow-black/40">
            {/* Top Accent Line */}
            <div className="absolute -top-[1px] left-8 right-8 h-[2px] bg-gradient-to-r from-transparent via-[var(--cyan)] to-transparent opacity-80" />
            
            {children}
          </div>
        </div>
      </main>

      {/* Security & Escrow Trust Footer */}
      <footer className="relative z-10 w-full max-w-5xl mx-auto px-4 py-6 border-t border-[var(--bd2)]/40 mt-auto">
        <div className="flex flex-wrap items-center justify-center gap-4 sm:gap-8 text-[11px] font-semibold text-[var(--tx-3)]">
          <div className="flex items-center gap-1.5">
            <ShieldCheck className="w-4 h-4 text-[var(--cyan)]" />
            <span>National Identity (NIA) Verification</span>
          </div>
          <div className="flex items-center gap-1.5">
            <Lock className="w-3.5 h-3.5 text-[#F59E0B]" />
            <span>Bank-Grade Escrow Vault</span>
          </div>
          <div className="flex items-center gap-1.5">
            <Smartphone className="w-3.5 h-3.5 text-[#10B981]" />
            <span>Sub-60s MoMo Settlements</span>
          </div>
          <div className="flex items-center gap-1.5">
            <Award className="w-3.5 h-3.5 text-[var(--cyan)]" />
            <span>Data Protection Act (Act 843)</span>
          </div>
        </div>
      </footer>
    </div>
  );
}
