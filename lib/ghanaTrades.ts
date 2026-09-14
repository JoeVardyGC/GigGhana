/**
 * Verified Standalone Ghanaian Trades Directory
 * Over 1901 discrete standalone professions across Ghana.
 * No merged job names (no 'X, Y & Z'). Every trade is discrete and standalone.
 */

export interface GhanaTradeOption {
  id: string;
  name: string;
  category: string;
  defaultRate: number;
  iconName: string;
}

export const GHANA_TRADES: GhanaTradeOption[] = [
  {
    "id": "mason",
    "name": "Mason",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "bricklayer",
    "name": "Bricklayer",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "blocklayer",
    "name": "Blocklayer",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "concrete-worker",
    "name": "Concrete Worker",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "tiler",
    "name": "Tiler",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "floor-tiler",
    "name": "Floor Tiler",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "wall-tiler",
    "name": "Wall Tiler",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "pop-ceiling-installer",
    "name": "POP Ceiling Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "plasterer",
    "name": "Plasterer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "drywaller",
    "name": "Drywaller",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "painter",
    "name": "Painter",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "house-painter",
    "name": "House Painter",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "interior-painter",
    "name": "Interior Painter",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "exterior-painter",
    "name": "Exterior Painter",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "carpenter",
    "name": "Carpenter",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "roofing-carpenter",
    "name": "Roofing Carpenter",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "cabinet-maker",
    "name": "Cabinet Maker",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "furniture-carpenter",
    "name": "Furniture Carpenter",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "joiner",
    "name": "Joiner",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "welder",
    "name": "Welder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "metal-fabricator",
    "name": "Metal Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "aluminum-glazier",
    "name": "Aluminum Glazier",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "aluminum-fabricator",
    "name": "Aluminum Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "burglar-proof-maker",
    "name": "Burglar Proof Maker",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "roofer",
    "name": "Roofer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "roof-truss-fabricator",
    "name": "Roof Truss Fabricator",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "gutter-installer",
    "name": "Gutter Installer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "plumber",
    "name": "Plumber",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "domestic-plumber",
    "name": "Domestic Plumber",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "borehole-driller",
    "name": "Borehole Driller",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "electrician",
    "name": "Electrician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "house-wiring-electrician",
    "name": "House Wiring Electrician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "3-phase-electrician",
    "name": "3-Phase Electrician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "solar-pv-installer",
    "name": "Solar PV Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "solar-inverter-technician",
    "name": "Solar Inverter Technician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "cctv-installer",
    "name": "CCTV Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "electric-fence-installer",
    "name": "Electric Fence Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "dstv-installer",
    "name": "DSTV Installer",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "air-conditioner-technician",
    "name": "Air Conditioner Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "commercial-refrigerator-mechanic",
    "name": "Commercial Refrigerator Mechanic",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "domestic-fridge-repairer",
    "name": "Domestic Fridge Repairer",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "cold-room-technician",
    "name": "Cold Room Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "auto-mechanic",
    "name": "Auto Mechanic",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "auto-electrician",
    "name": "Auto Electrician",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "auto-spray-painter",
    "name": "Auto Spray Painter",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "panel-beater",
    "name": "Panel Beater",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "vulcanizer",
    "name": "Vulcanizer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "motorcycle-mechanic",
    "name": "Motorcycle Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "pragya-mechanic",
    "name": "Pragya Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "aboboyaa-mechanic",
    "name": "Aboboyaa Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "tailor",
    "name": "Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "seamstress",
    "name": "Seamstress",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "mens-suit-tailor",
    "name": "Mens Suit Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "kaba-and-slit-specialist",
    "name": "Kaba and Slit Specialist",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "traditional-kente-weaver",
    "name": "Traditional Kente Weaver",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "barber",
    "name": "Barber",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "master-barber",
    "name": "Master Barber",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "hairdresser",
    "name": "Hairdresser",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "hair-braider",
    "name": "Hair Braider",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "knotless-braids-specialist",
    "name": "Knotless Braids Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "dreadlocks-loctician",
    "name": "Dreadlocks Loctician",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "wig-maker",
    "name": "Wig Maker",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "makeup-artist",
    "name": "Makeup Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "bridal-makeup-artist",
    "name": "Bridal Makeup Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "nail-technician",
    "name": "Nail Technician",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "acrylic-nail-specialist",
    "name": "Acrylic Nail Specialist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "esthetician",
    "name": "Esthetician",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "massage-therapist",
    "name": "Massage Therapist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "fitness-trainer",
    "name": "Fitness Trainer",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "professional-chef",
    "name": "Professional Chef",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "domestic-cook",
    "name": "Domestic Cook",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "event-caterer",
    "name": "Event Caterer",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "baker",
    "name": "Baker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "custom-cake-decorator",
    "name": "Custom Cake Decorator",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "meat-pie-baker",
    "name": "Meat Pie Baker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "barbecue-grill-master",
    "name": "Barbecue Grill Master",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "suya-meat-griller",
    "name": "Suya Meat Griller",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "fresh-fruit-juice-maker",
    "name": "Fresh Fruit Juice Maker",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "sobolo-brewer",
    "name": "Sobolo Brewer",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "coffee-barista",
    "name": "Coffee Barista",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "bartender",
    "name": "Bartender",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "waiter",
    "name": "Waiter",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "waitress",
    "name": "Waitress",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "event-planner",
    "name": "Event Planner",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "wedding-planner",
    "name": "Wedding Planner",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "event-decorator",
    "name": "Event Decorator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "balloon-garland-artist",
    "name": "Balloon Garland Artist",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "master-of-ceremonies",
    "name": "Master of Ceremonies",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "professional-dj",
    "name": "Professional DJ",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "sound-engineer",
    "name": "Sound Engineer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "photographer",
    "name": "Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "wedding-photographer",
    "name": "Wedding Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "videographer",
    "name": "Videographer",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "wedding-cinematographer",
    "name": "Wedding Cinematographer",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "drone-pilot",
    "name": "Drone Pilot",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "video-editor",
    "name": "Video Editor",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "graphic-designer",
    "name": "Graphic Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "brand-identity-designer",
    "name": "Brand Identity Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "logo-designer",
    "name": "Logo Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "signage-fabricator",
    "name": "Signage Fabricator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "software-engineer",
    "name": "Software Engineer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "full-stack-web-developer",
    "name": "Full-Stack Web Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "mobile-app-developer",
    "name": "Mobile App Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "wordpress-website-developer",
    "name": "WordPress Website Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "it-support-specialist",
    "name": "IT Support Specialist",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "laptop-repair-technician",
    "name": "Laptop Repair Technician",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "mobile-phone-repair-technician",
    "name": "Mobile Phone Repair Technician",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "iphone-repair-specialist",
    "name": "iPhone Repair Specialist",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "farm-manager",
    "name": "Farm Manager",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "poultry-farmer",
    "name": "Poultry Farmer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "catfish-breeder",
    "name": "Catfish Breeder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "tilapia-cage-farmer",
    "name": "Tilapia Cage Farmer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "landscape-architect",
    "name": "Landscape Architect",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "professional-gardener",
    "name": "Professional Gardener",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "domestic-cleaner",
    "name": "Domestic Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "deep-cleaning-specialist",
    "name": "Deep Cleaning Specialist",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "nanny",
    "name": "Nanny",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "fumigator",
    "name": "Fumigator",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "pest-control-specialist",
    "name": "Pest Control Specialist",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "security-guard",
    "name": "Security Guard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "vip-bodyguard",
    "name": "VIP Bodyguard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "locksmith",
    "name": "Locksmith",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "handyman",
    "name": "Handyman",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "home-tutor",
    "name": "Home Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "driving-school-instructor",
    "name": "Driving School Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "delivery-rider",
    "name": "Delivery Rider",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "private-driver",
    "name": "Private Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "uber-driver",
    "name": "Uber Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "agricultural-extension-officer",
    "name": "Agricultural Extension Officer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "agronomist",
    "name": "Agronomist",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "banana-farmer",
    "name": "Banana Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "bio-fertilizer-maker",
    "name": "Bio-Fertilizer Maker",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "borehole-irrigation-technician",
    "name": "Borehole Irrigation Technician",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "cabbage-farmer",
    "name": "Cabbage Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "carrot-farmer",
    "name": "Carrot Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "cashew-farmer",
    "name": "Cashew Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "cassava-farmer",
    "name": "Cassava Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "citrus-farmer",
    "name": "Citrus Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "cocoa-farmer",
    "name": "Cocoa Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "cocoa-pest-sprayer",
    "name": "Cocoa Pest Sprayer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "cocoa-pruner",
    "name": "Cocoa Pruner",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "coconut-harvester",
    "name": "Coconut Harvester",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "coconut-plantation-worker",
    "name": "Coconut Plantation Worker",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "commercial-greenhouse-farmer",
    "name": "Commercial Greenhouse Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "cucumber-farmer",
    "name": "Cucumber Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "drip-irrigation-farmer",
    "name": "Drip Irrigation Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "farm-mechanization-operator",
    "name": "Farm Mechanization Operator",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "fertilizer-application-specialist",
    "name": "Fertilizer Application Specialist",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "garden-egg-farmer",
    "name": "Garden Egg Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "ginger-farmer",
    "name": "Ginger Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "groundnut-farmer",
    "name": "Groundnut Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "hydroponics-specialist",
    "name": "Hydroponics Specialist",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "irrigation-specialist",
    "name": "Irrigation Specialist",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "knapsack-sprayer-operator",
    "name": "Knapsack Sprayer Operator",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "lettuce-farmer",
    "name": "Lettuce Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "maize-farmer",
    "name": "Maize Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "mango-farmer",
    "name": "Mango Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "millet-farmer",
    "name": "Millet Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "mushroom-cultivator",
    "name": "Mushroom Cultivator",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "nursery-operator",
    "name": "Nursery Operator",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "oil-palm-farmer",
    "name": "Oil Palm Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "oil-palm-harvester",
    "name": "Oil Palm Harvester",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "okra-farmer",
    "name": "Okra Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "onion-farmer",
    "name": "Onion Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "orange-farmer",
    "name": "Orange Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "organic-compost-producer",
    "name": "Organic Compost Producer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "oyster-mushroom-specialist",
    "name": "Oyster Mushroom Specialist",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "paddy-rice-thresher-operator",
    "name": "Paddy Rice Thresher Operator",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "papaya-farmer",
    "name": "Papaya Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "pepper-farmer",
    "name": "Pepper Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "pineapple-grower",
    "name": "Pineapple Grower",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "plantain-farmer",
    "name": "Plantain Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "rice-farmer",
    "name": "Rice Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "rubber-plantation-worker",
    "name": "Rubber Plantation Worker",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "rubber-tree-tapper",
    "name": "Rubber Tree Tapper",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "seedling-propagator",
    "name": "Seedling Propagator",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "soil-scientist-assistant",
    "name": "Soil Scientist Assistant",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "sorghum-farmer",
    "name": "Sorghum Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "soya-bean-farmer",
    "name": "Soya Bean Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "tomato-farmer",
    "name": "Tomato Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "turmeric-cultivator",
    "name": "Turmeric Cultivator",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "vertical-farming-technician",
    "name": "Vertical Farming Technician",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "watermelon-farmer",
    "name": "Watermelon Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "yam-farmer",
    "name": "Yam Farmer",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "yam-staking-specialist",
    "name": "Yam Staking Specialist",
    "category": "Agriculture & Crops",
    "defaultRate": 65,
    "iconName": "Sprout"
  },
  {
    "id": "abs-brake-technician",
    "name": "ABS Brake Technician",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "alloy-wheel-repairer",
    "name": "Alloy Wheel Repairer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "amplifier-installer",
    "name": "Amplifier Installer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "auto-body-straightener",
    "name": "Auto Body Straightener",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "auto-ceramic-coating-installer",
    "name": "Auto Ceramic Coating Installer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "auto-glass-installer",
    "name": "Auto Glass Installer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "auto-upholsterer",
    "name": "Auto Upholsterer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "automatic-transmission-specialist",
    "name": "Automatic Transmission Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "brake-system-specialist",
    "name": "Brake System Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-ac-electrician",
    "name": "Car AC Electrician",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-alarm-installer",
    "name": "Car Alarm Installer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-android-screen-installer",
    "name": "Car Android Screen Installer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-audio-specialist",
    "name": "Car Audio Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-battery-regeneration-specialist",
    "name": "Car Battery Regeneration Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-battery-technician",
    "name": "Car Battery Technician",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-buffing-specialist",
    "name": "Car Buffing Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-central-lock-specialist",
    "name": "Car Central Lock Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-computer-programmer",
    "name": "Car Computer Programmer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-dashboard-restorer",
    "name": "Car Dashboard Restorer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-dent-puller",
    "name": "Car Dent Puller",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-detailing-specialist",
    "name": "Car Detailing Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-diagnostic-specialist",
    "name": "Car Diagnostic Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-exhaust-welder",
    "name": "Car Exhaust Welder",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-floor-carpet-installer",
    "name": "Car Floor Carpet Installer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-key-cutting-artisan",
    "name": "Car Key Cutting Artisan",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-key-programmer",
    "name": "Car Key Programmer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-oven-baker",
    "name": "Car Oven Baker",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-roof-liner-specialist",
    "name": "Car Roof Liner Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-scratch-removal-specialist",
    "name": "Car Scratch Removal Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-seat-cover-tailor",
    "name": "Car Seat Cover Tailor",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-tinting-specialist",
    "name": "Car Tinting Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "car-wash-attendant",
    "name": "Car Wash Attendant",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "catalytic-converter-specialist",
    "name": "Catalytic Converter Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "chassis-alignment-technician",
    "name": "Chassis Alignment Technician",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "clutch-replacement-specialist",
    "name": "Clutch Replacement Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "computerized-wheel-balancer",
    "name": "Computerized Wheel Balancer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "crankshaft-grinder",
    "name": "Crankshaft Grinder",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "cylinder-head-machinist",
    "name": "Cylinder Head Machinist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "diesel-engine-mechanic",
    "name": "Diesel Engine Mechanic",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "drive-shaft-specialist",
    "name": "Drive Shaft Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "ecu-flashing-specialist",
    "name": "ECU Flashing Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "engine-overhaul-specialist",
    "name": "Engine Overhaul Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "gearbox-specialist",
    "name": "Gearbox Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "hydraulic-hose-crimper",
    "name": "Hydraulic Hose Crimper",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "manual-transmission-specialist",
    "name": "Manual Transmission Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "muffler-fabricator",
    "name": "Muffler Fabricator",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "obd2-scanner-operator",
    "name": "OBD2 Scanner Operator",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "paint-protection-film-installer",
    "name": "Paint Protection Film Installer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "paintless-dent-removal-specialist",
    "name": "Paintless Dent Removal Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "petrol-engine-mechanic",
    "name": "Petrol Engine Mechanic",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "radiator-repairer",
    "name": "Radiator Repairer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "radiator-welder",
    "name": "Radiator Welder",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "rim-straightener",
    "name": "Rim Straightener",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "shock-absorber-repairer",
    "name": "Shock Absorber Repairer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "steam-car-wash-operator",
    "name": "Steam Car Wash Operator",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "steering-rack-specialist",
    "name": "Steering Rack Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "subwoofer-installer",
    "name": "Subwoofer Installer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "suspension-specialist",
    "name": "Suspension Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "transponder-key-specialist",
    "name": "Transponder Key Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "tubeless-tyre-repairer",
    "name": "Tubeless Tyre Repairer",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "tyre-fitter",
    "name": "Tyre Fitter",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "wheel-alignment-specialist",
    "name": "Wheel Alignment Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "wheel-bearing-specialist",
    "name": "Wheel Bearing Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "windshield-replacement-specialist",
    "name": "Windshield Replacement Specialist",
    "category": "Automotive & Mechanics",
    "defaultRate": 85,
    "iconName": "Car"
  },
  {
    "id": "anniversary-cake-designer",
    "name": "Anniversary Cake Designer",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "artisan-bread-baker",
    "name": "Artisan Bread Baker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "birthday-cake-baker",
    "name": "Birthday Cake Baker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "bofrot-fryer",
    "name": "Bofrot Fryer",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "buttercream-cake-artist",
    "name": "Buttercream Cake Artist",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "chicken-pie-maker",
    "name": "Chicken Pie Maker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "commercial-bakery-operator",
    "name": "Commercial Bakery Operator",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "cornish-pasty-maker",
    "name": "Cornish Pasty Maker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "crepe-maker",
    "name": "Crepe Maker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "cupcake-specialist",
    "name": "Cupcake Specialist",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "dessert-table-designer",
    "name": "Dessert Table Designer",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "doughnut-maker",
    "name": "Doughnut Maker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "fish-roll-maker",
    "name": "Fish Roll Maker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "fondant-cake-sculptor",
    "name": "Fondant Cake Sculptor",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "ghanaian-butter-bread-baker",
    "name": "Ghanaian Butter Bread Baker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "gourmet-cookie-baker",
    "name": "Gourmet Cookie Baker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "meat-samosa-specialist",
    "name": "Meat Samosa Specialist",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "novelty-cake-artist",
    "name": "Novelty Cake Artist",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "pancake-maker",
    "name": "Pancake Maker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "pastry-chef",
    "name": "Pastry Chef",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "pizza-baker",
    "name": "Pizza Baker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "sausage-roll-baker",
    "name": "Sausage Roll Baker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "scotch-egg-specialist",
    "name": "Scotch Egg Specialist",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "spring-roll-maker",
    "name": "Spring Roll Maker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "sugar-bread-baker",
    "name": "Sugar Bread Baker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "tea-bread-specialist",
    "name": "Tea Bread Specialist",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "waffle-maker",
    "name": "Waffle Maker",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "wedding-cake-designer",
    "name": "Wedding Cake Designer",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "wood-fired-pizza-chef",
    "name": "Wood-Fired Pizza Chef",
    "category": "Baking & Pastry",
    "defaultRate": 75,
    "iconName": "Utensils"
  },
  {
    "id": "barber-shop-stylist",
    "name": "Barber Shop Stylist",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "beard-dyeing-specialist",
    "name": "Beard Dyeing Specialist",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "beard-grooming-specialist",
    "name": "Beard Grooming Specialist",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "beard-sculptor",
    "name": "Beard Sculptor",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "fade-specialist",
    "name": "Fade Specialist",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "hair-dye-barber",
    "name": "Hair Dye Barber",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "hair-line-pencil-artist",
    "name": "Hair Line Pencil Artist",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "hairline-enhancer",
    "name": "Hairline Enhancer",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "hot-towel-shave-barber",
    "name": "Hot Towel Shave Barber",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "kids-barber",
    "name": "Kids Barber",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "mobile-barber",
    "name": "Mobile Barber",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "razor-shaver",
    "name": "Razor Shaver",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "scalp-micropigmentation-artist",
    "name": "Scalp Micropigmentation Artist",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "skin-fade-barber",
    "name": "Skin Fade Barber",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "taper-fade-barber",
    "name": "Taper Fade Barber",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "vip-private-barber",
    "name": "VIP Private Barber",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "wave-specialist",
    "name": "Wave Specialist",
    "category": "Barbering & Grooming",
    "defaultRate": 65,
    "iconName": "Scissors"
  },
  {
    "id": "akpeteshie-distiller",
    "name": "Akpeteshie Distiller",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "asana-drink-maker",
    "name": "Asana Drink Maker",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "boba-tea-barista",
    "name": "Boba Tea Barista",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "brukina-specialist",
    "name": "Brukina Specialist",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "craft-cocktail-mixologist",
    "name": "Craft Cocktail Mixologist",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "draft-beer-dispenser-technician",
    "name": "Draft Beer Dispenser Technician",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "espresso-specialist",
    "name": "Espresso Specialist",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "event-beverage-caterer",
    "name": "Event Beverage Caterer",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "fresh-coconut-water-vendor",
    "name": "Fresh Coconut Water Vendor",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "fresh-palm-wine-tapper",
    "name": "Fresh Palm Wine Tapper",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "ice-delivery-specialist",
    "name": "Ice Delivery Specialist",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "lamugin-brewer",
    "name": "Lamugin Brewer",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "latte-art-specialist",
    "name": "Latte Art Specialist",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "milkshake-specialist",
    "name": "Milkshake Specialist",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "mobile-bar-operator",
    "name": "Mobile Bar Operator",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "mocktail-designer",
    "name": "Mocktail Designer",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "smoothie-maker",
    "name": "Smoothie Maker",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "sugarcane-juice-extractor",
    "name": "Sugarcane Juice Extractor",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "tiger-nut-milk-brewer",
    "name": "Tiger Nut Milk Brewer",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "wine-sommelier",
    "name": "Wine Sommelier",
    "category": "Beverages & Mixology",
    "defaultRate": 65,
    "iconName": "Coffee"
  },
  {
    "id": "affidavit-assistant",
    "name": "Affidavit Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "bookkeeper",
    "name": "Bookkeeper",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "business-plan-writer",
    "name": "Business Plan Writer",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "business-registration-agent",
    "name": "Business Registration Agent",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "commissioner-for-oaths-clerk",
    "name": "Commissioner for Oaths Clerk",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "company-profile-writer",
    "name": "Company Profile Writer",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "dvla-license-renewal-assistant",
    "name": "DVLA License Renewal Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "embossing-stamp-maker",
    "name": "Embossing Stamp Maker",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "gra-tax-filing-consultant",
    "name": "GRA Tax Filing Consultant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "ghana-card-registration-assistant",
    "name": "Ghana Card Registration Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "inventory-controller",
    "name": "Inventory Controller",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "legal-typist",
    "name": "Legal Typist",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "passport-application-assistant",
    "name": "Passport Application Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "payroll-specialist",
    "name": "Payroll Specialist",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "pitch-deck-designer",
    "name": "Pitch Deck Designer",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "police-clearance-certificate-assistant",
    "name": "Police Clearance Certificate Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "registrar-general-orc-assistant",
    "name": "Registrar General ORC Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "roadworthy-certificate-assistant",
    "name": "Roadworthy Certificate Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "rubber-stamp-artisan",
    "name": "Rubber Stamp Artisan",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "small-business-accountant",
    "name": "Small Business Accountant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "stock-auditor",
    "name": "Stock Auditor",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "tin-registration-assistant",
    "name": "TIN Registration Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "tax-clearance-certificate-assistant",
    "name": "Tax Clearance Certificate Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "vehicle-registration-assistant",
    "name": "Vehicle Registration Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "visa-application-assistant",
    "name": "Visa Application Assistant",
    "category": "Business, Registry & Documentation",
    "defaultRate": 75,
    "iconName": "FileText"
  },
  {
    "id": "acoustic-wood-diffuser-builder",
    "name": "Acoustic Wood Diffuser Builder",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "antique-furniture-restorer",
    "name": "Antique Furniture Restorer",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "bamboo-craftsman",
    "name": "Bamboo Craftsman",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "bed-frame-maker",
    "name": "Bed Frame Maker",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "bookshelf-builder",
    "name": "Bookshelf Builder",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "cnc-wood-carver",
    "name": "CNC Wood Carver",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "cane-furniture-craftsman",
    "name": "Cane Furniture Craftsman",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "casket-maker",
    "name": "Casket Maker",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "chainsaw-operator",
    "name": "Chainsaw Operator",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "dining-table-craftsman",
    "name": "Dining Table Craftsman",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "fantasy-coffin-sculptor",
    "name": "Fantasy Coffin Sculptor",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "french-polisher",
    "name": "French Polisher",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "furniture-polisher",
    "name": "Furniture Polisher",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "furniture-restorer",
    "name": "Furniture Restorer",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "kitchen-cabinet-designer",
    "name": "Kitchen Cabinet Designer",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "kitchen-cabinet-installer",
    "name": "Kitchen Cabinet Installer",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "office-desk-fabricator",
    "name": "Office Desk Fabricator",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "pallet-maker",
    "name": "Pallet Maker",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "sawmill-operator",
    "name": "Sawmill Operator",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "security-door-installer",
    "name": "Security Door Installer",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "showroom-display-carpenter",
    "name": "Showroom Display Carpenter",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "staircase-carpenter",
    "name": "Staircase Carpenter",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "structural-carpenter",
    "name": "Structural Carpenter",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "timber-mill-handyman",
    "name": "Timber Mill Handyman",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "upholsterer",
    "name": "Upholsterer",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "walk-in-closet-builder",
    "name": "Walk-in Closet Builder",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wardrobe-maker",
    "name": "Wardrobe Maker",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wood-carver",
    "name": "Wood Carver",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wood-machinist",
    "name": "Wood Machinist",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wood-router-operator",
    "name": "Wood Router Operator",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wood-spray-painter",
    "name": "Wood Spray Painter",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wood-turner",
    "name": "Wood Turner",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wooden-door-carver",
    "name": "Wooden Door Carver",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wooden-door-installer",
    "name": "Wooden Door Installer",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wooden-flooring-installer",
    "name": "Wooden Flooring Installer",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wooden-partition-builder",
    "name": "Wooden Partition Builder",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wooden-railing-specialist",
    "name": "Wooden Railing Specialist",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "wooden-window-maker",
    "name": "Wooden Window Maker",
    "category": "Carpentry & Furniture",
    "defaultRate": 80,
    "iconName": "Hammer"
  },
  {
    "id": "carpet-cleaner",
    "name": "Carpet Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "carpet-shampoo-operator",
    "name": "Carpet Shampoo Operator",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "commercial-janitor",
    "name": "Commercial Janitor",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "compound-sweeper",
    "name": "Compound Sweeper",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "couch-deep-cleaner",
    "name": "Couch Deep Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "curtain-steam-cleaner",
    "name": "Curtain Steam Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "drainage-desilting-laborer",
    "name": "Drainage Desilting Laborer",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "driveway-pressure-cleaner",
    "name": "Driveway Pressure Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "floor-scrubbing-machine-operator",
    "name": "Floor Scrubbing Machine Operator",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "floor-waxing-specialist",
    "name": "Floor Waxing Specialist",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "gutter-cleaner",
    "name": "Gutter Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "high-rise-window-cleaner",
    "name": "High-Rise Window Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "house-cleaner",
    "name": "House Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "mattress-sanitizing-specialist",
    "name": "Mattress Sanitizing Specialist",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "move-in-cleaning-specialist",
    "name": "Move-in Cleaning Specialist",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "move-out-cleaning-specialist",
    "name": "Move-out Cleaning Specialist",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "office-cleaner",
    "name": "Office Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "plastic-waste-recycler",
    "name": "Plastic Waste Recycler",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "post-construction-cleaning-specialist",
    "name": "Post-Construction Cleaning Specialist",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "pressure-washing-specialist",
    "name": "Pressure Washing Specialist",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "rubbish-collector",
    "name": "Rubbish Collector",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "scrap-metal-collector",
    "name": "Scrap Metal Collector",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "sofa-upholstery-cleaner",
    "name": "Sofa Upholstery Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "street-sweeper",
    "name": "Street Sweeper",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "tile-grout-steam-cleaner",
    "name": "Tile Grout Steam Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "waste-disposal-worker",
    "name": "Waste Disposal Worker",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "window-cleaner",
    "name": "Window Cleaner",
    "category": "Cleaning & Janitorial",
    "defaultRate": 60,
    "iconName": "Sparkles"
  },
  {
    "id": "aws-cloud-architect",
    "name": "AWS Cloud Architect",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "cloud-infrastructure-engineer",
    "name": "Cloud Infrastructure Engineer",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "cybersecurity-specialist",
    "name": "Cybersecurity Specialist",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "data-migration-specialist",
    "name": "Data Migration Specialist",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "database-administrator",
    "name": "Database Administrator",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "database-backup-technician",
    "name": "Database Backup Technician",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "devops-pipeline-engineer",
    "name": "DevOps Pipeline Engineer",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "docker-specialist",
    "name": "Docker Specialist",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "ethical-hacker",
    "name": "Ethical Hacker",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "firewall-configurator",
    "name": "Firewall Configurator",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "google-cloud-specialist",
    "name": "Google Cloud Specialist",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "kubernetes-administrator",
    "name": "Kubernetes Administrator",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "linux-server-administrator",
    "name": "Linux Server Administrator",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "microsoft-azure-administrator",
    "name": "Microsoft Azure Administrator",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "mongodb-specialist",
    "name": "MongoDB Specialist",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "mysql-database-specialist",
    "name": "MySQL Database Specialist",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "network-security-engineer",
    "name": "Network Security Engineer",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "penetration-tester",
    "name": "Penetration Tester",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "postgresql-administrator",
    "name": "PostgreSQL Administrator",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "soc-analyst",
    "name": "SOC Analyst",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "ssl-certificate-installer",
    "name": "SSL Certificate Installer",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "vulnerability-assessment-specialist",
    "name": "Vulnerability Assessment Specialist",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "windows-server-administrator",
    "name": "Windows Server Administrator",
    "category": "Cloud, DevOps & Cybersecurity",
    "defaultRate": 130,
    "iconName": "ShieldCheck"
  },
  {
    "id": "air-compressor-mechanic",
    "name": "Air Compressor Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "articulated-truck-mechanic",
    "name": "Articulated Truck Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "asphalt-paver-mechanic",
    "name": "Asphalt Paver Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "boat-engine-mechanic",
    "name": "Boat Engine Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "bulldozer-mechanic",
    "name": "Bulldozer Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "canoe-builder",
    "name": "Canoe Builder",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "caterpillar-mechanic",
    "name": "Caterpillar Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "coach-bus-technician",
    "name": "Coach Bus Technician",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "combine-harvester-mechanic",
    "name": "Combine Harvester Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "commercial-bus-mechanic",
    "name": "Commercial Bus Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "excavator-mechanic",
    "name": "Excavator Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "fishing-boat-repairer",
    "name": "Fishing Boat Repairer",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "forklift-mechanic",
    "name": "Forklift Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "fuel-tanker-mechanic",
    "name": "Fuel Tanker Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "heavy-duty-truck-mechanic",
    "name": "Heavy Duty Truck Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "hydraulic-crane-mechanic",
    "name": "Hydraulic Crane Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "jet-ski-mechanic",
    "name": "Jet Ski Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "marine-diesel-engineer",
    "name": "Marine Diesel Engineer",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "motor-grader-mechanic",
    "name": "Motor Grader Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "outboard-motor-mechanic",
    "name": "Outboard Motor Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "reach-stacker-mechanic",
    "name": "Reach Stacker Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "road-roller-mechanic",
    "name": "Road Roller Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "speedboat-mechanic",
    "name": "Speedboat Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "sprinter-van-mechanic",
    "name": "Sprinter Van Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "tipper-truck-mechanic",
    "name": "Tipper Truck Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "toyota-hiace-mechanic",
    "name": "Toyota HiAce Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "tractor-mechanic",
    "name": "Tractor Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "trotro-mechanic",
    "name": "Trotro Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "urvan-van-mechanic",
    "name": "Urvan Van Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "water-tanker-mechanic",
    "name": "Water Tanker Mechanic",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "yamaha-outboard-specialist",
    "name": "Yamaha Outboard Specialist",
    "category": "Commercial Vehicles & Fleet",
    "defaultRate": 95,
    "iconName": "Truck"
  },
  {
    "id": "apple-mac-repair-specialist",
    "name": "Apple Mac Repair Specialist",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "computer-hardware-technician",
    "name": "Computer Hardware Technician",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "data-recovery-specialist",
    "name": "Data Recovery Specialist",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "desktop-support-technician",
    "name": "Desktop Support Technician",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "fingerprint-clock-installer",
    "name": "Fingerprint Clock Installer",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "gaming-pc-assembler",
    "name": "Gaming PC Assembler",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "hard-drive-recovery-technician",
    "name": "Hard Drive Recovery Technician",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "helpdesk-support-officer",
    "name": "Helpdesk Support Officer",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "laptop-battery-specialist",
    "name": "Laptop Battery Specialist",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "laptop-keyboard-specialist",
    "name": "Laptop Keyboard Specialist",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "laptop-motherboard-specialist",
    "name": "Laptop Motherboard Specialist",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "laptop-screen-specialist",
    "name": "Laptop Screen Specialist",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "laser-printer-repair-technician",
    "name": "Laser Printer Repair Technician",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "macbook-logic-board-repairer",
    "name": "MacBook Logic Board Repairer",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "network-printer-setup-specialist",
    "name": "Network Printer Setup Specialist",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "operating-system-installer",
    "name": "Operating System Installer",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "pc-building-specialist",
    "name": "PC Building Specialist",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "pos-scanner-technician",
    "name": "POS Scanner Technician",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "photocopier-service-engineer",
    "name": "Photocopier Service Engineer",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "plotter-printer-technician",
    "name": "Plotter Printer Technician",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "thermal-receipt-printer-repairer",
    "name": "Thermal Receipt Printer Repairer",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "virus-removal-specialist",
    "name": "Virus Removal Specialist",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "imac-repair-technician",
    "name": "iMac Repair Technician",
    "category": "Computer Hardware & IT Support",
    "defaultRate": 80,
    "iconName": "Cpu"
  },
  {
    "id": "3d-architectural-visualizer",
    "name": "3D Architectural Visualizer",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "architectural-draftsman",
    "name": "Architectural Draftsman",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "asphalt-worker",
    "name": "Asphalt Worker",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "assistant-land-surveyor",
    "name": "Assistant Land Surveyor",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "assistant-quantity-surveyor",
    "name": "Assistant Quantity Surveyor",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "backhoe-operator",
    "name": "Backhoe Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "boom-pump-operator",
    "name": "Boom Pump Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "bridge-construction-worker",
    "name": "Bridge Construction Worker",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "building-contractor",
    "name": "Building Contractor",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "building-demolisher",
    "name": "Building Demolisher",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "building-inspector",
    "name": "Building Inspector",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "bulldozer-operator",
    "name": "Bulldozer Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "cad-technician",
    "name": "CAD Technician",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "clerk-of-works",
    "name": "Clerk of Works",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "compactor-operator",
    "name": "Compactor Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "concrete-finisher",
    "name": "Concrete Finisher",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "concrete-mixer-operator",
    "name": "Concrete Mixer Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "concrete-pump-operator",
    "name": "Concrete Pump Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "construction-supervisor",
    "name": "Construction Supervisor",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "core-driller",
    "name": "Core Driller",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "core-sampling-technician",
    "name": "Core Sampling Technician",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "crane-operator",
    "name": "Crane Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "culvert-builder",
    "name": "Culvert Builder",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "deep-well-digger",
    "name": "Deep Well Digger",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "demolition-worker",
    "name": "Demolition Worker",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "excavator-operator",
    "name": "Excavator Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "formwork-carpenter",
    "name": "Formwork Carpenter",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "foundation-digger",
    "name": "Foundation Digger",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "gabion-basket-installer",
    "name": "Gabion Basket Installer",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "grader-operator",
    "name": "Grader Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "gravel-and-sand-supplier",
    "name": "Gravel and Sand Supplier",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "interlocking-paver-installer",
    "name": "Interlocking Paver Installer",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "iron-bender",
    "name": "Iron Bender",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "kerb-layer",
    "name": "Kerb Layer",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "land-surveyor",
    "name": "Land Surveyor",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "mobile-crane-operator",
    "name": "Mobile Crane Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "pavement-block-layer",
    "name": "Pavement Block Layer",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "paver",
    "name": "Paver",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "pile-driver-operator",
    "name": "Pile Driver Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "precast-concrete-fabricator",
    "name": "Precast Concrete Fabricator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "quantity-surveyor",
    "name": "Quantity Surveyor",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "rebar-worker",
    "name": "Rebar Worker",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "retaining-wall-builder",
    "name": "Retaining Wall Builder",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "rigging-specialist",
    "name": "Rigging Specialist",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "road-construction-worker",
    "name": "Road Construction Worker",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "sandblaster",
    "name": "Sandblaster",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "scaffolder",
    "name": "Scaffolder",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "screeder",
    "name": "Screeder",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "shuttering-carpenter",
    "name": "Shuttering Carpenter",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "site-clearance-laborer",
    "name": "Site Clearance Laborer",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "site-foreman",
    "name": "Site Foreman",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "soil-testing-assistant",
    "name": "Soil Testing Assistant",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "steel-fixer",
    "name": "Steel Fixer",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "stone-mason",
    "name": "Stone Mason",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "structural-steel-worker",
    "name": "Structural Steel Worker",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "terrazzo-worker",
    "name": "Terrazzo Worker",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "tower-crane-operator",
    "name": "Tower Crane Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "trench-digger",
    "name": "Trench Digger",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "underpinning-specialist",
    "name": "Underpinning Specialist",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "wheel-loader-operator",
    "name": "Wheel Loader Operator",
    "category": "Construction & Civil",
    "defaultRate": 85,
    "iconName": "Building2"
  },
  {
    "id": "angwa-mu-specialist",
    "name": "Angwa Mu Specialist",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "banku-master",
    "name": "Banku Master",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "chinese-cuisine-chef",
    "name": "Chinese Cuisine Chef",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "continental-cuisine-chef",
    "name": "Continental Cuisine Chef",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "corporate-luncheon-caterer",
    "name": "Corporate Luncheon Caterer",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "fried-rice-specialist",
    "name": "Fried Rice Specialist",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "fufu-machine-operator",
    "name": "Fufu Machine Operator",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "funeral-caterer",
    "name": "Funeral Caterer",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "groundnut-soup-master",
    "name": "Groundnut Soup Master",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "head-chef",
    "name": "Head Chef",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "indian-curry-specialist",
    "name": "Indian Curry Specialist",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "italian-pasta-chef",
    "name": "Italian Pasta Chef",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "jollof-rice-master",
    "name": "Jollof Rice Master",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "kenkey-caterer",
    "name": "Kenkey Caterer",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "light-soup-master",
    "name": "Light Soup Master",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "live-in-house-cook",
    "name": "Live-in House Cook",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "local-ghanaian-dish-specialist",
    "name": "Local Ghanaian Dish Specialist",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "mexican-taco-chef",
    "name": "Mexican Taco Chef",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "okro-soup-specialist",
    "name": "Okro Soup Specialist",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "palm-nut-soup-specialist",
    "name": "Palm Nut Soup Specialist",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "private-domestic-cook",
    "name": "Private Domestic Cook",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "red-red-specialist",
    "name": "Red Red Specialist",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "shawarma-chef",
    "name": "Shawarma Chef",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "sous-chef",
    "name": "Sous Chef",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "tuo-zaafi-specialist",
    "name": "Tuo Zaafi Specialist",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "vegan-chef",
    "name": "Vegan Chef",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "vegetarian-chef",
    "name": "Vegetarian Chef",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "waakye-master",
    "name": "Waakye Master",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "wedding-caterer",
    "name": "Wedding Caterer",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "weekend-family-cook",
    "name": "Weekend Family Cook",
    "category": "Culinary & Chefs",
    "defaultRate": 85,
    "iconName": "Utensils"
  },
  {
    "id": "audio-transcriber",
    "name": "Audio Transcriber",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "blog-post-writer",
    "name": "Blog Post Writer",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "brand-ambassador",
    "name": "Brand Ambassador",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "content-creator",
    "name": "Content Creator",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "copywriter",
    "name": "Copywriter",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "customer-support-chat-agent",
    "name": "Customer Support Chat Agent",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "data-entry-clerk",
    "name": "Data Entry Clerk",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "digital-marketing-specialist",
    "name": "Digital Marketing Specialist",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "email-marketing-specialist",
    "name": "Email Marketing Specialist",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "excel-spreadsheet-specialist",
    "name": "Excel Spreadsheet Specialist",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "facebook-ads-specialist",
    "name": "Facebook Ads Specialist",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "google-ads-specialist",
    "name": "Google Ads Specialist",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "google-sheets-automator",
    "name": "Google Sheets Automator",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "instagram-page-manager",
    "name": "Instagram Page Manager",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "local-seo-optimizer",
    "name": "Local SEO Optimizer",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "newsletter-designer",
    "name": "Newsletter Designer",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "off-page-link-builder",
    "name": "Off-Page Link Builder",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "on-page-seo-optimizer",
    "name": "On-Page SEO Optimizer",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "online-community-manager",
    "name": "Online Community Manager",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "product-description-writer",
    "name": "Product Description Writer",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "remote-administrative-assistant",
    "name": "Remote Administrative Assistant",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "seo-specialist",
    "name": "SEO Specialist",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "sms-marketing-campaigner",
    "name": "SMS Marketing Campaigner",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "sales-copywriter",
    "name": "Sales Copywriter",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "social-media-influencer",
    "name": "Social Media Influencer",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "social-media-manager",
    "name": "Social Media Manager",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "technical-writer",
    "name": "Technical Writer",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "tiktok-ads-manager",
    "name": "TikTok Ads Manager",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "transcriptionist",
    "name": "Transcriptionist",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "video-subtitle-transcriber",
    "name": "Video Subtitle Transcriber",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "virtual-assistant",
    "name": "Virtual Assistant",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "website-content-writer",
    "name": "Website Content Writer",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "whatsapp-marketing-specialist",
    "name": "WhatsApp Marketing Specialist",
    "category": "Digital Marketing & Content",
    "defaultRate": 80,
    "iconName": "Globe"
  },
  {
    "id": "butler",
    "name": "Butler",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "day-babysitter",
    "name": "Day Babysitter",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "day-housemaid",
    "name": "Day Housemaid",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "domestic-housekeeper",
    "name": "Domestic Housekeeper",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "dry-cleaning-presser",
    "name": "Dry Cleaning Presser",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "elderly-caregiver",
    "name": "Elderly Caregiver",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "geriatric-nurse-assistant",
    "name": "Geriatric Nurse Assistant",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "home-health-aide",
    "name": "Home Health Aide",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "industrial-washer-operator",
    "name": "Industrial Washer Operator",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "infant-care-specialist",
    "name": "Infant Care Specialist",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "ironing-specialist",
    "name": "Ironing Specialist",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "laundry-attendant",
    "name": "Laundry Attendant",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "live-in-caregiver",
    "name": "Live-in Caregiver",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "live-in-house-help",
    "name": "Live-in House Help",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "live-in-nanny",
    "name": "Live-in Nanny",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "newborn-confinement-nurse",
    "name": "Newborn Confinement Nurse",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "palliative-care-assistant",
    "name": "Palliative Care Assistant",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "patient-bedside-attendant",
    "name": "Patient Bedside Attendant",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "physical-disability-assistant",
    "name": "Physical Disability Assistant",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "special-needs-child-caregiver",
    "name": "Special Needs Child Caregiver",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "toddler-caregiver",
    "name": "Toddler Caregiver",
    "category": "Domestic Staff & Caregiving",
    "defaultRate": 55,
    "iconName": "Home"
  },
  {
    "id": "abaya-tailor",
    "name": "Abaya Tailor",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "african-print-fashion-designer",
    "name": "African Print Fashion Designer",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "ankara-dress-specialist",
    "name": "Ankara Dress Specialist",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "baby-wear-maker",
    "name": "Baby Wear Maker",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "ball-gown-designer",
    "name": "Ball Gown Designer",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "boned-corset-specialist",
    "name": "Boned Corset Specialist",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "bridal-gown-designer",
    "name": "Bridal Gown Designer",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "bridesmaid-dress-tailor",
    "name": "Bridesmaid Dress Tailor",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "childrens-clothes-tailor",
    "name": "Childrens Clothes Tailor",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "church-dress-tailor",
    "name": "Church Dress Tailor",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "corset-dressmaker",
    "name": "Corset Dressmaker",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "evening-gown-maker",
    "name": "Evening Gown Maker",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "graduation-dressmaker",
    "name": "Graduation Dressmaker",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "hijab-designer",
    "name": "Hijab Designer",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "lingerie-maker",
    "name": "Lingerie Maker",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "maternity-dressmaker",
    "name": "Maternity Dressmaker",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "modest-wear-designer",
    "name": "Modest Wear Designer",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "peplum-top-designer",
    "name": "Peplum Top Designer",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "prom-dressmaker",
    "name": "Prom Dressmaker",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "swimwear-designer",
    "name": "Swimwear Designer",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "traditional-ghanaian-kaba-tailor",
    "name": "Traditional Ghanaian Kaba Tailor",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "wedding-dressmaker",
    "name": "Wedding Dressmaker",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "womens-dressmaker",
    "name": "Womens Dressmaker",
    "category": "Dressmaking & Bridal Fashion",
    "defaultRate": 85,
    "iconName": "Scissors"
  },
  {
    "id": "agricultural-tractor-operator",
    "name": "Agricultural Tractor Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "ambulance-driver",
    "name": "Ambulance Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "articulated-heavy-duty-driver",
    "name": "Articulated Heavy Duty Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "asphalt-paver-operator",
    "name": "Asphalt Paver Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "backhoe-loader-operator",
    "name": "Backhoe Loader Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "bolt-driver",
    "name": "Bolt Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "bulk-lpg-gas-tanker-driver",
    "name": "Bulk LPG Gas Tanker Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "cement-bulk-carrier-driver",
    "name": "Cement Bulk Carrier Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "cesspit-emptier-driver",
    "name": "Cesspit Emptier Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "combine-harvester-operator",
    "name": "Combine Harvester Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "commercial-bus-driver",
    "name": "Commercial Bus Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "concrete-mixer-truck-driver",
    "name": "Concrete Mixer Truck Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "container-handler-operator",
    "name": "Container Handler Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "crawler-crane-operator",
    "name": "Crawler Crane Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "dispatch-rider",
    "name": "Dispatch Rider",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "executive-corporate-driver",
    "name": "Executive Corporate Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "express-courier-rider",
    "name": "Express Courier Rider",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "family-chauffeur",
    "name": "Family Chauffeur",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "flatbed-recovery-operator",
    "name": "Flatbed Recovery Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "flatbed-trailer-driver",
    "name": "Flatbed Trailer Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "food-delivery-rider",
    "name": "Food Delivery Rider",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "forklift-operator",
    "name": "Forklift Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "fuel-tanker-driver",
    "name": "Fuel Tanker Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "grocery-delivery-courier",
    "name": "Grocery Delivery Courier",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "hearse-driver",
    "name": "Hearse Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "heavy-equipment-lowboy-operator",
    "name": "Heavy Equipment Lowboy Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "light-cargo-van-driver",
    "name": "Light Cargo Van Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "long-distance-trotro-driver",
    "name": "Long Distance Trotro Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "lowbed-equipment-transporter",
    "name": "Lowbed Equipment Transporter",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "mini-excavator-operator",
    "name": "Mini Excavator Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "motor-grader-operator",
    "name": "Motor Grader Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "pickup-truck-driver",
    "name": "Pickup Truck Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "reach-truck-operator",
    "name": "Reach Truck Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "ride-hailing-driver",
    "name": "Ride-Hailing Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "road-roller-operator",
    "name": "Road Roller Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "rough-terrain-crane-operator",
    "name": "Rough Terrain Crane Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "sand-truck-driver",
    "name": "Sand Truck Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "school-bus-driver",
    "name": "School Bus Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "skid-steer-operator",
    "name": "Skid Steer Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "soil-compactor-operator",
    "name": "Soil Compactor Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "station-taxi-driver",
    "name": "Station Taxi Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "stone-quarry-truck-driver",
    "name": "Stone Quarry Truck Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "taxi-driver",
    "name": "Taxi Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "tipper-truck-driver",
    "name": "Tipper Truck Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "tow-truck-operator",
    "name": "Tow Truck Operator",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "trotro-driver",
    "name": "Trotro Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "vip-bus-driver",
    "name": "VIP Bus Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "vip-protection-driver",
    "name": "VIP Protection Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "waste-compactor-truck-driver",
    "name": "Waste Compactor Truck Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "water-tanker-driver",
    "name": "Water Tanker Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "yango-driver",
    "name": "Yango Driver",
    "category": "Driving & Transportation",
    "defaultRate": 75,
    "iconName": "Car"
  },
  {
    "id": "arabic-language-tutor",
    "name": "Arabic Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "biology-tutor",
    "name": "Biology Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "business-management-tutor",
    "name": "Business Management Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "cambridge-a-level-tutor",
    "name": "Cambridge A-Level Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "cambridge-igcse-tutor",
    "name": "Cambridge IGCSE Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "chemistry-tutor",
    "name": "Chemistry Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "core-maths-tutor",
    "name": "Core Maths Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "cost-accounting-tutor",
    "name": "Cost Accounting Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "dagbani-language-tutor",
    "name": "Dagbani Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "economics-tutor",
    "name": "Economics Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "elective-maths-tutor",
    "name": "Elective Maths Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "english-language-tutor",
    "name": "English Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "english-literature-tutor",
    "name": "English Literature Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "ewe-language-tutor",
    "name": "Ewe Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "fante-language-tutor",
    "name": "Fante Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "french-language-tutor",
    "name": "French Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "gre-exam-prep-tutor",
    "name": "GRE Exam Prep Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "ga-language-tutor",
    "name": "Ga Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "geography-tutor",
    "name": "Geography Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "german-language-tutor",
    "name": "German Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "government-and-history-tutor",
    "name": "Government and History Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "hausa-language-tutor",
    "name": "Hausa Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "ielts-exam-prep-tutor",
    "name": "IELTS Exam Prep Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "integrated-science-tutor",
    "name": "Integrated Science Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "jhs-bece-home-tutor",
    "name": "JHS BECE Home Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "kids-coding-tutor",
    "name": "Kids Coding Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "mandarin-chinese-tutor",
    "name": "Mandarin Chinese Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "mathematics-tutor",
    "name": "Mathematics Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "nursery-kindergarten-tutor",
    "name": "Nursery Kindergarten Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "phonics-specialist",
    "name": "Phonics Specialist",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "physics-tutor",
    "name": "Physics Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "primary-school-home-tutor",
    "name": "Primary School Home Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "private-lesson-teacher",
    "name": "Private Lesson Teacher",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "reading-specialist",
    "name": "Reading Specialist",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "sat-exam-prep-tutor",
    "name": "SAT Exam Prep Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "shs-wassce-home-tutor",
    "name": "SHS WASSCE Home Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "stem-robotics-tutor",
    "name": "STEM Robotics Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "sign-language-interpreter",
    "name": "Sign Language Interpreter",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "social-studies-tutor",
    "name": "Social Studies Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "spanish-language-tutor",
    "name": "Spanish Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "special-needs-education-tutor",
    "name": "Special Needs Education Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "toefl-prep-tutor",
    "name": "TOEFL Prep Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "twi-language-tutor",
    "name": "Twi Language Tutor",
    "category": "Education & Academic Tutoring",
    "defaultRate": 70,
    "iconName": "GraduationCap"
  },
  {
    "id": "automatic-transfer-switch-technician",
    "name": "Automatic Transfer Switch Technician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "backup-generator-mechanic",
    "name": "Backup Generator Mechanic",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "cable-jointer",
    "name": "Cable Jointer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "chandelier-installer",
    "name": "Chandelier Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "circuit-breaker-technician",
    "name": "Circuit Breaker Technician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "commercial-electrician",
    "name": "Commercial Electrician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "conduit-pipe-installer",
    "name": "Conduit Pipe Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "diesel-generator-technician",
    "name": "Diesel Generator Technician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "domestic-electrician",
    "name": "Domestic Electrician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "earthing-protection-installer",
    "name": "Earthing Protection Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "electrical-motor-rewinder",
    "name": "Electrical Motor Rewinder",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "electrical-panel-builder",
    "name": "Electrical Panel Builder",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "energy-audit-technician",
    "name": "Energy Audit Technician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "generator-rewinder",
    "name": "Generator Rewinder",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "high-mast-light-technician",
    "name": "High Mast Light Technician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "high-voltage-cable-jointer",
    "name": "High Voltage Cable Jointer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "hybrid-solar-installer",
    "name": "Hybrid Solar Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "industrial-electrician",
    "name": "Industrial Electrician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "industrial-transformer-technician",
    "name": "Industrial Transformer Technician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "inverter-repairer",
    "name": "Inverter Repairer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "led-lighting-specialist",
    "name": "LED Lighting Specialist",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "lightning-arrester-installer",
    "name": "Lightning Arrester Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "motion-sensor-light-installer",
    "name": "Motion Sensor Light Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "neon-sign-electrician",
    "name": "Neon Sign Electrician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "off-grid-solar-specialist",
    "name": "Off-Grid Solar Specialist",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "overhead-powerline-technician",
    "name": "Overhead Powerline Technician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "prepaid-meter-installer",
    "name": "Prepaid Meter Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "solar-battery-specialist",
    "name": "Solar Battery Specialist",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "solar-street-light-installer",
    "name": "Solar Street Light Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "solar-water-pump-installer",
    "name": "Solar Water Pump Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "stage-lighting-electrician",
    "name": "Stage Lighting Electrician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "street-lighting-technician",
    "name": "Street Lighting Technician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "substation-maintenance-worker",
    "name": "Substation Maintenance Worker",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "surge-protector-installer",
    "name": "Surge Protector Installer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "switchgear-technician",
    "name": "Switchgear Technician",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "ups-systems-specialist",
    "name": "UPS Systems Specialist",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "voltage-stabilizer-repairer",
    "name": "Voltage Stabilizer Repairer",
    "category": "Electrical & Power",
    "defaultRate": 90,
    "iconName": "Zap"
  },
  {
    "id": "artificial-flower-decorator",
    "name": "Artificial Flower Decorator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "baby-shower-planner",
    "name": "Baby Shower Planner",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "balloon-arch-builder",
    "name": "Balloon Arch Builder",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "birthday-party-planner",
    "name": "Birthday Party Planner",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "bridal-shower-coordinator",
    "name": "Bridal Shower Coordinator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "bubble-machine-operator",
    "name": "Bubble Machine Operator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "ceiling-drapery-installer",
    "name": "Ceiling Drapery Installer",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "cold-spark-machine-operator",
    "name": "Cold Spark Machine Operator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "confetti-cannon-operator",
    "name": "Confetti Cannon Operator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "corporate-event-planner",
    "name": "Corporate Event Planner",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "dance-floor-wrap-installer",
    "name": "Dance Floor Wrap Installer",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "dry-ice-low-fog-operator",
    "name": "Dry Ice Low Fog Operator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "engagement-ceremony-planner",
    "name": "Engagement Ceremony Planner",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "event-creative-director",
    "name": "Event Creative Director",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "event-stylist",
    "name": "Event Stylist",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "fireworks-specialist",
    "name": "Fireworks Specialist",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "floral-designer",
    "name": "Floral Designer",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "fresh-flower-arranger",
    "name": "Fresh Flower Arranger",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "funeral-event-coordinator",
    "name": "Funeral Event Coordinator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "luxury-wedding-decorator",
    "name": "Luxury Wedding Decorator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "marquee-letter-light-operator",
    "name": "Marquee Letter Light Operator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "photo-booth-designer",
    "name": "Photo Booth Designer",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "stage-backdrop-fabricator",
    "name": "Stage Backdrop Fabricator",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "traditional-wedding-planner",
    "name": "Traditional Wedding Planner",
    "category": "Event Planning & Decor",
    "defaultRate": 90,
    "iconName": "Sparkles"
  },
  {
    "id": "chief-usher",
    "name": "Chief Usher",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "corporate-conference-usher",
    "name": "Corporate Conference Usher",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "event-registration-desk-clerk",
    "name": "Event Registration Desk Clerk",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "funeral-protocol-usher",
    "name": "Funeral Protocol Usher",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "gift-table-attendant",
    "name": "Gift Table Attendant",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "guest-greeter",
    "name": "Guest Greeter",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "seating-coordinator",
    "name": "Seating Coordinator",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "souvenir-distribution-assistant",
    "name": "Souvenir Distribution Assistant",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "ushering-agency-manager",
    "name": "Ushering Agency Manager",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "vip-escort",
    "name": "VIP Escort",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "wedding-usher",
    "name": "Wedding Usher",
    "category": "Event Protocol & Ushers",
    "defaultRate": 60,
    "iconName": "Users"
  },
  {
    "id": "banquet-table-setup-crew",
    "name": "Banquet Table Setup Crew",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "canopy-rigger",
    "name": "Canopy Rigger",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "carpet-runner-layer",
    "name": "Carpet Runner Layer",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "chiavari-chair-rental-operator",
    "name": "Chiavari Chair Rental Operator",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "clear-span-structure-rigger",
    "name": "Clear Span Structure Rigger",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "event-flooring-installer",
    "name": "Event Flooring Installer",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "event-mobile-toilet-operator",
    "name": "Event Mobile Toilet Operator",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "generator-operator-for-events",
    "name": "Generator Operator for Events",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "high-peak-tent-installer",
    "name": "High Peak Tent Installer",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "marquee-tent-specialist",
    "name": "Marquee Tent Specialist",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "mobile-cold-room-rental-operator",
    "name": "Mobile Cold Room Rental Operator",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "outdoor-cooling-fan-operator",
    "name": "Outdoor Cooling Fan Operator",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "plastic-chair-rental-operator",
    "name": "Plastic Chair Rental Operator",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "tent-rigger",
    "name": "Tent Rigger",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "vip-restroom-attendant",
    "name": "VIP Restroom Attendant",
    "category": "Event Rentals & Rigging",
    "defaultRate": 75,
    "iconName": "Layers"
  },
  {
    "id": "at-money-agent",
    "name": "AT Money Agent",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "agency-banking-operator",
    "name": "Agency Banking Operator",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "daily-savings-collector",
    "name": "Daily Savings Collector",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "foreign-currency-exchange-assistant",
    "name": "Foreign Currency Exchange Assistant",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "loan-collection-officer",
    "name": "Loan Collection Officer",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "mtn-momo-merchant",
    "name": "MTN MoMo Merchant",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "microfinance-field-agent",
    "name": "Microfinance Field Agent",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "mobile-money-agent",
    "name": "Mobile Money Agent",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "pos-cash-withdrawal-agent",
    "name": "POS Cash Withdrawal Agent",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "remittance-payout-agent",
    "name": "Remittance Payout Agent",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "susu-collector",
    "name": "Susu Collector",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "telecel-cash-agent",
    "name": "Telecel Cash Agent",
    "category": "Financial Agency & MoMo",
    "defaultRate": 60,
    "iconName": "CreditCard"
  },
  {
    "id": "3d-epoxy-floor-artisan",
    "name": "3D Epoxy Floor Artisan",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "3d-wall-panel-installer",
    "name": "3D Wall Panel Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "acoustic-ceiling-specialist",
    "name": "Acoustic Ceiling Specialist",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "acoustic-wall-panel-installer",
    "name": "Acoustic Wall Panel Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "baseboard-installer",
    "name": "Baseboard Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "ceiling-repairer",
    "name": "Ceiling Repairer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "ceramic-tiler",
    "name": "Ceramic Tiler",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "cornice-installer",
    "name": "Cornice Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "crown-molding-installer",
    "name": "Crown Molding Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "decorative-plasterer",
    "name": "Decorative Plasterer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "decorative-wall-finisher",
    "name": "Decorative Wall Finisher",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "epoxy-flooring-specialist",
    "name": "Epoxy Flooring Specialist",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "exterior-facade-painter",
    "name": "Exterior Facade Painter",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "floor-polisher",
    "name": "Floor Polisher",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "floor-sander",
    "name": "Floor Sander",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "granite-countertop-fabricator",
    "name": "Granite Countertop Fabricator",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "granite-installer",
    "name": "Granite Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "hardwood-floor-installer",
    "name": "Hardwood Floor Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "industrial-painter",
    "name": "Industrial Painter",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "laminate-flooring-installer",
    "name": "Laminate Flooring Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "marble-installer",
    "name": "Marble Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "marble-restoration-specialist",
    "name": "Marble Restoration Specialist",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "metallic-epoxy-installer",
    "name": "Metallic Epoxy Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "mosaic-tiler",
    "name": "Mosaic Tiler",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "pvc-wall-cladding-installer",
    "name": "PVC Wall Cladding Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "parquet-floor-installer",
    "name": "Parquet Floor Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "plaster-of-paris-artisan",
    "name": "Plaster of Paris Artisan",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "plasterboard-installer",
    "name": "Plasterboard Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "porcelain-tiler",
    "name": "Porcelain Tiler",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "spray-painter",
    "name": "Spray Painter",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "stucco-artisan",
    "name": "Stucco Artisan",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "stucco-painter",
    "name": "Stucco Painter",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "subway-tiler",
    "name": "Subway Tiler",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "suspended-ceiling-installer",
    "name": "Suspended Ceiling Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "terrazzo-polisher",
    "name": "Terrazzo Polisher",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "terrazzo-specialist",
    "name": "Terrazzo Specialist",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "textured-paint-specialist",
    "name": "Textured Paint Specialist",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "tile-regrouting-specialist",
    "name": "Tile Regrouting Specialist",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "tongue-and-groove-ceiling-installer",
    "name": "Tongue and Groove Ceiling Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "venetian-plasterer",
    "name": "Venetian Plasterer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "vinyl-plank-installer",
    "name": "Vinyl Plank Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "wall-skimmer",
    "name": "Wall Skimmer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "wallpaper-installer",
    "name": "Wallpaper Installer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "wood-floor-polisher",
    "name": "Wood Floor Polisher",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "wood-stainer",
    "name": "Wood Stainer",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "wood-varnisher",
    "name": "Wood Varnisher",
    "category": "Finishing & Ceilings",
    "defaultRate": 80,
    "iconName": "Paintbrush"
  },
  {
    "id": "adult-swimming-instructor",
    "name": "Adult Swimming Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "aerobics-instructor",
    "name": "Aerobics Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "basketball-coach",
    "name": "Basketball Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "bodybuilding-coach",
    "name": "Bodybuilding Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "boxing-trainer",
    "name": "Boxing Trainer",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "female-fitness-coach",
    "name": "Female Fitness Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "football-coach",
    "name": "Football Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "grassroots-soccer-coach",
    "name": "Grassroots Soccer Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "hiit-workout-coach",
    "name": "HIIT Workout Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "hatha-yoga-teacher",
    "name": "Hatha Yoga Teacher",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "judo-coach",
    "name": "Judo Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "karate-instructor",
    "name": "Karate Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "kickboxing-coach",
    "name": "Kickboxing Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "kids-swimming-coach",
    "name": "Kids Swimming Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "lifesaving-trainer",
    "name": "Lifesaving Trainer",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "mma-instructor",
    "name": "MMA Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "martial-arts-instructor",
    "name": "Martial Arts Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "mat-pilates-coach",
    "name": "Mat Pilates Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "personal-gym-trainer",
    "name": "Personal Gym Trainer",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "pilates-instructor",
    "name": "Pilates Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "reformer-pilates-trainer",
    "name": "Reformer Pilates Trainer",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "self-defense-trainer",
    "name": "Self-Defense Trainer",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "spin-class-instructor",
    "name": "Spin Class Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "sports-conditioning-specialist",
    "name": "Sports Conditioning Specialist",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "step-aerobics-instructor",
    "name": "Step Aerobics Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "swimming-coach",
    "name": "Swimming Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "table-tennis-coach",
    "name": "Table Tennis Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "taekwondo-coach",
    "name": "Taekwondo Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "tennis-coach",
    "name": "Tennis Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "track-athletics-coach",
    "name": "Track Athletics Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "vinyasa-yoga-instructor",
    "name": "Vinyasa Yoga Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "water-aerobics-instructor",
    "name": "Water Aerobics Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "weight-loss-coach",
    "name": "Weight Loss Coach",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "yoga-instructor",
    "name": "Yoga Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "zumba-dance-instructor",
    "name": "Zumba Dance Instructor",
    "category": "Fitness & Sports",
    "defaultRate": 80,
    "iconName": "Activity"
  },
  {
    "id": "ahenema-sandal-craftsman",
    "name": "Ahenema Sandal Craftsman",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "bespoke-leather-shoe-crafter",
    "name": "Bespoke Leather Shoe Crafter",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "casual-leather-boot-maker",
    "name": "Casual Leather Boot Maker",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "cobbler",
    "name": "Cobbler",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "formal-leather-shoe-maker",
    "name": "Formal Leather Shoe Maker",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "handbag-designer",
    "name": "Handbag Designer",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "heel-replacement-artisan",
    "name": "Heel Replacement Artisan",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "horse-tack-maker",
    "name": "Horse Tack Maker",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "leather-backpack-crafter",
    "name": "Leather Backpack Crafter",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "leather-bag-maker",
    "name": "Leather Bag Maker",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "leather-belt-maker",
    "name": "Leather Belt Maker",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "leather-dyeing-specialist",
    "name": "Leather Dyeing Specialist",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "leather-jacket-restorer",
    "name": "Leather Jacket Restorer",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "leather-keyholder-crafter",
    "name": "Leather Keyholder Crafter",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "leather-slippers-craftsman",
    "name": "Leather Slippers Craftsman",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "leather-upholsterer",
    "name": "Leather Upholsterer",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "leather-wallet-maker",
    "name": "Leather Wallet Maker",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "mens-loafer-craftsman",
    "name": "Mens Loafer Craftsman",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "royal-regalia-footwear-maker",
    "name": "Royal Regalia Footwear Maker",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "saddler",
    "name": "Saddler",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "shoe-cleaner",
    "name": "Shoe Cleaner",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "shoe-repairer",
    "name": "Shoe Repairer",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "shoe-shiner",
    "name": "Shoe Shiner",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "shoe-sole-replacer",
    "name": "Shoe Sole Replacer",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "shoe-stretching-specialist",
    "name": "Shoe Stretching Specialist",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "shoemaker",
    "name": "Shoemaker",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "tote-bag-maker",
    "name": "Tote Bag Maker",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "traditional-sandal-maker",
    "name": "Traditional Sandal Maker",
    "category": "Footwear & Leathercraft",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "artificial-turf-installer",
    "name": "Artificial Turf Installer",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "bermuda-grass-specialist",
    "name": "Bermuda Grass Specialist",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "bush-clearing-laborer",
    "name": "Bush Clearing Laborer",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "carpet-grass-installer",
    "name": "Carpet Grass Installer",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "chainsaw-woodcutter",
    "name": "Chainsaw Woodcutter",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "compound-beautification-specialist",
    "name": "Compound Beautification Specialist",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "florist",
    "name": "Florist",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "fresh-flower-retailer",
    "name": "Fresh Flower Retailer",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "garden-lighting-specialist",
    "name": "Garden Lighting Specialist",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "hedge-trimming-specialist",
    "name": "Hedge Trimming Specialist",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "landscape-designer",
    "name": "Landscape Designer",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "lawn-care-specialist",
    "name": "Lawn Care Specialist",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "lawn-mowing-operator",
    "name": "Lawn Mowing Operator",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "ornamental-plant-nursery-operator",
    "name": "Ornamental Plant Nursery Operator",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "paspalum-grass-layer",
    "name": "Paspalum Grass Layer",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "potted-plant-decorator",
    "name": "Potted Plant Decorator",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "rock-garden-builder",
    "name": "Rock Garden Builder",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "stump-removal-worker",
    "name": "Stump Removal Worker",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "tree-feller",
    "name": "Tree Feller",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "tree-lopping-specialist",
    "name": "Tree Lopping Specialist",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "tree-pruning-specialist",
    "name": "Tree Pruning Specialist",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "water-fountain-installer",
    "name": "Water Fountain Installer",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "weed-whacker-operator",
    "name": "Weed Whacker Operator",
    "category": "Gardening & Landscaping",
    "defaultRate": 65,
    "iconName": "TreePine"
  },
  {
    "id": "acrylic-canvas-artist",
    "name": "Acrylic Canvas Artist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "book-cover-designer",
    "name": "Book Cover Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "brochure-designer",
    "name": "Brochure Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "business-card-designer",
    "name": "Business Card Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "calligrapher",
    "name": "Calligrapher",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "caricature-artist",
    "name": "Caricature Artist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "catalog-designer",
    "name": "Catalog Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "charcoal-portraitist",
    "name": "Charcoal Portraitist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "clay-sculptor",
    "name": "Clay Sculptor",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "comic-book-artist",
    "name": "Comic Book Artist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "company-profile-designer",
    "name": "Company Profile Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "corporate-stationery-designer",
    "name": "Corporate Stationery Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "custom-hand-lettering-artist",
    "name": "Custom Hand Lettering Artist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "digital-portrait-artist",
    "name": "Digital Portrait Artist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "event-invitation-designer",
    "name": "Event Invitation Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "fine-art-painter",
    "name": "Fine Art Painter",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "flyer-designer",
    "name": "Flyer Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "funeral-program-designer",
    "name": "Funeral Program Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "graffiti-artist",
    "name": "Graffiti Artist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "infographic-designer",
    "name": "Infographic Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "magazine-layout-artist",
    "name": "Magazine Layout Artist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "menu-designer",
    "name": "Menu Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "oil-painter",
    "name": "Oil Painter",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "packaging-designer",
    "name": "Packaging Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "pencil-sketch-artist",
    "name": "Pencil Sketch Artist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "plaster-sculptor",
    "name": "Plaster Sculptor",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "poster-designer",
    "name": "Poster Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "product-label-designer",
    "name": "Product Label Designer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "sculptor",
    "name": "Sculptor",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "sign-writer",
    "name": "Sign Writer",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "storyboard-artist",
    "name": "Storyboard Artist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "vector-illustrator",
    "name": "Vector Illustrator",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "wall-muralist",
    "name": "Wall Muralist",
    "category": "Graphic Design & Branding",
    "defaultRate": 80,
    "iconName": "Palette"
  },
  {
    "id": "barbecue-ribs-chef",
    "name": "Barbecue Ribs Chef",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "beef-suya-specialist",
    "name": "Beef Suya Specialist",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "cassava-dough-processor",
    "name": "Cassava Dough Processor",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "charcoal-grilled-catfish-maker",
    "name": "Charcoal Grilled Catfish Maker",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "chichinga-griller",
    "name": "Chichinga Griller",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "coconut-oil-extractor",
    "name": "Coconut Oil Extractor",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "commercial-food-grinder-operator",
    "name": "Commercial Food Grinder Operator",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "corn-dough-miller",
    "name": "Corn Dough Miller",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "crispy-calamari-specialist",
    "name": "Crispy Calamari Specialist",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "fried-fish-vendor",
    "name": "Fried Fish Vendor",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "gari-processing-specialist",
    "name": "Gari Processing Specialist",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "grilled-tilapia-specialist",
    "name": "Grilled Tilapia Specialist",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "guinea-fowl-griller",
    "name": "Guinea Fowl Griller",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "palm-oil-processing-artisan",
    "name": "Palm Oil Processing Artisan",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "raw-shea-butter-processor",
    "name": "Raw Shea Butter Processor",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "roast-chicken-specialist",
    "name": "Roast Chicken Specialist",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "roast-pork-griller",
    "name": "Roast Pork Griller",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "smoked-catfish-processor",
    "name": "Smoked Catfish Processor",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "smoked-fish-processor",
    "name": "Smoked Fish Processor",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "smoked-herrings-processor",
    "name": "Smoked Herrings Processor",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "traditional-soap-maker",
    "name": "Traditional Soap Maker",
    "category": "Grills & Food Processing",
    "defaultRate": 70,
    "iconName": "Flame"
  },
  {
    "id": "ac-compressor-specialist",
    "name": "AC Compressor Specialist",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "ac-gas-refiller",
    "name": "AC Gas Refiller",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "ac-installation-specialist",
    "name": "AC Installation Specialist",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "ac-maintenance-specialist",
    "name": "AC Maintenance Specialist",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "ac-repairer",
    "name": "AC Repairer",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "air-curtain-installer",
    "name": "Air Curtain Installer",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "air-handling-unit-technician",
    "name": "Air Handling Unit Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "beverage-cooler-technician",
    "name": "Beverage Cooler Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "blast-freezer-technician",
    "name": "Blast Freezer Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "car-ac-compressor-specialist",
    "name": "Car AC Compressor Specialist",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "car-ac-gas-refiller",
    "name": "Car AC Gas Refiller",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "car-ac-technician",
    "name": "Car AC Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "cassette-ac-installer",
    "name": "Cassette AC Installer",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "central-ac-technician",
    "name": "Central AC Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "chiller-plant-technician",
    "name": "Chiller Plant Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "cold-storage-builder",
    "name": "Cold Storage Builder",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "deep-freezer-repairer",
    "name": "Deep Freezer Repairer",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "display-fridge-technician",
    "name": "Display Fridge Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "ducted-ac-technician",
    "name": "Ducted AC Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "flake-ice-machine-mechanic",
    "name": "Flake Ice Machine Mechanic",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "fresh-air-louver-installer",
    "name": "Fresh Air Louver Installer",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "ice-block-machine-technician",
    "name": "Ice Block Machine Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "ice-cream-machine-mechanic",
    "name": "Ice Cream Machine Mechanic",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "industrial-exhaust-blower-technician",
    "name": "Industrial Exhaust Blower Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "kitchen-extractor-hood-installer",
    "name": "Kitchen Extractor Hood Installer",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "slush-machine-technician",
    "name": "Slush Machine Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "split-ac-technician",
    "name": "Split AC Technician",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "standing-ac-specialist",
    "name": "Standing AC Specialist",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "under-counter-chiller-mechanic",
    "name": "Under-Counter Chiller Mechanic",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "vrf-system-specialist",
    "name": "VRF System Specialist",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "ventilation-duct-installer",
    "name": "Ventilation Duct Installer",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "water-dispenser-repairer",
    "name": "Water Dispenser Repairer",
    "category": "HVAC & Refrigeration",
    "defaultRate": 80,
    "iconName": "Wind"
  },
  {
    "id": "afro-hair-stylist",
    "name": "Afro Hair Stylist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "balayage-color-specialist",
    "name": "Balayage Color Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "blonde-bleaching-specialist",
    "name": "Blonde Bleaching Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "bohemian-braids-specialist",
    "name": "Bohemian Braids Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "box-braids-stylist",
    "name": "Box Braids Stylist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "butterfly-locs-specialist",
    "name": "Butterfly Locs Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "cornrow-stylist",
    "name": "Cornrow Stylist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "custom-hand-tied-wig-maker",
    "name": "Custom Hand-Tied Wig Maker",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "deep-conditioning-specialist",
    "name": "Deep Conditioning Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "dreadlock-colorist",
    "name": "Dreadlock Colorist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "dreadlock-extension-specialist",
    "name": "Dreadlock Extension Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "dreadlock-starter",
    "name": "Dreadlock Starter",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "dreadlocks-interlocking-specialist",
    "name": "Dreadlocks Interlocking Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "feed-in-braids-stylist",
    "name": "Feed-in Braids Stylist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "frontal-glueless-installer",
    "name": "Frontal Glueless Installer",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "goddess-braids-stylist",
    "name": "Goddess Braids Stylist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "hair-colorist",
    "name": "Hair Colorist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "hair-relaxing-specialist",
    "name": "Hair Relaxing Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "hair-steaming-technician",
    "name": "Hair Steaming Technician",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "hair-washing-specialist",
    "name": "Hair Washing Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "instant-locs-artisan",
    "name": "Instant Locs Artisan",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "lace-closure-maker",
    "name": "Lace Closure Maker",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "lace-frontal-ventilator",
    "name": "Lace Frontal Ventilator",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "machine-sewn-wig-maker",
    "name": "Machine Sewn Wig Maker",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "micro-locs-specialist",
    "name": "Micro Locs Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "microlink-extensions-specialist",
    "name": "Microlink Extensions Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "natural-hair-care-specialist",
    "name": "Natural Hair Care Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "passion-twists-stylist",
    "name": "Passion Twists Stylist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "retwist-loctician",
    "name": "Retwist Loctician",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "scalp-treatment-specialist",
    "name": "Scalp Treatment Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "senegalese-twists-stylist",
    "name": "Senegalese Twists Stylist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "sew-in-weave-specialist",
    "name": "Sew-in Weave Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "silk-press-specialist",
    "name": "Silk Press Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "sisterlocks-consultant",
    "name": "Sisterlocks Consultant",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "soft-locs-stylist",
    "name": "Soft Locs Stylist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "stitch-braids-specialist",
    "name": "Stitch Braids Specialist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "tape-in-extensions-installer",
    "name": "Tape-in Extensions Installer",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "texturizer-stylist",
    "name": "Texturizer Stylist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "wig-colorist",
    "name": "Wig Colorist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "wig-revamper",
    "name": "Wig Revamper",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "wig-stylist",
    "name": "Wig Stylist",
    "category": "Hairdressing & Braiding",
    "defaultRate": 75,
    "iconName": "Scissors"
  },
  {
    "id": "apotoyewa-earthenware-maker",
    "name": "Apotoyewa Earthenware Maker",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "asante-stool-carver",
    "name": "Asante Stool Carver",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "bamboo-furniture-craftsman",
    "name": "Bamboo Furniture Craftsman",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "bead-stringing-artist",
    "name": "Bead Stringing Artist",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "bolga-basket-weaver",
    "name": "Bolga Basket Weaver",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "calabash-carving-artist",
    "name": "Calabash Carving Artist",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "cane-weaver",
    "name": "Cane Weaver",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "chief-linguist-staff-carver",
    "name": "Chief Linguist Staff Carver",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "decorative-clay-sculptor",
    "name": "Decorative Clay Sculptor",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "djembe-drum-maker",
    "name": "Djembe Drum Maker",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "flower-pot-ceramist",
    "name": "Flower Pot Ceramist",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "fontomfrom-drum-maker",
    "name": "Fontomfrom Drum Maker",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "gourd-decorator",
    "name": "Gourd Decorator",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "rattan-furniture-weaver",
    "name": "Rattan Furniture Weaver",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "straw-hat-maker",
    "name": "Straw Hat Maker",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "straw-mat-weaver",
    "name": "Straw Mat Weaver",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "talking-drum-carver",
    "name": "Talking Drum Carver",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "traditional-clay-potter",
    "name": "Traditional Clay Potter",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "traditional-cooking-pot-maker",
    "name": "Traditional Cooking Pot Maker",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "traditional-drum-carver",
    "name": "Traditional Drum Carver",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "traditional-flywhisk-maker",
    "name": "Traditional Flywhisk Maker",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "traditional-mask-carver",
    "name": "Traditional Mask Carver",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "traditional-regalia-artisan",
    "name": "Traditional Regalia Artisan",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "traditional-royal-stool-maker",
    "name": "Traditional Royal Stool Maker",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "traditional-straw-basket-weaver",
    "name": "Traditional Straw Basket Weaver",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "wooden-figurine-sculptor",
    "name": "Wooden Figurine Sculptor",
    "category": "Handicrafts & Artisans",
    "defaultRate": 70,
    "iconName": "Palette"
  },
  {
    "id": "blind-installer",
    "name": "Blind Installer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "cabinet-knob-installer",
    "name": "Cabinet Knob Installer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "ceiling-fan-installer",
    "name": "Ceiling Fan Installer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "chandelier-hanging-specialist",
    "name": "Chandelier Hanging Specialist",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "clothesline-rigger",
    "name": "Clothesline Rigger",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "curtain-rod-installer",
    "name": "Curtain Rod Installer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "door-closer-installer",
    "name": "Door Closer Installer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "door-hinge-repairer",
    "name": "Door Hinge Repairer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "electrician-handyman",
    "name": "Electrician-Handyman",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "flat-pack-furniture-assembler",
    "name": "Flat Pack Furniture Assembler",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "fly-screen-installer",
    "name": "Fly Screen Installer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "general-home-maintenance-handyman",
    "name": "General Home Maintenance Handyman",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "mirror-hanging-specialist",
    "name": "Mirror Hanging Specialist",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "mosquito-net-installer",
    "name": "Mosquito Net Installer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "office-handyman",
    "name": "Office Handyman",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "picture-hanging-specialist",
    "name": "Picture Hanging Specialist",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "plumber-handyman",
    "name": "Plumber-Handyman",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "silicone-caulking-specialist",
    "name": "Silicone Caulking Specialist",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "sliding-door-track-repairer",
    "name": "Sliding Door Track Repairer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "water-tap-washer-replacer",
    "name": "Water Tap Washer Replacer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "window-handle-repairer",
    "name": "Window Handle Repairer",
    "category": "Handyman & Home Repairs",
    "defaultRate": 70,
    "iconName": "Wrench"
  },
  {
    "id": "baobab-pulp-processor",
    "name": "Baobab Pulp Processor",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "black-seed-oil-processor",
    "name": "Black Seed Oil Processor",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "herbal-medicine-bottler",
    "name": "Herbal Medicine Bottler",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "hibiscus-sachet-packer",
    "name": "Hibiscus Sachet Packer",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "medicinal-plant-gatherer",
    "name": "Medicinal Plant Gatherer",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "moringa-powder-producer",
    "name": "Moringa Powder Producer",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "neem-oil-processor",
    "name": "Neem Oil Processor",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "roots-herbal-processor",
    "name": "Roots Herbal Processor",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "shea-butter-formulator",
    "name": "Shea Butter Formulator",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "traditional-bone-setter-assistant",
    "name": "Traditional Bone Setter Assistant",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "traditional-herbalist-assistant",
    "name": "Traditional Herbalist Assistant",
    "category": "Herbal & Wellness",
    "defaultRate": 65,
    "iconName": "HeartPulse"
  },
  {
    "id": "banquet-food-server",
    "name": "Banquet Food Server",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "bellhop",
    "name": "Bellhop",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "buffet-food-attendant",
    "name": "Buffet Food Attendant",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "cocktail-party-server",
    "name": "Cocktail Party Server",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "commercial-dishwasher",
    "name": "Commercial Dishwasher",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "concierge",
    "name": "Concierge",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "drink-pourer",
    "name": "Drink Pourer",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "event-table-busser",
    "name": "Event Table Busser",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "food-station-carver",
    "name": "Food Station Carver",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "head-waiter",
    "name": "Head Waiter",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "hotel-front-desk-assistant",
    "name": "Hotel Front Desk Assistant",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "hotel-housekeeping-attendant",
    "name": "Hotel Housekeeping Attendant",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "kitchen-steward",
    "name": "Kitchen Steward",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "restaurant-hostess",
    "name": "Restaurant Hostess",
    "category": "Hospitality & Serving",
    "defaultRate": 55,
    "iconName": "Utensils"
  },
  {
    "id": "animal-feed-miller",
    "name": "Animal Feed Miller",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "aquarium-builder",
    "name": "Aquarium Builder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "beekeeper",
    "name": "Beekeeper",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "boerboel-breeder",
    "name": "Boerboel Breeder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "broiler-chicken-producer",
    "name": "Broiler Chicken Producer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "cat-groomer",
    "name": "Cat Groomer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "catfish-fingerling-specialist",
    "name": "Catfish Fingerling Specialist",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "cattle-herdsman",
    "name": "Cattle Herdsman",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "cattle-rancher",
    "name": "Cattle Rancher",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "dairy-milk-producer",
    "name": "Dairy Milk Producer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "day-old-chick-brooder",
    "name": "Day-Old Chick Brooder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "dog-breeder",
    "name": "Dog Breeder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "dog-groomer",
    "name": "Dog Groomer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "dog-trainer",
    "name": "Dog Trainer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "dog-walker",
    "name": "Dog Walker",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "duck-farmer",
    "name": "Duck Farmer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "feed-pellet-machine-operator",
    "name": "Feed Pellet Machine Operator",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "fish-farmer",
    "name": "Fish Farmer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "fish-feed-formulator",
    "name": "Fish Feed Formulator",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "fish-pond-excavator",
    "name": "Fish Pond Excavator",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "german-shepherd-breeder",
    "name": "German Shepherd Breeder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "giant-african-snail-specialist",
    "name": "Giant African Snail Specialist",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "goat-farmer",
    "name": "Goat Farmer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "grasscutter-breeder",
    "name": "Grasscutter Breeder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "guard-dog-trainer",
    "name": "Guard Dog Trainer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "guinea-fowl-breeder",
    "name": "Guinea Fowl Breeder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "hatchery-incubator-operator",
    "name": "Hatchery Incubator Operator",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "honey-harvester",
    "name": "Honey Harvester",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "honey-processor",
    "name": "Honey Processor",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "horse-groomer",
    "name": "Horse Groomer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "horse-trainer",
    "name": "Horse Trainer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "layer-hen-egg-producer",
    "name": "Layer Hen Egg Producer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "livestock-farmer",
    "name": "Livestock Farmer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "livestock-insemination-technician",
    "name": "Livestock Insemination Technician",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "ornamental-fish-specialist",
    "name": "Ornamental Fish Specialist",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "pet-groomer",
    "name": "Pet Groomer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "pet-sitter",
    "name": "Pet Sitter",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "pig-breeder",
    "name": "Pig Breeder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "piggery-farmer",
    "name": "Piggery Farmer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "poultry-vaccinator",
    "name": "Poultry Vaccinator",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "quail-breeder",
    "name": "Quail Breeder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "rabbit-farmer",
    "name": "Rabbit Farmer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "rottweiler-breeder",
    "name": "Rottweiler Breeder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "sheep-breeder",
    "name": "Sheep Breeder",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "snail-farmer",
    "name": "Snail Farmer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "turkey-farmer",
    "name": "Turkey Farmer",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "veterinarian",
    "name": "Veterinarian",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "veterinary-assistant",
    "name": "Veterinary Assistant",
    "category": "Livestock, Poultry & Aquaculture",
    "defaultRate": 70,
    "iconName": "Feather"
  },
  {
    "id": "auto-locksmith",
    "name": "Auto Locksmith",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "bank-vault-technician",
    "name": "Bank Vault Technician",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "broken-key-extractor",
    "name": "Broken Key Extractor",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "car-door-unlocking-specialist",
    "name": "Car Door Unlocking Specialist",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "combination-lock-reset-specialist",
    "name": "Combination Lock Reset Specialist",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "commercial-locksmith",
    "name": "Commercial Locksmith",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "deadbolt-lock-installer",
    "name": "Deadbolt Lock Installer",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "digital-safe-opening-specialist",
    "name": "Digital Safe Opening Specialist",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "door-hardware-installer",
    "name": "Door Hardware Installer",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "emergency-locksmith",
    "name": "Emergency Locksmith",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "key-duplication-artisan",
    "name": "Key Duplication Artisan",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "master-key-system-designer",
    "name": "Master Key System Designer",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "mortise-lock-installer",
    "name": "Mortise Lock Installer",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "padlock-specialist",
    "name": "Padlock Specialist",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "residential-locksmith",
    "name": "Residential Locksmith",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "rim-lock-installer",
    "name": "Rim Lock Installer",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "safe-and-vault-technician",
    "name": "Safe and Vault Technician",
    "category": "Locksmithing & Safes",
    "defaultRate": 75,
    "iconName": "Key"
  },
  {
    "id": "airport-cargo-handler",
    "name": "Airport Cargo Handler",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "cargo-loader",
    "name": "Cargo Loader",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "cement-bag-offloader",
    "name": "Cement Bag Offloader",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "clearing-assistant",
    "name": "Clearing Assistant",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "cold-storage-handler",
    "name": "Cold Storage Handler",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "container-unloader",
    "name": "Container Unloader",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "courier-dispatcher",
    "name": "Courier Dispatcher",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "fertilizer-bag-handler",
    "name": "Fertilizer Bag Handler",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "fragile-item-wrapper",
    "name": "Fragile Item Wrapper",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "freight-logistics-handler",
    "name": "Freight Logistics Handler",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "furniture-mover",
    "name": "Furniture Mover",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "grain-silo-worker",
    "name": "Grain Silo Worker",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "household-goods-packer",
    "name": "Household Goods Packer",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "luggage-porter",
    "name": "Luggage Porter",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "market-porter",
    "name": "Market Porter",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "moving-consultant",
    "name": "Moving Consultant",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "moving-truck-loader",
    "name": "Moving Truck Loader",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "pallet-jack-operator",
    "name": "Pallet Jack Operator",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "piano-mover-specialist",
    "name": "Piano Mover Specialist",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "shipping-container-stuffer",
    "name": "Shipping Container Stuffer",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "timber-yard-porter",
    "name": "Timber Yard Porter",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "warehouse-inventory-handler",
    "name": "Warehouse Inventory Handler",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "warehouse-order-picker",
    "name": "Warehouse Order Picker",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "waybill-clerk",
    "name": "Waybill Clerk",
    "category": "Logistics & Moving",
    "defaultRate": 70,
    "iconName": "Truck"
  },
  {
    "id": "circus-performer",
    "name": "Circus Performer",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "club-dj",
    "name": "Club DJ",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "corporate-event-mc",
    "name": "Corporate Event MC",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "event-humorist",
    "name": "Event Humorist",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "fire-breather",
    "name": "Fire Breather",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "funeral-mc",
    "name": "Funeral MC",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "illusionist",
    "name": "Illusionist",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "juggler",
    "name": "Juggler",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "karaoke-host",
    "name": "Karaoke Host",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "magician",
    "name": "Magician",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "mascot-performer",
    "name": "Mascot Performer",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "mobile-event-dj",
    "name": "Mobile Event DJ",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "panel-discussion-moderator",
    "name": "Panel Discussion Moderator",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "party-hypeman",
    "name": "Party Hypeman",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "radio-dj",
    "name": "Radio DJ",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "red-carpet-host",
    "name": "Red Carpet Host",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "stand-up-comedian",
    "name": "Stand-Up Comedian",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "stilt-walker",
    "name": "Stilt Walker",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "traditional-engagement-okyeame",
    "name": "Traditional Engagement Okyeame",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "video-dj",
    "name": "Video DJ",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "wedding-dj",
    "name": "Wedding DJ",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "wedding-reception-mc",
    "name": "Wedding Reception MC",
    "category": "MCs & Performers",
    "defaultRate": 95,
    "iconName": "Mic"
  },
  {
    "id": "auto-gele-maker",
    "name": "Auto-Gele Maker",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "body-painting-artist",
    "name": "Body Painting Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "bridal-henna-designer",
    "name": "Bridal Henna Designer",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "classic-lash-specialist",
    "name": "Classic Lash Specialist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "editorial-makeup-artist",
    "name": "Editorial Makeup Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "eyebrow-threading-specialist",
    "name": "Eyebrow Threading Specialist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "eyebrow-tinting-specialist",
    "name": "Eyebrow Tinting Specialist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "eyebrow-waxing-specialist",
    "name": "Eyebrow Waxing Specialist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "face-painter",
    "name": "Face Painter",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "film-makeup-artist",
    "name": "Film Makeup Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "gele-stylist",
    "name": "Gele Stylist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "glam-makeup-artist",
    "name": "Glam Makeup Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "headscarf-stylist",
    "name": "Headscarf Stylist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "henna-artist",
    "name": "Henna Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "hybrid-lash-technician",
    "name": "Hybrid Lash Technician",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "lash-extension-technician",
    "name": "Lash Extension Technician",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "lash-lift-specialist",
    "name": "Lash Lift Specialist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "lash-tinting-specialist",
    "name": "Lash Tinting Specialist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "lip-blush-tattoo-artist",
    "name": "Lip Blush Tattoo Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "mega-volume-lash-artist",
    "name": "Mega Volume Lash Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "microblading-artist",
    "name": "Microblading Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "microshading-artist",
    "name": "Microshading Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "natural-makeup-specialist",
    "name": "Natural Makeup Specialist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "ombre-powder-brows-specialist",
    "name": "Ombre Powder Brows Specialist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "soft-glam-artist",
    "name": "Soft Glam Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "special-effects-makeup-artist",
    "name": "Special Effects Makeup Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "traditional-wedding-makeup-specialist",
    "name": "Traditional Wedding Makeup Specialist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "turban-wrapper",
    "name": "Turban Wrapper",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "volume-lash-artist",
    "name": "Volume Lash Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "white-henna-artist",
    "name": "White Henna Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "white-wedding-makeup-artist",
    "name": "White Wedding Makeup Artist",
    "category": "Makeup & Brows",
    "defaultRate": 80,
    "iconName": "Sparkles"
  },
  {
    "id": "aluminium-pot-caster",
    "name": "Aluminium Pot Caster",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "argon-welder",
    "name": "Argon Welder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "automated-sliding-gate-fabricator",
    "name": "Automated Sliding Gate Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "automated-swing-gate-fabricator",
    "name": "Automated Swing Gate Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "balcony-glass-installer",
    "name": "Balcony Glass Installer",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "balustrade-installer",
    "name": "Balustrade Installer",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "billboard-metal-fabricator",
    "name": "Billboard Metal Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "blacksmith",
    "name": "Blacksmith",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "canopy-metal-fabricator",
    "name": "Canopy Metal Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "casement-window-fabricator",
    "name": "Casement Window Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "container-house-fabricator",
    "name": "Container House Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "curtain-wall-glazier",
    "name": "Curtain Wall Glazier",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "electric-arc-welder",
    "name": "Electric Arc Welder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "frameless-shower-glass-installer",
    "name": "Frameless Shower Glass Installer",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "gas-cutter",
    "name": "Gas Cutter",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "gate-fabricator",
    "name": "Gate Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "glass-cutter",
    "name": "Glass Cutter",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "glass-fitter",
    "name": "Glass Fitter",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "glass-tinting-specialist",
    "name": "Glass Tinting Specialist",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "handrail-fabricator",
    "name": "Handrail Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "industrial-pipe-welder",
    "name": "Industrial Pipe Welder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "iron-bed-frame-maker",
    "name": "Iron Bed Frame Maker",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "ironmonger",
    "name": "Ironmonger",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "mig-welder",
    "name": "MIG Welder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "metal-canopy-welder",
    "name": "Metal Canopy Welder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "metal-lathe-machinist",
    "name": "Metal Lathe Machinist",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "metal-staircase-builder",
    "name": "Metal Staircase Builder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "milling-machine-operator",
    "name": "Milling Machine Operator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "oxy-acetylene-welder",
    "name": "Oxy-Acetylene Welder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "plasma-torch-operator",
    "name": "Plasma Torch Operator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "projected-window-installer",
    "name": "Projected Window Installer",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "rolling-shutter-installer",
    "name": "Rolling Shutter Installer",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "scrap-metal-welder",
    "name": "Scrap Metal Welder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "security-cage-fabricator",
    "name": "Security Cage Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "security-grille-fabricator",
    "name": "Security Grille Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "sheet-metal-worker",
    "name": "Sheet Metal Worker",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "shipping-container-modifier",
    "name": "Shipping Container Modifier",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "sliding-glass-door-installer",
    "name": "Sliding Glass Door Installer",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "spiral-staircase-fabricator",
    "name": "Spiral Staircase Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "steel-warehouse-builder",
    "name": "Steel Warehouse Builder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "structural-steel-fabricator",
    "name": "Structural Steel Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "tig-welder",
    "name": "TIG Welder",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "tool-and-die-maker",
    "name": "Tool and Die Maker",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "traditional-metal-forger",
    "name": "Traditional Metal Forger",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "water-tank-stand-fabricator",
    "name": "Water Tank Stand Fabricator",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "wrought-iron-artisan",
    "name": "Wrought Iron Artisan",
    "category": "Metalwork & Glazing",
    "defaultRate": 85,
    "iconName": "Flame"
  },
  {
    "id": "bead-handbag-artisan",
    "name": "Bead Handbag Artisan",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "bead-rosary-maker",
    "name": "Bead Rosary Maker",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "chief-stool-regalia-specialist",
    "name": "Chief Stool Regalia Specialist",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "church-hat-maker",
    "name": "Church Hat Maker",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "coral-bead-specialist",
    "name": "Coral Bead Specialist",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "custom-jeweler",
    "name": "Custom Jeweler",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "fascinator-designer",
    "name": "Fascinator Designer",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "gemstone-setter",
    "name": "Gemstone Setter",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "gold-chain-smith",
    "name": "Gold Chain Smith",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "goldsmith",
    "name": "Goldsmith",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "headwrap-stylist",
    "name": "Headwrap Stylist",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "jewelry-polisher",
    "name": "Jewelry Polisher",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "krobo-bead-maker",
    "name": "Krobo Bead Maker",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "luxury-watch-servicing-specialist",
    "name": "Luxury Watch Servicing Specialist",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "mechanical-clock-repairer",
    "name": "Mechanical Clock Repairer",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "milliner",
    "name": "Milliner",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "quartz-watch-technician",
    "name": "Quartz Watch Technician",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "recycled-glass-bead-artisan",
    "name": "Recycled Glass Bead Artisan",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "ring-resizing-specialist",
    "name": "Ring Resizing Specialist",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "seed-bead-jewelry-designer",
    "name": "Seed Bead Jewelry Designer",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "silver-filigree-artisan",
    "name": "Silver Filigree Artisan",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "silversmith",
    "name": "Silversmith",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "traditional-bead-stringer",
    "name": "Traditional Bead Stringer",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "traditional-gold-ornament-maker",
    "name": "Traditional Gold Ornament Maker",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "traditional-royal-regalia-maker",
    "name": "Traditional Royal Regalia Maker",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "traditional-velvet-hat-maker",
    "name": "Traditional Velvet Hat Maker",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "wall-clock-technician",
    "name": "Wall Clock Technician",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "watch-repairer",
    "name": "Watch Repairer",
    "category": "Millinery, Beads & Jewelry",
    "defaultRate": 75,
    "iconName": "Sparkles"
  },
  {
    "id": "bajaj-boxer-mechanic",
    "name": "Bajaj Boxer Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "brush-cutter-repairer",
    "name": "Brush Cutter Repairer",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "cargo-tricycle-specialist",
    "name": "Cargo Tricycle Specialist",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "chainsaw-mechanic",
    "name": "Chainsaw Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "e-bike-repairer",
    "name": "E-Bike Repairer",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "electric-scooter-technician",
    "name": "Electric Scooter Technician",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "haojue-motorcycle-mechanic",
    "name": "Haojue Motorcycle Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "lawn-mower-mechanic",
    "name": "Lawn Mower Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "mistblower-sprayer-mechanic",
    "name": "Mistblower Sprayer Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "motorbike-engine-specialist",
    "name": "Motorbike Engine Specialist",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "passenger-tricycle-specialist",
    "name": "Passenger Tricycle Specialist",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "royal-motorcycle-specialist",
    "name": "Royal Motorcycle Specialist",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "small-generator-mechanic",
    "name": "Small Generator Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "tvs-motorcycle-mechanic",
    "name": "TVS Motorcycle Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "tricycle-mechanic",
    "name": "Tricycle Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "two-stroke-engine-specialist",
    "name": "Two-Stroke Engine Specialist",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "water-pump-engine-repairer",
    "name": "Water Pump Engine Repairer",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "yellow-yellow-mechanic",
    "name": "Yellow Yellow Mechanic",
    "category": "Motorcycles & Small Engines",
    "defaultRate": 65,
    "iconName": "Wrench"
  },
  {
    "id": "acoustic-guitarist",
    "name": "Acoustic Guitarist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "adowa-dancer",
    "name": "Adowa Dancer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "afrobeats-choreographer",
    "name": "Afrobeats Choreographer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "agbadza-dancer",
    "name": "Agbadza Dancer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "atumpan-drummer",
    "name": "Atumpan Drummer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "audio-mastering-engineer",
    "name": "Audio Mastering Engineer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "audio-mixing-engineer",
    "name": "Audio Mixing Engineer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "backup-singer",
    "name": "Backup Singer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "bandleader",
    "name": "Bandleader",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "bass-guitarist",
    "name": "Bass Guitarist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "beat-maker",
    "name": "Beat Maker",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "borborbor-dancer",
    "name": "Borborbor Dancer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "choral-director",
    "name": "Choral Director",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "church-organist",
    "name": "Church Organist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "contemporary-dancer",
    "name": "Contemporary Dancer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "damba-dancer",
    "name": "Damba Dancer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "djembe-drummer",
    "name": "Djembe Drummer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "drummer",
    "name": "Drummer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "flutist",
    "name": "Flutist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "fontomfrom-drum-master",
    "name": "Fontomfrom Drum Master",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "front-of-house-sound-mixer",
    "name": "Front of House Sound Mixer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "gome-drummer",
    "name": "Gome Drummer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "gospel-minister-vocalist",
    "name": "Gospel Minister Vocalist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "hip-hop-dancer",
    "name": "Hip-hop Dancer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "keyboardist",
    "name": "Keyboardist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "kpanlogo-dancer",
    "name": "Kpanlogo Dancer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "lead-guitarist",
    "name": "Lead Guitarist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "lead-vocalist",
    "name": "Lead Vocalist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "line-array-speaker-rigger",
    "name": "Line Array Speaker Rigger",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "live-event-audio-engineer",
    "name": "Live Event Audio Engineer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "monitor-sound-engineer",
    "name": "Monitor Sound Engineer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "music-producer",
    "name": "Music Producer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "pa-system-operator",
    "name": "PA System Operator",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "piano-accompanist",
    "name": "Piano Accompanist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "radio-jingle-producer",
    "name": "Radio Jingle Producer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "rhythm-guitarist",
    "name": "Rhythm Guitarist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "saxophonist",
    "name": "Saxophonist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "studio-recording-engineer",
    "name": "Studio Recording Engineer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "traditional-ghanaian-dancer",
    "name": "Traditional Ghanaian Dancer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "traditional-talking-drummer",
    "name": "Traditional Talking Drummer",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "trombonist",
    "name": "Trombonist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "trumpeter",
    "name": "Trumpeter",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "violinist",
    "name": "Violinist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "voiceover-artist",
    "name": "Voiceover Artist",
    "category": "Music & Sound",
    "defaultRate": 90,
    "iconName": "Music"
  },
  {
    "id": "acoustic-guitar-teacher",
    "name": "Acoustic Guitar Teacher",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "automatic-car-driving-instructor",
    "name": "Automatic Car Driving Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "baking-pastry-trainer",
    "name": "Baking Pastry Trainer",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "bass-guitar-teacher",
    "name": "Bass Guitar Teacher",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "carpentry-instructor",
    "name": "Carpentry Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "catering-culinary-instructor",
    "name": "Catering Culinary Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "choir-vocal-trainer",
    "name": "Choir Vocal Trainer",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "classical-piano-instructor",
    "name": "Classical Piano Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "computer-literacy-trainer",
    "name": "Computer Literacy Trainer",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "drawing-art-tutor",
    "name": "Drawing Art Tutor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "drumkit-instructor",
    "name": "Drumkit Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "electric-guitar-instructor",
    "name": "Electric Guitar Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "electrical-installation-trainer",
    "name": "Electrical Installation Trainer",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "flute-teacher",
    "name": "Flute Teacher",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "hairdressing-beauty-trainer",
    "name": "Hairdressing Beauty Trainer",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "heavy-duty-truck-driving-instructor",
    "name": "Heavy Duty Truck Driving Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "keyboard-teacher",
    "name": "Keyboard Teacher",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "makeup-artistry-instructor",
    "name": "Makeup Artistry Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "manual-car-driving-instructor",
    "name": "Manual Car Driving Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "motorcycle-riding-instructor",
    "name": "Motorcycle Riding Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "music-production-instructor",
    "name": "Music Production Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "music-theory-tutor",
    "name": "Music Theory Tutor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "painting-art-tutor",
    "name": "Painting Art Tutor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "photography-instructor",
    "name": "Photography Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "piano-teacher",
    "name": "Piano Teacher",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "plumbing-apprenticeship-trainer",
    "name": "Plumbing Apprenticeship Trainer",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "saxophone-instructor",
    "name": "Saxophone Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "sewing-fashion-design-instructor",
    "name": "Sewing Fashion Design Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "singing-instructor",
    "name": "Singing Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "sound-engineering-tutor",
    "name": "Sound Engineering Tutor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "traditional-drumming-instructor",
    "name": "Traditional Drumming Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "trumpet-teacher",
    "name": "Trumpet Teacher",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "videography-trainer",
    "name": "Videography Trainer",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "violin-teacher",
    "name": "Violin Teacher",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "voice-coach",
    "name": "Voice Coach",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "welding-metal-fabrication-instructor",
    "name": "Welding Metal Fabrication Instructor",
    "category": "Music, Arts & Driving Instruction",
    "defaultRate": 75,
    "iconName": "Music"
  },
  {
    "id": "3d-nail-art-specialist",
    "name": "3D Nail Art Specialist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "biab-nail-technician",
    "name": "BIAB Nail Technician",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "builder-gel-specialist",
    "name": "Builder Gel Specialist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "callus-removal-specialist",
    "name": "Callus Removal Specialist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "dip-powder-nail-specialist",
    "name": "Dip Powder Nail Specialist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "foot-reflexology-technician",
    "name": "Foot Reflexology Technician",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "foot-spa-specialist",
    "name": "Foot Spa Specialist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "french-tip-specialist",
    "name": "French Tip Specialist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "gel-nail-extension-artist",
    "name": "Gel Nail Extension Artist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "ingrown-toenail-specialist",
    "name": "Ingrown Toenail Specialist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "jelly-pedicure-specialist",
    "name": "Jelly Pedicure Specialist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "manicurist",
    "name": "Manicurist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "nail-piercing-artist",
    "name": "Nail Piercing Artist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "paraffin-wax-technician",
    "name": "Paraffin Wax Technician",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "pedicurist",
    "name": "Pedicurist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "polygel-nail-technician",
    "name": "Polygel Nail Technician",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "press-on-nail-designer",
    "name": "Press-on Nail Designer",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "russian-manicure-specialist",
    "name": "Russian Manicure Specialist",
    "category": "Nails & Hand Care",
    "defaultRate": 70,
    "iconName": "Sparkles"
  },
  {
    "id": "antimicrobial-disinfection-specialist",
    "name": "Antimicrobial Disinfection Specialist",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "bedbug-eradication-specialist",
    "name": "Bedbug Eradication Specialist",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "bee-removal-specialist",
    "name": "Bee Removal Specialist",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "biohazard-cleaning-technician",
    "name": "Biohazard Cleaning Technician",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "cesspit-emptier-operator",
    "name": "Cesspit Emptier Operator",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "cockroach-control-specialist",
    "name": "Cockroach Control Specialist",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "commercial-fumigation-technician",
    "name": "Commercial Fumigation Technician",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "mobile-toilet-sanitation-worker",
    "name": "Mobile Toilet Sanitation Worker",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "mosquito-fogging-operator",
    "name": "Mosquito Fogging Operator",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "residential-pest-exterminator",
    "name": "Residential Pest Exterminator",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "rodent-exterminator",
    "name": "Rodent Exterminator",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "snake-repellent-specialist",
    "name": "Snake Repellent Specialist",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "soakaway-odor-control-specialist",
    "name": "Soakaway Odor Control Specialist",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "subterranean-termite-specialist",
    "name": "Subterranean Termite Specialist",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "termite-control-specialist",
    "name": "Termite Control Specialist",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "thermal-fogging-technician",
    "name": "Thermal Fogging Technician",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "wasps-nest-remover",
    "name": "Wasps Nest Remover",
    "category": "Pest Control & Environmental",
    "defaultRate": 75,
    "iconName": "Bug"
  },
  {
    "id": "android-phone-hardware-technician",
    "name": "Android Phone Hardware Technician",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "apple-watch-screen-specialist",
    "name": "Apple Watch Screen Specialist",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "audio-amplifier-repairer",
    "name": "Audio Amplifier Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "bluetooth-speaker-repairer",
    "name": "Bluetooth Speaker Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "drone-repair-technician",
    "name": "Drone Repair Technician",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "game-console-repairer",
    "name": "Game Console Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "headphone-repair-technician",
    "name": "Headphone Repair Technician",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "led-smart-tv-technician",
    "name": "LED Smart TV Technician",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "nintendo-switch-repairer",
    "name": "Nintendo Switch Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "phone-charging-port-repairer",
    "name": "Phone Charging Port Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "phone-software-repairer",
    "name": "Phone Software Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "phone-unlocking-specialist",
    "name": "Phone Unlocking Specialist",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "phone-water-damage-technician",
    "name": "Phone Water Damage Technician",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "playstation-repairer",
    "name": "PlayStation Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "power-bank-repairer",
    "name": "Power Bank Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "samsung-phone-repair-specialist",
    "name": "Samsung Phone Repair Specialist",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "samsung-screen-repairer",
    "name": "Samsung Screen Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "smart-watch-repairer",
    "name": "Smart Watch Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "smartphone-screen-specialist",
    "name": "Smartphone Screen Specialist",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "tv-backlight-specialist",
    "name": "TV Backlight Specialist",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "tv-repairer",
    "name": "TV Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "tv-screen-board-repairer",
    "name": "TV Screen Board Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "tablet-repair-technician",
    "name": "Tablet Repair Technician",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "xbox-repair-technician",
    "name": "Xbox Repair Technician",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "ipad-battery-specialist",
    "name": "iPad Battery Specialist",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "ipad-screen-specialist",
    "name": "iPad Screen Specialist",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "iphone-battery-specialist",
    "name": "iPhone Battery Specialist",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "iphone-face-id-repairer",
    "name": "iPhone Face ID Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "iphone-motherboard-repairer",
    "name": "iPhone Motherboard Repairer",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "iphone-screen-specialist",
    "name": "iPhone Screen Specialist",
    "category": "Phone & Electronics Repair",
    "defaultRate": 75,
    "iconName": "Smartphone"
  },
  {
    "id": "architecture-photographer",
    "name": "Architecture Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "baby-photographer",
    "name": "Baby Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "beauty-retoucher",
    "name": "Beauty Retoucher",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "commercial-photographer",
    "name": "Commercial Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "construction-progress-photographer",
    "name": "Construction Progress Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "drone-aerial-photographer",
    "name": "Drone Aerial Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "event-documentary-photographer",
    "name": "Event Documentary Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "family-portrait-photographer",
    "name": "Family Portrait Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "fashion-photographer",
    "name": "Fashion Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "food-photographer",
    "name": "Food Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "headshot-photographer",
    "name": "Headshot Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "maternity-photographer",
    "name": "Maternity Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "newborn-photographer",
    "name": "Newborn Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "photo-booth-attendant",
    "name": "Photo Booth Attendant",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "photo-retoucher",
    "name": "Photo Retoucher",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "portrait-photographer",
    "name": "Portrait Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "product-photographer",
    "name": "Product Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "real-estate-photographer",
    "name": "Real Estate Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "runway-photographer",
    "name": "Runway Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "studio-photographer",
    "name": "Studio Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "traditional-engagement-photographer",
    "name": "Traditional Engagement Photographer",
    "category": "Photography & Imaging",
    "defaultRate": 95,
    "iconName": "Camera"
  },
  {
    "id": "bathroom-sanitary-ware-fitter",
    "name": "Bathroom Sanitary Ware Fitter",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "bathtub-installer",
    "name": "Bathtub Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "biodigester-installer",
    "name": "Biodigester Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "biofil-toilet-technician",
    "name": "Biofil Toilet Technician",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "commercial-plumber",
    "name": "Commercial Plumber",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "copper-pipe-specialist",
    "name": "Copper Pipe Specialist",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "deep-well-driller",
    "name": "Deep Well Driller",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "drain-unblocker",
    "name": "Drain Unblocker",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "drainage-specialist",
    "name": "Drainage Specialist",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "drip-irrigation-installer",
    "name": "Drip Irrigation Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "electric-water-heater-installer",
    "name": "Electric Water Heater Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "fire-hydrant-technician",
    "name": "Fire Hydrant Technician",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "fire-sprinkler-installer",
    "name": "Fire Sprinkler Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "galvanized-pipe-fitter",
    "name": "Galvanized Pipe Fitter",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "gas-water-heater-installer",
    "name": "Gas Water Heater Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "grease-trap-installer",
    "name": "Grease Trap Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "hdpe-butt-fusion-welder",
    "name": "HDPE Butt Fusion Welder",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "hand-pump-repairer",
    "name": "Hand Pump Repairer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "high-pressure-water-jetter",
    "name": "High Pressure Water Jetter",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "industrial-pipefitter",
    "name": "Industrial Pipefitter",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "irrigation-technician",
    "name": "Irrigation Technician",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "jacuzzi-installer",
    "name": "Jacuzzi Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "manhole-builder",
    "name": "Manhole Builder",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "overhead-water-tank-installer",
    "name": "Overhead Water Tank Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "ppr-pipe-welder",
    "name": "PPR Pipe Welder",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "pvc-pipe-fitter",
    "name": "PVC Pipe Fitter",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "pool-maintenance-worker",
    "name": "Pool Maintenance Worker",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "pool-pump-mechanic",
    "name": "Pool Pump Mechanic",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "pressure-booster-pump-technician",
    "name": "Pressure Booster Pump Technician",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "reverse-osmosis-installer",
    "name": "Reverse Osmosis Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "septic-tank-constructor",
    "name": "Septic Tank Constructor",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "sewer-line-technician",
    "name": "Sewer Line Technician",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "shower-cubicle-installer",
    "name": "Shower Cubicle Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "soakaway-builder",
    "name": "Soakaway Builder",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "solar-water-heater-installer",
    "name": "Solar Water Heater Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "sprinkler-system-installer",
    "name": "Sprinkler System Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "submersible-pump-installer",
    "name": "Submersible Pump Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "sump-pump-installer",
    "name": "Sump Pump Installer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "surface-water-pump-mechanic",
    "name": "Surface Water Pump Mechanic",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "swimming-pool-builder",
    "name": "Swimming Pool Builder",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "swimming-pool-technician",
    "name": "Swimming Pool Technician",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "tap-repairer",
    "name": "Tap Repairer",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "toilet-cistern-specialist",
    "name": "Toilet Cistern Specialist",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "uv-water-purifier-technician",
    "name": "UV Water Purifier Technician",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "water-filtration-technician",
    "name": "Water Filtration Technician",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "water-heater-technician",
    "name": "Water Heater Technician",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "water-leak-detector",
    "name": "Water Leak Detector",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "water-pipeline-pressure-tester",
    "name": "Water Pipeline Pressure Tester",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "water-storage-tank-cleaner",
    "name": "Water Storage Tank Cleaner",
    "category": "Plumbing & Water Systems",
    "defaultRate": 80,
    "iconName": "Wrench"
  },
  {
    "id": "3d-acrylic-letter-fabricator",
    "name": "3D Acrylic Letter Fabricator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "bookbinder",
    "name": "Bookbinder",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "car-wrap-installer",
    "name": "Car Wrap Installer",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "die-cutting-machine-operator",
    "name": "Die Cutting Machine Operator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "directional-road-sign-maker",
    "name": "Directional Road Sign Maker",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "flexi-banner-printer",
    "name": "Flexi Banner Printer",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "flexographic-press-operator",
    "name": "Flexographic Press Operator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "frosted-window-film-installer",
    "name": "Frosted Window Film Installer",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "hardcover-bookbinder",
    "name": "Hardcover Bookbinder",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "heat-press-operator",
    "name": "Heat Press Operator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "hot-foil-stamping-specialist",
    "name": "Hot Foil Stamping Specialist",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "id-card-printer",
    "name": "ID Card Printer",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "led-channel-letter-maker",
    "name": "LED Channel Letter Maker",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "lamination-machine-operator",
    "name": "Lamination Machine Operator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "lanyard-ribbon-printer",
    "name": "Lanyard Ribbon Printer",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "large-format-print-operator",
    "name": "Large Format Print Operator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "lightbox-signboard-fabricator",
    "name": "Lightbox Signboard Fabricator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "mug-printing-specialist",
    "name": "Mug Printing Specialist",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "neon-sign-artisan",
    "name": "Neon Sign Artisan",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "offset-printing-pressman",
    "name": "Offset Printing Pressman",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "one-way-vision-film-applicator",
    "name": "One-Way Vision Film Applicator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "pad-printing-operator",
    "name": "Pad Printing Operator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "paper-guillotine-operator",
    "name": "Paper Guillotine Operator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "pylon-billboard-fabricator",
    "name": "Pylon Billboard Fabricator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "reflective-sticker-applicator",
    "name": "Reflective Sticker Applicator",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "roll-up-banner-assembler",
    "name": "Roll-up Banner Assembler",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "souvenir-gift-printer",
    "name": "Souvenir Gift Printer",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "spiral-wire-binder",
    "name": "Spiral Wire Binder",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "sublimation-printer",
    "name": "Sublimation Printer",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "t-shirt-screen-printer",
    "name": "T-Shirt Screen Printer",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "vehicle-branding-specialist",
    "name": "Vehicle Branding Specialist",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "vinyl-sticker-printer",
    "name": "Vinyl Sticker Printer",
    "category": "Printing & Signage",
    "defaultRate": 75,
    "iconName": "Printer"
  },
  {
    "id": "boundary-pillar-marker",
    "name": "Boundary Pillar Marker",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "cadastral-site-plan-drafter",
    "name": "Cadastral Site Plan Drafter",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "commercial-property-agent",
    "name": "Commercial Property Agent",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "drone-land-mapping-operator",
    "name": "Drone Land Mapping Operator",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "estate-property-manager",
    "name": "Estate Property Manager",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "farmland-broker",
    "name": "Farmland Broker",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "house-rental-agent",
    "name": "House Rental Agent",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "land-broker",
    "name": "Land Broker",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "land-registry-search-assistant",
    "name": "Land Registry Search Assistant",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "office-space-broker",
    "name": "Office Space Broker",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "property-caretaker",
    "name": "Property Caretaker",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "real-estate-agent",
    "name": "Real Estate Agent",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "rent-collection-agent",
    "name": "Rent Collection Agent",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "rental-apartment-broker",
    "name": "Rental Apartment Broker",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "residential-plot-agent",
    "name": "Residential Plot Agent",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "tenant-screening-assistant",
    "name": "Tenant Screening Assistant",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "topographical-surveyor-assistant",
    "name": "Topographical Surveyor Assistant",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "warehouse-leasing-agent",
    "name": "Warehouse Leasing Agent",
    "category": "Real Estate & Property Agency",
    "defaultRate": 85,
    "iconName": "Home"
  },
  {
    "id": "aluminium-roofing-sheet-installer",
    "name": "Aluminium Roofing Sheet Installer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "aluminium-seamless-gutter-installer",
    "name": "Aluminium Seamless Gutter Installer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "asbestos-removal-technician",
    "name": "Asbestos Removal Technician",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "clay-tile-roofer",
    "name": "Clay Tile Roofer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "concrete-roof-sealer",
    "name": "Concrete Roof Sealer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "flashing-installer",
    "name": "Flashing Installer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "galvanized-gutter-fabricator",
    "name": "Galvanized Gutter Fabricator",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "industrial-roof-cladder",
    "name": "Industrial Roof Cladder",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "liquid-waterproofing-technician",
    "name": "Liquid Waterproofing Technician",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "membrane-waterproofing-specialist",
    "name": "Membrane Waterproofing Specialist",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "pvc-gutter-technician",
    "name": "PVC Gutter Technician",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "parapet-wall-waterproofer",
    "name": "Parapet Wall Waterproofer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "roof-gutter-cleaner",
    "name": "Roof Gutter Cleaner",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "roof-leak-specialist",
    "name": "Roof Leak Specialist",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "roof-painting-specialist",
    "name": "Roof Painting Specialist",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "roof-ventilation-specialist",
    "name": "Roof Ventilation Specialist",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "roofing-felt-installer",
    "name": "Roofing Felt Installer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "shingle-roofer",
    "name": "Shingle Roofer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "skylight-installer",
    "name": "Skylight Installer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "slate-roofer",
    "name": "Slate Roofer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "standing-seam-metal-roofer",
    "name": "Standing Seam Metal Roofer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "stone-coated-tile-roofer",
    "name": "Stone Coated Tile Roofer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "thermal-insulation-installer",
    "name": "Thermal Insulation Installer",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "valley-gutter-specialist",
    "name": "Valley Gutter Specialist",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "waterproofing-specialist",
    "name": "Waterproofing Specialist",
    "category": "Roofing & Gutters",
    "defaultRate": 85,
    "iconName": "HardHat"
  },
  {
    "id": "access-control-installer",
    "name": "Access Control Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "analog-camera-technician",
    "name": "Analog Camera Technician",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "automated-sliding-gate-motor-installer",
    "name": "Automated Sliding Gate Motor Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "automated-swing-gate-motor-installer",
    "name": "Automated Swing Gate Motor Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "barbed-wire-installer",
    "name": "Barbed Wire Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "biometric-time-attendance-technician",
    "name": "Biometric Time Attendance Technician",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "burglar-alarm-installer",
    "name": "Burglar Alarm Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "car-tracker-installer",
    "name": "Car Tracker Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "electric-gate-repairer",
    "name": "Electric Gate Repairer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "fire-alarm-installer",
    "name": "Fire Alarm Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "fire-extinguisher-servicing-technician",
    "name": "Fire Extinguisher Servicing Technician",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "fleet-management-device-installer",
    "name": "Fleet Management Device Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "gps-tracking-specialist",
    "name": "GPS Tracking Specialist",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "heat-detector-installer",
    "name": "Heat Detector Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "ip-camera-specialist",
    "name": "IP Camera Specialist",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "intercom-system-installer",
    "name": "Intercom System Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "metal-detector-archway-installer",
    "name": "Metal Detector Archway Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "motion-sensor-installer",
    "name": "Motion Sensor Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "perimeter-security-specialist",
    "name": "Perimeter Security Specialist",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "rfid-card-door-lock-installer",
    "name": "RFID Card Door Lock Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "razor-wire-installer",
    "name": "Razor Wire Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "security-camera-maintenance-worker",
    "name": "Security Camera Maintenance Worker",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "smart-door-lock-installer",
    "name": "Smart Door Lock Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "smart-home-automation-specialist",
    "name": "Smart Home Automation Specialist",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "smoke-detector-technician",
    "name": "Smoke Detector Technician",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "turnstile-gate-technician",
    "name": "Turnstile Gate Technician",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "vehicle-immobilizer-specialist",
    "name": "Vehicle Immobilizer Specialist",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "video-doorbell-installer",
    "name": "Video Doorbell Installer",
    "category": "Security & Smart Home",
    "defaultRate": 85,
    "iconName": "ShieldCheck"
  },
  {
    "id": "bank-security-guard",
    "name": "Bank Security Guard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "beach-lifeguard",
    "name": "Beach Lifeguard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "cctv-control-room-operator",
    "name": "CCTV Control Room Operator",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "cash-in-transit-armed-escort",
    "name": "Cash-in-Transit Armed Escort",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "central-alarm-dispatcher",
    "name": "Central Alarm Dispatcher",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "close-protection-officer",
    "name": "Close Protection Officer",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "compound-day-guard",
    "name": "Compound Day Guard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "corporate-security-officer",
    "name": "Corporate Security Officer",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "event-bouncer",
    "name": "Event Bouncer",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "executive-chauffeur-guard",
    "name": "Executive Chauffeur Guard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "fire-safety-warden",
    "name": "Fire Safety Warden",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "first-aid-attendant",
    "name": "First Aid Attendant",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "industrial-plant-guard",
    "name": "Industrial Plant Guard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "k9-patrol-handler",
    "name": "K9 Patrol Handler",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "mining-site-security-guard",
    "name": "Mining Site Security Guard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "mobile-patrol-guard",
    "name": "Mobile Patrol Guard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "night-watchman",
    "name": "Night Watchman",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "nightclub-doorman",
    "name": "Nightclub Doorman",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "occupational-safety-inspector",
    "name": "Occupational Safety Inspector",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "port-security-guard",
    "name": "Port Security Guard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "rapid-response-officer",
    "name": "Rapid Response Officer",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "residential-watchman",
    "name": "Residential Watchman",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "retail-mall-guard",
    "name": "Retail Mall Guard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "security-dog-handler",
    "name": "Security Dog Handler",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "swimming-pool-lifeguard",
    "name": "Swimming Pool Lifeguard",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "vip-access-controller",
    "name": "VIP Access Controller",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "workplace-safety-officer",
    "name": "Workplace Safety Officer",
    "category": "Security Guarding & Safety",
    "defaultRate": 65,
    "iconName": "ShieldCheck"
  },
  {
    "id": "android-kotlin-developer",
    "name": "Android Kotlin Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "angular-developer",
    "name": "Angular Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "automation-test-engineer",
    "name": "Automation Test Engineer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "backend-web-developer",
    "name": "Backend Web Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "django-developer",
    "name": "Django Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "fastapi-developer",
    "name": "FastAPI Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "flutter-developer",
    "name": "Flutter Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "frontend-web-developer",
    "name": "Frontend Web Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "graphql-developer",
    "name": "GraphQL Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "javascript-developer",
    "name": "JavaScript Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "laravel-developer",
    "name": "Laravel Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "nextjs-developer",
    "name": "Nextjs Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "nodejs-backend-developer",
    "name": "Nodejs Backend Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "php-developer",
    "name": "PHP Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "progressive-web-app-developer",
    "name": "Progressive Web App Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "python-developer",
    "name": "Python Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "rest-api-developer",
    "name": "REST API Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "react-developer",
    "name": "React Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "react-native-developer",
    "name": "React Native Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "shopify-store-developer",
    "name": "Shopify Store Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "software-qa-tester",
    "name": "Software QA Tester",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "typescript-developer",
    "name": "TypeScript Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "vue-developer",
    "name": "Vue Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "webflow-designer",
    "name": "Webflow Designer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "wix-website-builder",
    "name": "Wix Website Builder",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "woocommerce-specialist",
    "name": "WooCommerce Specialist",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "ios-swift-developer",
    "name": "iOS Swift Developer",
    "category": "Software & Web Development",
    "defaultRate": 120,
    "iconName": "Laptop"
  },
  {
    "id": "acne-treatment-specialist",
    "name": "Acne Treatment Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "aromatherapy-massage-therapist",
    "name": "Aromatherapy Massage Therapist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "bikini-waxing-technician",
    "name": "Bikini Waxing Technician",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "blackhead-extraction-specialist",
    "name": "Blackhead Extraction Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "body-polish-therapist",
    "name": "Body Polish Therapist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "body-scrub-specialist",
    "name": "Body Scrub Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "body-wrap-specialist",
    "name": "Body Wrap Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "brazilian-waxing-specialist",
    "name": "Brazilian Waxing Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "chemical-peel-specialist",
    "name": "Chemical Peel Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "chiropractic-assistant",
    "name": "Chiropractic Assistant",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "couples-massage-therapist",
    "name": "Couples Massage Therapist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "deep-tissue-massage-therapist",
    "name": "Deep Tissue Massage Therapist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "dermaplaning-technician",
    "name": "Dermaplaning Technician",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "facialist",
    "name": "Facialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "full-body-waxing-specialist",
    "name": "Full Body Waxing Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "hot-stone-massage-therapist",
    "name": "Hot Stone Massage Therapist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "hydrafacial-technician",
    "name": "Hydrafacial Technician",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "laser-hair-removal-technician",
    "name": "Laser Hair Removal Technician",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "licensed-skin-care-specialist",
    "name": "Licensed Skin Care Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "microneedling-specialist",
    "name": "Microneedling Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "physical-therapy-assistant",
    "name": "Physical Therapy Assistant",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "postnatal-massage-therapist",
    "name": "Postnatal Massage Therapist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "prenatal-massage-therapist",
    "name": "Prenatal Massage Therapist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "sports-massage-therapist",
    "name": "Sports Massage Therapist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "sugaring-hair-removal-specialist",
    "name": "Sugaring Hair Removal Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "swedish-massage-therapist",
    "name": "Swedish Massage Therapist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "waxing-specialist",
    "name": "Waxing Specialist",
    "category": "Spa, Massage & Skincare",
    "defaultRate": 85,
    "iconName": "Sparkles"
  },
  {
    "id": "academic-gown-tailor",
    "name": "Academic Gown Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "agbada-tailor",
    "name": "Agbada Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "apron-maker",
    "name": "Apron Maker",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "chef-uniform-maker",
    "name": "Chef Uniform Maker",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "clerical-robe-tailor",
    "name": "Clerical Robe Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "corporate-uniform-maker",
    "name": "Corporate Uniform Maker",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "fugu-embroidery-artisan",
    "name": "Fugu Embroidery Artisan",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "industrial-coverall-maker",
    "name": "Industrial Coverall Maker",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "kaftan-tailor",
    "name": "Kaftan Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "medical-scrub-tailor",
    "name": "Medical Scrub Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "mens-bespoke-tailor",
    "name": "Mens Bespoke Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "mens-native-shirt-maker",
    "name": "Mens Native Shirt Maker",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "mens-shirtmaker",
    "name": "Mens Shirtmaker",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "military-uniform-tailor",
    "name": "Military Uniform Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "safari-suit-maker",
    "name": "Safari Suit Maker",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "school-uniform-tailor",
    "name": "School Uniform Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "security-guard-uniform-tailor",
    "name": "Security Guard Uniform Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "senator-wear-tailor",
    "name": "Senator Wear Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "traditional-smock-tailor",
    "name": "Traditional Smock Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "trousers-specialist",
    "name": "Trousers Specialist",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "tuxedo-specialist",
    "name": "Tuxedo Specialist",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "wedding-suit-tailor",
    "name": "Wedding Suit Tailor",
    "category": "Tailoring & Menswear",
    "defaultRate": 80,
    "iconName": "Scissors"
  },
  {
    "id": "canal-plus-installer",
    "name": "Canal Plus Installer",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "cellular-antenna-rigger",
    "name": "Cellular Antenna Rigger",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "enterprise-router-configurator",
    "name": "Enterprise Router Configurator",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "fiber-optic-cable-puller",
    "name": "Fiber Optic Cable Puller",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "fiber-optic-otdr-tester",
    "name": "Fiber Optic OTDR Tester",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "fiber-optic-splicer",
    "name": "Fiber Optic Splicer",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "free-to-air-satellite-installer",
    "name": "Free-to-Air Satellite Installer",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "gsm-base-station-technician",
    "name": "GSM Base Station Technician",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "home-theater-system-installer",
    "name": "Home Theater System Installer",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "ip-pbx-voip-technician",
    "name": "IP-PBX VoIP Technician",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "lan-network-engineer",
    "name": "LAN Network Engineer",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "managed-switch-technician",
    "name": "Managed Switch Technician",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "multichoice-accredited-technician",
    "name": "MultiChoice Accredited Technician",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "pbx-intercom-technician",
    "name": "PBX Intercom Technician",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "patch-panel-wireman",
    "name": "Patch Panel Wireman",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "public-address-sound-technician",
    "name": "Public Address Sound Technician",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "satellite-dish-rigger",
    "name": "Satellite Dish Rigger",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "server-rack-cabinet-installer",
    "name": "Server Rack Cabinet Installer",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "structured-cabling-technician",
    "name": "Structured Cabling Technician",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "surround-sound-calibrator",
    "name": "Surround Sound Calibrator",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "tv-wall-mounting-specialist",
    "name": "TV Wall Mounting Specialist",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "telecom-mast-rigger",
    "name": "Telecom Mast Rigger",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "telecom-tower-maintenance-worker",
    "name": "Telecom Tower Maintenance Worker",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "two-way-radio-technician",
    "name": "Two-Way Radio Technician",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "vsat-installer",
    "name": "VSAT Installer",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "wan-network-specialist",
    "name": "WAN Network Specialist",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "walkie-talkie-technician",
    "name": "Walkie-Talkie Technician",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "wifi-access-point-installer",
    "name": "WiFi Access Point Installer",
    "category": "Telecom & Networks",
    "defaultRate": 75,
    "iconName": "Tv"
  },
  {
    "id": "batik-fabric-designer",
    "name": "Batik Fabric Designer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "beads-applique-artisan",
    "name": "Beads Applique Artisan",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "bonwire-kente-artisan",
    "name": "Bonwire Kente Artisan",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "broadloom-weaver",
    "name": "Broadloom Weaver",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "buttonhole-machine-operator",
    "name": "Buttonhole Machine Operator",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "computerized-embroidery-digitizer",
    "name": "Computerized Embroidery Digitizer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "dtf-t-shirt-printer",
    "name": "DTF T-Shirt Printer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "daboya-fugu-weaver",
    "name": "Daboya Fugu Weaver",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "embroidery-machine-operator",
    "name": "Embroidery Machine Operator",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "fabric-cutter",
    "name": "Fabric Cutter",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "fabric-pleating-specialist",
    "name": "Fabric Pleating Specialist",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "fabric-screen-printer",
    "name": "Fabric Screen Printer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "garment-alterations-specialist",
    "name": "Garment Alterations Specialist",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "gold-thread-embroiderer",
    "name": "Gold Thread Embroiderer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "hand-embroiderer",
    "name": "Hand Embroiderer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "heat-transfer-fabric-printer",
    "name": "Heat Transfer Fabric Printer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "invisible-mending-artisan",
    "name": "Invisible Mending Artisan",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "kente-dyer",
    "name": "Kente Dyer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "northern-smock-weaver",
    "name": "Northern Smock Weaver",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "rhinestone-fixing-specialist",
    "name": "Rhinestone Fixing Specialist",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "rotary-screen-printer",
    "name": "Rotary Screen Printer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "screen-printer",
    "name": "Screen Printer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "sublimation-fabric-printer",
    "name": "Sublimation Fabric Printer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "tie-and-dye-textile-artisan",
    "name": "Tie and Dye Textile Artisan",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "traditional-yarn-spinner",
    "name": "Traditional Yarn Spinner",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "wax-print-designer",
    "name": "Wax Print Designer",
    "category": "Textiles, Weaving & Printing",
    "defaultRate": 75,
    "iconName": "Palette"
  },
  {
    "id": "2d-motion-graphics-artist",
    "name": "2D Motion Graphics Artist",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "3d-animator",
    "name": "3D Animator",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "corporate-video-producer",
    "name": "Corporate Video Producer",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "crane-cameraman",
    "name": "Crane Cameraman",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "davinci-resolve-colorist",
    "name": "DaVinci Resolve Colorist",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "documentary-filmmaker",
    "name": "Documentary Filmmaker",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "event-cameraman",
    "name": "Event Cameraman",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "fpv-drone-operator",
    "name": "FPV Drone Operator",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "final-cut-pro-editor",
    "name": "Final Cut Pro Editor",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "gimbal-operator",
    "name": "Gimbal Operator",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "live-stream-broadcast-technician",
    "name": "Live Stream Broadcast Technician",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "multi-camera-setup-operator",
    "name": "Multi-Camera Setup Operator",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "music-video-director",
    "name": "Music Video Director",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "premiere-pro-video-editor",
    "name": "Premiere Pro Video Editor",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "short-form-video-editor",
    "name": "Short Form Video Editor",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "subtitle-creator",
    "name": "Subtitle Creator",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "traditional-marriage-videographer",
    "name": "Traditional Marriage Videographer",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "visual-effects-artist",
    "name": "Visual Effects Artist",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  },
  {
    "id": "youtube-video-editor",
    "name": "YouTube Video Editor",
    "category": "Videography & Cinema",
    "defaultRate": 105,
    "iconName": "Video"
  }
];
