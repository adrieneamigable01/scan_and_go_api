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
   
    public function get($payload,$payloadWhereIn){
        $this->db->select('program.*, college.college, college.short_name as college_short_name'); // Adjust columns as needed
        $this->db->from('program');
        
        // Perform the LEFT JOIN on the college table
        $this->db->join('college', 'college.college_id = program.college_id', 'left');
        
        // Apply the where condition from the payload
        $this->db->where($payload);
        
        if (!empty($payloadWhereIn)) {
            foreach ($payloadWhereIn as $key => $value) {
                $array_where_in = explode(',', $value);
                $this->db->where_in($key, $array_where_in);
            }
        }
        
        $query = $this->db->get();
        return $query->result();
        
    }
   
 }
?>