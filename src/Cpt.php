<?php namespace Lean;
/**
 * Class to crate new CPT files wiout to much repeated code.
 *
 * @since 0.1.0
 *
 * @package jcpr
 * @subpackage inc
 */

/**
 * Abstraction to avoid repeated code and create multiple CPT:
 */
class Cpt {
	/**
	 * The singular name of the custom post type.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var string
	 */
	protected $singular = '';

	/**
	 * The plural name of the custom post type.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var string
	 */
	protected $plural = '';

	/**
	 * The name of the custom post type saved as string
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var string
	 */
	public $post_type = '';

	/**
	 * Slug of the custom post type
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The placeholder to display on the title input area.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var string
	 */
	protected $title_placeholder = '';

	/**
	 * Holds the labels to be used as a value for hold all the different labels
	 * of the CPT.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var array
	 */
	protected $labels = array();

	/**
	 * Holsd the handling of rewrites for this post type using an array
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var array
	 */
	protected $rewrite = array();

	/**
	 * Holsd the supports of certain feature(s) for a given post type.
	 * of the CPT.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var array
	 */
	protected $supports = array(
		'title',
		'editor',
		'thumbnail',
		'page-attributes',
	);

	/**
	 * Has all of the arguments to be used to create the CPT.
	 *
	 * @since 0.1.0
	 * @access protected
	 * @var array
	 */
	protected $args = array(
		// Make this post type be visible to authors and site visitors.
		'public' => true,
		// Exclude this from the default search, but not from the custom one.
		'exclude_from_search'  => true,
		// Let this post type be queried for on the front-end via parse_request().
		'publicly_queryable' => true,
		// Display the user interface of this post type.
		'show_ui' => true,
		// This post type is not available for selection on navigation menus.
		'show_in_nav_menus' => false,
		// Show the post type in the admin menu. Relies on show_ui being true.
		'show_in_menu' => true,
		// Leave this at default 'post'. This string is used to build the read/edit/delete capabilities.
		'capability_type' => 'post',
		// Disallow hierarchy or parent/child relationship.
		'hierarchical' => false,
		// Icon to use for this menu.
		'menu_icon' => 'dashicons-wordpress',
		// Disable post type archive for this.
		'has_archive' => false,
		// Allow this post type to be exported.
		'can_export' => true,
		// Make this the fifth top-level menu in the the dashboard.
		'menu_position' => 5,
	);

	/**
	 * PHP5 Constructor
	 *
	 * @since 0.1.0
	 *
	 * @param array $options {.
	 *     @type string post_type The name for CPT.
	 *     @type string singular Singular name of the CPT.
	 *     @type string plural Plural name of the CPT.
	 *     @type string slug The slug of the CPT.
	 * }
	 */
	public function __construct( $options = array() ) {
		if ( ! is_array( $options ) ) {
			return;
		}

		// Set dynamic values to each instance variable.
		$values = array( 'post_type', 'singular', 'plural', 'slug', 'title_placeholder', 'supports' );
		foreach ( $values as $value ) {
			if ( array_key_exists( $value, $options ) ) {
				$this->$value = $options[ $value ];
			}
		}

		if ( isset( $options['args'] ) ) {
			$this->args = wp_parse_args( $options['args'], $this->args );
		}

		$this->set_default_labels();
		$this->set_default_rewrite();
		$this->set_default_args();
	}

	/**
	 * Register the CPT on the init action.
	 *
	 * @since 0.1.0
	 */
	public function register() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Function that register the CPT first tests if there is no such CPT on
	 * the site.
	 *
	 * @since 0.1.0
	 */
	public function init() {
		if ( ! post_type_exists( $this->post_type ) ) {
			register_post_type( $this->post_type, $this->args );
		}
		if ( '' !== $this->title_placeholder ) {
			add_filter( 'enter_title_here', array( $this, 'update_placeholder' ) );
		}
	}

	/**
	 * Creates the default group of arguments, all of the arguments can be
	 * overwritten by calling set_args, function with an instance of this object,
	 * the value is stored in the $args variable.
	 *
	 * @since 0.1.0
	 */
	private function set_default_args() {
		$this->set_args(array(
			// The array of labels to use in the UI for this post type.
			'labels' => $this->labels,
			// Array of supported fields ( title, editor, thumbnail, etc ).
			'supports' => $this->supports,
			// We use the query var 'store' as opposed to the post type 'acf-store'.
			'query_var' => strtolower( $this->singular ),
			// Triggers the handling of re-writes for this post-type.
			'rewrite' => $this->rewrite,
		));
	}

	/**
	 * Allows to overwrite any of the default arguments for this CPT, just
	 * send an associate array with the value you want to update.
	 *
	 * @since 0.1.0
	 *
	 * @param array $args The arguments to replace.
	 */
	public function set_args( $args ) {
		$this->merge( $this->args, $args );
	}

