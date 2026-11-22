import { EDIFICIOS, TIPOS as TIPOS_BASE, iconoDe as iconoBase } from './campus.js'
import { LUGARES_EXTRA } from './fotos.js'
import { COORDENADAS } from './coordenadas.js'

export { CAMPUS_CENTER } from './campus.js'

export const TIPOS = {
  ...TIPOS_BASE,
  aula: { label: 'Aulas y laboratorios', color: '#7c3aed', icono: '🏫' },
  oficina: { label: 'Oficinas y departamentos', color: '#1a4fa0', icono: '🗂️' },
  info: { label: 'Información', color: '#0891b2', icono: '🗺️' },
}

export function iconoDe(lugar) {
  if (lugar.icono) return lugar.icono
  const base = iconoBase(lugar)
  return base !== '📍' ? base : TIPOS[lugar.tipo]?.icono || '📍'
}

export function conCoordenadas(lugar, extra = {}) {
  const c = extra[lugar.id] || COORDENADAS[lugar.id]
  return c ? { ...lugar, lat: c[0], lng: c[1] } : lugar
}

export const LUGARES_BASE = [
  ...EDIFICIOS,
  ...LUGARES_EXTRA.filter((x) => !EDIFICIOS.some((e) => e.id === x.id)),
]
export const LUGARES = LUGARES_BASE.map((l) => conCoordenadas(l))
export const tieneCoordenadas = (l) => l.lat != null && l.lng != null

export function lugaresPlanos() {
  const items = []
  for (const base of LUGARES_BASE) {
    const l = conCoordenadas(base)
    items.push({ id: l.id, label: l.nombre, tipo: l.tipo, lat: l.lat, lng: l.lng })
    for (const aula of l.aulas || []) {
      items.push({
        id: aula.id,
        label: `${aula.nombre} · ${l.nombre}`,
        tipo: 'aula',
        aula: aula.nombre,
        lat: l.lat,
        lng: l.lng,
      })
    }
  }
  return items
}
