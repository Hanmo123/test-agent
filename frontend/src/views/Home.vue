<template>
  <div class="home">
    <!-- Daily Photo Section -->
    <section class="daily-photo-section">
      <h2>{{ $t('home.dailyPhoto') }}</h2>
      <div v-if="dailyPhoto" class="daily-photo">
        <div class="daily-photo-image">
          <img 
            :src="dailyPhoto.work.cover_resource?.display_url" 
            :alt="dailyPhoto.work.title"
            @click="viewWork(dailyPhoto.work.id)"
          />
        </div>
        <div class="daily-photo-info">
          <h3>{{ dailyPhoto.work.title }}</h3>
          <div class="author-info">
            <el-avatar 
              :src="dailyPhoto.work.user.avatar" 
              :size="32"
            >
              {{ dailyPhoto.work.user.nickname?.[0] }}
            </el-avatar>
            <span>{{ dailyPhoto.work.user.nickname }}</span>
          </div>
        </div>
      </div>
      <el-skeleton v-else :rows="3" animated />
    </section>

    <!-- Recent Works Section -->
    <section class="recent-works-section">
      <h2>{{ $t('home.recentWorks') }}</h2>
      
      <div class="works-grid" v-if="works.length">
        <div 
          v-for="work in works" 
          :key="work.id"
          class="work-card"
          @click="viewWork(work.id)"
        >
          <div class="work-image">
            <img 
              :src="work.cover_resource?.thumbnail_url" 
              :alt="work.title"
              loading="lazy"
            />
            <div class="work-overlay">
              <div class="work-stats">
                <span><el-icon><View /></el-icon> {{ work.views_count }}</span>
                <span><el-icon><Star /></el-icon> {{ work.likes_count }}</span>
              </div>
            </div>
          </div>
          
          <div class="work-info">
            <h4>{{ work.title }}</h4>
            <div class="work-author">
              <el-avatar 
                :src="work.user.avatar" 
                :size="24"
              >
                {{ work.user.nickname?.[0] }}
              </el-avatar>
              <span>{{ work.user.nickname }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else-if="loading" class="loading-grid">
        <el-skeleton v-for="n in 12" :key="n" :rows="3" animated />
      </div>
      
      <el-empty v-else description="暂无作品" />
      
      <!-- Load More -->
      <div v-if="hasMore" class="load-more">
        <el-button 
          @click="loadMore" 
          :loading="loadingMore"
          size="large"
        >
          加载更多
        </el-button>
      </div>
    </section>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { View, Star } from '@element-plus/icons-vue'
import api from '../api'

export default {
  name: 'Home',
  components: {
    View,
    Star
  },
  setup() {
    const router = useRouter()
    const dailyPhoto = ref(null)
    const works = ref([])
    const loading = ref(false)
    const loadingMore = ref(false)
    const hasMore = ref(true)
    const currentPage = ref(1)
    
    const fetchDailyPhoto = async () => {
      try {
        const response = await api.get('/daily-photo')
        dailyPhoto.value = response.data
      } catch (error) {
        console.error('Failed to fetch daily photo:', error)
      }
    }
    
    const fetchWorks = async (page = 1) => {
      try {
        if (page === 1) {
          loading.value = true
        } else {
          loadingMore.value = true
        }
        
        const response = await api.get('/works', {
          params: { page, per_page: 20 }
        })
        
        if (page === 1) {
          works.value = response.data.data
        } else {
          works.value.push(...response.data.data)
        }
        
        hasMore.value = response.data.current_page < response.data.last_page
        currentPage.value = page
        
      } catch (error) {
        console.error('Failed to fetch works:', error)
      } finally {
        loading.value = false
        loadingMore.value = false
      }
    }
    
    const loadMore = () => {
      if (hasMore.value && !loadingMore.value) {
        fetchWorks(currentPage.value + 1)
      }
    }
    
    const viewWork = (workId) => {
      router.push(`/works/${workId}`)
    }
    
    onMounted(() => {
      fetchDailyPhoto()
      fetchWorks()
    })
    
    return {
      dailyPhoto,
      works,
      loading,
      loadingMore,
      hasMore,
      loadMore,
      viewWork
    }
  }
}
</script>

<style scoped>
.home {
  max-width: 1200px;
  margin: 0 auto;
}

.daily-photo-section {
  margin-bottom: 48px;
}

.daily-photo-section h2 {
  margin-bottom: 24px;
  font-size: 28px;
  font-weight: 600;
}

.daily-photo {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 32px;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.daily-photo-image {
  position: relative;
  cursor: pointer;
}

.daily-photo-image img {
  width: 100%;
  height: 400px;
  object-fit: cover;
}

.daily-photo-info {
  padding: 32px 24px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.daily-photo-info h3 {
  margin: 0 0 24px 0;
  font-size: 24px;
  font-weight: 600;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.recent-works-section h2 {
  margin-bottom: 24px;
  font-size: 28px;
  font-weight: 600;
}

.works-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 32px;
}

.work-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.work-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.work-image {
  position: relative;
  overflow: hidden;
}

.work-image img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  transition: transform 0.3s;
}

.work-card:hover .work-image img {
  transform: scale(1.05);
}

.work-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.5));
  display: flex;
  align-items: flex-end;
  padding: 16px;
  opacity: 0;
  transition: opacity 0.2s;
}

.work-card:hover .work-overlay {
  opacity: 1;
}

.work-stats {
  display: flex;
  gap: 16px;
  color: white;
  font-size: 14px;
}

.work-stats span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.work-info {
  padding: 16px;
}

.work-info h4 {
  margin: 0 0 12px 0;
  font-size: 16px;
  font-weight: 500;
  line-height: 1.4;
}

.work-author {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #666;
  font-size: 14px;
}

.loading-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
}

.load-more {
  text-align: center;
  margin-top: 32px;
}

@media (max-width: 768px) {
  .daily-photo {
    grid-template-columns: 1fr;
  }
  
  .daily-photo-image img {
    height: 250px;
  }
  
  .works-grid {
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
  }
}
</style>