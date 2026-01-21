<aside class="sidebar">
    <div class="sidebar-header">
        <a href="../index.php" class="logo">📚 <?= SITE_NAME ?></a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span>
            <span>لوحة التحكم</span>
        </a>
        
        <?php if (hasRole(ROLE_ADMIN)): ?>
        <a href="users.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : '' ?>">
            <span class="nav-icon">👥</span>
            <span>المستخدمين</span>
        </a>
        <a href="moderators.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'moderators.php' ? 'active' : '' ?>">
            <span class="nav-icon">🛡️</span>
            <span>المشرفين</span>
        </a>
        <?php endif; ?>
        
        <a href="articles.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'articles.php' ? 'active' : '' ?>">
            <span class="nav-icon">📝</span>
            <span>المقالات</span>
        </a>
        
        <a href="categories.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>">
            <span class="nav-icon">📁</span>
            <span>التصنيفات</span>
        </a>
        
        <?php if (hasRole(ROLE_ADMIN)): ?>
        <a href="settings.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : '' ?>">
            <span class="nav-icon">⚙️</span>
            <span>الإعدادات</span>
        </a>
        <?php endif; ?>
    </nav>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?= mb_substr($user['full_name'], 0, 1) ?></div>
            <div class="user-details">
                <span class="user-name"><?= escape($user['full_name']) ?></span>
                <span class="user-role"><?= $user['role'] === 'admin' ? 'مدير' : 'مشرف' ?></span>
            </div>
        </div>
        <a href="../logout.php" class="logout-btn">🚪 خروج</a>
    </div>
</aside>
