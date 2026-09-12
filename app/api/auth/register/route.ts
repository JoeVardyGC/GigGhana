import { NextRequest, NextResponse } from 'next/server';
import crypto from 'crypto';
import { dbFindUserByIdentifier, dbCreateUser } from '@/lib/db';
import { hashPassword } from '@/lib/auth-passwords';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const {
      first_name,
      last_name,
      email,
      phone,
      password,
      role = 'client',
      location,
      trade,
      hourly_rate,
      ghana_card_pin,
      is_verified,
    } = body;

    // Validation matching PHP auth/register.php
    if (!first_name || first_name.trim().length < 2) {
      return NextResponse.json({ success: false, message: 'First name must be at least 2 characters.' }, { status: 400 });
    }
    if (!last_name || last_name.trim().length < 2) {
      return NextResponse.json({ success: false, message: 'Last name must be at least 2 characters.' }, { status: 400 });
    }
    if (!email || !email.includes('@')) {
      return NextResponse.json({ success: false, message: 'Please enter a valid email address.' }, { status: 400 });
    }
    if (!password || password.length < 6) {
      return NextResponse.json({ success: false, message: 'Password must be at least 6 characters.' }, { status: 400 });
    }

    const cleanEmail = email.trim().toLowerCase();
    const existing = await dbFindUserByIdentifier(cleanEmail);
    if (existing) {
      return NextResponse.json(
        { success: false, message: 'An account with this email address already exists. Try signing in instead.' },
        { status: 409 }
      );
    }

    // Generate credentials & OTP
    const uuid = crypto.randomUUID();
    const password_hash = hashPassword(password);
    const otp_code = Math.floor(100000 + Math.random() * 900000).toString();
    const expDate = new Date(Date.now() + 15 * 60 * 1000);
    const otp_expires_at = expDate.toISOString().slice(0, 19).replace('T', ' ');

    const newUser = await dbCreateUser({
      uuid,
      first_name: first_name.trim(),
      last_name: last_name.trim(),
      email: cleanEmail,
      phone: phone ? phone.trim() : '0240000000',
      password_hash,
      role: role === 'provider' ? 'provider' : 'client',
      location: location || 'Accra, Greater Accra',
      country: 'Ghana',
      ghana_card_number: ghana_card_pin || null,
      ghana_card_verified: is_verified ? 1 : 0,
      payment_verified: 0,
      email_verified: 0,
      phone_verified: 0,
      is_active: 1,
      is_banned: 0,
      otp_code,
      otp_expires_at,
      trade: trade || undefined,
      hourly_rate: hourly_rate ? Number(hourly_rate) : 80,
    });

    const authUser = {
      id: newUser.id,
      uuid: newUser.uuid,
      first_name: newUser.first_name,
      last_name: newUser.last_name,
      email: newUser.email,
      phone: newUser.phone,
      role: newUser.role,
      location: newUser.location,
      is_verified: Boolean(newUser.ghana_card_verified),
      trade: newUser.trade,
    };

    const res = NextResponse.json({
      success: true,
      user: authUser,
      otpCode: otp_code,
      message: 'Account created! Please verify your 6-digit OTP code to continue.',
      redirectTo: `/auth/verify-otp?userId=${newUser.id}&email=${encodeURIComponent(newUser.email)}`,
    });

    // Temporary pending cookie for OTP verification
    res.cookies.set('gg_pending_auth', JSON.stringify({ userId: newUser.id, email: newUser.email }), {
      path: '/',
      httpOnly: false,
      maxAge: 15 * 60,
    });

    return res;
  } catch (err: any) {
    console.error('API /api/auth/register error:', err);
    return NextResponse.json(
      { success: false, message: err?.message || 'Failed to complete registration in database.' },
      { status: 500 }
    );
  }
}
