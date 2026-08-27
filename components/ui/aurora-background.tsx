'use client';

import React from 'react';
import { cn } from '@/lib/utils';

export function AuroraBackground({
  className,
  children,
  ...props
}: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn(
        'relative flex flex-col items-center justify-center overflow-hidden bg-[#07090E] transition-colors',
        className
      )}
      {...props}
    >
      <div className="absolute inset-0 overflow-hidden pointer-events-none">
        <div
          className="absolute -top-[30%] left-1/2 -translate-x-1/2 w-[1000px] h-[600px] rounded-full blur-[140px] opacity-25"
          style={{
            background: 'radial-gradient(circle, #06B6D4 0%, #10B981 35%, #8B5CF6 70%, transparent 100%)',
          }}
        />
        <div
          className="absolute top-[20%] -left-[10%] w-[600px] h-[500px] rounded-full blur-[120px] opacity-15"
          style={{
            background: 'radial-gradient(circle, #F59E0B 0%, #EC4899 50%, transparent 100%)',
          }}
        />
        <div
          className="absolute top-[10%] -right-[10%] w-[600px] h-[500px] rounded-full blur-[130px] opacity-20"
          style={{
            background: 'radial-gradient(circle, #06B6D4 0%, #3B82F6 60%, transparent 100%)',
          }}
        />
        {/* Subtle grid pattern overlay */}
        <div
          className="absolute inset-0 opacity-[0.035]"
          style={{
            backgroundImage: `linear-gradient(to right, #ffffff 1px, transparent 1px), linear-gradient(to bottom, #ffffff 1px, transparent 1px)`,
            backgroundSize: '48px 48px',
          }}
        />
      </div>
      <div className="relative z-10 w-full">{children}</div>
    </div>
  );
}
