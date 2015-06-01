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
class packetizer
{
	var $SplitMethod;
	var $PacketSize;
	var $SplitID;
	var $TotalPackets;
	
	function packetizer($SplitMethod,$PacketSize)
	{
		$this->SplitMethod=$SplitMethod;
		$this->PacketSize=$PacketSize;
		$this->SplitID=uniqid(rand(), true);
	}
	
	function Split($Data)
	{
	 if($this->PacketSize<strlen($Data))
	 {
		$len=strlen($Data)/$this->PacketSize;
		$len=ceil($len);
		$this->TotalPackets=$len;
		for($i=0;$i<$len;$i++)
		{
			$splitArray[$i]=substr($Data,$i*$this->PacketSize,$this->PacketSize);
		}
		
	 }
	 else 
	 {
	 	$splitArray[0]=$Data;
	 	$this->TotalPackets=1;
	 }
	 
	 return $splitArray;
		 
	}
	
	function GetSplitMethod()
	{
		switch ($this->SplitMethod)
		{
			CASE "bySize":
				return "2";
			default:
				return "-1";//TODO	
		}
			
		
	}
	
}
?>
