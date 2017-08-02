<?php
add_action( 'bp_portfolio_print_screen_settings', 'bp_portfolio_print_screen_settings' );

function bp_portfolio_print_screen_settings(){
    include_once( bp_portfolio()->includes_dir . '/admin/views/settings.php' );
}