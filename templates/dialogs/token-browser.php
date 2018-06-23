<?php
/**
 * Plugin HTML Template
 *
 * Created:  June 21, 2018
 *
 * @package  Automation Rules
 * @author   Code Farma
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'dialogs/token-browser' ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div class="mwp-bootstrap modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">

      </div>
      <div class="modal-footer">
		<div class="col-xs-8">
			<input type="text" style="width:100%; border:0; box-shadow:none;" role="token-path" />
		</div>
		<div class="col-xs-4">
			<button type="button" class="btn btn-default" data-dismiss="modal" role="cancel"></button>
			<button type="button" class="btn btn-primary" disabled="disabled" role="done"></button>
		</div>
      </div>
    </div>
  </div>
</div>