<?php
/**
 * @package WordPress
 * @subpackage BP Portfolio
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

if (!class_exists('BP_Portfolio_Admin')):

    /**
     *
     * BP_Portfolio_Admin
     * ********************
     *
     *
     */
    class BP_Portfolio_Admin {
        /* Options/Load
         * ===================================================================
         */

        /**
         * Plugin options
         *
         * @var array
         */
        public $options = array();
        
        public $post_types = array('bb_project');

        private $plugin_slug = 'bp_portfolio';

        /**
         * Empty constructor function to ensure a single instance
         */
        public function __construct() {
            // ... leave empty, see Singleton below
        }

        /* Singleton
         * ===================================================================
         */

        /**
         * Admin singleton
         *
         * @since BP Portfolio (1.0.0)
         *
         * @param  array  $options [description]
         *
         * @uses BP_Portfolio_Admin::setup() Init admin class
         *
         * @return object Admin class
         */
        public static function instance() {
            static $instance = null;

            if (null === $instance) {
                $instance = new BP_Portfolio_Admin;
                $instance->setup();
            }

            return $instance;
        }

        /* Utility functions
         * ===================================================================
         */

        /**
         * Get option
         *
         * @since BP Portfolio (1.0.0)
         *
         * @param  string $key Option key
         *
         * @uses BP_Portfolio_Admin::option() Get option
         *
         * @return mixed      Option value
         */
        public function option($key) {
            $value = bp_portfolio()->option($key);
            return $value;
        }

        /* Actions/Init
         * ===================================================================
         */

        /**
         * Setup admin class
         *
         * @since BP Portfolio (1.0.0)
         *
         * @uses bp_portfolio() Get options from main BP_Portfolio_Admin class
         * @uses is_admin() Ensures we're in the admin area
         * @uses curent_user_can() Checks for permissions
         * @uses add_action() Add hooks
         */
        public function setup() {
            if ((!is_admin() && !is_network_admin() ) || !current_user_can('manage_options')) {
                return;
            }

            $actions = array(
                'admin_init',
                'admin_menu',
                'network_admin_menu',
                'add_meta_boxes'
            );

            if (isset($_GET['page']) && ( $_GET['page'] == 'bp-portfolio/includes/admin.php' )) {
                $actions[] = 'admin_enqueue_scripts';
            }
            
            foreach ($actions as $action) {
                add_action($action, array($this, $action));
            }
            
            add_action('save_post', array($this, 'save_post'), 10, 3);

            // add setting link
            $buddyboss = BP_Portfolio_Plugin::instance();
            $plugin = $buddyboss->basename;
            add_filter("plugin_action_links_$plugin", array($this, 'plugin_settings_link'));
        }

        /**
         * Register admin settings
         *
         * @since BP Portfolio (1.0.0)
         *
         * @uses register_setting() Register plugin options
         * @uses add_settings_section() Add settings page option sections
         * @uses add_settings_field() Add settings page option
         */
        public function admin_init() {
            register_setting( 'bp_portfolio_plugin_settings', 'bp_portfolio_plugin_settings' );
            register_setting( 'bp_portfolio_plugin_options', 'bp_portfolio_plugin_options' );
        }

        /**
         * Add plugin settings page
         *
         * @since BP Portfolio (1.0.0)
         *
         * @uses add_submenu_page() Add plugin settings page
         */
        public function admin_menu() {
			
			if ( function_exists('bp_portfolio_pro') ) {
				$menu_title = 'Portfolio PRO';
			} else {
				$menu_title = 'Portfolio';
			}
			
			add_submenu_page( 'buddyboss-settings', 'BP Portfolio', $menu_title, 'manage_options', $this->plugin_slug, array($this, 'bpcp_admin_page') );
        }

        /**
         * Add plugin settings page
         *
         * @since BP Portfolio (1.0.0)
         *
         * @uses BP_Portfolio_Admin::admin_menu() Add settings page option sections
         */
        public function network_admin_menu() {
            return $this->admin_menu();
        }
        
        public function add_meta_boxes($postType) {
            $types = apply_filters('bpcp_posttypes_array', $this->post_types);
            if(in_array($postType, $types)){
                add_meta_box("project-meta-box", "Project Fields", array($this, 'render_metabox'), $postType, "advanced", "high", null);
            }
        }
        
        public function render_metabox($object) {
            wp_nonce_field(basename(__FILE__), "project-box-nonce");
            if($object->post_type == 'bb_project') {
            ?>
            <div>
                <label for="project_visibility"><?php _e( 'Visibility', 'bp-portfolio' ); ?></label>
                <select id="project_visibility" name="project_visibility" class="project-visibility">
                    <?php
                    $options = array(
                        'public'	=> __('Everyone', 'bp-portfolio'),
                        'private'	=> __('Only Me', 'bp-portfolio'),
                        'members'	=> __('All Members', 'bp-portfolio'),
                    );

                    $project_visibility = get_post_meta($object->ID, 'project_visibility', true);

                    $selected_val = isset($project_visibility) ? $project_visibility : '';

                    foreach( $options as $key=>$val ){
                        $selected = $selected_val == $key ? ' selected' : '';
                        echo "<option value='" . esc_attr( $key ) . "' $selected>$val</option>";
                    }
                    ?>
                </select>
            </div>
            <?php
            }
            
            do_action('bpcp_after_metabox_fields', $object);
            
            if($object->post_type == 'bb_project' || $object->post_type == 'bb_wip') {
            ?>
            <br>
            <div>
                <label for="entry_status"><?php _e( 'Status', 'bp-portfolio' ); ?></label>
                <select id="entry_status" name="entry_status" class="entry-status">
                    <?php
                    $options = array(
                        'uncompleted'	=> __('Uncompleted', 'bp-portfolio'),
                        'completed'	=> __('Completed', 'bp-portfolio')
                    );

                    $entry_status = get_post_meta($object->ID, 'entry_status', true);

                    $selected_val = isset($entry_status) ? $entry_status : '';

                    foreach( $options as $key=>$val ){
                        $selected = $selected_val == $key ? ' selected' : '';
                        echo "<option value='" . esc_attr( $key ) . "' $selected>$val</option>";
                    }
                    ?>
                </select>
            </div>
            <?php
            }
        }
        
        public function save_post($post_id, $post, $update) {
            if (!isset($_POST["project-box-nonce"]) || !wp_verify_nonce($_POST["project-box-nonce"], basename(__FILE__)))
                return $post_id;

            if(!current_user_can("edit_post", $post_id))
                return $post_id;

            if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
                return $post_id;
        
            if(!in_array($post->post_type, apply_filters('bpcp_posttypes_array', $this->post_types)))
                return $post_id;

            if($post->post_type == 'bb_project') {
                $project_visibility = "";

                if(isset($_POST["project_visibility"]))
                {
                    $project_visibility = $_POST["project_visibility"];
                }   
                update_post_meta($post_id, "project_visibility", $project_visibility);
            }

            if($post->post_type == 'bb_project' || $post->post_type == 'bb_wip') {
                $entry_status = "";

                if(isset($_POST["entry_status"]))
                {
                    $entry_status = $_POST["entry_status"];
                }   
                update_post_meta($post_id, "entry_status", $entry_status);
            }
            
            do_action('bpcp_after_update_metabox_fields', $post);
        }

        // Add settings link on plugin page
        function plugin_settings_link($links) {
            $links[] = '<a href="'.admin_url("admin.php?page=bp_portfolio").'">'.__("Settings","bp-portfolio").'</a>';
            return $links;
        }

        /**
         * Register admin scripts
         *
         * @since BP Portfolio (1.0.0)
         *
         * @uses wp_enqueue_script() Enqueue admin script
         * @uses wp_enqueue_style() Enqueue admin style
         * @uses bp_portfolio()->assets_url Get plugin URL
         */
        public function admin_enqueue_scripts() {
            $js = bp_portfolio()->assets_url . '/js/';
            $css = bp_portfolio()->assets_url . '/css/';
        }

        public function bpcp_admin_page(){
            include_once( bp_portfolio()->includes_dir . '/admin/main.php' );
        }

        public function update_settings_content(){

        }

        private function get_screens(){
            return apply_filters( 'bpcp_admin_screens', array(
                'settings'	=> __( 'Settings', 'bp-portfolio' ),
                'pages'		=> __( 'Pages', 'bp-portfolio'),
                'support'		=> __( 'Support', 'bp-portfolio'),
            ) );
        }

        public function get_active_screen(){
            return isset( $_GET['screen'] ) ? $_GET['screen'] : 'settings';
        }

        /**
         * Print the tabs in admin screen
         *
         * @since    1.0.0
         */
        public function print_screen_tabs() {
            $active_screen = $this->get_active_screen();

            foreach( $this->get_screens() as $screen => $label ){
                $active = $active_screen == $screen ? 'nav-tab-active' : '';
                echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_slug . '&screen=' . $screen . '">' . $label . '</a>';
            }
        }

        /**
         *
         */
        public function print_screen_content(){
            $active_screen = $this->get_active_screen();
            do_action( 'bp_portfolio_print_screen_'.$active_screen );
        }

        /**
         * General settings section
         *
         * @since BP Portfolio (1.0.0)
         */
        public function section_general() {
            
        }

        /**
         * Style settings section
         *
         * @since BP Portfolio (1.0.0)
         */
        public function section_style() {
            
        }

    }

// End class BP_Portfolio_Admin

endif;