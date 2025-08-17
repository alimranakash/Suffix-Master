# Suffix Master – Smart Title, Price & Content Branding

A comprehensive WordPress plugin for managing suffixes across your website content, including titles, prices, content footers, and slugs with full WooCommerce integration.

## 📁 Documentation Index

- **[README.md](README.md)** - Main documentation (this file)
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Comprehensive troubleshooting guide

## Features

### Content-Type Specific Suffix Management
- **Global Title Suffix**: Fallback suffix for all post and page titles
- **Post Title Suffix**: Specific suffix for blog posts (overrides global)
- **Page Title Suffix**: Specific suffix for pages (overrides global)
- **WooCommerce Product Title Suffix**: Specific suffix for product titles
- **Global Price Suffix**: Applied to all WooCommerce product prices
- **Content Footer Suffix**: Static text/HTML appended to post/product content
- **Slug Suffix**: Automatically appended to slugs of new posts and products

### Per-Item Override System
- Custom metaboxes on post edit screens
- Individual item suffixes override global settings
- Separate controls for posts, pages, and WooCommerce products

### Dynamic Placeholders
- `{year}` - Current year
- `{site_name}` - WordPress site name
- Extensible system for future placeholder additions

### User Experience Features
- **Live Preview**: Real-time suffix preview as you type
- **Reset All**: Clear all settings and restore defaults
- **Validation**: Comprehensive input validation and sanitization
- **Security**: Proper nonces, capability checks, and logging

## Installation

1. Upload the plugin files to `/wp-content/plugins/suffix-master/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Settings → Suffix Master** to configure

## Configuration

### Global Settings

1. Go to **Settings → Suffix Master**
2. Configure suffixes in the **Global Suffix Settings** tab:
   - **Global Title Suffix**: Fallback suffix for all post/page titles
   - **Post Title Suffix**: Specific suffix for blog posts only
   - **Page Title Suffix**: Specific suffix for pages only
   - **Global Slug Suffix**: Added to new post/page slugs
   - **Content Footer Suffix**: Appended to post/page content

### WooCommerce Settings

Configure WooCommerce-specific suffixes in the **WooCommerce Settings** tab:
- **Product Title Suffix**: Applied to product titles
- **Global Price Suffix**: Added to all product prices
- **Product Slug Suffix**: Added to new product slugs

### Per-Item Overrides

1. Edit any post, page, or product
2. Find the **Suffix Master Settings** metabox
3. Enter custom suffixes to override global settings
4. Leave fields empty to use global settings

## Usage Examples

### Content-Type Specific Title Suffixes
```
Global Title Suffix: "- {year}"
Post Title Suffix: "- Blog {year}"
Page Title Suffix: "- Page {year}"

Results:
- Blog posts: "My Blog Post - Blog 2024"
- Pages: "About Us - Page 2024"
- Other content: "My Content - 2024" (uses global)
```

### Product Price Suffix
```
Global Price Suffix: "(incl. tax)"
Result: "$19.99 (incl. tax)"
```

### Content Footer with HTML
```
Content Footer Suffix: "<p>© {year} {site_name}. All rights reserved.</p>"
Result: Appends copyright notice to all content
```

### Slug Suffix
```
Global Slug Suffix: "{year}"
Original: "my-new-post"
Result: "my-new-post-2024"
```

## Available Placeholders

- `{year}` - Current year (e.g., 2024)
- `{site_name}` - WordPress site name

## Suffix Priority Hierarchy

The plugin applies suffixes based on the following priority order (highest to lowest):

### Title Suffixes
1. **Per-item override** (metabox setting on individual post/page)
2. **Content-type specific** (Post Title Suffix or Page Title Suffix)
3. **Global fallback** (Global Title Suffix)

### Example Priority Flow
```
Post Title: "My Blog Post"

1. Check metabox override → If set: "My Blog Post - Custom Override"
2. Check Post Title Suffix → If set: "My Blog Post - Blog 2024"
3. Check Global Title Suffix → If set: "My Blog Post - 2024"
4. No suffix → "My Blog Post"
```

This hierarchy ensures maximum flexibility while maintaining consistent fallbacks.

## Tools & Actions

### Live Preview
- Enable in **Tools & Actions** tab
- See real-time preview of suffix changes
- Updates automatically as you type

### Reset All Settings
- Clears all configured suffixes
- Restores plugin to default state
- Includes confirmation dialog

### System Report
- Displays WordPress and plugin information
- Useful for troubleshooting
- One-click copy functionality

## Security Features

- **Input Validation**: All inputs validated and sanitized
- **Capability Checks**: Proper WordPress permission checks
- **Nonce Verification**: CSRF protection on all forms
- **Security Logging**: Events logged for debugging
- **XSS Prevention**: All outputs properly escaped

## Compatibility

- **WordPress**: 4.0+
- **PHP**: 5.6+
- **WooCommerce**: 3.0+ (optional)

## File Structure

```
suffix-master/
├── app/                    # Application classes (Framework pattern)
│   ├── Admin.php          # Admin functionality & metaboxes
│   ├── Front.php          # Frontend suffix application (posts/pages)
│   ├── Woocommerce.php    # WooCommerce-specific functionality
│   ├── AJAX.php           # AJAX handlers
│   ├── Common.php         # Common functionality
│   ├── Settings.php       # Settings configuration
│   └── ...
├── assets/                # CSS and JS files
│   ├── css/
│   └── js/
├── classes/               # Core utility classes
│   ├── Helper.php         # Helper functions
│   └── Validator.php      # Validation and security
├── docs/                  # Documentation
│   ├── README.md          # This file
│   └── TROUBLESHOOTING.md # Troubleshooting guide
├── views/                 # Template files
└── suffix-master.php      # Main plugin file
```

## Development

### Testing
1. Include `test-suffix-master.php` for development testing
2. Add `?suffix_master_test=1` to any URL while logged in as admin
3. Check test results in bottom-right corner
4. Remove test file from production

### Hooks and Filters
The plugin provides various hooks for customization:
- `suffix_master_title_suffix`
- `suffix_master_price_suffix`
- `suffix_master_content_suffix`
- `suffix_master_placeholders`

## Support

For support and feature requests, please contact the development team.

## Changelog

### Version 0.9
- Initial release
- Global suffix management
- Per-item overrides
- Dynamic placeholders
- Live preview functionality
- Security enhancements
- WooCommerce integration

## License

This plugin is licensed under the GPL v2 or later.
