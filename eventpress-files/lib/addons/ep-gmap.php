<?php
/*
Addon Name: Location in Google Map
Description: Show event location in google map
Addon URI: https://duogeek.com
Version: 1.0
Author: DuoGeek
*/


/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) wp_die( __( DG_HACK_MSG, 'eventpress' ) );

if( ! class_exists( 'EP_Gmap' ) ){


	/**
	 * The GMap add on class
	 *
	 * @since 1.0
	 */
	class EP_Gmap{


		/**
		 * Event Location
		 *
		 * @since 1.0
		 * @access private
		 * @var STRING
		 */
		private $location;


		/**
		 * Event Latitude
		 *
		 * @since 1.0
		 * @access private
		 * @var STRING
		 */
		private $lat;



		/**
		 * Event Longitude
		 *
		 * @since 1.0
		 * @access private
		 * @var STRING
		 */
		private $long;


		/**
		 * Event Settings Options
		 *
		 * @since 1.0
		 * @access private
		 * @var ARRAY
		 */
		private $_data;



		/**
		 * Class constructor
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function __construct(){

			$aem = new EP_Options( 'ep', '' );
			$this->_data = $aem->get_options();

		}


		/**
		 * Initialization
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public static function serve () {
			$me = new EP_Gmap;
			$me->_add_hooks();
			do_action( 'ep_gmap_object_created', $me );
		}


		public function ep_gmap_scripts( $hook ){

			if( ! in_array( $hook, array( "post-new.php", "post.php", "edit.php" ) ) ) return false;

			global $post;
			$event = new EP_Event( $post->ID );
			wp_enqueue_script( 'ep-locationpicker', EP_FILES_URI . '/assets/js/locationpicker.jquery.js' );

			if( isset( $event->lat ) )
				wp_localize_script( 'ep-locationpicker', 'epcords', array( 'gmaplat' => ( float ) $event->lat, 'gmaplong' => ( float ) $event->long ) );
			else
				wp_localize_script( 'ep-locationpicker', 'epcords', array( 'gmaplat' => 0,'gmaplong' => 0 ) );
		}


		/**
		 * Run required hooks
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		private function _add_hooks() {
			add_action( 'ep_event_settings_addon', array( &$this, 'gmap_addon_settings' ) );
			add_filter( 'ep_event_settings_save_before', array( &$this, 'ep_event_settings_save_before_cb' ), 99, 1 );
			add_action( 'add_meta_boxes', array( &$this, 'gmap_lat_lang_meta' ) );
			add_action( 'wp_footer', array( &$this, 'map_style' ) );
			add_action( 'wp_enqueue_script', array( &$this, 'ad_gmap_script' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'ep_gmap_scripts') );
			wp_enqueue_script( 'ep-gmap-api', '//maps.google.com/maps/api/js?sensor=false&libraries=places', '', false );
			
			if( isset( $this->_data['map_before_content'] ) && $this->_data['map_before_content'] == 1 ){
				add_action( 'ep_before_single_content', array( &$this, 'show_event_location_map' ) );
			}else{
				add_action( 'ep_after_single_content', array( &$this, 'show_event_location_map' ) );
			}


		}
		

		/**
		 * Settings data for GMap add on
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function gmap_addon_settings() {

			?>
			<div class="postbox">
				<h3 class="hndle"><?php _e( 'GMap Settings', 'eventpress' ) ?></h3>
				<div class="inside">
					<table class="form-table">
						<tr>
							<th><?php _e( 'Show Map before the content?', 'eventpress' ) ?></th>
							<td>
								<input <?php checked( isset( $this->_data['map_before_content'] ) ? $this->_data['map_before_content'] : 0, 1, true ) ?> type="checkbox" name="ep[map_before_content]" value="1">
								<?php _e( 'Yes', 'eventpress' ) ?>
							</td>
						</tr>
					</table>
				</div>
			</div>
			<?php

		}



		/**
		 * Save gmap settings data
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_event_settings_save_before_cb( $ep ){

			if( ! isset( $ep['map_before_content'] ) ) $ep['map_before_content'] = 0;
			return $ep;

		}



		/**
		 * Meta box declaration for GMap
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function gmap_lat_lang_meta() {

			global $eventpress;
			add_meta_box( __( 'gmap_lat_lang', 'eventpress' ), 'GMap Settings', array( &$this, 'gmap_lat_lang_cb' ), $eventpress->post_type, 'normal', 'default' );

		}



		/**
		 * GMap meta box content
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function gmap_lat_lang_cb( $post ) {

			$event = new EP_Event( $post->ID );

			wp_nonce_field( 'ep_gmap_meta_box', 'ep_gmap_meta_box_nonce' );
			?>
			<div class="event_meta_fieldset">

				<h3 class="hndle dg-text-center"><span><?php _e( 'Event Location', 'eventpress' ) ?></span></h3>
				<div class="dg-content">
					<div class="dg-row dg-form-group">
						<div class="dg-col-md-12 dg-text-center"><p><?php _e( 'Type your location in the box below or move the pointer to select event location', 'eventpress' ) ?></p></div>
						<div class="dg-col-md-12 dg-text-center"><input name="event_meta[location]" type="text" id="ep-maps-address" class="dg-form-control dg-text-center" placeholder="<?php _e( 'Enter your location', 'evemtpress' ) ?>"/></div>
						<div class="dg-col-md-12 dg-text-center dg-top-space"><div id="ep-maps" style="width: 100%; height: 400px;"></div></div>
					</div>

					<div class="dg-row" style="margin: 20px 0">
						<div class="dg-col-md-12 dg-text-center"><h3><?php _e( 'Coordinates Settings of your Event Location', 'eventpress' ) ?></h3></div>
					</div>

					<div class="dg-row">
						<div class="dg-col-md-4 dg-text-center"><p><?php _e( 'Current Latitude', 'eventpress' ) ?></p></div>
						<div class="dg-col-md-8"><input type="text" name="event_meta[lat]" class="dg-form-control" id="lat" value="<?php echo isset( $event->lat ) ? $event->lat : '' ?>"></div>
					</div>

					<div class="dg-row">
						<div class="dg-col-md-4 dg-text-center"><p><?php _e( 'Current Longitude', 'eventpress' ) ?></p></div>
						<div class="dg-col-md-8"><input type="text" name="event_meta[long]" id="long" class="dg-form-control" value="<?php echo isset( $event->long ) ? $event->long : '' ?>"></div>
					</div>
				</div>
				<hr>
			</div>
			<?php

		}



		/**
		 * Render GMap
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function show_event_location_map() {

			global $post;
			$event = new EP_Event( $post->ID );

			if( isset($event->zoom_map) && $event->zoom_map == '' ) $event->zoom_map = 14;

			if( isset($event->lat) && $event->lat != '' && isset($event->long) && $event->long != '' ) {
			?>
			<div class="dg-row dg-top-space dg-eventpress-single-description">
				<div class="dg-col-md-12">
					<p class="dg-eventpress-title">Event <span class="dg-eventpress-red">Location</span></p>
					<p><?php echo $event->location; ?></p>
				</div>
			</div>
			<div class="dg-row dg-eventpress-single-featured">
			<div class="dg-col-md-12">
			<script type="text/javascript">

  				var directionsDisplay;
				var directionsService = new google.maps.DirectionsService();
				var map;
				var myLatlng = new google.maps.LatLng(<?php echo $event->lat ?>,<?php echo $event->long ?>);

				mylocation = mylat = mylong = '';
				navigator.geolocation.getCurrentPosition(function (position) {
					mylat = position.coords.latitude;
					mylong = position.coords.longitude;
					mylocation = new google.maps.LatLng(mylat, mylong);
				});


				function initialize() {
  					directionsDisplay = new google.maps.DirectionsRenderer();
  					var mapOptions = {
  		  				zoom: 14,
    					center: myLatlng
  					}
  					map = new google.maps.Map(document.getElementById("ep-map-canvas"), mapOptions);
  					var marker = new google.maps.Marker({
	      				position: myLatlng,
	      				map: map,
	      				title: '<?php echo $event->location ?>'
	  				});
  					directionsDisplay.setMap(map);
				}

				google.maps.event.addDomListener(window, 'load', initialize);

				function calcRoute() {
  					var selectedMode = 'DRIVING';
  					var request = {
      					origin: myLatlng,
      					destination: mylocation,

      					travelMode: google.maps.TravelMode[selectedMode]
  					};
	  				directionsService.route(request, function(response, status) {
	    				if (status == google.maps.DirectionsStatus.OK) {
	      					directionsDisplay.setDirections(response);
	    				}
	  				});
				}



		    </script>

		    <p class="dg-eventpress-title"><?php _e( 'See location', 'eventpress' ) ?> <span class="dg-eventpress-red"><?php _e( ' in Google Map', 'eventpress' ); ?></span></p>
		    <?php do_action( 'ep_gmap_render_before' ); ?>
			<div id="ep-map-canvas"></div>
			<?php do_action( 'ep_gmap_render_after' ); ?>
			<br>
			<button onclick="calcRoute();" type="button" class="dg-btn dg-btn-primary dg-btn-sm dg-btn-block"><?php _e( 'Get driving direction', 'eventpress' ) ?></button>
			</div>
			</div>
			<?php
			}

		}


		/**
		 * Some style for GMap
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function map_style() {
			?>
			<style type="text/css">
			#ep-map-canvas{
				width: 100%;
				height: 350px;
			}
			</style>
			<?php
		}

	}

	EP_Gmap::serve();

}