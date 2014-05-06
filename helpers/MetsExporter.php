<?php

Class MetsExporter 
{ 
  public $force_download = false;

  public function exportItem($itemID)
  {
    ob_start();
    $this->_generateMETS($itemID);
    return ob_get_clean();
  }

  public function exportCollection($collectionID)
  {
    include_once(dirname(dirname(__FILE__)).'/libraries/zipstream-php-0.2.2/zipstream.php');

    $collection = get_record_by_id("collection",$collectionID);

    $items = get_records('Item',array('collection'=>$collectionID),999);
    
    error_reporting(0);

    $zip = new ZipStream($collection->id.'.zip');

    foreach($items as $item)
      {
	ob_start();
	$this->_generateMETS($item->id);
	$zip->add_file($item->id.".mets", ob_get_clean() );
      }
    $zip->finish();
  }


  private function _generateMETS($itemID)
  {
    
    if(!is_numeric($itemID))
      {
	echo "ERROR: Invalid item ID";
	die();
      }

    $item = get_record_by_id("item",$itemID);
    $owner = $item->getOwner();
    $currentuser = current_user();

    if(is_null($item)||empty($item))
      {
	echo "ERROR: Invalid item ID";
	die();
      }

    //$title = "testTitle";
    $titles = $item->getElementTexts('Dublin Core','Title');
    $title = $titles[0];
    $title = htmlspecialchars($title);
    $type = $item->getItemType();
    $files = $item->getFiles();
    $agents = $this->_getAgents($item);


    if($this->force_download)
      {
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="ITEM_'.$itemID.'_METS.xml"');
      }
    echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

    //--------------------
    //BEGIN OUTER METS DIV
    //--------------------

    echo '<METS:mets ';
    echo 'xmlns:METS="http://www.loc.gov/METS/" ';
    echo 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
    echo 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
    echo 'xmlns:xlink="http://www.w3.org/1999/xlink" ';
    echo 'xsi:schemaLocation="http://www.loc.gov/METS/ http://www.loc.gov/standards/mets/mets.xsd  http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-0.xsd" ';
    echo 'ID="ITEM_'.$itemID.'" ';
    echo 'OBJID="ITEM_'.$itemID.'" ';
    echo 'LABEL="'.$title.'" ';
    echo 'TYPE="'.$type->name.'" ';

    //echo 'PROFILE="OMEKA PROFILE ONCE IT IS REGISTERED" ';

    echo ">\n";

    //--------------------
    //METS HEADER
    //--------------------

    echo "\n<METS:metsHdr ";
    echo 'CREATEDATE="'.date("Y-m-d\TH:i:s").'" ';
    //echo 'LASTMODDATE="'..'" ';
    echo 'ID="HDR_ITEM'.$itemID.'" ';
    //echo '"ADMID=ADM_HDR_ITEM'.$itemID.'" ';
    echo ">\n";

    foreach($agents as $agent)
      {
	echo '<METS:agent ';
	echo 'ROLE="'.$agent[1].'" ';
	echo 'TYPE="'.$agent[2].'" ';
	echo ">\n";
    
	echo "<METS:name>";
	echo $agent[0];
	echo "</METS:name>\n";

	echo "<METS:note>";
	echo $agent[3];
	echo "</METS:note>\n";

	echo "</METS:agent>\n";
      }

    echo "</METS:metsHdr>\n";

    //--------------------
    //DESCRIPTIVE METADATA
    //--------------------

    //---ITEM dmdSec

    echo "\n<METS:dmdSec ";
    echo 'ID="DMD_ITEM'.$itemID.'" ';
    //echo 'ADMID=AMD_ITEM"'.$itemID.'" ';
    //echo 'GROUPID="'..'" ';
    //echo 'CREATED="'..'" ';
    //echo 'STATUS="'..'" ';
    echo ">\n";

    $elementArray = $item->getAllElements();

    foreach($elementArray as $elementSetName => $elements)
      {
	ob_start();
	$flag = false;

	$eSSlug=$this->_getElementSetSlug($elementSetName);
    
	echo '<METS:mdWrap ';
	echo 'ID="MDW_ITEM'.$itemID.'_'.$eSSlug.'" ';
	//echo 'MIMETYPE="'..'" ';
	echo 'LABEL="'.$elementSetName.'" ';
	if($this->_is_type_other($eSSlug))
	  {
	    echo 'MDTYPE="OTHER" ';
	    //echo 'MDTYPEVERSION="'..'" ';
	    $eSSlug = "";
	    echo 'OTHERMDTYPE="'.strtoupper(preg_replace('/\s+/', '', $elementSetName)).'" ';
	  }
	//echo 'SIZE="'..'" ';
	//echo 'CREATED="'..'" ';
	//echo 'CHECKSUM="'..'" ';
	//echo 'CHECKSUMTYPE="'..'" ';
	echo ">\n";

	echo "<METS:xmlData>\n";
    
	if($eSSlug!=="")
	  $eSSlug .= ":";

	foreach($elements as $element)
	  {
	    $elementTexts =  $item->getElementTexts($elementSetName,$element->name);
	    if(empty($elementTexts))
	      continue;
	    $flag = true;

	    foreach($elementTexts as $elementText)
	      {
		echo '<'.$eSSlug.preg_replace('/\s+/', '',$element->name).">";
		echo $elementText->text;
		echo "</".$eSSlug.preg_replace('/\s+/', '', $element->name).">\n";
	      }
	  }

	echo "</METS:xmlData>\n";
	echo "</METS:mdWrap>\n";
	if($flag)
	  ob_end_flush();
	else
	  ob_end_clean();
      }

    echo "</METS:dmdSec>\n";


    //----FILE DmdSecs---//

    $i=0;
    foreach($files as $file)
      {
	echo "\n<METS:dmdSec ";
	echo 'ID="DMD_FILE'.$file->item_id.$i.'" ';
	//echo 'ADMID=AMD_ITEM"'.$itemID.'" ';
	//echo 'GROUPID="'..'" ';
	//echo 'CREATED="'..'" ';
	//echo 'STATUS="'..'" ';
	echo ">\n";

	$elements = $file->getAllElements();

	foreach($elements as $elementSetName => $elements)
	  {
	    ob_start();
	    $flag=false;

	    $eSSlug=$this->_getElementSetSlug($elementSetName);

	    echo '<METS:mdWrap ';
	    echo 'ID="MDW_FILE'.$file->item_id.$i.'" ';
	    //echo 'MIMETYPE="'..'" ';
	    echo 'LABEL="'.$elementSetName.'" ';
	    if($this->_is_type_other($eSSlug))
	      {
		echo 'MDTYPE="OTHER" ';
		//echo 'MDTYPEVERSION="'..'" ';
		if($eSSlug==="unknown")
		  $eSSlug = "";
		echo 'OTHERMDTYPE="'.strtoupper(preg_replace('/\s+/', '', $elementSetName)).'" ';
	      }
	    //echo 'SIZE="'..'" ';
	    //echo 'CREATED="'..'" ';
	    //echo 'CHECKSUM="'..'" ';
	    //echo 'CHECKSUMTYPE="'..'" ';
	    echo ">\n";

	    echo "<METS:xmlData>\n";
	
	    if($eSSlug!=="")
	      $eSSlug .= ":";

	    foreach($elements as $element)
	      {

		$elementTexts =  $file->getElementTexts($elementSetName,$element->name);

		if(empty($elementTexts))
		  continue;
		$flag = true;

		foreach($elementTexts as $elementText)
		  {
		    echo '<'.$eSSlug.preg_replace('/\s+/', '',$element->name).">";
		    echo $elementText->text;
		    echo "</".$eSSlug.preg_replace('/\s+/', '', $element->name).">\n";
		  }
	      }

	    echo "</METS:xmlData>\n";
	    echo "</METS:mdWrap>\n";
	    if($flag)
	      ob_end_flush();
	    else
	      ob_end_clean();
	  }    
	echo "</METS:dmdSec>\n";
      }
 
    //----COLLECTION dmdsec----//



    //--------------------
    //ADMINISTRATIVE METADATA
    //--------------------


    //**TODO** ADD SECTIONS HERE FOR OMEKA USER INFORMATION
    //(IF I CAN FIND ANYTHING RELEVANT TO INCLUDE)


    //--------------------
    //FILES
    //--------------------

    echo "\n<METS:fileSec ";
    echo 'ID="FILES_ITEM'.$itemID.'" ';
    echo ">\n";

    //echo '<METS:fileGrp ';
    //echo 'ID="'..'" ';
    //echo 'ADMID="'..'" ';
    //echo 'VERSDATE="'..'" ';
    //echo 'USE="'..'" ';
    //echo ">\n";

    $i=0;
    foreach($files as $file)
      {
	//echo "<h2>".$file->filename."</h2>";
	echo '<METS:file ';
	echo 'ID="FILE'.$file->item_id.$i.'" ';
	echo 'MIMETYPE="'.$file->mime_type.'" ';
	//echo 'SEQ="'..'" ';
	echo 'SIZE="'.$file->size.'" ';
	echo 'CREATED="'.$file->added.'" ';
	//echo 'CHECKSUM="'..'" ';
	//echo 'CHECKSUMTYPE="'..'" ';
	//echo 'OWNERID="'..'" ';
	//echo 'ADMID="AMD_FILE'.$file->item_id.$i.'" ';
	echo 'DMDID="DMD_FILE'.$file->item_id.$i.'" ';
	//echo 'GROUPID="'..'" ';
	//echo 'USE="'..'" ';
	echo ">\n";

	echo '<FLocat ';
	//echo 'ID="'..'" ';
	echo 'LOCTYPE="URL" ';
	//echo 'OTHERLOCTYPE="'..'" ';
	//echo 'USE="'..'" ';
	echo 'xlink:href="'.$file->getWebPath().'" ';
	echo "></FLocat>\n";
     

	echo "</METS:file>\n";
    
	$i++;
      }
    //**TODO** separate fileGroups for derivative images?
    //echo '</METS:fileGrp>\n';

    echo "</METS:fileSec>\n";

    //--------------------
    //STRUCTURAL MAP SECTION
    //--------------------

    echo "\n<METS:structMap ";
    //echo 'ID="STR_ITEM'.$itemID.'" ';
    //echo 'TYPE="'..'" ';
    //echo 'LABEL="'..'" ';
    echo ">\n";

    echo '<METS:div ';
    echo 'TYPE="ITEM" ';
    echo 'DMDID="DMD_ITEM'.$itemID.'" ';
    echo 'AMDID="AMD_ITEM'.$itemID.'" ';
    echo ">\n";

    $i=0;
    foreach ($files as $file)
      {
	echo '<METS:fptr FILEID="FILE'.$file->item_id.$i.'"/>'."\n";
	//--TODO---each file should be grouped by type,
	//and then with it's deriv. images (if any)
      }

    echo "</METS:div>\n";

    echo "\n</METS:structMap>\n";

    //--------------------
    //END OUTER METS DIV
    //--------------------
    echo "</METS:mets>\n";




  }

  

  private function _getAgents($item)
  {
    $owner = $item->getOwner();
    $currentuser = current_user();
    $rv[]=array($owner->name,"ARCHIVIST","INDIVIDUAL","");
    $rv[]=array($currentuser->name,"CREATOR","INDIVIDUAL","");
    $rv[]=array("Omeka MetsExport Plugin","OTHER","OTHER","The software used to generate this document is called Omeka MetsExport, which operates as a plugin for Omeka. Documentation can be found at http://github/MetsExport/");

    return $rv;
  
  }

  private function _getElementSetSlug($elementSetName)
  {
    //TODO - add support for all common metadata element sets
    switch($elementSetName)
      {
      case 'Dublin Core':
	return 'dc';
	break;
      default:
	return 'unknown';
      }

  }

  private function _is_type_other($eSSlug)
  {
    if($eSSlug==="unknown")
      return true;
    else
      return false;

  }


}

?>