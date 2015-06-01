<?php
Mage::app('default');
include 'Popup.php';
function getMazeTable($tbl)
{
    $tableName = Mage::getSingleton('core/resource')->getTableName($tbl);
    return($tableName);
}
//customer will be re-directed to this file. this file handle all token, email etc things.
class Loginradius_Sociallogin_IndexController extends Mage_Core_Controller_Front_Action
{
    var $blockObj;
    private $loginRadiusPopMsg;
    private $loginRadiusPopErr;
    private $socialProfileCheckboxes;
    private $loginRadiusContactIds = array();
    private $loginRadiusFacebookToken;
    private $loginRadiusProvider;
    private $loginRadiusToken = '';
    private $loginRadiusAccessToken = '';
    protected function _getSession()
    {
        return Mage::getSingleton('sociallogin/session');
    }
    // if token is posted then this function will be called. It will login user if already in database. else if email is provided by api, it will insert data and login user. It will handle all after token.
    public function tokenHandle()
    {
        $ApiSecrete = $this->blockObj->getApiSecret();
        $this -> login_radius_fetch_token();
        $user_obj = $this -> blockObj -> loginradius_get_user_profiledata( $this -> loginRadiusAccessToken );

        // validate the object
        if (is_object($user_obj) && isset($user_obj->ID)) {
            $id = $user_obj->ID;
        } else {
            header('Location:' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK));
            exit();
        }
        if (empty($id)) {
            //invalid user
            header('Location:' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK));
            exit();
        }
        // social linking variable
        $socialLinking = false;
        // social linking
        if (isset($_GET['loginRadiusLinking']) && trim($_GET['loginRadiusLinking']) == 1) {
            $socialLinking = true;
        }
        // save provider in a class member
        $this -> loginRadiusProvider = empty($user_obj->Provider) ? "" : $user_obj->Provider;
        //valid user, checking if user in sociallogin table
        $socialLoginIdResult = $this->loginRadiusRead( "sociallogin", "get user", array($id), true );
        $socialLoginIds = $socialLoginIdResult->fetchAll();
        // variable to hold user id of the logged in user
        $sociallogin_id = '';
        foreach ( $socialLoginIds as $socialLoginId ) {
            // check if the user exists in the customer_entity table for this social id
            $select = $this->loginRadiusRead( "customer_entity", "get user2", array($socialLoginId['entity_id']), true );
            if ($rowArray = $select->fetch()) {
                if ( $socialLoginId['verified'] == "0" ) {
                    if (!$socialLinking) {
                        SL_popUpWindow("Please verify your email to login.", "", false );
                        return;
                    } else {
                        // link account
                        $this->loginRadiusSocialLinking(Mage::getSingleton("customer/session")->getCustomer()->getId(), $user_obj->ID, $user_obj->Provider, $user_obj->ThumbnailImageUrl);
                        header("Location:".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)."customer/account/?LoginRadiusLinked=1");
                        die;
                    }
                }
                $sociallogin_id = $rowArray['entity_id'];
                break;
            }
        }

        if (!empty($sociallogin_id)) {    //user is in database
            if (!$socialLinking) {
                if ($this->blockObj->updateProfileData() != '1') {
                    $this->socialLoginUserLogin( $sociallogin_id, $id );
                    return;
                } else {
                    $socialloginProfileData = $this->socialLoginFilterData( '', $user_obj );
                    $socialloginProfileData['lrId'] = $user_obj->ID;
                    $this->socialLoginAddNewUser( $socialloginProfileData, $verify = false, true, $sociallogin_id );
                    return;
                }
            } else {
                // account already exists
                header("Location:".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)."customer/account/?LoginRadiusLinked=0");
                die;
            }
        }
        // social linking
        if ($socialLinking) {
            $this->loginRadiusSocialLinking(Mage::getSingleton("customer/session")->getCustomer()->getId(), $user_obj->ID, $user_obj->Provider, $user_obj->ThumbnailImageUrl, true);
            header("Location:".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)."customer/account/?LoginRadiusLinked=1");
            die;
        }
        // initialize email
        $email = '';
        if ( !empty($user_obj->Email[0]->Value) ) {
            //if email is provided by provider then check if it's in table
            $email = $user_obj->Email['0']->Value;
            $select = $this->loginRadiusRead( "customer_entity", "email exists login", array($email), true );
            if ( $rowArray = $select->fetch() ) {
                $sociallogin_id = $rowArray['entity_id'];
                if (!empty($sociallogin_id)) {
                    //user is in customer table
                    if ( $this->blockObj->getLinking() == "1" ) {    // Social Linking
                        $this->loginRadiusSocialLinking($sociallogin_id, $user_obj->ID, $user_obj->Provider, $user_obj->ThumbnailImageUrl);
                    }
                    if ($this->blockObj->updateProfileData() != '1') {
                        $this->socialLoginUserLogin( $sociallogin_id, $user_obj->ID );
                        return;
                    } else {
                        $socialloginProfileData = $this->socialLoginFilterData( '', $user_obj );
                        $socialloginProfileData['lrId'] = $user_obj->ID;
                        $this->socialLoginAddNewUser( $socialloginProfileData, $verify = false, true, $sociallogin_id );
                        return;
                    }
                }
            }
            $socialloginProfileData = $this->socialLoginFilterData( $email, $user_obj );
            $socialloginProfileData['lrId'] = $user_obj->ID;
            $socialloginProfileData['lrToken'] = $_REQUEST['token'];
            if ($this->blockObj->getProfileFieldsRequired() == 1) {
                $id = $user_obj->ID;
                $this->setInSession($id, $socialloginProfileData);
                // show a popup to fill required profile fields
                SL_popUpWindow("Please provide following details:-", "", true, $socialloginProfileData, false);
                return;
            }
            $this->socialLoginAddNewUser( $socialloginProfileData );
            return;
        }

        // empty email
        if ( $this->blockObj->getEmailRequired() == 0 ) {     // dummy email
            $email = $this->loginradius_get_randomEmail( $user_obj );
            $socialloginProfileData = $this->socialLoginFilterData( $email, $user_obj );
            $socialloginProfileData['lrId'] = $user_obj->ID;
            $socialloginProfileData['lrToken'] = $_REQUEST['token'];
            if ($this->blockObj->getProfileFieldsRequired() == 1) {
                $id = $user_obj->ID;
                //$socialloginProfileData = $this->socialLoginFilterData( $email, $user_obj );
                $this->setInSession($id, $socialloginProfileData);
                // show a popup to fill required profile fields
                SL_popUpWindow("Please provide following details:-", "", true, $socialloginProfileData, false);
                return;
            }
            // create new user
            $this->socialLoginAddNewUser( $socialloginProfileData );
            return;
        } else {        // show popup
            $id = $user_obj->ID;
            $socialloginProfileData = $this->socialLoginFilterData( $email, $user_obj );
            $socialloginProfileData['lrToken'] = $_REQUEST['token'];
            $this->setInSession($id, $socialloginProfileData);
            if ($this->blockObj->getProfileFieldsRequired() == 1) {
                // show a popup to fill required profile fields
                SL_popUpWindow("Please provide following details:-", "", true, $socialloginProfileData, true);
            } else {
                SL_popUpWindow($this->loginRadiusPopMsg, "", true, array(), true, true);
            }
            return;
        }
    }
    public function loginradius_get_randomEmail( $user_obj )
    {
        switch ( $user_obj->Provider ) {
            case 'twitter':
                $email = $user_obj->ID. '@' . $user_obj->Provider . '.com';
                break;
            case 'linkedin':
                $email = $user_obj->ID. '@' . $user_obj->Provider . '.com';
                break;
            default:
                $Email_id = substr( $user_obj->ID, 7 );
                $Email_id2 = str_replace("/", "_", $Email_id);
                $email = str_replace(".", "_", $Email_id2) . '@' . $user_obj->Provider . '.com';
                break;
        }
        return $email;
    }
    // social linking
    public function loginRadiusSocialLinking($entityId, $socialId, $provider, $thumbnail, $unique = false)
    {
        // check if any account from this provider is already linked
        if ($unique && $this->loginRadiusRead( "sociallogin", "provider exists in sociallogin", array($entityId, $provider))) {
            header("Location:".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK)."customer/account/?LoginRadiusLinked=2");
            die;
        }
        $socialLoginLinkData = array();
        $socialLoginLinkData['sociallogin_id'] = $socialId;
        $socialLoginLinkData['entity_id'] = $entityId;
        $socialLoginLinkData['provider'] = empty($provider) ? "" : $provider;
        $socialLoginLinkData['avatar'] = $this->socialLoginFilterAvatar( $socialId, $thumbnail, $provider );
        $socialLoginLinkData['avatar'] = ($socialLoginLinkData['avatar'] == "") ? NULL : $socialLoginLinkData['avatar'] ;
        $this->SocialLoginInsert( "sociallogin", $socialLoginLinkData );
    }
    public function socialLoginFilterData( $email, $user_obj )
    {
        $socialloginProfileData = array();
        $socialloginProfileData['SocialId'] = empty($user_obj->ID) ? '' : $user_obj -> ID;
        $socialloginProfileData['Email'] = $email;
        $socialloginProfileData['Provider'] = empty($user_obj->Provider) ? "" : $user_obj->Provider;
        $socialloginProfileData['FirstName'] = empty($user_obj->FirstName) ? "" : $user_obj->FirstName;
        $socialloginProfileData['FullName'] = empty($user_obj->FullName) ? "" : $user_obj->FullName;
        $socialloginProfileData['NickName'] = empty($user_obj->NickName) ? "" : $user_obj->NickName;
        $socialloginProfileData['ProfileName'] = !empty($user_obj->ProfileName) ? $user_obj->ProfileName : '';
        $socialloginProfileData['LastName'] = empty($user_obj->LastName) ? "" : $user_obj->LastName;
        if (isset($user_obj->Addresses) && is_array($user_obj->Addresses)) {
            foreach ($user_obj->Addresses as $address) {
                if (isset($address->Address1) && !empty($address->Address1)) {
                    $socialloginProfileData['Address'] = $address->Address1;
                    break;
                }
            }
        } elseif (isset($user_obj->Addresses) && is_string($user_obj->Addresses)) {
            $socialloginProfileData['Address'] = $user_obj->Addresses != "" ? $user_obj->Addresses : "";
        } else {
            $socialloginProfileData['Address'] = "";
        }
        $socialloginProfileData['PhoneNumber'] = empty( $user_obj->PhoneNumbers['0']->PhoneNumber ) ? "" : $user_obj->PhoneNumbers['0']->PhoneNumber;
        $socialloginProfileData['State'] = empty($user_obj->State) ? "" : $user_obj->State;
        $socialloginProfileData['City'] = empty($user_obj->City) || $user_obj->City == "unknown" ? "" : $user_obj->City;
        $socialloginProfileData['Industry'] = empty($user_obj->Positions['0']->Comapny->Name) ? "" : $user_obj->Positions['0']->Comapny->Name;
        if (isset($user_obj->Country->Code) && is_string($user_obj->Country->Code)) {
            $socialloginProfileData['Country'] = $user_obj->Country->Code;
        } else {
            $socialloginProfileData['Country'] = "";
        }
        $socialloginProfileData['thumbnail'] = $this->socialLoginFilterAvatar( $user_obj->ID, $user_obj->ThumbnailImageUrl, $socialloginProfileData['Provider'] );
        $explode= explode("@", $email);
        if ( empty( $socialloginProfileData['FirstName'] ) && !empty( $socialloginProfileData['FullName'] ) ) {
            $socialloginProfileData['FirstName'] = $socialloginProfileData['FullName'];
        } elseif (empty($socialloginProfileData['FirstName'] ) && !empty( $socialloginProfileData['NickName'] )) {
            $socialloginProfileData['FirstName'] = $socialloginProfileData['NickName'];
        } elseif (empty($socialloginProfileData['FirstName'] ) && empty($socialloginProfileData['NickName'] ) && !empty($socialloginProfileData['FullName'] ) ) {
            $socialloginProfileData['FirstName'] = $explode[0];
        }
        if ($socialloginProfileData['FirstName'] == '' ) {
            $letters = range('a', 'z');
            for ($i=0;$i<5;$i++) {
                $socialloginProfileData['FirstName'] .= $letters[rand(0, 26)];
            }
        }
        $socialloginProfileData['Gender'] = (!empty($user_obj->Gender) ? $user_obj->Gender : '');
        if ( strtolower(substr($socialloginProfileData['Gender'], 0, 1)) == 'm' ) {
            $socialloginProfileData['Gender'] = '1';
        } elseif ( strtolower(substr($socialloginProfileData['Gender'], 0, 1)) == 'f' ) {
            $socialloginProfileData['Gender'] = '2';
        } else {
            $socialloginProfileData['Gender'] = '';
        }
        $socialloginProfileData['BirthDate'] = (!empty($user_obj->BirthDate) ? $user_obj->BirthDate : '');
        if ( $socialloginProfileData['BirthDate'] != "" ) {
            $temp = explode( '/', $socialloginProfileData['BirthDate'] );   // mm/dd/yy
            $socialloginProfileData['BirthDate'] = $temp[2]."/".$temp[0]."/".$temp[1];
        }
        $socialloginProfileData['Bio'] = (!empty($user_obj->About) ? $user_obj->About : '');
        $socialloginProfileData['ProfileUrl'] = (!empty($user_obj->ProfileUrl) ? $user_obj->ProfileUrl : '');
        // profile data option is enabled
        if (trim($this -> blockObj -> getSocialProfileCheckboxes()) != '') {
            // convert the above string to array
            $this -> socialProfileCheckboxes = explode(',', trim($this -> blockObj -> getSocialProfileCheckboxes()));
            // basic profile data
            if (in_array('basic', $this -> socialProfileCheckboxes)) {
                $socialloginProfileData['Prefix'] = (!empty($user_obj->Prefix) ? $user_obj->Prefix : '');
                $socialloginProfileData['MiddleName'] = (!empty($user_obj->MiddleName) ? $user_obj->MiddleName : '');
                $socialloginProfileData['Suffix'] = (!empty($user_obj->Suffix) ? $user_obj->Suffix : '');
                // country name
                if (isset($user_obj->Country->Name) && is_string($user_obj->Country->Name)) {
                    $socialloginProfileData['CountryName'] = $user_obj->Country->Name != "unknown" ? $user_obj->Country->Name : "";
                } elseif (isset($user_obj->Country) && is_string($user_obj->Country) ) {
                    $socialloginProfileData['CountryName'] = $user_obj->Country != "unknown" ? $user_obj->Country : "";
                } else {
                    $socialloginProfileData['CountryName'] = "";
                }
                // Country Code
                if (isset($user_obj->Country->Code) && is_string($user_obj->Country->Code)) {
                    $socialloginProfileData['CountryCode'] = $user_obj->Country->Code != "unknown" ? $user_obj->Country->Code : "";
                } else {
                    $socialloginProfileData['CountryCode'] = "";
                }
                $socialloginProfileData['ImageUrl'] = (!empty($user_obj->ImageUrl) ? $user_obj->ImageUrl : '');
                $socialloginProfileData['LocalCountry'] = !empty($user_obj->LocalCountry) && $user_obj->LocalCountry != 'unknown' ? $user_obj->LocalCountry : '';
                $socialloginProfileData['ProfileCountry'] = !empty($user_obj->ProfileCountry) && $user_obj->ProfileCountry != 'unknown' ? $user_obj->ProfileCountry : '';
                $socialloginProfileData['Emails'] = $user_obj->Email;
            }
            // extended location data
            if (in_array('ex_location', $this -> socialProfileCheckboxes) && isset($user_obj->MainAddress)) {
                $socialloginProfileData['MainAddress'] = (!empty($user_obj->MainAddress) ? $user_obj->MainAddress : '');
                $socialloginProfileData['HomeTown'] = (!empty($user_obj->HomeTown) ? $user_obj->HomeTown : '');
                $socialloginProfileData['State'] = (!empty($user_obj->State) ? $user_obj->State : '');
                $socialloginProfileData['City'] = (!empty($user_obj->City) && $user_obj->City != "unknown" ? $user_obj->City : '');
                $socialloginProfileData['LocalCity'] = !empty($user_obj->LocalCity) && $user_obj->LocalCity != 'unknown' ? $user_obj->LocalCity : '';
                $socialloginProfileData['ProfileCity'] = !empty($user_obj->ProfileCity) && $user_obj->ProfileCity != 'unknown' ? $user_obj->ProfileCity : '';
                $socialloginProfileData['LocalLanguage'] = !empty($user_obj->LocalLanguage) && $user_obj->LocalLanguage != 'unknown' ? $user_obj->LocalLanguage : '';
                $socialloginProfileData['Language'] = !empty($user_obj->Language) && $user_obj->Language != 'unknown' ? $user_obj->Language : '';
            }
            // extended profile data
            if (in_array('ex_profile', $this -> socialProfileCheckboxes) && isset($user_obj->Website)) {
                $socialloginProfileData['Website'] = (!empty($user_obj->Website) ? $user_obj->Website : '');
                $socialloginProfileData['Favicon'] = (!empty($user_obj->Favicon) ? $user_obj->Favicon : '');
                $socialloginProfileData['Industry'] = (!empty($user_obj->Industry) ? $user_obj->Industry : '');
                $socialloginProfileData['TimeZone'] = (!empty($user_obj->TimeZone) ? $user_obj->TimeZone : '');
                $socialloginProfileData['LastProfileUpdate'] = (!empty($user_obj->UpdatedTime) ? $user_obj->UpdatedTime : '');
                $socialloginProfileData['Created'] = (!empty($user_obj->Created) ? $user_obj->Created : '');
                $socialloginProfileData['Verified'] = (!empty($user_obj->Verified) ? $user_obj->Verified : '');
                $socialloginProfileData['RelationshipStatus'] = (!empty($user_obj->RelationshipStatus) ? $user_obj->RelationshipStatus : '');
                $socialloginProfileData['Quote'] = (!empty($user_obj->Quota) ? $user_obj->Quota : '');
                $socialloginProfileData['InterestedIn'] = (!empty($user_obj->InterestedIn) ? $user_obj->InterestedIn : '');
                $socialloginProfileData['Interests'] = (!empty($user_obj->Interests) ? $user_obj->Interests : '');
                $socialloginProfileData['Religion'] = (!empty($user_obj->Religion) ? $user_obj->Religion : '');
                $socialloginProfileData['PoliticalView'] = (!empty($user_obj->Political) ? $user_obj->Political : '');
                $socialloginProfileData['HttpsImageUrl'] = (!empty($user_obj->HttpsImageUrl) ? $user_obj->HttpsImageUrl : '');
                $socialloginProfileData['FollowersCount'] = (!empty($user_obj->FollowersCount) ? $user_obj->FollowersCount : '');
                $socialloginProfileData['FriendsCount'] = (!empty($user_obj->FriendsCount) ? $user_obj->FriendsCount : '');
                $socialloginProfileData['IsGeoEnabled'] = (!empty($user_obj->IsGeoEnabled) ? $user_obj->IsGeoEnabled : '');
                $socialloginProfileData['TotalStatusCount'] = (!empty($user_obj->TotalStatusesCount) ? $user_obj->TotalStatusesCount : '');
                $socialloginProfileData['NumberOfRecommenders'] = (!empty($user_obj->NumRecommenders) ? $user_obj->NumRecommenders : '');
                $socialloginProfileData['Honors'] = (!empty($user_obj->Honors) ? $user_obj->Honors : '');
                $socialloginProfileData['Associations'] = (!empty($user_obj->Associations) ? $user_obj->Associations : '');
                $socialloginProfileData['Hirable'] = (!empty($user_obj->Hireable) ? $user_obj->Hireable : '');
                $socialloginProfileData['RepositoryUrl'] = (!empty($user_obj->RepositoryUrl) ? $user_obj->RepositoryUrl : '');
                $socialloginProfileData['Age'] = (!empty($user_obj->Age) ? $user_obj->Age : '');
                $socialloginProfileData['ProfessionalHeadline'] = (!empty($user_obj->ProfessionalHeadline) ? $user_obj->ProfessionalHeadline : '');
                if (isset($user_obj->ProviderAccessCredential)) {
                    // AccessToken
                    if (isset($user_obj->ProviderAccessCredential->AccessToken) && !empty($user_obj->ProviderAccessCredential->AccessToken)) {
                        $socialloginProfileData['ProviderAccessToken'] = $user_obj->ProviderAccessCredential->AccessToken;
                    } else {
                        $socialloginProfileData['ProviderAccessToken'] = "";
                    }
                    // TokenSecret
                    if (isset($user_obj->ProviderAccessCredential->TokenSecret) && !empty($user_obj->ProviderAccessCredential->TokenSecret)) {
                        $socialloginProfileData['ProviderTokenSecret'] = $user_obj->ProviderAccessCredential->TokenSecret;
                    } else {
                        $socialloginProfileData['ProviderTokenSecret'] = "";
                    }
                } else {
                    $socialloginProfileData['ProviderTokenSecret'] = "";
                    $socialloginProfileData['ProviderAccessToken'] = "";
                }
                // arrays
                $socialloginProfileData['Positions'] = isset($user_obj->Positions) ? $user_obj->Positions : '';
                $socialloginProfileData['Educations'] = isset($user_obj->Educations) ? $user_obj->Educations : '';
                $socialloginProfileData['PhoneNumbers'] = isset($user_obj->PhoneNumbers) ? $user_obj->PhoneNumbers : '';
                $socialloginProfileData['IMAccounts'] = isset($user_obj->IMAccounts) ? $user_obj->IMAccounts : '';
                $socialloginProfileData['Addresses'] = isset($user_obj->Addresses) ? $user_obj->Addresses : '';
                $socialloginProfileData['Sports'] = isset($user_obj->Sports) ? $user_obj->Sports : '';
                $socialloginProfileData['InspirationalPeople'] = isset($user_obj->InspirationalPeople) ? $user_obj->InspirationalPeople : '';
                $socialloginProfileData['Skills'] = isset($user_obj->Skills) ? $user_obj->Skills : '';
                $socialloginProfileData['CurrentStatus'] = isset($user_obj->CurrentStatus) ? $user_obj->CurrentStatus : '';
                $socialloginProfileData['Certifications'] = isset($user_obj->Certifications) ? $user_obj->Certifications : '';
                $socialloginProfileData['Courses'] = isset($user_obj->Courses) ? $user_obj->Courses : '';
                $socialloginProfileData['Volunteer'] = isset($user_obj->Volunteer) ? $user_obj->Volunteer : '';
                $socialloginProfileData['RecommendationsReceived'] = isset($user_obj->RecommendationsReceived) ? $user_obj->RecommendationsReceived : '';
                $socialloginProfileData['Languages'] = isset($user_obj->Languages) ? $user_obj->Languages : '';
                $socialloginProfileData['Patents'] = isset($user_obj->Patents) ? $user_obj->Patents : '';
                $socialloginProfileData['Favorites'] = isset($user_obj->FavoriteThings) ? $user_obj->FavoriteThings : '';
            }
            // store Facebook Access Token
            if ($socialloginProfileData['Provider'] == "facebook" && in_array('likes', $this -> socialProfileCheckboxes)) {
                // AccessToken
                if (isset($user_obj->ProviderAccessCredential->AccessToken) && !empty($user_obj->ProviderAccessCredential->AccessToken)) {
                    $this -> $loginRadiusFacebookToken = $user_obj->ProviderAccessCredential->AccessToken;
                } else {
                    $this -> $loginRadiusFacebookToken = "";
                }
            }
        }
        return $socialloginProfileData;
    }

    public function socialLoginFilterAvatar( $id, $imgUrl, $provider )
    {
        $thumbnail = (!empty($imgUrl) ? trim($imgUrl) : '');
        if (empty($thumbnail) && ( $provider == 'facebook' ) ) {
            $thumbnail = "http://graph.facebook.com/" . $id . "/picture?type=large";
        }
        return $thumbnail;
    }
    public function login_radius_validate_url($url)
    {
        $validUrlExpression = "/^(http:\/\/|https:\/\/|ftp:\/\/|ftps:\/\/|)?[a-z0-9_\-]+[a-z0-9_\-\.]+\.[a-z]{2,4}(\/+[a-z0-9_\.\-\/]*)?$/i";
        return (bool)preg_match($validUrlExpression, $url);
    }
    public function socialLoginUserLogin( $entityId, $socialId, $write = true, $token = '', $provider = '')
    {
        $this -> loginRadiusToken = isset($_REQUEST['token']) ? $_REQUEST['token'] : $token;
        if ($write && !$this -> login_radius_write_permissions($entityId, $socialId)) {
            return;
        }
        $session = Mage::getSingleton("customer/session");
        $session->loginById($entityId);
        $session->setLoginRadiusId($socialId);
        $session->setLoginRadiusToken($this -> loginRadiusToken);
        $write_url = $this->blockObj->getCallBack();
        $Hover = $this->blockObj->getRedirectOption();
        $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        // check if logged in from callback page
        if (isset($_GET['loginradiuscheckout'])) {
            header( 'Location: '.Mage::helper('checkout/url')->getCheckoutUrl() );
            exit();
            return;
        }
        if ($Hover == 'account') {
            header( 'Location: '.$url.'customer/account' );
            exit();
            return;
        } elseif ($Hover == 'index' ) {
            header( 'Location: '.$url.'') ;
            exit();
            return;
        } elseif ( $Hover == 'custom' && $write_url != '' && $this -> login_radius_validate_url($write_url) && (strpos(urldecode($write_url), 'http://') !== false || strpos(urldecode($write_url), 'https://') !== false)) {
            header( 'Location: '.$write_url.'' );
            exit();
            return;
        } else {
            if (isset($_GET['redirect_to'])) {
                $currentUrl = trim($_GET['redirect_to']);
            } elseif (isset($_POST['loginRadiusRedirect']) && trim($_POST['loginRadiusRedirect']) != '') {
                $currentUrl = trim($_POST['loginRadiusRedirect']);
            } else {
                $currentUrl = $url;
            }
            header( 'Location: '.$currentUrl);
            exit();
            return;
        }
    }

    public function setInSession( $id, $socialloginProfileData )
    {
        $socialloginProfileData['lrId'] = $id;
        Mage::getSingleton('core/session')->setSocialLoginData( $socialloginProfileData );
    }

    public function loginRadiusEmail( $subject, $message, $to, $toName )
    {
        $storeName =  Mage::app()->getStore()->getGroup()->getName();
        //$mail = new Zend_Mail('UTF-8'); //class for mail
        $mailObj = new Mage_Core_Model_Email_Template();
        $mail = $mailObj -> getMail();
        $mail->setBodyHtml( $message ); //for sending message containing html code
        $mail->setFrom( "Owner", $storeName );
        $mail->addTo( $to, $toName );
        //$mail->addCc($cc, $ccname);    //can set cc
        //$mail->addBCc($bcc, $bccname);    //can set bcc
        $mail->setSubject( $subject );
        try{
            $mail->send();
        }catch(Exception $ex) {
        }
    }
    // insert premium profile data in database
    public function login_radius_save_profile_data($userId, $profileData)
    {
        $token = isset($profileData['lrToken']) && $profileData['lrToken'] != '' ? $profileData['lrToken'] : '';
        $socialProfileCheckboxes = explode(',', $this -> blockObj -> getSocialProfileCheckboxes());
        if (!is_array($socialProfileCheckboxes)) {
            return;
        }
        // insert basic profile data if option is selected
        if (in_array('basic', $socialProfileCheckboxes)) {
            $basicData = array();
            $basicData['user_id'] = $userId;
            $basicData['loginradius_id'] = $profileData['SocialId'];
            $basicData['provider'] = $profileData['Provider'];
            $basicData['prefix'] = $profileData['Prefix'];
            $basicData['first_name'] = $profileData['FirstName'];
            $basicData['middle_name'] = $profileData['MiddleName'];
            $basicData['last_name'] = $profileData['LastName'];
            $basicData['suffix'] = $profileData['Suffix'];
            $basicData['full_name'] = $profileData['FullName'];
            $basicData['nick_name'] = $profileData['NickName'];
            $basicData['profile_name'] = $profileData['ProfileName'];
            $basicData['birth_date'] = $profileData['BirthDate'];
            $basicData['gender'] = $profileData['Gender'];
            $basicData['country_code'] = $profileData['CountryCode'];
            $basicData['country_name'] = $profileData['CountryName'];
            $basicData['thumbnail_image_url'] = $profileData['thumbnail'];
            $basicData['image_url'] = $profileData['ImageUrl'];
            $basicData['local_country'] = $profileData['LocalCountry'];
            $basicData['profile_country'] = $profileData['ProfileCountry'];

            $this -> SocialLoginInsert('loginradius_basic_profile_data', $basicData);
            // emails
            if (count($profileData['Emails']) > 0) {
                foreach ($profileData['Emails'] as $lrEmail) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['email_type'] = $lrEmail->Type;
                    $data['email'] = $lrEmail->Value;
                    $this -> SocialLoginInsert('loginradius_emails', $data);
                }
            }
        }
        // insert extended location data if option is selected
        if (in_array('ex_location', $socialProfileCheckboxes)) {
            $data = array();
            $data['user_id'] = $userId;
            $data['main_address'] = $profileData['MainAddress'];
            $data['hometown'] = $profileData['HomeTown'];
            $data['state'] = $profileData['State'];
            $data['city'] = $profileData['City'];
            $data['local_city'] = $profileData['LocalCity'];
            $data['profile_city'] = $profileData['ProfileCity'];
            $data['profile_url'] = $profileData['ProfileUrl'];
            $data['local_language'] = $profileData['LocalLanguage'];
            $data['language'] = $profileData['Language'];
            $this -> SocialLoginInsert( 'loginradius_extended_location_data', $data);
        }
        // insert extended profile data if option is selected
        if (in_array('ex_profile', $socialProfileCheckboxes)) {
            $data = array();
            $data['user_id'] = $userId;
            $data['website'] = $profileData['Website'];
            $data['favicon'] = $profileData['Favicon'];
            $data['industry'] = $profileData['Industry'];
            $data['about'] = $profileData['Bio'];
            $data['timezone'] = $profileData['TimeZone'];
            $data['verified'] = $profileData['Verified'];
            $data['last_profile_update'] = $profileData['LastProfileUpdate'];
            $data['created'] = $profileData['Created'];
            $data['relationship_status'] = $profileData['RelationshipStatus'];
            $data['quote'] = $profileData['Quote'];
            $data['interested_in'] = is_array($profileData['InterestedIn']) ? implode(', ', $profileData['InterestedIn']) : $profileData['InterestedIn'];
            $data['interests'] = is_array($profileData['Interests']) ? implode(', ', $profileData['Interests']) : $profileData['Interests'];
            $data['religion'] = $profileData['Religion'];
            $data['political_view'] = $profileData['PoliticalView'];
            $data['https_image_url'] = $profileData['HttpsImageUrl'];
            $data['followers_count'] = $profileData['FollowersCount'];
            $data['friends_count'] = $profileData['FriendsCount'];
            $data['is_geo_enabled'] = $profileData['IsGeoEnabled'];
            $data['total_status_count'] = $profileData['TotalStatusCount'];
            $data['number_of_recommenders'] = $profileData['NumberOfRecommenders'];
            $data['honors'] = $profileData['Honors'];
            $data['associations'] = $profileData['Associations'];
            $data['hirable'] = $profileData['Hirable'];
            $data['repository_url'] = $profileData['RepositoryUrl'];
            $data['age'] = $profileData['Age'];
            $data['professional_headline'] = $profileData['ProfessionalHeadline'];
            $data['provider_access_token'] = $profileData['ProviderAccessToken'];
            $data['provider_token_secret'] = $profileData['ProviderTokenSecret'];
            $this -> SocialLoginInsert( 'loginradius_extended_profile_data', $data);
            // positions
            if (is_array($profileData['Positions']) && count($profileData['Positions']) > 0) {
                foreach ($profileData['Positions'] as $lrPosition) {
                    // companies
                    if (isset($lrPosition->Comapny)) {
                        $temp = array();
                        $temp['id'] = NULL;
                        $temp['company_name'] = $lrPosition->Comapny->Name;
                        $temp['company_type'] = $lrPosition->Comapny->Type;
                        $temp['industry'] = $lrPosition->Comapny->Industry;
                        $tempId = $this -> SocialLoginInsert( 'loginradius_companies', $temp );
                    }
                    // positions
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['position'] = $lrPosition->Position;
                    $data['summary'] = $lrPosition->Summary;
                    $data['start_date'] = $lrPosition->StartDate;
                    $data['end_date'] = $lrPosition->EndDate;
                    $data['is_current'] = $lrPosition->IsCurrent;
                    $data['company'] = isset($tempId) ? $tempId : NULL;
                    $data['location'] = $lrPosition->Location;
                    $this -> SocialLoginInsert( 'loginradius_positions', $data );
                }
            }
            // education
            if (is_array($profileData['Educations']) && count($profileData['Educations']) > 0) {
                foreach ($profileData['Educations'] as $education) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['school'] = $education->School;
                    $data['year'] = $education->year;
                    $data['type'] = $education->type;
                    $data['notes'] = $education->notes;
                    $data['activities'] = $education->activities;
                    $data['degree'] = $education->degree;
                    $data['field_of_study'] = $education->fieldofstudy;
                    $data['start_date'] = $education->StartDate;
                    $data['end_date'] = $education->EndDate;
                    $this -> SocialLoginInsert( 'loginradius_education', $data );
                }
            }
            // phone numbers
            if (is_array($profileData['PhoneNumbers']) && count($profileData['PhoneNumbers']) > 0 ) {
                foreach ( $profileData['PhoneNumbers'] as $lrPhoneNumber ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['number_type'] = $lrPhoneNumber->PhoneType;
                    $data['phone_number'] = $lrPhoneNumber->PhoneNumber;
                    $this -> SocialLoginInsert( 'loginradius_phone_numbers', $data );
                }
            }
            // IM Accounts
            if (is_array($profileData['IMAccounts']) && count($profileData['IMAccounts']) > 0 ) {
                foreach ( $profileData['IMAccounts'] as $lrImacc ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['account_type'] = $lrImacc->AccountType;
                    $data['account_username'] = $lrImacc->AccountName;
                    $this -> SocialLoginInsert( 'loginradius_IMaccounts', $data );
                }
            }
            // Addresses
            if (is_array($profileData['Addresses']) && count($profileData['Addresses']) > 0 ) {
                foreach ( $profileData['Addresses'] as $lraddress ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['type'] = $lraddress->Type;
                    $data['address_line1'] = $lraddress->Address1;
                    $data['address_line2'] = $lraddress->Address2 ;
                    $data['city'] = $lraddress->City;
                    $data['state'] = $lraddress->State;
                    $data['postal_code'] = $lraddress->PostalCode;
                    $data['region'] = $lraddress->Region;
                    $this -> SocialLoginInsert( 'loginradius_addresses', $data );
                }
            }
            // Sports
            if (is_array($profileData['Sports']) && count($profileData['Sports']) > 0 ) {
                foreach ( $profileData['Sports'] as $lrSport ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['sport_id'] = $lrSport->Id;
                    $data['sport'] = $lrSport->Name;
                    $this -> SocialLoginInsert( 'loginradius_sports', $data );
                }
            }
            // Inspirational People
            if (is_array($profileData['InspirationalPeople']) && count($profileData['InspirationalPeople']) > 0 ) {
                foreach ( $profileData['InspirationalPeople'] as $lrIP ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['social_id'] = $lrIP->Id;
                    $data['name'] = $lrIP->Name;
                    $this -> SocialLoginInsert( 'loginradius_inspirational_people', $data );
                }
            }
            // Skills
            if (is_array($profileData['Skills']) && count($profileData['Skills']) > 0 ) {
                foreach ( $profileData['Skills'] as $lrSkill ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['skill_id'] = $lrSkill->Id;
                    $data['name'] = $lrSkill->Name;
                    $this -> SocialLoginInsert( 'loginradius_skills', $data );
                }
            }
            // Current Status
            if (is_array($profileData['CurrentStatus']) && count($profileData['CurrentStatus']) > 0 ) {
                foreach ( $profileData['CurrentStatus'] as $lrCurrentStatus ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['status_id'] = $lrCurrentStatus->Id;
                    $data['status'] = $lrCurrentStatus->Text;
                    $data['source'] = $lrCurrentStatus->Source;
                    $data['created_date'] = $lrCurrentStatus->CreatedDate;
                    $this -> SocialLoginInsert( 'loginradius_current_status', $data );
                }
            }
            // Certifications
            if (is_array($profileData['Certifications']) && count($profileData['Certifications']) > 0 ) {
                foreach ( $profileData['Certifications'] as $lrCertification ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['certification_id'] = $lrCertification->Id;
                    $data['certification_name'] = $lrCertification->Name;
                    $data['authority'] = $lrCertification->Authority;
                    $data['license_number'] = $lrCertification->Number;
                    $data['start_date'] = $lrCertification->StartDate;
                    $data['end_date'] = $lrCertification->EndDate;
                    $this -> SocialLoginInsert( 'loginradius_certifications', $data );
                }
            }
            // Courses
            if (is_array($profileData['Courses']) && count($profileData['Courses']) > 0 ) {
                foreach ( $profileData['Courses'] as $lrCourse ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['course_id'] = $lrCourse->Id;
                    $data['course'] = $lrCourse->Name;
                    $data['course_number'] = $lrCourse->Number;
                    $this -> SocialLoginInsert( 'loginradius_courses', $data );
                }
            }
            // Volunteer
            if (is_array($profileData['Volunteer']) && count($profileData['Volunteer']) > 0 ) {
                foreach ( $profileData['Volunteer'] as $lrVolunteer ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['volunteer_id'] = $lrVolunteer->Id;
                    $data['role'] = $lrVolunteer->Role;
                    $data['organization'] = $lrVolunteer->Organization;
                    $data['cause'] = $lrVolunteer->Cause;
                    $this -> SocialLoginInsert( 'loginradius_volunteer', $data );
                }
            }
            // Recommendations received
            if (is_array($profileData['RecommendationsReceived']) && count($profileData['RecommendationsReceived']) > 0 ) {
                foreach ( $profileData['RecommendationsReceived'] as $lrRR ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['recommendation_id'] = $lrRR->Id;
                    $data['recommendation_type'] = $lrRR->RecommendationType;
                    $data['recommendation_text'] = $lrRR->RecommendationText;
                    $data['recommender'] = $lrRR->Recommender;
                    $this -> SocialLoginInsert( 'loginradius_recommendations_received', $data );
                }
            }
            // Languages
            if (is_array($profileData['Languages']) && count($profileData['Languages']) > 0 ) {
                foreach ( $profileData['Languages'] as $lrLanguage ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['language_id'] = $lrLanguage->Id;
                    $data['language'] = $lrLanguage->Name;
                    $this -> SocialLoginInsert( 'loginradius_languages', $data );
                }
            }
            // Patents
            if (is_array($profileData['Patents']) && count($profileData['Patents']) > 0 ) {
                foreach ( $profileData['Patents'] as $lrPatent ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['patent_id'] = $lrPatent->Id;
                    $data['title'] = $lrPatent->Title;
                    $data['date'] = $lrPatent->Date;
                    $this -> SocialLoginInsert( 'loginradius_patents', $data );
                }
            }
            // Favorites
            if (is_array($profileData['Favorites']) && count($profileData['Favorites']) > 0 ) {
                foreach ( $profileData['Favorites'] as $lrFavorite ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['social_id'] = $lrFavorite->Id;
                    $data['name'] = $lrFavorite->Name;
                    $data['type'] = $lrFavorite->Type;
                    $this -> SocialLoginInsert( 'loginradius_favorites', $data );
                }
            }
        }
        // insert contacts if option is selected
        if (in_array($profileData['Provider'], array('twitter', 'facebook', 'linkedin', 'google', 'yahoo')) && in_array('contacts', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
            if ( isset( $contacts -> Data ) && is_array( $contacts -> Data ) && count( $contacts -> Data ) > 0 ) {
                foreach ($contacts -> Data as $contact) {
                    // collect social IDs of the contacts
                    if ($profileData['Provider'] == 'yahoo' || $profileData['Provider'] == 'google') {
                        $this -> loginRadiusContactIds[] = $contact->EmailID;
                    } else {
                        $this -> loginRadiusContactIds[] = $contact->ID;
                    }
                    // create array to insert data
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['provider'] = $profileData['Provider'];
                    $data['name'] = $contact->Name;
                    $data['email'] = $contact->EmailID;
                    $data['phone_number'] = $contact->PhoneNumber;
                    $data['social_id'] = $contact->ID;
                    $data['profile_url'] = $contact->ProfileUrl;
                    $data['image_url'] = $contact->ImageUrl;
                    $data['status'] = $contact->Status;
                    $data['industry'] = $contact->Industry;
                    $data['country'] = $contact->Country;
                    $data['gender'] = $contact->Gender;
                    $this -> SocialLoginInsert( 'loginradius_contacts', $data);
                }
            }
        }
        // insert facebook events if option is selected
        if ($profileData['Provider'] == 'facebook' && in_array('events', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $events = $this -> blockObj -> loginradius_get_events( $this -> loginRadiusAccessToken );
            if (is_array($events) && count($events) > 0) {
                foreach ($events as $event) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['event_id'] = $event->ID;
                    $data['event'] = $event->Name;
                    $data['start_time'] = $event->StartTime;
                    $data['rsvp_status'] = $event->RsvpStatus;
                    $data['location'] = $event->Location;
                    $this -> SocialLoginInsert( 'loginradius_facebook_events', $data);
                }
            }
        }
        // insert posts if option is selected
        if ($profileData['Provider'] == 'facebook' && in_array('posts', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $posts = $this -> blockObj -> loginradius_get_posts( $this -> loginRadiusAccessToken );
            if (is_array($posts) && count($posts) > 0) {
                foreach ($posts as $post) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['post_id'] = $post->ID;
                    $data['from_name'] = $post->Name;
                    $data['title'] = $post->Title;
                    $data['start_time'] = $post->StartTime;
                    $data['update_time'] = $post->UpdateTime;
                    $data['message'] = $post->Message;
                    $data['place'] = $post->Place;
                    $data['picture'] = $post->Picture;
                    $data['likes'] = $post->Likes;
                    $data['shares'] = $post->Share;
                    $this -> SocialLoginInsert( 'loginradius_facebook_posts', $data);
                }
            }
        }
        // insert LinkedIn Companies if option is selected
        if ($profileData['Provider'] == 'linkedin' && in_array('linkedin_companies', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $linkedInCompanies = $this -> blockObj -> loginradius_get_followed_companies( $this -> loginRadiusAccessToken );
            if (is_array($linkedInCompanies) && count($linkedInCompanies) > 0) {
                foreach ($linkedInCompanies as $company) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['company_id'] = $company->ID;
                    $data['company_name'] = $company->Name;
                    $this -> SocialLoginInsert( 'loginradius_linkedin_companies', $data);
                }
            }
        }
        // insert status if option is selected
        if (in_array($profileData['Provider'], array('twitter', 'facebook', 'linkedin')) && in_array('status', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $status = $this -> blockObj -> loginradius_get_status( $this -> loginRadiusAccessToken );
            if (is_array($status) && count($status) > 0) {
                foreach ($status as $lrStatus) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['provider'] = $profileData['Provider'];
                    $data['status_id'] = $lrStatus->Id;
                    $data['status'] = $lrStatus->Text;
                    $data['date_time'] = $lrStatus->DateTime;
                    $data['likes'] = $lrStatus->Likes;
                    $data['place'] = $lrStatus->Place;
                    $data['source'] = $lrStatus->Source;
                    $data['image_url'] = $lrStatus->ImageUrl;
                    $data['link_url'] = $lrStatus->LinkUrl;
                    $this -> SocialLoginInsert( 'loginradius_status', $data);
                }
            }
        }
        // insert mentions if option is selected
        if ($profileData['Provider'] == 'twitter' && in_array('mentions', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $mentions = $this -> blockObj -> loginradius_get_mentions( $this -> loginRadiusAccessToken );
            if (is_array($mentions) && count($mentions) > 0) {
                foreach ($mentions as $mention) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['mention_id'] = $mention->Id;
                    $data['tweet'] = $mention->Text;
                    $data['date_time'] = $mention->DateTime;
                    $data['likes'] = $mention->Likes;
                    $data['place'] = $mention->Place;
                    $data['source'] = $mention->Source;
                    $data['image_url'] = $mention->ImageUrl;
                    $data['link_url'] = $mention->LinkUrl;
                    $data['mentioned_by'] = $mention->Name;
                    $this -> SocialLoginInsert( 'loginradius_twitter_mentions', $data);
                }
            }
        }
        // insert groups if option is selected
        if (in_array($profileData['Provider'], array('facebook', 'linkedin')) && in_array('groups', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $groups = $this -> blockObj -> loginradius_get_groups( $this -> loginRadiusAccessToken );
            if (is_array($groups) && count($groups) > 0) {
                foreach ($groups as $group) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['provider'] = $profileData['Provider'];
                    $data['group_id'] = $group->ID;
                    $data['group_name'] = $group->Name;
                    $this -> SocialLoginInsert( 'loginradius_groups', $data);
                }
            }
        }
    }
    /**
     * Check which write permissions/options are enabled
     */
    public function login_radius_write_permissions($userId, $socialId)
    {
        $this -> login_radius_fetch_token( $this -> loginRadiusToken );
        global $loginRadiusSettingsAdvanced, $loginRadiusSettings, $loginRadiusObject;
        // facebook status post
        if ($this -> loginRadiusProvider == 'facebook' && $this -> blockObj -> facebookPostEnable() == '1') {
            // get parameters
            $title = trim($this -> blockObj -> facebookPostTitle());
            $url = trim($this -> blockObj -> facebookPostUrl());
            $status = trim($this -> blockObj -> facebookPostMessage());
            $description = trim($this -> blockObj -> facebookPostDescription());
            $this -> blockObj -> loginradius_post_status( $this -> loginRadiusAccessToken, $title, $url, '', $status, $title, $description );
            return true;
        }
        // twitter profile tweet
        if ($this -> loginRadiusProvider == 'twitter' && $this -> blockObj -> twitterPostEnable() == '1') {
            // get parameters
            $tweet = trim($this -> blockObj -> twitterTweet());
            // call API
            $this -> blockObj -> loginradius_post_status( $this -> loginRadiusAccessToken, '', '', '', $tweet, '', '' );
            // if twitter DM is disabled, return true
            if ($this -> blockObj -> twitterDMEnable() != '1') {
                return true;
            }
        }
        // linkedin post
        if ($this -> loginRadiusProvider == 'linkedin' && $this -> blockObj -> linkedinPostEnable() == '1') {
            // get parameters
            $title = trim($this -> blockObj -> linkedinPostTitle());
            $url = trim($this -> blockObj -> linkedinPostUrl());
            $imageUrl = trim($this -> blockObj -> linkedinPostImgurl());
            $message = trim($this -> blockObj -> linkedinPostMessage());
            // call API
            $this -> blockObj -> loginradius_post_status( $this -> loginRadiusAccessToken, $title, $url, $imageUrl, $message, '', '' );
            if ($this -> blockObj -> linkedinDMEnable() != '1') {
                return true;
            }
        }
        // twitter Direct Messages
        if ($this -> loginRadiusProvider == 'twitter' && $this -> blockObj -> twitterDMEnable() == '1') {
            $recipients = array();
            // get parameters
            $message = strip_tags(trim($this -> blockObj -> twitterDMMessage()));

            // send to all users
            if ($this -> blockObj -> twitterDMRecipients() == 'selected') {
                // get contacts' Social IDs
                $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
                // store temporary details
                // save values in session
                Mage::getSingleton('core/session') -> setLoginRadiusTemporaryData(array(
                                                                            'user_id' => $userId,
                                                                            'social_id' => $socialId,
                                                                            'token' => isset($_REQUEST['token']) ? $_REQUEST['token'] : $this -> loginRadiusToken,
                                                                            'provider' => $this -> loginRadiusProvider
                                                                        ));
                // display popup
                if(count($contacts -> Data) >=1) {
                login_radius_message_popup($this -> loginRadiusProvider, $contacts -> Data, '');
                return false;
            }
            } else {
                // get contacts
                if (count($this -> $loginRadiusContactIds) > 0) {
                    $recipients = $this -> $loginRadiusContactIds;
                } else {
                    // get contacts' Social IDs
                    $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
                    if ( isset( $contacts -> Data ) && is_array( $contacts -> Data ) && count( $contacts -> Data ) > 0 ) {
                        foreach ($contacts -> Data as $contact) {
                            $recipients[] = $contact->ID;
                        }
                    }
                }
                // call API
                $this -> blockObj -> loginradius_send_message( $this -> loginRadiusAccessToken, $recipients, '', $message, 'twitter' );
                return true;
            }
        }
        // LinkedIn Direct Messages
        if ($this -> loginRadiusProvider == 'linkedin' && $this -> blockObj -> linkedinDMEnable() == '1') {
            $recipients = array();
            // get parameters
            $subject = strip_tags(trim($this -> blockObj -> linkedinDMSubject()));
            $message = strip_tags(trim($this -> blockObj -> linkedinDMMessage()));
            // send to all users
            if ($this -> blockObj -> linkedinDMRecipients() == 'selected') {
                // get contacts' Social IDs
                $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
                // store temporary details
                // save values in session
                Mage::getSingleton('core/session') -> setLoginRadiusTemporaryData(array(
                                                                            'user_id' => $userId,
                                                                            'social_id' => $socialId,
                                                                            'token' => isset($_REQUEST['token']) ? $_REQUEST['token'] : $this -> loginRadiusToken,
                                                                            'provider' => $this -> loginRadiusProvider
                                                                        ));
                // display popup
                if(count($contacts -> Data) >=1) {
                login_radius_message_popup($this -> loginRadiusProvider, $contacts -> Data, '');
                return false;
            }
            } else {
                // get contacts
                if (count($this -> $loginRadiusContactIds) > 0) {
                    $recipients = $this -> $loginRadiusContactIds;
                } else {
                    // get contacts' Social IDs
                    $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
                    if ( isset( $contacts -> Data ) && is_array( $contacts -> Data ) && count( $contacts -> Data ) > 0 ) {
                        foreach ($contacts -> Data as $contact) {
                            $recipients[] = $contact->ID;
                        }
                    }
                }
                // call API
                $this -> blockObj -> loginradius_send_message( $this -> loginRadiusAccessToken, $recipients, $subject, $message, 'linkedin' );
                return true;
            }
        }
        // Gmail Email message
        if ($this -> loginRadiusProvider == 'google' && $this -> blockObj -> gmailDMEnable() == 1) {
            $recipients = array();
            // get parameters
            $subject = strip_tags(trim($this -> blockObj -> gmailDMSubject()));
            $message = trim($this -> blockObj -> gmailDMMessage());
            if ($this -> blockObj -> gmailDMRecipients() == 'selected') {
                // get contacts' Social IDs
                $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
                // store temporary details
                Mage::getSingleton('core/session') -> setLoginRadiusTemporaryData(array(
                                                                            'user_id' => $userId,
                                                                            'social_id' => $socialId,
                                                                            'token' => isset($_REQUEST['token']) ? $_REQUEST['token'] : $this -> loginRadiusToken,
                                                                            'provider' => $this -> loginRadiusProvider
                                                                        ));
                // display popup
                if(count($contacts -> Data) >=1) {
                login_radius_message_popup($this -> loginRadiusProvider, $contacts -> Data, '');
                return false;
            }
            } else {
                // get contacts
                if (count($this -> $loginRadiusContactIds) > 0) {
                    $recipients = $this -> $loginRadiusContactIds;
                } else {
                    $recipients = array();
                    // get contacts
                    $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
                    if ( isset( $contacts -> Data ) && is_array( $contacts -> Data ) && count( $contacts -> Data ) > 0 ) {
                        foreach ($contacts -> Data as $contact) {
                            $recipients[] = $contact->EmailID;
                        }
                    }
                }
                // send email to al recipients
                foreach ($recipients as $email) {
                    $this -> loginRadiusEmail($subject, $message, $email, '');
                }
                return true;
            }
        }
        // Yahoo Email message
        if ($this -> loginRadiusProvider == 'yahoo' && $this -> blockObj -> yahooDMEnable() == 1) {
            $recipients = array();
            // get parameters
            $subject = strip_tags(trim($this -> blockObj -> yahooDMSubject()));
            $message = trim($this -> blockObj -> yahooDMSubject());
            if ($this -> blockObj -> yahooDMRecipients() == 'selected') {
                // get contacts' Social IDs
                $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
                // store temporary details
                Mage::getSingleton('core/session') -> setLoginRadiusTemporaryData(array(
                                                                            'user_id' => $userId,
                                                                            'social_id' => $socialId,
                                                                            'token' => isset($_REQUEST['token']) ? $_REQUEST['token'] : $this -> loginRadiusToken,
                                                                            'provider' => $this -> loginRadiusProvider
                                                                        ));
                // display popup
                login_radius_message_popup($this -> loginRadiusProvider, $contacts -> Data, '');
                return false;
            } else {
                // get contacts
                if (count($this -> $loginRadiusContactIds) > 0) {
                    $recipients = $this -> $loginRadiusContactIds;
                } else {
                    $recipients = array();
                    // get contacts
                    $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
                    if ( isset( $contacts -> Data ) && is_array( $contacts -> Data ) && count( $contacts -> Data ) > 0 ) {
                        foreach ($contacts -> Data as $contact) {
                            $recipients[] = $contact->EmailID;
                        }
                    }
                }
                // send email to all recipients
                foreach ($recipients as $email) {
                    $this -> loginRadiusEmail($subject, $message, $email, '');
                }
                return true;
            }
        }
        return true;
    }

    // update premium profile data
    public function login_radius_update_profile_data($userId, $profileData)
    {
        $token = isset($profileData['lrToken']) && $profileData['lrToken'] != '' ? $profileData['lrToken'] : '';
        $socialProfileCheckboxes = explode(',', $this -> blockObj -> getSocialProfileCheckboxes());
        if (!is_array($socialProfileCheckboxes)) {
            return;
        }
        // update basic profile data if option is selected
        if (in_array('basic', $socialProfileCheckboxes)) {
            $data = array();
            $data['prefix'] = $profileData['Prefix'];
            $data['first_name'] = $profileData['FirstName'];
            $data['middle_name'] = $profileData['MiddleName'];
            $data['last_name'] = $profileData['LastName'];
            $data['suffix'] = $profileData['Suffix'];
            $data['full_name'] = $profileData['FullName'];
            $data['nick_name'] = $profileData['NickName'];
            $data['profile_name'] = $profileData['ProfileName'];
            $data['birth_date'] = $profileData['BirthDate'];
            $data['gender'] = $profileData['Gender'];
            $data['country_code'] = $profileData['CountryCode'];
            $data['country_name'] = $profileData['CountryName'];
            $data['thumbnail_image_url'] = $profileData['thumbnail'];
            $data['image_url'] = $profileData['ImageUrl'];
            $data['local_country'] = $profileData['LocalCountry'];
            $data['profile_country'] = $profileData['ProfileCountry'];
            $this -> SocialLoginInsert(
                "loginradius_basic_profile_data",
                $data,
                true,
                array('user_id' => $userId)
            );

            // update emails
            if (count($profileData['Emails']) > 0) {
                // delete old emails
                $this->login_radius_delete("loginradius_emails", array( 'user_id = ?' => $userId ));
                foreach ($profileData['Emails'] as $lrEmail) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['email_type'] = $lrEmail->Type;
                    $data['email'] = $lrEmail->Value;
                    $this->SocialLoginInsert( 'loginradius_emails', $data);
                }
            }
        }
        // update extended location data if option is selected
        if (in_array('ex_location', $socialProfileCheckboxes) && isset($profileData['MainAddress'])) {
            $data = array();
            $data['main_address'] = $profileData['MainAddress'];
            $data['hometown'] = $profileData['HomeTown'];
            $data['state'] = $profileData['State'];
            $data['city'] = $profileData['City'];
            $data['local_city'] = $profileData['LocalCity'];
            $data['profile_city'] = $profileData['ProfileCity'];
            $data['profile_url'] = $profileData['ProfileUrl'];
            $data['local_language'] = $profileData['LocalLanguage'];
            $data['language'] = $profileData['Language'];
            $this -> SocialLoginInsert(
                "loginradius_extended_location_data",
                $data,
                true,
                array('user_id' => $userId)
            );
        }
        // update extended profile data if option is selected
        if (in_array('ex_profile', $socialProfileCheckboxes) && isset($profileData['Website'])) {
            $data = array();
            $data['website'] = $profileData['Website'];
            $data['favicon'] = $profileData['Favicon'];
            $data['industry'] = $profileData['Industry'];
            $data['about'] = $profileData['Bio'];
            $data['timezone'] = $profileData['TimeZone'];
            $data['verified'] = $profileData['Verified'];
            $data['last_profile_update'] = $profileData['LastProfileUpdate'];
            $data['created'] = $profileData['Created'];
            $data['relationship_status'] = $profileData['RelationshipStatus'];
            $data['quote'] = $profileData['Quote'];
            $data['interested_in'] = is_array($profileData['InterestedIn']) ? implode(', ', $profileData['InterestedIn']) : $profileData['InterestedIn'];
            $data['interests'] = $profileData['Interests'];
            $data['religion'] = $profileData['Religion'];
            $data['political_view'] = $profileData['PoliticalView'];
            $data['https_image_url'] = $profileData['HttpsImageUrl'];
            $data['followers_count'] = $profileData['FollowersCount'];
            $data['friends_count'] = $profileData['FriendsCount'];
            $data['is_geo_enabled'] = $profileData['IsGeoEnabled'];
            $data['total_status_count'] = $profileData['TotalStatusCount'];
            $data['number_of_recommenders'] = $profileData['NumberOfRecommenders'];
            $data['honors'] = $profileData['Honors'];
            $data['associations'] = $profileData['Associations'];
            $data['hirable'] = $profileData['Hirable'];
            $data['repository_url'] = $profileData['RepositoryUrl'];
            $data['age'] = $profileData['Age'];
            $data['professional_headline'] = $profileData['ProfessionalHeadline'];
            $data['provider_access_token'] = $profileData['ProviderAccessToken'];
            $data['provider_token_secret'] = $profileData['ProviderTokenSecret'];
            $this -> SocialLoginInsert(
                "loginradius_extended_profile_data",
                $data,
                true,
                array('user_id' => $userId)
            );
            // positions
            if (is_array($profileData['Positions']) && count($profileData['Positions']) > 0) {
                $companyResult = $this->loginRadiusRead('loginradius_positions', 'get company ids', array($userId), true);
                $companyIdsArray = $companyResult -> fetchAll();
                $companyIds = array();
                foreach ($companyIdsArray as $arr) {
                    $companyIds[] = $arr['company'];
                }
                // delete the companies matching the ids
                $loginRadiusConn = Mage::getSingleton('core/resource')
                                ->getConnection('core_write');
                try{
                    $loginRadiusConn -> query('delete from '.getMazeTable("loginradius_companies").' where id in ('.implode(',', $companyIds).')');
                }catch(Exception $e) {
                }
                $this->login_radius_delete( 'loginradius_positions', array( 'user_id = ?' => $userId ));
                foreach ($profileData['Positions'] as $lrPosition) {
                    // companies
                    if (isset($lrPosition->Comapny)) {
                        $temp = array();
                        $temp['id'] = NULL;
                        $temp['company_name'] = $lrPosition->Comapny->Name;
                        $temp['company_type'] = $lrPosition->Comapny->Type;
                        $temp['industry'] = $lrPosition->Comapny->Industry;
                        $tempId = $this->SocialLoginInsert( 'loginradius_companies', $temp );
                    }
                    // positions
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['position'] = $lrPosition->Position;
                    $data['summary'] = $lrPosition->Summary;
                    $data['start_date'] = $lrPosition->StartDate;
                    $data['end_date'] = $lrPosition->EndDate;
                    $data['is_current'] = $lrPosition->IsCurrent;
                    $data['company'] = isset($tempId) ? $tempId : NULL;
                    $data['location'] = $lrPosition->Location;
                    $this->SocialLoginInsert( 'loginradius_positions', $data );
                }
            }
            // education
            if (is_array($profileData['Educations']) && count($profileData['Educations']) > 0) {
                $this->login_radius_delete( 'loginradius_education', array( 'user_id = ?' => $userId ));
                foreach ($profileData['Educations'] as $education) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['school'] = $education->School;
                    $data['year'] = $education->year;
                    $data['type'] = $education->type;
                    $data['notes'] = $education->notes;
                    $data['activities'] = $education->activities;
                    $data['degree'] = $education->degree;
                    $data['field_of_study'] = $education->fieldofstudy;
                    $data['start_date'] = $education->StartDate;
                    $data['end_date'] = $education->EndDate;
                    $this->SocialLoginInsert( 'loginradius_education', $data );
                }
            }
            // phone numbers
            if (is_array($profileData['PhoneNumbers']) && count($profileData['PhoneNumbers']) > 0 ) {
                $this->login_radius_delete( 'loginradius_phone_numbers', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['PhoneNumbers'] as $lrPhoneNumber ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['number_type'] = $lrPhoneNumber->PhoneType;
                    $data['phone_number'] = $lrPhoneNumber->PhoneNumber;
                    $this->SocialLoginInsert( 'loginradius_phone_numbers', $data );
                }
            }
            // IM Accounts
            if (is_array($profileData['IMAccounts']) && count($profileData['IMAccounts']) > 0 ) {
                $this->login_radius_delete( 'loginradius_IMaccounts', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['IMAccounts'] as $lrImacc ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['account_type'] = $lrImacc->AccountType;
                    $data['account_username'] = $lrImacc->AccountName;
                    $this->SocialLoginInsert( 'loginradius_IMaccounts', $data );
                }
            }
            // Addresses
            if (is_array($profileData['Addresses']) && count($profileData['Addresses']) > 0 ) {
                $this->login_radius_delete( 'loginradius_addresses', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['Addresses'] as $lraddress ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['type'] = $lraddress->Type;
                    $data['address_line1'] = $lraddress->Address1;
                    $data['address_line2'] = $lraddress->Address2 ;
                    $data['city'] = $lraddress->City;
                    $data['state'] = $lraddress->State;
                    $data['postal_code'] = $lraddress->PostalCode;
                    $data['region'] = $lraddress->Region;
                    $this->SocialLoginInsert( 'loginradius_addresses', $data );
                }
            }
            // Sports
            if (is_array($profileData['Sports']) && count($profileData['Sports']) > 0 ) {
                $this->login_radius_delete( 'loginradius_sports', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['Sports'] as $lrSport ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['sport_id'] = $lrSport->Id;
                    $data['sport'] = $lrSport->Name;
                    $this->SocialLoginInsert( 'loginradius_sports', $data );
                }
            }
            // Inspirational People
            if (is_array($profileData['InspirationalPeople']) && count($profileData['InspirationalPeople']) > 0 ) {
                $this->login_radius_delete( 'loginradius_inspirational_people', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['InspirationalPeople'] as $lrIP ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['social_id'] = $lrIP->Id;
                    $data['name'] = $lrIP->Name;
                    $this->SocialLoginInsert( 'loginradius_inspirational_people', $data );
                }
            }
            // Skills
            if (is_array($profileData['Skills']) && count($profileData['Skills']) > 0 ) {
                $this->login_radius_delete( 'loginradius_skills', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['Skills'] as $lrSkill ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['skill_id'] = $lrSkill->Id;
                    $data['name'] = $lrSkill->Name;
                    $this->SocialLoginInsert( 'loginradius_skills', $data );
                }
            }
            // Current Status
            if (is_array($profileData['CurrentStatus']) && count($profileData['CurrentStatus']) > 0 ) {
                $this->login_radius_delete( 'loginradius_current_status', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['CurrentStatus'] as $lrCurrentStatus ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['status_id'] = $lrCurrentStatus->Id;
                    $data['status'] = $lrCurrentStatus->Text;
                    $data['source'] = $lrCurrentStatus->Source;
                    $data['created_date'] = $lrCurrentStatus->CreatedDate;
                    $this->SocialLoginInsert( 'loginradius_current_status', $data );
                }
            }
            // Certifications
            if (is_array($profileData['Certifications']) && count($profileData['Certifications']) > 0 ) {
                $this->login_radius_delete( 'loginradius_certifications', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['Certifications'] as $lrCertification ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['certification_id'] = $lrCertification->Id;
                    $data['certification_name'] = $lrCertification->Name;
                    $data['authority'] = $lrCertification->Authority;
                    $data['license_number'] = $lrCertification->Number;
                    $data['start_date'] = $lrCertification->StartDate;
                    $data['end_date'] = $lrCertification->EndDate;
                    $this->SocialLoginInsert( 'loginradius_certifications', $data );
                }
            }
            // Courses
            if (is_array($profileData['Courses']) && count($profileData['Courses']) > 0 ) {
                $this->login_radius_delete( 'loginradius_courses', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['Courses'] as $lrCourse ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['course_id'] = $lrCourse->Id;
                    $data['course'] = $lrCourse->Name;
                    $data['course_number'] = $lrCourse->Number;
                    $this->SocialLoginInsert( 'loginradius_courses', $data );
                }
            }
            // Volunteer
            if (is_array($profileData['Volunteer']) && count($profileData['Volunteer']) > 0 ) {
                $this->login_radius_delete( 'loginradius_volunteer', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['Volunteer'] as $lrVolunteer ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['volunteer_id'] = $lrVolunteer->Id;
                    $data['role'] = $lrVolunteer->Role;
                    $data['organization'] = $lrVolunteer->Organization;
                    $data['cause'] = $lrVolunteer->Cause;
                    $this->SocialLoginInsert( 'loginradius_volunteer', $data );
                }
            }
            // Recommendations received
            if (is_array($profileData['RecommendationsReceived']) && count($profileData['RecommendationsReceived']) > 0 ) {
                $this->login_radius_delete( 'loginradius_recommendations_received', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['RecommendationsReceived'] as $lrRR ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['recommendation_id'] = $lrRR->Id;
                    $data['recommendation_type'] = $lrRR->RecommendationType;
                    $data['recommendation_text'] = $lrRR->RecommendationText;
                    $data['recommender'] = $lrRR->Recommender;
                    $this->SocialLoginInsert( 'loginradius_recommendations_received', $data );
                }
            }
            // Languages
            if (is_array($profileData['Languages']) && count($profileData['Languages']) > 0 ) {
                $this->login_radius_delete( 'loginradius_languages', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['Languages'] as $lrLanguage ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['language_id'] = $lrLanguage->Id;
                    $data['language'] = $lrLanguage->Name;
                    $this->SocialLoginInsert( 'loginradius_languages', $data );
                }
            }
            // Patents
            if (is_array($profileData['Patents']) && count($profileData['Patents']) > 0 ) {
                $this->login_radius_delete( 'loginradius_patents', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['Patents'] as $lrPatent ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['patent_id'] = $lrPatent->Id;
                    $data['title'] = $lrPatent->Title;
                    $data['date'] = $lrPatent->Date;
                    $this->SocialLoginInsert( 'loginradius_patents', $data );
                }
            }
            // Favorites
            if (is_array($profileData['Favorites']) && count($profileData['Favorites']) > 0 ) {
                $this->login_radius_delete( 'loginradius_favorites', array( 'user_id = ?' => $userId ));
                foreach ( $profileData['Favorites'] as $lrFavorite ) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['social_id'] = $lrFavorite->Id;
                    $data['name'] = $lrFavorite->Name;
                    $data['type'] = $lrFavorite->Type;
                    $this->SocialLoginInsert( 'loginradius_favorites', $data );
                }
            }
        }
        // insert contacts if option is selected
        if (in_array($profileData['Provider'], array('twitter', 'facebook', 'linkedin', 'google', 'yahoo')) && in_array('contacts', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
            if ( isset( $contacts -> Data ) && is_array( $contacts -> Data ) && count( $contacts -> Data ) > 0 ) {
                $this->login_radius_delete( 'loginradius_contacts', array( 'user_id = ?' => $userId ));
                foreach ($contacts -> Data as $contact) {
                    // collect social IDs of the contacts
                    if ($profileData['Provider'] == 'yahoo' || $profileData['Provider'] == 'google') {
                        $this -> loginRadiusContactIds[] = $contact->EmailID;
                    } else {
                        $this -> loginRadiusContactIds[] = $contact->ID;
                    }
                    // create array to insert data
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['provider'] = $profileData['Provider'];
                    $data['name'] = $contact->Name;
                    $data['email'] = $contact->EmailID;
                    $data['phone_number'] = $contact->PhoneNumber;
                    $data['social_id'] = $contact->ID;
                    $data['profile_url'] = $contact->ProfileUrl;
                    $data['image_url'] = $contact->ImageUrl;
                    $data['status'] = $contact->Status;
                    $data['industry'] = $contact->Industry;
                    $data['country'] = $contact->Country;
                    $data['gender'] = $contact->Gender;
                    $this->SocialLoginInsert( 'loginradius_contacts', $data);
                }
            }
        }
        // insert facebook events if option is selected
        if ($profileData['Provider'] == 'facebook' && in_array('events', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $events = $this -> blockObj -> loginradius_get_events( $this -> loginRadiusAccessToken );
            if (is_array($events) && count($events) > 0) {
                $this->login_radius_delete( 'loginradius_facebook_events', array( 'user_id = ?' => $userId ));
                foreach ($events as $event) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['event_id'] = $event->ID;
                    $data['event'] = $event->Name;
                    $data['start_time'] = $event->StartTime;
                    $data['rsvp_status'] = $event->RsvpStatus;
                    $data['location'] = $event->Location;
                    $this->SocialLoginInsert( 'loginradius_facebook_events', $data);
                }
            }
        }
        // insert posts if option is selected
        if ($profileData['Provider'] == 'facebook' && in_array('posts', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $posts = $this -> blockObj -> loginradius_get_posts( $this -> loginRadiusAccessToken );
            if (is_array($posts) && count($posts) > 0) {
                $this->login_radius_delete( 'loginradius_facebook_posts', array( 'user_id = ?' => $userId ));
                foreach ($posts as $post) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['post_id'] = $post->ID;
                    $data['from_name'] = $post->Name;
                    $data['title'] = $post->Title;
                    $data['start_time'] = $post->StartTime;
                    $data['update_time'] = $post->UpdateTime;
                    $data['message'] = $post->Message;
                    $data['place'] = $post->Place;
                    $data['picture'] = $post->Picture;
                    $data['likes'] = $post->Likes;
                    $data['shares'] = $post->Share;
                    $this->SocialLoginInsert('loginradius_facebook_posts', $data);
                }
            }
        }
        // insert LinkedIn Companies if option is selected
        if ($profileData['Provider'] == 'linkedin' && in_array('linkedin_companies', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $linkedInCompanies = $this -> blockObj -> loginradius_get_followed_companies( $this -> loginRadiusAccessToken );
            if (is_array($linkedInCompanies) && count($linkedInCompanies) > 0) {
                $this->login_radius_delete( 'loginradius_linkedin_companies', array( 'user_id = ?' => $userId ));
                foreach ($linkedInCompanies as $company) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['company_id'] = $company->ID;
                    $data['company_name'] = $company->Name;
                    $this->SocialLoginInsert( 'loginradius_linkedin_companies', $data);
                }
            }
        }
        // insert status if option is selected
        if (in_array($profileData['Provider'], array('twitter', 'facebook', 'linkedin')) && in_array('status', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $status = $this -> blockObj -> loginradius_get_status( $this -> loginRadiusAccessToken );
            if (is_array($status) && count($status) > 0) {
                $this->login_radius_delete( 'loginradius_status', array( 'user_id = ?' => $userId ));
                foreach ($status as $lrStatus) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['provider'] = $profileData['Provider'];
                    $data['status_id'] = $lrStatus->Id;
                    $data['status'] = $lrStatus->Text;
                    $data['date_time'] = $lrStatus->DateTime;
                    $data['likes'] = $lrStatus->Likes;
                    $data['place'] = $lrStatus->Place;
                    $data['source'] = $lrStatus->Source;
                    $data['image_url'] = $lrStatus->ImageUrl;
                    $data['link_url'] = $lrStatus->LinkUrl;
                    $this->SocialLoginInsert( 'loginradius_status', $data);
                }
            }
        }
        // insert mentions if option is selected
        if ($profileData['Provider'] == 'twitter' && in_array('mentions', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $mentions = $this -> blockObj -> loginradius_get_mentions( $this -> loginRadiusAccessToken );
            if (is_array($mentions) && count($mentions) > 0) {
                $this->login_radius_delete( 'loginradius_twitter_mentions', array( 'user_id = ?' => $userId ));
                foreach ($mentions as $mention) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['mention_id'] = $mention->Id;
                    $data['tweet'] = $mention->Text;
                    $data['date_time'] = $mention->DateTime;
                    $data['likes'] = $mention->Likes;
                    $data['place'] = $mention->Place;
                    $data['source'] = $mention->Source;
                    $data['image_url'] = $mention->ImageUrl;
                    $data['link_url'] = $mention->LinkUrl;
                    $data['mentioned_by'] = $mention->Name;
                    $this->SocialLoginInsert( 'loginradius_twitter_mentions', $data);
                }
            }
        }
        // insert groups if option is selected
        if (in_array($profileData['Provider'], array('facebook', 'linkedin')) && in_array('groups', $socialProfileCheckboxes)) {
            $this -> login_radius_fetch_token( $token );
            $groups = $this -> blockObj -> loginradius_get_groups( $this -> loginRadiusAccessToken );
            if (is_array($groups) && count($groups) > 0) {
                $this->login_radius_delete( 'loginradius_groups', array( 'user_id = ?' => $userId ));
                foreach ($groups as $group) {
                    $data = array();
                    $data['user_id'] = $userId;
                    $data['provider'] = $profileData['Provider'];
                    $data['group_id'] = $group->ID;
                    $data['group_name'] = $group->Name;
                    $this->SocialLoginInsert( 'loginradius_groups', $data);
                }
            }
        }
    }
    // create new customer
    public function socialLoginAddNewUser( $socialloginProfileData, $verify = false, $update = false, $customerId = '' )
    {
        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();
        if (!$update) {
            // add new user magento way
            $customer = Mage::getModel("customer/customer");
        } else {
            $customer = Mage::getModel('customer/customer') -> load($customerId);
        }
        $customer->website_id = $websiteId;
        $customer->setStore($store);
        if ($socialloginProfileData['FirstName'] != "") {
            $customer->firstname = $socialloginProfileData['FirstName'];
        }
        if (!$update) {
            $customer->lastname = $socialloginProfileData['LastName'] == "" ? $socialloginProfileData['FirstName'] : $socialloginProfileData['LastName'];
        } elseif ($update && $socialloginProfileData['LastName'] != "") {
            $customer->lastname = $socialloginProfileData['LastName'];
        }
        if (!$update) {
            $customer->email = $socialloginProfileData['Email'];
            $loginRadiusPwd = $customer->generatePassword(10);
            $customer->password_hash = md5( $loginRadiusPwd );
        }
        if ($socialloginProfileData['BirthDate'] != "") {
            $customer->dob = $socialloginProfileData['BirthDate'];
        }
        if ($socialloginProfileData['Gender'] != "") {
            $customer->gender = $socialloginProfileData['Gender'];
        }
        $customer->setConfirmation(null);
        $customer->save();

        // if updating user profile
        if ($update) {
            $addresses = $customer->getAddressesCollection();
            $matched = false;
            foreach ($addresses as $address) {
                $address = $address->toArray();
                if ($address['firstname'] == $socialloginProfileData['FirstName']
                     && $address['lastname'] == $socialloginProfileData['LastName']
                    && $address['country_id'] == ucfirst($socialloginProfileData['Country'])
                    && $address['city'] == ucfirst($socialloginProfileData['City'])
                    && $address['telephone'] == $socialloginProfileData['PhoneNumber']
                    && $address['company'] == ucfirst($socialloginProfileData['Industry'])
                    && $address['street'] == ucfirst($socialloginProfileData['Address'])) {
                    $matched = true;
                    // if profile data contains zipcode then match it with that in the address
                    if (isset($socialloginProfileData['Zipcode']) && $address['postcode'] != $socialloginProfileData['Zipcode']) {
                        $matched = false;
                    }
                    // if profile data contains province then match it with that in the address
                    if (isset($socialloginProfileData['Province']) && $address['region'] != $socialloginProfileData['Province']) {
                        $matched = false;
                    }
                }
                if ($matched) {
                    break;
                }
            }
        }
        $address = Mage::getModel("customer/address");
        if (!$update) {
            $address->setCustomerId($customer->getId());
        } else {
            $address->setCustomerId($customerId);
        }
        if (($update && !$matched) || !$update) {
            $address->firstname = $customer->firstname;
            $address->lastname = $customer->lastname;
            $address->country_id = ucfirst( $socialloginProfileData['Country'] ); //Country code here
            if (isset($socialloginProfileData['Zipcode'])) {
                $address->postcode = $socialloginProfileData['Zipcode'];
            }
            $address->city = ucfirst( $socialloginProfileData['City'] );
            // If country is USA, set up province
            if (isset($socialloginProfileData['Province'])) {
                $address->region = $socialloginProfileData['Province'];
            }
            $address->telephone = $socialloginProfileData['PhoneNumber'];
            $address->company = ucfirst( $socialloginProfileData['Industry'] );
            $address->street = ucfirst( $socialloginProfileData['Address'] );
            // set default billing, shipping address and save in address book
            $address -> setIsDefaultShipping('1') -> setIsDefaultBilling('1') -> setSaveInAddressBook('1');
            $address->save();
        }
        if (!$update) {
            // if profile data option is set, insert premium profile data in database
            $this -> login_radius_save_profile_data($customer->getId(), $socialloginProfileData);
        } else {
            // update premium profile data
            $this -> login_radius_update_profile_data($customer->getId(), $socialloginProfileData);
        }
        // add info in sociallogin table
        if ( !$verify ) {
            $fields = array();
            $fields['sociallogin_id'] = $socialloginProfileData['lrId'] ;
            $fields['entity_id'] = $customer->getId();
            $fields['avatar'] = $socialloginProfileData['thumbnail'] ;
            $fields['provider'] = $socialloginProfileData['Provider'] ;
            if (!$update) {
                $this->SocialLoginInsert( "sociallogin", $fields );
            } else {
                $this->SocialLoginInsert( "sociallogin", array('avatar' => $socialloginProfileData['thumbnail']), true, array('entity_id = ?' => $customerId) );
            }
            if (!$update) {
                $loginRadiusUsername = $socialloginProfileData['FirstName']." ".$socialloginProfileData['LastName'];
                // email notification to user
                if ( $this->blockObj->notifyUser() == "1" ) {
                    $loginRadiusMessage = $this->blockObj->notifyUserText();
                    if ( $loginRadiusMessage == "" ) {
                        $loginRadiusMessage = __("Welcome to ").$store->getGroup()->getName().". ".__("You can login to the store using following e-mail address and password:-");
                    }
                    $loginRadiusMessage .= "<br/>".
                                           __("Email : ").$socialloginProfileData['Email'].
                                           "<br/>".__("Password : ").$loginRadiusPwd;

                    $this->loginRadiusEmail( "Welcome ".$loginRadiusUsername."!", $loginRadiusMessage, $socialloginProfileData['Email'], $loginRadiusUsername );
                }
                // new user notification to admin
                if ( $this->blockObj->notifyAdmin() == "1" ) {
                    $loginRadiusAdminEmail = Mage::getStoreConfig('trans_email/ident_general/email');
                    $loginRadiusAdminName = Mage::getStoreConfig('trans_email/ident_general/name');
                    $loginRadiusMessage = trim($this->blockObj->notifyAdminText());
                    if ( $loginRadiusMessage == "" ) {
                        $loginRadiusMessage = __("New customer has been registered to your store with following details:-");
                    }
                    $loginRadiusMessage .= "<br/>".
                                           __("Name : ").$loginRadiusUsername."<br/>".
                                           __("Email : ").$socialloginProfileData['Email'];
                    $this->loginRadiusEmail( __("New User Registration"), $loginRadiusMessage, $loginRadiusAdminEmail, $loginRadiusAdminName );
                }
            }
            //login and redirect user
            $this->socialLoginUserLogin( $customer->getId(), $fields['sociallogin_id'], true, $this -> loginRadiusToken );
        }
        if ( $verify ) {
            $this->verifyUser( $socialloginProfileData['lrId'], $customer->getId(), $socialloginProfileData['thumbnail'], $socialloginProfileData['Provider'], $socialloginProfileData['Email'] );
        }
    }
    public function login_radius_delete($table, $condition)
    {
        $loginRadiusConn = Mage::getSingleton('core/resource')
                                ->getConnection('core_write');
        try{
            // delete query magento way
            $loginRadiusConn->delete(
               Mage::getSingleton('core/resource')->getTableName($table),
               $condition
            );
        }catch(Exception $e) {
        }
    }
    private function SocialLoginInsert( $lrTable, $lrInsertData, $update = false, $value = '' )
    {
        $connection = Mage::getSingleton('core/resource')
                            ->getConnection('core_write');
        $connection->beginTransaction();
        $sociallogin = getMazeTable($lrTable);
        if ( !$update ) {
            if ($lrTable == 'loginradius_facebook_events') {
                $query = "insert into ".$sociallogin." values ('".$lrInsertData['user_id']."', '".$lrInsertData['event_id']."', '".$lrInsertData['event']."', STR_TO_DATE('" . $lrInsertData['start_time'] . "', '%c/%e/%Y %r'), '".$lrInsertData['rsvp_status']."', '".$lrInsertData['location']."')";
                $connection -> query($query);
            } else {
                $connection->insert($sociallogin, $lrInsertData);
            }
        } else {
            // update query magento way
            $connection->update(
                $sociallogin,
                $lrInsertData,
                $value
            );
        }
        $connection->commit();
        if ($lrTable == 'loginradius_companies') {
            $loginRadiusConn = Mage::getSingleton('core/resource')
                                    ->getConnection('core_read');
            $result = $loginRadiusConn->raw_fetchRow("SELECT MAX(id) as LastID FROM `{$sociallogin}`");
            return $result['LastID'];
        }
    }

    private function SocialLoginShowLayout()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    private function loginRadiusDelete()
    {
        $loginRadiusConn = Mage::getSingleton('core/resource')
                                ->getConnection('core_write');
        try{
            // delete query magento way
            $loginRadiusConn->delete(
               Mage::getSingleton('core/resource')->getTableName('sociallogin'),
               array('entity_id = ?' => $loginRadiusUserId,
                      'sociallogin_id = ?' => trim($_GET['LoginRadiusUnlink']))
            );
        }catch(Exception $e) {
        }
    }
    private function loginRadiusRead( $table, $handle, $params, $result = false )
    {
        $socialLoginConn = Mage::getSingleton('core/resource')
                            ->getConnection('core_read');
        $tbl = getMazeTable($table);
        $websiteId = Mage::app()->getWebsite()->getId();
        $storeId = Mage::app()->getStore()->getId();
        $query = "";
        switch( $handle ) {
            case "email exists pop1":
                $query = "select entity_id from $tbl where email = '".$params[0]."' and website_id = $websiteId and store_id = $storeId";
                break;
            case "get user":
                $query = "select entity_id, verified from $tbl where sociallogin_id= '".$params[0]."'";
                break;
            case "get user2":
                $query = "select entity_id from $tbl where entity_id = ".$params[0]." and website_id = $websiteId and store_id = $storeId";
                break;
            case "email exists login":
                $query = "select * from $tbl where email = '".$params[0]."' and website_id = $websiteId and store_id = $storeId";
                break;
            case "email exists sl":
                $query = "select verified,sociallogin_id from $tbl where entity_id = '".$params[0]."' and provider = '".$params[1]."'";
                break;
            case "provider exists in sociallogin":
                $query = "select entity_id from $tbl where entity_id = '".$params[0]."' and provider = '".$params[1]."'";
                break;
            case "verification":
                $query = "select entity_id, provider from $tbl where vkey = '".$params[0]."'";
                break;
            case "verification2":
                $query = "select entity_id from $tbl where entity_id = ".$params[0]." and provider = '".$params[1]."' and vkey != '' ";
                break;
            case "get company ids":
                $query = "select company from $tbl where user_id = ".$params[0];
                break;
        }
        $select = $socialLoginConn->query($query);
        if ( $result ) {
            return $select;
        }
        if ( $rowArray = $select->fetch() ) {
            return true;
        }
        return false;
    }

    private function verifyUser( $slId, $entityId, $avatar, $provider, $email )
    {
        $vKey = md5(uniqid(rand(), TRUE));
        $data = array();
        $data['sociallogin_id'] = $slId;
        $data['entity_id'] = $entityId;
        $data['avatar'] = $avatar;
        $data['verified'] = "0";
        $data['vkey'] = $vKey;
        $data['provider'] = $provider;
        // insert details in sociallogin table
        $this->SocialLoginInsert( "sociallogin", $data );
        // send verification mail
        $message = __(Mage::helper('core')->htmlEscape(trim($this->blockObj->verificationText())));
        if ( $message == "" ) {
            $message = __("Please click on the following link or paste it in browser to verify your email:-");
        }
        $message .= "<br/>".Mage::getBaseUrl()."sociallogin/?loginRadiusKey=".$vKey;
        $this->loginRadiusEmail( __("Email verification"), $message, $email, $email);
        // show popup message
        SL_popUpWindow("Confirmation link has been sent to your email address. Please verify your email by clicking on confirmation link.", "", false );
        $this->SocialLoginShowLayout();
        return;
    }

    public function indexAction()
    {
        $this->blockObj = new Loginradius_Sociallogin_Block_Sociallogin();
        $this->loginRadiusPopMsg = trim($this->blockObj->getPopupText() );
        $this->loginRadiusPopMsg = $this->loginRadiusPopMsg == "" ? __("Please enter your email to proceed") : $this->loginRadiusPopMsg;
        $this->loginRadiusPopErr = trim($this->blockObj->getPopupError() );
        $this->loginRadiusPopErr = $this->loginRadiusPopErr == "" ? __("Email you entered is either invalid or already registered. Please enter a valid email.") : $this->loginRadiusPopErr;
        if (isset($_REQUEST['token'])) {
            $this->tokenHandle();
            $this->loadLayout();
            $this->renderLayout();
            return;
        }

        // email verification
        if ( isset($_GET['loginRadiusKey']) && !empty($_GET['loginRadiusKey']) ) {
            $loginRadiusVkey = trim( $_GET['loginRadiusKey'] );
            // get entity_id and provider of the vKey
            $result = $this->loginRadiusRead( "sociallogin", "verification", array( $loginRadiusVkey ), true );
            if ( $temp = $result->fetch() ) {
                // set verified status true at this verification key
                $tempUpdate = array("verified" => '1', "vkey" => '');
                $tempUpdateTwo = array("vkey = ?" => $loginRadiusVkey);
                $this->SocialLoginInsert( "sociallogin", $tempUpdate, true, $tempUpdateTwo );
                SL_popUpWindow("Your email has been verified. Now you can login to your account.", "", false );

                // check if verification for same provider is still pending on this entity_id
                if ( $this->loginRadiusRead( "sociallogin", "verification2", array( $temp['entity_id'], $temp['provider'] ) ) ) {
                    $tempUpdate = array("vkey" => '');
                    $tempUpdateTwo = array("entity_id = ?" => $temp['entity_id'], "provider = ?" => $temp['provider']);
                    $this->SocialLoginInsert( "sociallogin", $tempUpdate, true, $tempUpdateTwo );
                }
            }
        }

        $socialLoginProfileData = Mage::getSingleton('core/session')->getSocialLoginData();
        $sessionUserId = $socialLoginProfileData['lrId'];
        $loginRadiusPopProvider = $socialLoginProfileData['Provider'];
        $loginRadiusAvatar = $socialLoginProfileData['thumbnail'];

        if (isset($_POST['LoginRadiusRedSliderClick'])) {
            if (!empty($sessionUserId) ) {
                $loginRadiusProfileData = array();
                // address
                if (isset($_POST['loginRadiusAddress'])) {
                    $loginRadiusProfileData['Address'] = "";
                    $profileAddress = trim($_POST['loginRadiusAddress']);
                }
                // city
                if (isset($_POST['loginRadiusCity'])) {
                    $loginRadiusProfileData['City'] = "";
                    $profileCity = trim($_POST['loginRadiusCity']);
                }
                // country
                if (isset($_POST['loginRadiusCountry'])) {
                    $loginRadiusProfileData['Country'] = "";
                    $profileCountry = trim($_POST['loginRadiusCountry']);
                }
                // phone number
                if (isset($_POST['loginRadiusPhone'])) {
                    $loginRadiusProfileData['PhoneNumber'] = "";
                    $profilePhone = trim($_POST['loginRadiusPhone']);
                }
                // email
                if (isset($_POST['loginRadiusEmail'])) {
                    $email = trim($_POST['loginRadiusEmail']);
                    if ( !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email) ) {
                        if ($this->blockObj->getProfileFieldsRequired() == 1) {
                            $hideZipCountry = false;
                        } else {
                            $hideZipCountry = true;
                        }
                        SL_popUpWindow($this->loginRadiusPopMsg, $this->loginRadiusPopErr, true, $loginRadiusProfileData, true, $hideZipCountry);
                        $this->SocialLoginShowLayout();
                        return false;
                    }
                    // check if email already exists
                    $userId = $this->loginRadiusRead( "customer_entity", "email exists pop1", array($email), true );
                    if ( $rowArray = $userId->fetch() ) {  // email exists
                        //check if entry exists on same provider in sociallogin table
                        $verified = $this->loginRadiusRead( "sociallogin", "email exists sl", array( $rowArray['entity_id'], $loginRadiusPopProvider ), true );
                        if ( $rowArrayTwo = $verified->fetch() ) {
                            // check verified field
                            if ( $rowArrayTwo['verified'] == "1" ) {
                                // check sociallogin id
                                if ( $rowArrayTwo['sociallogin_id'] == $sessionUserId ) {
                                    $this->socialLoginUserLogin( $rowArray['entity_id'], $rowArrayTwo['sociallogin_id'] );
                                    return;
                                } else {
                                    SL_popUpWindow( $this->loginRadiusPopMsg, $this->loginRadiusPopErr, true, array(), true, true );
                                    $this->SocialLoginShowLayout();
                                    return;
                                }
                            } else {
                                // check sociallogin id
                                if ( $rowArrayTwo['sociallogin_id'] == $sessionUserId ) {
                                    SL_popUpWindow("Please verify your email to login", "", false );
                                    $this->SocialLoginShowLayout();
                                    return;
                                } else {
                                    // send verification email
                                    $this->verifyUser( $sessionUserId, $rowArray['entity_id'], $loginRadiusAvatar, $loginRadiusPopProvider, $email );
                                    return;
                                }
                            }
                        } else {
                            // send verification email
                            $this->verifyUser( $sessionUserId, $rowArray['entity_id'], $loginRadiusAvatar, $loginRadiusPopProvider, $email );
                            return;
                        }
                    }
                }
                // validate other profile fields
                if ((isset($profileAddress) && $profileAddress == "") || (isset($profileCity) && $profileCity == "") || (isset($profileCountry) && $profileCountry == "") || (isset($profilePhone) && $profilePhone == "")) {
                    SL_popUpWindow("", "Please fill all the fields", true, $loginRadiusProfileData, true);
                    $this->SocialLoginShowLayout();
                    return false;
                }
                $socialloginProfileData = Mage::getSingleton('core/session')->getSocialLoginData();
                // set provider class member variable
                $this -> loginRadiusProvider = $socialloginProfileData['Provider'];
                // set Lr token
                $this -> loginRadiusToken = $socialloginProfileData['lrToken'];
                // assign submitted profile fields to array
                // address
                if (isset($profileAddress)) {
                    $socialloginProfileData['Address'] = $profileAddress;
                }
                // city
                if (isset($profileCity)) {
                    $socialloginProfileData['City'] = $profileCity;
                }
                // Country
                if (isset($profileCountry)) {
                    $socialloginProfileData['Country'] = $profileCountry;
                }
                // Phone Number
                if (isset($profilePhone)) {
                    $socialloginProfileData['PhoneNumber'] = $profilePhone;
                }
                // Zipcode
                if (isset($_POST['loginRadiusZipcode'])) {
                    $socialloginProfileData['Zipcode'] = trim($_POST['loginRadiusZipcode']);
                }
                // Province
                if (isset($_POST['loginRadiusProvince'])) {
                    $socialloginProfileData['Province'] = trim($_POST['loginRadiusProvince']);
                }
                // Email
                if (isset($email)) {
                    $socialloginProfileData['Email'] = $email;
                    $verify = true;
                } else {
                    $verify = false;
                }
                Mage::getSingleton('core/session')->unsSocialLoginData();     // unset session
                $this->socialLoginAddNewUser( $socialloginProfileData, $verify );
            }
        } elseif ( isset($_POST['LoginRadiusPopupCancel']) ) {                 // popup cancelled
            Mage::getSingleton('core/session')->unsSocialLoginData();         // unset session
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
            header("Location:".$url);                                        // redirect to index page
            die;
        }

        // send message popup submission check
        if (isset($_POST['loginRadiusReferralSubmit'])) {
            // get temporary data saved in session before showing popup
            $sessionData = Mage::getSingleton('core/session') -> getLoginRadiusTemporaryData();
            $token = isset($sessionData['token']) ? $sessionData['token'] : '';
            // check if identifier has been tampered
            if ($token == '') {
                // redirect to home page
                header("Location:".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK));        // redirect to index page
                exit();
            }
            $provider = isset($sessionData['provider']) ? $sessionData['provider'] : '';
            if ($_POST['loginRadiusReferralSubmit'] == "Send Message") {
                // check if contacts are selected
                if (!isset($_POST['loginRadiusContacts']) || count($_POST['loginRadiusContacts']) <= 0) {
                    // get contacts' Social IDs
                    $this -> login_radius_fetch_token( $token );
                    $contacts = $this -> blockObj -> loginradius_get_contacts( $this -> loginRadiusAccessToken );
                    login_radius_message_popup($provider, $contacts -> Data, '', 'Please select contacts to send referral to.');
                    return;
                }
                if ($provider == 'twitter') {
                    // send message to the contacts selected
                    $this -> login_radius_fetch_token( $token );

                    $this -> blockObj -> loginradius_send_message( $this -> loginRadiusAccessToken, $_POST['loginRadiusContacts'], 'test', strip_tags(trim($this -> blockObj -> twitterDMMessage())), $provider );
                } elseif ($provider == 'linkedin') {
                    // send message to the contacts selected
                    $this -> login_radius_fetch_token( $token );
                    $this -> blockObj -> loginradius_send_message( $this -> loginRadiusAccessToken, $_POST['loginRadiusContacts'], strip_tags(trim($this -> blockObj -> linkedinDMSubject())), strip_tags(trim($this -> blockObj -> linkedinDMMessage())), $provider );
                } elseif ($provider == 'google') {
                    $subject = strip_tags(trim($this -> blockObj -> gmailDMSubject()));
                    $message = strip_tags(trim($this -> blockObj -> gmailDMMessage()));
                    // send email to all recipients
                    foreach ($_POST['loginRadiusContacts'] as $email) {
                        $this -> loginRadiusEmail($subject, $message, $email, '');
                    }
                } elseif ($provider == 'yahoo') {
                    $subject = strip_tags(trim($this -> blockObj -> yahooDMSubject()));
                    $message = strip_tags(trim($this -> blockObj -> yahooDMMessage()));
                    // send email to all recipients
                    foreach ($_POST['loginRadiusContacts'] as $email) {
                        $this -> loginRadiusEmail($subject, $message, $email, '');
                    }
                }
                // get user id and social id
                $userId = $sessionData['user_id'];
                $socialId = $sessionData['social_id'];
                // delete temporary data
                Mage::getSingleton('core/session') -> unsLoginRadiusTemporaryData();
                if ($userId == '' || $socialId == '') {
                    // redirect to home page
                    header("Location:".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK));        // redirect to index page
                    exit();
                }
                // login user
                $this -> socialLoginUserLogin($userId, $socialId, false, $token, $provider);
            } elseif ($_POST['loginRadiusReferralSubmit'] == "Skip") {
                // get user id and social id
                $userId = $sessionData['user_id'];
                $socialId = $sessionData['social_id'];
                // delete temporary data
                Mage::getSingleton('core/session') -> unsLoginRadiusTemporaryData();
                if ($userId == '' || $socialId == '') {
                    // redirect to home page
                    header("Location:".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK));        // redirect to index page
                    exit();
                }
                // login user
                $this -> socialLoginUserLogin($userId, $socialId, false, $token, $provider);
            } else {
                // delete temporary data
                Mage::getSingleton('core/session') -> unsLoginRadiusTemporaryData();
                // redirect to home page
                header("Location:".Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK));        // redirect to index page
                exit();
            }
        }
        $this->SocialLoginShowLayout();
    }

    /**
     * Fetch access token from LoginRadius
     */
    private function login_radius_fetch_token( $token = '' )
    {
        global $loginRadiusObject, $loginRadiusSettings;
        if ( $this -> loginRadiusAccessToken == '' ) {
            $this -> loginRadiusAccessToken = $this -> blockObj -> loginradius_fetch_access_token( $this->blockObj->getApiSecret(), isset( $_REQUEST['token'] ) ? $_REQUEST['token'] : $token );
        }
    }
}