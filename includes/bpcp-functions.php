<?php
/** portfolio slug can be changed from here
 * @return string
 */
function bpcp_portfolio_slug()
{
    return __( 'portfolio', 'bp-portfolio' );
}

/** portfolio name can be changed from here
 * @return string
 */
function bpcp_portfolio_name()
{
    return __( 'Portfolio', 'bp-portfolio' );
}

/** get includes dir
 * @return string
 */
function bpcp_portfolio_includes_dir()
{
    return bp_portfolio()->includes_dir;
}

/**
 * get user domain including portfolio slug link
 * @param $author_id
 * @return string
 */
function bpcp_user_domain_portfolio_slug($author_id){
    return bp_core_get_user_domain($author_id).bpcp_portfolio_slug();
}

/** portfolio subnav name can be changed from here
 * @return string
 */
function bpcp_portfolio_subnav_name()
{
    return __( 'Projects', 'bp-portfolio' );
}

/** portfolio subnav slug can be changed from here
 * @return string
 */
function bpcp_portfolio_subnav_slug()
{
    return __( 'projects', 'bp-portfolio' );
}

/**
 * Get all/one attachment image url array of a post
 * @param int $post_ID
 * @param string /array $thumb_size
 * @param string $num
 * @return array
 */
function bpcp_get_attachment_detail($post_ID, $thumb_size, $num = 'one')
{
    if (empty($post_ID)) {
        return array();
    }

    $args = array(
        'order' => 'ASC',
        'orderby' => 'menu_order',
        'post_type' => 'attachment',
        'post_parent' => $post_ID,
        'post_mime_type' => 'image',
        'post_status' => null,
        'numberposts' => -1,
    );
    $attachments = get_posts($args);

    if ($attachments) {
        $i = 0;
        foreach ($attachments as $attachment) {
            $image_attributes_detail = wp_get_attachment_image_src($attachment->ID, $thumb_size);
            $image_list[$i]['full_src'] = wp_get_attachment_url( $attachment->ID );
            $image_list[$i]['src'] = $image_attributes_detail[0];
            $image_list[$i]['ID'] = $attachment->ID;
            $image_list[$i]['title'] = $attachment->post_title;
            $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);
            $image_list[$i]['alt'] = !empty($alt_text) ? $alt_text : $image_list[$i]['title'];
            $image_list[$i]['caption'] = $attachment->post_excerpt;
            $image_list[$i]['description'] = $attachment->post_content;
            $i++;
        }
    }

    if ($num == 'one' && isset($image_list[0])) {
        $image_urls = $image_list[0];
    }
    if ($num == 'all' && isset($image_list)) {
        $image_urls = $image_list;
    }
    return !empty($image_list[0]['src']) ? $image_urls : array();
}

/**
 * @param int $post_id
 * @param string $image_size_name
 * @param string $blank_image_src
 * @return array
 */
function bpcp_featured_image_detail($post_id, $image_size_name, $blank_image_src = '')
{
    $featured_image = array();
    $blank_image = array();
    // get fetured image
    $featured_image_ID = get_post_thumbnail_id($post_id);
    if(!empty($featured_image_ID)){
        $featured_image_detail = get_post($featured_image_ID);
    }
    if( isset($featured_image_detail->ID) && !empty($featured_image_detail->ID) ){
        $featured_image_url = wp_get_attachment_image_src($featured_image_detail->ID, $image_size_name);
    }

    if( isset($featured_image_url[0]) && !empty($featured_image_url[0]) ){
        $featured_image['src'] = $featured_image_url[0];
        $featured_image['title'] = $featured_image_detail->post_title;
        $alt_text = get_post_meta($featured_image_detail->ID, '_wp_attachment_image_alt', true);
        $featured_image['alt'] = !empty($alt_text) ? $alt_text : $featured_image['title'];
        $featured_image['caption'] = $featured_image_detail->post_excerpt;
        $featured_image['description'] = $featured_image_detail->post_content;
    }

    if (!empty($blank_image_src)) {
        $blank_image = array(
            'src' => $blank_image_src,
            'alt' => __( 'Default Image', 'bp-portfolio' )
        );
        $featured_image = ( isset($featured_image['src']) && !empty($featured_image['src']) ) ? $featured_image : $blank_image;
    }

    return $featured_image;
}

/**
 * add required css js for media upload
 */
function bpcp_media_upload_scripts()
{
    if (is_admin()) {
        return;
    }
    if (function_exists('wp_enqueue_media')) {
        wp_enqueue_media();
    } else {
        wp_enqueue_style('thickbox');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
    }
}

