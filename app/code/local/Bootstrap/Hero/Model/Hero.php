<?php
class Bootstrap_Hero_Model_Hero extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
		$this->_init('hero/hero');
    }
}