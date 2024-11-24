<?php
/**
 * menu Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
 class SectionModel extends CI_Model{

    public function add($payload){
        return $this->db->set($payload)->get_compiled_insert('section');
    }

    public function update($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('section');
    }

   
   
    public function get($payload){
        $this->db->select('*');
        $this->db->from('section');
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result();
    }
   
 }
?>