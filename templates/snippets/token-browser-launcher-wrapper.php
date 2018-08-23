<?php
/**
 * Plugin HTML Template
 *
 * Created:  June 22, 2018
 *
 * @package  Automation Rules
 * @author   Code Farma
 * @since    1.1.0
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'snippets/token-browser-launcher', ... ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	array		$html		The browser launcher html
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>
<div class="token-launcher-wrapper"><?php echo $html ?></div>