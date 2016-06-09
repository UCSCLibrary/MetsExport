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
    
    /**
     * Define the METS context and set browser headers 
     * to output an XML file with a .mets extension
     *
     *@param array $contexts The unfiltered response contexts
     *@return array $contexts The filtered response contexts 
     *(with the METS ones added)
     */
    public function filterResponseContexts($contexts)
    {

      $contexts['METS'] = array('suffix' => 'mets',
				'headers' => array('Content-Type' => 'application/octet-stream')
				);

      $contexts['METScol'] = array('suffix' => 'metscol',
				'headers' => array('Content-Type' => 'application/octet-stream')
				);
      
      $contexts['METSzip'] = array('suffix' => 'metszip',
      				   //'headers' => array('Content-Type' => 'application/octet-stream')
				   'headers' => array('Content-Type' => 'text/xml')
      );
   
      return $contexts;

    }

    /**
     * Display the plugin config form.
     *
     *@return void
     */
    public function hookConfigForm() {
        
      try{
	require_once dirname(__FILE__) . '/forms/ConfigForm.php';
	$form = new MetsExport_Form_Config();
	echo($form->render());
      }catch(Exception $e) {
	throw $e; 
      }  //end try-catch
    }

    /**
     * Set the options from the config form input.
     *
     *@return void
     */
    public function hookConfig() {
      if(isset($_REQUEST['descMeta']))
	{
	  try{
	    require_once dirname(__FILE__) . '/forms/ConfigForm.php';
	    MetsExport_Form_Config::ProcessPost();
	  }catch(Exception $e) {
	    throw $e;
	  } //end try-catch
	} //end if
    }

   /**
     * Create options and load defaults on plugin install.
     *
     *@return void
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
     * Drop this plugins option from the db on plugin uninstall
     *
     *@return void
     */
    public function hookUninstall()
    {   
        $this->_uninstallOptions();
    }

    /**
     *  Add a button on the collection display page to export the 
     * collection as a zipped array of .mets files
     *
     *@param array $args Parameters sent to the plugin hook from Omeka
     *@return void
     */
    public function hookAdminCollectionsShow($args) {
      $collection = $args['collection'];
      echo '<a href="'.$collection->id.'?output=METSzip"><button>Export as zip file of METS xml files</button></a><br>';
      echo '<a href="'.$collection->id.'?output=METScol"><button>Export as single mets xml file</button></a>';
    }


    /**
     * Add METS format to Omeka item output list
     *
     *@param array $contexts The unfiltered action contexts
     *@param array $args Parameters sent to the plugin hook from Omeka
     *@return array $contexts The filtered action contexts
     *(with the Mets contexts added)
     */
    public function filterActionContexts($contexts, $args)
    {
      if($args['controller'] instanceOf ItemsController) {
	$contexts['show'][] = 'METS';
      } else if($args['controller'] instanceOf CollectionsController) {
	$contexts['show'][] = 'METSzip';
	$contexts['show'][] = 'METScol';
      }

      return $contexts;
    }

    /**
     *Queue javascript and css files when the admin section loads
     *
     *@return void
     */
    public function hookAdminHead()
    {
      queue_js_file('MetsExport');
      queue_css_file('MetsExport');
    }

}
