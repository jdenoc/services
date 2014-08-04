<?php
/**
 * User: denis
 * Date: 2014-07-26
 */

interface user_interface {

    public function validate($user);
    public function save($user_data);
    public function delete($user);
    public function get($user);

}