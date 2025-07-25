<template>
  <div class="work-detail">
    <div v-if="loading" class="loading">
      <el-skeleton :rows="8" animated />
    </div>
    
    <div v-else-if="work" class="work-content">
      <!-- Work Images -->
      <div class="work-images">
        <div class="main-image">
          <img 
            :src="currentImage.display_url || currentImage.hd_url" 
            :alt="work.title"
            @click="openImageViewer"
          />
          
          <div class="image-overlay">
            <div class="image-actions">
              <el-button 
                v-if="canDownload" 
                type="primary" 
                @click="downloadOriginal"
                :loading="downloading"
              >
                <el-icon><Download /></el-icon>
                {{ $t('work.download') }}
              </el-button>
            </div>
          </div>
        </div>
        
        <div v-if="work.resources.length > 1" class="image-thumbnails">
          <div 
            v-for="(resource, index) in work.resources" 
            :key="resource.id"
            class="thumbnail"
            :class="{ active: currentImageIndex === index }"
            @click="currentImageIndex = index"
          >
            <img 
              :src="resource.thumbnail_url" 
              :alt="`Image ${index + 1}`"
            />
          </div>
        </div>
      </div>
      
      <!-- Work Info -->
      <div class="work-info">
        <div class="work-header">
          <h1>{{ work.title }}</h1>
          
          <div class="work-stats">
            <span><el-icon><View /></el-icon> {{ work.views_count }}</span>
            <span><el-icon><Star /></el-icon> {{ work.likes_count }}</span>
          </div>
        </div>
        
        <div class="work-author">
          <el-avatar 
            :src="work.user.avatar" 
            :size="48"
          >
            {{ work.user.nickname?.[0] }}
          </el-avatar>
          
          <div class="author-info">
            <h3>{{ work.user.nickname }}</h3>
            <p>Level {{ work.user.level }}</p>
          </div>
          
          <div class="author-actions">
            <el-button 
              v-if="!isOwnWork && canFollow" 
              :type="isFollowing ? 'default' : 'primary'"
              @click="toggleFollow"
              :loading="followLoading"
            >
              {{ isFollowing ? '已关注' : '关注' }}
            </el-button>
          </div>
        </div>
        
        <div v-if="work.content" class="work-description">
          <div class="content-text" v-html="work.content"></div>
        </div>
        
        <div v-if="work.tags && work.tags.length > 0" class="work-tags">
          <h4>标签</h4>
          <div class="tags-list">
            <el-tag 
              v-for="tag in work.tags" 
              :key="tag"
              size="small"
            >
              {{ tag }}
            </el-tag>
          </div>
        </div>
        
        <!-- EXIF Information -->
        <div v-if="currentImage.exif_data && Object.keys(currentImage.exif_data).length > 0" class="exif-info">
          <h4>拍摄信息</h4>
          <div class="exif-grid">
            <div v-if="currentImage.exif_data.make" class="exif-item">
              <span class="label">{{ $t('work.camera') }}:</span>
              <span class="value">{{ currentImage.exif_data.make }} {{ currentImage.exif_data.model }}</span>
            </div>
            
            <div v-if="currentImage.exif_data.lens_model" class="exif-item">
              <span class="label">{{ $t('work.lens') }}:</span>
              <span class="value">{{ currentImage.exif_data.lens_model }}</span>
            </div>
            
            <div v-if="currentImage.exif_data.focal_length" class="exif-item">
              <span class="label">焦距:</span>
              <span class="value">{{ currentImage.exif_data.focal_length }}</span>
            </div>
            
            <div v-if="currentImage.exif_data.aperture" class="exif-item">
              <span class="label">光圈:</span>
              <span class="value">{{ currentImage.exif_data.aperture }}</span>
            </div>
            
            <div v-if="currentImage.exif_data.shutter_speed" class="exif-item">
              <span class="label">快门:</span>
              <span class="value">{{ currentImage.exif_data.shutter_speed }}</span>
            </div>
            
            <div v-if="currentImage.exif_data.iso" class="exif-item">
              <span class="label">ISO:</span>
              <span class="value">{{ currentImage.exif_data.iso }}</span>
            </div>
          </div>
        </div>
        
        <!-- Actions -->
        <div class="work-actions">
          <el-button 
            v-if="canLike"
            :type="work.is_liked ? 'primary' : 'default'" 
            @click="toggleLike"
            :loading="likingLoading"
          >
            <el-icon><Star /></el-icon>
            {{ work.is_liked ? $t('work.unlike') : $t('work.like') }}
          </el-button>
          
          <el-button @click="shareWork">
            <el-icon><Share /></el-icon>
            分享
          </el-button>
        </div>
      </div>
    </div>
    
    <div v-else class="error-state">
      <el-empty description="作品不存在或已被删除" />
      <el-button @click="$router.push('/')">返回首页</el-button>
    </div>
    
    <!-- Image Viewer Dialog -->
    <el-dialog 
      v-model="imageViewerVisible" 
      fullscreen
      class="image-viewer"
    >
      <div class="viewer-content">
        <img 
          :src="currentImage.hd_url || currentImage.display_url" 
          :alt="work?.title"
        />
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStore } from 'vuex'
import { ElMessage } from 'element-plus'
import { View, Star, Download, Share } from '@element-plus/icons-vue'
import api from '../api'

