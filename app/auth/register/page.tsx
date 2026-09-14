'use client';

import React, { useState, useEffect, Suspense, useMemo, useRef } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { AuthLayout } from '@/components/auth/AuthLayout';
import { PhoneInput, TelecomNetwork } from '@/components/ui/phone-input';
import { GhanaCardInput } from '@/components/ui/ghana-card-input';
import { useAuth } from '@/lib/context/AuthContext';
import confetti from 'canvas-confetti';
import {
  Check,
  ArrowRight,
  ArrowLeft,
  Lock,
  Eye,
  EyeOff,
  ShieldCheck,
  Smartphone,
  CheckCircle2,
  BadgeCheck,
  Star,
  MapPin,
  Search,
  X,
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



export interface GhanaLocation {
  city: string;
  region: string;
  full: string;
  popular?: boolean;
}

export const POPULAR_GHANA_LOCATIONS: GhanaLocation[] = [
  // Greater Accra Hubs
  { city: 'East Legon', region: 'Accra, Greater Accra', full: 'East Legon, Accra', popular: true },
  { city: 'Spintex Road', region: 'Accra, Greater Accra', full: 'Spintex Road, Accra', popular: true },
  { city: 'Airport Hills & Residential', region: 'Accra, Greater Accra', full: 'Airport Hills, Accra', popular: true },
  { city: 'Osu (Oxford Street / RE)', region: 'Accra, Greater Accra', full: 'Osu, Accra', popular: true },
  { city: 'Cantonments & Labone', region: 'Accra, Greater Accra', full: 'Cantonments, Accra', popular: true },
  { city: 'Dzorwulu & Roman Ridge', region: 'Accra, Greater Accra', full: 'Dzorwulu, Accra', popular: true },
  { city: 'Tema (Communities 1 - 25)', region: 'Greater Accra', full: 'Tema Industrial, Greater Accra', popular: true },
  { city: 'Madina & Ashaley Botwe', region: 'Greater Accra', full: 'Madina, Greater Accra', popular: true },
  { city: 'Adenta & Frafraha', region: 'Greater Accra', full: 'Adenta, Greater Accra', popular: true },
  { city: 'Dansoman & Exhibition', region: 'Accra, Greater Accra', full: 'Dansoman, Accra', popular: true },
  { city: 'Lapaz & Abeka', region: 'Accra, Greater Accra', full: 'Lapaz, Accra', popular: true },
  { city: 'Achimota & Mile 7', region: 'Accra, Greater Accra', full: 'Achimota, Accra', popular: true },
  { city: 'Dome & Kwabenya', region: 'Greater Accra', full: 'Dome, Greater Accra', popular: true },
  { city: 'Haatso & Agbogba', region: 'Greater Accra', full: 'Haatso, Greater Accra' },
  { city: 'Kwashieman & Santa Maria', region: 'Accra, Greater Accra', full: 'Kwashieman, Accra' },
  { city: 'Kwame Nkrumah Circle & Adabraka', region: 'Accra, Greater Accra', full: 'Circle, Accra' },
  { city: 'Kaneshie & Odorkor', region: 'Accra, Greater Accra', full: 'Kaneshie, Accra' },
  { city: 'Weija & SCC', region: 'Greater Accra', full: 'Weija, Greater Accra' },
  { city: 'Teshie & Nungua Estates', region: 'Greater Accra', full: 'Teshie, Greater Accra' },
  { city: 'Prampram & Dawhenya', region: 'Greater Accra', full: 'Prampram, Greater Accra' },
  { city: 'Amasaman & Pokuase', region: 'Greater Accra', full: 'Pokuase, Greater Accra' },
  { city: 'Kasoa & Amanfro', region: 'Central / Greater Accra Border', full: 'Kasoa, Central/Accra', popular: true },

  // Ashanti Hubs
  { city: 'Kumasi Central (Adum)', region: 'Kumasi, Ashanti', full: 'Kumasi Central, Ashanti', popular: true },
  { city: 'Bantama & Abrepo', region: 'Kumasi, Ashanti', full: 'Bantama, Kumasi', popular: true },
  { city: 'Ahodwo & Nhyiaeso', region: 'Kumasi, Ashanti', full: 'Ahodwo, Kumasi', popular: true },
  { city: 'KNUST Campus & Ayigya', region: 'Kumasi, Ashanti', full: 'KNUST, Kumasi', popular: true },
  { city: 'Suame (Magazine) & Tafo', region: 'Kumasi, Ashanti', full: 'Suame, Kumasi', popular: true },
  { city: 'Asokwa & Atonsu', region: 'Kumasi, Ashanti', full: 'Asokwa, Kumasi' },
  { city: 'Kwadaso & Sofoline', region: 'Kumasi, Ashanti', full: 'Kwadaso, Kumasi' },
  { city: 'Oforikrom & Anloga', region: 'Kumasi, Ashanti', full: 'Oforikrom, Kumasi' },
  { city: 'Tanoso & Abuakwa', region: 'Kumasi, Ashanti', full: 'Tanoso, Kumasi' },
  { city: 'Obuasi (Gold City)', region: 'Ashanti Region', full: 'Obuasi, Ashanti', popular: true },
  { city: 'Ejisu & Fumesua', region: 'Ashanti Region', full: 'Ejisu, Ashanti' },

  // Western & Western North Hubs
  { city: 'Takoradi (Market Circle)', region: 'Sekondi-Takoradi, Western', full: 'Takoradi, Western', popular: true },
  { city: 'Sekondi & Essikado', region: 'Sekondi-Takoradi, Western', full: 'Sekondi, Western' },
  { city: 'Anaji & Effia Kuma', region: 'Sekondi-Takoradi, Western', full: 'Anaji, Takoradi' },
  { city: 'Tarkwa (Mining Hub)', region: 'Western Region', full: 'Tarkwa, Western', popular: true },
  { city: 'Sefwi Wiawso & Bibiani', region: 'Western North Region', full: 'Sefwi Wiawso, Western North' },

  // Central Region Hubs
  { city: 'Cape Coast (Kotokuraba / UCC)', region: 'Central Region', full: 'Cape Coast, Central', popular: true },
  { city: 'Winneba (University Town)', region: 'Central Region', full: 'Winneba, Central', popular: true },
  { city: 'Elmina & Komenda', region: 'Central Region', full: 'Elmina, Central' },
  { city: 'Agona Swedru', region: 'Central Region', full: 'Swedru, Central' },
  { city: 'Mankessim (Trade Hub)', region: 'Central Region', full: 'Mankessim, Central' },

  // Eastern Region Hubs
  { city: 'Koforidua (New Juaben)', region: 'Eastern Region', full: 'Koforidua, Eastern', popular: true },
  { city: 'Nsawam & Adoagyiri', region: 'Eastern Region', full: 'Nsawam, Eastern' },
  { city: 'Nkawkaw & Kwahu Plateau', region: 'Eastern Region', full: 'Nkawkaw, Eastern' },
  { city: 'Akosombo & Atimpoku', region: 'Eastern Region', full: 'Akosombo, Eastern' },
  { city: 'Aburi & Mampong Ridge', region: 'Eastern Region', full: 'Aburi, Eastern' },

  // Northern, Savannah & North East Hubs
  { city: 'Tamale Central & Lamashegu', region: 'Northern Region', full: 'Tamale, Northern', popular: true },
  { city: 'Nyankpala & Sagnarigu', region: 'Northern Region', full: 'Sagnarigu, Tamale' },
  { city: 'Yendi & Bimbilla', region: 'Northern Region', full: 'Yendi, Northern' },
  { city: 'Damongo (Mole Gateway)', region: 'Savannah Region', full: 'Damongo, Savannah' },
  { city: 'Nalerigu & Walewale', region: 'North East Region', full: 'Nalerigu, North East' },

  // Volta & Oti Hubs
  { city: 'Ho (Civic Centre & Barracks)', region: 'Volta Region', full: 'Ho, Volta', popular: true },
  { city: 'Hohoe & Kpando', region: 'Volta Region', full: 'Hohoe, Volta' },
  { city: 'Aflao & Denu (Border Hub)', region: 'Volta Region', full: 'Aflao, Volta', popular: true },
  { city: 'Keta & Anloga', region: 'Volta Region', full: 'Keta, Volta' },
  { city: 'Dambai & Nkwanta', region: 'Oti Region', full: 'Dambai, Oti' },

  // Upper East & Upper West Hubs
  { city: 'Bolgatanga Central', region: 'Upper East Region', full: 'Bolgatanga, Upper East', popular: true },
  { city: 'Navrongo & Paga', region: 'Upper East Region', full: 'Navrongo, Upper East' },
  { city: 'Wa Central & Campus', region: 'Upper West Region', full: 'Wa, Upper West', popular: true },

  // Bono, Bono East & Ahafo Hubs
  { city: 'Sunyani Central & Fiapre', region: 'Bono Region', full: 'Sunyani, Bono', popular: true },
  { city: 'Techiman (Commercial Market)', region: 'Bono East Region', full: 'Techiman, Bono East', popular: true },
  { city: 'Berekum & Dormaa', region: 'Bono Region', full: 'Berekum, Bono' },
  { city: 'Goaso & Kenyasi', region: 'Ahafo Region', full: 'Goaso, Ahafo' },
];

export const GHANA_CITIES = POPULAR_GHANA_LOCATIONS.map((loc) => loc.full);

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

  // Stepper: 1 to 5 for Provider, 1 to 3 for Client
  const [step, setStep] = useState(1);
  const totalSteps = role === 'provider' ? 5 : 3;

  // SMS Verification State
  const [otpDigits, setOtpDigits] = useState(['', '', '', '', '', '']);
  const [resendTimer, setResendTimer] = useState(30);
  const [codeResentNotice, setCodeResentNotice] = useState(false);

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
  const [selectedCity, setSelectedCity] = useState(POPULAR_GHANA_LOCATIONS[0].full);
  const [locationQuery, setLocationQuery] = useState(POPULAR_GHANA_LOCATIONS[0].full);
  const [isLocationDropdownOpen, setIsLocationDropdownOpen] = useState(false);
  const locationRef = useRef<HTMLDivElement>(null);
  const [hourlyRate, setHourlyRate] = useState<string>('');
  const [ghanaCardPin, setGhanaCardPin] = useState('');
  const [isGhanaCardValid, setIsGhanaCardValid] = useState(false);
  const [cardFrontImg, setCardFrontImg] = useState<string | null>(null);
  const [cardBackImg, setCardBackImg] = useState<string | null>(null);
  const [payoutWallet, setPayoutWallet] = useState<'mtn' | 'telecel' | 'at'>('mtn');
  const [walletPhone, setWalletPhone] = useState('');

  // Real-time filtered trades based on user typing in search bar
  const filteredTrades = useMemo(() => {
    if (!tradeSearchQuery.trim()) {
      return GHANA_TRADES;
    }
    const q = tradeSearchQuery.toLowerCase().trim();
    return GHANA_TRADES.filter(
      (t) =>
        t.name.toLowerCase().includes(q) ||
        t.category.toLowerCase().includes(q)
    );
  }, [tradeSearchQuery]);

  // Real-time filtered locations (Facebook Location Autocomplete style)
  const filteredLocations = useMemo(() => {
    const q = locationQuery.toLowerCase().trim();
    if (!q) {
      return POPULAR_GHANA_LOCATIONS.filter((l) => l.popular);
    }
    return POPULAR_GHANA_LOCATIONS.filter(
      (l) =>
        l.city.toLowerCase().includes(q) ||
        l.region.toLowerCase().includes(q) ||
        l.full.toLowerCase().includes(q)
    );
  }, [locationQuery]);

  const hasExactLocationMatch = useMemo(() => {
    const q = locationQuery.toLowerCase().trim();
    if (!q) return true;
    return POPULAR_GHANA_LOCATIONS.some(
      (l) => l.city.toLowerCase() === q || l.full.toLowerCase() === q
    );
  }, [locationQuery]);

  // Dismiss location autocomplete when clicking outside
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (locationRef.current && !locationRef.current.contains(event.target as Node)) {
        setIsLocationDropdownOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);

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

  // SMS Resend Countdown Timer
  useEffect(() => {
    if (step === totalSteps && resendTimer > 0) {
      const timer = setInterval(() => {
        setResendTimer((prev) => (prev > 0 ? prev - 1 : 0));
      }, 1000);
      return () => clearInterval(timer);
    }
  }, [step, totalSteps, resendTimer]);

  const handleNextStep = () => {
    setErrorMsg('');

    // Step 1 validation
    if (step === 1) {
      if (!firstName.trim()) {
        setErrorMsg('Please enter your first name.');
        return;
      }
      if (!email.trim() || !email.includes('@')) {
        setErrorMsg('Please enter a valid email address.');
        return;
      }
      if (!phone.trim() || phone.replace(/\D/g, '').length < 9) {
        setErrorMsg('Please enter a valid Ghanaian mobile phone number to receive your SMS code.');
        return;
      }
      if (!password || password.length < 6) {
        setErrorMsg('Password must be at least 6 characters.');
        return;
      }
    }

    // Provider step 2 trade and location assignment
    if (role === 'provider' && step === 2) {
      if (!selectedTrade.trim() && tradeSearchQuery.trim()) {
        setSelectedTrade(tradeSearchQuery.trim());
      }
      if (locationQuery.trim()) {
        setSelectedCity(locationQuery.trim());
      } else if (!selectedCity.trim()) {
        setSelectedCity('East Legon, Accra');
        setLocationQuery('East Legon, Accra');
      }
    }

    // Final SMS verification check
    if (step === totalSteps) {
      const fullCode = otpDigits.join('');
      if (fullCode.length !== 6) {
        setErrorMsg('Please enter the full 6-digit SMS verification code dispatched to your phone.');
        return;
      }
      handleFinalSubmit();
      return;
    }

    if (step < totalSteps) {
      setStep(step + 1);
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
        phone_verified: true,
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
        if (role === 'provider') {
          router.push('/provider/dashboard');
        } else {
          router.push('/');
        }
      }, 900);
    } catch (err: any) {
      if (role === 'provider') {
        router.push('/provider/dashboard');
      } else {
        router.push('/');
      }
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
                  : step === 4
                  ? 'Payout Setup'
                  : 'SMS Verification'
                : step === 1
                ? 'Contact Info'
                : step === 2
                ? 'Project Intent'
                : 'SMS Verification'}
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
            <div className="space-y-3.5">
              {/* Header & Search Bar */}
              <div className="space-y-1.5">
                <div className="flex items-center justify-between">
                  <label className="text-xs font-bold text-[var(--tx)]">
                    Your Trade or Occupation
                  </label>
                  {selectedTrade ? (
                    <span className="text-[11px] font-bold text-[var(--cyan)] bg-[var(--cyan)]/10 px-2.5 py-0.5 rounded-full border border-[var(--cyan)]/25 flex items-center gap-1">
                      <Check className="w-3 h-3 stroke-[3]" />
                      <span className="truncate max-w-[150px]">{selectedTrade}</span>
                    </span>
                  ) : (
                    <span className="text-[11px] text-[var(--tx-3)] font-medium">
                      Type or select below
                    </span>
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
                    placeholder="Search or type custom occupation (e.g. Mason, Plumber, Tailor, AC Repairer)..."
                    className="w-full h-12 pl-10 pr-9 bg-[var(--surface)] text-[var(--tx)] text-xs sm:text-sm font-medium rounded-[18px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all placeholder:text-[var(--tx-3)]"
                  />
                  {tradeSearchQuery && (
                    <button
                      type="button"
                      onClick={() => {
                        setTradeSearchQuery('');
                        setSelectedTrade('');
                      }}
                      className="absolute right-3 text-[var(--tx-3)] hover:text-[var(--tx)] p-1 rounded-md transition-colors"
                      aria-label="Clear search"
                    >
                      <X className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>

                <div className="text-[11px] text-[var(--tx-3)] flex items-center justify-between px-0.5">
                  <span>Type any trade above, or choose from our verified Ghanaian directory below.</span>
                  {tradeSearchQuery && (
                    <button
                      type="button"
                      onClick={() => {
                        setTradeSearchQuery('');
                      }}
                      className="text-[10.5px] text-[var(--cyan)] font-bold hover:underline shrink-0 ml-2"
                    >
                      Clear
                    </button>
                  )}
                </div>
              </div>

              {/* Occupations Directory List */}
              <div className="space-y-2 max-h-[290px] overflow-y-auto pr-1">
                {filteredTrades.length > 0 ? (
                  filteredTrades.map((trade) => {
                    const isSelected = selectedTrade.toLowerCase() === trade.name.toLowerCase();
                    return (
                      <button
                        key={trade.id}
                        type="button"
                        onClick={() => {
                          setSelectedTrade(trade.name);
                          setTradeSearchQuery(trade.name);
                        }}
                        className={`w-full p-3.5 rounded-[18px] border text-left flex items-center justify-between gap-3 transition-all cursor-pointer ${
                          isSelected
                            ? 'border-[var(--cyan)] bg-[var(--cyan)]/[0.08] ring-1 ring-[var(--cyan)]/40 shadow-xs'
                            : 'border-[var(--bd2)] hover:border-[var(--bd)] bg-[var(--surface)] hover:bg-[var(--surface-elevated)]'
                        }`}
                      >
                        <div className="min-w-0 flex-1">
                          <div className="text-xs sm:text-sm font-bold text-[var(--tx)] leading-snug">
                            {trade.name}
                          </div>
                          <div className="text-[11px] text-[var(--tx-3)] flex items-center gap-2 mt-0.5">
                            <span className="font-semibold text-[var(--cyan)]">{trade.category}</span>
                            <span>•</span>
                            <span>Market avg. ₵{trade.defaultRate}/hr</span>
                          </div>
                        </div>

                        <div className="shrink-0 ml-1">
                          {isSelected ? (
                            <div className="w-5 h-5 rounded-full bg-[var(--cyan)] text-slate-950 flex items-center justify-center">
                              <Check className="w-3.5 h-3.5 stroke-[3]" />
                            </div>
                          ) : (
                            <div className="w-5 h-5 rounded-full border border-[var(--bd2)]" />
                          )}
                        </div>
                      </button>
                    );
                  })
                ) : (
                  <div className="p-4 text-center text-xs bg-[var(--surface)] rounded-[18px] border border-dashed border-[var(--cyan)]/40">
                    <div className="font-bold text-[var(--tx)] text-sm mb-1 flex items-center justify-center gap-1.5">
                      <Check className="w-4 h-4 text-[var(--cyan)]" />
                      <span>Custom Trade: "{tradeSearchQuery.trim()}"</span>
                    </div>
                    <p className="text-[11px] text-[var(--tx-3)]">
                      Not in preset directory — this will be registered directly as your official trade on GigGhana.
                    </p>
                  </div>
                )}
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
              {/* City / Hub Autocomplete (Facebook Location style) */}
              <div className="space-y-1.5 relative" ref={locationRef}>
                <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                  <span className="flex items-center gap-1.5">
                    <MapPin className="w-3.5 h-3.5 text-[var(--cyan)]" />
                    <span>Primary Operating Location</span>
                  </span>
                  {selectedCity && (
                    <span className="text-[10px] font-semibold text-[var(--cyan)] bg-[var(--cyan)]/10 px-2 py-0.5 rounded-full border border-[var(--cyan)]/25 truncate max-w-[130px]">
                      {selectedCity}
                    </span>
                  )}
                </label>

                <div className="relative flex items-center">
                  <MapPin className="w-4 h-4 text-[var(--tx-3)] absolute left-3.5 pointer-events-none" />
                  <input
                    type="text"
                    value={locationQuery}
                    onFocus={() => setIsLocationDropdownOpen(true)}
                    onChange={(e) => {
                      const val = e.target.value;
                      setLocationQuery(val);
                      setSelectedCity(val);
                      setIsLocationDropdownOpen(true);
                    }}
                    onKeyDown={(e) => {
                      if (e.key === 'Escape') {
                        setIsLocationDropdownOpen(false);
                      } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (filteredLocations.length > 0) {
                          const topLoc = filteredLocations[0];
                          setSelectedCity(topLoc.full);
                          setLocationQuery(topLoc.full);
                          setIsLocationDropdownOpen(false);
                        } else if (locationQuery.trim()) {
                          setSelectedCity(locationQuery.trim());
                          setIsLocationDropdownOpen(false);
                        }
                      }
                    }}
                    placeholder="Type city, suburb or area (e.g. East Legon)..."
                    className="w-full h-12 pl-10 pr-9 bg-[var(--surface)] text-[var(--tx)] text-xs sm:text-sm font-semibold rounded-[18px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all placeholder:text-[var(--tx-3)] placeholder:font-normal"
                  />
                  {locationQuery && (
                    <button
                      type="button"
                      onClick={() => {
                        setLocationQuery('');
                        setSelectedCity('');
                        setIsLocationDropdownOpen(true);
                      }}
                      className="absolute right-3 p-1 text-[var(--tx-3)] hover:text-[var(--tx)] transition-colors cursor-pointer"
                      aria-label="Clear location input"
                    >
                      <X className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>

                {/* Facebook-style Suggestions Popover Dropdown */}
                {isLocationDropdownOpen && (
                  <div className="absolute z-50 left-0 right-0 top-[calc(100%+6px)] bg-[var(--surface-elevated)] border border-[var(--bd2)] rounded-[20px] shadow-2xl overflow-hidden backdrop-blur-xl max-h-64 overflow-y-auto divide-y divide-[var(--bd2)]/40 animate-in fade-in-50 zoom-in-95 duration-150">
                    {/* Custom Location Option (Facebook style) when user typed something that is not an exact preset match */}
                    {locationQuery.trim() && !hasExactLocationMatch && (
                      <button
                        type="button"
                        onClick={() => {
                          setSelectedCity(locationQuery.trim());
                          setIsLocationDropdownOpen(false);
                        }}
                        className="w-full px-3.5 py-2.5 text-left flex items-center gap-3 hover:bg-[var(--cyan)]/[0.08] bg-[var(--surface)] transition-colors cursor-pointer group"
                      >
                        <div className="w-8 h-8 rounded-full bg-[var(--cyan)]/15 text-[var(--cyan)] flex items-center justify-center shrink-0">
                          <MapPin className="w-4 h-4" />
                        </div>
                        <div className="min-w-0 flex-1">
                          <div className="text-xs font-bold text-[var(--tx)] group-hover:text-[var(--cyan)] truncate">
                            Use &quot;{locationQuery.trim()}&quot;
                          </div>
                          <div className="text-[10px] text-[var(--tx-3)]">
                            Register as custom Ghanaian location
                          </div>
                        </div>
                        <span className="text-[9px] font-bold text-[var(--cyan)] bg-[var(--cyan)]/10 px-2 py-0.5 rounded-full border border-[var(--cyan)]/25 shrink-0">
                          Custom
                        </span>
                      </button>
                    )}

                    {/* Category Header */}
                    <div className="px-3.5 py-1.5 bg-[var(--surface)]/60 text-[10px] font-bold uppercase tracking-wider text-[var(--tx-3)] flex items-center justify-between">
                      <span>{locationQuery.trim() ? 'Matching Locations' : 'Popular Operating Locations'}</span>
                      <span className="text-[9px] text-[var(--cyan)] font-mono font-bold">GH 🇬🇭</span>
                    </div>

                    {/* Filtered Location List */}
                    {filteredLocations.length > 0 ? (
                      filteredLocations.map((loc) => {
                        const isSelected = selectedCity === loc.full;
                        return (
                          <button
                            key={loc.full}
                            type="button"
                            onClick={() => {
                              setSelectedCity(loc.full);
                              setLocationQuery(loc.full);
                              setIsLocationDropdownOpen(false);
                            }}
                            className={`w-full px-3.5 py-2.5 text-left flex items-center gap-3 transition-colors cursor-pointer group ${
                              isSelected
                                ? 'bg-[var(--cyan)]/[0.12] text-[var(--cyan)]'
                                : 'hover:bg-[var(--cyan)]/[0.06] text-[var(--tx)]'
                            }`}
                          >
                            <div
                              className={`w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-colors ${
                                isSelected
                                  ? 'bg-[var(--cyan)] text-slate-950 font-bold'
                                  : 'bg-[var(--surface)] border border-[var(--bd2)] text-[var(--cyan)] group-hover:border-[var(--cyan)]/50'
                              }`}
                            >
                              <MapPin className="w-4 h-4" />
                            </div>
                            <div className="min-w-0 flex-1">
                              <div className="text-xs font-bold truncate flex items-center gap-1.5">
                                <span>{loc.city}</span>
                                {loc.popular && !locationQuery.trim() && (
                                  <span className="text-[8.5px] px-1.5 py-0.2 rounded bg-[var(--cyan)]/10 text-[var(--cyan)] font-mono font-bold">
                                    HUB
                                  </span>
                                )}
                              </div>
                              <div className="text-[10px] text-[var(--tx-3)] truncate">
                                {loc.region}
                              </div>
                            </div>
                            {isSelected && (
                              <div className="w-5 h-5 rounded-full bg-[var(--cyan)] text-slate-950 flex items-center justify-center shrink-0">
                                <Check className="w-3.5 h-3.5 stroke-[3]" />
                              </div>
                            )}
                          </button>
                        );
                      })
                    ) : (
                      <div className="p-3 text-center text-xs text-[var(--tx-3)]">
                        No matching presets found. Click &quot;Use {locationQuery.trim()}&quot; above to register this area.
                      </div>
                    )}
                  </div>
                )}
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

        {/* FINAL STEP: SMS OTP VERIFICATION (Step 5 for Provider, Step 3 for Client) */}
        {step === totalSteps && (
          <div className="space-y-4">
            {/* Header Badge */}
            <div className="text-center space-y-1.5 pt-1">
              <div className="w-12 h-12 rounded-[20px] bg-gradient-to-br from-[var(--cyan)]/20 to-blue-500/20 border border-[var(--cyan)]/30 text-[var(--cyan)] flex items-center justify-center mx-auto shadow-xs">
                <Smartphone className="w-6 h-6" />
              </div>
              <h3 className="text-base font-extrabold text-[var(--tx)] tracking-tight">
                Verify Your Mobile Number
              </h3>
              <p className="text-xs text-[var(--tx-2)] max-w-sm mx-auto leading-relaxed">
                We dispatched a 6-digit security code via SMS to{' '}
                <strong className="text-[var(--tx)] font-mono">
                  {phone ? (phone.startsWith('0') ? `+233 ${phone.slice(1)}` : phone) : '+233 24 000 0000'}
                </strong>
              </p>
              <div className="flex items-center justify-center gap-2 pt-0.5">
                <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--cyan)]">
                  <CheckCircle2 className="w-3 h-3 text-[#10B981]" />
                  <span>{network !== 'unknown' ? `${network.toUpperCase()} SIM Detected` : 'Ghana Mobile'}</span>
                </span>
                <button
                  type="button"
                  onClick={() => {
                    setStep(1);
                    setErrorMsg('');
                  }}
                  className="text-[10.5px] text-[var(--tx-3)] hover:text-[var(--cyan)] underline font-medium cursor-pointer"
                >
                  Edit phone
                </button>
              </div>
            </div>

            {/* Dev / Prototype Testing Helper Pill */}
            <div className="p-3 rounded-[16px] bg-[var(--cyan)]/10 border border-[var(--cyan)]/25 flex items-center justify-between gap-2">
              <div className="text-[11.5px] text-[var(--tx)] flex items-center gap-1.5">
                <ShieldCheck className="w-4 h-4 text-[var(--cyan)] shrink-0" />
                <span>
                  Demo Code: <strong className="font-mono text-[var(--cyan)] font-black tracking-wider">123456</strong>
                </span>
              </div>
              <button
                type="button"
                onClick={() => {
                  setOtpDigits(['1', '2', '3', '4', '5', '6']);
                  setErrorMsg('');
                }}
                className="text-[11px] font-bold px-2.5 py-1 rounded-[10px] bg-[var(--cyan)] text-slate-950 hover:opacity-90 transition-all cursor-pointer shadow-xs"
              >
                Auto-Fill
              </button>
            </div>

            {/* 6-Digit OTP Input Boxes */}
            <div className="space-y-2 py-1">
              <label className="text-xs font-bold text-[var(--tx)] block text-center">
                Enter 6-Digit Verification Code
              </label>
              <div className="flex items-center justify-center gap-2">
                {otpDigits.map((digit, idx) => (
                  <input
                    key={idx}
                    id={`reg-sms-${idx}`}
                    type="text"
                    inputMode="numeric"
                    pattern="[0-9]*"
                    maxLength={1}
                    value={digit}
                    onChange={(e) => {
                      const val = e.target.value.replace(/\D/g, '');
                      const newCode = [...otpDigits];
                      newCode[idx] = val;
                      setOtpDigits(newCode);
                      if (val && idx < 5) {
                        document.getElementById(`reg-sms-${idx + 1}`)?.focus();
                      }
                    }}
                    onKeyDown={(e) => {
                      if (e.key === 'Backspace' && !otpDigits[idx] && idx > 0) {
                        document.getElementById(`reg-sms-${idx - 1}`)?.focus();
                      }
                    }}
                    onPaste={(e) => {
                      e.preventDefault();
                      const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                      if (pasted) {
                        const newDigits = pasted.split('');
                        while (newDigits.length < 6) newDigits.push('');
                        setOtpDigits(newDigits);
                        const nextIndex = Math.min(pasted.length, 5);
                        document.getElementById(`reg-sms-${nextIndex}`)?.focus();
                      }
                    }}
                    className="w-11 h-12 text-center font-mono font-bold text-lg rounded-[16px] bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
                  />
                ))}
              </div>
            </div>

            {/* Resend SMS Counter */}
            <div className="flex items-center justify-between text-xs px-1">
              <span className="text-[var(--tx-3)]">Didn't receive code?</span>
              <button
                type="button"
                disabled={resendTimer > 0}
                onClick={() => {
                  setResendTimer(30);
                  setCodeResentNotice(true);
                  setTimeout(() => setCodeResentNotice(false), 3500);
                }}
                className={`font-bold transition-colors ${
                  resendTimer > 0
                    ? 'text-[var(--tx-3)] cursor-not-allowed'
                    : 'text-[var(--cyan)] hover:underline cursor-pointer'
                }`}
              >
                {resendTimer > 0 ? `Resend SMS in ${resendTimer}s` : 'Resend SMS Code'}
              </button>
            </div>

            {codeResentNotice && (
              <div className="p-2.5 rounded-[14px] bg-[#10B981]/10 border border-[#10B981]/25 text-[#10B981] text-xs font-semibold text-center">
                A fresh 6-digit SMS verification code has been dispatched.
              </div>
            )}

            {/* Role-Specific Escrow Benefit Note */}
            <div className="rounded-[18px] p-3.5 bg-[var(--surface-elevated)] border border-[var(--bd2)] flex items-start gap-2.5 text-xs text-[var(--tx-2)]">
              <ShieldCheck className="w-4 h-4 text-[var(--cyan)] shrink-0 mt-0.5" />
              <div>
                {role === 'provider' ? (
                  <span>
                    <strong className="text-[var(--tx)]">Instant Escrow Payouts:</strong> Verifying your phone number secures your {payoutWallet === 'telecel' ? 'Telecel Cash' : payoutWallet === 'at' ? 'AT Money' : 'MTN MoMo'} wallet for sub-60s milestone cash-outs.
                  </span>
                ) : (
                  <span>
                    <strong className="text-[var(--tx)]">Protected Escrow Hiring:</strong> Verifying your phone secures your project deposits and activates real-time milestone SMS alerts.
                  </span>
                )}
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
              className="h-12 px-5 rounded-[18px] border border-[var(--bd2)] hover:border-[var(--bd)] text-[var(--tx-2)] hover:text-[var(--tx)] font-bold text-xs flex items-center gap-1.5 transition-all cursor-pointer"
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
            className={`h-12 px-7 rounded-[18px] font-black text-sm flex items-center gap-2 shadow-lg transition-all ml-auto cursor-pointer ${
              role === 'client'
                ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] hover:from-[#D97706] hover:to-[#B45309] text-white shadow-amber-500/20'
                : 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] hover:from-[#00B4A9] hover:to-[#008B82] text-white shadow-cyan-500/20'
            }`}
          >
            {isSubmitting ? (
              <>
                <span className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
                <span>Verifying &amp; Creating...</span>
              </>
            ) : step === totalSteps ? (
              <>
                <span>Verify &amp; Complete Registration</span>
                <Check className="w-4 h-4 stroke-[3]" />
              </>
            ) : step === totalSteps - 1 ? (
              <>
                <span>Continue to SMS Verification</span>
                <ArrowRight className="w-3.5 h-3.5" />
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
