export const iconMap: Record<string, string> = {
  'code': '💻',
  'smartphone': '📱',
  'pen-tool': '🎨',
  'trending-up': '📈',
  'file-text': '✍️',
  'film': '🎬',
  'cpu': '🤖',
  'dollar-sign': '💰',
  'briefcase': '⚖️',
  'headphones': '🎧',
  'camera': '📷',
  'globe': '🌐',
  'tool': '🔧',
  'bar-chart': '📊',
  'music': '🎵',
};

export const ghanaFallbackCats = [
  { id: 'tech', icon: 'code', name: 'IT & Tech', description: 'Web Dev, App Dev, Digital Marketing', slug: 'it-tech' },
  { id: 'design', icon: 'pen-tool', name: 'Creative Arts', description: 'Graphics, Photography, Video', slug: 'creative-arts' },
  { id: 'trades', icon: 'tool', name: 'Skilled Trades', description: 'Carpenter, Plumber, Electrician', slug: 'skilled-trades' },
  { id: 'health', icon: 'headphones', name: 'Health & Wellness', description: 'Nurse, Physio, Fitness Coach', slug: 'health-wellness' },
  { id: 'build', icon: 'briefcase', name: 'Construction', description: 'Builder, Architect, Surveyor', slug: 'construction' },
  { id: 'edu', icon: 'file-text', name: 'Education & Tutoring', description: 'Teacher, Music, Art Instructor', slug: 'education' },
  { id: 'hosp', icon: 'bar-chart', name: 'Hospitality', description: 'Chef, Event Planner, Driver', slug: 'hospitality' },
  { id: 'biz', icon: 'dollar-sign', name: 'Business Services', description: 'Accountant, Consultant, Admin', slug: 'business' },
  { id: 'farm', icon: 'globe', name: 'Agriculture', description: 'Farmer, Agri-tech, Livestock', slug: 'agriculture' },
  { id: 'other', icon: 'trending-up', name: 'Others', description: 'Delivery, Security, Handyman', slug: 'others' },
];

export const testimonialFallbacks = [
  { first_name: 'Kwame', last_name: 'Asante', comment: "I'm a painter and GigGhana changed my life — I now get 5 jobs per week from clients I never could have reached before!", rating_overall: 5, role: 'provider', location: 'Accra', avatar: '' },
  { first_name: 'Abena', last_name: 'Mensah', comment: "As a nurse, I now offer home care through GigGhana. The platform is safe and payments always come on time via MoMo.", rating_overall: 5, role: 'provider', location: 'Kumasi', avatar: '' },
  { first_name: 'Kofi', last_name: 'Boateng', comment: "Carpenters like me now get long-term contracts. GigGhana verified my skills and clients trust me immediately.", rating_overall: 5, role: 'provider', location: 'Takoradi', avatar: '' },
  { first_name: 'Ama', last_name: 'Owusu', comment: "Finding a reliable electrician used to be a nightmare. Now I hire through GigGhana in minutes and the escrow keeps me safe.", rating_overall: 5, role: 'client', location: 'Accra', avatar: '' },
];

