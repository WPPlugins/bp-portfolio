<?php
/**
 * @package WordPress
 * @subpackage BP Portfolio
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;


if (!class_exists('Portfolio_Component')):

/**
 * Main portfolio Component Class
 */
class Portfolio_Component extends BP_Component {

    public $hooks;
    public $bpcp_projects_enable;

/**
 * Constructor method
 *
 *
 * @package portfolio
 * @since 1.0
 */
    public function __construct() {
        global $bp;
        parent::start(
            bpcp_portfolio_slug(),
            bpcp_portfolio_name(),
            bpcp_portfolio_includes_dir()
        );

        $this->includes();
        $bp->active_components[$this->id] = 'on';
        $this->bpcp_projects_enable = bp_portfolio()->setting( 'bpcp-projects-enable' );

        // display admin notice to install pro plugin
        add_action( 'admin_notices', array($this, 'pro_plugin_admin_notice') );

        if($this->bpcp_projects_enable == 'on'){
            add_action( 'init', array( &$this, 'register_post_types' ), 1 );
            add_action( 'init', array( &$this, 'bpcp_register_taxonomies' ), 1 );

            // filtering to remove other user attchments from media library
            add_filter('pre_get_posts', 'bpcp_hide_media_by_other');
            add_filter( 'posts_where', 'bpcp_hide_media_query_where' );

            // ajax callback
            add_action('wp_ajax_bbpajax', 'bpcp_ajax_callback');
            add_action('wp_ajax_nopriv_bbpajax', 'bpcp_ajax_callback');

            //allow subscriber & contributor to upload image
            add_action('admin_init', 'bpcp_role_allow_uploads');
        }

    }

/**
 * portfolio needed files
 */
    public function includes( $includes = array() ) {

        // Files to include
        $includes = array(
            'bpcp-item-filters.php',
            'bpcp-item-actions.php',
            'bpcp-item-screens.php',
            'bpcp-item-functions.php',
            'bpcp-item-template.php',
            'bpcp-item-ajax.php',
        );

        parent::includes( $includes );
    }

/**
 * Convenince method for getting main plugin options.
 *
 * @since BP Portfolio (1.0.0)
 */
    public function option( $key )
    {
        return bp_portfolio()->option( $key );
    }

/**
 * SETUP GLOBAL OPTIONS
 */
    public function setup_globals( $args = array() )
    {
        parent::setup_globals( array(
            'has_directory' => false
        ) );
    }

/**
 * Setup actions
 */
    public function setup_actions()
    {
        // Add body class
        add_filter( 'body_class', array( $this, 'body_class' ) );
		
		add_action( 'bp_setup_nav', array( $this, 'cp_setup_nav' ), 100 );
		add_action( 'bp_setup_admin_bar', array( $this, 'cp_setup_admin_bar' ), 80 );

        // Front End Assets
        if ( ! is_admin() && ! is_network_admin() )
        {
			add_action( 'wp_enqueue_scripts', array( bp_portfolio(), 'assets' ) );
        }

        parent::setup_actions();
    }
	
	/**
	 * Set up portfolio navigation.
	 * Made hookable so that it can be overriden in themes/plugins.
	 */
	public function cp_setup_nav() {

		$default_subnav = bpcp_get_default_subnav();
		if ( empty( $default_subnav ) )
			return;

		bp_core_new_nav_item( array(
			'name' => bpcp_portfolio_name(),
			'slug' => bpcp_portfolio_slug(),
			'screen_function' => $default_subnav[ 'screen_function' ],
			'default_subnav_slug' => $default_subnav[ 'default_subnav_slug' ],
			'position' => 10
		) );

		$displayed_user_id = bp_displayed_user_id();
		$user_domain = ( ! empty( $displayed_user_id ) ) ? bp_displayed_user_domain() : bp_loggedin_user_domain();

		$portfolio_link = trailingslashit( $user_domain . bpcp_portfolio_slug() );

		// Add subnav items
		bp_core_new_subnav_item( array(
			'name' => $default_subnav[ 'default_subnav_name' ],
			'slug' => $default_subnav[ 'default_subnav_slug' ],
			'parent_url' => $portfolio_link,
			'parent_slug' => $this->slug,
			'screen_function' => $default_subnav[ 'screen_function' ],
			'position' => 10,
		) );
	}

