<?php

function bpcp_create_project_save()
{
    if (bpcp_is_accessible() ) {

        $portfolio_projects_slug = bpcp_portfolio_subnav_slug();

        // for project delete
        if (bpcp_is_portfolio_component() &&
            bp_is_current_action($portfolio_projects_slug) &&
            !empty($_POST['target_project']) &&
            ($_POST['delete_yes'] == 'Delete')
        ) {
            $target_project = $_POST['target_project'];
            $deleted_project = wp_delete_post($target_project);
            if($deleted_project){
                bp_core_add_message(__( 'Project deleted successfully', 'bp-portfolio' ), 'success');
            }
        }

        // for first step
        if ( bpcp_is_add_project_routine() &&
            isset($_POST['create_project']) &&
            !empty($_POST['create_project'])
        ) {

            $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : '';

            // while add a existing project
            if (!empty($project_id)) {
                $edit_project = array(
                    'ID'           => $project_id,
                );
                if(!empty($_POST['project_title'])){
                    $project_title = sanitize_text_field($_POST['project_title']);
                    $edit_project['post_title'] = $project_title;
                }
                
                $project_description = wp_kses_post($_POST['project_description']);
                $edit_project['post_content'] = $project_description;
                
                wp_update_post($edit_project);
                
                // edit taxonomy term to this project
                    $project_tags = sanitize_text_field($_POST['project_tags']);
                    $project_tags = explode(',', $project_tags);
                    wp_set_object_terms($project_id, $project_tags, 'bb_project_tag');
                
                // edit category term to this project
                    $project_tags = $_POST['project_category'];
                    wp_set_object_terms($project_id, $project_tags, 'bb_project_category');
                
                //callout before redirecting
                do_action( 'bpcp_after_project_udpated', $project_id, 'create_project' );
                
                // now redirect to second step
                $content_link = bpcp_project_step_url('project_content', $project_id);
                bp_core_redirect($content_link);
                exit();
            } else {
                // while add a new project
                if (!empty($_POST['project_title'])) {
                    $author_id = bp_loggedin_user_id();
                    $project_title = sanitize_text_field($_POST['project_title']);
                    $project_description = wp_kses_post($_POST['project_description']);

                    // prepare project tags
                    $project_tags = '';
                    $project_tags = sanitize_text_field($_POST['project_tags']);
                    $project_tags = explode(',', $project_tags);

                    $project_args = array(
                        'post_title' => $project_title,
                        'post_content' => $project_description,
                        'post_status' => 'publish',
                        'post_author' => $author_id,
                        'post_type' => 'bb_project',
                    );
                    $project_id = wp_insert_post($project_args);
                    if (isset($project_id) && !empty($project_id)) {
                        // add taxonomy term to this project
                        wp_set_object_terms($project_id, $project_tags, 'bb_project_tag');
                        
                        // edit category term to this project
                        if(!empty($_POST['project_category'])){
                            $project_tags = $_POST['project_category'];
                            wp_set_object_terms($project_id, $project_tags, 'bb_project_category');
                        }
                        
                        //callout before redirecting
                        do_action( 'bpcp_after_project_udpated', $project_id, 'create_project' );
                        
                        // now redirect to second step
                        $content_link = bpcp_project_step_url('project_content', $project_id);
                        bp_core_redirect($content_link);
                        exit();
                    } else {
                        bp_core_add_message(__( 'Some error happen while creating post. Please try again.', 'bp-portfolio' ), 'error');
                    }

                } else {
                    bp_core_add_message(__( 'Please insert project title', 'bp-portfolio' ), 'error');
                }
            }


        }

        // for second steps
        if (bpcp_is_add_project_routine() &&
            isset($_POST['next_cover']) &&
            !empty($_POST['next_cover'])
        ) {
            $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : '';
            if (!empty($project_id)) {
                // add the image as attachment for this project

                if ( isset( $_POST['chosen_file'] ) ) {
                    $chosen_files = $_POST['chosen_file'];
                    foreach($chosen_files as $single_file){
                        bpcp_insert_attachment_by_ID($single_file, $project_id);
                    }
                }

                //callout before redirecting
                do_action( 'bpcp_after_project_udpated', $project_id, 'next_cover' );
                
                // now redirect to third step
                $cover_link = bpcp_project_step_url('project_cover');
                bp_core_redirect($cover_link);
                exit();
            } else {
                bp_core_add_message(__( 'Some error happen while creating post. Please try again.', 'bp-portfolio' ), 'error');
            }
        }

        // for third steps
        if (bpcp_is_add_project_routine() &&
            isset($_POST['next_settings']) &&
            !empty($_POST['next_settings'])
        ) {

            $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : '';
            if (!empty($project_id)) {
                // add cover image as post thumbnail/feature image
                $get_attachment_id = ( isset($_POST['chosen_cover']) && !empty($_POST['chosen_cover']) ) ? $_POST['chosen_cover'] : '';
                if(!empty($get_attachment_id)){
                    set_post_thumbnail( $project_id, $get_attachment_id );
                }
                
                //callout before redirecting
                do_action( 'bpcp_after_project_udpated', $project_id, 'next_settings' );
                
                // now redirect to fourth step
                $settings_link = bpcp_project_step_url('project_settings');
                bp_core_redirect($settings_link);
                exit();
            } else {
                bp_core_add_message(__( 'Some error happen while creating post. Please try again.', 'bp-portfolio' ), 'error');
            }
        }

        // for fourth steps
        if (bpcp_is_add_project_routine() &&
            isset($_POST['project_finish']) &&
            !empty($_POST['project_finish'])
        ) {

            $project_id = isset($_GET['project_id']) ? $_GET['project_id'] : '';
            if (!empty($project_id)) {
                // set comment settings & visibility
                $project_visibility = ( isset($_POST['project_visibility']) && !empty($_POST['project_visibility']) ) ? $_POST['project_visibility'] : 'public';
                $project_comment = ( isset($_POST['project_comment']) && $_POST['project_comment'] == 'on' ) ? 'open' : 'closed';
                $edit_project = array(
                    'ID'           => $project_id,
                    'comment_status' => $project_comment,
                );
                $finished_id = wp_update_post($edit_project);

                // project visibility update
                update_post_meta($project_id, 'project_visibility', $project_visibility);

                if($finished_id){
                    $completed_flag = get_post_meta($project_id, 'entry_status', true);
                    if($completed_flag!='completed'){
                        // add activity
                        $get_project_thumb = bpcp_featured_image_detail($project_id, array(600, 400));
                        $project_thumb_url = ( isset($get_project_thumb['src']) && !empty($get_project_thumb['src']) ) ? $get_project_thumb['src'] : '';
                        $activity_content = !empty($project_thumb_url) ? '<img alt="'.get_the_title($project_id).'" src="'.$project_thumb_url.'" />' : '';
                        
                        $get_project_full_img = bpcp_featured_image_detail($project_id, 'full');
                        if( !empty( $get_project_full_img ) ){
                            $project_img_full_url = ( isset($get_project_full_img['src']) && !empty($get_project_full_img['src']) ) ? $get_project_full_img['src'] : '';
                            if( !empty( $project_img_full_url ) ){
                                $activity_content = "<a href='". get_the_permalink($project_id) ."'>". $activity_content ."</a>";
                            }
                        }
                        
                        $user_link = bp_core_get_userlink( bp_loggedin_user_id() );
                        $user_portfolio_link = '<a href="'.get_the_permalink($project_id).'">'.get_the_title($project_id).'</a>';
                        global $bp;
                        bpcp_record_activity( array(
                            'type' => 'activity_update',
                            'action' => apply_filters( 'bpcp_new_project_activity_action',
                                sprintf( __( '%s posted a new project, %s', 'bp-portfolio' ),
                                $user_link, $user_portfolio_link ), $user_link, $user_portfolio_link),
                            'content' => $activity_content,
                            'item_id' => bp_loggedin_user_id(),
                        ) );
                        // set completed flag
                        update_post_meta($finished_id, 'entry_status', 'completed');
                    }
                }
                
                //callout before redirecting
                do_action( 'bpcp_after_project_udpated', $project_id, 'project_finish' );
                
                // now redirect to component root page
                $base_link = bpcp_portfolio_component_root();
                bp_core_redirect($base_link);
                exit();
            } else {
                bp_core_add_message(__( 'Some error happen while creating post. Please try again.', 'bp-portfolio' ), 'error');
            }
        }


    }
}

add_action('bp_actions', 'bpcp_create_project_save');

/*
 * Add Link to Old added WIP.
 **/
function bpcp_activity_content_filter( $content ) {
    global $activities_template;
    $curr_id = isset( $activities_template->current_activity ) ? $activities_template->current_activity : false;

    if($curr_id === false) {
        return $content;
    }

    $current_activity = $activities_template->activities[$curr_id];
    $activity_primary_link = $current_activity->primary_link; //activity primary link

    //check if its wip one.
    if( ! empty( $activity_primary_link ) && strpos("bb_wip/",$current_activity->primary_link) !== false) {
        
        //check if content contain any link if not add one.
        if(strpos("bb_wip/",$content) === false AND strpos("<img",$content) !== false) {
            return '<a href="'.$current_activity->primary_link.'">'.$content.'</a>';
        }
        
    }
 
    return $content;
}

add_filter("bp_get_activity_content_body","bpcp_activity_content_filter",99);
