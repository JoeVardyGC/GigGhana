import { getCachedLandingPageData } from "@/lib/cached-data";
import ModernBlueLandingPage from "@/components/ModernBlueLandingPage";

export const revalidate = 60; // 60s ISR revalidation

export default async function Page() {
  const data = await getCachedLandingPageData();
  return <ModernBlueLandingPage initialData={data} />;
}
