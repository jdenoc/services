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

    public function create($data) {
        // TODO - build
    }

    public function disable($id) {
        // TODO - build
    }

    public function save_type($data) {
        if(empty($data['id']) || $data['id']==-1){
            return $this->insert_type($data);
        } else {
            return $this->update_type($data);
        }
    }

    private function insert_type($secret_data) {
        // TODO - build
        return null;
    }

    private function update_type($secret_data) {
        // TODO - build
        return null;
    }

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

    public function list_accounts(){
        // SELECT * FROM accounts
        return $this->db->from($this->_tbl_name)->get()->result_array();
    }

    public function get_all(){
        // SELECT a.id, a.account As account_name, at.id AS type_id, at.type_name, at.type, at.last_digits
        // FROM `accounts` AS a
        // LEFT JOIN `account_types` AS at ON at.account_group = a.id
        // ORDER BY a.account
        $this->db->select("a.id, a.account As account_name, at.id AS type_id, at.type_name, at.type, at.last_digits")->from($this->_tbl_name." AS a")->join("account_types AS at", "a.id=at.account_group", "left")->order_by('account_name');
        return $this->db->get()->result_array();
    }
    
    public function get_account_types(){
        $type = 'enum';
        $col_details = $this->db->query("SHOW COLUMNS FROM account_types WHERE Field='type'")->row_array();
        $field_values = explode(",", str_replace("'", "", substr($col_details['Type'], strlen($type)+1, (strlen($col_details['Type'])-(strlen($type)+2)))));
        sort($field_values);
        return $field_values;
    }

    public function get_account_id_from_entry($entry_id){
        // SELECT at.account_group AS id FROM entries AS e INNER JOIN account_types AS at ON at.id=e.account_type WHERE e.id=$id
        $this->db->select("at.account_group AS id")->from('entries AS e')->where(array('e.id'=>$entry_id))->join("account_types AS at", "at.id=e.account_type", "inner");
        $account = $this->db->get()->row_array();
        return $account['id'];
    }
}