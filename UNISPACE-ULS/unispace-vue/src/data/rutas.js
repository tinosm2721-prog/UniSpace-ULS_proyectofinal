// Cálculos simples sobre coordenadas (distancia, rumbo, lugares cercanos).
const R = 6371000
const rad = (g) => (g * Math.PI) / 180

export function distanciaM(a, b) {
  const dLat = rad(b.lat - a.lat)
  const dLng = rad(b.lng - a.lng)
  const h = Math.sin(dLat / 2) ** 2 + Math.cos(rad(a.lat)) * Math.cos(rad(b.lat)) * Math.sin(dLng / 2) ** 2
  return 2 * R * Math.asin(Math.sqrt(h))
}

export function rumboGrados(a, b) {
  const y = Math.sin(rad(b.lng - a.lng)) * Math.cos(rad(b.lat))
  const x = Math.cos(rad(a.lat)) * Math.sin(rad(b.lat)) - Math.sin(rad(a.lat)) * Math.cos(rad(b.lat)) * Math.cos(rad(b.lng - a.lng))
  return ((Math.atan2(y, x) * 180) / Math.PI + 360) % 360
}

const RUMBOS = ['norte', 'noreste', 'este', 'sureste', 'sur', 'suroeste', 'oeste', 'noroeste']
export const nombreRumbo = (grados) => RUMBOS[Math.round(grados / 45) % 8]

// caminando ~1.2 m/s (72 m por minuto)
export const minutosAPie = (metros) => Math.max(1, Math.round(metros / 72))

export function cercanos(destino, lugares, excluir = [], max = 3, radioM = 90) {
  return lugares
    .filter((l) => l.lat != null && l.lng != null && l.id !== destino.id && !excluir.includes(l.id))
    .map((l) => ({ lugar: l, metros: Math.round(distanciaM(destino, l)) }))
    .filter((x) => x.metros <= radioM)
    .sort((x, y) => x.metros - y.metros)
    .slice(0, max)
}
