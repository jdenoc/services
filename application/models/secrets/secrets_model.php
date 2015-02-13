<?php
/**
 * User: denis
 * Date: 2014-07-20
 */

class Secrets_model extends CI_Model {

    private $_updateable_table_columns = array();
    
    public function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->_updateable_table_columns = array("name","url","username","encrypted_password","password_length","notes","user_id");
    }

    /**
     * @param int $user_id
     * @param int $id
     */
    public function delete($user_id, $id) {
        $this->db->where(array('user_id'=>$user_id, 'id'=>$id))->delete(Secrets::TABLE_SECRETS);
    }

    /**
     * @param int $user_id
     * @param array $data
     * @return int
     */
    public function save($user_id, $data) {
        if(empty($data['id'])){
            return $this->insert($user_id, $data);
        } else {
            return $this->update($user_id, $data);
        }
    }

    /**
     * @param int $user_id
     * @param array $secret_data
     * @return int
     */
    private function insert($user_id, $secret_data) {
        $this->db->insert(Secrets::TABLE_SECRETS, array(
            'user_id'=>$user_id,
            'name'=>$secret_data['name'],
            'url'=>$secret_data['url'],
            'username'=>$secret_data['username'],
            'encrypted_password'=>$secret_data['encrypted_password'],
            'password_length'=>$secret_data['password_length'],
            'notes'=>$secret_data['notes'],
            'create_stamp'=>'NOW()'
        ));
        return $this->db->insert_id();
    }

    /**
     * @param int $user_id
     * @param array $secret_data
     * @return int
     */
    private function update($user_id, $secret_data) {
        $data = array();
        foreach($secret_data as $key=>$value){
            if(in_array($key, $this->_updateable_table_columns)){
                $data[$key] = $value;
            }
        }

        if(!empty($data)){
            $this->db->where(array('id'=>$secret_data['id'], 'user_id'=>$user_id))->update(Secrets::TABLE_SECRETS,$data);
            return $secret_data['id'];
        } else {
            return 0;
        }
    }

    /**
     * @param int $user_id
     * @param int $id
     * @return array
     */
    public function get_password($user_id, $id) {
        // SELECT encrypted_password, password_length FROM secrets WHERE id=$id AND user_id=$user_id",
        $this->db->select('encrypted_password, password_length')->from(Secrets::TABLE_SECRETS)->where(array('id'=>$id, 'user_id'=>$user_id));
        return $this->db->get()->row_array();
    }

    /**
     * @param int $user_id
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function get_names($user_id, $start, $limit){
        // SELECT id, `name` FROM secrets WHERE user_id=$user_id LIMIT $start, $limit
        $this->db->select('id, name')->from(Secrets::TABLE_SECRETS)->where('user_id', $user_id)->limit($limit, $start*$limit);
        return $this->db->get()->result_array();
    }

    /**
     * @param int $user_id
     * @return int
     */
    public function count($user_id){
        // SELECT COUNT(*) FROM secrets WHERE user_id=$user_id
        return $this->db->from(Secrets::TABLE_SECRETS)->where('user_id', $user_id)->count_all_results();
    }

    /**
     * @param int $user_id
     * @param int $id
     * @return array
     */
    public function get($user_id, $id){
        // SELECT * FROM secrets WHERE user_id=$user_id AND id=$id
        $this->db->from(Secrets::TABLE_SECRETS)->where(array('user_id'=>$user_id, 'id'=>$id));
        return $this->db->get()->row_array();
    }

}