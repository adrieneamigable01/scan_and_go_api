<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require './vendor/autoload.php';
use \Firebase\JWT\JWT;

class MY_Controller extends CI_Controller {

    protected $user_data;

    public function __construct() {
        parent::__construct();
        $this->load->library('Response',NULL,'response');
        $this->load->helper('jwt');
        $this->key = $this->config->item('jwt_key'); // Ensure this key is in your config
        // Check token
        $this->validate_token();
    }

    private function validate_token() {
        $headers = $this->input->request_headers();
        $token =  isset($headers['Authorization']) ? $headers['Authorization'] : null;
        // $token =  "";

        // if ($this->input->server('REQUEST_METHOD') == 'POST'){
        //     $token =  $this->input->post("token");

        // }else if ($this->input->server('REQUEST_METHOD') == 'GET'){
        //     $token =  $this->input->get("token");

        // }
        
        // Remove 'Bearer ' prefix if present
        if (strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }
      
        if ($token) {

            if($this->getBlackListToken($token) > 0){
                $res = array('_isError' => true,'message' => 'Token is invalid or expired.');
                $this->response->output($res,0,401);exit;
    
            }else{
                $decoded = decode_jwt($token, $this->key);
                if ($decoded) {
                    return true;
                } else {
                    $res = array('_isError' => true,'message' => 'Invalid token.');
                    $this->response->output($res,0,401);exit;
                    exit;
                }
            }

            
        } else {
            $res = array('_isError' => true,'message' => 'No token provided.');
            $this->response->output($res,0,401);exit;
            exit;

        }
    }

    private function getBlackListToken($token){
        $sql = "SELECT blacklist_token.token FROM blacklist_token
                WHERE blacklist_token.token = '$token'";
        return $this->db->query($sql)->num_rows();
    }
}