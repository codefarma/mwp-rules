<?php
/**
 * Plugin Class File
 *
 * Created:   December 12, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use MWP\Framework\Pattern\AdminController;
use MWP\Rules;

/**
 * DashboardController Class
 *
 */
class _DashboardController extends AdminController
{
	/**
	 * @var		MWP\Rules\Plugin
	 */
	protected $plugin;

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	protected function constructed()
	{
		$this->plugin = Rules\Plugin::instance();
	}
	
	/**
	 * Dashboard Index
	 * 
	 * @return	void
	 */
	public function do_index()
	{
		echo $this->wrap( __( 'Automation Dashboard', 'mwp-rules' ), $this->plugin->getTemplateContent( 'dashboard/layout/main', [
			'controller' => $this,
		]));
	}

	/**
	 * Import a package
	 *
	 * @return	void
	 */
	public function do_import_package()
	{
		$form = $this->plugin->createForm( 'rules_package_import' );
		
		$form->addField( 'package', 'file', array(
			'label' => __( 'Package File', 'mwp-rules' ),
			'required' => true,
		));
		
		$form->addField( 'submit', 'submit', array(
			'label' => __( 'Import', 'mwp-rules' ),
		));
		
		if ( $form->isValidSubmission() ) {
			$values = $form->getValues();
			$file = $values['package'];
			if ( $file instanceof \SplFileInfo and $file->isReadable() ) 
			{
				$package = json_decode( file_get_contents( $file->getRealPath() ), true );
				
				try {
					$results = $this->plugin->importPackage( $package );
					echo $this->wrap( __( 'Import Complete.', 'mwp-rules' ), $this->plugin->getTemplateContent( 'dashboard/layout/import_results', [ 
						'controller' => $this,
						'results' => $results,
					]));
					return;
				}
				catch( \ErrorException $e ) {
					$form->addHtml( 'import_error', '<div class="alert alert-danger">' . $e->getMessage() . '</div>' );
				}
			}
		}
		
		echo $this->wrap( __( 'Upload A Package', 'mwp-rules' ), $form->render() );
	}
	
	/**
	 * Wrap output
	 *
	 */
	public function wrap( $title, $output )
	{
		return $this->plugin->getTemplateContent( 'dashboard/wrapper', [
			'controller' => $this,
			'title' => $title,
			'content' => $output,
		]);
	}
}
