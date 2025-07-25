<template>
  <el-header class="navbar">
    <div class="nav-content">
      <div class="nav-left">
        <router-link to="/" class="logo">
          <h1>{{ $t('app.name') }}</h1>
          <span class="tagline">{{ $t('app.tagline') }}</span>
        </router-link>
      </div>
      
      <div class="nav-center">
        <el-menu
          :default-active="activeIndex"
          mode="horizontal"
          router
          class="nav-menu"
        >
          <el-menu-item index="/">{{ $t('nav.home') }}</el-menu-item>
          <el-menu-item 
            v-if="canUpload" 
            index="/upload"
          >
            {{ $t('nav.upload') }}
          </el-menu-item>
        </el-menu>
      </div>
      
      <div class="nav-right">
        <template v-if="isLoggedIn">
          <el-badge 
            v-if="needsQuiz" 
            is-dot 
            class="quiz-badge"
          >
            <el-button 
              type="warning" 
              size="small"
              @click="$router.push('/quiz')"
            >
              完成测试
            </el-button>
          </el-badge>
          
          <el-dropdown @command="handleCommand">
            <span class="user-dropdown">
              <el-avatar 
                :src="user.avatar" 
                :size="32"
              >
                {{ user.nickname?.[0] }}
              </el-avatar>
              <span class="username">{{ user.nickname }}</span>
              <el-icon><ArrowDown /></el-icon>
            </span>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="profile">
                  {{ $t('nav.profile') }}
                </el-dropdown-item>
                <el-dropdown-item divided command="logout">
                  {{ $t('nav.logout') }}
                </el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </template>
        
        <el-button 
          v-else 
          type="primary" 
          @click="$router.push('/login')"
        >
          {{ $t('nav.login') }}
        </el-button>
      </div>
    </div>
  </el-header>
</template>

<script>
import { computed } from 'vue'
import { useStore } from 'vuex'
import { useRouter, useRoute } from 'vue-router'
import { ArrowDown } from '@element-plus/icons-vue'

export default {
  name: 'NavBar',
  components: {
    ArrowDown
  },
  setup() {
    const store = useStore()
    const router = useRouter()
    const route = useRoute()
    
    const isLoggedIn = computed(() => store.getters.isLoggedIn)
    const user = computed(() => store.getters.user)
    const needsQuiz = computed(() => store.getters.needsQuiz)
    const canUpload = computed(() => store.getters.canUpload)
    const activeIndex = computed(() => route.path)
    
    const handleCommand = (command) => {
      if (command === 'logout') {
        store.dispatch('logout').then(() => {
          router.push('/')
        })
      } else if (command === 'profile') {
        router.push('/profile')
      }
    }
    
    return {
      isLoggedIn,
      user,
      needsQuiz,
      canUpload,
      activeIndex,
      handleCommand
    }
  }
}
</script>

<style scoped>
.navbar {
  background: white;
  border-bottom: 1px solid #e6e6e6;
  padding: 0;
}

.nav-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  height: 100%;
}

.nav-left .logo {
  display: flex;
  align-items: baseline;
  text-decoration: none;
  color: #333;
}

.nav-left h1 {
  margin: 0;
  font-size: 24px;
  font-weight: bold;
  margin-right: 8px;
}

.tagline {
  font-size: 12px;
  color: #666;
}

.nav-menu {
  border-bottom: none;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-dropdown {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.username {
  font-size: 14px;
}

.quiz-badge {
  margin-right: 12px;
}
</style>