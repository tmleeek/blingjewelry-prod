<?php
/**
 * Class to add reliable sub-product retrieval to magento api
 */

class ElevenMain_Catalog_Model_Product_Api extends Mage_Catalog_Model_Product_Api {
	/**
	 * Retrieve list of products with basic info (id, sku, type, set, name)
	 *
	 * @param null|object|array $filters
	 * @param string|int $store
	 * @return array
	 */
	public function items($filters = null, $store = null)
	{
		$collection = Mage::getModel('catalog/product')->getCollection()
			->addStoreFilter($this->_getStoreId($store))
			->addAttributeToSelect('name')
			->setOrder('product_id', 'ASC');

		/** @var $apiHelper Mage_Api_Helper_Data */
		$apiHelper = Mage::helper('api');

		// Hack in a filter to toggle showing details in the list
		if ( ! is_null($filters)) {
			$showDetails = (array_key_exists('show_details', $filters)) ? $filters['show_details'] : 'TRUE';
			unset($filters['show_details']);
		} else {
			$showDetails = 'TRUE';
		}

		$filters = $apiHelper->parseFilters($filters, $this->_filtersMap);

		try {
			foreach ($filters as $field => $value) {
				if ($field === 'limit') {
					$collection->getSelect()->limit((int) $value);
				} else {
					$collection->addFieldToFilter($field, $value);
				}
			}
		} catch (Mage_Core_Exception $e) {
			$this->_fault('filters_invalid', $e->getMessage());
		}

		$result = array();
		foreach ($collection as $product) {
			if (strtoupper($showDetails) === 'TRUE') {
				$result[] = array_merge(array(
					'product_id' => $product->getId(),
					'sku'		=> $product->getSku(),
					'name'	   => $product->getName(),
					'set'		=> $product->getAttributeSetId(),
					'type'	   => $product->getTypeId(),
					'category_ids' => $product->getCategoryIds(),
					'website_ids'  => $product->getWebsiteIds(),
				), $this->info($product->getId(), $store));
			} else {
				// Get child skus
				$childSkus = array();
				$childProductModel = Mage::getModel('catalog/product_type_configurable');
				$childProductsCollection = $childProductModel->getUsedProductCollection($product);

				foreach($childProductsCollection as $child) {
					$childSkus[$child->getId()] = $child->getSku();
				}

				$result[] = array(
					'product_id' => $product->getId(),
					'sku'		=> $product->getSku(),
					'name'	   => $product->getName(),
					'set'		=> $product->getAttributeSetId(),
					'type'	   => $product->getTypeId(),
					'category_ids' => $product->getCategoryIds(),
					'website_ids'  => $product->getWebsiteIds(),
					'children'	 => array_values($childSkus),
					'stock' => Mage::getModel('cataloginventory/stock_item_api')->items(array($product->getId()))
				);
			}
		}
		return $result;
	}

	/**
	 * Retrieve product info
	 *
	 * @param int|string $productId
	 * @param string|int $store
	 * @param array	  $attributes
	 * @param string	 $identifierType
	 * @return array
	 */
	public function info($productId, $store = null, $attributes = null, $identifierType = null)
	{
		// make sku flag case-insensitive
		if (!empty($identifierType)) {
			$identifierType = strtolower($identifierType);
		}

		$product = $this->_getProduct($productId, $store, $identifierType);

		// Get child skus
		$childSkus = array();
		$childProductModel = Mage::getModel('catalog/product_type_configurable');
		$childProductsCollection = $childProductModel->getUsedProductCollection($product);

		foreach($childProductsCollection as $child) {
			$childSkus[$child->getId()] = $child->getSku();
		}

		$result = array( // Basic product data
			'product_id' => $product->getId(),
			'sku'		=> $product->getSku(),
			'set'		=> $product->getAttributeSetId(),
			'type'	   => $product->getTypeId(),
			'categories' => $product->getCategoryIds(),
			'websites'   => $product->getWebsiteIds(),
			'children'	 => array_values($childSkus),
			'price_map' => $this->getAttributePrice($childSkus, $product),
			'images' => Mage::getModel('catalog/product_attribute_media_api')->items($product->getId()),
			'stock' => Mage::getModel('cataloginventory/stock_item_api')->items(array($product->getId()))
		);

		foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
			if ($this->_isAllowedAttribute($attribute, $attributes)) {
				$result[$attribute->getAttributeCode()] = $product->getData(
					$attribute->getAttributeCode()
				);
			}
		}

		return $result;
	}

	private function getAttributePrice($childSkus, $product)
	{
		if (empty($childSkus)) {
			return array();
		}

		$priceMapping = array();
		$attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);

		$basePrice = $product->getFinalPrice();

		foreach($attributes as $attribute) {
			$childId = $attribute->getProductId();
			$sku = $childSkus[$childId];

			$prices = $attribute->getPrices();

			$parent_attribute = $attribute->product_attribute->getAttributeCode();

			foreach($prices as $price) {
				if ($price['is_percent']) {
					$priceMapping[$parent_attribute][$price['label']] = (float) $price['pricing_value'] * $basePrice / 100;
				} else {
					$priceMapping[$parent_attribute][$price['label']] = (float) $price['pricing_value'];
				}
			}
		}

		return $priceMapping;
	}
}
// End of Api.php