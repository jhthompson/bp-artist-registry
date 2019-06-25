<?php
/**
 * Checkbox and piechart for graphical selection of amounts
 *
 * @package    BuddyPress Xprofile Custom Field Types
 * @subpackage Field_Types
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace BPXProfileCFTR\Field_Types;

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Tos field.
 */
class Field_Type_Checkbox_Piechart extends \BP_XProfile_Field_Type_Checkbox {

	public function __construct() {

		parent::__construct();

		$this->name     = _x( 'Checkbox and Pie Chart', 'xprofile field type', 'bp-xprofile-custom-field-types' );
		$this->category = _x( 'Custom Fields', 'xprofile field type category', 'bp-xprofile-custom-field-types' );

		$this->supports_multiple_defaults = false;
		$this->accepts_null_value         = false;
		$this->supports_options           = false;

		$this->set_format( '/^.+$/', 'replace' );
		do_action( 'bp_xprofile_field_type_checkbox_piechart', $this );
	}
}

