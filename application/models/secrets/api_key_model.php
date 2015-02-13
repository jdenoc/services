<?php
/**
 * User: denis
 * Date: 2014-07-26
 */

require_once(__DIR__.'/../api_key_interface.php');

class Api_key_model extends CI_Model {

    CONST API_KEY_MAX_LENGTH = 32;
    CONST REQUEST_API_HEADER = 'Authorization';
    CONST RESPONSE_API_HEADER = "Authentication-Info:";

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * @param int $user_id
     * @return bool
     */
    public function validate($user_id) {
        $client = $this->get_header_key();
        $server_keys = $this->get_service_keys($user_id);
        $valid = false;
        foreach($server_keys as $user_key){
            if($client == $user_key['api_key']){
                $valid = true;
                break;
            }
        }
        return $valid; 
    }

    /**
     * @return string
     */
    public function get_header_key() {
        return $this->input->get_request_header(self::REQUEST_API_HEADER);
    }

    /**
     * @param int $user_id
     * @return array
     */
    public function get_service_keys($user_id){
        $this->db->select('api_key')->from(Secrets::TABLE_API_KEYS)->where(array("user_id"=>$user_id, 'expired'=>0));
        return $this->db->get()->result_array();
    }

    /**
     * @param int $user_id
     * @param string $udid
     * @return string
     */
    public function get_user_key($user_id, $udid=''){
        $this->db->from(Secrets::TABLE_API_KEYS)->where(array('user_id'=>$user_id, 'udid'=>$udid));
        $key_data = $this->db->get()->row_array();
        if(empty($key_data)){
            $api_key = $this->generate_api_key();
            $this->save_new_api_key($api_key,$udid,$user_id);
        } else {
            $api_key = $this->refresh_key($key_data['api_key']);
        }
        
        return $api_key;
    }

    /**
     * @param string $api_key
     * @return string
     */
    public function refresh_key($api_key=''){
        if(empty($api_key)){
           return ''; 
        }
        
        $this->db->from(Secrets::TABLE_API_KEYS)->where(array("api_key"=>$api_key));
        $key_data = $this->db->get()->row_array();
        if(empty($key_data)){
            $api_key = "";
        } else {
            if ($key_data['expiration_stamp'] < date("Y-m-d H:i:s")) {
                $api_key = $this->generate_api_key();
                $this->db->where(array('id' => $key_data['id']))->update(Secrets::TABLE_API_KEYS, array('expired' => 1));
                $this->save_new_api_key($api_key, $key_data['udid'], $key_data['user_id']);
            } else {
                $api_key = $key_data['api_key'];
            }
        }

        return $api_key;
    }

    /**
     * @return string
     */
    private function generate_api_key() {
        return substr(sha1(rand()), 0, self::API_KEY_MAX_LENGTH);
    }

    /**
     * @param string $new_api_key
     * @param string $udid
     * @param int $user_id
     */
    private function save_new_api_key($new_api_key, $udid, $user_id){
        $this->db->insert(Secrets::TABLE_API_KEYS, array(
            'api_key'=>$new_api_key,
            'udid'=>$udid,
            'user_id'=>$user_id,
            'expiration_stamp'=>date("Y-m-d H:i:s", strtotime("+1 month"))
        ));
    }

    /**
     * @param string $api_key
     */
    public function set_return_api_key_header($api_key = ''){
        if(!empty($api_key)) {
            header(self::RESPONSE_API_HEADER.$api_key);
        }
    }
    
}