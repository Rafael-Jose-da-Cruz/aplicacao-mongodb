<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import Chart from 'chart.js/auto'
import { getId, useCharacters } from '../stores/characters'

const { personagens, carregando, erro, carregar, sincronizar } = useCharacters()

const canvasEspecies = ref(null)
const canvasStatus = ref(null)

let graficoEspecies = null
let graficoStatus = null

const acaoAtual = ref(null)
const ultimaAtualizacao = ref(null)

const totalPersonagens = computed(() => personagens.value.length)
const totalVivos = computed(() => personagens.value.filter((p) => p.status === 'Alive').length)
const totalMortos = computed(() => personagens.value.filter((p) => p.status === 'Dead').length)
const totalHumanos = computed(() => personagens.value.filter((p) => p.species === 'Human').length)

function percentual(valor) {
  if (!personagens.value.length) return '0.0'
  return ((valor / personagens.value.length) * 100).toFixed(1)
}

const percentualVivos = computed(() => percentual(totalVivos.value))
const percentualMortos = computed(() => percentual(totalMortos.value))
const percentualHumanos = computed(() => percentual(totalHumanos.value))

const especiesOrdenadas = computed(() => {
  const contagem = personagens.value.reduce((acc, personagem) => {
    const especie = personagem.species || 'Não informado'
    acc[especie] = (acc[especie] || 0) + 1
    return acc
  }, {})

  const total = personagens.value.length || 1

  return Object.entries(contagem)
    .map(([especie, quantidade]) => ({
      especie,
      quantidade,
      percentual: ((quantidade / total) * 100).toFixed(1),
    }))
    .sort((a, b) => b.quantidade - a.quantidade)
})

const totalEspecies = computed(() => especiesOrdenadas.value.length)

const especiesGrafico = computed(() => {
  const lista = especiesOrdenadas.value

  if (lista.length <= 8) return lista

  const top = lista.slice(0, 8)
  const outras = lista
    .slice(8)
    .reduce((soma, item) => soma + item.quantidade, 0)

  return [...top, { especie: 'Outras', quantidade: outras, percentual: percentual(outras) }]
})

const statusContagem = computed(() => [
  { chave: 'Alive', label: 'Vivos', quantidade: totalVivos.value, cor: '#42b883' },
  { chave: 'Dead', label: 'Mortos', quantidade: totalMortos.value, cor: '#e06c75' },
  {
    chave: 'unknown',
    label: 'Desconhecidos',
    quantidade: personagens.value.filter((p) => p.status === 'unknown').length,
    cor: '#6b7c8f',
  },
])

const statusGrafico = computed(() => statusContagem.value.filter((item) => item.quantidade > 0))

const topCincoPersonagens = computed(() => {
  return [...personagens.value]
    .sort((a, b) => (b.episode_count || 0) - (a.episode_count || 0))
    .slice(0, 5)
})

const maxEpisodios = computed(() => {
  const maior = Math.max(...personagens.value.map((p) => p.episode_count || 0), 0)
  return maior || 1
})

function percentualEpisodios(personagem) {
  const valor = ((personagem.episode_count || 0) / maxEpisodios.value) * 100
  return `${Math.max(valor, 4)}%`
}

function rotuloStatus(status) {
  return { Alive: 'Vivo', Dead: 'Morto', unknown: 'Desconhecido' }[status] || status
}

function classeStatus(status) {
  return { Alive: 'alive', Dead: 'dead', unknown: 'unknown' }[status] || 'unknown'
}

const ultimaAtualizacaoFormatada = computed(() => {
  if (!ultimaAtualizacao.value) return ''
  return ultimaAtualizacao.value.toLocaleTimeString('pt-BR', {
    hour: '2-digit',
    minute: '2-digit',
  })
})

const textoCarregando = computed(() =>
  acaoAtual.value === 'sincronizar' ? 'Sincronizando…' : 'Atualizando…',
)

