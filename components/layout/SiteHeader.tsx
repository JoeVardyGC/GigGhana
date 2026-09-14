'use client';

import React, { useState, useEffect, useRef } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/lib/context/AuthContext';
import { ShieldCheck, User, LogOut, ArrowRight, Menu, X, Sparkles, Briefcase, ChevronDown, Check } from 'lucide-react';

interface SiteHeaderProps {
  activeTab?: 'jobs' | 'providers' | 'home';
}

export function SiteHeader({ activeTab }: SiteHeaderProps) {
  const pathname = usePathname();
  const { user, isAuthenticated, logout } = useAuth();
  const [isLight, setIsLight] = useState(false);
  const [isMobOpen, setIsMobOpen] = useState(false);
  const [scrolledNav, setScrolledNav] = useState(false);
  const [isUserMenuOpen, setIsUserMenuOpen] = useState(false);
  const userMenuRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClickOutside = (e: MouseEvent) => {
      if (userMenuRef.current && !userMenuRef.current.contains(e.target as Node)) {
        setIsUserMenuOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // Initialize theme from storage
  useEffect(() => {
    const savedTheme = localStorage.getItem('gg_theme');
    if (savedTheme === 'light' || (!savedTheme && document.documentElement.classList.contains('lm'))) {
      setIsLight(true);
      document.documentElement.classList.add('lm');
      document.body.classList.add('lm');
      document.documentElement.setAttribute('data-theme', 'light');
      document.documentElement.classList.remove('dark');
      document.body.classList.remove('dark');
    } else {
      setIsLight(false);
      document.documentElement.classList.add('dark');
      document.body.classList.add('dark');
      document.documentElement.setAttribute('data-theme', 'dark');
      document.documentElement.classList.remove('lm');
      document.body.classList.remove('lm');
    }

    const handleScroll = () => {
      setScrolledNav(window.scrollY > 30);
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
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

  return (
    <>
      <header className={`navbar ${scrolledNav ? 'on' : ''}`} style={{ transition: 'all 0.3s ease' }}>
        {/* Brand Logo */}
        <Link href="/" className="logo">
          <div className="logo-mark">G</div>
          <span className="logo-text">
            Gig<span>Ghana</span>
          </span>
        </Link>

        {/* Primary Desktop Nav Links */}
        <nav className="nav-links hidden md:flex items-center gap-6">
          <Link
            href="/jobs"
            className={`transition-colors font-medium text-sm ${
              pathname === '/jobs' || activeTab === 'jobs'
                ? 'text-[var(--cyan)] font-bold'
                : 'text-[var(--tx-2)] hover:text-[var(--tx)]'
            }`}
          >
            Browse Jobs
          </Link>
          <Link
            href="/search/providers"
            className={`transition-colors font-medium text-sm ${
              pathname === '/search/providers' || activeTab === 'providers'
                ? 'text-[var(--cyan)] font-bold'
                : 'text-[var(--tx-2)] hover:text-[var(--tx)]'
            }`}
          >
            Find Talent
          </Link>
          <Link
            href="/#how"
            className="text-[var(--tx-2)] hover:text-[var(--tx)] transition-colors font-medium text-sm"
          >
            How Escrow Works
          </Link>
          <Link
            href="/#categories"
            className="text-[var(--tx-2)] hover:text-[var(--tx)] transition-colors font-medium text-sm"
          >
            Categories
          </Link>
        </nav>

        {/* Right Actions & Auth */}
        <div className="nav-acts flex items-center gap-3">
          {/* Theme Toggle Button */}
          <button
            onClick={toggleTheme}
            className="btn-theme p-2 rounded-xl border border-[var(--bd)] bg-[var(--surface-2)] text-[var(--tx)] hover:border-[var(--cyan)] transition-colors flex items-center justify-center text-sm"
            title="Toggle theme"
            aria-label="Toggle theme"
          >
            {isLight ? '☀️' : '🌙'}
          </button>

          {/* Authenticated User Profile Pill & Dropdown */}
          {isAuthenticated && user ? (
            <div className="relative" ref={userMenuRef}>
              <button
                type="button"
                onClick={() => setIsUserMenuOpen(!isUserMenuOpen)}
                className="flex items-center gap-2.5 pl-1.5 pr-3 py-1.5 rounded-full border border-[var(--bd2)] hover:border-[var(--cyan-border)] bg-[var(--surface)] hover:bg-[var(--surface-elevated)] transition-all shadow-xs backdrop-blur-md cursor-pointer select-none group"
                aria-expanded={isUserMenuOpen}
                aria-label="User account menu"
              >
                <div className="w-7 h-7 rounded-full bg-gradient-to-tr from-[var(--cyan)] to-[#00A89D] text-white font-black flex items-center justify-center text-xs shadow-xs relative ring-2 ring-[var(--cyan)]/25 shrink-0">
                  {user.first_name?.[0]?.toUpperCase() || 'U'}
                  <span className="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-[var(--surface)]" />
                </div>
                <span className="text-xs font-bold text-[var(--tx)] group-hover:text-[var(--cyan)] transition-colors hidden sm:inline">
                  {user.first_name}
                </span>
                <span
                  className={`text-[9.5px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider ${
                    user.role === 'client'
                      ? 'bg-amber-500/15 text-amber-500 border border-amber-500/30'
                      : 'bg-cyan-500/15 text-[var(--cyan)] border border-cyan-500/30'
                  }`}
                >
                  {user.role === 'client' ? 'Client' : 'Artisan'}
                </span>
                <ChevronDown
                  className={`w-3.5 h-3.5 text-[var(--tx-3)] group-hover:text-[var(--tx)] transition-transform duration-200 ${
                    isUserMenuOpen ? 'rotate-180 text-[var(--cyan)]' : ''
                  }`}
                />
              </button>

              {/* Profile Dropdown Card */}
              {isUserMenuOpen && (
                <div className="absolute right-0 mt-2 w-72 rounded-[24px] bg-[var(--surface)]/95 backdrop-blur-xl border border-[var(--bd2)] shadow-2xl p-4 z-50 animate-in fade-in slide-in-from-top-2 duration-200 text-left">
                  <div className="flex items-center gap-3 pb-3.5 border-b border-[var(--bd2)]">
                    <div className="w-11 h-11 rounded-full bg-gradient-to-tr from-[var(--cyan)] to-[#00A89D] text-white font-black flex items-center justify-center text-sm shadow-sm ring-2 ring-[var(--cyan)]/30 shrink-0">
                      {user.first_name?.[0]?.toUpperCase() || 'U'}
                    </div>
                    <div className="min-w-0 flex-1">
                      <div className="text-xs font-bold text-[var(--tx)] truncate flex items-center gap-1.5">
                        <span>{user.first_name} {user.last_name || ''}</span>
                        <ShieldCheck className="w-3.5 h-3.5 text-[var(--cyan)] shrink-0" />
                      </div>
                      <div className="text-[10.5px] text-[var(--tx-3)] truncate font-mono">
                        {user.phone || user.email || 'Registered Member'}
                      </div>
                      <div className="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-[9.5px] font-semibold border border-emerald-500/20">
                        <Check className="w-2.5 h-2.5 stroke-[3]" />
                        <span>Ghana Card Verified</span>
                      </div>
                    </div>
                  </div>

                  <div className="py-3 space-y-2 text-xs">
                    <div className="flex items-center justify-between text-[11px]">
                      <span className="text-[var(--tx-3)] font-medium">Workspace Role:</span>
                      <span className="font-bold text-[var(--tx)] capitalize">
                        {user.role === 'client' ? 'Project Client' : 'Master Artisan'}
                      </span>
                    </div>
                    <div className="flex items-center justify-between text-[11px]">
                      <span className="text-[var(--tx-3)] font-medium">Escrow Vault:</span>
                      <span className="font-bold text-[var(--cyan)]">₵ Protected</span>
                    </div>
                    {user.location && (
                      <div className="flex items-center justify-between text-[11px]">
                        <span className="text-[var(--tx-3)] font-medium">Location:</span>
                        <span className="font-semibold text-[var(--tx-2)] truncate max-w-[130px]">
                          {user.location}
                        </span>
                      </div>
                    )}
                  </div>

                  <div className="pt-2 border-t border-[var(--bd2)] space-y-2">
                    <Link
                      href={user.role === 'provider' ? '/provider/dashboard' : '/#how-it-works'}
                      onClick={() => setIsUserMenuOpen(false)}
                      className="w-full h-10 rounded-[14px] bg-[var(--cyan)] hover:opacity-90 text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-sm transition-all"
                    >
                      <Briefcase className="w-3.5 h-3.5" />
                      <span>{user.role === 'provider' ? 'Go to Artisan Dashboard' : 'Explore Escrow Vault'}</span>
                    </Link>

                    <button
                      type="button"
                      onClick={() => {
                        setIsUserMenuOpen(false);
                        logout();
                      }}
                      className="w-full h-10 rounded-[14px] bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 font-bold text-xs flex items-center justify-center gap-1.5 border border-rose-500/20 transition-all cursor-pointer"
                    >
                      <LogOut className="w-3.5 h-3.5" />
                      <span>Sign Out of GigGhana</span>
                    </button>
                  </div>
                </div>
              )}
            </div>
          ) : (
            <div className="hidden sm:flex items-center gap-2">
              <Link href="/auth/login" className="btn btn-ghost text-xs px-3.5 py-2">
                Sign In
              </Link>
              <Link
                href="/auth/register"
                className="btn btn-gold text-xs px-4 py-2 font-bold shadow-md shadow-amber-500/10 hover:shadow-amber-500/20"
              >
                Get Started
              </Link>
            </div>
          )}

          {/* Mobile Hamburger Toggle */}
          <button
            onClick={() => setIsMobOpen(!isMobOpen)}
            className="md:hidden p-2 rounded-xl border border-[var(--bd)] text-[var(--tx)] hover:border-[var(--cyan)]"
            aria-label="Toggle mobile navigation"
          >
            {isMobOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>
        </div>
      </header>

      {/* Mobile Drawer Menu */}
      <div
        className={`mobile-nav ${isMobOpen ? 'open' : ''} md:hidden fixed inset-x-0 top-[70px] bg-[var(--surface)] border-b border-[var(--bd)] p-6 shadow-2xl z-50 flex flex-col gap-4 transition-all duration-300 ${
          isMobOpen ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-4 pointer-events-none'
        }`}
      >
        <Link
          href="/#how"
          onClick={() => setIsMobOpen(false)}
          className="text-sm font-semibold text-[var(--tx)] py-2 border-b border-[var(--bd)]"
        >
          How It Works
        </Link>
        <Link
          href="/#categories"
          onClick={() => setIsMobOpen(false)}
          className="text-sm font-semibold text-[var(--tx)] py-2 border-b border-[var(--bd)]"
        >
          Categories
        </Link>
        <Link
          href="/#trending"
          onClick={() => setIsMobOpen(false)}
          className="text-sm font-semibold text-[var(--tx)] py-2 border-b border-[var(--bd)]"
        >
          Trending Artisans
        </Link>

        {isAuthenticated && user ? (
          <div className="pt-2 flex flex-col gap-2">
            <div className="p-3 rounded-[16px] bg-[var(--surface)] border border-[var(--bd2)] flex items-center gap-3">
              <div className="w-10 h-10 rounded-full bg-gradient-to-tr from-[var(--cyan)] to-[#00A89D] text-white font-black flex items-center justify-center text-sm shadow-xs shrink-0">
                {user.first_name?.[0]?.toUpperCase() || 'U'}
              </div>
              <div className="min-w-0 flex-1">
                <div className="text-xs font-bold text-[var(--tx)] truncate flex items-center gap-1.5">
                  <span>{user.first_name} {user.last_name || ''}</span>
                  <span className={`text-[9px] font-black px-1.5 py-0.2 rounded-full uppercase ${
                    user.role === 'client' ? 'bg-amber-500/15 text-amber-500' : 'bg-cyan-500/15 text-[var(--cyan)]'
                  }`}>
                    {user.role === 'client' ? 'Client' : 'Artisan'}
                  </span>
                </div>
                <div className="text-[10px] text-[var(--tx-3)] font-mono truncate">
                  {user.phone || user.email}
                </div>
              </div>
            </div>
            <button
              onClick={() => {
                logout();
                setIsMobOpen(false);
              }}
              className="w-full py-2.5 rounded-[14px] border border-rose-500/20 bg-rose-500/10 text-rose-500 font-bold text-xs flex items-center justify-center gap-1.5 hover:bg-rose-500/20 transition-colors"
            >
              <LogOut className="w-3.5 h-3.5" />
              <span>Sign Out</span>
            </button>
          </div>
        ) : (
          <div className="pt-2 flex flex-col gap-2.5">
            <Link
              href="/auth/login"
              onClick={() => setIsMobOpen(false)}
              className="w-full py-2.5 rounded-xl border border-[var(--bd)] text-[var(--tx)] font-semibold text-sm text-center"
            >
              Sign In
            </Link>
            <Link
              href="/auth/register"
              onClick={() => setIsMobOpen(false)}
              className="w-full py-2.5 rounded-xl bg-[var(--gold)] text-white font-bold text-sm text-center shadow-lg"
            >
              Get Started Free
            </Link>
          </div>
        )}
      </div>
    </>
  );
}
