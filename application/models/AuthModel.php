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
    public function authenticate($payload){
        $this->db->select('users.*');
        $this->db->from('users');
        $this->db->where($payload);
        // $this->db->join('tbl_user', 'tbl_user.id = tbl_order.user_id','left');
        $query = $this->db->get();
        return $query->result();
    }
    public function addBlackListToken($payload){
        return $this->db->set($payload)->get_compiled_insert('blacklist_token');
    }
 }
?>