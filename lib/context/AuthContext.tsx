'use client';

import React, { createContext, useContext, useState, useEffect } from 'react';

export interface AuthUser {
  id: string | number;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  role: 'client' | 'provider' | 'admin';
  avatar?: string;
  location?: string;
  is_verified?: boolean;
  membership_tier?: 'starter' | 'verified' | 'premium';
  trade?: string;
  payout_wallet?: 'mtn' | 'telecel' | 'at';
  wallet_number?: string;
}

interface AuthContextType {
  user: AuthUser | null;
  role: 'client' | 'provider' | 'admin' | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  login: (emailOrPhone: string, password?: string) => Promise<{ success: boolean; user?: AuthUser; message?: string }>;
  loginWithOtp: (phone: string, otp: string) => Promise<{ success: boolean; user?: AuthUser; message?: string }>;
  loginWithGhanaCard: (pin: string) => Promise<{ success: boolean; user?: AuthUser; message?: string }>;
  requestPasswordReset: (identifier: string) => Promise<{ success: boolean; message: string }>;
  resetPassword: (identifier: string, code: string, newPass: string) => Promise<{ success: boolean; message: string }>;
  register: (userData: Partial<AuthUser> & { password?: string }) => Promise<{ success: boolean; user?: AuthUser; message?: string }>;
  logout: () => void;
  loginDemoUser: (demoType: 'kwame_provider' | 'frimpong_client') => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

const DEMO_USERS: Record<string, AuthUser> = {
  kwame_provider: {
    id: 'prov-001',
    first_name: 'Kwame',
    last_name: 'Asante',
    email: 'kwame.asante@gigghana.com',
    phone: '024 412 3456',
    role: 'provider',
    avatar: '/images/occupations/interior_designer.jpg',
    location: 'Airport Hills, Accra',
    is_verified: true,
    membership_tier: 'verified',
    trade: 'Master POP Ceiling Designer & Decorative Plasterer',
    payout_wallet: 'mtn',
    wallet_number: '024 412 3456',
  },
  frimpong_client: {
    id: 'client-002',
    first_name: 'Dr. Kwabena',
    last_name: 'Frimpong',
    email: 'k.frimpong@legonholdings.com',
    phone: '020 891 0022',
    role: 'client',
    avatar: '/images/occupations/tech_hub.jpg',
    location: 'East Legon, Accra',
    is_verified: true,
    membership_tier: 'premium',
  },
};

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Initialize from localStorage
  useEffect(() => {
    try {
      const stored = localStorage.getItem('gigghana_auth_user');
      if (stored) {
        setUser(JSON.parse(stored));
      }
    } catch (e) {
      console.error('Failed to load stored auth session', e);
    } finally {
      setIsLoading(false);
    }
  }, []);

  const saveUserSession = (userData: AuthUser) => {
    setUser(userData);
    try {
      localStorage.setItem('gigghana_auth_user', JSON.stringify(userData));
    } catch (e) {
      console.error('Failed to persist user session', e);
    }
  };

  const login = async (emailOrPhone: string, _password?: string) => {
    setIsLoading(true);
    await new Promise((res) => setTimeout(res, 600)); // smooth realistic latency

    const cleanInput = emailOrPhone.trim().toLowerCase();
    
    // Check if matching demo
    if (cleanInput.includes('frimpong') || cleanInput.includes('client')) {
      const u = DEMO_USERS.frimpong_client;
      saveUserSession(u);
      setIsLoading(false);
      return { success: true, user: u };
    }

    // Default matching or new session
    const u: AuthUser = {
      id: 'usr_' + Date.now(),
      first_name: emailOrPhone.split('@')[0] || 'User',
      last_name: 'Ghana',
      email: emailOrPhone.includes('@') ? emailOrPhone : `${emailOrPhone.replace(/\s+/g, '')}@gigghana.com`,
      phone: emailOrPhone.includes('@') ? '024 000 0000' : emailOrPhone,
      role: 'provider',
      is_verified: true,
      membership_tier: 'verified',
      location: 'Accra, Greater Accra',
    };

    saveUserSession(u);
    setIsLoading(false);
    return { success: true, user: u };
  };

  const loginWithOtp = async (phone: string, otp: string) => {
    setIsLoading(true);
    await new Promise((res) => setTimeout(res, 700));

    if (!otp || otp.trim().length < 4) {
      setIsLoading(false);
      return { success: false, message: 'Invalid SMS verification code. Please enter the 6-digit code.' };
    }

    const clean = phone.replace(/\s+/g, '');
    if (clean.includes('020') || clean.includes('050')) {
      const u = DEMO_USERS.frimpong_client;
      saveUserSession(u);
      setIsLoading(false);
      return { success: true, user: u };
    }

    const u = DEMO_USERS.kwame_provider;
    saveUserSession(u);
    setIsLoading(false);
    return { success: true, user: u };
  };

  const loginWithGhanaCard = async (pin: string) => {
    setIsLoading(true);
    await new Promise((res) => setTimeout(res, 850));

    const clean = pin.toUpperCase().trim();
    if (!clean.startsWith('GHA-')) {
      setIsLoading(false);
      return { success: false, message: 'Invalid Ghana Card format. Must start with GHA-.' };
    }

    const u = DEMO_USERS.kwame_provider;
    saveUserSession(u);
    setIsLoading(false);
    return { success: true, user: u };
  };

  const requestPasswordReset = async (identifier: string) => {
    await new Promise((res) => setTimeout(res, 600));
    return {
      success: true,
      message: `A 6-digit secure recovery token has been dispatched to ${identifier}.`,
    };
  };

  const resetPassword = async (_identifier: string, code: string, _newPass: string) => {
    await new Promise((res) => setTimeout(res, 800));
    if (!code || code.length < 4) {
      return { success: false, message: 'Invalid recovery code. Please check your SMS or email.' };
    }
    return { success: true, message: 'Password has been successfully updated. You may now sign in.' };
  };

  const register = async (userData: Partial<AuthUser>) => {
    setIsLoading(true);
    await new Promise((res) => setTimeout(res, 800));

    const newUser: AuthUser = {
      id: 'usr_' + Date.now(),
      first_name: userData.first_name || 'Kwame',
      last_name: userData.last_name || 'Artisan',
      email: userData.email || 'artisan@gigghana.com',
      phone: userData.phone || '024 123 4567',
      role: userData.role || 'provider',
      avatar: userData.avatar || '/images/avatars/avatar_male_1.jpg',
      location: userData.location || 'Accra, Greater Accra',
      is_verified: Boolean(userData.is_verified ?? true),
      membership_tier: userData.membership_tier || 'starter',
      trade: userData.trade || 'Skilled Trades',
      payout_wallet: userData.payout_wallet || 'mtn',
      wallet_number: userData.wallet_number || userData.phone,
    };

    saveUserSession(newUser);
    setIsLoading(false);
    return { success: true, user: newUser };
  };

  const logout = () => {
    setUser(null);
    try {
      localStorage.removeItem('gigghana_auth_user');
    } catch (e) {
      console.error('Error during logout', e);
    }
  };

  const loginDemoUser = (demoType: 'kwame_provider' | 'frimpong_client') => {
    const u = DEMO_USERS[demoType];
    if (u) {
      saveUserSession(u);
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        role: user?.role || null,
        isAuthenticated: Boolean(user),
        isLoading,
        login,
        loginWithOtp,
        loginWithGhanaCard,
        requestPasswordReset,
        resetPassword,
        register,
        logout,
        loginDemoUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return ctx;
}
