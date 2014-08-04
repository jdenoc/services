<?php
/**
 * User: denis
 * Date: 2014-07-20
 */

class Attachment_model extends CI_Model {

    private $tbl_name = 'attachments';

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    public function delete($entry_id, $id=false) {
        // TODO - test
        $where_array = array('entry_id'=>$entry_id);
        if($id){
            $where_array['id'] = $id;
        }
        // DELETE FROM attachments WHERE entry_id=$entry_id AND id=$id
        $this->db->where($where_array)->delete('attachments');
        if($this->count($entry_id) < 1){
            $this->db->flush_cache();
            // UPDATE entries SET has_attachment=0 WHERE entry_id=$entry_id
            $this->db->where(array('id'=>$entry_id))->update('entries', array('has_attachment'=>0));
        }
    }

    public function save($user_id, $data) {
        // TODO - rebuild
//        if(empty($data['id'])){
//            $this->insert($user_id, $data);
//        } else {
//            $this->update($user_id, $data);
//        }
    }

    private function insert($user_id, $secret_data) {
        // TODO - rebuild
//        $this->db->insert('secrets', array(
//            'user_id'=>$user_id,
//            'name'=>$secret_data['name'],
//            'url'=>$secret_data['url'],
//            'username'=>$secret_data['username'],
//            'encrypted_password'=>$secret_data['encrypted_password'],
//            'password_length'=>$secret_data['password_length'],
//            'notes'=>$secret_data['notes'],
//            'create_stamp'=>'NOW()'
//        ));
    }

    private function update($user_id, $secret_data) {
        // TODO - rebuild
//        $data = array();
//        foreach($secret_data as $key=>$value){
//            if(!empty($value) && $key!='id'){
//                $data[$key] = $value;
//            }
//        }

//        $this->db->where(array('id'=>$secret_data['id'], 'user_id'=>$user_id))->update('secrets',$data);
    }

    public function get_entry($entry_id){
        // TODO - test
        // SELECT id, attachment AS filename FROM attachments WHERE entry_id=$entry_id
        $this->db->select('id, attachment')->from($this->tbl_name)->where('entry_id', $entry_id);
        return $this->db->get()->result_array();
    }

    public function count($entry_id){
        // TODO - test
        // SELECT COUNT(*) FROM attachments WHERE entry_id=$entry_id
        return $this->db->from($this->tbl_name)->where(array('entry_id'=>$entry_id))->get()->count_all_results();
    }

    public function get($user_id, $id){
        // TODO - rebuild
        // SELECT * FROM secrets WHERE user_id=$user_id AND id=$id
//        $this->db->from('secrets')->where(array('user_id'=>$user_id, 'id'=>$id));
//        return $this->db->get()->row_array();
    }

}