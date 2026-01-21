<?php
/**
 * فئة المستخدم - User Class
 */

class User {
    private $db;
    private $table = 'users';
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function findByUsernameOrEmail($identifier) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        $activationToken = bin2hex(random_bytes(32));
        
        $sql = "INSERT INTO {$this->table} (username, email, password, full_name, role, status, activation_token) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['username'], $data['email'], $hashedPassword, $data['full_name'],
            $data['role'] ?? ROLE_MEMBER, $data['status'] ?? STATUS_PENDING, $activationToken
        ]);
        
        return $result ? ['id' => $this->db->lastInsertId(), 'activation_token' => $activationToken] : false;
    }
    
    public function update($id, $data) {
        $fields = [];
        $values = [];
        $allowed = ['username', 'email', 'full_name', 'avatar', 'role', 'status'];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }
        
        if (!empty($data['password'])) {
            $fields[] = "password = ?";
            $values[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        }
        
        if (empty($fields)) return false;
        
        $values[] = $id;
        $stmt = $this->db->prepare("UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function activate($token) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE activation_token = ? AND status = ?");
        $stmt->execute([$token, STATUS_PENDING]);
        $user = $stmt->fetch();
        
        if ($user) {
            $updateStmt = $this->db->prepare("UPDATE {$this->table} SET status = ?, activation_token = NULL WHERE id = ?");
            return $updateStmt->execute([STATUS_ACTIVE, $user['id']]);
        }
        return false;
    }
    
    public function activateByAdmin($userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ?, activation_token = NULL WHERE id = ?");
        return $stmt->execute([STATUS_ACTIVE, $userId]);
    }
    
    public function suspend($userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
        return $stmt->execute([STATUS_SUSPENDED, $userId]);
    }
    
    public function ban($userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ? WHERE id = ?");
        return $stmt->execute([STATUS_BANNED, $userId]);
    }
    
    public function updateRememberToken($userId, $token = null) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET remember_token = ? WHERE id = ?");
        return $stmt->execute([$token, $userId]);
    }
    
    public function findByRememberToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE remember_token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }
    
    public function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET last_login = NOW(), login_attempts = 0, locked_until = NULL WHERE id = ?");
        return $stmt->execute([$userId]);
    }
    
    public function isLocked($userId) {
        $user = $this->findById($userId);
        return $user && $user['locked_until'] && strtotime($user['locked_until']) > time();
    }
    
    public function incrementLoginAttempts($userId) {
        $user = $this->findById($userId);
        $attempts = $user['login_attempts'] + 1;
        $lockedUntil = $attempts >= MAX_LOGIN_ATTEMPTS ? date('Y-m-d H:i:s', time() + LOCKOUT_TIME) : null;
        
        $stmt = $this->db->prepare("UPDATE {$this->table} SET login_attempts = ?, locked_until = ? WHERE id = ?");
        return $stmt->execute([$attempts, $lockedUntil, $userId]);
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if (!empty($filters['role'])) { $sql .= " AND role = ?"; $params[] = $filters['role']; }
        if (!empty($filters['status'])) { $sql .= " AND status = ?"; $params[] = $filters['status']; }
        if (!empty($filters['search'])) {
            $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
            $term = "%{$filters['search']}%";
            $params = array_merge($params, [$term, $term, $term]);
        }
        
        $sql .= " ORDER BY created_at DESC";
        if (isset($filters['limit'])) { $sql .= " LIMIT " . (int)$filters['limit']; }
        if (isset($filters['offset'])) { $sql .= " OFFSET " . (int)$filters['offset']; }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];
        if (!empty($filters['role'])) { $sql .= " AND role = ?"; $params[] = $filters['role']; }
        if (!empty($filters['status'])) { $sql .= " AND status = ?"; $params[] = $filters['status']; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    public function changeRole($userId, $role) {
        if (!in_array($role, [ROLE_ADMIN, ROLE_MODERATOR, ROLE_MEMBER])) return false;
        $stmt = $this->db->prepare("UPDATE {$this->table} SET role = ? WHERE id = ?");
        return $stmt->execute([$role, $userId]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
