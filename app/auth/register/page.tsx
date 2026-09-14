'use client';

import React, { useState, useEffect, Suspense, useMemo, useRef } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { AuthLayout } from '@/components/auth/AuthLayout';
import { PhoneInput, TelecomNetwork } from '@/components/ui/phone-input';
import { GhanaCardInput } from '@/components/ui/ghana-card-input';
import { useAuth } from '@/lib/context/AuthContext';
import confetti from 'canvas-confetti';
import {
  Check,
  ArrowRight,
  ArrowLeft,
  Lock,
  Eye,
  EyeOff,
  ShieldCheck,
  Smartphone,
  CheckCircle2,
  BadgeCheck,
  Star,
  MapPin,
  Search,
  X,
  FileText,
  AlertCircle,
} from 'lucide-react';
import { GHANA_TRADES, GhanaTradeOption } from '@/lib/ghanaTrades';
export type { GhanaTradeOption };



export interface GhanaLocation {
  city: string;
  region: string;
  full: string;
  popular?: boolean;
}

export const POPULAR_GHANA_LOCATIONS: GhanaLocation[] = [
  // ══════════════════════════════════════════════════════════════
  // AHAFO REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Duayaw Nkwanta', region: 'Tano North, Ahafo Region', full: 'Duayaw Nkwanta, Ahafo', popular: true },
  { city: 'Bechem', region: 'Tano South, Ahafo Region', full: 'Bechem, Ahafo', popular: true },
  { city: 'Goaso', region: 'Asunafo North, Ahafo Region', full: 'Goaso, Ahafo', popular: true },
  { city: 'Mim', region: 'Asunafo North, Ahafo Region', full: 'Mim, Ahafo', popular: true },
  { city: 'Kenyasi', region: 'Asutifi North, Ahafo Region', full: 'Kenyasi, Ahafo', popular: true },
  { city: 'Hwidiem', region: 'Asutifi South, Ahafo Region', full: 'Hwidiem, Ahafo' },
  { city: 'Kukuom', region: 'Asunafo South, Ahafo Region', full: 'Kukuom, Ahafo' },
  { city: 'Yamfo', region: 'Tano North, Ahafo Region', full: 'Yamfo, Ahafo' },
  { city: 'Tanoso (Ahafo)', region: 'Tano North, Ahafo Region', full: 'Tanoso, Ahafo' },
  { city: 'Akrodie', region: 'Asunafo North, Ahafo Region', full: 'Akrodie, Ahafo' },
  { city: 'Acherensua', region: 'Asutifi South, Ahafo Region', full: 'Acherensua, Ahafo' },
  { city: 'Bomaa', region: 'Tano North, Ahafo Region', full: 'Bomaa, Ahafo' },
  { city: 'Techimantia', region: 'Tano South, Ahafo Region', full: 'Techimantia, Ahafo' },

  // ══════════════════════════════════════════════════════════════
  // GREATER ACCRA REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'East Legon', region: 'Accra, Greater Accra', full: 'East Legon, Accra', popular: true },
  { city: 'Spintex Road', region: 'Accra, Greater Accra', full: 'Spintex Road, Accra', popular: true },
  { city: 'Airport Hills & Residential', region: 'Accra, Greater Accra', full: 'Airport Hills, Accra', popular: true },
  { city: 'Osu (Oxford Street / RE)', region: 'Accra, Greater Accra', full: 'Osu, Accra', popular: true },
  { city: 'Cantonments & Labone', region: 'Accra, Greater Accra', full: 'Cantonments, Accra', popular: true },
  { city: 'Dzorwulu & Roman Ridge', region: 'Accra, Greater Accra', full: 'Dzorwulu, Accra', popular: true },
  { city: 'Tema (Communities 1 - 25)', region: 'Tema, Greater Accra', full: 'Tema, Greater Accra', popular: true },
  { city: 'Madina & Ashaley Botwe', region: 'La-Nkwantanang, Greater Accra', full: 'Madina, Greater Accra', popular: true },
  { city: 'Adenta & Frafraha', region: 'Adentan, Greater Accra', full: 'Adenta, Greater Accra', popular: true },
  { city: 'Lapaz & Abeka', region: 'Okaikwei North, Greater Accra', full: 'Lapaz, Accra', popular: true },
  { city: 'Dansoman & Exhibition', region: 'Ablekuma West, Greater Accra', full: 'Dansoman, Accra', popular: true },
  { city: 'Achimota & Mile 7', region: 'Okaikwei North, Greater Accra', full: 'Achimota, Accra', popular: true },
  { city: 'Dome & Kwabenya', region: 'Ga East, Greater Accra', full: 'Dome, Greater Accra', popular: true },
  { city: 'Haatso & Agbogba', region: 'Ga East, Greater Accra', full: 'Haatso, Greater Accra' },
  { city: 'Kwashieman & Santa Maria', region: 'Ablekuma Central, Greater Accra', full: 'Kwashieman, Accra' },
  { city: 'Kwame Nkrumah Circle & Adabraka', region: 'Klottey-Korle, Greater Accra', full: 'Circle, Accra' },
  { city: 'Kaneshie & Odorkor', region: 'Okaikwei South, Greater Accra', full: 'Kaneshie, Accra' },
  { city: 'Weija & SCC', region: 'Ga South, Greater Accra', full: 'Weija, Greater Accra' },
  { city: 'Gbawe & Mallam', region: 'Ga South, Greater Accra', full: 'Gbawe, Greater Accra' },
  { city: 'Bortianor & Kokrobite', region: 'Ga South, Greater Accra', full: 'Kokrobite, Greater Accra' },
  { city: 'Teshie & Nungua Estates', region: 'Ledzokuku-Krowor, Greater Accra', full: 'Teshie, Greater Accra' },
  { city: 'Sakumono & Lashibi', region: 'Tema West, Greater Accra', full: 'Sakumono, Greater Accra' },
  { city: 'Prampram & Dawhenya', region: 'Ningo-Prampram, Greater Accra', full: 'Prampram, Greater Accra' },
  { city: 'Afienya & Shai Hills', region: 'Shai-Osudoku, Greater Accra', full: 'Afienya, Greater Accra' },
  { city: 'Dodowa', region: 'Shai-Osudoku, Greater Accra', full: 'Dodowa, Greater Accra' },
  { city: 'Amasaman & Pokuase', region: 'Ga West, Greater Accra', full: 'Pokuase, Greater Accra' },
  { city: 'Medie & Kotoku', region: 'Ga West, Greater Accra', full: 'Medie, Greater Accra' },
  { city: 'Oyarifa & Danfa', region: 'La-Nkwantanang, Greater Accra', full: 'Oyarifa, Greater Accra' },
  { city: 'Abokobi & Pantang', region: 'Ga East, Greater Accra', full: 'Abokobi, Greater Accra' },
  { city: 'Abelemkpe', region: 'Ayawaso West, Greater Accra', full: 'Abelemkpe, Accra' },
  { city: 'West Legon & North Legon', region: 'Ayawaso West, Greater Accra', full: 'West Legon, Accra' },
  { city: 'Legon Campus (UG)', region: 'Ayawaso West, Greater Accra', full: 'Legon, Accra' },
  { city: 'Tesano & Alajo', region: 'Ayawaso Central, Greater Accra', full: 'Tesano, Accra' },
  { city: 'Accra New Town & Kotobabi', region: 'Ayawaso Central, Greater Accra', full: 'Newtown, Accra' },
  { city: 'Nima & Maamobi', region: 'Ayawaso East, Greater Accra', full: 'Nima, Accra' },
  { city: 'Kanda & Ridge', region: 'Ayawaso East, Greater Accra', full: 'Kanda, Accra' },
  { city: 'Asylum Down', region: 'Klottey-Korle, Greater Accra', full: 'Asylum Down, Accra' },
  { city: 'Jamestown & Chorkor', region: 'Ashiedu Keteke, Greater Accra', full: 'Jamestown, Accra' },
  { city: 'Korle Bu & Korle Gonno', region: 'Ablekuma South, Greater Accra', full: 'Korle Bu, Accra' },
  { city: 'Mamprobi', region: 'Ablekuma South, Greater Accra', full: 'Mamprobi, Accra' },
  { city: 'Darkuman & Awoshie', region: 'Ablekuma North, Greater Accra', full: 'Darkuman, Accra' },
  { city: 'Anyaa & Ablekuma', region: 'Anyaa-Sowutuom, Greater Accra', full: 'Anyaa, Greater Accra' },
  { city: 'Sowutuom & Tabora', region: 'Anyaa-Sowutuom, Greater Accra', full: 'Sowutuom, Greater Accra' },
  { city: 'McCarthy Hill & Tetegu', region: 'Ga South, Greater Accra', full: 'McCarthy Hill, Greater Accra' },
  { city: 'Kpone & Tema Manhean', region: 'Kpone-Katamanso, Greater Accra', full: 'Kpone, Greater Accra' },
  { city: 'Old Ningo & New Ningo', region: 'Ningo-Prampram, Greater Accra', full: 'Ningo, Greater Accra' },
  { city: 'Ada Foah & Big Ada', region: 'Ada East, Greater Accra', full: 'Ada Foah, Greater Accra' },
  { city: 'Sege', region: 'Ada West, Greater Accra', full: 'Sege, Greater Accra' },
  { city: 'Kasoa Amanfro', region: 'Ga South / Central Border', full: 'Amanfro, Greater Accra' },

  // ══════════════════════════════════════════════════════════════
  // ASHANTI REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Kumasi Central (Adum)', region: 'Kumasi, Ashanti', full: 'Kumasi Central, Ashanti', popular: true },
  { city: 'Kejetia', region: 'Kumasi, Ashanti', full: 'Kejetia, Kumasi', popular: true },
  { city: 'Bantama & Abrepo', region: 'Kumasi, Ashanti', full: 'Bantama, Kumasi', popular: true },
  { city: 'Ahodwo & Nhyiaeso', region: 'Kumasi, Ashanti', full: 'Ahodwo, Kumasi', popular: true },
  { city: 'KNUST Campus & Ayigya', region: 'Oforikrom, Ashanti', full: 'KNUST, Kumasi', popular: true },
  { city: 'Suame (Magazine) & Tafo', region: 'Suame, Ashanti', full: 'Suame, Kumasi', popular: true },
  { city: 'Asokwa & Atonsu', region: 'Asokwa, Ashanti', full: 'Asokwa, Kumasi' },
  { city: 'Kwadaso & Sofoline', region: 'Kwadaso, Ashanti', full: 'Kwadaso, Kumasi' },
  { city: 'Oforikrom & Anloga', region: 'Oforikrom, Ashanti', full: 'Oforikrom, Kumasi' },
  { city: 'Tanoso & Abuakwa', region: 'Atwima Nwabiagya, Ashanti', full: 'Tanoso, Kumasi' },
  { city: 'Obuasi (Gold City)', region: 'Obuasi Municipal, Ashanti', full: 'Obuasi, Ashanti', popular: true },
  { city: 'Ejisu & Fumesua', region: 'Ejisu Municipal, Ashanti', full: 'Ejisu, Ashanti', popular: true },
  { city: 'Asante Bekwai', region: 'Bekwai Municipal, Ashanti', full: 'Bekwai, Ashanti', popular: true },
  { city: 'Mampong', region: 'Mampong Municipal, Ashanti', full: 'Mampong, Ashanti', popular: true },
  { city: 'Konongo & Odumase', region: 'Asante Akim Central, Ashanti', full: 'Konongo, Ashanti', popular: true },
  { city: 'Agogo', region: 'Asante Akim North, Ashanti', full: 'Agogo, Ashanti' },
  { city: 'Juaso', region: 'Asante Akim South, Ashanti', full: 'Juaso, Ashanti' },
  { city: 'Effiduase & Asokore', region: 'Sekyere East, Ashanti', full: 'Effiduase, Ashanti' },
  { city: 'Kumawu', region: 'Sekyere Kumawu, Ashanti', full: 'Kumawu, Ashanti' },
  { city: 'Juaben', region: 'Juaben Municipal, Ashanti', full: 'Juaben, Ashanti' },
  { city: 'Offinso', region: 'Offinso Municipal, Ashanti', full: 'Offinso, Ashanti' },
  { city: 'Akomadan & Abofour', region: 'Offinso North, Ashanti', full: 'Akomadan, Ashanti' },
  { city: 'Tepa', region: 'Ahafo Ano North, Ashanti', full: 'Tepa, Ashanti' },
  { city: 'Mankranso', region: 'Ahafo Ano South, Ashanti', full: 'Mankranso, Ashanti' },
  { city: 'Nkawie & Nyinahin', region: 'Atwima Mponua, Ashanti', full: 'Nkawie, Ashanti' },
  { city: 'Jacobu', region: 'Amansie Central, Ashanti', full: 'Jacobu, Ashanti' },
  { city: 'Manso Nkwanta', region: 'Amansie West, Ashanti', full: 'Manso Nkwanta, Ashanti' },
  { city: 'New Edubiase', region: 'Adansi South, Ashanti', full: 'New Edubiase, Ashanti' },
  { city: 'Fomena', region: 'Adansi North, Ashanti', full: 'Fomena, Ashanti' },
  { city: 'Akrokerri', region: 'Adansi North, Ashanti', full: 'Akrokerri, Ashanti' },
  { city: 'Kodie', region: 'Afigya Kwabre, Ashanti', full: 'Kodie, Ashanti' },
  { city: 'Agona (Ashanti)', region: 'Sekyere South, Ashanti', full: 'Agona, Ashanti' },
  { city: 'Nsuta & Jamasi', region: 'Sekyere Central, Ashanti', full: 'Nsuta, Ashanti' },
  { city: 'Drobonso', region: 'Sekyere Afram Plains, Ashanti', full: 'Drobonso, Ashanti' },
  { city: 'Kuntenase', region: 'Bosomtwe, Ashanti', full: 'Kuntenase, Ashanti' },
  { city: 'Kokofu', region: 'Bekwai Municipal, Ashanti', full: 'Kokofu, Ashanti' },
  { city: 'Barekese', region: 'Atwima Nwabiagya, Ashanti', full: 'Barekese, Ashanti' },
  { city: 'Pankrono & Kronum', region: 'Old Tafo, Ashanti', full: 'Pankrono, Kumasi' },
  { city: 'Breman & Ashtown', region: 'Manhyia, Ashanti', full: 'Breman, Kumasi' },
  { city: 'Dichemso & Manhyia', region: 'Manhyia, Ashanti', full: 'Manhyia, Kumasi' },
  { city: 'Bomso & Kentinkrono', region: 'Oforikrom, Ashanti', full: 'Bomso, Kumasi' },
  { city: 'Emena & Boadi', region: 'Oforikrom, Ashanti', full: 'Emena, Kumasi' },
  { city: 'Appiadu & Kotei', region: 'Oforikrom, Ashanti', full: 'Kotei, Kumasi' },
  { city: 'Kaase & Ahinsan', region: 'Asokwa, Ashanti', full: 'Kaase, Kumasi' },
  { city: 'Chirapatre & Gyinyase', region: 'Asokwa, Ashanti', full: 'Chirapatre, Kumasi' },
  { city: 'Santasi', region: 'Kwadaso, Ashanti', full: 'Santasi, Kumasi' },

  // ══════════════════════════════════════════════════════════════
  // BONO REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Sunyani Central', region: 'Sunyani Municipal, Bono Region', full: 'Sunyani, Bono', popular: true },
  { city: 'Fiapre & Abesim', region: 'Sunyani Municipal, Bono Region', full: 'Fiapre, Bono', popular: true },
  { city: 'Odumase (Sunyani West)', region: 'Sunyani West, Bono Region', full: 'Odumase, Bono' },
  { city: 'Berekum', region: 'Berekum East, Bono Region', full: 'Berekum, Bono', popular: true },
  { city: 'Jinijini', region: 'Berekum West, Bono Region', full: 'Jinijini, Bono' },
  { city: 'Dormaa Ahenkro', region: 'Dormaa Central, Bono Region', full: 'Dormaa Ahenkro, Bono', popular: true },
  { city: 'Wamfie', region: 'Dormaa East, Bono Region', full: 'Wamfie, Bono' },
  { city: 'Drobo & Babianiha', region: 'Jaman South, Bono Region', full: 'Drobo, Bono' },
  { city: 'Sampa', region: 'Jaman North, Bono Region', full: 'Sampa, Bono', popular: true },
  { city: 'Seikwa & Nsawkaw', region: 'Tain, Bono Region', full: 'Nsawkaw, Bono' },
  { city: 'Banda Ahenkro', region: 'Banda, Bono Region', full: 'Banda Ahenkro, Bono' },
  { city: 'Chiraa & Nsuatre', region: 'Sunyani West, Bono Region', full: 'Chiraa, Bono' },

  // ══════════════════════════════════════════════════════════════
  // BONO EAST REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Techiman (Commercial Hub)', region: 'Techiman Municipal, Bono East', full: 'Techiman, Bono East', popular: true },
  { city: 'Tuobodom', region: 'Techiman North, Bono East', full: 'Tuobodom, Bono East' },
  { city: 'Kintampo (Waterfalls City)', region: 'Kintampo North, Bono East', full: 'Kintampo, Bono East', popular: true },
  { city: 'Jema', region: 'Kintampo South, Bono East', full: 'Jema, Bono East' },
  { city: 'Nkoranza', region: 'Nkoranza South, Bono East', full: 'Nkoranza, Bono East', popular: true },
  { city: 'Busunya', region: 'Nkoranza North, Bono East', full: 'Busunya, Bono East' },
  { city: 'Atebubu', region: 'Atebubu-Amantin, Bono East', full: 'Atebubu, Bono East', popular: true },
  { city: 'Amantin', region: 'Atebubu-Amantin, Bono East', full: 'Amantin, Bono East' },
  { city: 'Yeji (Volta Lake Port)', region: 'Pru East, Bono East', full: 'Yeji, Bono East', popular: true },
  { city: 'Prang', region: 'Pru West, Bono East', full: 'Prang, Bono East' },
  { city: 'Kwame Danso', region: 'Sene West, Bono East', full: 'Kwame Danso, Bono East' },
  { city: 'Kajaji', region: 'Sene East, Bono East', full: 'Kajaji, Bono East' },

  // ══════════════════════════════════════════════════════════════
  // CENTRAL REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Cape Coast Central', region: 'Cape Coast Metropolitan, Central', full: 'Cape Coast, Central', popular: true },
  { city: 'University of Cape Coast (UCC)', region: 'Cape Coast, Central', full: 'UCC, Cape Coast', popular: true },
  { city: 'Kotokuraba & Pedu', region: 'Cape Coast, Central', full: 'Kotokuraba, Cape Coast' },
  { city: 'Elmina & Komenda', region: 'Komenda-Edina-Eguafo-Abirem, Central', full: 'Elmina, Central', popular: true },
  { city: 'Kasoa (Galilea / CP)', region: 'Awutu Senya East, Central', full: 'Kasoa, Central', popular: true },
  { city: 'Budumburam & Nyanyano', region: 'Gomoa East, Central', full: 'Budumburam, Central' },
  { city: 'Winneba (University Town)', region: 'Effutu Municipal, Central', full: 'Winneba, Central', popular: true },
  { city: 'Apam', region: 'Gomoa West, Central', full: 'Apam, Central' },
  { city: 'Mankessim (Trade Hub)', region: 'Mfantseman, Central', full: 'Mankessim, Central', popular: true },
  { city: 'Saltpond & Anomabo', region: 'Mfantseman, Central', full: 'Saltpond, Central' },
  { city: 'Agona Swedru', region: 'Agona West, Central', full: 'Swedru, Central', popular: true },
  { city: 'Agona Nsaba', region: 'Agona East, Central', full: 'Agona Nsaba, Central' },
  { city: 'Breman Asikuma', region: 'Asikuma-Odoben-Brakwa, Central', full: 'Breman Asikuma, Central' },
  { city: 'Ajumako', region: 'Ajumako-Enyan-Essiam, Central', full: 'Ajumako, Central' },
  { city: 'Assin Foso', region: 'Assin Central, Central', full: 'Assin Foso, Central', popular: true },
  { city: 'Assin Manso & Bereku', region: 'Assin North, Central', full: 'Assin Manso, Central' },
  { city: 'Twifo Praso & Heman', region: 'Twifo Atti-Morkwa, Central', full: 'Twifo Praso, Central' },
  { city: 'Dunkwa-on-Offin', region: 'Upper Denkyira East, Central', full: 'Dunkwa-on-Offin, Central', popular: true },
  { city: 'Diaso', region: 'Upper Denkyira West, Central', full: 'Diaso, Central' },
  { city: 'Bawjiase', region: 'Awutu Senya West, Central', full: 'Bawjiase, Central' },
  { city: 'Senya Beraku & Gomoa Fetteh', region: 'Awutu Senya, Central', full: 'Senya Beraku, Central' },

  // ══════════════════════════════════════════════════════════════
  // EASTERN REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Koforidua (New Juaben)', region: 'New Juaben, Eastern Region', full: 'Koforidua, Eastern', popular: true },
  { city: 'Effiduase & Asokore (Koforidua)', region: 'New Juaben North, Eastern Region', full: 'Effiduase, Koforidua' },
  { city: 'Nsawam & Adoagyiri', region: 'Nsawam Adoagyiri, Eastern Region', full: 'Nsawam, Eastern', popular: true },
  { city: 'Suhum', region: 'Suhum Municipal, Eastern Region', full: 'Suhum, Eastern', popular: true },
  { city: 'Asamankese', region: 'West Akim, Eastern Region', full: 'Asamankese, Eastern', popular: true },
  { city: 'Akwatia (Diamond Town)', region: 'Denkyembour, Eastern Region', full: 'Akwatia, Eastern', popular: true },
  { city: 'Kade', region: 'Kwaebibirem, Eastern Region', full: 'Kade, Eastern' },
  { city: 'Akim Oda (Akyem Oda)', region: 'Birim Central, Eastern Region', full: 'Akim Oda, Eastern', popular: true },
  { city: 'Akim Swedru & Achiase', region: 'Birim South, Eastern Region', full: 'Akim Swedru, Eastern' },
  { city: 'Nkawkaw', region: 'Kwahu West, Eastern Region', full: 'Nkawkaw, Eastern', popular: true },
  { city: 'Mpraeso & Abetifi', region: 'Kwahu East / South, Eastern Region', full: 'Mpraeso, Eastern' },
  { city: 'Kwahu Tafo & Nkwatia', region: 'Kwahu East, Eastern Region', full: 'Kwahu Tafo, Eastern' },
  { city: 'Donkorkrom & Tease', region: 'Kwahu Afram Plains, Eastern Region', full: 'Donkorkrom, Eastern' },
  { city: 'Kyebi (Kibi)', region: 'Abuakwa South, Eastern Region', full: 'Kyebi, Eastern', popular: true },
  { city: 'Akyem Tafo (New Tafo)', region: 'Abuakwa North, Eastern Region', full: 'New Tafo, Eastern' },
  { city: 'Anyinam & Bunso', region: 'Atiwa East, Eastern Region', full: 'Anyinam, Eastern' },
  { city: 'Begoro', region: 'Fanteakwa North, Eastern Region', full: 'Begoro, Eastern' },
  { city: 'Osino', region: 'Fanteakwa South, Eastern Region', full: 'Osino, Eastern' },
  { city: 'Somanya', region: 'Yilo Krobo, Eastern Region', full: 'Somanya, Eastern', popular: true },
  { city: 'Odumase Krobo', region: 'Lower Manya Krobo, Eastern Region', full: 'Odumase Krobo, Eastern', popular: true },
  { city: 'Kpong & Akuse', region: 'Lower Manya Krobo, Eastern Region', full: 'Kpong, Eastern' },
  { city: 'Akosombo & Atimpoku', region: 'Asuogyaman, Eastern Region', full: 'Akosombo, Eastern', popular: true },
  { city: 'Anum & Boso', region: 'Asuogyaman, Eastern Region', full: 'Anum, Eastern' },
  { city: 'Asesewa', region: 'Upper Manya Krobo, Eastern Region', full: 'Asesewa, Eastern' },
  { city: 'Aburi & Peduase', region: 'Akuapem South, Eastern Region', full: 'Aburi, Eastern', popular: true },
  { city: 'Mampong & Tutu (Akuapem)', region: 'Akuapem North, Eastern Region', full: 'Mampong, Eastern' },
  { city: 'Adukrom & Larteh', region: 'Okere, Eastern Region', full: 'Adukrom, Eastern' },
  { city: 'Adeiso & Coaltar', region: 'Upper West Akim, Eastern Region', full: 'Adeiso, Eastern' },

  // ══════════════════════════════════════════════════════════════
  // WESTERN REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Takoradi (Market Circle)', region: 'Sekondi-Takoradi Metropolitan, Western', full: 'Takoradi, Western', popular: true },
  { city: 'Sekondi & Essikado', region: 'Sekondi-Takoradi, Western', full: 'Sekondi, Western', popular: true },
  { city: 'Anaji & Effia Kuma', region: 'Sekondi-Takoradi, Western', full: 'Anaji, Takoradi', popular: true },
  { city: 'Kwesimintsim & Fijai', region: 'Effia-Kwesimintsim, Western', full: 'Kwesimintsim, Takoradi' },
  { city: 'Kojokrom', region: 'Sekondi-Takoradi, Western', full: 'Kojokrom, Western' },
  { city: 'Tarkwa (Gold City)', region: 'Tarkwa-Nsuaem, Western', full: 'Tarkwa, Western', popular: true },
  { city: 'Aboso & Nsuaem', region: 'Tarkwa-Nsuaem, Western', full: 'Aboso, Western' },
  { city: 'Bogoso & Prestea', region: 'Prestea-Huni Valley, Western', full: 'Bogoso, Western', popular: true },
  { city: 'Asankrangwa', region: 'Amenfi West, Western', full: 'Asankrangwa, Western', popular: true },
  { city: 'Manso Amenfi', region: 'Amenfi Central, Western', full: 'Manso Amenfi, Western' },
  { city: 'Wassa Akropong', region: 'Amenfi East, Western', full: 'Wassa Akropong, Western' },
  { city: 'Shama', region: 'Shama District, Western', full: 'Shama, Western' },
  { city: 'Agona Nkwanta', region: 'Ahanta West, Western', full: 'Agona Nkwanta, Western' },
  { city: 'Dixcove & Busua', region: 'Ahanta West, Western', full: 'Dixcove, Western' },
  { city: 'Axim', region: 'Nzema East, Western', full: 'Axim, Western', popular: true },
  { city: 'Half Assini & Elubo', region: 'Jomoro, Western', full: 'Elubo, Western', popular: true },
  { city: 'Nkroful', region: 'Ellembelle, Western', full: 'Nkroful, Western' },
  { city: 'Daboase & Mpohor', region: 'Wassa East, Western', full: 'Daboase, Western' },

  // ══════════════════════════════════════════════════════════════
  // WESTERN NORTH REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Sefwi Wiawso', region: 'Sefwi Wiawso Municipal, Western North', full: 'Sefwi Wiawso, Western North', popular: true },
  { city: 'Bibiani', region: 'Bibiani-Anhwiaso-Bekwai, Western North', full: 'Bibiani, Western North', popular: true },
  { city: 'Sefwi Bekwai', region: 'Bibiani-Anhwiaso-Bekwai, Western North', full: 'Sefwi Bekwai, Western North' },
  { city: 'Juaboso', region: 'Juaboso, Western North', full: 'Juaboso, Western North' },
  { city: 'Bodi', region: 'Bodi, Western North', full: 'Bodi, Western North' },
  { city: 'Enchi', region: 'Aowin Municipal, Western North', full: 'Enchi, Western North', popular: true },
  { city: 'Dadieso', region: 'Suaman, Western North', full: 'Dadieso, Western North' },
  { city: 'Essam & Debiso', region: 'Bia West / East, Western North', full: 'Essam, Western North' },
  { city: 'Akontombra', region: 'Sefwi Akontombra, Western North', full: 'Akontombra, Western North' },

  // ══════════════════════════════════════════════════════════════
  // VOLTA REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Ho (Civic Centre & Barracks)', region: 'Ho Municipal, Volta Region', full: 'Ho, Volta', popular: true },
  { city: 'Hohoe', region: 'Hohoe Municipal, Volta Region', full: 'Hohoe, Volta', popular: true },
  { city: 'Kpando', region: 'Kpando Municipal, Volta Region', full: 'Kpando, Volta', popular: true },
  { city: 'Aflao (Border City)', region: 'Ketu South, Volta Region', full: 'Aflao, Volta', popular: true },
  { city: 'Denu & Tokor', region: 'Ketu South, Volta Region', full: 'Denu, Volta' },
  { city: 'Dzodze & Penyi', region: 'Ketu North, Volta Region', full: 'Dzodze, Volta' },
  { city: 'Keta', region: 'Keta Municipal, Volta Region', full: 'Keta, Volta', popular: true },
  { city: 'Anloga & Tegbi', region: 'Anloga District, Volta Region', full: 'Anloga, Volta' },
  { city: 'Akatsi', region: 'Akatsi South, Volta Region', full: 'Akatsi, Volta', popular: true },
  { city: 'Sogakope', region: 'South Tongu, Volta Region', full: 'Sogakope, Volta', popular: true },
  { city: 'Adidome', region: 'Central Tongu, Volta Region', full: 'Adidome, Volta' },
  { city: 'Battor & Mepe', region: 'North Tongu, Volta Region', full: 'Battor, Volta' },
  { city: 'Juapong', region: 'North Tongu, Volta Region', full: 'Juapong, Volta' },
  { city: 'Peki & Tsito', region: 'South Dayi, Volta Region', full: 'Peki, Volta' },
  { city: 'Kpeve', region: 'South Dayi, Volta Region', full: 'Kpeve, Volta' },
  { city: 'Anfoega & Vakpo', region: 'North Dayi, Volta Region', full: 'Anfoega, Volta' },
  { city: 'Golokwati & Have', region: 'Afadzato South, Volta Region', full: 'Golokwati, Volta' },
  { city: 'Amedzofe', region: 'Ho Municipal, Volta Region', full: 'Amedzofe, Volta' },
  { city: 'Agbozume & Wheta', region: 'Ketu South, Volta Region', full: 'Agbozume, Volta' },

  // ══════════════════════════════════════════════════════════════
  // OTI REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Dambai (Regional Capital)', region: 'Krachi East, Oti Region', full: 'Dambai, Oti', popular: true },
  { city: 'Nkwanta', region: 'Nkwanta South, Oti Region', full: 'Nkwanta, Oti', popular: true },
  { city: 'Kpassa', region: 'Nkwanta North, Oti Region', full: 'Kpassa, Oti' },
  { city: 'Jasikan', region: 'Jasikan District, Oti Region', full: 'Jasikan, Oti', popular: true },
  { city: 'Kadjebi', region: 'Kadjebi District, Oti Region', full: 'Kadjebi, Oti' },
  { city: 'Worawora', region: 'Biakoye, Oti Region', full: 'Worawora, Oti' },
  { city: 'Kete Krachi', region: 'Krachi West, Oti Region', full: 'Kete Krachi, Oti' },
  { city: 'Chinderi', region: 'Krachi Nchumuru, Oti Region', full: 'Chinderi, Oti' },

  // ══════════════════════════════════════════════════════════════
  // NORTHERN REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Tamale Central', region: 'Tamale Metropolitan, Northern', full: 'Tamale Central, Northern', popular: true },
  { city: 'Lamashegu & Aboabo', region: 'Tamale, Northern', full: 'Lamashegu, Tamale', popular: true },
  { city: 'Sagnarigu', region: 'Sagnarigu Municipal, Northern', full: 'Sagnarigu, Tamale', popular: true },
  { city: 'Nyankpala', region: 'Tolon District, Northern', full: 'Nyankpala, Northern' },
  { city: 'Yendi (Kingdom Capital)', region: 'Yendi Municipal, Northern', full: 'Yendi, Northern', popular: true },
  { city: 'Savelugu', region: 'Savelugu Municipal, Northern', full: 'Savelugu, Northern', popular: true },
  { city: 'Bimbilla', region: 'Nanumba North, Northern', full: 'Bimbilla, Northern', popular: true },
  { city: 'Gushegu', region: 'Gushegu Municipal, Northern', full: 'Gushegu, Northern' },
  { city: 'Karaga', region: 'Karaga District, Northern', full: 'Karaga, Northern' },
  { city: 'Saboba', region: 'Saboba District, Northern', full: 'Saboba, Northern' },
  { city: 'Tolon & Kumbungu', region: 'Tolon / Kumbungu, Northern', full: 'Kumbungu, Northern' },
  { city: 'Nanton', region: 'Nanton District, Northern', full: 'Nanton, Northern' },
  { city: 'Zabzugu & Tatale', region: 'Zabzugu, Northern', full: 'Zabzugu, Northern' },
  { city: 'Wulensi', region: 'Nanumba South, Northern', full: 'Wulensi, Northern' },

  // ══════════════════════════════════════════════════════════════
  // SAVANNAH REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Damongo', region: 'West Gonja, Savannah Region', full: 'Damongo, Savannah', popular: true },
  { city: 'Larabanga (Historic Mosque)', region: 'West Gonja, Savannah Region', full: 'Larabanga, Savannah' },
  { city: 'Bole', region: 'Bole District, Savannah Region', full: 'Bole, Savannah', popular: true },
  { city: 'Sawla & Tuna', region: 'Sawla-Tuna-Kalba, Savannah Region', full: 'Sawla, Savannah' },
  { city: 'Salaga (Historic Town)', region: 'East Gonja, Savannah Region', full: 'Salaga, Savannah', popular: true },
  { city: 'Buipe (Inland Port)', region: 'Central Gonja, Savannah Region', full: 'Buipe, Savannah', popular: true },
  { city: 'Daboya', region: 'North Gonja, Savannah Region', full: 'Daboya, Savannah' },
  { city: 'Kpembe', region: 'East Gonja, Savannah Region', full: 'Kpembe, Savannah' },

  // ══════════════════════════════════════════════════════════════
  // NORTH EAST REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Nalerigu', region: 'East Mamprusi, North East', full: 'Nalerigu, North East', popular: true },
  { city: 'Gambaga', region: 'East Mamprusi, North East', full: 'Gambaga, North East', popular: true },
  { city: 'Walewale', region: 'West Mamprusi, North East', full: 'Walewale, North East', popular: true },
  { city: 'Chereponi', region: 'Chereponi District, North East', full: 'Chereponi, North East' },
  { city: 'Bunkpurugu & Nakpanduri', region: 'Bunkpurugu-Nakpanduri, North East', full: 'Bunkpurugu, North East' },
  { city: 'Yunyoo', region: 'Yunyoo-Nasuan, North East', full: 'Yunyoo, North East' },

  // ══════════════════════════════════════════════════════════════
  // UPPER EAST REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Bolgatanga Central', region: 'Bolgatanga Municipal, Upper East', full: 'Bolgatanga, Upper East', popular: true },
  { city: 'Zuarungu', region: 'Bolgatanga East, Upper East', full: 'Zuarungu, Upper East' },
  { city: 'Navrongo', region: 'Kassena-Nankana Municipal, Upper East', full: 'Navrongo, Upper East', popular: true },
  { city: 'Paga (Crocodile Pond)', region: 'Kassena-Nankana West, Upper East', full: 'Paga, Upper East', popular: true },
  { city: 'Bawku Central', region: 'Bawku Municipal, Upper East', full: 'Bawku, Upper East', popular: true },
  { city: 'Zebilla', region: 'Bawku West, Upper East', full: 'Zebilla, Upper East', popular: true },
  { city: 'Sandema', region: 'Builsa North, Upper East', full: 'Sandema, Upper East' },
  { city: 'Fumbisi', region: 'Builsa South, Upper East', full: 'Fumbisi, Upper East' },
  { city: 'Bongo', region: 'Bongo District, Upper East', full: 'Bongo, Upper East' },
  { city: 'Tongo (Whispering Rocks)', region: 'Talensi, Upper East', full: 'Tongo, Upper East' },
  { city: 'Garu & Tempane', region: 'Garu / Tempane, Upper East', full: 'Garu, Upper East' },
  { city: 'Pusiga', region: 'Pusiga District, Upper East', full: 'Pusiga, Upper East' },

  // ══════════════════════════════════════════════════════════════
  // UPPER WEST REGION
  // ══════════════════════════════════════════════════════════════
  { city: 'Wa Central', region: 'Wa Municipal, Upper West', full: 'Wa, Upper West', popular: true },
  { city: 'Bamahu (UDS Campus)', region: 'Wa Municipal, Upper West', full: 'Bamahu, Wa' },
  { city: 'Nandom', region: 'Nandom Municipal, Upper West', full: 'Nandom, Upper West', popular: true },
  { city: 'Lawra', region: 'Lawra Municipal, Upper West', full: 'Lawra, Upper West', popular: true },
  { city: 'Jirapa', region: 'Jirapa Municipal, Upper West', full: 'Jirapa, Upper West', popular: true },
  { city: 'Tumu', region: 'Sissala East, Upper West', full: 'Tumu, Upper West', popular: true },
  { city: 'Gwollu', region: 'Sissala West, Upper West', full: 'Gwollu, Upper West' },
  { city: 'Nadowli & Kaleo', region: 'Nadowli-Kaleo, Upper West', full: 'Nadowli, Upper West' },
  { city: 'Lambussie', region: 'Lambussie Karni, Upper West', full: 'Lambussie, Upper West' },
  { city: 'Wechiau', region: 'Wa West, Upper West', full: 'Wechiau, Upper West' },
  { city: 'Funsi', region: 'Wa East, Upper West', full: 'Funsi, Upper West' },
];

