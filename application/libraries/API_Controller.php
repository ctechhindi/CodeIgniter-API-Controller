<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter API Controller
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @author          Jeevan Lal
 * @license         MIT
 * @version         1.1.0
 */
class API_Controller extends CI_Controller
{
    /**
     * List of allowed HTTP methods
     *
     * @var array
     */
    protected $allowed_http_methods = ['get', 'delete', 'post', 'put', 'options', 'patch', 'head'];

    /**
     * The request method is not supported by the following resource
     * @link http://www.restapitutorial.com/httpstatuscodes.html
     */
    const HTTP_METHOD_NOT_ALLOWED = 405;

    /**
     * The request cannot be fulfilled due to multiple errors
     */
    const HTTP_BAD_REQUEST = 400;

    /**
     * Request Timeout
     */
    const HTTP_REQUEST_TIMEOUT = 408;

    /**
     * The requested resource could not be found
     */
    const HTTP_NOT_FOUND = 404;

    /**
     * The user is unauthorized to access the requested resource
     */
    const HTTP_UNAUTHORIZED = 401;

    /**
     * The request has succeeded
     */
    const HTTP_OK = 200;

    /**
     * HTTP status codes and their respective description
     */
    const HEADER_STATUS_STRINGS = [
        '405' => 'HTTP/1.1 405 Method Not Allowed',
        '400' => 'BAD REQUEST',
        '408' => 'Request Timeout',
        '404' => 'NOT FOUND',
        '401' => 'UNAUTHORIZED',
        '200' => 'OK',
    ];

    /**
     * API LIMIT TABLE NAME
     */
    protected $API_LIMIT_TABLE_NAME;

    /**
     * API KEYS TABLE NAME
     */
    protected $API_KEYS_TABLE_NAME;

    /**
     * RETURN DATA
     */
    protected $return_other_data = [];
    
    public function __construct() {
        parent::__construct();
        $this->CI =& get_instance();

        // load api config file
        $this->CI->load->config('api');

        // set timezone for api limit
        date_default_timezone_set($this->CI->config->item('api_timezone'));

        // Load Config Items Values
        $this->API_LIMIT_TABLE_NAME = $this->CI->config->item('api_limit_table_name');
        $this->API_KEYS_TABLE_NAME  = $this->CI->config->item('api_keys_table_name');
    }


    public function _APIConfig($config = [])
    {
        // return other data
        if(isset($config['data']))
            $this->return_other_data = $config['data'];

        // by default method `GET`
        if ((isset($config) AND empty($config)) OR empty($config['methods'])) {
            $this->_allow_methods(['GET']);
        } else {
            $this->_allow_methods($config['methods']);
        }

        // api limit function `_limit_method()`
        if(isset($config['limit']))
            $this->_limit_method($config['limit']);

        // api key function `_api_key()`
        if(isset($config['key']))
            $this->_api_key($config['key']);

        // IF Require Authentication
        if(isset($config['requireAuthorization']) AND $config['requireAuthorization'] === true) {
            $token_data = $this->_isAuthorized();
            // remove api time in user token data
            unset($token_data->API_TIME);
            // return token decode data
            return [ 'token_data' => (array) $token_data ];
        }
    }


    /**
     * Allow Methods
     * -------------------------------------
     * @param: {array} request methods
     */
    public function _allow_methods(array $methods)
    {
        $REQUEST_METHOD = $this->CI->input->server('REQUEST_METHOD', TRUE);

        // check request method in `$allowed_http_methods` array()
        if (in_array(strtolower($REQUEST_METHOD), $this->allowed_http_methods))
        {
            // check request method in user define `$methods` array()
            if (in_array(strtolower($REQUEST_METHOD), $methods) OR in_array(strtoupper($REQUEST_METHOD), $methods))
            {
                // allow request method
                return true;

            } else
            {
                // not allow request method
                $this->_response(['status' => FALSE, 'error' => 'Unknown method'], self::HTTP_METHOD_NOT_ALLOWED);
            }
        } else {
            $this->_response(['status' => FALSE, 'error' => 'Unknown method'], self::HTTP_METHOD_NOT_ALLOWED);
        }
    }


