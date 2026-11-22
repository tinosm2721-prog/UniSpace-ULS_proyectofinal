// Herramienta para administrar los lugares del mapa desde la terminal.
//
//   node scripts/lugar.mjs lista
//   node scripts/lugar.mjs ubicar  <id> <latitud> <longitud>
//   node scripts/lugar.mjs indicar <id> "Entra por el portón, sigue de frente..."
//   node scripts/lugar.mjs quitar  <id> [<id> ...]
//
// <id> es el identificador que muestra "lista" (ej. cafeteria, aula-a6).

import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { LUGARES_BASE } from '../src/data/lugares.js'
import { LUGARES_EXTRA, FOTOS } from '../src/data/fotos.js'
import { COORDENADAS } from '../src/data/coordenadas.js'
import { INDICACIONES } from '../src/data/indicaciones.js'

const raiz = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..')
const rutaCampus = path.join(raiz, 'src', 'data', 'campus.js')
const rutaCoord = path.join(raiz, 'src', 'data', 'coordenadas.js')
const rutaInd = path.join(raiz, 'src', 'data', 'indicaciones.js')

const [cmd, ...args] = process.argv.slice(2)

function salir(msg) { console.error('\n' + msg + '\n'); process.exit(1) }

function escribirCoordenadas(obj) {
  const filas = Object.entries(obj).map(([id, [a, b]]) => `  ${JSON.stringify(id)}: [${a}, ${b}],`).join('\n')
  fs.writeFileSync(rutaCoord, `// Posiciones de los lugares en el mapa: "id": [latitud, longitud]\n// Se edita con:  node scripts/lugar.mjs ubicar <id> <lat> <lng>   (o con el Modo ubicar del mapa)\nexport const COORDENADAS = {\n${filas}\n};\n`, 'utf8')
}

function escribirIndicaciones(obj) {
  const filas = Object.entries(obj).map(([id, t]) => `  ${JSON.stringify(id)}: ${JSON.stringify(t)},`).join('\n')
  fs.writeFileSync(rutaInd, `// Indicaciones paso a paso para llegar a cada lugar dentro del campus.\n// Se llena desde la terminal:  node scripts/lugar.mjs indicar <id> "texto"\nexport const INDICACIONES = {\n${filas}\n};\n`, 'utf8')
}

function existe(id) {
  const l = LUGARES_BASE.find((x) => x.id === id)
  if (l) return l
  const parecidos = LUGARES_BASE.filter((x) => x.id.includes(id) || id.includes(x.id)).map((x) => x.id)
  salir(`No existe el lugar "${id}".` + (parecidos.length ? `\n¿Quisiste decir: ${parecidos.join(', ')}?` : '') + '\nVer todos con:  node scripts/lugar.mjs lista')
}

switch (cmd) {
  case 'lista': {
    console.log('\n  Posición: ✓ guardada   ~ aproximada de ejemplo (ubícala)   ✗ sin ubicar      Ind.: indicación escrita\n')
    for (const l of LUGARES_BASE) {
      const pos = COORDENADAS[l.id] ? '✓' : l.lat != null ? '~' : '✗'
      const ind = INDICACIONES[l.id] ? 'ind.' : '    '
      console.log(`  ${pos} ${ind}  ${l.id.padEnd(48)} ${l.nombre}`)
    }
    console.log()
    break
  }

  case 'ubicar': {
    const [id, ...resto] = args
    if (!id || resto.length < 1) salir('Uso:  node scripts/lugar.mjs ubicar <id> <latitud> <longitud>\nEjemplo:  node scripts/lugar.mjs ubicar aula-a6 "13.675421, -89.193982"')
    existe(id)
    const nums = resto.join(' ').replace(/,/g, ' ').split(/\s+/).filter(Boolean).map(Number)
    const [lat, lng] = nums
    if (nums.length !== 2 || !Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) {
      salir('Las coordenadas no son válidas. Deben ser dos números, por ejemplo: 13.675421 -89.193982')
    }
    escribirCoordenadas({ ...COORDENADAS, [id]: [lat, lng] })
    console.log(`\n✓ ${id} ubicado en ${lat}, ${lng}\n`)
    break
  }

  case 'indicar': {
    const [id, ...texto] = args
    if (!id || !texto.length) salir('Uso:  node scripts/lugar.mjs indicar <id> "Entra por el portón y sigue de frente..."')
    existe(id)
    escribirIndicaciones({ ...INDICACIONES, [id]: texto.join(' ').trim() })
    console.log(`\n✓ Indicación guardada para ${id}\n`)
    break
  }

  case 'quitar': {
    if (!args.length) salir('Uso:  node scripts/lugar.mjs quitar <id> [<id> ...]   Ejemplo: node scripts/lugar.mjs quitar capilla cancha')
    let campus = fs.readFileSync(rutaCampus, 'utf8')
    const coords = { ...COORDENADAS }
    const inds = { ...INDICACIONES }
    for (const id of args) {
      const l = existe(id)
      const bloque = new RegExp(`\\r?\\n  \\{\\r?\\n    id: "${id}",[\\s\\S]*?\\r?\\n  \\},`)
      if (bloque.test(campus)) {
        campus = campus.replace(bloque, '')
        console.log(`✓ Quitado: ${l.nombre}`)
        if (campus.includes(`lugarId: "${id}"`)) console.log(`  ! Ojo: el Tour todavía usa "${id}" en TOUR_PASOS (src/data/campus.js). Quita ese paso o el tour mostrará otro lugar.`)
        if (FOTOS[id]) console.log(`  ! Tenía fotos: borra su carpeta dentro de fotos-mapa y ejecuta  node scripts/fotos.mjs`)
      } else if (LUGARES_EXTRA.some((x) => x.id === id)) {
        console.log(`• ${l.nombre} viene de una carpeta de fotos: borra su carpeta dentro de fotos-mapa y ejecuta  node scripts/fotos.mjs`)
      }
      delete coords[id]
      delete inds[id]
    }
    fs.writeFileSync(rutaCampus, campus, 'utf8')
    escribirCoordenadas(coords)
    escribirIndicaciones(inds)
    console.log('\nListo. Recarga /mapa con Ctrl + F5.\n')
    break
  }

  default:
    console.log(`
Uso:
  node scripts/lugar.mjs lista
  node scripts/lugar.mjs ubicar  <id> <latitud> <longitud>
  node scripts/lugar.mjs indicar <id> "texto paso a paso"
  node scripts/lugar.mjs quitar  <id> [<id> ...]
`)
}
