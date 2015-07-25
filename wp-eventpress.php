<?php
/*
Plugin Name: A EventPress
Plugin URI: http://duogeek.com
Description: Very easy and powerful plugin to use and user friendly best event manager plugin for WordPress with lots of useful options.
Version: 1.0.0
Author: duogeek
Author URI: http://duogeek.com
Textdomain: ep
License: GPL v2 or later
*/

if( ! defined( 'DG_HACK_MSG' ) ) define( 'DG_HACK_MSG', __( 'Sorry cowboy! This is not your place', 'ep' ) );

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) wp_die( DG_HACK_MSG );

/**
 * Defining constants
 */
if( ! defined( 'EP_VERSION' ) ) define( 'EP_VERSION', '1.0.0' );
if( ! defined( 'EP_MENU_POSITION' ) ) define( 'EP_MENU_POSITION', '50' );
if( ! defined( 'EP_PLUGIN_DIR' ) ) define( 'EP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if( ! defined( 'EP_FILES_DIR' ) ) define( 'EP_FILES_DIR', EP_PLUGIN_DIR . 'eventpress-files' );
if( ! defined( 'EP_PLUGIN_URI' ) ) define( 'EP_PLUGIN_URI', plugins_url( '', __FILE__ ) );
if( ! defined( 'EP_FILES_URI' ) ) define( 'EP_FILES_URI', EP_PLUGIN_URI . '/eventpress-files' );

/**
 * Include required files
 */
require_once EP_FILES_DIR . '/lib/functions.php';
require_once EP_FILES_DIR . '/classes/class.event.php';
require_once EP_FILES_DIR . '/classes/class.ep-options.php';
require_once EP_FILES_DIR . '/classes/class.event.addon.handler.php';
require_once EP_FILES_DIR . '/classes/class.event.template.handler.php';



if( ! class_exists( 'WP_EVENTPRESS' ) ){
    /**
     * The main plugin class for EventPress
     * 
     * Includes all the necessary settings, enqueue, formatting, other related fucntions/methods etc.
     *
     * @since 1.0.0
     */
    class WP_EVENTPRESS{
        
        /**
         * Singleton Instance of this class
         *
         * @since 1.0.0
         * @access private
         * @var OBJECT of WP_EVENTPRESS class
         */
        private static $_instance;
        
        /**
         * Event post type variable
         *
         * @since 1.0.0
         * @access public
         * @var STRING
         */
        public $post_type;
        
        /**
         * Event post type args
         *
         * @since 1.0.0
         * @access public
         * @var ARRAY
         */
        public $post_type_args;
        
        /**
         * Event taxonomies
         *
         * @since 1.0.0
         * @access public
         * @var ARRAY
         */
        public $taxonomies;
        
        /**
         * Event post type meta boxes
         *
         * @since 1.0.0
         * @access private
         * @var ARRAY
         */
        private $meta_boxes;
        
        /**
         * Event Statuses
         *
         * @since 1.0.0
         * @access private
         * @var ARRAY
         */
        private $statuses;
        
        /**
         * Event Types
         *
         * @since 1.0.0
         * @access private
         * @var ARRAY
         */
        private $types;
        
        /**
         * Event Settings Data
         *
         * @since 1.0.0
         * @access public
         * @var OBJECT
         */
        public $_data;
        
        /**
         * Event Currency
         *
         * @since 1.0.0
         * @access public
         * @var STRING
         */
        public $currency;
        
        /**
         * Event Table Name
         *
         * @since 1.0.0
         * @access public
         * @var STRING
         */
        public $event_tbl;
        
        /**
         * Instance of global $wpdb
         *
         * @since 1.0.0
         * @access public
         * @var OBJECT
         */
        public $_db;
        
        /**
         * Initializes the EP_EVENTS class
         *
         * Checks for an existing EP_EVENTS() instance
         * and if there is none, creates an instance.
         *
         * @since 1.0.0
         * @access public
         *
         * @return OBJECT WP_EVENTPRESS Object
         */
        public static function get_instance() {
            if ( ! self::$_instance instanceof WP_EVENTPRESS ) {
                self::$_instance = new WP_EVENTPRESS();
            }
            return self::$_instance;
        }
        
        /**
         * Class constructor
         */
        public function __construct() {
            global $wpdb;
            $this->_db = $wpdb;
            $this->_data = new EP_Options( 'ep', '' );
            
            $this->event_tbl = $this->_db->prefix . 'ep_events_rsvp';
            
            /**
             * Post type define
             */
            $this->post_type_args = array(
                                        'labels'             => array(
                                            'name'               => _x( 'Events', 'post type general name', 'ep' ),
                                            'singular_name'      => _x( 'Event', 'post type singular name', 'ep' ),
                                            'menu_name'          => _x( 'Events', 'admin menu', 'ep' ),
                                            'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'ep' ),
                                            'add_new'            => _x( 'Add New', 'book', 'ep' ),
                                            'add_new_item'       => __( 'Add New Event', 'ep' ),
                                            'new_item'           => __( 'New Event', 'ep' ),
                                            'edit_item'          => __( 'Edit Event', 'ep' ),
                                            'view_item'          => __( 'View Event', 'ep' ),
                                            'all_items'          => __( 'All Events', 'ep' ),
                                            'search_items'       => __( 'Search Events', 'ep' ),
                                            'parent_item_colon'  => __( 'Parent Events:', 'ep' ),
                                            'not_found'          => __( 'No event found.', 'ep' ),
                                            'not_found_in_trash' => __( 'No event found in Trash.', 'ep' )
                                        ),
                                        'description'        => __( 'EventPress', 'ep' ),
                                        'public'             => true,
                                        'publicly_queryable' => true,
                                        'show_ui'            => true,
                                        'show_in_menu'       => true,
                                        'query_var'          => true,
                                        'rewrite'            => array( 'slug' => 'event' ),
                                        'capability_type'    => 'post',
                                        'has_archive'        => 'events',
                                        'hierarchical'       => false,
                                        'menu_position'      => EP_MENU_POSITION,
                                        'menu_icon'          => EP_FILES_URI . '/assets/images/eventpress-20-20.png',
                                        'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
                                    );
            
            /**
             * @hook Filter
             * @filter ep_event_post_type_args
             * @param post_type args array
             * @since 1.0.0
             */
            $this->post_type_args = apply_filters( 'ep_event_post_type_args', $this->post_type_args );
            
            /**
             * @hook Filter
             * @filter ep_event_post_type
             * @param post_type name
             * @since 1.0.0
             */
            $this->post_type = apply_filters( 'ep_event_post_type', 'ep_events' );
            
            /**
             * Defining taxonomies
             */
            $this->taxonomies = array(
                                    array(
					'tax_name' => apply_filters( 'ep_event_category_tax', 'event_category' ),
					'name' => _x( 'Event Categories', 'taxonomy general name', 'ep' ),
					'singular_name' => _x( 'Event Category', 'taxonomy singular name', 'ep' ),
					'search_items' => __( 'Search Categories', 'ep' ),
					'all_items' => __( 'All Categories', 'ep' ),
					'parent_item' => __( 'Parent Category', 'ep' ),
					'parent_item_colon' => __( 'Parent Category:', 'ep' ),
					'edit_item' => __( 'Edit Category', 'ep' ),
					'update_item' => __( 'Update Category', 'ep' ),
					'add_new_item' => __( 'Add New Category', 'ep' ),
					'new_item_name' => __( 'New Category Name', 'ep' ),
					'menu_name' => __( 'Event Categories', 'ep' ),
					'rewrite' => array( 'slug' => 'event-category' ),
					'hierarchical' => true
				    ),
                                    array(
					'tax_name' => apply_filters( 'ep_event_tag_tax', 'event_tag' ),
					'name' => _x( 'Event Tags', 'taxonomy general name', 'ep' ),
					'singular_name' => _x( 'Event Tag', 'taxonomy singular name', 'ep' ),
					'search_items' => __( 'Search Tags', 'ep' ),
					'all_items' => __( 'All Tags', 'ep' ),
					'parent_item' => __( 'Parent Tag', 'ep' ),
					'parent_item_colon' => __( 'Parent Tag:', 'ep' ),
					'edit_item' => __( 'Edit Tag', 'ep' ),
					'update_item' => __( 'Update Tag', 'ep' ),
					'add_new_item' => __( 'Add New Tag', 'ep' ),
					'new_item_name' => __( 'New Tag Name', 'ep' ),
					'menu_name' => __( 'Event Tags', 'ep' ),
					'rewrite' => array( 'slug' => 'event-tag' ),
					'hierarchical' => false
				    )
                                );
            
            /**
             * @hook Filter
             * @filter ep_event_taxes_array
             * @param event taxonomies arguments
             * @since 1.0.0
             */
            $this->taxonomies = apply_filters( 'ep_event_taxes_array', $this->taxonomies );
            
            /**
             * Defining meta boxes for event post type
             */
            $this->meta_boxes = array(
                                    array(
					'id'            => 'event_details',
					'title'         => 'Event Details',
					'callback'      => array( &$this, 'event_details_meta_cb' ),
					'post_type'     => $this->post_type,
					'context'       => 'normal',
					'priority'      => 'high'
				    ),
                                    array(
					'id'            => 'event_child',
					'title'         => 'Event Instances',
					'callback'      => array( &$this, 'event_instances_meta_cb' ),
					'post_type'     => $this->post_type,
					'context'       => 'side',
					'priority'      => 'high'
				    ),
                                    array(
					'id'            => 'event_rsvp',
					'title'         => 'Event RSVP List',
					'callback'      => array( &$this, 'event_rsvp_meta_cb' ),
					'post_type'     => $this->post_type,
					'context'       => 'normal',
					'priority'      => 'high'
				    )
                                );
            
            /**
             * @hook Filter
             * @filter ep_event_meta_boxes
             * @param meta_boxes array
             * @since 1.0.0
             */
            $this->meta_boxes = apply_filters( 'ep_event_meta_boxes', $this->meta_boxes );
            
            /**
             * @hook Filter
             * @filter ep_status_list
             * @param status list array
             * @since 1.0.0
             */
            $this->statuses = apply_filters( 'ep_status_list', array( 'Open', 'Closed', 'Expired' ) );
            
            /**
             * @hook Filter
             * @filter p_types_list
             * @param types list array
             * @since 1.0.0
             */
            $this->types = apply_filters( 'ep_types_list', array( 'Free', 'Paid' ) );
            
            /**
             * Variable setting
             */
            $this->currency = 'USD';
            
            /**
             * Action and filter hooks calling
             */
            add_action( 'plugins_loaded', array( &$this, 'ep_load_textdomain' ) );
            register_activation_hook( __FILE__, array( &$this, 'ep_tables_install' ) );
            add_filter( 'wp_enqueue_scripts', array( &$this, 'front_ep_styles_scripts' ) );
            add_filter( 'admin_enqueue_scripts', array( &$this, 'admin_ep_styles_scripts' ) );
            add_action( 'init', array( &$this, 'ep_init' ), 1 );
            add_action( 'add_meta_boxes', array( &$this, 'register_event_meta_boxes' ) );
            add_action( 'save_post_' . $this->post_type, array( &$this, 'event_save_meta_box_data' ) );
            add_action( 'wp_trash_post', array( &$this, 'recurring_event_trash') );
            add_action( 'delete_post', array( &$this, 'recurring_event_delete') );
            add_action( 'admin_menu', array( &$this, 'register_event_settings_page' ) );
            add_action( 'admin_action_ep_events_settings', array( $this, 'ep_events_settings_cb' ) );
            add_filter( 'single_template', array( &$this, 'load_single_template' ) );
            add_filter( 'archive_template', array( &$this, 'load_archive_template' ) );
            add_action( 'template_redirect', array( &$this, 'ep_join_event_cb' ) );
            add_action( 'ep_event_notice', array( &$this, 'ep_event_notice_cb' ) );
	    
            
        }
        
        /**
         * Applying language file into the plugin
         *
         * @access public
         * @since 1.0.0
         */
        public function ep_load_textdomain() {
            load_plugin_textdomain( 'ep', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }
        
        /**
         * Install required event tables
         *
         * @access public
         * @since 1.0.0
         */
        public function ep_tables_install() {
            
            global $jal_db_version;
            $jal_db_version = '1.0';
            
            $charset_collate = $this->_db->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS {$this->event_tbl} (
                    id int(255) NOT NULL AUTO_INCREMENT,
                    event_id int(255) NOT NULL,
                    user_id int(255) NOT NULL,
                    event_paid varchar(255) NOT NULL,
                    user_paid int(10) NOT NULL,
                    PRIMARY KEY (id) ) $charset_collate;";
                    
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	    dbDelta( $sql );
            add_option( 'jal_db_version', $jal_db_version );
        }
        
        /**
         * Register event meta boxes
         */
        public function register_event_meta_boxes() {
            foreach( $this->meta_boxes as $meta_box ){
                add_meta_box(
                        $meta_box['id'],
                        $meta_box['title'],
                        $meta_box['callback'],
                        $meta_box['post_type'],
                        $meta_box['context'],
                        $meta_box['priority']
                );
            }
        }
        
        /**
         * Register styles and scripts for front end
         *
         * @since 1.0.0
         * @access public
         */
        public function front_ep_styles_scripts() {
            global $post;
            
            wp_enqueue_style( 'fontAwesome-css', EP_FILES_URI . '/assets/css/font-awesome-4.3.0/css/font-awesome.min.css', '', EP_VERSION );
            wp_enqueue_style( 'epstrap-css', EP_FILES_URI . '/assets/css/epstrap.css', '', EP_VERSION );
            wp_enqueue_style( 'epfront-css', EP_FILES_URI . '/assets/css/epfront.css', '', EP_VERSION );
            wp_enqueue_style( 'event-css', EP_FILES_URI . '/assets/css/event-front.css', '', EP_VERSION );
            
            wp_enqueue_script( 'ep-front-js', EP_FILES_URI . '/assets/js/ep-front.js', array( 'jquery' ), EP_VERSION, true );
            wp_enqueue_script( 'event-front-js', EP_FILES_URI . '/assets/js/event-front.js', array( 'jquery' ), EP_VERSION, true );
            
            wp_localize_script(
                            'event-front-js',
                            'obj',
                            array(
                                'event_url'     => get_permalink( $post->ID ),
                                'logged'        => is_user_logged_in(),
                                'login_url'     => wp_login_url( get_permalink( $post->ID ) ),
                                'join_text'     => __( 'Are you sure you want to join?', 'ep' ),
                                'cancel_text'   => __( 'Are you sure you want to cancel?', 'ep' ),
                                'log_msg'       => __( 'Please login to join in this event', 'ep' ),
                            )
                        );
        }
        
        /**
         * Register styles and scripts for admin end
         *
         * @since 1.0.0
         * @access public
         */
        public function admin_ep_styles_scripts() {
            wp_enqueue_style( 'epstrap-css', EP_FILES_URI . '/assets/css/epstrap.css', '', EP_VERSION );
            wp_enqueue_style( 'ep-admin-css', EP_FILES_URI . '/assets/css/ep-admin.css', '', EP_VERSION );
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_style( 'jquery-ui-css', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', '', EP_VERSION );
            wp_enqueue_style( 'event-admin-css', EP_FILES_URI . '/assets/css/event-admin.css', '', EP_VERSION );
            
            
            wp_enqueue_script( 'ep-admin-js', EP_FILES_URI . '/assets/js/ep-admin.js', array( 'jquery' ), EP_VERSION, true );
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_script( 'ep-timepicker-js', EP_FILES_URI . '/assets/js/jquery-ui-timepicker-addon.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-effects-clip' ), EP_VERSION, true );
            wp_enqueue_script( 'ep-admin-main-js', EP_FILES_URI . '/assets/js/event-admin.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-effects-clip', 'wp-color-picker' ), EP_VERSION, true );
            
        }
        
        /**
         * Initialization of Events post type, taxonomies etc
         *
         * @access public
         * @since 1.0.0
         */
        public function ep_init() {
            /**
             * Registering post type
             *
             * @hook filter
             * @filter ep_{post_type}_post_type_args
             * @var ARRAY Post type arguments
             */
            register_post_type( 
                $this->post_type, 
                apply_filters( 'ep_' . $this->post_type . '_post_type_args', $this->post_type_args ) 
            );
            
            /**
             * Registering event taxonomies
             */
            foreach( $this->taxonomies as $taxonomy ){
                register_taxonomy(
                                $taxonomy['tax_name'],
                                array( $this->post_type ),
                                $taxonomy
                            );
            }
            
            /**
             * Registering new post statuses
             */
            register_post_status( 'recurring-event' );
            register_post_status( 'recurring-trash' );

        }
        
        /**
         * Event details meta box
         *
         * @since 1.0.0
         * @access public
         *
         * @param OBJECT $post object of current post
         */
        public function event_details_meta_cb( $post ) {
            
            $event = new EP_Event( $post->ID );
	    wp_nonce_field( 'event_meta_box', 'event_meta_box_nonce' );
            
            ?>
            <div class="event_meta_fieldset">
                <h3 class="hndle dg-text-center"><span><?php _e( 'Event Type', 'ep' ) ?></span></h3>
                    <div class="dg-content">
                        <div class="dg-row dg-top-space dg-form-group">
                            <div class="dg-col-md-6 dg-text-center">
                                <label><input class="event_instance_chooser" type="radio" name="event_meta[instance]" value="single" <?php echo ( isset( $event->instance ) && $event->instance == 'single' ) || ! isset( $event->instance ) ? 'checked="checked"' : ''; ?>>
                                    <?php _e( 'Single Event', 'ep' ) ?>
                                </label>
                            </div>
                            <div class="dg-col-md-6 dg-text-center">
                                <label><input class="event_instance_chooser" type="radio" name="event_meta[instance]" value="recurring-event" <?php echo ( isset( $event->instance ) && $event->instance == 'recurring-event' ) ? 'checked="checked"' : ''; ?>>
                                    <?php _e( 'Recurring Event', 'ep' ) ?>
                                </label>
                            </div>
                    </div>
                </div>
                <hr>
                <h3 class="hndle dg-text-center"><span><?php _e( 'Event Date and Time', 'ep' ) ?></span></h3>
                <div class="instance_meta single_event_meta" <?php echo ( isset( $event->instance ) && $event->instance == 'recurring-event' ) ? 'style="display:none"' : ''; ?>>
                    <div class="dg-content">
                        <div class="dg-row dg-form-group">
                            <div class="dg-col-md-8 dg-text-center">
                                <input class="dg_datepicker dg-form-control" type="text" name="event_meta[start_date]" value="<?php echo isset( $event->start_date ) ? $event->start_date : ''; ?>" placeholder="<?php _e( 'Select Event Start Date', 'ep' ) ?>">
                            </div>
                            <div class="dg-col-md-4 dg-text-center">
                                <input class="dg_timepicker dg-form-control" type="text" name="event_meta[start_time]" value="<?php echo isset( $event->start_time ) ? $event->start_time : ''; ?>" placeholder="<?php _e( 'Select Event Start Time', 'ep' ) ?>">
                            </div>
                            <div class="dg-col-md-8 dg-text-center">
                                <input class="dg_datepicker dg-form-control" type="text" name="event_meta[end_date]" value="<?php echo isset( $event->end_date ) ? $event->end_date : ''; ?>" placeholder="<?php _e( 'Select Event End Date', 'ep' ) ?>">
                            </div>
                            <div class="dg-col-md-4 dg-text-center">
                                <input class="dg_timepicker dg-form-control" type="text" name="event_meta[end_time]" value="<?php echo isset( $event->end_time ) ? $event->end_time : ''; ?>" placeholder="<?php _e( 'Select Event End Time', 'ep' ) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="event_meta_filed">
                    <div class="instance_meta recurring-event_event_meta" <?php echo ( isset( $event->instance ) && $event->instance == 'recurring-event' ) ? 'style="display:block"' : 'style="display: none"'; ?>>
                        <div class="dg-content">
                            <div class="dg-row dg-form-group">
                                <div class="dg-col-md-8 dg-text-center">
                                    <input class="dg_datepicker dg-form-control" type="text" name="event_meta[rec_start_from]" value="<?php echo isset( $event->rec_start_from ) ? $event->rec_start_from : ''; ?>" placeholder="<?php _e( 'Select Event Start Date', 'ep' ) ?>">
                                </div>
                                <div class="dg-col-md-4 dg-text-center">
                                    <input class="dg_timepicker dg-form-control" type="text" name="event_meta[rec_day_start_time]" value="<?php echo isset( $event->rec_day_start_time ) ? $event->rec_day_start_time : ''; ?>" placeholder="<?php _e( 'Select Event Start Time', 'ep' ) ?>">
                                </div>
                            </div>
                            <div class="dg-row dg-form-group dg-top-space">
                                <div class="dg-col-md-12 dg-text-center">
                                    <?php _e( 'Repetation On Every', 'ep' ) ?>
                                </div>
                                <div class="dg-col-md-12 dg-text-center">
                                    <select name="event_meta[rec_repeat]" class="rec_repeat dg-form-control dg-text-center">
                                        <option value=""><?php _e( 'Select', 'ep' ) ?></option>
                                        <option <?php echo isset( $event->rec_repeat ) && $event->rec_repeat == 'day' ? 'selected="selected"' : ''; ?> value="day"><?php _e( 'Day', 'ep' ) ?></option>
                                        <option value="week"><?php _e( 'Week', 'ep' ) ?></option>
                                        <option value="month"><?php _e( 'Month', 'ep' ) ?></option>
                                        <option value="year"><?php _e( 'Year', 'ep' ) ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="dg-row dg-form-group">
                                <div class="dg-col-md-12 dg-text-center">
                                    <div class="rec_repeat_meta">
                                        <div class="rec_repeat_ind_meta rec_meta_day">
                                        </div>
                                        <div class="rec_repeat_ind_meta rec_meta_week">
                                            <label>
                                                <input type="checkbox" name="event_meta[rec_week_day][]" value="1">
                                                <?php _e( 'Monday', 'ep' ) ?>
                                            </label>
                                            <label>
                                                <input type="checkbox" name="event_meta[rec_week_day][]" value="2">
                                                <?php _e( 'Tuesday', 'ep' ) ?>
                                            </label>
                                            <label>
                                                <input type="checkbox" name="event_meta[rec_week_day][]" value="3">
                                                <?php _e( 'Wednesday', 'ep' ) ?>
                                            </label>
                                            <label>
                                                <input type="checkbox" name="event_meta[rec_week_day][]" value="4">
                                                <?php _e( 'Thursday', 'ep' ) ?>
                                            </label>
                                            <label>
                                                <input type="checkbox" name="event_meta[rec_week_day][]" value="5">
                                                <?php _e( 'Friday', 'ep' ) ?>
                                            </label>
                                            <label>
                                                <input type="checkbox" name="event_meta[rec_week_day][]" value="6">
                                                <?php _e( 'Saturday', 'ep' ) ?>
                                            </label>
                                            <label>
                                                <input type="checkbox" name="event_meta[rec_week_day][]" value="7">
                                                <?php _e( 'Sunday', 'ep' ) ?>
                                            </label>
                                        </div>
                                        <div class="rec_repeat_ind_meta rec_meta_month">
                                            <?php _e( 'On ', 'ep' ) ?><br>
                                            <input type="text" name="event_meta[rec_month_date]" value="<?php echo isset( $event->rec_month_date ) ? $event->rec_month_date : ''; ?>" class="dg-form-control">
                                        </div>
                                        <div class="rec_repeat_ind_meta rec_meta_year">
                                            <div class="dg-col-md-6">
                                                <div class="dg-col-md-12"><?php _e( 'Month', 'ep' ) ?></div>
                                                <div class="dg-col-md-12">
                                                    <select name="event_meta[rec_year_month]" class="dg-form-control">
                                                        <option value="1"><?php _e( 'January', 'ep' ) ?></option>
                                                        <option value="2"><?php _e( 'February', 'ep' ) ?></option>
                                                        <option value="3"><?php _e( 'March', 'ep' ) ?></option>
                                                        <option value="4"><?php _e( 'April', 'ep' ) ?></option>
                                                        <option value="5"><?php _e( 'May', 'ep' ) ?></option>
                                                        <option value="6"><?php _e( 'June', 'ep' ) ?></option>
                                                        <option value="7"><?php _e( 'July', 'ep' ) ?></option>
                                                        <option value="8"><?php _e( 'August', 'ep' ) ?></option>
                                                        <option value="9"><?php _e( 'September', 'ep' ) ?></option>
                                                        <option value="10"><?php _e( 'October', 'ep' ) ?></option>
                                                        <option value="11"><?php _e( 'November', 'ep' ) ?></option>
                                                        <option value="12"><?php _e( 'December', 'ep' ) ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="dg-col-md-6">
                                                <div class="dg-col-md-12"><?php _e( 'Date', 'ep' ) ?></div>
                                                <div class="dg-col-md-12">
                                                    <input type="text" name="event_meta[rec_year_date]" value="<?php echo isset( $event->rec_year_date ) ? $event->rec_year_date : ''; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="dg-col-md-8 dg-text-center">
                                    <input class="dg_datepicker dg-form-control" type="text" name="event_meta[rec_end_at]" value="<?php echo isset( $event->rec_end_at ) ? $event->rec_end_at : ''; ?>" placeholder="<?php _e( 'Select Event End Date', 'ep' ) ?>">
                                </div>
                                <div class="dg-col-md-4 dg-text-center">
                                    <input class="dg_timepicker dg-form-control" type="text" name="event_meta[rec_end_time]" value="<?php echo isset( $event->rec_end_time ) ? $event->rec_end_time : ''; ?>" placeholder="<?php _e( 'Select Event End Time', 'ep' ) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h3 class="hndle dg-text-center"><span><?php _e( 'Event Status & Type', 'ep' ) ?></span></h3>
                <div class="instance_meta_status">
                    <div class="dg-content">
                        <div class="dg-row dg-form-group">
                            <div class="dg-col-md-6">
                                <div class="dg-col-md-12 dg-text-center">
                                    <h3><?php _e( 'What is the event status?', 'ep' ) ?></h3>
                                </div>
                                <div class="dg-col-md-12 dg-text-center">
                                    <select name="event_meta[status]" class="dg-form-control dg-text-center">
                                        <?php foreach( $this->statuses as $status ) { ?>
                                                <option <?php echo checked_select( $status, isset( $event->status ) ? $event->status : '' ) ?> value="<?php echo $status ?>"><?php echo $status ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="dg-col-md-6">
                                <div class="dg-col-md-12 dg-text-center">
                                    <h3><?php _e( 'Is it a paid event?', 'ep' ) ?></h3>
                                </div>
                                <div class="dg-col-md-12 dg-text-center">
                                    <select name="event_meta[type]" class="event_meta_type dg-form-control dg-text-center">
                                            <?php foreach( $this->types as $type ) { ?>
                                            <option <?php echo checked_select( $type, isset( $event->type ) ? $event->type : '' ) ?> value="<?php echo $type ?>"><?php echo $type ?></option>
                                            <?php } ?>
                                    </select>
                                </div>
                                <div class="event_price_meta dg-col-md-12 dg-text-center" style="display: <?php echo isset( $event->fees ) ? 'block' : 'none'; ?>">
                                    <?php _e( 'Ticket Price: ', 'ep' ) ?><?php echo $this->currency; ?><br>
                                    <input type="text" name="event_meta[fees]" value="<?php echo isset( $event->fees ) ? $event->fees : ''; ?>" class="dg-form-control">
                                </div>
                                <div class="dg-col-md-12 dg-text-center"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        
        /**
         * Show instances for recurring events
         *
         * @since 1.0.0
         * @access public
         *
         * @var OBJECT post object of current post
         */
        public function event_instances_meta_cb( $post ){
            $event = new EP_Event( $post->ID );
            if( ! isset( $event->child_events->posts ) ){
                _e( '<p>This part is only for recurring events</p>', 'ep' );
            }else{
            ?>
            <ul>
                <?php
                foreach( $event->child_events->posts as $child_event ) {
                    $child_obj = new EP_Event( $child_event->ID );
                    ?>
                    <li><a href="<?php echo admin_url( sprintf( 'post.php?post=%d&action=edit', $child_event->ID ) ) ?>"><?php echo $child_event->post_title ?> - <?php echo $child_obj->start_date ?></a></li>
                    <?php
                }
                ?>
            </ul>
            <?php
            }
        }
        
        /**
         * Show registered member list
         *
         * @since 1.0.0
         * @access public
         *
         * @var OBJECT post object of current post
         */
        public function event_rsvp_meta_cb( $post ){
            
        }
        
        /**
         * Save meta values of an event
         *
         * @since 1.0.0
         * @access public
         *
         * @var INT $post_id ID of the event
         */
        public function event_save_meta_box_data( $post_id ){
            // Check if our nonce is set.
            if ( ! isset( $_POST['event_meta_box_nonce'] ) ) {
                return;
            }
            
            // Verify that the nonce is valid.
            if ( ! wp_verify_nonce( $_POST['event_meta_box_nonce'], 'event_meta_box' ) ) {
                return;
            }

            // If this is an autosave, our form has not been submitted, so we don't want to do anything.
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }
            
            $event = new EP_Event( $post_id );
            $event->update_post_meta( $_POST['event_meta'] );
            update_post_meta( $post_id, 'ep_event_start_date', $_POST['event_meta']['start_date'] );

            $post_thumbnail_id = get_post_thumbnail_id( $post_id );

            if( ( $_POST['post_status'] == 'publish' ) && ( $_POST['original_post_status'] != 'publish' ) ) {
                remove_action( 'save_post_' . $this->post_type, array( &$this, 'event_save_meta_box_data' ) );
                
                if( $event->instance == 'recurring-event' ){
                    $up_event_meta = $_POST['event_meta'];
                    $up_event_meta['start_date'] = $event->rec_start_from;
                    $up_event_meta['end_date'] = $event->rec_start_from;
                    $up_event_meta['start_time'] = $_POST['event_meta']['rec_day_start_time'];
                    $up_event_meta['end_time'] = $_POST['event_meta']['rec_end_time'];
                    $up_event_meta['instance'] = 'single';
                    $event->update_post_meta( $up_event_meta );

                    $begin = $event->rec_start_from;
                    $end_date = $event->rec_end_at;

                    switch ( $event->rec_repeat ) {
                        case 'day':
                            while (strtotime($begin) <= strtotime($end_date)) {

                                $args = array(
                                    'post_title'	=> $event->get_title(),
                                    'post_status'	=> 'recurring-event',
                                    'post_content'	=> $event->get_content(),
                                    'post_type'		=> $this->post_type,
                                    'post_author'	=> $event->get_author(),
                                    'post_parent'	=> $event->get_id()
                                );

                                $new_event_id = insert_post( $args );
                                $new_event = new EP_Event( $new_event_id );

                                $new_event_meta = $_POST['event_meta'];
                                $new_event_meta['start_date'] = $begin;
                                $new_event_meta['end_date'] = $begin;
                                $new_event_meta['start_time'] = $_POST['event_meta']['rec_day_start_time'];
                                $new_event_meta['end_time'] = $_POST['event_meta']['rec_end_time'];
                                $new_event_meta['instance'] = 'single';

                                set_post_thumbnail( $new_event_id, $post_thumbnail_id );

                                $new_event->update_post_meta( $new_event_meta );
                                $nmeta = date( "Y-m-d", strtotime( $begin ) );
                                update_post_meta( $new_event_id, 'ep_event_start_date', $nmeta );

                                $begin = date ( "d-m-Y", strtotime( "+1 day", strtotime( $begin ) ) );

                            }
                        break;

                        case 'week':
                            while (strtotime($begin) <= strtotime($end_date)) {
                                if( in_array( date( 'N', strtotime( $begin ) ), $_POST['event_meta']['rec_week_day'] ) ){
                                    $args = array(
                                        'post_title'	=> $event->get_title(),
                                        'post_status'	=> 'recurring-event',
                                        'post_content'	=> $event->get_content(),
                                        'post_type'     => $this->post_type,
                                        'post_author'	=> $event->get_author(),
                                        'post_parent'	=> $event->get_id()
                                    );

                                    $new_event_id = insert_post( $args );
                                    $new_event = new EP_Event( $new_event_id );

                                    $new_event_meta = $_POST['event_meta'];
                                    $new_event_meta['start_date'] = $begin;
                                    $new_event_meta['end_date'] = $begin;
                                    $new_event_meta['start_time'] = $_POST['event_meta']['rec_day_start_time'];
                                    $new_event_meta['end_time'] = $_POST['event_meta']['rec_end_time'];
                                    $new_event_meta['instance'] = 'single';

                                    set_post_thumbnail( $new_event_id, $post_thumbnail_id );

                                    $new_event->update_post_meta( $new_event_meta );
                                    $nmeta = date( "Y-m-d", strtotime( $begin ) );
                                    update_post_meta( $new_event_id, 'ep_event_start_date', $nmeta );
                                }
                                $begin = date ("d-m-Y", strtotime("+1 day", strtotime($begin)));
                            }
                        break;

                        case 'month':

                            while (strtotime($begin) <= strtotime($end_date)) {
                                if( date( 'j', strtotime( $begin ) ) == $_POST['event_meta']['rec_month_date'] ){
                                    $args = array(
                                        'post_title'	=> $event->get_title(),
                                        'post_status'	=> 'recurring-event',
                                        'post_content'	=> $event->get_content(),
                                        'post_type'	=> $this->post_type,
                                        'post_author'	=> $event->get_author(),
                                        'post_parent'	=> $event->get_id()
                                    );

                                    $new_event_id = insert_post( $args );
                                    $new_event = new EP_Event( $new_event_id );

                                    $new_event_meta = $_POST['event_meta'];
                                    $new_event_meta['start_date'] = $begin;
                                    $new_event_meta['end_date'] = $begin;
                                    $new_event_meta['start_time'] = $_POST['event_meta']['rec_day_start_time'];
                                    $new_event_meta['end_time'] = $_POST['event_meta']['rec_end_time'];
                                    $new_event_meta['instance'] = 'single';

                                    set_post_thumbnail( $new_event_id, $post_thumbnail_id );

                                    $new_event->update_post_meta( $new_event_meta );
                                    $nmeta = date( "Y-m-d", strtotime( $begin ) );
                                    update_post_meta( $new_event_id, 'ep_event_start_date', $nmeta );
                                }
                                $begin = date ("d-m-Y", strtotime("+1 day", strtotime($begin)));
                            }

                        break;

                        case 'year':

                            while (strtotime($begin) <= strtotime($end_date)) {
                                if( date( 'n', strtotime( $begin ) ) == $_POST['event_meta']['rec_year_month'] && date( 'j', strtotime( $begin ) ) == $_POST['event_meta']['rec_year_date'] ){
                                    $args = array(
                                        'post_title'	=> $event->get_title(),
                                        'post_status'	=> 'recurring-event',
                                        'post_content'	=> $event->get_content(),
                                        'post_type'	=> $this->post_type,
                                        'post_author'	=> $event->get_author(),
                                        'post_parent'	=> $event->get_id()
                                    );

                                    $new_event_id = insert_post( $args );
                                    $new_event = new EP_Event( $new_event_id );

                                    $new_event_meta = $_POST['event_meta'];
                                    $new_event_meta['start_date'] = $begin;
                                    $new_event_meta['end_date'] = $begin;
                                    $new_event_meta['start_time'] = $_POST['event_meta']['rec_day_start_time'];
                                    $new_event_meta['end_time'] = $_POST['event_meta']['rec_end_time'];
                                    $new_event_meta['instance'] = 'single';

                                    set_post_thumbnail( $new_event_id, $post_thumbnail_id );

                                    $new_event->update_post_meta( $new_event_meta );
                                    $nmeta = date( "Y-m-d", strtotime( $begin ) );
                                    update_post_meta( $new_event_id, 'ep_event_start_date', $nmeta );
                                }
                                $begin = date ("d-m-Y", strtotime("+1 day", strtotime($begin)));
                            }

                        break;

                        default:
                                # code...
                        break;
                    }
                }
                add_action( 'save_post_' . $this->post_type, array( &$this, 'event_save_meta_box_data' ) );
            }
        }
        
        /**
         * Trash a recurring event
         *
         * @since 1.0.0
         * @access public
         */
        public function recurring_event_trash() {
            global $post;
            if( $post->post_type == $this->post_type ) {
                $args = array(
                    'post_parent' => $post->ID,
                    'post_status' => 'recurring-event',
                    'post_type' => $this->post_type,
                );

                $child = get_children( $args );
                if( count($child) > 0 ) {
                    remove_action( 'wp_trash_post', array( &$this, 'recurring_event_trash' ) );
                    foreach( $child as $value ) {
                        $arg = array(
                            'ID'		=> $value->ID,
                            'post_status'	=> 'recurring-trash'
                        );
                        wp_update_post( $arg );
                    }
                    add_action( 'wp_trash_post', array( &$this, 'recurring_event_trash' ) );
                }
            }
        }
        
        /**
         * Delete a recurring event
         *
         * @since 1.0.0
         * @access public
         */
        public function recurring_event_delete() {
            global $post;
            if( $post->post_type == $this->post_type ) {
                $args = array(
                    'post_parent' => $post->ID,
                    'post_status' => 'recurring-event',
                    'post_type' => $this->post_type,
                );

                $child = get_children( $args );
                if( count($child) > 0 ) {
                    remove_action( 'delete_post', array( &$this, 'recurring_event_delete' ) );
                    foreach( $child as $value ) {
                        wp_delete_post($value->ID, true);
                    }
                    add_action( 'delete_post', array( &$this, 'recurring_event_delete' ) );
                }
            }
        }
        
        /**
         * Load single template for event
         *
         * @since 1.0.0
         * @access public
         */
        public function load_single_template( $template ){
            global $post;

            if( $post->post_type == $this->post_type )
                return EP_Event_Template_Handler::load_single_template( $this->post_type );

            return $template;
        }
        
        /**
         * Load archive template for event
         *
         * @since 1.0.0
         * @access public
         */
        public function load_archive_template( $template ) {
	    $ep = $this->_data->get_options();
	    if ( is_post_type_archive ( $this->post_type ) ){
		if ( ! empty( $ep ) ){
		    if( $ep['archive_template'] == 'list' ){
			return EP_Event_Template_Handler::load_archive_template( $this->post_type );
		    }
		    else{
			return EP_Event_Template_Handler::load_monthly_calendar_template( $this->post_type );
		    }
		}
	    }
            return $template;
	}
        
        /**
         * Get event archive
         *
         * @since 1.0.0
         * @access public
         *
         * @return ARRAY Event list
         */
	public function event_archive(){
	    $events = new WP_Query(
			array(
			    'post_type'    => $this->post_type,
			    'post_status'  => array( 'publish', 'recurring-event' ),
			    'posts_per_page'	=> apply_filters( 'ep_show_events_number', 30 )
			)
		    );
            $event_archive = array();
            foreach($events->posts as $value){
                $event = new EP_Event( $value->ID );
                $event_time =  strtotime( $event->start_date );
                if( $event_time > time() ){
                    $event_archive[] = $event;
                }
            }
            return $event_archive;
	}
        
        /**
         * Event Monthly Calendar
         *
         * @since 1.0
         * @access public
         *
         * @param INT $month Month number
         * @param INT $year Year
         */
	public function ep_draw_calendar( $month = '', $year = '' ){
            if( $month == '' ) $month = date( 'm' );
            if( strlen( $month ) == 1 ) $month = '0' . $month;
            if( $year =='' ) $year = date( 'Y' );

            /* draw table */
            $calendar = '<div class="custom-calendar-wrap custom-calendar-full">';

            $ep_prev_year = $ep_next_year = $year;

            $prev_month = $month - 1;
            if( $prev_month == 0 ){
		$prev_month = 12;
		$ep_prev_year = $year - 1;
	    }

            $next_month = $month + 1;
            if( $next_month == 13 ){
		$next_month = 1;
		$ep_next_year = $year + 1;
	    }

            $cal_year = apply_filters(
                    'ep_cal_year_list',
                    $year
                    );

            $cal_month = apply_filters(
                    'ep_cal_month_list',
                    array(
                            __( 'January', 'eventpress' ),
                            __( 'February', 'eventpress' ),
                            __( 'March', 'eventpress' ),
                            __( 'April', 'eventpress' ),
                            __( 'May', 'eventpress' ),
                            __( 'June', 'eventpress' ),
                            __( 'July', 'eventpress' ),
                            __( 'August', 'eventpress' ),
                            __( 'September', 'eventpress' ),
                            __( 'October', 'eventpress' ),
                            __( 'November', 'eventpress' ),
                            __( 'December', 'eventpress' ),
                            )
                    );

            $calendar .= '<div class="ep-cal-month-year">';
            $calendar .= apply_filters( 'ep_cal_date_format', '<h2>' . __( 'Events at ', 'eventpress' ) . $cal_year . " - " . $cal_month[$month - 1] . '</h2>', $cal_year, $cal_month, $month );
            $calendar .= '</div>';

            $calendar .= '<div class="cal_nav"><ul>';
            $calendar .= '<li class="cal_prev"><a href="' . add_query_arg( array( 'ep_month' => $prev_month, 'ep_year' => $ep_prev_year ) ) . '">'. __( 'Previous Month', 'eventpress' ) . '</a></li>';
            $calendar .= '<li class="cal_next"><a href="' . add_query_arg( array( 'ep_month' => $next_month, 'ep_year' => $ep_next_year ) ) . '">'. __( 'Next Month', 'eventpress' ) . '</a></li>';
            $calendar .= '</ul></div>';

            $calendar .= '<div class="fc-calendar-container" id="calendar"><div class="fc-calendar fc-five-rows">';

            /* table headings */
            $headings = array( 'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday' );
            $calendar .= '<div class="fc-head"><div>' . implode( '</div><div>',$headings ) . '</div></div>';

            /* days and weeks vars now ... */
            $running_day = date( 'w', mktime( 0, 0, 0, $month, 1, $year ) );
            $days_in_month = date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
            $days_in_this_week = 1;
            $day_counter = 0;
            $dates_array = array();

            /* row for week one */
            $calendar.= '<div class="fc-body"><div class="fc-row">';

            /* print "blank" days until the first of the current week */
            for($x = 0; $x < $running_day; $x++){
                $calendar .= '<div></div>';
                $days_in_this_week++;
            }

	    /* keep going with days.... */
            for($list_day = 1; $list_day <= $days_in_month; $list_day++){
                $calendar .= '<div>';
                /* add in the day number */
                $calendar .= '<div>';
                $calendar .= '<span class="fc-date">' . $list_day . '</span>';

		if( strlen( $list_day ) == 1 ) $list_day = '0' . $list_day;
                    $args = array(
                            'post_type'  => $this->post_type,
                            'post_status' => array( 'publish', 'recurring' ),
                            'posts_per_page' => -1,
                            'meta_query' => array(
                                    array(
                                            'key'     => 'ep_event_start_date',
                                            'value'   => "{$year}-{$month}-{$list_day}",
                                            'compare' => '=',
                                            ),
                                    ),
                            );

                $query = new WP_Query( $args );

                $calendar .= '<div class="cal_event">';
                foreach( $query->posts  as $post ) {
                    $calendar .= '<p><a href="' . get_permalink( $post->ID ) . '">' . $post->post_title . '</a></p>';
                }
                $calendar .= '</div>';
                $calendar .= '</div>';
                $calendar .= '</div>';
                
                if($running_day == 6){
                    if(($day_counter+1) != $days_in_month){
                        $calendar .= '</div>';
                        $calendar .= '<div class="fc-row">';
                    }
                    $running_day = -1;
                    $days_in_this_week = 0;
                }
                $days_in_this_week++; $running_day++; $day_counter++;
            }

            /* finish the rest of the days in the week */
            if($days_in_this_week < 8 && $days_in_this_week > 1){
                for($x = 1; $x <= (8 - $days_in_this_week); $x++){
                    $calendar .= '<div></div>';
                }
            }

            /* final row */
            $calendar .= '</div></div>';

            /* end the table */
            $calendar .= '</div></div></div>';

            /* all done, return result */
            return $calendar;

        }
    
        
        /**
         * Registering event settings page
         *
         * @since 1.0.0
         * @access public
         */
        public function register_event_settings_page() {
            add_submenu_page(
                            'edit.php?post_type=' . $this->post_type,
                            __( 'Event Settings', 'ep' ),
                            __( 'Event Settings', 'ep' ),
                            'manage_options',
                            'event-settings',
                            array( &$this, 'event_settings_cb' )
                        );
        }
        
        /**
         * Event settings page content
         *
         * @since 1.0.0
         * @access public
         */
        public function event_settings_cb() {
            $ep = $this->_data->get_options();
            ?>
            <div class="wrap duo_prod_panel">
                <h2><?php _e( 'Event Settings', 'ep' ) ?></h2>
                <!-- Any message like notice -->
                <?php if( isset( $_REQUEST['msg'] ) ) { ?>
                <div id="message" class="<?php echo isset( $_REQUEST['notice_result'] ) ? $_REQUEST['notice_result'] : 'updated' ?> below-h2">	<p><?php echo str_replace( '+', ' ', $_REQUEST['msg'] ) ?></p>
                </div>
                <?php } ?>

                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content">
                            <div class="event_settings_menu">
                                <ul>
                                    <li><a class="<?php echo ! isset( $_REQUEST['tab'] ) ? 'active' : '' ?>" href="<?php echo admin_url( 'edit.php?post_type=ep_events&page=event-settings' ) ?>"><?php _e( 'Settings', 'ep' ) ?></a></li>
                                    <li><a class="<?php echo isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'addons' ? 'active' : '' ?>" href="<?php echo admin_url( 'edit.php?post_type=ep_events&page=event-settings&tab=addons' ) ?>"><?php _e( 'Addons', 'ep' ) ?></a></li>
                                </ul>
                            </div>
                            
                            <!-- If no tab is selected -->
                            <?php if( ! isset( $_REQUEST['tab'] ) ) { ?>

                            <form action="<?php echo admin_url( 'admin.php?action=ep_events_settings' ) ?>" method="post">
                                <?php wp_nonce_field( 'ep_ev_nonce_action', 'ep_ev_nonce_field' ); ?>
                                <div class="postbox">
                                    <h3 class="hndle"><?php _e( 'General Settings', 'ep' ) ?></h3>
                                    <div class="inside">
                                        <table class="form-table">
                                            <tr>
                                                <th><?php _e( 'Display RSVP List in front end?', 'ep' ) ?></th>
                                                    <td>
                                                        <input <?php if( isset($ep['show_rsvp_front']) ){ checked( $ep['show_rsvp_front'], 1, true ); } ?> type="checkbox" name="ep[show_rsvp_front]" value="1">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php _e( 'Choose a theme color?', 'ep' ) ?></th>
                                                    <td>
                                                        <input type="text" class="wp-admin-color-picker" name="ep[color_theme]" class="small_box" value="<?php echo isset( $ep['color_theme'] ) ? $ep['color_theme'] : '#f35333' ?>" />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php _e( 'Choose archive template:', 'ep' ) ?></th>
                                                    <td>
                                                        <select name="ep[archive_template]">
                                                            <option value=""><?php _e( 'Select a template', 'ep' ) ?></option>
                                                            <option <?php echo isset( $ep['archive_template'] ) && $ep['archive_template'] == 'list' ? 'selected="selected"' : ''  ?> value="list"><?php _e( 'List Template', 'ep' ) ?></option>
                                                            <option <?php echo isset( $ep['archive_template'] ) && $ep['archive_template'] == 'calendar' ? 'selected="selected"' : ''  ?> value="calendar"><?php _e( 'Calendar Template', 'ep' ) ?></option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Action hook for addons -->
                                    <?php do_action( 'ep_event_settings_addon' ); ?>

                                    <input type="submit" name="ep[eam_event_settings]" value="<?php _e( 'Save Settings', 'ep' ); ?>" class="button button-primary">
                                </form>

                                <!-- If add on tab -->
                                <?php }elseif( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'addons' ) { ?>

                                <?php do_action( 'ep_event_addons_list' ); ?>

                            <?php } ?>

                        </div>
                        <!-- Settings Sidebar -->
                        <div class="postbox-container" id="postbox-container-1">
                            <?php do_action( 'dg_settings_sidebar', 'free', 'ep', 'https://wordpress.org/support/view/plugin-reviews/wp-eventpress?rate=5#postform' ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        
        /**
         * Function to save event settings
         *
         * @since 1.0.0
         * @access public
         */
        public function ep_events_settings_cb() {
            if( isset( $_POST['ep'] ) ){
                if ( ! check_admin_referer( 'ep_ev_nonce_action', 'ep_ev_nonce_field' )){
                    return;
                }
                $ep = $_POST['ep'];
                
                if( ! isset( $ep['show_rsvp_front'] ) ) $ep['show_rsvp_front'] = 0;
                if( ! isset( $ep['enable_payment'] ) ) $ep['enable_payment'] = 0;
                
                $ep = apply_filters( 'ep_event_settings_save_before', $ep );
                $this->_data->set_options( $ep );

                wp_redirect( admin_url( 'edit.php?post_type=ep_events&page=event-settings&notice_result=updated&msg=' . __( 'Settings+saved+successfully.', 'eventpress' ) ) );
            }
        }
        
        /**
         * Register, cancel etc function on an user to an event
         *
         * @since 1.0.0
         * @access public
         */
        public function ep_join_event_cb() {
            
            global $post;
            $user_id = get_current_user_id();
            $event = new EP_Event( $post->ID );
            $meta = $event->get_post_meta();
            
            if( isset( $_REQUEST['ep_join_event'] ) && $_REQUEST['ep_join_event'] == 'yes' ){
                // Check if joining an event is allowed
                if( apply_filters( 'ep_allow_join_rsvp', $event->can_rsvp ) == false ){
                    wp_redirect( get_permalink( $post->ID ) . '?ep_join_not_allowed=yes' );
                    die();
                }
                
                // Check if user already joined
                $query = "SELECT * from {$this->event_tbl} WHERE event_id = '{$post->ID}' AND user_id = '{$user_id}'";
                $res = $this->_db->get_results( $query, OBJECT );
                if( $this->_db->num_rows > 0 ){
                    wp_redirect( get_permalink( $post->ID ) . '?ep_user_already_joined=yes' );
                    die();
                }
                
                // Insert into event table
                $this->_db->insert(
                                $this->event_tbl,
                                array(
                                    'event_id'      => $post->ID,
                                    'user_id'       => $user_id,
                                    'event_paid'    => $meta->type,
                                    'user_paid'     => 0
                                )
                            );
                do_action( 'ep_user_join_rsvp', $meta, $post->ID, $user_id );
                wp_redirect( get_permalink( $post->ID ) . '?ep_user_joined_successful=yes' );
                die();
            }
            
            if( isset( $_REQUEST['ep_join_event_cancel'] ) && $_REQUEST['ep_join_event_cancel'] == 'yes' ){
                // Check if cancelling an event is allowed
                if( apply_filters( 'ep_allow_cancel_rsvp', false ) == true ){
                    wp_redirect( get_permalink( $post->ID ) . '?ep_cancel_not_allowed=yes' );
                    die();
                }
                
                // Delete the user from this event
                $this->_db->delete(
                                $this->event_tbl,
                                array(
                                    'event_id'  => $post->ID,
                                    'user_id'   => $user_id
                                )
                            );
                do_action( 'ep_user_cancel_rsvp', $meta, $post->ID, $user_id );
                wp_redirect( get_permalink( $post->ID ) . '?ep_user_cancelled_successful=yes' );
                die();
            }
            
        }
        
        /**
         * Single Event page notices
         *
         * @since 1.0.0
         * @access public
         */
        public function ep_event_notice_cb() {
            if( isset( $_REQUEST['ep_user_joined_successful'] ) && $_REQUEST['ep_user_joined_successful'] == 'yes' ){
                ?>
                <div class="dg-alert dg-alert-info" role="alert">
                    <?php _e( 'You have joined in this event.', 'ep' ) ?>
                </div>
                <?php
            }
            
            if( isset( $_REQUEST['ep_user_already_joined'] ) && $_REQUEST['ep_user_already_joined'] == 'yes' ){
                ?>
                <div class="dg-alert dg-alert-warning" role="alert">
                    <?php _e( 'You have already joined in this event.', 'ep' ) ?>
                </div>
                <?php
            }
            
            if( isset( $_REQUEST['ep_join_not_allowed'] ) && $_REQUEST['ep_join_not_allowed'] == 'yes' ){
                ?>
                <div class="dg-alert dg-alert-danger" role="alert">
                    <?php _e( 'Sorry! Joining in this event is not allowed.', 'ep' ) ?>
                </div>
                <?php
            }
            
            if( isset( $_REQUEST['ep_cancel_not_allowed'] ) && $_REQUEST['ep_cancel_not_allowed'] == 'yes' ){
                ?>
                <div class="dg-alert dg-alert-danger" role="alert">
                    <?php _e( 'Sorry! cancelling event is not allowed.', 'ep' ) ?>
                </div>
                <?php
            }
            
            if( isset( $_REQUEST['ep_user_cancelled_successful'] ) && $_REQUEST['ep_user_cancelled_successful'] == 'yes' ){
                ?>
                <div class="dg-alert dg-alert-info" role="alert">
                    <?php _e( 'You have cancelled this event.', 'ep' ) ?>
                </div>
                <?php
            }
        }
        
        
        
        
        
    }
    
    //add_action( 'plugins_loaded', 'ep_event_init' );
    /**
     * Initializationo of Events class
     *
     * @return OBJECT Instance of WP_EVENTPRESS class
     */
    function ep_event_init() {
        return WP_EVENTPRESS::get_instance();
    }
    $eventpress = ep_event_init();
}