'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useAuth } from '@/lib/context/AuthContext';
import {
  Bell,
  Sun,
  Moon,
  Menu,
  X,
  ShieldCheck,
  CheckCircle2,
  Wallet,
  Sparkles,
  Search,
  ArrowUpRight,
  Clock,
} from 'lucide-react';

interface ProviderHeaderProps {
  onOpenMobileMenu?: () => void;
  onOpenWithdrawModal?: () => void;
}

export function ProviderHeader({
  onOpenMobileMenu,
  onOpenWithdrawModal,
}: ProviderHeaderProps) {
  const { user } = useAuth();
  const [isLight, setIsLight] = useState(false);
  const [showNotifs, setShowNotifs] = useState(false);

  // Time-based Ghanaian greeting
  const [greeting, setGreeting] = useState('Good day');
  useEffect(() => {
    const hour = new Date().getHours();
    if (hour < 12) setGreeting('Good morning');
    else if (hour < 17) setGreeting('Good afternoon');
    else setGreeting('Good evening');

    // Sync theme
    const saved = localStorage.getItem('gg_theme');
    if (saved === 'light' || document.documentElement.classList.contains('lm')) {
      setIsLight(true);
    }
  }, []);

  const toggleTheme = () => {
    const next = !isLight;
    setIsLight(next);
    if (next) {
      document.documentElement.classList.add('lm');
      document.body.classList.add('lm');
      document.documentElement.setAttribute('data-theme', 'light');
      document.documentElement.classList.remove('dark');
      document.body.classList.remove('dark');
      localStorage.setItem('gg_theme', 'light');
    } else {
      document.documentElement.classList.remove('lm');
      document.body.classList.remove('lm');
      document.documentElement.setAttribute('data-theme', 'dark');
      document.documentElement.classList.add('dark');
      document.body.classList.add('dark');
      localStorage.setItem('gg_theme', 'dark');
    }
  };

  const notifications = [
    {
      id: 1,
      title: 'Milestone Escrow Released',
      desc: '₵1,500 deposited directly into your MoMo wallet from Ridge Commercial brief.',
      time: '12m ago',
      read: false,
    },
    {
      id: 2,
      title: 'New Bio-Digester Gig in Spintex',
      desc: 'Matches your trade: ₵3,800 Cedi budget with escrow pre-funded.',
      time: '1h ago',
      read: false,
    },
    {
      id: 3,
      title: 'Ghana Card Biometrics Validated',
      desc: 'Your national identity pin GHA-722019482-1 has verified status.',
      time: 'Yesterday',
      read: true,
    },
  ];

  return (
    <header className="h-16 px-4 md:px-8 border-b border-[var(--bd2)] bg-[var(--surface)]/90 backdrop-blur-md sticky top-0 z-30 flex items-center justify-between transition-colors">
      {/* Left: Mobile hamburger & Greeting */}
      <div className="flex items-center gap-3">
        <button
          type="button"
          onClick={onOpenMobileMenu}
          className="md:hidden p-2 rounded-[12px] border border-[var(--bd2)] text-[var(--tx)] hover:bg-[var(--surface-elevated)] transition-colors cursor-pointer"
          aria-label="Open navigation menu"
        >
          <Menu className="w-5 h-5" />
        </button>

        <div>
          <div className="flex items-center gap-2">
            <h1 className="text-sm sm:text-base font-extrabold text-[var(--tx)] leading-tight">
              {greeting}, {user?.first_name || 'Kwame'}!
            </h1>
            <span className="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#10B981]/10 text-[#10B981] border border-[#10B981]/20">
              <span className="w-1.5 h-1.5 rounded-full bg-[#10B981] animate-pulse" />
              <span>Available for Work</span>
            </span>
          </div>
          <p className="text-[11px] text-[var(--tx-3)] hidden sm:block">
            {user?.trade ? `${user.trade} • ` : 'Verified Master Artisan • '}
            Ghana Card Protected Escrow
          </p>
        </div>
      </div>

      {/* Right: Quick Cash-Out CTA, Notifications, Theme & Profile */}
      <div className="flex items-center gap-2.5">
        {/* Instant Cash-Out Quick Button */}
        <button
          type="button"
          onClick={onOpenWithdrawModal}
          className="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-[14px] bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-slate-950 font-black text-xs shadow-md shadow-cyan-500/20 hover:scale-[1.02] active:scale-[0.98] transition-all cursor-pointer"
        >
          <Wallet className="w-3.5 h-3.5" />
          <span>Withdraw to MoMo</span>
        </button>

        {/* Notifications Dropdown */}
        <div className="relative">
          <button
            type="button"
            onClick={() => setShowNotifs(!showNotifs)}
            className="w-9 h-9 rounded-[12px] border border-[var(--bd2)] bg-[var(--surface-elevated)] hover:border-[var(--bd)] text-[var(--tx)] flex items-center justify-center relative transition-colors cursor-pointer"
            aria-label="View notifications"
          >
            <Bell className="w-4 h-4" />
            <span className="w-2 h-2 rounded-full bg-[var(--coral)] absolute top-2 right-2 ring-2 ring-[var(--surface)]" />
          </button>

          {showNotifs && (
            <div className="absolute right-0 mt-2 w-80 rounded-[20px] bg-[var(--surface)] border border-[var(--bd2)] shadow-2xl p-3 z-50 animate-in fade-in zoom-in-95 duration-150">
              <div className="flex items-center justify-between pb-2 mb-2 border-b border-[var(--bd2)]">
                <span className="text-xs font-black text-[var(--tx)]">Notifications</span>
                <span className="text-[10px] text-[var(--cyan)] font-bold cursor-pointer hover:underline">
                  Mark all read
                </span>
              </div>
              <div className="space-y-1.5 max-h-72 overflow-y-auto">
                {notifications.map((n) => (
                  <div
                    key={n.id}
                    className={`p-2.5 rounded-[14px] transition-colors ${
                      n.read
                        ? 'bg-[var(--surface)] text-[var(--tx-3)]'
                        : 'bg-[var(--surface-elevated)] text-[var(--tx)] border border-[var(--bd2)]/60'
                    }`}
                  >
                    <div className="text-xs font-bold flex items-center justify-between">
                      <span>{n.title}</span>
                      <span className="text-[10px] font-normal text-[var(--tx-3)]">{n.time}</span>
                    </div>
                    <p className="text-[11px] text-[var(--tx-2)] mt-0.5 leading-snug">
                      {n.desc}
                    </p>
                  </div>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Dark/Light Theme Toggle */}
        <button
          type="button"
          onClick={toggleTheme}
          className="w-9 h-9 rounded-[12px] border border-[var(--bd2)] bg-[var(--surface-elevated)] hover:border-[var(--bd)] text-[var(--tx)] flex items-center justify-center transition-colors cursor-pointer"
          aria-label="Toggle theme mode"
        >
          {isLight ? <Moon className="w-4 h-4 text-amber-500" /> : <Sun className="w-4 h-4 text-amber-400" />}
        </button>

        {/* Artisan Profile Pill */}
        <div className="flex items-center gap-2 pl-2 border-l border-[var(--bd2)]">
          <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-[var(--cyan)] to-[#3B82F6] text-slate-950 font-black text-xs flex items-center justify-center shrink-0">
            {user?.first_name ? user.first_name[0] : 'K'}
          </div>
          <div className="hidden lg:block text-left">
            <div className="text-xs font-bold text-[var(--tx)] leading-tight">
              {user?.first_name || 'Kwame'}
            </div>
            <div className="text-[10px] font-semibold text-[var(--cyan)] flex items-center gap-0.5">
              <ShieldCheck className="w-3 h-3" />
              <span>Verified Pro</span>
            </div>
          </div>
        </div>
      </div>
    </header>
  );
}
