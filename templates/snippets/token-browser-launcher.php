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
 * @param	array		$config				The browser configuration options
 * @param	array		$params				The token context parameters
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$callback = isset( $config['callback'] ) ? $config['callback'] : NULL;
unset( $config['callback'] );

?>

<div class="mwp-bootstrap" data-view-model="mwp-rules">
	<i class="glyphicon glyphicon-arrow-left"></i>
	<a title="<?php echo isset( $config['title'] ) ? $config['title'] : '' ?>" href="javascript:;" data-bind="
		tokenSelector: {
			<?php if ( $callback ) : ?>callback: <?php echo $callback ?>,<?php endif ?>
			options: <?php echo $config ? esc_attr( json_encode( $config ) ) : '{}' ?>,
			params: <?php echo $params ? esc_attr( json_encode( $params ) ) : '{}' ?>
		}">
		<i class="glyphicon glyphicon-th"></i>
	</a>
</div>