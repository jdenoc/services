<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';
class Money_Tracker extends REST_Controller{
    
    // Table names
    CONST TABLE_TAGS = "tags";
    CONST TABLE_ACCOUNTS = "accounts";
    CONST TABLE_ATTACHMENTS = "attachments";
    CONST TABLE_ACCOUNTS_TYPES = "account_types";
    CONST TABLE_ENTRIES = "entries";
    CONST TABLE_ENTRY_TAGS = "entry_tags";
    CONST TABLE_USERS = "users";

    private $_db_config;
    private $_db_config_file = '/../../config/money-tracker.db_config.php';
    private $_origin;
    private $_model_dir = 'money_tracker/';

    public function __construct(){
        parent::__construct();
        if(!file_exists(__DIR__.$this->_db_config_file)){
            $error = 'DB config file not found';
            error_log($error);
            $this->send_response($error);
        } else {
            $this->_db_config = require(__DIR__.$this->_db_config_file);
        }
    }

    public function delete_get() {
        // TODO - test
        $this->validate_access();

        $id = $this->get('id');
        $this->load->model($this->_model_dir.'attachment_model', 'Attachment', $this->_db_config);
        $attachment_id = $this->get('attachment');
        if(!empty($attachment_id)){
            $this->Attachment->delete($id, $attachment_id);
        } else {
            $this->load->model($this->_model_dir.'entry_model', 'Entry', $this->_db_config);
            $this->load->model($this->_model_dir.'account_model', 'Account', $this->_db_config);
            $this->Attachment->delete($id);
            $entry_value = $this->Entry->delete($id);
            $account_id = $this->Account->get_account_id('entry', $id);
            $this->Account->update_balance(-1*$entry_value, $account_id);
        }
        $this->send_response(1, __FUNCTION__);
    }

    public function entry_get() {
        $this->validate_access();

        $id = $this->get('id');
        $this->load->model($this->_model_dir.'entry_model', 'Entry', $this->_db_config);
        $entry = $this->Entry->get($id);
        if(empty($entry)){
            $this->send_response('Entry not found');
        }
        
        if($this->Entry->has_attachments($id)){
            $this->load->model($this->_model_dir.'attachment_model', 'Attachment', $this->_db_config);
            $attachments = $this->Attachment->get_from_entry_id($id);
            if(empty($attachments)){
                $entry['attachments'] = array();
                $entry['has_attachment'] = 0;
            } else {
                $entry['attachments'] = $attachments;
                $entry['has_attachment'] = 1;
            }
        } else {
            $entry['attachments'] = array();
            $entry['has_attachment'] = 0;
        }
        
        if($this->Entry->has_tags($id)){
            $this->load->model($this->_model_dir.'tag_model', 'Tag', $this->_db_config);
            $entry['tags'] = $this->Tag->get_entry_tags($id);
        }

        $this->send_response($entry, __FUNCTION__);
    }

    public function count_post() {
        $this->validate_access();

        $where_array = $this->process_where_array(json_decode(base64_decode($this->post('where')), true));
        $this->load->model($this->_model_dir.'entry_model', 'Entry', $this->_db_config);
        $total_entries = $this->Entry->count($where_array);
        $this->send_response($total_entries, __FUNCTION__);
    }

    public function list_post() {
        $this->validate_access();

        $limit = $this->post('limit');
        $start = $this->post('start');
        $where_array = $this->process_where_array(json_decode(base64_decode($this->post('where')), true));
        $this->load->model($this->_model_dir.'entry_model', 'Entry', $this->_db_config);
        $entries = $this->Entry->list_entries($where_array, $start, $limit);

        $this->send_response($entries, __FUNCTION__);
    }

    public function list_accounts_get(){
        $this->validate_access();

        $this->load->model($this->_model_dir.'account_model', 'Account', $this->_db_config);
        $accounts = $this->Account->list_accounts();
        $this->send_response($accounts, __FUNCTION__);
    }

    public function save_post() {
        $this->validate_access();

        $entry_data = json_decode(base64_decode($this->post('data')), true);
        if(empty($entry_data)){
            $this->send_response(0, __FUNCTION__);
        }

        $this->load->model($this->_model_dir.'entry_model', 'Entry', $this->_db_config);
        $this->load->model($this->_model_dir.'attachment_model', 'Attachment', $this->_db_config);
        $this->load->model($this->_model_dir.'account_model', 'Account', $this->_db_config);
        $this->load->model($this->_model_dir.'tag_model', 'Tag', $this->_db_config);

        $account_id = null;
        if(!empty($entry_data['id']) && $entry_data['id'] != -1){
            $existing_entry_data = $this->Entry->get($entry_data['id']);
            if(!empty($existing_entry_data)){
                $existing_entry_data['value'] *= ($existing_entry_data['expense'] ? -1 : 1);
                $account_id = $this->Account->get_account_id('entry', $existing_entry_data['id']);
                $this->Account->update_balance((-1*$existing_entry_data['value']), $account_id);
            }
        }

        $entry_id = $this->Entry->save($entry_data);
        $this->Tag->save($entry_id, $entry_data['tags']);
        $this->Attachment->save($entry_id, $entry_data['attachments']);
        $entry_data['value'] *= ($entry_data['expense'] ? -1 : 1);
        if(is_null($account_id)){
            $account_id = $this->Account->get_account_id('entry', $entry_id);
        } elseif(isset($existing_entry_data) && $entry_data['account_type']!=$existing_entry_data['account_type']){
            $account_id = $this->Account->get_account_id('type', $entry_data['account_type']);
        }
        $this->Account->update_balance($entry_data['value'], $account_id);
        $this->send_response(1, __FUNCTION__);
    }

