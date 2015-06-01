<?php
function SL_popUpWindow( $loginRadiusPopupTxt, $socialLoginMsg = "", $loginRadiusShowForm = true, $profileData = array(), $emailRequired = true, $hideZipcode = false)
{
    $blockObj = new Loginradius_Sociallogin_Block_Sociallogin();
    ?>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <!--css of email block    -->
    <style type="text/css">
    .LoginRadius_overlay {
        background: none no-repeat scroll 0 0 rgba(127, 127, 127, 0.6);
        height: 100%;
        left: 0;
        overflow: auto;
        padding: 0px 20px 130px;
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 100001;
    }
    #popupouter{
        -moz-border-radius:4px;
        -webkit-border-radius:4px;
        border-radius:4px;
        margin-left:-185px;
        left:45%;    
        background:#f3f3f3;
        padding:1px 0px 1px 0px;
        width:432px;
        position: absolute;
        top:35%;
        z-index:9999;
        margin-top:-96px;
    }
    #popupinner {
        background: none repeat scroll 0 0 #FFFFFF;
        border-radius: 4px 4px 4px 4px;
        margin: 10px;
        overflow: auto;
        padding: 10px 8px 4px;
    }    
    #textmatter {
        color: #666666;
        font-family: Arial,Helvetica,sans-serif;
        font-size: 14px;
        margin: 10px 0;
        float:left
    }    
    .loginRadiusText{
        font-family:Arial, Helvetica, sans-serif;
        color:#a8a8a8;
        font-size:11px;
        border:#e5e5e5 1px solid;
        width:280px;
        height:27px;
        margin:5px 0px 15px 0px;
        float:left
    }
    .inputbutton{
        border:#dcdcdc 1px solid;
        -moz-border-radius:2px;
        -webkit-border-radius:2px;
        border-radius:2px;
        text-decoration:none;
        color:#6e6e6e;
        font-family:Arial, Helvetica, sans-serif;
        font-size:13px;
        cursor:pointer;
        background:#f3f3f3;
        padding:6px 7px 6px 8px;
        margin:0px 8px 0px 0px;
        float:left
    }
    .inputbutton:hover{
        border:#00ccff 1px solid;
        -moz-border-radius:2px;
        -webkit-border-radius:2px;
        border-radius:2px;
        khtml-border-radius:2px;
        text-decoration:none;
        color:#000000;
        font-family:Arial, Helvetica, sans-serif;
        font-size:13px;
        cursor:pointer;
        padding:6px 7px 6px 8px;
        -moz-box-shadow: 0px 0px  4px #8a8a8a;
        -webkit-box-shadow: 0px 0px  4px #8a8a8a;
        box-shadow: 0px 0px  4px #8a8a8a;
        background:#f3f3f3;
        margin:0px 8px 0px 0px;
    }
    #textdivpopup{
        text-align:right;
        font-family:Arial, Helvetica, sans-serif;
        font-size:11px;
        color:#000000;
    }
    .spanpopup{
        font-family:Arial, Helvetica, sans-serif;
        font-size:11px;
        color:#00ccff;
    }
    .span1{
        font-family:Arial, Helvetica, sans-serif;
        font-size:11px;
        color:#333333;
    }
    <!--[if IE]>
    .LoginRadius_content_IE
    {background:black;
    -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=90)";
    filter: alpha(opacity=90);
    }
    .loginRadiusDiv{
        float:left;
        margin: 0 0 4px 2px;
    }
    .loginRadiusDiv label{
        width: 94px;
        float: left;
        margin: 5px 10px 10px 0;
        display: block;
        text-align: left;
    }
    <![endif]-->
    </style>
    <script type="text/javascript">
    // variable to check if submit button of popup is clicked
    var loginRadiusPopupSubmit = true;
    // get trim() worked in IE 
    if (typeof String.prototype.trim !== 'function') {
          String.prototype.trim = function() {
            return this.replace(/^\s+|\s+$/g, ''); 
          }
    }
    // validate numeric data 
    function isNumber(n) {
      return !isNaN(parseFloat(n)) && isFinite(n);
    }
    // validate required fields form
    function loginRadiusValidateForm() {
        if (!loginRadiusPopupSubmit) {
            return true;
        }
        var loginRadiusForm = document.getElementById('loginRadiusForm');
        var loginRadiusErrorDiv = document.getElementById('textmatter');
        if (document.getElementById('loginRadiusCountry').value.trim() == "US") {
            var validateProvince = true;
        } else {
            var validateProvince = false;
        }
        for (var i = 0; i < loginRadiusForm.elements.length; i++) {
            if (!validateProvince && loginRadiusForm.elements[i].id == "loginRadiusProvince") {
                continue;
            }
            if (loginRadiusForm.elements[i].value.trim() == "") {
                loginRadiusErrorDiv.innerHTML = "<?php echo __("Please fill all the fields."); ?>";
                loginRadiusErrorDiv.style.backgroundColor = "rgb(255, 235, 232)";
                loginRadiusErrorDiv.style.border = "1px solid rgb(204, 0, 0)";
                loginRadiusErrorDiv.style.padding = "2px 5px";
                loginRadiusErrorDiv.style.width = "94%";
                loginRadiusErrorDiv.style.textAlign = "left";
                return false;
            }
            if (loginRadiusForm.elements[i].id == "loginRadiusEmail") {
                var email = loginRadiusForm.elements[i].value.trim();
                var atPosition = email.indexOf("@");
                var dotPosition = email.lastIndexOf(".");
                if (atPosition < 1 || dotPosition < atPosition+2 || dotPosition+2>=email.length) {
                    loginRadiusErrorDiv.innerHTML = "<?php echo trim($blockObj -> getPopupError()) != "" ? trim($blockObj -> getPopupError()) : __('Please enter a valid email address'); ?>";
                    loginRadiusErrorDiv.style.backgroundColor = "rgb(255, 235, 232)";
                    loginRadiusErrorDiv.style.border = "1px solid rgb(204, 0, 0)";
                    loginRadiusErrorDiv.style.padding = "2px 5px";
                    loginRadiusErrorDiv.style.width = "94%";
                    loginRadiusErrorDiv.style.textAlign = "left";
                    return false;
                }
            }
        }
        return true;
    }
    </script>
    </head>
    <body>
    <div id="fade" class="LoginRadius_overlay">    
    <div id="popupouter">
    <div id="popupinner">
    <div id="textmatter"><strong><?php echo __(Mage::helper('core')->htmlEscape($loginRadiusPopupTxt)); ?></strong></div>
    <div style="clear:both;"></div>
    <div style="color:red; text-align:justify"><?php echo __(Mage::helper('core')->htmlEscape($socialLoginMsg)); ?></div>
    <?php
    if ( $loginRadiusShowForm ) {
        ?>
        <form id="loginRadiusForm" method="post" action="" onSubmit="return loginRadiusValidateForm()">
        <?php
        if ($emailRequired) {
            ?>
            <div class="loginRadiusDiv">
            <label for="loginRadiusEmail"><?php echo __("Email"); ?> *</label>
            <input type="text" name="loginRadiusEmail" id="loginRadiusEmail" class="loginRadiusText" />
            </div>
            <?php
        }
        if (isset($profileData['Address']) && $profileData['Address'] == "") {
            ?>
            <div class="loginRadiusDiv">
            <label for="loginRadiusAddress"><?php echo __("Address"); ?> *</label>
            <input type="text" name="loginRadiusAddress" id="loginRadiusAddress" class="loginRadiusText" />
            </div>
            <?php
        }
        if (isset($profileData['City']) && $profileData['City'] == "") {
            ?>
            <div class="loginRadiusDiv">
            <label for="loginRadiusCity"><?php echo __("City"); ?> *</label>
            <input type="text" name="loginRadiusCity" id="loginRadiusCity" class="loginRadiusText" />
            </div>
            <?php
        }
        if (!$hideZipcode) {
            ?>
            <div class="loginRadiusDiv">
            <label for="loginRadiusCountry"><?php echo __("Country"); ?> *</label>
            <?php
            $countries = Mage::getResourceModel('directory/country_collection')
                                    ->loadData()
                                    ->toOptionArray(false);
            if (count($countries) > 0) { ?>
                <select onChange="if (this.value == 'US') { document.getElementById('loginRadiusProvinceContainer').style.display = 'block' } else { document.getElementById('loginRadiusProvinceContainer').style.display = 'none' }" name="loginRadiusCountry" id="loginRadiusCountry" class="loginRadiusText">
                    <option value="">-- <?php echo __("Please Select"); ?> --</option>
                    <?php
                foreach ($countries as $country): ?>
                        <option value="<?php echo $country['value'] ?>">
                            <?php echo $country['label'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                </div>
                <!-- United States province -->
                <div style="display:none" id="loginRadiusProvinceContainer" class="loginRadiusDiv">
                <label for="loginRadiusCountry"><?php echo __("State/Province"); ?> *</label>
                <select id="loginRadiusProvince" name="loginRadiusProvince" class="loginRadiusText">
                <option value="" selected="selected">-- <?php echo __("Please Select"); ?> --</option><option value="1"><?php echo __("Alabama"); ?></option><option value="2"><?php echo __("Alaska"); ?></option><option value="3"><?php echo __("American Samoa"); ?></option><option value="4"><?php echo __("Arizona"); ?></option><option value="5"><?php echo __("Arkansas"); ?></option><option value="6"><?php echo __("Armed Forces Africa"); ?></option><option value="7"><?php echo __("Armed Forces Americas"); ?></option><option value="8"><?php echo __("Armed Forces Canada"); ?></option><option value="9"><?php echo __("Armed Forces Europe"); ?></option><option value="10"><?php echo __("Armed Forces Middle East"); ?></option><option value="11"><?php echo __("Armed Forces Pacific"); ?></option><option value="12"><?php echo __("California"); ?></option><option value="13"><?php echo __("Colorado"); ?></option><option value="14"><?php echo __("Connecticut"); ?></option><option value="15"><?php echo __("Delaware"); ?></option><option value="16"><?php echo __("District of Columbia"); ?></option><option value="17"><?php echo __("Federated States Of Micronesia"); ?></option><option value="18"><?php echo __("Florida"); ?></option><option value="19"><?php echo __("Georgia"); ?></option><option value="20"><?php echo __("Guam"); ?></option><option value="21"><?php echo __("Hawaii"); ?></option><option value="22"><?php echo __("Idaho"); ?></option><option value="23"><?php echo __("Illinois"); ?></option><option value="24"><?php echo __("Indiana"); ?></option><option value="25"><?php echo __("Iowa"); ?></option><option value="26"><?php echo __("Kansas"); ?></option><option value="27"><?php echo __("Kentucky"); ?></option><option value="28"><?php echo __("Louisiana"); ?></option><option value="29"><?php echo __("Maine"); ?></option><option value="30"><?php echo __("Marshall Islands"); ?></option><option value="31"><?php echo __("Maryland"); ?></option><option value="32"><?php echo __("Massachusetts"); ?></option><option value="33"><?php echo __("Michigan"); ?></option><option value="34"><?php echo __("Minnesota"); ?></option><option value="35"><?php echo __("Mississippi"); ?></option><option value="36"><?php echo __("Missouri"); ?></option><option value="37"><?php echo __("Montana"); ?></option><option value="38"><?php echo __("Nebraska"); ?></option><option value="39"><?php echo __("Nevada"); ?></option><option value="40"><?php echo __("New Hampshire"); ?></option><option value="41"><?php echo __("New Jersey"); ?></option><option value="42"><?php echo __("New Mexico"); ?></option><option value="43"><?php echo __("New York"); ?></option><option value="44"><?php echo __("North Carolina"); ?></option><option value="45"><?php echo __("North Dakota"); ?></option><option value="46"><?php echo __("Northern Mariana Islands"); ?></option><option value="47"><?php echo __("Ohio"); ?></option><option value="48"><?php echo __("Oklahoma"); ?></option><option value="49"><?php echo __("Oregon"); ?></option><option value="50"><?php echo __("Palau"); ?></option><option value="51"><?php echo __("Pennsylvania"); ?></option><option value="52"><?php echo __("Puerto Rico"); ?></option><option value="53"><?php echo __("Rhode Island"); ?></option><option value="54"><?php echo __("South Carolina"); ?></option><option value="55"><?php echo __("South Dakota"); ?></option><option value="56"><?php echo __("Tennessee"); ?></option><option value="57"><?php echo __("Texas"); ?></option><option value="58"><?php echo __("Utah"); ?></option><option value="59"><?php echo __("Vermont"); ?></option><option value="60"><?php echo __("Virgin Islands"); ?></option><option value="61"><?php echo __("Virginia"); ?></option><option value="62"><?php echo __("Washington"); ?></option><option value="63"><?php echo __("West Virginia"); ?></option><option value="64"><?php echo __("Wisconsin"); ?></option><option value="65"><?php echo __("Wyoming"); ?></option></select>
                <?php
            } else {
                ?>
                <input type="text" name="loginRadiusCountry" id="loginRadiusCountry" class="loginRadiusText" />
                <?php
            }
            ?>
            </div>
            <div class="loginRadiusDiv">
            <label for="loginRadiusZipcode"><?php echo __("Zipcode"); ?> *</label>
            <input type="text" name="loginRadiusZipcode" id="loginRadiusZipcode" class="loginRadiusText" />
            </div>
        <?php
        }
        if (isset($profileData['PhoneNumber']) && $profileData['PhoneNumber'] == "") {
            ?>
            <div class="loginRadiusDiv">
            <label for="loginRadiusPhone"><?php echo __("Phone Number"); ?> *</label>
            <input type="text" name="loginRadiusPhone" id="loginRadiusPhone" class="loginRadiusText" />
            </div>
            <?php
        }
        ?>
        <input type="hidden" name="loginRadiusRedirect" value="<?php echo isset($_GET['redirect_to']) ? htmlspecialchars($_GET['redirect_to']) : '' ?>" />
        <div class="loginRadiusDiv">
        <input type="submit" id="LoginRadiusRedSliderClick" name="LoginRadiusRedSliderClick" value="<?php echo __("Submit"); ?>" onClick="loginRadiusPopupSubmit = true" class="inputbutton" />
        <input type="submit" value="<?php echo __("Cancel"); ?>" class="inputbutton" name="LoginRadiusPopupCancel" onClick="loginRadiusPopupSubmit = false" />
        </div>
        </form>
    <?php
    } else {
        ?>
        <input type="button" value="<?php echo __('Okay') ?>" class="inputbutton" onClick="location.href = '<?php echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK); ?>'" />
        <?php
    }
    ?>
    </div>
    </div>
    </div>
    <?php
}
/**
 * Function to show popup for sending message.
 */
