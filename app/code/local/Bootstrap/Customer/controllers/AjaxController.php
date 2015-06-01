<?php
class Bootstrap_Customer_AjaxController extends Mage_Core_Controller_Front_Action
{
    // This controller was created specifically for the product alert functionality, using ajax in a manner that works for cached pages
    public function isLoggedInAction() {
      $session = Mage::getSingleton('customer/session');
      $alreadyLoggedin = false;
      if($session->isLoggedIn()){
          $alreadyLoggedin = true;
      }

      $jsonData = json_encode(array($alreadyLoggedin));
      $this->getResponse()->setHeader('Content-type', 'application/json');
      $this->getResponse()->setBody($jsonData);
  }
}
