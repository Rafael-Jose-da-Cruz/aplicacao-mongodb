import { createRouter, createWebHistory } from 'vue-router'
import DashboardPage from '../pages/DashboardPage.vue'
import CharactersPage from '../pages/CharactersPage.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/dashboard' },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardPage,
      meta: { title: 'Dashboard' },
    },
    {
      path: '/characters',
      name: 'characters',
      component: CharactersPage,
      meta: { title: 'Personagens' },
    },
    { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
  ],
});

router.afterEach((to) => {
  document.title = to.meta.title
    ? `${to.meta.title} | Catálogo Rick and Morty`
    : 'Catálogo Rick and Morty'
});

export default router;