function bpcp_insert_attachment($fileurl, $parent_post_id)
{
    if (empty($fileurl) || empty($parent_post_id)) return;

    // Check the type of file. We'll use this as the 'post_mime_type'.
    $filename = realpath(str_replace(get_bloginfo('url'), '.', $fileurl));
    $filetype = wp_check_filetype(basename($filename), null);

    // Get the path to the upload directory.
    $wp_upload_dir = wp_upload_dir();

    // Prepare an array of post data for the attachment.
    $attachment = array(
        'guid' => $wp_upload_dir['url'] . '/' . basename($filename),
        'post_mime_type' => $filetype['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    // Insert the attachment.
    $attach_id = wp_insert_attachment($attachment, $filename, $parent_post_id);

    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
    wp_update_attachment_metadata($attach_id, $attach_data);
}

function bpcp_insert_attachment_by_ID($attach_id, $parent_post_id)
{
    if (empty($attach_id) || empty($parent_post_id)) return;

    $edit_project = array(
        'ID' => $attach_id,
    );
    $edit_project['post_parent'] = $parent_post_id;
    wp_update_post($edit_project);
}

function bpcp_get_attachment_id_from_url($attachment_url) {

    global $wpdb;
    $attachment_id = false;

    // If there is no url, return.
    if ( '' == $attachment_url )
        return;

    // Get the upload directory paths
    $upload_dir_paths = wp_upload_dir();

    // Make sure the upload path base directory exists in the attachment URL, to verify that we're working with a media library image
    if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {

        // If this is the URL of an auto-generated thumbnail, get the URL of the original image
        $attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );

        // Remove the upload path base directory from the attachment URL
        $attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );

        // Finally, run a custom database query to get the attachment ID from the modified attachment URL
        $attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );

    }

    return $attachment_id;
}


function bpcp_featured_img_url($post_id){
    $get_featured_img_url = '';
    $get_featured_img_id = get_post_thumbnail_id($post_id);
    if(!empty($get_featured_img_id)){
        $get_featured_img_url = wp_get_attachment_image_src($get_featured_img_id, 'medium');
        $get_featured_img_url = isset($get_featured_img_url[0]) ? $get_featured_img_url[0] : '';
    }
    return $get_featured_img_url;
}


function bpcp_hide_media_by_other($query) {
    global $pagenow;

    if( ( 'edit.php' != $pagenow && 'upload.php' != $pagenow   ) || !$query->is_admin ){
        return $query;
    }

    if( !current_user_can( 'manage_options' ) ) {
        global $user_ID;
        $query->set('author', $user_ID );
    }
    return $query;
}


function bpcp_hide_media_query_where( $where ){
    global $current_user;
    if( !current_user_can( 'manage_options' ) ) {
        if( is_user_logged_in() ){
            if( isset( $_POST['action'] ) ){
                // library query
                if( $_POST['action'] == 'query-attachments' ){
                    $where .= ' AND post_author='.$current_user->data->ID;
                }
            }
        }
    }

    return $where;
}

function bpcp_paged_query_var(){
    if ( get_query_var('paged') ) { $paged = get_query_var('paged'); }
    elseif ( get_query_var('page') ) { $paged = get_query_var('page'); }
    else { $paged = 1; }
    return $paged;
}

function bpcp_num_pagination($the_query){
    $total_pages = $the_query->max_num_pages;

    if ($total_pages > 1) {
        $paged = bpcp_paged_query_var();
        $current_page = max(1, $paged);
        $big = 999999999;

        echo '<div class="pagination no-ajax portfolio-pag" id="pag-top">';
        echo paginate_links(array(
            'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format' => '?page=%#%',
            'current' => $current_page,
            'total' => $total_pages,
            'type' => 'plain',
            'prev_next' => True,
            'prev_text' => __('&larr;', 'bp-portfolio'),
            'next_text' => __('&rarr;', 'bp-portfolio'),
        ));
        echo '</div>';
    }
}

function bpcp_role_allow_uploads() {
  if ( current_user_can('contributor') && !current_user_can('upload_files') ){
      $contributor = get_role('contributor');
      $contributor->add_cap('upload_files');
  }
  if ( current_user_can('subscriber') && !current_user_can('upload_files') ){
      $contributor = get_role('subscriber');
      $contributor->add_cap('upload_files');
  }
}

