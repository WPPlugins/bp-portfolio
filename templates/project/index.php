<?php
/**
 * BP Portfolio- Project Index
 *
 * @package WordPress
 * @subpackage BP Portfolio
 */
?>

<h2 class="entry-title">
    <span><?php _e( 'Projects', 'bp-portfolio' ) ?></span>
    <?php if(bp_displayed_user_id() == bp_loggedin_user_id()){ ?>
        <?php 
        //$add_project_link = wp_nonce_url( bpcp_portfolio_component_root() . '?bpcp_action=add_project', 'bpcp_add_project' ); 
        $base_link = bpcp_portfolio_component_root();
        if( bpcp_add_project_location()=='wp' ){
            $base_link = get_permalink( bp_portfolio()->option('add-project-page-select') );
        }
        $add_project_link = wp_nonce_url( add_query_arg( array( 'bpcp_action' => 'add_project' ), $base_link ), 'bpcp_add_project' );
        ?>
        <input onclick="location.href='<?php echo $add_project_link;?>'" type="button" value="<?php _e( 'Add Project', 'bp-portfolio' );?>" />
    <?php } ?>
</h2>

<?php
$meta_query = bpcp_project_meta_args('project_visibility');
$paged = bpcp_paged_query_var();
$pp_args = array(
    'post_type' => 'bb_project',
    'meta_query' => array($meta_query),
    'author' => bp_displayed_user_id(),
    'posts_per_page' => 20,
    'paged' => $paged
);
$the_query = new WP_Query($pp_args);
if ($the_query->have_posts()): ?>
<div class="bb-display-projects">
    <ul class="bb-project-items">
        <?php while ($the_query->have_posts()):
                $the_query->the_post();
                $default_img_url = BP_PORTFOLIO_PLUGIN_URL.'assets/images/project-placeholder.png';
                $get_project_thumb = bpcp_featured_image_detail(get_the_ID(), 'portfolio-thumbnail', $default_img_url);
                $project_thumb_url = ( isset($get_project_thumb['src']) && !empty($get_project_thumb['src']) ) ? $get_project_thumb['src'] : '';
                $all_tags = bpcp_custom_taxonomy_terms(get_the_ID(), 'bb_project_tag', true);
        ?>
            <li class="bb-project-item">
               <div class="bp-inner-wrap">
                    <div class="bb-project-thumb">
                        <a href="<?php the_permalink();?>"><img src="<?php echo $project_thumb_url;?>" alt="<?php the_title();?>" /></a>
                    </div>
                    <div class="bb-project-title"><h4><a href="<?php the_permalink();?>"><?php the_title();?></a></h4></div>
                    <div class="bb-project-author"><?php _e( 'by', 'bp-portfolio' );?> <a href="<?php echo bp_core_get_user_domain(get_the_author_meta('ID'));?>"><?php the_author();?></a></div>
                    <div class="bb-project-tags"><?php if(isset($all_tags) && !empty($all_tags)) { echo 'tags: '. implode(', ', $all_tags); }?></div>
                </div>
            </li>
        <?php endwhile;?>
    </ul>
</div>
<?php else: ?>
    <div id="message" class="bp-template-notice">
        <?php if(bp_displayed_user_id() == bp_loggedin_user_id()){ ?>
            <p>
                <?php _e( 'You have not added a project yet.', 'bp-portfolio' );?>
                <a href="<?php echo $add_project_link;?>"><?php _e( 'Add a project', 'bp-portfolio' );?></a>
            </p>
        <?php } else { ?>
            <p><?php _e( 'Sorry, there were no projects found.', 'bp-portfolio' );?></p>
        <?php } ?>
    </div>
<?php endif;
// pagination code
bpcp_num_pagination($the_query);
// Restore original Query & Post Data
wp_reset_query();
wp_reset_postdata();
?>
