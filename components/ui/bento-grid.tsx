'use client';

import React from 'react';
import { cn } from '@/lib/utils';
import { SpotlightCard } from './spotlight-card';

export function BentoGrid({
  className,
  children,
}: {
  className?: string;
  children: React.ReactNode;
}) {
  return (
    <div
      className={cn(
        'grid grid-cols-1 md:grid-cols-3 gap-5 max-w-6xl mx-auto',
        className
      )}
    >
      {children}
    </div>
  );
}

export function BentoCard({
  className,
  title,
  description,
  header,
  icon,
  badge,
  spotlightColor = 'rgba(0, 212, 200, 0.14)',
  children,
}: {
  className?: string;
  title: string;
  description: string;
  header?: React.ReactNode;
  icon?: React.ReactNode;
  badge?: string;
  spotlightColor?: string;
  children?: React.ReactNode;
}) {
  return (
    <SpotlightCard
      spotlightColor={spotlightColor}
      className={cn(
        'group relative flex flex-col justify-between p-6 transition-all duration-300 hover:translate-y-[-2px]',
        className
      )}
    >
      <div>
        <div className="flex items-center justify-between mb-4">
          <div className="flex items-center gap-3">
            {icon && (
              <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--cyan-dim)] border border-[rgba(0,212,200,0.18)] text-xl">
                {icon}
              </div>
            )}
            {badge && (
              <span className="text-[11px] font-bold px-2.5 py-1 rounded-full bg-[var(--cyan-dim)] text-[var(--cyan)] border border-[rgba(0,212,200,0.2)]">
                {badge}
              </span>
            )}
          </div>
        </div>

        {header && <div className="mb-4">{header}</div>}

        <h3 className="text-lg font-bold text-[var(--tx)] font-heading tracking-tight mb-2 group-hover:text-[var(--cyan)] transition-colors">
          {title}
        </h3>
        <p className="text-sm text-[var(--tx-2)] leading-relaxed">{description}</p>
      </div>

      {children && <div className="mt-4 pt-4 border-t border-[var(--border)]">{children}</div>}
    </SpotlightCard>
  );
}
