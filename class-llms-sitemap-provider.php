<?php
/**
 * LLMS.txt Sitemap Provider
 */

if (!defined('ABSPATH')) {
    exit;
}

class LLMS_Sitemap_Provider extends WP_Sitemaps_Provider {
    
    public function __construct() {
        $this->name = 'llms';
        $this->object_type = 'llms';
    }
    
    public function get_url_list($page_num, $object_subtype = '') {
        return [
            [
                'loc' => trailingslashit(get_site_url()) . 'llms.txt',
                'lastmod' => current_time('c'),
                'priority' => 0.8,
            ],
        ];
    }
    
    public function get_max_num_pages($object_subtype = '') {
        return 1;
    }
}