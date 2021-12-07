<?php
    add_action( 'wp_ajax_piotnetforms_form_abandonment', 'piotnetforms_form_abandonment' );
	add_action( 'wp_ajax_nopriv_piotnetforms_form_abandonment', 'piotnetforms_form_abandonment' );

	function piotnetforms_form_abandonment() {

        $fields = json_decode(stripslashes($_POST['fields']), true);
        $post_type = 'piotnetforms-aban';
        $form_type = $_POST['form_type'];
        $form_id = $fields['form_id'];
        $user_id = $fields['userId'];
        $function = $_POST['function'];
        $webhook = $_POST['webhook'];

        $args = array(
            'post_type' => $post_type,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'     => 'userId',
                    'value'   => $user_id,
                    'compare' => '=',
                ),
                array(
                    'key'     => 'form_id',
                    'value'   => $form_id,
                    'compare' => '=',
                ), 
            ),
        );

        $query = new WP_Query( $args );

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $form_database_post_id = get_the_ID();
            endwhile;
        else :
            $my_post = array(
                'post_title'    => wp_strip_all_tags( 'piotnetforms Elementor Form Database ' . $form_id ),
                'post_status'   => 'publish',
                'post_type'     => $post_type,
            );

            $form_database_post_id = wp_insert_post( $my_post );
        endif;

        if (!empty($form_database_post_id)) {

            $my_post_update = array(
                'ID'           => $form_database_post_id,
                'post_title'   => '#' . $form_database_post_id,
            );
            wp_update_post( $my_post_update );

            update_post_meta( $form_database_post_id, 'status', 'Abandonment' );
            update_post_meta( $form_database_post_id, 'form_type', $form_type );

            foreach ($fields as $key => $value) {
                if (is_array($value)) {
                    update_post_meta( $form_database_post_id, $key, implode(', ', $value ));
                } else {
                    update_post_meta( $form_database_post_id, $key, rtrim( str_replace('\n', '', $value) ));
                }
            }

            if ($function == 'success') {
                update_post_meta( $form_database_post_id, 'status', 'Success');
                do_action('piotnetforms/form_builder/new_record_abandonment_success',$fields);
            }else{
                do_action('piotnetforms/form_builder/new_record_abandonment',$fields);
            }

        }
        //Webhook
        if($webhook != 'false'){
            $body['fields'] = $fields;
            $body['form_id'] = $form_id;
            $args = [
                'body' => $body
            ];
            wp_remote_post($webhook, $args);
        }
        do_action('piotnetforms_action_form_abandonment', $fields);

        wp_die(); 
    }