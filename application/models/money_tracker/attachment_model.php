<?php
/**
 * User: denis
 * Date: 2014-07-20
 */

class Attachment_model extends CI_Model {

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
        $this->db->where($where_array)->delete(Money_Tracker::TABLE_ATTACHMENTS);
    }

    /**
     * @param int $entry_id
     * @param array $attachments
     */
    public function save($entry_id, $attachments) {
        $new_attachments = array();
        foreach($attachments as $attachment){
            $new_attachments[] = array(
                'uuid'=>$attachment['uuid'],
                'entry_id'=>$entry_id,
                'attachment'=>$attachment['filename'],
            );
        }
        if(!empty($new_attachments)){
            $this->db->insert_batch(Money_Tracker::TABLE_ATTACHMENTS, $new_attachments);
        }
    }

    /**
     * @param int $entry_id
     * @return array
     */
    public function get_from_entry_id($entry_id){
        // SELECT id, attachment AS filename FROM attachments WHERE entry_id=$entry_id
        $this->db->select('uuid, attachment AS filename')->from(Money_Tracker::TABLE_ATTACHMENTS)->where('entry_id', $entry_id);
        return $this->db->get()->result_array();
    }

    /**
     * @param int $entry_id
     * @return int
     */
    public function count($entry_id){
        // SELECT COUNT(*) FROM attachments WHERE entry_id=$entry_id
        return $this->db->from(Money_Tracker::TABLE_ATTACHMENTS)->where(array('entry_id'=>$entry_id))->count_all_results();
    }

    public function get($user_id, $id){
        // TODO - rebuild
    }

}