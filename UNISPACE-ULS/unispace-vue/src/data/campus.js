export const CAMPUS_CENTER = {
  lat: 13.6754187,
  lng: -89.193982,
  nombre: "Universidad Luterana Salvadoreña",
  direccion:
    "Intersección NorOriente Carretera a Los Planes de Renderos Km. 3 y Autopista a Comalapa, Bo. San Jacinto, San Salvador",
};

export function googleEmbedUrl(lat = CAMPUS_CENTER.lat, lng = CAMPUS_CENTER.lng, zoom = 18) {
  return `https://maps.google.com/maps?width=100%25&height=600&hl=es&q=${lat},${lng}&ie=UTF8&t=&z=${zoom}&iwloc=B&output=embed`;
}

// Tipos de lugar: etiqueta, color del marcador e icono por defecto.
export const TIPOS = {
  acceso: { label: "Accesos", color: "#64748b", icono: "🚪" },
  administracion: { label: "Administración", color: "#1a4fa0", icono: "🏛️" },
  facultad: { label: "Facultades y aulas", color: "#7c3aed", icono: "🎓" },
  servicio: { label: "Servicios", color: "#0d9488", icono: "📍" },
  evento: { label: "Eventos", color: "#e11d48", icono: "🎤" },
};

const ICONOS_LUGAR = {
  entrada: "🚪", rectoria: "🏛️", ciencias: "💻", humanidades: "📖",
  biblioteca: "📚", cafeteria: "☕", auditorio: "🎤",
  parqueo: "🅿️",
};

export function iconoDe(lugar) {
  return ICONOS_LUGAR[lugar.id] || TIPOS[lugar.tipo]?.icono || "📍";
}

// OJO: las coordenadas son aproximadas. Ajustalas con el clic derecho en Google Maps
// ("¿Qué hay aquí?") sobre cada edificio real del campus.
export const EDIFICIOS = [
  {
    id: "entrada",
    nombre: "Portón principal",
    tipo: "acceso",
    lat: 13.67512,
    lng: -89.19428,
    descripcion: "Entrada principal del campus ULS. Punto de partida del tour de bienvenida.",
    aulas: [],
  },
  {
    id: "aulas-lab",
    nombre: "Aulas y laboratorios",
    tipo: "facultad",
    lat: 13.67536,
    lng: -89.1942,
    descripcion: "Aulas y laboratorios del campus.",
    aulas: [{ id: "aula-a7", nombre: "Aula A7", piso: 1 }],
  },
  {
    id: "rectoria",
    nombre: "Edificio Administrativo / Rectoría",
    tipo: "administracion",
    lat: 13.67542,
    lng: -89.19412,
    descripcion: "Rectoría, secretaría, registro académico y atención al estudiante.",
    aulas: [
      { id: "adm-01", nombre: "Oficina de Registro Académico", piso: 1 },
      { id: "adm-02", nombre: "Caja y servicios financieros", piso: 1 },
      { id: "adm-03", nombre: "Sala de juntas", piso: 2 },
    ],
  },
  {
    id: "ciencias",
    nombre: "Facultad de Ciencias del Hombre y la Naturaleza",
    tipo: "facultad",
    lat: 13.67562,
    lng: -89.19378,
    descripcion:
      "Ciencias de la Computación, Ingeniería Agroecológica, Administración, Ciencias Jurídicas y Contaduría.",
    aulas: [
      { id: "aula-101", nombre: "Aula 101", piso: 1 },
      { id: "aula-102", nombre: "Aula 102", piso: 1 },
      { id: "aula-103", nombre: "Aula 103", piso: 1 },
      { id: "aula-201", nombre: "Aula 201", piso: 2 },
      { id: "aula-202", nombre: "Aula 202", piso: 2 },
      { id: "lab-comp", nombre: "Laboratorio de Computación", piso: 2 },
    ],
  },
  {
    id: "humanidades",
    nombre: "Facultad de Teología y Humanidades",
    tipo: "facultad",
    lat: 13.67578,
    lng: -89.19405,
    descripcion: "Trabajo Social, Teología y aulas de humanidades.",
    aulas: [
      { id: "aula-h1", nombre: "Aula H-1", piso: 1 },
      { id: "aula-h2", nombre: "Aula H-2", piso: 1 },
      { id: "aula-h3", nombre: "Aula H-3", piso: 2 },
      { id: "aula-h4", nombre: "Aula H-4", piso: 2 },
    ],
  },
  {
    id: "biblioteca",
    nombre: 'Biblioteca Central "Ana de los Ángeles de Salomóne"',
    tipo: "servicio",
    lat: 13.67535,
    lng: -89.1937,
    descripcion: "Sala de lectura, hemeroteca y recursos digitales para la comunidad ULS.",
    aulas: [
      { id: "sala-lectura", nombre: "Sala de lectura", piso: 1 },
      { id: "sala-estudio", nombre: "Salas de estudio grupal", piso: 2 },
    ],
  },
  {
    id: "cafeteria",
    nombre: "Cafetería",
    tipo: "servicio",
    lat: 13.67522,
    lng: -89.19388,
    descripcion: "Cafetería del campus. Punto de encuentro entre clases.",
    aulas: [],
  },
  {
    id: "auditorio",
    nombre: "Auditorio",
    tipo: "evento",
    lat: 13.67555,
    lng: -89.19355,
    descripcion: "Actos oficiales, conferencias, ferias y actividades estudiantiles.",
    aulas: [{ id: "aud-principal", nombre: "Sala principal", piso: 1 }],
  },
  {
    id: "parqueo",
    nombre: "Parqueo",
    tipo: "acceso",
    lat: 13.67505,
    lng: -89.19355,
    descripcion: "Estacionamiento de estudiantes, docentes y visitas.",
    aulas: [],
  },
];

