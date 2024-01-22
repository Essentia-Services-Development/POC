<?php

class PeepSoAdminSendy extends PeepSoAjaxCallback
{
    public function add_user(PeepSoAjaxResponse $resp)
    {
        if (!PeepSo::is_admin()) {
            $resp->success(FALSE);
            $resp->error(__('Insufficient permissions.', 'peepso-core'));
            return;
        }
        
        $sendy_list_id      = $this->_input->value('sendy_list_id','',false);// SQL safe, admin only
        $sendy_url          = $this->_input->value('sendy_url','',false);// SQL safe, admin only
        $sendy_name         = $this->_input->value('sendy_name','',false);// SQL safe, admin only
        $sendy_last_name    = $this->_input->value('sendy_last_name','',false);// SQL safe, admin only
        $sendy_email        = $this->_input->value('sendy_email','',false);// SQL safe, admin only

        $response= wp_remote_post( $sendy_url, array( 'body' => array( 'name' => $sendy_name, 'last_name' => $sendy_last_name, 'email' => $sendy_email, 'list' => $sendy_list_id,'boolean' => 'true') ) );

        $resp->success( FALSE );

        if( is_wp_error( $response ) ) {
            $resp->error( $response->get_error_message() );
        } else {

            if('1' == $response['body']) {
                $resp->success(TRUE);
            } else {
                $resp->error( $response['body']  );
            }
        }
    }
}
// EOF