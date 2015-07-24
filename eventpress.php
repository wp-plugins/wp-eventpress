<?php
/*
Plugin Name: EventPress
Plugin URI: http://duogeek.com
Description: Very easy to use and user friendly best event manager plugin for WordPress with lots of useful options.
Version: 0.9.1
Author: duogeek
Author URI: http://duogeek.com
Textdomain: aem
License: GPL v2 or later
*/

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) wp_die( __( DG_HACK_MSG, 'eventpress' ) );

/**
 * Defining constants
 */
if( ! defined( 'AEM_VERSION' ) ) define( 'AEM_VERSION', '1.0.0' );
if( ! defined( 'AEM_MLP_MENU_POSITION' ) ) define( 'AEM_MLP_MENU_POSITION', '50' );
if( ! defined( 'AEM_PLUGIN_DIR' ) ) define( 'AEM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if( ! defined( 'AEM_FILES_DIR' ) ) define( 'AEM_FILES_DIR', AEM_PLUGIN_DIR . 'eventpress-files' );
if( ! defined( 'AEM_PLUGIN_URI' ) ) define( 'AEM_PLUGIN_URI', plugins_url( '', __FILE__ ) );
if( ! defined( 'AEM_FILES_URI' ) ) define( 'AEM_FILES_URI', AEM_PLUGIN_URI . '/eventpress-files' );

/**
 * Including framework
 */
require_once 'duogeek/duogeek-panel.php';
require_once AEM_FILES_DIR . '/classes/class.event.php';

require_once AEM_FILES_DIR . '/classes/class.event.template.handler.php';

require_once AEM_FILES_DIR . '/lib/functions.php';


if( ! class_exists( 'EP_EVENTS' ) ){

	/**
	 * The main event plugin class extends DGCustomPostType class
	 *
	 * Includes all the necessary settings, enqueue, formatting, other related fucntions/methods etc.
	 *
	 * @since 1.0
	 */
	class EP_EVENTS extends DGCustomPostType{


		/**
		 * Singleton Instance of this class
		 *
		 * @since 1.0
		 * @access private
		 * @var OBJECT of AEM_EVENTS class
		 */
		private static $_instance;


		/**
		 * Event post type variable
		 *
		 * @since 1.0
		 * @access public
		 * @var ARRAY
		 */
		public $post_type;


		/**
		 * Event post type meta boxes
		 *
		 * @since 1.0
		 * @access private
		 * @var ARRAY
		 */
		private $meta_boxes;


		/**
		 * Event status list
		 *
		 * @since 1.0
		 * @access private
		 * @var ARRAY
		 */
		private $status;


		/**
		 * Event type list - paid or free
		 *
		 * @since 1.0
		 * @access private
		 * @var ARRAY
		 */
		private $types;


		/**
		 * Event Currency
		 *
		 * @since 1.0
		 * @access private
		 * @var STRING
		 */
		private $currency;


		/**
		 * Event Settings Data
		 *
		 * @since 1.0
		 * @access public
		 * @var OBJECT
		 */
		public $_data;



		/**
		 * Event Table
		 *
		 * @since 1.0
		 * @access public
		 * @var OBJECT
		 */
		public $event_tbl;



		/**
		 * Class constructor
		 *
		 *
		 * @since 1.0
		 * @access protected
		 */
		protected function __construct(){

			global $wpdb;
			$this->event_tbl = $wpdb->prefix . 'ep_events';

			// Initialization function
			add_action( 'init', array( $this, 'init' ), 1 );

			// Enqueue styles and scripts in front and back end
			add_filter( 'front_scripts_styles', array( $this, 'front_aem_styles_scripts' ) );
			add_filter( 'admin_scripts_styles', array( $this, 'admin_aem_styles_scripts' ) );
			register_activation_hook( __FILE__, array( &$this, 'ep_tables_install' ) );
			add_action( 'plugins_loaded', array( &$this, 'ep_load_textdomain' ) );

		}


		/**
		 * Initializes the EP_EVENTS class
		 *
		 * Checks for an existing EP_EVENTS() instance
		 * and if there is none, creates an instance.
		 *
		 * @since 1.0
		 * @access public
		 */
		public static function get_instance() {

			if ( ! self::$_instance instanceof EP_EVENTS ) {
				self::$_instance = new EP_EVENTS();
			}

			return self::$_instance;

		}



		public function ep_load_textdomain() {
			load_plugin_textdomain( 'eventpress', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}



	    /**
		 * Enqueue styles and scripts in front end
		 *
		 * @param ARRAY $enq Argument of the passed array
		 *
		 * @since 1.0
		 * @access public
		 */
	    public function front_aem_styles_scripts( $enq ) {

	    	global $post;

	    	$css = array(
	    		'aem_main_css' => 'event-front.min.css',
	    		'calendar_main_css' => 'calendar-main.css',
	    		'calendar_custom_css' => 'calendar-custom.css'
	    		);

	    	$js = array(
	    		'aem_main_js' => 'event-front.min.js'
	    		);

	    	if( defined( 'AEM_DEV_ENABLED' ) ){

	    		$css = array(
	    			'aem_main_css' => 'event-front.css',
	    			'calendar_main_css' => 'calendar-main.css',
	    			'calendar_custom_css' => 'calendar-custom.css'
	    			);

	    		$js = array(
	    			'aem_main_js' => 'event-front.js'
	    			);
	    	}


			$scripts = array(
                array(
                    'name' => 'aem_main_js',
                    'src' => AEM_FILES_URI . '/assets/js/' . $js['aem_main_js'],
                    'dep' => array( 'jquery', 'duogeek-js' ),
                    'version' => AEM_VERSION,
                    'footer' => true,
                    'condition' => true,
                    'localize' => true,
                    'localize_data' => array(
                    		'object' => 'obj',
                    		'passed_data' => apply_filters( 'ep_localized_data_text' , array(
                    			'logged' => is_user_logged_in(),
                    			'username'	=> __( 'Username', 'eventpress' ),
                    			'email'	=> __( 'Email', 'eventpress' ),
                    			'password'	=> __( 'Password', 'eventpress' ),
                    			'register'	=> __( 'Register', 'eventpress' ),
                    			'login'	=> __( 'Login', 'eventpress' ),
                    			'ep_files_url' => AEM_FILES_URI,
                    			'ep_nonce' => wp_create_nonce( "ep_nonce_security_string" ),
                    			'ajax_url' => admin_url( 'admin-ajax.php' ),
                    			'confirm_text' => __( 'Are you sure want to join?', 'eventpress' ),
                    			'confirm_btn' => __( 'Confirm', 'eventpress' ),
                    			'ID' => isset( $post ) ? $post->ID : '',
                    			'success_msg' => __( 'You have successfully joined.', 'eventpress' ),
                    			'exist_msg' => __( 'You have already registered for this event.', 'eventpress' ),
                    			'have_account' => __( 'Have an account already? Login here', 'eventpress' ),
                    			'login_error' => __( 'The username or password is wrong.', 'eventpress' ),
                    			'cancel_text' => __( 'Are you sure want to cancel?', 'eventpress' ),
                    			'cancel_btn' => __( 'Yes.', 'eventpress' ),
                    			'event_url' => get_permalink( $post->ID )
                    			) )
                    	)
                )
            );

            $styles = array(
                array(
                    'name' => 'aem_main_css',
                    'src' => AEM_FILES_URI . '/assets/css/' . $css['aem_main_css'],
                    'dep' => '',
                    'version' => AEM_VERSION,
                    'media' => 'all',
                    'condition' => true
                ),
                array(
                    'name' => 'calendar_main_css',
                    'src' => AEM_FILES_URI . '/assets/css/' . $css['calendar_main_css'],
                    'dep' => '',
                    'version' => AEM_VERSION,
                    'media' => 'all',
                    'condition' => true
                ),
                array(
                    'name' => 'calendar_custom_css',
                    'src' => AEM_FILES_URI . '/assets/css/' . $css['calendar_custom_css'],
                    'dep' => '',
                    'version' => AEM_VERSION,
                    'media' => 'all',
                    'condition' => true
                )
            );

            if( ! isset( $enq['scripts'] ) || ! is_array( $enq['scripts'] ) ) $enq['scripts'] = array();
            if( ! isset( $enq['styles'] ) || ! is_array( $enq['styles'] ) ) $enq['styles'] = array();
            $enq['scripts'] = array_merge( $enq['scripts'], $scripts );
            $enq['styles'] = array_merge( $enq['styles'], $styles );

            return $enq;

	    	$scripts = array(
	    		array(
	    			'name' => 'aem_main_js',
	    			'src' => AEM_FILES_URI . '/assets/js/' . $js['aem_main_js'],
	    			'dep' => array( 'jquery' ),
	    			'version' => AEM_VERSION,
	    			'footer' => false,
	    			'condition' => true
	    			)
	    		);

	    	$styles = array(
	    		array(
	    			'name' => 'aem_main_css',
	    			'src' => AEM_FILES_URI . '/assets/css/' . $css['aem_main_css'],
	    			'dep' => '',
	    			'version' => AEM_VERSION,
	    			'media' => 'all',
	    			'condition' => true
	    			),
	    		array(
	    			'name' => 'calendar_main_css',
	    			'src' => AEM_FILES_URI . '/assets/css/' . $css['calendar_main_css'],
	    			'dep' => '',
	    			'version' => AEM_VERSION,
	    			'media' => 'all',
	    			'condition' => true
	    			),
	    		array(
	    			'name' => 'calendar_custom_css',
	    			'src' => AEM_FILES_URI . '/assets/css/' . $css['calendar_custom_css'],
	    			'dep' => '',
	    			'version' => AEM_VERSION,
	    			'media' => 'all',
	    			'condition' => true
	    			)
	    		);

	    	if( ! isset( $enq['scripts'] ) || ! is_array( $enq['scripts'] ) ) $enq['scripts'] = array();
	    	if( ! isset( $enq['styles'] ) || ! is_array( $enq['styles'] ) ) $enq['styles'] = array();
	    	$enq['scripts'] = array_merge( $enq['scripts'], $scripts );
	    	$enq['styles'] = array_merge( $enq['styles'], $styles );

	    	return $enq;


	    }



	    /**
		 * Enqueue styles and scripts in admin end
		 *
		 * @param ARRAY $enq Argument of the passed array
		 *
		 * @since 1.0
		 * @access public
		 */
	    public function admin_aem_styles_scripts( $enq ){

	    	$css = array(
	    		'aem_main_css' => 'event-admin.min.css'
	    		);

	    	$js = array(
	    		'aem_main_js' 		=> 'event-admin.min.js',
	    		'aem_timepicker' 	=> 'jquery-ui-timepicker-addon.js'
	    		);

	    	if( defined( 'AEM_DEV_ENABLED' ) ){

	    		$css = array(
	    			'aem_main_css' => 'event-admin.css'
	    			);

	    		$js = array(
	    			'aem_main_js' 		=> 'event-admin.js',
	    			'aem_timepicker' 	=> 'jquery-ui-timepicker-addon.js'
	    			);
	    	}

	    	$scripts = array(
	    		array(
	    			'name' => 'aem_timepicker',
	    			'src' => AEM_FILES_URI . '/assets/js/' . $js['aem_timepicker'],
	    			'dep' => array( 'jquery', 'jquery-ui-datepicker', 'jquery-effects-clip' ),
	    			'version' => AEM_VERSION,
	    			'footer' => false,
	    			'condition' => true
	    			),
	    		array(
	    			'name' => 'aem_main_js',
	    			'src' => AEM_FILES_URI . '/assets/js/' . $js['aem_main_js'],
	    			'dep' => array( 'jquery', 'jquery-ui-datepicker', 'jquery-effects-clip', 'wp-color-picker' ),
	    			'version' => AEM_VERSION,
	    			'footer' => false,
	    			'condition' => true
	    			),
	    		array(
	    			'name' => 'aem_bootstrapjs',
	    			'src' => 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js',
	    			'dep' => '',
	    			'version' => AEM_VERSION,
	    			'footer' => false,
	    			'condition' => true
	    			)
	    		);

	    	$styles = array(
	    		array(
	    			'name' => 'jquery-ui-css',
	    			'src' => '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css',
	    			'dep' => '',
	    			'version' => AEM_VERSION,
	    			'media' => 'all',
	    			'condition' => true
	    			),
	    		array(
	    			'name' => 'aem_main_css',
	    			'src' => AEM_FILES_URI . '/assets/css/' . $css['aem_main_css'],
	    			'dep' => '',
	    			'version' => AEM_VERSION,
	    			'media' => 'all',
	    			'condition' => true
	    			)
	    		);

	    	if( ! isset( $enq['scripts'] ) || ! is_array( $enq['scripts'] ) ) $enq['scripts'] = array();
	    	if( ! isset( $enq['styles'] ) || ! is_array( $enq['styles'] ) ) $enq['styles'] = array();
	    	$enq['scripts'] = array_merge( $enq['scripts'], $scripts );
	    	$enq['styles'] = array_merge( $enq['styles'], $styles );

	    	return $enq;

	    }



	    /**
		 * Install required table(s)
		 *
		 * Event table installed
		 *
		 * @since 1.0
		 * @access public
		 */
	    public function ep_tables_install() {

	    	global $wpdb;

	    	$sql = "CREATE TABLE IF NOT EXISTS {$this->event_tbl} (
				id int(255) NOT NULL AUTO_INCREMENT,
				event_id int(255) NOT NULL,
				user_id int(255) NOT NULL,
				event_paid int(10) NOT NULL,
				user_paid int(10) NOT NULL,
				tickets int(50) NOT NULL,
				PRIMARY KEY (id) )";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

	    }



	    /**
		 * Initializes the class
		 *
		 * Checks for an existing AEM_EVENTS() instance
		 * and if there is none, creates an instance.
		 *
		 * @since 1.0
		 * @access public
		 */
	    public function init() {

			// Defining custom post type
	    	$this->post_type = array(
	    		'post_type'			 => 'aem_events',
	    		'name'               => _x( 'Events', 'post type general name', 'eventpress' ),
	    		'singular_name'      => _x( 'Event', 'post type singular name', 'eventpress' ),
	    		'menu_name'          => _x( 'Events', 'admin menu', 'eventpress' ),
	    		'name_admin_bar'     => _x( 'Event', 'add new on admin bar', 'eventpress' ),
	    		'add_new'            => _x( 'Add New', 'book', 'eventpress' ),
	    		'add_new_item'       => __( 'Add New Event', 'eventpress' ),
	    		'new_item'           => __( 'New Event', 'eventpress' ),
	    		'edit_item'          => __( 'Edit Event', 'eventpress' ),
	    		'view_item'          => __( 'View Event', 'eventpress' ),
	    		'all_items'          => __( 'All Events', 'eventpress' ),
	    		'search_items'       => __( 'Search Events', 'eventpress' ),
	    		'parent_item_colon'  => __( 'Parent Events:', 'eventpress' ),
	    		'not_found'          => __( 'No Events found.', 'eventpress' ),
	    		'not_found_in_trash' => __( 'No Events found in Trash.', 'eventpress' ),
	    		'menu_position'      => AEM_MLP_MENU_POSITION,
	    		'menu_icon'			 => AEM_FILES_URI . '/assets/images/eventpress-20-20.png',
	    		'rewrite'			 => array( 'slug' => 'event' ),
	    		'has_archive'		 => 'events'
	    		);

			// Defining meta boxes for event post type
			$this->meta_boxes = array(

				array(
					'id'            => 'event_details',
					'title'         => 'Event Details',
					'callback'      => array( $this, 'event_details_meta_cb' ),
					'post_type'     => $this->post_type['post_type'],
					'context'       => 'normal',
					'priority'      => 'high'
					),
				array(
					'id'            => 'event_child',
					'title'         => 'Event Instances',
					'callback'      => array( $this, 'event_instances_meta_cb' ),
					'post_type'     => $this->post_type['post_type'],
					'context'       => 'normal',
					'priority'      => 'high'
					),
				array(
					'id'            => 'event_rsvp',
					'title'         => 'Event RSVP List',
					'callback'      => array( $this, 'event_rsvp_meta_cb' ),
					'post_type'     => $this->post_type['post_type'],
					'context'       => 'normal',
					'priority'      => 'high'
					)

			);

			// Setting status, types etc
			$this->status = apply_filters( 'aem_status_list', array( 'Open', 'Closed', 'Expired' ) );
			$this->types = apply_filters( 'aem_types_list', array( 'Free', 'Paid' ) );
			$this->currency = 'USD';
			$this->_data = new DG_Options( 'aem', '' );
			$aem = $this->_data->get_options();

			// Calling parent constructor to register the post type and taxonomies
			parent::__construct( $this->post_type );
			add_action( 'init', array( $this, 'register_aem_post_type' ), 10 );
			add_action( 'init', array( $this, 'register_aem_taxonomies' ) );
			add_action( 'add_meta_boxes', array( $this, 'register_event_meta_boxes' ) );
			add_action( 'wp_footer', array( &$this, 'set_color_scheme' ) );

			add_action( 'save_post_' . $this->post_type['post_type'], array( $this, 'event_save_meta_box_data' ) );
			add_filter( 'post_row_actions', array( $this, 'set_duplicate_link' ), 10, 2 );
			add_action( 'admin_action_create_duplicate_events', array( $this, 'create_duplicate_events_cb' ) );
			add_action( 'admin_action_aem-events-settings', array( $this, 'aem_events_settings_cb' ) );

			//add_action( 'the_content', array( &$this, 'set_rsvp_join_meta' ) );
			add_filter( 'single_template', array( &$this, 'load_single_template' ) );
			add_filter( 'archive_template', array( &$this, 'load_archive_template' ) );
			//add_filter( 'archive_template', array( &$this, 'load_monthly_calendar_template' ) );
			add_action( 'delete_post', array( $this, 'recurring_event_delete') );
			add_action( 'wp_trash_post', array( $this, 'recurring_event_trash') );


			if( isset( $aem['show_rsvp_front'] ) && $aem['show_rsvp_front'] == 1 )
				add_action( 'ep_after_single_content', array( &$this, 'show_rsvp_on_front' ) );

			add_action( 'wp_ajax_ep_register_user', array( &$this, 'ep_register_user_cb' ) );
			add_action( 'wp_ajax_nopriv_ep_register_user', array( &$this, 'ep_register_user_cb' ) );
			add_action( 'template_redirect', array( &$this, 'ep_join_event_cb' ) );
			add_action( 'wp_ajax_ep_login_user', array( &$this, 'ep_login_user_cb' ) );
			add_action( 'wp_ajax_nopriv_ep_login_user', array( &$this, 'ep_login_user_cb' ) );
			add_action( 'wp_ajax_ep_cancel_event', array( &$this, 'ep_cancel_event_cb' ) );
			add_action( 'wp_ajax_nopriv_ep_cancel_event', array( &$this, 'ep_cancel_event_cb' ) );
			//Paypal IPN function
			//add_action( 'wp_ajax_ep_paypal_ipn', array( &$this, 'ep_paypal_ipn_cb' ) );
			//add_action( 'wp_ajax_nopriv_ep_paypal_ipn', array( &$this, 'ep_paypal_ipn_cb' ) );


			add_action( 'ep_event_notice', array( &$this, 'ep_set_user_notice' ) );


			add_filter( 'duogeek_submenu_pages', array( $this, 'aem_event_menu' ) );

		}


		/**
		 * Registering events post type
		 *
		 * @since 1.0
		 * @access public
		 */
		public function register_aem_post_type() {

			$this->register_custom_post_type();

		}


		/**
		 * Registering event categories and tags as taxonomies
		 *
		 * @since 1.0
		 * @access public
		 */
		public function register_aem_taxonomies() {

			$taxes = array(
				array(
					'tax_name' => 'event_category',
					'name' => _x( 'Event Categories', 'taxonomy general name', 'eventpress' ),
					'singular_name' => _x( 'Event Category', 'taxonomy singular name', 'eventpress' ),
					'search_items' => __( 'Search Categories', 'eventpress' ),
					'all_items' => __( 'All Categories', 'eventpress' ),
					'parent_item' => __( 'Parent Category', 'eventpress' ),
					'parent_item_colon' => __( 'Parent Category:', 'eventpress' ),
					'edit_item' => __( 'Edit Category', 'eventpress' ),
					'update_item' => __( 'Update Category', 'eventpress' ),
					'add_new_item' => __( 'Add New Category', 'eventpress' ),
					'new_item_name' => __( 'New Category Name', 'eventpress' ),
					'menu_name' => __( 'Event Categories', 'eventpress' ),
					'rewrite' => array( 'slug' => 'event-category' ),
					'hierarchical' => true
					),
				array(
					'tax_name' => 'event_tag',
					'name' => _x( 'Event Tags', 'taxonomy general name', 'eventpress' ),
					'singular_name' => _x( 'Event Tag', 'taxonomy singular name', 'eventpress' ),
					'search_items' => __( 'Search Tags', 'eventpress' ),
					'all_items' => __( 'All Tags', 'eventpress' ),
					'parent_item' => __( 'Parent Tag', 'eventpress' ),
					'parent_item_colon' => __( 'Parent Tag:', 'eventpress' ),
					'edit_item' => __( 'Edit Tag', 'eventpress' ),
					'update_item' => __( 'Update Tag', 'eventpress' ),
					'add_new_item' => __( 'Add New Tag', 'eventpress' ),
					'new_item_name' => __( 'New Tag Name', 'eventpress' ),
					'menu_name' => __( 'Event Tags', 'eventpress' ),
					'rewrite' => array( 'slug' => 'event-tag' ),
					'hierarchical' => false
					)
				);

			foreach ( $taxes as $tax ) {
				$this->set_tax( $tax );
				$this->register_custom_taxonomies();
			}

		}


		/**
		 * Registering event meta boxes
		 *
		 * @since 1.0
		 * @access public
		 */
		public function register_event_meta_boxes() {

			$this->add_custom_meta_boxes( $this->meta_boxes );

		}


		/**
		 * Event Meta Callback
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param OBJECT $post An event post object
		 */
		public function event_details_meta_cb( $post ) {

			$event = new DG_Event( $post->ID );
			wp_nonce_field( 'event_meta_box', 'event_meta_box_nonce' );
			?>
			<div class="event_meta_fieldset">


				<h3 class="hndle dg-text-center"><span><?php _e( 'Event Type', 'eventpress' ) ?></span></h3>
				<div class="dg-content">
					<div class="dg-row dg-top-space dg-form-group">
						<div class="dg-col-md-6 dg-text-center">
							<label><input type="radio" name="event_meta[instance]" value="single" <?php echo ( isset( $event->instance ) && $event->instance == 'single' ) || ! isset( $event->instance ) ? 'checked="checked"' : ''; ?>>
							<?php _e( 'Single Event', 'eventpress' ) ?></label>
						</div>
						<div class="dg-col-md-6 dg-text-center">
							<label><input type="radio" name="event_meta[instance]" value="recurring" <?php echo ( isset( $event->instance ) && $event->instance == 'recurring' ) ? 'checked="checked"' : ''; ?>>
							<?php _e( 'Recurring Event', 'eventpress' ) ?></label>
						</div>
					</div>
				</div>
				<hr>

				<h3 class="hndle dg-text-center"><span><?php _e( 'Event Date and Time', 'eventpress' ) ?></span></h3>
				<div class="instance_meta single_event_meta" <?php echo ( isset( $event->instance ) && $event->instance == 'recurring' ) ? 'style="display:none"' : ''; ?>>
					<div class="dg-content">
						<div class="dg-row dg-form-group">
							<div class="dg-col-md-8 dg-text-center"><input class="dg_datepicker dg-form-control" type="text" name="event_meta[start_date]" value="<?php echo isset( $event->start_date ) ? $event->start_date : ''; ?>" placeholder="<?php _e( 'Select Event Start Date', 'eventpress' ) ?>"></div>
							<div class="dg-col-md-4 dg-text-center"><input class="dg_timepicker dg-form-control" type="text" name="event_meta[start_time]" value="<?php echo isset( $event->start_time ) ? $event->start_time : ''; ?>" placeholder="<?php _e( 'Select Event Start Time', 'eventpress' ) ?>"></div>
							<div class="dg-col-md-8 dg-text-center"><input class="dg_datepicker dg-form-control" type="text" name="event_meta[end_date]" value="<?php echo isset( $event->end_date ) ? $event->end_date : ''; ?>" placeholder="<?php _e( 'Select Event End Date', 'eventpress' ) ?>"></div>
							<div class="dg-col-md-4 dg-text-center"><input class="dg_timepicker dg-form-control" type="text" name="event_meta[end_time]" value="<?php echo isset( $event->end_time ) ? $event->end_time : ''; ?>" placeholder="<?php _e( 'Select Event End Time', 'eventpress' ) ?>"></div>
						</div>
					</div>
				</div>


				<div class="event_meta_filed">
					<div class="instance_meta recurring_event_meta" <?php echo ( isset( $event->instance ) && $event->instance == 'recurring' ) ? 'style="display:block"' : 'style="display: none"'; ?>>
						<div class="dg-content">
							<div class="dg-row dg-form-group">
								<div class="dg-col-md-8 dg-text-center"><input class="dg_datepicker dg-form-control" type="text" name="event_meta[rec_start_from]" value="<?php echo isset( $event->rec_start_from ) ? $event->rec_start_from : ''; ?>" placeholder="<?php _e( 'Select Event Start Date', 'eventpress' ) ?>"></div>
								<div class="dg-col-md-4 dg-text-center"><input class="dg_timepicker dg-form-control" type="text" name="event_meta[rec_day_start_time]" value="<?php echo isset( $event->rec_day_start_time ) ? $event->rec_day_start_time : ''; ?>" placeholder="<?php _e( 'Select Event Start Time', 'eventpress' ) ?>"></div>
							</div>
							<div class="dg-row dg-form-group dg-top-space">
								<div class="dg-col-md-12 dg-text-center"><?php _e( 'Repetation On Every', 'eventpress' ) ?></div>
								<div class="dg-col-md-12 dg-text-center">
									<select name="event_meta[rec_repeat]" class="rec_repeat dg-form-control dg-text-center">
										<option value=""><?php _e( 'Select', 'eventpress' ) ?></option>
										<option <?php echo isset( $event->rec_repeat ) && $event->rec_repeat == 'day' ? 'selected="selected"' : ''; ?> value="day"><?php _e( 'Day', 'eventpress' ) ?></option>
										<option value="week"><?php _e( 'Week', 'eventpress' ) ?></option>
										<option value="month"><?php _e( 'Month', 'eventpress' ) ?></option>
										<option value="year"><?php _e( 'Year', 'eventpress' ) ?></option>
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
												<?php _e( 'Monday', 'eventpress' ) ?>
											</label>
											<label>
												<input type="checkbox" name="event_meta[rec_week_day][]" value="2">
												<?php _e( 'Tuesday', 'eventpress' ) ?>
											</label>
											<label>
												<input type="checkbox" name="event_meta[rec_week_day][]" value="3">
												<?php _e( 'Wednesday', 'eventpress' ) ?>
											</label>
											<label>
												<input type="checkbox" name="event_meta[rec_week_day][]" value="4">
												<?php _e( 'Thursday', 'eventpress' ) ?>
											</label>
											<label>
												<input type="checkbox" name="event_meta[rec_week_day][]" value="5">
												<?php _e( 'Friday', 'eventpress' ) ?>
											</label>
											<label>
												<input type="checkbox" name="event_meta[rec_week_day][]" value="6">
												<?php _e( 'Saturday', 'eventpress' ) ?>
											</label>
											<label>
												<input type="checkbox" name="event_meta[rec_week_day][]" value="7">
												<?php _e( 'Sunday', 'eventpress' ) ?>
											</label>
										</div>
										<div class="rec_repeat_ind_meta rec_meta_month">
											<?php _e( 'On ', 'eventpress' ) ?><br>
											<input type="text" name="event_meta[rec_month_date]" value="<?php echo isset( $event->rec_month_date ) ? $event->rec_month_date : ''; ?>" class="dg-form-control">
										</div>
										<div class="rec_repeat_ind_meta rec_meta_year">
										<div class="dg-col-md-6">
												<div class="dg-col-md-12"><?php _e( 'Month', 'eventpress' ) ?></div>
												<div class="dg-col-md-12">
													<select name="event_meta[rec_year_month]" class="dg-form-control">
														<option value="1"><?php _e( 'January', 'eventpress' ) ?></option>
														<option value="2"><?php _e( 'February', 'eventpress' ) ?></option>
														<option value="3"><?php _e( 'March', 'eventpress' ) ?></option>
														<option value="4"><?php _e( 'April', 'eventpress' ) ?></option>
														<option value="5"><?php _e( 'May', 'eventpress' ) ?></option>
														<option value="6"><?php _e( 'June', 'eventpress' ) ?></option>
														<option value="7"><?php _e( 'July', 'eventpress' ) ?></option>
														<option value="8"><?php _e( 'August', 'eventpress' ) ?></option>
														<option value="9"><?php _e( 'September', 'eventpress' ) ?></option>
														<option value="10"><?php _e( 'October', 'eventpress' ) ?></option>
														<option value="11"><?php _e( 'November', 'eventpress' ) ?></option>
														<option value="12"><?php _e( 'December', 'eventpress' ) ?></option>
													</select>
												</div>
											</div>
											<div class="dg-col-md-6">
												<div class="dg-col-md-12"><?php _e( 'Date', 'eventpress' ) ?></div>
												<div class="dg-col-md-12"><input type="text" name="event_meta[rec_year_date]" value="<?php echo isset( $event->rec_year_date ) ? $event->rec_year_date : ''; ?>"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="dg-col-md-8 dg-text-center"><input class="dg_datepicker dg-form-control" type="text" name="event_meta[rec_end_at]" value="<?php echo isset( $event->rec_end_at ) ? $event->rec_end_at : ''; ?>" placeholder="<?php _e( 'Select Event End Date', 'eventpress' ) ?>"></div>
								<div class="dg-col-md-4 dg-text-center"><input class="dg_timepicker dg-form-control" type="text" name="event_meta[rec_end_time]" value="<?php echo isset( $event->rec_end_time ) ? $event->rec_end_time : ''; ?>" placeholder="<?php _e( 'Select Event End Time', 'eventpress' ) ?>"></div>
							</div>
						</div>
					</div>
				</div>


				<h3 class="hndle dg-text-center"><span><?php _e( 'Event Status & Type', 'eventpress' ) ?></span></h3>
				<div class="instance_meta single_event_meta">
					<div class="dg-content">
						<div class="dg-row dg-form-group">
							<div class="dg-col-md-6">
								<div class="dg-col-md-12 dg-text-center"><h3><?php _e( 'What is the event status?', 'eventpress' ) ?></h3></div>
								<div class="dg-col-md-12 dg-text-center">
									<select name="event_meta[status]" class="dg-form-control dg-text-center">
									<?php foreach( $this->status as $status ) { ?>
										<option <?php echo checked_select( $status, isset( $event->status ) ? $event->status : '' ) ?> value="<?php echo $status ?>"><?php echo $status ?></option>
									<?php } ?>
									</select>
								</div>
							</div>
							<div class="dg-col-md-6">
								<div class="dg-col-md-12 dg-text-center"><h3><?php _e( 'Is it a paid event?', 'eventpress' ) ?></h3></div>
								<div class="dg-col-md-12 dg-text-center">
									<select name="event_meta[type]" class="event_meta_type dg-form-control dg-text-center">
										<?php foreach( $this->types as $type ) { ?>
										<option <?php echo checked_select( $type, isset( $event->type ) ? $event->type : '' ) ?> value="<?php echo $type ?>"><?php echo $type ?></option>
										<?php } ?>
									</select>
								</div>
								<div class="event_price_meta dg-col-md-12 dg-text-center">
									<?php _e( 'Ticket Price: ', 'eventpress' ) ?><?php echo $this->currency; ?><br>
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
		 * Event Instances meta box for recurring event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param OBJECT $post Event Object
		 */
		public function event_instances_meta_cb( $post ){

			$event = new DG_Event( $post->ID );
			if( ! isset( $event->child_events->posts ) ){
				_e( '<p>This part is only for recurring events</p>', 'eventpress' );
			}else{
			?>
			<ul>
				<?php
				foreach( $event->child_events->posts as $child_event ) {
					$child_obj = new DG_Event( $child_event->ID );
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
		 * Event RSVP meta box for event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param OBJECT $post Event Object
		 */
		public function event_rsvp_meta_cb( $post ){

			$event = new DG_Event( $post->ID );
			if( is_array( $event->rsvp ) ){
				echo '<ul class="ep-rsvp-list">';
				foreach( $event->rsvp as $rsvp ) {
					$user = new WP_User( $rsvp );
					?>
					<li>
						<input type="hidden" name="event_meta[rsvp][]" value="<?php echo $rsvp; ?>">
						<div class="ep-avatar">
							<a href="<?php echo admin_url( 'user-edit.php?user_id=' . $rsvp ) ?>"><?php echo get_avatar( $rsvp, 32 ); ?></a>
						</div>
						<div class="ep-name">
							<a href="<?php echo admin_url( 'user-edit.php?user_id=' . $rsvp ) ?>"><?php echo $user->display_name ?></a>
						</div>
					</li>
					<?php
				}
				echo '</ul>';
			}

		}



		/**
		 * Event Meta save
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param INT $post_id An event ID
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

			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return;
				}

			} else {

				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
			}

			$event = new DG_Event( $post_id );
			$event->update_post_meta( $_POST['event_meta'] );
			update_post_meta( $post_id, 'ep_event_start_date', $_POST['event_meta']['start_date'] );

			$post_thumbnail_id = get_post_thumbnail_id( $post_id );

			if( ( $_POST['post_status'] == 'publish' ) && ( $_POST['original_post_status'] != 'publish' ) ) {

				remove_action( 'save_post_' . $this->post_type['post_type'], array( $this, 'event_save_meta_box_data' ) );

				if( $event->instance == 'recurring' ){

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
								'post_status'	=> 'Recurring',
								'post_content'	=> $event->get_content(),
								'post_type'		=> $this->post_type['post_type'],
								'post_author'	=> $event->get_author(),
								'post_parent'	=> $event->get_id()
								);

							$new_event_id = insert_post( $args );
							$new_event = new DG_Event( $new_event_id );

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

							$begin = date ("d-m-Y", strtotime("+1 day", strtotime($begin)));

						}

						break;

						case 'week':

						while (strtotime($begin) <= strtotime($end_date)) {

							if( in_array( date( 'N', strtotime( $begin ) ), $_POST['event_meta']['rec_week_day'] ) ){

								$args = array(
									'post_title'	=> $event->get_title(),
									'post_status'	=> 'Recurring',
									'post_content'	=> $event->get_content(),
									'post_type'		=> $this->post_type['post_type'],
									'post_author'	=> $event->get_author(),
									'post_parent'	=> $event->get_id()
									);

								$new_event_id = insert_post( $args );
								$new_event = new DG_Event( $new_event_id );

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
									'post_status'	=> 'Recurring',
									'post_content'	=> $event->get_content(),
									'post_type'		=> $this->post_type['post_type'],
									'post_author'	=> $event->get_author(),
									'post_parent'	=> $event->get_id()
									);

								$new_event_id = insert_post( $args );
								$new_event = new DG_Event( $new_event_id );

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
									'post_status'	=> 'Recurring',
									'post_content'	=> $event->get_content(),
									'post_type'		=> $this->post_type['post_type'],
									'post_author'	=> $event->get_author(),
									'post_parent'	=> $event->get_id()
									);

								$new_event_id = insert_post( $args );
								$new_event = new DG_Event( $new_event_id );

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

				add_action( 'save_post_' . $this->post_type['post_type'], array( $this, 'event_save_meta_box_data' ) );
			}

		}



		/**
		 * Create Duplicate Event Link
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param ARRAY $actions An array od modified action
		 */
		public function set_duplicate_link( $actions, $post ){
			if ( $post->post_type == $this->post_type['post_type'] ) {
				$actions['duplicate'] = '<a href="admin.php?action=create_duplicate_events&post=' . $post->ID . '">Duplicate</a>';
			}
			return $actions;

		}



		/**
		 * Fire the duplication of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param NULL
		 */
		public function create_duplicate_events_cb() {

			global $wpdb;
			if( ! ( isset( $_GET['post']) || isset( $_POST['post'] )  || ( isset($_REQUEST['action'] ) && 'create_duplicate_events' == $_REQUEST['action'] ) ) ) {
				wp_die( _e( 'The post doesn\'t exist!', 'eventpress' ) );
			}

			/*
			 * get the original post id
			 */
			$post_id = ( isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post'] );

			/*
			 * and all the original post data then
			 */
			$post = get_post( $post_id );

			/*
			 * if you don't want current user to be the new post author,
			 * then change next couple of lines to this: $new_post_author = $post->post_author;
			 */
			$current_user = wp_get_current_user();
			$new_post_author = $current_user->ID;

			/*
			 * if post data exists, create the post duplicate
			 */
			if( isset( $post ) && $post != null ) {

				/*
				 * new post data array
				 */
				$args = array(
					'comment_status' => $post->comment_status,
					'ping_status'    => $post->ping_status,
					'post_author'    => $new_post_author,
					'post_content'   => $post->post_content,
					'post_excerpt'   => $post->post_excerpt,
					'post_name'      => $post->post_name,
					'post_parent'    => $post->post_parent,
					'post_password'  => $post->post_password,
					'post_status'    => apply_filters( 'eam_duplicated_post_status', 'draft' ),
					'post_title'     => $post->post_title . ' - Duplicate',
					'post_type'      => $post->post_type,
					'to_ping'        => $post->to_ping,
					'menu_order'     => $post->menu_order
					);

				/*
				 * insert the post by wp_insert_post() function
				 */
				$new_post_id = wp_insert_post( $args );

				/*
				 * get all current post terms ad set them to the new post draft
				 */
				$taxonomies = get_object_taxonomies( $post->post_type ); // returns array of taxonomy names for post type, ex array("category", "post_tag");
				foreach ( $taxonomies as $taxonomy ) {
					$post_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'slugs' ) );
					wp_set_object_terms( $new_post_id, $post_terms, $taxonomy, false );
				}

				/*
				 * duplicate all post meta
				 */
				$post_meta_infos = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d",
						$post_id
						)
					);

				if ( count( $post_meta_infos ) != 0 ) {
					$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
					foreach ($post_meta_infos as $meta_info) {
						$meta_key = $meta_info->meta_key;
						$meta_value = addslashes( $meta_info->meta_value );
						$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
					}
					$sql_query .= implode( " UNION ALL ", $sql_query_sel );
					$wpdb->query( $sql_query );
				}


				/*
				 * finally, redirect to the edit post screen for the new draft
				 */
				wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
				exit;
			} else {
				wp_die( sprintf( __( 'Post creation failed, could not find original post: %d', 'eventpress' ), $post_id ) ) ;
			}

		}


		/**
		 * Create Event submenu under DuoGeek
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param ARRAY $submenus Submenu Array
		 */
		public function aem_event_menu( $submenus ) {
			$submenus[] = array(
				'title' => __( 'Event Settings', 'df' ),
				'menu_title' => __( 'Event Settings', 'df' ),
				'capability' => 'manage_options',
				'slug' => 'aem-events-settings',
				'object' => $this,
				'function' => 'aem_event_settings_page'
				);

			return $submenus;

		}


		/**
		 * The page for Event Settings
		 *
		 * @since 1.0
		 * @access public
		 */
		public function aem_event_settings_page() {

			$aem = $this->_data->get_options();

			?>
			<div class="wrap duo_prod_panel">
				<h2><?php _e( 'Event Settings', 'eventpress' ) ?></h2>

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
									<li><a class="<?php echo ! isset( $_REQUEST['tab'] ) ? 'active' : '' ?>" href="<?php echo admin_url( 'admin.php?page=aem-events-settings' ) ?>"><?php _e( 'Settings', 'ame' ) ?></a></li>
									<li><a class="<?php echo isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'addons' ? 'active' : '' ?>" href="<?php echo admin_url( 'admin.php?page=aem-events-settings&tab=addons' ) ?>"><?php _e( 'Addons', 'ame' ) ?></a></li>
								</ul>
							</div>

							<!-- If no tab is selected -->
							<?php if( ! isset( $_REQUEST['tab'] ) ) { ?>

							<form action="<?php echo admin_url( 'admin.php?action=aem-events-settings' ) ?>" method="post">
								<?php wp_nonce_field( 'aem_ev_nonce_action', 'aem_ev_nonce_field' ); ?>

								<div class="postbox">
									<h3 class="hndle"><?php _e( 'General Settings', 'eventpress' ) ?></h3>
									<div class="inside">
										<table class="form-table">
											<tr>
												<th><?php _e( 'Display RSVP List in front end?', 'eventpress' ) ?></th>
												<td>
													<input <?php if( isset($aem['show_rsvp_front']) ){ checked( $aem['show_rsvp_front'], 1, true ); } ?> type="checkbox" name="aem[show_rsvp_front]" value="1">
												</td>
											</tr>
											<tr>
												<th><?php _e( 'Choose a theme color?', 'eventpress' ) ?></th>
												<td>
													<input type="text" class="wp-admin-color-picker" name="aem[color_theme]" class="small_box" value="<?php echo isset( $aem['color_theme'] ) ? $aem['color_theme'] : '#f35333' ?>" />
												</td>
											</tr>
											<!--tr>
												<th><?php _e( 'Enable payment?', 'eventpress' ) ?></th>
												<td>
													<input <?php checked( $aem['enable_payment'], 1, true ) ?> type="checkbox" name="aem[enable_payment]" value="1">
												</td>
											</tr-->
											<tr>
												<th><?php _e( 'Choose archive template:', 'eventpress' ) ?></th>
												<td>
													<select name="aem[archive_template]">
														<option value=""><?php _e( 'Select a template', 'eventpress' ) ?></option>
														<option <?php echo isset( $aem['archive_template'] ) && $aem['archive_template'] == 'list' ? 'selected="selected"' : ''  ?> value="list"><?php _e( 'List Template', 'eventpress' ) ?></option>
														<option <?php echo isset( $aem['archive_template'] ) && $aem['archive_template'] == 'calendar' ? 'selected="selected"' : ''  ?> value="calendar"><?php _e( 'Calendar Template', 'eventpress' ) ?></option>
													</select>
												</td>
											</tr>
										</table>
									</div>
								</div>

								<!-- Action hook for addons -->
								<?php do_action( 'ep_event_settings_addon' ); ?>

								<input type="submit" name="aem[eam_event_settings]" value="<?php _e( 'Save Settings', 'eventpress' ); ?>" class="button button-primary">
							</form>

							<!-- If add on tab -->
							<?php }elseif( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'addons' ) { ?>

							<?php do_action( 'aem_event_addons_list' ); ?>

							<?php } ?>

						</div>
						<!-- Settings Sidebar -->
						<div class="postbox-container" id="postbox-container-1">
							<?php do_action( 'dg_settings_sidebar', 'free', 'eventpress', 'https://wordpress.org/support/view/plugin-reviews/wp-eventpress?rate=5#postform' ); ?>
						</div>
					</div>
				</div>
			</div>
			<?php

		}



		/**
		 * Save the Event Settings
		 *
		 * @since 1.0
		 * @access public
		 */
		public function aem_events_settings_cb() {

			if( isset( $_POST['aem'] ) ){

				if ( ! check_admin_referer( 'aem_ev_nonce_action', 'aem_ev_nonce_field' )){
					return;
				}

				$aem = $_POST['aem'];
				if( ! isset( $aem['show_rsvp_front'] ) ) $aem['show_rsvp_front'] = 0;

				if( ! isset( $aem['enable_payment'] ) ) $aem['enable_payment'] = 0;

				$aem = apply_filters( 'ep_event_settings_save_before', $aem );

				$this->_data->set_options( $aem );

				wp_redirect( admin_url( 'admin.php?page=aem-events-settings&notice_result=updated&msg=' . __( 'Settings+saved+successfully.', 'eventpress' ) ) );

			}

		}



		/**
		 * Load single custom event template
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param STRING $template Event template
		 */
		public function load_single_template( $template ) {

			global $post;

			if( $post->post_type == $this->post_type['post_type'] )
				return EP_EVENT_TEMPLATE_HANDLER::load_single_template( $this->post_type['post_type'] );

			return $template;

		}

		/**
		 * Load archive custom event template
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param STRING $template Event Template
		 */
		public function load_archive_template( $template ) {

			$aem = $this->_data->get_options();

			if ( is_post_type_archive ( $this->post_type['post_type'] )){
				if (!empty($aem)){
					if( $aem['archive_template'] == 'list' )
						return EP_EVENT_TEMPLATE_HANDLER::load_archive_template( $this->post_type['post_type'] );
					else
						return EP_EVENT_TEMPLATE_HANDLER::load_monthly_calendar_template( $this->post_type['post_type'] );
				}
			}

			return $template;

		}

		/**
		 * Delete recurring event post
		 *
		 * @since 1.0
		 * @access public
		 */
		public function recurring_event_delete(){
			global $post;
			if( $post->post_type == $this->post_type['post_type'] ) {
				$args = array(
					'post_parent' => $post->ID,
					'post_status' => 'recurring',
					'post_type' => $this->post_type['post_type'],
				);

				$child = get_children( $args );
				if( count($child) > 0 ) {
					remove_action( 'delete_post', array( $this, 'recurring_event_delete' ) );
					foreach( $child as $value ) {
						wp_delete_post($value->ID, true);
					}
				}

				wp_redirect( admin_url( 'edit.php?post_status=trash&post_type' . $this->post_type['post_type'] . '&msg=' . __( '1+post+permanently+deleted.', 'eventpress' ) ) );
			}
		}

		/**
		 * Trash recurring event post
		 *
		 * @since 1.0
		 * @access public
		 */
		public function recurring_event_trash(){
			global $post;
			if( $post->post_type == $this->post_type['post_type'] ) {
				$args = array(
					'post_parent' => $post->ID,
					'post_status' => 'recurring',
					'post_type' => $this->post_type['post_type'],
				);

				$child = get_children( $args );
				if( count($child) > 0 ) {
					remove_action( 'wp_trash_post', array( $this, 'recurring_event_trash' ) );
					foreach( $child as $value ) {
						$arg = array(
							'ID'			=> $value->ID,
							'post_status'	=> 'Recurring-trash'
							);
						wp_update_post( $arg );
					}
				}

				//wp_redirect( admin_url( 'edit.php?post_type' . $this->post_type['post_type'] . '&msg=' . __( '1+post+permanently+deleted.', 'eventpress' ) ) );
			}
		}

		/**
		 * Get event archive list
		 *
		 * @since 1.0
		 * @access public
		 */
		public function event_archive(){
			$events = new WP_Query
			(
				array
				(
					'post_type'    => $this->post_type['post_type'],
					'post_status'  => array( 'publish', 'recurring' ),
					'posts_per_page'	=> apply_filters( 'ep_show_events_number', 30 )
					)
				);
			$event_archive = array();
			foreach($events->posts as $value){
				$event = new DG_Event( $value->ID );
				$event_time =  strtotime($event->start_date);
				if($event_time > time()){
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
					'post_type'  => $this->post_type['post_type'],
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
		 * Register an user
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_register_user_cb() {

			check_ajax_referer( 'ep_nonce_security_string', 'nonce' );

			$username = sanitize_text_field( $_POST['username'] );
			$email = sanitize_text_field( $_POST['email'] );
			$pw = sanitize_text_field( $_POST['pw'] );

			$user_id = username_exists( $username );
			if ( !$user_id and email_exists( $email ) == false ) {
				wp_create_user( $username, $pw, $email );

				wp_signon( apply_filters( 'ep_ajax_login_data', array(
					'user_login' => $username,
					'user_password' => $pw,
					'remember' => true
					) ), false );

				echo "created";
			}else{
				_e( 'Username or email already exists', 'eventpress' );
			}

			die();

		}



		/**
		 * Join in an event for an user
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_join_event_cb(){

			if( isset( $_REQUEST['ep_join_event'] ) && $_REQUEST['ep_join_event'] == 'yes' ){

				global $wpdb, $post;
				$user_id = get_current_user_id();
				$event_id = $post->ID;
				$no_of_ticket = isset( $_REQUEST['ticket'] ) ? sanitize_text_field( $_REQUEST['ticket'] ) : 1;

				$event = new DG_Event( $event_id );
				if( apply_filters( 'ep_allow_join_rsvp', $event->can_rsvp, $no_of_ticket ) == false ){
					wp_redirect( get_permalink( $post->ID ) . '?ep_ticket_available=no' );
					die();
				}
				$meta = $event->get_post_meta();
				$meta->rsvp = gettype( $meta->rsvp ) == 'string' ?  array() : $meta->rsvp;
				if( ! in_array( $user_id, $meta->rsvp ) ){
					$meta->rsvp[] = $user_id;

					$ticket_id = $wpdb->insert(
						$this->event_tbl,
						array(
							'event_id' => $event_id,
							'user_id' => $user_id,
							'event_paid' => $event->type,
							'user_paid' => 0,
							'tickets' => $no_of_ticket
							)
						);

					do_action( 'ep_user_join_rsvp', $meta->rsvp, $event_id, $user_id, $no_of_ticket, $ticket_id );
				}else{
					wp_redirect( get_permalink( $post->ID ) . '?ep_user_already_joined=yes' );
					die();
				}
				$event->update_post_meta( $meta );

				wp_redirect( get_permalink( $post->ID ) . '?ep_user_joined_successful=yes' );
				die();

			}

			if( isset( $_REQUEST['ep_join_event_cancel'] ) && $_REQUEST['ep_join_event_cancel'] == 'yes' ){

				global $wpdb, $post;
				$user_id = get_current_user_id();
				$event_id = $post->ID;
				$no_of_ticket = sanitize_text_field( $_REQUEST['ticket'] );

				$q = $wpdb->get_row( 'SELECT * from ' . $this->event_tbl . " where event_id = '".$event_id."' and user_id = '".$user_id."'" );

				$no_of_ticket = isset( $q->tickets ) ? $q->tickets : 0;

				$event = new DG_Event( $event_id );
				$meta = $event->get_post_meta();
				$meta->rsvp = gettype( $meta->rsvp ) == 'string' ?  array() : $meta->rsvp;
				if( in_array( $user_id, $meta->rsvp ) ){
					$key = array_search( $user_id, $meta->rsvp );
					if( $key !== false ){
					    unset( $meta->rsvp[$key] );

					    do_action( 'ep_user_cancel_rsvp', $meta->rsvp, $event_id, $user_id, $no_of_ticket );

					    $wpdb->delete(
					    	$this->event_tbl,
					    	array(
					    		'event_id' => $event_id,
								'user_id' => $user_id
					    	)
					    );

					}
				}else{
					wp_redirect( get_permalink( $post->ID ) . '?ep_user_not_joined=yes' );
				}
				$event->update_post_meta( $meta );

				wp_redirect( get_permalink( $post->ID ) . '?ep_user_cancel_event=yes' );
				die();

			}




			if( isset( $_REQUEST['ep_user_joined_successful'] ) && $_REQUEST['ep_user_joined_successful'] == 'yes' ){
				add_action( 'ep_event_notice', array( &$this, 'ep_set_join_notice' ) );
			}

			if( isset( $_REQUEST['ep_ticket_available'] ) && $_REQUEST['ep_ticket_available'] == 'no' ){
				add_action( 'ep_event_notice', array( &$this, 'ep_ticket_unavailable_notice' ) );
			}

			if( isset( $_REQUEST['ep_user_already_joined'] ) && $_REQUEST['ep_user_already_joined'] == 'yes' ){
				add_action( 'ep_event_notice', array( &$this, 'ep_user_already_joined_notice' ) );
			}

			if( isset( $_REQUEST['ep_user_not_joined'] ) && $_REQUEST['ep_user_not_joined'] == 'yes' ){
				add_action( 'ep_event_notice', array( &$this, 'ep_user_not_joined_notice' ) );
			}

			if( isset( $_REQUEST['ep_user_cancel_event'] ) && $_REQUEST['ep_user_cancel_event'] == 'yes' ){
				add_action( 'ep_event_notice', array( &$this, 'ep_user_cancel_event_notice' ) );
			}

		}




		/**
		 * Create joined notice in front end based on user action
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_set_user_notice() {
			if( is_user_going() && ! isset( $_REQUEST['ep_user_joined_successful'] ) ){
				?>
				<div class="dg-alert dg-alert-info" role="alert">
					<?php _e( 'You have joined in this event.', 'eventpress' ) ?>
				</div>
				<?php
			}
		}



		/**
		 * Create successful notice in front end based on user action
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_set_join_notice(){
			?>
			<div class="dg-alert dg-alert-info" role="alert">
				<?php _e( 'You have joined successfully in this event.', 'eventpress' ) ?>
			</div>
			<?php
		}



		/**
		 * Create unavailable notice in front end based on user action
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_ticket_unavailable_notice() {
			?>
			<div class="dg-alert dg-alert-danger" role="alert">
				<?php _e( 'Sorry! The number of tickets you want is not available at the moment.', 'eventpress' ) ?>
			</div>
			<?php
		}



		/**
		 * Create already joined notice in front end based on user action
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_user_already_joined_notice() {
			?>
			<div class="dg-alert dg-alert-warning" role="alert">
				<?php _e( 'You have already joined in this event.', 'eventpress' ) ?>
			</div>
			<?php
		}



		/**
		 * Create not-joined notice in front end based on user action
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_user_not_joined_notice() {

			?>
			<div class="dg-alert dg-alert-warning" role="alert">
				<?php _e( 'You are not joined yet in this event.', 'eventpress' ) ?>
			</div>
			<?php

		}



		/**
		 * Create cancel notice in front end based on user action
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_user_cancel_event_notice() {
			?>
			<div class="dg-alert dg-alert-danger" role="alert">
				<?php _e( 'You have cancelled joining this event.', 'eventpress' ) ?>
			</div>
			<?php
		}



		/**
		 * Show RSVP in front end
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param OBJECT $event Event Object
		 */
		public function show_rsvp_on_front( $event ) {

			if( is_array( $event->rsvp ) ){
				?>
				<div class="ep-rsvp-list">
					<h3><?php _e( 'RSVP List', 'eventpress' ) ?></h3>
					<ul>
					<?php
					foreach( $event->rsvp as $rsvp ) {
						$user = new WP_User( $rsvp );
						?>
						<li>
							<div class="ep-avatar">
								<?php echo get_avatar( $rsvp, 32 ); ?>
							</div>
							<div class="ep-name">
								<?php echo $user->display_name ?>
							</div>
						</li>
						<?php
					}
					echo '</ul>';
				echo '</div>';
			}

		}



		/**
		 * Login an user
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_login_user_cb() {

			check_ajax_referer( 'ep_nonce_security_string', 'nonce' );

			$username = sanitize_text_field( $_POST['username'] );
			$pw = sanitize_text_field( $_POST['pw'] );

			$login = wp_signon( apply_filters( 'ep_ajax_login_data', array(
					'user_login' => $username,
					'user_password' => $pw,
					'remember' => true
					) ), false );

			if ( is_wp_error( $login ) ) {
				echo 'invalid';
				die();
			}else{
				echo 'valid';
				die();
			}

		}



		/**
		 * Set color theme
		 *
		 * @since 1.0
		 * @access public
		 */
		public function set_color_scheme() {

			$aem = $this->_data->get_options();
			?>
			<style type="text/css">
			.dg-eventpress-datetime-icon{background: <?php echo $aem['color_theme']; ?> }
			.dg-eventpress-red{color: <?php echo $aem['color_theme']; ?> }
			.dg-eventpress-datetime-details{border:1px solid <?php echo $aem['color_theme']; ?> }
			.dg-eventpress-single-header{border-top: 3px solid <?php echo $aem['color_theme']; ?> }
			.dg-btn-primary{background: <?php echo $aem['color_theme']; ?>; border-color: <?php echo $aem['color_theme']; ?>}
			</style>
			<?php

		}


	}


	/**
     * Initialize the EP_EVENTS class by creating an instance
     *
     * @since 1.0
     *
     * @return OBJECT Object of EP_EVENTS
     */
	function ep_init() {

		return EP_EVENTS::get_instance();

	}

	require_once AEM_FILES_DIR . '/classes/class.event.addon.handler.php';
	AEM_EVENT_ADDON_HANDLER::serve();

	/**
	 * Load Paypal Single calls
	 */
	// require_once AEM_FILES_DIR . '/lib/gateways/paypal-single.php';
	// EP_Paypal_single::serve();

	/**
	 * Create object
	 */
	$eventpress = ep_init();


	function my_custom_post_status(){
		$args = array('show_in_admin_all_list' => false);
		if (is_admin()) $args['protected'] = true;
		else $args['public'] = true;
		register_post_status( __( 'Recurring', 'eventpress' ), $args);
		register_post_status( __( 'Recurring-trash', 'eventpress' ), $args);
	}
	add_action( 'init', 'my_custom_post_status' );

}

