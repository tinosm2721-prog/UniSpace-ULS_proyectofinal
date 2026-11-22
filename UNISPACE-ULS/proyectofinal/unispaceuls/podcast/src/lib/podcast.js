import api from "./api";

/**
 * @typedef {Object} Podcast
 * @property {string} tema
 * @property {string} titulo
 * @property {string} descripcion
 * @property {string} contenido
 * @property {string} user_id
 */

/**
 * @returns {Promise<Podcast[]>}
 */
export async function getPodcast() {
  const response = await api.get("/podcast");
  return response.data;
}

/**
 * @param {string} id
 * @returns {Promise<Podcast>}
 */
export async function getPodcastById(id) {
  const response = await api.get(`/podcast/${id}`);
  return response.data;
}

/**
 * @param {Partial<Podcast>} data
 * @returns {Promise<Podcast>}
 */
export async function createPodcast(data) {
  try {
    const response = await api.post("/podcast", data);
    return response.data;
  } catch (error) {
    throw new Error(
      error.response?.data?.message ||
      "Error al crear el podcast"
    );
  }
}

/**
 * @param {string} id
 * @param {Partial<Podcast>} data
 * @returns {Promise<Podcast>}
 */
export async function updatePodcast(id, data) {
  const formData = data instanceof FormData ? data : new FormData();
  if (!(data instanceof FormData)) {
    for (const [key, value] of Object.entries(data || {})) {
      if (value !== undefined && value !== null) formData.append(key, value);
    }
  }
  formData.append("_method", "PUT");
  const response = await api.post(`/podcast/${id}`, formData);
  return response.data;
}

export async function deletePodcast(id) {
  const response = await api.delete(`/podcast/${id}`);
  return response.data;
}

// Aliases en español para compatibilidad
export const actualizarPodcast = updatePodcast;
export const eliminarPodcast = deletePodcast;