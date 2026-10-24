<script setup>
import { ref } from "vue";

const count = ref(0);
const text = ref("");
const items = ref([])

function handleClick() {
  count.value = count.value + 1; 
}

function handleDiminuir() {
  count.value--;
}

function handleZero() {
  count.value = 0;
}

function handleAdicionar(){
  items.value.push(text.value);
  text.value = "";
}

function handleVazio(){
  items.value = [];
}

</script>

<template>
  <div>
    <h2>Olá! Seja bem vindo ao Mongo!</h2>

    <input type="text" v-model="text" placeholder="Digite  algo aqui: ">
    <h3>{{ text }}</h3>

    <ul>
      <li v-for="(item, index) in items" :key="index">
        {{ item }}
      </li>
    </ul>
    <button @click="handleAdicionar">Adicionar</button>
    <button @click="handleVazio">Zerar lista</button>


    <h1>{{ count }}</h1>
    <button @click="handleClick">Aumentar</button>
    <button @click="handleDiminuir">Diminuir</button>
    <button @click="handleZero">Zerar valor</button>

  </div>
</template> 

<style scoped>

</style>