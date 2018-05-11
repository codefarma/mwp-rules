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

use MWP\Rules;

?>

<style>
#wpcontent {
	padding-left: 0px;
}
.auto-fold #wpcontent {
	padding-left: 0px;
}
.header.color-overlay::after {
	background: url(<?php echo Rules\Plugin::instance()->fileUrl('assets/img/circles.png') ?>) center center/ cover no-repeat;
}
.wrap {
	padding-left: 20px;
}
.wrap.management.records {
	border-top: 0;
	margin-top: 0;
}
.wrap.management.records > h1 {
	margin: 0;
	font-size: 2em;
}
.notice, .update, .updated, .update-nag {
	display: none;
}
</style>

<div class="rules-header header color-overlay" style="background: rgb(51, 122, 183) none repeat scroll 0% 0%; position: relative;">
	<div class="inner-header-links mwp-bootstrap">
		<i class="glyphicon glyphicon-question-sign"></i> <a target="_blank" href="https://www.codefarma.com/docs/rules-user">Get Help</a>
	</div>
	<div class="inner-header-description gridContainer">
		<div class="row header-description-row">
			<div class="col-xs col-xs-12">
				<div class="hero-title">
					<?php echo $title ?>       
				</div>
			</div>
		</div>
	</div>
	<div class="split-header"></div>
	<div class="header-separator header-separator-bottom ">
		<svg class="mesmerize" preserveAspectRatio="none" width="1000" height="100" viewBox="0 0 1000 100" xmlns="http://www.w3.org/2000/svg">
			<defs>
			  <pattern id="img1" patternUnits="userSpaceOnUse" width="1920" height="1200">
				<image xlink:href="<?php echo Rules\Plugin::instance()->fileUrl('assets/img/gray-texture-bg.jpg') ?>" x="0" y="0" width="100%" height="100%" preserveAspectRatio="xMaxYMin slice" />
			  </pattern>
			</defs>
			<g fill="none">
				<path class="svg-white-bg" d="M-1.23 78.87c186.267-24.436 314.878-36.485 385.833-36.147 106.432.506 167.531 21.933 236.417 21.933s183.312-50.088 254.721-55.62c47.606-3.688 89.283 2.613 125.03 18.901v72.063l-1002 1.278v-22.408z" fill="url(#img1)"></path>
				<path class="svg-accent" d="M-1.23 87.791c171.627-34.447 300.773-52.658 387.438-54.634 129.998-2.964 166.902 40.422 235.909 40.422s175.29-63.463 246.825-68.994c47.69-3.687 91.633 10.063 131.828 41.25" stroke="#50E3C2" stroke-width="1"></path>
			</g>
		</svg>
	</div>
</div>
