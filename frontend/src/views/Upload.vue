<template>
  <div class="upload">
    <div class="upload-container">
      <div class="upload-header">
        <h1>{{ $t('upload.title') }}</h1>
        <p>分享您的摄影作品，让更多人欣赏</p>
      </div>

      <div class="upload-content">
        <div class="file-upload-section">
          <div class="upload-area" 
               @drop="handleDrop" 
               @dragover.prevent 
               @dragenter.prevent
               :class="{ 'dragging': isDragging }"
               @dragenter="isDragging = true"
               @dragleave="isDragging = false"
          >
            <input 
              ref="fileInput"
              type="file" 
              multiple 
              accept="image/jpeg,image/jpg,image/png,image/heif,image/dng"
              @change="handleFileSelect"
              style="display: none"
            />
            
            <div class="upload-icon">
              <el-icon size="48" color="#409eff"><UploadFilled /></el-icon>
            </div>
            
            <div class="upload-text">
              <h3>{{ $t('upload.selectFiles') }}</h3>
              <p>{{ $t('upload.dragHere') }}</p>
            </div>
            
            <el-button 
              type="primary" 
              size="large"
              @click="$refs.fileInput.click()"
            >
              选择文件
            </el-button>
            
            <div class="upload-info">
              <p>{{ $t('upload.supportedFormats') }}</p>
              <p>{{ $t('upload.maxSize') }}</p>
              <p>{{ $t('upload.maxCount') }}</p>
            </div>
          </div>
        </div>

        <div v-if="selectedFiles.length > 0" class="files-preview">
          <h3>已选择文件 ({{ selectedFiles.length }})</h3>
          
          <div class="files-grid">
            <div 
              v-for="(file, index) in selectedFiles" 
              :key="index"
              class="file-item"
            >
              <div class="file-preview">
                <img 
                  v-if="file.preview" 
                  :src="file.preview" 
                  :alt="file.name"
                />
                <div v-else class="file-placeholder">
                  <el-icon><Document /></el-icon>
                </div>
              </div>
              
              <div class="file-info">
                <p class="file-name">{{ file.name }}</p>
                <p class="file-size">{{ formatFileSize(file.size) }}</p>
                
                <div v-if="file.uploading" class="upload-progress">
                  <el-progress 
                    :percentage="file.progress" 
                    :status="file.error ? 'exception' : undefined"
                  />
                  <span class="progress-text">
                    {{ file.error || (file.progress === 100 ? '完成' : '上传中...') }}
                  </span>
                </div>
              </div>
              
              <div class="file-actions">
                <el-button 
                  size="small" 
                  type="danger" 
                  text
                  @click="removeFile(index)"
                  :disabled="file.uploading"
                >
                  <el-icon><Delete /></el-icon>
                </el-button>
              </div>
            </div>
          </div>
          
          <div class="upload-actions">
            <el-button 
              type="primary" 
              size="large"
              @click="startUpload"
              :loading="uploading"
              :disabled="selectedFiles.length === 0"
            >
              开始上传
            </el-button>
            
            <el-button 
              size="large"
              @click="clearFiles"
              :disabled="uploading"
            >
              清空
            </el-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { UploadFilled, Document, Delete } from '@element-plus/icons-vue'
import api from '../api'

