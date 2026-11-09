<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <header class="header">
    <RouterLink to="/dashboard" class="marca">
      <span class="portal"></span>
      <span>Catálogo Rick and Morty</span>
    </RouterLink>

    <nav class="navegacao" aria-label="Navegação principal">
      <RouterLink to="/dashboard" class="nav-link">Dashboard</RouterLink>
      <RouterLink to="/characters" class="nav-link">Personagens</RouterLink>
    </nav>
  </header>
</template>

<style scoped>
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
  padding: 16px 24px;
  margin-bottom: 32px;
  background-color: #1a232a;
  border: 1px solid var(--cor-primaria);
  border-radius: var(--arredondamento);
}

.marca {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--cor-primaria);
  font-size: 1.2rem;
  font-weight: 700;
  text-decoration: none;
}

.portal {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background-color: var(--cor-primaria);
  box-shadow: 0 0 16px var(--cor-primaria);
}

.navegacao {
  display: flex;
  gap: 10px;
}

.nav-link {
  padding: 9px 16px;
  border-radius: 8px;
  color: var(--cor-texto-secundario);
  font-weight: 600;
  text-decoration: none;
  transition: background-color 0.2s, color 0.2s;
}

.nav-link:hover {
  color: var(--cor-texto);
  background-color: #24313a;
}

.nav-link.router-link-active {
  color: var(--cor-texto);
  background-color: var(--cor-primaria-escura);
}

@media (max-width: 550px) {
  .header {
    justify-content: center;
    text-align: center;
  }

  .navegacao {
    width: 100%;
    justify-content: center;
  }
}
</style>
