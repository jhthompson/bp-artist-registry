<?php
/**
 * Leaflet JavaScript map
 *
 * @package    BuddyPress Xprofile Custom Field Types
 * @subpackage Field_Types
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @since      1.0.0
 */

namespace BPXProfileCFTR\Field_Types;

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Interactive JavaScript map Type
 */
class Field_Type_Leaflet_Map extends \BP_XProfile_Field_Type {
	public function __construct() {
		parent::__construct();

		$this->name     = __( 'Leaflet Map', 'bp-xprofile-custom-field-types' );
		$this->category = _x( 'Custom Fields', 'xprofile field type category', 'bp-xprofile-custom-field-types' );

		$this->accepts_null_value = true;
		$this->supports_options   = false;

		do_action( 'bp_xprofile_field_type_leaflet_map', $this );
	}

	/**
	 * Edit field html.
	 *
	 * @param array $raw_properties properties.
	 */
	public function edit_field_html( array $raw_properties = array() ) {
        global $field;

		if ( isset( $raw_properties['user_id'] ) ) {
			unset( $raw_properties['user_id'] );
		}

		$args = array(
			'type'  => 'range',
			'class' => 'bpxcftr-slider',
			'value' => bp_get_the_profile_field_edit_value(),
			'min'   => self::get_min_val( $field->id ),
			'max'   => self::get_max_val( $field->id ),
		);
		?>

        <legend id="<?php bp_the_profile_field_input_name(); ?>-1">
			<?php bp_the_profile_field_name(); ?>
			<?php bp_the_profile_field_required_label(); ?>
        </legend>

		<div id="mapid" style="height: 400px;"></div>

		<script>
		var mymap = L.map('mapid').setView([46.5107, -63.4168], 8);

		L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    		maxZoom: 16,
			id: 'mapbox.streets'		
		}).addTo(mymap);

		var marker = L.marker([46.332516, -63.391670],{
    		draggable: true,
    		autoPan: true
		}).addTo(mymap);

		</script>

        <?php
		// Errors.
		do_action( bp_get_the_profile_field_errors_action() );
		// Input.
		?>

        <?php  if ( bp_get_the_profile_field_description() ) : ?>
            <p class="description" id="<?php bp_the_profile_field_input_name(); ?>-3"><?php bp_the_profile_field_description(); ?></p>
		<?php endif;?>

		<?php
	}

	public function admin_field_html( array $raw_properties = array() ) {
		global $field;

		$args = array(
			'type'  => 'range',
			'class' => 'bpxcftr-slider',
			'min'   => self::get_min_val( $field->id ),
			'max'   => self::get_max_val( $field->id ),
		);
		?>

        <input <?php echo $this->get_edit_field_html_elements( array_merge( $args, $raw_properties ) ); ?> />

        <?php
	}


	public function admin_new_field_html( \BP_XProfile_Field $current_field, $control_type = '' ) {

	    $type = array_search( get_class( $this ), bp_xprofile_get_field_types() );

		if ( false === $type ) {
			return;
		}

		$class            = $current_field->type != $type ? 'display: none;' : '';

		$min     = self::get_min_val( $current_field->id );
		$max     = self::get_max_val( $current_field->id );

		?>
        <div id="<?php echo esc_attr( $type ); ?>" class="postbox bp-options-box" style="<?php echo esc_attr( $class ); ?> margin-top: 15px;">

        <h3><?php esc_html_e( 'Write min and max values.', 'bp-xprofile-custom-field-types' ); ?></h3>
            <div class="inside">
                <p>
                    <label for="bpxcftr_slider_min">
						<?php esc_html_e( 'Minimum:', 'bp-xprofile-custom-field-types' ); ?>
                    </label>
                    <input type="text" name="bpxcftr_slider_min" id="bpxcftr_slider_min" value="<?php echo esc_attr( $min ); ?>"/>
                    <label for="bpxcftr_slider_max">
						<?php esc_html_e( 'Maximum:', 'bp-xprofile-custom-field-types' ); ?>
                    </label>
                    <input type="text" name="bpxcftr_slider_max" id="bpxcftr_slider_max" value="<?php echo esc_attr( $max ); ?>"/>
                </p>
            </div>
        </div>

		<?php
	}

	/**
	 * Check the validity of the value
	 *
	 * @param string $values value.
	 *
	 * @return bool
	 */
	public function is_valid( $values ) {

		if ( empty( $values ) ) {
			return true;
		}

		$field = bpxcftr_get_current_field();
		// we don't know the field, have no idea how to validate.
		if ( empty( $field ) ) {
			return is_numeric( $values );
		}

		$min = self::get_min_val( $field->id );
		$max = self::get_max_val( $field->id );

		// unset the current field from our saved field id.
		bpxcftr_set_current_field(null );

		return ( $values >= $min ) && ( $values <= $max );
	}


	/**
	 * Modify the appearance of value. Apply autolink if enabled.
	 *
	 * @param  string $field_value Original value of field.
	 * @param  int $field_id Id of field.
	 *
	 * @return string   Value formatted
	 */
	public static function display_filter( $field_value, $field_id = 0 ) {

		return $field_value;

	}

	/**
	 * Get the minimum allowed value for the field id.
	 *
	 * @param int $field_id field id.
	 *
	 * @return float
	 */
	public static function get_min_val( $field_id ) {
		return bp_xprofile_get_meta( $field_id, 'field', 'min_val', true );
	}

	/**
	 * Get the max allowed value for the field id.
	 *
	 * @param int $field_id field id.
	 *
	 * @return float
	 */
	public static function get_max_val( $field_id ) {
		return bp_xprofile_get_meta( $field_id, 'field', 'max_val', true );
	}
}
