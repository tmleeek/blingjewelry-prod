<?php
class Loginradius_Sociallogin_Block_Verticalsharing extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    private $_loginRadiusVerticalSharing;
    public function __construct()
    {
        $this->_loginRadiusVerticalSharing = new Loginradius_Sociallogin_Block_Sociallogin();
    }
    protected function _toHtml()
    {
        $content = "";
        if ($this->_loginRadiusVerticalSharing->verticalShareEnable() == "1" ) {
            $content = "<div class='_loginRadiusVerticalSharing'></div>";
        }
        return $content;
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }
}