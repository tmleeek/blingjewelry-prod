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
class Celebros_Salesperson_Model_Api_SortingOptions
{
	var $Ascending;
	var $FieldName;
	var $NumericSort;
	var $Method;
	
	Function Celebros_Salesperson_Model_Api_SortingOptions($xml_SortingOptions)
	{
		if(is_object($xml_SortingOptions))
		{
			$this->Ascending = $xml_SortingOptions->get_attribute("Ascending");
			$this->FieldName = $xml_SortingOptions->get_attribute("FieldName");
			$this->NumericSort = $xml_SortingOptions->get_attribute("NumericSort");
			$this->Method = $xml_SortingOptions->get_attribute("Method");
		}
	}
}
?>