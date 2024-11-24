<?php
/**
 * menu Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
 class UserModel extends CI_Model{

    public function add($payload){
        return $this->db->set($payload)->get_compiled_insert('users');
    }

    public function updateUser($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('users');
    }
   
    public function getUser($payload){

       

        $sql = "SELECT users.userId ,
                        users.firstName,
                        users.middleName,
                        users.lastName,
                        CONCAT(users.firstName,' ',users.middleName,' ',users.lastName) as name,
                        users.userName,
                        users.email,
                        users.userType,
                        users.dateCreated,
                        users.dateStausChange,
                        users.storeid,
                        users.mobile,
                        users.role,
                        users.birthdate,
                        stores.storeName,
                        stores.address as store_address,
                        stores.email as storeEmail,
                        stores.telephone as storeTelephone,
                        stores.contact as storeMobile
                        FROM `users`
                LEFT JOIN stores ON stores.storeid  = `users`.storeid
                WHERE `users`.isActive = 1";
        

        if(isset($payload['userId'])){
            $userId = !empty($payload['userId']) ? $payload['userId']: "All";
            if($userId != "All"){
                $sql .= " AND `users`.userId = {$userId}";
            }
        }

        if(isset($payload['notuserId'])){
            $notuserId =  implode(', ', $payload['notuserId']);;
            $sql .= " AND users.userId NOT IN($notuserId)";
        }


        if(isset($payload['storeid'])){
            $storeid = !empty($payload['storeid']) ? $payload['storeid']: "All";
            if($storeid != "All"){
                $sql .= " AND `users`.storeid = {$storeid}";
            }
        }


        return  $this->db->query($sql)->result();
    }

    function generateUserID($prefix = 'USER') {
        // Get the current timestamp (uniqueness based on time)
        $timestamp = time();
        
        // Generate a random 4-digit number
        $randomNumber = rand(1000, 9999);
        
        // Combine prefix, timestamp, and random number to create a unique ID
        $studentID = $prefix . '-' . $timestamp . '-' . $randomNumber;
        
        return $studentID;
    }

    public function isUserIDExists($user_id) {
        $this->db->select('user_id');
        $this->db->from('users');  // Assuming the student data is in a table named 'students'
        $this->db->where('user_id', $user_id);
        $query = $this->db->get();
    
        // If any rows are returned, the student_id exists
        return ($query->num_rows() > 0);
    }
   
 }
?>