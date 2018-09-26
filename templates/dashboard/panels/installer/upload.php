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
 * @param	object			$upload_form			The upload form
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<style>
#package-uploader .mwp-bootstrap-form {
	border: 0;
	margin: 0;
	box-shadow: none;
	padding: 0;
}
</style>

<div id="package-uploader" class="panel panel-info package-uploader">
  <div class="panel-heading">
	<h3 class="panel-title">Upload A File</h3>
  </div>
  <div class="panel-body">
	<?php echo $upload_form->render() ?>
  </div>
</div>

