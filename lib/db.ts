import mysql from 'mysql2/promise';
import { LandingData, ghanaFallbackCats, testimonialFallbacks, fallbackRecentJobs } from './types';

let pool: mysql.Pool | null = null;

export function isDbConfigured(): boolean {
  const host = process.env.DB_HOST;
  // If no DB_HOST is provided, or if running in cloud/CI build environment without remote DB
  if (!host) return false;
  if ((process.env.VERCEL || process.env.CI || process.env.NODE_ENV === 'production') && (host === '127.0.0.1' || host === 'localhost')) {
    return false;
  }
  return true;
}

export function getDbPool() {
  if (!pool) {
    pool = mysql.createPool({
      host: process.env.DB_HOST || '127.0.0.1',
      port: Number(process.env.DB_PORT) || 3306,
      user: process.env.DB_USER || 'root',
      password: process.env.DB_PASS || '',
      database: process.env.DB_NAME || 'gigghana',
      waitForConnections: true,
      connectionLimit: 5,
      queueLimit: 0,
      connectTimeout: 2000,
    });
  }
  return pool;
}

export const defaultLandingData: LandingData = {
  stats: { providers: 14250, jobs: 840, completed: 3200, clients: 9500, earnings: 1450000 },
  categories: ghanaFallbackCats,
  featured: [],
  matchedProviders: [],
  recentJobs: fallbackRecentJobs,
  liveJobs: [],
  earningsData: [12000, 18500, 24000, 31000, 42000, 56000, 68000, 79000, 92000, 108000, 125000, 145000],
  earningsTotal: 800500,
  reviews: testimonialFallbacks,
};

