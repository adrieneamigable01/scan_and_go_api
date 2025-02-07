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

    function isCommaSeparated($string) {
        // Check if the string is not empty and contains commas
        if (!empty($string) && strpos($string, ',') !== false) {
            return true;
        }
        return false;
    }
   
    public function get($payload,$year_level_ids,$program_ids){
        $this->db->select('*');
        $this->db->from('section');
        $this->db->where($payload);  // Applying any other conditions from $payload
    
        
        if (!empty($year_level_ids)) {
            // Check if year_level_ids is a comma-separated string
            if ($this->isCommaSeparated($year_level_ids)) {
                // Convert the string to an array of values
                $array = is_array($year_level_ids) ? $year_level_ids : explode(',', $year_level_ids);
                $this->db->group_start();  // Open parentheses for the OR conditions
                // Loop through the array and apply FIND_IN_SET for each value
                foreach ($array as $key => $value) {
                    $this->db->or_where('FIND_IN_SET(' . $this->db->escape($value) . ', year_level_id) >', 0);
                }
                $this->db->group_end();  // Close parentheses for the OR conditions
            } else {
                // If it's a single value (not a comma-separated string)
                $this->db->where('FIND_IN_SET(' . $this->db->escape($year_level_ids) . ', year_level_id) >', 0);
            }
        }
        if (!empty($program_ids)) {
            // Check if year_level_ids is a comma-separated string
            if ($this->isCommaSeparated($program_ids)) {
                // Convert the string to an array of values
                $array = is_array($program_ids) ? $program_ids : explode(',', $program_ids);
                $this->db->group_start();  // Open parentheses for the OR conditions
                // Loop through the array and apply FIND_IN_SET for each value
                foreach ($array as $key => $value) {
                    $this->db->or_where('program_id', $value);
                }
                $this->db->group_end();  // Close parentheses for the OR conditions
            } else {
                // If it's a single value (not a comma-separated string)
                $this->db->where('program_id', $program_ids);
            }
        }
        // $this->db->get();
        // print_r($this->db->last_query());exit;
        // Execute the query
        $query = $this->db->get();
        return $query->result();

    }
    public function get_with_other($payload,$year_level_ids,$program_ids){
        $this->db->select('section.*, program.program, year_level.year_level');  // Select fields from section, program, and year_level
        $this->db->from('section');

        // Join with the program table (assuming the 'program_id' in 'section' corresponds to 'program.program_id')
        $this->db->join('program', 'program.program_id = section.program_id', 'left');

        // Join with the year_level table (assuming the 'year_level_id' in 'section' corresponds to 'year_level.year_level_id')
        $this->db->join('year_level', 'year_level.year_level_id = section.year_level_id', 'left');

        // Applying any conditions from $payload
        $this->db->where($payload);

        if (!empty($year_level_ids)) {
            // Check if year_level_ids is a comma-separated string
            if ($this->isCommaSeparated($year_level_ids)) {
                // Convert the string to an array of values
                $array = is_array($year_level_ids) ? $year_level_ids : explode(',', $year_level_ids);
                $this->db->group_start();  // Open parentheses for the OR conditions
                // Loop through the array and apply FIND_IN_SET for each value
                foreach ($array as $key => $value) {
                    $this->db->or_where('FIND_IN_SET(' . $this->db->escape($value) . ', section.year_level_id) >', 0);
                }
                $this->db->group_end();  // Close parentheses for the OR conditions
            } else {
                // If it's a single value (not a comma-separated string)
                $this->db->where('FIND_IN_SET(' . $this->db->escape($year_level_ids) . ', section.year_level_id) >', 0);
            }
        }

        if (!empty($program_ids)) {
            // Check if program_ids is a comma-separated string
            if ($this->isCommaSeparated($program_ids)) {
                // Convert the string to an array of values
                $array = is_array($program_ids) ? $program_ids : explode(',', $program_ids);
                $this->db->group_start();  // Open parentheses for the OR conditions
                // Loop through the array and apply FIND_IN_SET for each value
                foreach ($array as $key => $value) {
                    $this->db->or_where('section.program_id', $value);
                }
                $this->db->group_end();  // Close parentheses for the OR conditions
            } else {
                // If it's a single value (not a comma-separated string)
                $this->db->where('section.program_id', $program_ids);
            }
        }

        // Execute the query
        $query = $this->db->get();
        return $query->result();


    }
   
 }
?>