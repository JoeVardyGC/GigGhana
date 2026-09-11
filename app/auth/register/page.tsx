'use client';

import React, { useState, useEffect, Suspense } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { AuthLayout } from '@/components/auth/AuthLayout';
import { PhoneInput, TelecomNetwork } from '@/components/ui/phone-input';
import { GhanaCardInput } from '@/components/ui/ghana-card-input';
import { useAuth } from '@/lib/context/AuthContext';
import confetti from 'canvas-confetti';
import {
  Wrench,
  Building2,
  Check,
  ArrowRight,
  ArrowLeft,
  Lock,
  Eye,
  EyeOff,
  ShieldCheck,
  Sparkles,
  Smartphone,
  CheckCircle2,
  BadgeCheck,
  Star,
  MapPin,
} from 'lucide-react';

const GHANA_TRADES = [
  { id: 'pop', name: 'POP Ceilings & Decorative Plastering', icon: '🎨', defaultRate: 85 },
  { id: 'solar', name: 'Solar PV & 3-Phase Electrical', icon: '⚡', defaultRate: 90 },
  { id: 'plumbing', name: 'Industrial & Domestic Plumbing', icon: '🔧', defaultRate: 75 },
  { id: 'carpentry', name: 'Bespoke Joinery & Cabinetry', icon: '🪚', defaultRate: 80 },
  { id: 'masonry', name: 'Masonry & Tiling Construction', icon: '🧱', defaultRate: 85 },
  { id: 'tech', name: 'Full-Stack Web & Mobile Apps', icon: '💻', defaultRate: 110 },
  { id: 'couture', name: 'Haute Couture & Bespoke Fashion', icon: '👗', defaultRate: 95 },
  { id: 'health', name: 'Physiotherapy & Home Nursing', icon: '🏥', defaultRate: 100 },
];

const GHANA_CITIES = [
  'Airport Hills, Accra',
  'East Legon, Accra',
  'Spintex Road, Accra',
  'Tema Industrial, Greater Accra',
  'Kumasi Central, Ashanti',
  'Bantama, Kumasi',
  'Takoradi, Western',
  'Cape Coast, Central',
  'Tamale, Northern',
  'Ho, Volta',
  'Sunyani, Bono',
];

