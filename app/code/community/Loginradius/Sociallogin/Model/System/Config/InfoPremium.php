<?php
class Loginradius_Sociallogin_Model_System_Config_InfoPremium extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $element->getComment();
        if (!$html) {
            $html = $element->getText();
        }
        ?>
        <fieldset class="loginRadiusFieldset" style="margin-right:13px; background-color:#EAF7FF; border-color:rgb(195, 239, 250); padding-bottom:25px; width:65%">
        <h4 style="color:#000"><strong>Thank you for installing the LoginRadius Social Plugin!</strong></h4>
        <p>To activate the plugin, you will need to first configure it (manage your desired social networks, etc.) from your LoginRadius account. If you do not have an account, click <a target="_blank" href="http://www.loginradius.com/">here</a> and create one for FREE!</p>
        <p>
        We also offer Social Plugins for  <a href="http://ish.re/8PE6" target="_blank">Joomla</a>, <a href="http://ish.re/8PE9" target="_blank">Drupal</a>, <a href="http://ish.re/ADDT" target="_blank">WordPress</a>, <a href="http://ish.re/8PED" target="_blank">vBulletin</a>, <a href="http://ish.re/8PEE" target="_blank">VanillaForum</a>, <a href="http://ish.re/8PEG" target="_blank">osCommerce</a>, <a href="http://ish.re/8PEH" target="_blank">PrestaShop</a>, <a href="http://ish.re/8PFQ" target="_blank">X-Cart</a>, <a href="http://ish.re/8PFR" target="_blank">Zen-Cart</a>, <a href="http://ish.re/8PFS" target="_blank">DotNetNuke</a>, <a href="http://ish.re/8PFT" target="_blank">SMF</a> <?php echo __('and') ?> <a href="http://ish.re/8PFV" target="_blank">phpBB</a> !
        </p>
        <div style="margin-top:10px">
        <a style="text-decoration:none;" href="https://www.loginradius.com/" target="_blank">
            <input class="form-button" type="button" value="Set up my FREE account!">
        </a>
        <a class="loginRadiusHow" target="_blank" href="http://ish.re/ATM4">(How to set up an account?)</a>
        </div>
        </fieldset>
        <!-- Get Updates -->
        <fieldset class="loginRadiusFieldset" style="width:26%; background-color: rgb(231, 255, 224); border: 1px solid rgb(191, 231, 176); padding-bottom:6px;">
        <h4 style="border-bottom:#d7d7d7 1px solid;"><strong>Get Updates</strong></h4>
        <p>To receive updates on new features, future releases, etc, please connect with us via Facebook</p>
        <div>
            <div style="float:left">
                <iframe rel="tooltip" scrolling="no" frameborder="0" allowtransparency="true" style="border: none; overflow: hidden; width: 50px;
                            height: 61px; margin-right:10px" src="//www.facebook.com/plugins/like.php?app_id=194112853990900&amp;href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FLoginRadius%2F119745918110130&amp;send=false&amp;layout=box_count&amp;width=90&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font=arial&amp;height=90" data-original-title="Like us on Facebook"></iframe>
                </div>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        </div>
        </fieldset>
        <!-- Help & Documentation -->
        <fieldset class="loginRadiusHelpDiv" style="margin-right:13px; width:65%">
        <h4 style="border-bottom:#d7d7d7 1px solid;"><strong>Help &amp; Documentations</strong></h4>
        <ul style="float:left; margin-right:43px">
            <li><a target="_blank" href="http://ish.re/9WC9">Extension Installation, Configuration and Troubleshooting</a></li>
            <li><a target="_blank" href="http://ish.re/9VBI">How to get LoginRadius API Key &amp; Secret</a></li>
            <li><a target="_blank" href="http://ish.re/9Z34">Magento Multisite Feature</a></li>
            <li><a target="_blank" href="http://ish.re/96M9">LoginRadius Products</a></li>
        </ul>
        <ul style="float:left; margin-right:43px">
            <li><a target="_blank" href="http://ish.re/8PG2">Discussion Forum</a></li>
            <li><a target="_blank" href="http://ish.re/96M7">About LoginRadius</a></li>
            <li><a target="_blank" href="http://ish.re/8PG8">Social Plugins</a></li>
            <li><a target="_blank" href="http://ish.re/6JMW">Social SDKs</a></li>
        </ul>
        </fieldset>
        <!-- Support Us -->
        <fieldset class="loginRadiusFieldset" style="margin-right:5px; background-color: rgb(231, 255, 224); border: 1px solid rgb(191, 231, 176); width:26%; height:112px">
        <h4 style="border-bottom:#d7d7d7 1px solid;"><strong>Support Us</strong></h4>
        <p>
        If you liked our FREE open-source plugin, please send your feedback/testimonial to <a href="mailto:feedback@loginradius.com">feedback@loginradius.com</a> !</p>
        </fieldset>
        <div style='clear:both'></div>
        <div id="loginRadiusKeySecretNotification" style="background-color: rgb(255, 255, 224); border: 1px solid rgb(230, 219, 85); padding: 5px; margin-bottom: 11px; display:none">To activate the <strong>Social Login</strong>, insert LoginRadius API Key and Secret in the <strong>Social Login Basic Settings</strong> section below. <strong>Social Sharing does not require API Key and Secret</strong>.</div>
        <div style='clear:both'></div>
        <script type="text/javascript">
            window.onload = function(){
                // set 'maxlength' attribute of the tweet textarea
                document.getElementById('sociallogin_advanced_postMessage_tweet').setAttribute('maxlength', '140');
                // set 'maxlength' attribute of the LinkedIn post message textarea
                document.getElementById('sociallogin_advanced_postMessage_linkedinParamsMessage').setAttribute('maxlength', '500');
                // object having social profile data fields to show in the premium "Social Profile Data" option
                var loginRadiusProfileFields = [{value: 'basic',
                                         text: 'Basic Profile Data (All Plans) <a href="javascript:void(0)" title="Data fields include: Social ID, Social ID Provider, First Name, Middle Name, Last Name, Full Name, Nick Name, Profile Name, Birthdate, Gender, Country Code, Country Name, Thumbnail Image Url, Image Url, Local Country, Profile Country" style="text-decoration:none">(?)</a>'},
                                         {value: 'ex_location',
                                         text: 'Extended Location Data (Pro and Premium Plans) <a href="javascript:void(0)" title="Data fields include: Main Address, Hometown, State, City, Local City, Profile City, Profile Url, Local Language, Language" style="text-decoration:none">(?)</a>'},
                                         {value: 'ex_profile',
                                         text: 'Extended Profile Data (Pro and Premium Plans) <a href="javascript:void(0)" title="Data fields include: Website, Favicon, Industry, About, Timezone, Verified, Last Profile Update, Created, Relationship Status, Favorite Quote, Interested In, Interests, Religion, Political View, HTTPS Image Url, Followers Count, Friends Count, Is Geo Enabled, Total Status Count, Number of Recommenders, Honors, Associations, Hirable, Repository Url, Age, Professional Headline, Provider Access Token, Provider Token Secret, Positions, Companies, Education, Phone Numbers, IM Accounts, Addresses, Sports, Inspirational People, Skills, Current Status, Certifications, Courses, Volunteer, Recommendations Received, Languages, Patents, Favorites" style="text-decoration:none">(?)</a>'},
                                         {value: 'linkedin_companies',
                                         text: 'Followed Companies on LinkedIn (Pro and Premium Plans) <a href="javascript:void(0)" title="A list of all the companies this user follows on LinkedIn." style="text-decoration:none">(?)</a>'},
                                         {value: 'events',
                                         text: 'Facebook Profile Events (Pro and Premium Plans) <a href="javascript:void(0)" title="A list of events (birthdays, invitation, etc.) on the Facebook profile of user" style="text-decoration:none">(?)</a>'},
                                         {value: 'status',
                                         text: 'Status Messages (Pro and Premium Plans) <a href="javascript:void(0)" title="Facebook wall activity, Twitter tweets and LinkedIn status of the user, including links" style="text-decoration:none">(?)</a>'},
                                         {value: 'posts',
                                         text: 'Facebook Posts (Pro and Premium Plans) <a href="javascript:void(0)" title="Facebook posts of the user, including links" style="text-decoration:none">(?)</a>'},
                                         {value: 'mentions',
                                         text: 'Twitter Mentions (Pro and Premium Plans) <a href="javascript:void(0)" title="A list of tweets that the user is mentioned in." style="text-decoration:none">(?)</a>'},
                                         {value: 'groups',
                                         text: 'Groups (Pro and Premium Plans) <a href="javascript:void(0)" title="A list of the Facebook groups of user." style="text-decoration:none">(?)</a>'},
                                         {value: 'contacts',
                                         text: 'Contacts/Friend Data (Premium Plans) <a href="javascript:void(0)" title="For email providers (Google and Yahoo), a list of the contacts of user in his/her address book. For social networks (Facebook, Twitter, and LinkedIn), a list of the people in the network of user." style="text-decoration:none">(?)</a>'},
                                        ];
                // get the reference to the <td> corressponding to the Social Profile Data option
                var loginRadiusSocialProfileTds = document.getElementById('row_sociallogin_advanced_socialProfileData_profileDataCheckboxes').getElementsByTagName('td');
                // list these profile fields in the Social Profile Data option
                for(var ps = 0; ps < loginRadiusProfileFields.length; ps++){
                    var checkbox = document.createElement('input');
                    checkbox.setAttribute('type', 'checkbox');
                    checkbox.setAttribute('value', loginRadiusProfileFields[ps].value);
                    checkbox.setAttribute('id', 'login_radius_social_profile_'+loginRadiusProfileFields[ps].value);
                    checkbox.onclick = function(){
                                            loginRadiusPopulateProfileFields(this);
                                        }
                    var label = document.createElement('label');
                    label.setAttribute('for', 'login_radius_social_profile_'+loginRadiusProfileFields[ps].value);
                    label.innerHTML = loginRadiusProfileFields[ps].text;
                    loginRadiusSocialProfileTds[1].appendChild(checkbox);
                    loginRadiusSocialProfileTds[1].appendChild(label);
                    loginRadiusSocialProfileTds[1].appendChild(document.createElement('br'));
                }
                // append help text
                var helpText = document.createElement('div');
                helpText.setAttribute('style', 'clear: both !important;');
                helpText.innerHTML = '<p class="note"><span>Please select the user profile data fields you would like to save in your database. For a list of all fields: <a target="_blank" href="http://www.loginradius.com/user-profile-data">http://www.loginradius.com/user-profile-data</a></span></p>';
                loginRadiusSocialProfileTds[1].appendChild(helpText);
                // increase the width of the container <td>
                loginRadiusSocialProfileTds[1].style.width = '400px';

                // show profile fields checkbox checked according to the options saved.
                var loginRadiusProfileDataHidden = document.getElementById('sociallogin_advanced_socialProfileData_profileData').value.trim();
                if(loginRadiusProfileDataHidden != ""){
                    var loginRadiusProfileOptionsArray = loginRadiusProfileDataHidden.split(',');
                    for(var i = 0; i < loginRadiusProfileOptionsArray.length; i++){
                        document.getElementById('login_radius_social_profile_'+loginRadiusProfileOptionsArray[i]).checked = true;
                    }
                }
            }
            // add/remove icons from counter hidden field
            function loginRadiusPopulateProfileFields(elem){
                // get providers hidden field value
                var providers = document.getElementById('sociallogin_advanced_socialProfileData_profileData');
                if(elem.checked){
                    // add selected providers in the hiddem field value
                    //if(typeof elem.checked != "undefined" || lrDefault == true){
                        if(providers.value == ""){
                            providers.value = elem.value;
                        }else{
                            providers.value += ","+elem.value;
                        }
                    //}
                }else{
                    if(providers.value.indexOf(',') == -1){
                        providers.value = providers.value.replace(elem.value, "");
                    }else{
                        if(providers.value.indexOf(","+elem.value) == -1){
                            providers.value = providers.value.replace(elem.value+",", "");
                        }else{
                            providers.value = providers.value.replace(","+elem.value, "");
                        }
                    }
                }

            }
        </script>
        <?php
    }
}