<template>
  <div class="profile">
    <div class="profile-container">
      <!-- Profile Header -->
      <div class="profile-header">
        <div class="profile-avatar">
          <el-avatar 
            :src="user?.avatar" 
            :size="120"
          >
            {{ user?.nickname?.[0] }}
          </el-avatar>
        </div>
        
        <div class="profile-info">
          <h1>{{ user?.nickname }}</h1>
          <p>Level {{ user?.level }}</p>
          
          <div class="profile-stats">
            <div class="stat-item">
              <span class="stat-value">{{ stats.works_count }}</span>
              <span class="stat-label">作品</span>
            </div>
            <div class="stat-item">
              <span class="stat-value">{{ stats.followers_count }}</span>
              <span class="stat-label">粉丝</span>
            </div>
            <div class="stat-item">
              <span class="stat-value">{{ stats.following_count }}</span>
              <span class="stat-label">关注</span>
            </div>
            <div class="stat-item">
              <span class="stat-value">{{ stats.likes_received }}</span>
              <span class="stat-label">获赞</span>
            </div>
          </div>
          
          <div v-if="isOwnProfile" class="profile-actions">
            <el-button @click="editProfileVisible = true">
              编辑资料
            </el-button>
            <el-button @click="preferencesVisible = true">
              偏好设置
            </el-button>
          </div>
        </div>
        
        <!-- Shutter Time Display -->
        <div v-if="isOwnProfile && user?.shutter_time" class="shutter-time-card">
          <h3>快门时间</h3>
          <div class="shutter-balance">
            {{ user.shutter_time.balance || 0 }} / 100s
          </div>
          <el-button 
            size="small"
            type="primary"
            @click="claimDaily"
            :disabled="!canClaimDaily"
            :loading="claimingDaily"
          >
            {{ canClaimDaily ? '领取每日奖励' : '今日已领取' }}
          </el-button>
        </div>
      </div>
      
      <!-- Profile Tabs -->
      <div class="profile-content">
        <el-tabs v-model="activeTab">
          <el-tab-pane label="作品" name="works">
            <div v-if="works.length > 0" class="works-grid">
              <div 
                v-for="work in works" 
                :key="work.id"
                class="work-item"
                @click="$router.push(`/works/${work.id}`)"
              >
                <img 
                  :src="work.cover_resource?.thumbnail_url" 
                  :alt="work.title"
                />
                <div class="work-overlay">
                  <h4>{{ work.title }}</h4>
                  <div class="work-stats">
                    <span><el-icon><View /></el-icon> {{ work.views_count }}</span>
                    <span><el-icon><Star /></el-icon> {{ work.likes_count }}</span>
                  </div>
                </div>
              </div>
            </div>
            <el-empty v-else description="暂无作品" />
          </el-tab-pane>
          
          <el-tab-pane label="草稿" name="drafts" v-if="isOwnProfile">
            <div v-if="drafts.length > 0" class="drafts-list">
              <div 
                v-for="draft in drafts" 
                :key="draft.id"
                class="draft-item"
              >
                <div class="draft-preview">
                  <img 
                    v-if="draft.cover_resource" 
                    :src="draft.cover_resource.thumbnail_url" 
                    :alt="draft.title"
                  />
                  <div v-else class="draft-placeholder">
                    <el-icon><Document /></el-icon>
                  </div>
                </div>
                
                <div class="draft-info">
                  <h4>{{ draft.title || '无标题草稿' }}</h4>
                  <p>最后编辑: {{ formatDate(draft.updated_at) }}</p>
                </div>
                
                <div class="draft-actions">
                  <el-button size="small" @click="editDraft(draft)">
                    编辑
                  </el-button>
                  <el-button size="small" type="primary" @click="publishDraft(draft)">
                    发布
                  </el-button>
                </div>
              </div>
            </div>
            <el-empty v-else description="暂无草稿" />
          </el-tab-pane>
          
          <el-tab-pane label="收藏" name="collections" v-if="isOwnProfile">
            <div v-if="collections.length > 0" class="collections-grid">
              <div 
                v-for="collection in collections" 
                :key="collection.id"
                class="collection-item"
              >
                <div class="collection-cover">
                  <img 
                    v-if="collection.cover_resource" 
                    :src="collection.cover_resource.thumbnail_url" 
                    :alt="collection.title"
                  />
                  <div v-else class="collection-placeholder">
                    <el-icon><Folder /></el-icon>
                  </div>
                </div>
                
                <div class="collection-info">
                  <h4>{{ collection.title }}</h4>
                  <p>{{ collection.works_count || 0 }} 个作品</p>
                </div>
              </div>
            </div>
            <el-empty v-else description="暂无收藏" />
          </el-tab-pane>
        </el-tabs>
      </div>
    </div>
    
    <!-- Edit Profile Dialog -->
    <el-dialog v-model="editProfileVisible" title="编辑资料" width="500px">
      <el-form :model="profileForm" label-width="80px">
        <el-form-item label="昵称">
          <el-input v-model="profileForm.nickname" />
        </el-form-item>
        
        <el-form-item label="头像">
          <el-input v-model="profileForm.avatar" placeholder="头像URL" />
        </el-form-item>
        
        <el-form-item label="可见性">
          <el-select v-model="profileForm.visibility" style="width: 100%">
            <el-option label="公开" value="public" />
            <el-option label="仅关注者" value="followers_only" />
            <el-option label="私密" value="private" />
          </el-select>
        </el-form-item>
      </el-form>
      
      <template #footer>
        <el-button @click="editProfileVisible = false">取消</el-button>
        <el-button type="primary" @click="updateProfile" :loading="updatingProfile">
          保存
        </el-button>
      </template>
    </el-dialog>
    
    <!-- Preferences Dialog -->
    <el-dialog v-model="preferencesVisible" title="偏好设置" width="500px">
      <el-form :model="preferencesForm" label-width="100px">
        <el-form-item label="默认水印">
          <el-switch v-model="preferencesForm.default_watermark" />
        </el-form-item>
        
        <el-form-item label="主题">
          <el-select v-model="preferencesForm.theme" style="width: 100%">
            <el-option label="浅色" value="light" />
            <el-option label="深色" value="dark" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="通知频率">
          <el-select v-model="preferencesForm.notifications" style="width: 100%">
            <el-option label="实时" value="realtime" />
            <el-option label="每日摘要" value="daily" />
            <el-option label="关闭" value="disabled" />
          </el-select>
        </el-form-item>
      </el-form>
      
      <template #footer>
        <el-button @click="preferencesVisible = false">取消</el-button>
        <el-button type="primary" @click="updatePreferences" :loading="updatingPreferences">
          保存
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { View, Star, Document, Folder } from '@element-plus/icons-vue'
import dayjs from 'dayjs'
import api from '../api'

