import { NextResponse } from 'next/server';

export async function POST() {
  const res = NextResponse.json({ success: true, message: 'Logged out successfully.' });
  res.cookies.delete('gg_user_session');
  res.cookies.delete('gg_remember');
  res.cookies.delete('gg_pending_auth');
  return res;
}
