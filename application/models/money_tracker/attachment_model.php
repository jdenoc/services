<?php
/**
 * User: denis
 * Date: 2014-07-20
 */

class Attachment_model extends CI_Model {

    private $_tbl_name = 'attachments';

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * @param int $entry_id
     * @param int|bool $id
     */
    public function delete($entry_id, $id=false) {
        // TODO - test
        $where_array = array('entry_id'=>$entry_id);
        if($id){
            $where_array['id'] = $id;
        }
        // DELETE FROM attachments WHERE entry_id=$entry_id AND id=$id
        $this->db->where($where_array)->delete($this->_tbl_name);
        if($this->count($entry_id) < 1){
            $this->db->flush_cache();
            // UPDATE entries SET has_attachment=0 WHERE entry_id=$entry_id
            $this->db->where(array('id'=>$entry_id))->update('entries', array('has_attachment'=>0));
        }
    }

    /**
     * @param int $entry_id
     * @param array $attachments
     */
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
            $this->db->insert_batch($this->_tbl_name, $new_attachments);
            $this->db->flush_cache();
            $this->db->where(array('id'=>$entry_id))->update('entries', array('has_attachment'=>$has_attachment));
        }
    }

    /**
     * @param int $entry_id
     * @return array
     */
    public function get_entry($entry_id){
        // SELECT id, attachment AS filename FROM attachments WHERE entry_id=$entry_id
        $this->db->select('id, attachment AS filename')->from($this->_tbl_name)->where('entry_id', $entry_id);
        return $this->db->get()->result_array();
    }

    /**
     * @param int $entry_id
     * @return int
     */
    public function count($entry_id){
        // SELECT COUNT(*) FROM attachments WHERE entry_id=$entry_id
        return $this->db->from($this->_tbl_name)->where(array('entry_id'=>$entry_id))->count_all_results();
    }

    public function get($user_id, $id){
        // TODO - rebuild
    }

}