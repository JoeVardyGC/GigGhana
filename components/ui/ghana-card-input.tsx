'use client';

import React, { useState } from 'react';
import { ShieldCheck, CheckCircle2, Upload, AlertCircle, Camera, Check } from 'lucide-react';

interface GhanaCardInputProps {
  pin: string;
  onPinChange: (pin: string, isValid: boolean) => void;
  frontImage?: string | null;
  onFrontImageChange?: (dataUri: string) => void;
  backImage?: string | null;
  onBackImageChange?: (dataUri: string) => void;
  error?: string;
  className?: string;
}

export function validateGhanaCardPin(pin: string): boolean {
  // Format: GHA-XXXXXXXXX-X
  const regex = /^GHA-[0-9]{9}-[0-9]{1}$/;
  return regex.test(pin);
}

export function formatGhanaCardPin(input: string): string {
  // Normalize: remove non-alphanumeric except already prefixed GHA
  let clean = input.toUpperCase().replace(/[^A-Z0-9]/g, '');

  if (clean.startsWith('GHA')) {
    clean = clean.slice(3);
  }
  // Only keep numbers now for the rest
  clean = clean.replace(/\D/g, '');

  if (!clean) return 'GHA-';
  if (clean.length <= 9) {
    return `GHA-${clean}`;
  }
  return `GHA-${clean.slice(0, 9)}-${clean.slice(9, 10)}`;
}

