<?php

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) wp_die( __( DG_HACK_MSG, 'eventpress' ) );

if( ! class_exists( 'DG_Event' ) ){

	/**
	 * The event class that should hold all properties, methods of a single event
	 *
	 * @since 1.0
	 */
	class DG_Event{


		/**
		 * Event Meta Key Constant
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		const META_KEY = '_event_meta_keys';


		/**
		 * Event
		 *
		 * @since 1.0
		 * @access private
		 * @var OBJECT
		 */
		private $_event;


		/**
		 * Event ID
		 *
		 * @since 1.0
		 * @access private
		 * @var INT
		 */
		private $ID;


		/**
		 * Event Location
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $location;


		/**
		 * Event Start Date
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $start_date;


		/**
		 * Event End Date
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $end_date;


		/**
		 * Event Start Time
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $start_time;


		/**
		 * Event End Time
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $end_time;


		/**
		 * Event Status
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $status;


		/**
		 * Event Type
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $type;


		/**
		 * Event Fee
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $fees;

		/**
		 * Event Fee
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $capacity;

		/**
		 * Recurring Event Start From
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $rec_start_from;

		/**
		 * Recurring Event Start Time
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $rec_day_start_time;

		/**
		 * Recurring Event Repetition
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $rec_repeat;

		/**
		 * Recurring Event Repetition Week
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $rec_week_day;

		/**
		 * Recurring Event Repetition Month
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $rec_month_date;

		/**
		 * Recurring Event Repetition Year Month
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $rec_year_month;

		/**
		 * Recurring Event Repetition Year Date
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $rec_year_date;

		/**
		 * Recurring Event Repetition End Time
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $rec_end_at;

		/**
		 * Recurring Event Repetition Duration
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $rec_end_time;


		/**
		 * Event Featured Image
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $thumb;


		/**
		 * Event RSVP
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $rsvp = array();


		/**
		 * Event Featured Image
		 *
		 * @since 1.0
		 * @access public
		 * @var STRING
		 */
		public $child_events = array();



		/**
		 * if the event is still allowing RSVPs
		 *
		 * @since 1.0
		 * @access public
		 * @var BOOL
		 */
		public $can_rsvp;


		/**
		 * if the event is still allowing RSVPs
		 *
		 * @since 1.0
		 * @access public
		 * @var INT
		 */
		public $joined;



		/**
		 * Class constructor
		 *
		 * @since 1.0
		 * @access public
		 */
		public function __construct( $id ){

			if( ! $id ) $id = get_the_ID();
			if( ! $id ) return;

			$this->ID = $id;
			$this->can_rsvp = true;
			$this->_event = get_post( $id );
			$this->get_post_meta();

		}


		/**
		 * Get event meta data for a single event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return	self::OBJECT
		 */
		public function get_post_meta() {

			$event_meta = get_post_meta( $this->ID, self::META_KEY, true );


			if( is_array( $event_meta ) ){
				foreach ( $event_meta as $meta => $value ) {
					$this->$meta = $value;
				}
			}

			$args = array(
				'post_parent' => $this->ID,
				'posts_per_page' => -1,
				'post_status' => 'any'
			);

			$this->child_events = get_all_posts( array(
				'post_type' => get_post_type( $this->ID ),
				'post_parent' => $this->ID,
				'post_status'  => 'recurring',
				) );

			return $this;

		}


		/**
		 * Set event meta data for a single event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param ARRAY $meta Array of post meta
		 *
		 * @return	self::OBJECT
		 */
		public function set_post_meta( $meta ) {

			$meta_data = array();

			foreach ( $meta as $key => $value ) {
				$this->$key = $value;
				$meta_data[$key] = $value;
			}

			return $meta_data;

		}


		/**
		 * Update event meta data for a single event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @param ARRAY $meta Array of post meta
		 *
		 * @return	self::OBJECT
		 */
		public function update_post_meta( $meta ) {

			$meta_data = $this->set_post_meta( $meta );

			update_post_meta( $this->ID, self::META_KEY, $meta_data );

			$this->get_post_meta();

			return $this;

		}



		/**
		 * Get ID of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return INT ID of an event
		 */
		public function get_id() {

			return $this->ID;

		}



		/**
		 * Get Title of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return STRING Event Title
		 */
		public function get_title () {

			return ! empty ( $this->_event )
				? $this->_event->post_title
				: false;

		}




		/**
		 * Get Author ID of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return INT ID of an event author
		 */
		public function get_author () {

			return ! empty( $this->_event )
				? $this->_event->post_author
				: false;

		}



		/**
		 * Get Excerpt of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return STRING Event Excerpt
		 */
		public function get_excerpt () {

			return ! empty( $this->_event )
				? $this->_event->post_excerpt
				: false;

		}



		/**
		 * Get Content of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return STRING Event Content
		 */
		public function get_content () {

			return ! empty( $this->_event )
				? wpautop( $this->_event->post_content )
				: false;

		}



		/**
		 * Get type of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return STRING Paid or Free
		 */
		public function get_type () {

			return ! empty( $this->_event )
				? $this->_event->post_type
				: false;

		}




		/**
		 * Get Price of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return NUMBER Event Price
		 */
		public function get_fees () {

			$event_meta = get_post_meta( $this->ID, self::META_KEY, true );

			if( is_array( $event_meta ) ){
				return $event_meta['fees'];
			}

		}



		/**
		 * Get Parent of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return INT Parent ID of an event
		 */
		public function get_parent () {

			return ! empty( $this->_event )
				? $this->_event->post_parent
				: false;

		}



		/**
		 * If an event has featured image
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return BOOL TRUE | FALSE
		 */
		public function has_featured_image () {

			return has_post_thumbnail( $this->get_id() );

		}



		/**
		 * Get Featrued image of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return STRING Event Featured Image
		 */
		public function get_featured_image ( $size = false ) {

			$size = $size ? $size : 'thumbnail';
			return get_the_post_thumbnail( $this->get_id(), $size );

		}



		/**
		 * Get Featured Image URL of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return STRING Event Featured Image URL
		 */
		public function get_featured_image_url ( $size = false ) {

			return wp_get_attachment_url( get_post_thumbnail_id( $this->get_id() ) );

		}



		/**
		 * Get Featured Image ID of an event
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return INT ID of featrued image of an event
		 */
		public function get_featured_image_id () {

			return get_post_thumbnail_id( $this->get_id() );

		}



		/**
		 * Check if an user is going or not
		 *
		 * @since 1.0
		 * @access public
		 *
		 * @return BOOL TRUE | FALSE
		 */
		public function is_user_going() {

			if( in_array( get_current_user_id(), $this->rsvp ) )
				return true;
			return false;

		}


	}



}