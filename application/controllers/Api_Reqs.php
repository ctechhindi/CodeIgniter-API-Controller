<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/API_Controller.php';

class Api_Test extends API_Controller
{
    public function __construct() {
        parent::__construct();
    }

    public function Api_SETREQS(){
        $isTtpoes = $this->input->post('Hello');
        $variable = [
            "H",
            "E",
            "L",
            "L",
            "O"
        ];
        return $variable;
    }
}