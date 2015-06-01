<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */  
class Amasty_Xnotif_Model_Empty extends Mage_Core_Model_Abstract
{
    public function _construct()
    {    
        $this->_init('xnotif/empty');
        //$this->_initSelect();
    }
}