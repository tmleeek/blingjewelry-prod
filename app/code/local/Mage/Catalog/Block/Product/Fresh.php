<?php
//Ashwani Bhasin 12-14-2012
class Mage_Catalog_Block_Product_Fresh extends Mage_Catalog_Block_Product_List
{
   function get_prod_count()
   {
      //unset any saved limits
      Mage::getSingleton('catalog/session')->unsLimitPage();
      return (isset($_REQUEST['limit'])) ? intval($_REQUEST['limit']) : 40;
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

    /*
    mmc true way to get latest 200 then sort
        $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $collection = $this->_addProductAttributesAndPrices($collection)
          ->addStoreFilter();

        $id = $collection->addAttributeToSort('entity_id', 'DESC')->setPageSize(50)->getLastItem()->getId();

        // new query
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
        $collection = $this->_addProductAttributesAndPrices($collection)
         ->addStoreFilter()
         ->addAttributeToFilter('entity_id', array('gt'=>$id))
         ->setOrder(array('mysort'), 'desc')
         ->setPageSize($this->get_prod_count())
         ->setCurPage($this->get_cur_page())
         ->setMaxSize(50); // see EAV extension
        */


      $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

      $collection = Mage::getResourceModel('catalog/product_collection');
       $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());
      $collection = $this->_addProductAttributesAndPrices($collection)
         ->addStoreFilter()
       //  ->addAttributeToSort('entity_id', 'desc')
         ->setOrder(array('mysort', 'entity_id'), 'desc') //Ashwani Bhasin 11-26-2014 added mysort as first attribute
         ->setPageSize($this->get_prod_count())
         ->setCurPage($this->get_cur_page())
         ->setMaxSize(200);
        $this->setProductCollection($collection);
        //echo $collection->getSelect();
      return $collection;
   }// _getProductCollection
}// Mage_Catalog_Block_Product_Fresh
?>

