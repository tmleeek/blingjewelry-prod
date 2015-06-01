<?php
class Loginradius_Sociallogin_Model_System_Config_Info extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
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
                <iframe rel="tooltip" scrolling="no" frameborder="0" allowtransparency="true" style="border: none; overflow: hidden; width: 50px; height: 61px; margin-right:10px" src="//www.facebook.com/plugins/like.php?app_id=194112853990900&amp;href=http%3A%2F%2Fwww.facebook.com%2Fpages%2FLoginRadius%2F119745918110130&amp;send=false&amp;layout=box_count&amp;width=90&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;font=arial&amp;height=90" data-original-title="Like us on Facebook"></iframe>
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
        <script type="text/javascript">var islrsharing = true; var islrsocialcounter = true;</script>
        <script type="text/javascript" src="//share.loginradius.com/Content/js/LoginRadius.js" id="lrsharescript"></script>
        <script type="text/javascript">
            window.onload = function(){
                if(document.getElementById('sociallogin_options_messages_appid') != null && (document.getElementById('sociallogin_options_messages_appid').value.trim() == "" || document.getElementById('sociallogin_options_messages_appkey').value.trim() == "")){
                    document.getElementById('loginRadiusKeySecretNotification').style.display = 'block';
                }
                var sharingType = ['horizontal', 'vertical'];
                var sharingModes = ['Sharing', 'Counter'];
                for(var i = 0; i < sharingType.length; i++){
                    for(var j = 0; j < sharingModes.length; j++){
                        if(sharingModes[j] == 'Counter'){
                            var providers = $SC.Providers.All;
                        }else{
                            var providers = $SS.Providers.More;
                        }
                        // populate sharing providers checkbox
                        loginRadiusCounterHtml = "<ul class='checkboxes'>";
                        // prepare HTML to be shown as Vertical Counter Providers
                        for(var ii = 0; ii < providers.length; ii++){
                            loginRadiusCounterHtml += '<li><input type="checkbox" id="'+sharingType[i]+'_'+sharingModes[j]+'_'+providers[ii]+'" ';
                            loginRadiusCounterHtml += 'value="'+providers[ii]+'"> <label for="'+sharingType[i]+'_'+sharingModes[j]+'_'+providers[ii]+'">'+providers[ii]+'</label></li>';
                        }
                        loginRadiusCounterHtml += "</ul>";
                        var tds = document.getElementById('row_sociallogin_options_'+sharingType[i]+'Sharing_'+sharingType[i]+sharingModes[j]+'Providers').getElementsByTagName('td');
                        tds[1].innerHTML = loginRadiusCounterHtml;
                    }
                    document.getElementById('row_sociallogin_options_'+sharingType[i]+'Sharing_'+sharingType[i]+'CounterProvidersHidden').style.display = 'none';
                }
                loginRadiusPrepareAdminUI();
            }
            // toggle sharing/counter providers according to the theme and sharing type
            function loginRadiusToggleSharingProviders(element, sharingType){
                if(element.value == '32' || element.value == '16'){
                    document.getElementById('row_sociallogin_options_'+sharingType+'Sharing_'+sharingType+'SharingProviders').style.display = 'table-row';
                    document.getElementById('row_sociallogin_options_'+sharingType+'Sharing_'+sharingType+'CounterProviders').style.display = 'none';
                    document.getElementById('row_sociallogin_options_'+sharingType+'Sharing_'+sharingType+'SharingProvidersHidden').style.display = 'table-row';
                }else if(element.value == 'single_large' || element.value == 'single_small'){
                    document.getElementById('row_sociallogin_options_'+sharingType+'Sharing_'+sharingType+'SharingProviders').style.display = 'none';
                    document.getElementById('row_sociallogin_options_'+sharingType+'Sharing_'+sharingType+'CounterProviders').style.display = 'none';
                    document.getElementById('row_sociallogin_options_'+sharingType+'Sharing_'+sharingType+'SharingProvidersHidden').style.display = 'none';
                }else{
                    document.getElementById('row_sociallogin_options_'+sharingType+'Sharing_'+sharingType+'SharingProviders').style.display = 'none';
                    document.getElementById('row_sociallogin_options_'+sharingType+'Sharing_'+sharingType+'CounterProviders').style.display = 'table-row';
                    document.getElementById('row_sociallogin_options_'+sharingType+'Sharing_'+sharingType+'SharingProvidersHidden').style.display = 'none';
                }
            }
        </script>                
        <?php
    }
}