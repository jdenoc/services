<?php
/**
 * User: denis
 * Date: 2014-07-20
 */

class Entry_model extends CI_Model {

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
        $current_total = $this->db->select('total')->from($this->tbl_name)->where(array('id'=>$account_id))->get()->row_array();
        $this->db->flush_cache();
        // UPDATE accounts SET total=($current_total+$update_value) WHERE id=$account_id
        $this->db->where(array('id'=>$account_id))->update($this->tbl_name, array('total'=>($current_total+$update_value)));
        $this->db->flush_cache();
        $this->db->query("UNLOCK TABLES");
    }

    public function list_accounts($user_id, $start, $limit){
        // TODO - test
        // SELECT * FROM accounts
        return $this->db->from($this->tbl_name)->get()->result_array();
    }

    public function get($user_id, $id){
        // TODO - rebuild
    }
}