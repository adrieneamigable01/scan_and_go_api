<?php
/**
 * Auth Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/3/2020
 * Version: 0.0.1
 */
 class AuthModel extends CI_Model{
    /**
     * This will authenticate the user
     * @param array payload 
    */
    
    public function authenticate($payload) {


        $sql = "SELECT 
                users.user_id, 
                users.user_type, 
                users.username, 
                users.password,
                users.created_at, 
                users.updated_at, 
                CASE 
                    WHEN users.user_type = 'student' THEN students.first_name
                    WHEN users.user_type = 'admin' THEN admins.first_name
                    WHEN users.user_type = 'teacher' THEN teachers.first_name
                    ELSE NULL 
                END AS first_name,
                CASE 
                    WHEN users.user_type = 'student' THEN students.middle_name
                    WHEN users.user_type = 'admin' THEN admins.middle_name
                    WHEN users.user_type = 'teacher' THEN teachers.middle_name
                    ELSE NULL 
                END AS middle_name,
                CASE 
                    WHEN users.user_type = 'student' THEN students.last_name
                    WHEN users.user_type = 'admin' THEN admins.last_name
                    WHEN users.user_type = 'teacher' THEN teachers.last_name
                    ELSE NULL 
                END AS last_name
            FROM users
            LEFT JOIN students ON students.user_id = users.user_id AND users.user_type = 'student'
            LEFT JOIN admins ON admins.user_id = users.user_id AND users.user_type = 'admin'
            LEFT JOIN teachers ON teachers.user_id = users.user_id AND users.user_type = 'teacher'
            WHERE username = ?";

        // Execute the query with the payload's values (email and password)
        $query = $this->db->query($sql, array($payload['username']));
        // Return the result as an array
        return $query->result();
    }
    
    
    
    
    
    public function addBlackListToken($payload){
        return $this->db->set($payload)->get_compiled_insert('blacklist_token');
    }
 }
?>