function login_radius_message_popup($provider, $contacts, $identifier, $error = '')
{
        ?>
        <script>
        var loginRadiusReferralSubmit = null;
        function loginRadiusCheckAll(lrform, check) {
            var lrCheckList = lrform.elements['loginRadiusContacts[]'];
            if (typeof lrCheckList.length == "undefined") {
                lrCheckList.checked = (check ? 'checked' : '');
            } else {
                for (var i = 0; i < lrCheckList.length; i++) {
                    lrCheckList[i].checked = (check ? 'checked' : '');
                }
            }
        }
        function LoginRadiusReferralValidate(lrForm) {
            if (loginRadiusReferralSubmit === null || loginRadiusReferralSubmit == "Cancel") {
                return true;
            }
            var lrCheckList = lrForm.elements['loginRadiusContacts[]'];
            var lrValid = false;
            if (typeof lrCheckList.length != "undefined") {
                for (var i = 0; i < lrCheckList.length; i++) {
                    if (lrCheckList[i].checked === true) {
                        lrValid = true;
                        break;
                    }
                }
            } else {
                if (lrCheckList.checked === true) {
                    lrValid = true;
                }
            }
            if (!lrValid) {
                var loginRadiusErrorDiv = document.getElementById('loginRadiusError');
                loginRadiusErrorDiv.innerHTML = "Please select the contacts to send referral to.";
                loginRadiusErrorDiv.style.display = "block";
                document.getElementById('loginRadiusMiddiv').scrollTop = 0;
                return false;
            }
            return true;
        }
        </script>
        <style type="text/css">
        /* ----------- send message popup----------*/
        .LoginRadius_overlay 
        { 
            background: none no-repeat scroll 0 0 rgba(127, 127, 127, 0.6); 
            top: 0; 
            left: 0; 
            z-index: 100001; 
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            padding: 220px 20px 20px 20px; 
            padding-bottom: 130px;
            position:fixed
        } 
        .popup_main{
            width:506px !important;
            box-shadow:0px 4px 30px #B5B5B5 !important;
            -moz-box-shadow:0px 4px 30px #B5B5B5 !important;
            -webkit-box-shadow:0px 4px 30px #B5B5B5 !important;
            -moz-border-radius:8px !important;
            -webkit-border-radius:8px !important;
            border-radius:8px !important;
            margin-left:-253px !important;
            margin-top:-240px !important;
            left:50% !important;
            top:40% !important;
            position:fixed !important;
            z-index:999999 !important;
            border:#BABABA 1px solid !important;
            background:#FFFFFF !important;
            height:605px !important;
        }
        .popup_email{
            width:409px !important;
            box-shadow:0px 4px 30px #B5B5B5 !important;
            -moz-box-shadow:0px 4px 30px #B5B5B5 !important;
            -webkit-box-shadow:0px 4px 30px #B5B5B5 !important;
            -moz-border-radius:8px !important;
            -webkit-border-radius:8px !important;
            border-radius:8px !important;
            margin-left:-253px !important;
            margin-top:-240px !important;
            left:55% !important;
            top:70% !important;
            position:fixed !important;
            z-index:999999 !important;
            border:#BABABA 1px solid !important;
            background:#FFFFFF !important;
            padding-bottom:20px !important
        }
        
        .heading{
            background: #ececec;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#f8f8f8', endColorstr='#ececec');
            background: -webkit-gradient(linear, left top, left bottom, from(#f8f8f8), to(#ececec)); 
            background: -moz-linear-gradient(top,  #f8f8f8,  #ececec);
            height:46px !important;
            float:left !important;
            text-align:left !important;
            width:100% !important;
            color:#464241 !important;
            font-family:Arial, Helvetica, sans-serif !important;
            font-size:18px !important;
            font-weight:normal !important;
            padding:7px 0 0 0 !important;
            border-top-right-radius:8px !important;
            border-top-left-radius:8px !important;
            border-bottom: 1px solid #888888;
        }
        
        .heading .spanlogo{
            width:40px !important; float:left !important; padding:0 0 0 12px !important;
        }
        .heading .spantext{
            width:340px !important; float:left !important; padding:5px 0 0 10px !important;
            color:#D00604 !important;
        }
        sub{
            color:#D00604 !important;
            font-size:15px !important;
        }
        
        .middiv{
            margin: 0 auto !important;
            width: 94% !important;
            clear:both !important;
            overflow-y:scroll !important;
            height:500px !important
        }
        
        .close_button{
            position:absolute !important;
            z-index:99 !important;
            margin: -13px 2px 3px 490px !important;
        }
        
        
        .bottomheading{
            background:url(../images/heading_back.jpg) repeat-x top !important;
            height:22px !important;
            float:left !important;
            text-align:right !important;
            width:100% !important;
            color:#464241 !important;
            font-family:Arial, Helvetica, sans-serif !important;
            font-size:12px !important;
            font-weight:normal !important;
            border-bottom-right-radius:8px !important;
            border-bottom-left-radius:8px !important;
            border-top:#BABABA 1px solid !important;
            position:relative !important;
            bottom: -13px !important;
            
        }
        
        .inputbox{
             background: url("../images/input-bg.png") no-repeat scroll 0 0 transparent !important;
            border: 1px inset #ccc !important;
            color: #000000 !important;
            height: 27px !important;
            width:213px !important;
            
        }
        .submitbutton{
            display:block !important;
            border:#dcdcdc 1px solid !important;
            -moz-border-radius:3px !important;
            -webkit-border-radius:3px !important;
            border-radius:3px !important;
            khtml-border-radius:3px !important;
            text-decoration:none !important;
            behavior: url(border-radius.htc) !important;
            color:#6e6e6e !important;
            font-family:Arial, Helvetica, sans-serif !important;
            font-size:13px !important;
            padding:6px 17px 6px 28px !important;
            background:url(slider/send.png) no-repeat #f3f3f3 10px 7px !important;
        }
        
        .submitbutton:hover{
            display:block !important;
            border:#dcdcdc 1px solid !important;
            -moz-border-radius:3px !important;
            -webkit-border-radius:3px !important;
            border-radius:3px !important;
            khtml-border-radius:3px !important;
            text-decoration:none !important;
            behavior: url(border-radius.htc) !important;
            color:#000000 !important;
            font-family:Arial, Helvetica, sans-serif !important;
            font-size:13px !important;
            padding:6px 17px 6px 28px !important;
            background:url(slider/send.png) no-repeat #EAEAEA 10px 7px !important;
            cursor:pointer !important;
        }
        
        
        @-moz-document url-prefix() {
        
            .bottomheading { bottom: -20px !important; }
        
        }
        .form{
            color:#666666 !important; font-family:Arial, Helvetica, sans-serif !important; font-size:13px !important;
        }
        .form .div{
            float:left !important;
            margin-right:35px !important;
        }
        .form .divsubmit{
            width:auto !important; float:left !important; margin:20px 0 !important;
        }
        
        .form label{
            width: 160px !important; float:left !important; margin:3px 10px 10px 0 !important;
            display:block !important;
        }
        .bottomheading .spanbottom{
             padding: 7px 10px 1px 10px !important; 
        }
        .bottomheading .spanbottomlogo{
             color:#0099FF !important;
        }
        
        
        .button {
            display: inline-block !important;
            outline: none !important;
            cursor: pointer !important;
            text-align: center !important;
            text-decoration: none !important;
            font: 14px/100% Arial, Helvetica, sans-serif !important;
            padding: 5px 15px 5px !important;
            -webkit-border-radius: 4px !important; 
            -moz-border-radius: 4px !important;
            border-radius: 4px !important;
            width:auto !important
        }
        .blue {
            color: #fef4e9 !important;
            border:#1D6DC1 1px solid !important;
            background: #f78d1d !important;
            background: -webkit-gradient(linear, left top, left bottom, from(#77BAFE), to(#4196EE)) !important;
            background: -moz-linear-gradient(top,  #77BAFE,  #4196EE) !important;
            filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#E43A16', endColorstr='#D00604') !important;
        }
        .blue:hover {
            background: #f47c20 !important;
            border: #1D6DC1 1px solid !important;
            background: -webkit-gradient(linear, left top, left bottom, from(#98CBFE), to(#6FB0F2)) !important;
            background: -moz-linear-gradient(top, #98CBFE, #6FB0F2) !important;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#98CBFE', endColorstr='#6FB0F2') !important;
        }
        .loginRadiusCustomSelect{
            background: url("../images/input-bg.png") no-repeat scroll 0 0 transparent !important;
            border: 1px inset #ccc !important;
            color: #000000 !important;
            height: 27px !important;
            width: auto !important;
        }
        .div label{
            color:#000000 !important
        }
        .grey{
            color: #fef4e9 !important;
            border:#ccc 1px solid !important;
            background: #CCCCCC !important;
            background: -webkit-gradient(linear, left top, left bottom, from(#ccc), to(#999)) !important;
            background: -moz-linear-gradient(top,  #ccc,  #999) !important;
            filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='#E43A16', endColorstr='#D00604') !important;
        }
        .green{
            color: #fef4e9 !important;
            border:#669E00 1px solid !important;
            background: #f78d1d !important;
            background: -webkit-gradient(linear, left top, left bottom, from(#8DC300), to(#6BA500)) !important;
            background: -moz-linear-gradient(top, #8DC300, #6BA500) !important;
            filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#8DC300', endColorstr='#6BA500') !important;
        }
        .green:hover {
        background: #f47c20 !important;
        border:#669E00 1px solid !important;
        background: -webkit-gradient(linear, left top, left bottom, from(#C5E276), to(#A8D32E)) !important;
        background: -moz-linear-gradient(top, #C5E276, #A8D32E) !important;
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#C5E276', endColorstr='#A8D32E') !important;
        }
        .loginRadiusSubmitText{
            background-color: transparent;
            border: medium none;
            cursor: pointer;
        }
        .loginRadiusSubmitText:hover{
            text-decoration:underline
        }
        #loginRadiusError{
            color:red;
            display:none
        }
        </style>
        <div class="LoginRadius_overlay" id="fade" ></div>
            <div class="popup_main" id="lrReferralPopup">
                <form method="post" name="loginRadiusReferralForm" action="" onSubmit="return LoginRadiusReferralValidate(this)" class="form">
                <div class="heading">
                    <span class="spantext">Send Message</span>
                </div>
                <div style="clear:both"></div>
                <div class="middiv" id="loginRadiusMiddiv">
                    <div style="margin-bottom:20px; margin-top: 10px;"><span>Please select your contacts from the list mentioned below to whom you want to send message</span></div>
                    <?php
    if ($error != "") {
        ?>
        <div style="color:red">Please select the names to send referral to.</div>
        <?php
    }
    ?>
    <div id="loginRadiusError" style="height:20px"></div>
    <?php
    for ($i = 0; $i<count($contacts); $i++) {
        if ($provider == "live" && !is_email($contacts[$i]->EmailID)) {
            continue;
        }
        ?>
        <div class="div">
        <input style="float:left; margin-top:5px" type="checkbox" checked="checked" id="loginRadiusContact<?php echo $i; ?>" name="loginRadiusContacts[]" value="<?php
        if (in_array($provider, array('facebook', 'twitter', 'linkedin'))) {
            echo $contacts[$i]->ID;
        } else {
            echo $contacts[$i]->EmailID;
        }
        ?>" />
        <?php
        if ($provider != "yahoo") {
            ?>
            <label for="loginRadiusContact<?php echo $i; ?>"><?php echo ucfirst($contacts[$i]->Name); ?></label>
            <?php
        }
        if ($provider == "facebook") {
            $params[] = $contacts[$i]->ID;
            ?>
            <a onclick='loginRadiusPostToFeed("<?php echo $contacts[$i]->ID; ?>"); return false;' style="color:blue; cursor:pointer">Post to Feed</a>
            <?php
        }
        if (isset($contacts[$i]->EmailID) && trim($contacts[$i]->EmailID) != "") {
            ?>
            <label for="loginRadiusContact<?php echo $i; ?>"><?php echo $contacts[$i]->EmailID; ?></label>
            <?php
        }
        ?>
        </div>
        <?php
        if ($provider != "yahoo") {
            ?>
            <input type="hidden" name="loginRadiusReferralNames[]" value="<?php echo ucfirst($contacts[$i]->Name); ?>" />
            <input type="hidden" name="loginRadiusReferralIds[]" value="<?php echo ($contacts[$i]->ID != "") ? $contacts[$i]->ID : $contacts[$i]->EmailID; ?>" />
            <?php
        }
        if (isset($contacts[$i]->Email) && $contacts[$i]->Email != "") {
            ?>
            <input type="hidden" name="loginRadiusReferralEmails[]" value="<?php echo $contacts[$i]->Email; ?>" />
            <?php
        }
        // yahoo, msn
        if (($provider == "yahoo" || $provider == "live" || $provider == "google") && isset($contacts[$i]->EmailID)) {
            ?>
            <input type="hidden" name="loginRadiusReferralEmails[]" value="<?php echo $contacts[$i]->EmailID; ?>" />
            <?php
        }
    }
    ?>
    <input type="hidden" name="loginRadiusIdentifier" value="<?php echo $identifier; ?>" />
    </div>
    <input type="hidden" name="loginRadiusRedirect" value="<?php echo isset($_GET['redirect_to']) ? htmlspecialchars($_GET['redirect_to']) : '' ?>" />
    <div class="heading" style="border-bottom:none; border-top:1px solid #888888; border-top-left-radius: 0px !important; border-top-right-radius: 0px !important; border-bottom-left-radius: 8px !important; border-bottom-right-radius: 8px !important;">
        <span class="spantext" style="width:auto !important">
          <input type="button" onClick="loginRadiusCheckAll(document.loginRadiusReferralForm, true)" id="" name="" value="Select All" class="button blue">
          <input type="button" onClick="loginRadiusCheckAll(document.loginRadiusReferralForm, false)" name="" value="Deselect All" class="button buttonDeselect blue" />
          <input type="submit" name="loginRadiusReferralSubmit" value="Send Message" onClick="loginRadiusReferralSubmit = 'Submit'" class="button green">
          <input type="submit" name="loginRadiusReferralSubmit" value="Skip" onClick="loginRadiusReferralSubmit = 'Cancel'" class="button" />
        </span>
    </div>
    </form>
    </div>
    <?php
}