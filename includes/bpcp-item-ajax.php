<?php

/**
 * all Ajax callback
 */
function bpcp_ajax_callback()
{
    if ( isset($_POST['task']) && $_POST['task'] == 'remove_project_attachment' ) {
		
		$attachment_ID = ( isset($_POST['attachment_ID']) && !empty($_POST['attachment_ID']) ) ? $_POST['attachment_ID'] : '';
		
        if(!empty($attachment_ID)){
            $deleted_attachment = wp_delete_attachment( $attachment_ID );

        }
        exit;
    }
}
