<?php
   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

    class College extends MY_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->model('CollegeModel');
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
                $payload = array(
                    'college.is_active' => 1
                );
                $request = $this->CollegeModel->get($payload);
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
        public function add() {
            $transQuery = array();
        
            // Retrieve form data using the 'name' attributes from the HTML form
            $college = $this->input->post('college');
            $short_name = $this->input->post('short_name');
            $dateCreated = date("Y-m-d");
        
            // Validation checks
            if (empty($college)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'College name is required',
                );
            } else if (empty($short_name)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Short name is required',
                );
            } else {
                try {
        
                    // Payload array for new user data
                    $payload = array(
                        'college'   => $college,
                        'short_name'   => $short_name,
                        'created_at' => date("Y-m-d"),
                    );
        
                    // Call model function to add user
                    $response = $this->CollegeModel->add($payload);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully added new college',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error adding college',
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
            $college_id = $this->input->post('college_id');
            $college = $this->input->post('college');
            $short_name = $this->input->post('short_name');
            $dateCreated = date("Y-m-d");
        
            // Validation checks
            if (empty($college_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Id is required',
                );
            } else if (empty($college)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'College name is required',
                );
            } else if (empty($short_name)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'Short name is required',
                );
            } else {
                try {
        
                    // Payload array for new user data
                    $payload = array(
                        'college'   => $college,
                        'short_name'   => $short_name,
                        'updated_at' => date("Y-m-d"),
                    );
                    $where = array(
                        'college_id' => $college_id 
                    );
                    // Call model function to add user
                    $response = $this->CollegeModel->update($payload,$where);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully updated new college',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error updating college',
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
            $college_id = $this->input->post('college_id');
        
            // Validation checks
            if (empty($college_id)) {
                $return = array(
                    '_isError' => true,
                    'reason' => 'id is required',
                );
            }else {
                try {
        
                    // Payload array for new user data
                    $payload = array(
                        'is_active'   => 0,
                        'deleted_at'  => date("Y-m-d"),
                    );
                    $where = array(
                        'college_id' => $college_id
                    );
                    // Call model function to add user
                    $response = $this->CollegeModel->update($payload,$where);
                    array_push($transQuery, $response);
                    $result = array_filter($transQuery);
                    $res = $this->mysqlTQ($result);
        
                    // Success response
                    if ($res) {
                        $return = array(
                            '_isError' => false,
                            'reason' => 'Successfully void College',
                            'data' => $payload
                        );
                    } else {
                        $return = array(
                            '_isError' => true,
                            'reason' => 'Error void College',
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