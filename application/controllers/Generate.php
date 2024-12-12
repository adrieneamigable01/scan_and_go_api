<?php
    ini_set('memory_limit', '-1');
    date_default_timezone_set('Asia/Manila');
    define("DOMPDF_ENABLE_PHP", true);

    use Dompdf\Dompdf;
    use Dompdf\Options;
   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

    class Generate extends CI_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->helper('jwt');
            $this->load->library('Pdf', NULL, 'pdf');
            $this->load->library('Response',NULL,'response');
            $this->load->model('EventModel');
            $this->load->model('UserModel');
            $this->load->model('StudentModel');
            $this->load->model('TeacherModel');
            $this->load->model('CollegeModel');
            $this->load->model('ProgramModel');
            $this->load->model('YearLevelModel');
            $this->load->model('SectionModel');
            $this->load->library('Response',NULL,'response');
            $this->load->library('Pdf',NULL,'pdf');
        }
        // Check if the student or teacher is late based on their time_in and event start time
        private function check_lateness($time_in, $event_id) {
            // Assuming event has a start time field (event_start_time)
            $event = $this->EventModel->get_event_date($event_id);  // Get event details (e.g., start time)
            $start_time = $event->start_time;  // Get the event start time
            
            // Compare the time_in with the event_start_time
            if ($time_in > $start_time) {
                return 'Late';
            } else {
                return 'On Time';
            }
        }
        public function export_attendance_report(){
            // print_r($_SESSION);exit;
	  		ini_set('max_execution_time', 300); //300 seconds = 5 minutes
            $paper = 'letter';
            $orientation = "landscape";
            $dompdf = new Dompdf();
            $dompdf->setPaper($paper, $orientation);
            $dompdf->set_option("isPhpEnabled", true);
            $data = array();


            $event_id = $this->input->get("event_id");
            $section_id = $this->input->get("section_id");
            $program_id = $this->input->get("program_id");
            $college_id = $this->input->get("college_id");
            $year_level_id = $this->input->get("year_level_id");

            if(empty($event_id)){
                $return = array(
                    '_isError'      => true,
                    'message'        =>'Event id is required',
                    'attendance' =>  array(
                        'students' => [],
                        'teachers' => [],
                    )
                );
                $this->response->output($return);return;
            }else if(empty($section_id)){
                $return = array(
                    '_isError'      => true,
                    'message'        =>'Section id is required',
                    'attendance' =>  array(
                        'students' => [],
                        'teachers' => [],
                    )
                );
                $this->response->output($return);return;
            }else if(empty($program_id)){
                $return = array(
                    '_isError'      => true,
                    'message'        =>'Program id is required',
                    'attendance' =>  array(
                        'students' => [],
                        'teachers' => [],
                    )
                );
                $this->response->output($return);return;
            }else{
                // Step 1: Retrieve all students
                $students = $this->StudentModel->get(array(
                    'students.section_id' => $section_id,
                    'students.is_active' => 1,
                ));
                $student_ids = array_column($students, 'student_id');  // Extract student IDs
        
                // Step 2: Retrieve all teachers
                $teachers = $this->TeacherModel->get(array(
                    'teachers.program_id' => $program_id,
                    'teachers.is_active' => 1,
                ));
                $teacher_ids = array_column($teachers, 'teacher_id');  // Extract teacher IDs
        
                // Step 3: Retrieve the event date
                $event = $this->EventModel->get_event_date($event_id);
                $event_date = $event->date;  // Assuming the event date is in the format 'Y-m-d'
        
                // Step 4: Retrieve attendance records for students and teachers for the given event
                $attendance = $this->EventModel->get_multiple_attendance($student_ids, $teacher_ids, $event_id);
        
                // Step 5: Prepare the result to return
                $students_attendance_details = [];
                $teachers_attendance_details = [];
                $current_date = date('Y-m-d');  // Get the current date
        
                // Check attendance for each student
                foreach ($students as $student) {
                    $attendance_record = $this->EventModel->get_attendance($student->student_id, null, $event_id);
                    
                    if ($event_date > $current_date) {
                        // Event is in the future, set attendance status as null
                        $students_attendance_details[] = [
                            'id' => $student->student_id,
                            'name' => $student->first_name . ' ' . $student->last_name,
                            'college' => $student->college,
                            'college_short_name' => $student->short_name,
                            'program' => $student->program,
                            'program_short_name' => $student->program_short_name,
                            'year_level' => $student->year_level,
                            'section' => $student->section,
                            'status' => null,  // No attendance status for future events
                            'time_in' => null,
                            'time_out' => null
                        ];
                    } else {
                        // Event is today or in the past
                        if ($attendance_record) {
                            // Attendance exists, check for lateness
                            $status = $this->check_lateness($attendance_record->time_in, $event_id);
                            $students_attendance_details[] = [
                                'id' => $student->student_id,
                                'name' => $student->first_name . ' ' . $student->last_name,
                                'college' => $student->college,
                                'college_short_name' => $student->short_name,
                                'program' => $student->program,
                                'program_short_name' => $student->program_short_name,
                                'year_level' => $student->year_level,
                                'section' => $student->section,
                                'status' => $status,  // "Late" or "On Time"
                                'time_in' => $attendance_record->time_in,
                                'time_out' => $attendance_record->time_out
                            ];
                        } else {
                            // No attendance, mark as Absent
                            $students_attendance_details[] = [
                                'id' => $student->student_id,
                                'name' => $student->first_name . ' ' . $student->last_name,
                                'college' => $student->college,
                                'college_short_name' => $student->short_name,
                                'program' => $student->program,
                                'program_short_name' => $student->program_short_name,
                                'year_level' => $student->year_level,
                                'section' => $student->section,
                                'status' => 'Absent',  // Student has not attended
                                'time_in' => null,
                                'time_out' => null
                            ];
                        }
                    }
                }
        
                // Check attendance for each teacher
                foreach ($teachers as $teacher) {
                    $attendance_record = $this->EventModel->get_attendance(null, $teacher->teacher_id, $event_id);
        
                    if ($event_date > $current_date) {
                        // Event is in the future, set attendance status as null
                        $teachers_attendance_details[] = [
                            'id' => $teacher->teacher_id,
                            'name' => $teacher->first_name . ' ' . $teacher->last_name,
                            'status' => null,  // No attendance status for future events
                            'time_in' => null,
                            'time_out' => null
                        ];
                    } else {
                        // Event is today or in the past
                        if ($attendance_record) {
                            // Attendance exists, check for lateness
                            $status = $this->check_lateness($attendance_record->time_in, $event_id);
                            $teachers_attendance_details[] = [
                                'id' => $teacher->teacher_id,
                                'name' => $teacher->first_name . ' ' . $teacher->last_name,
                                'college' => $student->college,
                                'college_short_name' => $student->short_name,
                                'program' => $student->program,
                                'program_short_name' => $student->program_short_name,
                                'status' => $status,  // "Late" or "On Time"
                                'time_in' => $attendance_record->time_in,
                                'time_out' => $attendance_record->time_out
                            ];
                        } else {
                            // No attendance, mark as Absent
                            $teachers_attendance_details[] = [
                                'id' => $teacher->teacher_id,
                                'name' => $teacher->first_name . ' ' . $teacher->last_name,
                                'college' => $student->college,
                                'college_short_name' => $student->short_name,
                                'program' => $student->program,
                                'program_short_name' => $student->program_short_name,
                                'status' => 'Absent',  // Teacher has not attended
                                'time_in' => null,
                                'time_out' => null
                            ];
                        }
                    }
                }

                $college = $this->CollegeModel->get(array(
                    'college_id' => $college_id,
                ));

                $program = $this->ProgramModel->get(array(
                    'program_id' => $program_id,
                ),null);

                $year_level = $this->YearLevelModel->get(array(
                    'year_level_id' => $year_level_id,
                ));

                $section = $this->SectionModel->get(array(
                    'section_id' => $section_id,
                ),null,null);

                $college    = sizeof($college) > 0 ? $college[0] : [];
                $program    = sizeof($program) > 0 ? $program[0] : [];
                $year_level = sizeof($year_level) > 0 ? $year_level[0] : [];
                $section    = sizeof($section) > 0 ? $section[0] : [];
        
                $return['data'] = array(
                    '_isError'      => false,
                    'message'        =>'Success',
                    'college'        => $college,
                    'program'        => $program,
                    'year_level'        => $year_level,
                    'section'        => $section,
                    'event'           => $this->EventModel->get_single(array(
                        'event_id' => $event_id,
                    )),
                    'attendance' =>  array(
                        'students' => $students_attendance_details,
                        'teachers' => $teachers_attendance_details,
                    )
                );
                
                // $this->load->view('dashboard/loan/statement',$data);
                $this->pdf->load_view('pdf/attendace_report',$return);
                $this->pdf->render();
                $this->pdf->stream("Attendance", array('Attachment'=>0));
                // $this->load->view('dashboard/loan/statement',$data);
                
            }
  
              
        }
    }
?>