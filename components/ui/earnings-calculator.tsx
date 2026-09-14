'use client';

import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Calculator, ArrowRight, Sparkles, TrendingUp } from 'lucide-react';
import confetti from 'canvas-confetti';

const professions = [
  { name: 'Full-Stack Developer', icon: '💻', hourly: 95, category: 'Tech' },
  { name: 'Graphic / UI Designer', icon: '🎨', hourly: 65, category: 'Creative' },
  { name: 'Electrician / Plumber', icon: '🔧', hourly: 55, category: 'Trades' },
  { name: 'Private Chef / Caterer', icon: '🍽️', hourly: 70, category: 'Hospitality' },
  { name: 'Home Care Nurse', icon: '🏥', hourly: 60, category: 'Health' },
  { name: 'Math & Science Tutor', icon: '📚', hourly: 45, category: 'Education' },
  { name: 'Photographer / Videographer', icon: '📷', hourly: 80, category: 'Creative' },
  { name: 'Digital Marketer', icon: '📈', hourly: 60, category: 'Tech' },
];

export function EarningsCalculator() {
  const [selectedProfIndex, setSelectedProfIndex] = useState(0);
  const [hoursPerWeek, setHoursPerWeek] = useState(25);

  const currentProf = professions[selectedProfIndex];
  const monthlyEst = Math.round(currentProf.hourly * hoursPerWeek * 4.33);
  const yearlyEst = monthlyEst * 12;

  const handleConfetti = () => {
    try {
      confetti({
        particleCount: 60,
        spread: 60,
        origin: { y: 0.7 },
        colors: ['#06B6D4', '#10B981', '#F59E0B'],
      });
    } catch {
      // ignore
    }
  };

  return (
    <div className="relative rounded-3xl border border-white/10 bg-[#0C0E14]/90 p-8 backdrop-blur-2xl shadow-2xl max-w-4xl mx-auto overflow-hidden">
      {/* Ambient background glow */}
      <div className="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-[#06B6D4]/10 blur-[100px] pointer-events-none" />
      <div className="absolute -left-20 -top-20 w-80 h-80 rounded-full bg-[#10B981]/10 blur-[100px] pointer-events-none" />

      <div className="relative z-10">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-white/10">
          <div>
            <div className="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-[#06B6D4] font-heading mb-1.5">
              <Calculator className="w-4 h-4" />
              <span>FREELANCE INCOME ESTIMATOR</span>
            </div>
            <h3 className="text-2xl font-bold text-white font-heading tracking-tight">
              See How Much You Can Earn in Ghana
            </h3>
          </div>
          <div className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#10B981]/15 text-[#10B981] border border-[#10B981]/30 text-xs font-bold w-fit">
            <TrendingUp className="w-4 h-4" />
            <span>Avg 4.8x higher than local salary</span>
          </div>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
          {/* Left Controls */}
          <div className="lg:col-span-7 space-y-6">
            <div>
              <label className="block text-xs font-semibold text-white/70 mb-3">
                1. Select Your Profession or Skill:
              </label>
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
                {professions.map((p, idx) => (
                  <button
                    key={idx}
                    onClick={() => setSelectedProfIndex(idx)}
                    className={`flex flex-col items-center justify-center p-3 rounded-2xl border text-center transition-all ${
                      selectedProfIndex === idx
                        ? 'bg-[#06B6D4]/15 border-[#06B6D4] text-white shadow-lg shadow-[#06B6D4]/15'
                        : 'bg-white/[0.03] border-white/5 text-white/60 hover:bg-white/[0.06] hover:text-white'
                    }`}
                  >
                    <span className="text-xl mb-1">{p.icon}</span>
                    <span className="text-xs font-bold leading-tight line-clamp-1">{p.name}</span>
                    <span className="text-[10px] text-white/40 mt-1">₵{p.hourly}/hr avg</span>
                  </button>
                ))}
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between text-xs font-semibold text-white/70 mb-2">
                <span>2. Hours You Want to Work Per Week:</span>
                <span className="text-sm font-bold text-[#06B6D4]">{hoursPerWeek} Hours / Week</span>
              </div>
              <input
                type="range"
                min="5"
                max="50"
                step="5"
                value={hoursPerWeek}
                onChange={(e) => setHoursPerWeek(Number(e.target.value))}
                className="w-full h-2 bg-white/10 rounded-lg appearance-none cursor-pointer accent-[#06B6D4]"
              />
              <div className="flex justify-between text-[10px] text-white/40 mt-1.5 font-mono">
                <span>5 hrs (Side Hustle)</span>
                <span>25 hrs (Part-Time)</span>
                <span>45+ hrs (Full-Time)</span>
              </div>
            </div>
          </div>

          {/* Right Calculated Result Box */}
          <div className="lg:col-span-5 p-6 rounded-2xl bg-gradient-to-br from-white/[0.06] to-white/[0.02] border border-white/15 text-center flex flex-col justify-between">
            <div>
              <div className="text-xs font-semibold text-white/60 mb-1">Estimated Monthly Earnings</div>
              <div className="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-[#06B6D4] via-[#10B981] to-white font-heading tracking-tight mb-2">
                ₵{monthlyEst.toLocaleString('en-GH')}
              </div>
              <div className="text-xs text-white/50 mb-6">
                That&apos;s approximately <strong className="text-white">₵{yearlyEst.toLocaleString('en-GH')} GHS</strong> per year
              </div>

              <div className="p-3 rounded-xl bg-white/[0.04] border border-white/5 text-left text-xs space-y-2 mb-6">
                <div className="flex items-center justify-between text-white/70">
                  <span>Average hourly rate:</span>
                  <span className="font-bold text-white">₵{currentProf.hourly}.00 / hr</span>
                </div>
                <div className="flex items-center justify-between text-white/70">
                  <span>Escrow protection:</span>
                  <span className="font-bold text-[#10B981]">100% Guaranteed</span>
                </div>
                <div className="flex items-center justify-between text-white/70">
                  <span>Payout options:</span>
                  <span className="font-bold text-white">Instant MoMo / Bank</span>
                </div>
              </div>
            </div>

            <a
              href="/auth/register.php?role=provider"
              onClick={handleConfetti}
              className="flex items-center justify-center gap-2 w-full py-3.5 px-6 rounded-xl font-bold text-[#07090E] bg-gradient-to-r from-[#06B6D4] to-[#10B981] hover:brightness-110 shadow-lg shadow-cyan-500/25 transition-all text-sm"
            >
              <Sparkles className="w-4 h-4" />
              <span>Start Earning on GigGhana</span>
              <ArrowRight className="w-4 h-4" />
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}
