<?php
class Loginradius_Sociallogin_Block_Horizontalsharing extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    private $_loginRadiusHorizontalSharing;
    public function __construct()
    {
        $this->_loginRadiusHorizontalSharing = new Loginradius_Sociallogin_Block_Sociallogin();
    }
    protected function _toHtml()
    {
        $content = "";
        if ($this->_loginRadiusHorizontalSharing->horizontalShareEnable() == "1" ) {
            $content = "<div class='_loginRadiusHorizontalSharing'></div>";
            $titleText = trim($this->getLabelText());
            if ($titleText != "") {
                $content = "<div style='font-weight:bold'>" . __($titleText) . "</div>" . $content;
            }
        }
        return $content;
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }
}