import { ref } from 'vue'
import apiService from '../services/apiService'

const personagens = ref([])
const carregando = ref(false)
const erro = ref(null)

export function getId(personagem) {
  return personagem?.id || personagem?._id
}

async function buscarTodos() {
  const resposta = await apiService.getCharacters({ per_page: 100 })
  personagens.value = resposta.data.data
  return personagens.value
}

async function carregar() {
  carregando.value = true
  erro.value = null

  try {
    return await buscarTodos()
  } catch (exception) {
    erro.value = exception
    console.error('Erro ao carregar personagens!', exception)
    throw exception
  } finally {
    carregando.value = false
  }
}

async function sincronizar() {
  carregando.value = true
  erro.value = null

  try {
    await apiService.syncCharacters()
    return await buscarTodos()
  } catch (exception) {
    erro.value = exception
    console.error('Erro ao sincronizar personagens!', exception)
    throw exception
  } finally {
    carregando.value = false
  }
}

async function criar(personagem) {
  const resposta = await apiService.createCharacter(personagem)
  await carregar()
  return resposta.data.data
}

async function atualizar(id, dados) {
  const resposta = await apiService.patchCharacter(id, dados)
  const atualizado = resposta.data.data || resposta.data
  const index = personagens.value.findIndex((personagem) => getId(personagem) === id)

  if (index !== -1) {
    personagens.value[index] = { ...personagens.value[index], ...atualizado }
  }

  return atualizado
}

async function remover(id) {
  await apiService.deleteCharacter(id)
  personagens.value = personagens.value.filter((personagem) => getId(personagem) !== id)
}

async function alternarStatus(personagem) {
  const novoStatus = personagem.status === 'Alive' ? 'Dead' : 'Alive'
  await apiService.patchCharacter(getId(personagem), { status: novoStatus })
  personagem.status = novoStatus
  return novoStatus
}

export function useCharacters() {
  return {
    personagens,
    carregando,
    erro,
    carregar,
    sincronizar,
    criar,
    atualizar,
    remover,
    alternarStatus,
  }
}
