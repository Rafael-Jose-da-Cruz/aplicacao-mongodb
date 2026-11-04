/**
 * apiService.js
 *
 * Todas as funções retornam Promises do axios (resposta em .data).
 */

/**
 *
 * Tipagem das propriedades dos objetos
 * 
 * @typedef {Object} Character
 * @property {string}   _id        - ID gerado pelo MongoDB
 * @property {string}   name       - Nome do personagem
 * @property {string}   status     - Status (alive, dead, unknown)
 * @property {string}   species    - Espécie
 * @property {string}   image      - URL da imagem
 * @property {string}   createdAt  - Data de criação (ISO string)
 * @property {string}   updatedAt  - Data de atualização (ISO string)
 */

import axios from 'axios'

const API = axios.create({
  baseURL: 'http://localhost:3000/api',
  headers: {
    'Content-Type': 'application/json',
  },
})

API.interceptors.response.use(
  (response) => response,
  (error) => {
    const mensagem =
      error.response?.data?.message ||
      error.message ||
      'Erro inesperado na requisição'
    console.error('[apiService]', error.config?.method?.toUpperCase(),
      error.config?.url, '->', mensagem)
    return Promise.reject(error)
  }
)
/**
 * COMO USAR NOS COMPONENTES
 *
 *   import apiService from '@/services/apiService'
 *
 *   const personagens = ref([])
 *   const carregando = ref(false)
 *   const erro = ref(null)
 *
 *   const listar = async () => {
 *     carregando.value = true
 *     try {
 *       const resposta = await apiService.getCharacters()
 *       personagens.value = resposta.data
 *     } catch (e) {
 *       erro.value = 'Falha ao carregar personagens'
 *       console.error(e)
 *     } finally {
 *       carregando.value = false
 *     }
 *   }
 *
 *   const cadastrar = async (novo) => {
 *     try {
 *       const resposta = await apiService.createCharacter(novo)
 *       personagens.value.push(resposta.data)
 *     } catch (e) {
 *       alert('Não foi possível cadastrar')
 *     }
 *   }
 *
 *   const remover = async (id) => {
 *     await apiService.deleteCharacter(id)
 *     personagens.value = personagens.value.filter(p => p._id !== id)
 *   }
 *
 * Aqui eu to usando o try catch para fazer essa lógica carregamento, porém se quiser faça sem loadding mesmo
 */

const apiService = {
  /**
   * GET /characters - listar todos os personagens
   * @returns {Promise<AxiosResponse<Character[]>>}
   */
  getCharacters: () => API.get('/characters'),

  /**
   * GET /characters/:id - buscar personagem pelo ID
   * @param {string} id - ID do personagem
   * @returns {Promise<AxiosResponse<Character>>}
   */
  getCharacterById: (id) => API.get(`/characters/${id}`),

  /**
   * POST /characters - criar novo personagem
   * @param {Object} character - dados do personagem (name, status, species...)
   * @returns {Promise<AxiosResponse<Character>>}
   */
  createCharacter: (character) => API.post('/characters', character),

  /**
   * PUT /characters/:id - atualizar personagem existente
   * @param {string} id - ID do personagem
   * @param {Object} character - campos a atualizar
   * @returns {Promise<AxiosResponse<Character>>}
   */
  updateCharacter: (id, character) => API.put(`/characters/${id}`, character),

  /**
   * DELETE /characters/:id - deletar personagem
   * @param {string} id - ID do personagem
   * @returns {Promise<AxiosResponse>}
   */
  deleteCharacter: (id) => API.delete(`/characters/${id}`),
}

export default apiService
export { apiService }
