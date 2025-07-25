<template>
  <div id="app">
    <el-config-provider :locale="locale">
      <NavBar />
      <main class="main-content">
        <router-view />
      </main>
      <Footer />
    </el-config-provider>
  </div>
</template>

<script>
import { ref } from 'vue'
import zhCn from 'element-plus/es/locale/lang/zh-cn'
import NavBar from './components/NavBar.vue'
import Footer from './components/Footer.vue'

export default {
  name: 'App',
  components: {
    NavBar,
    Footer
  },
  setup() {
    const locale = ref(zhCn)
    
    return {
      locale
    }
  }
}
</script>

<style>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

body {
  margin: 0;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
    'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
    sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

* {
  box-sizing: border-box;
}
</style>