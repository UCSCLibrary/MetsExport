<?php

include_once(dirname(dirname(dirname(dirname(__FILE__)))).'/helpers/MetsExporter.php');


$item = get_current_record('item');
$itemID = $item->id;

$metsExporter = new MetsExporter();

if(!isset($itemID))
  die('ERROR: item ID not set');

try{
  echo $metsExporter->exportItem($itemID);
} catch (Exception $e) {
    echo 'Exception while exporting item: ',  $e->getMessage(), "\n";
}
?>
