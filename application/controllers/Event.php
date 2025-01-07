<?php
   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 
        ini_set('memory_limit', '-1');
        date_default_timezone_set('Asia/Manila');
        define("DOMPDF_ENABLE_PHP", true);
        use Dompdf\Dompdf;
        use Dompdf\Options;
    class Event extends MY_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
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
        public function checkToken(){
            $return = array(
                '_isError'      => false,
                'reason'        =>'Valid Token',
            );
            $this->response->output($return); //return the json encoded data
        }
        public function mysqlTQ($arrQuery){
            $arrayIds = array();
            if (!empty($arrQuery)) {
                $this->db->trans_start();
                foreach ($arrQuery as $value) {
                    $this->db->query($value);
                    $last_id = $this->db->insert_id();
                    array_push($arrayIds,$last_id);
                }
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                } else {
                    $this->db->trans_commit();
                   
                    return $arrayIds;
                }
            }
        }
        /**
            * Get all active suppliers
            * 
            *
            * @return array Returns the _isError define if the request is error or not.
            * reason indicated the system message 
            * data where the data object has 
        */
        public function get(){
            /**
             * @var string post data $key
             * @var string session data $accessKey
            */
            try{

                
               
                /** 
                    * Call the supploer model
                    * then call the getUser method
                    * @param array $payload.
                */
                $event_id = $this->input->get("event_id");
                $student_id = $this->input->get("student_id");
                $teacher_id = $this->input->get("teacher_id");
                $program_id = $this->input->get("program_id");
                $section_id = $this->input->get("section_id");
                $type = $this->input->get("type");
                
                $payload = array(
                    'events.is_active' => 1
                );
                
                if(!empty($event_id)){
                    $payload['events.event_id'] = $event_id;
                }
                else if(empty($type)){
                    $type = "upcomming";
                }

                $extra = array(
                    'student_id' => $student_id, 
                    'teacher_id' => $teacher_id, 
                    'program_id' => $program_id, 
                    'section_id' => $section_id, 
                );

            
                
              
                $request = $this->EventModel->get($payload,$type,$extra);
                $return = array(
                    '_isError'      => false,
                    'message'        =>'Success',
                    'data'          => $request,
                );
                if(!empty($event_id)){
                    $return['files'] = $this->get_files($event_id);
                }

            }catch (Exception $e) {
                //set server error
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   => $e->getMessage(),
                );
            }
            $this->response->output($return); //return the json encoded data
        }
        public function get_files($event_id) {
            // Path to the folder containing the files
            $directory_path = FCPATH . 'uploads/events/'.$event_id;  // FCPATH is the root folder of your project
    
            // Check if the directory exists
            if (is_dir($directory_path)) {
                // Get all files from the directory
                $files = scandir($directory_path);
    
                // Remove the '.' and '..' directories from the list
                $files = array_diff($files, array('.', '..'));
    
                // Return the file list as a JSON response
              return array_values($files);
            } else {
                // Return an error if the directory doesn't exist
                return [];
            }
        }
        public function generateHashPassword($pass,$key){
            $passwithKey = $pass.'-'.$key;
            return md5($passwithKey);
        }
        public function generateUsername($fullName){
            // Remove any unnecessary prefixes and trim whitespace
            $fullName = trim($fullName);
            
            // Convert to lowercase and replace spaces and hyphens with dots
            $usernameBase = strtolower(preg_replace('/[\s-]+/', '.', $fullName));


            return $usernameBase;
        }

        // function generateeventId($dbid) {
        //     // Get the current date in the format mmddyy
        //     $date = date('mdy'); 
            
        //     // Format the event ID as STUD-mmddYY-dbid
        //     $eventId = "STUD-" . $date . "-" . str_pad($dbid, 3, '0', STR_PAD_LEFT); 
            
        //     return $eventId;
        // }


        
        

        public function add() {
            $transQuery = array();
        
            // Retrieve form data using the 'name' attributes from the HTML form
        
            $event_name = $this->input->post('event_name');
            $event_venue = $this->input->post('event_venue');
            $event_description = $this->input->post('event_description');
            $event_date = $this->input->post('event_date');
            $start_time = $this->input->post('start_time');
            $end_time = $this->input->post('end_time');

            // $college_ids = $this->input->post('college_ids');
            // $program_ids = $this->input->post('program_ids');
            // $year_level_ids = $this->input->post('year_level_ids');
            // $section_ids = $this->input->post('section_ids');
            $event_image = $this->input->post('event_image');
            $event_participants = $this->input->post('event_participants');
    
            $dateCreated = date("Y-m-d");
        
            // Validation checks
            if (empty($event_name)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event name is required',
                );
            } 
            else if (empty($event_venue)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event venue is required',
                );
            } 
            else if (empty($event_description)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event description is required',
                );
            } else if (empty($event_date)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event date is required',
                );
            } else if (empty($start_time)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event Start time is required',
                );
            }else if (empty($end_time)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event End time is required',
                );
            }else if (empty($event_participants)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Participants is required',
                );
            } 
            // else if (empty($college_ids)) {
            //     $return = array(
            //         '_isError' => true,
            //         'reason' => 'College Ids is required',
            //     );
            // }else if (empty($program_ids)) {
            //     $return = array(
            //         '_isError' => true,
            //         'reason' => 'Program Ids is required',
            //     );
            // } else if (empty($year_level_ids)) {
            //     $return = array(
            //         '_isError' => true,
            //         'reason' => 'Year Level Ids mu is required',
            //     );
            // } else if (empty($section_ids)) {
            //     $return = array(
            //         '_isError' => true,
            //         'reason' => 'Section Ids is required',
            //     );
            // }
            else {
                try {
                    // Hash the password
                    $event_id = $this->EventModel->generatEeventID();
                    // $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                    while ($this->EventModel->isEventIDExists($event_id)) {
                        // Regenerate event ID if it already exists
                        $event_id = $this->EventModel->generateEventID();
                    }

        
                    // Payload array for new user data
                    $payload = array(
                        'event_id'              => $event_id,
                        'name'                  => $event_name,
                        'venue'                  => $event_venue,
                        'description'           => $event_description,
                        'date'                  => $event_date,
                        'start_time'            => $start_time,
                        'end_time'              => $end_time,
                        'event_image'           => $event_image,
                        'created_at'            => date("Y-m-d"),
                    );
        
                    // Call model function to add user
                    $response = $this->EventModel->add($payload);
                    array_push($transQuery, $response);

                    // $program_ids = !is_array($program_ids) ?json_decode($program_ids,true) : $program_ids;
                    // $year_level_ids = !is_array($year_level_ids) ?json_decode($year_level_ids,true) : $year_level_ids;
                    // $section_ids = !is_array($section_ids) ?json_decode($section_ids,true) : $section_ids;
                    // foreach ($program_ids as $program_id_key => $program_id_value) {
                    //     foreach ($year_level_ids as $year_level_key => $year_level_id_value) {
                           
                    //         foreach ($section_ids as $section_id_key => $section_id_value) {
                    //             $participants_data = array(
                    //                 'event_id'          => $event_id,
                    //                 'program_id'        => $program_id_value,
                    //                 'year_level_id'     => $year_level_id_value,
                    //                 'section_id'        => $section_id_value,
                    //                 'created_at'        =>  date("Y-m-d"),
                    //             );
                    //             $response = $this->EventModel->add_participants($participants_data);
                    //             array_push($transQuery, $response);
                    //         }
                    //     } 
                    // }

                    if (is_string($event_participants)) {
                        // Convert comma-separated string to an array
                        $event_participants = json_decode($event_participants,true);
                    }
                    
                    foreach ($event_participants as $participants_key => $participants_value) {
                        $participants_data = array(
                            'event_id'          => $event_id,
                            'college_id'        => $participants_value['college_id'],
                            'program_id'        => implode(',', $participants_value['program_value']),
                            'year_level_id'     => implode(',', $participants_value['year_level_value']),
                            'section_id'        => implode(',', $participants_value['section_value']),
                            'created_at'        =>  date("Y-m-d"),
                        );
                        $response = $this->EventModel->add_participants($participants_data);
                        array_push($transQuery, $response);
                    }

                    if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])){
                        $upload = $this->uploadDocuments($event_id);
                        if(!$upload){
                            $return = array(
                                '_isError' => true,
                                'reason' => $upload,
                            );
                            $this->response->output($return);
                            return false;
                        }
                    }
                        
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully added new event',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error adding event',
                        );
                    }
        
                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        '_isError' => true,
                        'reason' => $e->getMessage(),
                    );
                }
            }
        
            // Output the response
            $this->response->output($return);
        }

        function uploadDocuments($event_id){
            if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
                // Get the file details
                $files = $_FILES['documents'];
                
                // Define allowed file types
                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                
                // Upload directory
                $uploadDir = 'uploads/events/'.$event_id.'/';
                
                // Create uploads directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true); // Ensure directory is created with proper permissions
                }
            
                $errors = []; // To accumulate errors
                $uploadedFiles = []; // To track uploaded files
            
                // Check if 'documents' is an array and process the files
                if (isset($files['name']) && is_array($files['name'])) {
                    // Loop through each uploaded file
                    for ($i = 0; $i < count($files['name']); $i++) {
                        // Check that the file arrays are indeed arrays
                        if (isset($files['name'][$i], $files['tmp_name'][$i], $files['size'][$i], $files['error'][$i], $files['type'][$i])) {
                            // Get the file properties for each file
                            $fileName = $files['name'][$i];
                            $fileTmpName = $files['tmp_name'][$i];
                            $fileSize = $files['size'][$i];
                            $fileError = $files['error'][$i];
                            $fileType = $files['type'][$i];
            
                            // Check if the file type is allowed
                            if (in_array($fileType, $allowedTypes)) {
                                // Generate a unique file name to prevent overwriting
                                // $newFileName = uniqid('', true) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
                                $newFileName = $fileName;
                                $uploadPath = $uploadDir . $newFileName;
            
                                // Check for errors during file upload
                                if ($fileError === 0) {
                                    // Move the uploaded file to the target directory
                                    if (move_uploaded_file($fileTmpName, $uploadPath)) {
                                        $uploadedFiles[] = $newFileName; // Track successful uploads
                                    } else {
                                        $errors[] = "Error uploading '$fileName'.";
                                    }
                                } else {
                                    $errors[] = "Error with file '$fileName'. Error code: $fileError.";
                                }
                            } else {
                                $errors[] = "Invalid file type for '$fileName'. Only PDF, JPG, PNG, DOC, and DOCX are allowed.";
                            }
                        } else {
                            $errors[] = "File information is incomplete for index $i.";
                        }
                    }
                } else {
                    $errors[] = "No files were uploaded or 'documents' is not an array.";
                }
            
                // Return result: handle errors or return success
                if (empty($errors)) {
                    return "Files uploaded successfully: " . implode(", ", $uploadedFiles);
                } else {
                    return "Errors encountered during upload: <br>" . implode("<br>", $errors);
                }
            }
            
            
        }
        
        public function update() {
            $transQuery = array();
        
            // Retrieve form data using the 'name' attributes from the HTML form
        
            $event_id = $this->input->post('event_id');
            $name = $this->input->post('name');
            $venue = $this->input->post('event_venue');
            $description = $this->input->post('description');
            $date = $this->input->post('date');
            $start_time = $this->input->post('start_time');
            $end_time = $this->input->post('end_time');

            $college_ids = $this->input->post('college_ids');
            $program_ids = $this->input->post('program_ids');
            $year_level_ids = $this->input->post('year_level_ids');
            $section_ids = $this->input->post('section_ids');
    
            $dateCreated = date("Y-m-d");
        
            // Validation checks
            if (empty($event_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event id is required',
                );
            }
            else if (empty($name)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event name is required',
                );
            } 
            else if (empty($venue)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event venue is required',
                );
            } 
            else if (empty($description)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event description is required',
                );
            } else if (empty($date)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event date is required',
                );
            } else if (empty($start_time)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event Start time is required',
                );
            }else if (empty($end_time)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event End time is required',
                );
            } else if (empty($college_ids)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'College Ids is required',
                );
            }else if (empty($program_ids)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Program Ids is required',
                );
            } else if (empty($year_level_ids)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Year Level Ids mu is required',
                );
            } else if (empty($section_ids)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Section Ids is required',
                );
            }else {
                try {
                
        
                    // Payload array for new user data
                    $payload = array(
                        'name'                  => $name,
                        'venue'                  => $venue,
                        'description'           => $description,
                        'date'                  => $date,
                        'college_ids'           => $college_ids,
                        'program_ids'           => $program_ids,
                        'year_level_ids'        => $year_level_ids,
                        'section_ids'           => $section_ids,
                        'start_time'            => $start_time,
                        'end_time'              => $end_time,
                        'created_at'            => date("Y-m-d"),
                    );

                    $where = array(
                        'event_id' => $event_id,
                    );
        
                    // Call model function to add user
                    $response = $this->EventModel->update($payload,$where);
                    array_push($transQuery, $response);

                    
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully updated event',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error adding event',
                        );
                    }
        
                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        '_isError' => true,
                        'reason' => $e->getMessage(),
                    );
                }
            }
        
            // Output the response
            $this->response->output($return);
        }
        
        public function void() {
            $transQuery = array();
        
            // Retrieve form data using the 'name' attributes from the HTML form
            $event_id = $this->input->post('event_id');
        
            // Validation checks
            if (empty($event_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'event id is required',
                );
            }else {
                try {
        
                    // Payload array for new user data
                    $payload = array(
                        'is_active'   => 0,
                        'deleted_at'  => date("Y-m-d"),
                    );
                    $where = array(
                        'event_id' => $event_id
                    );
                    // Call model function to add user
                    $response = $this->EventModel->update($payload,$where);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully void event',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error void event',
                        );
                    }
        
                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        '_isError' => true,
                        'reason' => $e->getMessage(),
                    );
                }
            }
        
            // Output the response
            $this->response->output($return);
        }
        public function get_event_participants(){
            /**
             * @var string post data $key
             * @var string session data $accessKey
            */
            try{
                /** 
                    * Call the supploer model
                    * then call the getUser method
                    * @param array $payload.
                */
                $type = $this->input->get("type");
                $event_id = $this->input->get("event_id");

                $college_id = $this->input->get("college_id");
                
                $payload = array();

            

            
            

                $request = $this->EventModel->get($payload,$type);
                $return = array(
                    '_isError'      => false,
                    'message'        =>'Success',
                    'data'          => $request,
                );
            }catch (Exception $e) {
                //set server error
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'message'   => $e->getMessage(),
                );
            }
            $this->response->output($return); //return the json encoded data
        }
        
        public function remove_participants() {
            $transQuery = array();
            $return = array();
            // Retrieve form data using the 'name' attributes from the HTML form
            $event_id = $this->input->post('event_id');
            $type = $this->input->post('type');
            $college_id = $this->input->post('college_id');
            $program_id = $this->input->post('program_id');
            $year_level_id = $this->input->post('year_level_id');
            $section_id = $this->input->post('section_id');
            $message = "";
            $validType = ['college','program','year_level','section'];  
        
            // Validation checks
            if (empty($event_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'event id is required',
                );
            }
            else if (empty($type)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Deletion type is required',
                );
            }
            else if (!in_array($type,$validType)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Invalid Deletion type',
                );
            }
            else {
                try {
                    $payload = array();
                    $where = array();
                    if($type == "college"){
                        $payload = array(
                            'is_active'   => 0,
                            'deleted_at'  => date("Y-m-d"),
                        );
                        $where = array(
                            'college_id' => $college_id,
                            'event_id' => $event_id
                        );

                        $response = $this->EventModel->updateParticipants($payload,$where);
                        array_push($transQuery, $response);
                        $message = "Successfuly remove college";

                    }else if($type == "program"){
                      
                        if (empty($college_id)) {
                            $return = array(
                                '_isError' => true,
                                'reason' => 'College id is required',
                            );
                            $this->response->output($return);return false;  
                        }
                        else if (empty($program_id)) {
                            $return = array(
                                '_isError' => true,
                                'reason' => 'Program id is required',
                            );
                            $this->response->output($return);return false;
                        }else{
                            $payload = array(
                                'event_id'=>$event_id,
                                'college_id'=>$college_id,
                            );
                            $data = $this->EventModel->getParticipantsDetails($payload);
                            if(sizeof($data) > 0){
                                $program_ids = $data[0]->program_id;

                                $array = explode(",", $program_ids);  // Split string by comma
                                $array = array_filter($array, function($value) use ($program_id) {
                                    return $value !== (string) $program_id;  // Remove the value "1"
                                });
                                $string = implode(",", $array);  // Join the array back into a string
                       
                                $payload = array(
                                    'program_id'   => $string,
                                    'updated_at'  => date("Y-m-d"),
                                );
                                $where = array(
                                    'college_id' => $college_id,
                                    'event_id' => $event_id
                                );

                                $response = $this->EventModel->updateParticipants($payload,$where);
                                array_push($transQuery, $response);
                                $message = "Successfuly remove program";

                            }else{
                                $return = array(
                                    '_isError' => true,
                                    'reason' => 'No data to delete',
                                );
                            }
                        }

                        
                    }else if($type == "year_level"){
                      
                        if (empty($college_id)) {
                            $return = array(
                                '_isError' => true,
                                'reason' => 'College id is required',
                            );
                            $this->response->output($return);return false;  
                        }
                        else if (empty($year_level_id)) {
                            $return = array(
                                '_isError' => true,
                                'reason' => 'Year Level id is required',
                            );
                            $this->response->output($return);return false;
                        }else{
                            $payload = array(
                                'event_id'=>$event_id,
                                'college_id'=>$college_id,
                            );
                            $data = $this->EventModel->getParticipantsDetails($payload);
                            if(sizeof($data) > 0){
                                $year_level_ids = $data[0]->year_level_id;

                                $array = explode(",", $year_level_ids);  // Split string by comma
                                $array = array_filter($array, function($value) use ($year_level_id) {
                                    return $value !== (string) $year_level_id;  // Remove the value "1"
                                });
                                $string = implode(",", $array);  // Join the array back into a string
                       
                                $payload = array(
                                    'year_level_id'   => $string,
                                    'updated_at'  => date("Y-m-d"),
                                );
                                $where = array(
                                    'college_id' => $college_id,
                                    'event_id' => $event_id
                                );

                                $response = $this->EventModel->updateParticipants($payload,$where);
                                array_push($transQuery, $response);
                                $message = "Successfuly remove year level";

                            }else{
                                $return = array(
                                    '_isError' => true,
                                    'reason' => 'No data to delete',
                                );
                            }
                        }

                        
                    }else if($type == "section"){
                      
                        if (empty($college_id)) {
                            $return = array(
                                '_isError' => true,
                                'reason' => 'College id is required',
                            );
                            $this->response->output($return);return false;  
                        }
                        else if (empty($section_id)) {
                            $return = array(
                                '_isError' => true,
                                'reason' => 'Section id is required',
                            );
                            $this->response->output($return);return false;
                        }else{
                            $payload = array(
                                'event_id'=>$event_id,
                                'college_id'=>$college_id,
                            );
                            $data = $this->EventModel->getParticipantsDetails($payload);
                            if(sizeof($data) > 0){
                                $section_ids = $data[0]->section_id;

                                $array = explode(",", $section_ids);  // Split string by comma
                                $array = array_filter($array, function($value) use ($section_id) {
                                    return $value !== (string) $section_id;  // Remove the value "1"
                                });
                                $string = implode(",", $array);  // Join the array back into a string
                       
                                $payload = array(
                                    'section_id'   => $string,
                                    'updated_at'  => date("Y-m-d"),
                                );
                                $where = array(
                                    'college_id' => $college_id,
                                    'event_id' => $event_id
                                );

                                $response = $this->EventModel->updateParticipants($payload,$where);
                                array_push($transQuery, $response);
                                $message = "Successfuly remove section";

                            }else{
                                $return = array(
                                    '_isError' => true,
                                    'reason' => 'No data to delete',
                                );
                            }
                        }

                        
                    }



        
                    // // Payload array for new user data
            
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => $message,
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error void event',
                        );
                    }
        
                } catch (Exception $e) {
                    // Handle exception and return error response
                    $return = array(
                        '_isError' => true,
                        'reason' => $e->getMessage(),
                    );
                }
            }
        
            // Output the response
            $this->response->output($return);
        }

        // public function event_attendance() {

        //     $event_id = $this->input->get("event_id");
        //     $section_id = $this->input->get("section_id");
        //     $program_id = $this->input->get("program_id");

        //     if(empty($event_id)){
        //         $return = array(
        //             '_isError'      => true,
        //             'message'        =>'Event id is required',
        //             'attendance' =>  array(
        //                 'students' => [],
        //                 'teachers' => [],
        //             )
        //         );
        //     }else if(empty($section_id)){
        //         $return = array(
        //             '_isError'      => true,
        //             'message'        =>'Section id is required',
        //             'attendance' =>  array(
        //                 'students' => [],
        //                 'teachers' => [],
        //             )
        //         );
        //     }else if(empty($program_id)){
        //         $return = array(
        //             '_isError'      => true,
        //             'message'        =>'Program id is required',
        //             'attendance' =>  array(
        //                 'students' => [],
        //                 'teachers' => [],
        //             )
        //         );
        //     }else{
        //         // Step 1: Retrieve all students
        //         $students = $this->StudentModel->get(array(
        //             'students.section_id' => $section_id
        //         ));
        //         $student_ids = array_column($students, 'student_id');  // Extract student IDs
        
        //         // Step 2: Retrieve all teachers
        //         $teachers = $this->TeacherModel->get(array(
        //             'teachers.program_id' => $program_id
        //         ));
        //         $teacher_ids = array_column($teachers, 'teacher_id');  // Extract teacher IDs
        
        //         // Step 3: Retrieve the event date
        //         $event = $this->EventModel->get_event_date($event_id);
        //         $event_date = $event->date;  // Assuming the event date is in the format 'Y-m-d'
        
        //         // Step 4: Retrieve attendance records for students and teachers for the given event
        //         $attendance = $this->EventModel->get_multiple_attendance($student_ids, $teacher_ids, $event_id);
        
        //         // Step 5: Prepare the result to return
        //         $students_attendance_details = [];
        //         $teachers_attendance_details = [];
        //         $current_date = date('Y-m-d');  // Get the current date
        
        //         // Check attendance for each student
        //         foreach ($students as $student) {
        //             $attendance_record = $this->EventModel->get_attendance($student->student_id, null, $event_id);
                    
        //             if ($event_date > $current_date) {
        //                 // Event is in the future, set attendance status as null
        //                 $students_attendance_details[] = [
        //                     'id' => $student->student_id,
        //                     'name' => $student->first_name . ' ' . $student->last_name,
        //                     'college' => $student->college,
        //                     'college_short_name' => $student->short_name,
        //                     'program' => $student->program,
        //                     'program_short_name' => $student->program_short_name,
        //                     'year_level' => $student->year_level,
        //                     'section' => $student->section,
        //                     'status' => null,  // No attendance status for future events
        //                     'time_in' => null,
        //                     'time_out' => null
        //                 ];
        //             } else {
        //                 // Event is today or in the past
        //                 if ($attendance_record) {
        //                     // Attendance exists, check for lateness
        //                     $status = $this->check_lateness($attendance_record->time_in, $event_id);
        //                     $students_attendance_details[] = [
        //                         'id' => $student->student_id,
        //                         'name' => $student->first_name . ' ' . $student->last_name,
        //                         'college' => $student->college,
        //                         'college_short_name' => $student->short_name,
        //                         'program' => $student->program,
        //                         'program_short_name' => $student->program_short_name,
        //                         'year_level' => $student->year_level,
        //                         'section' => $student->section,
        //                         'status' => $status,  // "Late" or "On Time"
        //                         'time_in' => $attendance_record->time_in,
        //                         'time_out' => $attendance_record->time_out
        //                     ];
        //                 } else {
        //                     // No attendance, mark as Absent
        //                     $students_attendance_details[] = [
        //                         'id' => $student->student_id,
        //                         'name' => $student->first_name . ' ' . $student->last_name,
        //                         'college' => $student->college,
        //                         'college_short_name' => $student->short_name,
        //                         'program' => $student->program,
        //                         'program_short_name' => $student->program_short_name,
        //                         'year_level' => $student->year_level,
        //                         'section' => $student->section,
        //                         'status' => 'Absent',  // Student has not attended
        //                         'time_in' => null,
        //                         'time_out' => null
        //                     ];
        //                 }
        //             }
        //         }
        
        //         // Check attendance for each teacher
        //         foreach ($teachers as $teacher) {
        //             $attendance_record = $this->EventModel->get_attendance(null, $teacher->teacher_id, $event_id);
        
        //             if ($event_date > $current_date) {
        //                 // Event is in the future, set attendance status as null
        //                 $teachers_attendance_details[] = [
        //                     'id' => $teacher->teacher_id,
        //                     'name' => $teacher->first_name . ' ' . $teacher->last_name,
        //                     'status' => null,  // No attendance status for future events
        //                     'time_in' => null,
        //                     'time_out' => null
        //                 ];
        //             } else {
        //                 // Event is today or in the past
        //                 if ($attendance_record) {
        //                     // Attendance exists, check for lateness
        //                     $status = $this->check_lateness($attendance_record->time_in, $event_id);
        //                     $teachers_attendance_details[] = [
        //                         'id' => $teacher->teacher_id,
        //                         'name' => $teacher->first_name . ' ' . $teacher->last_name,
        //                         'college' => $student->college,
        //                         'college_short_name' => $student->short_name,
        //                         'program' => $student->program,
        //                         'program_short_name' => $student->program_short_name,
        //                         'status' => $status,  // "Late" or "On Time"
        //                         'time_in' => $attendance_record->time_in,
        //                         'time_out' => $attendance_record->time_out
        //                     ];
        //                 } else {
        //                     // No attendance, mark as Absent
        //                     $teachers_attendance_details[] = [
        //                         'id' => $teacher->teacher_id,
        //                         'name' => $teacher->first_name . ' ' . $teacher->last_name,
        //                         'college' => $student->college,
        //                         'college_short_name' => $student->short_name,
        //                         'program' => $student->program,
        //                         'program_short_name' => $student->program_short_name,
        //                         'status' => 'Absent',  // Teacher has not attended
        //                         'time_in' => null,
        //                         'time_out' => null
        //                     ];
        //                 }
        //             }
        //         }
        
        //         $return = array(
        //             '_isError'      => false,
        //             'message'        =>'Success',
        //             'attendance' =>  array(
        //                 'students' => $students_attendance_details,
        //                 'teachers' => $teachers_attendance_details,
        //             )
        //         );
        //     }

        //     $this->response->output($return);
        // }

        public function event_attendance() {
            $event_id = $this->input->get("event_id");
            $section_id = $this->input->get("section_id");
            $program_id = $this->input->get("program_id");
        
            if (empty($event_id)) {
                $return = array(
                    '_isError' => true,
                    'message' => 'Event id is required',
                    'attendance' => array(
                        'students' => [],
                        'teachers' => [],
                    ),
                );
            } else {
                

                $event_payload = array(
                    'event_id' => $event_id,
                );
                $event = $this->EventModel->get_single($event_payload);
                
                if($event->is_ended == 0){
                    // Step 1: Retrieve event participants to determine sections and programs
                    $event_participants = $this->EventModel->get_event_participants($event_id);
            
                    // print_r( $event_participants );exit;
                    $all_section_ids = [];
                    $all_program_ids = [];
                
                    // If section_id or program_id is not provided, get all sections and programs from event participants
                    if (empty($section_id) && empty($program_id)) {
                    
                    

                        foreach ($event_participants as $participant) {
                            // Split the comma-separated program_id and section_id into arrays
                            $program_ids = explode(',', $participant->program_id);
                            $section_ids = explode(',', $participant->section_id);
                        
                            // Merge the arrays and remove duplicates
                            $all_section_ids = array_merge($all_section_ids, $section_ids);
                            $all_program_ids = array_merge($all_program_ids, $program_ids);
                        }
                    
                        $all_section_ids = array_unique($all_section_ids);
                        $all_program_ids = array_unique($all_program_ids);
                    
                        // Optional: Re-index the arrays if needed
                        $all_section_ids = array_values($all_section_ids);
                        $all_program_ids = array_values($all_program_ids);
                    }else{
                        $all_section_ids = array_unique([$section_id]);
                        $all_program_ids = array_unique([$program_id]);

                        // Optional: Re-index the arrays if needed
                        $all_section_ids = array_values($all_section_ids);
                        $all_program_ids = array_values($all_program_ids);  
                    }

                
            

            
                    // Step 2: Retrieve all students and teachers based on section_ids and program_ids
                    $students = $this->StudentModel->get_student_event($all_section_ids);
                    $student_ids = array_column($students, 'student_id');  // Extract student IDs
            
                    // Step 3: Retrieve all teachers based on program_ids
                    $teachers = $this->TeacherModel->get_teacher_event($all_program_ids);
                    $teacher_ids = array_column($teachers, 'teacher_id');  // Extract teacher IDs
            
                    // Step 4: Retrieve the event date
                    $event = $this->EventModel->get_event_date($event_id);
                    $event_date = $event->date;  // Assuming the event date is in the format 'Y-m-d'
            
                    // Step 5: Retrieve attendance records for students and teachers for the given event
                    $attendance = $this->EventModel->get_multiple_attendance($student_ids, $teacher_ids, $event_id);
            
                    // Step 6: Prepare the result to return
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
                                    'college' => $teacher->college,
                                    'college_short_name' => $teacher->short_name,
                                    'program' => $teacher->program,
                                    'program_short_name' => $teacher->program_short_name,
                                    'status' => $status,  // "Late" or "On Time"
                                    'time_in' => $attendance_record->time_in,
                                    'time_out' => $attendance_record->time_out
                                ];
                            } else {
                                // No attendance, mark as Absent
                                $teachers_attendance_details[] = [
                                    'id' => $teacher->teacher_id,
                                    'name' => $teacher->first_name . ' ' . $teacher->last_name,
                                    'college' => $teacher->college,
                                    'college_short_name' => $teacher->short_name,
                                    'program' => $teacher->program,
                                    'program_short_name' => $teacher->program_short_name,
                                    'status' => 'Absent',  // Teacher has not attended
                                    'time_in' => null,
                                    'time_out' => null
                                ];
                            }
                        }
                    }

                
                    $return = array(
                        '_isError' => false,
                        'message' => 'Success',
                        'event' =>  $event,
                        'attendance' => array(
                            'students' => $students_attendance_details,
                            'teachers' => $teachers_attendance_details,
                        ),
                    );
                }else{
                    $event = $this->EventModel->get_event_record($event_id);
                 
                    $return = array(
                        '_isError' => false,
                        'message' => 'Success',
                        'event' =>  $this->EventModel->get_single($event_payload),
                        'attendance' => array(
                            'students'=>json_decode($event->records)->attendance->students,
                            'teachers'=>json_decode($event->records)->attendance->teachers,
                        ),
                    );
                }
                
                
            }
        
            $this->response->output($return);
        }

        public function end_event_attendance() {
            $event_id = $this->input->post("event_id");
            $section_id = $this->input->post("section_id");
            $program_id = $this->input->post("program_id");
        
            if (empty($event_id)) {
                $return = array(
                    '_isError' => true,
                    'message' => 'Event id is required',
                    'attendance' => array(
                        'students' => [],
                        'teachers' => [],
                    ),
                );
            } else {
                // Step 1: Retrieve event participants to determine sections and programs
                $event_participants = $this->EventModel->get_event_participants($event_id);
          
                // print_r( $event_participants );exit;
                $all_section_ids = [];
                $all_program_ids = [];
             
                // If section_id or program_id is not provided, get all sections and programs from event participants
                if (empty($section_id) && empty($program_id)) {
                  
                  

                    foreach ($event_participants as $participant) {
                        // Split the comma-separated program_id and section_id into arrays
                        $program_ids = explode(',', $participant->program_id);
                        $section_ids = explode(',', $participant->section_id);
                    
                        // Merge the arrays and remove duplicates
                        $all_section_ids = array_merge($all_section_ids, $section_ids);
                        $all_program_ids = array_merge($all_program_ids, $program_ids);
                    }
                   
                    $all_section_ids = array_unique($all_section_ids);
                    $all_program_ids = array_unique($all_program_ids);
                
                    // Optional: Re-index the arrays if needed
                    $all_section_ids = array_values($all_section_ids);
                    $all_program_ids = array_values($all_program_ids);
                }else{
                    $all_section_ids = array_unique([$section_id]);
                    $all_program_ids = array_unique([$program_id]);

                    // Optional: Re-index the arrays if needed
                    $all_section_ids = array_values($all_section_ids);
                    $all_program_ids = array_values($all_program_ids);  
                }

              
           

        
                // Step 2: Retrieve all students and teachers based on section_ids and program_ids
                $students = $this->StudentModel->get_student_event($all_section_ids);
                $student_ids = array_column($students, 'student_id');  // Extract student IDs
        
                // Step 3: Retrieve all teachers based on program_ids
                $teachers = $this->TeacherModel->get_teacher_event($all_program_ids);
                $teacher_ids = array_column($teachers, 'teacher_id');  // Extract teacher IDs
        
                // Step 4: Retrieve the event date
                $event = $this->EventModel->get_event_date($event_id);
                $event_date = $event->date;  // Assuming the event date is in the format 'Y-m-d'
        
                // Step 5: Retrieve attendance records for students and teachers for the given event
                $attendance = $this->EventModel->get_multiple_attendance($student_ids, $teacher_ids, $event_id);
        
                // Step 6: Prepare the result to return
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
                                'college' => $teacher->college,
                                'college_short_name' => $teacher->short_name,
                                'program' => $teacher->program,
                                'program_short_name' => $teacher->program_short_name,
                                'status' => $status,  // "Late" or "On Time"
                                'time_in' => $attendance_record->time_in,
                                'time_out' => $attendance_record->time_out
                            ];
                        } else {
                            // No attendance, mark as Absent
                            $teachers_attendance_details[] = [
                                'id' => $teacher->teacher_id,
                                'name' => $teacher->first_name . ' ' . $teacher->last_name,
                                'college' => $teacher->college,
                                'college_short_name' => $teacher->short_name,
                                'program' => $teacher->program,
                                'program_short_name' => $teacher->program_short_name,
                                'status' => 'Absent',  // Teacher has not attended
                                'time_in' => null,
                                'time_out' => null
                            ];
                        }
                    }
                }
                
                $event_payload = array(
                    'event_id' => $event_id,
                );
                $data = array(
                    'attendance' => array(
                        'students' => $students_attendance_details,
                        'teachers' => $teachers_attendance_details,
                    ),
                );
            }
          
            $transQuery = array();
            
            $array_save = array(
                'event_id' => $event_id,
                'records' =>  json_encode($data),               
            );
            
            $event_query = $this->EventModel->add_record($array_save);

            array_push($transQuery, $event_query);

            $where = array(
                'event_id' => $event_id,
            );
            $array_event_update = array(
                'is_ended' => 1,               
            );
            
            $event_update_query = $this->EventModel->update($array_event_update,$where);
            array_push($transQuery, $event_update_query);

            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);

            // Success response
            if ($res) { 
                $return = array(
                    'isError' => false,
                    'data' => [],
                    'message' => "Successfuly edded event",
                );
                $this->response->output($return);
                return;
            }else{
                $return = array(
                    '_isError' => true,
                    'reason' => 'Error ending the event',
                );
                $this->response->output($return);
                return;
            }
        }
        

    // Check if the student or teacher is late based on their time_in and event start time
    private function check_lateness($time_in, $event_id) {
        // Assuming event has a start time field (event_start_time)
        $event = $this->EventModel->get_event_date($event_id);  // Get event details (e.g., start time)
        $start_time = $event->start_time;  // Get the event start time
        
        $allowance_time = strtotime($start_time) + 30 * 60;  // Add 30 minutes in seconds
    
        // Compare the time_in with the adjusted start time (allowance time)
        if (strtotime($time_in) > $allowance_time) {
            return 'Late';
        } else {
            return 'On Time';
        }
    }

    public function get_all_face_descriptors($event_id)
    {
        // Step 1: Retrieve event participants to determine sections and programs
        $event_participants = $this->EventModel->get_event_participants($event_id);
        // print_r( $event_participants );exit;
        $all_section_ids = [];
        $all_program_ids = [];
        
        // If section_id or program_id is not provided, get all sections and programs from event participants
        if (empty($section_id) && empty($program_id)) {
            foreach ($event_participants as $participant) {
                // Split the comma-separated program_id and section_id into arrays
                $program_ids = explode(',', $participant->program_id);
                $section_ids = explode(',', $participant->section_id);
            
                // Merge the arrays and remove duplicates
                $all_program_ids = array_merge($all_program_ids, $program_ids);
                $all_section_ids = array_merge($all_section_ids, $section_ids);
            }

            $all_section_ids = array_unique($all_section_ids);
            $all_program_ids = array_unique($all_program_ids);

            // Optional: Re-index the arrays if needed
            $all_section_ids = array_values($all_section_ids);
            $all_program_ids = array_values($all_program_ids);
        }else{
            $all_section_ids = array_unique([$section_id]);
            $all_program_ids = array_unique([$program_id]);

            // Optional: Re-index the arrays if needed
            $all_section_ids = array_values($all_section_ids);
            $all_program_ids = array_values($all_program_ids);  
        }

        



        // Step 2: Retrieve all students and teachers based on section_ids and program_ids
        $students = $this->StudentModel->get_student_event($all_section_ids);
        $student_ids = array_column($students, 'student_id');  // Extract student IDs

        // Step 3: Retrieve all teachers based on program_ids
        $teachers = $this->TeacherModel->get_teacher_event($all_program_ids);
        $teacher_ids = array_column($teachers, 'teacher_id');  // Extract teacher IDs

        // Step 4: Retrieve the event date
        $event = $this->EventModel->get_event_date($event_id);
        $event_date = $event->date;  // Assuming the event date is in the format 'Y-m-d'

        // Step 5: Retrieve attendance records for students and teachers for the given event
        $attendance = $this->EventModel->get_multiple_attendance($student_ids, $teacher_ids, $event_id);

        // Step 6: Prepare the result to return
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
                    'face_descriptor' => $student->face_descriptor,
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
                        'face_descriptor' => $student->face_descriptor,
                        'time_in' => $attendance_record->time_in,
                        'time_out' => $attendance_record->time_out
                    ];
                } else {
                    // No attendance, mark as Absent
                    $students_attendance_details[] = [
                        'id' => $student->student_id,
                        'name' => $student->first_name . ' ' . $student->last_name,
                        'face_descriptor' => $student->face_descriptor,
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
                    'face_descriptor' => $student->face_descriptor,
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
                        'face_descriptor' => $student->face_descriptor,
                        'time_in' => $attendance_record->time_in,
                        'time_out' => $attendance_record->time_out
                    ];
                } else {
                    // No attendance, mark as Absent
                    $teachers_attendance_details[] = [
                        'id' => $teacher->teacher_id,
                        'name' => $teacher->first_name . ' ' . $teacher->last_name,
                        'face_descriptor' => $teacher->face_descriptor,
                        'time_in' => null,
                        'time_out' => null
                    ];
                }
            }
        }
        
        $event_payload = array(
            'event_id' => $event_id,
        );

        $combined_attendance_details = array_merge(
            array_map(function($student) {
                $student['type'] = 'student';  // Add a type to differentiate students
                return $student;
            }, $students_attendance_details),
            
            array_map(function($teacher) {
                $teacher['type'] = 'teacher';  // Add a type to differentiate teachers
                return $teacher;
            }, $teachers_attendance_details)
        );
        
        return $combined_attendance_details;
    }


    public function add_attendance()
    {
        $transQuery      = array();
        $date            = date("Y-m-d");
        $attendance_type = $this->input->post("attendance_type");
        $user_type       = $this->input->post("user_type");
        $student_id      = $this->input->post("student_id");
        $teacher_id      = $this->input->post("teacher_id");
        $event_id        = $this->input->post("event_id");


        $savedData = $this->get_all_face_descriptors($event_id);

    
        // Define a reasonable threshold for face match (adjustable)
        $threshold = 0.6;
        $participants = 0;
        // Loop through all saved descriptors and compare with the incoming one
        foreach ($savedData as $saved) {
           

            // If a match is found (distance below threshold), return the corresponding user data
            if ($user_type == "student") {
                if($saved['id'] == $student_id){
                    $participants = 1;
                    if($attendance_type == "time_in" && $saved['time_in'] != null){
                        $return = array(
                            'isError' => true,
                            'data' => $saved,
                            'message' => 'This face already time_in',
                        );
                        $this->response->output($return);
                        return;
                    }else if($attendance_type == "time_out" && $saved['time_in'] == null){
                        $return = array(
                            'isError' => true,
                            'data' => $saved,
                            'message' => 'Cant timeout with a time-in',
                        );
                        $this->response->output($return);
                        return;
                    }
                    else if($attendance_type == "time_out" && $saved['time_out'] != '00:00:00'){
                        $return = array(
                            'isError' => true,
                            'data' => $saved,
                            'message' => 'This face already time_out',
                        );
                        $this->response->output($return);
                        return;
                    }
                    else{
                        $date = date("Y-m-d");
                        if($attendance_type == "time_in"){
                            $timelog_payload['date'] = $date;
                            $timelog_payload['time_in'] = date("H:i:s");
                            $timelog_payload['event_id'] = $event_id;
                            if($saved['type'] == "student"){
                                $timelog_payload['student_id'] = $saved['id'];
                            }else if($saved['type'] == "teacher"){
                                $timelog_payload['teacher_id'] = $saved['id'];
                            }

                            $response = $this->EventModel->time_in($timelog_payload);
                            array_push($transQuery, $response);
                        }
                        if($attendance_type == "time_out"){
                            $timelog_payload['time_out'] = date("H:i:s");
                            $where = array(
                                'event_id' => $event_id,
                            );

                            if($saved['type'] == "student"){
                                $where['student_id'] = $saved['id'];
                            }else if($saved['type'] == "teacher"){
                                $where['teacher_id'] = $saved['id'];
                            }


                            $response = $this->EventModel->time_out($timelog_payload,$where);
                            array_push($transQuery, $response);
                        }

                        $result = array_filter($transQuery);
                        $res = $this->mysqlTQ($result);
            
                        // Success response
                        if ($res) { 
                            $t = $attendance_type == 'time_in' ? 'Time in' : 'Time out';
                            $return = array(
                                'isError' => false,
                                'data' => $saved,
                                'message' => "Face recognized! and {$t}",
                            );
                            $this->response->output($return);
                            return;
                        }else{
                            $return = array(
                                '_isError' => true,
                                'reason' => 'Error adding, please contact a support or scan you QR',
                            );
                            $this->response->output($return);
                            return;
                        }
                    }
                }
            }else if ($user_type == "teacher") {
                if($saved['id'] == $teacher_id){
                    $participants = 1;
                    if($attendance_type == "time_in" && $saved['time_in'] != null){
                        $return = array(
                            'isError' => true,
                            'data' => $saved,
                            'message' => 'This face already time_in',
                        );
                        $this->response->output($return);
                        return;
                    }else if($attendance_type == "time_out" && $saved['time_in'] == null){
                        $return = array(
                            'isError' => true,
                            'data' => $saved,
                            'message' => 'Cant timeout with a time-in',
                        );
                        $this->response->output($return);
                        return;
                    }
                    else if($attendance_type == "time_out" && $saved['time_out'] != '00:00:00'){
                        $return = array(
                            'isError' => true,
                            'data' => $saved,
                            'message' => 'This face already time_out',
                        );
                        $this->response->output($return);
                        return;
                    }
                    else{
                        $date = date("Y-m-d");
                        if($attendance_type == "time_in"){
                            $timelog_payload['date'] = $date;
                            $timelog_payload['time_in'] = date("H:i:s");
                            $timelog_payload['event_id'] = $event_id;
                            if($saved['type'] == "student"){
                                $timelog_payload['student_id'] = $saved['id'];
                            }else if($saved['type'] == "teacher"){
                                $timelog_payload['teacher_id'] = $saved['id'];
                            }

                            $response = $this->EventModel->time_in($timelog_payload);
                            array_push($transQuery, $response);
                        }
                        if($attendance_type == "time_out"){
                            $timelog_payload['time_out'] = date("H:i:s");
                            $where = array(
                                'event_id' => $event_id,
                            );

                            if($saved['type'] == "student"){
                                $where['student_id'] = $saved['id'];
                            }else if($saved['type'] == "teacher"){
                                $where['teacher_id'] = $saved['id'];
                            }


                            $response = $this->EventModel->time_out($timelog_payload,$where);
                            array_push($transQuery, $response);
                        }

                        $result = array_filter($transQuery);
                        $res = $this->mysqlTQ($result);
            
                        // Success response
                        if ($res) { 
                            $t = $attendance_type == 'time_in' ? 'Time in' : 'Time out';
                            $return = array(
                                'isError' => false,
                                'data' => $saved,
                                'message' => "Face recognized! and {$t}",
                            );
                            $this->response->output($return);
                            return;
                        }else{
                            $return = array(
                                '_isError' => true,
                                'reason' => 'Error adding, please contact a support or scan you QR',
                            );
                            $this->response->output($return);
                            return;
                        }
                    }
                }
            }
        }

        if($participants == 0){
            $return = array(
                '_isError' => true,
                'message' => 'This student is not part of the event',
            );
            $this->response->output($return);
            return;
        }
        



      
    }

    public function recognize_face_event_attendance(){
    // Check if the request is a POST request
        $transQuery = array();
        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            // Get the incoming descriptor from POST data
            $event_id = $this->input->post("event_id");
            $type = $this->input->post("type");
            $incomingDescriptor = json_decode($this->input->post('descriptor'), true);
        
            // Check if descriptor data is provided
            if (empty($incomingDescriptor)) {   
                $return = array(
                    'isError' => true,
                    'data' => [],
                    'message' => 'No descriptor data received.',
                );
                $this->response->output($return);
                return;
            }
            else if (empty($type)) {   
                $return = array(
                    'isError' => true,
                    'data' => [],
                    'message' => 'Type must be time_in or time_out.',
                );
                $this->response->output($return);
                return;
            }

            // Fetch all saved face descriptors and associated user info from the database
            $savedData = $this->get_all_face_descriptors($event_id);
        
            // Define a reasonable threshold for face match (adjustable)
            $threshold = 0.6;

            // Loop through all saved descriptors and compare with the incoming one
            foreach ($savedData as $saved) {
                $savedDescriptor = json_decode($saved['face_descriptor'], true);

                // Ensure both descriptors are in the correct format
                if ($savedDescriptor === null || !is_array($savedDescriptor)) {
                    // Skip this record if the saved descriptor is invalid or empty
                    continue;
                }
            
                // Ensure both descriptors (saved and incoming) are in the correct format
                if (!is_array($incomingDescriptor)) {
                    $return = array(
                        'isError' => true,
                        'data' => [],
                        'message' => 'Incoming face descriptor is invalid.'
                    );
                    $this->response->output($return);
                    return;
                }

                // Calculate the Euclidean distance between the saved and incoming descriptors
                $distance = $this->euclidean_distance($savedDescriptor, $incomingDescriptor);

                // If a match is found (distance below threshold), return the corresponding user data
                if ($distance < $threshold) {
              
                    if($type == "time_in" && $saved['time_in'] != null){
                        $return = array(
                            'isError' => true,
                            'data' => $saved,
                            'message' => 'This face already time_in',
                        );
                        $this->response->output($return);
                        return;
                    }else if($type == "time_out" && $saved['time_in'] == null){
                        $return = array(
                            'isError' => true,
                            'data' => $saved,
                            'message' => 'Cant timeout with a time-in',
                        );
                        $this->response->output($return);
                        return;
                    }
                    else if($type == "time_out" && $saved['time_out'] != '00:00:00'){
                        $return = array(
                            'isError' => true,
                            'data' => $saved,
                            'message' => 'This face already time_out',
                        );
                        $this->response->output($return);
                        return;
                    }
                    else{
                        $date = date("Y-m-d");
                        if($type == "time_in"){
                            $timelog_payload['date'] = $date;
                            $timelog_payload['time_in'] = date("H:i:s");
                            $timelog_payload['event_id'] = $event_id;
                            if($saved['type'] == "student"){
                                $timelog_payload['student_id'] = $saved['id'];
                            }else if($saved['type'] == "teacher"){
                                $timelog_payload['teacher_id'] = $saved['id'];
                            }

                            $response = $this->EventModel->time_in($timelog_payload);
                            array_push($transQuery, $response);
                        }
                        if($type == "time_out"){
                            $timelog_payload['time_out'] = date("H:i:s");
                            $where = array(
                                'event_id' => $event_id,
                            );

                            if($saved['type'] == "student"){
                                $where['student_id'] = $saved['id'];
                            }else if($saved['type'] == "teacher"){
                                $where['teacher_id'] = $saved['id'];
                            }


                            $response = $this->EventModel->time_out($timelog_payload,$where);
                            array_push($transQuery, $response);
                        }

                        $result = array_filter($transQuery);
                        $res = $this->mysqlTQ($result);
            
                        // Success response
                        if ($res) { 
                            $t = $type == 'time_in' ? 'Time in' : 'Time out';
                            $return = array(
                                'isError' => false,
                                'data' => $saved,
                                'message' => "Face recognized! and {$t}",
                            );
                            $this->response->output($return);
                            return;
                        }else{
                            $return = array(
                                '_isError' => true,
                                'reason' => 'Error adding, please contact a support or scan you QR',
                            );
                            $this->response->output($return);
                            return;
                        }
                    }
                }
            }

            // If no match is found
            $return = array(
                'isError' => true,
                'message' => 'Face not recognized.',
            );

        } else {
            $return = array(
                'isError' => true,
                'message' => 'Invalid request.',
            );
        }

        $this->response->output($return);
        
    }

    // Function to calculate the Euclidean distance between two descriptors
    private function euclidean_distance($a, $b)
    {
        $sum = 0;
        foreach ($a as $key => $value) {
            $sum += pow($value - $b[$key], 2);
        }
        return sqrt($sum);
    }
        

    }
?>