/** @type {import('next').NextConfig} */
const nextConfig = {
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: '**',
      },
      {
        protocol: 'http',
        hostname: '**',
      },
    ],
  },
  async rewrites() {
    return [
      {
        source: '/jobs.php',
        destination: '/jobs',
      },
      {
        source: '/search/providers.php',
        destination: '/search/providers',
      },
    ];
  },
};

export default nextConfig;
