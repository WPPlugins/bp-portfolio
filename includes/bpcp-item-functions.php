<?php
if( !defined( 'ABSPATH' ) ) {
    exit();
}

/**
 * Where is the add project form location?
 * is it under user profile > projects  or is it on a wordpress page?
 * Check settings for details.
 * 
 * 
 * @return string 'bp'-> under user profile. 'wp'-> on a wordpress page.
 */
function bpcp_add_project_location(){
    $location = 'bp';
    if( bp_portfolio()->option('add-project-page-select') ){
        $location = 'wp';
    }
    
    return $location;
}

function bpcp_is_add_project_page(){
    $is_our_page = false;
    if( bp_portfolio()->option('add-project-page-select') ){
        if( is_page( bp_portfolio()->option('add-project-page-select') ) ){
            $is_our_page = true;
        }
    }
    
    return $is_our_page;
}

function bpcp_is_add_project_routine(){
    $flag = bpcp_is_add_project_page();
    if( !$flag ){
        if( bpcp_is_portfolio_component() && bp_is_current_action( bpcp_portfolio_subnav_slug() ) ){
            $flag = true;
        }
    }
    
    return $flag;
}

function bpcp_is_accessible( ){
    if( !is_user_logged_in() )
        return;
    
    $is_accesible = bpcp_is_add_project_page();
    if( !$is_accesible && function_exists( 'bpcp_pro_is_add_wip_page' ) ){
        $is_accesible = bpcp_pro_is_add_wip_page();
    }
    
    if( !$is_accesible && bpcp_is_portfolio_component() && bp_loggedin_user_id()==  bp_displayed_user_id() ){
        $is_accesible = true;
    }
    
    return $is_accesible;
}

function bpcp_is_portfolio_component() {
    $bpcp_portfolio_slug = bpcp_portfolio_slug();
    $is_portfolio_component = bp_is_current_component( $bpcp_portfolio_slug );
    return apply_filters( 'bpcp_is_portfolio_component', $is_portfolio_component );
}

function bpcp_portfolio_component_root(){
    global $bp;
    $portfolio_component = bp_get_root_slug( bpcp_portfolio_slug() );
    if( isset($bp->loggedin_user->domain) && !empty($bp->loggedin_user->domain) ){
        $base_link = $bp->loggedin_user->domain . $portfolio_component;
    }else{
        $base_link = $bp->displayed_user->domain . $portfolio_component;
    }
    return trailingslashit($base_link);
}


function bpcp_project_top_menu()
{
    global $bp;
    if ( !bpcp_is_accessible() ) return;
    $project_id = bpcp_valid_project_ID();
    $action = ( isset($_GET['bpcp_action']) && !empty($_GET['bpcp_action']) ) ? $_GET['bpcp_action'] : 'add_project';
    $menu_content = '';

    $base_link = bpcp_portfolio_component_root();
    if( bpcp_add_project_location()=='wp' ){
        $base_link = get_permalink( bp_portfolio()->option('add-project-page-select') );
    }
    $detail_link = add_query_arg(array('bpcp_action' => 'add_project', 'project_id' => $project_id), $base_link);
    $content_link = add_query_arg(array('bpcp_action' => 'project_content', 'project_id' => $project_id), $base_link);
    $cover_link = add_query_arg(array('bpcp_action' => 'project_cover', 'project_id' => $project_id), $base_link);
    $settings_link = add_query_arg(array('bpcp_action' => 'project_settings', 'project_id' => $project_id), $base_link);

    $details_label = __( 'Details', 'bp-portfolio' );
    $content_label = __( 'Content', 'bp-portfolio' );
    $cover_label = __( 'Cover', 'bp-portfolio' );
    $settings_label = __( 'Settings', 'bp-portfolio' );

    $menu_content .= ($action != 'add_project') ? '<a href="' . $detail_link . '">'.$details_label.'</a>' : '<span>'.$details_label.'</span>';
    $menu_content .= ($action != 'project_content' && !empty($project_id)) ? ' | <a href="' . $content_link . '">'.$content_label.'</a>' : ' | <span>'.$content_label.'</span>';
    $menu_content .= ($action != 'project_cover' && !empty($project_id)) ? ' | <a href="' . $cover_link . '">'.$cover_label.'</a>' : ' | <span>'.$cover_label.'</span>';
    $menu_content .= ($action != 'project_settings' && !empty($project_id)) ? ' | <a href="' . $settings_link . '">'.$settings_label.'</a>' : ' | <span>'.$settings_label.'</span>';

    return '<div class="add_project_top_nav">' . $menu_content . '</div>';
}


