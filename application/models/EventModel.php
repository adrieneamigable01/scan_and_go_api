<?php
/**
 * menu Model
 * Author: Adriene Carre Amigable
 * Date Created : 5/10/2020
 * Version: 0.0.1
 */
 class EventModel extends CI_Model{

    public function add($payload){
        return $this->db->set($payload)->get_compiled_insert('events');
    }
    public function add_record($payload){
        return $this->db->set($payload)->get_compiled_insert('event_records');
    }

    public function add_participants($payload){
        return $this->db->set($payload)->get_compiled_insert('event_participants');
    }


    public function update($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('events');
    }
    public function updateParticipants($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('event_participants');
    }
   
    // public function get($payload){
    //     $this->db->select('
    //         events.id,
    //         events.event_id,
    //         events.name,
    //         events.description,
    //         events.date,
    //         events.start_time,
    //         events.end_time,
    //         COUNT(event_participants.section_id) AS total_participants,
    //         COUNT(DISTINCT program.college_id) AS total_colleges,  
    //         COUNT(DISTINCT event_participants.program_id) AS total_programs,
    //         COUNT(DISTINCT event_participants.year_level_id) AS total_year_levels,
    //         GROUP_CONCAT(DISTINCT program.college_id ORDER BY program.college_id) AS colleges,
    //         GROUP_CONCAT(DISTINCT event_participants.program_id ORDER BY event_participants.program_id) AS programs,
    //         GROUP_CONCAT(DISTINCT event_participants.year_level_id ORDER BY event_participants.year_level_id) AS year_levels,
    //         GROUP_CONCAT(DISTINCT event_participants.section_id ORDER BY event_participants.section_id) AS sections,
    //         GROUP_CONCAT(DISTINCT program.college_id ORDER BY program.college_id) AS college_ids,
    //         GROUP_CONCAT(DISTINCT event_participants.program_id ORDER BY event_participants.program_id) AS programs_ids,
    //         GROUP_CONCAT(DISTINCT event_participants.year_level_id ORDER BY event_participants.year_level_id) AS year_level_ids,
    //         GROUP_CONCAT(DISTINCT event_participants.section_id ORDER BY event_participants.section_id) AS section_ids,
    //         GROUP_CONCAT(DISTINCT college.short_name ORDER BY college.short_name) AS short_names,
    //         GROUP_CONCAT(DISTINCT program.program_short_name ORDER BY program.program) AS programs_names,
    //         GROUP_CONCAT(DISTINCT year_level.year_level ORDER BY year_level.year_level) AS year_level_names,
    //         GROUP_CONCAT(DISTINCT section.section ORDER BY section.section) AS section_names
    //     ');
    //     $this->db->from('events');
    //     $this->db->join('event_participants', 'event_participants.event_id = events.event_id', 'left');
    //     $this->db->join('program', 'program.program_id = event_participants.program_id', 'left');
    //     $this->db->join('college', 'college.college_id = program.college_id', 'left');
    //     $this->db->join('year_level', 'year_level.year_level_id = event_participants.year_level_id', 'left');
    //     $this->db->join('section', 'section.section_id = event_participants.section_id', 'left');
    //     $this->db->group_by('events.event_id');
    //     $query = $this->db->get();
    //     $result = $query->result();

    //     // Now let's process the result to convert ids into full data
    //     foreach ($result as $event) {
    //         // Convert selected colleges to an array of objects (fetch full data)
    //         if (!empty($event->colleges)) {
    //             $college_ids = explode(',', $event->colleges);
    //             $event->colleges = [];

    //             // Fetch data for each college_id
    //             $this->db->select('*');
    //             $this->db->from('college');
    //             $this->db->where_in('college_id', $college_ids);
    //             $college_query = $this->db->get();
    //             $event->colleges = $college_query->result();  // This will return an array of colleges
    //         }

    //         // Convert selected programs to an array of objects (fetch full data)
    //         if (!empty($event->programs)) {
    //             $program_ids = explode(',', $event->programs);
    //             $event->programs = [];

    //             // Fetch data for each program_id
    //             $this->db->select('*');
    //             $this->db->from('program');
    //             $this->db->where_in('program_id', $program_ids);
    //             $program_query = $this->db->get();
    //             $event->programs = $program_query->result();  // This will return an array of programs
    //         }

    //         // Convert selected year_levels to an array of objects (fetch full data)
    //         if (!empty($event->year_levels)) {
    //             $year_level_ids = explode(',', $event->year_levels);
    //             $event->year_levels = [];

    //             // Fetch data for each year_level_id
    //             $this->db->select('*');
    //             $this->db->from('year_level');
    //             $this->db->where_in('year_level_id', $year_level_ids);
    //             $year_level_query = $this->db->get();
    //             $event->year_levels = $year_level_query->result();  // This will return an array of year levels
    //         }

    //         // Convert selected sections to an array of objects (fetch full data)
    //         if (!empty($event->sections)) {
    //             $section_ids = explode(',', $event->sections);
    //             $event->sections = [];

    //             // Fetch data for each section_id
    //             $this->db->select('*');
    //             $this->db->from('section');
    //             $this->db->where_in('section_id', $section_ids);
    //             $section_query = $this->db->get();
    //             $event->sections = $section_query->result();  // This will return an array of sections
    //         }
    //     }

    //     // Return the result with detailed data
    //     return $result;
    // }
    public function get_event_record($event_id){
        $current_datetime = date('Y-m-d H:i:s'); // Format: 'YYYY-MM-DD HH:MM:SS'
        $this->db->select('*');
        $this->db->from('event_records');
        $this->db->where('event_id',$event_id);
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result()[0];
        }else{
            return [];
        }
      
    }
    public function get_single($payload){
        $current_datetime = date('Y-m-d H:i:s'); // Format: 'YYYY-MM-DD HH:MM:SS'
        $this->db->select('
            events.id,
            events.event_id,
            events.name,
            events.description,
            events.date,
            events.start_time,
            events.end_time,
            events.is_ended,
            events.event_image,
            COUNT(events.id) AS total_participants,
            GROUP_CONCAT(DISTINCT college.short_name ORDER BY college.short_name) AS college_names,
            GROUP_CONCAT(DISTINCT program.program_short_name ORDER BY program.program) AS program_names,
            GROUP_CONCAT(DISTINCT year_level.year_level ORDER BY year_level.year_level) AS year_level_names,
            GROUP_CONCAT(DISTINCT section.section ORDER BY section.section) AS section_names
        ');
        
        $this->db->from('events');
        if(!isset($payload['event_id'])){
            $this->db->where($payload);
        }
        // Fix for college_ids stored as JSON: remove the square brackets and handle as a CSV
        $this->db->join('college', 'FIND_IN_SET(college.college_id, REPLACE(REPLACE(events.college_ids, "[", ""), "]", "")) > 0', 'left');
        $this->db->join('program', 'FIND_IN_SET(program.program_id, REPLACE(REPLACE(events.program_ids, "[", ""), "]", "")) > 0', 'left');
        $this->db->join('year_level', 'FIND_IN_SET(year_level.year_level_id, REPLACE(REPLACE(events.year_level_ids, "[", ""), "]", "")) > 0', 'left');
        $this->db->join('section', 'FIND_IN_SET(section.section_id, REPLACE(REPLACE(events.section_ids, "[", ""), "]", "")) > 0', 'left');
       
       
        
        $this->db->group_by('events.event_id');
        $query = $this->db->get();
        if($query->num_rows() > 0){
            return $query->result()[0];
        }else{
            return [];
        }
      
    }
    public function get($payload,$type,$extra){
        $current_datetime = date('Y-m-d H:i:s'); // Format: 'YYYY-MM-DD HH:MM:SS'

        $this->db->select('
            events.id,
            events.event_id,
            events.name,
            events.description,
            events.date,
            events.start_time,
            events.end_time,
            events.event_image,
            events.is_ended,
            COUNT(events.id) AS total_participants,
            GROUP_CONCAT(DISTINCT college.short_name ORDER BY college.short_name) AS college_names,
            GROUP_CONCAT(DISTINCT program.program_short_name ORDER BY program.program) AS program_names,
            GROUP_CONCAT(DISTINCT year_level.year_level ORDER BY year_level.year_level) AS year_level_names,
            GROUP_CONCAT(DISTINCT section.section ORDER BY section.section) AS section_names
        ');

        $this->db->from('events');

        // Fix for college_ids stored as JSON: remove the square brackets and handle as a CSV
        $this->db->join('college', 'FIND_IN_SET(college.college_id, REPLACE(REPLACE(events.college_ids, "[", ""), "]", "")) > 0', 'left');
        $this->db->join('program', 'FIND_IN_SET(program.program_id, REPLACE(REPLACE(events.program_ids, "[", ""), "]", "")) > 0', 'left');
        $this->db->join('year_level', 'FIND_IN_SET(year_level.year_level_id, REPLACE(REPLACE(events.year_level_ids, "[", ""), "]", "")) > 0', 'left');
        $this->db->join('section', 'FIND_IN_SET(section.section_id, REPLACE(REPLACE(events.section_ids, "[", ""), "]", "")) > 0', 'left');
        $this->db->where($payload);
        
        if (!empty($extra['section_id'])) {
            // Add a condition to ensure the student's section matches one of the event's sections
            $this->db->join('event_participants', 'event_participants.event_id = events.event_id', 'left');
            $this->db->where('FIND_IN_SET(' . (int)$extra['section_id'] . ', event_participants.section_id) > 0');
        }

        if (!empty($extra['program_id'])) {
            // Add a condition to ensure the student's section matches one of the event's sections
            $this->db->join('event_participants', 'event_participants.event_id = events.event_id', 'left');
            $this->db->where('FIND_IN_SET(' . (int)$extra['program_id'] . ', event_participants.program_id) > 0');
        }
        // student_id

        if(!empty($type)){
            if($type == "upcomming"){
                $this->db->where('CONCAT(events.date, " ", events.end_time) >', $current_datetime);
            }else if($type == "ended"){
                $this->db->where('CONCAT(events.date, " ", events.end_time) <', $current_datetime);
            }
        }
      
        
        // Group by the event
        $this->db->group_by('events.event_id');
        $query = $this->db->get();
        
        $result = $query->result();

        if (isset($payload['events.event_id'])) {
            foreach ($result as $event) {
                // Fetch participants based on the event_id
                $this->db->select('*');
                $this->db->from('event_participants');
                $this->db->where('event_participants.event_id', $event->event_id);
                $this->db->where('event_participants.is_active', 1);
                $participant_query = $this->db->get();
                $event->participants = $participant_query->result(); // This will return an array of participants for the event
        
                // Now you can fetch additional details for each participant (e.g., college, program, year level, section)
                $event->colleges = $this->get_participant_details($event->participants, 'college');
                $event->programs = $this->get_participant_details($event->participants, 'program');
                $event->year_levels = $this->get_participant_details($event->participants, 'year_level');
                $event->sections = $this->get_participant_details($event->participants, 'section');
            
            }
        }
        return $result;

    }
    private function get_participant_details($participants, $table_name) {
        $details = [];
        foreach ($participants as $participant) {
            $college_id = $participant->college_id;
            // Fetch details from each table (college, program, year_level, section)
            if ($table_name === 'program') {
                $this->db->select('program.*, college.college, college.short_name'); // Fetch program data + college data
                $this->db->from('program');
                $this->db->join('college', 'program.college_id = college.college_id', 'left'); // LEFT JOIN to get the college details
                
                // Check if program_id is a comma-separated list or a single value
                if (strpos($participant->program_id, ',') !== false) {
                    // If it's a comma-separated string, split it into an array
                    $program_ids = explode(',', $participant->program_id);
                    $this->db->where_in('program.program_id', $program_ids); // Use WHERE IN for multiple program_ids
                } else {
                    $this->db->where('program.program_id', $participant->program_id); // Single program_id
                }
                $this->db->where('program.is_active', '1');  // Add the check for active status
                $query = $this->db->get();
                foreach ($query->result() as $row) {
                    $details[] = $row;  // Flatten the array into a single array of objects
                }
            } else {
                // For other tables, check if the value is a comma-separated string
                $this->db->select('*');
                $this->db->from($table_name);
                
                // Check if the id is a comma-separated list or a single value
                if (strpos($participant->{$table_name . '_id'}, ',') !== false) {
                    // If it's a comma-separated string, split it into an array
                    $ids = explode(',', $participant->{$table_name . '_id'});
                    $this->db->where_in($table_name . '_id', $ids); // Use WHERE IN for multiple ids
                } else {
                    $this->db->where($table_name . '_id', $participant->{$table_name . '_id'}); // Single id
                }
                $this->db->where($table_name . '.is_active', '1'); // Add the check for active status
                
                $query = $this->db->get();
                foreach ($query->result() as $row) {
                    // Add college_id explicitly to each result if available
                    $row->college_id = isset($college_id) ? $college_id : null; // Check if college_id is available
                    $details[] = $row;  // Flatten the array into a single array of objects
                }
            }
          
        }
        return $details;
    }

    public function getParticipantsDetails($payload){
        $this->db->select('*');
        $this->db->from('event_participants');
        $this->db->where($payload); // Single id
        $query = $this->db->get();
        return $query->result();
    }
    function generateEventID($prefix = 'EVNT') {
        // Get the current timestamp (uniqueness based on time)
        $timestamp = time();
        
        // Generate a random 4-digit number
        $randomNumber = rand(1000, 9999);
        
        // Combine prefix, timestamp, and random number to create a unique ID
        $studentID = $prefix . '-' . $timestamp . '-' . $randomNumber;
        
        return $studentID;
    }
    public function isEventIDExists($event_id) {
        $this->db->select('event_id');
        $this->db->from('events');  // Assuming the student data is in a table named 'events'
        $this->db->where('event_id', $event_id);
        $query = $this->db->get();
    
        // If any rows are returned, the event_id exists
        return ($query->num_rows() > 0);
    }


    public function get_event_participants($event_id) {
        $this->db->select('*');
        $this->db->where('event_id', $event_id);
        $this->db->where('event_participants.is_active', 1);
        $query = $this->db->get('event_participants');  // Assuming 'events' table contains the event information
        return $query->result();
    }
    public function get_event_date($event_id) {
        $this->db->select('*');
        $this->db->where('event_id', $event_id);
        $query = $this->db->get('events');  // Assuming 'events' table contains the event information
        return $query->row();  // Return event date
    }

    // Get attendance record for a student or teacher for a specific event
    public function get_attendance($student_id = null, $teacher_id = null, $event_id) {
        $this->db->where('event_id', $event_id); // Filter by event_id
        
        // Check if it's a student or teacher
        if ($student_id) {
            $this->db->where('student_id', $student_id);
        } elseif ($teacher_id) {
            $this->db->where('teacher_id', $teacher_id);
        }

        $query = $this->db->get('attendance');
        return $query->row();  // Return a single row (if any)
    }

    // Get attendance details for multiple students and teachers for a specific event
    public function get_multiple_attendance($student_ids = [], $teacher_ids = [], $event_id) {
        $this->db->where('event_id', $event_id); // Filter by event_id
        
        // Check for student IDs
        if (!empty($student_ids)) {
            $this->db->where_in('student_id', $student_ids);
        }
        
        // Check for teacher IDs
        if (!empty($teacher_ids)) {
            $this->db->where_in('teacher_id', $teacher_ids);
        }

        $query = $this->db->get('attendance');
        return $query->result();  // Returns multiple rows
    }
    
    public function time_in($payload){
        return $this->db->set($payload)->get_compiled_insert('attendance');
    }
    public function time_out($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('attendance');
    }
 }
?>