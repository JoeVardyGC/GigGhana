# GigGhana Developer Specifications & Technical Reference Matrix

This document codifies the official documentation, API specifications, and design registries strictly adhered to during development.

---

## 1. ⚡ Next.js 15 & React 19 App Router Specifications
- **Data Caching**: Next.js 15 `unstable_cache` with named tags (`revalidateTag`) and 60-second default stale-while-revalidate windows.
- **Server Actions**: Mutating operations strictly use Server Actions (`'use server'`) with Zod schema validation.
- **Session Management**: Secure, HTTP-only, SameSite=Strict cookies with JWT / Iron Session tokens.

---

## 2. 🧩 UI Registry & Design System Specs (Shadcn & 21st.dev)
- **Design Tokens**: Modern Blue palette (`--blue-primary: #2563EB`, `--blue-electric: #3B82F6`, `--bg: #080D1A`, `--surface: #111C35`, `.lm` light mode).
- **Typography**: `Plus Jakarta Sans` for Headings & `DM Sans` for Body.
- **Accessibility**: 100% WCAG 2.1 AA compliance, keyboard focus rings (`focus-visible:ring-2`), and descriptive ARIA roles.

---

## 3. 💳 Paystack & Mobile Money (MoMo) API Specifications
- **API Base**: `https://api.paystack.co`
- **Currency**: `GHS` (Ghanaian Cedi, values converted to Pesewas `amount * 100` for Paystack).
- **Channels**: `['mobile_money', 'card', 'bank']`
- **Supported Ghana Telcos**:
  - `mtn`: MTN Mobile Money (MoMo)
  - `vod` / `telecel`: Telecel Cash (formerly Vodafone Cash)
  - `tgo` / `at`: AT Money (formerly AirtelTigo)
- **Webhook Security**: Verify `x-paystack-signature` against `HMAC_SHA512(payload, PAYSTACK_SECRET_KEY)`.

---

## 4. 🇬🇭 Ghana National Identification Authority (NIA) Card Specs
- **PIN Schema**: `^GHA-[0-9]{9}-[0-9]{1}$` (e.g. `GHA-712894012-4`).
- **Physical ID-1 Format**: Dual-language English & French fields, 6-contact gold EMV smart chip, machine-readable optical zone (MRZ).

---

## 5. 🗄️ Database & ACID Transaction Rules (MariaDB 10.11)
- **Transaction Safety**: All wallet balance changes, escrow status transitions, and proposal acceptances MUST run within `db.beginTransaction()`, `db.commit()`, and `db.rollback()`.
- **Platform Commission**: Fixed `10%` platform fee (`PLATFORM_FEE_PERCENT = 10`).
- **Minimum Withdrawal**: `₵50.00 GHS` (`MIN_WITHDRAWAL = 50`).