export const fallbackRecentJobs = [
  {
    id: 101,
    title: 'Interior Renovation & Modern POP Ceiling for 4-Bedroom Villa',
    description: 'Looking for a certified Ghanaian interior plasterer/artisan to design and install custom POP ceilings with embedded LED ambient coving in Airport Hills.',
    budget_min: 4500,
    budget_max: 6000,
    budget_type: 'fixed',
    is_urgent: 1,
    is_featured: 1,
    proposal_count: 4,
    location: 'Airport Hills, Accra',
    cat_name: 'Creative Arts',
    cat_icon: 'pen-tool',
    created_at: new Date(Date.now() - 25 * 60 * 1000).toISOString(),
    first_name: 'Emmanuel',
    last_name: 'Agyeman',
  },
  {
    id: 102,
    title: 'Full-Stack Next.js 15 & Mobile Money (MoMo) Escrow Web Platform',
    description: 'Seeking an expert Ghanaian TypeScript developer to build a high-performance marketplace API with Paystack webhook processing and automated sub-60s MoMo payouts.',
    budget_min: 8500,
    budget_max: 12000,
    budget_type: 'fixed',
    is_urgent: 0,
    is_featured: 1,
    proposal_count: 7,
    location: 'East Legon, Accra',
    cat_name: 'IT & Tech',
    cat_icon: 'code',
    created_at: new Date(Date.now() - 65 * 60 * 1000).toISOString(),
    first_name: 'Selorm',
    last_name: 'Klutse',
  },
  {
    id: 103,
    title: '15kVA Commercial Solar Inverter & Hybrid Storage Installation',
    description: 'Require a certified renewable energy electrician to install a 15kVA 3-phase hybrid solar power system with lithium batteries for our logistics hub.',
    budget_min: 14000,
    budget_max: 18500,
    budget_type: 'fixed',
    is_urgent: 1,
    is_featured: 0,
    proposal_count: 3,
    location: 'Kumasi Central, Ashanti',
    cat_name: 'Skilled Trades',
    cat_icon: 'tool',
    created_at: new Date(Date.now() - 130 * 60 * 1000).toISOString(),
    first_name: 'Kwabena',
    last_name: 'Osei',
  },
  {
    id: 104,
    title: 'Architectural 3D Structural Modeling & Municipality Permit Drawings',
    description: 'Need a registered Ghanaian architect or structural draftsman to produce 3D isometric renderings and approved permit drawings for a commercial plaza in Takoradi.',
    budget_min: 5200,
    budget_max: 7500,
    budget_type: 'fixed',
    is_urgent: 0,
    is_featured: 1,
    proposal_count: 5,
    location: 'Takoradi, Western',
    cat_name: 'Construction',
    cat_icon: 'briefcase',
    created_at: new Date(Date.now() - 210 * 60 * 1000).toISOString(),
    first_name: 'Nana',
    last_name: 'Yeboah',
  },
  {
    id: 105,
    title: 'Commercial Restaurant Kitchen Plumbing & Grease Trap System',
    description: 'Licensed master plumber needed to re-pipe industrial drainage and connect high-pressure supply lines for a newly opened eatery in Tema Community 1.',
    budget_min: 3200,
    budget_max: 4500,
    budget_type: 'fixed',
    is_urgent: 1,
    is_featured: 0,
    proposal_count: 2,
    location: 'Tema Comm. 1, Greater Accra',
    cat_name: 'Skilled Trades',
    cat_icon: 'tool',
    created_at: new Date(Date.now() - 320 * 60 * 1000).toISOString(),
    first_name: 'Ekow',
    last_name: 'Baidoo',
  },
  {
    id: 106,
    title: 'Brand Identity, Export Packaging & Label Design for Cocoa Product',
    description: 'Graphic designer specializing in premium FMCG packaging to craft export-ready pouch labels with Ghana FDA & barcode compliance guidelines.',
    budget_min: 2800,
    budget_max: 3900,
    budget_type: 'fixed',
    is_urgent: 0,
    is_featured: 0,
    proposal_count: 6,
    location: 'Cape Coast, Central',
    cat_name: 'Creative Arts',
    cat_icon: 'pen-tool',
    created_at: new Date(Date.now() - 480 * 60 * 1000).toISOString(),
    first_name: 'Akosua',
    last_name: 'Darko',
  },
  {
    id: 107,
    title: 'Custom Solid Hardwood Office Desks & Acoustic Wall Paneling Fabrication',
    description: 'Master carpenter needed to build 6 modern executive mahogany workstations and sound-dampening acoustic wall paneling for fintech office on Spintex Road.',
    budget_min: 6000,
    budget_max: 8500,
    budget_type: 'fixed',
    is_urgent: 1,
    is_featured: 1,
    proposal_count: 4,
    location: 'Spintex Road, Accra',
    cat_name: 'Skilled Trades',
    cat_icon: 'tool',
    created_at: new Date(Date.now() - 520 * 60 * 1000).toISOString(),
    first_name: 'Kofi',
    last_name: 'Mensah',
  },
  {
    id: 108,
    title: 'Cross-Platform React Native Mobile App with GPS Dispatch & MoMo Checkout',
    description: 'Looking for a senior mobile application engineer to finalize real-time location dispatch, push notifications, and MTN/Telecel payment SDK integration.',
    budget_min: 11000,
    budget_max: 16000,
    budget_type: 'fixed',
    is_urgent: 0,
    is_featured: 1,
    proposal_count: 8,
    location: 'Cantonments, Accra',
    cat_name: 'IT & Tech',
    cat_icon: 'code',
    created_at: new Date(Date.now() - 610 * 60 * 1000).toISOString(),
    first_name: 'Abena',
    last_name: 'Frimpong',
  }
];

export interface LandingData {
  stats: {
    providers: number;
    jobs: number;
    completed: number;
    clients: number;
    earnings: number;
  };
  categories: Array<{
    id: string | number;
    name: string;
    slug?: string;
    icon: string;
    description?: string;
  }>;
  featured: any[];
  matchedProviders: any[];
  recentJobs: any[];
  liveJobs: any[];
  earningsData: number[];
  earningsTotal: number;
  reviews: any[];
}
