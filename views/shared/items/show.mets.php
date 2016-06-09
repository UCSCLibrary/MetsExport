<?php
/**
 * METS Export item view script 
 *
 * Output a single METS file for an Omeka item.
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

include_once(dirname(dirname(dirname(dirname(__FILE__)))).'/helpers/MetsExporter.php');


$item = get_current_record('item');
$itemID = $item->id;

$metsExporter = new MetsExporter();

if(!isset($itemID))
  die('ERROR: item ID not set');

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="Item_'.$itemID.'_METS.xml"');

try{
  echo $metsExporter->exportItem($itemID);
} catch (Exception $e) {
  $this->flashMessenger->addMessage($e->getMessage(),'error');;
}
