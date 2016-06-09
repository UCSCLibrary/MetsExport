<?php

/**
 * METS Export configuration form
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * METS Export configuration form class
 * @package MetsExport
 *
 */
class MetsExport_Form_Config extends Omeka_Form
{
  /**
   * Construct the report generation form.
   *
   *@return void
   */
  public function init()
  {
    parent::init();
    $this->_registerElements();
	
  }

    public function render()
    {
        $formString = parent::render();
        $formContents =  preg_replace('/<\/?form(.*)>/U','',$formString);
        return $formContents;
    }

  /**
   * Define the form elements.
   *
   *@return void
   */
  private function _registerElements()
  {
    /*
    $this->addElement('checkbox', 'derivImages', array(
					    'label'         => __('Derivative Images'),
					    'description'   => __('Would you like to include derivative image files created by Omeka in your METS files?'),
					    'checked'         => 'checked',
					    'order'         => 1
					    )
		      );
    */

    
     
    // Include log information:
    if(plugin_is_active('HistoryLog'))
      {
	$includeLogsParams= array(
				  'label'         => __('Item History Logs'),
				  'description'   => __('Do you want to include logs of item curation events as administrative metadata in your METS files?'),
				  'order'         => 2
				  );
	
	if(get_option("mets_includeLogs")=='true')
	  $includeLogsParams['checked']='checked';
	$this->addElement('checkbox', 'includeLogs', $includeLogsParams);
      }

    $metaElementOptions = $this->_getDescAdmElements();

    // Descriptive metadata list:
    $this->addElement('select', 'descMeta', array(
						  'label'         => __('Descriptive metadata elements'),
						  'description'   => __('Metadata elements in this list will be included in the "descriptive metadata" section of each item\'s METS file'),
						  'order'         => 3,
						  'validators'    => array( array('alpha', false, array("allowWhiteSpace" => true))),
						  'id'            => 'desc-meta',
						  'multiOptions'       => $metaElementOptions['desc']
						  )
		      );
 
    $this->addElement('button', 'makeAdmButton', array(
	    'label'=>'Mark as Administrative    v',
	    'id' => 'make-adm-button',
	    'class' => 'select-button',
	    'order'         => 4
						       )
		      );

	// Administrative metadata list:
        $this->addElement('select', 'admMeta', array(
						      'label'         => __('Administrative metadata elements'),
						      'description'   => __('Metadata elements in this list will be included in the "administrative metadata" section of each item\'s METS file'),
						      'order'         => 6,
						      'validators'    => array( array('alpha', false, array("allowWhiteSpace" => true))),
						      'id'            => 'adm-meta',
						      'multiOptions'       => $metaElementOptions['adm']
						      )
			  );

    $this->addElement('button', 'makeDescButton', array(
	    'label'=>'^   Mark as Descriptive',
	    'id' => 'make-desc-button',
	    'class' => 'select-button',
	    'order'         => 5
						       )
		      );


    /*
    $this->addElement('submit', 'metsSubmitButton', array(
	    'label'=>'Save Options',
	    'id'   =>'mets-submit-button',
	    'order'=> 7
						       )
		  );
    */

    $this->addElement('radio', 'updateDialog', array(
            'label'=>'Metadata type',
	    'description' => 'Administrative metadata in METS files are divided into types. Please select the type that best describes the metadata element you are marking as "administrative"',
	    'id'            => 'updateDialog',
	    'order'         => 20,
	    'value' => 'tech',
	    'multiOptions'  => array(
				     'tech'=>'Technical (camera info, original format, etc)',
				     'rights'=>'Rights (licensing, copywright)',
				     'source'=>'Source (info about an analog source document used to create this digital document)',
				     'digiprov'=>'Digital Provenance (digital library object\'s life-cycle and history)',

				     )
							)
		      );

    $this->getElement('updateDialog')->getDecorator('FieldTag')->setOption('id','updateDialogDiv');


    $displayGroup = array(
			  //'derivImages',
			  'descMeta',
			  'admMeta',
			  'makeDescButton',
			  'makeAdmButton',
			  );

    if(plugin_is_active('HistoryLog'))
      $displayGroup[] = 'includeLogs';

    //Display Groups:
    $this->addDisplayGroup($displayGroup,'options');
    

  }

  /**
   * Process the data from the form and save changes to options
   *
   *@return void
   */
  public static function ProcessPost()
  {
    if(!empty($_REQUEST['derivImages']))
      set_option('mets_includeDeriv','true');
    else
      set_option('mets_includeDeriv','false');
    
    if(plugin_is_active('HistoryLog')) {
      if(!empty($_REQUEST['includeLogs'])) {
	set_option('mets_includeLogs','true');
      } else {
	set_option('mets_includeLogs','false');
      }
    }
    
    if(isset($_REQUEST['admElements'])) {

      

      $options = array();
      foreach($_REQUEST['admElements'] as $elementName)
	{
	  $options[$elementName]=$_REQUEST['adm_type_'.str_replace(' ', '', $elementName)];
	}
      set_option('mets_admElements',serialize($options));
    }
    

  }


  /**
   *Return array of metadata elements sorted as descriptive or administrative
   *
   *@return array $elements Array containing two array with the keys 
   *'adm' and 'desc' containing all metadata elements between them
   */
  private function _getDescAdmElements() {
    $admElements = unserialize(get_option('mets_admElements'));
    if(!is_array($admElements))
      $admElements = array();
    
    try{
      $db = get_db();
      $sql = "
        SELECT es.name AS element_set_name, e.id AS element_id, 
        e.name AS element_name, it.name AS item_type_name
        FROM {$db->ElementSet} es 
        JOIN {$db->Element} e ON es.id = e.element_set_id 
        LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id 
        LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id 
         WHERE es.record_type IS NULL OR es.record_type = 'Item' 
        ORDER BY es.name, it.name, e.name";
      $elements = $db->fetchAll($sql);
    }catch(Exception $e) {
      throw new Exception("Error connecting to database");
    }

    $options = array();

    foreach ($elements as $element) {
      $optGroup = $element['item_type_name'] 
	? __('Item Type') . ': ' . __($element['item_type_name']) 
	: __($element['element_set_name']);
      $value = __($element['element_name']);
            
      if(array_key_exists($element['element_name'],$admElements))
	$options['adm'][$optGroup][$element['element_id']] = $value;
      else
	$options['desc'][$optGroup][$element['element_id']] = $value;
    }

    if(empty($options['adm']))
      $options['adm'] = array(array());
    
    return $options;
  }
   
}
