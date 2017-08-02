<?php
add_action( 'bp_portfolio_print_screen_support', 'bp_portfolio_print_screen_support' );

function bp_portfolio_print_screen_support() {
	
	if ( function_exists('bp_portfolio_pro') ) {
		include_once( bp_portfolio_pro()->includes_dir . '/help-support.php' );
	} else {
		include_once( bp_portfolio()->includes_dir . '/help-support.php' );
	}
}