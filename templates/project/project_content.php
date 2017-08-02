<?php
/**
 * BP Portfolio- Project Content
 *
 * @package WordPress
 * @subpackage BP Portfolio
 */
?>

<?php if ( bpcp_is_accessible() ) { ?>
    <h2 class="entry-title"><?php echo bpcp_add_project_title();?></h2>
    <?php echo bpcp_project_top_menu(); ?>

    <form class="add-project-content standard-form" method="post" action="">

        <div class="bpcp-li-no-margin uploaded-images">
            <?php
            $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : '';
            if(isset($project_id) && !empty($project_id)):
                $get_all_attachment = bpcp_get_attachment_detail($project_id, 'portfolio-thumbnail', 'all');
                if(!empty($get_all_attachment)):
                    foreach($get_all_attachment as $single):
                    ?>
                        <ul class="bpcp-li-left">
                            <li><img class="display-image" alt="<?php echo $single['alt'];?>" src="<?php echo $single['src'];?>"/></li>
                            <li>
                                <input type="hidden" name="atachment_id" value="<?php echo $single['ID'];?>" />
                                <a href="#" class="delete-image"><?php _e( 'Delete', 'bp-portfolio' ); ?></a>
                            </li>
                        </ul>
                    <?php
                    endforeach;
                endif;
            endif;
            ?>
        </div>

        <div class="bpcp-buttons">
            <div class="bpcp-media-upload-title">
                <?php if(count($get_all_attachment) > 0): _e( 'Upload Another Image', 'bp-portfolio' ); else: _e( 'Upload image', 'bp-portfolio' ); endif; ?>
            </div>
            <input type="button" id="upload_image" class="upload-image" rel="201" value="Photo" />
        </div>

        <div class="bpcp-buttons">
            <input onclick="location.href='<?php echo bpcp_project_step_url('add_project'); ?>'"
                   id="back_details" class="back-button" type="button" name="back_details"
                   value="<?php _e( 'Back to Previous Step', 'bp-portfolio' ); ?>"/>
            <?php _e( '&nbsp;&nbsp;&nbsp;', 'bp-portfolio' ); ?>
            <input id="next_cover" class="next-cover" type="submit" name="next_cover"
                   value="<?php _e( 'Next Step', 'bp-portfolio' ); ?>"/>
        </div>
    </form>


<?php } ?>
