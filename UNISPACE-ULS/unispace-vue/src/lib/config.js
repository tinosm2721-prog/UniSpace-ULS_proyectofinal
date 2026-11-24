export const API_URL = import.meta.env.VITE_API_URL;

// Servidor Node (chatbot y objetos perdidos). Vacío = mismo origen (desarrollo con el proxy de Vite).
export const NODE_API_URL = (import.meta.env.VITE_NODE_API_URL || "").replace(/\/$/, "");

// Encabezado con el token guardado en el navegador, para llamar al servidor Node desde otro dominio.
export function authHeaders() {
  try {
    const token = localStorage.getItem("token");
    return token ? { Authorization: `Bearer ${token}` } : {};
  } catch {
    return {};
  }
}