	/**
	 * Allows to overwrite any of the default labels for this CPT, just
	 * send an associate array with the value you want to update.
	 *
	 * @since 0.1.0
	 *
	 * @param array $labels The group of labels to update.
	 */
	public function set_labels( $labels ) {
		$this->merge( $this->labels, $labels );
		$this->update_arg( 'labels', $this->labels );
	}

	/**
	 * Overwrites the default variable by mergin the default values with
	 * the new ones if the new values are empty the default values keep as they
	 * are.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $default Reference to the original values.
	 * @param array $new_values The array with the new values to be updated on
	 *							the default variable.
	 */
	public function merge( &$default, $new_values ) {
		if ( is_array( $new_values ) && ! empty( $new_values ) ) {
			$default = array_merge( $default, $new_values );
		}
	}

	/**
	 * Creates the default labels to be used with this CPT.
	 *
	 * @since 0.10
	 */
	private function set_default_labels() {
		$this->labels = array(
			'name' => $this->interpolate( '%s', $this->plural ),
			'singular_name' => $this->interpolate( '%s', $this->singular ),
			'add_new' => $this->interpolate( 'Add New' ),
			'all_items' => $this->interpolate( 'All %s', $this-> plural ),
			'new_item' => $this->interpolate( 'New %s', $this->singular ),
			'add_new_item' => $this->interpolate( 'Add New %s', $this->singular ),
			'view_item' => $this->interpolate( 'View %S', $this->singular ),
			'menu_name' => $this->interpolate( '%s', $this->plural ),
			'search_items' => $this->interpolate( 'Search %s', $this->plural ),
			'not_found' => $this->interpolate( 'No %s found.', $this->plural ),
			'not_found_in_trash' => $this->interpolate( 'No %s found in trash.', $this->plural ),
		);
	}

	/**
	 * Uses the sprintf function to create an interpolation of the message and
	 * arguments.
	 *
	 * @since 0.1.0
	 *
	 * @param string $msg The message to be displayed.
	 * @param string $arg The argument to replace insode of the $message.
	 * @return string The message with the interpolation.
	 */
	private function interpolate( $msg = '', $arg = '' ) {
		return $this->label( sprintf( $msg, $arg ) );
	}

	/**
	 * Creates an escaped label
	 *
	 * @since 0.1.0
	 *
	 * @param string $str The string to be used in the label.
	 * @return string The escpaed and translated label.
	 */
	private function label( $str = '' ) {
		if ( is_string( $str ) && ! empty( $str ) ) {
			return esc_html__( $str , 'Lean' );
		} else {
			return '';
		}
	}

	/**
	 * Set default options for the rewrite of the CPT.
	 *
	 * @since 0.1.0
	 */
	private function set_default_rewrite() {
		$this->rewrite = array(
			// Customize the permalink structure slug. Should be translatable.
			'slug' => $this->interpolate( '%s', $this->slug ),

			/*
			 * Do not prepend the front base to the permalink strucure.
			 *
			 * For example, if your permalink structure is /blog/, then your links will be:
			 * false->/news/, true->/blog/news/
			 */
			'with_front' => false,
		);
	}

	/**
	 * Updates the default options in the $rewrite variable.
	 *
	 * @since 0.1.0
	 *
	 * @param array $rules The associate array with the new rules.
	 */
	public function set_rewrite( $rules ) {
		$this->merge( $this->rewrite, $rules );
		$this->update_arg( 'rewrite', $this->rewrite );
	}

	/**
	 * Allow to update the default supports values for the CPT.
	 *
	 * @since 0.1.0
	 *
	 * @param array $support The new array with the supported featurs.
	 */
	public function set_supports( $support ) {
		if ( is_array( $support ) ) {
			$this->supports = $support;
			$this->update_arg( 'supports', $this->supports );
		}
	}

	/**
	 * Update the args with the latest changes.
	 *
	 * @since 0.1.0
	 *
	 * @param string $name The name of the key to be updated in the $args.
	 * @param mixed  $value The value to be stored inside of $args[ $name ].
	 */
	private function update_arg( $name = '', $value = '' ) {
		if ( ! empty( $name ) ) {
			$this->set_args( array(
				$name => $value,
			));
		}
	}

	/**
	 * Function that is being colled by the filter: 'enter_title_here' to update
	 * the default 'enter title here' in the admin.
	 *
	 * @since 0.1.0
	 *
	 * @param string $title The current title on the input.
	 * @return string $title The new title
	 */
	public function update_placeholder( $title ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$current_cpt = is_object( $screen ) && property_exists( $screen, 'post_type' )
			? $screen->post_type
			: '';

		if ( $this->post_type === $current_cpt ) {
			$title = $this->label( $this->title_placeholder );
		}
		return $title;
	}
}
