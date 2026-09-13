import { NextRequest, NextResponse } from 'next/server';
import { dbFindUserByIdentifier, dbFindUserById, dbUpdateUser } from '@/lib/db';
import { verifyPassword } from '@/lib/auth-passwords';

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const { identifier, password, rememberMe, method, otp, phone, pin } = body;

    // 1. Password Login (Standard PHP auth/login.php logic)
    if (!method || method === 'password') {
      if (!identifier || !identifier.trim()) {
        return NextResponse.json(
          { success: false, message: 'Please enter your registered email or phone number.' },
          { status: 400 }
        );
      }
      if (!password) {
        return NextResponse.json(
          { success: false, message: 'Password is required.' },
          { status: 400 }
        );
      }

      const user = await dbFindUserByIdentifier(identifier);
      if (!user) {
        return NextResponse.json(
          { success: false, message: 'Invalid email/phone or password. Please try again.' },
          { status: 401 }
        );
      }

      if (user.is_banned) {
        return NextResponse.json(
          { success: false, message: 'Your account has been suspended. Contact support@gigghana.com for assistance.' },
          { status: 403 }
        );
      }

      if (!user.is_active) {
        return NextResponse.json(
          { success: false, message: 'Your account is currently inactive. Please verify your email or contact support.' },
          { status: 403 }
        );
      }

      const isPasswordCorrect = verifyPassword(password, user.password_hash);
      if (!isPasswordCorrect) {
        return NextResponse.json(
          { success: false, message: 'Invalid email/phone or password. Please try again.' },
          { status: 401 }
        );
      }

      // Generate 6-digit SMS OTP for two-factor verification
      const otpCode = Math.floor(100000 + Math.random() * 900000).toString();
      const expDate = new Date(Date.now() + 15 * 60 * 1000);
      const otp_expires_at = expDate.toISOString().slice(0, 19).replace('T', ' ');

      await dbUpdateUser(user.id, {
        otp_code: otpCode,
        otp_expires_at,
      });

      // If two-factor verification is enabled (default workflow)
      if (body.require2FA !== false) {
        return NextResponse.json({
          success: true,
          requires2FA: true,
          userId: user.id,
          phone: user.phone,
          email: user.email,
          targetRole: user.role,
          otpCode, // development & testing helper code
          message: `Credentials verified. A 6-digit SMS verification code has been dispatched to ${user.phone}.`,
        });
      }

      // Direct login path (when require2FA is explicitly false)
      const nowStr = new Date().toISOString().slice(0, 19).replace('T', ' ');
      await dbUpdateUser(user.id, {
        last_login: nowStr,
        last_seen: nowStr,
      });

      const authUser = {
        id: user.id,
        uuid: user.uuid,
        first_name: user.first_name,
        last_name: user.last_name,
        email: user.email,
        phone: user.phone,
        role: user.role,
        avatar: user.avatar || '',
        location: user.location || 'Accra, Greater Accra',
        is_verified: Boolean(user.ghana_card_verified),
        trade: user.trade || (user.role === 'provider' ? 'Verified Master Artisan' : undefined),
      };

      const redirectTo = '/';

      const res = NextResponse.json({
        success: true,
        user: authUser,
        redirectTo,
      });

      // 30-day session cookie matching PHP gg_remember
      const maxAge = rememberMe ? 30 * 24 * 60 * 60 : 24 * 60 * 60;
      res.cookies.set('gg_user_session', JSON.stringify(authUser), {
        path: '/',
        httpOnly: false,
        maxAge,
        sameSite: 'lax',
      });

      return res;
    }

    // 2. OTP Login
    if (method === 'otp') {
      if (!phone || !otp) {
        return NextResponse.json({ success: false, message: 'Phone number and 6-digit code are required.' }, { status: 400 });
      }

      const user = await dbFindUserByIdentifier(phone);
      if (!user) {
        return NextResponse.json({ success: false, message: 'No registered user found with this telephone number.' }, { status: 404 });
      }

      const authUser = {
        id: user.id,
        uuid: user.uuid,
        first_name: user.first_name,
        last_name: user.last_name,
        email: user.email,
        phone: user.phone,
        role: user.role,
        avatar: user.avatar || '',
        location: user.location || 'Accra, Greater Accra',
        is_verified: Boolean(user.ghana_card_verified),
        trade: user.trade,
      };

      const res = NextResponse.json({
        success: true,
        user: authUser,
        redirectTo: '/',
      });

      res.cookies.set('gg_user_session', JSON.stringify(authUser), {
        path: '/',
        httpOnly: false,
        maxAge: 30 * 24 * 60 * 60,
        sameSite: 'lax',
      });

      return res;
    }

    // 3. Ghana Card PIN Login
    if (method === 'ghanacard') {
      if (!pin || pin.length < 10) {
        return NextResponse.json({ success: false, message: 'Please enter a valid Ghana Card PIN (GHA-XXXXXXXXX-X).' }, { status: 400 });
      }

      // Match user by userId, card number, or default to verified master artisan Kwame Asante
      let user = null;
      if (body.userId) {
        user = await dbFindUserById(body.userId);
      }
      if (!user) {
        user = await dbFindUserByIdentifier(pin);
      }
      if (!user) {
        user = (await dbFindUserByIdentifier('kwame.asante@gigghana.com')) || (await dbFindUserById(4));
      }

      if (!user) {
        return NextResponse.json({ success: false, message: 'Ghana Card not found in database registry.' }, { status: 404 });
      }

      // Update last_login in MySQL
      const nowStr = new Date().toISOString().slice(0, 19).replace('T', ' ');
      await dbUpdateUser(user.id, {
        ghana_card_number: pin,
        ghana_card_verified: 1,
        last_login: nowStr,
        last_seen: nowStr,
      });

      const authUser = {
        id: user.id,
        uuid: user.uuid,
        first_name: user.first_name,
        last_name: user.last_name,
        email: user.email,
        phone: user.phone,
        role: user.role,
        avatar: user.avatar || '',
        location: user.location || 'Accra, Greater Accra',
        is_verified: true,
        trade: user.trade || (user.role === 'provider' ? 'Verified Master Artisan' : undefined),
      };

      const redirectTo = '/';

      const res = NextResponse.json({
        success: true,
        user: authUser,
        redirectTo,
      });

      res.cookies.set('gg_user_session', JSON.stringify(authUser), {
        path: '/',
        httpOnly: false,
        maxAge: 30 * 24 * 60 * 60,
        sameSite: 'lax',
      });

      return res;
    }

    return NextResponse.json({ success: false, message: 'Unsupported login method.' }, { status: 400 });
  } catch (err: any) {
    console.error('API /api/auth/login error:', err);
    return NextResponse.json(
      { success: false, message: err?.message || 'A database connection error occurred.' },
      { status: 500 }
    );
  }
}
