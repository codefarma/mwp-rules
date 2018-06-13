<?php
/**
 * Plugin HTML Template
 *
 * Created:  June 13, 2018
 *
 * @package  Automation Rules
 * @author   Code Farma
 * @since    {build_version}
 *
 * Here is an example of how to get the contents of this template while 
 * providing the values of the $title and $content variables:
 * ```
 * $content = $plugin->getTemplateContent( 'snippets/site-list', array( 'title' => 'Some Custom Title', 'content' => 'Some custom content' ) ); 
 * ```
 * 
 * @param	Plugin		$this		The plugin instance which is loading this template
 *
 * @param	string		$sites		The applied sites
 * @param	string		$content	The provided content
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}
?>

<div class="mwp-bootstrap" style="margin-top: 4px" data-view-model="mwp-rules">
<?php if ( $sites ) {
	$site_count = count( $sites );
	$sites_list = array_column( $sites, 'blogname' );
	?> 
	<div class="label label-info" 
		style="cursor: pointer;"
		data-bind="jquery: { popover: { title: 'Applied Network Sites', html: true, content: '<?php echo esc_attr( '<ul><li>' . implode( '</li><li>', $sites_list ) . '</li></ul>' ) ?>' } }"> 
		<?php echo count( $sites ) ?> <?php echo $site_count == 1 ? 'site' : 'sites' ?> 
	</div>
<?php } else { ?>
	<span class="label label-info"><?php echo __( 'All sites', 'mwp-rules' ) ?></span>
<?php } ?>
</div>