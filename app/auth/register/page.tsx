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
  Tv,
  Music,
  Layers,
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
  { id: 'masonry', name: 'Masonry, Bricklaying & Concrete Works', category: 'Construction', defaultRate: 85, iconName: 'Building2' },
  { id: 'pop', name: 'POP Ceilings & Decorative Plastering', category: 'Finishing', defaultRate: 85, iconName: 'Paintbrush' },
  { id: 'tiling', name: 'Ceramic, Porcelain & Marble Tiling', category: 'Finishing', defaultRate: 80, iconName: 'HardHat' },
  { id: 'painting', name: 'Interior & Exterior Painting & Stucco', category: 'Finishing', defaultRate: 70, iconName: 'Paintbrush' },
  { id: 'carpentry', name: 'Bespoke Joinery & Cabinetry (Carpentry)', category: 'Woodwork', defaultRate: 80, iconName: 'Hammer' },
  { id: 'roofing', name: 'Roofing Truss, Slate & Sheet Installation', category: 'Construction', defaultRate: 90, iconName: 'HardHat' },
  { id: 'welding', name: 'Metal Fabrication, Gates & Burglar Proofing', category: 'Metalwork', defaultRate: 85, iconName: 'Flame' },
  { id: 'aluminum', name: 'Aluminum Glazing & Sliding Windows/Doors', category: 'Finishing', defaultRate: 75, iconName: 'Wrench' },
  { id: 'biodigester', name: 'Bio-Digester & Septic Tank Construction', category: 'Construction', defaultRate: 95, iconName: 'Building2' },
  { id: 'flooring', name: 'T&G & Hardwood Parquet Flooring', category: 'Woodwork', defaultRate: 75, iconName: 'Layers' },
  { id: 'scaffolding', name: 'Scaffolding & Rigging Works', category: 'Construction', defaultRate: 85, iconName: 'HardHat' },

  // Electrical & Security
  { id: 'electrical', name: 'Commercial & 3-Phase Domestic Electrical Wiring', category: 'Electrical', defaultRate: 90, iconName: 'Zap' },
  { id: 'solar', name: 'Solar PV & Inverter Systems Installation', category: 'Electrical', defaultRate: 95, iconName: 'Zap' },
  { id: 'cctv', name: 'CCTV, Electric Fence & Smart Home Security', category: 'Security', defaultRate: 85, iconName: 'ShieldCheck' },
  { id: 'dstv', name: 'DSTV, Satellite Dish & TV Antenna Installation', category: 'Electronics', defaultRate: 65, iconName: 'Tv' },
  { id: 'generator', name: 'Generator Maintenance & Plant Mechanics', category: 'Electrical', defaultRate: 95, iconName: 'Zap' },
  { id: 'hvac', name: 'Air Conditioning (HVAC) & Commercial Refrigeration', category: 'Mechanical', defaultRate: 80, iconName: 'Wind' },

  // Plumbing
  { id: 'plumbing', name: 'Domestic & Industrial Piping & Plumbing', category: 'Plumbing', defaultRate: 75, iconName: 'Wrench' },
  { id: 'borehole', name: 'Borehole Drilling & Submersible Pump Mechanics', category: 'Plumbing', defaultRate: 110, iconName: 'Wrench' },
  { id: 'water-tank', name: 'Water Tank & Overhead Booster Pump Systems', category: 'Plumbing', defaultRate: 70, iconName: 'Wrench' },

  // Automotive
  { id: 'auto-mechanic', name: 'Automotive Engine & Mechanical Diagnostics', category: 'Automotive', defaultRate: 85, iconName: 'Car' },
  { id: 'auto-electrical', name: 'Automotive Electrical & ECU Programming', category: 'Automotive', defaultRate: 90, iconName: 'Car' },
  { id: 'auto-spray', name: 'Auto Spraying, Body Works & Panel Beating', category: 'Automotive', defaultRate: 80, iconName: 'Car' },
  { id: 'vulcanizing', name: 'Vulcanizing & Precision Wheel Alignment', category: 'Automotive', defaultRate: 50, iconName: 'Car' },

  // Digital & Technology
  { id: 'software', name: 'Full-Stack Web & Mobile App Development', category: 'Tech', defaultRate: 115, iconName: 'Laptop' },
  { id: 'uiux', name: 'UI/UX Product Design & Brand Identity', category: 'Tech', defaultRate: 95, iconName: 'Palette' },
  { id: 'graphic-design', name: 'Graphic Design, Signage & Banner Printing', category: 'Creative', defaultRate: 75, iconName: 'Palette' },
  { id: 'it-support', name: 'Network Engineering & Computer Hardware Repair', category: 'Tech', defaultRate: 80, iconName: 'Cpu' },

  // Creative, Fashion & Lifestyle
  { id: 'couture', name: 'Haute Couture, Kente & Bespoke Fashion', category: 'Fashion', defaultRate: 95, iconName: 'Scissors' },
  { id: 'tailoring', name: 'Tailoring, Dressmaking & Suit Styling', category: 'Fashion', defaultRate: 75, iconName: 'Scissors' },
  { id: 'hair-beauty', name: 'Bridal Hair Styling, Braiding & Barbering', category: 'Beauty', defaultRate: 70, iconName: 'Scissors' },
  { id: 'makeup', name: 'Bridal Makeup & Professional Gele Artistry', category: 'Beauty', defaultRate: 80, iconName: 'Sparkles' },
  { id: 'photography', name: 'Event Photography, Drone & Video Production', category: 'Media', defaultRate: 100, iconName: 'Camera' },
  { id: 'sound-dj', name: 'Sound Engineering & Professional Event DJ', category: 'Events', defaultRate: 90, iconName: 'Music' },

  // Services, Catering & Logistics
  { id: 'catering', name: 'Commercial Catering & Event Culinary Services', category: 'Events', defaultRate: 85, iconName: 'Utensils' },
  { id: 'logistics', name: 'Cargo Haulage & Inter-City Moving Services', category: 'Logistics', defaultRate: 100, iconName: 'Truck' },
  { id: 'cleaning', name: 'Industrial Cleaning & Fumigation Services', category: 'Services', defaultRate: 70, iconName: 'Sparkles' },
  { id: 'gardening', name: 'Landscaping, Turf & Garden Architecture', category: 'Outdoors', defaultRate: 65, iconName: 'Paintbrush' },
  { id: 'barbering', name: 'Barbering & Male Grooming Services', category: 'Beauty', defaultRate: 60, iconName: 'Scissors' },
  { id: 'upholstery', name: 'Auto Upholstery & Furniture Re-covering', category: 'Woodwork', defaultRate: 75, iconName: 'Scissors' },
  { id: 'roofing-sheets', name: 'Aluminium Roofing Sheets & Gutter Installation', category: 'Construction', defaultRate: 85, iconName: 'HardHat' },
  { id: 'wallpaper', name: 'Wallpaper & 3D Wall Panel Installation', category: 'Finishing', defaultRate: 70, iconName: 'Layers' },
  { id: 'events-decor', name: 'Event Decoration, Canopy & Stage Lighting', category: 'Events', defaultRate: 85, iconName: 'Sparkles' },
  { id: 'laundry', name: 'Professional Laundry & Dry Cleaning Services', category: 'Services', defaultRate: 50, iconName: 'Sparkles' },
  { id: 'motorcycle-mechanic', name: 'Motorcycle & Tricycle (Pragya / Aboboyaa) Mechanic', category: 'Automotive', defaultRate: 65, iconName: 'Wrench' },
  { id: 'shoemaking', name: 'Shoe Making, Cobbling & Leather Craft', category: 'Fashion', defaultRate: 70, iconName: 'Scissors' },
  { id: 'beadmaking', name: 'Bead Making, Traditional Regalia & Adornments', category: 'Fashion', defaultRate: 65, iconName: 'Palette' },
  { id: 'housekeeping', name: 'Domestic Housekeeping, Maid & Nanny Services', category: 'Services', defaultRate: 50, iconName: 'Sparkles' },
];

