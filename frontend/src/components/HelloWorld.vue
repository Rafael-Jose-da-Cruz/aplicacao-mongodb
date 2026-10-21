<script setup>
import { ref } from "vue";

const count = ref(0);

function handleClick() {
 count.value = count.value + 1; 
}

function handleDiminuir() {
  count.value--;
}

function handleZero(){
  count.value = 0;
}

function handleRadon(){
  count.value = count.value;
}

</script>

<template>
  <h1>{{ count }}</h1>
  <button @click="handleClick">Aumentar</button>
  <button @click="handleDiminuir">Diminuir</button>
  <button @click="handleZero">Zerar</button>
  <button @click="handleRandom">Novo botão</button>
</template>

<style scoped>

</style>
