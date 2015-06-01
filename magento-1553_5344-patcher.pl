#!/usr/bin/perl
use strict;
use warnings;

use IPC::Open3;

print "*** Nexcess Magento SUPEE-1533 & SUPEE-5344 Patch Checker/Applier ***\n\n";

my $username = getpwuid( $< );

if ($username eq 'root') {
    die("This tool should not be run as 'root'.  Please run it as the system user whom owns the files you're trying to patch.\n\n");
}

ispatched();

sub getmagver {
    if (!-e 'app/Mage.php') {
        die("Unable to locate 'app/Mage.php', are you in a Magento installation directory?\n");
    }

    my $magphp = 'include \'app/Mage.php\'; echo Mage::getVersion();';
    my $version = `php -r "$magphp"`;

    return $version;
}

sub getmagedition {
    my $magver = shift;
    if (!-e 'app/Mage.php') {
        die("Unable to locate 'app/Mage.php', are you in a Magento installation directory?\n");
    }

    my $magphp = 'include \'app/Mage.php\'; echo Mage::getEdition();';
    my $edition = `php -r "$magphp" 2>/dev/null`;

    # Special case here.  EE 1.9 (so far) does not have the method 'getEdition()' in app/Mage.php
    # As a crude way around this, we'll try and grep out edition from its comment header if we
    # fail to find it the first way
    if ($?) {
        $edition = `grep -e "^ \\* Magento \\S\\+ Edition" app/Mage.php`;
        if ($edition) {
            chomp $edition;
            $edition =~ s/^ \* Magento //;
            $edition =~ s/ Edition$//;
        }
    }

    # Another special case.  Old CEs (1.6 and earlier, it looks like?) also don't have getEdition()
    # but they have no unique "Community" reference like how old EE's have a unique "Enterprise"
    # reference.  This is not ideal, but since EE 1.3 - EE 1.6 doesn't seem to exist, we can infer
    # it by version number if we get this far
    if (!$edition && $magver =~ /^1\.[3-6]\./) {
        $edition = 'Community';
    } 

    # If we still don't have the edition name.... I'll need to add more special cases as I find them
    if (!$edition) {
        die ("Unable to determine Magento Edition.  Please contact me and let me know so that I can investigate.\n\n");
    }

    return $edition;
}

sub check_patch {
    my $patchcontent = shift;

    open(PATCH, '| patch -p0 -R --dry-run >/dev/null 2>&1') or die("Unable to open pipe to 'patch' binary: $!");
    print PATCH $patchcontent;
    close(PATCH);

    if (!$?) {
        return 1;
    }
    else {
        return 0;
    }
}

sub apply_patch {
    my $patchcontent = shift;

    my $cmd = 'patch -p0';
    my $pid = open3(local *PATCH_IN, local *PATCH_OUT, '', $cmd);
    print PATCH_IN $patchcontent;
    close PATCH_IN;
    my @patchoutput = <PATCH_OUT>;
    close PATCH_OUT;
    waitpid($pid, 0);

    my $child_exit = $? >> 8;
    
    if (!$child_exit) {
        return @patchoutput;
    }
    else {
        return 0;
    }
}

sub reverse_patch {
    my $patchcontent = shift;

    my $cmd = 'patch -p0 -R';
    my $pid = open3(local *PATCH_IN, local *PATCH_OUT, '', $cmd);
    print PATCH_IN $patchcontent;
    close PATCH_IN;
    my @patchoutput = <PATCH_OUT>;
    close PATCH_OUT;
    waitpid($pid, 0);

    my $child_exit = $? >> 8;
    
    if (!$child_exit) {
       return @patchoutput;
    }
    else {
        return 0;
    }
}