const pluginCentroDonut = {
  id: 'centroDonut',
  afterDraw(chart) {
    if (chart.config.type !== 'doughnut') return

    const { ctx, chartArea } = chart
    if (!chartArea) return

    const total = (chart.data.datasets[0]?.data || []).reduce(
      (soma, valor) => soma + (Number(valor) || 0),
      0,
    )
    const x = (chartArea.left + chartArea.right) / 2
    const y = (chartArea.top + chartArea.bottom) / 2

    ctx.save()
    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    ctx.fillStyle = '#ffffff'
    ctx.font = '700 30px system-ui, sans-serif'
    ctx.fillText(String(total), x, y - 10)
    ctx.fillStyle = '#a0aec0'
    ctx.font = '600 11px system-ui, sans-serif'
    ctx.fillText('PERSONAGENS', x, y + 16)
    ctx.restore()
  },
}

function renderizarGraficos() {
  if (!canvasEspecies.value || !canvasStatus.value) return

  graficoEspecies?.destroy()
  graficoEspecies = null
  graficoStatus?.destroy()
  graficoStatus = null

  graficoEspecies = new Chart(canvasEspecies.value, {
    type: 'bar',
    data: {
      labels: especiesGrafico.value.map((item) => item.especie),
      datasets: [
        {
          label: 'Personagens',
          data: especiesGrafico.value.map((item) => item.quantidade),
          backgroundColor: 'rgba(66, 184, 131, 0.85)',
          hoverBackgroundColor: '#5ee0a6',
          borderRadius: 6,
          maxBarThickness: 44,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 450 },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (contexto) => ` ${contexto.parsed.y} personagem(ns)`,
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#a0aec0', autoSkip: false, maxRotation: 45, font: { size: 11 } },
        },
        y: {
          beginAtZero: true,
          ticks: { color: '#a0aec0', precision: 0 },
          grid: { color: 'rgba(255, 255, 255, 0.07)' },
        },
      },
    },
  })

  graficoStatus = new Chart(canvasStatus.value, {
    type: 'doughnut',
    data: {
      labels: statusGrafico.value.map((item) => item.label),
      datasets: [
        {
          data: statusGrafico.value.map((item) => item.quantidade),
          backgroundColor: statusGrafico.value.map((item) => item.cor),
          borderColor: '#1a232a',
          borderWidth: 3,
          hoverOffset: 6,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      animation: { duration: 450 },
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#a0aec0',
            usePointStyle: true,
            pointStyle: 'circle',
            boxWidth: 8,
            padding: 16,
            font: { size: 12 },
          },
        },
      },
    },
    plugins: [pluginCentroDonut],
  })
}

function agendarGraficos() {
  nextTick(renderizarGraficos)
}

async function atualizar() {
  acaoAtual.value = 'carregar'

  try {
    await carregar()
    ultimaAtualizacao.value = new Date()
  } catch {
    // O erro já é exibido pela store.
  } finally {
    acaoAtual.value = null
  }
}

async function sincronizarApi() {
  acaoAtual.value = 'sincronizar'

  try {
    await sincronizar()
    ultimaAtualizacao.value = new Date()
  } catch {
    // O erro já é exibido pela store.
  } finally {
    acaoAtual.value = null
  }
}

onMounted(async () => {
  if (!personagens.value.length) {
    await atualizar()
  }

  await nextTick()
  renderizarGraficos()
})

watch([personagens, carregando], agendarGraficos, { deep: true })

onBeforeUnmount(() => {
  graficoEspecies?.destroy()
  graficoStatus?.destroy()
})
</script>

