<?php
   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

    class Utils extends MY_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->library('Response',NULL,'response');
            $this->load->model('TransactionModel');
        }
        
        public function getTransactionByYear(){

            $date = $this->input->get('year');

            if(empty($date)){
                $date = date("Y");
            }

            $data = $this->TransactionModel->getTransactionByYear($date);
            $areaChartData = array();
            foreach ($data as $key => $value) {
                array_push($areaChartData,$value->total);
            }
            $return = array(
                '_isError'      => false,
                // 'code'       =>http_response_code(),
                'reason'        =>'Success',
                'data'          => $data,
                'areaChartData' => $areaChartData,
            );
            $this->response->output($return); //return the json encoded data
        }

        public function getExpensesType(){

        }
    }
?>