    /**
     * Limit Method
     * ------------------------
     * @param: {int} number
     * @param: {type} ip
     * 
     * Total Number Limit without Time
     * 
     * @param: {minute} time/everyday

     * Total Number Limit with Last {3,4,5...} minute
     * --------------------------------------------------------
     */
    public function _limit_method(array $data)
    {
        // check limit number
        if (!isset($data[0])) {
            $this->_response(['status' => FALSE, 'error' => 'Limit Number Required'], self::HTTP_BAD_REQUEST);
        }

        // check limit type
        if (!isset($data[1])) {
            $this->_response(['status' => FALSE, 'error' => 'Limit Type Required'], self::HTTP_BAD_REQUEST);
        }

        // check limit database table exists
        if (!$this->db->table_exists($this->API_LIMIT_TABLE_NAME)) {
            $this->_response(['status' => FALSE, 'error' => 'Create Limit Database Table'], self::HTTP_BAD_REQUEST);
        }

        $limit_num = $data[0]; // limit number
        $limit_type = $data[1]; // limit type

        $limit_time = isset($data[2])? $data[2]:''; // time minute

        if ($limit_type == 'ip')
        {
            $where_data_ip = [
                'uri' => $this->CI->uri->uri_string(),
                'class' => $this->CI->router->fetch_class(),
                'method' => $this->CI->router->fetch_method(),
                'ip_address' => $this->CI->input->ip_address(),
            ];

            $limit_query = $this->CI->db->get_where($this->API_LIMIT_TABLE_NAME, $where_data_ip);
            if ($this->db->affected_rows() >= $limit_num)
            {
                // time limit not empty
                if (isset($limit_time) AND !empty($limit_time))
                {
                    // if time limit `numeric` numbers
                    if (is_numeric($limit_time))
                    {
                        $limit_timestamp = time() - ($limit_time*60);
                        // echo Date('d/m/Y h:i A', $times);
    
                        $where_data_ip_with_time = [
                            'uri' => $this->CI->uri->uri_string(),
                            'class' => $this->CI->router->fetch_class(),
                            'method' => $this->CI->router->fetch_method(),
                            'ip_address' => $this->CI->input->ip_address(),
                            'time >=' => $limit_timestamp
                        ];
    
                        $time_limit_query = $this->CI->db->get_where($this->API_LIMIT_TABLE_NAME, $where_data_ip_with_time);
                        // echo $this->CI->db->last_query();
                        if ($this->db->affected_rows() >= $limit_num)
                        {
                            $this->_response(['status' => FALSE, 'error' => 'This IP Address has reached the time limit for this method'], self::HTTP_REQUEST_TIMEOUT);
                        } else
                        {
                            // insert limit data
                            $this->limit_data_insert();
                        }
                    }

                    // if time limit equal to `everyday`
                    if ($limit_time == 'everyday')
                    {
                        $this->CI->load->helper('date');

                        $bad_date = mdate('%d-%m-%Y', time());

                        $start_date = nice_date($bad_date, 'd/m/Y h:i A'); // {DATE} 12:00 AM
                        $end_date = nice_date($bad_date .' 12:00 PM', 'd/m/Y h:i A'); // {DATE} 12:00 PM
                        
                        $start_date_timestamp = strtotime($start_date);
                        $end_date_timestamp = strtotime($end_date);

                        $where_data_ip_with_time = [
                            'uri' => $this->CI->uri->uri_string(),
                            'class' => $this->CI->router->fetch_class(),
                            'method' => $this->CI->router->fetch_method(),
                            'ip_address' => $this->CI->input->ip_address(),
                            'time >=' => $start_date_timestamp,
                            'time <=' => $end_date_timestamp,
                        ];
    
                        $time_limit_query = $this->CI->db->get_where($this->API_LIMIT_TABLE_NAME, $where_data_ip_with_time);
                        // echo $this->CI->db->last_query();exit;
                        if ($this->db->affected_rows() >= $limit_num)
                        {
                            $this->_response(['status' => FALSE, 'error' => 'This IP Address has reached the time limit for this method'], self::HTTP_REQUEST_TIMEOUT);
                        } else
                        {
                            // insert limit data
                            $this->limit_data_insert();
                        }
                    }

                } else {
                    $this->_response(['status' => FALSE, 'error' => 'This IP Address has reached limit for this method'], self::HTTP_REQUEST_TIMEOUT);
                }

            } else
            {
                // insert limit data
                $this->limit_data_insert();
            }
        } else {
            $this->_response(['status' => FALSE, 'error' => 'Limit Type Invalid'], self::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Limit Data Insert
     */
    private function limit_data_insert()
    {
        $this->CI->load->helper('api_helper');

        $insert_data = [
            'uri' => $this->CI->uri->uri_string(),
            'class' => $this->CI->router->fetch_class(),
            'method' => $this->CI->router->fetch_method(),
            'ip_address' => $this->CI->input->ip_address(),
            'time' => time(),
        ];

        insert($this->API_LIMIT_TABLE_NAME, $insert_data);
    }

    /**
     * API key
     */
    private function _api_key(array $key)
    {
        if (!isset($key[0])) {
            $api_key_type = 'header';
        } else {
            $api_key_type = $key[0];
        }

        if (!isset($key[1])) {
            $api_key = 'table';
        } else {
            $api_key = $key[1];
        }

        // api key type `Header`
        if (strtolower($api_key_type) == 'header')
        {
            $api_key_header_name = $this->config->item('api_key_header_name');

            // check api key header name in request headers
            $is_header = $this->exists_header($api_key_header_name); // return status and header value
            if (isset($is_header['status']) === TRUE)
            {
                $HEADER_VALUE = trim($is_header['value']);

                // if api key equal to `table`
                if ($api_key != "table")
                {
                    if ($HEADER_VALUE != $api_key) {
                        $this->_response(['status' => FALSE, 'error' => 'API Key invalid'], self::HTTP_UNAUTHORIZED);
                    }

                } else
                {
                    // check api key database table exists
                    if (!$this->db->table_exists($this->API_KEYS_TABLE_NAME)) {
                        $this->_response(['status' => FALSE, 'error' => 'Create API Key Database Table'], self::HTTP_BAD_REQUEST);
                    }

                    $where_key_data = [
                        'controller' => $this->CI->router->fetch_class(),
                        'api_key' => $HEADER_VALUE,
                    ];

                    $limit_query = $this->CI->db->get_where($this->API_KEYS_TABLE_NAME, $where_key_data);
                    if (!$this->db->affected_rows() > 0)
                    {
                        $this->_response(['status' => FALSE, 'error' => 'API Key invalid'], self::HTTP_NOT_FOUND);
                    } 
                }

            } else
            {
                $this->_response(['status' => FALSE, 'error' => 'API Key Header Required'], self::HTTP_NOT_FOUND);
            }
        } else if (strtolower($api_key_type) == 'get') // // api key type `get`
        {
            // return status and header value `Content-Type`
            $is_header = $this->exists_header('Content-Type');
            if (isset($is_header['status']) === TRUE) {
                if ($is_header['value'] === "application/json")
                {
                    $stream_clean = $this->CI->security->xss_clean($this->CI->input->raw_input_stream);
                    $_GET = json_decode($stream_clean, true);
                }
            }

            $api_key_get_name = $this->config->item('api_key_get_name');
            
            $get_param_value = $this->CI->input->get($api_key_get_name, TRUE);
            if (!empty($get_param_value) AND is_string($get_param_value))
            {
                // if api key equal to `table`
                if ($api_key != "table")
                {
                    if ($get_param_value != $api_key) {
                        $this->_response(['status' => FALSE, 'error' => 'API Key invalid'], self::HTTP_UNAUTHORIZED);
                    }

                } else
                {
                    // check api key database table exists
                    if (!$this->db->table_exists($this->API_KEYS_TABLE_NAME)) {
                        $this->_response(['status' => FALSE, 'error' => 'Create API Key Database Table'], self::HTTP_BAD_REQUEST);
                    }

                    $where_key_data = [
                        'controller' => $this->CI->router->fetch_class(),
                        'api_key' => $get_param_value,
                    ];

                    $limit_query = $this->CI->db->get_where($this->API_KEYS_TABLE_NAME, $where_key_data);
                    if (!$this->db->affected_rows() > 0)
                    {
                        $this->_response(['status' => FALSE, 'error' => 'API Key invalid'], self::HTTP_NOT_FOUND);
                    } 
                }
            } else
            {
                $this->_response(['status' => FALSE, 'error' => 'API Key GET Parameter Required'], self::HTTP_NOT_FOUND);
            }
        } else if (strtolower($api_key_type) == 'post') // // api key type `post`
        {
            // return status and header value `Content-Type`
            $is_header = $this->exists_header('Content-Type');
            if (isset($is_header['status']) === TRUE) {
                if ($is_header['value'] === "application/json")
                {
                    $stream_clean = $this->CI->security->xss_clean($this->CI->input->raw_input_stream);
                    $_POST = json_decode($stream_clean, true);
                }
            }

            $api_key_post_name = $this->config->item('api_key_post_name');
            
            $get_param_value = $this->CI->input->post($api_key_post_name, TRUE);
            if (!empty($get_param_value) AND is_string($get_param_value))
            {
                // if api key equal to `table`
                if ($api_key != "table")
                {
                    if ($get_param_value != $api_key) {
                        $this->_response(['status' => FALSE, 'error' => 'API Key invalid'], self::HTTP_UNAUTHORIZED);
                    }

                } else
                {
                    // check api key database table exists
                    if (!$this->db->table_exists($this->API_KEYS_TABLE_NAME)) {
                        $this->_response(['status' => FALSE, 'error' => 'Create API Key Database Table'], self::HTTP_BAD_REQUEST);
                    }

                    $where_key_data = [
                        'controller' => $this->CI->router->fetch_class(),
                        'api_key' => $get_param_value,
                    ];

                    $limit_query = $this->CI->db->get_where($this->API_KEYS_TABLE_NAME, $where_key_data);
                    if (!$this->db->affected_rows() > 0)
                    {
                        $this->_response(['status' => FALSE, 'error' => 'API Key invalid'], self::HTTP_NOT_FOUND);
                    } 
                }
            } else
            {
                $this->_response(['status' => FALSE, 'error' => 'API Key POST Parameter Required'], self::HTTP_NOT_FOUND);
            }

        } else {
            $this->_response(['status' => FALSE, 'error' => 'API Key Parameter Required'], self::HTTP_NOT_FOUND);
        }
    }

    /**
     * Is Authorized
     */
    private function _isAuthorized()
    {
        // Load Authorization Library
        $this->CI->load->library('authorization_token');

        // check token is valid
        $result = $this->authorization_token->validateToken();

        if (isset($result['status']) AND $result['status'] === true)
        {
            return $result['data'];

        } else {

            $this->_response(['status' => FALSE, 'error' => $result['message']], self::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Check Request Header Exists
     * @return ['status' => true, 'value' => value ]
     */
    private function exists_header($header_name)
    {
        $headers = apache_request_headers();
        foreach ($headers as $header => $value) {
            if($header === $header_name) {
                return ['status' => true, 'value' => $value ];
            }
        }
    }

    /**
     * Private Response Function
     */
    private function _response($data = NULL, $http_code = NULL)
    {
        ob_start();
        header('content-type:application/json; charset=UTF-8');
        header(self::HEADER_STATUS_STRINGS[$http_code], true, $http_code);

        if (!is_array($this->return_other_data)) {
            print_r(json_encode(['status' => false, 'error' => 'Invalid data format']));
        } else {
            print_r(json_encode(array_merge($data, $this->return_other_data)));
        }
        ob_end_flush();
        die();
    }

    /**
     * Public Response Function
     */
    public function api_return($data = NULL, $http_code = NULL)
    {
        ob_start();
        header('content-type:application/json; charset=UTF-8');
        header(self::HEADER_STATUS_STRINGS[$http_code], true, $http_code);
        print_r(json_encode($data));
        ob_end_flush();
    }
}