import mysql from 'mysql2/promise';
import fs from 'fs';
import path from 'path';
import { LandingData, ghanaFallbackCats, testimonialFallbacks, fallbackRecentJobs } from './types';

let pool: mysql.Pool | null = null;

export function getDbPool() {
  if (!pool) {
    pool = mysql.createPool({
      host: process.env.DB_HOST || '127.0.0.1',
      port: Number(process.env.DB_PORT) || 3306,
      user: process.env.DB_USER || 'root',
      password: process.env.DB_PASS || '',
      database: process.env.DB_NAME || 'gigghana',
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0,
      connectTimeout: 2000,
    });
  }
  return pool;
}

export function isDbConfigured(): boolean {
  // Pure front-end mode: bypass MySQL network calls completely during front-end construction
  if (process.env.ENABLE_MYSQL !== 'true') {
    return false;
  }
  const host = process.env.DB_HOST;
  if (!host) return false;
  if ((process.env.VERCEL || process.env.CI) && (host === '127.0.0.1' || host === 'localhost')) {
    return false;
  }
  return true;
}

/* ══════════════════════════════════════════════════════
   DATABASE MODELS & PERSISTENCE ADAPTER
   Matches MySQL `users`, `wallets`, `providers` tables
══════════════════════════════════════════════════════ */

export interface DbUserRecord {
  id: number;
  uuid: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  password_hash: string;
  role: 'client' | 'provider' | 'admin';
  avatar?: string;
  bio?: string;
  location?: string;
  country: string;
  ghana_card_number?: string;
  ghana_card_verified: number;
  payment_verified: number;
  email_verified: number;
  phone_verified: number;
  is_active: number;
  is_banned: number;
  last_login?: string | null;
  last_seen?: string | null;
  otp_code?: string | null;
  otp_expires_at?: string | null;
  email_verification_token?: string | null;
  password_reset_token?: string | null;
  password_reset_expires?: string | null;
  created_at: string;
  updated_at: string;
  trade?: string;
  hourly_rate?: number;
}

export interface DbWalletRecord {
  id: number;
  user_id: number;
  balance: number;
  pending_balance: number;
  currency: string;
}

export interface DbProviderRecord {
  id: number;
  user_id: number;
  tagline: string;
  hourly_rate: number;
  rating_avg: number;
  rating_count: number;
  completed_jobs: number;
  is_verified: number;
  is_featured: number;
  availability: string;
  experience_level: string;
}

// Local storage path for offline resilience
const STORE_DIR = path.join(process.cwd(), '.data');
const STORE_FILE = path.join(STORE_DIR, 'gigghana_store.json');

