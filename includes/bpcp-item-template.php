<?php
/**
 * load the template file by looking into childtheme, parent theme, plugin's template folder, in that order.
 * looks for bp-portfolio/$template.php inside child/parent themes.
 *
 * @param string $template name of the template file, without '.php'
 */
function bpcp_portfolio_load_template($template){
    $template .= '.php';
    if(file_exists(STYLESHEETPATH.'/bp-portfolio/'.$template))
        include_once(STYLESHEETPATH.'/bp-portfolio/'.$template);
    else if(file_exists(TEMPLATEPATH.'/bp-portfolio/'.$template))
        include_once (TEMPLATEPATH.'/bp-portfolio/'.$template);
    else{
        $template_dir = apply_filters('bpcp_templates_dir_filter', bp_portfolio()->templates_dir);
        include_once trailingslashit($template_dir) . $template;
    }
}

// add custom post types templates
function bpcp_content_single_posts( $content ){
    if( !is_main_query() )
        return $content;

    global $post;
    // custom post types
    $post_type_array = array('bb_project');
    $post_type_array = apply_filters('bpcp_post_types_filter', $post_type_array);
    if( in_array($post->post_type, $post_type_array) && is_single($post) ){
        foreach ($post_type_array as $post_type_single) {
            if (is_singular($post_type_single)) {
                $template_name = 'single-'.$post->post_type;
                $template_content = bpcp_buffer_template_part( $template_name, false );
                $content = apply_filters('bpcp_single_project_content', $template_content, $content);
            }
        }
    }
    
    return $content;
}

function bpcp_load_single_post_page() {
    add_filter( 'the_content', 'bpcp_content_single_posts');
}
add_action('wp_head','bpcp_load_single_post_page');

function bpcp_comment_reform ($arg) {
    global $post;
    if(is_singular('bb_project')) {
        $arg['title_reply'] = __('Discuss this Project', 'bp-portfolio');
    }

    return $arg;
}
add_filter('comment_form_defaults','bpcp_comment_reform');


function bpcp_content_global_pages( $content ){
    if( !is_main_query() )
        return $content;

    if( bp_portfolio()->setting( 'bpcp-projects-enable' ) == 'on' &&
        bp_portfolio()->option('all-portfolio-page') &&
        is_page( bp_portfolio()->option('all-portfolio-page') )
    ){
        $content = wpautop(apply_filters('bpc_global_projects_content', $content)) . bpcp_buffer_template_part( 'global-portfolio', false );
    }
    return $content;
}

//Loaded after wp_head for yoast seo compatibility
function bpcp_load_global_page_query() {
    add_filter( 'the_content', 'bpcp_content_global_pages' );
}
add_action('wp_head','bpcp_load_global_page_query');


function bpcp_buffer_template_part( $template, $echo=true ){
    ob_start();

    bpcp_portfolio_load_template( $template );
    // Get the output buffer contents
    $output = ob_get_clean();

    // Echo or return the output buffer contents
    if ( true === $echo ) {
        echo $output;
    } else {
        return $output;
    }
}

//Loaded after wp_head for yoast seo compatibility
function bpcp_load_add_project_page() {
    add_filter( 'the_content', 'bpcp_content_add_project_page' );
}
add_action('wp_head','bpcp_load_add_project_page');

function bpcp_content_add_project_page( $content ){
    if( !is_main_query() )
        return $content;
    
    global $bp_portfolio_options;
    if( bp_portfolio()->option('add-project-page-select') ){
        if( is_page( bp_portfolio()->option('add-project-page-select') ) ){
            ob_start();
            
            echo "<div id='buddypress'><!-- to automatically apply all css rules -->";
            $get_action = ( isset($_GET['bpcp_action']) && !empty($_GET['bpcp_action']) ) ? $_GET['bpcp_action'] : '';
            switch($get_action){
                case 'project_content':
                    add_action('wp_enqueue_scripts', 'bpcp_media_upload_scripts');
                    bpcp_template_project_content();
                    break;

                case 'project_cover':
                    add_action('wp_enqueue_scripts', 'bpcp_media_upload_scripts');
                    bpcp_template_project_cover();
                    break;

                case 'project_settings':
                    bpcp_template_project_settings();
                    break;

                case 'add_project':
                default:
                    bpcp_template_add_project();
                    break;
            }
            echo "</div><!-- #buddypress -->";
            
            $content .= ob_get_clean();
        }
    }
    
    return $content;
}

add_filter( 'body_class', 'bpcp_add_project_page_body_class' );
function bpcp_add_project_page_body_class( $classes ){
    if( bp_portfolio()->option('add-project-page-select') ){
        if( is_page( bp_portfolio()->option('add-project-page-select') ) ){
            $classes[] = 'bpcp_add_project';
        }
    }
    return $classes;
}

function bpcp_check_single_project_access() {
    
    if( 'bb_project' != get_post_type() ) {
        return;
    }
    
    $project_id = get_the_ID();
    $project_visibility = get_post_meta($project_id, 'project_visibility', true);
    $post_author_id = get_post_field( 'post_author', $project_id );
    
    if ( 'public' != $project_visibility ) {
        if ( !is_user_logged_in() || ('private' == $project_visibility && $post_author_id != bp_loggedin_user_id() ) ) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
        }
    }
    
}
add_action('wp','bpcp_check_single_project_access');

function bpcp_check_single_wip_access() {
    
    if( 'bb_wip' != get_post_type() ) {
        return;
    }
    
    $wip_id = get_the_ID();
    $wip_visibility = get_post_meta($wip_id, 'wip_visibility', true);
    $post_author_id = get_post_field( 'post_author', $wip_id );
    $is_friend = friends_check_friendship_status($post_author_id, bp_loggedin_user_id());
    
    if ( 'public' != $wip_visibility) {
        if ( !is_user_logged_in() || ( $post_author_id != bp_loggedin_user_id() && $is_friend != 'is_friend' )) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
        }
    }
    
}
add_action('wp','bpcp_check_single_wip_access');

function bpcp_check_single_collection_access() {
    
    if( 'bb_collection' != get_post_type() ) {
        return;
    }
    
    $collection_id = get_the_ID();
    $collection_visibility = get_post_meta($collection_id, 'collection_visibility', true);
    $post_author_id = get_post_field( 'post_author', $collection_id );
    
    if ( 'public' != $collection_visibility ) {
        if ( !is_user_logged_in() || ('private' == $collection_visibility && $post_author_id != bp_loggedin_user_id() ) ) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
        }
    }
    
}
add_action('wp','bpcp_check_single_collection_access');