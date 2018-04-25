<?php
/**
 * Plugin HTML Template
 *
 * Created:  April 12, 2018
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    1.0.0
 *
 * @param	string		$controller			The dashboard controller
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;


?>

<div class="mwp-bootstrap rules-dashboard wrap rules">
	<?php if ( ! isset( $_REQUEST['do'] ) ) : 
		wp_enqueue_style( 'indie_flower_font', 'https://fonts.googleapis.com/css?family=Abel|Do+Hyeon|Indie+Flower' );	
	?>
		<h2 style="font-family: 'Abel'; font-size:40px; line-height: 50px; text-align: center;">
			Automation <span style="background-image:url('<?php echo Rules\Plugin::instance()->fileUrl( 'assets/img/rules.png' ) ?>'); color: transparent; background-position: -3px 2px;">Rules</span> for WordPress
		</h2>
	<?php else : ?>
		<h2><?php echo $title ?></h2>	
	<?php endif; ?>
	<hr>
	<?php echo $content ?>
</div>