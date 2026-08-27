import { unstable_cache } from 'next/cache';
import { getLandingPageData } from './db';
import type { LandingData } from './types';

export const getCachedLandingPageData = unstable_cache(
  async (): Promise<LandingData> => {
    return getLandingPageData();
  },
  ['gigghana-landing-data-v1'],
  {
    revalidate: 60, // ISR: regenerate every 60 seconds
    tags: ['landing-data', 'stats', 'jobs', 'categories', 'providers'],
  }
);
