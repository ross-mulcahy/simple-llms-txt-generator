<?php
/**
 * Plugin Name: Simple LLMS.txt Generator
 * Description: Dynamically generates llms.txt file and adds it to XML sitemap
 * Version: 1.0.1
 * Author: Ross Mulcahy
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-llms-txt-generator
 */

if (!defined('ABSPATH')) {
    exit;
}

class LLMS_TXT_Generator {
    
    private $endpoint = 'llms.txt';
    private $option_group = 'llms_txt_settings';
    private $option_name = 'llms_txt_options';
    
    public function __construct() {
        // Register the rewrite rule for llms.txt
        add_action('init', [$this, 'add_rewrite_rule']);
        
        // Handle the llms.txt request
        add_action('template_redirect', [$this, 'serve_llms_txt']);
        
        // Add to sitemap
        add_filter('wp_sitemaps_add_provider', [$this, 'add_llms_sitemap_provider'], 10, 2);
        
        // Admin menu and settings
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        
        // Flush rewrite rules on activation
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }
    
    /**
     * Add rewrite rule for llms.txt
     */
    public function add_rewrite_rule() {
        add_rewrite_rule(
            '^llms\.txt$',
            'index.php?llms_txt=1',
            'top'
        );
        add_rewrite_tag('%llms_txt%', '([^&]+)');
    }
    
    /**
     * Serve the llms.txt content
     */
    public function serve_llms_txt() {
        if (get_query_var('llms_txt')) {
            header('Content-Type: text/plain; charset=utf-8');
            echo $this->generate_llms_txt();
            exit;
        }
    }
    
    /**
     * Get plugin options
     */
    private function get_options() {
        $defaults = [
            'site_description' => get_bloginfo('description'),
            'contact_email' => get_option('admin_email'),
            'contact_url' => '',
            'include_pages' => true,
            'include_posts' => true,
            'max_pages' => 10,
            'max_posts' => 10,
        ];
        
        $options = get_option($this->option_name, []);
        return wp_parse_args($options, $defaults);
    }
    
    /**
     * Generate the llms.txt content
     */
    private function generate_llms_txt() {
        $site_url = trailingslashit(get_site_url());
        $site_name = get_bloginfo('name');
        $options = $this->get_options();
        
        $content = "# {$site_name}\n\n";
        
        // Site description
        if (!empty($options['site_description'])) {
            $content .= "> {$options['site_description']}\n\n";
        }
        
        // Contact section
        if (!empty($options['contact_email']) || !empty($options['contact_url'])) {
            $content .= "## Contact\n\n";
            
            if (!empty($options['contact_email'])) {
                $content .= "- Email: {$options['contact_email']}\n";
            }
            
            if (!empty($options['contact_url'])) {
                $content .= "- Contact Form: {$options['contact_url']}\n";
            }
            
            $content .= "\n";
        }
        
        // Add main site URL
        $content .= "## Site\n\n";
        $content .= "- {$site_url}\n\n";
        
        // Add sitemap
        $content .= "## Sitemap\n\n";
        $content .= "- {$site_url}wp-sitemap.xml\n\n";
        
        // Optional: Add important pages
        if ($options['include_pages']) {
            $pages = get_pages([
                'post_status' => 'publish',
                'number' => absint($options['max_pages']),
                'sort_column' => 'menu_order'
            ]);
            
            if ($pages) {
                $content .= "## Important Pages\n\n";
                foreach ($pages as $page) {
                    $content .= "- " . get_permalink($page->ID) . " # {$page->post_title}\n";
                }
                $content .= "\n";
            }
        }
        
        // Optional: Add recent blog posts
        if ($options['include_posts']) {
            $posts = get_posts([
                'post_status' => 'publish',
                'numberposts' => absint($options['max_posts']),
                'orderby' => 'date',
                'order' => 'DESC'
            ]);
            
            if ($posts) {
                $content .= "## Recent Posts\n\n";
                foreach ($posts as $post) {
                    $content .= "- " . get_permalink($post->ID) . " # {$post->post_title}\n";
                }
                $content .= "\n";
            }
        }
        
        // Allow filtering of the content
        return apply_filters('llms_txt_content', $content);
    }
    
