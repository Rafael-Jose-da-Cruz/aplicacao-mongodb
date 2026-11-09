<script setup>
import { computed, ref, watch } from 'vue'
import CharacterForm from './CharacterForm.vue'

const props = defineProps({
  personagem: {
    type: Object,
    required: true,
  },
  editing: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['editar', 'salvar', 'cancelar', 'excluir', 'alternar-status'])

const id = computed(() => props.personagem.id || props.personagem._id)
const form = ref(criarFormulario())

watch(
  () => props.editing,
  (editing) => {
    if (editing) {
      form.value = criarFormulario()
    }
  },
  { immediate: true },
)

function criarFormulario() {
  return {
    name: props.personagem.name || '',
    species: props.personagem.species || '',
    image: props.personagem.image || '',
    gender: props.personagem.gender || 'unknown',
    status: props.personagem.status || 'unknown',
  }
}

function salvar() {
  emit('salvar', {
    ...form.value,
    image: form.value.image || null,
  })
}
</script>

<template>
  <li :class="{ 'em-edicao': editing }">
    <template v-if="editing">
      <div class="edicao">
        <img v-if="form.image || personagem.image" :src="form.image || personagem.image" :alt="form.name" />

        <div class="formulario-edicao">
          <CharacterForm
            v-model="form"
            :titulo="`Editando: ${personagem.name}`"
            texto-confirmar="Salvar Alterações"
            @confirmar="salvar"
            @cancelar="$emit('cancelar')"
          />
        </div>
      </div>
    </template>

    <template v-else>
      <img v-if="personagem.image" :src="personagem.image" :alt="personagem.name" />
      <div v-else class="sem-imagem">Sem imagem</div>

      <span>
        <strong>{{ personagem.name }}</strong>
        {{ personagem.species }} ({{ personagem.status }})
      </span>

      <div class="buttons-container">
        <button class="btn-2" @click="$emit('editar', id)">Editar</button>
        <button class="btn-3" @click="$emit('excluir', id)">Excluir personagem</button>
        <button class="btn-1" @click="$emit('alternar-status', personagem)">Mudar Status</button>
      </div>
    </template>
  </li>
</template>

<style scoped>

.buttons-container {
  display: flex;
  gap: 5px;
  flex-direction: column;
}

li {
  width: calc(22% - 16px);
  min-width: 220px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  text-align: center;
  background-color: var(--cor-fundo);
  border: 1px solid #333;
  border-radius: var(--arredondamento);
}

li.em-edicao {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  padding: 16px;
  align-items: stretch;
  background-color: #1a232a;
  border: 2px solid var(--cor-primaria);
}

li:not(.em-edicao) img,
.sem-imagem {
  width: 100%;
  max-width: 180px;
  height: 180px;
  margin-bottom: 8px;
  border-radius: 8px;
  background-color: #121212;
}

li:not(.em-edicao) img {
  object-fit: contain;
}

.sem-imagem {
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--cor-texto-secundario);
  font-size: 0.9rem;
}

li span {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 12px;
  color: var(--cor-texto);
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
  border: 2px solid var(--cor-secundaria);
  border-radius: 10px;
}

.formulario-edicao {
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 10px;
  text-align: left;
}

@media (max-width: 900px) {
  li {
    width: calc(50% - var(--espacamento));
  }
}

@media (max-width: 550px) {
  li {
    width: 100%;
  }

  .edicao {
    flex-direction: column;
  }
}
</style>