    public function tags_get(){
        $this->validate_access();

        $this->load->model($this->_model_dir.'tag_model', 'Tag', $this->_db_config);
        $tags = $this->Tag->get_all_tags();
        $this->send_response($tags, __FUNCTION__);
    }

    public function account_details_get(){
        $this->validate_access();

        $this->load->model($this->_model_dir.'account_model', 'Account', $this->_db_config);
        $account_data = $this->Account->get_all();
        $return_account_data = array(
            'types'=>$this->Account->get_account_types()
        );
        foreach($account_data as $acd){
            $return_account_data[$acd['id']]['account_name'] = $acd['account_name'];
            if(!is_null($acd['type_id'])){
                $return_account_data[$acd['id']]['type'][] = array(
                    'type_id'=>$acd['type_id'],
                    'type_name'=>$acd['type_name'],
                    'type'=>$acd['type'],
                    'last_digits'=>$acd['last_digits']
                );
            } else {
                $return_account_data[$acd['id']]['type'] = array();
            }
        }
        $this->send_response($return_account_data, __FUNCTION__);
    }
    
    public function add_account_post(){
        // TODO - take in a base64 encoding of a JSON string;
        // TODO -       containing: account name
        // TODO - pass info to account_model::create();
    }
    
    public function close_account_get(){
        // TODO - take in account ID.
        // TODO - pass info to account_model::disable();
    }
    
    public function save_account_type_post(){
        // Handles account type creation and updates
        $this->validate_access();

        $type_data = json_decode(base64_decode($this->post('data')), true);
        if(empty($type_data)){
            $this->send_response(0, __FUNCTION__);
        }

        $this->load->model($this->_model_dir.'account_model', 'Account', $this->_db_config);
        $this->Account->save_type($type_data);
        $this->send_response(1, __FUNCTION__);
    }
    
    public function disable_account_type_post(){
        // Handles account type disabling
        $this->validate_access();

        $type_data = json_decode(base64_decode($this->post('data')), true);
        if(empty($type_data)){
            $this->send_response(0, __FUNCTION__);
        } else {
            $type_data['disabled'] = 1;
        }
        $this->load->model($this->_model_dir.'account_model', 'Account', $this->_db_config);
        $this->Account->save_type($type_data);
        $this->send_response(1, __FUNCTION__);
    }
    
    private function validate_access(){
        $this->load->model($this->_model_dir.'api_key_model', 'API');
        $valid_key = $this->API->validate();
        if(!$valid_key){
            $this->send_response("Invalid API Key:".$this->API->get_header_key());
        }

        $this->_origin= $this->API->get_key_origin();
    }

    private function send_response($data, $function=false){
        if($function){
            $code = 200;
            $error_msg = '';
            switch($function){
                case 'list_accounts_get':
                case 'entry_get':
                case 'tags_get':
                case 'list_post':
                case 'account_details_get':
                    $result = base64_encode(json_encode($data));
                    break;
                default:
                    $result = $data;
            }
        } else {
            $code = 400;
            $error_msg = $data;
            $result = '';
        }
        $this->response(array('error'=>$error_msg, 'result'=>$result), $code);
    }

    private function process_where_array($where_array){
        $where_stmt = array('entries.deleted'=>0);
        if(!empty($where_array['start_date']))
            $where_stmt["entries.`date` >="] = $where_array['start_date'];
        if(!empty($where_array['end_date']))
            $where_stmt["entries.`date` <="] = $where_array["end_date"];
        if(!empty($where_array['account_type']))
            $where_stmt["entries.account_type"] = $where_array["account_type"];
        if(isset($where_array['attachments']) && in_array($where_array['attachments'], array(0,1)))
            $where_stmt["has_attachment"] = $where_array["attachments"];
        if(isset($where_array['expense']) && in_array($where_array['expense'], array(0,1)))
            $where_stmt['entries.expense'] = $where_array['expense'];
        if(!empty($where_array['confirm'])){
            $where_stmt["entries.confirm"] = 0;
            unset($where_array['confirm']);
        }
        if(!empty($where_array['min_value']))
            $where_stmt["entries.value >="] = $where_array["min_value"];
        if(!empty($where_array['max_value']))
            $where_stmt["entries.value <="] = $where_array["max_value"];
        if(!empty($where_array['group']))
            $where_stmt["account_types.account_group"] = $where_array["group"];
        if(!empty($where_array['tags'])){
            $where_stmt["tags"] = $where_array['tags'];
        }
        return $where_stmt;
    }
}
