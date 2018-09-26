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
 * @param	object		$upload_form			The automation upload form
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="row">
	<div class="col-md-5">
		<?php echo $this->getTemplateContent( 'dashboard/panels/installer/upload', [ 'upload_form' => $upload_form ] ) ?>
	</div>
	<div class="col-md-7">
		<?php echo $this->getTemplateContent( 'dashboard/panels/installer/packages' ) ?>
	</div>
</div>