export const TOUR_PASOS = [
  {
    id: "entrada",
    titulo: "Bienvenida al campus ULS",
    texto: "¡Qué onda! Este es el portón principal. Desde acá arranca tu vida universitaria, en UniSpace y en el campus físico.",
    lugarId: "entrada",
  },
  {
    id: "rectoria",
    titulo: "Administración y registro",
    texto: "En Rectoría resolvés trámites, inscripción y consultas académicas. Guardá este punto para cualquier gestión.",
    lugarId: "rectoria",
  },
  {
    id: "ciencias",
    titulo: "Aulas y laboratorio de computación",
    texto: "La Facultad de Ciencias concentra las aulas 101 a 202 y el laboratorio de computación. En el foro podés etiquetar en qué aula ocurre una actividad.",
    lugarId: "ciencias",
  },
  {
    id: "humanidades",
    titulo: "Teología y Humanidades",
    texto: "Acá están las aulas H-1 a H-4 de Trabajo Social, Teología y humanidades.",
    lugarId: "humanidades",
  },
  {
    id: "biblioteca",
    titulo: "Biblioteca",
    texto: "La Biblioteca Ana de los Ángeles de Salomóne es ideal para estudiar. También es un lugar frecuente de objetos perdidos.",
    lugarId: "biblioteca",
  },
  {
    id: "cafeteria",
    titulo: "Cafetería",
    texto: "Punto de encuentro entre clases. Acá se publican muchas actividades informales del foro.",
    lugarId: "cafeteria",
  },
  {
    id: "auditorio",
    titulo: "Auditorio",
    texto: "Conferencias, actos y eventos institucionales. Revisá el feed para ver qué se anuncia.",
    lugarId: "auditorio",
  },
];

export function lugaresPlanos() {
  const items = [];
  for (const edificio of EDIFICIOS) {
    items.push({
      id: edificio.id,
      label: edificio.nombre,
      tipo: "edificio",
      edificioId: edificio.id,
      lat: edificio.lat,
      lng: edificio.lng,
    });
    for (const aula of edificio.aulas) {
      items.push({
        id: aula.id,
        label: `${aula.nombre} · ${edificio.nombre}`,
        tipo: "aula",
        edificioId: edificio.id,
        aula: aula.nombre,
        lat: edificio.lat,
        lng: edificio.lng,
      });
    }
  }
  return items;
}

export function findLugar(id) {
  return lugaresPlanos().find((l) => l.id === id) || null;
}