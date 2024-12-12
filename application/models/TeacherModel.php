<?php
/**
 * menu Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
 class TeacherModel extends CI_Model{

    public function add($payload){
        return $this->db->set($payload)->get_compiled_insert('teachers');
    }

    public function update($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('teachers');
    }
   
    public function get($payload){
        $this->db->select('
            teachers.id,
            teachers.teacher_id,
            teachers.first_name,
            teachers.last_name,
            teachers.middle_name,
            CONCAT(teachers.first_name, " ", teachers.middle_name, " ", teachers.last_name) AS full_name, 
            teachers.email,
            teachers.mobile,
            teachers.teacher_image,
            teachers.created_at,
            teachers.updated_at,
            teachers.deleted_at,
            teachers.user_id,
            teachers.face_descriptor,
            teachers.is_active,
            college.college_id,
            college.college,
            college.short_name,
            program.program_id,
            program.program,
            program.program_short_name,
            program.program
        ');
        $this->db->from('teachers');
        $this->db->join('college', 'college.college_id = teachers.college_id', 'left'); 
        $this->db->join('program', 'program.program_id = teachers.program_id', 'left'); 
        $this->db->where($payload);
        $query = $this->db->get();
        return $query->result();
    }
    public function get_teacher_event($payload){
        $this->db->select('
            teachers.id,
            teachers.teacher_id,
            teachers.first_name,
            teachers.last_name,
            teachers.middle_name,
            CONCAT(teachers.first_name, " ", teachers.middle_name, " ", teachers.last_name) AS full_name, 
            teachers.email,
            teachers.mobile,
            teachers.teacher_image,
            teachers.created_at,
            teachers.updated_at,
            teachers.deleted_at,
            teachers.user_id,
            teachers.face_descriptor,
            teachers.is_active,
            college.college_id,
            college.college,
            college.short_name,
            program.program_id,
            program.program,
            program.program_short_name,
            program.program
        ');
        $this->db->from('teachers');
        $this->db->join('college', 'college.college_id = teachers.college_id', 'left'); 
        $this->db->join('program', 'program.program_id = teachers.program_id', 'left'); 
        // $this->db->where($payload);
        $this->db->where('teachers.is_active',1);
        $this->db->where_in('teachers.program_id',$payload);
        $query = $this->db->get();
        return $query->result();
    }
    function generateTeacherID($prefix = 'TCHR') {
        // Get the current timestamp (uniqueness based on time)
        $timestamp = time();
        
        // Generate a random 4-digit number
        $randomNumber = rand(1000, 9999);
        
        // Combine prefix, timestamp, and random number to create a unique ID
        $teacher_id = $prefix . '-' . $timestamp . '-' . $randomNumber;
        
        return $teacher_id;
    }
    public function isTeacherIDExists($teacher_id,$user_id = "") {
        $this->db->select('teacher_id');
        $this->db->from('teachers');  // Assuming the student data is in a table named 'students'
        $this->db->where('teacher_id', $teacher_id);
        if (!empty($user_id)) {
            $this->db->where('user_id !=', $user_id);
        }
        $query = $this->db->get();
    
        // If any rows are returned, the teacher_id exists
        return ($query->num_rows() > 0);
    }
   
 }
?>