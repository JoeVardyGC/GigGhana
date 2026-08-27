---
name: escrow-payment-pipeline
description: Comprehensive guide and rules for implementing Escrow Vault, Milestone contracts, Paystack STK push, and sub-60s Mobile Money (MTN MoMo, Telecel Cash, AT Money) settlement workflows in GigGhana.
---

# Escrow & Mobile Money Payment Pipeline Skill

This skill governs all financial operations, wallet mutations, and Escrow Vault state transitions across the GigGhana platform.

## Core Rules

1. **ACID Transaction Wrapping**:
   - Every escrow lock, milestone release, fee deduction, and withdrawal MUST be executed inside an atomic database transaction (`START TRANSACTION`, `COMMIT`, `ROLLBACK`).
2. **Platform Fee Calculation**:
   - Platform Commission: `10%` (`PLATFORM_FEE_PERCENT = 10`).
   - On contract deposit of ₵1,000.00:
     - Platform Fee: ₵100.00
     - Provider Net Allocation: ₵900.00
3. **Multi-Channel Mobile Money Payouts**:
   - MTN Mobile Money (Network code: `mtn`)
   - Telecel Cash (Network code: `vodafone` / `telecel`)
   - AT Money (Network code: `airteltigo`)
4. **Withdrawal Safeguards**:
   - Minimum withdrawal threshold: `₵50.00 GHS`.
   - Prevent duplicate withdrawal requests using transaction idempotency keys.
