-- =====================================================
-- قاعدة بيانات موقع المقالات
-- Articles Website Database
-- =====================================================

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS proo_articles 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE proo_articles;

-- =====================================================
-- جدول المستخدمين
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default.png',
    role ENUM('admin', 'moderator', 'member') NOT NULL DEFAULT 'member',
    status ENUM('pending', 'active', 'suspended', 'banned') NOT NULL DEFAULT 'pending',
    activation_token VARCHAR(64) DEFAULT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    remember_token VARCHAR(64) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    login_attempts INT UNSIGNED DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_role (role),
    INDEX idx_activation_token (activation_token),
    INDEX idx_reset_token (reset_token),
    INDEX idx_remember_token (remember_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول الجلسات
-- =====================================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    payload TEXT NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول سجل تسجيل الدخول
-- =====================================================
CREATE TABLE IF NOT EXISTS login_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    username VARCHAR(50) DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    status ENUM('success', 'failed', 'locked') NOT NULL,
    failure_reason VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول التصنيفات
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50) DEFAULT '📁',
    color VARCHAR(7) DEFAULT '#6366f1',
    parent_id INT UNSIGNED DEFAULT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent_id (parent_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول المقالات
-- =====================================================
CREATE TABLE IF NOT EXISTS articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255) DEFAULT NULL,
    status ENUM('draft', 'pending', 'published', 'archived') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) DEFAULT 0,
    views INT UNSIGNED DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_slug (slug),
    INDEX idx_published_at (published_at),
    INDEX idx_is_featured (is_featured),
    FULLTEXT idx_fulltext (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول التعليقات
-- =====================================================
CREATE TABLE IF NOT EXISTS comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED DEFAULT NULL,
    parent_id INT UNSIGNED DEFAULT NULL,
    author_name VARCHAR(100) DEFAULT NULL,
    author_email VARCHAR(100) DEFAULT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam', 'trash') NOT NULL DEFAULT 'pending',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE,
    INDEX idx_article_id (article_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول الصلاحيات
-- =====================================================
CREATE TABLE IF NOT EXISTS permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول صلاحيات الأدوار
-- =====================================================
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'moderator', 'member') NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role, permission_id),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- جدول إعدادات الموقع
-- =====================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- إدخال البيانات الافتراضية
-- =====================================================

-- إنشاء حساب المدير الافتراضي (كلمة السر: Admin@123)
INSERT INTO users (username, email, password, full_name, role, status) VALUES
('admin', 'admin@example.com', '$2y$12$LQv3c1yqBwErKn8YB2leNOxLXzRIXDN.wy6jLwEqW3F9VJjV2S6Pu', 'مدير الموقع', 'admin', 'active');

-- إدخال التصنيفات الافتراضية
INSERT INTO categories (name, slug, description, icon, color) VALUES
('التقنية', 'technology', 'مقالات عن التقنية والبرمجة', '💻', '#6366f1'),
('الصحة', 'health', 'مقالات عن الصحة والعناية الشخصية', '🏥', '#10b981'),
('الثقافة', 'culture', 'مقالات ثقافية وأدبية', '📖', '#f59e0b'),
('السفر', 'travel', 'مقالات عن السفر والسياحة', '✈️', '#ec4899');

-- إدخال الصلاحيات
INSERT INTO permissions (name, description) VALUES
('manage_users', 'إدارة المستخدمين'),
('manage_articles', 'إدارة المقالات'),
('manage_comments', 'إدارة التعليقات'),
('manage_categories', 'إدارة التصنيفات'),
('manage_settings', 'إدارة الإعدادات'),
('activate_users', 'تنشيط حسابات المستخدمين'),
('view_dashboard', 'عرض لوحة التحكم'),
('manage_moderators', 'إدارة المشرفين'),
('create_article', 'إنشاء مقال'),
('edit_own_article', 'تعديل المقالات الخاصة'),
('delete_own_article', 'حذف المقالات الخاصة');

-- ربط الصلاحيات بالأدوار
-- صلاحيات المدير (جميع الصلاحيات)
INSERT INTO role_permissions (role, permission_id) 
SELECT 'admin', id FROM permissions;

-- صلاحيات المشرف
INSERT INTO role_permissions (role, permission_id) 
SELECT 'moderator', id FROM permissions WHERE name IN ('manage_articles', 'manage_comments', 'view_dashboard', 'activate_users', 'create_article', 'edit_own_article', 'delete_own_article');

-- صلاحيات العضو
INSERT INTO role_permissions (role, permission_id) 
SELECT 'member', id FROM permissions WHERE name IN ('create_article', 'edit_own_article', 'delete_own_article');

-- إدخال الإعدادات الافتراضية
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'مقالاتي', 'text', 'اسم الموقع'),
('site_description', 'منصة عربية للمقالات المتنوعة', 'text', 'وصف الموقع'),
('articles_per_page', '10', 'number', 'عدد المقالات في الصفحة'),
('allow_registration', '1', 'boolean', 'السماح بالتسجيل'),
('require_activation', '1', 'boolean', 'طلب تنشيط الحساب'),
('allow_comments', '1', 'boolean', 'السماح بالتعليقات'),
('moderate_comments', '1', 'boolean', 'مراجعة التعليقات قبل النشر');

-- =====================================================
-- إنشاء الـ Triggers
-- =====================================================

-- تحديث عدد محاولات تسجيل الدخول
DELIMITER //
CREATE TRIGGER after_failed_login
AFTER INSERT ON login_logs
FOR EACH ROW
BEGIN
    IF NEW.status = 'failed' AND NEW.user_id IS NOT NULL THEN
        UPDATE users 
        SET login_attempts = login_attempts + 1,
            locked_until = CASE 
                WHEN login_attempts + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                ELSE locked_until 
            END
        WHERE id = NEW.user_id;
    ELSEIF NEW.status = 'success' AND NEW.user_id IS NOT NULL THEN
        UPDATE users 
        SET login_attempts = 0, 
            locked_until = NULL,
            last_login = NOW()
        WHERE id = NEW.user_id;
    END IF;
END//
DELIMITER ;