export const GHANA_CITIES = POPULAR_GHANA_LOCATIONS.map((loc) => loc.full);

function RegisterContent() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const { register } = useAuth();

  const roleParam = searchParams.get('role');
  const tierParam = searchParams.get('tier');

  // Role: 'provider' or 'client'
  const [role, setRole] = useState<'provider' | 'client'>(
    roleParam === 'client' ? 'client' : 'provider'
  );

  // Selected Tier
  const [tier, setTier] = useState<'starter' | 'verified' | 'premium'>(
    tierParam === 'premium' ? 'premium' : tierParam === 'verified' ? 'verified' : 'starter'
  );

  // Stepper: 1 to 5 for Provider, 1 to 3 for Client
  const [step, setStep] = useState(1);
  const totalSteps = role === 'provider' ? 5 : 3;

  // SMS Verification State
  const [otpDigits, setOtpDigits] = useState(['', '', '', '', '', '']);
  const [resendTimer, setResendTimer] = useState(30);
  const [codeResentNotice, setCodeResentNotice] = useState(false);

  // Form Fields
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [network, setNetwork] = useState<TelecomNetwork>('unknown');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);

  // Provider specific
  const [selectedTrade, setSelectedTrade] = useState('');
  const [tradeSearchQuery, setTradeSearchQuery] = useState('');
  const [selectedCity, setSelectedCity] = useState(POPULAR_GHANA_LOCATIONS[0].full);
  const [locationQuery, setLocationQuery] = useState(POPULAR_GHANA_LOCATIONS[0].full);
  const [isLocationDropdownOpen, setIsLocationDropdownOpen] = useState(false);
  const locationRef = useRef<HTMLDivElement>(null);
  const [hourlyRate, setHourlyRate] = useState<string>('');
  const [ghanaCardPin, setGhanaCardPin] = useState('');
  const [isGhanaCardValid, setIsGhanaCardValid] = useState(false);
  const [cardFrontImg, setCardFrontImg] = useState<string | null>(null);
  const [cardBackImg, setCardBackImg] = useState<string | null>(null);
  const [payoutWallet, setPayoutWallet] = useState<'mtn' | 'telecel' | 'at'>('mtn');
  const [walletPhone, setWalletPhone] = useState('');

  // Real-time filtered trades based on user typing in search bar
  const filteredTrades = useMemo(() => {
    const q = tradeSearchQuery.toLowerCase().trim();
    let results: GhanaTradeOption[];
    if (!q) {
      results = GHANA_TRADES.slice(0, 40);
    } else {
      results = GHANA_TRADES.filter(
        (t) => t.name.toLowerCase().includes(q)
      ).slice(0, 50);
    }

    // Keep selected trade visible if not already in results
    if (selectedTrade && !results.some((t) => t.name.toLowerCase() === selectedTrade.toLowerCase())) {
      const found = GHANA_TRADES.find((t) => t.name.toLowerCase() === selectedTrade.toLowerCase());
      if (found) {
        return [found, ...results];
      }
    }
    return results;
  }, [tradeSearchQuery, selectedTrade]);

  // Real-time filtered locations (Facebook Location Autocomplete style)
  const filteredLocations = useMemo(() => {
    const q = locationQuery.toLowerCase().trim();
    if (!q) {
      return POPULAR_GHANA_LOCATIONS.filter((l) => l.popular);
    }
    return POPULAR_GHANA_LOCATIONS.filter(
      (l) =>
        l.city.toLowerCase().includes(q) ||
        l.region.toLowerCase().includes(q) ||
        l.full.toLowerCase().includes(q)
    );
  }, [locationQuery]);

  const hasExactLocationMatch = useMemo(() => {
    const q = locationQuery.toLowerCase().trim();
    if (!q) return true;
    return POPULAR_GHANA_LOCATIONS.some(
      (l) => l.city.toLowerCase() === q || l.full.toLowerCase() === q
    );
  }, [locationQuery]);

  // Dismiss location autocomplete when clicking outside
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (locationRef.current && !locationRef.current.contains(event.target as Node)) {
        setIsLocationDropdownOpen(false);
      }
    }
    document.addEventListener('mousedown', handleClickOutside);
    return () => {
      document.removeEventListener('mousedown', handleClickOutside);
    };
  }, []);

  // Client specific
  const [companyName, setCompanyName] = useState('');
  const [projectIntent, setProjectIntent] = useState<'post_job' | 'hire_artisan'>('hire_artisan');

  // UI state
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState('');

  // Auto-sync wallet when network is detected
  useEffect(() => {
    if (network === 'mtn') setPayoutWallet('mtn');
    else if (network === 'telecel') setPayoutWallet('telecel');
    else if (network === 'at') setPayoutWallet('at');

    if (phone && !walletPhone) {
      setWalletPhone(phone);
    }
  }, [network, phone]);

  // SMS Resend Countdown Timer
  useEffect(() => {
    if (step === totalSteps && resendTimer > 0) {
      const timer = setInterval(() => {
        setResendTimer((prev) => (prev > 0 ? prev - 1 : 0));
      }, 1000);
      return () => clearInterval(timer);
    }
  }, [step, totalSteps, resendTimer]);

  const handleNextStep = () => {
    setErrorMsg('');

    // Step 1 validation
    if (step === 1) {
      if (!firstName.trim()) {
        setErrorMsg('Please enter your first name.');
        return;
      }
      if (!email.trim() || !email.includes('@')) {
        setErrorMsg('Please enter a valid email address.');
        return;
      }
      if (!phone.trim() || phone.replace(/\D/g, '').length < 9) {
        setErrorMsg('Please enter a valid Ghanaian mobile phone number to receive your SMS code.');
        return;
      }
      if (!password || password.length < 6) {
        setErrorMsg('Password must be at least 6 characters.');
        return;
      }
    }

    // Provider step 2 trade and location assignment
    if (role === 'provider' && step === 2) {
      if (!selectedTrade.trim() && tradeSearchQuery.trim()) {
        setSelectedTrade(tradeSearchQuery.trim());
      }
      if (locationQuery.trim()) {
        setSelectedCity(locationQuery.trim());
      } else if (!selectedCity.trim()) {
        setSelectedCity('East Legon, Accra');
        setLocationQuery('East Legon, Accra');
      }
    }

    // Final SMS verification check
    if (step === totalSteps) {
      const fullCode = otpDigits.join('');
      if (fullCode.length !== 6) {
        setErrorMsg('Please enter the full 6-digit SMS verification code dispatched to your phone.');
        return;
      }
      handleFinalSubmit();
      return;
    }

    if (step < totalSteps) {
      setStep(step + 1);
    }
  };

  const handlePrevStep = () => {
    setErrorMsg('');
    if (step > 1) {
      setStep(step - 1);
    }
  };

  const handleFinalSubmit = async () => {
    setErrorMsg('');
    setIsSubmitting(true);

    try {
      const userData = {
        first_name: firstName.trim() || 'Master',
        last_name: lastName.trim() || (role === 'client' ? 'Client' : 'Artisan'),
        email: email.trim().toLowerCase() || 'user@gigghana.com',
        phone: phone.trim() || '0240000000',
        role,
        location: selectedCity,
        is_verified: true,
        phone_verified: true,
        membership_tier: tier,
        trade: selectedTrade.trim() || tradeSearchQuery.trim() || (role === 'provider' ? 'Verified Master Artisan' : undefined),
        hourly_rate: hourlyRate || undefined,
        payout_wallet: payoutWallet,
        wallet_number: walletPhone || phone,
      };

      try {
        await register(userData);
      } catch (_) {
        // Front-end resilience
      }

      try {
        confetti({
          particleCount: 90,
          spread: 70,
          origin: { y: 0.6 },
          colors: ['#00D4C8', '#F59E0B', '#10B981', '#ffffff'],
        });
      } catch (_) {}

      setTimeout(() => {
        if (role === 'provider') {
          router.push('/provider/dashboard');
        } else {
          router.push('/');
        }
      }, 900);
    } catch (err: any) {
      if (role === 'provider') {
        router.push('/provider/dashboard');
      } else {
        router.push('/');
      }
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <AuthLayout
      title={role === 'provider' ? 'Join as a Verified Master Artisan' : 'Hire Ghana Card Verified Talent'}
      subtitle={
        role === 'provider'
          ? 'Set up your profile, verify your Ghana Card, and receive instant sub-60s Mobile Money escrow settlements.'
          : 'Post your project in Cedis (₵), review verified local bids, and protect your milestone funds in escrow.'
      }
    >
      {/* ══════ DUAL-ROLE TOGGLE SWITCHER ══════ */}
      <div className="grid grid-cols-2 gap-2 p-1.5 bg-[var(--surface-elevated)] rounded-[22px] mb-6 border border-[var(--bd2)] shadow-inner">
        <button
          type="button"
          onClick={() => {
            setRole('provider');
            setStep(1);
          }}
          className={`flex items-center justify-center py-3 px-3.5 rounded-[16px] text-xs sm:text-sm font-black transition-all ${
            role === 'provider'
              ? 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] text-white shadow-md shadow-cyan-500/25 scale-[1.01]'
              : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface)] font-bold'
          }`}
        >
          <span>Find Jobs & Work</span>
        </button>

        <button
          type="button"
          onClick={() => {
            setRole('client');
            setStep(1);
          }}
          className={`flex items-center justify-center py-3 px-3.5 rounded-[16px] text-xs sm:text-sm font-black transition-all ${
            role === 'client'
              ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] text-white shadow-md shadow-amber-500/25 scale-[1.01]'
              : 'text-[var(--tx-2)] hover:text-[var(--tx)] hover:bg-[var(--surface)] font-bold'
          }`}
        >
          <span>Hire a Worker</span>
        </button>
      </div>

      {/* ══════ STEPPER PROGRESS BAR ══════ */}
      <div className="mb-6">
        <div className="flex items-center justify-between text-[11px] font-bold text-[var(--tx-3)] mb-2">
          <span>
            STEP {step} OF {totalSteps}:{' '}
            <strong className="text-[var(--tx)]">
              {role === 'provider'
                ? step === 1
                  ? 'Account Details'
                  : step === 2
                  ? 'Craft & Location'
                  : step === 3
                  ? 'Ghana Card Biometrics'
                  : step === 4
                  ? 'Payout Setup'
                  : 'SMS Verification'
                : step === 1
                ? 'Contact Info'
                : step === 2
                ? 'Project Intent'
                : 'SMS Verification'}
            </strong>
          </span>
          <span className="text-[var(--cyan)] font-mono">{Math.round((step / totalSteps) * 100)}%</span>
        </div>
        <div className="w-full h-1.5 bg-[var(--bd2)] rounded-full overflow-hidden">
          <div
            className="h-full bg-gradient-to-r from-[var(--cyan)] via-[#3B82F6] to-[#10B981] transition-all duration-300 rounded-full"
            style={{ width: `${(step / totalSteps) * 100}%` }}
          />
        </div>
      </div>

      {/* ══════ STEP CONTENT ══════ */}
      <div className="space-y-4">
        {/* PROVIDER STEP 1 & CLIENT STEP 1: Account Credentials */}
        {step === 1 && (
          <>
            {/* Tier banner reminder if selected from landing page */}
            {role === 'provider' && tier !== 'starter' && (
              <div className="p-3 rounded-xl bg-gradient-to-r from-amber-500/10 to-transparent border border-amber-500/30 flex items-center justify-between text-xs mb-2">
                <div className="flex items-center gap-2">
                  <BadgeCheck className="w-4 h-4 text-[#F59E0B]" />
                  <span className="font-semibold text-[var(--tx)]">
                    Selected Tier: <strong>{tier === 'premium' ? 'Premium Master (₵99/mo)' : 'Verified Pro (₵49/mo)'}</strong>
                  </span>
                </div>
                <button
                  type="button"
                  onClick={() => setTier('starter')}
                  className="text-[10px] text-[var(--tx-3)] hover:text-[var(--tx)] underline"
                >
                  Change to Free
                </button>
              </div>
            )}

            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                  <span>First Name</span>
                  <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  value={firstName}
                  onChange={(e) => setFirstName(e.target.value)}
                  placeholder="e.g. Kwame"
                  className="w-full h-12 px-4 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
                />
              </div>

              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                  <span>Surname</span>
                  <span className="text-rose-500">*</span>
                </label>
                <input
                  type="text"
                  value={lastName}
                  onChange={(e) => setLastName(e.target.value)}
                  placeholder="e.g. Asante"
                  className="w-full h-12 px-4 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
                />
              </div>
            </div>

            {/* Ghanaian Phone with Network Auto-Detect */}
            <PhoneInput
              value={phone}
              onChange={(val, net) => {
                setPhone(val);
                setNetwork(net);
              }}
              required
            />

            {/* Email Address */}
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                <span>Email Address</span>
                <span className="text-rose-500">*</span>
              </label>
              <input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="name@domain.com"
                className="w-full h-12 px-4 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
              />
            </div>

            {/* Password with Eye Toggle */}
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center gap-1">
                <span>Create Password</span>
                <span className="text-rose-500">*</span>
              </label>
              <div className="relative flex items-center">
                <input
                  type={showPassword ? 'text' : 'password'}
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="At least 6 characters"
                  className="w-full h-12 pl-4 pr-11 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute right-3.5 text-[var(--tx-3)] hover:text-[var(--tx)] transition-colors p-1"
                  aria-label={showPassword ? 'Hide password' : 'Show password'}
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>
          </>
        )}

        {/* PROVIDER STEP 2: Trade & Location */}
        {role === 'provider' && step === 2 && (
          <>
            <div className="space-y-3.5">
              {/* Header & Search Bar */}
              <div className="space-y-1.5">
                <div className="flex items-center justify-between">
                  <label className="text-xs font-bold text-[var(--tx)]">
                    Your Trade or Occupation
                  </label>
                  {selectedTrade ? (
                    <span className="text-[11px] font-bold text-[var(--cyan)] bg-[var(--cyan)]/10 px-2.5 py-0.5 rounded-full border border-[var(--cyan)]/25 flex items-center gap-1">
                      <Check className="w-3 h-3 stroke-[3]" />
                      <span className="truncate max-w-[150px]">{selectedTrade}</span>
                    </span>
                  ) : (
                    <span className="text-[11px] text-[var(--tx-3)] font-medium">
                      Type or select below
                    </span>
                  )}
                </div>

                <div className="relative flex items-center">
                  <Search className="w-4 h-4 text-[var(--tx-3)] absolute left-3.5 pointer-events-none" />
                  <input
                    type="text"
                    value={tradeSearchQuery}
                    onChange={(e) => {
                      const val = e.target.value;
                      setTradeSearchQuery(val);
                      setSelectedTrade(val);
                    }}
                    placeholder="Search or type custom occupation (e.g. Mason, Plumber, Tailor, AC Repairer)..."
                    className="w-full h-12 pl-10 pr-9 bg-[var(--surface)] text-[var(--tx)] text-xs sm:text-sm font-medium rounded-[18px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all placeholder:text-[var(--tx-3)]"
                  />
                  {tradeSearchQuery && (
                    <button
                      type="button"
                      onClick={() => {
                        setTradeSearchQuery('');
                        setSelectedTrade('');
                      }}
                      className="absolute right-3 text-[var(--tx-3)] hover:text-[var(--tx)] p-1 rounded-md transition-colors"
                      aria-label="Clear search"
                    >
                      <X className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>

                <div className="text-[11px] text-[var(--tx-3)] flex items-center justify-between px-0.5">
                  <span>Search across 1,900+ standalone Ghanaian trades, or choose below.</span>
                  {tradeSearchQuery && (
                    <button
                      type="button"
                      onClick={() => {
                        setTradeSearchQuery('');
                      }}
                      className="text-[10.5px] text-[var(--cyan)] font-bold hover:underline shrink-0 ml-2"
                    >
                      Clear
                    </button>
                  )}
                </div>
              </div>

              {/* Occupations Directory List */}
              <div className="space-y-2 max-h-[290px] overflow-y-auto pr-1">
                {filteredTrades.length > 0 ? (
                  filteredTrades.map((trade) => {
                    const isSelected = selectedTrade.toLowerCase() === trade.name.toLowerCase();
                    return (
                      <button
                        key={trade.id}
                        type="button"
                        onClick={() => {
                          setSelectedTrade(trade.name);
                          setTradeSearchQuery(trade.name);
                        }}
                        className={`w-full p-3.5 rounded-[18px] border text-left flex items-center justify-between gap-3 transition-all cursor-pointer ${
                          isSelected
                            ? 'border-[var(--cyan)] bg-[var(--cyan)]/[0.08] ring-1 ring-[var(--cyan)]/40 shadow-xs'
                            : 'border-[var(--bd2)] hover:border-[var(--bd)] bg-[var(--surface)] hover:bg-[var(--surface-elevated)]'
                        }`}
                      >
                        <div className="min-w-0 flex-1">
                          <div className="text-xs sm:text-sm font-bold text-[var(--tx)] leading-snug">
                            {trade.name}
                          </div>
                        </div>

                        <div className="shrink-0 ml-1">
                          {isSelected ? (
                            <div className="w-5 h-5 rounded-full bg-[var(--cyan)] text-slate-950 flex items-center justify-center">
                              <Check className="w-3.5 h-3.5 stroke-[3]" />
                            </div>
                          ) : (
                            <div className="w-5 h-5 rounded-full border border-[var(--bd2)]" />
                          )}
                        </div>
                      </button>
                    );
                  })
                ) : (
                  <div className="p-4 text-center text-xs bg-[var(--surface)] rounded-[18px] border border-dashed border-[var(--cyan)]/40">
                    <div className="font-bold text-[var(--tx)] text-sm mb-1 flex items-center justify-center gap-1.5">
                      <Check className="w-4 h-4 text-[var(--cyan)]" />
                      <span>Custom Trade: "{tradeSearchQuery.trim()}"</span>
                    </div>
                    <p className="text-[11px] text-[var(--tx-3)]">
                      Not in preset directory — this will be registered directly as your official trade on GigGhana.
                    </p>
                  </div>
                )}
              </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
              {/* City / Hub Autocomplete (Facebook Location style) */}
              <div className="space-y-1.5 relative" ref={locationRef}>
                <label className="text-xs font-bold text-[var(--tx)]">
                  Location
                </label>

                <div className="relative flex items-center">
                  <MapPin className="w-4 h-4 text-[var(--tx-3)] absolute left-3.5 pointer-events-none" />
                  <input
                    type="text"
                    value={locationQuery}
                    onFocus={() => setIsLocationDropdownOpen(true)}
                    onChange={(e) => {
                      const val = e.target.value;
                      setLocationQuery(val);
                      setSelectedCity(val);
                      setIsLocationDropdownOpen(true);
                    }}
                    onKeyDown={(e) => {
                      if (e.key === 'Escape') {
                        setIsLocationDropdownOpen(false);
                      } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (filteredLocations.length > 0) {
                          const topLoc = filteredLocations[0];
                          setSelectedCity(topLoc.full);
                          setLocationQuery(topLoc.full);
                          setIsLocationDropdownOpen(false);
                        } else if (locationQuery.trim()) {
                          setSelectedCity(locationQuery.trim());
                          setIsLocationDropdownOpen(false);
                        }
                      }
                    }}
                    placeholder="Type city, suburb or area (e.g. East Legon)..."
                    className="w-full h-12 pl-10 pr-9 bg-[var(--surface)] text-[var(--tx)] text-xs sm:text-sm font-semibold rounded-[18px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all placeholder:text-[var(--tx-3)] placeholder:font-normal"
                  />
                  {locationQuery && (
                    <button
                      type="button"
                      onClick={() => {
                        setLocationQuery('');
                        setSelectedCity('');
                        setIsLocationDropdownOpen(true);
                      }}
                      className="absolute right-3 p-1 text-[var(--tx-3)] hover:text-[var(--tx)] transition-colors cursor-pointer"
                      aria-label="Clear location input"
                    >
                      <X className="w-3.5 h-3.5" />
                    </button>
                  )}
                </div>

                {/* Facebook-style Suggestions Popover Dropdown */}
                {isLocationDropdownOpen && (
                  <div className="absolute z-50 left-0 right-0 top-[calc(100%+6px)] bg-[var(--surface-elevated)] border border-[var(--bd2)] rounded-[20px] shadow-2xl overflow-hidden backdrop-blur-xl max-h-64 overflow-y-auto divide-y divide-[var(--bd2)]/40 animate-in fade-in-50 zoom-in-95 duration-150">
                    {/* Custom Location Option (Facebook style) when user typed something that is not an exact preset match */}
                    {locationQuery.trim() && !hasExactLocationMatch && (
                      <button
                        type="button"
                        onClick={() => {
                          setSelectedCity(locationQuery.trim());
                          setIsLocationDropdownOpen(false);
                        }}
                        className="w-full px-3.5 py-2.5 text-left flex items-center gap-3 hover:bg-[var(--cyan)]/[0.08] bg-[var(--surface)] transition-colors cursor-pointer group"
                      >
                        <div className="w-8 h-8 rounded-full bg-[var(--cyan)]/15 text-[var(--cyan)] flex items-center justify-center shrink-0">
                          <MapPin className="w-4 h-4" />
                        </div>
                        <div className="min-w-0 flex-1">
                          <div className="text-xs font-bold text-[var(--tx)] group-hover:text-[var(--cyan)] truncate">
                            Use &quot;{locationQuery.trim()}&quot;
                          </div>
                          <div className="text-[10px] text-[var(--tx-3)]">
                            Register as custom Ghanaian location
                          </div>
                        </div>
                        <span className="text-[9px] font-bold text-[var(--cyan)] bg-[var(--cyan)]/10 px-2 py-0.5 rounded-full border border-[var(--cyan)]/25 shrink-0">
                          Custom
                        </span>
                      </button>
                    )}


                    {/* Filtered Location List */}
                    {filteredLocations.length > 0 ? (
                      filteredLocations.map((loc) => {
                        const isSelected = selectedCity === loc.full;
                        return (
                          <button
                            key={loc.full}
                            type="button"
                            onClick={() => {
                              setSelectedCity(loc.full);
                              setLocationQuery(loc.full);
                              setIsLocationDropdownOpen(false);
                            }}
                            className={`w-full px-3.5 py-2.5 text-left flex items-center gap-3 transition-colors cursor-pointer group ${
                              isSelected
                                ? 'bg-[var(--cyan)]/[0.12] text-[var(--cyan)]'
                                : 'hover:bg-[var(--cyan)]/[0.06] text-[var(--tx)]'
                            }`}
                          >
                            <div
                              className={`w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-colors ${
                                isSelected
                                  ? 'bg-[var(--cyan)] text-slate-950 font-bold'
                                  : 'bg-[var(--surface)] border border-[var(--bd2)] text-[var(--cyan)] group-hover:border-[var(--cyan)]/50'
                              }`}
                            >
                              <MapPin className="w-4 h-4" />
                            </div>
                            <div className="min-w-0 flex-1">
                              <div className="text-xs font-bold truncate flex items-center gap-1.5">
                                <span>{loc.city}</span>
                                {loc.popular && !locationQuery.trim() && (
                                  <span className="text-[8.5px] px-1.5 py-0.2 rounded bg-[var(--cyan)]/10 text-[var(--cyan)] font-mono font-bold">
                                    HUB
                                  </span>
                                )}
                              </div>
                              <div className="text-[10px] text-[var(--tx-3)] truncate">
                                {loc.region}
                              </div>
                            </div>
                            {isSelected && (
                              <div className="w-5 h-5 rounded-full bg-[var(--cyan)] text-slate-950 flex items-center justify-center shrink-0">
                                <Check className="w-3.5 h-3.5 stroke-[3]" />
                              </div>
                            )}
                          </button>
                        );
                      })
                    ) : (
                      <div className="p-3 text-center text-xs text-[var(--tx-3)]">
                        No matching presets found. Click &quot;Use {locationQuery.trim()}&quot; above to register this area.
                      </div>
                    )}
                  </div>
                )}
              </div>

              {/* Hourly / Estimate Rate (Optional) */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                  <span className="flex items-center gap-1.5">
                    <span>Base Rate (Cedis)</span>
                    <span className="text-[10px] font-bold text-[var(--cyan)] bg-[var(--cyan)]/10 px-2 py-0.5 rounded-full uppercase tracking-wider border border-[var(--cyan)]/25">
                      Optional
                    </span>
                  </span>
                  <span className="text-[10px] font-mono text-[var(--tx-3)]">₵ GHS</span>
                </label>
                <div className="relative flex items-center">
                  <span className="absolute left-3.5 text-xs font-bold text-[var(--tx-2)]">₵</span>
                  <input
                    type="number"
                    value={hourlyRate}
                    onChange={(e) => setHourlyRate(e.target.value)}
                    placeholder="Optional (leave blank or e.g. 75)"
                    className="w-full h-12 pl-8 pr-12 bg-[var(--surface)] text-[var(--tx)] text-sm font-bold rounded-[18px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none placeholder:font-normal placeholder:text-xs placeholder:text-[var(--tx-3)]"
                  />
                  <span className="absolute right-3.5 text-xs text-[var(--tx-3)] font-medium">/ hr</span>
                </div>
              </div>
            </div>
          </>
        )}

        {/* PROVIDER STEP 3: Ghana Card Biometrics */}
        {role === 'provider' && step === 3 && (
          <GhanaCardInput
            pin={ghanaCardPin}
            onPinChange={(pinVal, valid) => {
              setGhanaCardPin(pinVal);
              setIsGhanaCardValid(valid);
            }}
            frontImage={cardFrontImg}
            onFrontImageChange={setCardFrontImg}
            backImage={cardBackImg}
            onBackImageChange={setCardBackImg}
          />
        )}

        {/* PROVIDER STEP 4: Mobile Money Settlement */}
        {role === 'provider' && step === 4 && (
          <div className="space-y-4">
            <div className="space-y-2">
              <label className="text-xs font-bold text-[var(--tx)]">
                Select Mobile Money Escrow Settlement Wallet
              </label>
              <div className="grid grid-cols-3 gap-2">
                {[
                  { id: 'mtn', name: 'MTN MoMo', logo: '/images/payments/mtn_momo.svg', color: 'border-amber-400' },
                  { id: 'telecel', name: 'Telecel Cash', logo: '/images/payments/telecel_cash.svg', color: 'border-red-500' },
                  { id: 'at', name: 'AT Money', logo: '/images/payments/at_money.svg', color: 'border-blue-500' },
                ].map((w) => (
                  <button
                    key={w.id}
                    type="button"
                    onClick={() => setPayoutWallet(w.id as any)}
                    className={`p-3.5 rounded-[18px] border flex flex-col items-center justify-center gap-1.5 transition-all ${
                      payoutWallet === w.id
                        ? `${w.color} bg-[var(--cyan)]/[0.06] shadow-xs ring-1 ring-current`
                        : 'border-[var(--bd2)] bg-[var(--surface)] hover:border-[var(--bd)]'
                    }`}
                  >
                    <img src={w.logo} alt={w.name} className="h-5 w-auto object-contain" />
                    <span className="text-[10.5px] font-bold text-[var(--tx)]">{w.name}</span>
                  </button>
                ))}
              </div>
            </div>

            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                <span>Wallet Mobile Number</span>
                <span className="text-[10.5px] text-[#10B981] font-semibold">Sub-60s Direct Settlement</span>
              </label>
              <input
                type="tel"
                value={walletPhone}
                onChange={(e) => setWalletPhone(e.target.value)}
                placeholder="024 000 0000"
                className="w-full h-12 px-4 bg-[var(--surface)] text-[var(--tx)] text-sm font-mono font-bold rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
              />
            </div>

            {/* Instant verification assurance banner */}
            <div className="rounded-[18px] p-4 bg-gradient-to-r from-[#10B981]/10 to-[var(--cyan)]/10 border border-[#10B981]/25 flex items-start gap-2.5">
              <CheckCircle2 className="w-4 h-4 text-[#10B981] shrink-0 mt-0.5" />
              <div className="text-xs text-[var(--tx-2)] leading-relaxed">
                <strong className="text-[var(--tx)]">Escrow Guarantee:</strong> When clients approve milestone deliverables, your earnings are automatically transferred directly to this Mobile Money account with zero withdrawal delays.
              </div>
            </div>
          </div>
        )}

        {/* CLIENT STEP 2: Intent & Organization */}
        {role === 'client' && step === 2 && (
          <div className="space-y-4">
            <div className="space-y-1.5">
              <label className="text-xs font-bold text-[var(--tx)] flex items-center justify-between">
                <span>Company or Household Name</span>
                <span className="text-[10px] text-[var(--tx-3)]">Optional</span>
              </label>
              <input
                type="text"
                value={companyName}
                onChange={(e) => setCompanyName(e.target.value)}
                placeholder="e.g. Ridge Commercial Ltd or Private Residence"
                className="w-full h-12 px-4 bg-[var(--surface)] text-[var(--tx)] text-sm font-medium rounded-[16px] border border-[var(--bd2)] focus:border-[var(--cyan)] focus:outline-none"
              />
            </div>

            <div className="space-y-2">
              <label className="text-xs font-bold text-[var(--tx)]">
                What is your immediate hiring objective?
              </label>
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <button
                  type="button"
                  onClick={() => setProjectIntent('hire_artisan')}
                  className={`p-3.5 rounded-[18px] border text-left transition-all ${
                    projectIntent === 'hire_artisan'
                      ? 'border-[var(--cyan)] bg-[var(--cyan)]/[0.08] shadow-xs'
                      : 'border-[var(--bd2)] bg-[var(--surface)] hover:border-[var(--bd)]'
                  }`}
                >
                  <div className="text-xs font-bold text-[var(--tx)] mb-0.5 flex items-center gap-1.5">
                    <Search className="w-3.5 h-3.5 text-[var(--cyan)]" />
                    <span>Browse &amp; Direct Hire</span>
                  </div>
                  <div className="text-[11px] text-[var(--tx-3)]">Look through Ghana Card verified profiles and message them.</div>
                </button>

                <button
                  type="button"
                  onClick={() => setProjectIntent('post_job')}
                  className={`p-3.5 rounded-[18px] border text-left transition-all ${
                    projectIntent === 'post_job'
                      ? 'border-[#F59E0B] bg-[#F59E0B]/[0.08] shadow-xs'
                      : 'border-[var(--bd2)] bg-[var(--surface)] hover:border-[var(--bd)]'
                  }`}
                >
                  <div className="text-xs font-bold text-[var(--tx)] mb-0.5 flex items-center gap-1.5">
                    <FileText className="w-3.5 h-3.5 text-[#F59E0B]" />
                    <span>Post a Project Brief</span>
                  </div>
                  <div className="text-[11px] text-[var(--tx-3)]">Receive competitive Cedi proposals from top local masters within 15 min.</div>
                </button>
              </div>
            </div>

            {/* Escrow assurance note */}
            <div className="rounded-[18px] p-4 bg-gradient-to-r from-[var(--cyan)]/10 to-[#3B82F6]/10 border border-[var(--cyan)]/25 flex items-start gap-2.5">
              <ShieldCheck className="w-4 h-4 text-[var(--cyan)] shrink-0 mt-0.5" />
              <div className="text-xs text-[var(--tx-2)] leading-relaxed">
                <strong className="text-[var(--tx)]">Zero Upfront Risk:</strong> Your milestone deposits are safely locked in the Bank-Grade Escrow Vault until you inspect and approve the completed work.
              </div>
            </div>
          </div>
        )}

        {/* FINAL STEP: SMS OTP VERIFICATION (Step 5 for Provider, Step 3 for Client) */}
        {step === totalSteps && (
          <div className="space-y-4">
            {/* Header Badge */}
            <div className="text-center space-y-1.5 pt-1">
              <div className="w-12 h-12 rounded-[20px] bg-gradient-to-br from-[var(--cyan)]/20 to-blue-500/20 border border-[var(--cyan)]/30 text-[var(--cyan)] flex items-center justify-center mx-auto shadow-xs">
                <Smartphone className="w-6 h-6" />
              </div>
              <h3 className="text-base font-extrabold text-[var(--tx)] tracking-tight">
                Verify Your Mobile Number
              </h3>
              <p className="text-xs text-[var(--tx-2)] max-w-sm mx-auto leading-relaxed">
                We dispatched a 6-digit security code via SMS to{' '}
                <strong className="text-[var(--tx)] font-mono">
                  {phone ? (phone.startsWith('0') ? `+233 ${phone.slice(1)}` : phone) : '+233 24 000 0000'}
                </strong>
              </p>
              <div className="flex items-center justify-center gap-2 pt-0.5">
                <span className="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--cyan)]">
                  <CheckCircle2 className="w-3 h-3 text-[#10B981]" />
                  <span>{network !== 'unknown' ? `${network.toUpperCase()} SIM Detected` : 'Ghana Mobile'}</span>
                </span>
                <button
                  type="button"
                  onClick={() => {
                    setStep(1);
                    setErrorMsg('');
                  }}
                  className="text-[10.5px] text-[var(--tx-3)] hover:text-[var(--cyan)] underline font-medium cursor-pointer"
                >
                  Edit phone
                </button>
              </div>
            </div>

            {/* Dev / Prototype Testing Helper Pill */}
            <div className="p-3 rounded-[16px] bg-[var(--cyan)]/10 border border-[var(--cyan)]/25 flex items-center justify-between gap-2">
              <div className="text-[11.5px] text-[var(--tx)] flex items-center gap-1.5">
                <ShieldCheck className="w-4 h-4 text-[var(--cyan)] shrink-0" />
                <span>
                  Demo Code: <strong className="font-mono text-[var(--cyan)] font-black tracking-wider">123456</strong>
                </span>
              </div>
              <button
                type="button"
                onClick={() => {
                  setOtpDigits(['1', '2', '3', '4', '5', '6']);
                  setErrorMsg('');
                }}
                className="text-[11px] font-bold px-2.5 py-1 rounded-[10px] bg-[var(--cyan)] text-slate-950 hover:opacity-90 transition-all cursor-pointer shadow-xs"
              >
                Auto-Fill
              </button>
            </div>

            {/* 6-Digit OTP Input Boxes */}
            <div className="space-y-2 py-1">
              <label className="text-xs font-bold text-[var(--tx)] block text-center">
                Enter 6-Digit Verification Code
              </label>
              <div className="flex items-center justify-center gap-2">
                {otpDigits.map((digit, idx) => (
                  <input
                    key={idx}
                    id={`reg-sms-${idx}`}
                    type="text"
                    inputMode="numeric"
                    pattern="[0-9]*"
                    maxLength={1}
                    value={digit}
                    onChange={(e) => {
                      const val = e.target.value.replace(/\D/g, '');
                      const newCode = [...otpDigits];
                      newCode[idx] = val;
                      setOtpDigits(newCode);
                      if (val && idx < 5) {
                        document.getElementById(`reg-sms-${idx + 1}`)?.focus();
                      }
                    }}
                    onKeyDown={(e) => {
                      if (e.key === 'Backspace' && !otpDigits[idx] && idx > 0) {
                        document.getElementById(`reg-sms-${idx - 1}`)?.focus();
                      }
                    }}
                    onPaste={(e) => {
                      e.preventDefault();
                      const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                      if (pasted) {
                        const newDigits = pasted.split('');
                        while (newDigits.length < 6) newDigits.push('');
                        setOtpDigits(newDigits);
                        const nextIndex = Math.min(pasted.length, 5);
                        document.getElementById(`reg-sms-${nextIndex}`)?.focus();
                      }
                    }}
                    className="w-11 h-12 text-center font-mono font-bold text-lg rounded-[16px] bg-[var(--surface-elevated)] border border-[var(--bd2)] text-[var(--tx)] focus:border-[var(--cyan)] focus:ring-2 focus:ring-[var(--cyan)]/20 focus:outline-none transition-all"
                  />
                ))}
              </div>
            </div>

            {/* Resend SMS Counter */}
            <div className="flex items-center justify-between text-xs px-1">
              <span className="text-[var(--tx-3)]">Didn't receive code?</span>
              <button
                type="button"
                disabled={resendTimer > 0}
                onClick={() => {
                  setResendTimer(30);
                  setCodeResentNotice(true);
                  setTimeout(() => setCodeResentNotice(false), 3500);
                }}
                className={`font-bold transition-colors ${
                  resendTimer > 0
                    ? 'text-[var(--tx-3)] cursor-not-allowed'
                    : 'text-[var(--cyan)] hover:underline cursor-pointer'
                }`}
              >
                {resendTimer > 0 ? `Resend SMS in ${resendTimer}s` : 'Resend SMS Code'}
              </button>
            </div>

            {codeResentNotice && (
              <div className="p-2.5 rounded-[14px] bg-[#10B981]/10 border border-[#10B981]/25 text-[#10B981] text-xs font-semibold text-center">
                A fresh 6-digit SMS verification code has been dispatched.
              </div>
            )}

            {/* Role-Specific Escrow Benefit Note */}
            <div className="rounded-[18px] p-3.5 bg-[var(--surface-elevated)] border border-[var(--bd2)] flex items-start gap-2.5 text-xs text-[var(--tx-2)]">
              <ShieldCheck className="w-4 h-4 text-[var(--cyan)] shrink-0 mt-0.5" />
              <div>
                {role === 'provider' ? (
                  <span>
                    <strong className="text-[var(--tx)]">Instant Escrow Payouts:</strong> Verifying your phone number secures your {payoutWallet === 'telecel' ? 'Telecel Cash' : payoutWallet === 'at' ? 'AT Money' : 'MTN MoMo'} wallet for sub-60s milestone cash-outs.
                  </span>
                ) : (
                  <span>
                    <strong className="text-[var(--tx)]">Protected Escrow Hiring:</strong> Verifying your phone secures your project deposits and activates real-time milestone SMS alerts.
                  </span>
                )}
              </div>
            </div>
          </div>
        )}

        {/* Error message alert */}
        {errorMsg && (
          <div className="p-3.5 rounded-[16px] bg-rose-500/10 border border-rose-500/30 text-rose-500 text-xs font-medium flex items-center gap-2">
            <AlertCircle className="w-4 h-4 shrink-0" />
            <span>{errorMsg}</span>
          </div>
        )}

        {/* ══════ ACTION NAVIGATION BUTTONS ══════ */}
        <div className="pt-3 flex items-center justify-between gap-3">
          {step > 1 ? (
            <button
              type="button"
              onClick={handlePrevStep}
              className="h-12 px-5 rounded-[18px] border border-[var(--bd2)] hover:border-[var(--bd)] text-[var(--tx-2)] hover:text-[var(--tx)] font-bold text-xs flex items-center gap-1.5 transition-all cursor-pointer"
            >
              <ArrowLeft className="w-3.5 h-3.5" />
              <span>Back</span>
            </button>
          ) : (
            <div />
          )}

          <button
            type="button"
            onClick={handleNextStep}
            disabled={isSubmitting}
            className={`h-12 px-7 rounded-[18px] font-black text-sm flex items-center gap-2 shadow-lg transition-all ml-auto cursor-pointer ${
              role === 'client'
                ? 'bg-gradient-to-r from-[#F59E0B] to-[#D97706] hover:from-[#D97706] hover:to-[#B45309] text-white shadow-amber-500/20'
                : 'bg-gradient-to-r from-[var(--cyan)] to-[#00A89D] hover:from-[#00B4A9] hover:to-[#008B82] text-white shadow-cyan-500/20'
            }`}
          >
            {isSubmitting ? (
              <>
                <span className="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin" />
                <span>Verifying &amp; Creating...</span>
              </>
            ) : step === totalSteps ? (
              <>
                <span>Verify &amp; Complete Registration</span>
                <Check className="w-4 h-4 stroke-[3]" />
              </>
            ) : step === totalSteps - 1 ? (
              <>
                <span>Continue to SMS Verification</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </>
            ) : (
              <>
                <span>Continue</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </>
            )}
          </button>
        </div>

        {/* Bottom Switch to Login */}
        <div className="pt-4 text-center border-t border-[var(--bd2)]/40 text-xs text-[var(--tx-2)]">
          Already have an account?{' '}
          <a href="/auth/login" className="font-extrabold text-[var(--cyan)] hover:underline">
            Sign In here
          </a>
        </div>
      </div>
    </AuthLayout>
  );
}

export default function RegisterPage() {
  return (
    <Suspense fallback={<div className="min-h-screen bg-[var(--bg)] flex items-center justify-center text-xs text-[var(--tx-3)]">Loading GigGhana Onboarding...</div>}>
      <RegisterContent />
    </Suspense>
  );
}
