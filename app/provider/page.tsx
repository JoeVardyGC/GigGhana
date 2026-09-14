import { redirect } from 'next/navigation';

export default function ProviderRootRedirect() {
  redirect('/provider/dashboard');
}
