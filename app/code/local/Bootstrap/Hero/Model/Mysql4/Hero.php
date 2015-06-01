<?php
class Bootstrap_Hero_Model_Mysql4_Hero extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("hero/hero", "hero_id");
    }
}