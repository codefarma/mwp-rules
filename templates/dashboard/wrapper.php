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

<?php echo Rules\Plugin::instance()->getTemplateContent( 'snippets/styles/texture_bg' ) ?>
<?php echo Rules\Plugin::instance()->getTemplateContent( 'snippets/screen_header', [ 'title' => $title ] ) ?>

<div class="mwp-bootstrap rules-dashboard wrap rules">
	<h2 style="display:none;"></h2>		
	<?php echo $content ?>
</div>