const INITIAL_SEED_USERS: DbUserRecord[] = [
  {
    id: 1,
    uuid: 'cc186d2b-84a0-4405-a6d4-d65d9bf8877a',
    first_name: 'Joe',
    last_name: 'Vardy',
    email: 'joevardy2004@gmail.com',
    phone: '0244001122',
    password_hash: '$2y$12$RSSTyFyJAy9/jCxJ/h9VQejzet1a4hVMAVSDADw2Lwz//WXsk9IXS',
    role: 'client',
    location: 'Airport Hills, Accra',
    country: 'Ghana',
    ghana_card_verified: 1,
    payment_verified: 1,
    email_verified: 1,
    phone_verified: 1,
    is_active: 1,
    is_banned: 0,
    created_at: '2026-03-14 00:35:52',
    updated_at: '2026-03-15 11:55:33',
  },
  {
    id: 2,
    uuid: '864acfe1-d137-42e1-ba9a-d7cfdf9a0260',
    first_name: 'GigGhana',
    last_name: 'Admin',
    email: 'admin@gigghana.com',
    phone: '0200000000',
    password_hash: '$2y$12$DyQoSWWnE0n3H79LFqatc.qeyCExuAkL5i.J8XfyHkxQd7o0yqyfW',
    role: 'admin',
    location: 'Accra Industrial',
    country: 'Ghana',
    ghana_card_verified: 1,
    payment_verified: 1,
    email_verified: 1,
    phone_verified: 1,
    is_active: 1,
    is_banned: 0,
    created_at: '2026-03-14 00:54:56',
    updated_at: '2026-03-15 18:04:57',
  },
  {
    id: 3,
    uuid: 'a861f805-d2f5-4dbe-9d79-7a97bb96295c',
    first_name: 'Musah',
    last_name: 'Sadik',
    email: 'abubakarsadikmusah2004@gmail.com',
    phone: '+233256259336',
    password_hash: '$2y$12$wWj02FRM8UQJuIqBab0C8.cW.cWe8EAMf2qar2YNRFdF7RTkQqUo6',
    role: 'provider',
    location: 'Kumasi Central, Ashanti',
    country: 'Ghana',
    trade: 'Interior & Exterior Painting & Stucco',
    hourly_rate: 75,
    ghana_card_verified: 1,
    payment_verified: 1,
    email_verified: 1,
    phone_verified: 1,
    is_active: 1,
    is_banned: 0,
    created_at: '2026-03-14 16:24:04',
    updated_at: '2026-03-15 16:49:02',
  },
  {
    id: 4,
    uuid: '16e23691-209b-11f1-9e6e-f0761c2b872c',
    first_name: 'Kwame',
    last_name: 'Asante',
    email: 'kwame.asante@gigghana.com',
    phone: '0244123456',
    password_hash: '$2y$12$RSSTyFyJAy9/jCxJ/h9VQejzet1a4hVMAVSDADw2Lwz//WXsk9IXS',
    role: 'provider',
    location: 'Airport Hills, Accra',
    country: 'Ghana',
    trade: 'POP Ceilings & Decorative Plastering',
    hourly_rate: 85,
    ghana_card_verified: 1,
    payment_verified: 1,
    email_verified: 1,
    phone_verified: 1,
    is_active: 1,
    is_banned: 0,
    created_at: '2026-03-15 18:16:30',
    updated_at: '2026-03-15 18:16:30',
  },
  {
    id: 5,
    uuid: '9c1b2a7c-4ee2-45b7-8d65-2351711f2eef',
    first_name: 'Dr. Kwabena',
    last_name: 'Frimpong',
    email: 'k.frimpong@legonholdings.com',
    phone: '0208910022',
    password_hash: '$2y$12$NMlL.odjbvxGyEGrVGUhy.eX6pfFi6DH7e3fz.Q3yT1mqaWxObmF.',
    role: 'client',
    location: 'East Legon, Accra',
    country: 'Ghana',
    ghana_card_verified: 1,
    payment_verified: 1,
    email_verified: 1,
    phone_verified: 1,
    is_active: 1,
    is_banned: 0,
    created_at: '2026-03-15 00:19:02',
    updated_at: '2026-03-15 00:19:07',
  },
];

function readLocalStore(): {
  users: DbUserRecord[];
  wallets: DbWalletRecord[];
  providers: DbProviderRecord[];
} {
  try {
    if (!fs.existsSync(STORE_DIR)) {
      fs.mkdirSync(STORE_DIR, { recursive: true });
    }
    if (fs.existsSync(STORE_FILE)) {
      const data = fs.readFileSync(STORE_FILE, 'utf-8');
      return JSON.parse(data);
    }
  } catch (e) {
    console.error('Error reading store file:', e);
  }

  const initialStore = {
    users: INITIAL_SEED_USERS,
    wallets: INITIAL_SEED_USERS.map((u) => ({
      id: u.id,
      user_id: u.id,
      balance: 0.0,
      pending_balance: 0.0,
      currency: 'GHS',
    })),
    providers: INITIAL_SEED_USERS.filter((u) => u.role === 'provider').map((u, i) => ({
      id: i + 1,
      user_id: u.id,
      tagline: u.trade || 'Professional Ghanaian Artisan',
      hourly_rate: u.hourly_rate || 80,
      rating_avg: 5.0,
      rating_count: 12,
      completed_jobs: 18,
      is_verified: 1,
      is_featured: 1,
      availability: 'available',
      experience_level: 'master',
    })),
  };

  try {
    if (!fs.existsSync(STORE_DIR)) {
      fs.mkdirSync(STORE_DIR, { recursive: true });
    }
    fs.writeFileSync(STORE_FILE, JSON.stringify(initialStore, null, 2), 'utf-8');
  } catch {}

  return initialStore;
}

function writeLocalStore(store: {
  users: DbUserRecord[];
  wallets: DbWalletRecord[];
  providers: DbProviderRecord[];
}) {
  try {
    if (!fs.existsSync(STORE_DIR)) {
      fs.mkdirSync(STORE_DIR, { recursive: true });
    }
    fs.writeFileSync(STORE_FILE, JSON.stringify(store, null, 2), 'utf-8');
  } catch (e) {
    console.error('Error writing store file:', e);
  }
}