function bpcp_project_step_url($target, $project_id='')
{
    if( !bpcp_is_accessible() )return;
    
    $project_id = empty($project_id) ? $_GET['project_id'] : $project_id;
    $base_link = bpcp_portfolio_component_root();
    if( bpcp_add_project_location()=='wp' ){
        $base_link = get_permalink( bp_portfolio()->option('add-project-page-select') );
    }
    $target_url = add_query_arg(array('bpcp_action' => $target, 'project_id' => $project_id), $base_link);
    return $target_url;
}

function bpcp_get_post_detail($post_id){
    $post_detail = array();
    if(isset($post_id) && !empty($post_id)){
        $post_detail = get_post($post_id);
    }
    return $post_detail;
}

function bpcp_custom_taxonomy_terms($post_id, $taxonomy_name, $html=''){
    $all_tags = array();
    if(!empty($post_id)){
        $get_post_tags = wp_get_object_terms($post_id, $taxonomy_name);
    }
    // get all tags html into array
    if ( isset($get_post_tags) && !empty($get_post_tags) ):
        foreach ($get_post_tags as $single_tag):
            if($html){
                $all_tags[] = '<a href="'.get_term_link($single_tag->slug, $taxonomy_name).'" title="'.$single_tag->name.'">'.$single_tag->name.'</a>';
            }else{
                $all_tags[] = $single_tag->name;
            }
        endforeach;
    endif;
    return $all_tags;
}

function bpcp_custom_taxonomy_single_term_slug( $post_id, $taxonomy ){
    $term_slugs = '';
    if(!empty($post_id)){
        $get_post_tags = wp_get_object_terms($post_id, $taxonomy);
    }
    // get all tags html into array
    if ( isset($get_post_tags) && !empty($get_post_tags) ){
        foreach ($get_post_tags as $single_tag){
            $term_slugs[] = $single_tag->slug;
        }
    }
    return $term_slugs;
}

function bpcp_project_visibility_filter( $where, &$wp_query )
{   if(bp_loggedin_user_id()){
      global $wpdb;
      $where .= ' OR ( ( ' . $wpdb->posts . '.post_author = ' . bp_loggedin_user_id() . ' ) ';
      $where .= ' AND  ( ' . $wpdb->posts . '.post_type   = "' . $wp_query->query['post_type'] . '" ) ';
      $where .= ' AND  ( ' . $wpdb->posts . '.post_status = "publish" ) ) ';
    }
    return $where;
}

function bpcp_add_date_query_args( $args='' ){
    $filter = isset( $_GET['bb_filter'] ) ? $_GET['bb_filter'] : '';
    
    $today = getdate();
    $date_query = array(
        'year'  => $today['year'],
        /*'month' => $today['mon'],
        'day'   => $today['mday'],*/
    );
    switch( $filter ){
        case 'past_year':
            $args['date_query'] = array( $date_query );
            break;
        case 'past_month':
            $date_query['month'] = $today['mon'];
            $args['date_query'] = array( $date_query );
            break;
        case 'past_week':
            $date_query['week'] = date( 'W' );
            $args['date_query'] = array( $date_query );
            break;
        case 'today':
            $date_query['month'] = $today['mon'];
            $date_query['day']   = $today['mday'];
            $args['date_query'] = array( $date_query );
            break;
        default:
            break;
    }
    return $args;
}

function bpcp_add_category_query_args( $args='' ){
    $filter = isset( $_GET['bb_category'] ) ? $_GET['bb_category'] : '';
    if( !(int)$filter ){
        return $args;
    }
    
    if( !in_array( $args['post_type'], array( 'bb_project', 'bb_wip' ) ) ){
        return $args;
    }
    
    $tax_query = isset( $args['tax_query'] ) && !empty( $args['tax_query'] ) ? $args['tax_query'] : array();
    
    $taxonomy = '';
    if( $args['post_type']=='bb_project' ){
        $taxonomy = 'bb_project_category';
    }
    if( $args['post_type']=='bb_wip' ){
        $taxonomy = 'bb_wip_category';
    }
    
    if( !$taxonomy ){
        return $args;
    }
    
    $t_tax_query = array(
        'taxonomy'  => $taxonomy,
        'field'     => 'term_id',
        'terms'     => array( $filter ),
    );
    $tax_query[] = $t_tax_query;
    
    $args['tax_query'] = $tax_query;
    return $args;
}

