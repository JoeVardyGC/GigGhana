'use client';

import React, { useState, useEffect, Suspense, useMemo } from 'react';
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
  Search,
  X,
  Paintbrush,
  Zap,
  Hammer,
  HardHat,
  Flame,
  Wind,
  Car,
  Laptop,
  Palette,
  Cpu,
  Scissors,
  Camera,
  Stethoscope,
  Utensils,
  Truck,
  FileText,
  AlertCircle,
} from 'lucide-react';

export interface GhanaTradeOption {
  id: string;
  name: string;
  category: string;
  defaultRate: number;
  iconName: string;
}

const GHANA_TRADES: GhanaTradeOption[] = [
  // Construction & Finishing
  { id: 'pop', name: 'POP Ceilings & Decorative Plastering', category: 'Finishing', defaultRate: 85, iconName: 'Paintbrush' },
  { id: 'tiling', name: 'Ceramic, Porcelain & Marble Tiling', category: 'Finishing', defaultRate: 80, iconName: 'HardHat' },
  { id: 'painting', name: 'Interior & Exterior Painting & Stucco', category: 'Finishing', defaultRate: 70, iconName: 'Paintbrush' },
  { id: 'masonry', name: 'Masonry, Bricklaying & Concrete Works', category: 'Construction', defaultRate: 85, iconName: 'Building2' },
  { id: 'carpentry', name: 'Bespoke Joinery & Cabinetry', category: 'Woodwork', defaultRate: 80, iconName: 'Hammer' },
  { id: 'roofing', name: 'Roofing Truss, Slate & Sheet Installation', category: 'Construction', defaultRate: 90, iconName: 'HardHat' },
  { id: 'welding', name: 'Metal Fabrication, Gates & Burglar Proofing', category: 'Metalwork', defaultRate: 85, iconName: 'Flame' },
  { id: 'aluminum', name: 'Aluminum Glazing & Sliding Windows', category: 'Finishing', defaultRate: 75, iconName: 'Wrench' },

  // Electrical & Security
  { id: 'solar', name: 'Solar PV & Inverter Systems Installation', category: 'Electrical', defaultRate: 95, iconName: 'Zap' },
  { id: 'electrical', name: 'Commercial & 3-Phase Domestic Electrical', category: 'Electrical', defaultRate: 90, iconName: 'Zap' },
  { id: 'cctv', name: 'CCTV, Electric Fence & Smart Home Security', category: 'Security', defaultRate: 85, iconName: 'ShieldCheck' },
  { id: 'hvac', name: 'Air Conditioning & Commercial Refrigeration', category: 'Mechanical', defaultRate: 80, iconName: 'Wind' },

  // Plumbing
  { id: 'plumbing', name: 'Industrial & Domestic Piping & Plumbing', category: 'Plumbing', defaultRate: 75, iconName: 'Wrench' },
  { id: 'borehole', name: 'Borehole Drilling & Pumping Mechanics', category: 'Plumbing', defaultRate: 110, iconName: 'Wrench' },

  // Automotive
  { id: 'auto-mechanic', name: 'Automotive Engine & Mechanical Diagnostics', category: 'Automotive', defaultRate: 85, iconName: 'Car' },
  { id: 'auto-electrical', name: 'Automotive Electrical & ECU Programming', category: 'Automotive', defaultRate: 90, iconName: 'Car' },

  // Digital & Technology
  { id: 'software', name: 'Full-Stack Web & Mobile App Development', category: 'Tech', defaultRate: 115, iconName: 'Laptop' },
  { id: 'uiux', name: 'UI/UX Product Design & Brand Identity', category: 'Tech', defaultRate: 95, iconName: 'Palette' },
  { id: 'it-support', name: 'Network Engineering & IT Hardware Support', category: 'Tech', defaultRate: 80, iconName: 'Cpu' },

  // Creative & Lifestyle
  { id: 'couture', name: 'Haute Couture, Kente & Bespoke Fashion', category: 'Fashion', defaultRate: 95, iconName: 'Scissors' },
  { id: 'photography', name: 'Event Photography, Drone & Video Production', category: 'Media', defaultRate: 100, iconName: 'Camera' },
  { id: 'hair-beauty', name: 'Bridal Hair Styling & Professional Makeup', category: 'Beauty', defaultRate: 80, iconName: 'Sparkles' },

  // Health
  { id: 'nursing', name: 'Physiotherapy, Geriatric & Home Nursing', category: 'Health', defaultRate: 100, iconName: 'Stethoscope' },

  // Services
  { id: 'catering', name: 'Commercial Catering & Event Culinary Services', category: 'Events', defaultRate: 85, iconName: 'Utensils' },
  { id: 'logistics', name: 'Cargo Haulage & Inter-City Moving Services', category: 'Logistics', defaultRate: 100, iconName: 'Truck' },
  { id: 'cleaning', name: 'Industrial Cleaning & Fumigation Services', category: 'Services', defaultRate: 70, iconName: 'Sparkles' },
];