export default {
  name: 'Profile',
  components: {
    View,
    Star,
    Document,
    Folder
  },
  setup() {
    const store = useStore()
    const router = useRouter()
    
    const activeTab = ref('works')
    const editProfileVisible = ref(false)
    const preferencesVisible = ref(false)
    const updatingProfile = ref(false)
    const updatingPreferences = ref(false)
    const claimingDaily = ref(false)
    
    const works = ref([])
    const drafts = ref([])
    const collections = ref([])
    const stats = ref({
      works_count: 0,
      followers_count: 0,
      following_count: 0,
      likes_received: 0
    })
    
    const profileForm = reactive({
      nickname: '',
      avatar: '',
      visibility: 'public'
    })
    
    const preferencesForm = reactive({
      default_watermark: false,
      theme: 'light',
      notifications: 'realtime'
    })
    
    const user = computed(() => store.getters.user)
    const isOwnProfile = computed(() => true) // Since this is the user's own profile page
    
    const canClaimDaily = computed(() => {
      if (!user.value?.shutter_time) return false
      const lastClaimed = user.value.shutter_time.last_daily_claimed_at
      if (!lastClaimed) return true
      return dayjs(lastClaimed).isBefore(dayjs(), 'day')
    })
    
    const formatDate = (date) => {
      return dayjs(date).format('YYYY-MM-DD HH:mm')
    }
    
    const fetchUserProfile = async () => {
      try {
        const response = await api.get('/auth/me')
        const userData = response.data.user
        
        store.commit('SET_USER', userData)
        
        // Update forms with current data
        profileForm.nickname = userData.nickname || ''
        profileForm.avatar = userData.avatar || ''
        profileForm.visibility = userData.visibility || 'public'
        
        const preferences = userData.preferences || {}
        preferencesForm.default_watermark = preferences.default_watermark || false
        preferencesForm.theme = preferences.theme || 'light'
        preferencesForm.notifications = preferences.notifications || 'realtime'
        
      } catch (error) {
        console.error('Failed to fetch user profile:', error)
      }
    }
    
    const fetchUserWorks = async () => {
      try {
        const response = await api.get('/works', {
          params: { user_id: user.value.id }
        })
        works.value = response.data.data || []
        stats.value.works_count = works.value.length
      } catch (error) {
        console.error('Failed to fetch works:', error)
      }
    }
    
    const fetchUserDrafts = async () => {
      try {
        const response = await api.get('/drafts')
        drafts.value = response.data.data || []
      } catch (error) {
        console.error('Failed to fetch drafts:', error)
      }
    }
    
    const fetchUserCollections = async () => {
      try {
        const response = await api.get('/collections', {
          params: { user_id: user.value.id }
        })
        collections.value = response.data.data || []
      } catch (error) {
        console.error('Failed to fetch collections:', error)
      }
    }
    
    const updateProfile = async () => {
      updatingProfile.value = true
      try {
        await api.patch('/users/me', profileForm)
        ElMessage.success('资料更新成功')
        editProfileVisible.value = false
        await fetchUserProfile()
      } catch (error) {
        ElMessage.error('更新失败')
        console.error('Profile update failed:', error)
      } finally {
        updatingProfile.value = false
      }
    }
    
    const updatePreferences = async () => {
      updatingPreferences.value = true
      try {
        await api.patch('/users/me/preferences', {
          preferences: preferencesForm
        })
        ElMessage.success('偏好设置更新成功')
        preferencesVisible.value = false
        await fetchUserProfile()
      } catch (error) {
        ElMessage.error('更新失败')
        console.error('Preferences update failed:', error)
      } finally {
        updatingPreferences.value = false
      }
    }
    
    const claimDaily = async () => {
      claimingDaily.value = true
      try {
        const response = await api.post('/shutter-time/claim-daily')
        ElMessage.success(`获得 ${response.data.claimed_amount} 快门时间`)
        await fetchUserProfile()
      } catch (error) {
        if (error.response?.status === 400) {
          ElMessage.warning('今日已领取')
        } else {
          ElMessage.error('领取失败')
        }
        console.error('Daily claim failed:', error)
      } finally {
        claimingDaily.value = false
      }
    }
    
    const editDraft = (draft) => {
      // Implement draft editing
      ElMessage.info('草稿编辑功能开发中')
    }
    
    const publishDraft = async (draft) => {
      try {
        await api.post('/works', { draft_id: draft.id })
        ElMessage.success('作品发布成功')
        fetchUserWorks()
        fetchUserDrafts()
      } catch (error) {
        ElMessage.error('发布失败')
        console.error('Publish failed:', error)
      }
    }
    
    onMounted(() => {
      fetchUserProfile()
      fetchUserWorks()
      fetchUserDrafts()
      fetchUserCollections()
    })
    
    return {
      activeTab,
      editProfileVisible,
      preferencesVisible,
      updatingProfile,
      updatingPreferences,
      claimingDaily,
      works,
      drafts,
      collections,
      stats,
      profileForm,
      preferencesForm,
      user,
      isOwnProfile,
      canClaimDaily,
      formatDate,
      updateProfile,
      updatePreferences,
      claimDaily,
      editDraft,
      publishDraft
    }
  }
}
</script>

