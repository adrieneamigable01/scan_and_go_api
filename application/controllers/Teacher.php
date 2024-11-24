<?php
   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

    class Teacher extends MY_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->model('TeacherModel');
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
                $teacher_id = $this->input->get("teacher_id");
                $payload = array(
                    'teachers.is_active' => 1
                );

                if(!empty($teacher_id)){
                    $payload['teacher_id'] = $teacher_id;
                }

                $request = $this->TeacherModel->get($payload);
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
                    $teacher_id = $this->TeacherModel->generateTeacherID();
                    $user_id = $this->UserModel->generateUserID();
                    // $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                    while ($this->TeacherModel->isTeacherIDExists($teacher_id)) {
                        // Regenerate teacher id if it already exists
                        $teacher_id = $this->TeacherModel->generateTeacherID();
                    }

                    // $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                    while ($this->UserModel->isUserIDExists($user_id)) {
                        // Regenerate teacher id if it already exists
                        $user_id = $this->UserModel->generateUserID();
                    }
        
                    // Payload array for new user data
                    $payload = array(
                        'teacher_id'            => $teacher_id,
                        'user_id'               => $user_id,
                        'first_name'            => $first_name,
                        'middle_name'           => $middle_name,
                        'last_name'             => $last_name,
                        'college_id'            => $college_id,
                        'program_id'            => $program_id,
                        'email'                 => $email,
                        'mobile'       => $mobile,
                        'created_at' => date("Y-m-d"),
                    );
        
                    // Call model function to add user
                    $response = $this->TeacherModel->add($payload);
                    array_push($transQuery, $response);


                    // Add Student
                    
                    
                     $payload_user = array(
                        'user_id'               => $user_id,
                        'user_type'             => 'teacher',
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
                            'reason' => 'Successfully added new teacher',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error adding teacher',
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
        
            $teacher_id = $this->input->post('teacher_id');
            $first_name = $this->input->post('first_name');
            $middle_name = $this->input->post('middle_name');
            $last_name = $this->input->post('last_name');
            $college_id = $this->input->post('college_id');
            $program_id = $this->input->post('program_id');
            $email = $this->input->post('email');
            $mobile = $this->input->post('mobile');
            $teacher_image = $this->input->post('teacher_image');
            $dateCreated = date("Y-m-d");
        
            // Validation checks
            if (empty($teacher_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Teacher ID is required',
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
            }  else {
                try {
                    // Hash the password
                    
        
                    // Payload array for new user data
                    $payload = array(
                        'first_name'            => $first_name,
                        'middle_name'           => $middle_name,
                        'last_name'             => $last_name,
                        'college_id'            => $college_id,
                        'program_id'            => $program_id,
                        'email'                 => $email,
                        'mobile'                => $mobile,
                        'teacher_image'         => $teacher_image,
                        'updated_at' => date("Y-m-d"),
                    );
                    $where = array(
                        'teacher_id' => $teacher_id,
                    );
                    // Call model function to add user
                    $response = $this->TeacherModel->update($payload,$where);
                    array_push($transQuery, $response);

                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully updated new teacher',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error updating teacher',
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
            $teacher_id = $this->input->post('teacher_id');
            $descriptor = $this->input->post('descriptor');
        
            // Validation checks
            if (empty($teacher_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'teacher id is required',
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
                        'teacher_id' => $teacher_id
                    );
                    // Call model function to add user
                    $response = $this->TeacherModel->update($payload,$where);
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
            $teacher_id = $this->input->post('teacher_id');
        
            // Validation checks
            if (empty($teacher_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'teacher id is required',
                );
            }else {
                try {
        
                    // Payload array for new user data
                    $payload = array(
                        'is_active'   => 0,
                        'deleted_at'  => date("Y-m-d"),
                    );
                    $where = array(
                        'teacher_id' => $teacher_id
                    );
                    // Call model function to add user
                    $response = $this->TeacherModel->update($payload,$where);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully void teacher',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error void teacher',
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