<template>
  <section class="pagina-dashboard">
    <header class="cabecalho-pagina">
      <div class="cabecalho-texto">
        <h1 class="titulo">Dashboard</h1>
        <p class="subtitulo">Visão geral dos personagens importados da Rick and Morty API.</p>
        <p v-if="ultimaAtualizacaoFormatada" class="atualizacao">
          Atualizado hoje às {{ ultimaAtualizacaoFormatada }}
        </p>
      </div>

      <div class="acoes-pagina">
        <span v-if="carregando" class="pill-carregando">
          <span class="spinner"></span>
          {{ textoCarregando }}
        </span>
        <button class="btn-2" :disabled="carregando" @click="atualizar">Atualizar</button>
        <button class="btn-1" :disabled="carregando" @click="sincronizarApi">
          Sincronizar com a API
        </button>
      </div>
    </header>

    <p v-if="erro" class="mensagem-erro">
      Não foi possível carregar os personagens. Tente novamente em instantes.
    </p>

    <div v-if="carregando && !personagens.length" class="painel estado-carregando">
      <span class="spinner grande"></span>
      <p>Carregando dashboard…</p>
    </div>

    <template v-else-if="personagens.length">
      <div class="indicadores-cards">
        <article class="card-indicador card-total">
          <span class="rotulo">Total</span>
          <strong>{{ totalPersonagens }}</strong>
          <span class="detalhe">{{ totalEspecies }} espécies catalogadas</span>
        </article>

        <article class="card-indicador card-vivos">
          <span class="rotulo">Vivos</span>
          <strong>{{ totalVivos }}</strong>
          <span class="detalhe">{{ percentualVivos }}% do catálogo</span>
        </article>

        <article class="card-indicador card-mortos">
          <span class="rotulo">Mortos</span>
          <strong>{{ totalMortos }}</strong>
          <span class="detalhe">{{ percentualMortos }}% do catálogo</span>
        </article>

        <article class="card-indicador card-humanos">
          <span class="rotulo">Humanos</span>
          <strong>{{ percentualHumanos }}%</strong>
          <span class="detalhe">{{ totalHumanos }} personagens humanos</span>
        </article>
      </div>

      <div class="graficos-grid">
        <div class="painel">
          <div class="painel-cabecalho">
            <h3>Personagens por espécie</h3>
            <span class="painel-badge">{{ totalEspecies }} espécies</span>
          </div>
          <div class="grafico-area">
            <canvas ref="canvasEspecies"></canvas>
          </div>
        </div>

        <div class="painel">
          <div class="painel-cabecalho">
            <h3>Distribuição de status</h3>
            <span class="painel-badge">{{ totalPersonagens }} no total</span>
          </div>
          <div class="grafico-area">
            <canvas ref="canvasStatus"></canvas>
          </div>
        </div>
      </div>

      <div class="rodape-grid">
        <div class="painel">
          <div class="painel-cabecalho">
            <h3>Top 5 destaques</h3>
            <span class="painel-badge">por episódios</span>
          </div>

          <ol class="ranking-lista">
            <li
              v-for="(personagem, indice) in topCincoPersonagens"
              :key="getId(personagem)"
              class="ranking-item"
            >
              <span class="posicao" :class="{ destaque: indice === 0 }">{{ indice + 1 }}</span>

              <div class="ranking-info">
                <div class="ranking-nome">
                  <strong :title="personagem.name">{{ personagem.name }}</strong>
                  <span class="ranking-episodios">{{ personagem.episode_count || 0 }} eps</span>
                </div>

                <div class="ranking-meta">
                  <span class="chip" :class="classeStatus(personagem.status)">
                    {{ rotuloStatus(personagem.status) }}
                  </span>
                  <span class="chip">{{ personagem.species || 'Sem espécie' }}</span>
                </div>

                <div class="barra">
                  <span :style="{ width: percentualEpisodios(personagem) }"></span>
                </div>
              </div>
            </li>
          </ol>
        </div>

        <div class="painel">
          <div class="painel-cabecalho">
            <h3>Espécies mais comuns</h3>
            <span class="painel-badge">top 6</span>
          </div>

          <ul class="especies-lista">
            <li
              v-for="item in especiesOrdenadas.slice(0, 6)"
              :key="item.especie"
              class="especie-item"
            >
              <div class="especie-linha">
                <span class="especie-nome" :title="item.especie">{{ item.especie }}</span>
                <span class="especie-valor">{{ item.quantidade }} · {{ item.percentual }}%</span>
              </div>
              <div class="barra">
                <span :style="{ width: item.percentual + '%' }"></span>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </template>

    <div v-else class="painel estado-vazio">
      <p>Nenhum personagem encontrado.</p>
      <button class="btn-1" @click="sincronizarApi">Sincronizar com a API</button>
    </div>
  </section>
</template>

<style scoped>
.pagina-dashboard {
  display: flex;
  flex-direction: column;
  gap: 24px;
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
}

