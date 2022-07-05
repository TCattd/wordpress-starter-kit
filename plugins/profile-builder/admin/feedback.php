<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $gf_feedback_custom_poll_id;
global $gf_feedback_custom_poll_action_link;
$gf_feedback_custom_poll_id = '14';
$gf_feedback_custom_poll_action_link = 'https://www.cozmoslabs.com/profile-builder-poll/';

/* add the modal on the plugin screen and embed a polldaddy form in it */
add_action( 'admin_footer', 'wppb_add_feedback_modal' );
function wppb_add_feedback_modal(){
    global $current_screen;
    global $gf_feedback_custom_poll_id;
    global $gf_feedback_custom_poll_action_link;

    $gf_id = $gf_feedback_custom_poll_id;
    $action_link = $gf_feedback_custom_poll_action_link;

    if( $current_screen->base == 'plugins' ){
        ?>
        <div id="custom-feedback-modal">

            <div class="custom-feedback-poll-content">

                <h1 style="padding-left:10px;padding-top:15px;"><?php esc_html_e('Quick Feedback', 'profile-builder'); ?></h1>
                <p><?php esc_html_e( 'Because we care about our clients, please leave us feedback on why you are no longer using our plugin.', 'profile-builder'); ?></p>
                <form method="post" enctype="multipart/form-data" id="gform_<?php echo esc_attr( $gf_id ); ?>" class="gpoll_enabled gpoll" action="<?php echo esc_attr( $action_link ); ?>">
                    <div>
                        <ul>
                            <li>
                                <input name="input_1" type="radio" value="gpoll1d7fc0436" id="choice_<?php echo esc_attr( $gf_id ); ?>_1_0">
                                <label for="choice_<?php echo esc_attr( $gf_id ); ?>_1_0" id="label_<?php echo esc_attr( $gf_id ); ?>_1_0"><?php esc_html_e( 'Lacking certain features', 'profile-builder'); ?></label>
                                <input class="poll_custom_hidden_detail" name="input_2" id="input_<?php echo esc_attr( $gf_id ); ?>_2" type="text" value="" placeholder="<?php esc_html_e( 'What feature would you like to see?', 'profile-builder' ); ?>">
                            </li>
                            <li>
                                <input name="input_1" type="radio" value="gpoll19c993bd1" id="choice_<?php echo esc_attr( $gf_id ); ?>_1_1">
                                <label for="choice_<?php echo esc_attr( $gf_id ); ?>_1_1" id="label_<?php echo esc_attr( $gf_id ); ?>_1_1"><?php esc_html_e( 'Hard to use', 'profile-builder'); ?></label>
                                <input class="poll_custom_hidden_detail" name="input_3" id="input_<?php echo esc_attr( $gf_id ); ?>_3" type="text" value="" placeholder="<?php esc_html_e( 'How can we improve our user experience ?', 'profile-builder' ); ?>">
                            </li>
                            <li>
                                <input name="input_1" type="radio" value="gpoll147502d8a" id="choice_<?php echo esc_attr( $gf_id ); ?>_1_2">
                                <label for="choice_<?php echo esc_attr( $gf_id ); ?>_1_2" id="label_<?php echo esc_attr( $gf_id ); ?>_1_2"><?php esc_html_e( 'Unsatisfactory support', 'profile-builder' ); ?></label>
                                <span class="poll_custom_hidden_detail"><?php esc_html_e( "Give us another try! Open a support ticket <a href='https://www.cozmoslabs.com/support/open-ticket/' target='_blank'>here</a>", 'profile-builder' ) ?></span>
                            </li>
                            <li>
                                <input name="input_1" type="radio" value="gpoll1353bb209" id="choice_<?php echo esc_attr( $gf_id ); ?>_1_4">
                                <label for="choice_<?php echo esc_attr( $gf_id ); ?>_1_4" id="label_<?php echo esc_attr( $gf_id ); ?>_1_4"><?php esc_html_e( 'Other', 'profile-builder'); ?></label>
                                <input class="poll_custom_hidden_detail" name="input_4" id="input_<?php echo esc_attr( $gf_id ); ?>_4" type="text" value="" placeholder="<?php esc_html_e( 'Please tell us more', 'profile-builder' ); ?>">
                            </li>
                            <li>
                                <input name="input_1" type="radio" value="gpoll18cbe0189" id="choice_<?php echo esc_attr( $gf_id ); ?>_1_3">
                                <label for="choice_<?php echo esc_attr( $gf_id ); ?>_1_3" id="label_<?php echo esc_attr( $gf_id ); ?>_1_3"><?php esc_html_e( 'Poor Documentation', 'profile-builder'); ?></label>
                                <input class="poll_custom_hidden_detail" name="input_5" id="input_<?php echo esc_attr( $gf_id ); ?>_5" type="text" value="" placeholder="<?php esc_html_e( "Tell us what you couldn't find", 'profile-builder' ); ?>">
                            </li>
                        </ul>
                    </div>
                    <div class="gform_footer">
                        <input type="submit" id="gform_submit_button_<?php echo esc_attr( $gf_id ); ?>" class="button button-primary" value="<?php esc_html_e( 'Submit & Deactivate', 'profile-builder' ); ?>" disabled="disabled">
                        <input type="hidden" class="gform_hidden" name="is_submit_<?php echo esc_attr( $gf_id ); ?>" value="1">
                        <input type="hidden" class="gform_hidden" name="gform_submit" value="<?php echo esc_attr( $gf_id ); ?>">
                        <a href="#" class="button secondary custom-feedback-skip"><?php esc_html_e('Skip and Deactivate', 'profile-builder'); ?></a>
                    </div>
                </form>


            </div>

        </div>
        <?php
    }
}

