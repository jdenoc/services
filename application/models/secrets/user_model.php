<?php
/**
 * User: denis
 * Date: 2014-07-26
 */

require_once(__DIR__.'/../user_interface.php');

class User_model extends CI_Model implements user_interface{

    private $_updateable_table_columns = array();
    
    public function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->_updateable_table_columns = array("email");
    }

    /**
     * @param string $email
     * @return int
     */
    public function confirm_email($email){
        // SELECT id FROM users WHERE email=$email
        $user = $this->db->select('id')->from(Secrets::TABLE_USERS)->where('email', $email)->get()->row_array();
        if(empty($user)) {
            return 0;
        } else {
            return $user['id'];
        }
    }

    /**
     * @param int $user_id
     * @return bool
     */
    public function validate($user_id){
        $user_data = $this->get($user_id);
        return !empty($user_data);
    }

    /**
     * @param array $user_data
     * @return int
     */
    public function save($user_data){
        if(isset($user_data['id'])){
            return $this->update($user_data);
        } else {
            return $this->insert($user_data);
        }
    }

    /**
     * @param int $user_id
     */
    public function delete($user_id){

    }

    /**
     * @param int $user_id
     * @return array
     */
    public function get($user_id){
        // SELECT * FROM users WHERE id=$$user_id
        $this->db->from(Secrets::TABLE_USERS)->where('id', $user_id);
        return $this->db->get()->row_array();
    }

    /**
     * @param array $user_data
     * @return int
     */
    private function insert($user_data){
        $this->db->insert(Secrets::TABLE_USERS, array(
            'user_id'=>$user_data['id'],
            'email'=>$user_data['email']
        ));
        return $this->db->insert_id();
    }

    /**
     * @param array $user_data
     * @return int
     */
    private function update($user_data){
        $data = array();
        foreach($user_data as $key=>$value){
            if(in_array($key, $this->_updateable_table_columns)){
                $data[$key] = $value;
            }
        }

        if(!empty($data)){
            $this->db->where(array('id'=>$user_data['id']))->update(Secrets::TABLE_USERS,$data);
            return $user_data['id'];
        } else {
            return 0;
        }
    }

}