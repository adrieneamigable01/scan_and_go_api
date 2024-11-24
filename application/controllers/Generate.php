<?php
    ini_set('memory_limit', '-1');
    date_default_timezone_set('Asia/Manila');
    define("DOMPDF_ENABLE_PHP", true);

    use Dompdf\Dompdf;
    use Dompdf\Options;
   /**
     * @author  Adriene Care Llanos Amigable <adrienecarreamigable01@gmail.com>
     * @version 0.1.0
    */ 

    class Generate extends CI_Controller{
        /**
            * Class constructor.
            *
        */
        public function __construct() {
			parent::__construct();
            date_default_timezone_set('Asia/Manila');
            $this->load->helper('jwt');
            $this->load->library('Pdf', NULL, 'pdf');
            $this->load->library('Response',NULL,'response');
            $this->load->model('StudentModel');
            include_once(APPPATH . 'libraries/phpqrcode/qrlib.php');
        }
        
        public function genereate_student_details(){

            ini_set('max_execution_time', 300); //300 seconds = 5 minutes
            $student_id = $this->input->get("student_id");
            if (!empty($student_id)) {
				$response = [];
				$d = [];
				ini_set("max_execution_time", 300); //300 seconds = 5 minutes
				$paper = "legal";
				$orientation = "letter";
				$dompdf = new Dompdf();
				$dompdf->setPaper($paper, $orientation);
				$dompdf->set_option("isPhpEnabled", true);

               
                $payload = array(
                    'students.is_active' => 1
                );

                if(!empty($student_id)){
                    $payload['student_id'] = $student_id;
                }

                $request = $this->StudentModel->get($payload);
                if(sizeof($request)  > 0){
				
                    $name = $request[0]->full_name;
                    $name = "Student Info";
                    $data["title"] = $name;
                    $data["student"] = $request[0];
                    $qrtext = array(
                        'app' => 'scan_and_go',
                        'student_id' => $student_id,
                    );
                    $qr = $this->generateBase64($qrtext);
                    $data['qr'] = $qr;
                    $this->pdf->load_view2_portrait(
                        $name,
                        "pdf/student_info",
                        $data
                    );
                }else{
                    print_r("No Student On This Student ID ".$student_id);
                }
			} else {
				print_r("No Student Selected");
			}
        }

        public function generateBase64($text) {
            // Start output buffering
            ob_start();
    
            // Generate QR code in the form of PNG
            QRcode::png($text, null, QR_ECLEVEL_L, 10);
    
            // Get the image content and encode it as base64
            $imageData = ob_get_contents();
            ob_end_clean();
    
            // Return the base64 encoded string with the proper data URI scheme
            return 'data:image/png;base64,' . base64_encode($imageData);
        }
    }
?>