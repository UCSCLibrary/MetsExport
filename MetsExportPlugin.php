<?php
/**
 * METS Export
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */


/**
 * METS Export plugin.
 */
class MetsExportPlugin extends Omeka_Plugin_AbstractPlugin
{

    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
			      'config_form',
			      'admin_head',
			      'admin_collections_show',
			      'install',
			      'uninstall', 
			      'config'
			      );
    /**
     * @var array $_filters Filters for the plugin.
     */
    protected $_filters = array('action_contexts','response_contexts');

    /**
     * @var array $options Options for the plugin.
     */
    protected $_options = array(
			       //'mets_includeDeriv'=>false,
			       'mets_includeLogs'=>0,
			       'mets_admElements'=>""
			       );
    
    /*
     * Define the METS context and set browser headers 
     * to output an XML file with a .mets extension
     */
    public function filterResponseContexts($contexts)
    {

      $contexts['METS'] = array('suffix' => 'mets',
				'headers' => array('Content-Type' => 'application/octet-stream')
				);
      
      $contexts['METSzip'] = array('suffix' => 'metszip',
      				   'headers' => array('Content-Type' => 'application/octet-stream')
      );
   
      return $contexts;

    }

    /**
     * Display the plugin config form.
     */
    public function hookConfigForm() {
      require_once dirname(__FILE__) . '/forms/ConfigForm.php';
      $form = new MetsExport_Form_Config();
      echo($form->render());
    }

    /**
     * Set the options from the config form input.
     */
    public function hookConfig() {
      if(isset($_REQUEST['descMeta']))
	{
	  try{
	    require_once dirname(__FILE__) . '/forms/ConfigForm.php';
	    MetsExport_Form_Config::ProcessPost();
	  }catch(Exception $e) {
	    $flashMessenger = $this->_helper->FlashMessenger;
	    $flashMessenger->addMessage("Unable to save new Mets Export options","error");  
	  }
	}
    }

   /**
     * Install the plugin.
     */
    public function hookInstall()
    {
      
      $admElementsDefault = array(
				  'License'=>'rights',
				  'Rights'=>'rights',
				  'Source'=>'source',
				  'Rights Holder'=>'rights',
				  'Original Format'=>'tech',
				  'Provenance'=>'digiprov',
				  'Compression'=>'tech',
				  'Physical Dimensions'=>'tech',
				  'OwningInstitution'=>'rights',
				  'OwningInstitutionURL'=>'rights',
				  'Bit Rate/Frequency'=>'tech',
				  'Date Created'=>'tech'
				  );
      $this->_options['mets_admElements']=serialize($admElementsDefault);
      $this->_installOptions();
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {   
        $this->_uninstallOptions();
    }

    /**
     *  Add a button on the collection display page to export the 
     * collection as a zipped array of .mets files
     */
    public function hookAdminCollectionsShow($args) {
      $collection = $args['collection'];
      echo '<a href="'.$collection->id.'?output=METSzip"><button>Export as .mets</button></a>';
    }


    /**
     * Add METS format to Omeka item output list
     */
    public function filterActionContexts($contexts, $args)
    {
      if($args['controller'] instanceOf ItemsController)
	$contexts['show'][] = 'METS';
      else if($args['controller'] instanceOf CollectionsController)
	$contexts['show'][] = 'METSzip';

      return $contexts;
    }

    public function hookAdminHead()
    {
      queue_js_file('MetsExport');
      queue_css_file('MetsExport');
    }

}
