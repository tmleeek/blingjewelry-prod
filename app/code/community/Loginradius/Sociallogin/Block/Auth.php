<?php
class Loginradius_Sociallogin_Block_Auth extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    private $_blockAnyplace;
    public function __construct()
    {
        $this->_blockAnyplace = new Loginradius_Sociallogin_Block_Sociallogin();
    }
    public function loginradius_buttons()
    {
        $apiKey = trim($this->_blockAnyplace->getApikey());
        $apiSecret = trim($this->_blockAnyplace->getApiSecret());
        $userAuth = $this->_blockAnyplace->getApiResult($apiKey, $apiSecret);
        $titleText = $this->getLabelText();
        $errormsg = '<p style ="color:red;">To activate your plugin, please log in to LoginRadius and get API Key & Secret. Web: <b><a href ="http://www.loginradius.com" target = "_blank">www.LoginRadius.com</a></b></p>';
        if ($this->_blockAnyplace->user_is_already_login()) {
            $userName = Mage::getSingleton('customer/session')->getCustomer()->getName();
            return '<span>'.__('Welcome').'! '.$userName .'</span>';
        } else {
            if ( $apiKey == "" && $apiSecret == "" ) {
                return $errormsg;
            } elseif ( $userAuth == false ) {
                return '<p style ="color:red;">Your LoginRadius API Key and Secret is not valid, please correct it or contact LoginRadius support at <b><a href ="http://www.loginradius.com" target = "_blank">www.LoginRadius.com</a></b></p>';
            } else {
                $isHttps = (!empty($userAuth->isHttps)) ? $userAuth->isHttps : '';
                $iframeHeight = (!empty($userAuth->height)) ? $userAuth->height : 50;
                $iframeWidth = (!empty($userAuth->width)) ? $userAuth->width : 138;
                $http = ($isHttps == 1) ? "https://" : "http://";
                $loc = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)."sociallogin/";
                if (empty($titleText)) {
                    $titleText = __('Social Login');
                }
                $label = '<span ><b>' . __($titleText) . '</b></span>';
                $iframe = '<div class="interfacecontainerdiv" style="margin-left:10px"></div>';
                return $label.$iframe;
            }
        }
    }
    protected function _toHtml()
    {
        $content = '';
        if (Mage::getSingleton('customer/session')->isLoggedIn() == false && $this->_blockAnyplace->loginEnable() == "1" ) {
            $content = $this->loginradius_buttons();
        }
        return $content;
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }
}