sub ispatched {

    my $patch_1533_14_name = 'SUPEE-1533 | EE_1.9 | v1 | _ | n/a | SUPEE-1533_EE_1.9_v1.patch';
    my $patch_1533_14 = <<'END_PATCH';
diff --git app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
index 86a0645..25847dd 100644
--- app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
+++ app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
@@ -339,7 +339,7 @@ class Mage_Adminhtml_Block_Dashboard_Graph extends Mage_Adminhtml_Block_Dashboar
             }
             return self::API_URL . '?' . implode('&', $p);
         } else {
-            $gaData = urlencode(base64_encode(serialize($params)));
+            $gaData = urlencode(base64_encode(json_encode($params)));
             $gaHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             $params = array('ga' => $gaData, 'h' => $gaHash);
             return $this->getUrl('*/*/tunnel', array('_query' => $params));
diff --git app/code/core/Mage/Adminhtml/controllers/DashboardController.php app/code/core/Mage/Adminhtml/controllers/DashboardController.php
index ca0f179..34eed31 100644
--- app/code/core/Mage/Adminhtml/controllers/DashboardController.php
+++ app/code/core/Mage/Adminhtml/controllers/DashboardController.php
@@ -77,7 +77,8 @@ class Mage_Adminhtml_DashboardController extends Mage_Adminhtml_Controller_Actio
         if ($gaData && $gaHash) {
             $newHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             if ($newHash == $gaHash) {
-                if ($params = unserialize(base64_decode(urldecode($gaData)))) {
+                $params = json_decode(base64_decode(urldecode($gaData)), true);
+                if ($params) {
                     $response = $httpClient->setUri(Mage_Adminhtml_Block_Dashboard_Graph::API_URL)
                             ->setParameterGet($params)
                             ->setConfig(array('timeout' => 5))
END_PATCH

    my $patch_1533_150_name = 'SUPEE-1533 | EE_1.10.0.0 | v1 | _ | n/a | SUPEE-1533_EE_1.10.0.0_v1.patch';
    my $patch_1533_150 = <<'END_PATCH';
diff --git app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
index 86a0645..25847dd 100644
--- app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
+++ app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
@@ -339,7 +339,7 @@ class Mage_Adminhtml_Block_Dashboard_Graph extends Mage_Adminhtml_Block_Dashboar
             }
             return self::API_URL . '?' . implode('&', $p);
         } else {
-            $gaData = urlencode(base64_encode(serialize($params)));
+            $gaData = urlencode(base64_encode(json_encode($params)));
             $gaHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             $params = array('ga' => $gaData, 'h' => $gaHash);
             return $this->getUrl('*/*/tunnel', array('_query' => $params));
diff --git app/code/core/Mage/Adminhtml/controllers/DashboardController.php app/code/core/Mage/Adminhtml/controllers/DashboardController.php
index ca0f179..34eed31 100644
--- app/code/core/Mage/Adminhtml/controllers/DashboardController.php
+++ app/code/core/Mage/Adminhtml/controllers/DashboardController.php
@@ -77,7 +77,8 @@ class Mage_Adminhtml_DashboardController extends Mage_Adminhtml_Controller_Actio
         if ($gaData && $gaHash) {
             $newHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             if ($newHash == $gaHash) {
-                if ($params = unserialize(base64_decode(urldecode($gaData)))) {
+                $params = json_decode(base64_decode(urldecode($gaData)), true);
+                if ($params) {
                     $response = $httpClient->setUri(Mage_Adminhtml_Block_Dashboard_Graph::API_URL)
                             ->setParameterGet($params)
                             ->setConfig(array('timeout' => 5))
END_PATCH

    my $patch_1533_151_name = 'SUPEE-1533 | EE_1.10.1.0 | v1 | _ | n/a | SUPEE-1533_EE_1.10.1.0_v1.patch';
    my $patch_1533_151 = <<'END_PATCH';
diff --git app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
index 4813690..d5b22f1 100644
--- app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
+++ app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
@@ -437,7 +437,7 @@ class Mage_Adminhtml_Block_Dashboard_Graph extends Mage_Adminhtml_Block_Dashboar
             }
             return self::API_URL . '?' . implode('&', $p);
         } else {
-            $gaData = urlencode(base64_encode(serialize($params)));
+            $gaData = urlencode(base64_encode(json_encode($params)));
             $gaHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             $params = array('ga' => $gaData, 'h' => $gaHash);
             return $this->getUrl('*/*/tunnel', array('_query' => $params));
diff --git app/code/core/Mage/Adminhtml/controllers/DashboardController.php app/code/core/Mage/Adminhtml/controllers/DashboardController.php
index ca0f179..34eed31 100644
--- app/code/core/Mage/Adminhtml/controllers/DashboardController.php
+++ app/code/core/Mage/Adminhtml/controllers/DashboardController.php
@@ -77,7 +77,8 @@ class Mage_Adminhtml_DashboardController extends Mage_Adminhtml_Controller_Actio
         if ($gaData && $gaHash) {
             $newHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             if ($newHash == $gaHash) {
-                if ($params = unserialize(base64_decode(urldecode($gaData)))) {
+                $params = json_decode(base64_decode(urldecode($gaData)), true);
+                if ($params) {
                     $response = $httpClient->setUri(Mage_Adminhtml_Block_Dashboard_Graph::API_URL)
                             ->setParameterGet($params)
                             ->setConfig(array('timeout' => 5))
END_PATCH

    my $patch_1533_16_name = 'SUPEE-1533 | EE_1.11 | v1 | _ | n/a | SUPEE-1533_EE_1.11_v1.patch';
    my $patch_1533_16 = <<'END_PATCH';
diff --git app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
index a1c6f52..3c0b862 100644
--- app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
+++ app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
@@ -444,7 +444,7 @@ class Mage_Adminhtml_Block_Dashboard_Graph extends Mage_Adminhtml_Block_Dashboar
             }
             return self::API_URL . '?' . implode('&', $p);
         } else {
-            $gaData = urlencode(base64_encode(serialize($params)));
+            $gaData = urlencode(base64_encode(json_encode($params)));
             $gaHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             $params = array('ga' => $gaData, 'h' => $gaHash);
             return $this->getUrl('*/*/tunnel', array('_query' => $params));
diff --git app/code/core/Mage/Adminhtml/controllers/DashboardController.php app/code/core/Mage/Adminhtml/controllers/DashboardController.php
index 5c49d2e..cb86404 100644
--- app/code/core/Mage/Adminhtml/controllers/DashboardController.php
+++ app/code/core/Mage/Adminhtml/controllers/DashboardController.php
@@ -77,7 +77,8 @@ class Mage_Adminhtml_DashboardController extends Mage_Adminhtml_Controller_Actio
         if ($gaData && $gaHash) {
             $newHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             if ($newHash == $gaHash) {
-                if ($params = unserialize(base64_decode(urldecode($gaData)))) {
+                $params = json_decode(base64_decode(urldecode($gaData)), true);
+                if ($params) {
                     $response = $httpClient->setUri(Mage_Adminhtml_Block_Dashboard_Graph::API_URL)
                             ->setParameterGet($params)
                             ->setConfig(array('timeout' => 5))
END_PATCH

    my $patch_1533_17_name = 'SUPEE-1533 | EE_1.12 | v1 | _ | n/a | SUPEE-1533_EE_1.12_v1.patch';
    my $patch_1533_17 = <<'END_PATCH';
diff --git app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
index c698108..6e256bb 100644
--- app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
+++ app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
@@ -444,7 +444,7 @@ class Mage_Adminhtml_Block_Dashboard_Graph extends Mage_Adminhtml_Block_Dashboar
             }
             return self::API_URL . '?' . implode('&', $p);
         } else {
-            $gaData = urlencode(base64_encode(serialize($params)));
+            $gaData = urlencode(base64_encode(json_encode($params)));
             $gaHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             $params = array('ga' => $gaData, 'h' => $gaHash);
             return $this->getUrl('*/*/tunnel', array('_query' => $params));
diff --git app/code/core/Mage/Adminhtml/controllers/DashboardController.php app/code/core/Mage/Adminhtml/controllers/DashboardController.php
index eebb471..f9cb8d2 100644
--- app/code/core/Mage/Adminhtml/controllers/DashboardController.php
+++ app/code/core/Mage/Adminhtml/controllers/DashboardController.php
@@ -92,7 +92,8 @@ class Mage_Adminhtml_DashboardController extends Mage_Adminhtml_Controller_Actio
         if ($gaData && $gaHash) {
             $newHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             if ($newHash == $gaHash) {
-                if ($params = unserialize(base64_decode(urldecode($gaData)))) {
+                $params = json_decode(base64_decode(urldecode($gaData)), true);
+                if ($params) {
                     $response = $httpClient->setUri(Mage_Adminhtml_Block_Dashboard_Graph::API_URL)
                             ->setParameterGet($params)
                             ->setConfig(array('timeout' => 5))
END_PATCH

    my $patch_1533_1819_name = 'SUPEE-1533 | EE_1.13 | v1 | _ | n/a | SUPEE-1533_EE_1.13_v1.patch';
    my $patch_1533_1819 = <<'END_PATCH';
diff --git app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
index c698108..6e256bb 100644
--- app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
+++ app/code/core/Mage/Adminhtml/Block/Dashboard/Graph.php
@@ -444,7 +444,7 @@ class Mage_Adminhtml_Block_Dashboard_Graph extends Mage_Adminhtml_Block_Dashboar
             }
             return self::API_URL . '?' . implode('&', $p);
         } else {
-            $gaData = urlencode(base64_encode(serialize($params)));
+            $gaData = urlencode(base64_encode(json_encode($params)));
             $gaHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             $params = array('ga' => $gaData, 'h' => $gaHash);
             return $this->getUrl('*/*/tunnel', array('_query' => $params));
diff --git app/code/core/Mage/Adminhtml/controllers/DashboardController.php app/code/core/Mage/Adminhtml/controllers/DashboardController.php
index eebb471..f9cb8d2 100644
--- app/code/core/Mage/Adminhtml/controllers/DashboardController.php
+++ app/code/core/Mage/Adminhtml/controllers/DashboardController.php
@@ -92,7 +92,8 @@ class Mage_Adminhtml_DashboardController extends Mage_Adminhtml_Controller_Actio
         if ($gaData && $gaHash) {
             $newHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
             if ($newHash == $gaHash) {
-                if ($params = unserialize(base64_decode(urldecode($gaData)))) {
+                $params = json_decode(base64_decode(urldecode($gaData)), true);
+                if ($params) {
                     $response = $httpClient->setUri(Mage_Adminhtml_Block_Dashboard_Graph::API_URL)
                             ->setParameterGet($params)
                             ->setConfig(array('timeout' => 5))
END_PATCH

    my $patch_5344_13_name = 'SUPEE-5344 | EE_1.7.0.0 | v1 | ? | Wed Feb 11 15:23:31 2015 +0000 | NuBlue Unofficial Patch';
    my $patch_5344_13 = <<'END_PATCH';
diff -crB app/code/core/Mage/Admin/Model/Observer.php app/code/core/Mage/Admin/Model/Observer.php
*** app/code/core/Mage/Admin/Model/Observer.php	2009-07-23 06:27:17.000000000 +0100
--- app/code/core/Mage/Admin/Model/Observer.php	2015-02-11 15:23:31.000000000 +0000
***************
*** 37,42 ****
--- 37,45 ----
      {
          $session  = Mage::getSingleton('admin/session');
          /* @var $session Mage_Admin_Model_Session */
+         /**
+          * @var $request Mage_Core_Controller_Request_Http
+          */
          $request = Mage::app()->getRequest();
          $user = $session->getUser();
  
***************
*** 55,61 ****
                      $user = $session->login($username, $password, $request);
                      $request->setPost('login', null);
                  }
!                 if (!$request->getParam('forwarded')) {
                      if ($request->getParam('isIframe')) {
                          $request->setParam('forwarded', true)
                              ->setControllerName('index')
--- 58,65 ----
                      $user = $session->login($username, $password, $request);
                      $request->setPost('login', null);
                  }
!                 if (!$request->getInternallyForwarded()) {
!                     $request->setInternallyForwarded();
                      if ($request->getParam('isIframe')) {
                          $request->setParam('forwarded', true)
                              ->setControllerName('index')
diff -crB app/code/core/Mage/Core/Controller/Request/Http.php app/code/core/Mage/Core/Controller/Request/Http.php
*** app/code/core/Mage/Core/Controller/Request/Http.php	2009-07-23 06:28:42.000000000 +0100
--- app/code/core/Mage/Core/Controller/Request/Http.php	2015-02-11 15:24:37.000000000 +0000
***************
*** 56,61 ****
--- 56,68 ----
  
      protected $_directFrontNames = array();
      protected $_controllerModule = null;
+     
+     /**
+      * Flag for recognizing if request internally forwarded
+      *
+      * @var bool
+      */
+     protected $_internallyForwarded = false;
  
      public function __construct($uri = null)
      {
***************
*** 391,394 ****
--- 398,423 ----
          }
          return $this->getActionName();
      }
+     
+     /**
+      * Define that request was forwarded internally
+      *
+      * @param boolean $flag
+      * @return Mage_Core_Controller_Request_Http
+      */
+     public function setInternallyForwarded($flag = true)
+     {
+     	$this->_internallyForwarded = (bool)$flag;
+     	return $this;
+     }
+     
+     /**
+      * Checks if request was forwarded internally
+      *
+      * @return bool
+      */
+     public function getInternallyForwarded()
+     {
+     	return $this->_internallyForwarded;
+     }
  }
\ No newline at end of file
END_PATCH

    my $patch_5344_140150_name = 'SUPEE-5388 | EE_1.8.0.0 | v1 | 488a0787fd5dc50378ed96f643faa2eabeb39c21 | Wed Feb 11 13:47:07 2015 +0200 | v1.8.0.0..HEAD';
    my $patch_5344_140150 = <<'END_PATCH';
diff --git app/code/core/Mage/Admin/Model/Observer.php app/code/core/Mage/Admin/Model/Observer.php
index 1b436a2..d26eeaa 100644
--- app/code/core/Mage/Admin/Model/Observer.php
+++ app/code/core/Mage/Admin/Model/Observer.php
@@ -37,6 +37,10 @@ class Mage_Admin_Model_Observer
     {
         $session  = Mage::getSingleton('admin/session');
         /* @var $session Mage_Admin_Model_Session */
+
+        /**
+         * @var $request Mage_Core_Controller_Request_Http
+         */
         $request = Mage::app()->getRequest();
         $user = $session->getUser();
 
@@ -44,7 +48,7 @@ class Mage_Admin_Model_Observer
             $request->setDispatched(true);
         }
         else {
-            if($user) {
+            if ($user) {
                 $user->reload();
             }
             if (!$user || !$user->getId()) {
@@ -55,14 +59,15 @@ class Mage_Admin_Model_Observer
                     $user = $session->login($username, $password, $request);
                     $request->setPost('login', null);
                 }
-                if (!$request->getParam('forwarded')) {
+                if (!$request->getInternallyForwarded()) {
+                    $request->setInternallyForwarded();
                     if ($request->getParam('isIframe')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedIframe')
                             ->setDispatched(false);
                     }
-                    elseif($request->getParam('isAjax')) {
+                    elseif ($request->getParam('isAjax')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedJson')
diff --git app/code/core/Mage/Core/Controller/Request/Http.php app/code/core/Mage/Core/Controller/Request/Http.php
index d5e8597..35db9f8 100644
--- app/code/core/Mage/Core/Controller/Request/Http.php
+++ app/code/core/Mage/Core/Controller/Request/Http.php
@@ -37,6 +37,13 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
     const XML_NODE_DIRECT_FRONT_NAMES = 'global/request/direct_front_name';
 
     /**
+     * Flag for recognizing if request internally forwarded
+     *
+     * @var bool
+     */
+    protected $_internallyForwarded = false;
+
+    /**
      * ORIGINAL_PATH_INFO
      * @var string
      */
@@ -459,4 +466,26 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
         }
         return $this->_isStraight;
     }
+
+    /**
+     * Define that request was forwarded internally
+     *
+     * @param boolean $flag
+     * @return Mage_Core_Controller_Request_Http
+     */
+    public function setInternallyForwarded($flag = true)
+    {
+        $this->_internallyForwarded = (bool)$flag;
+        return $this;
+    }
+
+    /**
+     * Checks if request was forwarded internally
+     *
+     * @return bool
+     */
+    public function getInternallyForwarded()
+    {
+        return $this->_internallyForwarded;
+    }
 }
diff --git lib/Varien/Data/Collection/Db.php lib/Varien/Data/Collection/Db.php
index dbe6162..b4e6c4b 100644
--- lib/Varien/Data/Collection/Db.php
+++ lib/Varien/Data/Collection/Db.php
@@ -421,9 +421,6 @@ class Varien_Data_Collection_Db extends Varien_Data_Collection
 
         $sql = '';
         $fieldName = $this->_getConditionFieldName($fieldName);
-        if (is_array($condition) && isset($condition['field_expr'])) {
-            $fieldName = str_replace('#?', $this->getConnection()->quoteIdentifier($fieldName), $condition['field_expr']);
-        }
         if (is_array($condition)) {
             if (isset($condition['from']) || isset($condition['to'])) {
                 if (isset($condition['from'])) {
END_PATCH

    my $patch_5344_151_name = 'SUPEE-5390 | EE_1.10.1.0 | v1 | 6b4151716e436e74d74f2055faa35b8741048bba | Wed Feb 11 14:03:25 2015 +0200 | v1.10.1.0..HEAD';
    my $patch_5344_151 = <<'END_PATCH';
diff --git app/code/core/Mage/Admin/Model/Observer.php app/code/core/Mage/Admin/Model/Observer.php
index 9379239..c39747f 100644
--- app/code/core/Mage/Admin/Model/Observer.php
+++ app/code/core/Mage/Admin/Model/Observer.php
@@ -37,6 +37,10 @@ class Mage_Admin_Model_Observer
     {
         $session  = Mage::getSingleton('admin/session');
         /* @var $session Mage_Admin_Model_Session */
+
+        /**
+         * @var $request Mage_Core_Controller_Request_Http
+         */
         $request = Mage::app()->getRequest();
         $user = $session->getUser();
 
@@ -44,7 +48,7 @@ class Mage_Admin_Model_Observer
             $request->setDispatched(true);
         }
         else {
-            if($user) {
+            if ($user) {
                 $user->reload();
             }
             if (!$user || !$user->getId()) {
@@ -55,14 +59,15 @@ class Mage_Admin_Model_Observer
                     $user = $session->login($username, $password, $request);
                     $request->setPost('login', null);
                 }
-                if (!$request->getParam('forwarded')) {
+                if (!$request->getInternallyForwarded()) {
+                    $request->setInternallyForwarded();
                     if ($request->getParam('isIframe')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedIframe')
                             ->setDispatched(false);
                     }
-                    elseif($request->getParam('isAjax')) {
+                    elseif ($request->getParam('isAjax')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedJson')
diff --git app/code/core/Mage/Core/Controller/Request/Http.php app/code/core/Mage/Core/Controller/Request/Http.php
index be8dc60..a86afdb 100644
--- app/code/core/Mage/Core/Controller/Request/Http.php
+++ app/code/core/Mage/Core/Controller/Request/Http.php
@@ -38,6 +38,13 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
     const DEFAULT_HTTP_PORT = 80;
 
     /**
+     * Flag for recognizing if request internally forwarded
+     *
+     * @var bool
+     */
+    protected $_internallyForwarded = false;
+
+    /**
      * ORIGINAL_PATH_INFO
      * @var string
      */
@@ -529,4 +536,26 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
         }
         return false;
     }
+
+    /**
+     * Define that request was forwarded internally
+     *
+     * @param boolean $flag
+     * @return Mage_Core_Controller_Request_Http
+     */
+    public function setInternallyForwarded($flag = true)
+    {
+        $this->_internallyForwarded = (bool)$flag;
+        return $this;
+    }
+
+    /**
+     * Checks if request was forwarded internally
+     *
+     * @return bool
+     */
+    public function getInternallyForwarded()
+    {
+        return $this->_internallyForwarded;
+    }
 }
diff --git lib/Varien/Data/Collection/Db.php lib/Varien/Data/Collection/Db.php
index d779f5b..b403c94 100644
--- lib/Varien/Data/Collection/Db.php
+++ lib/Varien/Data/Collection/Db.php
@@ -442,13 +442,6 @@ class Varien_Data_Collection_Db extends Varien_Data_Collection
 
         $sql = '';
         $fieldName = $this->_getConditionFieldName($fieldName);
-        if (is_array($condition) && isset($condition['field_expr'])) {
-            $fieldName = str_replace(
-                '#?',
-                $this->getConnection()->quoteIdentifier($fieldName),
-                $condition['field_expr']
-            );
-        }
         if (is_array($condition)) {
             if (isset($condition['from']) || isset($condition['to'])) {
                 if (isset($condition['from'])) {
END_PATCH

    my $patch_5344_160_name = 'SUPEE-5341 | EE_1.11.0.0 | v1 | f2b879155e454a19b4c22d109139e4af36e20603 | Thu Feb 5 19:29:29 2015 +0200 | v1.11.0.0..HEAD';
    my $patch_5344_160 = <<'END_PATCH';
diff --git app/code/core/Mage/Admin/Model/Observer.php app/code/core/Mage/Admin/Model/Observer.php
index 23e243a..f22eba1 100644
--- app/code/core/Mage/Admin/Model/Observer.php
+++ app/code/core/Mage/Admin/Model/Observer.php
@@ -37,6 +37,10 @@ class Mage_Admin_Model_Observer
     {
         $session  = Mage::getSingleton('admin/session');
         /* @var $session Mage_Admin_Model_Session */
+
+        /**
+         * @var $request Mage_Core_Controller_Request_Http
+         */
         $request = Mage::app()->getRequest();
         $user = $session->getUser();
 
@@ -44,7 +48,7 @@ class Mage_Admin_Model_Observer
             $request->setDispatched(true);
         }
         else {
-            if($user) {
+            if ($user) {
                 $user->reload();
             }
             if (!$user || !$user->getId()) {
@@ -55,14 +59,15 @@ class Mage_Admin_Model_Observer
                     $user = $session->login($username, $password, $request);
                     $request->setPost('login', null);
                 }
-                if (!$request->getParam('forwarded')) {
+                if (!$request->getInternallyForwarded()) {
+                    $request->setInternallyForwarded();
                     if ($request->getParam('isIframe')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedIframe')
                             ->setDispatched(false);
                     }
-                    elseif($request->getParam('isAjax')) {
+                    elseif ($request->getParam('isAjax')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedJson')
diff --git app/code/core/Mage/Core/Controller/Request/Http.php app/code/core/Mage/Core/Controller/Request/Http.php
index 368f392..951b8f0 100644
--- app/code/core/Mage/Core/Controller/Request/Http.php
+++ app/code/core/Mage/Core/Controller/Request/Http.php
@@ -76,6 +76,13 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
     protected $_beforeForwardInfo = array();
 
     /**
+     * Flag for recognizing if request internally forwarded
+     *
+     * @var bool
+     */
+    protected $_internallyForwarded = false;
+
+    /**
      * Returns ORIGINAL_PATH_INFO.
      * This value is calculated instead of reading PATH_INFO
      * directly from $_SERVER due to cross-platform differences.
@@ -530,4 +537,26 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
         }
         return false;
     }
+
+    /**
+     * Define that request was forwarded internally
+     *
+     * @param boolean $flag
+     * @return Mage_Core_Controller_Request_Http
+     */
+    public function setInternallyForwarded($flag = true)
+    {
+        $this->_internallyForwarded = (bool)$flag;
+        return $this;
+    }
+
+    /**
+     * Checks if request was forwarded internally
+     *
+     * @return bool
+     */
+    public function getInternallyForwarded()
+    {
+        return $this->_internallyForwarded;
+    }
 }
diff --git lib/Varien/Db/Adapter/Pdo/Mysql.php lib/Varien/Db/Adapter/Pdo/Mysql.php
index 0e5d4e6..8f16141 100644
--- lib/Varien/Db/Adapter/Pdo/Mysql.php
+++ lib/Varien/Db/Adapter/Pdo/Mysql.php
@@ -2595,10 +2595,6 @@ class Varien_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql implements V
 
         $query = '';
         if (is_array($condition)) {
-            if (isset($condition['field_expr'])) {
-                $fieldName = str_replace('#?', $this->quoteIdentifier($fieldName), $condition['field_expr']);
-                unset($condition['field_expr']);
-            }
             $key = key(array_intersect_key($condition, $conditionKeyMap));
 
             if (isset($condition['from']) || isset($condition['to'])) {
END_PATCH

    my $patch_5344_161162_name = 'SUPEE-5346 | EE_1.11.1.0 | v1 | 08e4b6cd424a9603d24d16cf8b57e11301fa8528 | Thu Feb 5 20:06:56 2015 +0200 | v1.11.1.0..HEAD';
    my $patch_5344_161162 = <<'END_PATCH';
diff --git app/code/core/Mage/Admin/Model/Observer.php app/code/core/Mage/Admin/Model/Observer.php
index cb2aa4f..ef3020c 100644
--- app/code/core/Mage/Admin/Model/Observer.php
+++ app/code/core/Mage/Admin/Model/Observer.php
@@ -43,6 +43,10 @@ class Mage_Admin_Model_Observer
     {
         $session = Mage::getSingleton('admin/session');
         /** @var $session Mage_Admin_Model_Session */
+
+        /**
+         * @var $request Mage_Core_Controller_Request_Http
+         */
         $request = Mage::app()->getRequest();
         $user = $session->getUser();
 
@@ -56,7 +60,7 @@ class Mage_Admin_Model_Observer
         if (in_array($requestedActionName, $openActions)) {
             $request->setDispatched(true);
         } else {
-            if($user) {
+            if ($user) {
                 $user->reload();
             }
             if (!$user || !$user->getId()) {
@@ -67,13 +71,14 @@ class Mage_Admin_Model_Observer
                     $user = $session->login($username, $password, $request);
                     $request->setPost('login', null);
                 }
-                if (!$request->getParam('forwarded')) {
+                if (!$request->getInternallyForwarded()) {
+                    $request->setInternallyForwarded();
                     if ($request->getParam('isIframe')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedIframe')
                             ->setDispatched(false);
-                    } elseif($request->getParam('isAjax')) {
+                    } elseif ($request->getParam('isAjax')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedJson')
diff --git app/code/core/Mage/Core/Controller/Request/Http.php app/code/core/Mage/Core/Controller/Request/Http.php
index 368f392..123e89e 100644
--- app/code/core/Mage/Core/Controller/Request/Http.php
+++ app/code/core/Mage/Core/Controller/Request/Http.php
@@ -76,6 +76,13 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
     protected $_beforeForwardInfo = array();
 
     /**
+     * Flag for recognizing if request internally forwarded
+     *
+     * @var bool
+     */
+    protected $_internallyForwarded = false;
+
+    /**
      * Returns ORIGINAL_PATH_INFO.
      * This value is calculated instead of reading PATH_INFO
      * directly from $_SERVER due to cross-platform differences.
@@ -530,4 +537,27 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
         }
         return false;
     }
+
+    /**
+     * Define that request was forwarded internally
+     *
+     * @param boolean $flag
+     * @return Mage_Core_Controller_Request_Http
+     */
+    public function setInternallyForwarded($flag = true)
+    {
+        $this->_internallyForwarded = (bool)$flag;
+        return $this;
+    }
+
+    /**
+     * Checks if request was forwarded internally
+     *
+     * @return bool
+     */
+    public function getInternallyForwarded()
+    {
+        return $this->_internallyForwarded;
+    }
+
 }
diff --git lib/Varien/Db/Adapter/Pdo/Mysql.php lib/Varien/Db/Adapter/Pdo/Mysql.php
index 7b903df..a688695 100644
--- lib/Varien/Db/Adapter/Pdo/Mysql.php
+++ lib/Varien/Db/Adapter/Pdo/Mysql.php
@@ -2651,10 +2651,6 @@ class Varien_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql implements V
 
         $query = '';
         if (is_array($condition)) {
-            if (isset($condition['field_expr'])) {
-                $fieldName = str_replace('#?', $this->quoteIdentifier($fieldName), $condition['field_expr']);
-                unset($condition['field_expr']);
-            }
             $key = key(array_intersect_key($condition, $conditionKeyMap));
 
             if (isset($condition['from']) || isset($condition['to'])) {
END_PATCH

    my $patch_5344_17_name = 'SUPEE-5345 | EE_1.12.0.2 | v1 | 2d36f61cf684ed26286b6d10307fcb99dd47ff02 | Thu Feb 5 19:39:01 2015 +0200 | v1.12.0.2..HEAD';
    my $patch_5344_17 = <<'END_PATCH';
diff --git app/code/core/Mage/Admin/Model/Observer.php app/code/core/Mage/Admin/Model/Observer.php
index 2544df4..56411af 100644
--- app/code/core/Mage/Admin/Model/Observer.php
+++ app/code/core/Mage/Admin/Model/Observer.php
@@ -44,6 +44,10 @@ class Mage_Admin_Model_Observer
     {
         $session = Mage::getSingleton('admin/session');
         /** @var $session Mage_Admin_Model_Session */
+
+        /**
+         * @var $request Mage_Core_Controller_Request_Http
+         */
         $request = Mage::app()->getRequest();
         $user = $session->getUser();
 
@@ -58,7 +62,7 @@ class Mage_Admin_Model_Observer
         if (in_array($requestedActionName, $openActions)) {
             $request->setDispatched(true);
         } else {
-            if($user) {
+            if ($user) {
                 $user->reload();
             }
             if (!$user || !$user->getId()) {
@@ -69,13 +73,14 @@ class Mage_Admin_Model_Observer
                     $session->login($username, $password, $request);
                     $request->setPost('login', null);
                 }
-                if (!$request->getParam('forwarded')) {
+                if (!$request->getInternallyForwarded()) {
+                    $request->setInternallyForwarded();
                     if ($request->getParam('isIframe')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedIframe')
                             ->setDispatched(false);
-                    } elseif($request->getParam('isAjax')) {
+                    } elseif ($request->getParam('isAjax')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedJson')
diff --git app/code/core/Mage/Core/Controller/Request/Http.php app/code/core/Mage/Core/Controller/Request/Http.php
index a7e8dbf..699e74b 100644
--- app/code/core/Mage/Core/Controller/Request/Http.php
+++ app/code/core/Mage/Core/Controller/Request/Http.php
@@ -76,6 +76,13 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
     protected $_beforeForwardInfo = array();
 
     /**
+     * Flag for recognizing if request internally forwarded
+     *
+     * @var bool
+     */
+    protected $_internallyForwarded = false;
+
+    /**
      * Returns ORIGINAL_PATH_INFO.
      * This value is calculated instead of reading PATH_INFO
      * directly from $_SERVER due to cross-platform differences.
@@ -530,4 +537,26 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
         }
         return false;
     }
+
+    /**
+     * Define that request was forwarded internally
+     *
+     * @param boolean $flag
+     * @return Mage_Core_Controller_Request_Http
+     */
+    public function setInternallyForwarded($flag = true)
+    {
+        $this->_internallyForwarded = (bool)$flag;
+        return $this;
+    }
+
+    /**
+     * Checks if request was forwarded internally
+     *
+     * @return bool
+     */
+    public function getInternallyForwarded()
+    {
+        return $this->_internallyForwarded;
+    }
 }
diff --git app/code/core/Mage/Oauth/controllers/Adminhtml/Oauth/AuthorizeController.php app/code/core/Mage/Oauth/controllers/Adminhtml/Oauth/AuthorizeController.php
index a7f4655..eb1adf4 100644
--- app/code/core/Mage/Oauth/controllers/Adminhtml/Oauth/AuthorizeController.php
+++ app/code/core/Mage/Oauth/controllers/Adminhtml/Oauth/AuthorizeController.php
@@ -55,7 +55,7 @@ class Mage_Oauth_Adminhtml_Oauth_AuthorizeController extends Mage_Adminhtml_Cont
      */
     public function preDispatch()
     {
-        $this->getRequest()->setParam('forwarded', true);
+        Mage::app()->getRequest()->setInternallyForwarded();
 
         // check login data before it set null in Mage_Admin_Model_Observer::actionPreDispatchAdmin
         $loginError = $this->_checkLoginIsEmpty();
diff --git lib/Varien/Db/Adapter/Pdo/Mysql.php lib/Varien/Db/Adapter/Pdo/Mysql.php
index 80ed9b5..a3d5924 100644
--- lib/Varien/Db/Adapter/Pdo/Mysql.php
+++ lib/Varien/Db/Adapter/Pdo/Mysql.php
@@ -2672,10 +2672,6 @@ class Varien_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql implements V
 
         $query = '';
         if (is_array($condition)) {
-            if (isset($condition['field_expr'])) {
-                $fieldName = str_replace('#?', $this->quoteIdentifier($fieldName), $condition['field_expr']);
-                unset($condition['field_expr']);
-            }
             $key = key(array_intersect_key($condition, $conditionKeyMap));
 
             if (isset($condition['from']) || isset($condition['to'])) {
END_PATCH

    my $patch_5344_1819_name = 'SUPEE-5344 | EE_1.14.1.0 | v1 | a5c9abcb6a387aabd6b33ebcb79f6b7a97bbde77 | Thu Feb 5 19:14:49 2015 +0200 | v1.14.1.0..HEAD';
    my $patch_5344_1819 = <<'END_PATCH';
diff --git app/code/core/Mage/Admin/Model/Observer.php app/code/core/Mage/Admin/Model/Observer.php
index bd00181..6a5281c 100644
--- app/code/core/Mage/Admin/Model/Observer.php
+++ app/code/core/Mage/Admin/Model/Observer.php
@@ -44,6 +44,10 @@ class Mage_Admin_Model_Observer
     {
         $session = Mage::getSingleton('admin/session');
         /** @var $session Mage_Admin_Model_Session */
+
+        /**
+         * @var $request Mage_Core_Controller_Request_Http
+         */
         $request = Mage::app()->getRequest();
         $user = $session->getUser();
 
@@ -58,7 +62,7 @@ class Mage_Admin_Model_Observer
         if (in_array($requestedActionName, $openActions)) {
             $request->setDispatched(true);
         } else {
-            if($user) {
+            if ($user) {
                 $user->reload();
             }
             if (!$user || !$user->getId()) {
@@ -69,13 +73,14 @@ class Mage_Admin_Model_Observer
                     $session->login($username, $password, $request);
                     $request->setPost('login', null);
                 }
-                if (!$request->getParam('forwarded')) {
+                if (!$request->getInternallyForwarded()) {
+                    $request->setInternallyForwarded();
                     if ($request->getParam('isIframe')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedIframe')
                             ->setDispatched(false);
-                    } elseif($request->getParam('isAjax')) {
+                    } elseif ($request->getParam('isAjax')) {
                         $request->setParam('forwarded', true)
                             ->setControllerName('index')
                             ->setActionName('deniedJson')
diff --git app/code/core/Mage/Core/Controller/Request/Http.php app/code/core/Mage/Core/Controller/Request/Http.php
index 6513db9..31eb6d6 100644
--- app/code/core/Mage/Core/Controller/Request/Http.php
+++ app/code/core/Mage/Core/Controller/Request/Http.php
@@ -76,6 +76,13 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
     protected $_beforeForwardInfo = array();
 
     /**
+     * Flag for recognizing if request internally forwarded
+     *
+     * @var bool
+     */
+    protected $_internallyForwarded = false;
+
+    /**
      * Returns ORIGINAL_PATH_INFO.
      * This value is calculated instead of reading PATH_INFO
      * directly from $_SERVER due to cross-platform differences.
@@ -534,4 +541,26 @@ class Mage_Core_Controller_Request_Http extends Zend_Controller_Request_Http
         }
         return false;
     }
+
+    /**
+     * Define that request was forwarded internally
+     *
+     * @param boolean $flag
+     * @return Mage_Core_Controller_Request_Http
+     */
+    public function setInternallyForwarded($flag = true)
+    {
+        $this->_internallyForwarded = (bool)$flag;
+        return $this;
+    }
+
+    /**
+     * Checks if request was forwarded internally
+     *
+     * @return bool
+     */
+    public function getInternallyForwarded()
+    {
+        return $this->_internallyForwarded;
+    }
 }
diff --git app/code/core/Mage/Oauth/controllers/Adminhtml/Oauth/AuthorizeController.php app/code/core/Mage/Oauth/controllers/Adminhtml/Oauth/AuthorizeController.php
index c30d273..36542f9 100644
--- app/code/core/Mage/Oauth/controllers/Adminhtml/Oauth/AuthorizeController.php
+++ app/code/core/Mage/Oauth/controllers/Adminhtml/Oauth/AuthorizeController.php
@@ -55,7 +55,7 @@ class Mage_Oauth_Adminhtml_Oauth_AuthorizeController extends Mage_Adminhtml_Cont
      */
     public function preDispatch()
     {
-        $this->getRequest()->setParam('forwarded', true);
+        Mage::app()->getRequest()->setInternallyForwarded();
 
         // check login data before it set null in Mage_Admin_Model_Observer::actionPreDispatchAdmin
         $loginError = $this->_checkLoginIsEmpty();
diff --git app/code/core/Mage/XmlConnect/Model/Observer.php app/code/core/Mage/XmlConnect/Model/Observer.php
index e6cb947..36142ac 100644
--- app/code/core/Mage/XmlConnect/Model/Observer.php
+++ app/code/core/Mage/XmlConnect/Model/Observer.php
@@ -143,7 +143,7 @@ class Mage_XmlConnect_Model_Observer
         /** @var $request Mage_Core_Controller_Request_Http */
         $request = Mage::app()->getRequest();
         if (true === $this->_checkAdminController($request, $event->getControllerAction())) {
-            $request->setParam('forwarded', true)->setDispatched(true);
+            $request->setInternallyForwarded()->setDispatched(true);
         }
     }
 
@@ -160,7 +160,7 @@ class Mage_XmlConnect_Model_Observer
         if (false === $this->_checkAdminController($request, $event->getControllerAction())
             && !Mage::getSingleton('admin/session')->isLoggedIn()
         ) {
-            $request->setParam('forwarded', true)->setRouteName('adminhtml')->setControllerName('connect_user')
+            $request->setInternallyForwarded()->setRouteName('adminhtml')->setControllerName('connect_user')
                 ->setActionName('loginform')->setDispatched(false);
         }
     }
diff --git lib/Varien/Db/Adapter/Pdo/Mysql.php lib/Varien/Db/Adapter/Pdo/Mysql.php
index 2226331..d1c6942 100644
--- lib/Varien/Db/Adapter/Pdo/Mysql.php
+++ lib/Varien/Db/Adapter/Pdo/Mysql.php
@@ -2834,10 +2834,6 @@ class Varien_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql implements V
 
         $query = '';
         if (is_array($condition)) {
-            if (isset($condition['field_expr'])) {
-                $fieldName = str_replace('#?', $this->quoteIdentifier($fieldName), $condition['field_expr']);
-                unset($condition['field_expr']);
-            }
             $key = key(array_intersect_key($condition, $conditionKeyMap));
 
             if (isset($condition['from']) || isset($condition['to'])) {
END_PATCH

    my $magver = getmagver();
    my $magedition = getmagedition($magver);

    print "Detected Magento: $magver ($magedition)\n\n";

    my $patch_1533;
    my $patch_5344;
    my $patch_1533_name;
    my $patch_5344_name;

    # SUPEE-1533
    if ($magedition eq 'Community') {
        # CE 1.8-1.9
        if ($magver =~ /^1\.[8-9]\./) {
            $patch_1533 = $patch_1533_1819;
            $patch_1533_name = $patch_1533_1819_name;
        }
        # CE 1.7
        elsif ($magver =~ /^1\.7\./) {
            $patch_1533 = $patch_1533_17;
            $patch_1533_name = $patch_1533_17_name;
        }
        # CE 1.6
        elsif ($magver =~ /^1\.6\./) {
            $patch_1533 = $patch_1533_16;
            $patch_1533_name = $patch_1533_16_name;
        }
        # CE 1.5.1
        elsif ($magver =~ /^1\.5\.1\./) {
            $patch_1533 = $patch_1533_151;
            $patch_1533_name = $patch_1533_151_name;
        }
        # CE 1.5.0
        elsif ($magver =~ /^1\.5\.0\./) {
            $patch_1533 = $patch_1533_150;
            $patch_1533_name = $patch_1533_150_name;
        }
        # CE 1.4 and let's try it on 1.3
        elsif ($magver =~ /^1\.[3-4]\./) {
            $patch_1533 = $patch_1533_14;
            $patch_1533_name = $patch_1533_14_name;
        }
        else {
            die("Magento has not distributed a patch for this version.  Magento patches exist for 1.3+ CE and 1.7+ EE. This patcher can't do anything.\n");
        }
    }
    elsif ($magedition eq 'Enterprise') {
        # EE 1.13-1.14
        if ($magver =~ /^1\.1[3-4]\./) {
            $patch_1533 = $patch_1533_1819;
            $patch_1533_name = $patch_1533_1819_name;
        }
        # EE 1.12
        elsif ($magver =~ /^1\.12\./) {
            $patch_1533 = $patch_1533_17;
            $patch_1533_name = $patch_1533_17_name;
        }
        # EE 1.11
        elsif ($magver =~ /^1\.11\./) {
            $patch_1533 = $patch_1533_16;
            $patch_1533_name = $patch_1533_16_name;
        }
        # EE 1.10.1
        elsif ($magver =~ /^1\.10\.1\./) {
            $patch_1533 = $patch_1533_151;
            $patch_1533_name = $patch_1533_151_name;
        }
        # EE 1.10.0
        elsif ($magver =~ /^1\.10\.0\./) {
            $patch_1533 = $patch_1533_150;
            $patch_1533_name = $patch_1533_150_name;
        }
        # EE 1.8-1.9 and let's try it on 1.7
        elsif ($magver =~ /^1\.[7-9]\./) {
            $patch_1533 = $patch_1533_14;
            $patch_1533_name = $patch_1533_14_name;
        }
        else {
            die("Magento has not distributed a patch for this version.  Magento patches exist for 1.3+ CE and 1.7+ EE. This patcher can't do anything.\n");
        }
    }
    else {
        die("This patcher only understands how to patch 'Community' and 'Enterprise' editions, not 'Professional' or 'Go'.  If you know which patches are appropriate for this version, let me know so I can update this tool.  I couldn't find anything on magento.com about them for these patches.");
    }

    # SUPEE-5344

    if ($magedition eq 'Community') {
        # CE 1.8 - 1.9
        if ($magver =~ /^1\.[8-9]\./) {
            $patch_5344 = $patch_5344_1819;
            $patch_5344_name = $patch_5344_1819_name;
        }
        # CE 1.7
        elsif ($magver =~ /^1\.7\./) {
            $patch_5344 = $patch_5344_17;
            $patch_5344_name = $patch_5344_17_name;
        }
        # CE 1.6.1-1.6.2
        elsif ($magver =~ /^1\.6\.[1-2]\./) {
            $patch_5344 = $patch_5344_161162;
            $patch_5344_name = $patch_5344_161162_name;
        }
        # CE 1.6.0
        elsif ($magver =~ /^1\.6\.0\./) {
            $patch_5344 = $patch_5344_160;
            $patch_5344_name = $patch_5344_160_name;
        }
        # CE 1.5.1
        elsif ($magver =~ /^1\.5\.1\./) {
            $patch_5344 = $patch_5344_151;
            $patch_5344_name = $patch_5344_151_name;
        }
        # CE 1.4-1.5.0
        elsif ($magver =~ /^1\.(4|5)\./) {
            $patch_5344 = $patch_5344_140150;
            $patch_5344_name = $patch_5344_140150_name;
        }
        # CE 1.3 (Unofficial patch from NuBlue)
        elsif ($magver =~ /^1\.3\./) {
            $patch_5344 = $patch_5344_13;
            $patch_5344_name = $patch_5344_13_name;
        }
        else {
            die("Magento has not distributed a patch for this version.  Magento patches exist for 1.3+ CE and 1.7+ EE. This patcher can't do anything.\n");
        }
    }
    elsif ($magedition eq 'Enterprise') {
        # EE 1.13-1.14
        if ($magver =~ /^1\.1[3-4]\./) {
            $patch_5344 = $patch_5344_1819;
            $patch_5344_name = $patch_5344_1819_name;
        }
        # EE 1.12
        elsif ($magver =~ /^1\.12\./) {
            $patch_5344 = $patch_5344_17;
            $patch_5344_name = $patch_5344_17_name;
        }
        # EE 1.11.1+
        elsif ($magver =~ /^1\.11\.[1-9]\./) {
            $patch_5344 = $patch_5344_161162;
            $patch_5344_name = $patch_5344_161162_name;
        }
        # EE 1.11.0
        elsif ($magver =~ /^1\.11\.0\./) {
            $patch_5344 = $patch_5344_160;
            $patch_5344_name = $patch_5344_160_name;
        }
        # EE 1.10.1
        elsif ($magver =~ /^1\.10\.1/) {
            $patch_5344 = $patch_5344_151;
            $patch_5344_name = $patch_5344_151_name;
        }
        # EE 1.8-1.10.0
        elsif ($magver =~ /^1\.([8-9]|10)\./) {
            $patch_5344 = $patch_5344_140150;
            $patch_5344_name = $patch_5344_140150_name;
        }
        # EE 1.7 (Unofficial patch from NuBlue.  Not claimed to run on EE, but I think this is the equivalent version to CE 1.3)
        elsif ($magver =~ /^1\.7\./) {
            $patch_5344 = $patch_5344_13;
            $patch_5344_name = $patch_5344_13_name;
        }
        else {
            die("Magento has not distributed a patch for this version.  Magento patches exist for 1.3+ CE and 1.7+ EE. This patcher can't do anything.\n");
        }
    }
    else {
        die("This patcher only understands how to patch 'Community' and 'Enterprise' editions, not 'Professional' or 'Go'.  If you know which patches are appropriate for this version, let me know so I can update this tool.  I couldn't find anything on magento.com about them for these patches.");
    }

    print "Checking SUPEE-1533...";
    my $is_1533_patched = check_patch($patch_1533);
    print( ($is_1533_patched ? 'already patched' : 'NOT PATCHED')."!\n");

    print "Checking SUPEE-5344...";
    my $is_5344_patched = check_patch($patch_5344);
    print( ($is_5344_patched ? 'already patched' : 'NOT PATCHED')."!\n");

    my $reverse_patches = 0;
    my $apply_patches   = 0;
    if (defined($ARGV[0]) && $ARGV[0] =~ /^-r(everse)?$/) {
        $reverse_patches = 1;
    }
    elsif (defined($ARGV[0]) && $ARGV[0] =~ /^-a(pply)?$/) {
        $apply_patches = 1;
    }

    if ($reverse_patches) {
        if ($is_1533_patched || $is_5344_patched) {
            my (@did_1533_reverse, @did_5344_reverse);
            print "\nAttempting to reverse patches...\n";

            if ($is_1533_patched) {
                print "Reversing SUPEE-1533...";
                @did_1533_reverse = reverse_patch($patch_1533);
                print( ($#did_1533_reverse > 0 ? 'success' : 'FAILED')."!\n");
            }

            if ($is_5344_patched) {
                print "Reversing SUPEE-5344...";
                @did_5344_reverse = reverse_patch($patch_5344);
                print( ($#did_5344_reverse > 0 ? 'success' : 'FAILED')."!\n");
            }

            my $patchdate = `date -u +"%F %T UTC"`;
            chomp $patchdate;
            my $patchfile = 'app/etc/applied.patches.list';
            open(PATCHLIST, '>>', $patchfile) or die ("Unable to open $patchfile for writing: $!");
            if ($#did_1533_reverse > 0) {
                print PATCHLIST "$patchdate | $patch_1533_name | REVERTED\n";
                foreach my $patchline (@did_1533_reverse) {
                    chomp $patchline;
                    print PATCHLIST "$patchline\n";
                }
                print PATCHLIST "\n\n";
            }
            if ($#did_5344_reverse > 0) {
                print PATCHLIST "$patchdate | $patch_5344_name | REVERTED\n";
                foreach my $patchline (@did_5344_reverse) {
                    chomp $patchline;
                    print PATCHLIST "$patchline\n";
                }
                print PATCHLIST "\n\n";
            }
            close(PATCHLIST);

            if (($is_1533_patched && $#did_1533_reverse <= 0) || ($is_5344_patched && $#did_5344_reverse <= 0)) {
                print "\nOne or more patches failed to reverse.  Please pursue removing the patches from this Magento install manually.\n";
            }
            else {
                print "\nPatches appear to have been reversed successfully.\n";
            }
        }
        else {
            print "\nNothing to do here.  Both patches do not seem to have been previously reversed.\n";
        }
    }
    elsif ($apply_patches) {
        if (!$is_1533_patched || !$is_5344_patched) {
            my (@did_1533_apply, @did_5344_apply);
            print "\nAttempting to apply patches...\n";

            if (!$is_1533_patched) {
                print "Applying SUPEE-1533...";
                @did_1533_apply = apply_patch($patch_1533);
                print( ($#did_1533_apply > 0 ? 'success' : 'FAILED')."!\n");
            }

            if (!$is_5344_patched) {
                print "Applying SUPEE-5344...";
                @did_5344_apply = apply_patch($patch_5344);
                print( ($#did_5344_apply > 0 ? 'success' : 'FAILED')."!\n");
            }

            my $patchdate = `date -u +"%F %T UTC"`;
            chomp $patchdate;
            my $patchfile = 'app/etc/applied.patches.list';
            open(PATCHLIST, '>>', $patchfile) or die ("Unable to open $patchfile for writing: $!");
            if ($#did_1533_apply > 0) {
                print PATCHLIST "$patchdate | $patch_1533_name\n";
                foreach my $patchline (@did_1533_apply) {
                    chomp $patchline;
                    print PATCHLIST "$patchline\n";
                }
                print PATCHLIST "\n\n";
            }
            if ($#did_5344_apply > 0) {
                print PATCHLIST "$patchdate | $patch_5344_name\n";
                foreach my $patchline (@did_5344_apply) {
                    chomp $patchline;
                    print PATCHLIST "$patchline\n";
                }
                print PATCHLIST "\n\n";
            }
            close(PATCHLIST);

            if ((!$is_1533_patched && $#did_1533_apply <= 0) || (!$is_5344_patched && $#did_5344_apply <= 0)) {
                print "\nOne or more patches failed to apply.  Please pursue patching this Magento install manually.\n";
            }
            else {
                print "\nPatches appear to have been applied successfully.  Verifying...\n";

                if (!$is_1533_patched) {
                    print "Checking SUPEE-1533...";
                    $is_1533_patched = check_patch($patch_1533);
                    print( ($is_1533_patched ? 'patched' : 'NOT PATCHED')."!\n");
                }

                if (!$is_5344_patched) {
                    print "Checking SUPEE-5344...";
                    $is_5344_patched = check_patch($patch_5344);
                    print( ($is_5344_patched ? 'patched' : 'NOT PATCHED')."!\n");
                }
               
                if (!$is_1533_patched || !$is_5344_patched) {
                    print "\nSomething went wrong.  The patches cannot be verified as applied correctly.  Please try manual patch installation.\n";
                }
                else {
                    print "\nDone! This magento install has had SUPEE-1533 and SUPEE-5344 patches successfully applied.\n";
                    print "\n(Did you know: You can add '-r' as an argument to this script to REVERSE these patches.)\n";
                }
            }
        } else {
            print "\nBoth SUPEE-1533 and SUPEE-5344 are already applied.  Nothing to do here...\n";
            print "\n(Did you know: You can add '-r' as an argument to this script to REVERSE these patches.)\n";
        }
    }
    else {
        print "\nNo work has been done! You may pass -a (apply patches) or -r (reverse patches) to affect this Magento install\n";
    }
}
