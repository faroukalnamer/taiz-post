<?php
/**
 * ملف التهيئة الرئيسي
 * Main Initialization File
 */

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    // إعدادات أمان الجلسة
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // تغيير إلى 1 في الإنتاج مع HTTPS
    
    session_start();
}

// تعريف ثابت الوصول الآمن
define('SECURE_ACCESS', true);

// تضمين الملفات المطلوبة
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../classes/Validator.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Session.php';

// تهيئة الجلسة
$session = new Session();

// التحقق من صلاحية الجلسة
$session->validateSession();

// دالة مساعدة للهروب من HTML
function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// دالة مساعدة لإعادة التوجيه
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit;
}

// دالة مساعدة لعرض رسائل الفلاش
function flash($key, $message = null, $type = 'info') {
    if ($message !== null) {
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    } else {
        if (isset($_SESSION['flash'][$key])) {
            $flash = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $flash;
        }
        return null;
    }
}

// دالة للتحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// دالة للحصول على معلومات المستخدم الحالي
function currentUser() {
    if (isLoggedIn()) {
        static $user = null;
        if ($user === null) {
            $userObj = new User();
            $user = $userObj->findById($_SESSION['user_id']);
        }
        return $user;
    }
    return null;
}

// دالة للتحقق من الصلاحيات
function hasPermission($permission) {
    $user = currentUser();
    if (!$user) return false;
    
    $permissions = [
        'admin' => ['manage_users', 'manage_articles', 'manage_settings', 'activate_users', 'view_dashboard', 'manage_moderators'],
        'moderator' => ['manage_articles', 'view_dashboard', 'activate_users'],
        'member' => ['create_article', 'edit_own_article'],
        'guest' => ['view_articles']
    ];
    
    $role = $user['role'];
    
    // المدير لديه جميع الصلاحيات
    if ($role === ROLE_ADMIN) return true;
    
    return isset($permissions[$role]) && in_array($permission, $permissions[$role]);
}

// دالة للتحقق من الدور
function hasRole($role) {
    $user = currentUser();
    if (!$user) return false;
    
    if (is_array($role)) {
        return in_array($user['role'], $role);
    }
    
    return $user['role'] === $role;
}

// دالة لتوليد توكن CSRF
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// دالة للتحقق من توكن CSRF
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// دالة لعرض حقل توكن CSRF
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}