export default {
  name: 'Upload',
  components: {
    UploadFilled,
    Document,
    Delete
  },
  setup() {
    const router = useRouter()
    const fileInput = ref(null)
    const isDragging = ref(false)
    const uploading = ref(false)
    const selectedFiles = reactive([])
    
    const formatFileSize = (bytes) => {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }
    
    const validateFile = (file) => {
      const maxSize = 100 * 1024 * 1024 // 100MB
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/heif', 'image/dng']
      
      if (file.size > maxSize) {
        return '文件大小不能超过 100MB'
      }
      
      if (!allowedTypes.some(type => file.type.includes(type) || file.name.toLowerCase().endsWith(type.split('/')[1]))) {
        return '不支持的文件格式'
      }
      
      return null
    }
    
    const createFilePreview = (file) => {
      return new Promise((resolve) => {
        if (file.type.startsWith('image/')) {
          const reader = new FileReader()
          reader.onload = (e) => resolve(e.target.result)
          reader.onerror = () => resolve(null)
          reader.readAsDataURL(file)
        } else {
          resolve(null)
        }
      })
    }
    
    const addFiles = async (files) => {
      const newFiles = Array.from(files)
      
      if (selectedFiles.length + newFiles.length > 50) {
        ElMessage.warning('最多只能选择 50 个文件')
        return
      }
      
      for (const file of newFiles) {
        const error = validateFile(file)
        if (error) {
          ElMessage.error(`${file.name}: ${error}`)
          continue
        }
        
        const preview = await createFilePreview(file)
        
        selectedFiles.push({
          file,
          name: file.name,
          size: file.size,
          preview,
          uploading: false,
          progress: 0,
          error: null
        })
      }
    }
    
    const handleFileSelect = (event) => {
      const files = event.target.files
      if (files.length > 0) {
        addFiles(files)
      }
    }
    
    const handleDrop = (event) => {
      event.preventDefault()
      isDragging.value = false
      
      const files = event.dataTransfer.files
      if (files.length > 0) {
        addFiles(files)
      }
    }
    
    const removeFile = (index) => {
      selectedFiles.splice(index, 1)
    }
    
    const clearFiles = () => {
      selectedFiles.splice(0)
    }
    
    const uploadFile = async (fileItem, relationshipId) => {
      const formData = new FormData()
      formData.append('files[]', fileItem.file)
      formData.append('relationship_id', relationshipId)
      
      fileItem.uploading = true
      fileItem.progress = 0
      
      try {
        const response = await api.post('/resources/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          },
          onUploadProgress: (progressEvent) => {
            fileItem.progress = Math.round(
              (progressEvent.loaded * 100) / progressEvent.total
            )
          }
        })
        
        fileItem.progress = 100
        fileItem.uploading = false
        
        return response.data.results[0]
        
      } catch (error) {
        fileItem.error = error.response?.data?.message || '上传失败'
        fileItem.uploading = false
        throw error
      }
    }
    
    const startUpload = async () => {
      if (selectedFiles.length === 0) return
      
      uploading.value = true
      
      try {
        // Get user's personal relationship ID (assuming it exists)
        const userResponse = await api.get('/auth/me')
        const personalRelationship = userResponse.data.user.relationships.find(r => r.type === 'personal')
        
        if (!personalRelationship) {
          throw new Error('未找到个人关系')
        }
        
        const results = []
        
        // Upload files in batches to avoid overwhelming the server
        const batchSize = 5
        for (let i = 0; i < selectedFiles.length; i += batchSize) {
          const batch = selectedFiles.slice(i, i + batchSize)
          
          const batchPromises = batch.map(fileItem => 
            uploadFile(fileItem, personalRelationship.id)
          )
          
          try {
            const batchResults = await Promise.allSettled(batchPromises)
            results.push(...batchResults)
          } catch (error) {
            console.error('Batch upload error:', error)
          }
        }
        
        const successful = results.filter(r => r.status === 'fulfilled').length
        const failed = results.length - successful
        
        if (successful > 0) {
          ElMessage.success(`成功上传 ${successful} 个文件` + (failed > 0 ? `，${failed} 个失败` : ''))
        }
        
        if (failed === 0) {
          // All files uploaded successfully, redirect to library or create drafts
          setTimeout(() => {
            router.push('/profile')
          }, 2000)
        }
        
      } catch (error) {
        ElMessage.error('上传失败: ' + (error.message || '未知错误'))
        console.error('Upload error:', error)
      } finally {
        uploading.value = false
      }
    }
    
    return {
      fileInput,
      isDragging,
      uploading,
      selectedFiles,
      formatFileSize,
      handleFileSelect,
      handleDrop,
      removeFile,
      clearFiles,
      startUpload
    }
  }
}
</script>

<style scoped>
.upload {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.upload-header {
  text-align: center;
  margin-bottom: 40px;
}

.upload-header h1 {
  margin: 0 0 12px 0;
  font-size: 32px;
  font-weight: 600;
  color: #333;
}

.upload-header p {
  margin: 0;
  font-size: 16px;
  color: #666;
}

.upload-area {
  border: 2px dashed #d9d9d9;
  border-radius: 16px;
  padding: 60px 40px;
  text-align: center;
  background: #fafafa;
  transition: all 0.3s;
  cursor: pointer;
}

.upload-area:hover,
.upload-area.dragging {
  border-color: #409eff;
  background: #f0f8ff;
}

.upload-icon {
  margin-bottom: 20px;
}

.upload-text h3 {
  margin: 0 0 8px 0;
  font-size: 20px;
  color: #333;
}

.upload-text p {
  margin: 0 0 24px 0;
  color: #666;
}

.upload-info {
  margin-top: 24px;
}

.upload-info p {
  margin: 4px 0;
  font-size: 12px;
  color: #999;
}

.files-preview {
  margin-top: 40px;
}

.files-preview h3 {
  margin-bottom: 20px;
  font-size: 18px;
  color: #333;
}

.files-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.file-item {
  background: white;
  border: 1px solid #e6e6e6;
  border-radius: 8px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.file-preview {
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

.file-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.file-placeholder {
  color: #999;
  font-size: 24px;
}

.file-info {
  flex: 1;
  min-width: 0;
}

.file-name {
  margin: 0 0 4px 0;
  font-weight: 500;
  truncate: true;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.file-size {
  margin: 0 0 8px 0;
  font-size: 12px;
  color: #666;
}

.upload-progress {
  margin-top: 8px;
}

.progress-text {
  font-size: 12px;
  color: #666;
  margin-top: 4px;
  display: block;
}

.file-actions {
  flex-shrink: 0;
}

.upload-actions {
  display: flex;
  justify-content: center;
  gap: 16px;
  padding-top: 24px;
  border-top: 1px solid #e6e6e6;
}

@media (max-width: 768px) {
  .upload {
    padding: 12px;
  }
  
  .upload-area {
    padding: 40px 20px;
  }
  
  .files-grid {
    grid-template-columns: 1fr;
  }
  
  .upload-actions {
    flex-direction: column;
  }
}
</style>