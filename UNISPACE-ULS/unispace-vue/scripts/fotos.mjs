// Uso:  node scripts/fotos.mjs
//
// Lee la carpeta  fotos-mapa/  (una subcarpeta por lugar, con sus fotos adentro), y:
//  1) copia las fotos a  public/campus/  (achicadas a 1400 px si "sharp" esta instalado)
//  2) escribe  src/data/fotos.js  con las fotos y con los lugares nuevos que no existian en el mapa
//
// El nombre de la subcarpeta es el nombre del lugar: "Aula A6", "Cafeteria", "Lactario"...
// Si el nombre coincide con un lugar que ya existe (Cafeteria, Parqueo, Biblioteca, Entrada,
// Rectoria, Auditorio...) se le agregan las fotos; si no, se crea un lugar nuevo.
// Los lugares nuevos aparecen en la lista; para ponerlos en el mapa usa el "Modo ubicar".

import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { EDIFICIOS } from '../src/data/campus.js'

const raiz = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const origen = path.join(raiz, 'fotos-mapa')
const destino = path.join(raiz, 'public', 'campus')
const EXT = ['.jpg', '.jpeg', '.png', '.webp']

let sharp = null
try { sharp = (await import('sharp')).default } catch { /* sin sharp: solo copia */ }

const limpiar = (t) =>
  t.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')

// nombres de carpeta (completos) que corresponden a lugares que ya existen
const ALIAS = {
  cafetin: 'cafeteria', cafeteria: 'cafeteria',
  parqueo: 'parqueo', estacionamiento: 'parqueo',
  biblioteca: 'biblioteca', capilla: 'capilla', auditorio: 'auditorio', cancha: 'cancha',
  entrada: 'entrada', porton: 'entrada', 'portón': 'entrada',
  rectoria: 'rectoria', jardin: 'areas-verdes', jardines: 'areas-verdes', 'areas-verdes': 'areas-verdes',
}
// para fotos sueltas (sin carpeta) se busca ademas por palabra
const ALIAS_PALABRA = { ...ALIAS, laboratorio: 'lab-comp', lab: 'lab-comp' }

const idsValidos = new Set()
for (const e of EDIFICIOS) { idsValidos.add(e.id); for (const a of e.aulas) idsValidos.add(a.id) }

function idExistente(texto, fuzzy) {
  const base = limpiar(texto)
  if (idsValidos.has(base)) return base
  if (ALIAS[base]) return ALIAS[base]
  if (!fuzzy) return null
  let m = base.match(/aula-?h-?(\d)/); if (m && idsValidos.has(`aula-h${m[1]}`)) return `aula-h${m[1]}`
  m = base.match(/aula-?(\d{3})/); if (m && idsValidos.has(`aula-${m[1]}`)) return `aula-${m[1]}`
  for (const p of base.split('-')) if (ALIAS_PALABRA[p]) return ALIAS_PALABRA[p]
  return null
}

// tildes para los nombres que se muestran
const TILDES = {
  administracion: 'Administración', direccion: 'Dirección', gestion: 'Gestión', computo: 'Cómputo',
  computacion: 'Computación', biologia: 'Biología', quimica: 'Química', psicologia: 'Psicología',
  teologia: 'Teología', colecturia: 'Colecturía', proyeccion: 'Proyección', agroecologica: 'Agroecológica',
  caceta: 'Caseta', cafeteria: 'Cafetería', rectoria: 'Rectoría', educacion: 'Educación',
  academica: 'Académica', multiples: 'Múltiples', ingenieria: 'Ingeniería', informatica: 'Informática',
}
const embellecer = (n) => n.trim().replace(/\s+/g, ' ').split(' ')
  .map((w) => { const k = limpiar(w); return TILDES[k] ? (w[0] === w[0].toLowerCase() ? TILDES[k].toLowerCase() : TILDES[k]) : w })
  .join(' ')

// tipo e icono segun el nombre
function clasificar(nombre) {
  const s = limpiar(nombre)
  const t = (re) => re.test(s)
  let tipo = 'oficina'
  if (t(/mapa/)) tipo = 'info'
  else if (t(/entrada|parqueo|caceta|caseta|vigilancia/)) tipo = 'acceso'
  else if (t(/auditorio/)) tipo = 'evento'
  else if (t(/aula|computo|laboratorio|sala-de-usos/)) tipo = 'aula'
  else if (t(/edificio/)) tipo = 'facultad'
  else if (t(/biblioteca|cafeteria|lactario|banos|copias|mercado|colectur/)) tipo = 'servicio'
  let icono = ''
  if (t(/banos/)) icono = '🚻'
  else if (t(/lactario/)) icono = '🍼'
  else if (t(/mercado/)) icono = '🛒'
  else if (t(/copias/)) icono = '🖨️'
  else if (t(/computo|computacion/)) icono = '💻'
  else if (t(/laboratorio/)) icono = '🔬'
  else if (t(/aula/)) icono = '🏫'
  else if (t(/docentes/)) icono = '👩‍🏫'
  else if (t(/pastoral/)) icono = '⛪'
  else if (t(/caceta|caseta|vigilancia/)) icono = '🛡️'
  else if (t(/colectur/)) icono = '💵'
  else if (t(/mapa/)) icono = '🗺️'
  else if (t(/nuevo-ingreso/)) icono = '🎓'
  else if (t(/edificio/)) icono = '🏢'
  else if (t(/sala-de-usos/)) icono = '🏛️'
  else if (t(/departamento|direccion|relaciones|proyeccion|administracion|rectoria/)) icono = '🗂️'
  return { tipo, icono }
}

