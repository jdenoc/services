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
        // SELECT e.`value` AS `value`, e.expense AS expense, at.account_group AS id FROM entries AS e INNER JOIN account_types AS at ON at.id=e.account_type WHERE e.id=$id AND e.deleted=1
        $this->db->select("e.`value`, e.expense AS , account_types.account_group AS id")->from($this->tbl_name.' AS e')->where(array('e.deleted'=>1, 'e.id'=>$id))->join("account_types AS at", "at.id=e.account_type", "inner");
        $entry_data = $this->db->get()->row_array();
        $entry_data['value'] *= ($entry_data['expense'] ? -1 : 1);
        unset($entry_data['expense']);
        return $entry_data;
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

    private function insert($secret_data) {
        // TODO - rebuild
//        $this->db->insert('secrets', array(
//            'name'=>$secret_data['name'],
//            'url'=>$secret_data['url'],
//            'username'=>$secret_data['username'],
//            'encrypted_password'=>$secret_data['encrypted_password'],
//            'password_length'=>$secret_data['password_length'],
//            'notes'=>$secret_data['notes'],
//            'create_stamp'=>'NOW()'
//        ));
    }

    private function update($secret_data) {
        // TODO - rebuild
//        $data = array();
//        foreach($secret_data as $key=>$value){
//            if(!empty($value) && $key!='id'){
//                $data[$key] = $value;
//            }
//        }

//        $this->db->where(array('id'=>$secret_data['id']))->update('secrets',$data);
    }

    public function list_entries($where_array, $start, $limit){
        // TODO - test
        // SELECT entries.*, account_types.type_name AS account_type_name, account_types.last_digits AS account_last_digits
        // FROM entries AS e
        // INNER JOIN account_types AS at ON at.id = e.account_type $where
        // ORDER BY e.`date` DESC, entries.id DESC LIMIT ($start*$limit), $limit
        $this->db->select("e.*, at.type_name AS account_type_name, at.last_digits AS account_last_digits")->from($this->tbl_name." AS e")->join('account_types AS at', "at.id=e.account_type")->where($where_array)->order_by('e.date', 'DESC')->limit($limit, $start*$limit);
        return $this->db->get()->result_array();
    }

    public function count($where){
        // TODO - test
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