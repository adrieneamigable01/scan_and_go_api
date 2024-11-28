<?php

   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

    class Auth extends CI_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->helper('jwt');
            $this->load->model('AuthModel');
            $this->load->model('StudentModel');
            $this->load->model('TeacherModel');
            $this->load->model('UserModel');
            $this->load->library('Response',NULL,'response');
           
        }
        /**
            * Generate a key
            * 
            *
            * @return string return a string use to be the accessKey 
        */
        private function keygen($length=10)
        {
            $key = '';
            list($usec, $sec) = explode(' ', microtime());
            mt_srand((int) $sec + ((int) $usec * 100000));
            
            $inputs = array_merge(range('z','a'),range(0,9),range('A','Z'));

            for($i=0; $i<$length; $i++)
            {
                $key .= $inputs[mt_rand(0,61)];
            }
            return $key;
        }
        /**
            * Authenticate a user
            * 
            *
            * @return array return the data info of a user
        */
        public function login(){

            /**
             * @var string post data $username
             * @var string post data $password
             * @var array  data $return
            */
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $return   = array();

            // conditions
            //this will filter so that no php error will found
            if(empty($username) || empty($password)){ //check if the username and password is not empty
                $return = array(
                    '_isError' =>true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Empty username and password',
                );
            }else{
               //set payload
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $payload = array('username' => $username);
                /** 
                    * Call the auth  model
                    * then call the authenticate method
                    * @param array $payload.
                */
                $authenticate = $this->AuthModel->authenticate($payload);
                
           
                try{
                    if(count($authenticate) > 0){

                        if (password_verify($password, $authenticate[0]->password)) {
                            $data = array(
                                'user_id'        => $authenticate[0]->user_id,
                                'full_name'      => $authenticate[0]->last_name.', '.$authenticate[0]->first_name,
                                'first_name'     => $authenticate[0]->first_name,
                                'last_name'      => $authenticate[0]->last_name,
                                'username'         => $authenticate[0]->username,
                                'user_type'      => $authenticate[0]->user_type,
                                'created_at'   => $authenticate[0]->created_at,
                                'updated_at'   => $authenticate[0]->updated_at,
                            );
    
                            
    
                            $jwtpayload = array(
                                "iss" => "scanandgo",
                                "aud" => "scanandgo-api",
                                "iat" => time(),
                                "exp" => time() + (60 * 60),  // Token expires in 1 hour
                                "data" => $data
                            );
    
                            $jwt = generate_jwt($jwtpayload, $this->config->item('jwt_key'));
    
                            $return = array(
                                '_isError'      => false,
                                // 'code'       =>http_response_code(),
                                'reason'        =>'Login successfuly',
                                'data'  => $data,
                                'token' => $jwt,
                            );
    
                            if($authenticate[0]->user_type == "student"){
                                $student_id = $this->input->get("student_id");
                                $student_payload = array(
                                    'students.is_active' => 1,
                                    'students.user_id' => $authenticate[0]->user_id
                                );
                
                                $student_data = $this->StudentModel->get($student_payload);
    
                                $return['student'] = $student_data;
                            }
    
                            if($authenticate[0]->user_type == "teacher"){
                                $teacher_payload = array(
                                    'teachers.is_active' => 1,
                                    'teachers.user_id' => $authenticate[0]->user_id
                                );
                
                                $teacher_data = $this->TeacherModel->get($teacher_payload);
    
                                $return['teacher'] = $teacher_data;
                            }
                        } else {
                            $return = array(
                                '_isError' => true,
                                'data'=> $payload,
                                // 'code'     =>http_response_code(),
                                'reason'   =>'Invalid login credentials',
                            );
                        }

                       

                    }else{
                        $return = array(
                            '_isError' => true,
                            'data'=> $payload,
                            // 'code'     =>http_response_code(),
                            'reason'   =>'Invalid login credentials',
                        );
                    }
                }catch (Exception $e) {
                    //set the server error
                    $return = array(
                        '_isError' => true,
                        // 'code'     =>http_response_code(),
                        'reason'   => $e->getMessage(),
                    );
                }
            }
            $this->response->output($return,1); //return the json encoded data
        }
        public function register(){

        }
        public function addTeacher() {
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
            $password = $this->input->post('password');
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
            } else if (empty($password)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Password is required',
                );
            }  else {
                try {
                    // Hash the password
                    $teacher_id = $this->TeacherModel->generateTeacherID();
                    $user_id = $this->UserModel->generateUserID();
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

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
                        'username'              => $email,
                        'password'              => $hashedPassword,
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
        public function addStudent() {
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
            $password = $this->input->post('password');
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
            } else if (empty($password)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Password is required',
                );
            } else {
                try {
                    // Hash the password
                    $student_id = $this->StudentModel->generateStudentID();
                    $user_id = $this->UserModel->generateUserID();
                    // $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                    while ($this->StudentModel->isStudentIDExists($student_id)) {
                        // Regenerate student ID if it already exists
                        $student_id = $this->StudentModel->generateStudentID();
                    }

                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

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
                        'mobile'       => $mobile,
                        'created_at' => date("Y-m-d"),
                    );
        
                    // Call model function to add user
                    $response = $this->StudentModel->add($payload);
                    array_push($transQuery, $response);


                    // Add Student
                    
                    
                    
        
                     $payload_user = array(
                        'user_id'               => $user_id,
                        'user_type'             => 'student',
                        'username'              => $email,
                        'password'              => $hashedPassword,
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
        /* Logout user */
        public function logout(){
            $transQuery      = array();
            $headers = $this->input->request_headers();
            $token = $headers['Authorization'];
            if (strpos($token, 'Bearer ') === 0) {
                $token = substr($token, 7);
            }
            $return   = array();
           
            $payload = array(
                'token' => $token,
            );
            $response = $this->AuthModel->addBlackListToken($payload);
            array_push($transQuery, $response);
            $result = array_filter($transQuery);
            $res = $this->mysqlTQ($result);
           
            if($res){
                $return = array(
                    '_isError' => false,
                    'reason'   =>'Success',
                );
            }else{
                $return = array(
                    '_isError' => true,
                    'reason'   =>'Error',
                );
            }
            $this->response->output($return);
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
    }
    
?>