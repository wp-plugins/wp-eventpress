<?php
/*
Addon Name: Capacity
Description: Set capacity for an event
Addon URI: https://duogeek.com
Version: 1.0
Author: DuoGeek
*/

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) wp_die( __( DG_HACK_MSG, 'eventpress' ) );

if( ! class_exists( 'EP_Capacity' ) ){


	/**
	 * The capacity add on class
	 *
	 * @since 1.0
	 */
	class EP_Capacity{


		/**
		 * Event Capacity
		 *
		 * @since 1.0
		 * @access private
		 * @var INT
		 */
		private $capacity;



		/**
		 * Class constructor
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function __construct(){

			$aem = new DG_Options( 'aem', '' );
			$this->_data = $aem->get_options();
			//global $pos;
			//echo $tickets = get_post_meta( $post->ID, 'ep_capacity', true );

		}



		/**
		 * Initialization
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public static function serve () {

			$me = new EP_Capacity;
			$me->_add_hooks();
			do_action( 'ep_capacity_object_created', $me );

		}



		/**
		 * Run required hooks
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function _add_hooks() {

			add_action( 'add_meta_boxes', array( &$this, 'ep_capacity_meta' ) );
			add_action( 'ep_user_join_rsvp', array( &$this, 'ep_user_join_rsvp_cb' ), 99, 5 );
			add_action( 'ep_user_cancel_rsvp', array( &$this, 'ep_user_cancel_rsvp_cb' ), 99, 4 );
			add_filter( 'ep_allow_join_rsvp', array( &$this, 'ep_allow_join_rsvp_cb' ), 99, 2 );

		}



		/**
		 * Create Capacity Meta Box
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_capacity_meta(){
			global $eventpress;
			add_meta_box( __( 'ep_capacity' ), __( 'Capacity', 'eventpress' ), array( &$this, 'ep_capacity_meta_cb' ), $eventpress->post_type['post_type'], 'side', 'high' );
		}



		/**
		 * capacity Meta box content
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param OBJECT $post Event object
		 */
		public function ep_capacity_meta_cb( $post ) {

			$event = new DG_Event( $post->ID );

			wp_nonce_field( 'ep_capacity_meta_box', 'ep_capacity_meta_box_nonce' );
			?>
			<div class="event_meta_fieldset">
				<div class="event_meta_filed">
					<p>
						<label for="capacity"><?php _e( 'Capacity', 'eventpress' ) ?></label>
						<input type="text" name="event_meta[capacity]" id="capacity" value="<?php echo isset( $event->capacity ) ? $event->capacity : '' ?>">
					</p>
				</div>
			</div>
			<?php

		}



		/**
		 * Process ticket number when user join in an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param ARRAY $rsvp RSVP List as array
		 * @param INT $event_id Event ID
		 * @param INT $user_id User ID
		 * @param INT $ticket No of tickets
		 * @param INT $ticket_id Ticket ID
		 */
		public function ep_user_join_rsvp_cb( $rsvp, $event_id, $user_id, $ticket, $ticket_id ) {

			$tickets = get_post_meta( $event_id, 'ep_joined', true );
			$tickets += $ticket;
			update_post_meta( $event_id, 'ep_joined', $tickets );
			do_action( 'ep_join_ticket_no_updated', $rsvp, $event_id, $user_id, $ticket, $ticket_id );

		}



		/**
		 * Process ticket number when an user cancel an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param ARRAY $rsvp RSVP List as array
		 * @param INT $event_id Event ID
		 * @param INT $user_id User ID
		 * @param INT $ticket No of tickets
		 */
		public function ep_user_cancel_rsvp_cb( $rsvp, $event_id, $user_id, $ticket ){

			$tickets = get_post_meta( $event_id, 'ep_joined', true );
			$tickets -= $ticket;
			update_post_meta( $event_id, 'ep_joined', $tickets );
			do_action( 'ep_cancel_ticket_no_updated', $rsvp, $event_id, $user_id, $ticket );

		}



		/**
		 * Class constructor
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param BOOL $allow TRUE | FALSE
		 * @param INT $no_of_ticket Number of tickets
		 */
		public function ep_allow_join_rsvp_cb( $allow, $no_of_ticket ){

			remove_filter( 'ep_allow_join_rsvp', array( &$this, 'ep_allow_join_rsvp_cb' ), 99, 2 );
			global $post;
			if( $post ){
				$event = new DG_Event( $post->ID );
				add_filter( 'ep_allow_join_rsvp', array( &$this, 'ep_allow_join_rsvp_cb' ), 99, 2 );

				$tickets = get_post_meta( $post->ID, 'ep_joined', true );
				//echo $event->capacity;
				//echo $tickets + $no_of_ticket;
				if( $event->capacity == 0 ) return true;
				if( ( $tickets + $no_of_ticket ) >= $event->capacity ){
					return false;
				}
			}
			return true;

		}


	}

	EP_Capacity::serve();

}