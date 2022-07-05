<?php
/**
 * Function that returns the lists set up in the MailPoet plugin
 *
 * @since v.1.0.0
 *
 * @return array or false
 *
 */
function wppb_in_mpi_get_lists() {

    //this will return an array of results with the name and list_id of each mailing list
    if ( ! wppb_in_mailpoet_installed() ){
        return ;
    }
    switch ( WPPB_IN_MP_VERSION ) {
	    case 2:
		    $model_list     = WYSIJA::get( 'list', 'model' );
		    $mailpoet_lists = $model_list->get( array( 'name', 'list_id' ), array( 'is_enabled' => 1 ) );
		    foreach($mailpoet_lists as $key => $list ){
			    $mailpoet_lists[$key]['id'] = $mailpoet_lists[$key]['list_id'];
		    }
		    break;
	    case 3:
		    $mailpoet_lists = \MailPoet\API\API::MP( 'v1' )->getLists();
		    break;
    }
    if ( isset( $mailpoet_lists ) && ! empty( $mailpoet_lists ) ) {
	    return $mailpoet_lists;
    }else {
	    return false;
    }
}

/**
 * Function that subscribes a user to a MailPoet list
 *
 * @since v.1.0.0
 *
 * @param string $list_id The MailPoet list id the user needs to be subscribed to
 * @param int $user_id The ID of the user to be subscribed.
 *
 */
function wppb_in_mpi_add_subscriber($list_id, $user_id){
    if ( ! wppb_in_mailpoet_installed() ){
        return ;
    }

    $user_data = get_userdata($user_id );
    if ( ! $user_data ){
        return ;
    }

    // Check if the MailPoet List ID exists
    $mailpoet_lists = wppb_in_mpi_get_lists();

    if ( isset ( $mailpoet_lists ) ) {
        foreach($mailpoet_lists as $list){
            if ( $list['id'] == $list_id ) {
	            switch ( WPPB_IN_MP_VERSION ) {
		            case 2:
			            // Prepare arguments
			            $user_info = array(
				            'email' => $user_data->user_email,
				            'firstname' => $user_data->first_name,
				            'lastname' => $user_data->last_name,
			            );

			            $data_subscriber = array(
				            'user'      => $user_info,
				            'user_list' => array( 'list_ids' => array( $list_id ) )
			            );

			            $helper_user = WYSIJA::get( 'user', 'helper' );
			            $helper_user->addSubscriber( $data_subscriber );
			            //if double optin is on it will send a confirmation email
			            //to the subscriber

			            //if double optin is off and you have an active automatic
			            //newsletter then it will send the automatic newsletter to the subscriber
			            break;
		            case 3:
			            try {
				            $subscriber = \MailPoet\API\API::MP('v1')->subscribeToList($user_data->user_email, $list_id);
				            $sub = MailPoet\Models\Subscriber::findOne($subscriber['id']);
				            if ( $sub->status === MailPoet\Models\Subscriber::STATUS_UNSUBSCRIBED ){
					            $sub->set(['status' => 'subscribed']);
					            $sub->save();
				            }
			            } catch ( Exception $exception ) {
				            //$ex = $exception->getMessage();
				            //wp_die($ex);
			            }
			            break;
	            }
            }
        }
    }
}


/**
 * Function that unsubscribes a user from a MailPoet list
 *
 * @since v.1.0.0
 *
 * @param string $list_id The MailPoet list id the user needs to be unsubscribed from
 * @param int $user_id The ID of the user to be unsubscribed
 *
 */
function wppb_in_mpi_remove_list_from_user($list_id, $user_id){
    if ( ! wppb_in_mailpoet_installed() ){
        return ;
    }

    $wp_user = get_userdata($user_id);
	switch ( WPPB_IN_MP_VERSION ) {
		case 2:
			$helper_user = WYSIJA::get( 'user', 'helper' );
			$model_user  = WYSIJA::get( 'user', 'model' );

			// MailPoet uses a different User ID than the standard WordPress user ID
			$mailpoet_user = $model_user->getOne( false, array( 'email' => $wp_user->user_email ) );
			$helper_user->removeFromLists( array( $list_id ), array( $mailpoet_user['user_id'] ) );
			break;
		case 3:
			try {
				$subscriber = \MailPoet\API\API::MP( 'v1' )->unsubscribeFromList( $wp_user->user_email, $list_id );
                $sub = MailPoet\Models\Subscriber::findOne($subscriber['id']);
                if ( $sub->status === MailPoet\Models\Subscriber::STATUS_SUBSCRIBED ) {
                    $sub->set(['status' => 'unsubscribed']);
                    $sub->save();
                }
			} catch ( Exception $exception ) {
				// $exception->getMessage();
			}
			break;
	}
}

/**
 * Function that checks if a user is subscribed to a list
 * @since v.1.0.0
 *
 * @param string $list_id The MailPoet list id
 * @param int $user_id The ID of the user to be checked
 *
 * @return bool
 */
function wppb_in_mpi_check_user_subscription($list_id, $user_id){
    if ( ! wppb_in_mailpoet_installed() ){
        return false;
    }

    $wp_user = get_userdata($user_id);
	switch ( WPPB_IN_MP_VERSION ) {
		case 2:
			$model_user  = WYSIJA::get( 'user', 'model' );
			$helper_user = WYSIJA::get( 'user', 'helper' );

			// MailPoet uses a different User ID than the standard WordPress user ID
			$mailpoet_user   = $model_user->getOne( false, array( 'email' => $wp_user->user_email ) );
			$list_subscribed = $helper_user->getUserLists( $mailpoet_user['user_id'], array( $list_id ) );

			if ( empty( $list_subscribed ) || $list_subscribed[0]['unsub_date'] != 0 ) {
				return false;
			} else {
				return true;
			}
			break;
		case 3:
			try {
				$subscriber = \MailPoet\API\API::MP( 'v1' )->getSubscriber( $wp_user->user_email ); // $identifier can be either a subscriber ID or e-mail
				if ( $subscriber['subscriptions'] ) {
					foreach ( $subscriber['subscriptions'] as $subscription ) {
						if ( $subscription['segment_id'] == $list_id && $subscription['status'] == 'subscribed' ){
							return true;
						}
					}
				}
				return false;
			} catch ( Exception $exception ) {
				// $exception->getMessage();
			}
			break;
	}
}

/**
 * Function that checks if MailPoet plugin is installed
 *
 * @since v.1.0.0
 *
 * @return bool
 */
function wppb_in_mailpoet_installed(){
	if ( defined ( 'WPPB_IN_MP_VERSION' ) ) {
		return true;
	}

	if ( defined( 'MAILPOET_VERSION' ) && version_compare( MAILPOET_VERSION, '3.0.0', '>=' ) ) {
		define( 'WPPB_IN_MP_VERSION', 3 );
		return true;
	} else if ( class_exists( 'WYSIJA' ) ) {
		define( 'WPPB_IN_MP_VERSION', 2 );
		return true;
	}

	return false;
}

