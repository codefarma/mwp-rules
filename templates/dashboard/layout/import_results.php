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
 * @param	array		$results			The import results
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Rules;

?>

<div class="row">
	<div class="col-xs-12 text-right">
		<a href="<?php echo $controller->getUrl() ?>" class="btn btn-primary"><i class="glyphicon glyphicon-dashboard"></i> Return To Dashboard</a>
	</div>
</div>

<hr>

<pre>
	<?php print_r( $results ); ?>
</pre>