/**
 * Initializes MySQL tables matching gigghana.sql if MySQL is available
 */
export async function ensureAuthTables(): Promise<boolean> {
  if (!isDbConfigured()) return true;
  try {
    const db = getDbPool();
    await db.query(`
      CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT,
        uuid VARCHAR(36) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(191) NOT NULL UNIQUE,
        phone VARCHAR(20) DEFAULT NULL,
        password_hash VARCHAR(255) DEFAULT NULL,
        role ENUM('client','provider','admin') DEFAULT 'client',
        avatar VARCHAR(255) DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        location VARCHAR(150) DEFAULT NULL,
        country VARCHAR(100) DEFAULT 'Ghana',
        ghana_card_number VARCHAR(50) DEFAULT NULL,
        ghana_card_verified TINYINT(1) DEFAULT 0,
        payment_verified TINYINT(1) DEFAULT 0,
        email_verified TINYINT(1) DEFAULT 0,
        phone_verified TINYINT(1) DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        is_banned TINYINT(1) DEFAULT 0,
        last_login TIMESTAMP NULL DEFAULT NULL,
        last_seen TIMESTAMP NULL DEFAULT NULL,
        otp_code VARCHAR(10) DEFAULT NULL,
        otp_expires_at TIMESTAMP NULL DEFAULT NULL,
        email_verification_token VARCHAR(100) DEFAULT NULL,
        password_reset_token VARCHAR(100) DEFAULT NULL,
        password_reset_expires TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    `);

    await db.query(`
      CREATE TABLE IF NOT EXISTS wallets (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        balance DECIMAL(10,2) DEFAULT 0.00,
        pending_balance DECIMAL(10,2) DEFAULT 0.00,
        currency VARCHAR(10) DEFAULT 'GHS',
        PRIMARY KEY (id),
        KEY user_id (user_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    `);

    await db.query(`
      CREATE TABLE IF NOT EXISTS providers (
        id INT(11) NOT NULL AUTO_INCREMENT,
        user_id INT(11) NOT NULL,
        tagline VARCHAR(255) DEFAULT NULL,
        hourly_rate DECIMAL(10,2) DEFAULT 0.00,
        rating_avg DECIMAL(3,2) DEFAULT 5.00,
        rating_count INT(11) DEFAULT 0,
        completed_jobs INT(11) DEFAULT 0,
        is_verified TINYINT(1) DEFAULT 0,
        is_featured TINYINT(1) DEFAULT 0,
        availability VARCHAR(50) DEFAULT 'available',
        experience_level VARCHAR(50) DEFAULT 'expert',
        PRIMARY KEY (id),
        KEY user_id (user_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    `);

    return true;
  } catch (e: any) {
    return false;
  }
}

/**
 * Finds user by email or phone number in MySQL (with fallback to local store)
 */
export async function dbFindUserByIdentifier(identifier: string): Promise<DbUserRecord | null> {
  const clean = identifier.trim().toLowerCase();
  const cleanPhone = identifier.replace(/\D/g, '');

  if (isDbConfigured()) {
    try {
      const db = getDbPool();
      const [rows]: any = await db.query(
        `SELECT * FROM users WHERE LOWER(email) = ? OR REPLACE(REPLACE(phone, ' ', ''), '-', '') LIKE ? LIMIT 1`,
        [clean, `%${cleanPhone}%`]
      );
      if (rows && rows.length > 0) {
        return rows[0] as DbUserRecord;
      }
    } catch {}
  }

  // Local fallback
  const store = readLocalStore();
  const found = store.users.find(
    (u) =>
      u.email.toLowerCase() === clean ||
      (cleanPhone && u.phone && u.phone.replace(/\D/g, '').includes(cleanPhone))
  );
  return found || null;
}

/**
 * Finds user by ID in MySQL (with fallback to local store)
 */
export async function dbFindUserById(id: number | string): Promise<DbUserRecord | null> {
  const numId = Number(id);

  if (isDbConfigured()) {
    try {
      const db = getDbPool();
      const [rows]: any = await db.query(`SELECT * FROM users WHERE id = ? LIMIT 1`, [numId]);
      if (rows && rows.length > 0) {
        return rows[0] as DbUserRecord;
      }
    } catch {}
  }

  const store = readLocalStore();
  return store.users.find((u) => u.id === numId) || null;
}

