<?php
   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

    class Student extends MY_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->model('StudentModel');
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
                $student_id = $this->input->get("student_id");
                $payload = array(
                    'students.is_active' => 1
                );

                if(!empty($student_id)){
                    $payload['student_id'] = $student_id;
                }

                $request = $this->StudentModel->get($payload);
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

        // function generateStudentId($dbid) {
        //     // Get the current date in the format mmddyy
        //     $date = date('mdy'); 
            
        //     // Format the student ID as STUD-mmddYY-dbid
        //     $studentId = "STUD-" . $date . "-" . str_pad($dbid, 3, '0', STR_PAD_LEFT); 
            
        //     return $studentId;
        // }


        
        

        public function add() {
            $transQuery = array();
        
            // Retrieve form data using the 'name' attributes from the HTML form
        
            $first_name = $this->input->post('first_name');
            $middle_name = $this->input->post('middle_name');
            $last_name = $this->input->post('last_name');
            $college_id = $this->input->post('college_id');
            $program_id = $this->input->post('program_id');
            $year_level_id = $this->input->post('year_level_id');
            $section_id = $this->input->post('section_id');
            $email = $this->input->post('email');
            $mobile = $this->input->post('mobile');
            $mobile = $this->input->post('mobstudent_imageile');
            $dateCreated = date("Y-m-d");
        
            // Validation checks
            if (empty($first_name)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'First name is required',
                );
            } else if (empty($last_name)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Last name is required',
                );
            } else if (empty($email)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Email is required',
                );
            } else if (empty($mobile)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Mobile number is required',
                );
            }  else {
                try {
                    // Hash the password
                    $student_id = $this->StudentModel->generateStudentID();
                    $user_id = $this->UserModel->generateUserID();
                    // $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                    while ($this->StudentModel->isStudentIDExists($student_id)) {
                        // Regenerate student ID if it already exists
                        $student_id = $this->StudentModel->generateStudentID();
                    }

                    // $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                    while ($this->UserModel->isUserIDExists($user_id)) {
                        // Regenerate student ID if it already exists
                        $user_id = $this->UserModel->generateUserID();
                    }
        
                    // Payload array for new user data
                    $payload = array(
                        'student_id'            => $student_id,
                        'user_id'               => $user_id,
                        'first_name'            => $first_name,
                        'middle_name'           => $middle_name,
                        'last_name'             => $last_name,
                        'college_id'            => $college_id,
                        'program_id'            => $program_id,
                        'year_level_id'         => $year_level_id,
                        'section_id'            => $section_id,
                        'email'                 => $email,
                        'students.mobile'       => $mobile,
                        'created_at' => date("Y-m-d"),
                    );
        
                    // Call model function to add user
                    $response = $this->StudentModel->add($payload);
                    array_push($transQuery, $response);


                    // Add Student
                    
                    
                    
        
                     $payload_user = array(
                        'user_id'               => $user_id,
                        'user_type'             => 'student',
                        'username'              => strtolower($first_name).'.'.strtolower($last_name),
                        'password'              => strtolower($last_name),
                        'created_at' => date("Y-m-d"),
                    );
        
                    // Call model function to add user
                    $response_user = $this->UserModel->add($payload_user);
                    array_push($transQuery, $response_user);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully added new student',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error adding student',
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
        
        public function update() {
            $transQuery = array();
        
            // Retrieve form data using the 'name' attributes from the HTML form
            $student_id = $this->input->post('student_id');
            $first_name = $this->input->post('first_name');
            $middle_name = $this->input->post('middle_name');
            $last_name = $this->input->post('last_name');
            $college_id = $this->input->post('college_id');
            $program_id = $this->input->post('program_id');
            $year_level_id = $this->input->post('year_level_id');
            $section_id = $this->input->post('section_id');
            $email = $this->input->post('email');
            $mobile = $this->input->post('mobile');
            $student_image = $this->input->post('student_image');
        
            // Validation checks
            if (empty($student_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'student id is required',
                );
            }
            else if (empty($first_name)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'First name is required',
                );
            } else if (empty($last_name)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Last name is required',
                );
            } else if (empty($email)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Email is required',
                );
            } else if (empty($mobile)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Mobile number is required',
                );
            } else {
                try {
                    // Hash the password
                    // $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
                    // Payload array for new user data
                    $payload = array(
                        'first_name'            => $first_name,
                        'middle_name'           => $middle_name,
                        'last_name'             => $last_name,
                        'college_id'            => $college_id,
                        'program_id'            => $program_id,
                        'year_level_id'         => $year_level_id,
                        'section_id'            => $section_id,
                        'email'                 => $email,
                        'mobile'                => $mobile,
                        'student_image'         => $student_image,
                        // 'students.password' => $password,
                        'updated_at' => date("Y-m-d"),
                    );
                    $where = array(
                        'student_id' => $student_id
                    );
                    // Call model function to add user
                    $response = $this->StudentModel->update($payload,$where);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully updated student',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error updating student',
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
        public function update_face() {
            $transQuery = array();
        
            // Retrieve form data using the 'name' attributes from the HTML form
            $student_id = $this->input->post('student_id');
            $descriptor = $this->input->post('descriptor');
        
            // Validation checks
            if (empty($student_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'student id is required',
                );
            }
            else if (empty($descriptor)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Descriptor is required',
                );
            }  else {
                try {
                    // Hash the password
                    // $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
                    // Payload array for new user data
                    $payload = array(
                        'face_descriptor'            => $descriptor,
                        'updated_at' => date("Y-m-d"),
                    );
                    $where = array(
                        'student_id' => $student_id
                    );
                    // Call model function to add user
                    $response = $this->StudentModel->update($payload,$where);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully updated face',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error updating face',
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
            $student_id = $this->input->post('student_id');
        
            // Validation checks
            if (empty($student_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'student id is required',
                );
            }else {
                try {
        
                    // Payload array for new user data
                    $payload = array(
                        'is_active'   => 0,
                        'deleted_at'  => date("Y-m-d"),
                    );
                    $where = array(
                        'student_id' => $student_id
                    );
                    // Call model function to add user
                    $response = $this->StudentModel->update($payload,$where);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully void student',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error void student',
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