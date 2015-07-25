<?php
/*
Addon Name: Event Tickets
Description: Show and Email Tickets to the user
Addon URI: https://duogeek.com
Version: 1.0
Author: DuoGeek
*/


/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) wp_die( __( DG_HACK_MSG, 'eventpress' ) );

if( ! class_exists( 'EP_Tickets' ) ){


	/**
	 * The Ticket add on class
	 *
	 * @since 1.0
	 */
	class EP_Tickets{



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

			$me = new EP_Tickets;
			$me->_add_hooks();
			do_action( 'ep_ticket_object_created', $me );

		}

		/**
		 * Run required hooks
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function _add_hooks() {

			add_action( 'ep_event_settings_addon', array( &$this, 'ep_event_ticket_settings' ) );
			add_filter( 'ep_localized_data_text', array( &$this, 'ep_localized_data_text_cb' ) );
			//add_action( 'ep_user_join_rsvp', array( &$this, 'process_ticket' ), 99, 5 );

		}



		/**
		 * Ticket settings data
		 *
		 *
		 * @since 1.0
		 * @access public
		 */
		public function ep_event_ticket_settings() {

			$template = '<h2>EVENT_NAME</h2>
						<p>EVENT_LOCATION</p>
						<p>No of Seats: TICKET_AMOUNT</p>
						<p>#TICKET_NO</p>
						<br><br>
						Name: USER_DISPLAY_NAME<br>
						Start: EVENT_START_DATE at EVENT_START_TIME<br>
						End: EVENT_END_DATE at EVENT_END_TIME<br><br>
						<p>Thank you for joining us!</p>';

			?>
			<div class="postbox">
				<h3 class="hndle"><?php _e( 'Ticket Settings', 'eventpress' ) ?></h3>
				<div class="inside">
					<table class="form-table">
						<tr>
							<th><?php _e( 'Email Ticket Template', 'eventpress' ) ?></th>
							<td>
								<textarea name="aem[ticket_template]" style="width: 100%; height: 200px"><?php echo isset( $this->_data['ticket_template'] ) && $this->_data['ticket_template'] != '' ? stripcslashes( $this->_data['ticket_template'] ) : stripcslashes( $template ); ?></textarea><br>
								<em><?php _e( 'You can use these macros: EVENT_NAME, EVENT_LOCATION, EVENT_START_DATE, EVENT_START_TIME, EVENT_END_DATE, EVENT_END_TIME, EVENT_PRICE, USER_DISPLAY_NAME, USER_EMAIL, TICKET_AMOUNT, TICKET_NO', 'eventpress' ) ?></em>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<?php

		}



		/**
		 * Process and send ticket in email to the user
		 *
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
		public function process_ticket( $rsvp, $event_id, $user_id, $ticket, $ticket_id ) {

			$event = new DG_Event( $event_id );
			$user = new WP_User( $user_id );

			$temp = array(
				$event->get_title(),
				$event->location,
				$event->start_date,
				$event->start_time,
				$event->end_date,
				$event->end_time,
				$event->fees,
				$user->display_name,
				$user->user_email,
				$ticket,
				$ticket_id
				);

			$replace = array(
				'EVENT_NAME',
				'EVENT_LOCATION',
				'EVENT_START_DATE',
				'EVENT_START_TIME',
				'EVENT_END_DATE',
				'EVENT_END_TIME',
				'EVENT_PRICE',
				'USER_DISPLAY_NAME',
				'USER_EMAIL',
				'TICKET_AMOUNT',
				'TICKET_NO'
				);

			$content = $this->_data['ticket_template'];
			$content = str_replace( $replace, $temp, $content );

			wp_mail( $user->user_email, apply_filters( 'EP_EVENT_TICKET_MAIL_SUBJECT', __( 'Your event ticket', 'eventpress' ) ) );


		}


		/**
		 * Customized Localized Data
		 *
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param ARRAY $arr Localized data array
		 */
		public function ep_localized_data_text_cb( $arr ){

			global $post;
			if( class_exists( 'EP_Capacity' ) ) {
				$cap = new EP_Capacity;
				remove_filter( 'ep_allow_join_rsvp', array( $cap, 'ep_allow_join_rsvp_cb' ), 99, 2 );
				$event = new DG_Event( $post->ID );
				add_filter( 'ep_allow_join_rsvp', array( $cap, 'ep_allow_join_rsvp_cb' ), 99, 2 );
				//delete_post_meta( $post->ID, 'ep_joined' );
				$tickets = get_post_meta( $post->ID, 'ep_joined', true );
				$arr['ticket'] = 1;
				$arr['ticket_txt'] = __( 'No of tickets:', 'eventpress' );
				$arr['available'] = $event->capacity - $tickets;
				$arr['ticket_alert'] = sprintf( __( "Sorry, we have %d available.", 'eventpress' ), $arr['available'] );
			}else{
				$arr['ticket'] = 1;
				$arr['ticket_txt'] = __( 'No of tickets:', 'eventpress' );
			}
			return $arr;

		}

	}

	EP_Tickets::serve();

}