<template>
  <div class="quiz">
    <div class="quiz-container">
      <div class="quiz-header">
        <h1>{{ $t('auth.quizTitle') }}</h1>
        <p>{{ $t('auth.quizDescription') }}</p>
        <div class="quiz-info">
          <el-tag type="warning">{{ $t('auth.quizPassing') }}</el-tag>
          <span class="question-count">{{ currentQuestionIndex + 1 }} / {{ questions.length }}</span>
        </div>
      </div>

      <div v-if="!showResult" class="quiz-content">
        <div v-if="loading" class="loading">
          <el-skeleton :rows="5" animated />
        </div>
        
        <div v-else-if="questions.length > 0" class="question-section">
          <div class="progress-bar">
            <el-progress 
              :percentage="((currentQuestionIndex + 1) / questions.length) * 100"
              :show-text="false"
              stroke-width="4"
            />
          </div>
          
          <div class="question">
            <h3>{{ currentQuestion.question }}</h3>
            
            <div class="options">
              <el-radio-group 
                v-model="answers[currentQuestionIndex]" 
                @change="handleAnswerChange"
              >
                <div 
                  v-for="(option, index) in currentQuestion.options" 
                  :key="index"
                  class="option-item"
                >
                  <el-radio :label="index" class="option-radio">
                    {{ option }}
                  </el-radio>
                </div>
              </el-radio-group>
            </div>
          </div>
          
          <div class="quiz-navigation">
            <el-button 
              @click="previousQuestion"
              :disabled="currentQuestionIndex === 0"
            >
              上一题
            </el-button>
            
            <el-button 
              v-if="currentQuestionIndex < questions.length - 1"
              type="primary"
              @click="nextQuestion"
              :disabled="answers[currentQuestionIndex] === undefined"
            >
              下一题
            </el-button>
            
            <el-button 
              v-else
              type="primary"
              @click="submitQuiz"
              :disabled="!allQuestionsAnswered"
              :loading="submitting"
            >
              {{ $t('auth.submit') }}
            </el-button>
          </div>
        </div>
      </div>

      <div v-else class="quiz-result">
        <div class="result-card">
          <div :class="['result-icon', resultPassed ? 'success' : 'failure']">
            <el-icon v-if="resultPassed"><Check /></el-icon>
            <el-icon v-else><Close /></el-icon>
          </div>
          
          <h2>{{ resultPassed ? $t('auth.passed') : $t('auth.failed') }}</h2>
          
          <div class="score-display">
            <span class="score-label">{{ $t('auth.score') }}:</span>
            <span class="score-value">{{ Math.round(result.score) }}%</span>
          </div>
          
          <div class="score-details">
            <p>正确答题: {{ result.correct_answers }} / {{ result.total_questions }}</p>
          </div>
          
          <div class="result-actions">
            <el-button 
              v-if="resultPassed" 
              type="primary" 
              @click="goToHome"
              size="large"
            >
              开始使用
            </el-button>
            
            <el-button 
              v-else 
              type="warning" 
              @click="retakeQuiz"
              size="large"
            >
              重新答题
            </el-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Check, Close } from '@element-plus/icons-vue'
import api from '../api'

