<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Secrets
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
 */

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Secrets extends REST_Controller{

    // Table names
    CONST TABLE_SECRETS = "secrets";

    private $_db_config;
    private $_db_config_file = '/../../config/secrets.db_config.php';
    private $_origin;
    private $_model_dir = 'secrets/';

    public function __construct(){
        parent::__construct();
        if(!file_exists(__DIR__.$this->_db_config_file)){
            $this->send_response('DB config file not found');
        } else {
            $this->_db_config = require(__DIR__.$this->_db_config_file);
        }
    }

    public function delete_get() {
        $user_id = $this->get('user');
        $this->validate_access($user_id);

        $id = $this->get('id');
        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $this->Secrets->delete($user_id, $id);
        $this->send_response(1, __FUNCTION__);
    }

    public function display_get() {
        $user_id = $this->get('user');
        $this->validate_access($user_id);

        $id = $this->get('id');
        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $secret = $this->Secrets->get($user_id, $id);

        $this->send_response($secret, __FUNCTION__);
    }

    public function password_get() {
        $user_id = $this->get('user');
        $this->validate_access($user_id);

        $id = $this->get('id');
        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $password_data = $this->Secrets->get_password($user_id, $id);
        $this->send_response($password_data, __FUNCTION__);
    }

    public function count_get() {
        $user_id = $this->get('id');
        $this->validate_access($user_id);

        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $total_secrets = $this->Secrets->count($user_id);
        $this->send_response($total_secrets, __FUNCTION__);
    }

    public function list_get() {
        $user_id = $this->get('id');
        $this->validate_access($user_id);

        $limit = $this->get('limit');
        $start = $this->get('start');
        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $records = $this->Secrets->get_names($user_id, $start, $limit);

        $this->send_response($records, __FUNCTION__);
    }

    public function save_post() {
        $user_id = $this->post('user');
        $this->validate_access($user_id);

        $secret_data = json_decode(base64_decode($this->post('data')), true);
        if(empty($secret_data)){
            $this->send_response(0, __FUNCTION__);
        }
        $this->load->model($this->_model_dir.'secrets_model', 'Secrets', $this->_db_config);
        $this->Secrets->save($user_id, $secret_data);
        $this->send_response(1, __FUNCTION__);
    }

    public function user_key_get(){
        $user_id = $this->get('id');
        $this->validate_access($user_id);
        // TODO - get user decryption key
        $this->send_response("This doesn't work yet");
    }

    private function validate_access($user_id){
        $this->load->model($this->_model_dir.'api_key_model', 'API');
        $valid_key = $this->API->validate();
        if(!$valid_key){
            $this->send_response("Invalid API Key:".$this->API->get_header_key());
        }

        $this->load->model($this->_model_dir.'user_model', 'User', $this->_db_config);
        $valid_user = $this->User->validate($user_id);
        if(!$valid_user){
            $this->send_response("User does not exist");
        }

        $this->_origin= $this->API->get_key_origin();
    }

    private function send_response($data, $function=false){
        if($function){
            switch($function){
                case 'list_get':
                case 'display_get':
                case 'user_key_get':
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