<?php

$item = get_current_record('item');
$itemID = $item->id;

$moduleDir = str_replace("views/shared/items","modules/",dirname(__FILE__));
$moduleName = 'METS';
try{
  include($moduleDir."OmeConnections_Abstract_Module.php");
  include($moduleDir.$moduleName.".php");
}catch (Exception $e) {
  echo 'Exception while loading export module: ',  $e->getMessage(), "\n";
 }

$moduleName .= "_module";

try{
  $module = new $moduleName;
}catch (Exception $e) {
  echo 'Exception while instantiating export module: ',  $e->getMessage(), "\n";
 }

$module->force_download = false;

if(!isset($module))
  die('ERROR: export module not sent to export view script');

if(!isset($itemID))
  die('ERROR: item ID not sent to export view');

if(!$module->installed)
  die('ERROR: '.$moduleName.'export module not installed');

if(!$module->active)
  die('ERROR: '.$moduleName.'export module not activated');

if(!$module->supports_push)
  die('ERROR: '.$moduleName.'export module does not support push');
try{
  echo $module->push($itemID);
} catch (Exception $e) {
    echo 'Exception while pushing item record: ',  $e->getMessage(), "\n";
}
?>
