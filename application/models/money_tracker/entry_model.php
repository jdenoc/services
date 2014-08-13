<?php
/**
 * User: denis
 * Date: 2014-07-20
 */

class Entry_model extends CI_Model {

    private $tbl_name = 'entries';

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    public function delete($id) {
        // TODO - test
        // UPDATE entries SET deleted=1 WHERE id=$id
        $this->db->where(array('id'=>$id))->update($this->tbl_name, array('deleted'=>1));
        $this->db->flush_cache();
        // SELECT `value`, expense FROM entries WHERE id=$id AND deleted=1
        $this->db->select("`value`, expense")->from($this->tbl_name)->where(array('e.deleted'=>1, 'e.id'=>$id));
        $entry_data = $this->db->get()->row_array();
        $entry_data['value'] *= ($entry_data['expense'] ? -1 : 1);
        unset($entry_data['expense']);
        return $entry_data['value'];
    }

    public function save($data) {
        // TODO - finish
        $tags = implode("','", $data['tags']);
        $this->db->select("id")->from('tags')->where_in('tag', $tags);
        $tag_ids = $this->db->get()->result_array();
        $data['tags'] = (empty($tag_ids)) ? '' : json_encode($tag_ids);

        if(empty($data['id']) || $data['id']==-1){
            $this->insert($data);
        } else {
            $this->update($data);
        }
    }

    private function insert($entry_data) {
        // TODO - test
        $this->db->insert($this->tbl_name, array(
            'date'=>$entry_data['date'],
            'account_type'=>$entry_data['account_type'],
            'value'=>$entry_data['value'],
            'tags'=>$entry_data['tags'],
            'memo'=>$entry_data['memo'],
            'confirm'=>$entry_data['confirm'],
            'expense'=>$entry_data['expense']
        ));
        return $this->db->insert_id();
    }

    private function update($entry_data) {
        // TODO - test
        $data = array();
        foreach($entry_data as $key=>$value){
            if(!empty($value) && $key!='id'){
                $data[$key] = $value;
            }
        }

        $this->db->where(array('id'=>$entry_data['id']))->update($this->tbl_name,$data);
        return $entry_data['id'];
    }

    public function list_entries($where_array, $start, $limit){
        // SELECT entries.*, account_types.type_name AS account_type_name, account_types.last_digits AS account_last_digits
        // FROM entries
        // INNER JOIN account_types ON account_types.id = entries.account_type $where
        // ORDER BY entries.`date` DESC, entries.id DESC LIMIT ($start*$limit), $limit
        $this->db->select("entries.*, account_types.type_name AS account_type_name, account_types.last_digits AS account_last_digits")->from($this->tbl_name)->join('account_types', "account_types.id=entries.account_type")->where($where_array)->order_by('entries.date', 'DESC')->limit($limit, $start*$limit);
        return $this->db->get()->result_array();
    }

    public function count($where){
        // SELECT COUNT(*) FROM entries INNER JOIN account_types ON account_types.id = entries.account_type WHERE $where
        $this->db->from($this->tbl_name)->join('account_types', 'account_types.id = entries.account_type', 'inner')->where($where);
        return $this->db->count_all_results();
    }

    public function get_all_tags(){
        // SELECT * FROM tags
        return $this->db->from("tags")->get()->result_array();
    }

    public function get_select_tags($tag_ids){
        // TODO - test
        if(!empty($tag_ids)){
            // SELECT * FROM tags WHERE id IN ($tag_ids);
            return $this->db->from('tags')->where_in(array('id'=>$tag_ids))->get()->result_array();
        } else {
            return array();
        }
    }

    public function get($id){
        // TODO - test
        // SELECT e.*, at.type_name AS account_type_name, at.last_digits AS account_last_digits
        // FROM entries AS e WHERE id=$id
        $this->db->select("e.*, at.type_name AS account_type_name, at.last_digits AS account_last_digits")->from($this->tbl_name." AS e")->join("account_types AS at", "at.id=e.account_type", 'inner')->where(array("e.id"=>$id));
        return $this->db->get()->row_array();
    }
}