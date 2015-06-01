<?php
class Loginradius_Sociallogin_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns whether the Enabled config variable is set to true
     *
     * @return bool
     */
    public function isSocialloginEnabled()
	{
        if (Mage::getStoreConfig('sociallogin_options/messages/enabled') == '1'){
            return true;
	    }
        return false;
    }
}