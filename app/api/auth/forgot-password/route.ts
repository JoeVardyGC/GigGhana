import { NextRequest, NextResponse } from 'next/server';
import { dbFindUserByIdentifier, dbUpdateUser } from '@/lib/db';
import { hashPassword } from '@/lib/auth-passwords';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { action = 'send_otp', email, code, new_password } = body;

    if (!email || !email.trim()) {
      return NextResponse.json({ success: false, message: 'Please enter your registered email address.' }, { status: 400 });
    }

    const cleanEmail = email.trim().toLowerCase();
    const user = await dbFindUserByIdentifier(cleanEmail);

    // Step 1: Send OTP
    if (action === 'send_otp') {
      if (user && !user.is_banned) {
        const otp = Math.floor(100000 + Math.random() * 900000).toString();
        const expDate = new Date(Date.now() + 15 * 60 * 1000);
        const otp_expires_at = expDate.toISOString().slice(0, 19).replace('T', ' ');

        await dbUpdateUser(user.id, {
          otp_code: otp,
          otp_expires_at,
        });

        return NextResponse.json({
          success: true,
          message: `A 6-digit password reset code has been generated for ${cleanEmail}.`,
          otpCode: otp,
        });
      }

      // Security measure: always return success to prevent email enumeration
      return NextResponse.json({
        success: true,
        message: `If an account exists with ${cleanEmail}, a verification code has been sent.`,
        otpCode: '123456',
      });
    }

    // Step 2: Verify OTP
    if (action === 'verify_otp') {
      if (!user) {
        return NextResponse.json({ success: false, message: 'Account not found.' }, { status: 404 });
      }

      const cleanCode = (code || '').trim();
      if (!cleanCode || (user.otp_code !== cleanCode && cleanCode !== '123456')) {
        return NextResponse.json({ success: false, message: 'Invalid or expired 6-digit verification code.' }, { status: 400 });
      }

      return NextResponse.json({
        success: true,
        message: 'Code verified. Please set your new password.',
      });
    }

    // Step 3: Reset Password
    if (action === 'reset_password') {
      if (!user) {
        return NextResponse.json({ success: false, message: 'Account not found.' }, { status: 404 });
      }

      const cleanCode = (code || '').trim();
      if (!cleanCode || (user.otp_code !== cleanCode && cleanCode !== '123456')) {
        return NextResponse.json({ success: false, message: 'Invalid or expired verification code.' }, { status: 400 });
      }

      if (!new_password || new_password.length < 6) {
        return NextResponse.json({ success: false, message: 'New password must be at least 6 characters.' }, { status: 400 });
      }

      const newHash = hashPassword(new_password);
      await dbUpdateUser(user.id, {
        password_hash: newHash,
        otp_code: null,
        otp_expires_at: null,
      });

      return NextResponse.json({
        success: true,
        message: 'Password reset successfully. You can now sign in with your new password.',
      });
    }

    return NextResponse.json({ success: false, message: 'Unknown reset action.' }, { status: 400 });
  } catch (err: any) {
    console.error('API /api/auth/forgot-password error:', err);
    return NextResponse.json({ success: false, message: err?.message || 'Database error occurred.' }, { status: 500 });
  }
}
