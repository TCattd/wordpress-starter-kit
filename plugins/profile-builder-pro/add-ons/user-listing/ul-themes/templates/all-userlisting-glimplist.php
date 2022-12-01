<div class="wppb-ul-theme" id="wppb-ul-theme-glimplist">
    <h3 class="wppb-ul-title">Users <span class="wppb-ul-count">{{{user_count}}}</span></h3>
    <p class="wppb-ul-description">Lorem ipsum dolor sit amet, consectetur adipiscing.</p>
    <div class="wppb-ul-search">
        {{{extra_search_all_fields}}}
    </div>
    <div class="wppb-ul-filters">
        {{{faceted_menus}}}
    </div>
    <div class="wppb-ul-container">
        {{#users}}
            <div class="wppb-ul-user">
                <a href="{{{more_info_url}}}" class="wppb-ul-name">{{meta_first_name}} {{meta_last_name}}</a>
                <div class="wppb-ul-details">
                    <div class="wppb-ul-image">
                        {{{avatar_or_gravatar}}}
                    </div>
                    <div class="wppb-ul-user-description-container">
                        <div class="wppb-ul-user-description">
                            {{{meta_biographical_info}}}
                        </div>
                    </div>
                </div>
            </div>
        {{/users}}
    </div>
    {{{pagination}}}
</div>