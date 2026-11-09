<script setup>
defineProps({
  titulo: {
    type: String,
    default: '',
  },
  textoConfirmar: {
    type: String,
    default: 'Confirmar',
  },
})

const form = defineModel({ type: Object, required: true })

defineEmits(['confirmar', 'cancelar'])
</script>

<template>
  <form class="character-form" @submit.prevent="$emit('confirmar')">
    <p v-if="titulo" class="form-titulo">{{ titulo }}</p>

    <input v-model="form.name" placeholder="Digite o nome" required />
    <input v-model="form.species" placeholder="Digite a espécie" required />
    <input v-model="form.image" placeholder="Digite a URL da imagem" />

    <div class="form-selects">
      <select v-model="form.gender" aria-label="Gênero">
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Genderless">Genderless</option>
        <option value="unknown">unknown</option>
      </select>

      <select v-model="form.status" aria-label="Status">
        <option value="Alive">Alive</option>
        <option value="Dead">Dead</option>
        <option value="unknown">unknown</option>
      </select>
    </div>

    <div class="acoes-form">
      <button type="submit" class="btn-1">{{ textoConfirmar }}</button>
      <button type="button" class="btn-3" @click="$emit('cancelar')">Cancelar</button>
    </div>
  </form>
</template>

<style scoped>
.character-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
  width: 100%;
}

.form-titulo {
  margin: 0 0 6px;
  color: var(--cor-primaria);
  font-weight: 700;
  text-align: center;
}

input,
select {
  width: 100%;
  box-sizing: border-box;
  padding: 10px 12px;
  border: 1px solid #444;
  border-radius: 6px;
  background-color: #121212;
  color: var(--cor-texto);
  font-family: inherit;
  outline: none;
}

input:focus,
select:focus {
  border-color: var(--cor-primaria);
}

.form-selects {
  display: flex;
  gap: 10px;
}

.form-selects select {
  flex: 1;
}

.acoes-form {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 8px;
}
</style>
