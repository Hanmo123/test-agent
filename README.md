# OnlyShots - 摄影社区

OnlyShots 是一个专为摄影爱好者和专业摄影师打造的作品分享与资源管理社区平台。

## 功能特性

### 核心功能
- **用户认证系统** - 基于 Logto 的多平台登录（邮箱、GitHub、微信、Apple）
- **答题注册系统** - 摄影知识测试，80分及格解锁完整功能
- **无损原图托管** - 支持 JPG、PNG、HEIF、RAW (DNG) 格式，最大 100MB
- **EXIF 信息管理** - 自动提取并展示拍摄参数，支持隐私保护
- **作品发布系统** - 富文本编辑器，支持多图作品，自动内容审核
- **快门时间机制** - 游戏化积分系统，每日登录奖励，会员翻倍
- **用户等级系统** - 0级（未答题）、1级（基础功能）、X级（高级功能）

### 交互功能
- **点赞关注系统** - 1级以上用户可点赞作品，关注摄影师
- **作品集管理** - 创建个人作品集，支持排序和分类
- **每日一图** - 运营精选优秀作品展示
- **个人资料** - 三档可见性（公开/仅粉丝/私密）

### 会员权益
- **无损原图查看** - 会员可查看完整分辨率图片
- **快门时间翻倍** - 每日登录奖励 x2
- **自定义水印** - 支持上传 PNG 水印图片
- **学生优惠** - 教育邮箱验证享 50% 折扣

## 技术架构

### 后端 (Laravel)
- **框架**: Laravel 10.x
- **数据库**: MySQL
- **认证**: Laravel Sanctum + Logto JWT
- **文件存储**: 阿里云 OSS
- **图片处理**: Intervention Image
- **内容审核**: 阿里云内容安全

### 前端 (Vue.js)
- **框架**: Vue 3 + Composition API
- **路由**: Vue Router 4
- **状态管理**: Vuex 4
- **UI 组件**: Element Plus
- **国际化**: Vue I18n
- **构建工具**: Vite

## 安装部署

### 环境要求
- PHP 8.1+
- Node.js 16+
- MySQL 8.0+
- Composer
- npm/yarn

### 后端安装

```bash
cd backend
composer install
cp .env.example .env
# 编辑 .env 文件，配置数据库和第三方服务
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

### 前端安装

```bash
cd frontend
npm install
npm run dev
```

## 数据库结构

### 核心表设计
- `users` - 用户基础信息
- `quiz_questions` - 答题题库  
- `relationships` - 用户关系（个人/机构/品牌）
- `resources` - 图片资源管理
- `works` - 发布作品
- `drafts` - 草稿管理
- `collections` - 作品集
- `likes` / `follows` - 交互关系
- `shutter_times` - 快门时间账户
- `orders` - 订单记录

## API 接口

### 认证相关
```
POST /api/v1/auth/login          # 登录
POST /api/v1/auth/logout         # 登出
GET  /api/v1/auth/me             # 获取用户信息
GET  /api/v1/auth/quiz           # 获取答题
POST /api/v1/auth/quiz           # 提交答题
```

### 资源管理
```
GET    /api/v1/resources         # 资源列表
POST   /api/v1/resources/upload  # 上传文件
GET    /api/v1/resources/{id}    # 资源详情
GET    /api/v1/resources/{id}/download # 下载原图
DELETE /api/v1/resources/{id}    # 删除资源
```

### 作品管理
```
GET    /api/v1/works             # 作品列表
POST   /api/v1/works             # 发布作品
GET    /api/v1/works/{id}        # 作品详情
PATCH  /api/v1/works/{id}        # 编辑作品
DELETE /api/v1/works/{id}        # 删除作品
POST   /api/v1/works/{id}/like   # 点赞
DELETE /api/v1/works/{id}/like   # 取消点赞
```

## 项目结构

```
onlyshots/
├── backend/                 # Laravel 后端
│   ├── app/
│   │   ├── Http/Controllers/Api/V1/  # API 控制器
│   │   ├── Models/          # 数据模型
│   │   └── Services/        # 业务逻辑服务
│   ├── database/
│   │   ├── migrations/      # 数据库迁移
│   │   └── seeders/         # 数据填充
│   └── routes/api.php       # API 路由
├── frontend/                # Vue.js 前端
│   ├── src/
│   │   ├── components/      # 通用组件
│   │   ├── views/           # 页面组件
│   │   ├── store/           # Vuex 状态管理
│   │   └── api/             # API 请求
│   └── package.json
└── README.md
```

## 贡献指南

1. Fork 项目
2. 创建特性分支 (`git checkout -b feature/amazing-feature`)
3. 提交更改 (`git commit -m 'Add amazing feature'`)
4. 推送分支 (`git push origin feature/amazing-feature`)
5. 创建 Pull Request

---

© 2024 OnlyShots. All rights reserved.