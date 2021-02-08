<?php

class qetl_templateDebugger
{
    public $settings = [];
    private $defaults = [
        'qetl_max_recursive' => 99,
        'qetl_checkbox_exectime' => 1,
        'qetl_checkbox_plugins' => 0,
        'qetl_show_parts' => 1
    ];

    public $wpContentPath = null;
    public $wpContentName = null;

    public $mainTemplate = null;
    public $currentTemplate = null;
    public $errorMessage = null;
    public $successMessage = null;

    public function __construct()
    {
        $this->_initSettings();

        $this->wpContentPath = WP_CONTENT_DIR;
        $this->wpContentName = basename( $this->wpContentPath );

        add_action('admin_bar_init', [$this,'_initActions']);
        add_action('admin_menu', [$this,'_initMenu']);
        add_action('admin_init', [$this,'_initConfig']);
        add_action('add_meta_boxes', [$this,'_metaBoxes']);
        add_action('save_post', [$this,'_savePageTemplate']);
    }

    /**
     * @return null
     */
    public function getMainTemplate()
    {
        return $this->mainTemplate;
    }

    /**
     * @param null $mainTemplate
     */
    public function setMainTemplate($mainTemplate)
    {
        $this->mainTemplate = $mainTemplate;
    }



    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function getSetting($key){
        if( !empty($this->settings[$key])) {
            return $this->settings[$key];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }


    private function _initSettings() {

        $this->setSettings( $this->getDefaults() );

        $options = get_option('qetl_settings');

        if( is_array($options) ) {
            $this->setSettings( array_merge($this->getDefaults(), $options ));
        }
    }

    public function _initMenu() {
        add_menu_page('Template Debugger', 'T. Debugger', 'manage_options', 'quick_edit_template_link',
            [$this,'_optionsPage']);
    }

    public function _initConfig() {

        register_setting('pluginPage', 'qetl_settings');

        add_settings_section('qetl_pluginPage_section', __('General', 'quick-edit-template-link'),
            [$this,'sectionCallback'], 'pluginPage');
        add_settings_section('qetl_pluginPage_section2', __('Plugins', 'quick-edit-template-link'),
            [$this,'section2Callback'], 'pluginPage');

        add_settings_field('qetl_checkbox_exectime', __('Show load time', 'quick-edit-template-link'),
            [$this,'loadTimeRender'], 'pluginPage', 'qetl_pluginPage_section');

        add_settings_field('qetl_checkbox_includes', __('Show Template Parts', 'quick-edit-template-link'),
            [$this,'echoIncludesRender'], 'pluginPage', 'qetl_pluginPage_section');

        add_settings_field('qetl_checkbox_plugins', __('Show Plugins in Dropdown', 'quick-edit-template-link'),
            [$this,'pluginsDropdownRender'], 'pluginPage', 'qetl_pluginPage_section2');

        add_settings_field('qetl_exclude_plugins', __('Exclude From Dropdown', 'quick-edit-template-link'),
            [$this,'excludeCheckboxes'], 'pluginPage', 'qetl_pluginPage_section2');
        add_settings_field('qetl_exclude_plugins', __('Maximum recursive depth', 'quick-edit-template-link'),
            [$this,'maxRecursiveRender'], 'pluginPage', 'qetl_pluginPage_section');

        add_settings_section('qetl_pluginPage_child', '', [$this,'_null'], 'childTheme');
        add_settings_field('qetl_select_theme', __('Select Parent Theme', 'quick-edit-template-link'),
            [$this,'parentThemeRender'],'childTheme', 'qetl_pluginPage_child');
        add_settings_field('qetl_theme_name1', __('Child Theme Name', 'quick-edit-template-link'),
            [$this,'childThemeName'], 'childTheme', 'qetl_pluginPage_child');
        add_settings_field('qetl_theme_name2', __('Author Name', 'quick-edit-template-link'),
            [$this,'childThemeAuthor'], 'childTheme', 'qetl_pluginPage_child');
        add_settings_field('qetl_theme_name3', __('Author URI', 'quick-edit-template-link'),
            [$this,'childThemeUri'], 'childTheme', 'qetl_pluginPage_child');
        add_settings_field('qetl_theme_name4', __('Child Theme Version', 'quick-edit-template-link'),
            [$this,'childThemeVersion'], 'childTheme', 'qetl_pluginPage_child');

        $this->checkFormAction();
    }

    public function _null() {}

    public function _initActions()
    {
        if (!is_super_admin() || !is_admin_bar_showing() || is_admin()) {
            return;
        }

        load_plugin_textdomain('quick-edit-template-link', false, 'quick-edit-template-link/languages');
        add_action('wp_enqueue_scripts', [$this, 'adminBarStyling']);
        add_action('admin_bar_menu', [$this, 'adminBarLink'], 500);
        if ($this->getSetting('qetl_show_parts')) {
            add_action('get_template_part', [$this, 'showTemplatePart'], 10,3);
            add_filter('render_block', [$this, 'renderBlock'], 10,2);
        }
    }


    public function adminBarStyling() {
        wp_register_style('template-debugger', plugin_dir_url(__FILE__) . '../css/quick_edit_template_link.min.css');
        wp_enqueue_style('template-debugger');
    }

    public function adminBarLink() {
        global $wp_admin_bar, $template;

        $href = $url = '#';

        $explode_on = 'themes';
        if (strstr($template, '/' . $this->wpContentName . '/plugins/')) {
            $explode_on = 'plugins';
        }
        $explode_content    = explode('/' . $this->wpContentName . '/' . $explode_on . '/', $template);
        $cur_name = end($explode_content);
        $this->setMainTemplate($cur_name);
        $name               = str_replace('/', ' &rarr; ', $cur_name);

        $explode_content    = explode('/', $cur_name);
        $qetl_main_template = end($explode_content);
        $this->setMainTemplate($qetl_main_template);

        if (current_user_can('edit_themes')) {
            $parts = explode('/', $cur_name, 2);
            $url   = admin_url('theme-editor.php?file=%s&theme=%s');

            if (count($parts) > 1) {
                $href = sprintf($url, $parts[1], $parts[0]);
            }
        }

        $lt = null;
        if ($this->getSetting('qetl_checkbox_exectime') == 1) {

            $load_time = sprintf('<span class="dashicons dashicons-clock"></span> %ss',timer_stop(0));

            $lt = ' / ' . $load_time;
        }

        // Add as a parent menu
        $wp_admin_bar->add_node(array(
            'title' => '<span class="ab-icon"></span>' . $name . $lt,
            'href'  => $href,
            'id'    => 'edit-tpl'
        ));



        $this->addPart($this->parseIncludes(), 'edit-tpl', $url, 0, $this->getSetting('qetl_max_recursive'));
    }


    private function addPart($parts, $class, $url, $depth = 0, $max_depth = 99, $prepend_path = '', $type = '')
    {

        global $wp_admin_bar;

        $current_prepend_path = $prepend_path;


        if ( ! is_array($parts)) {
            return;
        }
        if ($depth > $max_depth) {
            return;
        }

        foreach ($parts as $key => $part) {

            if ($depth == 0) {
                $type = $key;
            }
            if ($depth == 1) {
                $this->currentTemplate = $key;
            }

            $id = $class . '-' . $key;

            if (is_array($part)) {

                if ($depth >= 2) {
                    $current_prepend_path = $prepend_path . $key . '/';
                }

                $wp_admin_bar->add_node(array(
                    'parent' => $class,
                    'title'  => $key,
                    'href'   => '#',
                    'id'     => $id
                ));

                $this->addPart($part, $id, $url, ($depth + 1), $max_depth, $current_prepend_path, $type);

            } else {

                $href = '#';

                if (current_user_can('edit_themes') && $type == 'themes') {
                    $_part = $prepend_path . $part;
                    $href  = sprintf($url, $_part, $this->currentTemplate);
                }


                if ($part == $this->mainTemplate) {
                    $part = '* ' . $part;
                }

                $wp_admin_bar->add_node(array(
                    'parent' => $class,
                    'title'  => $part,
                    'href'   => $href,
                    'id'     => $id
                ));
            }

        }

    }

    private function pathToArray($path, $separator = '/')
    {
        if (($pos = strpos($path, $separator)) === false) {
            return array($path);
        }

        return array(substr($path, 0, $pos) => $this->pathToArray(substr($path, $pos + 1)));
    }

    private function parseIncludes()
    {
        $files = get_included_files();

        $incs = array();

        foreach ($files as $f => $file) {
            if ( ! strstr($file, '/' . $this->wpContentName . '/themes/') && ! strstr($file, '/' . $this->wpContentName . '/plugins/')) {
                continue;
            }

            if (!$this->getSetting('qetl_checkbox_plugins') && strstr($file, '/' . $this->wpContentName . '/plugins/')) {
                continue;
            }

            $explode_file = explode('/' . $this->wpContentName . '/', $file);
            $file         = end($explode_file);

            if (strstr($file, 'plugins')) {
                $str_replaced     = str_replace('plugins/', '', $file);
                $replaced_explode = explode('/', $str_replaced);
                $replaced_current = current($replaced_explode);
                $hash             = md5($replaced_current);
                if ($this->getSetting('qetl_exclude_plugin_' . $hash)) {
                    continue;
                }
            }

            $incs = array_merge_recursive($incs, $this->pathToArray($file));
        }

        return $incs;
    }







    public function _optionsPage() {
        $active_tab = 'general';

        if (isset($_GET['tab'])) {
            $active_tab = $_GET['tab'];
        }
        ?>
        <div class="wrap">
            <h1>Template Debugger</h1>

            <?php
            $donate_link = 'https://wordpress.org/support/view/plugin-reviews/quick-edit-template-link';
            ?>
            <p><?php echo sprintf(__('If you find this plugin useful or would like to see something added, please take a minute to <a href="%s" target="_blank">Rate &amp; Review</a> the plugin.',
                    'quest-edit-template-link'), $donate_link); ?></p>

            <div style="width:60%;float:left;">

                <h2 class="nav-tab-wrapper">
                    <a class="nav-tab <?php echo(($active_tab == 'general') ? 'nav-tab-active' : null); ?>" href="admin.php?page=quick_edit_template_link"><?php echo __('General',
                            'quick-edit-template-link'); ?></a>
                    <a class="nav-tab <?php echo(($active_tab == 'child_theme') ? 'nav-tab-active' : null); ?>" href="admin.php?page=quick_edit_template_link&tab=child_theme"><?php echo __('Create a Child Theme',
                            'quick-edit-template-link'); ?></a>
                </h2>

                <?php
                switch ($active_tab) {
                    case 'general':
                        ?>
                        <form action='options.php' method='post'>
                            <?php
                            settings_fields('pluginPage');
                            do_settings_sections('pluginPage');
                            submit_button();
                            ?>
                        </form>
                        <?php
                        break;

                    case 'child_theme':
                        $this->_initThemeCreator();
                        break;

                    default:
                        settings_fields('pluginPage');
                        do_settings_sections('pluginPage');
                        submit_button();
                }

                ?>
            </div>
            <div style="width:35%;float:right;">
                <h3><?php echo __('Support', 'quick-edit-template-link'); ?></h3>
                <?php
                $donate_link = 'https://wordpress.org/support/view/plugin-reviews/quick-edit-template-link';
                ?>
                <p><?php echo sprintf(__('If you find this plugin useful or would like to see something added, please take a minute to <a href="%s" target="_blank">Rate &amp; Review</a> the plugin.',
                        'quest-edit-template-link'), $donate_link); ?></p>

                <p><a href="<?=$donate_link?>" target="_blank" style="text-decoration: none"><span class="dashicons dashicons-wordpress" style="color:black"></span> <span class="dashicons dashicons-star-filled" style="color:#ffb900"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900"></span> </a></p>

                <p>And if it saved your life in some way, you may fancy buying me a coffee</p>
                <style>.bmc-button img{width: 27px !important;margin-bottom: 1px !important;box-shadow: none !important;border: none !important;vertical-align: middle !important;}.bmc-button{line-height: 36px !important;height:37px !important;text-decoration: none !important;display:inline-flex !important;color:#ffffff !important;background-color:#FF813F !important;border-radius: 3px !important;border: 1px solid transparent !important;padding: 1px 9px !important;font-size: 22px !important;letter-spacing:0.6px !important;box-shadow: 0px 1px 2px rgba(190, 190, 190, 0.5) !important;-webkit-box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;margin: 0 auto !important;font-family:'Cookie', cursive !important;-webkit-box-sizing: border-box !important;box-sizing: border-box !important;-o-transition: 0.3s all linear !important;-webkit-transition: 0.3s all linear !important;-moz-transition: 0.3s all linear !important;-ms-transition: 0.3s all linear !important;transition: 0.3s all linear !important;}.bmc-button:hover, .bmc-button:active, .bmc-button:focus {-webkit-box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;text-decoration: none !important;box-shadow: 0px 1px 2px 2px rgba(190, 190, 190, 0.5) !important;opacity: 0.85 !important;color:#ffffff !important;}</style><link href="https://fonts.googleapis.com/css?family=Cookie" rel="stylesheet"><a class="bmc-button" target="_blank" href="https://www.buymeacoffee.com/developerdanny"><img src="https://bmc-cdn.nyc3.digitaloceanspaces.com/BMC-button-images/BMC-btn-logo.svg" alt="Buy me a coffee"><span style="margin-left:5px">Buy me a coffee</span></a>
                <br>
                <br>
                <p><a href="https://twitter.com/danny_developer" target="_blank" style="text-decoration: none"><span class="dashicons dashicons-twitter" style="color:#1da1f2"></span> Or just drop me a tweet!</a></p>
            </div>
            <div class="clear:both;"></div>
        </div>
        <?php

    }



    public function _initThemeCreator() {
        ?>
        <p><?php echo __('When you download a theme and want to modify it, you should always do so in a child theme, this ensures your changes are not lost when the theme is updated by the author.',
                'quick-edit-template-link'); ?></p>

        <p><?php echo __('Using the form below, you can easily create the child theme', 'quick-edit-template-link'); ?></p>

        <?php
        if (is_wp_error($this->errorMessage)) {
            ?>
            <div id="" class="error">
                <p><?php echo $this->errorMessage->get_error_message(); ?></p>
            </div>
            <div style="border-left:solid 4px #f90000; background-color:#fff; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
                <p style="margin: 0;padding: 13px 17px;"><?php echo $this->errorMessage->get_error_message(); ?></p>
            </div>
            <?php
        }

        if ($this->successMessage) {

            $message = sprintf(__('Your Child theme has been created, head over to <a href="%s" target="_blank">Themes</a> to activate it.',
                'quest-edit-template-link'), admin_url('themes.php'));
            ?>
            <div id="" class="updated">
                <p><?php echo $message; ?></p>
            </div>
            <div id="" style="border-left:solid 4px #7ad03a; background-color:#fff; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);">
                <p style="margin: 0;padding: 13px 17px;"><?php echo $message; ?></p>
            </div>

            <?php
            $donate_link = 'https://wordpress.org/support/view/plugin-reviews/quick-edit-template-link';
            ?>
            <p style="font-weight: bold;"><?php echo sprintf(__('If you find this plugin useful or would like to see something added, please take a minute to <a href="%s" target="_blank">Rate &amp; Review</a> the plugin.',
                    'quest-edit-template-link'), $donate_link); ?></p>
            <?php
        } else {
            ?>
            <form action="" method="post">
                <input type="hidden" name="action" value="qetl_generate_child">
                <?php
                do_settings_sections('childTheme');
                submit_button(__('Create Child Theme', 'quick-edit-template-link'));
                ?>
            </form>
            <?php
        }
    }





    public function sectionCallback() {
        echo __('This plugin appends the clone bar with a dropdown showing you what files are being included on that specific page',
            'quick-edit-template-link');
    }

    public function section2Callback() {
        echo __('If you want to exclude specific plugins from the dropdown, select them here', 'quick-edit-template-link');
    }


    public function echoIncludesRender() {
        ?>

        <label style="margin-right:20px"><input type='radio' name='qetl_settings[qetl_show_parts]' <?=($this->getSetting('qetl_show_parts') == '1') ? 'checked': '' ?> value='1'>Yes</label>
        <label><input type='radio' name='qetl_settings[qetl_show_parts]' <?=(!$this->getSetting('qetl_show_parts')) ? 'checked': '' ?> value='0'>
            No</label>
        <p>Checking this box will display information about the file being called by <tt>get_template_part()</tt> in HTML comments.</p>
<code style="display: block;white-space: pre;padding: 15px 25px;width: 100%;box-sizing: border-box;box-shadow: 2px 1px 16px -11px #000;margin-top: 20px;font-size: 10px;">&lt;!--
Template Debugger
=================
Called: get_template_part('template-parts/header/site','branding')
Slug: template-parts/header/site
Name: branding
Called From: /<?=$this->wpContentName?>/themes/twentynineteen/header.php
Template used: /<?=$this->wpContentName?>/themes/twentynineteen/template-parts/header/site-branding.php

Template options:
------------------
template-parts/header/site-branding.php
template-parts/header/site.php
------------------
--&gt;</code>
        <p>To view the information you must use Developer Tools or View Source in the browser, eg:</p>
        <?php
    }

    public function loadTimeRender() {
        ?>
        <label style="margin-right:20px"><input type='radio' name='qetl_settings[qetl_checkbox_exectime]' <?=($this->getSetting('qetl_checkbox_exectime') == '1') ? 'checked': '' ?> value='1'>Yes</label>
        <label><input type='radio' name='qetl_settings[qetl_checkbox_exectime]' <?=(!$this->getSetting('qetl_checkbox_exectime')) ? 'checked': '' ?> value='0'>
            No</label>
        <?php
    }

    public function pluginsDropdownRender() {
        ?>
        <label style="margin-right:20px"><input type='radio' name='qetl_settings[qetl_checkbox_plugins]' <?=($this->getSetting('qetl_checkbox_plugins') == '1') ? 'checked': '' ?> value='1'>Yes</label>
        <label><input type='radio' name='qetl_settings[qetl_checkbox_plugins]' <?=(!$this->getSetting('qetl_checkbox_plugins')) ? 'checked': '' ?> value='0'>
            No</label>
        <?php
    }

    public function excludeCheckboxes() {
        $plugins = get_plugins();

        foreach ($plugins as $key => $val) {
            $hash = md5(current(explode('/', $key)));
            ?>
            <label><input type='checkbox' name='qetl_settings[qetl_exclude_plugin_<?php echo $hash; ?>]' <?php checked($this->getSetting('qetl_exclude_plugin_' . $hash),1); ?> value='1'>
                <?php echo $val['Name']; ?></label>
            <br>
            <?php
        }
    }

    public function maxRecursiveRender() {
        ?>
        <input type='text' name='qetl_settings[qetl_max_recursive]' value='<?php echo $this->getSetting('qetl_max_recursive'); ?>'> (0 = <?php echo __('unlimited',
            'quick-edit-template-link'); ?>)
        <?php
    }

    public function parentThemeRender() {
        $theme_list = wp_get_themes();
        ?>
        <select name="qetl_parent_theme" id="">
            <option value=""><?php echo __('Select Theme', 'quick-edit-template-link'); ?></option>
            <?php
            foreach ($theme_list as $theme_slug => $theme__) {
                $theme = wp_get_theme($theme_slug);
                if ($theme->get('Template')) {
                    continue;
                }

                $name = $theme->get('Name');

                if ( ! $name) {
                    $name = $theme_slug;
                }

                ?>
                <option value="<?php echo $theme_slug; ?>" <?php echo((isset($_POST['qetl_parent_theme'])) ? ' selected=selected ' : null); ?> ><?php echo $name; ?></option>
                <?php
            }
            ?>
        </select>
        <?php
    }

    public function childThemeName() {
        ?>
        <input type='text' name='qetl_child_name' value="<?php echo((isset($_POST['qetl_child_name'])) ? $_POST['qetl_child_name'] : null); ?>">
        <?php
    }

    public function childThemeAuthor() {
        $current_user = wp_get_current_user();
        ?>
        <input type='text' name='qetl_child_author' value="<?php echo((isset($_POST['qetl_child_name'])) ? $_POST['qetl_child_author'] : $current_user->display_name); ?>">
        <?php
    }

    public function childThemeUri() {
        ?>
        <input type='text' name='qetl_child_author_uri' value="<?php echo((isset($_POST['qetl_child_name'])) ? $_POST['qetl_child_author_uri'] : null); ?>">
        <?php
    }

    public function childThemeVersion() {
        ?>
        <input type='text' name='qetl_child_version' value="<?php echo((isset($_POST['qetl_child_name'])) ? $_POST['qetl_child_version'] : '1.0.0'); ?>">
        <?php
    }



    public function checkFormAction() {
        if ( ! isset($_POST['action'])) {
            return;
        }
        if ($_POST['action'] != 'qetl_generate_child') {
            return;
        }

        // lets create the child theme
        $parent     = $_POST['qetl_parent_theme'];
        $name       = $_POST['qetl_child_name'];
        $slug       = sanitize_title($name);
        $author     = $_POST['qetl_child_author'];
        $author_uri = $_POST['qetl_child_author_uri'];
        $version    = $_POST['qetl_child_version'];

        if (empty($parent) || empty($name) || empty($author) || empty($version)) {
            $this->errorMessage = new WP_Error('broke',
                __("The parent theme, name, author and version must not be blank", "quick-edit-template-link"));
            return;
        }

        $ph = array(
            '$name',
            '$uri',
            '$author_uri',
            '$author',
            '$parent',
            '$version'
        );
        $live = array(
            $name,
            '',
            $author_uri,
            $author,
            $parent,
            $version
        );

        $root = get_theme_root();
        $path = $root . '/' . $slug;


        if (is_dir($path)) {
            $path .= '_' . wp_generate_password(5, false);
        }

        $ok = wp_mkdir_p($path);
        if ( ! $ok) {
            $this->errorMessage = new WP_Error('broke',
                __("I could not create your theme directory, please make sure " . $root . " is writable",
                    "quick-edit-template-link"));
            return;
        }


        // create default files
        $default_functions = file_get_contents(plugin_dir_path(__FILE__) . '../clone/functions.php');
        $functions         = str_replace($ph, $live, $default_functions);
        $fp                = fopen($path . '/functions.php', 'w');
        fwrite($fp, $functions);
        fclose($fp);

        $default_css = file_get_contents(plugin_dir_path(__FILE__) . '../clone/style.css');
        $css         = str_replace($ph, $live, $default_css);
        $fp          = fopen($path . '/style.css', 'w');
        fwrite($fp, $css);
        fclose($fp);

        $this->successMessage = true;
    }

    public function _metaBoxes() {
        add_meta_box('qetl_meta_box', __('Template Debugger'), [$this,'metaBoxRender'], null, 'side', 'high');
    }


    public function metaBoxRender($post, $metabox) {

        $templateDirectory = get_template_directory();
        ?>
        <strong><?php echo __('Template(s)', 'quick-edit-template-link'); ?></strong>
        <?php
        if ( ! $post->ID || ! $post->post_name) {
            ?>
            <p><?php echo __('You must save this item before options are available', 'quick-edit-template-link'); ?></p>
            <?php
        } else {

            $is_post_type    = false;
            $template_prefix = 'page';
            $version_used    = $more_options = $version_used = $version_id = null;

            switch ($post->post_type) {
                case 'page':
                    break;

                case 'post':
                    $template_prefix = 'single';
                    $is_post_type    = true;
                    break;

                default:
                    $is_post_type    = true;
                    $template_prefix = 'single-' . $post->post_type;
                    $more_options    = '<option value="custom_post_type">' . $template_prefix . '.php</option>';
            }


            if ($is_post_type) {

                if (file_exists($templateDirectory . '/' . $template_prefix . '.php')) {
                    $version_used = 'custom_post_type';
                }

            } else {

                $slug_version = $template_prefix . '-' . $post->post_name . '.php';
                $id_version   = $template_prefix . '-' . $post->ID . '.php';


                if (file_exists($templateDirectory . '/' . $slug_version)) {
                    $version_used = 'slug';
                }
                if (file_exists($templateDirectory . '/' . $id_version)) {
                    $version_used = 'id';
                }


                $frontpage_id = get_option('page_on_front');
                if ($frontpage_id == $post->ID) {
                    if (file_exists($templateDirectory . '/front-page.php')) {
                        $version_used = 'slug';
                        $slug_version = 'front-page.php';
                    }
                    $more_options = '<option value="front-page">front-page.php</option>';
                }
            }

            if ($version_used) {
                if ($is_post_type) {
                    $v = $template_prefix . '.php';
                } else {
                    $v = (($version_used == 'id') ? $id_version : $slug_version);
                }
                ?>
                <p><?php echo sprintf(__('This uses <strong>%s</strong>', 'quick-edit-template-link'), $v); ?></p>
                <?php
                return;
            }
            if ( ! is_writable($templateDirectory . '/')) {
                ?>
                <p><?php echo __('Your themes directory is not writable', 'quick-edit-template-link'); ?></p>
                <?php
                return;
            }
            ?>
            <p><?php echo __('Create a page specific template', 'quick-edit-template-link'); ?></p>
            <select name="qetl_create_template" id="">
                <option value=""><?php echo __('Select Template', 'quick-edit-template-link'); ?></option>
                <?php echo $more_options; ?>
                <?php
                if ( ! $is_post_type) {
                    ?>
                    <option value="slug"><?php echo $template_prefix; ?>-<?php echo $post->post_name; ?>.php</option>
                    <option value="id"><?php echo $template_prefix; ?>-<?php echo $post->ID; ?>.php</option>
                    <?php
                }
                ?>
            </select>
            <?php
        }
    }

    public function _savePageTemplate($post_id) {

        $templateDirectory = get_template_directory();

        if (empty($_POST['qetl_create_template'])) {
            return;
        }

        $version = $_POST['qetl_create_template'];
        $post    = get_post($post_id);

        $file      = null;
        $copy_from = 'page';

        if ($version == 'slug') {
            $file = 'page-' . $post->post_name;
        } elseif ($version == 'id') {
            $file = 'page-' . $post->ID;
        } elseif ($version == 'front-page') {
            $file = 'front-page';
        } elseif ($version == 'custom_post_type') {
            $file      = 'single-' . $post->post_type;
            $copy_from = 'single';
        }

        if ($file) {
            if ( ! file_exists($templateDirectory . '/' . $file . '.php')) {
                if ( ! file_exists($templateDirectory . '/' . $copy_from . '.php')) {
                    $copy_from = 'index';
                }
                copy($templateDirectory . '/' . $copy_from . '.php', $templateDirectory . '/' . $file . '.php');
            }
        }

    }


    public function _locateTemplate( $template_names ) {
        $located = '';
        foreach ( (array) $template_names as $template_name ) {
            if ( ! $template_name ) {
                continue;
            }
            if ( file_exists( STYLESHEETPATH . '/' . $template_name ) ) {
                $located = STYLESHEETPATH . '/' . $template_name;
                break;
            } elseif ( file_exists( TEMPLATEPATH . '/' . $template_name ) ) {
                $located = TEMPLATEPATH . '/' . $template_name;
                break;
            } elseif ( file_exists( ABSPATH . WPINC . '/theme-compat/' . $template_name ) ) {
                $located = ABSPATH . WPINC . '/theme-compat/' . $template_name;
                break;
            }
        }

        $located = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $located);

        return $located;
    }
    public function showTemplatePart( $slug, $name, $templates ) {
        echo '<!-- ' . "\n";
        echo 'Template Debugger ' . "\n";
        echo '================= ' . "\n";
        echo 'Method: ' . sprintf("get_template_part('%s','%s')", $slug, $name) . "\n";
        echo 'Slug: ' . $slug . "\n";
        echo 'Name: ' . $name . "\n";

        foreach(debug_backtrace() as $arr ) {
            if( !empty($arr['function']) && $arr['function'] == 'showTemplatePart' ) {continue;}
            if( empty($arr['args'][0]) ) {continue;}
            if( $arr['args'][0] == $slug && ( ($name && $arr['args'][1] == $name) || !$name && empty($arr['args'][1]) ) ) {
                //var_dump($arr);
                $file = str_replace( $_SERVER['DOCUMENT_ROOT'], '', $arr['file'] );
                echo 'Called From: ' . sprintf('%s:%s', $file, $arr['line']) . "\n";
            }
        }

        echo 'Template used: ' . $this->_locateTemplate($templates) . "\n\n";
        echo 'Template options: ' . "\n";
        echo '------------------ ' . "\n";
        echo implode("\n",$templates) . "\n";
        echo '------------------ ' . "\n";
        echo ' --> ' . "\n";
    }

    private function generateBlockMeta($block) {

        if( !$block['blockName'] ) {
            return '';
        }
        ob_start();
        ?><!--
Template Debugger
=================
Block: <?=$block['blockName']?>

Variables: <?=json_encode($block['attrs'])?>

--><?php
        return ob_get_clean();
    }

    public function renderBlock( $block_content, $block ){

        return $this->generateBlockMeta($block) . $block_content;
    }
}