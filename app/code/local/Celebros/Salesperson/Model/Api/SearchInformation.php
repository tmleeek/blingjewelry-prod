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
class Celebros_Salesperson_Model_Api_SearchInformation
{
	var $Query;
	var $OriginalQuery;
	var $SearchProfileName;
	var $PriceFieldName;
	var $NumberOfPages;	
	var $CurrentPage;
	var $PageSize;
	var $IsDefaultPageSize;
	var $IsDefaultSearchProfileName;
	var $SkuSearchOccured;
	var $DeadEndOccurred;
	var $FirstQuestionId;
	var $SessionId;
	var $Stage;
	var $SortingOptions;

	
	Function Celebros_Salesperson_Model_Api_SearchInformation($xml_SearchInformation)
	{
		if(is_object($xml_SearchInformation))
		{
			$this->Query = $xml_SearchInformation->get_attribute("Query");
			$this->OriginalQuery = $xml_SearchInformation->get_attribute("OriginalQuery");
			$this->SearchProfileName = $xml_SearchInformation->get_attribute("SearchProfileName");
			$this->PriceFieldName = $xml_SearchInformation->get_attribute("PriceFieldName");
			$this->NumberOfPages = $xml_SearchInformation->get_attribute("NumberOfPages");
			$this->CurrentPage = $xml_SearchInformation->get_attribute("CurrentPage");
			$this->PageSize = $xml_SearchInformation->get_attribute("PageSize");
			$this->IsDefaultPageSize = $xml_SearchInformation->get_attribute("IsDefaultPageSize");
			$this->SkuSearchOccured = $xml_SearchInformation->get_attribute("SkuSearchOccured");
			$this->DeadEndOccurred = $xml_SearchInformation->get_attribute("DeadEndOccurred");
			$this->FirstQuestionId = $xml_SearchInformation->get_attribute("FirstQuestionId");
			$this->SessionId = $xml_SearchInformation->get_attribute("SessionId");
			$this->Stage = $xml_SearchInformation->get_attribute("Stage");
		
			$this->SortingOptions = Mage::getModel('salesperson/Api_SortingOptions', current($xml_SearchInformation->get_elements_by_tagname("SortingOptions")));
		}
	}	
}

?>