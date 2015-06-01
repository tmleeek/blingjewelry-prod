<?php
class Mage_Catalog_Block_Product_Outofstock extends Mage_Catalog_Block_Product_List
{
   function get_prod_count()
   {
      //unset any saved limits
      Mage::getSingleton('catalog/session')->unsLimitPage();
      return (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 32;
   }// get_prod_count
   function get_cur_page()
   {
      return (isset($_REQUEST['p'])) ? intval($_REQUEST['p']) : 1;
   }// get_cur_page
   /**
    * Retrieve loaded category collection
    *
    * @return Mage_Eav_Model_Entity_Collection_Abstract
   **/
   protected function _getProductCollection()
   {
		 $stockCollection = Mage::getModel('cataloginventory/stock_item')->getCollection()
        ->addFieldToFilter('is_in_stock', 0);
	/*	->addFieldToFilter('qty', 0);*/ //this can also be used to filtr
				 
$productIds = array();
            
    foreach ($stockCollection as $item) {
        $productIds[] = $item->getOrigData('product_id');
    }
        
    $productCollection = Mage::getModel('catalog/product')->getCollection();
	$productCollection = $this->_addProductAttributesAndPrices($productCollection)
        ->addIdFilter($productIds)
		->addAttributeToFilter('visibility', 4)
		
		 ->addAttributeToSort('entity_id', 'desc') //THIS WILL SHOW THE LATEST PRODUCTS FIRST
         ->setPageSize($this->get_prod_count())
         ->setCurPage($this->get_cur_page());
        $this->setProductCollection($productCollection);
        return $productCollection;
   }// _getProductCollection
}// Mage_Catalog_Block_Product_Outofstock
?>