<style scoped>
.profile {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.profile-header {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 32px;
  align-items: center;
  margin-bottom: 32px;
  padding: 32px;
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.profile-info h1 {
  margin: 0 0 8px 0;
  font-size: 32px;
  font-weight: 600;
  color: #333;
}

.profile-info p {
  margin: 0 0 20px 0;
  color: #666;
  font-size: 16px;
}

.profile-stats {
  display: flex;
  gap: 32px;
  margin-bottom: 24px;
}

.stat-item {
  text-align: center;
}

.stat-value {
  display: block;
  font-size: 24px;
  font-weight: 600;
  color: #333;
}

.stat-label {
  font-size: 14px;
  color: #666;
}

.profile-actions {
  display: flex;
  gap: 12px;
}

.shutter-time-card {
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  text-align: center;
  min-width: 180px;
}

.shutter-time-card h3 {
  margin: 0 0 12px 0;
  font-size: 16px;
}

.shutter-balance {
  font-size: 24px;
  font-weight: 600;
  margin-bottom: 16px;
}

.profile-content {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  padding: 24px;
}

.works-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
}

.work-item {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s;
}

.work-item:hover {
  transform: translateY(-2px);
}

.work-item img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.work-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(transparent, rgba(0,0,0,0.8));
  color: white;
  padding: 16px;
}

.work-overlay h4 {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 500;
}

.work-stats {
  display: flex;
  gap: 12px;
  font-size: 12px;
}

.work-stats span {
  display: flex;
  align-items: center;
  gap: 2px;
}

.drafts-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.draft-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  border: 1px solid #e6e6e6;
  border-radius: 8px;
}

.draft-preview {
  width: 60px;
  height: 60px;
  border-radius: 6px;
  overflow: hidden;
  background: #f5f5f5;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.draft-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.draft-placeholder {
  color: #999;
  font-size: 24px;
}

.draft-info {
  flex: 1;
}

.draft-info h4 {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 500;
}

.draft-info p {
  margin: 0;
  font-size: 12px;
  color: #666;
}

.draft-actions {
  display: flex;
  gap: 8px;
}

.collections-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
}

.collection-item {
  border: 1px solid #e6e6e6;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  transition: box-shadow 0.2s;
}

.collection-item:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.collection-cover {
  width: 100%;
  height: 150px;
  background: #f5f5f5;
  display: flex;
  align-items: center;
  justify-content: center;
}

.collection-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.collection-placeholder {
  color: #999;
  font-size: 32px;
}

.collection-info {
  padding: 16px;
}

.collection-info h4 {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 500;
}

.collection-info p {
  margin: 0;
  font-size: 12px;
  color: #666;
}

@media (max-width: 768px) {
  .profile-header {
    grid-template-columns: 1fr;
    text-align: center;
    gap: 20px;
  }
  
  .profile-stats {
    justify-content: center;
  }
  
  .works-grid,
  .collections-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
  }
  
  .draft-item {
    flex-direction: column;
    text-align: center;
  }
}
</style>