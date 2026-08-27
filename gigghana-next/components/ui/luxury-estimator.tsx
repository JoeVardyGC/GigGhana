'use client';

import React, { useState } from 'react';
import { Sparkles, ArrowRight, ShieldCheck, TrendingUp, Gem } from 'lucide-react';
import confetti from 'canvas-confetti';

const disciplines = [
  { name: 'Lead Software Architect', icon: '💻', rate: 120, guild: 'Technology' },
  { name: 'Master Carpenter & Joiner', icon: '🪚', rate: 75, guild: 'Craftsmen' },
  { name: 'Private Executive Chef', icon: '🍽️', rate: 90, guild: 'Culinary' },
  { name: 'Senior Creative Director', icon: '🎨', rate: 85, guild: 'Creative' },
  { name: 'Certified Physiotherapist', icon: '🏥', rate: 80, guild: 'Health' },
  { name: 'Chartered Financial Advisor', icon: '📊', rate: 100, guild: 'Advisory' },
];

export function LuxuryEstimator() {
  const [selectedIdx, setSelectedIdx] = useState(0);
  const [hours, setHours] = useState(25);

  const current = disciplines[selectedIdx];
  const monthlyEarnings = Math.round(current.rate * hours * 4.33);
  const annualEarnings = monthlyEarnings * 12;

  const triggerGoldConfetti = () => {
    try {
      confetti({
        particleCount: 70,
        spread: 60,
        origin: { y: 0.7 },
        colors: ['#D4AF37', '#F3E5AB', '#10B981', '#FFFFFF'],
      });
    } catch {
      // ignore
    }
  };

  return (
    <div className="relative rounded-3xl border border-[var(--border-hi)] bg-[var(--card)] p-8 sm:p-12 shadow-2xl backdrop-blur-2xl transition-all duration-300">
      {/* Decorative luxury watermark */}
      <div className="absolute top-6 right-8 opacity-5 text-7xl font-serif select-none pointer-events-none text-[var(--gold)]">
        GG
      </div>

      <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8 border-b border-[var(--border)] mb-8">
        <div>
          <div className="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-[var(--gold)] font-sans mb-2">
            <Gem className="w-3.5 h-3.5" />
            <span>SOVEREIGN PRACTICE ESTIMATOR</span>
          </div>
          <h3 className="text-3xl sm:text-4xl font-serif font-bold text-[var(--tx)] tracking-tight">
            Estimate Your Professional Earning Power
          </h3>
          <p className="text-sm text-[var(--tx-2)] mt-2 max-w-xl">
            Independent Ghanaian practitioners and master artisans earn market-leading rates with verified client escrow protection.
          </p>
        </div>

        <div className="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[var(--gold-dim)] text-[var(--gold)] border border-[var(--border-hi)] text-xs font-bold w-fit">
          <TrendingUp className="w-4 h-4" />
          <span>Guaranteed Escrow Payouts</span>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        {/* Discipline Selectors & Hours Slider */}
        <div className="lg:col-span-7 space-y-6">
          <div>
            <label className="block text-xs font-bold uppercase tracking-wider text-[var(--tx-3)] mb-3">
              1. Select Your Guild Discipline:
            </label>
            <div className="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
              {disciplines.map((d, idx) => (
                <button
                  key={idx}
                  onClick={() => setSelectedIdx(idx)}
                  className={`p-3.5 rounded-2xl border text-left transition-all ${
                    selectedIdx === idx
                      ? 'bg-[var(--gold-dim)] border-[var(--gold)] text-[var(--tx)] shadow-md shadow-gold/10'
                      : 'bg-[var(--surface-2)] border-[var(--border)] text-[var(--tx-2)] hover:border-[var(--border-hi)] hover:text-[var(--tx)]'
                  }`}
                >
                  <div className="text-xl mb-1">{d.icon}</div>
                  <div className="text-xs font-bold leading-tight line-clamp-1">{d.name}</div>
                  <div className="text-[10px] text-[var(--gold)] mt-1 font-mono">₵{d.rate}/hr benchmark</div>
                </button>
              ))}
            </div>
          </div>

          <div>
            <div className="flex items-center justify-between text-xs font-bold text-[var(--tx-2)] mb-2">
              <span>2. Dedicated Hours Per Week:</span>
              <span className="text-sm font-bold text-[var(--gold)] font-mono">{hours} Hours / Week</span>
            </div>
            <input
              type="range"
              min="5"
              max="50"
              step="5"
              value={hours}
              onChange={(e) => setHours(Number(e.target.value))}
              className="w-full h-2 bg-[var(--border)] rounded-lg appearance-none cursor-pointer accent-[var(--gold)]"
            />
            <div className="flex justify-between text-[10px] text-[var(--tx-3)] mt-1.5 font-mono">
              <span>5 hrs (Select Retainers)</span>
              <span>25 hrs (Standard Practice)</span>
              <span>45+ hrs (Full Commitment)</span>
            </div>
          </div>
        </div>

        {/* Calculated Luxury Output Box */}
        <div className="lg:col-span-5 p-7 rounded-3xl bg-[var(--surface-2)] border border-[var(--border-hi)] text-center flex flex-col justify-between shadow-xl">
          <div>
            <div className="text-xs font-bold uppercase tracking-widest text-[var(--tx-3)] mb-1">
              Estimated Monthly Revenue
            </div>
            <div className="text-4xl sm:text-5xl font-serif font-extrabold text-[var(--tx)] my-2">
              <span className="text-gold-gradient">₵{monthlyEarnings.toLocaleString('en-GH')}</span>
            </div>
            <div className="text-xs text-[var(--tx-2)] mb-6">
              Approx. <strong className="text-[var(--tx)]">₵{annualEarnings.toLocaleString('en-GH')} GHS</strong> annually
            </div>

            <div className="p-3.5 rounded-2xl bg-[var(--surface)] border border-[var(--border)] text-left text-xs space-y-2 mb-6">
              <div className="flex items-center justify-between text-[var(--tx-2)]">
                <span>Direct rate per hour:</span>
                <span className="font-bold text-[var(--tx)] font-mono">₵{current.rate}.00 / hr</span>
              </div>
              <div className="flex items-center justify-between text-[var(--tx-2)]">
                <span>Escrow security:</span>
                <span className="font-bold text-[var(--emerald)] flex items-center gap-1">
                  <ShieldCheck className="w-3.5 h-3.5" /> 100% Guaranteed
                </span>
              </div>
              <div className="flex items-center justify-between text-[var(--tx-2)]">
                <span>Disbursement speed:</span>
                <span className="font-bold text-[var(--tx)]">&lt; 60s to MoMo / Bank</span>
              </div>
            </div>
          </div>

          <a
            href="/auth/register.php?role=provider"
            onClick={triggerGoldConfetti}
            className="flex items-center justify-center gap-2 w-full py-4 px-6 rounded-2xl font-bold text-xs uppercase tracking-wider text-[#090A0F] bg-gradient-to-r from-[var(--gold-light)] via-[var(--gold)] to-[#B8860B] hover:brightness-110 shadow-lg shadow-gold/20 transition-all font-sans"
          >
            <Sparkles className="w-4 h-4" />
            <span>Join the Sovereign Guild</span>
            <ArrowRight className="w-4 h-4" />
          </a>
        </div>
      </div>
    </div>
  );
}
