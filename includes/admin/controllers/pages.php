<?php 
add_action( 'bp_portfolio_print_screen_pages', 'bp_portfolio_print_screen_pages' );

function bp_portfolio_print_screen_pages(){
	include_once( bp_portfolio()->includes_dir . '/admin/views/pages.php' );
}