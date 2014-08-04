<?php
/**
 * User: denis
 * Date: 2014-07-20
 */

class Secrets_model extends CI_Model {

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    public function delete($user_id, $id) {
        $this->db->where(array('user_id'=>$user_id, 'id'=>$id))->delete('secrets');
    }

    public function save($user_id, $data) {
        if(empty($data['id'])){
            $this->insert($user_id, $data);
        } else {
            $this->update($user_id, $data);
        }
    }

    private function insert($user_id, $secret_data) {
        $this->db->insert('secrets', array(
            'user_id'=>$user_id,
            'name'=>$secret_data['name'],
            'url'=>$secret_data['url'],
            'username'=>$secret_data['username'],
            'encrypted_password'=>$secret_data['encrypted_password'],
            'password_length'=>$secret_data['password_length'],
            'notes'=>$secret_data['notes'],
            'create_stamp'=>'NOW()'
        ));
    }

    private function update($user_id, $secret_data) {
        $data = array();
        foreach($secret_data as $key=>$value){
            if(!empty($value) && $key!='id'){
                $data[$key] = $value;
            }
        }

        $this->db->where(array('id'=>$secret_data['id'], 'user_id'=>$user_id))->update('secrets',$data);
    }

    public function get_password($user_id, $id) {
        // SELECT encrypted_password, password_length FROM secrets WHERE id=$id AND user_id=$user_id",
        $this->db->select('encrypted_password, password_length')->from('secrets')->where(array('id'=>$id, 'user_id'=>$user_id));
        return $this->db->get()->row_array();
    }

    public function get_names($user_id, $start, $limit){
        // SELECT id, `name` FROM secrets WHERE user_id=$user_id LIMIT $start, $limit
        $this->db->select('id, name')->from('secrets')->where('user_id', $user_id)->limit($limit, $start*$limit);
        return $this->db->get()->result_array();
    }

    public function count($user_id){
        // SELECT COUNT(*) FROM secrets WHERE user_id=$user_id
        return $this->db->from('secrets')->where('user_id', $user_id)->count_all_results();
    }

    public function get($user_id, $id){
        // SELECT * FROM secrets WHERE user_id=$user_id AND id=$id
        $this->db->from('secrets')->where(array('user_id'=>$user_id, 'id'=>$id));
        return $this->db->get()->row_array();
    }

}