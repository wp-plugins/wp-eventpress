<?php

/**
 * Protect Direct Access
 */
if( ! defined( 'ABSPATH' ) ) wp_die( __( DG_HACK_MSG, 'eventpress' ) );

if( ! class_exists( 'EP_Event_Template_Handler' ) ) {

	/**
	 * Template Handler Class
	 *
	 * @since 1.0
	 */
	class EP_Event_Template_Handler {

		/**
		 * Class constructor
		 *
		 * @since 1.0
		 * @access public
		 */
		public function __construct(){

		}

		/**
		 * Load single template file
		 *
		 * @since 1.0
		 * @access public static
		 *
		 * @return FILE Single Template
		 */
		public static function load_single_template( $post_type ){

			global $post;

			if( ! empty( $post ) && $post->post_type == $post_type ){

				$single_template_file = apply_filters( 'ep_single_event_template', 'single-ep-event.php' );

				$child_theme = get_stylesheet_directory() . '/eventpress/' . $single_template_file;
				$parent_theme = get_template_directory() . '/eventpress/' . $single_template_file;
				$plugin_theme = EP_FILES_DIR . '/templates/' . $single_template_file;

				if( file_exists( $child_theme ) ){
					$single_template = $child_theme;
				} elseif ( file_exists( $parent_theme ) ) {
					$single_template = $parent_theme;
				} else {
					$single_template = $plugin_theme;
				}

			}

			return $single_template;

		}

		/**
		 * Load archive template file
		 *
		 * @since 1.0
		 * @access public static
		 *
		 * @return FILE Archive Template
		 */
		public static function load_archive_template( $post_type ){

			global $post;
			global $event_archive;
			global $eventpress;

			$event_archive = $eventpress->event_archive();

			if( ! empty( $post ) && $post->post_type == $post_type ){

				$archive_template_file = apply_filters( 'ep_archive_event_template', 'archive-ep-event.php' );

				$child_theme = get_stylesheet_directory() . '/eventpress/' . $archive_template_file;
				$parent_theme = get_template_directory() . '/eventpress/' . $archive_template_file;
				$plugin_theme = EP_FILES_DIR . '/templates/' . $archive_template_file;

				if( file_exists( $child_theme ) ){
					$archive_template = $child_theme;
				} elseif ( file_exists( $parent_theme ) ) {
					$archive_template = $parent_theme;
				} else {
					$archive_template = $plugin_theme;
				}

			}

			return $archive_template;

		}

		/**
		 * Load monthly calendar template file
		 *
		 * @since 1.0
		 * @access public static
		 *
		 * @return FILE Monthly Calendar Template
		 */
		public static function load_monthly_calendar_template( $post_type ){

			global $post;
			global $event_archive;
			global $eventpress;

			$event_archive = $eventpress->event_archive();

			if( ! empty( $post ) && $post->post_type == $post_type ){

				$monthly_calendar_template_file = apply_filters( 'ep_monthly_calendar_event_template', 'monthly-calendar-ep-event.php' );

				$child_theme = get_stylesheet_directory() . '/eventpress/' . $monthly_calendar_template_file;
				$parent_theme = get_template_directory() . '/eventpress/' . $monthly_calendar_template_file;
				$plugin_theme = EP_FILES_DIR . '/templates/' . $monthly_calendar_template_file;

				if( file_exists( $child_theme ) ){
					$monthly_calendar_template = $child_theme;
				} elseif ( file_exists( $parent_theme ) ) {
					$monthly_calendar_template = $parent_theme;
				} else {
					$monthly_calendar_template = $plugin_theme;
				}

			}

			return $monthly_calendar_template;

		}

	}

}