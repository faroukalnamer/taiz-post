<?php
/**
 * إعدادات قاعدة البيانات
 * Database Configuration
 */

// منع الوصول المباشر
if (!defined('SECURE_ACCESS')) {
    die('الوصول المباشر غير مسموح');
}

// إعدادات قاعدة البيانات
define('DB_HOST', 'localhost');
define('DB_NAME', 'proo_articles');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// إعدادات الموقع
define('SITE_NAME', 'مقالاتي');
define('SITE_URL', 'http://localhost/proo');
define('ADMIN_EMAIL', 'admin@example.com');

// إعدادات الأمان
define('HASH_COST', 12); // تكلفة تشفير bcrypt
define('SESSION_LIFETIME', 3600); // مدة الجلسة بالثواني (ساعة واحدة)
define('COOKIE_LIFETIME', 86400 * 30); // مدة الكوكي (30 يوم)
define('MAX_LOGIN_ATTEMPTS', 5); // الحد الأقصى لمحاولات تسجيل الدخول
define('LOCKOUT_TIME', 900); // وقت الحظر بالثواني (15 دقيقة)

// مفتاح التشفير للكوكيز
define('SECRET_KEY', 'your_secret_key_here_change_in_production_2026');

// أنواع المستخدمين
define('ROLE_ADMIN', 'admin');
define('ROLE_MODERATOR', 'moderator');
define('ROLE_MEMBER', 'member');
define('ROLE_GUEST', 'guest');

// حالات الحساب
define('STATUS_PENDING', 'pending');
define('STATUS_ACTIVE', 'active');
define('STATUS_SUSPENDED', 'suspended');
define('STATUS_BANNED', 'banned');

/**
 * فئة الاتصال بقاعدة البيانات
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // منع النسخ
    private function __clone() {}
    
    // منع إلغاء التسلسل
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * دالة مساعدة للحصول على اتصال قاعدة البيانات
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
