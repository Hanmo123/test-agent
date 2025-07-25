import { createStore } from 'vuex'
import api from '../api'

export const store = createStore({
  state: {
    user: null,
    token: localStorage.getItem('token'),
    isLoggedIn: false,
    loading: false
  },
  
  mutations: {
    SET_USER(state, user) {
      state.user = user
      state.isLoggedIn = !!user
    },
    
    SET_TOKEN(state, token) {
      state.token = token
      if (token) {
        localStorage.setItem('token', token)
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
      } else {
        localStorage.removeItem('token')
        delete api.defaults.headers.common['Authorization']
      }
    },
    
    SET_LOADING(state, status) {
      state.loading = status
    },
    
    LOGOUT(state) {
      state.user = null
      state.token = null
      state.isLoggedIn = false
      localStorage.removeItem('token')
      delete api.defaults.headers.common['Authorization']
    }
  },
  
  actions: {
    async login({ commit }, logtoToken) {
      commit('SET_LOADING', true)
      try {
        const response = await api.post('/auth/login', { logto_token: logtoToken })
        const { user, token } = response.data
        
        commit('SET_USER', user)
        commit('SET_TOKEN', token)
        
        return response.data
      } catch (error) {
        throw error
      } finally {
        commit('SET_LOADING', false)
      }
    },
    
    async logout({ commit }) {
      try {
        await api.post('/auth/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        commit('LOGOUT')
      }
    },
    
    async fetchUser({ commit }) {
      try {
        const response = await api.get('/auth/me')
        commit('SET_USER', response.data.user)
        return response.data.user
      } catch (error) {
        commit('LOGOUT')
        throw error
      }
    },
    
    async submitQuiz({ commit }, answers) {
      const response = await api.post('/auth/quiz', { answers })
      if (response.data.passed) {
        const user = await this.dispatch('fetchUser')
        commit('SET_USER', user)
      }
      return response.data
    }
  },
  
  getters: {
    isLoggedIn: state => state.isLoggedIn,
    user: state => state.user,
    userLevel: state => state.user?.level || 0,
    needsQuiz: state => state.user?.level === 0,
    canUpload: state => (state.user?.level || 0) >= 1,
    canLike: state => (state.user?.level || 0) >= 1
  }
})