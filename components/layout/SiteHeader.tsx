'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/lib/context/AuthContext';
import { ShieldCheck, User, LogOut, ArrowRight, Menu, X, Sparkles, Briefcase } from 'lucide-react';

interface SiteHeaderProps {
  activeTab?: 'jobs' | 'providers' | 'home';
}

export function SiteHeader({ activeTab }: SiteHeaderProps) {
  const pathname = usePathname();
  const { user, isAuthenticated, logout } = useAuth();
  const [isLight, setIsLight] = useState(false);
  const [isMobOpen, setIsMobOpen] = useState(false);
  const [scrolledNav, setScrolledNav] = useState(false);

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

  const workspaceLink = user?.role === 'client' ? '/dashboard/client' : '/dashboard/provider';

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

          {/* Authenticated Workspace or Guest Login */}
          {isAuthenticated && user ? (
            <div className="flex items-center gap-2">
              <Link
                href={workspaceLink}
                className="flex items-center gap-2 px-3 py-1.5 rounded-xl border border-[var(--cyan-border)] bg-[var(--cyan-dim)] text-[var(--cyan)] hover:bg-[var(--cyan)] hover:text-black font-semibold text-xs transition-all shadow-sm group"
              >
                <div className="w-6 h-6 rounded-full bg-[var(--cyan)] text-black font-bold flex items-center justify-center text-[11px]">
                  {user.first_name[0]}
                </div>
                <span className="hidden sm:inline">{user.first_name}</span>
                <span className="text-[10px] opacity-80 uppercase tracking-wider hidden lg:inline">
                  ({user.role === 'client' ? 'Client' : 'Artisan'})
                </span>
                <ArrowRight className="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" />
              </Link>
              <button
                onClick={logout}
                title="Log Out"
                className="p-2 rounded-xl text-[var(--tx-3)] hover:text-red-500 hover:bg-red-500/10 transition-colors"
              >
                <LogOut className="w-4 h-4" />
              </button>
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
          href="/jobs"
          onClick={() => setIsMobOpen(false)}
          className="text-base font-semibold text-[var(--tx)] py-2 border-b border-[var(--bd)]"
        >
          💼 Browse Jobs & Gigs
        </Link>
        <Link
          href="/search/providers"
          onClick={() => setIsMobOpen(false)}
          className="text-base font-semibold text-[var(--tx)] py-2 border-b border-[var(--bd)]"
        >
          🔍 Find Verified Artisans
        </Link>
        <Link
          href="/#how"
          onClick={() => setIsMobOpen(false)}
          className="text-base font-semibold text-[var(--tx)] py-2 border-b border-[var(--bd)]"
        >
          🛡️ Escrow Protection System
        </Link>
        <Link
          href="/#categories"
          onClick={() => setIsMobOpen(false)}
          className="text-base font-semibold text-[var(--tx)] py-2 border-b border-[var(--bd)]"
        >
          🏷️ Browse Categories
        </Link>

        {isAuthenticated && user ? (
          <div className="pt-2 flex flex-col gap-2">
            <Link
              href={workspaceLink}
              onClick={() => setIsMobOpen(false)}
              className="w-full py-3 rounded-xl bg-gradient-to-r from-[var(--cyan)] to-emerald-500 text-black font-bold text-sm text-center flex items-center justify-center gap-2 shadow-lg"
            >
              <span>Go to {user.role === 'client' ? 'Client' : 'Provider'} Workspace</span>
              <ArrowRight className="w-4 h-4" />
            </Link>
            <button
              onClick={() => {
                logout();
                setIsMobOpen(false);
              }}
              className="w-full py-2.5 rounded-xl border border-red-500/20 text-red-500 font-semibold text-xs flex items-center justify-center gap-2 hover:bg-red-500/10"
            >
              <LogOut className="w-4 h-4" />
              <span>Log Out</span>
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
              className="w-full py-2.5 rounded-xl bg-[var(--gold)] text-black font-bold text-sm text-center shadow-lg"
            >
              Get Started Free
            </Link>
          </div>
        )}
      </div>
    </>
  );
}
