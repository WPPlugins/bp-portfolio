<?php 
if ( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true'  ) { ?>
			<div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"> 
			<p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div><?php
} ?>

<h3><?php _e( 'Portfolio Pages', 'bp-portfolio' );?></h3>
<p><?php _e( 'Associate a WordPress Page with each Portfolio component.', 'bp-portfolio' );?></p>

<form action="options.php" method="post">

    <?php
    settings_fields('bp_portfolio_plugin_options');
    $all_portfolio_page = bp_portfolio()->option( 'all-portfolio-page' );
    $add_project_page = bp_portfolio()->option( 'add-project-page-select' );
    $bpcp_projects_enable = bp_portfolio()->setting( 'bpcp-projects-enable' );
    ?>

    <?php if($bpcp_projects_enable =='on'): ?>

    <table class="form-table"><tbody><tr><th scope="row"><?php _e( 'Projects', 'bp-portfolio');?></th><td>

    <?php
    echo wp_dropdown_pages( array(
    'name'             => 'bp_portfolio_plugin_options[all-portfolio-page]',
    'echo'             => false,
    'show_option_none' => __( '- None -', 'bp-portfolio' ),
    'selected'         => $all_portfolio_page
    ) );
    ?>
    <a href="<?php echo admin_url( add_query_arg( array( 'post_type' => 'page' ), 'post-new.php' ) );?>" class="button-secondary"><?php _e( 'New Page', 'bp-portfolio' );?></a>

    <?php if(!empty($all_portfolio_page)): ?>
    <a href="<?php echo get_the_permalink($all_portfolio_page);?>" class="button-secondary" target="_blank"><?php _e( 'View', 'bp-portfolio' );?></a>
    <?php endif; ?>

    <p class="description"><?php _e( 'Use a WordPress page to display all Projects uploaded by all users. (Optional)', 'bp-portfolio' );?></p>
    </td></tr></tbody></table>
	
    <table class="form-table"><tbody><tr><th scope="row"><?php _e( 'Add Project', 'bp-portfolio');?></th><td>

    <?php
    echo wp_dropdown_pages( array(
    'name'             => 'bp_portfolio_plugin_options[add-project-page-select]',
    'echo'             => false,
    'show_option_none' => __( '- None -', 'bp-portfolio' ),
    'selected'         => $add_project_page
    ) );
    ?>
    <a href="<?php echo admin_url( add_query_arg( array( 'post_type' => 'page' ), 'post-new.php' ) );?>" class="button-secondary"><?php _e( 'New Page', 'bp-portfolio' );?></a>

    <?php if(!empty($add_project_page)): ?>
		<a href="<?php echo get_the_permalink($add_project_page);?>" class="button-secondary" target="_blank"><?php _e( 'View', 'bp-portfolio' );?></a>
    <?php endif; ?>

    <p class="description"><?php _e( 'Use a WordPress page to display Add Project form. (Optional)<br /> If not selected, Add Project form will be displayed at <code>/members/username/portfolio</code>.', 'bp-portfolio' );?></p>
    </td></tr></tbody></table>

    <?php endif; ?>

    <?php do_action('bpcp_component_pages_fields');?>

    <p class="submit">
        <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' , 'bp-portfolio' ); ?>" />
    </p>

</form>