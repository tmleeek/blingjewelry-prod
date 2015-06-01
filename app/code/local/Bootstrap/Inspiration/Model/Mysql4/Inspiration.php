<?php
class Bootstrap_Inspiration_Model_Mysql4_Inspiration extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("inspiration/inspiration", "inspiration_id");
    }
}