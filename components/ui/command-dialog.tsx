'use client';

import React, { useEffect } from 'react';
import * as Dialog from '@radix-ui/react-dialog';
import { Command } from 'cmdk';
import { Search, Briefcase, User, Sparkles, Folder, ArrowRight } from 'lucide-react';

interface CommandDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  categories: Array<{ id: string | number; name: string }>;
}

export function CommandSearchDialog({
  open,
  onOpenChange,
  categories,
}: CommandDialogProps) {
  useEffect(() => {
    const down = (e: KeyboardEvent) => {
      if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
        e.preventDefault();
        onOpenChange(!open);
      }
    };
    document.addEventListener('keydown', down);
    return () => document.removeEventListener('keydown', down);
  }, [open, onOpenChange]);

  const navigateTo = (url: string) => {
    onOpenChange(false);
    window.location.href = url;
  };

  return (
    <Dialog.Root open={open} onOpenChange={onOpenChange}>
      <Dialog.Portal>
        <Dialog.Overlay className="fixed inset-0 z-50 bg-black/60 backdrop-blur-md transition-opacity data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0" />
        <Dialog.Content className="fixed left-[50%] top-[25%] z-50 w-full max-w-xl -translate-x-[50%] -translate-y-[20%] rounded-2xl border border-[var(--border-hi)] bg-[var(--surface)] p-0 shadow-2xl shadow-cyan-950/40 focus:outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95">
          <Dialog.Title className="sr-only">Global Search</Dialog.Title>
          <Command className="w-full rounded-2xl overflow-hidden bg-[var(--surface)] text-[var(--tx)]">
            <div className="flex items-center border-b border-[var(--border)] px-4 py-3">
              <Search className="mr-3 h-4 w-4 shrink-0 text-[var(--cyan)]" />
              <Command.Input
                placeholder="Search service providers, trades, jobs..."
                className="w-full bg-transparent text-sm placeholder:text-[var(--tx-3)] focus:outline-none font-body text-[var(--tx)]"
              />
              <kbd className="hidden sm:inline-block px-1.5 py-0.5 text-[10px] font-mono rounded bg-[var(--surface-2)] text-[var(--tx-3)] border border-[var(--border)]">
                ESC
              </kbd>
            </div>

            <Command.List className="max-h-[340px] overflow-y-auto p-2 scrollbar-thin">
              <Command.Empty className="py-8 text-center text-xs text-[var(--tx-3)]">
                No matching results found.
              </Command.Empty>

              <Command.Group heading="Quick Actions" className="px-2 py-1.5 text-[11px] font-bold text-[var(--cyan)] font-heading uppercase tracking-wider">
                <Command.Item
                  onSelect={() => navigateTo('/jobs.php')}
                  className="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs text-[var(--tx)] hover:bg-[var(--cyan-dim)] hover:text-[var(--cyan)] cursor-pointer transition-colors"
                >
                  <div className="flex items-center gap-2.5">
                    <Briefcase className="w-4 h-4 text-[var(--cyan)]" />
                    <span>Browse All Open Jobs</span>
                  </div>
                  <ArrowRight className="w-3.5 h-3.5 opacity-60" />
                </Command.Item>
                <Command.Item
                  onSelect={() => navigateTo('/search/providers.php')}
                  className="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs text-[var(--tx)] hover:bg-[var(--cyan-dim)] hover:text-[var(--cyan)] cursor-pointer transition-colors"
                >
                  <div className="flex items-center gap-2.5">
                    <User className="w-4 h-4 text-[var(--cyan)]" />
                    <span>Find Verified Service Providers</span>
                  </div>
                  <ArrowRight className="w-3.5 h-3.5 opacity-60" />
                </Command.Item>
                <Command.Item
                  onSelect={() => navigateTo('/auth/register.php?role=client')}
                  className="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs text-[var(--tx)] hover:bg-[var(--cyan-dim)] hover:text-[var(--cyan)] cursor-pointer transition-colors"
                >
                  <div className="flex items-center gap-2.5">
                    <Sparkles className="w-4 h-4 text-[var(--coral)]" />
                    <span>Post a New Job</span>
                  </div>
                  <ArrowRight className="w-3.5 h-3.5 opacity-60" />
                </Command.Item>
              </Command.Group>

              <Command.Group heading="Popular Categories" className="px-2 py-1.5 text-[11px] font-bold text-[var(--tx-3)] font-heading uppercase tracking-wider mt-2">
                {categories.slice(0, 8).map((cat) => (
                  <Command.Item
                    key={cat.id}
                    onSelect={() => navigateTo(`/search/providers.php?category=${cat.id}`)}
                    className="flex items-center justify-between px-3 py-2 rounded-xl text-xs text-[var(--tx-2)] hover:bg-[var(--surface-2)] hover:text-[var(--tx)] cursor-pointer transition-colors"
                  >
                    <div className="flex items-center gap-2.5">
                      <Folder className="w-3.5 h-3.5 text-[var(--tx-3)]" />
                      <span>{cat.name}</span>
                    </div>
                    <span className="text-[10px] text-[var(--tx-3)]">Explore</span>
                  </Command.Item>
                ))}
              </Command.Group>
            </Command.List>
          </Command>
        </Dialog.Content>
      </Dialog.Portal>
    </Dialog.Root>
  );
}
