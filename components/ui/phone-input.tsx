'use client';

import React, { useMemo } from 'react';

export type TelecomNetwork = 'mtn' | 'telecel' | 'at' | 'unknown';

interface PhoneInputProps {
  value: string;
  onChange: (value: string, network: TelecomNetwork) => void;
  error?: string;
  placeholder?: string;
  label?: string;
  required?: boolean;
  className?: string;
  id?: string;
}

export function detectGhanaNetwork(phone: string): TelecomNetwork {
  const clean = phone.replace(/\D/g, '');
  // Extract the prefix (handle 233 format or 0 format)
  let prefix = '';
  if (clean.startsWith('233') && clean.length >= 5) {
    prefix = '0' + clean.slice(3, 5);
  } else if (clean.startsWith('0') && clean.length >= 3) {
    prefix = clean.slice(0, 3);
  }

  const mtnPrefixes = ['024', '025', '053', '054', '055', '059'];
  const telecelPrefixes = ['020', '050'];
  const atPrefixes = ['026', '027', '056', '057'];

  if (mtnPrefixes.includes(prefix)) return 'mtn';
  if (telecelPrefixes.includes(prefix)) return 'telecel';
  if (atPrefixes.includes(prefix)) return 'at';
  return 'unknown';
}

export function formatGhanaPhone(val: string): string {
  const clean = val.replace(/\D/g, '');
  if (!clean) return '';
  
  // Format as: 024 123 4567
  if (clean.length <= 3) return clean;
  if (clean.length <= 6) return `${clean.slice(0, 3)} ${clean.slice(3)}`;
  return `${clean.slice(0, 3)} ${clean.slice(3, 6)} ${clean.slice(6, 10)}`;
}

export function PhoneInput({
  value,
  onChange,
  error,
  placeholder = '024 000 0000',
  label = 'Ghanaian Phone Number',
  required = false,
  className = '',
  id = 'phone-input',
}: PhoneInputProps) {
  const network = useMemo(() => detectGhanaNetwork(value), [value]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const raw = e.target.value;
    const formatted = formatGhanaPhone(raw);
    const net = detectGhanaNetwork(formatted);
    onChange(formatted, net);
  };

  return (
    <div className={`space-y-1.5 ${className}`}>
      {label && (
        <div className="flex items-center justify-between">
          <label htmlFor={id} className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
            <span>{label}</span>
            {required && <span className="text-rose-500">*</span>}
          </label>
          {network !== 'unknown' && (
            <span
              className={`text-[10.5px] font-extrabold px-2 py-0.5 rounded-full uppercase tracking-wider flex items-center gap-1 transition-all ${
                network === 'mtn'
                  ? 'bg-amber-400/15 text-amber-600 border border-amber-400/40 dark:text-amber-400'
                  : network === 'telecel'
                  ? 'bg-red-500/15 text-red-600 border border-red-500/40 dark:text-red-400'
                  : 'bg-blue-500/15 text-blue-600 border border-blue-500/40 dark:text-blue-400'
              }`}
            >
              <span className="w-1.5 h-1.5 rounded-full bg-current animate-pulse" />
              {network === 'mtn' ? 'MTN MoMo' : network === 'telecel' ? 'Telecel Cash' : 'AT Money'}
            </span>
          )}
        </div>
      )}

      <div className="relative flex items-center">
        {/* Country Flag & Code */}
        <div className="absolute left-3 flex items-center gap-1.5 text-xs font-semibold text-[var(--tx-2)] pointer-events-none select-none">
          <span className="text-base leading-none">🇬🇭</span>
          <span>+233</span>
          <span className="w-[1px] h-3.5 bg-[var(--bd2)] ml-0.5" />
        </div>

        <input
          id={id}
          type="tel"
          value={value}
          onChange={handleChange}
          maxLength={12}
          placeholder={placeholder}
          className={`w-full h-11 pl-20 pr-10 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-xl border transition-all placeholder:text-[var(--tx-3)] focus:outline-none focus:ring-2 ${
            error
              ? 'border-rose-500 focus:ring-rose-500/20'
              : 'border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-[var(--cyan)]/20'
          }`}
        />

        {/* Network Icon / Indicator at right */}
        {network !== 'unknown' && (
          <div className="absolute right-3 pointer-events-none">
            {network === 'mtn' && (
              <span className="text-[11px] font-black text-amber-500 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/30">
                MTN
              </span>
            )}
            {network === 'telecel' && (
              <span className="text-[11px] font-black text-red-500 bg-red-500/10 px-1.5 py-0.5 rounded border border-red-500/30">
                TELECEL
              </span>
            )}
            {network === 'at' && (
              <span className="text-[11px] font-black text-blue-500 bg-blue-500/10 px-1.5 py-0.5 rounded border border-blue-500/30">
                AT
              </span>
            )}
          </div>
        )}
      </div>

      {error && <p className="text-[11px] font-medium text-rose-500 mt-1">{error}</p>}
    </div>
  );
}
