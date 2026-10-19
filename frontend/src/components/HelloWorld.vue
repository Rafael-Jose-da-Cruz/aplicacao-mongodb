<script setup>
import { ref } from "vue";

const count = ref(0);

function handleClick() {
 count.value = count.value + 1; 
}

</script>

<template>
  <h1>{{ count }}</h1>
  <button @click="handleClick">Aumentar</button>
</template>

<style scoped>

</style>