export function GhanaCardInput({
  pin,
  onPinChange,
  frontImage,
  onFrontImageChange,
  backImage,
  onBackImageChange,
  error,
  className = '',
}: GhanaCardInputProps) {
  const isValid = validateGhanaCardPin(pin);
  const frontInputRef = React.useRef<HTMLInputElement>(null);
  const backInputRef = React.useRef<HTMLInputElement>(null);

  const handlePinChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const raw = e.target.value;
    const formatted = formatGhanaCardPin(raw);
    const valid = validateGhanaCardPin(formatted);
    onPinChange(formatted, valid);
  };

  const handleFileSelect = (type: 'front' | 'back', e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (event) => {
      const result = event.target?.result as string;
      if (type === 'front' && onFrontImageChange) {
        onFrontImageChange(result);
      } else if (type === 'back' && onBackImageChange) {
        onBackImageChange(result);
      }
    };
    reader.readAsDataURL(file);
  };

  return (
    <div className={`space-y-4 ${className}`}>
      {/* PIN Input Field */}
      <div className="space-y-1.5">
        <div className="flex items-center justify-between">
          <label htmlFor="ghana-card-pin" className="text-xs font-bold text-[var(--tx)] flex items-center gap-1.5">
            <ShieldCheck className="w-3.5 h-3.5 text-[var(--cyan)]" />
            <span>Ghana Card National ID PIN</span>
            <span className="text-rose-500">*</span>
          </label>
          <span className="text-[10px] font-mono text-[var(--tx-3)]">Format: GHA-XXXXXXXXX-X</span>
        </div>

        <div className="relative flex items-center">
          <input
            id="ghana-card-pin"
            type="text"
            value={pin}
            onChange={handlePinChange}
            maxLength={15}
            placeholder="GHA-712894012-4"
            className={`w-full h-12 px-4 pr-11 bg-[var(--surface)] text-[var(--tx)] font-mono text-sm tracking-wider font-semibold rounded-[16px] border transition-all placeholder:text-[var(--tx-3)] placeholder:font-sans focus:outline-none focus:ring-2 ${
              isValid
                ? 'border-emerald-500/80 focus:border-emerald-500 focus:ring-emerald-500/20 bg-emerald-500/[0.03]'
                : error
                ? 'border-rose-500 focus:ring-rose-500/20'
                : 'border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-[var(--cyan)]/20'
            }`}
          />

          <div className="absolute right-3.5 flex items-center">
            {isValid ? (
              <div className="flex items-center gap-1 text-emerald-500 font-bold text-xs" title="Valid NIA Format">
                <CheckCircle2 className="w-4 h-4 fill-emerald-500 text-white dark:text-slate-900" />
              </div>
            ) : pin.length > 4 ? (
              <div className="text-[10px] text-[var(--tx-3)] font-mono">
                {15 - pin.length} digits left
              </div>
            ) : null}
          </div>
        </div>

        {error && <p className="text-[11px] font-medium text-rose-500 mt-1">{error}</p>}
      </div>

      {/* Document Photo Upload Area */}
      <div className="space-y-2">
        <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
          <span>Card Document Photos (Front &amp; Back)</span>
          <span className="text-[10.5px] font-normal text-[var(--tx-3)]">Clear photo, no glare</span>
        </label>

        <div className="grid grid-cols-2 gap-3">
          {/* Hidden File Inputs */}
          <input
            ref={frontInputRef}
            type="file"
            accept="image/*"
            className="hidden"
            onChange={(e) => handleFileSelect('front', e)}
          />
          <input
            ref={backInputRef}
            type="file"
            accept="image/*"
            className="hidden"
            onChange={(e) => handleFileSelect('back', e)}
          />

          {/* Front Photo */}
          <div
            onClick={() => frontInputRef.current?.click()}
            className={`border border-dashed rounded-[18px] p-4 flex flex-col items-center justify-center text-center cursor-pointer transition-all ${
              frontImage
                ? 'border-emerald-500/60 bg-emerald-500/[0.04]'
                : 'border-[var(--bd2)] hover:border-[var(--cyan)] bg-[var(--s2)]/40 hover:bg-[var(--surface)]'
            }`}
          >
            {frontImage ? (
              <div className="flex flex-col items-center gap-1.5">
                <div className="relative w-12 h-9 rounded-lg overflow-hidden border border-emerald-500/40 shadow-xs">
                  <img src={frontImage} alt="Card Front" className="w-full h-full object-cover" />
                  <div className="absolute inset-0 bg-emerald-950/20 flex items-center justify-center">
                    <Check className="w-3.5 h-3.5 text-white stroke-[3]" />
                  </div>
                </div>
                <span className="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">Front Attached</span>
                <span className="text-[9.5px] text-[var(--tx-3)]">Click to replace</span>
              </div>
            ) : (
              <div className="flex flex-col items-center gap-1">
                <Camera className="w-5 h-5 text-[var(--cyan)] mb-0.5" />
                <span className="text-[11px] font-bold text-[var(--tx)]">Card Front</span>
                <span className="text-[9.5px] text-[var(--tx-3)]">Click to open files / camera</span>
              </div>
            )}
          </div>

          {/* Back Photo */}
          <div
            onClick={() => backInputRef.current?.click()}
            className={`border border-dashed rounded-[18px] p-4 flex flex-col items-center justify-center text-center cursor-pointer transition-all ${
              backImage
                ? 'border-emerald-500/60 bg-emerald-500/[0.04]'
                : 'border-[var(--bd2)] hover:border-[var(--cyan)] bg-[var(--s2)]/40 hover:bg-[var(--surface)]'
            }`}
          >
            {backImage ? (
              <div className="flex flex-col items-center gap-1.5">
                <div className="relative w-12 h-9 rounded-lg overflow-hidden border border-emerald-500/40 shadow-xs">
                  <img src={backImage} alt="Card Back" className="w-full h-full object-cover" />
                  <div className="absolute inset-0 bg-emerald-950/20 flex items-center justify-center">
                    <Check className="w-3.5 h-3.5 text-white stroke-[3]" />
                  </div>
                </div>
                <span className="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">Back Attached</span>
                <span className="text-[9.5px] text-[var(--tx-3)]">Click to replace</span>
              </div>
            ) : (
              <div className="flex flex-col items-center gap-1">
                <Upload className="w-5 h-5 text-[var(--cyan)] mb-0.5" />
                <span className="text-[11px] font-bold text-[var(--tx)]">Card Back</span>
                <span className="text-[9.5px] text-[var(--tx-3)]">Click to open files / camera</span>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Trust Badge Live Preview Box */}
      <div className="rounded-xl p-3 bg-gradient-to-r from-emerald-500/[0.08] to-[var(--cyan)]/[0.06] border border-emerald-500/20 flex items-center justify-between">
        <div className="flex items-center gap-2.5">
          <div className="w-8 h-8 rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-500 shrink-0">
            <ShieldCheck className="w-4 h-4" />
          </div>
          <div>
            <div className="text-xs font-extrabold text-[var(--tx)] flex items-center gap-1.5">
              <Check className="w-3.5 h-3.5 text-emerald-500 stroke-[3]" />
              <span>Ghana Card Biometric Verified</span>
            </div>
            <div className="text-[10.5px] text-[var(--tx-2)]">
              {isValid ? 'NIA format passed · Trust Badge active' : 'Enter PIN to unlock instant Verified Pro status'}
            </div>
          </div>
        </div>
        <span className={`text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider ${
          isValid
            ? 'bg-emerald-500 text-white font-extrabold shadow-xs'
            : 'bg-[var(--bd2)] text-[var(--tx-3)]'
        }`}>
          {isValid ? 'Ready' : 'Pending'}
        </span>
      </div>
    </div>
  );
}
