<?php

// Register the script
wp_register_script( 'hfcm_showboxes', plugins_url( 'js/nnr-hfcm-showboxes.js', dirname( __FILE__ ) ), array( 'jquery' ) );

// prepare ID (for AJAX)
if ( !isset( $id ) ) {
    $id = -1;
}

// Localize the script with new data
$translation_array = array(
    'header'         => __( 'Header', 'header-footer-code-manager' ),
    'before_content' => __( 'Before Content', 'header-footer-code-manager' ),
    'after_content'  => __( 'After Content', 'header-footer-code-manager' ),
    'footer'         => __( 'Footer', 'header-footer-code-manager' ),
    'id'             => absint( $id ),
    'security'       => wp_create_nonce( 'hfcm-get-posts' ),
);
wp_localize_script( 'hfcm_showboxes', 'hfcm_localize', $translation_array );

// Enqueued script with localized data.
wp_enqueue_script( 'hfcm_showboxes' );
?>

    <div class="wrap">
        <h1>
            <?php echo $update ? esc_html__( 'Edit Snippet', 'header-footer-code-manager' ) : esc_html__( 'Add New Snippet', 'header-footer-code-manager' ) ?>
            <?php if ( $update ) : ?>
                <a href="<?php echo admin_url( 'admin.php?page=hfcm-create' ) ?>" class="page-title-action">
                    <?php esc_html_e( 'Add New Snippet', 'header-footer-code-manager' ) ?>
                </a>
            <?php endif; ?>
        </h1>
        <?php
        if ( !empty( $_GET['message'] ) ) :
            if ( 1 === $_GET['message'] ) :
                ?>
                <div class="updated">
                    <p><?php esc_html_e( 'Script updated', 'header-footer-code-manager' ); ?></p>
                </div>
                <a href="<?php echo admin_url( 'admin.php?page=hfcm-list' ) ?>">&laquo; <?php esc_html_e( 'Back to list', 'header-footer-code-manager' ); ?></a>
            <?php elseif ( 6 === $_GET['message'] ) : ?>
                <div class="updated">
                    <p><?php esc_html_e( 'Script Added Successfully', 'header-footer-code-manager' ); ?></p>
                </div>
                <a href="<?php echo admin_url( 'admin.php?page=hfcm-list' ) ?>">&laquo; <?php esc_html_e( 'Back to list', 'header-footer-code-manager' ); ?></a>
            <?php
            endif;
        endif;

        if ( $update ) :
            $hfcm_form_action = admin_url( 'admin.php?page=hfcm-request-handler&id=' . absint( $id ) );
        else :
            $hfcm_form_action = admin_url( 'admin.php?page=hfcm-request-handler' );
        endif;
        ?>
        <form method="post" action="<?php echo $hfcm_form_action ?>">
            <?php
            if ( $update ) :
                wp_nonce_field( 'update-snippet_' . absint( $id ) );
            else :
                wp_nonce_field( 'create-snippet' );
            endif;
            ?>
            <table class="wp-list-table widefat fixed hfcm-form-width form-table">
                <tr>
                    <th class="hfcm-th-width"><?php esc_html_e( 'Snippet Name', 'header-footer-code-manager' ); ?></th>
                    <td>
                        <input type="text" name="data[name]" value="<?php echo esc_attr( $name ); ?>"
                               class="hfcm-field-width"/>
                    </td>
                </tr>
                <?php
                $nnr_hfcm_snippet_type_array = array(
                    'html' => esc_html__( 'HTML', 'header-footer-code-manager' ),
                    'css'  => esc_html__( 'CSS', 'header-footer-code-manager' ),
                    'js'   => esc_html__( 'Javascript', 'header-footer-code-manager' )
                ); ?>
                <tr id="snippet_type">
                    <th class="hfcm-th-width">
                        <?php esc_html_e( 'Snippet Type', 'header-footer-code-manager' ); ?>
                    </th>
                    <td>
                        <select name="data[snippet_type]">
                            <?php
                            foreach ( $nnr_hfcm_snippet_type_array as $nnr_key => $nnr_item ) {
                                if ( $nnr_key === $nnr_snippet_type ) {
                                    echo "<option value='" . esc_attr( $nnr_key ) . "' selected>" . esc_html( $nnr_item ) . "</option>";
                                } else {
                                    echo "<option value='" . esc_attr( $nnr_key ) . "'>" . esc_html( $nnr_item ) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php
                $nnr_hfcm_display_array = array(
                    'All'            => esc_html__( 'Site Wide', 'header-footer-code-manager' ),
                    's_posts'        => esc_html__( 'Specific Posts', 'header-footer-code-manager' ),
                    's_pages'        => esc_html__( 'Specific Pages', 'header-footer-code-manager' ),
                    's_categories'   => esc_html__( 'Specific Categories (Archive & Posts)', 'header-footer-code-manager' ),
                    's_custom_posts' => esc_html__( 'Specific Post Types (Archive & Posts)', 'header-footer-code-manager' ),
                    's_tags'         => esc_html__( 'Specific Tags (Archive & Posts)', 'header-footer-code-manager' ),
                    's_is_home'      => esc_html__( 'Home Page', 'header-footer-code-manager' ),
                    's_is_search'    => esc_html__( 'Search Page', 'header-footer-code-manager' ),
                    's_is_archive'   => esc_html__( 'Archive Page', 'header-footer-code-manager' ),
                    'latest_posts'   => esc_html__( 'Latest Posts', 'header-footer-code-manager' ),
                    'manual'         => esc_html__( 'Shortcode Only', 'header-footer-code-manager' ),
                ); ?>
                <tr>
                    <th class="hfcm-th-width"><?php esc_html_e( 'Site Display', 'header-footer-code-manager' ); ?></th>
                    <td>
                        <select name="data[display_on]" onchange="hfcm_showotherboxes(this.value);">
                            <?php
                            foreach ( $nnr_hfcm_display_array as $dkey => $statusv ) {
                                if ( $display_on === $dkey ) {
                                    printf( '<option value="%1$s" selected="selected">%2$s</option>', $dkey, $statusv );
                                } else {
                                    printf( '<option value="%1$s">%2$s</option>', $dkey, $statusv );
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php
                $nnr_hfcm_pages                      = get_pages();
                $nnr_hfcm_exclude_pages_style        = ('s_pages' === $display_on) ? 'display:none;' : '';
                $nnr_hfcm_exclude_posts_style        = ('s_posts' === $display_on) ? 'display:none;' : '';
                $nnr_hfcm_exclude_categories_style   = 's_categories' === $display_on ? 'display:none;' : '';
                $nnr_hfcm_exclude_tags_style         = 's_tags' === $display_on ? 'display:none;' : '';
                $nnr_hfcm_exclude_custom_posts_style = 's_custom_posts' === $display_on ? 'display:none;' : '';
                $nnr_hfcm_exclude_lp_count_style     = 'latest_posts' === $display_on ? 'display:none;' : '';
                $nnr_hfcm_exclude_manual_style       = 'manual' === $display_on ? 'display:none;' : '';
                ?>
                <tr id="ex_pages"
                    style="<?php echo esc_attr( $nnr_hfcm_exclude_pages_style . $nnr_hfcm_exclude_posts_style . $nnr_hfcm_exclude_tags_style . $nnr_hfcm_exclude_custom_posts_style . $nnr_hfcm_exclude_categories_style . $nnr_hfcm_exclude_lp_count_style . $nnr_hfcm_exclude_manual_style ); ?>">
                    <th class="hfcm-th-width"><?php esc_html_e( 'Exclude Pages', 'header-footer-code-manager' ); ?></th>
                    <td>
                        <select name="data[ex_pages][]" multiple>
                            <?php
                            foreach ( $nnr_hfcm_pages as $pdata ) {
                                if ( in_array( $pdata->ID, $ex_pages ) ) {
                                    printf( '<option value="%1$s" selected="selected">%2$s</option>', $pdata->ID, $pdata->post_title );
                                } else {
                                    printf( '<option value="%1$s">%2$s</option>', $pdata->ID, $pdata->post_title );
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr id="ex_posts"
                    style="<?php echo esc_attr( $nnr_hfcm_exclude_pages_style . $nnr_hfcm_exclude_posts_style . $nnr_hfcm_exclude_tags_style . $nnr_hfcm_exclude_custom_posts_style . $nnr_hfcm_exclude_categories_style . $nnr_hfcm_exclude_lp_count_style . $nnr_hfcm_exclude_manual_style ); ?>">
                    <th class="hfcm-th-width"><?php esc_html_e( 'Exclude Posts', 'header-footer-code-manager' ); ?></th>
                    <td>
                        <select class="nnr-wraptext" name="data[ex_posts][]" multiple>
                            <option disabled></option>
                        </select> <img id="loader"
                                       src="<?php echo plugins_url( 'images/ajax-loader.gif', dirname( __FILE__ ) ); ?>">
                    </td>
                </tr>
                <?php
                $nnr_hfcm_pages       = get_pages();
                $nnr_hfcm_pages_style = ('s_pages' === $display_on) ? '' : 'display:none;';
                ?>
                <tr id="s_pages" style="<?php echo esc_attr( $nnr_hfcm_pages_style ); ?>">
                    <th class="hfcm-th-width">
                        <?php esc_html_e( 'Page List', 'header-footer-code-manager' ); ?>
                    </th>
                    <td>
                        <select name="data[s_pages][]" multiple>
                            <?php
                            foreach ( $nnr_hfcm_pages as $pdata ) {
                                if ( in_array( $pdata->ID, $s_pages ) ) {
                                    printf( '<option value="%1$s" selected="selected">%2$s</option>', esc_attr( $pdata->ID ), esc_attr( $pdata->post_title ) );
                                } else {
                                    printf( '<option value="%1$s">%2$s</option>', esc_attr( $pdata->ID ), esc_attr( $pdata->post_title ) );
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php $nnr_hfcm_posts_style = 's_posts' === $display_on ? '' : 'display:none;'; ?>
                <tr id="s_posts" style="<?php echo esc_attr( $nnr_hfcm_posts_style ); ?>">
                    <th class="hfcm-th-width">
                        <?php esc_html_e( 'Post List', 'header-footer-code-manager' ); ?>
                    </th>
                    <td>
                        <select class="nnr-wraptext" name="data[s_posts][]" multiple>
                            <option disabled>...</option>
                        </select>
                    </td>
                </tr>
                <?php
                $nnr_hfcm_categories = NNR_HFCM::hfcm_get_categories();
                $nnr_hfcm_tags       = NNR_HFCM::hfcm_get_tags();

                $nnr_hfcm_categories_style   = 's_categories' === $display_on ? '' : 'display:none;';
                $nnr_hfcm_tags_style         = 's_tags' === $display_on ? '' : 'display:none;';
                $nnr_hfcm_custom_posts_style = 's_custom_posts' === $display_on ? '' : 'display:none;';
                $nnr_hfcm_lpcount_style      = 'latest_posts' === $display_on ? '' : 'display:none;';
                $nnr_hfcm_location_style     = 'manual' === $display_on ? 'display:none;' : '';

                // Get all names of Post Types
                $args = array(
                    'public' => true,
                );

                $output   = 'names';
                $operator = 'and';

                $nnr_hfcm_custom_post_types = get_post_types( $args, $output, $operator );
                $nnr_hfcm_post_types        = array( 'post' );
                foreach ( $nnr_hfcm_custom_post_types as $cpdata ) {
                    $nnr_hfcm_post_types[] = $cpdata;
                }
                ?>
                <tr id="s_categories" style="<?php echo esc_attr( $nnr_hfcm_categories_style ); ?>">
                    <th class="hfcm-th-width"><?php esc_html_e( 'Category List', 'header-footer-code-manager' ); ?></th>
                    <td>
                        <select name="data[s_categories][]" multiple>
                            <?php
                            foreach ( $nnr_hfcm_categories as $nnr_key_cat => $nnr_item_cat ) {
                                foreach ( $nnr_item_cat['terms'] as $nnr_item_cat_key => $nnr_item_cat_term ) {
                                    if ( in_array( $nnr_item_cat_term->term_id, $s_categories ) ) {
                                        echo "<option value='" . esc_attr( $nnr_item_cat_term->term_id ) . "' selected>" . esc_html( $nnr_item_cat['name'] ) . " - " . esc_html( $nnr_item_cat_term->name ) . "</option>";
                                    } else {
                                        echo "<option value='" . esc_attr( $nnr_item_cat_term->term_id ) . "'>" . esc_html( $nnr_item_cat['name'] ) . " - " . esc_html( $nnr_item_cat_term->name ) . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr id="s_tags" style="<?php echo esc_attr( $nnr_hfcm_tags_style ); ?>">
                    <th class="hfcm-th-width"><?php esc_html_e( 'Tags List', 'header-footer-code-manager' ); ?></th>
                    <td>
                        <select name="data[s_tags][]" multiple>
                            <?php
                            foreach ( $nnr_hfcm_tags as $nnr_key_cat => $nnr_item_tag ) {
                                foreach ( $nnr_item_tag['terms'] as $nnr_item_tag_key => $nnr_item_tag_term ) {
                                    if ( in_array( $nnr_item_tag_term->term_id, $s_tags ) ) {
                                        echo "<option value='" . esc_attr( $nnr_item_tag_term->term_id ) . "' selected>" . esc_html( $nnr_item_tag['name'] ) . " - " . esc_html( $nnr_item_tag_term->name ) . "</option>";
                                    } else {
                                        echo "<option value='" . esc_attr( $nnr_item_tag_term->term_id ) . "'>" . esc_html( $nnr_item_tag['name'] ) . " - " . esc_html( $nnr_item_tag_term->name ) . "</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr id="c_posttype" style="<?php echo esc_attr( $nnr_hfcm_custom_posts_style ); ?>">
                    <th class="hfcm-th-width"><?php esc_html_e( 'Post Types', 'header-footer-code-manager' ); ?></th>
                    <td>
                        <select name="data[s_custom_posts][]" multiple>
                            <?php
                            foreach ( $nnr_hfcm_custom_post_types as $cpkey => $cpdata ) {
                                if ( in_array( $cpkey, $s_custom_posts ) ) {
                                    echo "<option value='" . esc_attr( $cpkey ) . "' selected>" . esc_html( $cpdata ) . "</option>";
                                } else {
                                    echo "<option value='" . esc_attr( $cpkey ) . "'>" . esc_html( $cpdata ) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr id="lp_count" style="<?php echo esc_attr( $nnr_hfcm_lpcount_style ); ?>">
                    <th class="hfcm-th-width"><?php esc_html_e( 'Post Count', 'header-footer-code-manager' ); ?></th>
                    <td>
                        <select name="data[lp_count]">
                            <?php
                            for ( $i = 1; $i <= 20; $i++ ) {
                                if ( $i == $lp_count ) {
                                    echo "<option value='" . esc_attr( $i ) . "' selected>" . esc_html( $i ) . "</option>";
                                } else {
                                    echo "<option value='" . esc_attr( $i ) . "'>" . esc_html( $i ) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php
                if ( in_array( $display_on, array( 's_posts', 's_pages', 's_custom_posts', 's_tags',
                                                   'latest_posts' ) ) ) {
                    $nnr_hfcm_locations = array(
                        'header'         => __( 'Header', 'header-footer-code-manager' ),
                        'before_content' => __( 'Before Content', 'header-footer-code-manager' ),
                        'after_content'  => __( 'After Content', 'header-footer-code-manager' ),
                        'footer'         => __( 'Footer', 'header-footer-code-manager' )
                    );
                } else {
                    $nnr_hfcm_locations = array(
                        'header' => __( 'Header', 'header-footer-code-manager' ),
                        'footer' => __( 'Footer', 'header-footer-code-manager' )
                    );
                }
                ?>
                <tr id="locationtr" style="<?php echo esc_attr( $nnr_hfcm_location_style ); ?>">
                    <th class="hfcm-th-width">
                        <?php esc_html_e( 'Location', 'header-footer-code-manager' ); ?>
                    </th>
                    <td>
                        <select name="data[location]" id="data_location">
                            <?php
                            foreach ( $nnr_hfcm_locations as $lkey => $statusv ) {
                                if ( $location === $lkey ) {
                                    echo "<option value='" . esc_attr( $lkey ) . "' selected='selected'>" . esc_html( $statusv ) . '</option>';
                                } else {
                                    echo "<option value='" . esc_attr( $lkey ) . "'>" . esc_html( $statusv ) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <p>
                            <b><?php _e( "Note", 'header-footer-code-manager' ); ?></b>: <?php _e( "Not all locations (such as before content) exist on all page/post types. The location will only appear as an option if the appropriate hook exists on the page.", 'header-footer-code-manager' ); ?>
                        </p>
                    </td>
                </tr>
                <?php $nnr_hfcm_device_type_array = array(
                    'both'    => __( 'Show on All Devices', 'header-footer-code-manager' ),
                    'desktop' => __( 'Only Desktop', 'header-footer-code-manager' ),
                    'mobile'  => __( 'Only Mobile Devices', 'header-footer-code-manager' )
                ) ?>
                <?php $nnr_hfcm_status_array = array(
                    'active'   => __( 'Active', 'header-footer-code-manager' ),
                    'inactive' => __( 'Inactive', 'header-footer-code-manager' )
                ) ?>
                <tr>
                    <th class="hfcm-th-width"><?php esc_html_e( 'Device Display', 'header-footer-code-manager' ); ?></th>
                    <td>
                        <select name="data[device_type]">
                            <?php
                            foreach ( $nnr_hfcm_device_type_array as $smkey => $typev ) {
                                if ( $device_type === $smkey ) {
                                    echo "<option value='" . esc_attr( $smkey ) . "' selected='selected'>" . esc_html( $typev ) . '</option>';
                                } else {
                                    echo "<option value='" . esc_attr( $smkey ) . "'>" . esc_html( $typev ) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th class="hfcm-th-width"><?php esc_html_e( 'Status', 'header-footer-code-manager' ); ?></th>
                    <td>
                        <select name="data[status]">
                            <?php
                            foreach ( $nnr_hfcm_status_array as $skey => $statusv ) {
                                if ( $status === $skey ) {
                                    echo "<option value='" . esc_attr( $skey ) . "' selected='selected'>" . esc_html( $statusv ) . '</option>';
                                } else {
                                    echo "<option value='" . esc_attr( $skey ) . "'>" . esc_html( $statusv ) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <?php if ( $update ) : ?>
                    <tr>
                        <th class="hfcm-th-width"><?php esc_html_e( 'Shortcode', 'header-footer-code-manager' ); ?></th>
                        <td>
                            <p>
                                [hfcm id="<?php echo esc_html( $id ); ?>"]
                                <?php if ( $update ) :
                                    ?>
                                    <a data-shortcode='[hfcm id="<?php echo absint( $id ); ?>"]'
                                       href="javascript:void(0);" class="nnr-btn-click-to-copy nnr-btn-copy-inline"
                                       id="hfcm_copy_shortcode">
                                        <?php esc_html_e( 'Copy', 'header-footer-code-manager' ); ?>
                                    </a>
                                <?php endif; ?>
                            </p>

                        </td>
                    </tr>
                    <tr>
                        <th class="hfcm-th-width">
                            <?php esc_html_e( 'Changelog', 'header-footer-code-manager' ); ?>
                        </th>
                        <td>
                            <p>
                                <?php esc_html_e( 'Snippet created by', 'header-footer-code-manager' ); ?>
                                <b><?php echo esc_html( $createdby ); ?></b> <?php echo _e( 'on', 'header-footer-code-manager' ) . ' ' . date_i18n( get_option( 'date_format' ), strtotime( $createdon ) ) . ' ' . __( 'at', 'header-footer-code-manager' ) . ' ' . date_i18n( get_option( 'time_format' ), strtotime( $createdon ) ) ?>
                                <br/>
                                <?php if ( !empty( $lastmodifiedby ) ) : ?>
                                    <?php esc_html_e( 'Last edited by', 'header-footer-code-manager' ); ?>
                                    <b><?php echo esc_html( $lastmodifiedby ); ?></b> <?php echo _e( 'on', 'header-footer-code-manager' ) . ' ' . date_i18n( get_option( 'date_format' ), strtotime( $lastrevisiondate ) ) . ' ' . __( 'at', 'header-footer-code-manager' ) . ' ' . date_i18n( get_option( 'time_format' ), strtotime( $lastrevisiondate ) ) ?>
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>
            </table>
            <div class="nnr-mt-20">
                <h1><?php esc_html_e( 'Snippet', 'header-footer-code-manager' ); ?>
                    / <?php esc_html_e( 'Code', 'header-footer-code-manager' ) ?></h1>
                <div class="nnr-mt-20 nnr-hfcm-codeeditor-box">
                    <textarea name="data[snippet]" aria-describedby="nnr-newcontent-description" id="nnr_newcontent"
                              rows="20"><?php echo html_entity_decode( $snippet ); ?></textarea>

                    <p class="notice notice-warning nnr-padding10" id="nnr-snippet-warning">
                        <?php _e( 'Warning: Using improper code or untrusted sources code can break your site or create security risks. <a href="https://draftpress.com/security-risks-of-wp-plugins-that-allow-code-editing-or-insertion" target="_blank">Learn more</a>.', 'header-footer-code-manager' ); ?>
                    </p>
                    <div class="wp-core-ui">
                        <input type="submit"
                               name="<?php echo $update ? 'update' : 'insert'; ?>"
                               value="<?php echo $update ? esc_html__( 'Update', 'header-footer-code-manager' ) : esc_html__( 'Save', 'header-footer-code-manager' ) ?>"
                               class="button button-primary button-large nnr-btnsave">
                        <?php if ( $update ) :
                            $delete_nonce = wp_create_nonce( 'hfcm_delete_snippet' );
                            ?>
                            <a onclick="return nnr_confirm_delete_snippet();"
                               href="<?php echo esc_url( admin_url( 'admin.php?page=hfcm-list&action=delete&_wpnonce=' . $delete_nonce . '&snippet=' . absint( $id ) ) ); ?>"
                               class="button button-secondary button-large nnr-btndelete"><?php esc_html_e( 'Delete', 'header-footer-code-manager' ); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>

<?php
if (defined( 'DISALLOW_FILE_EDIT' ) && true === DISALLOW_FILE_EDIT && !get_user_meta(get_current_user_id(),'hfcm_file_edit_plugin_notice_dismissed', true) ) {
    ?>
    <div id="file-editor-warning" class="notification-dialog-wrap file-editor-warning hide-if-no-js">
        <div class="notification-dialog-background"></div>
        <div class="notification-dialog">
            <div class="file-editor-warning-content">
                <div class="file-editor-warning-message">
                    <h1>Heads up!</h1>
                    <p>
                        <?php _e('Your site has <a href="https://draftpress.com/disallow-file-edit-setting-wordpress" target="_blank">disallow_file_edit</a> setting enabled inside the wp-config file to prevent file edits. By using this plugin, you acknowledge that you know what you’re doing and intend on adding code snippets only from trusted sources.', 'header-footer-code-manager'); ?>
                    </p>
                </div>
                <p>
                    <?php
                    if ( $update ) :
                        $hfcm_file_edit_dismiss_action = admin_url( 'admin.php?page=hfcm-update&hfcm-file-edit-notice-dismissed=1&id=' . absint( $id ) );
                    else :
                        $hfcm_file_edit_dismiss_action = admin_url( 'admin.php?page=hfcm-create&hfcm-file-edit-notice-dismissed=1' );
                    endif;
                    ?>
                    <a href="<?php echo $hfcm_file_edit_dismiss_action; ?>" class="file-editor-warning-dismiss button button-primary" id="nnr-dismiss-editor-warning">I understand</a>
                </p>
            </div>
        </div>
    </div>
    <?php
}
?>