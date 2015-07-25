<?php

/**
 * Protect direct access
 */
if( ! defined( 'ABSPATH' ) ) wp_die( __( DG_HACK_MSG, 'eventpress' ) );

if( ! class_exists( 'AEM_EVENT_ADDON_HANDLER' ) ){

	/**
	 * Addon handler class for Events
	 *
	 * @since 1.0
	 */
	class AEM_EVENT_ADDON_HANDLER{


		/**
		 * Event Addon Meta Key
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		const META_KEY = 'ep_event_active_addon';


		/**
		 * Class constructor
		 *
		 *
		 * @since 1.0
		 * @access private
		 */
		private function __construct () {
			define( 'EP_EVENTS_ADDONS_DIR', EP_FILES_DIR . '/lib/addons' );
			$this->_load_active_addons();

		}


		/**
		 * Singleton Class Object
		 * Call hooks
		 *
		 * @since 1.0
		 * @access public
		 */
		public static function serve () {

			$me = new AEM_EVENT_ADDON_HANDLER;
			$me->_add_hooks();

		}


		/**
		 * Calling required hooks
		 *
		 * @since 1.0
		 * @access private
		 */
		private function _add_hooks () {

			add_action( 'ep_event_addons_list', array( &$this, 'ep_event_addons_list' ) );
			add_action( 'admin_action_activate_ep_addon', array( &$this, 'activate_ep_addon_cb' ) );
			add_action( 'admin_action_deactivate_ep_addon', array( &$this, 'deactivate_ep_addon_cb' ) );

		}


		/**
		 * Get all addons
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return ARRAY Array of addons
		 */
		public static function get_all_addons () {

			$all = glob( EP_EVENTS_ADDONS_DIR . '/*.php' );
			$all = $all ? $all : array();
			$ret = array();
			foreach( $all as $path ) {
				$ret[] = pathinfo( $path, PATHINFO_FILENAME );
			}
			return $ret;

		}


		/**
		 * Path of addon
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param STRING $addon Addon File Name
		 *
		 * @return STRING Path of the addon
		 */
		public static function addon_to_path( $addon ) {

			$addon = str_replace('/', '_', $addon);
			return EP_EVENTS_ADDONS_DIR . '/' . "{$addon}.php";

		}


		/**
		 * Load active addons
		 *
		 * @since 1.0
		 * @access private
		 */
		private function _load_active_addons () {

			$active = $this->get_active_addons();

			foreach( $active as $addon ) {
				$path = self::addon_to_path( $addon );
				if ( ! file_exists( $path ) ) continue;
				else require_once( $path );
			}

		}


		/**
		 * Get active addons
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return ARRAY An array of active addons
		 */
		public static function get_active_addons () {

			$active = get_option( self::META_KEY );
			$active = $active ? $active : array();

			return $active;

		}


		/**
		 * Check if an addon is active
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param STRING $addon addon
		 *
		 * @return BOOL TRUE if active, otherwise FALSE
		 */
		public static function is_addon_active( $addon ) {

			$active = self::get_active_addons();
			return in_array( $addon, $active );

		}


		/**
		 * Get info from addon file
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param STRING $addon addon
		 *
		 * @return BOOL TRUE if active, otherwise FALSE
		 */
		public static function get_addon_info( $addon ) {

			$path = self::addon_to_path( $addon );
			$default_headers = array(
				'Name' => 'Addon Name',
				'Author' => 'Author',
				'Description' => 'Description',
				'Addon URI' => 'Addon URI',
				'Version' => 'Version',
				'Detail' => 'Detail'
			);

			return get_file_data( $path, $default_headers, 'addon' );

		}


		/**
		 * Event Addon List page
		 *
		 * @since 1.0
		 * @access public
		 *
		 */
		public function ep_event_addons_list() {

			$all = self::get_all_addons();
			$active = self::get_active_addons();

			?>

			<table class='widefat event_addon' id='eab_addons_hub'>
				<thead>
					<tr>
						<th><?php _e( 'Addon Name', 'eventpress' ) ?></th>
						<th><?php _e( 'Addon Description', 'eventpress' ) ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e( 'Addon Name', 'eventpress' ) ?></th>
						<th><?php _e( 'Addon Description', 'eventpress' ) ?></th>
					</tr>
				</tfoot>
				<tbody>
					<?php
						foreach( $all as $addon ) {
							$addon_data = self::get_addon_info( $addon );
							// Name is MUST
							if ( empty( $addon_data['Name'] ) ) continue;
							$is_active = in_array( $addon, $active );
					?>
					<tr>
						<td>
							<?php echo $addon_data['Name'] ?><br>
							<?php if( $is_active ) { ?>
								<a class="active" href="<?php echo admin_url( 'admin.php?action=deactivate_ep_addon&addon=' . $addon ) ?>"><?php _e( 'Deactivate', 'eventpress' ) ?></a>
							<?php }else{ ?>
								<a href="<?php echo admin_url( 'admin.php?action=activate_ep_addon&addon=' . $addon ) ?>"><?php _e( 'Activate', 'eventpress' ) ?></a>
							<?php } ?>
						</td>
						<td>
							<?php echo $addon_data['Description'] ?><br>
							<?php _e( 'Version:', 'eventpress' ) ?> <?php echo $addon_data['Version'] ?> | <?php _e( 'By', 'eventpress' ) ?> <a href="<?php echo $addon_data['Addon URI'] ?>" target="_blank"><?php echo $addon_data['Author'] ?></a>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>

			<?php

		}


		/**
		 * Event Addon Activation
		 *
		 * @since 1.0
		 * @access public
		 *
		 */
		public function activate_ep_addon_cb() {

			if( ! current_user_can( 'manage_options' ) ) return false;

			$addon = $_REQUEST['addon'];
			$active = self::get_active_addons();
			// Check if already active
			if( ! in_array( $addon, $active ) ) {

				$active[] = $addon;

				update_option( self::META_KEY, $active );

			}

			wp_redirect( admin_url( 'edit.php?post_type=ep_events&page=event-settings&tab=addons&tab=addons&notice_result=updated&msg=' . __( 'Addon+activated+successfully.', 'eventpress' ) ) );

		}


		/**
		 * Event Addon Deactivation
		 *
		 * @since 1.0
		 * @access public
		 *
		 */
		public function deactivate_ep_addon_cb() {

			if( ! current_user_can( 'manage_options' ) ) return false;

			$addon = $_REQUEST['addon'];
			$active = self::get_active_addons();

			// Check if already active
			if( in_array( $addon, $active ) ) {

				$key = array_search( $addon, $active );
				// The addon is not found in active list
				if( $key === false ) return false;

				unset( $active[$key] );
				update_option( self::META_KEY, $active );

			}

			wp_redirect( admin_url( 'edit.php?post_type=ep_events&page=event-settings&tab=addons&tab=addons&notice_result=updated&msg=' . __( 'Addon+deactivated+successfully.', 'eventpress' ) ) );

		}




	}
	
	AEM_EVENT_ADDON_HANDLER::serve();

}