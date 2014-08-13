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
        $this->db->where($where_array)->delete($this->tbl_name);
        if($this->count($entry_id) < 1){
            $this->db->flush_cache();
            // UPDATE entries SET has_attachment=0 WHERE entry_id=$entry_id
            $this->db->where(array('id'=>$entry_id))->update('entries', array('has_attachment'=>0));
        }
    }

    public function save($entry_id, $attachments) {
        // TODO - rebuild
        $new_attachments = array();
        foreach($attachments as $attachment){
            $pos = strrpos($attachment, '.');
            $ext = substr($attachment, $pos);
            $new_attachments[] = array(
                'entry_id'=>$entry_id,
                'attachment'=>$attachment,
                'ext'=>$ext
            );
        }
        if(!empty($new_attachments)){
            $has_attachment = 1;
            $this->db->insert_batch($this->tbl_name, $new_attachments);
            $this->db->flush_cache();
            $this->db->where(array('id'=>$entry_id))->update('entries', array('has_attachment'=>$has_attachment));
        }
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
        return $this->db->from($this->tbl_name)->where(array('entry_id'=>$entry_id))->count_all_results();
    }

    public function get($user_id, $id){
        // TODO - rebuild
        // SELECT * FROM secrets WHERE user_id=$user_id AND id=$id
//        $this->db->from('secrets')->where(array('user_id'=>$user_id, 'id'=>$id));
//        return $this->db->get()->row_array();
    }

}