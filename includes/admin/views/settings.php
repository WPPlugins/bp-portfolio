<?php 
if ( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true'  ) { ?>
            <div class="updated settings-error notice is-dismissible" id="setting-error-settings_updated"> 
            <p><strong>Settings saved.</strong></p><button class="notice-dismiss" type="button"><span class="screen-reader-text">Dismiss this notice.</span></button></div><?php
} ?>

<style type="text/css">
div.pro {
    color:white;
    background:gray;
    padding:2px 5px;
    border-radius:3px;
    display:inline;
    margin-left:2px;
    font-size:12px;
}
div.pro a {
    color:white;
    text-decoration:none;
}
div.pro a:hover {
    text-decoration:underline;
}
td.disabled {
    color:gray;
}
</style>

<form action="options.php" method="post">

    <!-- Component section -->
    <h3><?php _e( 'Components', 'bp-portfolio' );?></h3>

    <?php
    settings_fields('bp_portfolio_plugin_settings');
    $bpcp_projects_enable = bp_portfolio()->setting( 'bpcp-projects-enable' );
    $checked = $bpcp_projects_enable == 'on' ? 'checked="checked"' : '';
    ?>
    <table class="form-table">
        <tbody>
        <tr>
            <td>
                <input type="checkbox" <?php echo $checked;?> name="bp_portfolio_plugin_settings[bpcp-projects-enable]" />
                <?php if ( !function_exists('bp_portfolio_pro') ) : ?>
                    <?php _e( '<b>Projects</b> &mdash; Let users create Projects to showcase their photos and artwork', 'bp-portfolio' );?>
                <?php elseif ( function_exists('bp_portfolio_pro') ) : ?>
                    <?php _e( '<b>Projects</b> &mdash; Let users create Projects to showcase their photos, artwork, audio and videos', 'bp-portfolio' );?>
                <?php endif; ?>
				<?php if ( $bpcp_projects_enable == 'on' ) : ?>
					<?php _e('<a href="'.admin_url().'edit.php?post_type=bb_project">[edit]</a>','bp-portfolio'); ?>
				<?php endif; ?>
            </td>
        </tr>
        </tbody>
    </table>
    
    <?php if ( !function_exists('bp_portfolio_pro') ) { ?>
    <table class="form-table">
        <tbody>
        <tr>
            <td class="disabled">
                <input disabled type="checkbox" name="bp_portfolio_plugin_settings[bpcp-wip-enable]" />
                <?php _e( '<b>Advanced Projects</b> &mdash; Let users add audio and videos to their Projects', 'bp-portfolio' );?> <div class="pro"><a href="http://www.buddyboss.com/product/social-portfolio/" target="_blank"><?php _e( 'PRO version', 'bp-portfolio' );?></div>
            </td>
        </tr>
        </tbody>
    </table>

    <table class="form-table">
        <tbody>
        <tr>
            <td class="disabled">
                <input disabled type="checkbox" name="bp_portfolio_plugin_settings[bpcp-wip-enable]" />
                <?php _e( '<b>Works In Progress</b> &mdash; Let users share and discuss revisions on incomplete work', 'bp-portfolio' );?> <div class="pro"><a href="http://www.buddyboss.com/product/social-portfolio/" target="_blank"><?php _e( 'PRO version', 'bp-portfolio' );?></div>
            </td>
        </tr>
        </tbody>
    </table>

    <table class="form-table">
        <tbody>
        <tr>
            <td class="disabled">
                <input disabled type="checkbox" name="bp_portfolio_plugin_settings[bpcp-collections-enable]" />
                <?php _e( '<b>Collections</b> &mdash; Let users share collections of their favorite Projects from the site', 'bp-portfolio' );?> <div class="pro"><a href="http://www.buddyboss.com/product/social-portfolio/" target="_blank"><?php _e( 'PRO version', 'bp-portfolio' );?></div>
            </td>
        </tr>
        </tbody>
    </table><?php
    } ?>

    <?php do_action('bpcp_settings_component_fields');?>
    
    <br />

    <h3><?php _e( 'Categories', 'bp-portfolio' );?></h3>
    <p><?php _e( 'You need to create at least one category in backend before they will display on frontend.', 'bp-portfolio' );?></p>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><?php _e( 'Project Category', 'bp-portfolio' );?></th>
                <td>
                    <?php 
                    $catg_enable = bp_portfolio()->setting( 'bpcp-projects-category-required' );
                    $checked = $catg_enable == 'yes' ? 'checked="checked"' : '';
                    ?>
                    <input type="checkbox" <?php echo $checked;?> name="bp_portfolio_plugin_settings[bpcp-projects-category-required]" value='yes' />
                    <?php _e( '<strong>Required</strong> &ndash; Category must be added when creating a Project', 'bp-portfolio' );?>
                    <br /><br />
                    <?php _e( 'Project category label = ', 'bp-portfolio' );?> <input type="text" name="bp_portfolio_plugin_settings[projects-category-label]" value="<?php echo bpcp_project_category_label(); ?>" />
                </td>
            </tr>
            
            <?php if( function_exists( 'bp_portfolio_pro' ) ):?>
            <tr>
                <th scope="row"><?php _e( 'WIP Category', 'bp-portfolio' );?></th>
                <td>
                    <?php 
                    $catg_enable = bp_portfolio()->setting( 'bpcp-wip-category-required' );
                    $checked = $catg_enable == 'yes' ? 'checked="checked"' : '';
                    ?>
                    <input type="checkbox" <?php echo $checked;?> name="bp_portfolio_plugin_settings[bpcp-wip-category-required]" value='yes' />
                    <?php _e( '<strong>Required</strong> &ndash; Category must be added when creating a WIP', 'bp-portfolio' );?>
                    <br /><br />
                    <?php _e( 'WIP category label = ', 'bp-portfolio' );?> <input type="text" name="bp_portfolio_plugin_settings[wip-category-label]" value="<?php echo bpcp_pro_wip_category_label(); ?>" />
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Settings section -->
    <?php do_action('bpcp_settings_misc_fields');?>


    <p class="submit">
        <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' , 'bp-portfolio' ); ?>" />
    </p>

</form>
