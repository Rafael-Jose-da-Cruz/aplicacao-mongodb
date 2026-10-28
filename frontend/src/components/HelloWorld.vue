<script setup>
import {ref, computed} from "vue";

const count = ref(0);
const text = ref("");
const items = ref([]);
const termoBusca = ref("");
const mensagemErro = ref("");

function handleClick() {
  mensagemErro.value = "";
  if(count.value <10){
    count.value++; 
  } else {
    mensagemErro.value = "Erro! O valor não pode passar de 10!";
  }
}

function handleDiminuir() {
  mensagemErro.value = "";
  if(count.value > 0){
    count.value--;
  } else {
    mensagemErro.value = "Erro! O valor não pode ser menor do que zero!"
  }
}

function handleZero() {
  mensagemErro.value = "";
  count.value = 0;
}

function handleAdicionar(){
  items.value.push(text.value);
  text.value = "";
}

function handleVazio(){
  items.value = [];
}

function handleUltimo(){
  items.value.pop();
}

const itensFiltrados = computed(() => {
  return items.value.filter(item => 
    item.toLowerCase().includes(termoBusca.value.toLowerCase())
  );
});

</script>

<template>
  <div>
    <h2>Olá! Seja bem vindo ao Mongo!</h2>

    <input type="text" v-model="text" placeholder="Digite algo aqui: ">
    <h3>{{ text }}</h3>

    <ul>
      <li v-for="(item, index) in items" :key="index">
        {{ item }}
      </li>
    </ul>
    <button class="btn-1" @click="handleAdicionar">Adicionar</button>
    <button class="btn-2" @click="handleVazio">Zerar lista</button>
    <button class="btn-3" @click="handleUltimo">Remover último</button>


    <h1>{{ count }}</h1>
    <button class="btn-4" @click="handleClick">Aumentar</button>
    <button class="btn-5" @click="handleDiminuir">Diminuir</button>
    <button class="btn-6" @click="handleZero">Zerar valor</button>
    <p style="color: red;" v-if="mensagemErro">{{ mensagemErro }}</p>
    <hr>

    <input type="text" v-model="termoBusca" placeholder="Digite um termo para buscar">
    <h3>Resultados encontrados: </h3>
    <ul>
      <li v-for="(item, index) in itensFiltrados" :key="index">
        {{ item }}
      </li>
    </ul>

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