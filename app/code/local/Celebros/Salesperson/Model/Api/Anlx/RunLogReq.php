<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality. 
 * If you wish to customize it, please contact Celebros.
 *
 */
//@-; This file was released in build:QANLX V1.6.0049 Date: 9/20/2006 6:32:27 PM @@@
include_once("AnalyticsFunctions.php"); 

//$sr =new AnalyticsFunctions();
//$sr->Celebros_Analytics_Visit("","","","","");



$gr =new LogRequest();
$gr->CustomerID = "yfat";
$gr->CustomerName = "yfat";
$gr->SH = "aaa";
$gr->DataCollectorIP = "1";

$gr->AddCustomProperty("yfat","test");
$gr->bFromBrowse="true";
$gr->CorrectedFrom = "yfat";
$gr->SpellingCorrectionDriven = "true";
$a = $gr->GetLogRequest("LogSearchResult");

$b = $a;

//$gr->Celebros_Analytics_GenericSearchResults("1","red","true","23","","","","");
/*$gr->AddCustomProperty("a","b");
$message = $gr->GetLastErrorMessage();
$message1 = $gr->GetLastErrorSeverity();
*/
?>
