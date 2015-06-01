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
	/*function GetValidateMessageID($messageID)
	{	
		switch ($messageID)
		{
			CASE "NoError":
					return "0";
			CASE "evrErrSearchHandleIsMissing":
					return "1";
			CASE "evrErrSearchSessionIDIsMissing":
					return "2";
			CASE "evrErrSearchSessionIsMissing":
					return "3";
			CASE "evrErrSKUIsMissing":
					return "4";
			CASE "evrErrCartIDIsMissing":
					return "5";
			CASE "evrErrServerNameIsMissing":
					return "6";
			CASE "evrErrCustomerIDIsMissing":
					return "7";
			CASE "evrWrnCustomerNameISMissing":
					return "8";
			CASE "evrWrnConflictInDCParam":
					return "9";
			CASE "evrErrOrderIDIsMissing":
					return "10";
			CASE "evrErrLogHandleIsMissing":
					return "11";

			default:
					return "0";
		}
			
	}*/
		
	/*function GetSeverity($Severity)
	{
		switch ($Severity)
		{
			case "RequestOK":
				return "0";
			case "Warning":
				return "1";
			case "Error":
				return "2";
			
		}
	}*/
	
	class sValidateResult
	{
		var $iSeverity;
		var $strMessage;
	}
		
	
?>
