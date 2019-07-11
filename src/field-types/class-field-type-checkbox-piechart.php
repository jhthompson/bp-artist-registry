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
 * Checkbox and piechart field.
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

		printf("
		<script>

		jQuery(function($) {
			$(document).ready(function() {
				//set initial state.
				//$('#field_7_3').val(this.checked);
			
				$('#field_7_3').change(function() {
					var label = $(\"label[for='\" + $(this).attr('id') + \"'] span\");

					if($(this).is(':checked')) {
						//var returnVal = confirm('Are you sure?');
						//var label = $(\"label[for='\" + $(this).attr('id') + \"'] span\");
						//var oldtext = $(label).text();

						$(label).text(' hello world');
						console.log(label);

						$('#field_7_3').attr('new value');
						//$(this).prop('checked', returnVal);
					}
					else {
						$(label).text(' ');
					}
					$('#field_7_3').val(this.checked);        
				});
			});
		});

		</script>
		");

		$options       = $this->field_obj->get_children();
		foreach ($options as $value) {
			//print_r($value);
		}

	
		$checkbox_ids = array_fill(0, count($options), 'blank');

		for ($i = 0; $i < count($options); ++$i) {
			$checkbox_ids[$i] = sprintf('field_%s_%s', $options[$i]->id, $options[$i]->option_order - 1);
			//echo $checkbox_ids[$i];
		}

		$checkbox_string = implode(", ", $checkbox_ids);
		//echo($checkbox_string);

		echo sprintf('

		<script>
				
		var newPie; // testing as global 

		// Start by setting up all checkbox listeners and getting which ones are checked
		var all_checkbox_ids = [%s]; // global for access in the on click method for table

		
		window.onresize = function(event) {
			if(newPie) {
				console.log("trying to redraw?");
				var canvas = document.getElementById("piechart");
				var parent = document.getElementsByClassName("field_type_piechart")[0];
				canvas.width = parent.offsetWidth * 0.75;
				canvas.height = canvas.width;
				newPie.draw();
			}
		};

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

			function checkboxListener() {
				alert("checked");
			}

			function setupPieChart() {

				var canvas = document.getElementById("piechart");
				var parent = document.getElementsByClassName("field_type_piechart")[0];
				canvas.width = parent.offsetWidth * 0.75;
				canvas.height = canvas.width;

				var checkbox_ids_checked = [];

				var i;
				for (i = 0; i < all_checkbox_ids.length; i++) {

					//console.log(all_checkbox_ids[i]);
					all_checkbox_ids[i].onclick = setupPieChart; // this allows checked and unchecked boxes to be added and removed from pie chart dynamically
															     // would be better if it was its own different function, not the original setup
					if (all_checkbox_ids[i].checked) {
						//console.log("adding " + all_checkbox_ids[i][\'value\'] + " to the checked ");
					 	checkbox_ids_checked.push(all_checkbox_ids[i][\'value\']);
					}
				}

				//var checkbox_ids_strings = all_checkbox_ids.map(function(element) {
				//	return element[\'value\'];
				//});

				//var defaults = [\'walking\', \'programming\', \'chess\', \'eating\', \'sleeping\'];
				//console.log(checkbox_ids_checked);
				//console.log(defaults);

				var dimensions = checkbox_ids_checked; //knuthfisheryates2(checkbox_ids_checked);
				var equalProportions = generateEqualProportions(dimensions.length);
				var proportions = dimensions.map(function(d,i) { return {
					label: d,
					proportion: equalProportions[i],
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

				if (newPie) {
					newPie.setData(setup);
					//console.log("pie chart already created");
				} else {
					newPie = new DraggablePiechart(setup); 
				}

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

					var canvasInner = document.getElementById("piechart");
					var parentInner = document.getElementsByClassName("field_type_piechart")[0];

					var fontSize = Math.floor(parentInner.offsetWidth / 35 );
					//console.log(fontSize);
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
					for(var i = 0; i < piechart.data.length; i += 1) {
						labelsRow += \'<th>\' + piechart.data[i].format.label + \'</th>\';

						var v = \'<var>\' + percentages[i].toFixed(0) + \'</var>\';
						var plus = \'<div id="plu-\' + newPie.data[i].format.label + \'" class="adjust-button" data-i="\' + i + \'" data-d="-1">&#43;</div>\';
						var minus = \'<div id="min-\' + newPie.data[i].format.label + \'" class="adjust-button" data-i="\' + i + \'" data-d="1">&#8722;</div>\';
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

						var name = this.getAttribute(\'id\').substr(4);

						var i;
						for(i = 0; i < all_checkbox_ids.length; i++) {
							if (all_checkbox_ids[i][\'value\'] == name) {
								//console.log("We got a match " + name);
							}
						}
						
						//console.log(name);
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
				* Generates n equal proportions
				*/
				function generateEqualProportions(n) {
					var proportions = [];
					var i;

					for (i = 0; i < n; i++) {
						proportions.push(1.0 / n);	
					}

					return proportions;
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
		', $checkbox_string, $options[0]->name);
	}
}
