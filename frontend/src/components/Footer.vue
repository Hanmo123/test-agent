<template>
  <el-footer class="footer">
    <div class="footer-content">
      <div class="footer-section">
        <h3>{{ $t('app.name') }}</h3>
        <p>{{ $t('app.tagline') }}</p>
      </div>
      
      <div class="footer-section">
        <h4>产品</h4>
        <ul>
          <li><a href="/">首页</a></li>
          <li><a href="/upload">上传作品</a></li>
          <li><a href="/collections">作品集</a></li>
        </ul>
      </div>
      
      <div class="footer-section">
        <h4>社区</h4>
        <ul>
          <li><a href="/help">帮助中心</a></li>
          <li><a href="/guidelines">社区准则</a></li>
          <li><a href="/feedback">意见反馈</a></li>
        </ul>
      </div>
      
      <div class="footer-section">
        <h4>关于</h4>
        <ul>
          <li><a href="/about">关于我们</a></li>
          <li><a href="/privacy">隐私政策</a></li>
          <li><a href="/terms">服务条款</a></li>
        </ul>
      </div>
    </div>
    
    <div class="footer-bottom">
      <p>&copy; 2024 OnlyShots. All rights reserved.</p>
    </div>
  </el-footer>
</template>

<script>
export default {
  name: 'Footer'
}
</script>

<style scoped>
.footer {
  background: #f8f9fa;
  border-top: 1px solid #e6e6e6;
  padding: 40px 0 20px;
  margin-top: auto;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 40px;
}

.footer-section h3 {
  margin: 0 0 12px 0;
  font-size: 18px;
  color: #333;
}

.footer-section h4 {
  margin: 0 0 16px 0;
  font-size: 14px;
  color: #333;
  font-weight: 600;
}

.footer-section p {
  margin: 0;
  color: #666;
  font-size: 14px;
}

.footer-section ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-section li {
  margin-bottom: 8px;
}

.footer-section a {
  color: #666;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.2s;
}

.footer-section a:hover {
  color: #409eff;
}

.footer-bottom {
  max-width: 1200px;
  margin: 30px auto 0;
  padding: 20px 20px 0;
  border-top: 1px solid #e6e6e6;
  text-align: center;
}

.footer-bottom p {
  margin: 0;
  color: #999;
  font-size: 12px;
}

@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
    gap: 24px;
  }
}
</style>