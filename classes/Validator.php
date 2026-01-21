<?php
/**
 * فئة التحقق من صحة البيانات
 * Data Validation Class
 */

class Validator {
    private $errors = [];
    private $data = [];
    private $db;
    
    public function __construct($data = []) {
        $this->data = $data;
        $this->db = getDB();
    }
    
    /**
     * تعيين البيانات
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    
    /**
     * الحصول على الأخطاء
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * التحقق من وجود أخطاء
     */
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    /**
     * الحصول على أول خطأ
     */
    public function getFirstError() {
        return !empty($this->errors) ? reset($this->errors) : null;
    }
    
    /**
     * إضافة خطأ
     */
    public function addError($field, $message) {
        $this->errors[$field] = $message;
        return $this;
    }
    
    /**
     * مسح الأخطاء
     */
    public function clearErrors() {
        $this->errors = [];
        return $this;
    }
    
    /**
     * التحقق من أن الحقل مطلوب
     */
    public function required($field, $message = null) {
        $value = $this->getValue($field);
        if (empty($value) && $value !== '0') {
            $this->errors[$field] = $message ?? "حقل {$field} مطلوب";
        }
        return $this;
    }
    
    /**
     * التحقق من البريد الإلكتروني
     */
    public function email($field, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? "البريد الإلكتروني غير صالح";
        }
        return $this;
    }
    
    /**
     * التحقق من الحد الأدنى للطول
     */
    public function minLength($field, $length, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && mb_strlen($value) < $length) {
            $this->errors[$field] = $message ?? "يجب أن يكون {$field} على الأقل {$length} أحرف";
        }
        return $this;
    }
    
    /**
     * التحقق من الحد الأقصى للطول
     */
    public function maxLength($field, $length, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && mb_strlen($value) > $length) {
            $this->errors[$field] = $message ?? "يجب ألا يتجاوز {$field} {$length} حرف";
        }
        return $this;
    }
    
    /**
     * التحقق من تطابق الحقول
     */
    public function matches($field, $matchField, $message = null) {
        $value = $this->getValue($field);
        $matchValue = $this->getValue($matchField);
        if ($value !== $matchValue) {
            $this->errors[$field] = $message ?? "الحقول غير متطابقة";
        }
        return $this;
    }
    
    /**
     * التحقق من القيمة الفريدة في قاعدة البيانات
     */
    public function unique($field, $table, $column = null, $exceptId = null, $message = null) {
        $value = $this->getValue($field);
        $column = $column ?? $field;
        
        if (!empty($value)) {
            $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
            $params = [$value];
            
            if ($exceptId !== null) {
                $sql .= " AND id != ?";
                $params[] = $exceptId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            if ($stmt->fetchColumn() > 0) {
                $this->errors[$field] = $message ?? "هذه القيمة مستخدمة بالفعل";
            }
        }
        return $this;
    }
    
    /**
     * التحقق من قوة كلمة المرور
     */
    public function strongPassword($field, $message = null) {
        $value = $this->getValue($field);
        
        if (!empty($value)) {
            $errors = [];
            
            if (strlen($value) < 8) {
                $errors[] = "8 أحرف على الأقل";
            }
            if (!preg_match('/[A-Z]/', $value)) {
                $errors[] = "حرف كبير واحد على الأقل";
            }
            if (!preg_match('/[a-z]/', $value)) {
                $errors[] = "حرف صغير واحد على الأقل";
            }
            if (!preg_match('/[0-9]/', $value)) {
                $errors[] = "رقم واحد على الأقل";
            }
            if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $value)) {
                $errors[] = "رمز خاص واحد على الأقل";
            }
            
            if (!empty($errors)) {
                $this->errors[$field] = $message ?? "كلمة المرور يجب أن تحتوي على: " . implode(', ', $errors);
            }
        }
        return $this;
    }
    
    /**
     * التحقق من اسم المستخدم
     */
    public function username($field, $message = null) {
        $value = $this->getValue($field);
        
        if (!empty($value)) {
            // يجب أن يبدأ بحرف ويحتوي فقط على حروف وأرقام وشرطات سفلية
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{2,29}$/', $value)) {
                $this->errors[$field] = $message ?? "اسم المستخدم يجب أن يبدأ بحرف ويحتوي فقط على حروف إنجليزية وأرقام وشرطات سفلية (3-30 حرف)";
            }
        }
        return $this;
    }
    
    /**
     * التحقق من أن القيمة رقم
     */
    public function numeric($field, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = $message ?? "يجب أن يكون {$field} رقماً";
        }
        return $this;
    }
    
    /**
     * التحقق من القيمة ضمن قائمة
     */
    public function in($field, array $allowedValues, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && !in_array($value, $allowedValues)) {
            $this->errors[$field] = $message ?? "القيمة المحددة غير مسموحة";
        }
        return $this;
    }
    
    /**
     * التحقق من الصورة
     */
    public function image($field, $maxSize = 5242880, $message = null) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES[$field];
            
            // التحقق من وجود أخطاء في الرفع
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->errors[$field] = $message ?? "حدث خطأ أثناء رفع الملف";
                return $this;
            }
            
            // التحقق من نوع الملف
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $this->errors[$field] = $message ?? "نوع الملف غير مسموح. الأنواع المسموحة: JPG, PNG, GIF, WEBP";
                return $this;
            }
            
            // التحقق من حجم الملف
            if ($file['size'] > $maxSize) {
                $maxSizeMB = $maxSize / 1024 / 1024;
                $this->errors[$field] = $message ?? "حجم الملف يجب ألا يتجاوز {$maxSizeMB} ميجابايت";
            }
        }
        return $this;
    }
    
    /**
     * التحقق من URL
     */
    public function url($field, $message = null) {
        $value = $this->getValue($field);
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = $message ?? "الرابط غير صالح";
        }
        return $this;
    }
    
    /**
     * التحقق من التاريخ
     */
    public function date($field, $format = 'Y-m-d', $message = null) {
        $value = $this->getValue($field);
        if (!empty($value)) {
            $date = DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->errors[$field] = $message ?? "صيغة التاريخ غير صحيحة";
            }
        }
        return $this;
    }
    
    /**
     * تنظيف البيانات
     */
    public static function sanitize($value) {
        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], $value);
        }
        $value = trim($value);
        $value = stripslashes($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        return $value;
    }
    
    /**
     * الحصول على قيمة حقل
     */
    private function getValue($field) {
        return isset($this->data[$field]) ? $this->data[$field] : null;
    }
    
    /**
     * التحقق من بيانات التسجيل
     */
    public function validateRegistration() {
        return $this
            ->required('username', 'اسم المستخدم مطلوب')
            ->username('username')
            ->minLength('username', 3, 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل')
            ->maxLength('username', 30, 'اسم المستخدم يجب ألا يتجاوز 30 حرف')
            ->unique('username', 'users', 'username', null, 'اسم المستخدم مستخدم بالفعل')
            ->required('email', 'البريد الإلكتروني مطلوب')
            ->email('email')
            ->unique('email', 'users', 'email', null, 'البريد الإلكتروني مستخدم بالفعل')
            ->required('password', 'كلمة المرور مطلوبة')
            ->strongPassword('password')
            ->required('password_confirm', 'تأكيد كلمة المرور مطلوب')
            ->matches('password_confirm', 'password', 'كلمات المرور غير متطابقة')
            ->required('full_name', 'الاسم الكامل مطلوب')
            ->minLength('full_name', 3, 'الاسم يجب أن يكون 3 أحرف على الأقل')
            ->maxLength('full_name', 100, 'الاسم يجب ألا يتجاوز 100 حرف');
    }
    
    /**
     * التحقق من بيانات تسجيل الدخول
     */
    public function validateLogin() {
        return $this
            ->required('username', 'اسم المستخدم أو البريد الإلكتروني مطلوب')
            ->required('password', 'كلمة المرور مطلوبة');
    }
}
