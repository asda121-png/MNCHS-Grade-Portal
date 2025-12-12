<?php
/**
 * User Model
 */

namespace src\Models;

class User extends BaseModel {
    protected $table = 'users';
    protected $hidden = ['password'];
    
    /**
     * Find user by email
     */
    public function findByEmail($email) {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email],
            's'
        );
    }
    
    /**
     * Find user by username
     */
    public function findByUsername($username) {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE username = ?",
            [$username],
            's'
        );
    }
    
    /**
     * Get users by role
     */
    public function getByRole($role) {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE role = ?",
            [$role],
            's'
        );
    }
    
    /**
     * Verify password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Hash password
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
