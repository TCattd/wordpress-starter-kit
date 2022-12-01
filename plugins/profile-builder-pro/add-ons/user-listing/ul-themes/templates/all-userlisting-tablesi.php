<div class="wppb-ul-theme" id="wppb-ul-theme-tablesi">
    <h3 class="wppb-ul-title">Users <span class="wppb-ul-count">{{{user_count}}}</span></h3>
    <p class="wppb-ul-description">Lorem ipsum dolor sit amet, consectetur adipiscing.</p>
    <div class="wppb-ul-filters-container">
        <div class="wppb-ul-search">
            {{{extra_search_all_fields}}}
            <div class="wppb-ul-show-filters">
                <button class="wppb-ul-filter-button" type="button">Filters</button>
            </div>
        </div>
        <div class="wppb-ul-filters">
            {{{faceted_menus}}}
        </div>
    </div>
    <table class="wppb-table">
        <thead>
        <tr>
            <th scope="col" colspan="2" class="wppb-sorting">{{{sort_user_name}}}</th>
            <th scope="col" class="wppb-sorting">{{{sort_user_id}}}</th>
            <th scope="col" class="wppb-sorting">{{{sort_role}}}</th>
            <th scope="col" class="wppb-sorting">{{{sort_number_of_posts}}}</th>
            <th scope="col" class="wppb-sorting">{{{sort_registration_date}}}</th>
        </tr>
        </thead>
        <tbody>
        {{#users}}
        <tr>
            <td data-label="Avatar" class="wppb-avatar">{{{avatar_or_gravatar}}}</td>
            <td data-label="Firstname" class="wppb-name">{{meta_first_name}} {{meta_last_name}}</td>
            <td data-label="User-ID" class="wppb-role">{{meta_user_id}}</td>
            <td data-label="Role" class="wppb-role">{{meta_role}}</td>
            <td data-label="Posts" class="wppb-posts">{{{meta_number_of_posts}}}</td>
            <td data-label="Sign-up Date" class="wppb-signup">{{meta_registration_date}}</td>
            <td data-label="More" class="wppb-more"><a href="{{{more_info_url}}}" id="wppb-view-profile">View</a></td>
        </tr>
        {{/users}}
        </tbody>
    </table>
    {{{pagination}}}
</div>