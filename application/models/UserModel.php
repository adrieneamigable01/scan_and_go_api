<?php
/**
 * menu Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
 class UserModel extends CI_Model{

    public function addUser($payload){
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
   
 }
?>