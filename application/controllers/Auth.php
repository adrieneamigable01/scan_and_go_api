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
                $payload = array('username' => $username, 'password' => $password);
                /** 
                    * Call the auth  model
                    * then call the authenticate method
                    * @param array $payload.
                */
                $authenticate = $this->AuthModel->authenticate($payload);
                
               
                try{
                    if(count($authenticate) > 0){
     

                        $data = array(
                            'user_id'        => $authenticate[0]->user_id,
                            'fullN_name'      => $authenticate[0]->last_name.', '.$authenticate[0]->first_name,
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
                    }else{
                        $return = array(
                            '_isError' => true,
                            // 'code'     =>http_response_code(),
                            'reason'   =>'User not found',
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
        /* Logout user */
        public function logout(){
            $transQuery      = array();
            // $headers = $this->input->request_headers();
            // $token = $headers['Authorization'];
            // if (strpos($token, 'Bearer ') === 0) {
            //     $token = substr($token, 7);
            // }
            $return   = array();
            $token = $this->input->post("token");
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