<?php
/**
 * METS Export collection view script
 *
 *Outputs a zip file for collections filled with mets files for each item
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */
include_once (dirname(dirname(dirname(dirname(__FILE__)))) . '/helpers/MetsExporter.php');

$collection = get_current_record('collection');
$collectionID = $collection->id;

// header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="Collection_' . $collection->id . '.mets.xml"');

$metsExporter = new MetsExporter();

$flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
if (! isset($collectionID)) {
    $flashMessenger->addMessage('ERROR: collection ID not set', 'error');
} else {
    try {
        echo $metsExporter->exportCollection($collectionID);
    } catch (Exception $e) {
        $flashMessenger->addMessage($e->getMessage(), 'error');
    }
}
