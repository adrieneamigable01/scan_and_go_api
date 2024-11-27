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

    public function add_participants($payload){
        return $this->db->set($payload)->get_compiled_insert('event_participants');
    }


    public function update($payload,$where){
        $this->db->where($where);
        return $this->db->set($payload)->get_compiled_update('events');
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
    public function get($payload,$type){
        $current_datetime = date('Y-m-d H:i:s'); // Format: 'YYYY-MM-DD HH:MM:SS'
        $this->db->select('
            events.id,
            events.event_id,
            events.name,
            events.description,
            events.date,
            events.start_time,
            events.end_time,
            events.college_ids,
            events.event_image,
            events.program_ids,
            events.year_level_ids,
            events.section_ids,
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
      
        
        // Group by the event
        $this->db->group_by('events.event_id');
        $query = $this->db->get();
        $result = $query->result();
        
        if(isset($payload['event_id'])){
            foreach ($result as $event) {
                // Convert selected college_ids to an array of objects (fetch full data)
                if (!empty($event->college_ids)) {
                    $college_ids = explode(',', $event->college_ids);  // Split the string into an array
                    $event->colleges = [];

                    // Fetch data for each college_id
                    $this->db->select('*');
                    $this->db->from('college');
                    $this->db->where_in('college_id', $college_ids);
                    $college_query = $this->db->get();
                    $event->colleges = $college_query->result();  // This will return an array of colleges with names
                }

                // Convert selected program_ids to an array of objects (fetch full data)
                if (!empty($event->program_ids)) {
                    $program_ids = explode(',', $event->program_ids);  // Split the string into an array
                    $event->programs = [];

                    // Fetch data for each program_id
                    $this->db->select('*');
                    $this->db->from('program');
                    $this->db->where_in('program_id', $program_ids);
                    $program_query = $this->db->get();
                    $event->programs = $program_query->result();  // This will return an array of programs with names
                }

                // Convert selected year_level_ids to an array of objects (fetch full data)
                if (!empty($event->year_level_ids)) {
                    $year_level_ids = explode(',', $event->year_level_ids);  // Split the string into an array
                    $event->year_levels = [];

                    // Fetch data for each year_level_id
                    $this->db->select('*');
                    $this->db->from('year_level');
                    $this->db->where_in('year_level_id', $year_level_ids);
                    $year_level_query = $this->db->get();
                    $event->year_levels = $year_level_query->result();  // This will return an array of year levels with names
                }

                // Convert selected section_ids to an array of objects (fetch full data)
                if (!empty($event->section_ids)) {
                    $section_ids = explode(',', $event->section_ids);  // Split the string into an array
                    $event->sections = [];

                    // Fetch data for each section_id
                    $this->db->select('*');
                    $this->db->from('section');
                    $this->db->where_in('section_id', $section_ids);
                    $section_query = $this->db->get();
                    $event->sections = $section_query->result();  // This will return an array of sections with names
                }

            
            }   
        }
        return $result;

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
   
 }
?>