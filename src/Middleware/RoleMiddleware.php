<?php
/**
 * Role Middleware
 * Verifies user has required role to access resources
 */

namespace src\Middleware;

use src\Models\User;

class RoleMiddleware {
    private static $userModel = null;
    
    /**
     * Initialize user model
     */
    private static function init() {
        if (self::$userModel === null) {
            self::$userModel = new User();
        }
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($requiredRole) {
        self::init();
        
        if (!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'Not authenticated'
            ];
        }
        
        $user = self::$userModel->find($_SESSION['user_id']);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        if ($user['role'] !== $requiredRole) {
            return [
                'success' => false,
                'message' => 'Insufficient permissions',
                'code' => 403
            ];
        }
        
        return [
            'success' => true,
            'user' => $user
        ];
    }
    
    /**
     * Check if user has any of the specified roles
     */
    public static function hasAnyRole($roles) {
        self::init();
        
        if (!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'Not authenticated'
            ];
        }
        
        $user = self::$userModel->find($_SESSION['user_id']);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        if (!in_array($user['role'], $roles)) {
            return [
                'success' => false,
                'message' => 'Insufficient permissions',
                'code' => 403
            ];
        }
        
        return [
            'success' => true,
            'user' => $user
        ];
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::hasRole('admin');
    }
    
    /**
     * Check if user is teacher
     */
    public static function isTeacher() {
        return self::hasRole('teacher');
    }
    
    /**
     * Check if user is student
     */
    public static function isStudent() {
        return self::hasRole('student');
    }
}