function bpcp_get_default_subnav(){
    $default_subnav = array();
    $pro_subcomponents = array();
    // get project component enable status
    $bpcp_projects_enable = bp_portfolio()->setting( 'bpcp-projects-enable' );
    // get pro plugin subcomponents
    if( function_exists('bpcp_pro_portfolio_subcomponents') ){
        $pro_subcomponents = bpcp_pro_portfolio_subcomponents();
    }

    if($bpcp_projects_enable == 'on'){
        $default_subnav = array(
            'default_subnav_name' => bpcp_portfolio_subnav_name(),
            'default_subnav_slug' => bpcp_portfolio_subnav_slug(),
            'screen_function'     => 'bpcp_portfolio_project_screen'
        );
    }else{
        if(!empty($pro_subcomponents)) {
            foreach($pro_subcomponents as $single){
                if($single['enable_status'] == 'on'){
                    $default_subnav = array(
                        'default_subnav_name' => $single['name'],
                        'default_subnav_slug' => $single['slug'],
                        'screen_function'     => $single['screen']
                    );
                    break;
                }
            }
        }
    }
    return $default_subnav;
}

function bpcp_project_category_label(){
    $catg_label = bp_portfolio()->setting( 'projects-category-label' );
    if( !$catg_label ){
        $catg_label = __( 'Category', 'bp-portfolio' );
    }
    return $catg_label;
}

function bpcp_global_page_top_menu($selected_item){
    $menu_html = '';

    if( $selected_item == 'projects' ){
        $menu_html .= bpcp_projects_order_filter_items();
    }

    $subnav_options = array(
        'all'           => __( 'All Time', 'bp-portfolio' ),
        'past_year'     => __( 'This Past Year', 'bp-portfolio' ),
        'past_month'    => __( 'This past month', 'bp-portfolio' ),
        'past_week'     => __( 'This past week', 'bp-portfolio' ),
        'today'         => __( 'Today', 'bp-portfolio' ),
    );

    $selected = isset( $_GET['bb_filter'] ) ? $_GET['bb_filter'] : 'all';
    $is_valid = false;
    foreach( $subnav_options as $key=>$label ){
        if( $key==$selected ){
            $is_valid = true;
            break;
        }
    }
    if( !$is_valid ){
        $selected = 'all';
    }

    $page_url = get_permalink();
    if( isset($_GET['bb_orderby']) && !empty( $_GET['bb_orderby']) ){
        $page_url = add_query_arg( array( 'bb_orderby' => $_GET['bb_orderby'] ), $page_url );
    }
    if( isset($_GET['bb_category']) && !empty( $_GET['bb_category']) ){
        $page_url = add_query_arg( array( 'bb_category' => $_GET['bb_category'] ), $page_url );
    }

    $menu_html .= '<div class="bp-project-time">';
    $menu_html .= '<select id="time-filter" class="bp-work-filter">';
    foreach( $subnav_options as $key=>$label ){
        $selected_class = $selected == $key ? 'selected="selected"' : '';
        $url = add_query_arg( array( 'bb_filter' => $key ), $page_url );
        $menu_html .= "<option {$selected_class} value='{$url}'>{$label}</option>";
    }
    $menu_html .= '</select>';
    $menu_html .= '</div>';

    if( $selected_item == 'projects' ){
        $menu_html .= '<form method="GET" action="'. get_permalink() .'">';
        $menu_html .= '<input type="hidden" name="bb_filter" value="'. esc_attr( @$_GET['bb_filter'] ) .'">';
        $menu_html .= '<input type="hidden" name="bb_orderby" value="'. esc_attr( @$_GET['bb_orderby'] ) .'">';

        $category_label = bpcp_project_category_label();

        $taxonomy = 'bb_project_category';
        if( $selected_item == 'wip' ){
            $taxonomy = 'bb_wip_category';
        }

        $menu_html .= wp_dropdown_categories( array(
            'echo'              => 0,
            'taxonomy'          => $taxonomy,
            'hide_empty'        => 1,
            'show_option_none'  => __( 'Categories', 'bp-portfolio' ),
            'option_none_value' => '',
            'hierarchical'      => 1,
            'orderby'           => 'name',
            'name'              => 'bb_category',
            'class'             => 'auto_submit_pform',
            'selected'          => isset( $_GET['bb_category'] ) ? $_GET['bb_category'] : '',
        ) );
        $menu_html .= '</form>';
    }

    return $menu_html;
}

