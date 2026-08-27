'use client';

import React from 'react';
import { motion } from 'framer-motion';
import { Briefcase, UserCheck } from 'lucide-react';

interface RoleSwitcherProps {
  role: 'hire' | 'work';
  onChange: (role: 'hire' | 'work') => void;
}

export function RoleSwitcher({ role, onChange }: RoleSwitcherProps) {
  return (
    <div className="relative inline-flex items-center p-1.5 rounded-full border border-white/15 bg-white/[0.04] backdrop-blur-xl shadow-inner">
      <button
        onClick={() => onChange('hire')}
        className={`relative z-10 flex items-center gap-2 px-5 py-2 rounded-full text-xs font-bold transition-colors ${
          role === 'hire' ? 'text-[#07090E]' : 'text-white/70 hover:text-white'
        }`}
      >
        <Briefcase className="w-3.5 h-3.5" />
        <span>I Want to Hire Talent</span>
      </button>

      <button
        onClick={() => onChange('work')}
        className={`relative z-10 flex items-center gap-2 px-5 py-2 rounded-full text-xs font-bold transition-colors ${
          role === 'work' ? 'text-[#07090E]' : 'text-white/70 hover:text-white'
        }`}
      >
        <UserCheck className="w-3.5 h-3.5" />
        <span>I Want to Find Work</span>
      </button>

      {/* Animated pill background */}
      <motion.div
        layout
        transition={{ type: 'spring', stiffness: 450, damping: 35 }}
        className={`absolute top-1.5 bottom-1.5 rounded-full shadow-lg ${
          role === 'hire'
            ? 'left-1.5 w-[calc(50%-6px)] bg-gradient-to-r from-[#F59E0B] to-[#FBBF24]'
            : 'left-[calc(50%+3px)] w-[calc(50%-6px)] bg-gradient-to-r from-[#06B6D4] to-[#10B981]'
        }`}
      />
    </div>
  );
}
