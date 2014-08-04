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

    public function web_list($data){
        $display_secrets = '';
        foreach($data as $row){
            $display_secrets .= "<tr id='secret_".$row['id']."' class='secret_row'>\r\n";
            $display_secrets .= "  <td></td>\r\n";
            $display_secrets .= '  <td class="secret_name">'.$row['name']."</td>\r\n";
            $display_secrets .= "  <td></td>\r\n";
            $display_secrets .= '</tr>';
        }
        return base64_encode($display_secrets);
    }

    public function web_display($secret){
        $display_secret = '';
        if(!empty($secret)){
            $secret['url'] = (strpos($secret['url'], 'http')!==false ? '' : 'http://').$secret['url'];
            $display_secret .= "<tr>\r\n";
            $display_secret .= "  <td></td>\r\n";
            $display_secret .= '  <td colspan="2" class="display_secret">'."\r\n";
            $display_secret .= '      <label>Username: ';
            $display_secret .= '          <input type="text" name="secret_username" class="form-control" value="'.$secret['username'].'" readonly/>';
            $display_secret .= $this->web_display_unlock_button('username', 1);
            $display_secret .= $this->web_display_copy_button();
            $display_secret .= '      </label>'."\r\n";
            $display_secret .= '      <label>Password: ';
            $display_secret .= '          <input type="password" name="secret_password" class="form-control" placeholder="'.str_repeat("&bull;", $secret['password_length']).'" readonly/>';
            $display_secret .= $this->web_display_unlock_button('password', 2);
            $display_secret .= '          <button type="button" title="show/hide" id="show_password" class="btn btn-default glyphicon glyphicon-eye-open" onclick="secretField.revealHandler();"></button>';
            $display_secret .= $this->web_display_copy_button();
            $display_secret .= '      </label>'."\r\n";
            $display_secret .= '      <label>URL: ';
            $display_secret .= '          <input type="text" name="secret_url" class="form-control" value="'.$secret['url'].'" readonly/>';
            $display_secret .= $this->web_display_unlock_button('url', 3);
            $display_secret .= '          <button type="button" title="open" id="open" class="btn btn-default glyphicon glyphicon-new-window" onclick="window.open(\''.$secret['url'].'\');"></button>';
            $display_secret .= $this->web_display_copy_button();
            $display_secret .= '      </label>'."\r\n";
            $display_secret .= '      <label>Notes: ';
            $display_secret .= '          <textarea name="secret_notes" class="form-control" readonly>'.$secret['notes'].'</textarea>';
            $display_secret .= $this->web_display_unlock_button('notes', 4);
            $display_secret .= '      </label>'."\r\n";
            $display_secret .= "      <div id='btn_zone'>\r\n";
            $display_secret .= '          <button type="button" class="btn btn-default glyphicon glyphicon glyphicon-repeat" onclick="secretField.revert();"></button>';
            $display_secret .= '          <button type="button" class="btn btn-success glyphicon glyphicon-floppy-saved" onclick="secrets.save('.$secret['id'].');"></button>';
            $display_secret .= '          <button type="button" class="btn btn-danger glyphicon glyphicon-trash" onclick="secrets.del('.$secret['id'].');"></button>';
            $display_secret .= "      </div>\r\n";
            $display_secret .= '  </td>\r\n';
            $display_secret .= '</tr>';
        }
        return base64_encode($display_secret);
    }

    public function web_save(){
    }

    private function web_display_unlock_button($element, $node){
        return '          <button type="button" title="edit" id="edit_'.$element.'" class="btn btn-default glyphicon glyphicon-pencil" onclick="secretField.edit('.$node.');"></button>';
    }

    private function web_display_copy_button(){
        //'          <img src="" alt="copy" title="copy"/>';    //TODO - get copy code.
        return '';
    }
}