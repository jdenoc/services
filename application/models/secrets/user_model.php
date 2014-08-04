<?php
/**
 * User: denis
 * Date: 2014-07-26
 */

require_once(__DIR__.'/../user_interface.php');

class User_model extends CI_Model implements user_interface{

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    public function validate($user_id){
        $user_data = $this->get($user_id);
        return !empty($user_data);
    }

    public function save($user_data){
        // TODO - do something with insert/update
    }

    public function delete($user_id){

    }

    public function get($user_id){
        // SELECT * FROM users WHERE id=$$user_id
        return $this->db->from('users')->where('id', $user_id)->get()->result_array();
    }

    private function insert(){

    }

    private function update(){

    }

    public function get_key($user_id){

    }
}