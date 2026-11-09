<script setup>
import {ref, computed, nextTick} from "vue";
import {onMounted} from "vue";
import apiService from "../services/apiService";

const personagens = ref([]);
const novoPersonagem = ref({name: '', species: '', status: 'Alive', gender: 'Male', image: ''});
const idEdicao = ref(null);
const editar = ref({name: '', species: '', status: 'Alive', gender: 'Male', image: ''});
const exibir = ref(false);
const buscaTexto = ref('');
const filtroStatus = ref('');

let graficoEspeciesInstance = null;
let graficoStatusInstance = null;

const totalPersonagens = computed(() => personagens.value.length);

const totalVivos = computed(() => 
  personagens.value.filter(p => p.status === 'Alive').length
);

const totalMortos = computed(() => 
  personagens.value.filter(p => p.status === 'Dead').length
);

const percentualHumanos = computed(() => {
  if (!personagens.value.length) return 0;
  const humanos = personagens.value.filter(p => p.species === 'Human').length;
  return ((humanos / personagens.value.length) * 100).toFixed(1);
});

const topCincoPersonagens = computed(() => {
  return [...personagens.value].slice(0, 5);
});

function atualizarGraficos() {
  nextTick(() => {
    const canvasEspecies = document.getElementById('chartEspecies');
    const canvasStatus = document.getElementById('chartStatus');
    
    if (!canvasEspecies || !canvasStatus) return;

    const especiesQtd = {};
    personagens.value.forEach(p => {
      especiesQtd[p.species] = (especiesQtd[p.species] || 0) + 1;
    });

    if (graficoEspeciesInstance) graficoEspeciesInstance.destroy();
    graficoEspeciesInstance = new Chart(canvasEspecies, {
      type: 'bar',
      data: {
        labels: Object.keys(especiesQtd),
        datasets: [{
          label: 'Quantidade por Espécie',
          data: Object.values(especiesQtd),
          backgroundColor: '#42b883'
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#ffffff' } } },
        scales: {
          x: { ticks: { color: '#ffffff' } },
          y: { ticks: { color: '#ffffff' } }
        }
      }
    });

    const statusQtd = {
      Alive: personagens.value.filter(p => p.status === 'Alive').length,
      Dead: personagens.value.filter(p => p.status === 'Dead').length,
      unknown: personagens.value.filter(p => p.status === 'unknown').length
    };

    if (graficoStatusInstance) graficoStatusInstance.destroy();
    graficoStatusInstance = new Chart(canvasStatus, {
      type: 'doughnut',
      data: {
        labels: Object.keys(statusQtd),
        datasets: [{
          data: Object.values(statusQtd),
          backgroundColor: ['#42b883', '#5c2c2c', '#35495e']
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#ffffff' } } }
      }
    });
  });
}

const personagensFiltrados = computed(() => {
  return personagens.value.filter(p => {
    const nomeBate = p.name.toLowerCase().includes(buscaTexto.value.toLowerCase());
    const statusBate = filtroStatus.value ? p.status === filtroStatus.value : true;
    return nomeBate && statusBate;
  });
});

async function getPersonagens() {
  try {
    await apiService.syncCharacters();
    const resposta = await apiService.getCharacters();
    personagens.value = resposta.data.data;
    atualizarGraficos();
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

async function alternarStatusRapido(personagem) {
  try {
    const id = personagem.id || personagem._id;
    const novoStatus = personagem.status === 'Alive' ? 'Dead' : 'Alive';

    await apiService.patchCharacter(id, { status: novoStatus });
    
    personagem.status = novoStatus;
    atualizarGraficos();
  } catch (erro) {
    console.error("Erro ao aplicar o PATCH!", erro);
  }
}

async function editarPersonagem(){
  try {
    await apiService.updateCharacter(idEdicao.value, editar.value);
    idEdicao.value = null;
    editar.value = { name: '', species: '', status: 'Alive', gender: 'Male', image: '' };
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

</script>

<template>
  <div>
    <h1>Catálogo Rick and Morty</h1>

    
    <h2>Buscar personagens</h2>
    <p>Procure seus personagens favoritos de Rick and Morty!</p>
    <button class="btn-1" @click="getPersonagens">Listar personagens</button>

    <h2>Criar personagem</h2>
    <button v-if="!exibir" class="btn-1" @click="exibir = true">+ Novo personagem</button>

    <div v-if="exibir" class="criar-personagem">
      <p>Criando personagem</p>
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
        <button class="btn-1" @click="criarPersonagem">Confirmar</button>
        <button class="btn-3" @click="exibir = false">Cancelar</button>
      </div>
    </div>

    <div class="filtros" v-if="!exibir" style="display: flex; gap: 10px; justify-content: center; margin-bottom: 16px;">
      <input v-model="buscaTexto" placeholder="Buscar por nome..." />
      <select v-model="filtroStatus">
        <option value="">Todos os status</option>
        <option value="Alive">Vivos</option>
        <option value="Dead">Mortos</option>
        <option value="unknown">Desconhecido</option>
      </select>
    </div>

    <ul v-if="!exibir">
      <li v-for="personagem in personagensFiltrados" :key="personagem.id || personagem._id"
        :class="{ 'em-edicao': idEdicao === (personagem.id || personagem._id) }"
        v-show="!idEdicao || idEdicao === (personagem.id || personagem._id)">

        <template v-if="idEdicao === (personagem.id || personagem._id)">
          <div class="edicao">
            <img :src="editar.image || personagem.image" :alt="editar.name">
            
            <div class="formulario-edicao">
              <h3>Editando: {{ personagem.name }}</h3>
              
              <input v-model="editar.name" placeholder="Nome" />
              <input v-model="editar.species" placeholder="Espécie" />
              <input v-model="editar.image" placeholder="URL da Imagem" />

              <div class="editar-genero">
                <select v-model="editar.gender">
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
              </div>

              <div class="acoes">
                <button class="btn-4" @click="editarPersonagem">Salvar Alterações</button>
                <button class="btn-5" @click="idEdicao = null">Cancelar</button>
              </div>
            </div>
          </div>
        </template>

        <template v-else>
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

          <button class="btn-1" @click="alternarStatusRapido(personagem)">
          Mudar Status
          </button>
        </template>
      </li>
    </ul>
  </div>
  <div class="dashboard" v-if="personagens.length && !exibir && !idEdicao">
  <h2>Painel de Indicadores</h2>
  
  <div class="indicadores-cards">
    <div class="card-indicador">
      <span>Total</span>
      <strong>{{ totalPersonagens }}</strong>
    </div>
    <div class="card-indicador">
      <span>Vivos</span>
      <strong>{{ totalVivos }}</strong>
    </div>
    <div class="card-indicador">
      <span>Mortos</span>
      <strong>{{ totalMortos }}</strong>
    </div>
    <div class="card-indicador">
      <span>% Humanos</span>
      <strong>{{ percentualHumanos }}%</strong>
    </div>
  </div>

  <div class="graficos-container">
    <div class="box-grafico">
      <h3>Personagens por Espécie</h3>
      <canvas id="chartEspecies"></canvas>
    </div>
    <div class="box-grafico">
      <h3>Distribuição de Status</h3>
      <canvas id="chartStatus"></canvas>
    </div>
  </div>

  <div class="ranking-container">
    <h3>Top 5 Destaques</h3>
    <ol>
      <li v-for="p in topCincoPersonagens" :key="p.id || p._id">
        <strong>{{ p.name }}</strong> - {{ p.species }} ({{ p.status }})
      </li>
    </ol>
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

h1{
  text-align: center;
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

/* Garante que a lista aplique o Flexbox corretamente */
ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  gap: var(--espacamento);
  justify-content: center;
  width: 100%;
}

ul > li {
  width: calc(22% - 16px);
  min-width: 220px;
  background-color: var(--cor-fundo);
  border-radius: var(--arredondamento);
  padding: 12px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center; 
  text-align: center;
  border: 1px solid #333;
}

ul > li:not(.em-edicao) img {
  width: 100%;
  max-width: 180px; 
  height: 180px;
  object-fit: contain;
  border-radius: 8px;
  margin-bottom: 8px;
  background-color: #121212;
}

ul > li.em-edicao {
  width: 100% !important;
  max-width: 800px;
  margin: 0 auto;
  background-color: #1a232a;
  border: 2px solid var(--cor-primaria);
  padding: 16px;
  align-items: stretch;
}

@media (max-width: 900px) {
  ul > li {
    width: calc(50% - var(--espacamento));
  }
}

@media (max-width: 550px) {
  ul > li {
    width: 100%;
  }
}

li span {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 12px;
  color: var(--cor-texto);
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

.criar-personagem {
  background-color: #1a232a;
  border: 1px solid var(--cor-primaria);
  border-radius: var(--arredondamento);
  padding: 20px;
  max-width: 500px;
  margin: 16px auto 24px auto;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.criar-personagem p {
  margin: 0 0 6px 0;
  font-weight: bold;
  color: var(--cor-primaria);
  text-align: center;
}

.criar-personagem input,
.criar-personagem select {
  width: 95%;
  padding: 10px 12px;
  border-radius: 6px;
  border: 1px solid #444;
  background-color: #121212;
  color: var(--cor-texto);
}

.criar-personagem input:focus,
.criar-personagem select:focus {
  border-color: var(--cor-primaria);
}

.acoes-form {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 8px;
}

li.em-edicao {
  width: 100% !important;
  max-width: 800px;
  background-color: #1a232a;
  border: 2px solid var(--cor-primaria);
  padding: 16px;
  align-items: stretch;
}

.edicao {
  display: flex;
  flex-direction: row;
  gap: 20px;
  width: 100%;
  align-items: center;
}

.edicao img {
  width: 150px;
  height: 150px;
  object-fit: cover;
  border-radius: 10px;
  border: 2px solid var(--cor-secundaria);
}

.formulario-edicao {
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex: 1;
  text-align: left;
}

.formulario-edicao input {
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid #444;
  background-color: #121212;
}

.editar-genero {
  display: flex;
  gap: 10px;
}

.editar-genero select {
  flex: 1;
  padding: 8px;
  border-radius: 6px;
  border: 1px solid #444;
  background-color: #121212;
  color: var(--cor-texto-secundario);
}

.acoes {
  display: flex;
  gap: 10px;
  margin-top: 6px;
}

.dashboard {
  background-color: #1a232a;
  border-radius: var(--arredondamento);
  padding: 20px;
  margin-bottom: 24px;
  border: 1px solid var(--cor-primaria);
}

.indicadores-cards {
  display: flex;
  gap: 16px;
  justify-content: space-around;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.card-indicador {
  background-color: var(--cor-fundo);
  padding: 16px;
  border-radius: 8px;
  min-width: 130px;
  text-align: center;
  border: 1px solid #333;
}

.card-indicador span {
  display: block;
  color: var(--cor-texto-secundario);
  font-size: 0.9rem;
}

.card-indicador strong {
  font-size: 1.8rem;
  color: var(--cor-primaria);
}

.graficos-container {
  display: flex;
  gap: 20px;
  justify-content: space-between;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.box-grafico {
  flex: 1;
  min-width: 280px;
  background-color: var(--cor-fundo);
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #333;
}

.ranking-container {
  background-color: var(--cor-fundo);
  padding: 16px;
  border-radius: 8px;
  border: 1px solid #333;
  text-align: left;
}

.ranking-container ol {
  padding-left: 20px;
  color: var(--cor-texto);
}

.ranking-container li {
  width: 100%;
  background: none;
  border: none;
  display: list-item;
  text-align: left;
  padding: 4px 0;
}

@media (max-width: 900px) {
  li {
    width: calc(50% - 8px);
  }
}
</style>