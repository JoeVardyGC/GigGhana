'use client';

import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { ShieldCheck, CheckCircle2, ArrowRight, Smartphone, Lock, Sparkles, Star } from 'lucide-react';

export function TransactionVisualizerCard() {
  const [activeStep, setActiveStep] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setActiveStep((prev) => (prev + 1) % 3);
    }, 3800);
    return () => clearInterval(timer);
  }, []);

  return (
    <div className="relative w-full max-w-md mx-auto">
      {/* Outer ambient glow */}
      <div className="absolute -inset-1 rounded-3xl bg-gradient-to-r from-[#06B6D4] via-[#10B981] to-[#8B5CF6] opacity-30 blur-xl transition-all duration-1000 group-hover:opacity-60" />

      {/* Main card */}
      <div className="relative rounded-3xl border border-white/10 bg-[#0C0E14]/90 p-6 backdrop-blur-2xl shadow-2xl shadow-black/80">
        {/* Header */}
        <div className="flex items-center justify-between pb-4 border-b border-white/5">
          <div className="flex items-center gap-3">
            <div className="relative flex h-10 w-10 items-center justify-center rounded-2xl bg-[#06B6D4]/10 border border-[#06B6D4]/30 text-[#06B6D4]">
              <ShieldCheck className="w-5 h-5" />
              <span className="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#10B981] opacity-75" />
                <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#10B981]" />
              </span>
            </div>
            <div>
              <div className="text-xs font-bold text-white tracking-wide font-heading">
                GIGGHANA ESCROW VAULT
              </div>
              <div className="text-[10px] text-white/50">Contract #GG-2026-984</div>
            </div>
          </div>
          <div className="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#10B981]/15 text-[#10B981] border border-[#10B981]/25 flex items-center gap-1">
            <Lock className="w-3 h-3" />
            <span>PROTECTED</span>
          </div>
        </div>

        {/* Milestone Detail */}
        <div className="py-4 space-y-3">
          <div className="flex items-center justify-between">
            <span className="text-xs text-white/60">Milestone: Custom Web App</span>
            <span className="text-sm font-bold text-white font-heading tracking-tight">
              ₵2,500.00 GHS
            </span>
          </div>

          {/* 3-Step Process Indicator */}
          <div className="grid grid-cols-3 gap-2">
            {[
              { label: '1. Deposit', icon: Lock, status: 'Secured in Escrow' },
              { label: '2. Delivery', icon: Sparkles, status: 'Code Approved' },
              { label: '3. Release', icon: Smartphone, status: 'Instant MoMo' },
            ].map((step, idx) => {
              const isCurrent = activeStep === idx;
              const isPast = activeStep > idx;
              return (
                <div
                  key={idx}
                  onClick={() => setActiveStep(idx)}
                  className={`p-2.5 rounded-2xl border transition-all cursor-pointer ${
                    isCurrent
                      ? 'bg-[#06B6D4]/15 border-[#06B6D4]/50 shadow-md shadow-[#06B6D4]/10'
                      : isPast
                      ? 'bg-[#10B981]/10 border-[#10B981]/30 text-[#10B981]'
                      : 'bg-white/[0.02] border-white/5 text-white/40'
                  }`}
                >
                  <div className="flex items-center justify-between mb-1">
                    <span className="text-[10px] font-bold">{step.label}</span>
                    {isPast ? (
                      <CheckCircle2 className="w-3 h-3 text-[#10B981]" />
                    ) : (
                      <step.icon className={`w-3 h-3 ${isCurrent ? 'text-[#06B6D4]' : 'opacity-40'}`} />
                    )}
                  </div>
                  <div className="text-[9px] truncate opacity-75">{step.status}</div>
                </div>
              );
            })}
          </div>

          {/* Dynamic Active Step Highlight Box */}
          <AnimatePresence mode="wait">
            <motion.div
              key={activeStep}
              initial={{ opacity: 0, y: 8 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -8 }}
              transition={{ duration: 0.2 }}
              className="p-3.5 rounded-2xl bg-gradient-to-r from-white/[0.04] to-white/[0.02] border border-white/10 flex items-center justify-between"
            >
              {activeStep === 0 && (
                <div className="flex items-center gap-3">
                  <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-amber-400">
                    <Lock className="w-4 h-4" />
                  </div>
                  <div>
                    <div className="text-xs font-bold text-white">Client Funded Milestone</div>
                    <div className="text-[10px] text-white/50">₵2,500.00 locked safely in escrow</div>
                  </div>
                </div>
              )}
              {activeStep === 1 && (
                <div className="flex items-center gap-3">
                  <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#06B6D4]/15 text-[#06B6D4]">
                    <Sparkles className="w-4 h-4" />
                  </div>
                  <div>
                    <div className="text-xs font-bold text-white">Milestone Verified &amp; Signed Off</div>
                    <div className="text-[10px] text-white/50">Deliverables approved by Client</div>
                  </div>
                </div>
              )}
              {activeStep === 2 && (
                <div className="flex items-center gap-3">
                  <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#10B981]/15 text-[#10B981]">
                    <Smartphone className="w-4 h-4" />
                  </div>
                  <div>
                    <div className="text-xs font-bold text-white">Payout Released in 42s</div>
                    <div className="text-[10px] text-[#10B981]">Credited to MTN MoMo (024****892)</div>
                  </div>
                </div>
              )}
              <span className="text-[11px] font-bold text-[#06B6D4]">100% Guaranteed</span>
            </motion.div>
          </AnimatePresence>
        </div>

        {/* Freelancer snippet in card */}
        <div className="pt-3 border-t border-white/5 flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <div className="h-8 w-8 rounded-full bg-gradient-to-tr from-[#06B6D4] to-[#10B981] flex items-center justify-center font-bold text-xs text-[#07090E]">
              KA
            </div>
            <div>
              <div className="text-xs font-bold text-white flex items-center gap-1">
                <span>Kwame Asante</span>
                <span className="text-[10px] text-[#06B6D4]">✓</span>
              </div>
              <div className="text-[10px] text-white/50">Full-Stack Web Architect</div>
            </div>
          </div>
          <div className="flex items-center gap-1 text-[11px] font-bold text-amber-400">
            <Star className="w-3.5 h-3.5 fill-amber-400" />
            <span>4.99 (58 jobs)</span>
          </div>
        </div>
      </div>
    </div>
  );
}
