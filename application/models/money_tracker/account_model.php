<?php
/**
 * User: denis
 * Date: 2014-07-20
 */

class Account_model extends CI_Model {

    private $_tbl_name = 'accounts';

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    /**
     * @param array $data
     */
    public function create($data) {
        // TODO - build
    }

    /**
     * @param int $id
     */
    public function disable($id) {
        // TODO - build
    }

    /**
     * @param array $data
     * @return int
     */
    public function save_type($data) {
        if(empty($data['id']) || $data['id']==-1){
            $this->insert_type($data);
        } else {
            $this->update_type($data);
        }
    }

    /**
     * @param array $type_data
     */
    private function insert_type($type_data) {
        $this->db->insert('account_types', array(
            'type_name'=>$type_data['type_name'],
            'type'=>$type_data['type'],
            'last_digits'=>$type_data['last_digits'],
            'account_group'=>$type_data['accountID']
        ));
    }

    /**
     * @param array $type_data
     */
    private function update_type($type_data) {
        $data = array();
        foreach($type_data as $key=>$value){
            if(!in_array($key, array('accountID', 'id'))){
                $data[$key] = $value;
            }
        }
        // UPDATE account_types SET $data
        // WHERE account_group=$type_data['accountID']
        // AND id=$type_data['id']
        $this->db->where(array('account_group'=>$type_data['accountID'], 'id'=>$type_data['id']))->update('account_types', $data);
    }

    /**
     * @param double $update_value
     * @param int $account_id
     */
    public function update_balance($update_value, $account_id){
        $this->db->query("LOCK TABLE accounts WRITE");
        $this->db->flush_cache();
        // SELECT total FROM accounts WHERE id=$account_id
        $account = $this->db->select('total')->from($this->_tbl_name)->where(array('id'=>$account_id))->get()->row_array();
        $this->db->flush_cache();
        // UPDATE accounts SET total=($current_total+$update_value) WHERE id=$account_id
        $this->db->where(array('id'=>$account_id))->update($this->_tbl_name, array('total'=>($account['total']+$update_value)));
        $this->db->flush_cache();
        $this->db->query("UNLOCK TABLES");
    }

    /**
     * @return array
     */
    public function list_accounts(){
        // SELECT * FROM accounts
        return $this->db->from($this->_tbl_name)->get()->result_array();
    }

    /**
     * @return array
     */
    public function get_all(){
        // SELECT a.id, a.account As account_name, at.id AS type_id, at.type_name, at.type, at.last_digits
        // FROM `accounts` AS a
        // LEFT JOIN `account_types` AS at ON at.account_group = a.id
        // ORDER BY a.account
        $this->db->select("a.id, a.account As account_name, at.id AS type_id, at.type_name, at.type, at.last_digits")->from($this->_tbl_name." AS a")->join("account_types AS at", "a.id=at.account_group", "left")->order_by('account_name');
        return $this->db->get()->result_array();
    }

    /**
     * @return array
     */
    public function get_account_types(){
        $type = 'enum';
        $col_details = $this->db->query("SHOW COLUMNS FROM account_types WHERE Field='type'")->row_array();
        $field_values = explode(",", str_replace("'", "", substr($col_details['Type'], strlen($type)+1, (strlen($col_details['Type'])-(strlen($type)+2)))));
        sort($field_values);
        return $field_values;
    }

    /**
     * @param int $entry_id
     * @return int
     */
    public function get_account_id_from_entry($entry_id){
        // SELECT at.account_group AS id FROM entries AS e INNER JOIN account_types AS at ON at.id=e.account_type WHERE e.id=$id
        $this->db->select("at.account_group AS id")->from('entries AS e')->where(array('e.id'=>$entry_id))->join("account_types AS at", "at.id=e.account_type", "inner");
        $account = $this->db->get()->row_array();
        return intval($account['id']);
    }
}