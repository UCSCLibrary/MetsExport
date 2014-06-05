<?php

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
?>
