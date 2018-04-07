<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/API_Controller.php';

class Api_Test extends API_Controller
{
    public function __construct() {
        parent::__construct();
    }

    public function get_users()
    {
        header("Access-Control-Allow-Origin: *");

        $this->_apiConfig([
            /**
             * By Default Method `GET`
             */
            'methods' => ['GET', 'POST', 'OPTIONS'],
            /**
             * number limit, type limit, time limit (last minute)
             */
            // 'limit' => [5, 'ip', 'everyday'], 

            /**
             * type :: ['header', 'get', 'post']
             * key  :: ['table : Check Key in Database', 'key']
             */
            'key' => ['GET'], // type, {key}|table (by default)
        ]);
        
        
        // echo "HELLO WORLD";
    }
}