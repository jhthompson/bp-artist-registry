<?php
/**
 * Asset Loader for BuddyPress Xprofile Custom Field Types.
 *
 * @package    BuddyPress Xprofile Custom Field Types
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace BPXProfileCFTR\Bootstrap;

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Assets Loader.
 */
class Assets_Loader {

	/**
	 * Data to be send as localized js.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Boot itself
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Setup
	 */
	public function setup() {
		add_action( 'bp_enqueue_scripts', array( $this, 'register' ) );
		add_action( 'bp_admin_enqueue_scripts', array( $this, 'register' ) );

		add_action( 'bp_enqueue_scripts', array( $this, 'load_assets' ) );
		add_action( 'bp_admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );

	}

	/**
	 * Load plugin assets
	 */
	public function load_assets() {
		// css should be always loaded.
		wp_enqueue_style( 'bp-xprofile-custom-field-types' );

		$load = bp_is_user_profile_edit() || bp_is_register_page();
		$load = apply_filters( 'bpxcftr_load_front_assets', $load );

		if ( ! $load ) {
			return;
		}
		$this->enqueue_vendors();
		$this->enqueue_front();
	}

	/**
	 * Load plugin assets
	 */
	public function load_admin_assets() {

		$load = isset( $_GET['page'] ) && 'bp-profile-edit' === $_GET['page'];
		$load = apply_filters( 'bpxcftr_load_admin_assets', $load );

		if ( ! $load ) {
			return;
		}

		$this->enqueue_vendors();
		$this->enqueue_admin();
	}

	/**
	 * Register assets.
	 */
	public function register() {
		$this->register_vendors();
		$this->register_core();
		$this->register_admin();
	}

	/**
	 * Enqueue front end assets.
	 */
	public function enqueue_front() {
		wp_enqueue_script( 'bp-xprofile-custom-field-types' );

		wp_localize_script( 'bp-xprofile-custom-field-types', 'BPXprofileCFTR', $this->data );
	}

	/**
	 * Load vendor assets.
	 */
	public function enqueue_vendors() {
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'modernizr' );
		wp_enqueue_script( 'jscolor' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_script( 'select2-i18n' );
		wp_enqueue_script( 'draggable-piechart' );
		wp_enqueue_style( 'leaflet' );
		wp_enqueue_script( 'leaflet-js' );
		wp_enqueue_style( 'rangeslider' );
		wp_enqueue_script( 'rangeslider-js' );
		wp_enqueue_style( 'rangeslider-custom-income' );
	}

	/**
	 * Enqueue admin assets.
	 */
	public function enqueue_admin() {
		wp_enqueue_script( 'bp-xprofile-custom-field-types-admin' );
		wp_enqueue_script( 'bp-xprofile-custom-field-types' );

		wp_localize_script( 'bp-xprofile-custom-field-types', 'BPXprofileCFTR', $this->data );

		wp_localize_script( 'bp-xprofile-custom-field-types-admin', 'BPXprofileCFTRAdmin', $this->data );
	}

	/**
	 * Register vendor scripts.
	 */
	private function register_vendors() {

		$url  = bp_xprofile_cftr()->url;
		$path = bp_xprofile_cftr()->path;

		$version = bp_xprofile_cftr()->version;

		wp_register_script( 'modernizr', $url . 'assets/vendors/modernizr.js', array(), $version, false );
		wp_register_script( 'jscolor', $url . 'assets/vendors/jscolor/jscolor.js', array(), '1.4.1', true );

		wp_register_script( 'select2', $url . 'assets/vendors/select2/select2.min.js', array( 'jquery' ), '4.0.2', true );

		$locale = apply_filters( 'bpxcftr_select2_js_locale', get_locale() );
		// Select 2, locale.
		$locale_js = is_readable( $path . "assets/vendors/select2/i18n/{$locale}.js" ) ? "{$locale}.js" : 'en.js';

		wp_register_script( 'select2-i18n', $url . "assets/vendors/select2/i18n/{$locale_js}", array( 'select2' ), '4.0.2', true );

		wp_register_style( 'select2', $url . 'assets/vendors/select2/select2.min.css', array(), '4.0.2' );

		wp_register_script( 'draggable-piechart', $url . 'assets/vendors/draggable-piechart/draggable-piechart.js', array(), '1.0.2', false );

		wp_register_style( 'leaflet', $url . 'assets/vendors/leaflet/leaflet.css', array(), '1.5.1' );

		wp_register_script( 'leaflet-js', $url . "assets/vendors/leaflet/leaflet.js", array(), '1.5.1', false );

		wp_register_style( 'rangeslider', $url . 'assets/vendors/rangeslider/rangeslider.css', array(), '2.3.0' );

		wp_register_script( 'rangeslider-js', $url . "assets/vendors/rangeslider/rangeslider.js", array( 'jquery' ), '2.3.0', false );

		wp_register_style( 'rangeslider-custom-income', $url . 'assets/vendors/rangeslider/rangeslider-custom-income.css', array(), '2.3.0' );

	}

	/**
	 * Register core assets.
	 */
	private function register_core() {
		$url = bp_xprofile_cftr()->url;

		$version = bp_xprofile_cftr()->version;

		wp_register_style( 'bp-xprofile-custom-field-types', $url . 'assets/css/bp-xprofile-custom-field-types.css' );

		wp_register_script( 'bp-xprofile-custom-field-types', $url . 'assets/js/bp-xprofile-custom-field-types.js', array( 'jquery' ), $version, true );

		$this->data = array();
	}

	/**
	 * Register core assets.
	 */
	private function register_admin() {
		$url = bp_xprofile_cftr()->url;

		$version = bp_xprofile_cftr()->version;

		wp_register_script( 'bp-xprofile-custom-field-types-admin', $url . 'assets/js/bp-xprofile-custom-field-types-admin.js', array( 'jquery' ), $version, true );

		$this->data = array(
			'selectableTypes' => bpxcftr_get_selectable_field_types(),
		);
	}
}
