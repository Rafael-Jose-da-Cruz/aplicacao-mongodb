<script setup>
import { computed, onMounted, ref } from 'vue'
import CharacterCard from '../components/CharacterCard.vue'
import CharacterForm from '../components/CharacterForm.vue'
import { getId, useCharacters } from '../stores/characters'

const {
  personagens,
  carregando,
  erro,
  carregar,
  sincronizar,
  criar,
  atualizar,
  remover,
  alternarStatus: alternarStatusPersonagem,
} = useCharacters()

const buscaTexto = ref('')
const filtroStatus = ref('')
const exibirFormulario = ref(false)
const idEdicao = ref(null)
const novoPersonagem = ref(criarPersonagemVazio())

const personagensFiltrados = computed(() => {
  const busca = buscaTexto.value.trim().toLowerCase()

  return personagens.value.filter((personagem) => {
    const nomeCombina = String(personagem.name || '').toLowerCase().includes(busca)
    const statusCombina = filtroStatus.value ? personagem.status === filtroStatus.value : true
    return nomeCombina && statusCombina
  })
})

function criarPersonagemVazio() {
  return {
    name: '',
    species: '',
    status: 'Alive',
    gender: 'Male',
    image: '',
  }
}

async function carregarPagina() {
  if (personagens.value.length) return

  try {
    await carregar()
  } catch {
   
  }
}

async function atualizarLista() {
  try {
    await carregar()
  } catch {
    
  }
}

async function sincronizarApi() {
  try {
    await sincronizar()
  } catch {
  }
}

async function criarPersonagem() {
  try {
    await criar({
      ...novoPersonagem.value,
      image: novoPersonagem.value.image || null,
    })

    novoPersonagem.value = criarPersonagemVazio()
    exibirFormulario.value = false
  } catch (exception) {
    console.error('Erro ao criar personagem!', exception)
  }
}

function selecionarPersonagem(id) {
  idEdicao.value = id
}

function cancelarEdicao() {
  idEdicao.value = null
}

async function salvarEdicao(dados) {
  try {
    await atualizar(idEdicao.value, dados)
    idEdicao.value = null
  } catch (exception) {
    console.error('Erro ao editar personagem!', exception)
  }
}

async function excluirPersonagem(id) {
  if (!confirm('Deseja mesmo apagar este personagem?')) return

  try {
    await remover(id)
    if (idEdicao.value === id) {
      idEdicao.value = null
    }
  } catch (exception) {
    console.error('Erro ao deletar personagem!', exception)
  }
}

async function alternarStatusRapido(personagem) {
  try {
    await alternarStatusPersonagem(personagem)
  } catch (exception) {
    console.error('Erro ao aplicar o PATCH!', exception)
  }
}

onMounted(carregarPagina)
</script>

<template>
  <section class="pagina-personagens">
    <div class="cabecalho-pagina">
      <div>
        <h1>Personagens</h1>
        <p>Gerencie os personagens do catálogo.</p>
      </div>

      <div class="acoes-pagina">
        <button class="btn-2" :disabled="carregando" @click="atualizarLista">Atualizar</button>
        <button class="btn-1" :disabled="carregando" @click="sincronizarApi">
          Sincronizar com a API
        </button>
        <button class="btn-1" @click="exibirFormulario = !exibirFormulario">
          {{ exibirFormulario ? 'Fechar formulário' : '+ Novo personagem' }}
        </button>
      </div>
    </div>

    <p v-if="erro" class="mensagem-erro">Não foi possível carregar os personagens.</p>

    <div v-if="exibirFormulario" class="criar-personagem">
      <CharacterForm
        v-model="novoPersonagem"
        titulo="Criando personagem"
        @confirmar="criarPersonagem"
        @cancelar="exibirFormulario = false"
      />
    </div>

    <div class="filtros">
      <input v-model="buscaTexto" placeholder="Buscar por nome..." />
      <select v-model="filtroStatus" aria-label="Filtrar por status">
        <option value="">Todos os status</option>
        <option value="Alive">Vivos</option>
        <option value="Dead">Mortos</option>
        <option value="unknown">Desconhecido</option>
      </select>
    </div>

    <p v-if="carregando && !personagens.length" class="mensagem-info">Carregando personagens...</p>

    <ul v-else class="lista-personagens">
      <CharacterCard
        v-for="personagem in personagensFiltrados"
        :key="getId(personagem)"
        v-show="!idEdicao || idEdicao === getId(personagem)"
        :personagem="personagem"
        :editing="idEdicao === getId(personagem)"
        @editar="selecionarPersonagem"
        @salvar="salvarEdicao"
        @cancelar="cancelarEdicao"
        @excluir="excluirPersonagem"
        @alternar-status="alternarStatusRapido"
      />
    </ul>

    <p v-if="!carregando && !personagensFiltrados.length" class="estado-vazio">
      Nenhum personagem encontrado.
    </p>
  </section>
</template>

<style scoped>
.pagina-personagens {
  padding: 24px;
  background-color: #1a232a;
  border: 1px solid var(--cor-primaria);
  border-radius: var(--arredondamento);
}

.cabecalho-pagina {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  margin-bottom: 24px;
}

.cabecalho-pagina h1 {
  margin: 0 0 6px;
}

.cabecalho-pagina p {
  margin: 0;
  color: var(--cor-texto-secundario);
}

.acoes-pagina {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.mensagem-erro {
  color: #ff8a8a;
}

.mensagem-info {
  color: var(--cor-texto-secundario);
}

.criar-personagem {
  max-width: 500px;
  margin: 16px auto 24px;
  padding: 20px;
  background-color: var(--cor-fundo);
  border: 1px solid var(--cor-primaria);
  border-radius: var(--arredondamento);
}

.filtros {
  display: flex;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}

input {
  width: 75%;
}

.filtros input,
.filtros select {
  padding: 10px 12px;
  border: 1px solid #444;
  border-radius: 6px;
  background-color: #121212;
  color: var(--cor-texto-secundario);
  font-family: inherit;
  font-weight: 600;
  outline: none;
}

.filtros input:focus,
.filtros select:focus {
  border-color: var(--cor-primaria);
}

.lista-personagens {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: var(--espacamento);
  width: 100%;
  margin: 0;
  padding: 0;
  list-style: none;
}

.estado-vazio {
  padding: 32px;
  text-align: center;
  color: var(--cor-texto-secundario);
}

@media (max-width: 550px) {
  .pagina-personagens {
    padding: 16px;
  }
}
</style>
