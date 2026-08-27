'use client';

import React from 'react';

interface GhanaCardProps {
  surname?: string;
  firstNames?: string;
  nationality?: string;
  dob?: string;
  sex?: string;
  height?: string;
  docNo?: string;
  pin?: string;
  expiry?: string;
  verified?: boolean;
}

export function GhanaCard({
  surname = 'ASANTE',
  firstNames = 'KWAME',
  nationality = 'GHANAIAN',
  dob = '14/08/1992',
  sex = 'M',
  height = '1.78m',
  docNo = 'A2894104',
  pin = 'GHA-712894012-4',
  expiry = '12/05/2030',
  verified = true,
}: GhanaCardProps) {
  return (
    <div className="relative w-full max-w-md mx-auto aspect-[1.586/1] rounded-2xl p-3.5 sm:p-4 text-slate-900 shadow-2xl overflow-hidden select-none border border-slate-300/80 bg-gradient-to-br from-[#EBF5FB] via-[#E8F8F5] to-[#FCF3CF] font-sans">
      {/* ═══ GUILLOCHE & SECURITY WATERMARK BACKGROUND ═══ */}
      <div
        className="absolute inset-0 opacity-[0.14] pointer-events-none"
        style={{
          backgroundImage: `
            radial-gradient(circle at 50% 50%, #006B3F 10%, transparent 60%),
            repeating-linear-gradient(45deg, #2563EB 0, #2563EB 1px, transparent 0, transparent 8px),
            repeating-linear-gradient(-45deg, #D97706 0, #D97706 1px, transparent 0, transparent 8px)
          `,
          backgroundSize: '100% 100%, 16px 16px, 16px 16px',
        }}
      />

      {/* Subtle Coat of Arms & Ghana Map Watermark */}
      <div className="absolute right-6 top-1/2 -translate-y-1/2 w-32 h-32 rounded-full border-2 border-emerald-600/15 opacity-25 flex items-center justify-center pointer-events-none">
        <div className="text-4xl text-emerald-800">★</div>
      </div>

      {/* ═══ CARD HEADER ═══ */}
      <div className="relative z-10 flex items-center justify-between pb-2 border-b border-slate-300/80">
        {/* Left: ECOWAS / CEDEAO */}
        <div className="flex items-center gap-1.5">
          <div className="h-6 w-6 rounded-full bg-gradient-to-tr from-[#006B3F] to-[#00A859] p-0.5 flex items-center justify-center text-white shadow-xs">
            <span className="text-[7px] font-black tracking-tighter">ECOWAS</span>
          </div>
          <div className="leading-none">
            <div className="text-[7px] font-extrabold tracking-wider text-[#006B3F]">CEDEAO</div>
            <div className="text-[6px] font-bold text-slate-600">COMMUNITY</div>
          </div>
        </div>

        {/* Center: REPUBLIC OF GHANA */}
        <div className="text-center">
          <div className="text-[10px] sm:text-[11px] font-extrabold tracking-wider text-[#002B49] font-heading leading-tight uppercase">
            Republic of Ghana
          </div>
          <div className="text-[7px] font-bold text-[#006B3F] uppercase tracking-wider">
            National Identity Card
          </div>
        </div>

        {/* Right: Official Flag of Ghana */}
        <div className="flex flex-col items-end">
          <div className="h-4 w-7 rounded-xs overflow-hidden border border-slate-400/80 shadow-xs flex flex-col">
            <div className="h-1/3 bg-[#CE1126]" />
            <div className="h-1/3 bg-[#FCD116] flex items-center justify-center">
              <span className="text-[6px] text-black leading-none font-bold">★</span>
            </div>
            <div className="h-1/3 bg-[#006B3F]" />
          </div>
          <span className="text-[6px] font-mono font-bold text-slate-500 mt-0.5">GHA</span>
        </div>
      </div>

      {/* ═══ CARD BODY (PHOTO, CHIP, DETAILS) ═══ */}
      <div className="relative z-10 grid grid-cols-12 gap-3 pt-2.5 items-center">
        {/* Left Column: Photo Portrait + EMV Smart Chip */}
        <div className="col-span-4 flex flex-col items-center space-y-1.5">
          {/* Bearer Portrait */}
          <div className="relative h-20 w-16 rounded-lg bg-gradient-to-tr from-[#1E3A8A] via-[#2563EB] to-[#06B6D4] p-0.5 shadow-md border border-slate-300">
            <div className="h-full w-full rounded-[6px] bg-slate-800 flex flex-col items-center justify-center text-white overflow-hidden relative">
              <span className="font-heading font-black text-base">{firstNames[0]}{surname[0]}</span>
              <span className="text-[7px] text-slate-300 font-mono">KWAME</span>
              {/* Holographic Security Sheen */}
              <div className="absolute inset-0 bg-gradient-to-tr from-transparent via-white/20 to-transparent opacity-60" />
            </div>
            {/* Ghost Mini Star */}
            <div className="absolute -bottom-1 -right-1 h-4 w-4 rounded-full bg-[#FCD116] border border-black/30 flex items-center justify-center text-[8px] text-black font-bold shadow-xs">
              ★
            </div>
          </div>

          {/* Authentic Gold Contact Smart Chip */}
          <div className="h-5 w-7 rounded bg-gradient-to-tr from-[#E5B500] via-[#FCD116] to-[#B8860B] border border-amber-600/50 shadow-xs flex items-center justify-center p-0.5">
            <div className="h-full w-full border border-black/40 rounded-[2px] grid grid-cols-2 gap-0.5 p-0.5">
              <div className="border-r border-b border-black/30" />
              <div className="border-b border-black/30" />
              <div className="border-r border-black/30" />
              <div />
            </div>
          </div>
        </div>

        {/* Right Column: Citizen Information Fields */}
        <div className="col-span-8 space-y-1 text-left text-[8.5px] sm:text-[9.5px]">
          {/* Surname */}
          <div>
            <div className="text-[6.5px] uppercase tracking-wider text-slate-500 font-semibold leading-none">
              Surname / Nom:
            </div>
            <div className="font-heading font-extrabold text-[#002B49] text-[10.5px] leading-tight">
              {surname}
            </div>
          </div>

          {/* First Names */}
          <div>
            <div className="text-[6.5px] uppercase tracking-wider text-slate-500 font-semibold leading-none">
              First Names / Prénoms:
            </div>
            <div className="font-heading font-bold text-[#002B49] leading-tight">
              {firstNames}
            </div>
          </div>

          {/* Nationality & Sex & Height */}
          <div className="grid grid-cols-3 gap-1 pt-0.5">
            <div>
              <div className="text-[6px] text-slate-500 font-semibold leading-none">Nationality:</div>
              <div className="font-bold text-[#002B49] text-[8px]">{nationality}</div>
            </div>
            <div>
              <div className="text-[6px] text-slate-500 font-semibold leading-none">Sex / Sexe:</div>
              <div className="font-bold text-[#002B49] text-[8px]">{sex}</div>
            </div>
            <div>
              <div className="text-[6px] text-slate-500 font-semibold leading-none">DOB / Né(e):</div>
              <div className="font-bold text-[#002B49] text-[8px]">{dob}</div>
            </div>
          </div>

          {/* Document No & Expiry */}
          <div className="grid grid-cols-2 gap-1 pt-0.5">
            <div>
              <div className="text-[6px] text-slate-500 font-semibold leading-none">Doc No:</div>
              <div className="font-mono font-bold text-[#002B49] text-[8px]">{docNo}</div>
            </div>
            <div>
              <div className="text-[6px] text-slate-500 font-semibold leading-none">Expiry Date:</div>
              <div className="font-mono font-bold text-[#002B49] text-[8px]">{expiry}</div>
            </div>
          </div>

          {/* Official Ghana Personal ID Number (PIN) */}
          <div className="pt-1.5 border-t border-slate-300/80 flex items-center justify-between">
            <div>
              <div className="text-[6px] text-slate-500 font-semibold uppercase leading-none">
                Personal ID No. / N° Personnel
              </div>
              <div className="font-mono font-black text-[#006B3F] text-[10px] tracking-wider">
                {pin}
              </div>
            </div>

            {verified && (
              <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-600 text-white font-heading font-black text-[7.5px] shadow-sm">
                <span className="h-1.5 w-1.5 rounded-full bg-white animate-ping" />
                NIA VERIFIED
              </span>
            )}
          </div>
        </div>
      </div>

      {/* ═══ MACHINE READABLE ZONE (MRZ) OPTICAL BAR ═══ */}
      <div className="absolute bottom-1.5 inset-x-3 text-center pointer-events-none opacity-40">
        <div className="font-mono text-[5.5px] tracking-widest text-slate-800 overflow-hidden whitespace-nowrap">
          I&lt;GHAKWAME&lt;&lt;ASANTE&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;&lt;
        </div>
        <div className="font-mono text-[5.5px] tracking-widest text-slate-800 overflow-hidden whitespace-nowrap">
          GHA7128940124M3005128GHAA2894104&lt;&lt;&lt;&lt;8
        </div>
      </div>
    </div>
  );
}
