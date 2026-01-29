# Installation Guide

Learn how to install and configure F-Shop on your WordPress website.

## System Requirements

Before installing F-Shop, ensure your server meets these requirements:

- **WordPress**: 5.0 or higher
- **PHP**: 7.2 or higher
- **MySQL**: 5.6 or higher
- **Web Server**: Apache, Nginx, or compatible

## Installation Methods

### Method 1: WordPress Plugin Directory (Recommended)

1. Log into your WordPress admin dashboard
2. Navigate to **Plugins** → **Add New**
3. Search for "F-Shop"
4. Click **Install Now**
5. Click **Activate Plugin**

### Method 2: Manual Installation

1. Download the F-Shop plugin ZIP file
2. Extract the contents
3. Upload the `f-shop` folder to `/wp-content/plugins/`
4. Activate the plugin through the WordPress plugins screen

### Method 3: FTP Installation

1. Download the plugin ZIP file
2. Extract and upload via FTP to `/wp-content/plugins/`
3. Activate through WordPress admin

## Initial Setup

### First-Time Configuration

After activation, F-Shop will automatically:

- Create necessary database tables
- Set up default pages (Cart, Checkout, Account)
- Register user roles
- Configure basic settings

### Essential Pages

F-Shop creates these pages automatically:

- **Shop** - Main product catalog
- **Cart** - Shopping cart page
- **Checkout** - Order processing page
- **My Account** - Customer account management

You can customize these pages later through WordPress.

## Configuration

### Basic Settings

Navigate to **F-Shop** → **Settings** to configure:

1. **General Settings**
   - Store name and contact information
   - Currency and pricing options
   - Measurement units

2. **Payment Methods**
   - Enable/disable payment gateways
   - Configure payment processor settings

3. **Shipping Options**
   - Set up delivery methods
   - Configure shipping zones and rates

### Product Categories

Set up your product categories:

1. Go to **Products** → **Categories**
2. Create main categories and subcategories
3. Assign products to appropriate categories

## Testing Your Installation

### Verify Installation

Check that everything is working properly:

1. **Visit your shop page** - Ensure products display correctly
2. **Test the cart** - Add a product and verify it appears in cart
3. **Process a test order** - Complete a sample purchase
4. **Check admin functionality** - Verify all admin screens work

### Common Installation Issues

#### Issue: Plugin won't activate
**Solution**: Check PHP version requirements and plugin conflicts

#### Issue: Pages not created
**Solution**: Manually create pages and assign shortcodes:
- Cart: `[fs_cart]`
- Checkout: `[fs_checkout]`
- Account: `[fs_account]`

#### Issue: Database errors
**Solution**: Check database permissions and table creation

## Post-Installation Steps

### 1. Add Your First Product

1. Go to **Products** → **Add New**
2. Enter product details
3. Set price and inventory
4. Assign to categories
5. Publish the product

### 2. Configure Payment Methods

1. Navigate to **F-Shop** → **Payment Methods**
2. Enable desired payment options
3. Configure API keys and settings
4. Test payment processing

### 3. Set Up Shipping

1. Go to **F-Shop** → **Shipping**
2. Configure delivery zones
3. Set shipping rates
4. Test shipping calculations

### 4. Customize Appearance

1. **Themes**: Choose F-Shop compatible themes
2. **Widgets**: Add shopping cart widgets
3. **Menus**: Include shop navigation in menus
4. **Shortcodes**: Use F-Shop shortcodes in pages/posts

## Advanced Configuration

### Performance Optimization

- Enable caching plugins
- Optimize image sizes
- Use CDN for static assets
- Configure database optimization

### Security Settings

- Enable SSL/HTTPS
- Configure user roles and permissions
- Set up security plugins
- Regular backups

## Updating F-Shop

### Automatic Updates

Enable automatic updates through WordPress:

1. Go to **Dashboard** → **Updates**
2. Enable automatic plugin updates
3. F-Shop will update automatically when new versions are released

### Manual Updates

1. Backup your site
2. Deactivate F-Shop plugin
3. Delete old plugin files
4. Upload new version
5. Reactivate plugin
6. Check for database updates

## Troubleshooting

### Diagnostic Information

If you encounter issues, gather this information:

- WordPress version
- PHP version
- Active plugins list
- Error messages
- Browser console errors

### Support Resources

- **Documentation**: [Official Docs](https://karakushan.github.io/f-shop/)
- **GitHub Issues**: Report bugs and request features
- **Community Forum**: Get help from other users
- **Premium Support**: Available for commercial licenses

## Next Steps

Once installed and configured:

1. **Learn the basics** - Read the [Getting Started](stock-status/getting-started.md) guide
2. **Explore features** - Check out advanced functionality
3. **Customize** - Adapt F-Shop to your business needs
4. **Integrate** - Connect with other plugins and services

---

**Need help?** Visit our [support forum](https://github.com/karakushan/f-shop/issues) for assistance.