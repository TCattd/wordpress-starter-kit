<div class="wppb-ul-theme" id="wppb-ul-theme-vergrid">
    <h3 class="wppb-ul-title">Users <span class="wppb-ul-count">{{{user_count}}}</span></h3>
    <p class="wppb-ul-description">Lorem ipsum dolor sit amet, consectetur adipiscing.</p>
    <div class="wppb-ul-search">
        {{{extra_search_all_fields}}}
        <div class="wppb-ul-show-filters">
            <button class="wppb-ul-filter-button" type="button">Filters</button>
        </div>
    </div>
    <div class="wppb-ul-filters">
        {{{faceted_menus}}}
    </div>
    <div class="wppb-ul-container">
        {{#users}}
            <div class="wppb-ul-user">
                <div class="wppb-ul-image">
                    {{{avatar_or_gravatar}}}
                </div>
                <div class="wppb-ul-details">
                    <h4>{{meta_first_name}} {{meta_last_name}}</h4>
                    <h5>{{meta_role}}</h5>
                    <div class="wppb-ul-extra-info">
                        <p><span class="wppb-ul-label">Posts:</span>{{{meta_number_of_posts}}}</p>
                        <p><span class="wppb-ul-label">Sign-up date:</span>{{meta_registration_date}}</p>
                        <a href="{{{more_info_url}}}" id="wppb-view-profile">View Profile</a>
                    </div>
                </div>
            </div>
        {{/users}}
    </div>
    {{{pagination}}}
</div>