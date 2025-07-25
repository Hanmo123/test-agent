import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createStore } from 'vuex'
import { createI18n } from 'vue-i18n'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import zhCn from 'element-plus/es/locale/lang/zh-cn'

import App from './App.vue'
import { routes } from './router'
import { store } from './store'
import { messages } from './i18n'

const router = createRouter({
  history: createWebHistory(),
  routes
})

const i18n = createI18n({
  locale: 'zh-CN',
  fallbackLocale: 'en',
  messages
})

const app = createApp(App)

app.use(router)
app.use(store)
app.use(i18n)
app.use(ElementPlus, { locale: zhCn })

app.mount('#app')