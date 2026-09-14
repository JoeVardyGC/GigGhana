'use client';

import React from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useAuth } from '@/lib/context/AuthContext';
import {
  LayoutDashboard,
  Briefcase,
  Search,
  FileText,
  Wallet,
  User,
  ShieldCheck,
  LogOut,
  ChevronRight,
  ExternalLink,
} from 'lucide-react';

interface ProviderSidebarProps {
  onCloseMobile?: () => void;
}

export function ProviderSidebar({ onCloseMobile }: ProviderSidebarProps) {
  const pathname = usePathname();
  const { user, logout } = useAuth();

  const navItems = [
    {
      label: 'Dashboard',
      href: '/provider/dashboard',
      icon: LayoutDashboard,
      badge: undefined,
    },
    {
      label: 'Active Contracts',
      href: '#active-contracts',
      icon: Briefcase,
      badge: '2 Live',
      badgeColor: 'bg-[var(--cyan)] text-slate-950',
    },
    {
      label: 'Browse Gigs',
      href: '#recommended-jobs',
      icon: Search,
      badge: 'New',
      badgeColor: 'bg-amber-500/20 text-amber-400 border border-amber-500/30',
    },
    {
      label: 'My Proposals',
      href: '#my-proposals',
      icon: FileText,
      badge: undefined,
    },
    {
      label: 'MoMo Wallet & Escrow',
      href: '#momo-wallet',
      icon: Wallet,
      badge: undefined,
    },
    {
      label: 'Public Profile',
      href: '#artisan-profile',
      icon: User,
      badge: undefined,
    },
  ];

  return (
    <aside className="w-64 bg-[var(--surface)] border-r border-[var(--bd2)] flex flex-col h-full shrink-0 select-none">
      {/* Brand Header */}
      <div className="p-5 border-b border-[var(--bd2)] flex items-center justify-between">
        <Link href="/" className="flex items-center gap-2.5 group">
          <div className="w-9 h-9 rounded-[12px] bg-gradient-to-br from-[var(--cyan)] to-[#009E95] flex items-center justify-center font-black text-slate-950 text-base shadow-md shadow-cyan-500/20 group-hover:scale-105 transition-transform">
            G
          </div>
          <div>
            <div className="font-extrabold text-base tracking-tight text-[var(--tx)] flex items-center gap-1">
              <span>Gig</span>
              <span className="text-[var(--cyan)]">Ghana</span>
            </div>
            <div className="text-[10px] font-bold text-[var(--tx-3)] tracking-wider uppercase flex items-center gap-1">
              <span className="w-1.5 h-1.5 rounded-full bg-[var(--cyan)] animate-pulse" />
              <span>Artisan Portal</span>
            </div>
          </div>
        </Link>
      </div>

      {/* Verified Master Artisan Status Banner */}
      <div className="px-4 pt-4 pb-2">
        <div className="p-3 rounded-[16px] bg-gradient-to-r from-[var(--cyan)]/10 to-[#10B981]/10 border border-[var(--cyan)]/25 flex items-center gap-2.5">
          <div className="w-7 h-7 rounded-[10px] bg-[var(--cyan)] text-slate-950 flex items-center justify-center shrink-0">
            <ShieldCheck className="w-4 h-4 stroke-[2.5]" />
          </div>
          <div className="min-w-0">
            <div className="text-xs font-black text-[var(--tx)] truncate">
              {user?.trade || 'Master Artisan'}
            </div>
            <div className="text-[10.5px] text-[#10B981] font-bold flex items-center gap-1">
              <span>Ghana Card Verified</span>
            </div>
          </div>
        </div>
      </div>

      {/* Navigation Links */}
      <div className="flex-1 px-3 py-3 space-y-1 overflow-y-auto scrollbar-none">
        <div className="px-3 pb-2 text-[10px] font-extrabold tracking-widest text-[var(--tx-3)] uppercase">
          Workspace
        </div>

        {navItems.map((item) => {
          const Icon = item.icon;
          const isActive = pathname === item.href;
          return (
            <Link
              key={item.label}
              href={item.href}
              onClick={onCloseMobile}
              className={`flex items-center justify-between px-3 py-2.5 rounded-[14px] text-xs font-bold transition-all ${
                isActive
                  ? 'bg-[var(--cyan)] text-slate-950 shadow-sm font-black'
                  : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface-elevated)]'
              }`}
            >
              <div className="flex items-center gap-2.5 min-w-0">
                <Icon className={`w-4 h-4 shrink-0 ${isActive ? 'text-slate-950' : 'text-[var(--cyan)]'}`} />
                <span className="truncate">{item.label}</span>
              </div>
              {item.badge && (
                <span
                  className={`text-[10px] font-extrabold px-2 py-0.5 rounded-full whitespace-nowrap ml-2 ${
                    isActive ? 'bg-slate-950 text-[var(--cyan)]' : item.badgeColor
                  }`}
                >
                  {item.badge}
                </span>
              )}
            </Link>
          );
        })}

        <div className="pt-5 px-3 pb-2 text-[10px] font-extrabold tracking-widest text-[var(--tx-3)] uppercase">
          Quick Links
        </div>

        <Link
          href="/"
          className="flex items-center justify-between px-3 py-2 rounded-[14px] text-xs font-semibold text-[var(--tx-3)] hover:text-[var(--tx)] hover:bg-[var(--surface-elevated)] transition-colors"
        >
          <div className="flex items-center gap-2">
            <ExternalLink className="w-3.5 h-3.5 text-[var(--tx-3)]" />
            <span>Visit Homepage</span>
          </div>
          <ChevronRight className="w-3 h-3 text-[var(--tx-3)]" />
        </Link>
      </div>

      {/* Artisan User Footer */}
      <div className="p-3 border-t border-[var(--bd2)] bg-[var(--surface-elevated)]/50">
        <div className="flex items-center justify-between p-2 rounded-[14px] bg-[var(--surface)] border border-[var(--bd2)]">
          <div className="flex items-center gap-2.5 min-w-0">
            <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-[var(--cyan)] to-[#3B82F6] text-slate-950 font-black text-xs flex items-center justify-center shrink-0 shadow-inner">
              {user?.first_name ? user.first_name[0] : 'K'}
            </div>
            <div className="min-w-0 flex-1">
              <div className="text-xs font-bold text-[var(--tx)] truncate">
                {user ? `${user.first_name} ${user.last_name}` : 'Kwame Asante'}
              </div>
              <div className="text-[10px] text-[var(--tx-3)] truncate">
                {user?.location || 'Accra, Greater Accra'}
              </div>
            </div>
          </div>
          <button
            type="button"
            onClick={logout}
            title="Sign out"
            className="p-1.5 rounded-[10px] text-[var(--tx-3)] hover:text-rose-500 hover:bg-rose-500/10 transition-colors shrink-0 cursor-pointer"
          >
            <LogOut className="w-4 h-4" />
          </button>
        </div>
      </div>
    </aside>
  );
}
