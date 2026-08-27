'use client';

import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { cn } from '@/lib/utils';
import { Search, Briefcase, PlusCircle, ArrowUp } from 'lucide-react';

interface FloatingDockProps {
  onOpenSearch: () => void;
}

export function FloatingDock({ onOpenSearch }: FloatingDockProps) {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setVisible(window.scrollY > 480);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <AnimatePresence>
      {visible && (
        <motion.div
          initial={{ opacity: 0, y: 30, scale: 0.95 }}
          animate={{ opacity: 1, y: 0, scale: 1 }}
          exit={{ opacity: 0, y: 30, scale: 0.95 }}
          transition={{ duration: 0.25, ease: 'easeOut' }}
          className="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 p-2 rounded-full border border-[var(--border-hi)] bg-[rgba(12,14,20,0.85)] backdrop-blur-xl shadow-2xl shadow-cyan-950/20"
        >
          <button
            onClick={onOpenSearch}
            className="flex items-center gap-2 px-3.5 py-2 rounded-full text-xs font-semibold text-[var(--tx)] hover:text-[var(--cyan)] hover:bg-[var(--cyan-dim)] transition-all"
            title="Search Platform (Cmd + K)"
          >
            <Search className="w-3.5 h-3.5 text-[var(--cyan)]" />
            <span className="hidden sm:inline">Search</span>
            <kbd className="hidden sm:inline-block px-1.5 py-0.5 text-[9px] font-mono rounded bg-[var(--surface-2)] text-[var(--tx-3)] border border-[var(--border)]">
              ⌘K
            </kbd>
          </button>

          <div className="w-[1px] h-4 bg-[var(--border)]" />

          <a
            href="/jobs.php"
            className="flex items-center gap-2 px-3.5 py-2 rounded-full text-xs font-semibold text-[var(--tx)] hover:text-[var(--cyan)] hover:bg-[var(--surface-2)] transition-all"
          >
            <Briefcase className="w-3.5 h-3.5 text-[var(--tx-2)]" />
            <span className="hidden sm:inline">Browse Jobs</span>
          </a>

          <a
            href="/auth/register.php?role=client"
            className="flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold text-[#0C0E14] bg-gradient-to-r from-[#00D4C8] to-[#00b8ad] hover:brightness-110 shadow-md shadow-cyan-500/20 transition-all"
          >
            <PlusCircle className="w-3.5 h-3.5" />
            <span>Post a Job</span>
          </a>

          <div className="w-[1px] h-4 bg-[var(--border)]" />

          <button
            onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
            className="p-2 rounded-full text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface-2)] transition-all"
            title="Back to top"
          >
            <ArrowUp className="w-4 h-4" />
          </button>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
