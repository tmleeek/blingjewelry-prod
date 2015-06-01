<?php
// Define LoginRadius domain
define('LR_DOMAIN', 'api.loginradius.com');
class Loginradius_Sociallogin_Block_Sociallogin extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        if ( $this->horizontalShareEnable() == "1" || $this->verticalShareEnable() == "1" ) {
            $this->setTemplate('sociallogin/socialshare.phtml');
        }
    }
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    public function getSociallogin()
    { 
        if (!$this->hasData('sociallogin')) {
            $this->setData('sociallogin', Mage::registry('sociallogin'));
        }
        return $this->getData('sociallogin');
    }
    public function user_is_already_login()
    {
        if ( Mage::getSingleton('customer/session')->isLoggedIn() ) {
            return true;
        }
        return false;
    }
    public function loginEnable()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/loginEnable');
    }
    public function getApikey()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/appid');
    }
    public function getAvatar( $id )
    {
        $socialLoginConn = Mage::getSingleton('core/resource')
                            ->getConnection('core_read');
        $socialLoginTbl = Mage::getSingleton('core/resource')->getTableName("sociallogin");
        $select = $socialLoginConn->query("select avatar from $socialLoginTbl where entity_id = '$id' limit 1");
        if ( $rowArray = $select->fetch() ) {  
            if ( ($avatar = trim($rowArray['avatar'])) != "" ) {
                return $avatar;
            }
        }
        return false;
    }
    public function getShowDefault()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/showdefault');
    }
    public function getAboveLogin()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/aboveLogin');
    }
    public function getBelowLogin()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/belowLogin');
    }
    public function getAboveRegister()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/aboveRegister');
    }
    public function getBelowRegister()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/belowRegister');
    }
    public function getApiSecret()
    {     
        return trim(Mage::getStoreConfig('sociallogin_options/messages/appkey'));
    }
    public function getLoginRadiusTitle()
    {     
        return Mage::getStoreConfig('sociallogin_options/messages/loginradius_title');
    }
    public function iconSize()
    {     
        return Mage::getStoreConfig('sociallogin_options/messages/iconSize');
    }
    public function iconsPerRow()
    {     
        return Mage::getStoreConfig('sociallogin_options/messages/iconsPerRow');
    }
    public function backgroundColor()
    {     
        return Mage::getStoreConfig('sociallogin_options/messages/backgroundColor');
    }
    public function getRedirectOption()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/redirect');
    }
    public function getApiOption()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/api');
    }
    public function getProfileFieldsRequired()
    {
        return Mage::getStoreConfig('sociallogin_options/email_settings/profileFieldsRequired');
    }
    public function updateProfileData()
    {
        return Mage::getStoreConfig('sociallogin_options/email_settings/updateProfileData');
    }
    public function getEmailRequired()
    {
        return Mage::getStoreConfig('sociallogin_options/email_settings/emailrequired');
    }
    public function verificationText()
    {
        return Mage::getStoreConfig('sociallogin_options/email_settings/verificationText');
    }
    public function getPopupText()
    {
        return Mage::getStoreConfig('sociallogin_options/email_settings/popupText');
    }
    public function getPopupError()
    {
        return Mage::getStoreConfig('sociallogin_options/email_settings/popupError');
    }
    public function getLinking()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/socialLinking');
    }
    public function notifyUser()
    {
        return Mage::getStoreConfig('sociallogin_options/email_settings/notifyUser');
    }
    public function notifyUserText()
    {
        return Mage::getStoreConfig('sociallogin_options/email_settings/notifyUserText');
    }
    public function notifyAdmin()
    {
        return Mage::getStoreConfig('sociallogin_options/email_settings/notifyAdmin');
    }
    public function notifyAdminText()
    {
        return Mage::getStoreConfig('sociallogin_options/email_settings/notifyAdminText');
    }
    public function horizontalShareEnable()
    {
        return Mage::getStoreConfig('sociallogin_options/horizontalSharing/horizontalShareEnable');
    }
    public function verticalShareEnable()
    {
        return Mage::getStoreConfig('sociallogin_options/verticalSharing/verticalShareEnable');
    }
    public function horizontalShareProduct()
    {
        return Mage::getStoreConfig('sociallogin_options/horizontalSharing/horizontalShareProduct');
    }
    public function verticalShareProduct()
    {
        return Mage::getStoreConfig('sociallogin_options/verticalSharing/verticalShareProduct');
    }
    public function horizontalShareSuccess()
    {
        return Mage::getStoreConfig('sociallogin_options/horizontalSharing/horizontalShareSuccess');
    }
    public function verticalShareSuccess()
    {
        return Mage::getStoreConfig('sociallogin_options/verticalSharing/verticalShareSuccess');
    }
    public function sharingTitle()
    {
        return Mage::getStoreConfig('sociallogin_options/horizontalSharing/sharingTitle');
    }
    public function horizontalSharingTheme()
    {
        return Mage::getStoreConfig('sociallogin_options/horizontalSharing/horizontalSharingTheme');
    }
    public function verticalSharingTheme()
    {
        return Mage::getStoreConfig('sociallogin_options/verticalSharing/verticalSharingTheme');
    }
    public function verticalAlignment()
    {
        return Mage::getStoreConfig('sociallogin_options/verticalSharing/verticalAlignment');
    }
    public function offset()
    {
        return Mage::getStoreConfig('sociallogin_options/verticalSharing/offset');
    }
    public function horizontalSharingProviders()
    {
        return Mage::getStoreConfig('sociallogin_options/horizontalSharing/horizontalSharingProvidersHidden');
    }
    public function verticalSharingProviders()
    {
        return Mage::getStoreConfig('sociallogin_options/verticalSharing/verticalSharingProvidersHidden');
    }
    public function horizontalCounterProviders()
    {
        return Mage::getStoreConfig('sociallogin_options/horizontalSharing/horizontalCounterProvidersHidden');
    }
    public function verticalCounterProviders()
    {
        return Mage::getStoreConfig('sociallogin_options/verticalSharing/verticalCounterProvidersHidden');
    }
    public function getCallBack()
    {
        return Mage::getStoreConfig('sociallogin_options/messages/call');
    }
    public function getSocialProfileCheckboxes()
    {
        return Mage::getStoreConfig('sociallogin_advanced/socialProfileData/profileData');
    }
    public function facebookPostEnable()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/facebookEnable');
    }
    public function facebookPostUrl()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/facebookParamsUrl');
    }
    public function facebookPostTitle()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/facebookParamsTitle');
    }
    public function facebookPostDescription()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/facebookParamsDescription');
    }
    public function facebookPostMessage()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/facebookParamsMessage');
    }
    public function twitterPostEnable()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/twitterEnable');
    }
    public function twitterTweet()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/tweet');
    }
    public function linkedinPostEnable()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/linkedinEnable');
    }
    public function linkedinPostTitle()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/linkedinParamsTitle');
    }
    public function linkedinPostUrl()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/linkedinParamsUrl');
    }
    public function linkedinPostImgurl()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/linkedinParamsImgurl');
    }
    public function linkedinPostMessage()
    {
        return Mage::getStoreConfig('sociallogin_advanced/postMessage/linkedinParamsMessage');
    }
    public function twitterDMEnable()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/twitterEnable');
    }
    public function twitterDMRecipients()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/twitterRecipients');
    }
    public function twitterDMMessage()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/twitterParamsMessage');
    }
    public function linkedinDMEnable()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/linkedinEnable');
    }
    public function linkedinDMRecipients()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/linkedinRecipients');
    }
    public function linkedinDMSubject()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/linkedinParamsSubject');
    }
    public function linkedinDMMessage()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/linkedinParamsMessage');
    }
    public function gmailDMEnable()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/gmailEnable');
    }
    public function gmailDMRecipients()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/gmailRecipients');
    }
    public function gmailDMSubject()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/gmailParamsSubject');
    }
    public function gmailDMMessage()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/gmailParamsMessage');
    }
    public function yahooDMEnable()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/yahooEnable');
    }
    public function yahooDMRecipients()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/yahooRecipients');
    }
    public function yahooDMSubject()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/yahooParamsSubject');
    }
    public function yahooDMMessage()
    {
        return Mage::getStoreConfig('sociallogin_advanced/sendMessage/yahooParamsMessage');
    }
    public function getProfileResult($apiSecret) 
    { 
        if (isset($_REQUEST['token'])) {
            $validateUrl = "https://hub.loginradius.com/userprofile/".trim($apiSecret)."/".$_REQUEST['token'];
            return $this->loginradius_call_api($validateUrl);
        }
    }
    public function getApiResult($apiKey, $apiSecret) 
    { 
        if ( !empty($apiKey) && !empty($apiSecret) && preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $apiKey) && preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $apiSecret) ) {
            return true;
        } else {
            return false;
        }
    }

    /** 
     * Facebook Status Update. 
     */     
    public function login_radius_status_update($provider, $title, $url, $imageurl, $status, $caption, $description, $token = '')
    {
        $secret = $this -> getApiSecret();
        $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : $token;
        $validateUrl = 'https://hub.loginradius.com/status/update/'.$secret.'/' . $token . '?' . http_build_query(array(
            'to' => '',
            'title' => $title,
            'url' => $url,
            'imageurl' => $imageurl,
            'status' => $status,
            'caption' => $caption,
            'description' => $description
        ));
        $jsonResponse = $this->loginradius_call_api($validateUrl);
    }

    /** 
     * Twitter, LinkedIn direct message. 
     */     
    public function login_radius_direct_message($provider, $to, $subject, $message, $token = '')
    {
        if (isset($_REQUEST['token']) || $token != '') {
            $secret = $this -> getApiSecret();
            $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : $token;
            if ($provider == 'twitter') {
                foreach ($to as $id) {
                    $validateUrl = 'https://hub.loginradius.com/directmessage/'.$secret.'/' . $token . '?'.http_build_query(array(
                        'sendto' => $id,
                        'subject' => $subject,
                        'message' => $message
                    ));
                    $jsonResponse = $this->loginradius_call_api($validateUrl);
                }
            } else {
                $validateUrl = 'https://hub.loginradius.com/directmessage/'.$secret.'/' . $token . '?'.http_build_query(array(
                    'sendto' => implode(',', $to),
                    'subject' => $subject,
                    'message' => $message
                ));
                $jsonResponse = $this->loginradius_call_api($validateUrl);
            }
        }
    }

    /** 
     * Get extended profile data from LoginRadius. 
     */     
    public function login_radius_get_extended_data($api, $token = '')
    {
        if (isset($_REQUEST['token']) || $token != "") {
            $apiSecret = $this -> getApiSecret();
            $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : $token;
            switch($api){
                case 'contacts':
                    $validateUrl = "https://hub.loginradius.com/contacts/$apiSecret/".$token; 
                    break;
                case 'events':
                    $validateUrl = "https://hub.loginradius.com/GetEvents/$apiSecret/".$token;  
                    break;
                case 'posts':
                    $validateUrl = "https://hub.loginradius.com/GetPosts/$apiSecret/".$token;  
                    break;
                case 'groups':
                    $validateUrl = "https://hub.loginradius.com/GetGroups/$apiSecret/".$token;  
                    break;
                case 'linkedin_companies':
                    $validateUrl = "https://hub.loginradius.com/GetCompany/$apiSecret/".$token;  
                    break;
                case 'status':
                    $validateUrl = "https://hub.loginradius.com/status/get/$apiSecret/".$token;  
                    break;
                case 'mentions':
                    $validateUrl = "https://hub.loginradius.com/status/mentions/$apiSecret/".$token;  
                    break;
            }
            $userProfile = $this->loginradius_call_api($validateUrl);
            if (!isset($userProfile->errorcode)) {
                return $userProfile; 
            }
        } 
    }

    /**
     * LoginRadius function - It validates against GUID format of keys.
     * 
     * @param string $value data to validate.
     *
     * @return boolean If valid - true, else - false
     */ 
    public function loginradius_is_valid_guid($value)
    {
        return preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $value);
    }

    /**
     * LoginRadius function - Check, if it is a valid callback i.e. LoginRadius authentication token is set 
     *
     * @return boolean True, if a valid callback.
     */ 
    public function loginradius_is_callback()
    {
        if (isset($_REQUEST['token'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * LoginRadius function - Fetch LoginRadius access token after authentication. It will be valid for the specific duration of time specified in the response.
     *
     * @param string LoginRadius API Secret
     *
     * @return string LoginRadius access token.
     */ 
    public function loginradius_fetch_access_token($secret, $token = '')
    {
        if (!$this -> loginradius_is_valid_guid($secret)) {
            return false;
        }
        $token       = isset( $_REQUEST['token'] ) ? $_REQUEST['token'] : $token;
        if ( ! empty( $token ) ) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/access_token?token=".$token."&secret=".$secret;
            $response = json_decode($this->loginradius_call_api($validateUrl));
            if (isset($response -> access_token) && $response -> access_token != '') {
                return $response -> access_token;
            } else {
                return false;
            }
        }
    }

    /**
     * LoginRadius function - To fetch social profile data from the user's social account after authentication. The social profile will be retrieved via oAuth and OpenID protocols. The data is normalized into LoginRadius' standard data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw        If true, raw data is fetched
     *
     * @return object User profile data.
     */ 
    public function loginradius_get_user_profiledata($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/userprofile?access_token=".$accessToken;
        
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/userprofile/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To get the Albums data from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object User's albums data.
     */ 
    public function loginradius_get_photo_albums($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/album?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/album/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To fetch photo data from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param string $albumId ID of the album to fetch photos from
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object User's photo data.
     */ 
    public function loginradius_get_photos($accessToken, $albumId, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/photo?access_token=".$accessToken."&albumid=".$albumId;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/photo/raw?access_token=".$accessToken."&albumid=".$albumId;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To fetch check-ins data from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object User's check-ins.
     */ 
    public function loginradius_get_checkins($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/checkin?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/checkin/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To fetch user's audio files data from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object User's audio files data.
     */ 
    public function loginradius_get_audio($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/audio?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/audio/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - Post messages to the user's contacts. After using the Contact API, you can send messages to the retrieved contacts.
     *
     * @param string $accessToken LoginRadius access token
     * @param string $to          Social ID of the receiver
     * @param string $subject     Subject of the message
     * @param string $message     Message
     *
     * @return bool True on success, false otherwise
     */ 
    public function loginradius_send_message( $accessToken, $to, $subject, $message, $provider )
    {
        if ( $provider == 'twitter' ) {
            
            foreach ( $to as $id ) {
                $params = array( 
                    'access_token' => $accessToken,
                    'to' => $id,
                    'subject' => $subject,
                    'message' => $message
                );
                $url  = 'https://'.LR_DOMAIN.'/api/v2/message?'.http_build_query( $params );
                $this->loginradius_call_api($url, true);
            }
        } else {
            $params = array( 
                'access_token' => $accessToken,
                'to' => implode( ',', $to ),
                'subject' => $subject,
                'message' => $message
            );
            $url  = 'https://'.LR_DOMAIN.'/api/v2/message?'.http_build_query( $params );
            $this->loginradius_call_api($url, true);
        }
    }

    /**
     * LoginRadius function - To fetch user's contacts/friends/connections data from the user's social account. The data will normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param integer $nextCursor Offset to start fetching contacts from
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object User's contacts/friends/followers.
     */ 
    public function loginradius_get_contacts($accessToken, $nextCursor = '', $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/contact?access_token=".$accessToken."&nextcursor=".$nextCursor;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/contact/raw?access_token=".$accessToken."&nextcursor=".$nextCursor;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To get mention data from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object User's twitter mentions.
     */ 
    public function loginradius_get_mentions($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/mention?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/mention/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To fetch information of the people, user is following on Twitter.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object Information of the people, user is following.
     */ 
    public function loginradius_get_following($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/following?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/following/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To get the event data from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object User's event data.
     */ 
    public function loginradius_get_events($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/event?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/event/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To get posted messages from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object User's posted messages.
     */ 
    public function loginradius_get_posts($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/post?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/post/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To get the followed company's data in the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object Companies followed by user.
     */ 
    public function loginradius_get_followed_companies($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/company?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/company/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To get group data from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object Group data.
     */ 
    public function loginradius_get_groups($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/group?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/group/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To get the status messages from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object Status messages.
     */ 
    public function loginradius_get_status($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/status?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/status/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To update the status on the user's wall.
     * 
     * @param string $accessToken LoginRadius access token
     * @param string $title       Title for status message (Optional).
     * @param string $url         A web link of the status message (Optional).
     * @param string $imageurl    An image URL of the status message (Optional).
     * @param string $status      The status message text (Required).
     * @param string $caption     Caption of the status message (Optional).
     * @param string $description Description of the status message (Optional).
     *
     * @return boolean Returns true if successful, false otherwise.
     */ 
    public function loginradius_post_status($accessToken, $title, $url, $imageurl = '', $status, $caption, $description)
    {
        $url = "https://".LR_DOMAIN."/api/v2/status?" . http_build_query(array(
            'access_token' => $accessToken,
            'title' => $title,
            'url' => $url,
            'imageurl' => $imageurl,
            'status' => $status,
            'caption' => $caption,
            'description' => $description
        ));
        return json_decode($this->loginradius_call_api($url, true));
    }

    /**
     * LoginRadius function - To get videos data from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object Videos data.
     */ 
    public function loginradius_get_videos($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/video?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/video/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To get likes data from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object Videos data.
     */ 
    public function loginradius_get_likes($accessToken, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/like?access_token=".$accessToken;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/like/raw?access_token=".$accessToken;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To get the page data from the user's social account. The data will be normalized into LoginRadius' data format.
     *
     * @param string $accessToken LoginRadius access token
     * @param string $pageName Page name
     * @param boolean $raw If true, raw data is fetched
     *
     * @return object Page data.
     */ 
    public function loginradius_get_pages($accessToken, $pageName, $raw = false)
    {
        $validateUrl = "https://".LR_DOMAIN."/api/v2/page?access_token=".$accessToken."&pagename=".$pageName;
        if ($raw) {
            $validateUrl = "https://".LR_DOMAIN."/api/v2/page/raw?access_token=".$accessToken."&pagename=".$pageName;
            return $this->loginradius_call_api($validateUrl);
        }
        return json_decode($this->loginradius_call_api($validateUrl));
    }

    /**
     * LoginRadius function - To fetch data from the LoginRadius API URL.
     * 
     * @param string $validateUrl Target URL to fetch data from.
     *
     * @return string Data fetched from the LoginRadius API.
     */ 
    public function loginradius_call_api($validateUrl, $post = false, $method = '')
    {
        if ( $this->getApiOption() == 'curl' ) {
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $validateUrl);
            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curlHandle, CURLOPT_TIMEOUT, 15);
            curl_setopt($curlHandle, CURLOPT_ENCODING, 'json');
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
            if ($post) {
                curl_setopt($curlHandle, CURLOPT_POST, 1);
                curl_setopt($curlHandle, CURLOPT_POSTFIELDS, '');
            }
            if (ini_get('open_basedir') == '' && (ini_get('safe_mode') == 'Off' or !ini_get('safe_mode'))) {
                curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            } else {
                curl_setopt($curlHandle, CURLOPT_HEADER, 1); 
                $url = curl_getinfo($curlHandle, CURLINFO_EFFECTIVE_URL);
                curl_close($curlHandle);
                $curlHandle = curl_init(); 
                $url = str_replace('?', '/?', $url); 
                curl_setopt($curlHandle, CURLOPT_URL, $url); 
                curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            }
            $jsonResponse = curl_exec($curlHandle);
            $httpCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
            if (in_array($httpCode, array(400, 401, 403, 404, 500, 503)) && $httpCode != 200) {
                return "service connection timeout";
            } else {
                if (curl_errno($curlHandle) == 28) {
                    return "timeout";
                }
            }
            curl_close($curlHandle);
        } elseif ( $this->getApiOption() == 'fopen' ) {
            if ($post) {
                $postdata = http_build_query(array('var1' => 'val'));
                $options = array('http' =>
                    array(
                        'method'  => 'POST',
                        'timeout' => 10,
                        'header'  => 'Content-type: application/x-www-form-urlencoded',
                        'content' => $postdata
                    )
                );
                $context  = stream_context_create($options);
            } else {
                $context = null;
            }
            $jsonResponse = file_get_contents($validateUrl, false, $context);
            if (strpos(@$httpResponseHeader[0], "400") !== false || strpos(@$httpResponseHeader[0], "401") !== false || strpos(@$httpResponseHeader[0], "403") !== false || strpos(@$httpResponseHeader[0], "404") !== false || strpos(@$httpResponseHeader[0], "500") !== false || strpos(@$httpResponseHeader[0], "503") !== false) {
                return "service connection timeout";
            }
        } else {
            if ($post) {
                $method = 'POST';    
            } else {
                $method = 'GET';
            }
            try{
                $client = new Varien_Http_Client($validateUrl);
                $response = $client->request($method);
                $jsonResponse = $response->getBody();
            } catch(Exception $e) {
            }
        }  
        if ($jsonResponse) {
            return $jsonResponse;
        } else {
            return "something went wrong, Can not get api response.";
        }
        return $jsonResponse;
    }
}