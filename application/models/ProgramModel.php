<?php
/**
 * menu Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
 class ProgramModel extends CI_Model{

    public function add($payload){
        return $this->db->set($payload)->get_compiled_insert('program');
    }

    public function update($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('program');
    }
   
    public function get($payload){
        $this->db->select('*');
        $this->db->from('program');
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result();
    }
   
 }
?>