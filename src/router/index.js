import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomeView
  },
  {
    path: '/customer',
    name: 'customer',

    component: () => import( '../views/Customer.vue')
  },
 
  {
    path: '/Contact',
    name: 'Contact',

    component: () => import( '../views/Contact.vue')
  }, 
  {
    path: '/Type',
    name: 'Type',

    component: () => import ('../views/type.vue')
  },
  {
    path: '/Employees',
    name: 'Employees',

    component: () => import( '../views/Employees.vue')
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
