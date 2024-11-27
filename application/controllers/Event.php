<?php
   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

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
            $this->load->library('Response',NULL,'response');
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
                $type = $this->input->get("type");
                $payload = array(
                    'events.is_active' => 1
                );
                
                if(!empty($event_id)){
                    $payload['event_id'] = $event_id;
                }
                else if(empty($type)){
                    $type = "upcomming";
                }else{
                    
                }

            
                

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
            $event_description = $this->input->post('event_description');
            $event_date = $this->input->post('event_date');
            $start_time = $this->input->post('start_time');
            $end_time = $this->input->post('end_time');

            $college_ids = $this->input->post('college_ids');
            $program_ids = $this->input->post('program_ids');
            $year_level_ids = $this->input->post('year_level_ids');
            $section_ids = $this->input->post('section_ids');
            $event_image = $this->input->post('event_image');
    
            $dateCreated = date("Y-m-d");
        
            // Validation checks
            if (empty($event_name)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Event name is required',
                );
            } else if (empty($event_description)) {
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
                        'description'           => $event_description,
                        'date'                  => $event_date,
                        'college_ids'           => $college_ids,
                        'program_ids'           => $program_ids,
                        'year_level_ids'        => $year_level_ids,
                        'section_ids'           => $section_ids,
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
                    $upload = $this->uploadDocuments($event_id);
                    if(!$upload){
                        $return = array(
                            '_isError' => true,
                            'reason' => $upload,
                        );
                        $this->response->output($return);
                        return false;
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
            } else if (empty($description)) {
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
    }
?>