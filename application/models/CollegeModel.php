<?php
/**
 * menu Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
 class CollegeModel extends CI_Model{

    public function add($payload){
        return $this->db->set($payload)->get_compiled_insert('college');
    }

    public function update($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('college');
    }
   
    public function get($payload){
        $this->db->select('*');
        $this->db->from('college');
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result();
    }
    
   
 }
?>