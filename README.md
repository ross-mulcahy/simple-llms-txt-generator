# LLMS.txt Generator for WordPress

A WordPress plugin that dynamically generates an `llms.txt` file for Large Language Models (LLMs) and automatically includes it in your XML sitemap. Designed specifically to work with WordPress VIP and other managed WordPress environments that restrict static file creation.

## Description

This plugin creates a dynamically-generated `llms.txt` file at `yoursite.com/llms.txt` that provides LLMs with structured information about your website, including:

- Site name and description
- Contact information
- Important pages
- Recent blog posts
- Sitemap reference

The file is generated on-demand without writing to the filesystem, making it perfect for WordPress VIP and other enterprise hosting environments.

## Features

- ✅ **Dynamic Generation** - No static files created, fully VIP-compatible
- ✅ **Sitemap Integration** - Automatically adds llms.txt to your XML sitemap
- ✅ **Admin Settings Page** - Easy configuration through WordPress admin
- ✅ **Customizable Content** - Control what appears in your llms.txt file
- ✅ **Contact Information** - Add email and contact form URLs
- ✅ **Custom Site Description** - Override the default tagline with LLM-specific content
- ✅ **Content Control** - Toggle inclusion of pages and posts
- ✅ **Developer-Friendly** - Includes filters for advanced customization

## Installation

### Via WordPress Admin (Recommended for most users)

1. Download the plugin zip file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Choose the zip file and click "Install Now"
4. Activate the plugin
5. Go to Settings → LLMS.txt to configure

### Manual Installation

1. Upload the `llms-txt-generator` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings → LLMS.txt to configure your settings

### File Structure
```
wp-content/plugins/llms-txt-generator/
├── llms-txt-generator.php
├── class-llms-sitemap-provider.php
└── README.md
```

## Configuration

After activation, navigate to **Settings → LLMS.txt** in your WordPress admin to configure:

### General Settings

- **Site Description**: Custom description for LLMs (defaults to site tagline if left empty)

### Contact Information

- **Contact Email**: Email address for LLM-related inquiries
- **Contact Form URL**: Link to your contact form or contact page

### Content Settings

- **Include Pages**: Toggle whether to include published pages
- **Max Pages**: Maximum number of pages to include (1-100)
- **Include Posts**: Toggle whether to include recent blog posts
- **Max Posts**: Maximum number of posts to include (1-100)

## Usage

Once configured, your llms.txt file will be available at:
```
https://yoursite.com/llms.txt
```

The file will also automatically appear in your XML sitemap at:
```
https://yoursite.com/wp-sitemap.xml
```

## Example Output
```
# Your Site Name

> Your custom site description for LLMs

## Contact

- Email: contact@yoursite.com
- Contact Form: https://yoursite.com/contact

## Site

- https://yoursite.com/

## Sitemap

- https://yoursite.com/wp-sitemap.xml

## Important Pages

- https://yoursite.com/about/ # About Us
- https://yoursite.com/services/ # Our Services
- https://yoursite.com/pricing/ # Pricing

## Recent Posts

- https://yoursite.com/2024/12/latest-post/ # Latest Blog Post
- https://yoursite.com/2024/11/previous-post/ # Previous Post
```

## Developer Customization

### Filter: `llms_txt_content`

You can modify the generated content using the `llms_txt_content` filter:
```php
add_filter('llms_txt_content', function($content) {
    // Add custom sections
    $content .= "## API Documentation\n\n";
    $content .= "- " . site_url('/api/docs') . "\n\n";
    
    // Add resources
    $content .= "## Resources\n\n";
    $content .= "- " . site_url('/whitepaper.pdf') . " # Technical Whitepaper\n";
    $content .= "- " . site_url('/case-studies/') . " # Case Studies\n\n";
    
    return $content;
});
```

### Programmatic Access

You can retrieve the options programmatically:
```php
$options = get_option('llms_txt_options');
$site_description = $options['site_description'] ?? '';
$contact_email = $options['contact_email'] ?? '';
```

## Requirements

- WordPress 5.5 or higher
- PHP 7.4 or higher
- WordPress VIP compatible (no filesystem writes required)

## WordPress VIP Compatibility

This plugin is specifically designed for WordPress VIP:

- ✅ No static file creation
- ✅ Uses WordPress rewrite rules
- ✅ Dynamic content generation
- ✅ Follows VIP coding standards
- ✅ No direct filesystem access
- ✅ Proper use of WordPress APIs

## Frequently Asked Questions

### Does this plugin write files to the server?

No. The plugin dynamically generates the llms.txt content on each request, making it perfect for WordPress VIP and other managed hosting environments.

### Can I customize the content that appears in llms.txt?

Yes! Use the admin settings page for basic customization, or use the `llms_txt_content` filter for advanced modifications.

### Will this work with multisite?

Yes, the plugin works with WordPress multisite installations. Each site will have its own llms.txt file and settings.

### How do I verify it's working?

1. Visit `yoursite.com/llms.txt` directly in your browser
2. Check your XML sitemap at `yoursite.com/wp-sitemap.xml`
3. Look for the llms.txt entry in the sitemap

### What if I need to exclude certain pages or posts?

Currently, the plugin includes pages by menu order and posts by date. For more complex filtering, use the `llms_txt_content` filter to modify the output.

## Support

For bug reports, feature requests, or contributions, please contact your development team or submit through your organization's standard channels.

## Changelog

### 1.0.0
- Initial release
- Dynamic llms.txt generation
- Sitemap integration
- Admin settings page
- Contact information section
- Customizable site description
- Content control options

## Credits

Developed for WordPress VIP compatibility by Ross Mulcahy

## License

This plugin is free software, is released under the terms of the GNU General Public License version 2 or (at your option) any later version. See LICENSE.md for complete license.