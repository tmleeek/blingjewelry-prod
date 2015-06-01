<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
abstract class Celebros_Salesperson_Block_Product_Abstract extends Mage_Core_Block_Template
{
    protected $_priceBlock = array();
    /**
     * Default price block
     *
     * @var string
     */
    protected $_block = 'catalog/product_price';
    protected $_priceBlockDefaultTemplate = 'catalog/product/price.phtml';
    protected $_tierPriceDefaultTemplate  = 'catalog/product/view/tierprices.phtml';
    protected $_priceBlockTypes = array();
    /**
     * Flag which allow/disallow to use link for as low as price
     *
     * @var bool
     */
    protected $_useLinkForAsLowAs = true;

    protected $_reviewsHelperBlock;

    /**
     * Default product amount per row
     *
     * @var int
     */
    protected $_defaultColumnCount = 3;

    /**
     * Product amount per row depending on custom page layout of category
     *
     * @var array
     */
    protected $_columnCountLayoutDepend = array();
    
    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp';
    
    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param Celebros_Salesperson_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        return $this->helper('salesperson/checkout_cart')->getAddUrl($product, $additional);
    }

    /**
     * Retrieve url for add product to wishlist
     *
     * @param Celebros_Salesperson_Model_Product $product
     * @return string
     */
    public function getAddToWishlistUrl($product)
    {
        return $this->helper('salesperson/wishlist')->getAddUrl($product);
    }

    /**
     * Retrieve Add Product to Compare Products List URL
     *
     * @param Celebros_Salesperson_Model_Product $product
     * @return string
     */
    public function getAddToCompareUrl($product)
    {
        return $this->helper('salesperson/product_compare')->getAddUrl($product);
    }


    protected function _getPriceBlock($productTypeId)
    {
        if (!isset($this->_priceBlock[$productTypeId])) {
            $block = $this->_block;
            if (isset($this->_priceBlockTypes[$productTypeId])) {
                if ($this->_priceBlockTypes[$productTypeId]['block'] != '') {
                    $block = $this->_priceBlockTypes[$productTypeId]['block'];
                }
            }
            $this->_priceBlock[$productTypeId] = $this->getLayout()->createBlock($block);
        }
        return $this->_priceBlock[$productTypeId];
    }

    protected function _getPriceBlockTemplate($productTypeId)
    {
        if (isset($this->_priceBlockTypes[$productTypeId])) {
            if ($this->_priceBlockTypes[$productTypeId]['template'] != '') {
                return $this->_priceBlockTypes[$productTypeId]['template'];
            }
        }
        return $this->_priceBlockDefaultTemplate;
    }

    /**
     * Prepares and returns block to render some product type
     *
     * @param string $productType
     * @return Mage_Core_Block_Template
     */
    public function _preparePriceRenderer($productType)
    {
        return $this->_getPriceBlock($productType)
            ->setTemplate($this->_getPriceBlockTemplate($productType))
            ->setUseLinkForAsLowAs($this->_useLinkForAsLowAs);
    }    
    /**
     * Returns product price block html
     *
     * @param Mage_Catalog_Model_Product $product
     * @param boolean $displayMinimalPrice
     */
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix='')
    {
    	if(key_exists($this->getMapping('id'), $product->Field)){
    		//Load the product from magento database in order to get the correct price block
    		$realProduct = Mage::getModel('catalog/product')->load($product->Field[$this->getMapping('id')]);
    		
        $type_id = $realProduct->getTypeId();
        if (method_exists(Mage::helper('catalog'), 'canApplyMsrp') &&  Mage::helper('catalog')->canApplyMsrp($realProduct)) {
            $realPriceHtml = $this->_preparePriceRenderer($type_id)
                ->setProduct($realProduct)
                ->setDisplayMinimalPrice($displayMinimalPrice)
                ->setIdSuffix($idSuffix)
                ->toHtml();
            $realProduct->setAddToCartUrl($this->getAddToCartUrl($realProduct));
            $realProduct->setRealPriceHtml($realPriceHtml);
            $type_id = $this->_mapRenderer;
        }
        
        return $this->_preparePriceRenderer($type_id)
	            ->setProduct($realProduct)
	            ->setDisplayMinimalPrice($displayMinimalPrice)
	            ->setIdSuffix($idSuffix)
	            ->toHtml();
    	}
    }

    /**
     * Adding customized price template for product type
     *
     * @param string $type
     * @param string $block
     * @param string $template
     */
    public function addPriceBlockType($type, $block = '', $template = '')
    {
        if ($type) {
            $this->_priceBlockTypes[$type] = array(
                'block' => $block,
                'template' => $template
            );
        }
    }

    /**
     * Get product reviews summary
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     * @return string
     */
    public function getReviewsSummaryHtml($product, $templateType = false, $displayIfNoReviews = false)
    {
		//$product = Mage::getSingleton('catalog/product')->load($product->getId());
        $this->_initReviewsHelperBlock();
        return $this->_reviewsHelperBlock->getSummaryHtml($product, $templateType, $displayIfNoReviews);
    }

    /**
     * Add/replace reviews summary template by type
     *
     * @param string $type
     * @param string $template
     */
    public function addReviewSummaryTemplate($type, $template)
    {
        $this->_initReviewsHelperBlock();
        $this->_reviewsHelperBlock->addTemplate($type, $template);
    }

    /**
     * Create reviews summary helper block once
     *
     */
    protected function _initReviewsHelperBlock()
    {
        if (!$this->_reviewsHelperBlock) {
            $this->_reviewsHelperBlock = $this->getLayout()->createBlock('salesperson/review_helper');
        }
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', Mage::registry('product'));
        }
        return $this->getData('product');
    }

    public function getTierPriceTemplate()
    {
        if (!$this->hasData('tier_price_template')) {
            return $this->_tierPriceDefaultTemplate;
        }

        return $this->getData('tier_price_template');
    }
    /**
     * Returns product tierprice block html
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function getTierPriceHtml($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        return $this->_getPriceBlock($product->getTypeId())
            ->setTemplate($this->getTierPriceTemplate())
            ->setProduct($product)
            ->setInGrouped($this->getProduct()->isGrouped())
            ->toHtml();
    }

    /**
     * Get tier prices (formatted)
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getTierPrices($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $prices  = $product->getFormatedTierPrice();

        $res = array();
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['price_qty'] = $price['price_qty']*1;
                if ($product->getPrice() != $product->getFinalPrice()) {
                    if ($price['price']<$product->getFinalPrice()) {
                        $price['savePercent'] = ceil(100 - (( 100/$product->getFinalPrice() ) * $price['price'] ));
                        $price['formated_price'] = Mage::app()->getStore()->formatPrice(Mage::app()->getStore()->convertPrice(Mage::helper('tax')->getPrice($product, $price['website_price'])));
                        $price['formated_price_incl_tax'] = Mage::app()->getStore()->formatPrice(Mage::app()->getStore()->convertPrice(Mage::helper('tax')->getPrice($product, $price['website_price'], true)));
                        $res[] = $price;
                    }
                }
                else {
                    if ($price['price']<$product->getPrice()) {
                        $price['savePercent'] = ceil(100 - (( 100/$product->getPrice() ) * $price['price'] ));
                        $price['formated_price'] = Mage::app()->getStore()->formatPrice(Mage::app()->getStore()->convertPrice(Mage::helper('tax')->getPrice($product, $price['website_price'])));
                        $price['formated_price_incl_tax'] = Mage::app()->getStore()->formatPrice(Mage::app()->getStore()->convertPrice(Mage::helper('tax')->getPrice($product, $price['website_price'], true)));
                        $res[] = $price;
                    }
                }
            }
        }

        return $res;
    }

    /**
     * Add all attributes and apply pricing logic to products collection
     * to get correct values in different products lists.
     * E.g. crosssells, upsells, new products, recently viewed
     *
     * @param Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _addProductAttributesAndPrices(Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection $collection)
    {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes());
    }

    /**
     * Retrieve given media attribute label or product name if no label
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $mediaAttributeCode
     *
     * @return string
     */
    public function getImageLabel($product=null, $mediaAttributeCode='image')
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        $label = $product->getData($mediaAttributeCode.'_label');
        if (empty($label)) {
            $label = $product->getName();
        }

        return $label;
    }
    
    public function getMapping($code_field = ""){
    	return $this->helper('salesperson/mapping')->getMapping($code_field);
    }
    
    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product)
    {
		return $product->Field[$this->getMapping('link')];
    }

    /**
     * Check Product has URL
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve product amount per row
     *
     * @return int
     */
    public function getColumnCount()
    {
        if (!$this->_getData('column_count')) {
            $pageLayout = $this->getPageLayout();
            if ($pageLayout && $this->getColumnCountLayoutDepend($pageLayout)) {
                $this->setData(
                    'column_count',
                    $this->getColumnCountLayoutDepend($pageLayout)
                );
            } else {
                $this->setData('column_count', $this->_defaultColumnCount);
            }
        }

        return (int)$this->_getData('column_count');
    }

    /**
     * Add row size depends on page layout
     *
     * @param string $pageLayout
     * @param int $rowSize
     * @return Mage_Catalog_Block_Product_List
     */
    public function addColumnCountLayoutDepend($pageLayout, $columnCount)
    {
        $this->_columnCountLayoutDepend[$pageLayout] = $columnCount;
        return $this;
    }

    /**
     * Remove row size depends on page layout
     *
     * @param string $pageLayout
     * @return Mage_Catalog_Block_Product_List
     */
    public function removeColumnCountLayoutDepend($pageLayout)
    {
        if (isset($this->_columnCountLayoutDepend[$pageLayout])) {
            unset($this->_columnCountLayoutDepend[$pageLayout]);
        }

        return $this;
    }

    /**
     * Retrieve row size depends on page layout
     *
     * @param string $pageLayout
     * @return int|boolean
     */
    public function getColumnCountLayoutDepend($pageLayout)
    {
        if (isset($this->_columnCountLayoutDepend[$pageLayout])) {
            return $this->_columnCountLayoutDepend[$pageLayout];
        }

        return false;
    }

    /**
     * Retrieve current page layout
     *
     * @return Varien_Object
     */
    public function getPageLayout()
    {
    	$pageLayoutHandles = Mage::getSingleton('page/config')->getPageLayoutHandles();
    	$pageLayoutHandleskeys = array_keys($pageLayoutHandles);
//    	return false;
    	switch(Mage::getStoreConfig('salesperson/display_settings/layout')){
    		case "salesperson/1column.phtml":
    			return $pageLayoutHandleskeys[1];
    			break;
    		case "salesperson/2columns-left.phtml":
    			return $pageLayoutHandleskeys[2];
    			break;
    		case "salesperson/2columns-right.phtml":
    			return $pageLayoutHandleskeys[3];
    			break;
    		case "salesperson/3columns.phtml":
    			return $pageLayoutHandleskeys[4];
    			break;
    			
    	}
        //return $this->helper('page/layout')->getCurrentPageLayout();
    }

    /**
     * If exists price template block, retrieve price blocks from it
     *
     * @return Mage_Catalog_Block_Product_Abstract
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /* @var $block Mage_Catalog_Block_Product_Price_Template */
        $block = $this->getLayout()->getBlock('catalog_product_price_template');
        if ($block) {
            foreach ($block->getPriceBlockTypes() as $type => $priceBlock) {
                $this->addPriceBlockType($type, $priceBlock['block'], $priceBlock['template']);
            }
        }

        return $this;
    }
}