function bpcp_project_meta_args($meta_key, $global_page = false){
    if( $global_page === false && (bp_displayed_user_id() == bp_loggedin_user_id()) ){
        $visibility = array( 'public', 'members' , 'private','friends' );
    }
    elseif ( bp_is_active('friends') && friends_check_friendship_status(bp_displayed_user_id(), bp_loggedin_user_id()) == 'is_friend'  ) {
        $visibility = array( 'public', 'members','friends' );
    }
    elseif ( is_user_logged_in() ) {
        $visibility = array( 'public', 'members' );
    }else{
        $visibility = array( 'public' );
    }

    $meta_query = array(
        'key' => $meta_key,
        'value' => $visibility,
        'compare' => 'IN',
    );
    return $meta_query;
}

function bpcp_project_edit_link($project_id){
    $target = 'add_project';
    $bpcp_portfolio_slug = bpcp_portfolio_slug();
    $base_link = bp_loggedin_user_domain() . bp_core_component_slug_from_root_slug($bpcp_portfolio_slug);
    if( bpcp_add_project_location()=='wp' ){
        $base_link = get_permalink( bp_portfolio()->option('add-project-page-select') );
    }
    $target_url = add_query_arg(array('bpcp_action' => $target, 'project_id' => $project_id), $base_link);
    return $target_url;
}

function bpcp_valid_project_ID(){
    $project_ID = '';
    if( isset($_GET['project_id']) && !empty($_GET['project_id']) ){
        $project_detail = get_post($_GET['project_id']);
        if( isset($project_detail->post_type) && $project_detail->post_type == 'bb_project' ){
            $project_ID = $project_detail->ID;
        }
    }
    return $project_ID;
}

function bpcp_add_project_title(){
    $add_project_title = '';
    $project_ID = bpcp_valid_project_ID();
    $entry_status = get_post_meta($project_ID, 'entry_status', true);
    if($entry_status == 'completed'){
        $add_project_title = __( 'Edit Project', 'bp-portfolio' );
    }else{
        $add_project_title = __( 'Add Project', 'bp-portfolio' );
    }
    return $add_project_title;
}


/**
 * Record an activity item
 */
function bpcp_record_activity( $args = '' ) {
    global $bp;

    if ( !function_exists( 'bp_activity_add' ) )
        return false;

    $defaults = array(
        'id' => false,
        'user_id' => $bp->loggedin_user->id,
        'action' => '',
        'content' => '',
        'primary_link' => '',
        'component' => $bp->portfolio->id,
        'type' => false,
        'item_id' => false,
        'secondary_item_id' => false,
        'recorded_time' => gmdate( "Y-m-d H:i:s" ),
        'hide_sitewide' => false
    );

    $r = wp_parse_args( $args, $defaults );
    extract( $r );

    return bp_activity_add( array(
        'id' => $id,
        'user_id' => $user_id,
        'action' => $action,
        'content' => $content,
        'primary_link' => $primary_link,
        'component' => $component,
        'type' => $type,
        'item_id' => $item_id,
        'secondary_item_id' => $secondary_item_id,
        'recorded_time' => $recorded_time,
        'hide_sitewide' => $hide_sitewide
    ) );
}

/**
 * Create HTML dropdown list of Categories.
 *
 * @package WordPress
 * @since 2.1.0
 * @uses Walker
 */
class Walker_CategoryDropdown_Projects extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @since 2.1.0
	 * @var string
	 */
	public $tree_type = 'category';

	/**
	 * @see Walker::$db_fields
	 * @since 2.1.0
	 * @todo Decouple this
	 * @var array
	 */
	public $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string $output   Passed by reference. Used to append additional content.
	 * @param object $category Category data object.
	 * @param int    $depth    Depth of category. Used for padding.
	 * @param array  $args     Uses 'selected', 'show_count', and 'value_field' keys, if they exist.
	 *                         See {@see wp_dropdown_categories()}.
	 */
	public function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		/** This filter is documented in wp-includes/category-template.php */
		$cat_name = apply_filters( 'list_cats', $category->name, $category );

		if ( isset( $args['value_field'] ) && isset( $category->{$args['value_field']} ) ) {
			$value_field = $args['value_field'];
		} else {
			$value_field = 'term_id';
		}

		$output .= "\t<option class=\"level-$depth\" value=\"" . esc_attr( $category->{$value_field} ) . "\"";
        
		// Type-juggling causes false matches, so we force everything to a string.
		if ( is_array($args['selected']) && in_array($category->{$value_field}, $args['selected']))
			$output .= ' selected="selected"';
		$output .= '>';
		$output .= $pad.$cat_name;
		if ( $args['show_count'] )
			$output .= '&nbsp;&nbsp;('. number_format_i18n( $category->count ) .')';
		$output .= "</option>\n";
	}
}