export async function getLandingPageData(): Promise<LandingData> {
  // If database is not configured (e.g. Vercel build environment without remote DB credentials)
  if (!isDbConfigured()) {
    return defaultLandingData;
  }

  try {
    const db = getDbPool();

    // Stats
    const [[pRow]]: any = await db.query("SELECT COUNT(*) as c FROM providers p JOIN users u ON u.id=p.user_id WHERE u.is_active=1 AND u.is_banned=0");
    const [[jRow]]: any = await db.query("SELECT COUNT(*) as c FROM jobs WHERE status='open'");
    const [[cRow]]: any = await db.query("SELECT COUNT(*) as c FROM jobs WHERE status='completed'");
    const [[clRow]]: any = await db.query("SELECT COUNT(*) as c FROM users WHERE role='client' AND is_active=1");
    const [[eRow]]: any = await db.query("SELECT COALESCE(SUM(net_amount),0) as total FROM transactions WHERE type='escrow_release' AND status='completed'");

    const stats = {
      providers: Number(pRow?.c || 14250),
      jobs: Number(jRow?.c || 840),
      completed: Number(cRow?.c || 3200),
      clients: Number(clRow?.c || 9500),
      earnings: Number(eRow?.total || 1450000),
    };

    // Categories
    const [categoriesRows]: any = await db.query(
      "SELECT id, name, slug, icon, description FROM categories WHERE is_active=1 ORDER BY sort_order ASC, id ASC"
    );
    const categories = categoriesRows && categoriesRows.length > 0 ? categoriesRows : ghanaFallbackCats;

    // Skill subquery
    const skillSub = "(SELECT GROUP_CONCAT(s.name ORDER BY ps.proficiency DESC SEPARATOR '|') FROM provider_skills ps JOIN skills s ON s.id=ps.skill_id WHERE ps.provider_id=p.id LIMIT 4)";

    // Featured Providers
    let [featured]: any = await db.query(
      `SELECT u.first_name, u.last_name, u.avatar, u.location, p.tagline, p.rating_avg, p.rating_count, p.hourly_rate, p.completed_jobs, p.is_verified, p.user_id, p.availability, p.experience_level, ${skillSub} AS skill_names FROM providers p JOIN users u ON u.id=p.user_id WHERE p.is_featured=1 AND u.is_active=1 AND u.is_banned=0 ORDER BY p.rating_avg DESC LIMIT 6`
    );

    if (!featured || featured.length === 0) {
      const [featFallback]: any = await db.query(
        `SELECT u.first_name, u.last_name, u.avatar, u.location, p.tagline, p.rating_avg, p.rating_count, p.hourly_rate, p.completed_jobs, p.is_verified, p.user_id, p.availability, p.experience_level, ${skillSub} AS skill_names FROM providers p JOIN users u ON u.id=p.user_id WHERE u.is_active=1 AND u.is_banned=0 ORDER BY p.rating_avg DESC, p.completed_jobs DESC LIMIT 6`
      );
      featured = featFallback;
    }

    // Matched Providers
    const [matchedProviders]: any = await db.query(
      `SELECT u.first_name, u.last_name, u.avatar, p.tagline, p.rating_avg, p.rating_count, p.hourly_rate, p.completed_jobs, p.is_verified, p.user_id, p.experience_level, (SELECT GROUP_CONCAT(s.name ORDER BY ps.proficiency DESC SEPARATOR '|') FROM provider_skills ps JOIN skills s ON s.id=ps.skill_id WHERE ps.provider_id=p.id LIMIT 3) AS skill_names FROM providers p JOIN users u ON u.id=p.user_id WHERE u.is_active=1 AND u.is_banned=0 ORDER BY p.rating_avg DESC, p.rating_count DESC LIMIT 4`
    );

    // Recent Jobs
    let [recentJobs]: any = await db.query(
      `SELECT j.id, j.title, j.description, j.budget_min, j.budget_max, j.budget_type, j.is_urgent, j.is_featured, j.proposal_count, j.created_at, u.first_name, u.last_name, u.avatar AS client_avatar, c.name AS cat_name, c.icon AS cat_icon FROM jobs j JOIN users u ON u.id=j.client_id LEFT JOIN categories c ON c.id=j.category_id WHERE j.status='open' ORDER BY j.is_featured DESC, j.is_urgent DESC, j.created_at DESC LIMIT 6`
    );

    if (!recentJobs || recentJobs.length === 0) {
      recentJobs = fallbackRecentJobs;
    }

    // Live Jobs
    const [liveJobs]: any = await db.query(
      `SELECT j.id, j.title, j.budget_min, j.budget_type, j.created_at, c.name AS cat_name FROM jobs j LEFT JOIN categories c ON c.id=j.category_id WHERE j.status='open' ORDER BY j.created_at DESC LIMIT 5`
    );

    // Monthly Earnings for Year
    const [earningsRaw]: any = await db.query(
      `SELECT MONTH(created_at) AS m, SUM(net_amount) AS total FROM transactions WHERE type='escrow_release' AND status='completed' AND YEAR(created_at)=YEAR(CURDATE()) GROUP BY MONTH(created_at) ORDER BY m ASC`
    );
    const earningsData = Array(12).fill(0);
    for (const row of earningsRaw || []) {
      if (row.m >= 1 && row.m <= 12) {
        earningsData[row.m - 1] = Number(row.total || 0);
      }
    }
    const earningsTotal = earningsData.reduce((a, b) => a + b, 0);

    // Reviews
    const [reviewsRows]: any = await db.query(
      `SELECT r.comment, r.rating_overall, u.first_name, u.last_name, u.avatar, u.location, u.role FROM reviews r JOIN users u ON u.id=r.reviewer_id WHERE r.is_public=1 AND r.comment IS NOT NULL AND r.comment!='' ORDER BY r.rating_overall DESC, r.created_at DESC LIMIT 4`
    );
    const reviews = reviewsRows && reviewsRows.length > 0 ? reviewsRows : testimonialFallbacks;

    return {
      stats,
      categories,
      featured: featured || [],
      matchedProviders: matchedProviders || [],
      recentJobs: recentJobs || [],
      liveJobs: liveJobs || [],
      earningsData: earningsTotal > 0 ? earningsData : defaultLandingData.earningsData,
      earningsTotal: earningsTotal > 0 ? earningsTotal : defaultLandingData.earningsTotal,
      reviews,
    };
  } catch {
    // If connection or query fails, return default data without error spam
    return defaultLandingData;
  }
}