export default {
  name: 'WorkDetail',
  components: {
    View,
    Star,
    Download,
    Share
  },
  setup() {
    const route = useRoute()
    const router = useRouter()
    const store = useStore()
    
    const loading = ref(false)
    const work = ref(null)
    const currentImageIndex = ref(0)
    const imageViewerVisible = ref(false)
    const downloading = ref(false)
    const likingLoading = ref(false)
    const followLoading = ref(false)
    const isFollowing = ref(false)
    
    const currentUser = computed(() => store.getters.user)
    const canLike = computed(() => store.getters.canLike)
    const canDownload = computed(() => currentUser.value && currentUser.value.level >= 1)
    
    const currentImage = computed(() => {
      return work.value?.resources?.[currentImageIndex.value] || {}
    })
    
    const isOwnWork = computed(() => {
      return currentUser.value && work.value && currentUser.value.id === work.value.user.id
    })
    
    const canFollow = computed(() => {
      return currentUser.value && !isOwnWork.value
    })
    
    const fetchWork = async () => {
      loading.value = true
      try {
        const response = await api.get(`/works/${route.params.id}`)
        work.value = response.data.work
        isFollowing.value = work.value.user.is_following || false
      } catch (error) {
        if (error.response?.status === 404) {
          work.value = null
        } else {
          ElMessage.error('加载失败')
        }
        console.error('Failed to fetch work:', error)
      } finally {
        loading.value = false
      }
    }
    
    const toggleLike = async () => {
      if (!canLike.value) {
        ElMessage.warning('需要完成答题测试才能点赞')
        return
      }
      
      likingLoading.value = true
      try {
        if (work.value.is_liked) {
          await api.delete(`/works/${work.value.id}/like`)
          work.value.is_liked = false
          work.value.likes_count--
        } else {
          await api.post(`/works/${work.value.id}/like`)
          work.value.is_liked = true
          work.value.likes_count++
        }
      } catch (error) {
        ElMessage.error('操作失败')
        console.error('Like toggle failed:', error)
      } finally {
        likingLoading.value = false
      }
    }
    
    const toggleFollow = async () => {
      if (!canFollow.value) return
      
      followLoading.value = true
      try {
        if (isFollowing.value) {
          await api.delete(`/users/${work.value.user.id}/follow`)
          isFollowing.value = false
          ElMessage.success('已取消关注')
        } else {
          await api.post(`/users/${work.value.user.id}/follow`)
          isFollowing.value = true
          ElMessage.success('关注成功')
        }
      } catch (error) {
        ElMessage.error('操作失败')
        console.error('Follow toggle failed:', error)
      } finally {
        followLoading.value = false
      }
    }
    
    const downloadOriginal = async () => {
      if (!canDownload.value) {
        ElMessage.warning('需要完成答题测试才能下载原图')
        return
      }
      
      downloading.value = true
      try {
        const response = await api.get(`/resources/${currentImage.value.id}/download`)
        const downloadUrl = response.data.download_url
        
        // Open download URL in new tab
        window.open(downloadUrl, '_blank')
        
        ElMessage.success('下载开始')
      } catch (error) {
        if (error.response?.status === 429) {
          ElMessage.warning('今日下载次数已达上限')
        } else {
          ElMessage.error('下载失败')
        }
        console.error('Download failed:', error)
      } finally {
        downloading.value = false
      }
    }
    
    const openImageViewer = () => {
      imageViewerVisible.value = true
    }
    
    const shareWork = () => {
      if (navigator.share) {
        navigator.share({
          title: work.value.title,
          text: `查看 ${work.value.user.nickname} 的摄影作品`,
          url: window.location.href
        })
      } else {
        // Fallback: copy URL to clipboard
        navigator.clipboard.writeText(window.location.href).then(() => {
          ElMessage.success('链接已复制到剪贴板')
        })
      }
    }
    
    watch(() => route.params.id, (newId) => {
      if (newId) {
        fetchWork()
      }
    })
    
    onMounted(() => {
      fetchWork()
    })
    
    return {
      loading,
      work,
      currentImageIndex,
      currentImage,
      imageViewerVisible,
      downloading,
      likingLoading,
      followLoading,
      isFollowing,
      canLike,
      canDownload,
      isOwnWork,
      canFollow,
      toggleLike,
      toggleFollow,
      downloadOriginal,
      openImageViewer,
      shareWork
    }
  }
}
</script>

