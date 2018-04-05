<?php
/**
 * Plugin HTML Template
 *
 * Created:  April 4, 2018
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	MWP\Rules\Rule		$rule		The rule to display an overview for
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

?>

<h1 class="overview-title">
	<?php if ( isset( $icon ) ) { echo $icon; } ?>
	<?php if ( isset( $label ) ) { echo "<span class=\"text-info\" style=\"opacity:0.8\">" . $label . ":</span> "; } ?>
	<?php echo esc_html( $title ) ?>
</h1>
<hr>

