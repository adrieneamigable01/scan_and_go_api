<?php
/**
 * menu Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
 class StudentModel extends CI_Model{

    public function add($payload){
        return $this->db->set($payload)->get_compiled_insert('students');
    }

    public function update($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('students');
    }
   
    public function get($payload){
        $this->db->select('
            students.id,
            students.student_id,
            students.first_name,
            students.last_name,
            students.middle_name,
            CONCAT(students.first_name, " ", students.middle_name, " ", students.last_name) AS full_name, 
            students.email,
            students.mobile,
            students.student_image,
            students.created_at,
            students.updated_at,
            students.deleted_at,
            students.is_active,
            college.college_id,
            college.college,
            college.short_name,
            program.program_id,
            program.program,
            program.program_short_name,
            section.section_id,
            section.section,
            program.program,
            year_level.year_level_id,
            year_level.year_level,
        ');
        $this->db->from('students');
        $this->db->join('college', 'college.college_id = students.college_id', 'left'); 
        $this->db->join('program', 'program.program_id = students.program_id', 'left'); 
        $this->db->join('year_level', 'year_level.year_level_id = students.year_level_id', 'left'); 
        $this->db->join('section', 'section.section_id = students.section_id', 'left'); 
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result();
    }
    function generateStudentID($prefix = 'STU') {
        // Get the current timestamp (uniqueness based on time)
        $timestamp = time();
        
        // Generate a random 4-digit number
        $randomNumber = rand(1000, 9999);
        
        // Combine prefix, timestamp, and random number to create a unique ID
        $studentID = $prefix . '-' . $timestamp . '-' . $randomNumber;
        
        return $studentID;
    }
    public function isStudentIDExists($student_id) {
        $this->db->select('student_id');
        $this->db->from('students');  // Assuming the student data is in a table named 'students'
        $this->db->where('student_id', $student_id);
        $query = $this->db->get();
    
        // If any rows are returned, the student_id exists
        return ($query->num_rows() > 0);
    }
   
 }
?>