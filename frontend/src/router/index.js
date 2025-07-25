import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Quiz from '../views/Quiz.vue'
import Profile from '../views/Profile.vue'
import Upload from '../views/Upload.vue'
import WorkDetail from '../views/WorkDetail.vue'

export const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/quiz',
    name: 'Quiz',
    component: Quiz,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  },
  {
    path: '/upload',
    name: 'Upload',
    component: Upload,
    meta: { requiresAuth: true, requiresLevel: 1 }
  },
  {
    path: '/works/:id',
    name: 'WorkDetail',
    component: WorkDetail,
    props: true
  }
]