/**
 * Creates user in MySQL (with fallback to local store)
 */
export async function dbCreateUser(userData: Omit<DbUserRecord, 'id' | 'created_at' | 'updated_at'>): Promise<DbUserRecord> {
  const now = new Date().toISOString().slice(0, 19).replace('T', ' ');

  if (isDbConfigured()) {
    try {
      const db = getDbPool();
      const [result]: any = await db.query(
        `INSERT INTO users (
          uuid, first_name, last_name, email, phone, password_hash, role,
          location, country, ghana_card_number, ghana_card_verified,
          email_verified, phone_verified, is_active, is_banned,
          otp_code, otp_expires_at, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())`,
        [
          userData.uuid,
          userData.first_name,
          userData.last_name,
          userData.email,
          userData.phone,
          userData.password_hash,
          userData.role,
          userData.location || 'Accra, Greater Accra',
          userData.country || 'Ghana',
          userData.ghana_card_number || null,
          userData.ghana_card_verified || 0,
          userData.email_verified || 0,
          userData.phone_verified || 0,
          userData.is_active || 1,
          userData.is_banned || 0,
          userData.otp_code || null,
          userData.otp_expires_at || null,
        ]
      );

      const newId = result.insertId;

      // Create wallet
      await db.query(`INSERT INTO wallets (user_id, balance, pending_balance, currency) VALUES (?, 0.00, 0.00, 'GHS')`, [newId]);

      // If provider, create provider profile
      if (userData.role === 'provider') {
        await db.query(
          `INSERT INTO providers (user_id, tagline, hourly_rate, rating_avg, rating_count, completed_jobs, is_verified, availability) VALUES (?, ?, ?, 5.00, 0, 0, ?, 'available')`,
          [newId, userData.trade || 'Skilled Master Artisan', userData.hourly_rate || 80, userData.ghana_card_verified || 0]
        );
      }

      return {
        ...userData,
        id: newId,
        created_at: now,
        updated_at: now,
      };
    } catch {}
  }

  // Local fallback
  const store = readLocalStore();
  const nextId = store.users.length > 0 ? Math.max(...store.users.map((u) => u.id)) + 1 : 1;

  const newUser: DbUserRecord = {
    ...userData,
    id: nextId,
    created_at: now,
    updated_at: now,
  };

  store.users.push(newUser);
  store.wallets.push({
    id: nextId,
    user_id: nextId,
    balance: 0.0,
    pending_balance: 0.0,
    currency: 'GHS',
  });

  if (userData.role === 'provider') {
    store.providers.push({
      id: store.providers.length + 1,
      user_id: nextId,
      tagline: userData.trade || 'Skilled Master Artisan',
      hourly_rate: userData.hourly_rate || 80,
      rating_avg: 5.0,
      rating_count: 0,
      completed_jobs: 0,
      is_verified: userData.ghana_card_verified || 0,
      is_featured: 0,
      availability: 'available',
      experience_level: 'master',
    });
  }

  writeLocalStore(store);
  return newUser;
}

/**
 * Updates user in MySQL (with fallback to local store)
 */
export async function dbUpdateUser(id: number, updates: Partial<DbUserRecord>): Promise<boolean> {
  if (isDbConfigured()) {
    try {
      const db = getDbPool();
      const setClauses: string[] = [];
      const values: any[] = [];

      for (const [key, val] of Object.entries(updates)) {
        setClauses.push(`${key} = ?`);
        values.push(val);
      }

      if (setClauses.length > 0) {
        values.push(id);
        await db.query(`UPDATE users SET ${setClauses.join(', ')}, updated_at = NOW() WHERE id = ?`, values);
        return true;
      }
    } catch {}
  }

  const store = readLocalStore();
  const idx = store.users.findIndex((u) => u.id === id);
  if (idx !== -1) {
    store.users[idx] = {
      ...store.users[idx],
      ...updates,
      updated_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
    };
    writeLocalStore(store);
    return true;
  }
  return false;
}

/* ══════════════════════════════════════════════════════
   HOMEPAGE LANDING DATA
══════════════════════════════════════════════════════ */

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
    return defaultLandingData;
  }
}
