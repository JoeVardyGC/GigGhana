import type { Config } from "tailwindcss";

const config: Config = {
  content: [
    "./pages/**/*.{js,ts,jsx,tsx,mdx}",
    "./components/**/*.{js,ts,jsx,tsx,mdx}",
    "./app/**/*.{js,ts,jsx,tsx,mdx}",
  ],
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        background: "var(--bg)",
        foreground: "var(--tx)",
        surface: "var(--surface)",
        blue: {
          primary: "var(--blue-primary)",
          electric: "var(--blue-electric)",
          light: "var(--blue-light)",
          dim: "var(--blue-dim)",
        },
        cyan: {
          DEFAULT: "var(--cyan)",
          light: "var(--cyan-light)",
          dim: "var(--cyan-dim)",
        },
        emerald: {
          DEFAULT: "var(--emerald)",
          dim: "var(--emerald-dim)",
        },
      },
      fontFamily: {
        heading: ["var(--font-heading)", "Plus Jakarta Sans", "-apple-system", "sans-serif"],
        body: ["var(--font-body)", "DM Sans", "-apple-system", "sans-serif"],
        sans: ["var(--font-body)", "DM Sans", "-apple-system", "sans-serif"],
      },
      animation: {
        marquee: 'marquee var(--duration) linear infinite',
        'marquee-vertical': 'marquee-vertical var(--duration) linear infinite',
      },
      keyframes: {
        marquee: {
          from: { transform: 'translateX(0)' },
          to: { transform: 'translateX(calc(-100% - var(--gap)))' },
        },
        'marquee-vertical': {
          from: { transform: 'translateY(0)' },
          to: { transform: 'translateY(calc(-100% - var(--gap)))' },
        },
      },
    },
  },
  plugins: [],
};
export default config;
