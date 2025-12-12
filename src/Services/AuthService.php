<?php
/**
 * Authentication Service
 * Handles login, registration, and user verification
 */

namespace src\Services;

use src\Models\User;

class AuthService {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Authenticate user with email and password
     */
    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Invalid password'
            ];
        }
        
        return [
            'success' => true,
            'user' => $user,
            'message' => 'Login successful'
        ];
    }
    
    /**
     * Register new user
     */
    public function register($email, $username, $password, $firstName, $lastName, $role = 'student') {
        // Check if email exists
        if ($this->userModel->findByEmail($email)) {
            return [
                'success' => false,
                'message' => 'Email already registered'
            ];
        }
        
        // Check if username exists
        if ($this->userModel->findByUsername($username)) {
            return [
                'success' => false,
                'message' => 'Username already taken'
            ];
        }
        
        $hashedPassword = $this->userModel->hashPassword($password);
        
        $userId = $this->userModel->create([
            'email' => $email,
            'username' => $username,
            'password' => $hashedPassword,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role
        ]);
        
        if ($userId) {
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Registration successful'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Registration failed'
        ];
    }
    
    /**
     * Verify user session
     */
    public function verifySession() {
        if (!isset($_SESSION['user_id'])) {
            return [
                'success' => false,
                'message' => 'Not authenticated'
            ];
        }
        
        $user = $this->userModel->find($_SESSION['user_id']);
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User not found'
            ];
        }
        
        return [
            'success' => true,
            'user' => $user
        ];
    }
    
    /**
     * Check if user has specific role
     */
    public function hasRole($userId, $role) {
        $user = $this->userModel->find($userId);
        return $user && $user['role'] === $role;
    }
}
