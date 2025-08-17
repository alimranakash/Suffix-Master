# Suffix Master - Troubleshooting Guide

## 🔍 Quick Diagnostics

### Step 1: Test Plugin Functionality
Add `?suffix_master_test=1` to any frontend URL while logged in as admin to run diagnostics:
```
https://yoursite.com/?suffix_master_test=1
```

### Step 2: Debug Suffix Settings
Add `?suffix_debug=1` to any frontend URL while logged in as admin to see current settings:
```
https://yoursite.com/?suffix_debug=1
```

## 🚨 Common Issues & Solutions

### Issue 1: Suffixes Not Appearing on Frontend

**Symptoms:**
- Settings saved successfully in admin
- No suffixes visible on posts, pages, or products

**Solutions:**

1. **Check Settings Location**
   - Go to **Settings → Suffix Master** (not under WooCommerce)
   - Verify settings are saved correctly

2. **Verify Plugin Activation**
   - Deactivate and reactivate the plugin
   - Check for any error messages

3. **Test with Default Theme**
   - Switch to a default WordPress theme (Twenty Twenty-Four)
   - Check if suffixes appear
   - If yes, there's a theme conflict

4. **Check for Plugin Conflicts**
   - Deactivate all other plugins
   - Test suffix functionality
   - Reactivate plugins one by one to identify conflicts

### Issue 2: WooCommerce Suffixes Not Working

**Symptoms:**
- Post/page suffixes work
- Product title/price suffixes don't appear

**Solutions:**

1. **Verify WooCommerce is Active**
   - Ensure WooCommerce plugin is installed and activated
   - Check WooCommerce version compatibility

2. **Check Product Pages**
   - Test on single product pages
   - Test on shop/category pages
   - Different contexts may behave differently

3. **Clear WooCommerce Cache**
   - Go to WooCommerce → Status → Tools
   - Clear transients and cache

### Issue 3: Placeholders Not Processing

**Symptoms:**
- Suffixes appear but show `{year}` or `{site_name}` literally

**Solutions:**

1. **Check Placeholder Syntax**
   - Use exact syntax: `{year}` and `{site_name}`
   - Case sensitive
   - No extra spaces

2. **Test Placeholder Processing**
   - Use the test URL: `?suffix_master_test=1`
   - Check if placeholder processing test passes

### Issue 4: Content Footer Not Appearing

**Symptoms:**
- Title suffixes work
- Content footer suffix doesn't appear

**Solutions:**

1. **Check Page Context**
   - Content footer only appears on singular pages (single posts/pages/products)
   - Not on archive pages, home page, etc.

2. **Check Theme Compatibility**
   - Ensure theme uses `the_content()` function
   - Some custom themes may not call this hook

3. **Check Hook Priority**
   - Other plugins might override content
   - Try deactivating content-related plugins

## 🔧 Advanced Troubleshooting

### Enable WordPress Debug Mode

Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Check `/wp-content/debug.log` for errors.

### Check Hook Registration

Add this to your theme's `functions.php` temporarily:
```php
add_action('wp_footer', function() {
    if (current_user_can('manage_options')) {
        global $wp_filter;
        echo '<pre>';
        echo 'the_title hooks: ' . (isset($wp_filter['the_title']) ? 'YES' : 'NO') . "\n";
        echo 'the_content hooks: ' . (isset($wp_filter['the_content']) ? 'YES' : 'NO') . "\n";
        echo 'woocommerce_get_price_html hooks: ' . (isset($wp_filter['woocommerce_get_price_html']) ? 'YES' : 'NO') . "\n";
        echo '</pre>';
    }
});
```

### Manual Settings Check

Check if settings are saved correctly:
```php
$settings = get_option('suffix-master');
var_dump($settings);
```

## 🎯 Testing Checklist

### Basic Functionality
- [ ] Plugin activated without errors
- [ ] Settings page accessible at Settings → Suffix Master
- [ ] Settings save successfully
- [ ] Test URL `?suffix_master_test=1` shows all green checkmarks

### Title Suffixes
- [ ] Global title suffix appears on posts
- [ ] Global title suffix appears on pages
- [ ] Product title suffix appears on WooCommerce products
- [ ] Per-item overrides work in metaboxes

### Price Suffixes
- [ ] Price suffix appears on single product pages
- [ ] Price suffix appears on shop pages
- [ ] Price suffix appears in cart/checkout

### Content Suffixes
- [ ] Content footer appears on single posts
- [ ] Content footer appears on single pages
- [ ] Content footer appears on single products
- [ ] HTML formatting works in content footer

### Placeholders
- [ ] `{year}` shows current year
- [ ] `{site_name}` shows site name
- [ ] Placeholders work in all suffix types

## 🆘 Still Having Issues?

### Collect Debug Information

1. **WordPress Info:**
   - WordPress version
   - PHP version
   - Active theme
   - Active plugins list

2. **Plugin Info:**
   - Suffix Master version
   - Settings configuration
   - Error messages (if any)

3. **Test Results:**
   - Results from `?suffix_master_test=1`
   - Results from `?suffix_debug=1`

### Common Plugin Conflicts

**SEO Plugins:**
- Yoast SEO
- RankMath
- All in One SEO

**Caching Plugins:**
- WP Rocket
- W3 Total Cache
- WP Super Cache

**Page Builders:**
- Elementor
- Divi
- Beaver Builder

**WooCommerce Extensions:**
- WooCommerce Subscriptions
- WooCommerce Bookings
- Custom product plugins

### Reset Plugin

If all else fails, reset the plugin:

1. Go to Settings → Suffix Master → Tools & Actions
2. Click "Reset All Settings"
3. Reconfigure your suffixes

### Manual Reset

If admin is not accessible:
```php
delete_option('suffix-master');
```

## 📞 Support

For additional support:
1. Check WordPress.org plugin support forum
2. Review plugin documentation
3. Contact plugin developer

Remember to include debug information and test results when seeking support.
