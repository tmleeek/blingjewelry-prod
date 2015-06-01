<?php

class Grep_Minicart_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getMax()
    {
        $value = Mage::getStoreConfig('grep_minicart/settings/products');
        
        if (!$value) {
            $value = 3;
        }
        
        return $value;
    }
}
