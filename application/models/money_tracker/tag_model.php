<?php
/**
 * Created by 
 * User: denis.oconnor
 * Date: 2014-12-14
 */

class Tag_model extends CI_Model {

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * @return array
     */
    public function get_all_tags(){
        // SELECT * FROM tags
        return $this->db->from(Money_Tracker::TABLE_TAGS)->get()->result_array();
    }

    /**
     * @param int $id
     * @return array
     */
    public function get_entry_tags($id){
        // SELECT t.id, t.tag FROM tags AS t
        // INNER JOIN entry_tags AS et ON t.id=et.tag_id
        // WHERE et.entry_id=$id;
        $this->db->select("t.id, t.tag")
            ->from(Money_Tracker::TABLE_TAGS." AS t")
            ->join(Money_Tracker::TABLE_ENTRY_TAGS." AS et", "t.id=et.tag_id", 'inner')
            ->where(array("et.entry_id"=>$id));
        return $this->db->get()->result_array();
    }

    /**
     * @param string $col
     * @param array $tag_values
     * @return array
     */
    public function get_select_tags($col, $tag_values){
        if(!empty($tag_values)){
            // SELECT * FROM tags WHERE $col IN ($tag_values);
            return $this->db->from(Money_Tracker::TABLE_TAGS)->where_in($col, $tag_values)->get()->result_array();
        } else {
            return array();
        }
    }

    /**
     * @param int $entry_id
     * @param int $new_tags
     */
    public function save($entry_id, $new_tags){
        $new_tags = $this->get_select_tags('tag', $new_tags);
        $existing_tags = $this->get_entry_tags($entry_id);
        foreach($new_tags as $tag){
            if(in_array($tag, $existing_tags)){
                unset($existing_tags[ array_search($tag, $existing_tags) ]);
            } else {
                $this->insert($entry_id, $tag['id']);
            }
        }
        
        foreach($existing_tags as $tag){
            $this->delete($entry_id, $tag['id']);
        }
    }

    /**
     * @param int $entry_id
     * @param int $tag_id
     */
    private function insert($entry_id, $tag_id){
        $this->db->insert(Money_Tracker::TABLE_ENTRY_TAGS, array(
            'entry_id'=>$entry_id,
            'tag_id'=>$tag_id,
        ));
    }

    /**
     * @param int $entry_id
     * @param int $tag_id
     */
    private function delete($entry_id, $tag_id){
        $this->db->where(array('entry_id'=>$entry_id, 'tag_id'=>$tag_id))->delete(Money_Tracker::TABLE_ENTRY_TAGS);
    }
}