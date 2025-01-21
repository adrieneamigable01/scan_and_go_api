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
        public function certificate(){

            $student_id = $this->input->get("student_id");
            $event_id = $this->input->get("event_id");
            ini_set('max_execution_time', 300); //300 seconds = 5 minutes

            if(empty($student_id)){
                echo errorPage("Student id is required");exit;
            }
            if(empty($event_id)){
                echo errorPage("Event id is required");exit;
            }
           
         
            $paper = 'letter';
            $orientation = "landscape";
            
            $dompdf = new Dompdf();
            $dompdf->setPaper($paper, $orientation);
            $dompdf->set_option("isPhpEnabled", true);
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $return = array();

            $student = $this->StudentModel->get(array(
                'students.student_id' => "$student_id",
                'students.is_active' => 1,
            ),);

            if (sizeof($student) > 0) {
                $return['students']  = $student[0];
            }else{
                echo errorPage("No student under this id.");exit;
            }
            $event = $this->EventModel->get(array(
                'events.event_id' => "$event_id",
                'events.is_active' => 1,
            ),array(),array());

            if (sizeof($event) > 0) {
                $return['event']  = $event[0];
            }
            $event = $this->EventModel->get(array(
                'events.event_id' => "$event_id",
                'events.is_active' => 1,
            ),array(),array());

            if (sizeof($event) > 0) {
                $return['event']  = $event[0];
            }
            $event_signature = $this->EventModel->get_event_signature($event_id);

            if ($event_signature->num_rows() > 0) {
                $return['event_signature']  = $event_signature->result()[0];
            }else{
                echo errorPage("No signature available, Please wait for the admin to add");exit;
            }

            

            // print_r($return['students']);exit;

            $this->pdf->load_view_landscape('pdf/certificate',$return);
        }
    }
    function errorPage($errorMEssage = 'Error Page'){
        $logo = base_url().'assets/img/an-color.png';
        return '<!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Error - Signature Pending</title>
                <style>
                    /* Global Styles */
                    body {
                        font-family: "Arial", sans-serif;
                        background: linear-gradient(135deg, #f6d365 10%, #fda085 100%);
                        margin: 0;
                        padding: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        animation: fadeIn 1s ease-out;
                    }

                    .container {
                        text-align: center;
                        background-color: #ffffff;
                        border-radius: 12px;
                        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
                        padding: 40px;
                        max-width: 600px;
                        width: 90%;
                        animation: slideIn 1.2s ease-out;
                    }

                    /* Logo styling */
                    .logo {
                        max-width: 150px;
                        margin-bottom: 20px;
                        transition: transform 0.3s ease;
                    }

                    .logo:hover {
                        transform: scale(1.1);
                    }

                    h1 {
                        font-size: 38px;
                        color: #333;
                        margin-bottom: 20px;
                        font-weight: bold;
                    }

                    /* Error Message Styling */
                    .error-message {
                        background-color: #f8d7da;
                        color: #721c24;
                        border: 1px solid #f5c6cb;
                        padding: 20px;
                        border-radius: 8px;
                        font-size: 18px;
                        margin: 20px 0;
                        font-weight: 500;
                        animation: fadeInMessage 1.5s ease-out;
                    }

                    /* Button Styling */
                    .button {
                        background-color: #007bff;
                        color: #fff;
                        padding: 12px 24px;
                        border: none;
                        border-radius: 8px;
                        font-size: 18px;
                        text-decoration: none;
                        display: inline-block;
                        margin-top: 20px;
                        transition: background-color 0.3s ease;
                    }

                    .button:hover {
                        background-color: #0056b3;
                    }

                    /* Animation for smooth page transitions */
                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                        }
                        to {
                            opacity: 1;
                        }
                    }

                    @keyframes slideIn {
                        from {
                            transform: translateY(100px);
                            opacity: 0;
                        }
                        to {
                            transform: translateY(0);
                            opacity: 1;
                        }
                    }

                    @keyframes fadeInMessage {
                        from {
                            opacity: 0;
                        }
                        to {
                            opacity: 1;
                        }
                    }

                    /* Subtle progress bar animation */
                    .progress-bar {
                        width: 100%;
                        height: 6px;
                        background-color: #ddd;
                        border-radius: 8px;
                        margin-top: 30px;
                        animation: loading 3s linear infinite;
                    }

                    .progress-bar .filler {
                        height: 100%;
                        background-color: #007bff;
                        width: 0;
                        animation: fillBar 3s linear infinite;
                        border-radius: 8px;
                    }

                    @keyframes loading {
                        0% {
                            width: 0;
                        }
                        50% {
                            width: 50%;
                        }
                        100% {
                            width: 100%;
                        }
                    }

                    @keyframes fillBar {
                        0% {
                            width: 0%;
                        }
                        100% {
                            width: 100%;
                        }
                    }

                </style>
            </head>
            <body>
                <div class="container">
                    <!-- Logo -->
                    <img src="'.$logo.'" alt="Logo" class="logo">

                    <h1>Error</h1>

                    <div class="error-message">
                        '.$errorMEssage.'
                    </div>

                    <!-- Optional Progress Bar -->
                    <div class="progress-bar">
                        <div class="filler"></div>
                    </div>
                </div>
            </body>
            </html>';
    }
?>