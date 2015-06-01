<?php
class Bootstrap_Press_Model_Mysql4_Press extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("press/press", "press_id");
    }
}