/* add the scripts for the modal on the plugin screen */
add_action( 'admin_footer', 'wppb_add_feedback_script' );
function wppb_add_feedback_script(){
    global $current_screen;
    global $gf_feedback_custom_poll_id;
    global $gf_feedback_custom_poll_action_link;
    $action_link = $gf_feedback_custom_poll_action_link;

    $gf_id = $gf_feedback_custom_poll_id;

    if( $current_screen->base == 'plugins' ) {
        ?>
        <script>
            jQuery(function () {
                pluginSlug = 'profile-builder';// define the plugin slug here

                if (jQuery('tr[data-slug="' + pluginSlug + '"] .deactivate a').length != 0) {

                    /* the conditional fields */
                    jQuery("#gform_<?php echo esc_attr( $gf_id ); ?> input[type='radio']").on('click', function(){
                        jQuery("#gform_<?php echo esc_attr( $gf_id ); ?> input[type='submit']").prop("disabled", false);
                        jQuery( '.poll_custom_hidden_detail' ).hide();
                        jQuery( '.poll_custom_hidden_detail', jQuery(this).parent() ).show();
                    });

                    /* this is the deactivation link */
                    deactivationLink = jQuery('tr[data-slug="' + pluginSlug + '"] .deactivate a').attr('href');

                    /* show the modal when you click deactivate */
                    jQuery('tr[data-slug="' + pluginSlug + '"] .deactivate a').on('click', function (e){
                        e . preventDefault();
                        e . stopPropagation();
                        tb_show("Profile Builder Quick Feedback", "#TB_inline?width=740&height=500&inlineId=custom-feedback-modal");
                        jQuery('#TB_ajaxContent').closest('#TB_window').css({ height : "auto", top: "50%", marginTop: "-300px" });
                    });

                    /* on submit */
                    jQuery("#gform_<?php echo esc_attr( $gf_id ); ?>").on('submit', function(e) {
                        var url = "<?php echo esc_url( $action_link ); ?>"; // the script where you handle the form input.

                        jQuery.ajax({
                            type: "POST",
                            url: url,
                            data: jQuery("#gform_<?php echo esc_attr( $gf_id ); ?>").serialize(), // serializes the form's elements.
                            complete: function()
                            {
                                tb_remove();
                                window.location.href = deactivationLink;
                            }
                        });

                        e.preventDefault(); // avoid to execute the actual submit of the form.
                    });

                    /* on skip */
                    jQuery('.custom-feedback-skip').on('click', function(e){
                        e.preventDefault();
                        self.parent.tb_remove();
                        window.location.href = deactivationLink;
                    });

                }
            });
        </script>
        <?php
    }
}

/* add styling for the modal */
add_action( 'admin_footer', 'wppb_add_feedback_style' );
function wppb_add_feedback_style(){
    global $current_screen;
    if( $current_screen->base == 'plugins' ) {
        ?>
        <style type="text/css">
            #TB_window .pds-box{
                border:0 !important;
            }
            #TB_window .pds-links{
                display:none;
            }
            #TB_window .pds-question-top{
                font-size:13px;
                font-weight:normal;
            }
            #TB_window .pds-answer{
                border:0;
            }
            #TB_window .pds-vote-button span{
                display:none;
            }
            #TB_window .pds-vote-button:after{
                content:"<?php esc_html_e('Submit and Deactivate', 'profile-builder')?>";
            }
            #TB_window .pds-vote-button{
                padding: 6px 14px;
                line-height: normal;
                font-size: 14px;
                font-weight: normal;
                vertical-align: middle;
                height: auto;
                margin-bottom: 4px;
                background: #0085ba;
                border-color: #0073aa #006799 #006799;
                box-shadow: 0 1px 0 #006799;
                color: #fff;
                text-decoration: none;
                text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
                cursor: pointer;
                border-width: 1px;
                border-style: solid;
                -webkit-appearance: none;
                border-radius: 3px;
            }

            #custom-feedback-modal{
                display: none;
            }

            .custom-feedback-poll-content{
                font-size:15px;
                padding:0 10px;
            }

            .custom-feedback-poll-content h1{
                padding-left: 0 !important;
                margin-left:0;
            }

            .custom-feedback-poll-content form ul li > *{
                vertical-align: middle;
            }

            .custom-feedback-poll-content label{
                line-height:27px;
            }

            .custom-feedback-poll-content span{
                font-size: 13px;
                line-height:27px;
            }

            .custom-feedback-poll-content input[type='radio']{
                margin-top:2px;
            }

            .custom-feedback-poll-content p{
                margin-bottom:30px;
            }

            .custom-feedback-poll-content .gform_footer{
                margin-top:25px;
            }

            .custom-feedback-poll-content .button-primary{
                font-size:15px;
            }

            .custom-feedback-skip{
                float: right;
            }

            .poll_custom_hidden_detail{
                display:none;
                width:300px;
                margin-left:15px;
            }

        </style>
        <?php
    }
}