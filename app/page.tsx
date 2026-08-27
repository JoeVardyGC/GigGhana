import { getCachedLandingPageData } from "@/lib/cached-data";
import LandingPage from "@/components/LandingPage";

export const revalidate = 60; // 60s ISR revalidation

export default async function Page() {
  const data = await getCachedLandingPageData();
  return <LandingPage initialData={data} />;
}
