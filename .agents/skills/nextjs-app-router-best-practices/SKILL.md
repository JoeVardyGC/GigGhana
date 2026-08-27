---
name: nextjs-app-router-best-practices
description: Best practices for building high-performance, accessible, full-stack Next.js 15 App Router applications with Server Components, ISR caching, Server Actions, and Zod validation.
---

# Next.js 15 App Router Best Practices Skill

## Guidelines
1. **Server Components (RSC) by Default**:
   - Keep page-level data fetching inside async Server Components.
   - Use `'use client'` only on interactive leaf nodes (modals, sliders, forms, tickers).
2. **Data Caching & ISR**:
   - Use Next.js 15 `unstable_cache` with revalidation tags (e.g. `revalidate = 60`) for public search and landing page data.
3. **Server Actions & Form Validation**:
   - Validate all form submissions with Zod schemas.
   - Handle database mutations securely with parameterized SQL queries.
4. **Theme & Styling**:
   - Native dark mode and `.lm` light mode powered by CSS variables and Tailwind CSS.
