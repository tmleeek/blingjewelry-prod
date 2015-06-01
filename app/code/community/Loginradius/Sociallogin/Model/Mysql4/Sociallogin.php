<?php
class Loginradius_Sociallogin_Model_Mysql4_Sociallogin extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('socialogin/socialogin', 'socialogin_id');
    }
}