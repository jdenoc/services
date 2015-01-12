<?php
/**
 * User: denis
 * Date: 2014-07-20
 */

class Entry_model extends CI_Model {

    private $_table_columns = array();
    
    public function __construct() {
        // Call the Model constructor
        parent::__construct();
        $this->_table_columns = array("id", "date", "account_type", "value", "memo", "expense", "confirm", "deleted", "stamp");
    }

    /**
     * @param int $id
     * @return int
     */
    public function delete($id) {
        // UPDATE entries SET deleted=1 WHERE id=$id
        $this->db->where(array('id'=>$id))->update(Money_Tracker::TABLE_ENTRIES, array('deleted'=>1));
        $this->db->flush_cache();
        // SELECT `value`, expense FROM entries WHERE id=$id AND deleted=1
        $this->db->select("`value`, expense")->from(Money_Tracker::TABLE_ENTRIES)->where(array('deleted'=>1, 'id'=>$id));
        $entry_data = $this->db->get()->row_array();
        $entry_data['value'] *= ($entry_data['expense'] ? -1 : 1);
        unset($entry_data['expense']);
        return $entry_data['value'];
    }

    /**
     * @param array $data
     * @return int
     */
    public function save($data) {
        if(empty($data['id']) || $data['id']==-1){
            return $this->insert($data);
        } else {
            return $this->update($data);
        }
    }

    /**
     * @param array $entry_data
     * @return int
     */
    private function insert($entry_data) {
        $this->db->insert(Money_Tracker::TABLE_ENTRIES, array(
            'date'=>$entry_data['date'],
            'account_type'=>$entry_data['account_type'],
            'value'=>$entry_data['value'],
            'memo'=>$entry_data['memo'],
            'confirm'=>$entry_data['confirm'],
            'expense'=>$entry_data['expense']
        ));
        return $this->db->insert_id();
    }

    /**
     * @param array $entry_data
     * @return int
     */
    private function update($entry_data) {
        $data = array();
        foreach($entry_data as $key=>$value){
            if($key!='id' && in_array($key, $this->_table_columns)){
                $data[$key] = $value;
            }
            unset($key, $value);
        }

        if(!empty($data)){
            $this->db->where(array('id'=>$entry_data['id']))->update(Money_Tracker::TABLE_ENTRIES,$data);
        }
        return $entry_data['id'];
    }

    /**
     * @param array $where_array
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function list_entries($where_array, $start, $limit){
        $this->db = $this->generate_list_count_query($where_array);
        // Building query with the following components:
        // SELECT
        //      entries.*,
        //      IF((SELECT COUNT(*) FROM attachments WHERE attachments.entry_id=entries.id) > 0, 1, 0) AS has_attachment,
        //      account_types.type_name AS account_type_name,
        //      account_types.last_digits AS account_last_digits,
        //      CONCAT('[', GROUP_CONCAT(entry_tags.tag_id SEPARATOR ', '), ']') AS tags
        // ########################
        // GROUP BY entries.id
        // ORDER BY entries.`date` DESC, entries.id DESC
        // LIMIT ($start*$limit), $limit;
        $this->db->select(
                "entries.*,
                IF((SELECT COUNT(*) FROM attachments WHERE attachments.entry_id=entries.id) > 0, 1, 0) AS has_attachment,
                account_types.type_name AS account_type_name,
                account_types.last_digits AS account_last_digits,
                CONCAT('[', GROUP_CONCAT(entry_tags.tag_id SEPARATOR ', '), ']') AS tags"
            )
            ->group_by(Money_Tracker::TABLE_ENTRIES.'.id')
            ->order_by(Money_Tracker::TABLE_ENTRIES.'.date DESC, '.Money_Tracker::TABLE_ENTRIES.'.id DESC')
            ->limit($limit, $start*$limit);
        return $this->db->get()->result_array();
    }

    /**
     * @param array $where
     * @return int
     */
    public function count($where){
        $this->db = $this->generate_list_count_query($where);
        return $this->db->count_all_results();
    }

    /**
     * @param int $id
     * @return array
     */
    public function get($id){
        // SELECT e.*, at.type_name AS account_type_name, at.last_digits AS account_last_digits
        // FROM entries AS e
        // INNER JOIN account_types AS at ON at.id=e.account_type
        // WHERE e.id=$id
        $this->db->select("e.*, at.type_name AS account_type_name, at.last_digits AS account_last_digits")
            ->from(Money_Tracker::TABLE_ENTRIES." AS e")
            ->join(Money_Tracker::TABLE_ACCOUNTS_TYPES." AS at", "at.id=e.account_type", 'inner')
            ->where(array("e.id"=>$id));
        return $this->db->get()->row_array();
    }

    /**
     * @param int $id
     * @return bool
     */
    public function has_tags($id){
        // SELECT COUNT(*) FROM entry_tags WHERE entry_id=$id;
        $this->db->from(Money_Tracker::TABLE_ENTRY_TAGS)->where(array("entry_id"=>$id));
        return $this->db->count_all_results() > 0;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function has_attachments($id){
        // SELECT COUNT(*) FROM attachements WHERE entry_id=$id;
        $this->db->from(Money_Tracker::TABLE_ATTACHMENTS)->where(array("entry_id"=>$id));
        return $this->db->count_all_results() > 0;
    }

    /**
     * @param array $where_array
     * @return mixed
     */
    private function generate_list_count_query($where_array){
        if(isset($where_array['tags'])) {
            // Adding the following to the query to be run:
            // WHERE entry_tags.tag_id IN ($where_array['tags'])
            $this->db->where_in("entry_tags.tag_id", $where_array['tags']);
            unset($where_array['tags']);
        }
        if(isset($where_array["has_attachment"])){
            if($where_array["has_attachment"]==1){
                // Building query with the following component:
                // INNER JOIN attachments ON attachments.entry_id=entries.id
                $this->db->join(Money_Tracker::TABLE_ATTACHMENTS, Money_Tracker::TABLE_ATTACHMENTS.".entry_id=".Money_Tracker::TABLE_ENTRIES.".id", 'inner');
            } elseif($where_array["has_attachment"]==0){
                // Building query with the following component:
                // LEFT JOIN attachments ON attachments.entry_id=entries.id
                // WHERE attachments.entry_id IS NULL
                $this->db->join(Money_Tracker::TABLE_ATTACHMENTS, Money_Tracker::TABLE_ATTACHMENTS.".entry_id=".Money_Tracker::TABLE_ENTRIES.".id", 'left');
                $this->db->where(Money_Tracker::TABLE_ATTACHMENTS.".entry_id IS NULL");
            }
            unset($where_array["has_attachment"]);
        }

        // Building query with the following component:
        // SELECT * FROM entries
        // INNER JOIN account_types ON account_types.id = entries.account_type
        // LEFT JOIN entry_tags ON entry_tags.entry_id=entries.id
        // WHERE $where_array
        $this->db->from(Money_Tracker::TABLE_ENTRIES)
            ->join(Money_Tracker::TABLE_ACCOUNTS_TYPES, Money_Tracker::TABLE_ACCOUNTS_TYPES.".id=".Money_Tracker::TABLE_ENTRIES.".account_type", 'inner')
            ->join(Money_Tracker::TABLE_ENTRY_TAGS, Money_Tracker::TABLE_ENTRY_TAGS.".entry_id=.".Money_Tracker::TABLE_ENTRIES.".id", 'left')
            ->where($where_array);
        return $this->db;
    }
}