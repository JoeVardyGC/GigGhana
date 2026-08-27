---
name: ghana-card-biometric-kyc
description: Guidelines and validation algorithms for verifying Ghana Card (National Identity Card) biometrics, PIN formatting (GHA-XXXXXXXXX-X), and KYC approval status.
---

# Ghana Card Biometric KYC Skill

This skill governs citizen identity verification for freelancers and master artisans on GigGhana.

## PIN Validation Format
- Format: `GHA-XXXXXXXXX-X` (where X is numeric).
- Example: `GHA-712894012-4`.
- Regular expression: `^GHA-[0-9]{9}-[0-9]{1}$`.

## Verification Lifecycle
1. **Unverified**: User registers; no Ghana Card submitted.
2. **Pending Review**: User submits PIN, document front/back photos, and live selfie.
3. **Verified**: Admin or automated NIA verification checks details and awards the `✓ Ghana Card Verified` trust badge.
4. **Rejected**: Mismatched name or illegible image; user prompted with specific reason.
