<div class="wppb-ul-theme" id="wppb-ul-theme-glimplist">
    <h3 class="wppb-ul-title">{{meta_first_name}} {{meta_last_name}}</h3>
    <p class="wppb-ul-subtitle">{{meta_role}}</p>
    <div class="wppb-ul-user">
        <div class="wppb-ul-image">
            {{{avatar_or_gravatar}}}
        </div>
        <div class="wppb-ul-user-details">
            <div class="wppb-ul-description">
                {{{meta_biographical_info}}}
            </div>

            <div class="wppb-ul-tabs">
                <div class="wppb-ul-headers">
                    <button class="wppb-ul-tab-title active" id="basic-info-title" type="button" onclick="activateTab( 'basic-info-title', 'basic-info-content')">Basic info</button>
                    <button class="wppb-ul-tab-title" id="contact-details-title" type="button" onclick="activateTab( 'contact-details-title', 'contact-details-content')">Contact details</button>
                    <button class="wppb-ul-tab-title" id="employment-title" type="button" onclick="activateTab( 'employment-title', 'employment-content')">User Info</button>
                </div>
                <div class="wppb-ul-content">
                    <div class="wppb-ul-tab-content" id="basic-info-content" style="display: block;">
                        <p><span>First Name:</span>{{meta_first_name}}</p>
                        <p><span>Last Name:</span>{{meta_last_name}}</p>
                        <p><span>Username:</span>{{meta_user_name}}</p>
                        <p><span>NickName:</span>{{meta_nickname}}</p>
                    </div>
                    <div class="wppb-ul-tab-content" id="contact-details-content">
                        <p><span>Email:</span><a href="mailto:{{meta_email}}" class="wppb-ul-email">{{meta_email}}</a></p>
                        <p><span>Website:</span>{{meta_website}}</p>
                    </div>
                    <div class="wppb-ul-tab-content" id="employment-content">
                        <p><span>Role:</span>{{meta_role}}</p>
                        <p><span>Posts:</span>{{{meta_number_of_posts}}}</p>
                        <p><span>Sign-up date:</span>{{meta_registration_date}}</p>
                    </div>
                </div>

            </div>

        </div>
    </div>
    {{{extra_go_back_link}}}
</div>