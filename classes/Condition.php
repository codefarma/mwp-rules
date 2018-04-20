<?php
/**
 * Plugin Class File
 *
 * Created:   December 4, 2017
 *
 * @package:  MWP Rules
 * @author:   Kevin Carwile
 * @since:    0.0.0
 */
namespace MWP\Rules;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

use \MWP\Framework\Pattern\ActiveRecord;

/**
 * Condition Class
 */
class _Condition extends GenericOperation
{
	/**
     * @var    array        Required for all active record classes
     */
    protected static $multitons = array();

    /**
     * @var    string        Table name
     */
    public static $table = "rules_conditions";

    /**
     * @var    array        Table columns
     */
    public static $columns = array(
        'id',
		'uuid',
        'title',
        'weight',
		'parent_id',
		'rule_id',
		'key',
		'data' => array(
			'format' => 'JSON'
		),
        'enabled',
		'group_compare',
		'not',
		'imported',
    );

    /**
     * @var    string        Table primary key
     */
    public static $key = 'id';

    /**
     * @var    string        Table column prefix
     */
    public static $prefix = 'condition_';
	
	/**
	 * @var	string
	 */
	public static $plugin_class = 'MWP\Rules\Plugin';
	
	/**
	 * @var	string
	 */
	public static $lang_singular = 'Condition';
	
	/**
	 * @var	string
	 */
	public static $lang_plural = 'Conditions';
	
	/**
	 * @var	string
	 */
	public static $sequence_col = 'weight';
	
	/**
	 * @var	string
	 */
	public static $parent_col = 'parent_id';
	 
	/**
	 * Associated Rule
	 */
	public $rule = NULL;
	
	/**
	 * @var string
	 */
	public static $optype = 'condition';
	
	/**
	 * Get controller actions
	 *
	 * @return	array
	 */
	public function getControllerActions()
	{
		return array(
			'add' => array(
				'icon' => 'glyphicon glyphicon-plus',
				'attr' => array(
					'class' => 'btn btn-sm btn-default',
					'title' => __( 'Add New Subcondition', 'mwp-rules' ),
				),
				'params' => array(
					'do' => 'new',
					'parent_id' => $this->id,
				)
			),
			'edit' => array(
				'icon' => 'glyphicon glyphicon-wrench',
				'attr' => array(
					'class' => 'btn btn-sm btn-default',
					'title' => __( 'Configure Condition', 'mwp-rules' ),
				),
				'params' => array(
					'do' => 'edit',
					'id' => $this->id,
					'_tab' => 'operation_config',
				),
			),
			'delete' => array(
				'icon' => 'glyphicon glyphicon-trash',
				'attr' => array( 
					'class' => 'btn btn-sm btn-default',
					'title' => __( 'Delete Condition', 'mwp-rules' ),
				),
				'params' => array(
					'do' => 'delete',
					'id' => $this->id,
				),
			)
		);
	}
	
	/**
	 * Build an editing form
	 *
	 * @return	MWP\Framework\Helpers\Form
	 */
	protected function buildEditForm()
	{
		$plugin = $this->getPlugin();
		$form = static::createForm( 'edit', array( 'attr' => array( 'class' => 'form-horizontal mwp-rules-form' ) ) );
		$condition = $this;
		
		/* Display details for the app/bundle/parent */
		$form->addHtml( 'rule_overview', $plugin->getTemplateContent( 'rules/overview/header', [ 
			'rule_item' => 'rule_conditions',
			'rule' => $this->getRule(), 
			'bundle' => $this->getRule() ? $this->getRule()->getBundle() : null, 
			'app' => $this->getRule() ? $this->getRule()->getApp() : null, 
		]));
		
		if ( $condition->title ) {
			$form->addHtml( 'rule_title', $plugin->getTemplateContent( 'rules/overview/title', [
				'icon' => '<i class="glyphicon glyphicon-filter"></i>',
				'label' => 'Condition',
				'title' => $condition->title,
			]));
		}
		
		static::buildConfigForm( $form, $condition );
		
		$form->addField( 'enabled', 'checkbox', array(
			'label' => __( 'Condition Enabled?', 'mwp-rules' ),
			'value' => 1,
			'data' => isset( $condition->enabled ) ? (bool) $condition->enabled : true,
			'row_suffix' => '<hr>',
		),
		'operation_details', 'key', 'before' );
		
		/** Condition specific form fields **/
		
		$form->addField( 'not', 'checkbox', array(
			'label' => __( 'NOT', 'mwp-rules' ),
			'value' => 1,
			'description' => __( 'Using NOT will reverse the condition result so that the result is TRUE if the condition is NOT MET. ', 'mwp-rules' ),
			'data' => (bool) $condition->not,
		),
		'operation_details', 'title' );
		
		if ( $condition->children() ) {
			$form->addField( 'group_compare', 'choice', array(
				'label' => __( 'Group Compare Mode', 'mwp-rules' ),
				'choices' => array(
					'AND' => 'and',
					'OR' => 'or',
				),
				'data' => $condition->compare_mode ?: 'and',
				'required' => true,
				'expanded' => true,
				'description' => "
					Since this condition has subconditions, you can choose how you want those subconditions to affect the state of this condition.<br>
					<ul>
						<li>If you choose AND, this condition and all subconditions must be true for this condition to be valid.</li>
						<li>If you choose OR, this condition will pass if it is valid, or if any subcondition is valid.</li>
					</ul>",
				'row_suffix' => '<hr>',
			),
			'operation_details', 'enabled' );
		}
		
		if ( ! $condition->id ) {
			$form->onComplete( function() use ( $condition, $plugin ) {
				$controller = $plugin->getConditionsController();
				wp_redirect( $controller->getUrl( array( 'do' => 'edit', 'id' => $condition->id(), '_tab' => 'operation_config' ) ) );
				exit;
			});
		}
		
		return $form;
	}
	
