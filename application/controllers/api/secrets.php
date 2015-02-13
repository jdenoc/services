<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Secrets extends REST_Controller{

    // Table names
    CONST TABLE_SECRETS = "secrets";
    CONST TABLE_USERS = "users";
    CONST TABLE_API_KEYS = "api_keys";

    private $_db_config;
    private $_db_config_file = '/../../config/secrets.db_config.php';
    private $_model_dir = 'secrets/';
    private $_error_messages = array();

    public function __construct(){
        parent::__construct();
        $this->_error_messages = array(
            'parameter'=>"Parameter not set",
            'db_config'=>'DB config file not found',
            'oauth'=>"Invalid OAuth type submitted",
            'api_key'=>"Invalid API Key",
            'user'=>"User does not exist"
        );
        
        if(!file_exists(__DIR__.$this->_db_config_file)){
            error_log($this->_error_messages['db_config']);
            $this->send_response($this->_error_messages['db_config']);
        } else {
            $this->_db_config = require(__DIR__.$this->_db_config_file);
        }
    }

    public function delete_get() {
        $user_id = $this->get('user');
        $id = $this->get('id');
        $this->validate_access($user_id);
        $this->confirm_parameters_set($user_id, $id);

        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $this->Secrets->delete($user_id, $id);
        $this->send_response(1, __FUNCTION__);
    }

    public function secret_get() {
        $user_id = $this->get('user');
        $id = $this->get('id'); 
        $this->validate_access($user_id);
        $this->confirm_parameters_set($user_id, $id);

        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $secret = $this->Secrets->get($user_id, $id);

        $this->send_response($secret, __FUNCTION__);
    }

    public function password_get() {
        $user_id = $this->get('user');
        $id = $this->get('id');
        $this->validate_access($user_id);
        $this->confirm_parameters_set($user_id, $id);

        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $password_data = $this->Secrets->get_password($user_id, $id);
        $this->send_response($password_data, __FUNCTION__);
    }

    public function count_get() {
        $user_id = $this->get('id');
        $this->validate_access($user_id);
        $this->confirm_parameters_set($user_id);

        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $total_secrets = $this->Secrets->count($user_id);
        $this->send_response($total_secrets, __FUNCTION__);
    }

    public function list_get() {
        $user_id = $this->get('id');
        $limit = $this->get('limit');
        $start = $this->get('start');
        $this->validate_access($user_id);
        $this->confirm_parameters_set($user_id, $limit, $start);

        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $records = $this->Secrets->get_names($user_id, $start, $limit);

        $this->send_response($records, __FUNCTION__);
    }

    public function save_post() {
        $user_id = $this->post('user');
        $this->validate_access($user_id);
        $this->confirm_parameters_set($user_id, $this->post('data'));

        $secret_data = json_decode(base64_decode($this->post('data')), true);
        if(empty($secret_data)){
            $this->send_response(0, __FUNCTION__);
        }
        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $secret_id = $this->Secrets->save($user_id, $secret_data);
        $this->send_response($secret_id, __FUNCTION__);
    }
    
    public function user_auth_post(){
        $this->load->model($this->_model_dir.'api_key_model', 'API', $this->_db_config);
        $this->load->model($this->_model_dir.'user_model', 'User', $this->_db_config);
        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        
        $user_email = $this->post('email');
        $oauth_type = $this->post('auth_type');
        $udid = $this->post('udid');
        $token = $this->API->get_header_key();
        $this->confirm_parameters_set($user_email, $oauth_type, $udid, $token);
        
        switch($oauth_type){
            case 'google':
                // TODO - figure out how to authenticate the token that is passed
                // "https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=".$token;
                // curl -H "Authorization: Bearer $token" https://www.googleapis.com/plus/v1/people/me
                break;
            default:
                $this->send_response($this->_error_messages['oauth']);
        }
        
        $user_id = $this->User->confirm_email($user_email);
        $this->API->set_return_api_key_header($this->API->get_user_key($user_id, $udid));
        $user_data = $this->User->get($user_id);
        $user_data['secret_count'] = $this->Secrets->count($user_id);
        $this->send_response($user_data, __FUNCTION__);
    }

    /**
     * @param int $user_id
     */
    private function validate_access($user_id){
        $this->load->model($this->_model_dir.'api_key_model', 'API', $this->_db_config);
        $valid_key = $this->API->validate($user_id);
        if(!$valid_key){
            $this->send_response($this->_error_messages['api_key']);
        }

        $this->load->model($this->_model_dir.'user_model', 'User', $this->_db_config);
        $valid_user = $this->User->validate($user_id);
        if(!$valid_user){
            $this->send_response($this->_error_messages['user']);
        }

        $this->API->set_return_api_key_header($this->API->refresh_key($this->API->get_header_key()));
    }

    /**
     * mixed $var - multiple variables.
     */
    private function confirm_parameters_set(){
        $all_set = true;
        foreach(func_get_args() as $var){
            if(empty($var) && $var != 0){
                $all_set = false;
                break;
            }
        }
        
        if(!$all_set){
            $this->send_response($this->_error_messages['parameter']);
        }
    }

    /**
     * @param mixed $data 
     * @param bool $function
     */
    private function send_response($data, $function=false){
        if($function){
            switch($function){
                case 'list_get':
                case 'secret_get':
                case 'user_auth_post':
                case 'password_get':
                    $result = base64_encode(json_encode($data));
                    break;
                default:
                    $result = $data;
            }
            $code = 200;
            $error_msg = '';
            
        } else {
            $code = 400;
            $error_msg = $data;
            $result = '';
        }
        $this->response(array('error'=>$error_msg, 'result'=>$result), $code);
    }

}