function bpcp_projects_order_filter_items(){
    $filter_html = '';
    //$projects_filter_by = isset($_SESSION['projects_filter_by']) && !empty($_SESSION['projects_filter_by']) ? $_SESSION['projects_filter_by'] : 'recent';
    $projects_filter_by = isset($_GET['bb_orderby']) && !empty($_GET['bb_orderby']) ? $_GET['bb_orderby'] : 'recent';
    $recent_selected = $projects_filter_by == "recent" ? "selected" : "" ;
    $commented_selected = $projects_filter_by == "most_commented" ? "selected" : "";
    $popular_selected = $projects_filter_by == "most_popular" ? "selected" : "";
    $viewed_selected = $projects_filter_by == "most_viewed" ? "selected" : "";
    $filter_html .= '<form method="GET" action="'. get_permalink() .'">';
    $filter_html .= '<input type="hidden" name="bb_filter" value="'. esc_attr( @$_GET['bb_filter'] ) .'">';
    $filter_html .= '<input type="hidden" name="bb_category" value="'. esc_attr( @$_GET['bb_category'] ) .'">';
    $filter_html .= '<select id="projects-order-by" name="bb_orderby" class="auto_submit_pform">';
    $filter_html .= '<option value="recent" ' .$recent_selected. '>' . __('Recent', 'bp-portfolio'). '</option>';
    $filter_html .= '<option value="most_commented" ' . $commented_selected . '>' . __('Most Commented', 'bp-portfolio'). '</option>';
    $filter_html .= '</select></form>';
    return $filter_html;
}

if ( !function_exists( 'project_loop' ) ) {
    function project_loop( $atts ){
        // usage: [project_loop posts_per_page=20 show_filters=true default_order='date'/]
        $a = shortcode_atts( array(
            'posts_per_page' => 20,
            'show_filters' => true,
            'default_order' => 'date'
        ), $atts );
       ?>
       <div id="buddypress">
            <?php if($a['show_filters']): ?>
            <div class="bp-project-filters">
                <?php echo bpcp_global_page_top_menu('projects'); ?>
            </div>
            <?php endif; ?>

            <div class="activity">

                <div class="bpcp-grid-wrapper">
                    <?php
                    $projects_filter_by = isset( $_GET['bb_orderby'] ) && !empty( $_GET['bb_orderby'] ) ? $_GET['bb_orderby'] : 'recent';
                    switch($projects_filter_by){
                        case 'recent':          $order_by = 'date'; break;
                        case 'most_commented':  $order_by = 'comment_count'; break;
                        case 'most_viewed':     $order_by = 'bpcp_pro_views'; break;
                        case 'most_popular':    $order_by = 'like'; break;
                        default:                $order_by = $a['default_order'];
                    };
                    $paged = bpcp_paged_query_var();
                    $meta_query = bpcp_project_meta_args( 'project_visibility', true );
                    $gp_args = array(
                        'post_type' => 'bb_project',
                        'meta_query' => array($meta_query),
                        'orderby' => $order_by,
                        'order' => 'DESC',
                        'posts_per_page' => $a['posts_per_page'],
                        'paged' => $paged
                    );

                    if( 'bpcp_pro_views'==$order_by ){
                        $gp_args['orderby'] = 'meta_value_num';
                        $gp_args['meta_key'] = $order_by;
                    }

                    $gp_args = bpcp_add_date_query_args( $gp_args );
                    $gp_args = bpcp_add_category_query_args( $gp_args );
                    //add_filter( 'posts_where', 'bpcp_project_visibility_filter', 10, 2 );
                    $the_query = new WP_Query($gp_args);
                    //remove_filter( 'posts_where', 'bpcp_project_visibility_filter', 10, 2 );
                    if ($the_query->have_posts()): ?>
                    <ul id="portfolio-activity" class="portfolio-list">
                        <?php while ($the_query->have_posts()):
                            $the_query->the_post();
                            $default_img_url = BP_PORTFOLIO_PLUGIN_URL.'assets/images/project-placeholder.png';
                            $get_project_thumb = bpcp_featured_image_detail(get_the_ID(), 'portfolio-thumbnail', $default_img_url);
                            $project_thumb_url = ( isset($get_project_thumb['src']) && !empty($get_project_thumb['src']) ) ? $get_project_thumb['src'] : '';
                            $project_visibility = get_post_meta(get_the_ID(), 'project_visibility', true);
                            ?>
                            <li class="bpcp-grid-item">
                               <div class="bp-inner-wrap">
                                    <div class="bb-project-thumb">
                                        <a href="<?php the_permalink();?>"><img src="<?php echo $project_thumb_url;?>" alt="<?php the_title();?>" /></a>
                                    </div>
                                    <div class="bb-project-title"><h4><a href="<?php the_permalink();?>"><?php the_title();?></a><h4></div>
                                    <div class="bb-project-author"><?php _e( 'by', 'bp-portfolio' );?> <a href="<?php echo bp_core_get_user_domain(get_the_author_meta('ID'));?>"><?php the_author();?></a></div>
                                </div>
                            </li>
                        <?php endwhile;?>
                    </ul>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php

        // pagination code
        bpcp_num_pagination($the_query);

        // Restore original Query & Post Data
        wp_reset_query();
        wp_reset_postdata();
    }
}
add_shortcode( 'project_loop', 'project_loop' );
