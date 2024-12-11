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
            students.user_id,
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
            students.face_descriptor,
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
    public function get_student_event($payload){
        $this->db->select('
            students.id,
            students.user_id,
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
            students.face_descriptor,
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
        // $this->db->where_in($payload);
        $this->db->where_in('students.section_id',$payload);
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
    public function isStudentIDExists($student_id,$user_id = "") {
        try {
            $this->db->select('student_id');
            $this->db->from('students');
            $this->db->where('student_id', $student_id);
    
            // Exclude the specific user_id if it's provided
            if (!empty($user_id)) {
                $this->db->where('user_id !=', $user_id);
            }
    
            $query = $this->db->get();
    
            // Check if there are any rows returned
            return ($query->num_rows() > 0);
        } catch (Exception $e) {
            // Log the error or handle it as needed
            log_message('error', 'Error in isStudentIDExists method: ' . $e->getMessage());
            return false;  // Return false in case of an error
        }
    }
   
 }
?>