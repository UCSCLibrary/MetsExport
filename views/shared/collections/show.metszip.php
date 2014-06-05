<?php

include_once(dirname(dirname(dirname(dirname(__FILE__)))).'/helpers/MetsExporter.php');

$collection = get_current_record('collection');
$collectionID = $collection->id;

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="'.$collection->id.'.zip"');

$metsExporter = new MetsExporter();

if(!isset($collectionID))
  die('ERROR: collection ID not set');

try{
  echo $metsExporter->exportCollection($collectionID);
} catch (Exception $e) {
  $this->flashMessenger->addMessage($e->getMessage(),'error');
}
?>
