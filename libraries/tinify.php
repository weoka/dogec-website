<?php
ini_set('max_execution_time', 1800); // 30 mins
ini_set('memory_limit','2048M');

include('../inc_functions.php');


/*
============================================
Tinify
============================================
*/

// Parameters
$strFile = req('file', true, 'get');
$strPath = req('path', true, 'get', 'images/');
$strOver = req('override', true, 'get', false);

$strPath = $strPath . '*.*';


// Process
tinify($strFile, $strPath, true, $strOver);
?>