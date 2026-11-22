// Carga las publicaciones de la página de Facebook de la ULS en server/facebook.json
// Uso: node scripts/fb-cargar.mjs
// OJO: reemplaza el contenido actual de server/facebook.json (antes hace una copia .bak).
import fs from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const RAIZ = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const ARCHIVO = path.join(RAIZ, "server", "facebook.json");
const PAGINA = "https://www.facebook.com/ULuterana";

const posts = [
  // ---------- 19 de octubre ----------
  {
    fecha: "2026-10-19",
    vence: "2026-10-21",
    texto:
      "Convocatoria #YoSoyULS: el Departamento de Ciencias Jurídicas invita a formar parte del Comité de Estudiantes de Derecho. Hay tres comisiones: Educación y Cultura, Deporte y Desarrollo Comunitario. Fecha límite de inscripción: 21 de octubre. Requisitos: ser estudiante activo de Ciencias Jurídicas de la ULS, mostrar interés en formar parte y tener disponibilidad para reuniones y actividades. Proceso: entregar en el Departamento de la Licenciatura en Ciencias Jurídicas copia de DUI, copia de carnet estudiantil y hoja de inscripción. También se puede solicitar la inscripción con el código QR del afiche.",
  },
  {
    fecha: "2026-10-19",
    texto:
      "#YoSoyULS: ¡Tu futuro profesional puede llevarte hasta España! Becas Alumni de la Universidad de Navarra, España. Más información en www.indesgua.org.gt/espana-becas-alumni-universidad-de-navarra/",
  },
  {
    fecha: "2026-10-19",
    texto:
      "Datos de contacto de la Universidad Luterana Salvadoreña: teléfono 2133 2600, correo uls@uls.edu.sv, sitio web uls.edu.sv, ubicada en la Autopista al Aeropuerto El Salvador, San Salvador.",
  },

  // ---------- 14 de octubre ----------
  {
    fecha: "2026-10-14",
    vence: "2026-10-24",
    texto:
      "Curso de Microsoft Excel Avanzado de la ULS. Aprende herramientas y funciones avanzadas para trabajar de forma más eficiente. Curso 100 % virtual por Microsoft Teams. Inicia el sábado 24 de octubre y finaliza el domingo 15 de noviembre. Días: sábado y domingo, de 6:00 p. m. a 8:00 p. m. Inversión: $60.00 (pago único), con descuento especial para docentes, estudiantes, egresados y graduados ULS. Facilitador: José Neftalí Monjarás (Licenciado en Administración de Empresas, Máster en Big Data y Business Analytics, Máster en Project Management). Más información: posgrado@uls.edu.sv, abigail.ramos@uls.edu.sv, teléfonos 2133-2609 y 2133-2619.",
  },
  {
    fecha: "2026-10-14",
    vence: "2026-11-14",
    texto:
      "Campaña de Recolección de Víveres, por cuarto año consecutivo, impulsada por la Coordinación de Trabajo Social y la cátedra de Adulto Mayor y Sociedad, para contribuir al bienestar de personas adultas mayores. Se puede donar hasta el 14 de noviembre de 2026. Punto de recolección: Coordinación de Trabajo Social, Edificio A de la ULS. Productos que se pueden donar: frijoles, arroz, pasta, maseca, azúcar, leche en polvo, aceite de cocina, café, miel de abeja, galletas, salsa de tomate, gelatina, atol, sal, cereal, pasta dental, cepillos de dientes y papel higiénico. Participan estudiantes de las asignaturas de Ética, Inglés y Sociología.",
  },
  {
    fecha: "2026-10-14",
    texto:
      "Oferta académica y matrícula del ciclo 01-2027. La ULS promueve estas carreras: Licenciatura en Contaduría Pública, Licenciatura en Psicología, Licenciatura en Idioma Inglés, Licenciatura en Ciencias Jurídicas, Licenciatura en Administración de Empresas, Ingeniería Agroecológica y Técnico en Ingeniería Agroecológica. Respuesta oficial de la ULS en Facebook: el proceso de matrícula para el ciclo 01-2027 ya está abierto y se puede matricular hasta enero.",
  },
  {
    fecha: "2026-10-14",
    texto:
      "Licenciatura en Contaduría Pública, ciclo 01-2027. Para quien le gustan los números, el análisis y la organización. Aprendes a analizar, registrar y tomar decisiones financieras; desarrollas habilidades para liderar y gestionar con responsabilidad; construyes un futuro con oportunidades en el mundo laboral. Docentes con experiencia en el campo profesional.",
  },
  {
    fecha: "2026-10-14",
    texto:
      "Licenciatura en Psicología, ciclo 01-2027. Para quien quiere comprender las emociones, los pensamientos y el comportamiento humano. Ejes: salud mental y bienestar, ciencia para la vida, acompañamiento y desarrollo humano, compromiso social y transformación.",
  },
  {
    fecha: "2026-10-14",
    texto:
      "Licenciatura en Idioma Inglés, ciclo 01-2027. Para quien le apasionan los idiomas, la comunicación y las nuevas culturas. Ejes: dominio del idioma inglés, comunicación intercultural, oportunidades académicas y profesionales.",
  },
  {
    fecha: "2026-10-14",
    texto:
      "Licenciatura en Ciencias Jurídicas, ciclo 01-2027. Para quien quiere defender ideas, analizar situaciones y comprender cómo las leyes influyen en la sociedad. Ejes: formación jurídica integral, justicia y derechos humanos, análisis e interpretación de las leyes, compromiso con la transformación social.",
  },
  {
    fecha: "2026-10-14",
    texto:
      "Licenciatura en Administración de Empresas, ciclo 01-2027. Para quien sueña con dirigir proyectos, emprender y contribuir al crecimiento de las organizaciones. Desarrolla habilidades de liderazgo, toma decisiones estratégicas, gestiona recursos y equipos y te prepara para un mundo global.",
  },
  {
    fecha: "2026-10-14",
    texto:
      "Ingeniería Agroecológica y Técnico en Ingeniería Agroecológica, ciclo 01-2027. Para quien se interesa en el campo, la producción agrícola, la innovación agrícola y el cuidado de los recursos naturales. Ejes: sostenibilidad, innovación en el campo, desarrollo rural y compromiso social.",
  },

  // ---------- 13 de octubre ----------
  {
    fecha: "2026-10-13",
    vence: "2026-11-15",
    texto:
      "Matrimonios Civiles Colectivos en la ULS. Invitación a las parejas interesadas en contraer matrimonio civil. Requisitos: ser mayor de 18 años; no estar casado/a actualmente; certificación de partida de nacimiento con fecha de expedición no mayor a un mes y medio; DUI vigente de ambos contrayentes, ampliado al 150 %; DUI vigente de dos testigos, ampliado al 150 %. Fecha límite de inscripción: 15 de noviembre. Fecha de la ceremonia: 5 de diciembre, a las 9:30 a. m., en el Auditorio 2 de la ULS. Preinscripción escaneando el código QR del afiche. Información e inscripciones: krisia.morales@uls.edu.sv, teléfono 2133-2608.",
  },
  {
    fecha: "2026-10-13",
    texto:
      "Estudiantes de la Licenciatura en Administración de Empresas vivieron una jornada en Barra de Santiago, donde conocieron el valor del turismo sostenible y la importancia de proteger los recursos naturales. Participaron en la liberación de tortugas marinas. Los detalles están en la nota web de la ULS (uls.edu.sv, 'Entre manglares y tortugas').",
  },
  {
    fecha: "2026-10-13",
    vence: "2026-10-20",
    texto:
      "Primeras Olimpiadas Pedagógicas Iberoamericanas, 'Unidos por la educación': las competencias comenzaron el martes 13 de octubre, con equipos de Guatemala, El Salvador, Portugal, España, México, Ecuador y Honduras. Próximas competencias: miércoles 14 de octubre, competencia de video de TikTok; lunes 19 de octubre, competencia de cálculo mental; martes 20 de octubre, competencia de estudio de casos. Se transmiten en vivo por Facebook, YouTube y TikTok a la 1:00 p. m.",
  },
  {
    fecha: "2026-10-13",
    texto:
      "La ULS participó en la quinta sesión del II Seminario Internacional de Estudios Multidisciplinarios sobre Infancias y Adolescencias, un encuentro académico con instituciones de México y El Salvador sobre neuroeducación y diversidades, con énfasis en el espectro autista. En representación de la ULS, el maestro Rodrigo Colorado compartió aportes sobre neurodivergencia y educación inclusiva.",
  },

  // ---------- 12 de octubre ----------
  {
    fecha: "2026-10-12",
    vence: "2026-10-14",
    texto:
      "Estudiantes de las licenciaturas en Psicología e Idioma Inglés representan a la ULS en las Primeras Olimpiadas Pedagógicas Iberoamericanas. El 30 % de la calificación final depende de los likes y comentarios en sus videos de TikTok, y el conteo finalizaba el miércoles 14 de octubre.",
  },
];

// Copia de seguridad
if (fs.existsSync(ARCHIVO)) {
  fs.copyFileSync(ARCHIVO, ARCHIVO + ".bak");
}

const final = posts
  .map((p, i) => ({
    id: "fb-" + String(i + 1).padStart(3, "0"),
    fecha: p.fecha,
    ...(p.vence ? { vence: p.vence } : {}),
    texto: p.texto,
    link: p.link || PAGINA,
  }))
  .sort((a, b) => b.fecha.localeCompare(a.fecha) || a.id.localeCompare(b.id));

fs.mkdirSync(path.dirname(ARCHIVO), { recursive: true });
fs.writeFileSync(ARCHIVO, JSON.stringify(final, null, 2) + "\n", "utf8");
console.log(`\n✓ ${final.length} publicaciones guardadas en server/facebook.json\n`);