		/**
 * Builds the user's navigation in WP Admin Bar
 */
    public function cp_setup_admin_bar( $wp_admin_nav = array() ) {

        $default_subnav = bpcp_get_default_subnav();
        if(empty($default_subnav)) return;
		
		global $wp_admin_bar;
        $portfolio_slug = bpcp_portfolio_slug();

        // Menus for logged in user
        if ( is_user_logged_in() ) {

            // Setup the logged in user variables
            $portfolio_link = trailingslashit( bp_loggedin_user_domain() . $portfolio_slug );
			
			$wp_admin_bar->add_menu( array(
                'parent' => 'my-account-buddypress',
                'id'     => 'my-account-' . $portfolio_slug,
                'title'  => bpcp_portfolio_name(),
                'href'   => trailingslashit( $portfolio_link )
            ) );

            // Add portfolio submenu
            if($this->bpcp_projects_enable == 'on'){
                $wp_admin_bar->add_menu( array(
                    'parent' => 'my-account-' . $portfolio_slug,
                    'id'     => 'my-account-' . $portfolio_slug .'-'.bpcp_portfolio_subnav_slug(),
                    'title'  => bpcp_portfolio_subnav_name(),
                    'href'   => trailingslashit( $portfolio_link )
                ) );
            }

        }
		
    }

/**
 * registering portfolio custom post types
 */
    public function register_post_types() {

        // Set up some labels for the post type

        $labels_project = array(
            'name'	             => __( 'Projects', 'bp-portfolio' ),
            'singular'           => __( 'Project', 'bp-portfolio' ),
            'menu_name'          => __( 'Projects', 'bp-portfolio' ),
            'all_items'          => __( 'All Projects', 'bp-portfolio' ),
            'singular_name'      => __( 'Project', 'bp-portfolio' ),
            'add_new'            => __( 'Add New Project', 'bp-portfolio' ),
            'add_new_item'       => __( 'Add New Project', 'bp-portfolio' ),
            'edit_item'          => __( 'Edit Project', 'bp-portfolio' ),
            'new_item'           => __( 'New Project', 'bp-portfolio' ),
            'view_item'          => __( 'View Project', 'bp-portfolio' ),
            'search_items'       => __( 'Search Projects', 'bp-portfolio' ),
            'not_found'          => __( 'No Projects Found', 'bp-portfolio' ),
            'not_found_in_trash' => __( 'No Projects Found in Trash', 'bp-portfolio' )
        );

        $args_project = array(
            'label'	=> __( 'Project', 'bp-portfolio' ),
            'labels' => $labels_project,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_icon' => 'dashicons-admin-customizer',
            'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields', 'comments')
        );

        // Register the post type for files.
        register_post_type( 'bb_project', $args_project );

        parent::register_post_types();
    }

    public function bpcp_register_taxonomies()
    {
        $labels_project_tag = array(
            'name' => __('Tags', 'bp-portfolio'),
            'singular_name' => __('Tag', 'bp-portfolio'),
        );
        $args_project_tag = array(
            'labels' => $labels_project_tag,
            'hierarchical' => false,
            'show_ui' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array('slug' => 'bb_project_tag'),
        );
        register_taxonomy('bb_project_tag', 'bb_project', $args_project_tag);
		
        $labels_project_tag = array(
            'name' => bpcp_project_category_label(),
            'singular_name' => bpcp_project_category_label(),
        );
        $args_project_tag = array(
            'labels' => $labels_project_tag,
            'hierarchical' => true,
            'show_ui' => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array('slug' => 'bb_project_category'),
        );
        register_taxonomy( 'bb_project_category', 'bb_project', $args_project_tag);
    }


/**
 * Add active portfolio class
 */
    public function body_class( $classes )
    {
        $classes[] = apply_filters( 'bp_portfolio_body_class', 'bp-portfolio' );
        return $classes;
    }


/**
 * display admin notice to install pro plugin
 */
    public function pro_plugin_admin_notice(){
        global $pagenow;
            if ( !class_exists('BPCP_Pro_Components') && ($pagenow == 'options-general.php') && ($_GET['page'] == 'bp_portfolio') ) {
            $has_error = __('For <strong>Works in Progress</strong> and <strong>Collections</strong>, purchase the Pro version at <a href="http://www.buddyboss.com/plugins/">BuddyBoss.com</a>.', 'bp-portfolio');
            echo '<div class="updated">
               <p>'.$has_error.'</p>
            </div>';
        }
    }


}

endif;