    /**
     * Add llms.txt to sitemap
     */
    public function add_llms_sitemap_provider($provider, $name) {
        if ('users' === $name) {
            require_once __DIR__ . '/class-llms-sitemap-provider.php';
            return new LLMS_Sitemap_Provider();
        }
        return $provider;
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('LLMS.txt Settings', 'simple-llms-txt-generator'),
            __('LLMS.txt', 'simple-llms-txt-generator'),
            'manage_options',
            'llms-txt-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            $this->option_group,
            $this->option_name,
            [$this, 'sanitize_settings']
        );
        
        // General Section
        add_settings_section(
            'llms_txt_general',
            __('General Settings', 'simple-llms-txt-generator'),
            [$this, 'render_general_section'],
            'llms-txt-settings'
        );
        
        // Site Description
        add_settings_field(
            'site_description',
            __('Site Description', 'simple-llms-txt-generator'),
            [$this, 'render_textarea_field'],
            'llms-txt-settings',
            'llms_txt_general',
            [
                'label_for' => 'site_description',
                'description' => __('Custom description for LLMs. Leave empty to use site tagline.', 'simple-llms-txt-generator'),
            ]
        );
        
        // Contact Section
        add_settings_section(
            'llms_txt_contact',
            __('Contact Information', 'simple-llms-txt-generator'),
            [$this, 'render_contact_section'],
            'llms-txt-settings'
        );
        
        // Contact Email
        add_settings_field(
            'contact_email',
            __('Contact Email', 'simple-llms-txt-generator'),
            [$this, 'render_text_field'],
            'llms-txt-settings',
            'llms_txt_contact',
            [
                'label_for' => 'contact_email',
                'type' => 'email',
                'description' => __('Email address for LLM inquiries.', 'simple-llms-txt-generator'),
            ]
        );
        
        // Contact URL
        add_settings_field(
            'contact_url',
            __('Contact Form URL', 'simple-llms-txt-generator'),
            [$this, 'render_text_field'],
            'llms-txt-settings',
            'llms_txt_contact',
            [
                'label_for' => 'contact_url',
                'type' => 'url',
                'description' => __('URL to your contact form or contact page.', 'simple-llms-txt-generator'),
            ]
        );
        
        // Content Section
        add_settings_section(
            'llms_txt_content',
            __('Content Settings', 'simple-llms-txt-generator'),
            [$this, 'render_content_section'],
            'llms-txt-settings'
        );
        
        // Include Pages
        add_settings_field(
            'include_pages',
            __('Include Pages', 'simple-llms-txt-generator'),
            [$this, 'render_checkbox_field'],
            'llms-txt-settings',
            'llms_txt_content',
            [
                'label_for' => 'include_pages',
                'description' => __('Include published pages in llms.txt', 'simple-llms-txt-generator'),
            ]
        );
        
        // Max Pages
        add_settings_field(
            'max_pages',
            __('Max Pages', 'simple-llms-txt-generator'),
            [$this, 'render_number_field'],
            'llms-txt-settings',
            'llms_txt_content',
            [
                'label_for' => 'max_pages',
                'description' => __('Maximum number of pages to include.', 'simple-llms-txt-generator'),
            ]
        );
        
        // Include Posts
        add_settings_field(
            'include_posts',
            __('Include Posts', 'simple-llms-txt-generator'),
            [$this, 'render_checkbox_field'],
            'llms-txt-settings',
            'llms_txt_content',
            [
                'label_for' => 'include_posts',
                'description' => __('Include recent blog posts in llms.txt', 'simple-llms-txt-generator'),
            ]
        );
        
        // Max Posts
        add_settings_field(
            'max_posts',
            __('Max Posts', 'simple-llms-txt-generator'),
            [$this, 'render_number_field'],
            'llms-txt-settings',
            'llms_txt_content',
            [
                'label_for' => 'max_posts',
                'description' => __('Maximum number of posts to include.', 'simple-llms-txt-generator'),
            ]
        );
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = [];
        
