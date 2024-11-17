<?php
   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

    class User extends MY_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
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
        public function getUsers(){
            /**
             * @var string post data $key
             * @var string session data $accessKey
            */

            
            try{
                $storeid  = $this->input->get("storeid");
                
                $token      = $this->input->get('token');
                $decoded    = decode_jwt($token, $this->config->item('jwt_key'));
                $reqUserId  = $userid = $decoded->data->userid;

                //set payload
                $payload = array(
                    'storeid'   => $storeid,
                    'notuserId' => array($reqUserId),
                );
                /** 
                    * Call the supploer model
                    * then call the getSuppliers method
                    * @param array $payload.
                */
                $request = $this->UserModel->getUser($payload);
                $return = array(
                    '_isError'      => false,
                    // 'code'       =>http_response_code(),
                    'reason'        =>'Success',
                    'data'          => $request,
                );
            }catch (Exception $e) {
                //set server error
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   => $e->getMessage(),
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
        public function createUser(){   

           
            $transQuery         = array();
            $firstName          = $this->input->post('firstName');
            $middleName         = $this->input->post('middleName');
            $lastName           = $this->input->post('lastName');
            $birthdate          = $this->input->post('birthdate');
            $email              = $this->input->post('email');
            $mobile           = $this->input->post('mobile');
            $userType           = $this->input->post('userType');
            $storeid            = $this->input->post('storeid');
            $role               = $this->input->post('role');
            $dateCreated        = date("Y-m-d");

            
            $username           = $this->generateUsername($firstName.' '.$lastName);
            $hashpassword       = $this->generateHashPassword($lastName, $this->config->item('password_key'));

            if(empty($role)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Firstname is required',
                );
            }
            else if(empty($lastName)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Lastname is required',
                );
            }else if(empty($birthdate)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Birthdate is required',
                );
            }else if(empty($userType)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Usertype is required',
                );
            }else if(empty($storeid)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Store is required',
                );
            }else if(empty($role)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Role is required',
                );
            }
            else{
                try{
                   
                    // $headers = $this->input->request_headers();
                    // $token = $headers['Authorization'];
                    // if (strpos($token, 'Bearer ') === 0) {
                    //     $token = substr($token, 7);
                    // }
                    // $decoded = decode_jwt($token, $this->config->item('jwt_key'));
                    
                    //set ayload
                    $payload = array(
                        'users.firstName'       => $firstName,
                        'users.middleName'      => $middleName,
                        'users.lastName'        => $lastName,
                        'users.userName'        => $username,
                        'users.password'        => $hashpassword,
                        'users.birthdate'       => $birthdate,
                        'users.email'           => $email,
                        'users.mobile'          => $mobile,
                        'users.userType'        => $userType,
                        'users.dateCreated'     => $dateCreated,
                        'users.storeid'         => $storeid,
                        'users.role'            => $role,
                    );
                    $addUserResponse = $this->UserModel->addUser($payload);
                    array_push($transQuery, $addUserResponse);
                    $result = array_filter($transQuery);
			        $res = $this->mysqlTQ($result);


                    if($res){
                        $return = array(
                            '_isError'      => false,
                            // 'code'       =>http_response_code(),
                            'reason'        =>'Successfuly added new user',
                            'data'          => $payload
                        );
                    }else{
                        $return = array(
                            '_isError' => true,
                            // 'code'     =>http_response_code(),
                            'reason'   => 'Error adding user',
                        );
                    }
                    
                }catch (Exception $e) {
                    //return the server error
                    $return = array(
                        '_isError' => true,
                        // 'code'     =>http_response_code(),
                        'reason'   => $e->getMessage(),
                    );
                }
                
            }
            $this->response->output($return); //echo the json encoded data
        }
        public function updateUser(){   

           
            $transQuery         = array();
            $userid             = $this->input->post('userId');
            $firstName          = $this->input->post('firstName');
            $middleName         = $this->input->post('middleName');
            $lastName           = $this->input->post('lastName');
            $birthdate          = $this->input->post('birthdate');
            $email              = $this->input->post('email');
            $mobile             = $this->input->post('mobile');
            $userType           = $this->input->post('userType');
            $storeid            = $this->input->post('storeid');
            $role               = $this->input->post('role');
            $dateCreated        = date("Y-m-d");

            
            $username           = $this->generateUsername($firstName.' '.$lastName);
            $hashpassword       = $this->generateHashPassword($lastName, $this->config->item('password_key'));

            if(empty($userid)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'User id is required',
                );
            }
            else if(empty($firstName)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Firstname is required',
                );
            }
            else if(empty($lastName)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Lastname is required',
                );
            }else if(empty($birthdate)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Birthdate is required',
                );
            }else if(empty($userType)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Usertype is required',
                );
            }else if(empty($storeid)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Store is required',
                );
            }else if(empty($role)){ //check the data is not empty
                $return = array(
                    '_isError' => true,
                    // 'code'     =>http_response_code(),
                    'reason'   =>'Role is required',
                );
            }
            else{
                try{
                   
                    $headers = $this->input->request_headers();
                    $token = $headers['Authorization'];
                    if (strpos($token, 'Bearer ') === 0) {
                        $token = substr($token, 7);
                    }
                    $decoded = decode_jwt($token, $this->config->item('jwt_key'));
                    
                    //set ayload
                    $payload = array(
                        'users.firstName'       => $firstName,
                        'users.middleName'      => $middleName,
                        'users.lastName'        => $lastName,
                        'users.userName'        => $username,
                        'users.password'        => $hashpassword,
                        'users.birthdate'       => $birthdate,
                        'users.email'           => $email,
                        'users.mobile'          => $mobile,
                        'users.userType'        => $userType,
                        'users.dateCreated'     => $dateCreated,
                        'users.storeid'         => $storeid,
                        'users.role'            => $role,
                    );
                    $where = array(
                        'userId' => $userid,
                    );
                    $addUserResponse = $this->UserModel->updateUser($payload,$where);
                    array_push($transQuery, $addUserResponse);
                    $result = array_filter($transQuery);
			        $res = $this->mysqlTQ($result);


                    if($res){
                        $return = array(
                            '_isError'      => false,
                            // 'code'       =>http_response_code(),
                            'reason'        =>'Successfuly update user',
                            'data'          => $payload
                        );
                    }else{
                        $return = array(
                            '_isError' => true,
                            // 'code'     =>http_response_code(),
                            'reason'   => 'Error updating user',
                        );
                    }
                    
                }catch (Exception $e) {
                    //return the server error
                    $return = array(
                        '_isError' => true,
                        // 'code'     =>http_response_code(),
                        'reason'   => $e->getMessage(),
                    );
                }
                
            }
            $this->response->output($return); //echo the json encoded data
        }
    }
?>