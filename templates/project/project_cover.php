<?php
/**
 * BP Portfolio- Project Cover
 *
 * @package WordPress
 * @subpackage BP Portfolio
 */
?>

<?php if( bpcp_is_accessible() ){ ?>
    <h2 class="entry-title"><?php echo bpcp_add_project_title();?></h2>
    <?php echo bpcp_project_top_menu();?>

    <?php
    $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : '';
    $featured_img_url = bpcp_featured_img_url($project_id);
    $featured_img_url = !empty($featured_img_url) ? $featured_img_url : BP_PORTFOLIO_PLUGIN_URL.'assets/images/project-placeholder.png';
    ?>

    <form class="add-project-cover standard-form"  method="post" action="">
        <div class="bpcp-li-no-margin bpcp-distance-bottom">
            <ul class="bpcp-li-left">
                <li><input type="hidden" class="chosen-cover" name="chosen_cover" value="" />
                    <img id="display_cover_image" class="display-image" src="<?php echo $featured_img_url;?>"/>
                </li>
                <li>
                    <p class="bpcp-distance"><?php _e( 'Upload an image to use as a cover photo for this project.<br/> The image will be shown on the main project page, and in search results.', 'bp-portfolio' ); ?></p>
                    <div>
                        <input type="button" id="upload_image_lib" class="upload-image-lib" rel="201" value="<?php _e( 'Library', 'bp-portfolio' ); ?>" />
                        <input type="button" id="upload_cover" class="upload-cover" rel="201" value="<?php _e( 'Upload Image', 'bp-portfolio' ); ?>" />
                    </div>
                </li>
            </ul>
        </div>

        <div class="bpcp-buttons">
            <input onclick="location.href='<?php echo bpcp_project_step_url('project_content');?>'" id="back_content" class="back-button" type="button" name="back_content" value="<?php _e( 'Back to Previous Step', 'bp-portfolio' ); ?>" />
            <?php _e( '&nbsp;&nbsp;&nbsp;', 'bp-portfolio' ); ?>
            <input id="next_settings" class="next-settings" type="submit" name="next_settings" value="<?php _e( 'Next Step', 'bp-portfolio' ); ?>" />
        </div>
    </form>


<?php } ?>