if (!fs.existsSync(origen)) {
  console.error('No encuentro la carpeta fotos-mapa en la raiz del proyecto:\n  ' + origen)
  process.exit(1)
}
fs.mkdirSync(destino, { recursive: true })

function recorrer(dir, carpeta = '') {
  const res = []
  for (const e of fs.readdirSync(dir, { withFileTypes: true })) {
    const p = path.join(dir, e.name)
    if (e.isDirectory()) res.push(...recorrer(p, carpeta || e.name))
    else if (EXT.includes(path.extname(e.name).toLowerCase())) res.push({ ruta: p, nombre: e.name, carpeta })
  }
  return res.sort((x, y) => x.ruta.localeCompare(y.ruta, 'es', { numeric: true }))
}

const archivos = recorrer(origen)
if (!archivos.length) {
  console.error('La carpeta fotos-mapa no tiene fotos .jpg, .jpeg, .png o .webp')
  process.exit(1)
}

const fotos = {}
const nuevos = {} // id -> lugar nuevo
const sinLugar = []
const usados = new Set()
const contador = {}

for (const f of archivos) {
  const ext = path.extname(f.nombre).toLowerCase()
  const salidaExt = sharp ? '.jpg' : ext

  let nombre
  if (f.carpeta) {
    contador[f.carpeta] = (contador[f.carpeta] || 0) + 1
    nombre = `${limpiar(f.carpeta) || 'foto'}-${String(contador[f.carpeta]).padStart(2, '0')}`
  } else {
    nombre = limpiar(path.parse(f.nombre).name) || 'foto'
  }
  let final = nombre + salidaExt
  for (let i = 2; usados.has(final); i++) final = `${nombre}-${i}${salidaExt}`
  usados.add(final)

  const hasta = path.join(destino, final)
  if (sharp) await sharp(f.ruta).rotate().resize({ width: 1400, withoutEnlargement: true }).jpeg({ quality: 80 }).toFile(hasta)
  else fs.copyFileSync(f.ruta, hasta)

  let id = null
  if (f.carpeta) {
    id = idExistente(f.carpeta, false)
    if (!id) {
      id = limpiar(f.carpeta)
      if (!nuevos[id]) {
        const nombreBonito = embellecer(f.carpeta)
        nuevos[id] = { id, nombre: nombreBonito, ...clasificar(f.carpeta), descripcion: '', aulas: [], lat: null, lng: null }
      }
    }
  } else {
    id = idExistente(f.nombre.replace(/\.[^.]+$/, ''), true)
  }
  if (id) (fotos[id] ||= []).push('/campus/' + final)
  else sinLugar.push(f.nombre)
}

const lineasFotos = Object.entries(fotos)
  .map(([id, lista]) => `  ${JSON.stringify(id)}: [${lista.map((s) => JSON.stringify(s)).join(', ')}],`).join('\n')
const lineasLugares = Object.values(nuevos)
  .map((l) => '  ' + JSON.stringify(l)).join(',\n')

fs.writeFileSync(path.join(raiz, 'src', 'data', 'fotos.js'), `// ARCHIVO GENERADO por: node scripts/fotos.mjs  (no lo edites a mano)
// Para cambiar fotos o lugares, edita la carpeta fotos-mapa/ y vuelve a ejecutar el script.
export const FOTOS = {
${lineasFotos}
};

// Lugares que salen de las carpetas y no existian antes en el mapa
export const LUGARES_EXTRA = [
${lineasLugares}
];

export function fotosDe(id) {
  return FOTOS[id] || [];
}
`, 'utf8')

// coordenadas.js solo se crea si no existe (ahi guardas las posiciones; no se sobrescribe)
const coordPath = path.join(raiz, 'src', 'data', 'coordenadas.js')
if (!fs.existsSync(coordPath)) {
  fs.writeFileSync(coordPath, '// Posiciones de los lugares: "id": [latitud, longitud]\nexport const COORDENADAS = {};\n', 'utf8')
}

console.log(`\nFotos procesadas: ${archivos.length}  ${sharp ? '(achicadas a 1400 px)' : '(sin achicar; instala sharp)'}`)
console.log(`Lugares con fotos: ${Object.keys(fotos).length}  (${Object.keys(nuevos).length} lugares nuevos)\n`)
for (const [id, lista] of Object.entries(fotos)) console.log(`  ${(nuevos[id] ? '+ ' : '  ') + id.padEnd(46)} ${lista.length} foto(s)`)
if (sinLugar.length) {
  console.log('\nFotos sueltas que no supe a que lugar pertenecen (mételas en una carpeta con el nombre del lugar):')
  for (const f of sinLugar) console.log('  - ' + f)
}
console.log('\nListo. Recarga /mapa con Ctrl + F5.')