export default {
  name: 'Quiz',
  components: {
    Check,
    Close
  },
  setup() {
    const store = useStore()
    const router = useRouter()
    
    const loading = ref(false)
    const submitting = ref(false)
    const showResult = ref(false)
    const questions = ref([])
    const answers = ref([])
    const currentQuestionIndex = ref(0)
    const result = ref(null)
    
    const currentQuestion = computed(() => {
      return questions.value[currentQuestionIndex.value]
    })
    
    const allQuestionsAnswered = computed(() => {
      return answers.value.every(answer => answer !== undefined)
    })
    
    const resultPassed = computed(() => {
      return result.value?.passed || false
    })
    
    const fetchQuestions = async () => {
      loading.value = true
      try {
        const response = await api.get('/auth/quiz')
        questions.value = response.data.questions
        answers.value = new Array(questions.value.length).fill(undefined)
      } catch (error) {
        ElMessage.error('获取题目失败')
        console.error('Failed to fetch quiz questions:', error)
      } finally {
        loading.value = false
      }
    }
    
    const handleAnswerChange = (value) => {
      answers.value[currentQuestionIndex.value] = value
    }
    
    const nextQuestion = () => {
      if (currentQuestionIndex.value < questions.value.length - 1) {
        currentQuestionIndex.value++
      }
    }
    
    const previousQuestion = () => {
      if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--
      }
    }
    
    const submitQuiz = async () => {
      submitting.value = true
      try {
        const answersData = questions.value.map((question, index) => ({
          question_id: question.id,
          selected_option: answers.value[index]
        }))
        
        const response = await store.dispatch('submitQuiz', answersData)
        result.value = response
        showResult.value = true
        
        if (response.passed) {
          ElMessage.success('恭喜通过测试！')
        } else {
          ElMessage.warning('测试未通过，请重新尝试')
        }
      } catch (error) {
        ElMessage.error('提交失败，请重试')
        console.error('Quiz submission failed:', error)
      } finally {
        submitting.value = false
      }
    }
    
    const retakeQuiz = () => {
      showResult.value = false
      currentQuestionIndex.value = 0
      answers.value = new Array(questions.value.length).fill(undefined)
      result.value = null
      fetchQuestions()
    }
    
    const goToHome = () => {
      router.push('/')
    }
    
    onMounted(() => {
      // Check if user needs to take quiz
      if (store.getters.userLevel > 0) {
        router.push('/')
        return
      }
      
      fetchQuestions()
    })
    
    return {
      loading,
      submitting,
      showResult,
      questions,
      answers,
      currentQuestionIndex,
      currentQuestion,
      allQuestionsAnswered,
      result,
      resultPassed,
      handleAnswerChange,
      nextQuestion,
      previousQuestion,
      submitQuiz,
      retakeQuiz,
      goToHome
    }
  }
}
</script>

<style scoped>
.quiz {
  min-height: calc(100vh - 120px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.quiz-container {
  max-width: 800px;
  width: 100%;
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
  overflow: hidden;
}

.quiz-header {
  padding: 32px 40px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  text-align: center;
}

.quiz-header h1 {
  margin: 0 0 12px 0;
  font-size: 28px;
  font-weight: 600;
}

.quiz-header p {
  margin: 0 0 24px 0;
  font-size: 16px;
  opacity: 0.9;
}

.quiz-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
}

.question-count {
  font-size: 14px;
  opacity: 0.8;
}

.quiz-content {
  padding: 40px;
}

.loading {
  text-align: center;
}

.progress-bar {
  margin-bottom: 32px;
}

.question h3 {
  margin: 0 0 24px 0;
  font-size: 20px;
  font-weight: 500;
  line-height: 1.5;
  color: #333;
}

.options {
  margin-bottom: 32px;
}

.option-item {
  margin-bottom: 16px;
}

.option-radio {
  width: 100%;
  padding: 16px;
  border: 2px solid #e6e6e6;
  border-radius: 8px;
  transition: all 0.2s;
  display: flex;
  align-items: center;
}

.option-radio:hover {
  border-color: #409eff;
  background: #f0f8ff;
}

.option-radio.is-checked {
  border-color: #409eff;
  background: #e6f3ff;
}

.quiz-navigation {
  display: flex;
  justify-content: space-between;
  padding-top: 24px;
  border-top: 1px solid #e6e6e6;
}

.quiz-result {
  padding: 40px;
  text-align: center;
}

.result-card {
  max-width: 400px;
  margin: 0 auto;
}

.result-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 24px;
  font-size: 32px;
}

.result-icon.success {
  background: #f0f9ff;
  color: #67c23a;
}

.result-icon.failure {
  background: #fef0f0;
  color: #f56c6c;
}

.result-card h2 {
  margin: 0 0 24px 0;
  font-size: 24px;
  font-weight: 600;
}

.score-display {
  margin-bottom: 16px;
}

.score-label {
  font-size: 18px;
  color: #666;
  margin-right: 8px;
}

.score-value {
  font-size: 28px;
  font-weight: 600;
  color: #333;
}

.score-details {
  margin-bottom: 32px;
  color: #666;
}

.result-actions {
  display: flex;
  justify-content: center;
  gap: 16px;
}

@media (max-width: 768px) {
  .quiz-container {
    margin: 20px;
  }
  
  .quiz-header,
  .quiz-content,
  .quiz-result {
    padding: 24px 20px;
  }
  
  .quiz-navigation {
    flex-direction: column;
    gap: 12px;
  }
}
</style>