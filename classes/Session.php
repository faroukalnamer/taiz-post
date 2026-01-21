<?php
/**
 * فئة الجلسة - Session Class
 */

class Session {
    public function __construct() {
        $this->checkRememberMe();
    }
    
    public function validateSession() {
        if (isset($_SESSION['user_id'])) {
            // التحقق من انتهاء الجلسة
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
                $this->destroy();
                return false;
            }
            
            // التحقق من IP و User Agent
            if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
                $this->destroy();
                return false;
            }
            
            $_SESSION['last_activity'] = time();
        }
        return true;
    }
    
    private function checkRememberMe() {
        if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
            $auth = new Auth();
            $auth->checkRememberCookie();
        }
    }
    
    public function destroy() {
        $_SESSION = [];
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        if (session_id()) {
            session_destroy();
        }
    }
    
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public function remove($key) {
        unset($_SESSION[$key]);
    }
    
    public function flash($key, $message = null, $type = 'info') {
        if ($message !== null) {
            $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
        } else {
            if (isset($_SESSION['flash'][$key])) {
                $flash = $_SESSION['flash'][$key];
                unset($_SESSION['flash'][$key]);
                return $flash;
            }
            return null;
        }
    }
}
