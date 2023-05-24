<?php namespace CheckEmail\Core;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

class Check_Email_From_Handler {
    
    public $options;
    /**
    * Constructor
    */
    public function __construct() {

        $this->options = get_option('check-email-log-core', false);
        
        add_filter( 'wp_mail', array( $this, 'override_values'), 15 );
        add_filter( 'wp_mail_from', array($this, 'set_wp_mail_from' ), 99 );
        add_filter( 'wp_mail_from_name', array($this, 'set_wp_mail_from_name' ), 99 );
    }


    /**
    * Overrides the current wp_mail_from with the one from settings.
    *
    * @param String $email - Wordpress current mail from address.
    * @return String $email - "From" email address set in settings if it exists, else return the $email unchanged
    *
    * @since 1.0.5
    */
    public function set_wp_mail_from( $email ){
        if( $this->override_enabled() && isset( $this->options['email_from_email'] ) && '' != $this->options['email_from_email']){
            return $this->options['email_from_email'];
        }
    
        return $email;
    }

   /**
    * Overrides the current wp_mail_from_name with the one from settings.
    *
    * @param String $name - Wordpress current mail from name.
    * @return String $name - "From" name set in settings if it exists, else return the $name unchanged
    *
    * @since 1.0.5
    */
    public function set_wp_mail_from_name( $name ){
        if( $this->override_enabled() && isset( $this->options['email_from_name'] ) && '' != $this->options['email_from_name']){
            return $this->options['email_from_name'];
        }

        return $name;
    }
   /**
    * Check if the setting to override the default from email and from name is active.
    *
    * @since 1.0.5
    */
    public function override_enabled(){

        if( $this->options && isset( $this->options['override_emails_from'] ) && $this->options['override_emails_from'] ){
            return true;
        }

        return false;
    }

   /**
    * Replaces the current wp_mail $headers with the values from settings.
    *
    * @param Array $headers - Wordpress current mail headers
    * @return Array $headers - New headers from settings
    *
    * @since 1.0.5
    */    
    public function override_values( $headers ) {
        if( $this->override_enabled() && isset( $this->options['email_from_email'] ) && '' != $this->options['email_from_email']){
           
            $headers['headers'] = "MIME-Version: 1.0\r\n";

            $email = $this->options['email_from_email'];
 
            if( $this->override_enabled() && isset( $this->options['email_from_name'] ) && '' != $this->options['email_from_name'] ){
                
                $headers['headers'] .= "From: " . $this->options['email_from_name'] . " <". $email .">\r\n" ;
            }else{
               
                $headers['headers'] .= "From: <". $email .">\r\n" ;
            }

            $headers['headers'] .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
        }


        return $headers;
    }
}
