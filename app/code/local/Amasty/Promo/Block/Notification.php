<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */
class Amasty_Promo_Block_Notification extends Mage_Core_Block_Template
{
    protected function _toHtml()
    {
        $products = Mage::helper('ampromo')->getNewItems();

        if ($products){
            return parent::_toHtml();
        }
        else {
            return '';
        }
    }

    public function getMessage()
    {
        $pattern = Mage::getStoreConfig('ampromo/general/notification_text');

        $message = Mage::helper('ampromo')->processPattern($pattern);

        return $message;
    }
}
