<?php
class Loginradius_Sociallogin_Model_System_Config_ProfileData extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
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
        $lruserdataempty = true;
        // get id here
        $customerId = $this->getRequest()->getParam('id');
        if (!empty($customerId)) {
            ?>
            <style type="text/css">
            .sociallogin_table {
                background-color: #efefef;
                border: 1px solid #ccc;
                margin-bottom: 10px;
                border-collapse: collapse;
                font-family: sans-serif;
                font-size: 12px;
                line-height: 1.4em;
                margin-left: 2px;
                width: 100%;
                clear: both
            }
            .sociallogin_table th {
                padding: 10px;
                text-align: left;
                vertical-align: top;
                width: 200px;
                word-break: break-all;
            }
            .ui-tabs {
                position: relative;/* position: relative prevents IE scroll bug (element with position: relative inside container with overflow: auto appear as "fixed") */
                padding: .2em;
            }
            .ui-tabs .ui-tabs-nav {
                margin: 0;
                padding: .2em .2em 0;
            }
            .ui-tabs .ui-tabs-nav li {
                list-style: none;
                float: left;
                position: relative;
                top: 0;
            margin-right: 6px;
                border-bottom-width: 0;
                padding: 0;
                white-space: nowrap;
            }
            .ui-tabs .ui-tabs-nav li a {
                float: left;
                padding: .5em 1em;
                text-decoration: none;

            }
            .ui-tabs .ui-tabs-nav li.ui-tabs-active {
                margin-bottom: -1px;
                padding-bottom: 1px;
            }
            .ui-tabs .ui-tabs-nav li.ui-tabs-active a,
            .ui-tabs .ui-tabs-nav li.ui-state-disabled a,
            .ui-tabs .ui-tabs-nav li.ui-tabs-loading a {
                cursor: text;
            }
            .ui-tabs .ui-tabs-nav li a, /* first selector in group seems obsolete, but required to overcome bug in Opera applying cursor: text overall if defined elsewhere... */
            .ui-tabs-collapsible .ui-tabs-nav li.ui-tabs-active a {
                cursor: pointer;
            }
            .ui-tabs .ui-tabs-panel {
                display: block;
                border-width: 0;
                dding-left: 3px;
                background: none;
            }
            .ui-tooltip {
                padding: 8px;
                position: absolute;
                z-index: 9999;
                max-width: 300px;
                -webkit-box-shadow: 0 0 5px #aaa;
                box-shadow: 0 0 5px #aaa;
            }
            body .ui-tooltip {
                border-width: 2px;
            }

            ui-state-default,
            .ui-widget-content .ui-state-default,
            .ui-widget-header .ui-state-default {
                border: 1px solid #d3d3d3;
                background: #e6e6e6 url(images/ui-bg_glass_75_e6e6e6_1x400.png) 50% 50% repeat-x;
                font-weight: normal;
                color: #555555;
            }
            .ui-state-default a,
            .ui-state-default a:link,
            .ui-state-default a:visited {
                color: #555555;
                text-decoration: none;
            }
            .ui-state-hover,
            .ui-widget-content .ui-state-hover,
            .ui-widget-header .ui-state-hover,
            .ui-state-focus,
            .ui-widget-content .ui-state-focus,
            .ui-widget-header .ui-state-focus {
                border: 1px solid #999999;
                background: #dadada url(images/ui-bg_glass_75_dadada_1x400.png) 50% 50% repeat-x;
                font-weight: normal;
                color: #212121;
            }
            .ui-state-hover a,
            .ui-state-hover a:hover,
            .ui-state-hover a:link,
            .ui-state-hover a:visited {
                color: #212121;
                text-decoration: none;
            }
            .ui-state-active,
            .ui-widget-content .ui-state-active,
            .ui-widget-header .ui-state-active {
                border: 1px solid #aaaaaa;
                background: #ffffff url(images/ui-bg_glass_65_ffffff_1x400.png) 50% 50% repeat-x;
                font-weight: normal;
                color: #212121;
            }
            .ui-state-active a,
            .ui-state-active a:link,
            .ui-state-active a:visited {
                color: #212121;
                text-decoration: none;
            }
            </style>
            <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
            <script type="text/javascript">
            jQuery(function() {
                jQuery( "#tabs" ).tabs();
                jQuery( ".content-header").hide();

            });
            </script>
            <?php
            // basic user profile
            $basicProfileResult = $this->getprofiledata('loginradius_basic_profile_data', $customerId);
            $lrEmails = $this->getprofiledata('loginradius_emails', $customerId);
            //contect
            $lrContacts = $this->getprofiledata('loginradius_contacts', $customerId);
            //extended location profile
            $lrExtendedLocation = $this->getprofiledata('loginradius_extended_location_data', $customerId);
            //extended user profile
            $lrExtendedProfile = $this->getprofiledata('loginradius_extended_profile_data', $customerId);
            $lrPositions = $this->getprofiledata('loginradius_positions', $customerId);
            //$basicProfileResult = $this->getprofiledata('loginradius_companies', $customerId);
            $lrEducation = $this->getprofiledata('loginradius_education', $customerId);
            $lrPhoneNumbers = $this->getprofiledata('loginradius_phone_numbers', $customerId);
            $lrIMaccounts = $this->getprofiledata('loginradius_IMaccounts', $customerId);
            $lrAddress = $this->getprofiledata('loginradius_addresses', $customerId);
            $lrSports = $this->getprofiledata('loginradius_sports', $customerId);
            $lrInspirationalPeople = $this->getprofiledata('loginradius_inspirational_people', $customerId);
            $lrSkills = $this->getprofiledata('loginradius_skills', $customerId);
            $lrCurrentStatus = $this->getprofiledata('loginradius_current_status', $customerId);
            $lrCertifications = $this->getprofiledata('loginradius_certifications', $customerId);
            $lrCourses = $this->getprofiledata('loginradius_courses', $customerId);
            $lrVolunteer = $this->getprofiledata('loginradius_volunteer', $customerId);
            $lrRecommendations = $this->getprofiledata('loginradius_recommendations_received', $customerId);
            $lrLanguages = $this->getprofiledata('loginradius_languages', $customerId);
            $lrPatents = $this->getprofiledata('loginradius_patents', $customerId);
            $lrFavorites = $this->getprofiledata('loginradius_favorites', $customerId);
            //facebook events
            $lrFbEvents = $this->getprofiledata('loginradius_facebook_events', $customerId);
            //facebook post
            $lrFbPost = $this->getprofiledata('loginradius_facebook_posts', $customerId);
            //loginradius groups
            $lrGroups = $this->getprofiledata('loginradius_groups', $customerId);
            //linkedin companies
            $lrLinkedinCompanies = $this->getprofiledata('loginradius_linkedin_companies', $customerId);
            //status
            $lrStatus = $this->getprofiledata('loginradius_status', $customerId);
            //twitter mentions
            $lrTwitterMentions = $this->getprofiledata('loginradius_twitter_mentions', $customerId);
            ?>
            <h2>User Profile</h2>
            <div id="tabs">
            <ul>
            <?php
            if (count($basicProfileResult) > 0) {
                $lruserdataempty = false; ?>
                <li style="cursor:pointer;"><a href="#tabs-1"><?php echo 'Basic Profile Data'; ?></a></li>
                <?php
            }
            if (count($lrExtendedLocation) > 0) {
                $lruserdataempty = false; ?>
                <li style="cursor:pointer;"><a href="#tabs-2"><?php echo 'Extended Location Data'; ?></a></li>
                <?php
            }
            if (count($lrExtendedProfile) > 0) {
                $lruserdataempty = false; ?>
                <li style="cursor:pointer;"><a href="#tabs-3"><?php echo 'Extended Profile Data'; ?></a></li>
                <?php
            }
            if (count($lrFbEvents) > 0) {
                $lruserdataempty = false; ?>
                <li style="cursor:pointer;"><a href="#tabs-4"><?php echo 'Facebook events'; ?></a></li>
                <?php
            }
            if (count($lrContacts) > 0) {
                $lruserdataempty = false; ?>
                <li style="cursor:pointer;"><a href="#tabs-5"><?php echo 'Contacts'; ?></a></li>
                <?php
            }
            if (count($lrFbPost) > 0) {
                $lruserdataempty = false; ?>
                <li style="cursor:pointer;"><a href="#tabs-6"><?php echo 'Facebook Post'; ?></a></li>
                <?php
            }
            if (count($lrGroups) > 0) {
                $lruserdataempty = false; ?>
                <li style="cursor:pointer;"><a href="#tabs-7"><?php echo 'Groups'; ?></a></li>
                <?php
            }
            if (count($lrLinkedinCompanies) > 0) {
                $lruserdataempty = false; ?>
                <li style="cursor:pointer;"><a href="#tabs-8"><?php echo 'Companies followed'; ?></a></li>
                <?php
            }
            if (count($lrStatus) > 0) {
                $lruserdataempty = false; ?>
                <li style="cursor:pointer;"><a href="#tabs-9"><?php echo 'Status'; ?></a></li>
                <?php
            }
            if (count($lrTwitterMentions) > 0) {
                $lruserdataempty = false; ?>
                <li style="cursor:pointer;"><a href="#tabs-10"><?php echo 'Twitter Mentions'; ?></a></li>
                <?php
            }
            ?>
            </ul>
            <?php
            if (count($basicProfileResult) > 0) {
                $lruserdataempty = false; ?>
                <div class="menu_containt_div" id="tabs-1">
                <?php $this->login_radius_show_data($basicProfileResult); ?>
                    <?php
                if (count($lrEmails) > 0) {
                    echo '<h3>Emails</h3>';
                    ?>
                    <table class="sociallogin_table" cellspacing="0">
                    <thead>
                    <tr>
                        <th class="head"><?php echo 'Type'; ?></th>
                        <th class="head"><?php echo 'Email Address'; ?></th>
                    </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrEmails, true);
                    ?>
                    </table>
                    <?php
                }?>
                </div>
                <?php
            }
            if (count($lrExtendedLocation) > 0) {
                ?>
                <div id="tabs-2">
                        <?php $this->login_radius_show_data($lrExtendedLocation); ?>
                </div>
                <?php
            }
            if (count($lrExtendedProfile) > 0) {
                ?>
                <div id="tabs-3">
                <?php $this->login_radius_show_data($lrExtendedProfile); ?>
                <?php
                if (count($lrPositions) > 0) {
                    echo '<h3>Positions</h3>';
                    ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Positions'; ?></th>
                            <th class="head"><?php echo 'Summary'; ?></th>
                            <th class="head"><?php echo 'Start Date'; ?></th>
                            <th class="head"><?php echo 'End Date'; ?></th>
                            <th class="head"><?php echo 'Current'; ?></th>
                            <th class="head"><?php echo 'Company'; ?></th>
                            <th class="head"><?php echo 'Type'; ?></th>
                            <th class="head"><?php echo 'Industry'; ?></th>
                            <th class="head"><?php echo 'Location'; ?></th>
                        </tr>
                    </thead>
                    <tfoot>
                    <?php
                    $count = 0;
                    foreach ($lrPositions as $position) {
                        ?>
                        <tr <?php if (($count % 2) == 0) { echo 'style="background-color:#fcfcfc"'; } ?>>
                        <?php
                        foreach ($position as $key => $val) {
                            if ($key == 'user_id') {
                                continue;
                            } elseif ($key == 'company') {
                                if ($val == "NULL" || $val == "") {
                                    ?>
                                    <th scope="col" class="manage-colum"></th>
                                    <th scope="col" class="manage-colum"></th>
                                    <th scope="col" class="manage-colum"></th>
                                    <?php
                                } else {
                                    // companies
                                    $companies = $this->getprofiledata('loginradius_companies', $val);
                                    if (count($companies) > 0) {
                                        foreach ($companies[0] as $k => $v) {
                                            if ($k == 'id') {
                                                continue;
                                            }
                                            ?>
                                            <th scope="col" class="manage-colum"><?php echo ucfirst($v) ?></th>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <th scope="col" class="manage-colum"></th>
                                        <th scope="col" class="manage-colum"></th>
                                        <th scope="col" class="manage-colum"></th>
                                        <?php
                                    }
                                }
                                continue;
                            } else {
                                ?>
                                <th scope="col" class="manage-colum"><?php echo ucfirst($val) ?></th>
                                <?php
                            }
                        }
                        ?>
                        </tr>
                        <?php
                        $count++;
                    }
                }
                ?>
                </tfoot>
                </table>
                <?php
                if (count($lrEducation) > 0) {
                    echo '<h3>Education</h3>';
                    ?>
                    <table class="sociallogin_table" cellspacing="0">
                    <thead>
                    <tr>
                        <th class="head"><?php echo 'School'; ?></th>
                        <th class="head"><?php echo 'Year'; ?></th>
                        <th class="head"><?php echo 'Type'; ?></th>
                        <th class="head"><?php echo 'Notes'; ?></th>
                        <th class="head"><?php echo 'Activites'; ?></th>
                        <th class="head"><?php echo 'Degree'; ?></th>
                        <th class="head"><?php echo 'FOS'; ?></th>
                        <th class="head"><?php echo 'Start Date'; ?></th>
                        <th class="head"><?php echo 'End Date'; ?></th>
                    </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrEducation, true);
                    ?>
                    </table>
                    <?php
                }
                if (count($lrPhoneNumbers) > 0) {
                    echo '<h3>PhoneNumbers</h3>';
                    ?><table class="sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Type'; ?></th>
                            <th class="head"><?php echo 'Value'; ?></th>
                         </tr>
                    </thead>
                    <?php $this->login_radius_show_data($lrPhoneNumbers, true); ?>
                    </table>
                    <?php
                }
                if (count($lrIMaccounts) > 0) {
                    echo '<h3>IMaccounts</h3>';
                    ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Type'; ?></th>
                            <th class="head"><?php echo 'UserName'; ?></th>
                         </tr>
                    </thead>
                    <?php $this->login_radius_show_data($lrIMaccounts, true); ?>
                    </table>
                    <?php
                }
                if (count($lrAddress) > 0) {
                    echo '<h3>Address</h3>';
                    ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                         <th class="head"><?php echo 'Type'; ?></th>
                         <th class="head"><?php echo 'Address Line 1'; ?></th>
                         <th class="head"><?php echo 'Address Line 2';; ?></th>
                         <th class="head"><?php echo 'City'; ?></th>
                         <th class="head"><?php echo 'State'; ?></th>
                         <th class="head"><?php echo 'Postal Code'; ?></th>
                         <th class="head"><?php echo 'Region'; ?></th>
                        </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrAddress, true); ?>
                    </table>
                    <?php
                }
                if (count($lrSports) > 0) {
                    echo '<h3>Sports</h3>'; ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Sport'; ?></th>
                        </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrSports, true);
                    ?>
                    </table>
                    <?php
                }
                if (count($lrInspirationalPeople) > 0) {
                    echo '<h3>InspirationalPeople</h3>'; ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Name'; ?></th>
                        </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrInspirationalPeople, true);
                    ?>
                    </table>
                    <?php
                }
                if (count($lrSkills) > 0) {
                    echo '<h3>Skills</h3>';
                    ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Skills'; ?></th>
                        </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrSkills, true);
                    ?>
                    </table>
                    <?php
                }
                if (count($lrCurrentStatus) > 0) {
                    echo '<h3>CurrentStatus</h3>';
                    ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Status'; ?></th>
                            <th class="head"><?php echo 'Source'; ?></th>
                            <th class="head"><?php echo 'Create Date'; ?></th>
                        </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrCurrentStatus, true);
                    ?>
                    </table>
                    <?php
                }
                if (count($lrCertifications) > 0) {
                    echo '<h3>Certifications</h3>'; ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Name'; ?></th>
                            <th class="head"><?php echo 'Authority'; ?></th>
                            <th class="head"><?php echo 'License Number'; ?></th>
                            <th class="head"><?php echo 'Start Date'; ?></th>
                            <th class="head"><?php echo 'End Date'; ?></th>
                        </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrCertifications, true);
                    ?>
                    </table>
                    <?php
                }
                if (count($lrCourses) > 0) {
                    echo '<h3>Courses</h3>'; ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Cources'; ?></th>
                            <th class="head"><?php echo 'Cources Numbers'; ?></th>
                        </tr>
                    </thead>
                    <?php $this->login_radius_show_data($lrCourses, true); ?></table><?php
                }
                if (count($lrVolunteer) > 0) {
                    echo '<h3>Volunteer</h3>'; ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Role'; ?></th>
                            <th class="head"><?php echo 'Orgenization'; ?></th>
                            <th class="head"><?php echo 'Cause'; ?></th>
                        </tr>
                    </thead>
                    <?php $this->login_radius_show_data($lrVolunteer, true); ?></table>
                    <?php
                }
                if (count($lrRecommendations) > 0) {
                    echo '<h3>Recommendations</h3>'; ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Type'; ?></th>
                            <th class="head"><?php echo 'Text'; ?></th>
                            <th class="head"><?php echo 'Recommendation'; ?></th>
                        </tr>
                    </thead>
                    <?php $this->login_radius_show_data($lrRecommendations, true); ?></table>
                    <?php
                }
                if (count($lrLanguages) > 0) {
                    echo '<h3>Languages</h3>'; ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                             <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Language'; ?></th>
                        </tr>
                    </thead>
                    <?php $this->login_radius_show_data($lrLanguages, true); ?>
                    </table>
                    <?php
                }
                if (count($lrPatents) > 0) {
                    echo '<h3>Patents</h3>';
                    ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                    <tr>
                        <th class="head"><?php echo 'Id'; ?></th>
                        <th class="head"><?php echo 'Title'; ?></th>
                        <th class="head"><?php echo 'Date'; ?></th>
                    </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrPatents, true);
                    ?>
                    </table>
                    <?php
                }
                if (count($lrFavorites) > 0) {
                    echo '<h3>Favorites</h3>'; ?>
                    <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Name'; ?></th>
                            <th class="head"><?php echo 'Type'; ?></th>
                        </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrFavorites, true);
                    ?>
                    </table>
                    <?php
                }
                ?>
                </div>
                <?php
            }
            if (count($lrFbEvents) > 0) {
                ?>
                <div id="tabs-4">
                <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Event'; ?></th>
                            <th class="head"><?php echo 'Start Time'; ?></th>
                            <th class="head"><?php echo 'RSVP Status'; ?></th>
                            <th class="head"><?php echo 'Location'; ?></th>
                        </tr>
                    </thead>
                    <?php $this->login_radius_show_data($lrFbEvents, true); ?>
                </table></div>
                <?php
            }
            if (count($lrContacts)>0) {
                ?>
                <div id="tabs-5">
                <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Provider'; ?></th>
                            <th class="head"><?php echo 'Name'; ?></th>
                            <th class="head"><?php echo 'Email'; ?></th>
                            <th class="head"><?php echo 'Phone Number'; ?></th>
                            <th class="head"><?php echo 'Social Id'; ?></th>
                            <th class="head"><?php echo 'Profile URL'; ?></th>
                            <th class="head"><?php echo 'Image URL'; ?></th>
                            <th class="head"><?php echo 'Status'; ?></th>
                            <th class="head"><?php echo 'Industry'; ?></th>
                            <th class="head"><?php echo 'Country'; ?></th>
                            <th class="head"><?php echo 'Gender'; ?></th>
                        </tr>
                    </thead>
                    <?php $this->login_radius_show_data($lrContacts, true); ?>
                </table></div>
                <?php
            }
            if (count($lrFbPost) > 0) {
                ?>
                <div id="tabs-6">
                    <table class="form-table sociallogin_table" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="head"><?php echo 'Id'; ?></th>
                                <th class="head"><?php echo 'From'; ?></th>
                                <th class="head"><?php echo 'Title'; ?></th>
                                <th class="head"><?php echo 'Start Time'; ?></th>
                                <th class="head"><?php echo 'Update Tine'; ?></th>
                                <th class="head"><?php echo 'Message'; ?></th>
                                <th class="head"><?php echo 'Place'; ?></th>
                                <th class="head"><?php echo 'Picture'; ?></th>
                                <th class="head"><?php echo 'Likes'; ?></th>
                                <th class="head"><?php echo 'Shares'; ?></th>
                            </tr>
                        </thead>
                    <?php $this->login_radius_show_data($lrFbPost, true); ?>
                    </table>
                </div>
                <?php
            }
            if (count($lrGroups) > 0) {
                ?>
                <div id="tabs-7">
                     <table class="form-table sociallogin_table" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="head"><?php echo 'Provider'; ?></th>
                                <th class="head"><?php echo 'Id'; ?></th>
                                <th class="head"><?php echo 'Name'; ?></th>
                            </tr>
                        </thead>
                    <?php $this->login_radius_show_data($lrGroups, true); ?>
                    </table>
                </div>
                <?php
            }
            if (count($lrLinkedinCompanies) > 0) {
                ?>
                <div id="tabs-8">
                <table class="form-table sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Company'; ?></th>
                        </tr>
                    </thead>
                    <?php $this->login_radius_show_data($lrLinkedinCompanies, true); ?>
                </table>
                </div>
                <?php
            }
            if (count($lrStatus) > 0) {
                ?>
                <div id="tabs-9">
                <table class="form-table sociallogin_table" cellspacing="0">
                <thead>
                <tr>
                    <th class="head"><?php echo 'Provider'; ?></th>
                    <th class="head"><?php echo 'Id'; ?></th>
                    <th class="head"><?php echo 'Status'; ?></th>
                    <th class="head"><?php echo 'Time'; ?></th>
                    <th class="head"><?php echo 'Likes'; ?></th>
                    <th class="head"><?php echo 'Place'; ?></th>
                    <th class="head"><?php echo 'Source'; ?></th>
                    <th class="head"><?php echo 'Image URL'; ?></th>
                    <th class="head"><?php echo 'Link URL'; ?></th>
                </tr>
                </thead>
                <?php
                $this->login_radius_show_data($lrStatus, true);
                ?>
                </table>
                </div>
                <?php
            }
            if (count($lrTwitterMentions) > 0) {
                ?>
                <div id="tabs-10">
                <table class="sociallogin_table" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="head"><?php echo 'Id'; ?></th>
                            <th class="head"><?php echo 'Tweet'; ?></th>
                            <th class="head"><?php echo 'Date Time'; ?></th>
                            <th class="head"><?php echo 'Likes'; ?></th>
                            <th class="head"><?php echo 'Place'; ?></th>
                            <th class="head"><?php echo 'Source'; ?></th>
                            <th class="head"><?php echo 'Image URL'; ?></th>
                            <th class="head"><?php echo 'Link URL'; ?></th>
                            <th class="head"><?php echo 'Mentionedby'; ?></th>
                        </tr>
                    </thead>
                    <?php
                    $this->login_radius_show_data($lrTwitterMentions, true);
                    ?>
                </table>
                </div>
                </div>
                <?php
            }
        } elseif ($lruserdataempty == true) {
            echo 'Profile data not found';
        }
    }
    public function getprofiledata($tablename, $customerid)
    {
        $loginRadiusConn = Mage::getSingleton('core/resource')->getConnection('core_read');
        if ($tablename == 'loginradius_companies') {
            $loginRadiusQuery = "select * from ".Mage::getSingleton('core/resource')->getTableName($tablename)." where id = ".$customerid;
        } else {
            $loginRadiusQuery = "select * from ".Mage::getSingleton('core/resource')->getTableName($tablename)." where user_id = ".$customerid;
        }
        $loginRadiusQueryHandle = $loginRadiusConn->query($loginRadiusQuery);
        $loginRadiusResult = $loginRadiusQueryHandle->fetchAll();
        return $loginRadiusResult;
    }
    //show userprofile in table
    public function login_radius_show_data($array, $subTable = false)
    {
        if ($subTable) {
            ?>
            <tfoot>
            <?php
            $count = 1;
            foreach ($array as $temp) {
                ?>
                <tr <?php if (($count % 2) == 0) { echo 'style="background-color:#fcfcfc"'; } ?>>
                <?php
                foreach ($temp as $key => $val) {
                    if ($key == 'user_id') {
                        continue;
                    } else {
                        ?>
                        <th scope="col" class="manage-colum"><?php echo ucfirst($val) ?></th>
                        <?php
                    }
                }
                ?>
                </tr>
                <?php
                $count++;
            }
            ?>
            </tfoot>
            <?php
        } else {
            ?>
            <table class="sociallogin_table" cellspacing="0">
            <tfoot>
            <?php
            $count = 1;
            foreach ($array[0] as $key => $value) {
                if ($value != '') {
                    if ($key == 'user_id') {
                        continue;
                    } elseif ($key == 'loginradius_id') {
                        $key = 'LoginRadius ID';
                    } elseif ($key == 'birth_date') {
                        $key .= ' (dd-mm-yyyy)';
                        $valueParts = explode('-', $value);
                        $value = $valueParts[2].'-'.$valueParts[1].'-'.$valueParts[0];
                    } elseif ($key == 'age' && $value == 0) {
                        continue;
                    }
                    ?>
                    <tr <?php if (($count % 2) == 0) { echo 'style="background-color:#fcfcfc"'; } ?>>
                        <?php
                        $keyParts = explode('_', $key);
                        $keyParts = array_map(function ($elem) { return ucfirst($elem); }, $keyParts);
                        ?>
                        <th scope="col" class="manage-colum"><?php echo count($keyParts) > 1 ? implode(' ', $keyParts) : ucfirst($key) ?></th>
                        <th scope="col" class="manage-colum"><?php echo ucfirst($value) ?></th>
                    </tr>
                    <?php
                    $count++;
                }
            }
            ?>
            </tfoot>
            </table>
        <?php
        }
    }
}