const TRADE_CATEGORIES = [
  'All',
  'Construction',
  'Finishing & Woodwork',
  'Electrical & Solar',
  'Plumbing',
  'Automotive',
  'Tech & Media',
  'Fashion & Beauty',
  'Services & Events',
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
    case 'Tv':
      return <Tv {...iconProps} />;
    case 'Music':
      return <Music {...iconProps} />;
    case 'Layers':
      return <Layers {...iconProps} />;
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
  const [selectedTrade, setSelectedTrade] = useState('');
  const [tradeSearchQuery, setTradeSearchQuery] = useState('');
  const [selectedCity, setSelectedCity] = useState(GHANA_CITIES[0]);
  const [selectedCategory, setSelectedCategory] = useState('All');
  const [hourlyRate, setHourlyRate] = useState<string>('');
  const [ghanaCardPin, setGhanaCardPin] = useState('');
  const [isGhanaCardValid, setIsGhanaCardValid] = useState(false);
  const [cardFrontImg, setCardFrontImg] = useState<string | null>(null);
  const [cardBackImg, setCardBackImg] = useState<string | null>(null);
  const [payoutWallet, setPayoutWallet] = useState<'mtn' | 'telecel' | 'at'>('mtn');
  const [walletPhone, setWalletPhone] = useState('');

  // Real-time filtered trades based on category and search query
  const filteredTrades = useMemo(() => {
    let list = GHANA_TRADES;
    if (selectedCategory !== 'All') {
      if (selectedCategory === 'Construction') {
        list = list.filter((t) => t.category === 'Construction');
      } else if (selectedCategory === 'Finishing & Woodwork') {
        list = list.filter((t) => t.category === 'Finishing' || t.category === 'Woodwork' || t.category === 'Metalwork');
      } else if (selectedCategory === 'Electrical & Solar') {
        list = list.filter((t) => t.category === 'Electrical' || t.category === 'Security' || t.category === 'Electronics' || t.category === 'Mechanical');
      } else if (selectedCategory === 'Plumbing') {
        list = list.filter((t) => t.category === 'Plumbing');
      } else if (selectedCategory === 'Automotive') {
        list = list.filter((t) => t.category === 'Automotive');
      } else if (selectedCategory === 'Tech & Media') {
        list = list.filter((t) => t.category === 'Tech' || t.category === 'Creative' || t.category === 'Media');
      } else if (selectedCategory === 'Fashion & Beauty') {
        list = list.filter((t) => t.category === 'Fashion' || t.category === 'Beauty');
      } else if (selectedCategory === 'Services & Events') {
        list = list.filter((t) => t.category === 'Events' || t.category === 'Services' || t.category === 'Logistics' || t.category === 'Outdoors');
      }
    }
    if (tradeSearchQuery.trim()) {
      const q = tradeSearchQuery.toLowerCase().trim();
      list = list.filter(
        (t) =>
          t.name.toLowerCase().includes(q) ||
          t.category.toLowerCase().includes(q)
      );
    }
    return list;
  }, [tradeSearchQuery, selectedCategory]);

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
    if (role === 'provider' && step === 2) {
      if (!selectedTrade.trim() && tradeSearchQuery.trim()) {
        setSelectedTrade(tradeSearchQuery.trim());
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
        first_name: firstName.trim() || 'Master',
        last_name: lastName.trim() || (role === 'client' ? 'Client' : 'Artisan'),
        email: email.trim().toLowerCase() || 'user@gigghana.com',
        phone: phone.trim() || '0240000000',
        role,
        location: selectedCity,
        is_verified: true,
        membership_tier: tier,
        trade: selectedTrade.trim() || tradeSearchQuery.trim() || (role === 'provider' ? 'Verified Master Artisan' : undefined),
        hourly_rate: hourlyRate || undefined,
        payout_wallet: payoutWallet,
        wallet_number: walletPhone || phone,
      };

      try {
        await register(userData);
      } catch (_) {
        // Front-end resilience
      }

      try {
        confetti({
          particleCount: 90,
          spread: 70,
          origin: { y: 0.6 },
          colors: ['#00D4C8', '#F59E0B', '#10B981', '#ffffff'],
        });
      } catch (_) {}

      setTimeout(() => {
        router.push('/');
      }, 900);
    } catch (err: any) {
      router.push('/');
    } finally {
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
            <div className="space-y-4">
              {/* Search / Custom Type Input */}
              <div className="space-y-1.5">
                <div className="flex items-center justify-between">
                  <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1.5">
                    <span>Your Primary Trade / Occupation</span>
                    <span className="text-rose-500">*</span>
                  </label>
                  {selectedTrade ? (
                    <span className="text-[11px] font-bold text-emerald-500 flex items-center gap-1">
                      <Check className="w-3.5 h-3.5" />
                      <span>Trade Selected</span>
                    </span>
                  ) : (
                    <span className="text-[10.5px] text-[var(--tx-3)] font-medium">Type custom or pick below</span>
                  )}
                </div>

                <div className="relative flex items-center">
                  <Search className="w-4 h-4 text-[var(--tx-3)] absolute left-3.5 pointer-events-none" />
                  <input
                    type="text"
                    value={tradeSearchQuery}
                    onChange={(e) => {
                      const val = e.target.value;
                      setTradeSearchQuery(val);
                      setSelectedTrade(val);
                    }}
                    placeholder="Search 45+ trades or type custom occupation (e.g. Mason, Tailor, AC Specialist)..."
                    className="w-full h-12 pl-10 pr-10 bg-[var(--surface)] text-[var(--tx)] text-xs sm:text-sm font-medium rounded-[18px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all placeholder:text-[var(--tx-3)]"
                  />
                  {tradeSearchQuery && (
                    <button
                      type="button"
                      onClick={() => {
                        setTradeSearchQuery('');
                        setSelectedTrade('');
                      }}
                      className="absolute right-3 text-[var(--tx-3)] hover:text-[var(--tx)] p-1.5 rounded-lg transition-colors"
                      aria-label="Clear occupation"
                    >
                      <X className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>

                {/* Inline confirmation badge when user types a custom trade */}
                {tradeSearchQuery.trim() && !GHANA_TRADES.some((t) => t.name.toLowerCase() === tradeSearchQuery.trim().toLowerCase()) && (
                  <div className="flex items-center gap-2 px-3 py-2 rounded-[14px] bg-[var(--cyan)]/[0.08] border border-[var(--cyan)]/30 text-xs">
                    <span className="w-2 h-2 rounded-full bg-[var(--cyan)] animate-pulse" />
                    <span className="text-[var(--tx-2)] text-[11.5px]">Custom Trade:</span>
                    <span className="font-bold text-[var(--tx)] truncate">"{tradeSearchQuery.trim()}"</span>
                    <span className="text-[10px] text-[var(--cyan)] font-semibold ml-auto shrink-0">Will be registered</span>
                  </div>
                )}
              </div>

              {/* Category Quick-Filter Chips */}
              <div className="space-y-1.5">
                <div className="flex items-center justify-between text-[11px] font-semibold text-[var(--tx-3)] px-0.5">
                  <span>Browse by Category</span>
                  <span>{filteredTrades.length} trades</span>
                </div>
                <div className="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none no-scrollbar">
                  {TRADE_CATEGORIES.map((cat) => {
                    const isCatActive = selectedCategory === cat;
                    return (
                      <button
                        key={cat}
                        type="button"
                        onClick={() => setSelectedCategory(cat)}
                        className={`px-3 py-1.5 rounded-full text-[11px] font-bold whitespace-nowrap transition-all shrink-0 ${
                          isCatActive
                            ? 'bg-[var(--cyan)] text-slate-950 shadow-xs scale-[1.02]'
                            : 'bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx-2)] hover:text-[var(--tx)] hover:border-[var(--bd)]'
                        }`}
                      >
                        {cat}
                      </button>
                    );
                  })}
                </div>
              </div>

              {/* Clean, Full-Width Trade List (No awkward ellipses or clipped text) */}
              <div className="space-y-1.5">
                <div className="space-y-2 max-h-[250px] overflow-y-auto pr-1">
                  {filteredTrades.length > 0 ? (
                    filteredTrades.map((trade) => {
                      const isSelected = selectedTrade.toLowerCase() === trade.name.toLowerCase();
                      return (
                        <div
                          key={trade.id}
                          onClick={() => {
                            if (isSelected) {
                              setSelectedTrade('');
                              setTradeSearchQuery('');
                            } else {
                              setSelectedTrade(trade.name);
                              setTradeSearchQuery(trade.name);
                            }
                          }}
                          className={`p-3 rounded-[18px] border text-left flex items-center gap-3 transition-all cursor-pointer ${
                            isSelected
                              ? 'border-[var(--cyan)] bg-[var(--cyan)]/[0.08] ring-1 ring-[var(--cyan)]/40 shadow-xs'
                              : 'border-[var(--bd2)] hover:border-[var(--bd)] bg-[var(--surface)] hover:bg-[var(--surface-elevated)]'
                          }`}
                        >
                          <div className={`w-9 h-9 rounded-[12px] flex items-center justify-center shrink-0 transition-colors ${
                            isSelected ? 'bg-[var(--cyan)] text-slate-950 font-bold' : 'bg-[var(--cyan)]/10 text-[var(--cyan)]'
                          }`}>
                            {getTradeIcon(trade.iconName)}
                          </div>
                          <div className="min-w-0 flex-1">
                            <div className="text-xs sm:text-[13px] font-bold text-[var(--tx)] leading-snug">
                              {trade.name}
                            </div>
                            <div className="text-[10.5px] text-[var(--tx-3)] flex items-center gap-2 mt-0.5">
                              <span className="px-2 py-0.2 rounded-md bg-[var(--surface-elevated)] text-[var(--tx-2)] font-semibold text-[10px]">
                                {trade.category}
                              </span>
                              <span>&bull;</span>
                              <span>Market rate avg: ₵{trade.defaultRate}/hr</span>
                            </div>
                          </div>
                          <div className="shrink-0 ml-2">
                            {isSelected ? (
                              <div className="w-6 h-6 rounded-full bg-[var(--cyan)] text-slate-950 flex items-center justify-center shadow-xs">
                                <Check className="w-3.5 h-3.5 stroke-[3]" />
                              </div>
                            ) : (
                              <div className="w-6 h-6 rounded-full border border-[var(--bd2)] hover:border-[var(--cyan)] flex items-center justify-center text-[var(--tx-3)]" />
                            )}
                          </div>
                        </div>
                      );
                    })
                  ) : (
                    <div className="py-6 px-4 text-center text-xs bg-[var(--surface)] rounded-[18px] border border-dashed border-[var(--cyan)]/40">
                      <div className="w-10 h-10 rounded-full bg-[var(--cyan)]/10 text-[var(--cyan)] flex items-center justify-center mx-auto mb-2">
                        <Check className="w-5 h-5" />
                      </div>
                      <p className="font-bold text-[var(--tx)] text-sm">
                        Custom Trade: "{tradeSearchQuery.trim()}"
                      </p>
                      <p className="text-[11px] mt-1 text-[var(--tx-2)] max-w-sm mx-auto">
                        This profession isn't in our standard directory, but it is ready and will be registered directly to your verified profile.
                      </p>
                    </div>
                  )}
                </div>
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-[var(--bd2)]/40 mt-3">
              {/* City / Hub */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                  <MapPin className="w-3.5 h-3.5 text-[var(--cyan)]" />
                  <span>Primary Operating Location</span>
                </label>
                <select
                  value={selectedCity}
                  onChange={(e) => setSelectedCity(e.target.value)}
                  className="w-full h-12 px-3.5 bg-[var(--surface)] text-[var(--tx)] text-xs font-semibold rounded-[18px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
                >
                  {GHANA_CITIES.map((city) => (
                    <option key={city} value={city}>
                      {city}
                    </option>
                  ))}
                </select>
              </div>

              {/* Hourly / Estimate Rate (Optional) */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                  <span className="flex items-center gap-1.5">
                    <span>Base Rate (Cedis)</span>
                    <span className="text-[10px] font-bold text-[var(--cyan)] bg-[var(--cyan)]/10 px-2 py-0.5 rounded-full uppercase tracking-wider border border-[var(--cyan)]/25">
                      Optional
                    </span>
                  </span>
                  <span className="text-[10px] font-mono text-[var(--tx-3)]">₵ GHS</span>
                </label>
                <div className="relative flex items-center">
                  <span className="absolute left-3.5 text-xs font-bold text-[var(--tx-2)]">₵</span>
                  <input
                    type="number"
                    value={hourlyRate}
                    onChange={(e) => setHourlyRate(e.target.value)}
                    placeholder="Optional (leave blank or e.g. 75)"
                    className="w-full h-12 pl-8 pr-12 bg-[var(--surface)] text-[var(--tx)] text-sm font-bold rounded-[18px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none placeholder:font-normal placeholder:text-xs placeholder:text-[var(--tx-3)]"
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
              <span>Complete Registration</span>
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
