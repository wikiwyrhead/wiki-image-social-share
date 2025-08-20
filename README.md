# Wiki Image Social Share

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![GitHub Issues](https://img.shields.io/github/issues/wikiwyrhead/wiki-image-social-share.svg)](https://github.com/wikiwyrhead/wiki-image-social-share/issues)

Enhanced social media sharing plugin for WordPress images with rich preview support across all major platforms including **WhatsApp thumbnail display**.

## 🚀 Features

### Universal Platform Support
- **WhatsApp** - Proper thumbnail display with rich previews ✅
- **Facebook** - Complete Open Graph integration
- **Twitter/X** - Enhanced Twitter Cards support  
- **LinkedIn** - Professional content optimization
- **Pinterest** - Rich Pins implementation
- **Instagram** - Story and post sharing
- **Telegram** - Rich message previews
- **Discord** - Embedded link previews
- **Reddit** - Enhanced link sharing

### Advanced Metadata Management
- Complete Open Graph tags implementation
- Twitter Card metadata optimization
- JSON-LD structured data support
- Dynamic content generation
- Platform-specific optimizations

### Image Optimization
- Multiple image sizes (1200x630, 1024x512, 600x315)
- WebP format support with fallbacks
- Automatic image validation
- Platform-specific aspect ratio optimization
- Performance-optimized image delivery

### Performance & Security
- WordPress VIP compliant code
- Advanced caching mechanisms
- Lazy loading support
- Comprehensive input sanitization
- CSRF protection with nonces
- SQL injection prevention

## 📦 Installation

### From WordPress Admin
1. Download the latest release from [GitHub Releases](https://github.com/wikiwyrhead/wiki-image-social-share/releases)
2. Go to **Plugins > Add New > Upload Plugin**
3. Upload the zip file and activate

### Manual Installation
1. Clone this repository: `git clone https://github.com/wikiwyrhead/wiki-image-social-share.git`
2. Upload to `/wp-content/plugins/wiki-image-social-share/`
3. Activate through the WordPress admin

### Composer
```bash
composer require wikiwyrhead/wiki-image-social-share
```

## 🔧 Configuration

### Basic Setup
1. Go to **Settings > Wiki Image Social Share**
2. Configure your social media handles
3. Set up display rules for images
4. Choose sharing button positions and styles

### WhatsApp Optimization
The plugin automatically optimizes images for WhatsApp:
- Minimum 200x200px resolution
- Preferred aspect ratios: 1:1, 16:9, or 1.91:1
- File size optimization under 300KB
- Proper Open Graph meta tags

### Advanced Configuration
```php
// Custom image sizes
add_filter('wiss_image_sizes', function($sizes) {
    $sizes['whatsapp'] = [1200, 630];
    $sizes['twitter'] = [1024, 512];
    return $sizes;
});

// Platform-specific metadata
add_filter('wiss_og_tags', function($tags, $platform) {
    if ($platform === 'whatsapp') {
        $tags['og:image:width'] = '1200';
        $tags['og:image:height'] = '630';
    }
    return $tags;
}, 10, 2);
```

## 🎯 Usage

### Shortcodes
```php
// Basic sharing buttons
[wiss_buttons buttons="facebook,twitter,whatsapp,linkedin"]

// Custom image sharing
[wiss_image image="https://example.com/image.jpg" title="Custom Title" description="Custom Description"]
```

### Template Functions
```php
// Display sharing buttons
if (function_exists('wiss_sharing_buttons')) {
    wiss_sharing_buttons([
        'image' => 'https://example.com/image.jpg',
        'title' => 'Custom Title',
        'platforms' => ['facebook', 'twitter', 'whatsapp']
    ]);
}
```

## 🧪 Testing

### Playwright Test Suite
```bash
# Install dependencies
npm install

# Run cross-browser tests
npm run test:playwright

# Run specific platform tests
npm run test:whatsapp
npm run test:facebook
npm run test:twitter
```

### Manual Testing
- [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
- [Twitter Card Validator](https://cards-dev.twitter.com/validator)
- [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/)

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup
```bash
git clone https://github.com/wikiwyrhead/wiki-image-social-share.git
cd wiki-image-social-share
npm install
composer install
```

### Code Standards
- Follow WordPress Coding Standards
- Use PHP 7.4+ features
- Write comprehensive tests
- Document all functions

## 📊 Performance

- **PageSpeed Insights**: 90+ score
- **LCP**: <2.5s
- **FID**: <100ms
- **CLS**: <0.1

## 🐛 Support

- **GitHub Issues**: [Report bugs](https://github.com/wikiwyrhead/wiki-image-social-share/issues)
- **Documentation**: [Wiki](https://github.com/wikiwyrhead/wiki-image-social-share/wiki)
- **Community**: [Discussions](https://github.com/wikiwyrhead/wiki-image-social-share/discussions)

## 📄 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Original inspiration from the Share This Image plugin
- WordPress community for feedback and testing
- All contributors who help improve this project

## 📈 Roadmap

- [ ] Instagram API integration
- [ ] TikTok sharing support
- [ ] Advanced analytics dashboard
- [ ] Multi-language support
- [ ] Custom CSS builder
- [ ] Bulk image optimization

---

**Made with ❤️ by [Arnel Go](https://arnelbg.com/)**

[⭐ Star this repo](https://github.com/wikiwyrhead/wiki-image-social-share) if you find it useful!
