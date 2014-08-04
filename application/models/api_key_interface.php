<?php
/**
 * User: denis
 * Date: 2014-07-26
 */

interface api_key_interface {

    public function get_header_key();
    public function get_service_key();
    public function validate();
    public function get_key_origin();

}