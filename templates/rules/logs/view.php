<?php
/**
 * Plugin HTML Template
 *
 * Created:  January 8, 2018
 *
 * @package  MWP Rules
 * @author   Kevin Carwile
 * @since    {build_version}
 *
 * @param	MWP\Rules\Log										$log			The log record
 * @param	Modern\Wordpress\Helpers\ActiveRecordTable			$conditions		The table of associated conditions
 * @param	Modern\Wordpress\Helpers\ActiveRecordTable			$actions		The table of associated actions
 * @param	Modern\Wordpress\Helpers\ActiveRecordTable			$subrules		The table of associated subrules
 * @param	Modern\Wordpress\Plugin								$plugin			The plugin associated with the active records/view
 * @param	Modern\Wordpress\Helpers\ActiveRecordController		$controller		The associated controller displaying this view
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

?>

<div>
	<div class="mwp-bootstrap">
		<div class="alert alert-info">
			Event: <strong><?php echo $log->event()->title ?></strong> 
			<span class="pull-right"><?php echo date_i18n( 'Y-m-d H:i:s', $log->time ) ?></span>
		</div>
		
		<div class="alert alert-success">
			<ul>
				<li>Rule: <strong><?php if ( $rule = $log->rule() ) { echo '<a href="' . $rule->url() . '">' . esc_html( $rule->title ) . '</a>'; } else { echo "Unknown (deleted)"; } ?></strong></li>
				<li>Status: <strong><?php echo $log->message ?></strong></li>
				<li>Result: <strong><?php echo $log->result ?></strong></li>
			</ul>
		</div>
	</div>
	
	<h3 class="mwp-bootstrap" style="margin-bottom: -30px"><i class="glyphicon glyphicon-filter"></i> Conditions</h3>
	<?php echo $conditions->getDisplay() ?>
	
	<h3 class="mwp-bootstrap" style="margin-bottom: -30px"><i class="glyphicon glyphicon-flash"></i> Actions</h3>
	<?php echo $actions->getDisplay() ?>
	
	<h3 class="mwp-bootstrap" style="margin-bottom: -30px"><i class="glyphicon glyphicon-link"></i> Linked Rules</h3>
	<?php echo $subrules->getDisplay() ?>
	
</div>