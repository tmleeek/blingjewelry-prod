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
	function GetSRValidateResult($searchHandle, $logHandle)
	{	
		if ($searchHandle == "" || $searchHandle == null)
		{
			return "evrErrSearchSessionIDIsMissing"; 
		}
		elseif ($logHandle == "" || $logHandle == null)
		{
			return "evrErrLogHandleIsMissing"; 	
		}

		return "";
	}
	
	function GetGRValidateResult($sessionID)
	{
		if ($sessionID == "" || $sessionID == null)
		{
			return "evrErrSearchSessionIDIsMissing"; 
		}
		return "";
	}
	
	function GetPDValidateResult($sessionID,$sku)
	{
		if ($sessionID  == null || $sessionID = "")
		{
			return "evrErrSearchSessionIDIsMissing"; 
		}
		elseif ($sku == null || $sku == "")
		{
			return "evrErrSKUIsMissing"; 	
		}
		return "";
	}
	
	function GetATCValidateResult($sessionID, $sku, $cartID)
	{
		if ($sessionID  == null || $sessionID = "")
		{
			return "evrErrSearchSessionIDIsMissing"; 
		}
		elseif ($sku == null || $sku == "")
		{
			return "evrErrSKUIsMissing"; 	
		}
		elseif ($cartID == null || $cartID = "")
		{
			return "evrErrCartIDIsMissing"; 
		}
		return "";
		
	}
	
	function GetCOValidateResult($cartID, $orderID)
	{
		if ($cartID == null || $cartID = "")
		{
			return "evrErrCartIDIsMissing"; 
		}
		elseif ($orderID == null || $orderID == "")
		{
			return "evrErrOrderIDIsMissing"; 	
		}
		return "";
	}
	
?>
