<?php
/**
 * Plugin HTML Template
 *
 * Created:  December 27, 2017
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    0.0.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

$bundle = isset( $operation ) && $operation instanceof MWP\Rules\GenericOperation ? $operation->getBundle() : null;
$bundle_id = $bundle ? $bundle->id() : null;

?>

<p class="alert alert-warning"><strong>Warning:</strong> PHP is for advanced users only. Do not include a &lt;?php tag at the beginning of your code, or comment it out if you do.</p>

<?php if ( isset( $return_args ) and ! empty( $return_args ) ) : ?>
	<p class="text-success">
		Your php code needs to return one of the following argument types:
	</p>
	<ul>
		<?php foreach ( $return_args as $arg_description ) : ?>
		<li><?php echo $arg_description ?></li>
		<?php endforeach ?>
	</ul>
<?php endif ?>

<?php if ( ( isset( $variables ) and ! empty( $variables ) ) or ( isset( $event ) and ! empty( $event ) ) or ( isset( $operation ) and ! empty( $operation ) ) ) : ?>
	<hr>
	<p class="text-success">
		The following variables are available to your php code:
	</p>
	<?php if ( isset( $variables ) and ! empty( $variables ) ) { ?>
	<ul>
		<?php foreach ( $variables as $var_description ) { ?>
		<li><?php echo $var_description ?></li>
		<?php } ?>
	</ul>
	<?php } ?>
	<?php if ( isset( $event ) and ! empty( $event ) ) : ?>
		<?php echo $event->getDisplayArgInfo() ?>
	<?php endif ?>
	<?php if ( isset( $operation ) and ! empty( $operation ) ) : ?>
		<hr>
		<ul>
			<li><code>$operation</code> (object) - This <?php echo $operation instanceof MWP\Rules\Condition ? 'condition' : ( $operation instanceof MWP\Rules\Action ? 'action' : 'operation' ) ?></li>
			<li><code>$token_value</code> (function) - Retrieve token values through code
				<div style="margin-top: 5px">
					<blockquote style="font-size: 1.1em;">
						<pre style="display: inline-block; padding: 0px 3px; vertical-align: -16px;">$user_id = $token_value('global:current_user:id')</pre><br>
						<span class="mwp-bootstrap"><i class="glyphicon glyphicon-arrow-right"></i></span> <a href="javascript:;" data-bind="
						click: function() { 
							mwp.controller.get('mwp-rules').openTokenBrowser({
								title: 'Browsing All Data Tokens',
								cancel_label: 'Close',
								callback: function( node, tokens, tree, dialog ) {
									dialog.find('[role=token-path]').select();
									return false;
								}
							},
							{ 
								<?php if ( isset( $event ) and ! empty( $event ) ) : ?>
								event_type: '<?php echo $event->type ?>', 
								event_hook: '<?php echo esc_attr( $event->hook ) ?>',
								<?php endif ?>
								bundle_id: <?php echo $bundle_id ?: 'undefined' ?>
							}); 
						}">
							Browse all data tokens
						</a>
					</blockquote>
				</div>
			</li>
		</ul>
	<?php endif ?>
<?php endif ?>