function RegisterContent() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const { register } = useAuth();

  const roleParam = searchParams.get('role');
  const tierParam = searchParams.get('tier');

  // Role: 'provider' or 'client'
  const [role, setRole] = useState<'provider' | 'client'>(
    roleParam === 'client' ? 'client' : 'provider'
  );

  // Selected Tier
  const [tier, setTier] = useState<'starter' | 'verified' | 'premium'>(
    tierParam === 'premium' ? 'premium' : tierParam === 'verified' ? 'verified' : 'starter'
  );

  // Stepper: 1 to 4 for Provider, 1 to 2 for Client
  const [step, setStep] = useState(1);
  const totalSteps = role === 'provider' ? 4 : 2;

  // Form Fields
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [network, setNetwork] = useState<TelecomNetwork>('unknown');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);

  // Provider specific
  const [selectedTrade, setSelectedTrade] = useState(GHANA_TRADES[0].name);
  const [selectedCity, setSelectedCity] = useState(GHANA_CITIES[0]);
  const [hourlyRate, setHourlyRate] = useState<number>(GHANA_TRADES[0].defaultRate);
  const [ghanaCardPin, setGhanaCardPin] = useState('');
  const [isGhanaCardValid, setIsGhanaCardValid] = useState(false);
  const [cardFrontImg, setCardFrontImg] = useState<string | null>(null);
  const [cardBackImg, setCardBackImg] = useState<string | null>(null);
  const [payoutWallet, setPayoutWallet] = useState<'mtn' | 'telecel' | 'at'>('mtn');
  const [walletPhone, setWalletPhone] = useState('');

  // Client specific
  const [companyName, setCompanyName] = useState('');
  const [projectIntent, setProjectIntent] = useState<'post_job' | 'hire_artisan'>('hire_artisan');

  // UI state
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  // Auto-sync wallet when network is detected
  useEffect(() => {
    if (network === 'mtn') setPayoutWallet('mtn');
    else if (network === 'telecel') setPayoutWallet('telecel');
    else if (network === 'at') setPayoutWallet('at');

    if (phone && !walletPhone) {
      setWalletPhone(phone);
    }
  }, [network, phone]);

  const handleNextStep = () => {
    setErrorMsg('');
    if (step === 1) {
      if (!firstName.trim() || !lastName.trim()) {
        setErrorMsg('Please enter both your first and last name.');
        return;
      }
      if (!phone || phone.replace(/\D/g, '').length < 9) {
        setErrorMsg('Please enter a valid 10-digit Ghanaian telephone number.');
        return;
      }
      if (!email.trim() || !email.includes('@')) {
        setErrorMsg('Please enter a valid email address.');
        return;
      }
      if (!password || password.length < 6) {
        setErrorMsg('Password must be at least 6 characters.');
        return;
      }
    }

    if (role === 'provider' && step === 3) {
      if (!ghanaCardPin || !isGhanaCardValid) {
        setErrorMsg('Please provide a valid Ghana Card PIN in GHA-XXXXXXXXX-X format.');
        return;
      }
    }

    if (step < totalSteps) {
      setStep(step + 1);
    } else {
      handleFinalSubmit();
    }
  };

  const handlePrevStep = () => {
    setErrorMsg('');
    if (step > 1) {
      setStep(step - 1);
    }
  };

  const handleFinalSubmit = async () => {
    setErrorMsg('');
    setIsSubmitting(true);

    try {
      const userData = {
        first_name: firstName.trim(),
        last_name: lastName.trim(),
        email: email.trim().toLowerCase(),
        phone: phone.trim(),
        role,
        location: selectedCity,
        is_verified: isGhanaCardValid,
        membership_tier: tier,
        trade: selectedTrade,
        payout_wallet: payoutWallet,
        wallet_number: walletPhone || phone,
      };

      const res = await register(userData);

      if (res.success) {
        try {
          confetti({
            particleCount: 90,
            spread: 70,
            origin: { y: 0.6 },
            colors: ['#00D4C8', '#F59E0B', '#10B981', '#ffffff'],
          });
        } catch (_) {}

        // Forward to respective dashboard after short celebratory delay
        setTimeout(() => {
          router.push(role === 'client' ? '/dashboard/client' : '/dashboard/provider');
        }, 1200);
      } else {
        setErrorMsg(res.message || 'Registration could not be completed. Please try again.');
        setIsSubmitting(false);
      }
    } catch (err: any) {
      setErrorMsg(err?.message || 'An unexpected error occurred. Please try again.');
      setIsSubmitting(false);
    }
  };

  return (
    <AuthLayout
      title={role === 'provider' ? 'Join as a Verified Master Artisan' : 'Hire Ghana Card Verified Talent'}
      subtitle={
        role === 'provider'
          ? 'Set up your profile, verify your Ghana Card, and receive instant sub-60s Mobile Money escrow settlements.'
          : 'Post your project in Cedis (₵), review verified local bids, and protect your milestone funds in escrow.'
      }
    >
      {/* ══════ DUAL-ROLE TOGGLE SWITCHER ══════ */}
      <div className="grid grid-cols-2 gap-2 p-1.5 bg-[var(--s2)] rounded-xl mb-6 border border-[var(--bd2)]">
        <button
          type="button"
          onClick={() => {
            setRole('provider');
            setStep(1);
          }}
          className={`flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-xs font-bold transition-all ${
            role === 'provider'
              ? 'bg-[var(--surface)] text-[var(--tx)] shadow-xs border border-[var(--cyan-border)]'
              : 'text-[var(--tx-2)] hover:text-[var(--tx)]'
          }`}
        >
          <Wrench className={`w-3.5 h-3.5 ${role === 'provider' ? 'text-[var(--cyan)]' : ''}`} />
          <span>I Want to Work (Artisan)</span>
        </button>

        <button
          type="button"
          onClick={() => {
            setRole('client');
            setStep(1);
          }}
          className={`flex items-center justify-center gap-2 py-2.5 px-3 rounded-lg text-xs font-bold transition-all ${
            role === 'client'
              ? 'bg-[var(--surface)] text-[var(--tx)] shadow-xs border border-[#F59E0B]/50'
              : 'text-[var(--tx-2)] hover:text-[var(--tx)]'
          }`}
        >
          <Building2 className={`w-3.5 h-3.5 ${role === 'client' ? 'text-[#F59E0B]' : ''}`} />
          <span>I Want to Hire (Client)</span>
        </button>
      </div>

      {/* ══════ STEPPER PROGRESS BAR ══════ */}
      <div className="mb-6">
        <div className="flex items-center justify-between text-[11px] font-bold text-[var(--tx-3)] mb-2">
          <span>
            STEP {step} OF {totalSteps}:{' '}
            <strong className="text-[var(--tx)]">
              {role === 'provider'
                ? step === 1
                  ? 'Account Details'
                  : step === 2
                  ? 'Craft & Location'
                  : step === 3
                  ? 'Ghana Card Biometrics'
                  : 'Payout Setup'
                : step === 1
                ? 'Contact Info'
                : 'Project Intent'}
            </strong>
          </span>
          <span className="text-[var(--cyan)] font-mono">{Math.round((step / totalSteps) * 100)}%</span>
        </div>
        <div className="w-full h-1.5 bg-[var(--bd2)] rounded-full overflow-hidden">
          <div
            className="h-full bg-gradient-to-r from-[var(--cyan)] via-[#3B82F6] to-[#10B981] transition-all duration-300 rounded-full"
            style={{ width: `${(step / totalSteps) * 100}%` }}
          />
        </div>
      </div>

      {/* ══════ STEP CONTENT ══════ */}
      <div className="space-y-4">
        {/* PROVIDER STEP 1 & CLIENT STEP 1: Account Credentials */}
        {step === 1 && (
          <>
            {/* Tier banner reminder if selected from landing page */}
            {role === 'provider' && tier !== 'starter' && (
              <div className="p-3 rounded-xl bg-gradient-to-r from-amber-500/10 to-transparent border border-amber-500/30 flex items-center justify-between text-xs mb-2">
                <div className="flex items-center gap-2">
                  <BadgeCheck className="w-4 h-4 text-[#F59E0B]" />
                  <span className="font-semibold text-[var(--tx)]">
                    Selected Tier: <strong>{tier === 'premium' ? '⭐ Premium Master (₵99/mo)' : '👑 Verified Pro (₵49/mo)'}</strong>
                  </span>
                </div>
                <button
                  type="button"
                  onClick={() => setTier('starter')}
                  className="text-[10px] text-[var(--tx-3)] hover:text-[var(--tx)] underline"
                >
                  Change to Free
                </button>
              </div>
            )}

            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                  <span>First Name</span>
                  <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  value={firstName}
                  onChange={(e) => setFirstName(e.target.value)}
                  placeholder="e.g. Kwame"
                  className="w-full h-11 px-3.5 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
                />
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                  <span>Surname</span>
                  <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  value={lastName}
                  onChange={(e) => setLastName(e.target.value)}
                  placeholder="e.g. Asante"
                  className="w-full h-11 px-3.5 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
                />
              </div>
            </div>

            {/* Ghanaian Phone with Network Auto-Detect */}
            <PhoneInput
              value={phone}
              onChange={(val, net) => {
                setPhone(val);
                setNetwork(net);
              }}
              required
            />

            {/* Email Address */}
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                <span>Email Address</span>
                <span className="text-rose-500">*</span>
              </label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="name@domain.com"
                className="w-full h-11 px-3.5 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
              />
            </div>

            {/* Password with Eye Toggle */}
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                <span>Create Password</span>
                <span className="text-rose-500">*</span>
              </label>
              <div className="relative flex items-center">
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="At least 6 characters"
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
          </>
        )}

        {/* PROVIDER STEP 2: Trade & Location */}
        {role === 'provider' && step === 2 && (
          <>
            <div className="space-y-2">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                <span>Select Your Master Specialization</span>
                <span className="text-[10.5px] font-normal text-[var(--tx-3)]">Primary Trade</span>
              </label>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                {GHANA_TRADES.map((trade) => (
                  <button
                    key={trade.id}
                    type="button"
                    onClick={() => {
                      setSelectedTrade(trade.name);
                      setHourlyRate(trade.defaultRate);
                    }}
                    className={`p-2.5 rounded-xl border text-left flex items-center gap-2.5 transition-all ${
                      selectedTrade === trade.name
                        ? 'border-[var(--cyan)] bg-[var(--cyan)]/[0.08] shadow-xs'
                        : 'border-[var(--bd2)] hover:border-[var(--bd)] bg-[var(--surface)]'
                    }`}
                  >
                    <span className="text-lg leading-none">{trade.icon}</span>
                    <div className="min-w-0 flex-1">
                      <div className="text-xs font-bold text-[var(--tx)] truncate">{trade.name}</div>
                      <div className="text-[10px] text-[var(--tx-3)]">Avg. ₵{trade.defaultRate}/hr</div>
                    </div>
                    {selectedTrade === trade.name && (
                      <Check className="w-3.5 h-3.5 text-[var(--cyan)] shrink-0" />
                    )}
                  </button>
                ))}
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
              {/* City / Hub */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                  <MapPin className="w-3.5 h-3.5 text-[var(--cyan)]" />
                  <span>Primary Operating Location</span>
                </label>
                <select
                  value={selectedCity}
                  onChange={(e) => setSelectedCity(e.target.value)}
                  className="w-full h-11 px-3 bg-[var(--surface)] text-[var(--tx)] text-xs font-semibold rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                >
                  {GHANA_CITIES.map((city) => (
                    <option key={city} value={city}>
                      {city}
                    </option>
                  ))}
                </select>
              </div>

              {/* Hourly / Estimate Rate */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                  <span>Base Rate (Cedis)</span>
                  <span className="text-[10px] font-mono text-[var(--tx-3)]">₵ GHS</span>
                </label>
                <div className="relative flex items-center">
                  <span className="absolute left-3.5 text-xs font-bold text-[var(--tx-2)]">₵</span>
                  <input
                    type="number"
                    value={hourlyRate}
                    onChange={(e) => setHourlyRate(Number(e.target.value))}
                    min={30}
                    max={2000}
                    className="w-full h-11 pl-8 pr-12 bg-[var(--surface)] text-[var(--tx)] text-sm font-bold rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                  />
                  <span className="absolute right-3.5 text-xs text-[var(--tx-3)] font-medium">/ hr</span>
                </div>
              </div>
            </div>
          </>
        )}

        {/* PROVIDER STEP 3: Ghana Card Biometrics */}
        {role === 'provider' && step === 3 && (
          <GhanaCardInput
            pin={ghanaCardPin}
            onPinChange={(pinVal, valid) => {
              setGhanaCardPin(pinVal);
              setIsGhanaCardValid(valid);
            }}
            frontImage={cardFrontImg}
            onFrontImageChange={setCardFrontImg}
            backImage={cardBackImg}
            onBackImageChange={setCardBackImg}
          />
        )}

        {/* PROVIDER STEP 4: Mobile Money Settlement */}
        {role === 'provider' && step === 4 && (
          <div className="space-y-4">
            <div className="space-y-2">
              <label className="text-xs font-bold text-[var(--tx)]">
                Select Mobile Money Escrow Settlement Wallet
              </label>
              <div className="grid grid-cols-3 gap-2">
                {[
                  { id: 'mtn', name: 'MTN MoMo', logo: '/images/payments/mtn_momo.svg', color: 'border-amber-400' },
                  { id: 'telecel', name: 'Telecel Cash', logo: '/images/payments/telecel_cash.svg', color: 'border-red-500' },
                  { id: 'at', name: 'AT Money', logo: '/images/payments/at_money.svg', color: 'border-blue-500' },
                ].map((w) => (
                  <button
                    key={w.id}
                    type="button"
                    onClick={() => setPayoutWallet(w.id as any)}
                    className={`p-3 rounded-xl border flex flex-col items-center justify-center gap-1.5 transition-all ${
                      payoutWallet === w.id
                        ? `${w.color} bg-[var(--cyan)]/[0.06] shadow-xs ring-1 ring-current`
                        : 'border-[var(--bd2)] bg-[var(--surface)] hover:border-[var(--bd)]'
                    }`}
                  >
                    <img src={w.logo} alt={w.name} className="h-5 w-auto object-contain" />
                    <span className="text-[10.5px] font-bold text-[var(--tx)]">{w.name}</span>
                  </button>
                ))}
              </div>
            </div>

            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                <span>Wallet Mobile Number</span>
                <span className="text-[10.5px] text-[#10B981] font-semibold">Sub-60s Direct Settlement</span>
              </label>
              <input
                type="tel"
                value={walletPhone}
                onChange={(e) => setWalletPhone(e.target.value)}
                placeholder="024 000 0000"
                className="w-full h-11 px-3.5 bg-[var(--surface)] text-[var(--tx)] text-sm font-mono font-bold rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
              />
            </div>

            {/* Instant verification assurance banner */}
            <div className="rounded-xl p-3.5 bg-gradient-to-r from-[#10B981]/10 to-[var(--cyan)]/10 border border-[#10B981]/25 flex items-start gap-2.5">
              <CheckCircle2 className="w-4 h-4 text-[#10B981] shrink-0 mt-0.5" />
              <div className="text-xs text-[var(--tx-2)] leading-relaxed">
                <strong className="text-[var(--tx)]">Escrow Guarantee:</strong> When clients approve milestone deliverables, your earnings are automatically transferred directly to this Mobile Money account with zero withdrawal delays.
              </div>
            </div>
          </div>
        )}

        {/* CLIENT STEP 2: Intent & Organization */}
        {role === 'client' && step === 2 && (
          <div className="space-y-4">
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                <span>Company or Household Name</span>
                <span className="text-[10px] text-[var(--tx-3)]">Optional</span>
              </label>
              <input
                type="text"
                value={companyName}
                onChange={(e) => setCompanyName(e.target.value)}
                placeholder="e.g. Ridge Commercial Ltd or Private Residence"
                className="w-full h-11 px-3.5 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-xl border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
              />
            </div>

            <div className="space-y-2">
              <label className="text-xs font-bold text-[var(--tx)]">
                What is your immediate hiring objective?
              </label>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button
                  type="button"
                  onClick={() => setProjectIntent('hire_artisan')}
                  className={`p-3 rounded-xl border text-left transition-all ${
                    projectIntent === 'hire_artisan'
                      ? 'border-[var(--cyan)] bg-[var(--cyan)]/[0.08] shadow-xs'
                      : 'border-[var(--bd2)] bg-[var(--surface)] hover:border-[var(--bd)]'
                  }`}
                >
                  <div className="text-xs font-bold text-[var(--tx)] mb-0.5">🔍 Browse &amp; Direct Hire</div>
                  <div className="text-[11px] text-[var(--tx-3)]">Look through Ghana Card verified profiles and message them.</div>
                </button>

                <button
                  type="button"
                  onClick={() => setProjectIntent('post_job')}
                  className={`p-3 rounded-xl border text-left transition-all ${
                    projectIntent === 'post_job'
                      ? 'border-[#F59E0B] bg-[#F59E0B]/[0.08] shadow-xs'
                      : 'border-[var(--bd2)] bg-[var(--surface)] hover:border-[var(--bd)]'
                  }`}
                >
                  <div className="text-xs font-bold text-[var(--tx)] mb-0.5">📝 Post a Project Brief</div>
                  <div className="text-[11px] text-[var(--tx-3)]">Receive competitive Cedi proposals from top local masters within 15 min.</div>
                </button>
              </div>
            </div>

            {/* Escrow assurance note */}
            <div className="rounded-xl p-3.5 bg-gradient-to-r from-[var(--cyan)]/10 to-[#3B82F6]/10 border border-[var(--cyan)]/25 flex items-start gap-2.5">
              <ShieldCheck className="w-4 h-4 text-[var(--cyan)] shrink-0 mt-0.5" />
              <div className="text-xs text-[var(--tx-2)] leading-relaxed">
                <strong className="text-[var(--tx)]">Zero Upfront Risk:</strong> Your milestone deposits are safely locked in the Bank-Grade Escrow Vault until you inspect and approve the completed work.
              </div>
            </div>
          </div>
        )}

        {/* Error message alert */}
        {errorMsg && (
          <div className="p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{errorMsg}</span>
          </div>
        )}

        {/* ══════ ACTION NAVIGATION BUTTONS ══════ */}
        <div className="pt-3 flex items-center justify-between gap-3">
          {step > 1 ? (
            <button
              type="button"
              onClick={handlePrevStep}
              className="h-11 px-4 rounded-xl border border-[var(--bd2)] hover:border-[var(--bd)] text-[var(--tx-2)] hover:text-[var(--tx)] font-bold text-xs flex items-center gap-1.5 transition-all"
            >
              <ArrowLeft className="w-3.5 h-3.5" />
              <span>Back</span>
            </button>
          ) : (
            <div />
          )}

          <button
            type="button"
            onClick={handleNextStep}
            disabled={isSubmitting}
            className={`h-11 px-6 rounded-xl font-bold text-xs flex items-center gap-2 shadow-lg transition-all ml-auto ${
              role === 'client'
                ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] hover:from-[#D97706] hover:to-[#B45309] text-slate-950 font-black shadow-amber-500/20'
                : 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] hover:from-[#00B4A9] hover:to-[#008B82] text-slate-950 font-black shadow-cyan-500/20'
            }`}
          >
            {isSubmitting ? (
              <>
                <span className="w-4 h-4 rounded-full border-2 border-slate-950 border-t-transparent animate-spin" />
                <span>Creating Account...</span>
              </>
            ) : step === totalSteps ? (
              <>
                <span>Complete Registration</span>
                <Sparkles className="w-3.5 h-3.5" />
              </>
            ) : (
              <>
                <span>Continue</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </>
            )}
          </button>
        </div>

        {/* Bottom Switch to Login */}
        <div className="pt-4 text-center border-t border-[var(--bd2)]/40 text-xs text-[var(--tx-2)]">
          Already have an account?{' '}
          <a href="/auth/login" className="font-extrabold text-[var(--cyan)] hover:underline">
            Sign In here
          </a>
        </div>
      </div>
    </AuthLayout>
  );
}

export default function RegisterPage() {
  return (
    <Suspense fallback={<div className="min-h-screen bg-[var(--bg)] flex items-center justify-center text-xs text-[var(--tx-3)]">Loading GigGhana Onboarding...</div>}>
      <RegisterContent />
    </Suspense>
  );
}
