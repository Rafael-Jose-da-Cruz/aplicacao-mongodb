<script setup>
import {ref, computed} from "vue";
import {onMounted} from "vue";
import apiService from "../services/apiService";

const personagens = ref([]);
const novoPersonagem = ref({name: '', species: '', status: 'Alive', gender: 'Male', image: ''});
const idEdicao = ref(null);
const editar = ref({name: '', species: '', status: 'Alive', gender: 'Male', image: ''});
const exibir = ref(false);

async function getPersonagens() {
  try {
    await apiService.syncCharacters();
    const resposta = await apiService.getCharacters();
    personagens.value = resposta.data.data;
  } catch (erro) {
    console.error("Calma veloz!", erro);
  }
}

async function criarPersonagem(){
  try {
    await apiService.createCharacter(novoPersonagem.value);
    novoPersonagem.value = { name: '', species: '', status: 'Alive', gender: 'Male', image: '' };
    exibir.value = false;
    await getPersonagens();
  } catch (erro) {
    console.log("Erro ao criar personagem!", erro);
  }
}

async function selecionarPersonagem(id){
  try{
    const resposta = await apiService.getCharacterById(id);
    idEdicao.value = id;
    editar.value = resposta.data.data || resposta.data;
  } catch (erro) {
    console.log("Erro ao selecionar personagem", erro);
  }
}

async function editarPersonagem(){
  try {
    await apiService.updateCharacter(idEdicao.value, editar.value);
    idEdicao.value = null;
    novoPersonagem.value = { name: '', species: '', status: 'Alive', gender: 'Male', image: '' };
    await getPersonagens();
  } catch(erro) {
    console.log("Erro ao editar personagem!", erro);
  }
}

async function deletarPersonagem(id) {
  if (!confirm("Deseja mesmo apagar este personagem?")) return;
  try {
    await apiService.deleteCharacter(id);
    await getPersonagens();
  } catch (erro) {
    console.log("Erro ao deletar personagem!", erro);
  }
}

/*onMounted(() => {
  carregarPersonagens();
})
  */

</script>

<template>
  <div>
    <h2>Catálogo Rick and Morty</h2>

    <h3>Buscar personagens</h3>
    <button class="btn-1" @click="getPersonagens">Botão get</button>
    <ul>
      <li v-for="personagem in personagens" :key="personagem.id || personagem._id">
        <img :src="personagem.image" :alt="personagem.name">
        <span>
      <strong>{{ personagem.name }}</strong> {{ personagem.species }} ({{ personagem.status }})
    </span>
      <button class="btn-2" @click="selecionarPersonagem(personagem.id || personagem._id)">
      Editar
      </button>

      <button class="btn-3" @click="deletarPersonagem(personagem.id || personagem._id)">
      Excluir personagem
      </button>
      </li>
    </ul>

    <h2>Criar personagem</h2>
    <button v-if="!exibir" class="btn-1" @click="exibir = true">+ Novo personagem</button>

    <div v-if="exibir" class="criar-personagem">
      <h3>Criando personagem</h3>
      <input v-model="novoPersonagem.name" placeholder="Digite o nome">
      <input v-model="novoPersonagem.species" placeholder="Digite a espécie">
      <input v-model="novoPersonagem.image" placeholder="Digite a URL da imagem">
      <select v-model="novoPersonagem.gender">
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Genderless">Genderless</option>
        <option value="unknown">unknown</option>
      </select>
      <select v-model="novoPersonagem.status">
        <option value="Alive">Alive</option>
        <option value="Dead">Dead</option>
        <option value="unknown">unknown</option>
      </select>
      <div class="acoes-form">
        <button class="btn-2" @click="criarPersonagem">Confirmar</button>
        <button class="btn-3" @click="exibir = false">Cancelar</button>
      </div>
    </div>


    <div v-if="idEdicao" class="atualizar-personagem">
      <h3>Editar personagem</h3>
      <input v-model="editar.name" placeholder="Nome" />
      <input v-model="editar.species" placeholder="Espécie" />
      <input v-model="editar.image" placeholder="URL da Imagem" />
      <select v-model="novoPersonagem.gender">
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Genderless">Genderless</option>
        <option value="unknown">unknown</option>
      </select>
      <select v-model="editar.status">
        <option value="Alive">Alive</option>
        <option value="Dead">Dead</option>
        <option value="unknown">unknown</option>
      </select>
      <button class="btn-4" @click="editarPersonagem">Salvar Alterações</button>
      <button class="btn-5" @click="idEdicao = null">Cancelar</button>
    </div>

  </div>
</template> 

<style scoped>
div{
  --cor-primaria: #42b883;
  --cor-secundaria: #35495e;
  --cor-primaria-escura: #338d63;
  --cor-destaque: #5c2c2c;
  --cor-fundo: #242424;          
  --cor-texto: #ffffff;
  --cor-texto-secundario: #a0aec0;
  --espacamento: 16px;
  --arredondamento: 12px;

  box-sizing: border-box;
}

h2, h3{
  color: var(--cor-primaria);
}

button {
  padding: 10px 16px;
  margin: 6px 4px;
  border-radius: 8px;
  border: none;
  font-weight: bold;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.1s;
}

.btn-1, .btn-4{
background-color: var(--cor-primaria-escura);
color: var(--cor-texto);
}

.btn-2, .btn-5{
background-color: var(--cor-secundaria);
color: var(--cor-texto);
}

.btn-3, .btn-6{
background-color: var(--cor-destaque);
color: var(--cor-texto);
}

input{
  color: var(--cor-texto-secundario);
  font-weight: bold;
  outline: none;
}
</style>