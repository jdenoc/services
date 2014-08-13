<?php
/**
 * User: denis
 * Date: 2014-07-20
 */

class Account_model extends CI_Model {

    private $tbl_name = 'accounts';

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    public function delete($id) {
        // TODO - rebuild
    }

    public function save($data) {
        // TODO - rebuild
    }

    private function insert($secret_data) {
        // TODO - rebuild
    }

    private function update($secret_data) {
        // TODO - rebuild
    }

    public function update_balance($update_value, $account_id){
        // TODO - test
        $this->db->query("LOCK TABLE accounts WRITE");
        $this->db->flush_cache();
        // SELECT total FROM accounts WHERE id=$account_id
        $account = $this->db->select('total')->from($this->tbl_name)->where(array('id'=>$account_id))->get()->row_array();
        $this->db->flush_cache();
        // UPDATE accounts SET total=($current_total+$update_value) WHERE id=$account_id
        $this->db->where(array('id'=>$account_id))->update($this->tbl_name, array('total'=>($account['total']+$update_value)));
        $this->db->flush_cache();
        $this->db->query("UNLOCK TABLES");
    }

    public function list_accounts(){
        // TODO - test
        // SELECT * FROM accounts
        return $this->db->from($this->tbl_name)->get()->result_array();
    }

    public function get_account_id_from_entry($entry_id){
        // SELECT at.account_group AS id FROM entries AS e INNER JOIN account_types AS at ON at.id=e.account_type WHERE e.id=$id
        $this->db->select("at.account_group AS id")->from('entries AS e')->where(array('e.id'=>$entry_id))->join("account_types AS at", "at.id=e.account_type", "inner");
        $account = $this->db->get()->row_array();
        return $account['id'];
    }

    public function get($user_id, $id){
        // TODO - rebuild
    }
}