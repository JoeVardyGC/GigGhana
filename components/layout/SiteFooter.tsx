'use client';

import React from 'react';
import Link from 'next/link';
import { WhatsAppIcon, FacebookIcon, LinkedInIcon, InstagramIcon, TwitterXIcon } from '@/components/ui/social-icons';
import { ShieldCheck, Lock, CheckCircle2, Award, Heart } from 'lucide-react';

export function SiteFooter() {
  return (
    <footer className="w-full bg-[var(--surface)] border-t border-[var(--bd)] pt-16 pb-12 transition-colors">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Top Banner: Escrow Security & MoMo Settlement Guarantee */}
        <div className="mb-14 p-6 sm:p-8 rounded-3xl border border-[var(--cyan-border)] bg-gradient-to-r from-[var(--cyan-dim)] via-transparent to-[var(--gold-dim)] flex flex-col md:flex-row items-center justify-between gap-6 shadow-sm">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 rounded-2xl bg-[var(--cyan)] text-black flex items-center justify-center font-black shadow-lg shadow-cyan-500/20 shrink-0">
              <ShieldCheck className="w-8 h-8" />
            </div>
            <div>
              <div className="flex items-center gap-2">
                <h3 className="font-extrabold text-base sm:text-lg text-[var(--tx)] font-heading">
                  Bank-Grade Escrow Vault Guarantee
                </h3>
                <span className="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 uppercase tracking-wide">
                  Regulated
                </span>
              </div>
              <p className="text-xs sm:text-sm text-[var(--tx-2)] mt-0.5">
                Funds are securely held in escrow and disbursed to artisans via MTN MoMo, Telecel Cash, or AT Money only when work is inspected & approved.
              </p>
            </div>
          </div>
          <div className="flex items-center gap-3 shrink-0">
            <Link
              href="/auth/register?role=provider"
              className="px-4 py-2.5 rounded-xl bg-[var(--gold)] text-black font-bold text-xs hover:opacity-95 transition-all shadow-md"
            >
              Join as Artisan
            </Link>
            <Link
              href="/auth/register?role=client"
              className="px-4 py-2.5 rounded-xl border border-[var(--bd2)] text-[var(--tx)] font-bold text-xs hover:border-[var(--cyan)] transition-colors"
            >
              Hire Verified Pro
            </Link>
          </div>
        </div>

        {/* 4-Column Directory Grid */}
        <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 mb-12">
          
          {/* Col 1: Brand & Identity */}
          <div className="col-span-2 lg:col-span-2 space-y-4">
            <Link href="/" className="logo">
              <div className="logo-mark">G</div>
              <span className="logo-text">
                Gig<span className="text-[var(--cyan)]">Ghana</span>
              </span>
            </Link>
            <p className="text-xs sm:text-sm text-[var(--tx-2)] max-w-sm leading-relaxed">
              Ghana&apos;s #1 vetted marketplace connecting homeowners, diaspora investors, and businesses with certified master artisans and top tech freelancers.
            </p>
            <div className="flex items-center gap-3 pt-2">
              <span className="text-[11px] font-semibold text-[var(--tx-3)] uppercase tracking-wider">Connect:</span>
              <div className="flex items-center gap-2">
                <a href="https://wa.me/233240000000" target="_blank" rel="noreferrer" className="w-8 h-8 rounded-lg bg-[var(--surface-2)] flex items-center justify-center text-[var(--tx-2)] hover:text-emerald-500 transition-colors" aria-label="WhatsApp">
                  <WhatsAppIcon size={16} />
                </a>
                <a href="https://facebook.com" target="_blank" rel="noreferrer" className="w-8 h-8 rounded-lg bg-[var(--surface-2)] flex items-center justify-center text-[var(--tx-2)] hover:text-blue-500 transition-colors" aria-label="Facebook">
                  <FacebookIcon size={16} />
                </a>
                <a href="https://instagram.com" target="_blank" rel="noreferrer" className="w-8 h-8 rounded-lg bg-[var(--surface-2)] flex items-center justify-center text-[var(--tx-2)] hover:text-pink-500 transition-colors" aria-label="Instagram">
                  <InstagramIcon size={16} />
                </a>
                <a href="https://linkedin.com" target="_blank" rel="noreferrer" className="w-8 h-8 rounded-lg bg-[var(--surface-2)] flex items-center justify-center text-[var(--tx-2)] hover:text-blue-400 transition-colors" aria-label="LinkedIn">
                  <LinkedInIcon size={16} />
                </a>
                <a href="https://x.com" target="_blank" rel="noreferrer" className="w-8 h-8 rounded-lg bg-[var(--surface-2)] flex items-center justify-center text-[var(--tx-2)] hover:text-[var(--tx)] transition-colors" aria-label="X (Twitter)">
                  <TwitterXIcon size={14} />
                </a>
              </div>
            </div>
          </div>

          {/* Col 2: High-Demand Trades */}
          <div className="space-y-3">
            <h4 className="text-xs font-bold text-[var(--tx)] uppercase tracking-wider font-heading">
              Popular Trades
            </h4>
            <ul className="space-y-2 text-xs text-[var(--tx-2)]">
              <li><Link href="/search/providers?category=trades" className="hover:text-[var(--cyan)] transition-colors">POP Ceilings & Moulding</Link></li>
              <li><Link href="/search/providers?category=trades" className="hover:text-[var(--cyan)] transition-colors">Solar Inverters & Wiring</Link></li>
              <li><Link href="/search/providers?category=trades" className="hover:text-[var(--cyan)] transition-colors">Plumbing & Water Systems</Link></li>
              <li><Link href="/search/providers?category=build" className="hover:text-[var(--cyan)] transition-colors">Masonry & Tiling</Link></li>
              <li><Link href="/search/providers?category=tech" className="hover:text-[var(--cyan)] transition-colors">Web & Mobile Developers</Link></li>
              <li><Link href="/search/providers?category=design" className="hover:text-[var(--cyan)] transition-colors">Haute Couture & Tailoring</Link></li>
            </ul>
          </div>

          {/* Col 3: Regional Hubs */}
          <div className="space-y-3">
            <h4 className="text-xs font-bold text-[var(--tx)] uppercase tracking-wider font-heading">
              Regional Hubs
            </h4>
            <ul className="space-y-2 text-xs text-[var(--tx-2)]">
              <li><Link href="/search/providers?region=Accra" className="hover:text-[var(--cyan)] transition-colors">Greater Accra (East Legon, Airport)</Link></li>
              <li><Link href="/search/providers?region=Ashanti" className="hover:text-[var(--cyan)] transition-colors">Ashanti (Kumasi, Ahodwo)</Link></li>
              <li><Link href="/search/providers?region=Western" className="hover:text-[var(--cyan)] transition-colors">Western (Takoradi, Sekondi)</Link></li>
              <li><Link href="/search/providers?region=Tema" className="hover:text-[var(--cyan)] transition-colors">Tema Industrial Freezone</Link></li>
              <li><Link href="/search/providers?region=Central" className="hover:text-[var(--cyan)] transition-colors">Central (Cape Coast, Elmina)</Link></li>
              <li><Link href="/search/providers?region=Eastern" className="hover:text-[var(--cyan)] transition-colors">Eastern (Koforidua)</Link></li>
            </ul>
          </div>

          {/* Col 4: Platform & Trust */}
          <div className="space-y-3">
            <h4 className="text-xs font-bold text-[var(--tx)] uppercase tracking-wider font-heading">
              Platform & Safety
            </h4>
            <ul className="space-y-2 text-xs text-[var(--tx-2)]">
              <li><Link href="/jobs" className="hover:text-[var(--cyan)] transition-colors">Browse Live Jobs</Link></li>
              <li><Link href="/search/providers" className="hover:text-[var(--cyan)] transition-colors">Find Verified Artisans</Link></li>
              <li><Link href="/#how" className="hover:text-[var(--cyan)] transition-colors">How Escrow Works</Link></li>
              <li><Link href="/auth/register" className="hover:text-[var(--cyan)] transition-colors">Ghana Card Biometric KYC</Link></li>
              <li><Link href="/#faq" className="hover:text-[var(--cyan)] transition-colors">Help Center & FAQ</Link></li>
            </ul>
          </div>

        </div>

        {/* Payment Partners & Supported Telecoms Strip */}
        <div className="pt-8 border-t border-[var(--bd)] flex flex-col md:flex-row items-center justify-between gap-6 text-xs text-[var(--tx-3)]">
          <div className="flex flex-wrap items-center gap-4">
            <span className="font-semibold uppercase tracking-wider text-[11px] text-[var(--tx-2)]">
              Settlement Partners:
            </span>
            <span className="px-2.5 py-1 rounded-md bg-[var(--surface-2)] font-mono text-[11px] font-bold text-[var(--tx)] border border-[var(--bd)]">
              🟡 MTN Mobile Money
            </span>
            <span className="px-2.5 py-1 rounded-md bg-[var(--surface-2)] font-mono text-[11px] font-bold text-[var(--tx)] border border-[var(--bd)]">
              🔴 Telecel Cash
            </span>
            <span className="px-2.5 py-1 rounded-md bg-[var(--surface-2)] font-mono text-[11px] font-bold text-[var(--tx)] border border-[var(--bd)]">
              🔵 AT Money
            </span>
            <span className="px-2.5 py-1 rounded-md bg-[var(--surface-2)] font-mono text-[11px] font-bold text-[var(--tx)] border border-[var(--bd)]">
              🟢 Paystack Escrow API
            </span>
          </div>

          <div className="flex items-center gap-6">
            <span>© {new Date().getFullYear()} GigGhana Ltd. All rights reserved.</span>
            <span>Accra, Ghana 🇬🇭</span>
          </div>
        </div>

      </div>
    </footer>
  );
}
