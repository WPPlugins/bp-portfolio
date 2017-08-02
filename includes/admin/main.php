<div class="wrap">
	<?php
	if ( function_exists('bp_portfolio_pro') ) {
		$page_title = __( 'BP Portfolio PRO' , 'bp-portfolio' );
	} else {
		$page_title = __( 'BP Portfolio' , 'bp-portfolio' );
	}
	?>
    <h2><?php echo $page_title; ?></h2>
	<?php 
	$controller = BP_Portfolio_Admin::instance();
	$controller->update_settings_content();
	?>
	<div class='bboss_dashboards_admin'>
		<h2 class="nav-tab-wrapper">
			<?php $controller->print_screen_tabs();?>
		</h2>
		<div class='tabs-panel'>
			<?php $controller->print_screen_content();?>
		</div>
	</div><!-- .bboss_dashboards_admin -->

</div>