<style scoped>
.work-detail {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.loading {
  max-width: 800px;
  margin: 0 auto;
}

.work-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 40px;
}

.work-images {
  position: sticky;
  top: 20px;
  height: fit-content;
}

.main-image {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 16px;
  cursor: pointer;
  group: hover;
}

.main-image img {
  width: 100%;
  height: auto;
  display: block;
}

.image-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.5));
  display: flex;
  align-items: flex-end;
  padding: 20px;
  opacity: 0;
  transition: opacity 0.3s;
}

.main-image:hover .image-overlay {
  opacity: 1;
}

.image-actions {
  display: flex;
  gap: 12px;
}

.image-thumbnails {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding: 4px 0;
}

.thumbnail {
  width: 60px;
  height: 60px;
  border-radius: 6px;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  transition: border-color 0.2s;
  flex-shrink: 0;
}

.thumbnail.active {
  border-color: #409eff;
}

.thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.work-info {
  padding: 20px 0;
}

.work-header {
  margin-bottom: 24px;
}

.work-header h1 {
  margin: 0 0 12px 0;
  font-size: 28px;
  font-weight: 600;
  color: #333;
}

.work-stats {
  display: flex;
  gap: 20px;
  color: #666;
  font-size: 14px;
}

.work-stats span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.work-author {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px 0;
  border-bottom: 1px solid #e6e6e6;
  margin-bottom: 24px;
}

.author-info {
  flex: 1;
}

.author-info h3 {
  margin: 0 0 4px 0;
  font-size: 16px;
  font-weight: 500;
}

.author-info p {
  margin: 0;
  font-size: 12px;
  color: #666;
}

.work-description {
  margin-bottom: 24px;
}

.content-text {
  line-height: 1.6;
  color: #333;
}

.work-tags {
  margin-bottom: 24px;
}

.work-tags h4 {
  margin: 0 0 12px 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.tags-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.exif-info {
  margin-bottom: 32px;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
}

.exif-info h4 {
  margin: 0 0 16px 0;
  font-size: 14px;
  font-weight: 600;
  color: #333;
}

.exif-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.exif-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.exif-item .label {
  font-size: 12px;
  color: #666;
}

.exif-item .value {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.work-actions {
  display: flex;
  gap: 12px;
  padding-top: 24px;
  border-top: 1px solid #e6e6e6;
}

.error-state {
  text-align: center;
  padding: 60px 20px;
}

.image-viewer .viewer-content {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background: #000;
}

.image-viewer img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

@media (max-width: 968px) {
  .work-content {
    grid-template-columns: 1fr;
    gap: 24px;
  }
  
  .work-images {
    position: static;
  }
  
  .work-actions {
    flex-direction: column;
  }
}

@media (max-width: 768px) {
  .work-detail {
    padding: 12px;
  }
  
  .work-author {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .exif-grid {
    grid-template-columns: 1fr;
  }
}
</style>