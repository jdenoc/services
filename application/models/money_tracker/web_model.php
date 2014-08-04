<?php
/**
 * User: denis
 * Date: 2014-07-27
 */

class Web_model extends CI_Model {

    public function __construct() {
        // Call the Model constructor
        parent::__construct();
    }

    public function web_list_accounts($accounts){
        // TODO - test
        $display = '';
        $account_position = 3;
        foreach($accounts as $account){
            $display .= '<li><a href="#" onclick="resetFilter();displayAccount({\'group\':'.$account['id'].'}, '.$account_position.')">'.$account['account'].'<br/>$'.number_format($account['total'], 2).'</a></li>'."\r\n";
            $account_position++;
        }
        return $display;
    }

    public function web_list_entries($entries, $tags){
        $display = '';
        foreach($entries as $row){
            $tag_displays = '';
            if(!empty($row['tags'])){
                $tag_ids = json_decode($row['tags'], true);
                foreach($tags as $t){
                    if(in_array($t['id'], $tag_ids)){
                        $tag_displays .= '<span class="label label-default">'.$t['tag'].'</span><br/>'."\r\n";
                    }
                }
            }
            $display .= '<tr class="'.(!$row['confirm'] ? 'warning' : (!$row['expense'] ? 'success' : '' )).'">';
            $display .= '  <td class="check-col" data-toggle="modal" data-target="#entry-modal" onclick="editDisplay.fill('.$row['id'].');">';
            $display .= '      <span class="glyphicon glyphicon-pencil"></span>';
            $display .= '  </td>';
            $display .= '  <td class="date-col">'.$row['date'].'</td>';
            $display .= '  <td>'.$row['memo'].'</td>';
            $display .= '  <td class="value-col">$'.number_format($row['value'], 2).'</td>';
            $display .= '  <td class="type-col"><span class="glyphicon glyphicon-list-alt" onclick="alert(\''.$row['account_type_name'].' ('.$row['account_last_digits'].')\n'.($row['expense']?'Expense':'Income').($row['confirm']?'\nConfirmed':'').'\')"></span></td>';
            $display .= '  <td><input type="checkbox" '.($row['has_attachment']==1 ? 'checked' : '' ).' onclick="return false;" /></td>';
            $display .= '  <td>'.$tag_displays.'</td>';
            $display .= '</tr>';
        }
        return $display;
    }

    public function web_display($secret){
        // TODO - rebuild?
    }

    public function web_save(){
        // TODO - rebuild?
    }
}