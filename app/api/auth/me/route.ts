import { NextRequest, NextResponse } from 'next/server';
import { dbFindUserById } from '@/lib/db';

export async function GET(request: NextRequest) {
  try {
    const sessionCookie = request.cookies.get('gg_user_session');
    if (!sessionCookie || !sessionCookie.value) {
      return NextResponse.json({ authenticated: false, user: null });
    }

    const parsed = JSON.parse(sessionCookie.value);
    if (!parsed || !parsed.id) {
      return NextResponse.json({ authenticated: false, user: null });
    }

    // Refresh from database
    const dbUser = await dbFindUserById(parsed.id);
    if (!dbUser || dbUser.is_banned || !dbUser.is_active) {
      const res = NextResponse.json({ authenticated: false, user: null });
      res.cookies.delete('gg_user_session');
      return res;
    }

    const authUser = {
      id: dbUser.id,
      uuid: dbUser.uuid,
      first_name: dbUser.first_name,
      last_name: dbUser.last_name,
      email: dbUser.email,
      phone: dbUser.phone,
      role: dbUser.role,
      avatar: dbUser.avatar || '',
      location: dbUser.location,
      is_verified: Boolean(dbUser.ghana_card_verified),
      trade: dbUser.trade,
    };

    return NextResponse.json({ authenticated: true, user: authUser });
  } catch {
    return NextResponse.json({ authenticated: false, user: null });
  }
}
