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
class ReferrerInfoHolder
{
	var $ReferrerUrl;
	var $CampaignName;
	var $CampaignType;
	
	function ToString()
	{
		$strStream = "";
		$sc; 
		
		if( trim($this->CampaignType=="") || $this->CampaignType==null)
			$this->CampaignType=0;

		$sc[]=$this->ReferrerUrl;
		$sc[]=$this->CampaignName;
		$sc[]=$this->CampaignType;
		$sc[]=$this->SearchPhrase;
			
		$strStream = GetStream($sc);
		$strStream = UUEncode($strStream);
		return $strStream;
	}
}

class GenericSRHolder
{
	var $SearchSession;
	var $Query;
	var $FromBrowse;
	
	function ToString()
	{
		$strStream = "";
		$sc; 
		
		$sc[]=$this->SearchSession;
		$sc[]=$this->Query;
		$sc[]=$this->FromBrowse;
			
		$strStream = GetStream($sc);
		$strStream = UUEncode($strStream);
		return $strStream;
	}
}

class SRAdditionalHolder
{
	var $SpellingCorrectionDriven;
	var $CorrectedFrom;
	var $FromBrowse;
	
	
	function ToString()
	{
		$strStream = "";
		$sc; 
		
		$sc[]=$this->SpellingCorrectionDriven;
		$sc[]=$this->CorrectedFrom;
		$sc[]=$this->FromBrowse;
			
		$strStream = GetStream($sc);
		$strStream = UUEncode($strStream);
		return $strStream;
	}
}

class CartInfoHolder
{
	var $CartID;
	var $Coupon;
	var $Discount;
	var $ProductCount;
	var $SubTotal;

		function ToString()
		{
			$strStream = "";
			$sc=array();
			
			if($this->ProductCount==null || trim($this->ProductCount)=="")
				$this->ProductCount=0;			
			if($this->Discount==null ||  trim($this->ProductCount==""))
				$this->Discount=0;	
			if($this->SubTotal==null ||  trim($this->SubTotal==""))
				$this->SubTotal=0;
				
			$sc[]=$this->CartID;
			$sc[]=$this->ProductCount;
			$sc[]=$this->Coupon;
			$sc[]=$this->Discount;
			$sc[]=$this->SubTotal;

			$strStream = GetStream($sc);
			$strStream = UUEncode($strStream);
			return $strStream;
		}
}

class ProductDetailsHolder
{
	var $ProductList;
	
		function  ToString()
		{
			$strStream = "";
			$sc=array();

			// add the number of products in array.
			$sc[]=count($this->ProductList);

			// add products
			foreach ($this->ProductList as $p)
			{
			if($p["Price"]==null || trim($p["Price"])=="")
				$p["Price"]=0;
			if($p["Quantity"]==null || trim($p["Quantity"])=="")
				$p["Quantity"]=0;	
									
				$scProduct[]=$p["SKU"];
				$scProduct[]=$p["Variant"];
				$scProduct[]=$p["Price"];
				$scProduct[]=$p["Quantity"];
				$scProduct[]=$p["Category"];
				$scProduct[]=$p["Name"];

				$sc[]=GetStream($scProduct);
			}
			
			$strStream = GetStream($sc);
			$strStream = UUEncode($strStream);
			//$strStream = UUEncode($sc);
			return $strStream;
		}
}

class ProductCOHolder
{
	var $ProductList;
	
		function  ToString()
		{
			$strStream = "";
			$sc=array();

			// add the number of products in array.
			$sc[]=count($this->ProductList);
			if (count($this->ProductList)>0)
			{
				// add products
				foreach ($this->ProductList as $p)
				{
				if($p["Price"]==null || trim($p["Price"])=="")
					$p["Price"]=0;
				if($p["Quantity"]==null || trim($p["Quantity"])=="")
					$p["Quantity"]=0;
				if($p["Discount"]==null || trim($p["Discount"])=="")
					$p["Discount"]=0;
														
					$scProduct[]=$p["SKU"];
					$scProduct[]=$p["Variant"];
					$scProduct[]=$p["Price"];
					$scProduct[]=$p["Quantity"];
					$scProduct[]=$p["Coupon"];
					$scProduct[]=$p["Discount"];

					$sc[]=GetStream($scProduct);
					//echo "FROM HOLDERS _____ ".var_dump($sc) . "<br>";
					$scProduct=array();	
				}
			}
			$strStream = GetStream($sc);
			$strStream = UUEncode($strStream);
			return $strStream;
		}
}

class PageInfoHolder
{
	var $Name;
	var $Url;
	var $Category;
	
	function ToString()
	{
		$sc[]=$this->Name;
		$sc[]=$this->Url;
		$sc[]=$this->Category;
		$str=GetStream($sc);
		return UUEncode($str);
	}
}


	function GetStream($arr)
	{
		 $sb = "";
			for($i=0;$i<count($arr);$i++)
			{
				$strStream = $arr[$i];
				$strStream = MakeStreamItem($strStream);
				$sb.=$strStream;
			}
			$res = MakeStreamItem($sb);
			return $res;	
	}

    function MakeStreamItem($strItem)
    {
			$res = "";

			$strLength = strlen($strItem);
			for($i=strlen($strLength);$i<4;$i++)
				$res.="0";
			$res.=$strLength;
			$res.=$strItem;

			return $res;
     }

    

?>
