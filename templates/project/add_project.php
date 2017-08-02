<?php
/**
 * BP Portfolio- Add Project
 *
 * @package WordPress
 * @subpackage BP Portfolio
 */
?>

<?php if( bpcp_is_accessible() ){ ?>
    <h2 class="entry-title"><?php echo bpcp_add_project_title();?></h2>
    <?php echo bpcp_project_top_menu();?>

    <?php
    $project_id = ( isset($_GET['project_id']) && !empty($_GET['project_id']) ) ? $_GET['project_id'] : '';
    $project_detail = bpcp_get_post_detail($project_id);
    $all_tags = bpcp_custom_taxonomy_terms($project_id, 'bb_project_tag');
    ?>

    <form class="add-project-details standard-form new"  method="post" action="">
        <div>
            <label for="project_title"><?php _e( 'Title (required)', 'bp-portfolio' ); ?></label>
            <div><input id="project_title" name="project_title" class="project-title" type="text" value="<?php if(isset($project_detail->post_title)) { echo $project_detail->post_title; }?>"/></div>
        </div>

        <div>
            <label for="project_description"><?php _e( 'Description', 'bp-portfolio' ); ?></label>
            <div><textarea id="project_description" name="project_description" class="project-description"><?php if(isset($project_detail->post_content)) { echo $project_detail->post_content; }?></textarea></div>
        </div>

        <div>
            <label for="project_tags"><?php _e( 'Tags', 'bp-portfolio' ); ?></label>
            <div><input id="project_tags" name="project_tags" class="project-tags" type="text" value="<?php if(isset($all_tags) && !empty($all_tags)) { echo implode(', ', $all_tags); }?>"/></div>
        </div>
        
        <?php
        $args = array(
            'echo'  => 0,
            'taxonomy'          => 'bb_project_category',
            'hide_empty'        => 0,
            'option_none_value' => '',
            'hierarchical'      => 1,
            'orderby'           => 'name',
            'name'              => 'project_category[]',
            'class'             => 'bp-category-multiselect',
            'id'             => 'project-category',
            'value_field'       => 'slug',
            'selected'          => bpcp_custom_taxonomy_single_term_slug( $project_id, 'bb_project_category' ),
            'walker'            => new Walker_CategoryDropdown_Projects
        );                     
        $select_cats = wp_dropdown_categories($args);  
        if(!empty($select_cats)):
        ?>
        <div>
            <label for="project_category"><?php echo bpcp_project_category_label(); if( bp_portfolio()->setting( 'bpcp-projects-category-required' )=='yes' ){ echo " (required) ";}?></label>
            <div>
                <?php 
                $select_cats = str_replace( 'id=', 'multiple="multiple" id=', $select_cats );
                echo $select_cats;
                ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="bpcp-buttons">
            <input id="create_project" class="create-project" type="submit" name="create_project" value="<?php if(!empty($project_id)) { _e( 'Update Project', 'bp-portfolio' ); } else { _e( 'Add Project and Continue', 'bp-portfolio' ); } ?>" />
        </div>
    </form>


<?php } ?>
