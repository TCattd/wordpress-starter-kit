<?php

namespace FuerteWpDep\Carbon_Fields\Datastore;

use FuerteWpDep\Carbon_Fields\Field\Field;
/**
 * Theme options datastore class.
 */
class Network_Datastore extends Meta_Datastore
{
    /**
     * {@inheritDoc}
     */
    public function get_meta_type()
    {
        return 'site';
    }
    /**
     * {@inheritDoc}
     */
    public function get_table_name()
    {
        global $wpdb;
        return $wpdb->sitemeta;
    }
    /**
     * {@inheritDoc}
     */
    public function get_table_field_name()
    {
        return 'site_id';
    }
}
