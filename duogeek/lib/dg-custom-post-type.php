<?php
/**
 * Custom Post Type Class
 *
 * 
 */

/*
 * Restrict direct access
 */
if ( ! defined( 'ABSPATH' ) ) wp_die( DG_HACK_MSG );


if( ! class_exists( 'DGCustomPostType' ) ) {

	/**
	 * Class as a framework of custom post type, custom taxonomies, custom meta box etc
	 *
	 * @since 1.0
	 */
	abstract class DGCustomPostType{

		/**
		 * An array of custom post type attributes to be created
		 * 
		 * @since 1.0
		 * @access protected
		 * @var array
		 */
		protected $custom_post_type = array();

		/**
		 * An array of custom taxonomies attributes to be created
		 * 
		 * @since 1.0
		 * @access protected
		 * @var ARRAY
		 */
		protected $custom_tax = array();


		/**
		 * Class constructor
		 * 
		 * @since 1.0
		 * @access protected
		 */
		protected function __construct( $args = array() ) {
			 
			$defaults = array(
				'post_type'			=> 'duogeek',
				'add_new'			=> 'Add New',
				'add_new_item'		=> 'Add New Item',
				'new_item'			=> 'New Item',
				'edit_item'			=> 'Edit Item',
				'view_item'			=> 'View Item',
				'all_items'			=> 'All Items',
				'search_items'		=> 'Search Items',
				'parent_item_colon'	=> 'Parent Items:',
				'not_found'			=> 'No items found',
				'not_found_in_trash'=> 'No items found in trash',
				'capability_type'	=> 'post',
				'supports'			=> array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
				'rewrite'			=> array( 'slug' => 'duogeek' ),
				'has_archive'		=> true
			);
			
			$this->custom_post_type = wp_parse_args( $args, $defaults );

			/**
			 * Adding Filters
			 */
			$this->custom_post_type['post_type'] = apply_filters( 'dg_' . $this->custom_post_type['post_type'] . '_post_type', $this->custom_post_type['post_type'] );

			$this->custom_post_type['supports'] = apply_filters( 'dg_' . $this->custom_post_type['post_type'] . '_post_type_supports', $this->custom_post_type['supports'] );

			$this->custom_post_type['rewrite'] = apply_filters( 'dg_' . $this->custom_post_type['post_type'] . '_post_type_rewrite', $this->custom_post_type['rewrite'] );

			$this->custom_post_type['has_archive'] = apply_filters( 'dg_' . $this->custom_post_type['post_type'] . '_post_type_archive_slug', $this->custom_post_type['has_archive'] );

		}


		/**
		 * Register custom post type
		 * 
		 * @since 1.0
		 * @access protected
		 */
		protected function register_custom_post_type() {
			$labels = array(
				'name'               => $this->custom_post_type['name'],
				'singular_name'      => $this->custom_post_type['singular_name'],
				'menu_name'          => $this->custom_post_type['menu_name'],
				'name_admin_bar'     => $this->custom_post_type['name_admin_bar'],
				'add_new'            => $this->custom_post_type['add_new'],
				'add_new_item'       => $this->custom_post_type['add_new_item'],
				'new_item'           => $this->custom_post_type['new_item'],
				'edit_item'          => $this->custom_post_type['edit_item'],
				'view_item'          => $this->custom_post_type['view_item'],
				'all_items'          => $this->custom_post_type['all_items'],
				'search_items'       => $this->custom_post_type['search_items'],
				'parent_item_colon'  => $this->custom_post_type['parent_item_colon'],
				'not_found'          => $this->custom_post_type['not_found'],
				'not_found_in_trash' => $this->custom_post_type['not_found_in_trash']
			);
		
			$args = array(
				'labels'             => apply_filters( 'dg_' . $this->custom_post_type['post_type'] . '_post_type_labels', $labels ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'menu_icon'			 => isset( $this->custom_post_type['menu_icon'] ) ? $this->custom_post_type['menu_icon'] : null,
				'query_var'          => true,
				'rewrite'            => $this->custom_post_type['rewrite'],
				'capability_type'    => 'post',
				'has_archive'        => $this->custom_post_type['has_archive'],
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => $this->custom_post_type['supports']
			);

			register_post_type( 
				$this->custom_post_type['post_type'], 
				apply_filters( 'dg_' . $this->custom_post_type['post_type'] . '_post_type_args', $args ) 
			);
		}


		/**
		 * Set custom taxonomies
		 * 
		 * @since 1.0
		 * @access protected
		 *
		 * @param array $args custom taxonomy arguments
		 */
		protected function set_tax( $args ){
			
			$defaults = array(
				'search_items'      => 'Search Items',
				'all_items'         => 'All Items',
				'parent_item'       => 'Parent Items',
				'parent_item_colon' => 'Parent Item:',
				'edit_item'         => 'Edit Item',
				'update_item'       => 'Update Item',
				'add_new_item'      => 'Add New Item',
				'new_item_name'     => 'New Item Name',
				'menu_name'         => 'Item',
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true
			);
			
			$this->custom_tax = wp_parse_args( $args, $defaults );
		}


		/**
		 * Register custom taxonomies
		 * 
		 * @since 1.0
		 * @access protected
		 */
		protected function register_custom_taxonomies() {
			$labels = array(
				'name'              => $this->custom_tax['name'],
				'singular_name'     => $this->custom_tax['singular_name'],
				'search_items'      => $this->custom_tax['search_items'],
				'all_items'         => $this->custom_tax['all_items'],
				'parent_item'       => $this->custom_tax['parent_item'],
				'parent_item_colon' => $this->custom_tax['parent_item_colon'],
				'edit_item'         => $this->custom_tax['edit_item'],
				'update_item'       => $this->custom_tax['update_item'],
				'add_new_item'      => $this->custom_tax['add_new_item'],
				'new_item_name'     => $this->custom_tax['new_item_name'],
				'menu_name'         => $this->custom_tax['menu_name'],
			);
		
			$args = array(
				'hierarchical'      => $this->custom_tax['hierarchical'],
				'labels'            => $labels,
				'show_ui'           => $this->custom_tax['show_ui'],
				'show_admin_column' => $this->custom_tax['show_admin_column'],
				'query_var'         => $this->custom_tax['query_var'],
				'rewrite'           => $this->custom_tax['rewrite'],
			);
		
			register_taxonomy( 
				apply_filters( 'dg_' . $this->custom_tax['tax_name'] . '_tax', $this->custom_tax['tax_name'] ),
				$this->custom_post_type['post_type'], 
				apply_filters( 'dg_' . $this->custom_tax['tax_name'] . '_tax_args', $args )
			);
		}


		/**
		 * Register meta boxes
		 * 
		 * @since 1.0
		 * @access public
		 *
		 * @param array $meta_boxes Array of meta boxes
		 */
		public function add_custom_meta_boxes( $meta_boxes ) {
			foreach( $meta_boxes as $meta_box ){
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

	}

}