function getTradeIcon(iconName: string) {
  const iconProps = { className: 'w-4 h-4 text-[var(--cyan)] shrink-0' };
  switch (iconName) {
    case 'Paintbrush':
      return <Paintbrush {...iconProps} />;
    case 'HardHat':
      return <HardHat {...iconProps} />;
    case 'Building2':
      return <Building2 {...iconProps} />;
    case 'Hammer':
      return <Hammer {...iconProps} />;
    case 'Flame':
      return <Flame {...iconProps} />;
    case 'Zap':
      return <Zap {...iconProps} />;
    case 'ShieldCheck':
      return <ShieldCheck {...iconProps} />;
    case 'Wind':
      return <Wind {...iconProps} />;
    case 'Wrench':
      return <Wrench {...iconProps} />;
    case 'Car':
      return <Car {...iconProps} />;
    case 'Laptop':
      return <Laptop {...iconProps} />;
    case 'Palette':
      return <Palette {...iconProps} />;
    case 'Cpu':
      return <Cpu {...iconProps} />;
    case 'Scissors':
      return <Scissors {...iconProps} />;
    case 'Camera':
      return <Camera {...iconProps} />;
    case 'Sparkles':
      return <Sparkles {...iconProps} />;
    case 'Stethoscope':
      return <Stethoscope {...iconProps} />;
    case 'Utensils':
      return <Utensils {...iconProps} />;
    case 'Truck':
      return <Truck {...iconProps} />;
    default:
      return <Wrench {...iconProps} />;
  }
}

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
  const [tradeSearchQuery, setTradeSearchQuery] = useState('');
  const [selectedCity, setSelectedCity] = useState(GHANA_CITIES[0]);
  const [hourlyRate, setHourlyRate] = useState<number>(GHANA_TRADES[0].defaultRate);
  const [ghanaCardPin, setGhanaCardPin] = useState('');
  const [isGhanaCardValid, setIsGhanaCardValid] = useState(false);
  const [cardFrontImg, setCardFrontImg] = useState<string | null>(null);
  const [cardBackImg, setCardBackImg] = useState<string | null>(null);
  const [payoutWallet, setPayoutWallet] = useState<'mtn' | 'telecel' | 'at'>('mtn');
  const [walletPhone, setWalletPhone] = useState('');

  // Real-time filtered trades based on user typing in search bar
  const filteredTrades = useMemo(() => {
    if (!tradeSearchQuery.trim()) return GHANA_TRADES;
    const q = tradeSearchQuery.toLowerCase().trim();
    return GHANA_TRADES.filter(
      (t) =>
        t.name.toLowerCase().includes(q) ||
        t.category.toLowerCase().includes(q)
    );
  }, [tradeSearchQuery]);

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

    if (role === 'provider' && step === 2) {
      if (tradeSearchQuery.trim() && (filteredTrades.length === 0 || selectedTrade === GHANA_TRADES[0].name)) {
        setSelectedTrade(tradeSearchQuery.trim());
      }
      if (!selectedTrade.trim() && !tradeSearchQuery.trim()) {
        setErrorMsg('Please select or type your occupation/trade.');
        return;
      }
      if (!selectedTrade.trim() && tradeSearchQuery.trim()) {
        setSelectedTrade(tradeSearchQuery.trim());
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

        // Forward to verification or respective dashboard after celebratory delay
        setTimeout(() => {
          if (res.redirectTo) {
            router.push(res.redirectTo);
          } else {
            router.push(role === 'client' ? '/dashboard/client' : '/dashboard/provider');
          }
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
      <div className="grid grid-cols-2 gap-2 p-1.5 bg-[var(--surface-elevated)] rounded-[22px] mb-6 border border-[var(--bd2)] shadow-inner">
        <button
          type="button"
          onClick={() => {
            setRole('provider');
            setStep(1);
          }}
          className={`flex items-center justify-center py-3 px-3.5 rounded-[16px] text-xs sm:text-sm font-black transition-all ${
            role === 'provider'
              ? 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-white shadow-md shadow-cyan-500/25 scale-[1.01]'
              : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface)] font-bold'
          }`}
        >
          <span>Find Jobs & Work</span>
        </button>

        <button
          type="button"
          onClick={() => {
            setRole('client');
            setStep(1);
          }}
          className={`flex items-center justify-center py-3 px-3.5 rounded-[16px] text-xs sm:text-sm font-black transition-all ${
            role === 'client'
              ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] text-white shadow-md shadow-amber-500/25 scale-[1.01]'
              : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface)] font-bold'
          }`}
        >
          <span>Hire a Worker</span>
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
                    Selected Tier: <strong>{tier === 'premium' ? 'Premium Master (₵99/mo)' : 'Verified Pro (₵49/mo)'}</strong>
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
                  className="w-full h-12 px-4 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
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
                  className="w-full h-12 px-4 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
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
                className="w-full h-12 px-4 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
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
                  className="w-full h-12 pl-4 pr-11 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3.5 text-[var(--tx-3)] hover:text-[var(--tx)] transition-colors p-1"
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
            <div className="space-y-3">
              <div>
                <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between mb-1.5">
                  <span>Select or Type Your Occupation</span>
                  <span className="text-[10.5px] font-normal text-[var(--tx-3)]">Primary Trade</span>
                </label>
                <div className="relative flex items-center">
                  <Search className="w-4 h-4 text-[var(--tx-3)] absolute left-3.5 pointer-events-none" />
                  <input
                    type="text"
                    value={tradeSearchQuery}
                    onChange={(e) => {
                      setTradeSearchQuery(e.target.value);
                    }}
                    placeholder="Type your occupation (e.g. Electrician, POP, Tiler, Fashion)..."
                    className="w-full h-12 pl-10 pr-9 bg-[var(--surface)] text-[var(--tx)] text-xs font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all placeholder:text-[var(--tx-3)]"
                  />
                  {tradeSearchQuery && (
                    <button
                      type="button"
                      onClick={() => setTradeSearchQuery('')}
                      className="absolute right-3 text-[var(--tx-3)] hover:text-[var(--tx)] p-1 rounded-md transition-colors"
                      aria-label="Clear search"
                    >
                      <X className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>
              </div>

              {/* Quick option to confirm whatever custom trade user typed */}
              {tradeSearchQuery.trim() && (
                <button
                  type="button"
                  onClick={() => {
                    setSelectedTrade(tradeSearchQuery.trim());
                  }}
                  className={`w-full p-3 rounded-[16px] border text-left flex items-center gap-2.5 transition-all ${
                    selectedTrade.toLowerCase() === tradeSearchQuery.trim().toLowerCase()
                      ? 'border-[var(--cyan)] bg-[var(--cyan)]/[0.1] text-[var(--cyan)] ring-1 ring-[var(--cyan)]/30'
                      : 'border-dashed border-[var(--cyan)]/50 hover:border-[var(--cyan)] bg-[var(--surface)] hover:bg-[var(--cyan)]/[0.04]'
                  }`}
                >
                  <div className="w-7 h-7 rounded-lg bg-[var(--cyan)]/10 flex items-center justify-center shrink-0">
                    <Wrench className="w-3.5 h-3.5 text-[var(--cyan)]" />
                  </div>
                  <div className="min-w-0 flex-1">
                    <div className="text-xs font-bold text-[var(--tx)] flex items-center gap-1.5">
                      <span>Use Typed Occupation:</span>
                      <span className="text-[var(--cyan)] font-semibold truncate underline">"{tradeSearchQuery.trim()}"</span>
                    </div>
                    <div className="text-[10px] text-[var(--tx-3)]">Click to confirm as your primary registered trade</div>
                  </div>
                  {selectedTrade.toLowerCase() === tradeSearchQuery.trim().toLowerCase() ? (
                    <Check className="w-4 h-4 text-[var(--cyan)] shrink-0" />
                  ) : (
                    <span className="text-[10.5px] font-bold text-[var(--cyan)] shrink-0">Select</span>
                  )}
                </button>
              )}

              {/* Active Selection Badge */}
              <div className="flex items-center justify-between px-3.5 py-2.5 rounded-[16px] bg-[var(--surface-elevated)] border border-[var(--bd2)] text-xs">
                <div className="flex items-center gap-2 min-w-0">
                  <span className="text-[var(--tx-3)] text-[11px] shrink-0">Selected Trade:</span>
                  <span className="font-bold text-[var(--tx)] truncate">{selectedTrade || 'None selected'}</span>
                </div>
                <span className="text-[11px] font-mono text-[var(--cyan)] font-semibold shrink-0 ml-2">
                  ₵{hourlyRate}/hr base
                </span>
              </div>

              {/* Scrollable list of matched trades */}
              <div className="space-y-1.5">
                <div className="text-[10.5px] font-semibold uppercase tracking-wider text-[var(--tx-3)] flex items-center justify-between px-0.5">
                  <span>Available Occupations {tradeSearchQuery && `(${filteredTrades.length} matches)`}</span>
                  {tradeSearchQuery && (
                    <button
                      type="button"
                      onClick={() => setTradeSearchQuery('')}
                      className="text-[10px] text-[var(--cyan)] hover:underline normal-case font-medium"
                    >
                      Clear search
                    </button>
                  )}
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[220px] overflow-y-auto pr-1">
                  {filteredTrades.length > 0 ? (
                    filteredTrades.map((trade) => {
                      const isSelected = selectedTrade === trade.name;
                      return (
                        <button
                          key={trade.id}
                          type="button"
                          onClick={() => {
                            setSelectedTrade(trade.name);
                            setHourlyRate(trade.defaultRate);
                          }}
                          className={`p-3 rounded-[16px] border text-left flex items-center gap-2.5 transition-all ${
                            isSelected
                              ? 'border-[var(--cyan)] bg-[var(--cyan)]/[0.1] ring-1 ring-[var(--cyan)]/30'
                              : 'border-[var(--bd2)] hover:border-[var(--bd)] bg-[var(--surface)] hover:bg-[var(--surface-elevated)]'
                          }`}
                        >
                          <div className="w-7 h-7 rounded-lg bg-[var(--cyan)]/10 flex items-center justify-center shrink-0">
                            {getTradeIcon(trade.iconName)}
                          </div>
                          <div className="min-w-0 flex-1">
                            <div className="text-xs font-bold text-[var(--tx)] truncate">{trade.name}</div>
                            <div className="text-[10px] text-[var(--tx-3)] flex items-center gap-1.5">
                              <span className="text-[var(--cyan)] font-medium">{trade.category}</span>
                              <span>&bull;</span>
                              <span>Avg. ₵{trade.defaultRate}/hr</span>
                            </div>
                          </div>
                          {isSelected && <Check className="w-3.5 h-3.5 text-[var(--cyan)] shrink-0" />}
                        </button>
                      );
                    })
                  ) : (
                    <div className="col-span-full py-4 px-3 text-center text-xs text-[var(--tx-3)] bg-[var(--surface)] rounded-[16px] border border-dashed border-[var(--bd2)]">
                      <p className="font-semibold text-[var(--tx)]">No preset trades matching "{tradeSearchQuery}"</p>
                      <p className="text-[11px] mt-1 text-[var(--tx-2)]">
                        Click the <span className="text-[var(--cyan)] font-bold">"Use Typed Occupation"</span> button above to register with this custom profession.
                      </p>
                    </div>
                  )}
                </div>
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
                  className="w-full h-12 px-3.5 bg-[var(--surface)] text-[var(--tx)] text-xs font-semibold rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
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
                    className="w-full h-12 pl-8 pr-12 bg-[var(--surface)] text-[var(--tx)] text-sm font-bold rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
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
                    className={`p-3.5 rounded-[18px] border flex flex-col items-center justify-center gap-1.5 transition-all ${
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
                className="w-full h-12 px-4 bg-[var(--surface)] text-[var(--tx)] text-sm font-mono font-bold rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
              />
            </div>

            {/* Instant verification assurance banner */}
            <div className="rounded-[18px] p-4 bg-gradient-to-r from-[#10B981]/10 to-[var(--cyan)]/10 border border-[#10B981]/25 flex items-start gap-2.5">
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
                className="w-full h-12 px-4 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
              />
            </div>

            <div className="space-y-2">
              <label className="text-xs font-bold text-[var(--tx)]">
                What is your immediate hiring objective?
              </label>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <button
                  type="button"
                  onClick={() => setProjectIntent('hire_artisan')}
                  className={`p-3.5 rounded-[18px] border text-left transition-all ${
                    projectIntent === 'hire_artisan'
                      ? 'border-[var(--cyan)] bg-[var(--cyan)]/[0.08] shadow-xs'
                      : 'border-[var(--bd2)] bg-[var(--surface)] hover:border-[var(--bd)]'
                  }`}
                >
                  <div className="text-xs font-bold text-[var(--tx)] mb-0.5 flex items-center gap-1.5">
                    <Search className="w-3.5 h-3.5 text-[var(--cyan)]" />
                    <span>Browse &amp; Direct Hire</span>
                  </div>
                  <div className="text-[11px] text-[var(--tx-3)]">Look through Ghana Card verified profiles and message them.</div>
                </button>

                <button
                  type="button"
                  onClick={() => setProjectIntent('post_job')}
                  className={`p-3.5 rounded-[18px] border text-left transition-all ${
                    projectIntent === 'post_job'
                      ? 'border-[#F59E0B] bg-[#F59E0B]/[0.08] shadow-xs'
                      : 'border-[var(--bd2)] bg-[var(--surface)] hover:border-[var(--bd)]'
                  }`}
                >
                  <div className="text-xs font-bold text-[var(--tx)] mb-0.5 flex items-center gap-1.5">
                    <FileText className="w-3.5 h-3.5 text-[#F59E0B]" />
                    <span>Post a Project Brief</span>
                  </div>
                  <div className="text-[11px] text-[var(--tx-3)]">Receive competitive Cedi proposals from top local masters within 15 min.</div>
                </button>
              </div>
            </div>

            {/* Escrow assurance note */}
            <div className="rounded-[18px] p-4 bg-gradient-to-r from-[var(--cyan)]/10 to-[#3B82F6]/10 border border-[var(--cyan)]/25 flex items-start gap-2.5">
              <ShieldCheck className="w-4 h-4 text-[var(--cyan)] shrink-0 mt-0.5" />
              <div className="text-xs text-[var(--tx-2)] leading-relaxed">
                <strong className="text-[var(--tx)]">Zero Upfront Risk:</strong> Your milestone deposits are safely locked in the Bank-Grade Escrow Vault until you inspect and approve the completed work.
              </div>
            </div>
          </div>
        )}

        {/* Error message alert */}
        {errorMsg && (
          <div className="p-3.5 rounded-[16px] bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
            <AlertCircle className="w-4 h-4 shrink-0" />
            <span>{errorMsg}</span>
          </div>
        )}

        {/* ══════ ACTION NAVIGATION BUTTONS ══════ */}
        <div className="pt-3 flex items-center justify-between gap-3">
          {step > 1 ? (
            <button
              type="button"
              onClick={handlePrevStep}
              className="h-12 px-5 rounded-[18px] border border-[var(--bd2)] hover:border-[var(--bd)] text-[var(--tx-2)] hover:text-[var(--tx)] font-bold text-xs flex items-center gap-1.5 transition-all"
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
            className={`h-12 px-7 rounded-[18px] font-black text-sm flex items-center gap-2 shadow-lg transition-all ml-auto ${
              role === 'client'
                ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] hover:from-[#D97706] hover:to-[#B45309] text-white shadow-amber-500/20'
                : 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] hover:from-[#00B4A9] hover:to-[#008B82] text-white shadow-cyan-500/20'
            }`}
          >
            {isSubmitting ? (
              <>
                <span className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
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
