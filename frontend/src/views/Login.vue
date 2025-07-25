<template>
  <div class="login">
    <div class="login-container">
      <div class="login-form">
        <h1>{{ $t('auth.loginTitle') }}</h1>
        <p>{{ $t('auth.loginDescription') }}</p>
        
        <div class="login-methods">
          <el-button 
            type="primary" 
            size="large" 
            @click="loginWithLogto"
            :loading="loading"
            class="login-button"
          >
            <el-icon><User /></el-icon>
            使用 Logto 登录
          </el-button>
          
          <div class="divider">或使用以下方式登录</div>
          
          <el-button 
            size="large" 
            @click="loginWithGitHub"
            class="login-button github"
          >
            <el-icon><StarFilled /></el-icon>
            GitHub
          </el-button>
          
          <el-button 
            size="large" 
            @click="loginWithWechat"
            class="login-button wechat"
          >
            <el-icon><ChatDotRound /></el-icon>
            微信
          </el-button>
          
          <el-button 
            size="large" 
            @click="loginWithApple"
            class="login-button apple"
          >
            <el-icon><Apple /></el-icon>
            Apple
          </el-button>
        </div>
        
        <div class="login-footer">
          <p>登录即表示您同意我们的 <a href="/terms">服务条款</a> 和 <a href="/privacy">隐私政策</a></p>
        </div>
      </div>
      
      <div class="login-showcase">
        <div class="showcase-images">
          <img src="/api/placeholder/400/600" alt="摄影作品展示" />
          <img src="/api/placeholder/300/450" alt="摄影作品展示" />
          <img src="/api/placeholder/350/525" alt="摄影作品展示" />
        </div>
        <div class="showcase-overlay">
          <h2>发现世界的美好瞬间</h2>
          <p>加入 OnlyShots，与摄影师们分享您的作品，探索无限创意</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { User, StarFilled, ChatDotRound } from '@element-plus/icons-vue'

export default {
  name: 'Login',
  components: {
    User,
    StarFilled,
    ChatDotRound
  },
  setup() {
    const store = useStore()
    const router = useRouter()
    const loading = ref(false)
    
    const loginWithLogto = async () => {
      loading.value = true
      try {
        // This would integrate with Logto SDK
        // For now, simulate a successful login
        const mockToken = 'mock-logto-token'
        const result = await store.dispatch('login', mockToken)
        
        ElMessage.success('登录成功')
        
        if (result.needs_quiz) {
          router.push('/quiz')
        } else {
          router.push('/')
        }
      } catch (error) {
        ElMessage.error('登录失败，请重试')
        console.error('Login error:', error)
      } finally {
        loading.value = false
      }
    }
    
    const loginWithGitHub = () => {
      // Implement GitHub OAuth login
      ElMessage.info('GitHub 登录功能开发中')
    }
    
    const loginWithWechat = () => {
      // Implement WeChat login
      ElMessage.info('微信登录功能开发中')
    }
    
    const loginWithApple = () => {
      // Implement Apple Sign In
      ElMessage.info('Apple 登录功能开发中')
    }
    
    return {
      loading,
      loginWithLogto,
      loginWithGitHub,
      loginWithWechat,
      loginWithApple
    }
  }
}
</script>

<style scoped>
.login {
  min-height: calc(100vh - 60px);
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-container {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  max-width: 1000px;
  width: 100%;
  margin: 40px 20px;
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.login-form {
  padding: 48px 40px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.login-form h1 {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 600;
  color: #333;
}

.login-form p {
  margin: 0 0 32px 0;
  color: #666;
  font-size: 16px;
  line-height: 1.5;
}

.login-methods {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.login-button {
  width: 100%;
  height: 48px;
  font-size: 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.login-button.github {
  background: #333;
  color: white;
  border: none;
}

.login-button.github:hover {
  background: #444;
}

.login-button.wechat {
  background: #07c160;
  color: white;
  border: none;
}

.login-button.wechat:hover {
  background: #06ad56;
}

.login-button.apple {
  background: #000;
  color: white;
  border: none;
}

.login-button.apple:hover {
  background: #333;
}

.divider {
  text-align: center;
  color: #999;
  font-size: 14px;
  margin: 8px 0;
  position: relative;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: #e6e6e6;
  z-index: 0;
}

.divider::after {
  content: attr(data-text);
  background: white;
  padding: 0 16px;
  position: relative;
  z-index: 1;
}

.login-footer {
  margin-top: 32px;
  text-align: center;
}

.login-footer p {
  font-size: 12px;
  color: #999;
  margin: 0;
}

.login-footer a {
  color: #409eff;
  text-decoration: none;
}

.login-footer a:hover {
  text-decoration: underline;
}

.login-showcase {
  position: relative;
  background: linear-gradient(45deg, #667eea, #764ba2);
  display: flex;
  align-items: center;
  justify-content: center;
}

.showcase-images {
  display: flex;
  gap: 20px;
  opacity: 0.3;
}

.showcase-images img {
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.showcase-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: white;
  z-index: 2;
}

.showcase-overlay h2 {
  margin: 0 0 16px 0;
  font-size: 32px;
  font-weight: 600;
}

.showcase-overlay p {
  margin: 0;
  font-size: 18px;
  opacity: 0.9;
  line-height: 1.5;
}

@media (max-width: 768px) {
  .login-container {
    grid-template-columns: 1fr;
    margin: 20px;
  }
  
  .login-form {
    padding: 32px 24px;
  }
  
  .login-showcase {
    display: none;
  }
}
</style>