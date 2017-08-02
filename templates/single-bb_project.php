<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage BP Portfolio
 */
global $post;
?>

<!-- left part -->
<div class="project-single-left">

    <?php if ( $post->post_author == bp_loggedin_user_id() ): ?>
        <div class="project-meta">
            <button class="button" onclick="window.location='<?php echo bpcp_project_edit_link( get_the_ID() ); ?>';"><?php _e( 'Edit Project', 'bp-portfolio' ); ?></button>   
            <button class="bpcp-modal button" data-mfp-src="#delete_project" title="Delete Project"><?php _e( 'Delete Project', 'bp-portfolio' ); ?></button>

            <div id="delete_project" class="bbcp-white-popup-block mfp-hide">
                <h3 class="bbpc-delete-title">Delete Project</h3>
                <form class="standard-form"  method="post" action="<?php echo bpcp_portfolio_component_root(); ?>">
                    <div class="delete_confirm">Do you really want to delete this project?</div>
                    <input name="target_project" type="hidden" value="<?php the_ID(); ?>" />
                    <input type="submit" name="delete_yes" id="delete_yes" class="delete-yes" value="Delete" />
                    <input type="button" name="bpcp_modal_cancel" id="bpcp_modal_cancel" class="bpcp-modal-cancel" value="Cancel" />
                </form>
            </div>
        </div>
    <?php endif; ?>

    <div class="project-content">
        <div class="project-description">
            <?php echo $post->post_content; ?>
        </div>

        <?php
        $get_all_attachment = bpcp_get_attachment_detail( $post->ID, 'large', 'all' );
        if ( !empty( $get_all_attachment ) ):
            ?>

            <div class="bpcp_project_photo">

                <?php
                foreach ( $get_all_attachment as $single ):
                    ?>
                    <a href="<?php echo $single[ 'full_src' ]; ?>"><img class="entry-post-thumbnail" alt="<?php echo $single[ 'alt' ]; ?>" src="<?php echo $single[ 'src' ]; ?>"/></a>
                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</div>

<!-- right part -->
<div class="project-single-right">

    <div class="project-single-author">
        <h3><?php _e( 'Project by', 'bp-portfolio' ); ?></h3>
        <p>
            <?php
            $avatar_args	 = array(
                'item_id'	 => get_the_author_meta( 'ID' ),
                'type'		 => 'thumb',
                'width'		 => 100,
                'height'	 => 100,
                'class'		 => 'author-avatar',
                'html'		 => true
            );
            $author_avatar	 = bp_core_fetch_avatar( $avatar_args );
            ?>
            <a class="author-avatar-link" href="<?php echo bpcp_user_domain_portfolio_slug( get_the_author_meta( 'ID' ) ); ?>"><?php echo $author_avatar; ?></a>
            <span><a href="<?php echo bpcp_user_domain_portfolio_slug( get_the_author_meta( 'ID' ) ); ?>"><?php the_author(); ?></a></span>
        </p>
    </div>

    <?php $all_tags		 = bpcp_custom_taxonomy_terms( get_the_ID(), 'bb_project_tag', true ); ?>
    <?php if ( isset( $all_tags ) && !empty( $all_tags ) ): ?>
        <div class="project-single-tags">
            <h3><?php _e( 'Project Tags', 'bp-portfolio' ); ?></h3>
            <p><?php echo implode( ' ', $all_tags ); ?></p>
        </div>
    <?php endif; ?>

</div>

<div class="clearfix"></div>