.titulo {
  margin: 0;
}

.subtitulo {
  margin: 6px 0 0;
  color: var(--cor-texto-secundario);
}

.atualizacao {
  margin: 8px 0 0;
  color: #7f8c9b;
  font-size: 0.8rem;
}

.acoes-pagina {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.pill-carregando {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--cor-texto-secundario);
  font-size: 0.85rem;
}

.spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid rgba(255, 255, 255, 0.25);
  border-top-color: var(--cor-primaria);
  border-radius: 50%;
  animation: girar 0.7s linear infinite;
}

.spinner.grande {
  width: 28px;
  height: 28px;
  border-width: 3px;
}

@keyframes girar {
  to {
    transform: rotate(360deg);
  }
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.mensagem-erro {
  margin: 0;
  color: #ff8a8a;
}

.indicadores-cards {
  display: flex;
  gap: 16px;
  justify-content: space-around;
  flex-wrap: wrap;
}

.card-indicador {
  flex: 1;
  min-width: 150px;
  padding: 16px;
  text-align: center;
  background-color: var(--cor-fundo);
  border: 1px solid #333;
  border-radius: 8px;
}

.rotulo {
  display: block;
  color: var(--cor-texto-secundario);
  font-size: 0.9rem;
}

.card-indicador strong {
  display: block;
  color: var(--cor-primaria);
  font-size: 1.8rem;
  line-height: 1.2;
}

.detalhe {
  color: #7f8c9b;
  font-size: 0.78rem;
}

.painel {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 16px;
  background-color: var(--cor-fundo);
  border: 1px solid #333;
  border-radius: 8px;
}

.painel-cabecalho {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.painel-cabecalho h3 {
  margin: 0;
}

.painel-badge {
  flex-shrink: 0;
  color: var(--cor-texto-secundario);
  font-size: 0.78rem;
}

.graficos-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
  gap: 20px;
}

.grafico-area {
  position: relative;
  width: 100%;
  height: 280px;
}

.rodape-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
  gap: 20px;
}

.ranking-lista,
.especies-lista {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.ranking-item {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  align-items: center;
  gap: 10px;
}

.posicao {
  min-width: 18px;
  color: var(--cor-texto-secundario);
  font-size: 0.85rem;
  font-weight: 700;
  text-align: right;
}

.posicao.destaque {
  color: var(--cor-primaria);
}

.ranking-info {
  min-width: 0;
}

.ranking-nome {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 10px;
}

.ranking-nome strong {
  overflow: hidden;
  color: var(--cor-texto);
  font-size: 0.95rem;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.ranking-episodios {
  flex-shrink: 0;
  color: var(--cor-texto-secundario);
  font-size: 0.78rem;
}

.ranking-meta {
  display: flex;
  gap: 6px;
  margin-top: 3px;
}

.chip {
  display: inline-block;
  color: var(--cor-texto-secundario);
  font-size: 0.78rem;
}

.chip + .chip::before {
  content: '· ';
}

.chip.alive,
.chip.dead,
.chip.unknown {
  color: var(--cor-texto-secundario);
}

.barra {
  height: 6px;
  margin-top: 8px;
  overflow: hidden;
  background-color: #121212;
  border-radius: 999px;
}

.barra > span {
  display: block;
  height: 100%;
  border-radius: 999px;
  background-color: var(--cor-primaria);
}

.especie-linha {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 10px;
}

.especie-nome {
  overflow: hidden;
  color: var(--cor-texto);
  font-size: 0.9rem;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.especie-valor {
  flex-shrink: 0;
  color: var(--cor-texto-secundario);
  font-size: 0.78rem;
}

.estado-carregando,
.estado-vazio {
  align-items: center;
  justify-content: center;
  min-height: 200px;
  padding: 32px;
  text-align: center;
  color: var(--cor-texto-secundario);
}

.estado-carregando p,
.estado-vazio p {
  margin: 0;
}

@media (max-width: 980px) {
  .graficos-grid,
  .rodape-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 550px) {
  .pagina-dashboard {
    padding: 16px;
  }

  .grafico-area {
    height: 240px;
  }
}
</style>
