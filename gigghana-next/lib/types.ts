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