	/**
	 * Get the condition definition
	 * 
	 * @return	array|NULL
	 */
	public function definition()
	{
		return \MWP\Rules\Plugin::instance()->getCondition( $this->key );
	}
	
	/**
	 * Get Compare Mode
	 *
	 * @return	string
	 */
	public function compareMode()
	{
		return $this->group_compare ?: 'and';
	}
	
	/**
	 * Recursion Protection
	 */
	public $locked = FALSE;
	
	/**
	 * Invoke Condition
	 *
	 * @return	bool
	 */
	public function invoke()
	{
		$plugin = \MWP\Rules\Plugin::instance();
		
		if ( ! $this->locked or $this->rule()->enable_recursion )
		{
			/**
			 * Lock this from being triggered recursively
			 * and creating never ending loops
			 */
			$this->locked = TRUE;
			
			try
			{
				$result = $this->opInvoke( func_get_args() );
			}
			catch( \Exception $e )
			{
				$this->locked = FALSE;
				throw $e;
			}
			
			if ( count( $this->children() ) )
			{
				$compareMode = $this->compareMode();
				
				/**
				 * We already have a winner
				 */
				if ( $result and $compareMode == 'or' )
				{
					return TRUE;
				}
				
				/**
				 * We have already failed
				 */
				if ( ! $result and $compareMode == 'and' )
				{
					return FALSE;
				}
				
				/* Only possibilities at this point */
				// result FALSE mode OR
				// result TRUE mode AND
				
				foreach ( $this->children() as $condition )
				{
					if ( $condition->enabled )
					{
						$conditionsCount++;
						$_result = call_user_func_array( array( $condition, 'invoke' ), func_get_args() );
						
						if ( $_result and $compareMode == 'or' ) 
						{
							$result = TRUE;
							break;
						}

						if ( ! $_result and $compareMode == 'and' )
						{
							$result = FALSE;
							break;
						}
					}
					else
					{
						if ( $rule = $this->rule() and $rule->debug )
						{
							$plugin->rulesLog( $rule->event(), $rule, $condition, '--', 'Condition not evaluated (disabled)' );
						}
					}
				}
			}
			
			$this->locked = FALSE;
			
			return $result;
		}
		else
		{
			if ( $rule = $this->rule() and $rule->debug )
			{
				$plugin->rulesLog( $rule->event(), $rule, $this, '--', 'Condition recursion protection (not evaluated)' );
			}
		}
	}
	
	/**
	 * Get the action url
	 *
	 * @param	array			$params			Url params
	 * @return	string
	 */
	public function url( $params=array() )
	{
		return $this->getPlugin()->getConditionsController()->getUrl( array_merge( array( 'id' => $this->id, 'do' => 'edit', 'rule_id' => $this->rule_id ), $params ) );
	}
	
	/**
	 * @var	array
	 */
	protected $childrenCache;
	
	/**
	 * Get the children
	 * 
	 * @return array[Condition]
	 */
	public function children()
	{
		if ( ! $this->id ) {
			return array();
		}

		if ( isset( $this->childrenCache ) ) {
			return $this->childrenCache;
		}
		
		$this->childrenCache = static::loadWhere( array( 'condition_parent_id=%d', $this->id ), 'condition_weight ASC' );
		return $this->childrenCache;
	}
	
	public function getChildren()
	{
		return $this->children();
	}
	
	/**
	 * Get export data
	 *
	 * @return	array
	 */
	public function getExportData()
	{
		$export = parent::getExportData();
		$export['children'] = array_map( function( $subrule ) { return $subrule->getExportData(); }, $this->getChildren() );
		return $export;
	}
	
	/**
	 * Import data
	 *
	 * @param	array			$data				The data to import
	 * @param	Rule			$rule_id			The parent rule id
	 * @param	int				$parent_id			The parent condition id
	 * @return	array
	 */
	public static function import( $data, $rule_id, $parent_id=0 )
	{
		$uuid_col = static::$prefix . 'uuid';
		$results = [];
		
		if ( isset( $data['data'] ) ) 
		{
			$_existing = ( isset( $data['data'][ $uuid_col ] ) and $data['data'][ $uuid_col ] ) ? static::loadWhere( array( $uuid_col . '=%s', $data['data'][ $uuid_col ] ) ) : [];
			$condition = count( $_existing ) ? array_shift( $_existing ) : new static;
			
			/* Set column values */
			foreach( $data['data'] as $col => $value ) {
				$col = substr( $col, strlen( static::$prefix ) );
				$condition->_setDirectly( $col, $value );
			}
			
			$condition->rule_id = $rule_id;
			$condition->parent_id = $parent_id;
			$condition->imported = time();
			$result = $condition->save();
			
			if ( ! is_wp_error( $result ) ) 
			{
				$results['imports']['conditions'][] = $data;
				
				if ( isset( $data['children'] ) and ! empty( $data['children'] ) ) {
					foreach( $data['children'] as $subcondition ) {
						$results = array_merge_recursive( $results, Condition::import( $subcondition, $rule_id, $condition->id() ) );
					}
				}
			} else {
				$results['errors']['conditions'][] = $result;
			}
		}
		
		return $results;
	}
	
	/**
	 * [ActiveRecord] Delete Record
	 *
	 * @return	bool|WP_Error
	 */
	public function delete()
	{
		foreach ( $this->children() as $child )
		{
			$child->delete();
		}
		
		return parent::delete();
	}	
	
	/**
	 * Save
	 *
	 * @return	bool|WP_Error
	 */
	public function save()
	{
		if ( ! $this->uuid ) { 
			$this->uuid = uniqid( '', true ); 
		}
		
		return parent::save();
	}
	
}