        if (isset($input['site_description'])) {
            $sanitized['site_description'] = sanitize_textarea_field($input['site_description']);
        }
        
        if (isset($input['contact_email'])) {
            $sanitized['contact_email'] = sanitize_email($input['contact_email']);
        }
        
        if (isset($input['contact_url'])) {
            $sanitized['contact_url'] = esc_url_raw($input['contact_url']);
        }
        
        $sanitized['include_pages'] = !empty($input['include_pages']);
        $sanitized['include_posts'] = !empty($input['include_posts']);
        $sanitized['max_pages'] = absint($input['max_pages'] ?? 10);
        $sanitized['max_posts'] = absint($input['max_posts'] ?? 10);
        
        return $sanitized;
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $llms_url = trailingslashit(get_site_url()) . 'llms.txt';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="notice notice-info">
                <p>
                    <?php _e('Configure your llms.txt file settings below.', 'simple-llms-txt-generator'); ?>
                    <br>
                    <?php printf(
                        __('View your llms.txt file at: <a href="%s" target="_blank">%s</a>', 'simple-llms-txt-generator'),
                        esc_url($llms_url),
                        esc_html($llms_url)
                    ); ?>
                </p>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields($this->option_group);
                do_settings_sections('llms-txt-settings');
                submit_button(__('Save Settings', 'simple-llms-txt-generator'));
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render section descriptions
     */
    public function render_general_section() {
        echo '<p>' . __('Configure general llms.txt settings.', 'simple-llms-txt-generator') . '</p>';
    }
    
    public function render_contact_section() {
        echo '<p>' . __('Provide contact information for LLMs to reach you.', 'simple-llms-txt-generator') . '</p>';
    }
    
    public function render_content_section() {
        echo '<p>' . __('Control which content appears in your llms.txt file.', 'simple-llms-txt-generator') . '</p>';
    }
    
    /**
     * Render text field
     */
    public function render_text_field($args) {
        $options = $this->get_options();
        $value = $options[$args['label_for']] ?? '';
        $type = $args['type'] ?? 'text';
        ?>
        <input 
            type="<?php echo esc_attr($type); ?>"
            id="<?php echo esc_attr($args['label_for']); ?>"
            name="<?php echo esc_attr($this->option_name . '[' . $args['label_for'] . ']'); ?>"
            value="<?php echo esc_attr($value); ?>"
            class="regular-text"
        >
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Render textarea field
     */
    public function render_textarea_field($args) {
        $options = $this->get_options();
        $value = $options[$args['label_for']] ?? '';
        ?>
        <textarea 
            id="<?php echo esc_attr($args['label_for']); ?>"
            name="<?php echo esc_attr($this->option_name . '[' . $args['label_for'] . ']'); ?>"
            rows="4"
            class="large-text"
        ><?php echo esc_textarea($value); ?></textarea>
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args) {
        $options = $this->get_options();
        $checked = !empty($options[$args['label_for']]);
        ?>
        <label>
            <input 
                type="checkbox"
                id="<?php echo esc_attr($args['label_for']); ?>"
                name="<?php echo esc_attr($this->option_name . '[' . $args['label_for'] . ']'); ?>"
                value="1"
                <?php checked($checked); ?>
            >
            <?php if (!empty($args['description'])): ?>
                <span class="description"><?php echo esc_html($args['description']); ?></span>
            <?php endif; ?>
        </label>
        <?php
    }
    
    /**
     * Render number field
     */
    public function render_number_field($args) {
        $options = $this->get_options();
        $value = $options[$args['label_for']] ?? 10;
        ?>
        <input 
            type="number"
            id="<?php echo esc_attr($args['label_for']); ?>"
            name="<?php echo esc_attr($this->option_name . '[' . $args['label_for'] . ']'); ?>"
            value="<?php echo esc_attr($value); ?>"
            min="1"
            max="100"
            class="small-text"
        >
        <?php if (!empty($args['description'])): ?>
            <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Activation hook
     */
    public function activate() {
        $this->add_rewrite_rule();
        flush_rewrite_rules();
    }
    
    /**
     * Deactivation hook
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new LLMS_TXT_Generator();