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
if (!defined('ABSPATH')) {
	exit(0);
}

/**
 * Tos field.
 */
class Field_Type_Checkbox_Piechart extends \BP_XProfile_Field_Type_Checkbox
{

	public function __construct()
	{

		parent::__construct();

		$this->category = _x('Multi Fields', 'xprofile field type category', 'buddypress');
		$this->name     = _x('Checkboxes with Pie Chart', 'xprofile field type', 'buddypress');

		$this->supports_multiple_defaults = true;
		$this->accepts_null_value         = true;
		$this->supports_options           = true;

		$this->set_format('/^.+$/', 'replace');

		/**
		 * Fires inside __construct() method for BP_XProfile_Field_Type_Checkbox class.
		 *
		 * @since 2.0.0
		 *
		 * @param BP_XProfile_Field_Type_Checkbox_Piechart $this Current instance of
		 *                                              the field type checkbox.
		 */
		do_action('bp_xprofile_field_type_checkbox_piechart', $this);
	}

	/**
	 * Output the edit field options HTML for this field type.
	 *
	 * BuddyPress considers a field's "options" to be, for example, the items in a selectbox.
	 * These are stored separately in the database, and their templating is handled separately.
	 *
	 * This templating is separate from {@link BP_XProfile_Field_Type::edit_field_html()} because
	 * it's also used in the wp-admin screens when creating new fields, and for backwards compatibility.
	 *
	 * Must be used inside the {@link bp_profile_fields()} template loop.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Optional. The arguments passed to {@link bp_the_profile_field_options()}.
	 */
	public function edit_field_options_html(array $args = array())
	{
		// do everything that a normal checkbox would do
		parent::edit_field_options_html($args);

		// now do custom things to get selected values and output them in the pie chart
		printf('<p>Hello world!</p>');

		printf('
		<div id="piechart-controls">
			<canvas id="piechart" width="400" height="400">Your browser is too old!</canvas>
			<br>
			<table id="proportions-table"></table>
			<br>
			<p id="piechart-instructions">
				Drag the circles or click the buttons to adjust the pie chart. If a segment has gone,
				you can get it back by clicking its plus button.
			</p>

		</div>
		');

		printf('

		<script>
				
		(function(){

			//IE9+ http://youmightnotneedjquery.com/
			function ready(fn) {
				if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading"){
					fn();
				} else {
					document.addEventListener("DOMContentLoaded", fn);
				}
			}

			ready(setupPieChart);


			function setupPieChart() {


				var dimensions = knuthfisheryates2([\'walking\', \'programming\', \'chess\', \'eating\', \'sleeping\']);
				var randomProportions = generateRandomProportions(dimensions.length, 0.05);
				var proportions = dimensions.map(function(d,i) { return {
					label: d,
					proportion: randomProportions[i],
					collapsed: false,
					format: {
						label: d.charAt(0).toUpperCase() + d.slice(1) // capitalise first letter
					}
				}});


				var setup = {
					canvas: document.getElementById(\'piechart\'),
					radius: 0.9,
					collapsing: true,
					proportions: proportions,
					drawSegment: drawSegmentOutlineOnly,
					onchange: onPieChartChange
				};

				var newPie = new DraggablePiechart(setup);

				function drawSegmentOutlineOnly(context, piechart, centerX, centerY, radius, startingAngle, arcSize, format, collapsed) {

					if (collapsed) { return; }

					// Draw segment
					context.save();
					var endingAngle = startingAngle + arcSize;
					context.beginPath();
					context.moveTo(centerX, centerY);
					context.arc(centerX, centerY, radius, startingAngle, endingAngle, false);
					context.closePath();

					context.fillStyle = \'#f5f5f5\';
					context.fill();
					context.stroke();
					context.restore();

					// Draw label on top
					context.save();
					context.translate(centerX, centerY);
					context.rotate(startingAngle);

					var fontSize = Math.floor(context.canvas.height / 25);
					var dx = radius - fontSize;
					var dy = centerY / 10;

					context.textAlign = "right";
					context.font = fontSize + "pt Helvetica";
					context.fillText(format.label, dx, dy);
					context.restore();
				}

				function onPieChartChange(piechart) {

					var table = document.getElementById(\'proportions-table\');
					var percentages = piechart.getAllSliceSizePercentages();

					var labelsRow = \'<tr>\';
					var propsRow = \'<tr>\';
					for(var i = 0; i < proportions.length; i += 1) {
						labelsRow += \'<th>\' + proportions[i].format.label + \'</th>\';

						var v = \'<var>\' + percentages[i].toFixed(0) + \'</var>\';
						var plus = \'<div id="plu-\' + dimensions[i] + \'" class="adjust-button" data-i="\' + i + \'" data-d="-1">&#43;</div>\';
						var minus = \'<div id="min-\' + dimensions[i] + \'" class="adjust-button" data-i="\' + i + \'" data-d="1">&#8722;</div>\';
						propsRow += \'<td>\' + v + plus + minus + \'</td>\';
					}
					labelsRow += \'</tr>\';
					propsRow += \'</tr>\';

					table.innerHTML = labelsRow + propsRow;

					var adjust = document.getElementsByClassName("adjust-button");

					function adjustClick(e) {
						var i = this.getAttribute(\'data-i\');
						var d = this.getAttribute(\'data-d\');

						piechart.moveAngle(i, (d * 0.1));
					}

					for (i = 0; i < adjust.length; i++) {
						adjust[i].addEventListener(\'click\', adjustClick);
					}

				}

				/*
				* Generates n proportions with a minimum percentage gap between them
				*/
				function generateRandomProportions(n, min) {

					// n random numbers 0 - 1
					var rnd = Array.apply(null, {length: n}).map(function(){ return Math.random(); });

					// sum of numbers
					var rndTotal = rnd.reduce(function(a, v) { return a + v; }, 0);

					// get proportions, then make sure each propoertion is above min
					return validateAndCorrectProportions(rnd.map(function(v) { return v / rndTotal; }), min);


					function validateAndCorrectProportions(proportions, min) {
						var sortedProportions = proportions.sort(function(a,b){return a - b});

						for (var i = 0; i < sortedProportions.length; i += 1) {
							if (sortedProportions[i] < min) {
								var diff = min - sortedProportions[i];
								sortedProportions[i] += diff;
								sortedProportions[sortedProportions.length - 1] -= diff;
								return validateAndCorrectProportions(sortedProportions, min);
							}
						}

						return sortedProportions;
					}
				}

				/*
				* Array sorting algorithm
				*/
				function knuthfisheryates2(arr) {
					var temp, j, i = arr.length;
					while (--i) {
						j = ~~(Math.random() * (i + 1));
						temp = arr[i];
						arr[i] = arr[j];
						arr[j] = temp;
					}

					return arr;
				}
			}

		})();

		</script>
		');
	}
}
