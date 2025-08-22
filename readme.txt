=== Wiki Image Social Share ===
Contributors: wikiwyrhead
Tags: social sharing, whatsapp, facebook, twitter, linkedin, pinterest, instagram, telegram, discord, reddit, open graph, twitter cards, image sharing, social media, rich previews
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 1.2.1
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enhanced social media sharing plugin for WordPress images with rich preview support across all major platforms including WhatsApp thumbnail display.

== Description ==

**Enhanced social media sharing with rich previews for all major platforms!**

Wiki Image Social Share is a comprehensive, open-source WordPress plugin that enables rich social media sharing with proper thumbnail previews across all major platforms. Specifically designed to solve WhatsApp thumbnail display issues while providing universal social media compatibility.

= Key Features =

**🚀 Universal Platform Support:**
* **WhatsApp** - Proper thumbnail display with rich previews
* **Facebook** - Complete Open Graph integration
* **Twitter/X** - Enhanced Twitter Cards support
* **LinkedIn** - Professional content optimization
* **Pinterest** - Rich Pins implementation
* **Instagram** - Story and post sharing
* **Telegram** - Rich message previews
* **Discord** - Embedded link previews
* **Reddit** - Enhanced link sharing

**🎯 Advanced Metadata Management:**
* Complete Open Graph tags implementation
* Twitter Card metadata optimization
* JSON-LD structured data support
* Dynamic content generation
* Platform-specific optimizations

**🖼️ Image Optimization:**
* Multiple image sizes (1200x630, 1024x512, 600x315)
* WebP format support with fallbacks
* Automatic image validation
* Platform-specific aspect ratio optimization
* Performance-optimized image delivery

**⚡ Performance & Security:**
* WordPress VIP compliant code
* Advanced caching mechanisms
* Lazy loading support
* Comprehensive input sanitization
* CSRF protection with nonces
* SQL injection prevention

**🛠️ Developer-Friendly:**
* Extensive hook and filter system
* Custom shortcodes support
* REST API integration
* Comprehensive documentation
* Open source with active development



== Installation ==

1. Upload share-this-image to the /wp-content/plugins/ directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= What Share This Image plugin do? =

Share This Image ( STI ) is an image sharing plugin for WordPress. The main difference between this plugin and many other social sharing plugins is that STI specializes in image sharing and shares exactly selected images with fully customizable content like title, description and URL.
Here is a list of some main plugin features:

* Exact image sharing.
* Fully customizable url, image, title and description that you want to share.
* Supports all most popular social networks/messengers.
* Choose what images on what pages to share.
* and many more...


= What are the requirements to use Share This Image? =

Share This Image is a plugin for self-hosted WordPress sites, or wordpress.com hosted sites that allow installation of third party plugins.
Share This Image requires the following at minimum to work properly:

* WordPress 4.0 or greater
* WooCommerce 3.0.0 or greater
* PHP 5.5 or greater
* MySQL 5.6 or MariaDB 10.0 or greater
* Apache or Nginx server (recommended, but other options may work as well)

= Is it work with my theme/plugin? =

Plugin will work with most of the available WordPress themes. And it also was tested with the most popular WordPress plugins. If yo still faced any problems using the plugin please plugin [contact support]

= What is the steps to make this plugin works on my site? =

In order to start showing plugin sharing buttons you need to take the following steps:
1. Install and activate the Share This Image plugin.
2. Check the plugin settings page. 
3. Check display rules. With these special options it is possible to choose what images on what pages must be available for sharing.
4. Set sharing button. Enable/disable sharing buttons that must be visible on your site. 
5. Finish! From now sharing buttons must work on selected pages ( based on current display rules ).

= Can I enable image sharing only for certain pages or images? =



= What about mobile devices support? =

Sharing buttons works great on both desktop and mobile devices. Optionally you can disable mobile sharing by opening the plugin settings page and turning off Enable on mobile? option.
By default on mobile instead of a sharing button block you will see one sharing icon that will show a sharing button when clicking on it. But this behaviour can be changed.

= Can I change the styles for sharing buttons? =

Yes. With the PRO plugin settings you can choose from several pre-defined sharing buttons styles or change styling for each button individually.

= How can I customize what url, image, title and description to share? =

When sharing new image for title and description plugin first of all looks in 'data-title' and 'data-summary' attributes of image.

So you can set your fully customizable content by adding this attributes. It's can look like that:

`<img src="images/youre-cool-image.jpg" data-title="Title for image" data-summary="Description for image">`

If image doesn't have data attributes then plugin will use title attribute for title and attr attribute for summary.

`<img src="images/youre-cool-image.jpg" title="Title for image" attr="Description for image">`

If image doesn't have data, title and attr attributes then will be used default title and description that you set in the plugin settings page.

Also it is possible to set shared image that can be different from image in the 'img' tag.

It's can be done with help of 'data-media' attribute:

`<img src="youre-image-to-display-on-page.jpg" data-media="youre-image-to-share.jpg">`

Also you can change shared link.

By default plugin will share link to the page where your shared image in situated. But you can simply change this behavior.

Just add 'data-url' attribute with link that you want to be shared.

`img src="images/youre-cool-image.jpg" data-url="http://your-link.com">`

With the PRO plugin version you have the more advanced features in content customization. You can set priority for content sources, use special text variables and even customize sharing content individually for each sharing button.

= How to use plugins build-in shortcode? =

Most common there is no need to use shortcode. Plugin will automatically work with all images that you have on your site.

But with shorcode it is very simple to share desired image with custom title and description.

All you need to do is add this shortcode inside your page or post content section

`[sti_image image="http://your-image-to-display.jpg" shared_image="http://your-image-to-share.jpg" shared_title="Your-title" shared_desc="Your-Description"]`

It is very simple and don't need additional explanation.

= Is it works only with 'img' tag? =

No.

Plugin give ability to run it not only for images, but for any blocks of content.

Only one condition - this block must have data-media attribute with link to shared image.

For example - we have block with custom content inside. This block has class shared-box. So it is very easy to add sharing content for it.

`<div class="shared-box" data-media="images/youre-cool-image.jpg" data-title="Title for image" data-summary="Description for image">
   Youre custom content ( text, html or any other )
</div>`

Don't forget, that class name of block must be specified in plugin selector option. For example, if we want to share all images and this block then selector will be img, .shared-box.

That's all! After this if any of your visitors hover on block with class name shared-box he will see appeared share box with social buttons.

= I install this plugin on my local server and have issues with sharing images. What's wrong? =

In order to share images and all other data social networks must scrap data from you website page. So if your website is not publicly available data will not be scraped.

= Sharing buttons are not visible on my page. What to do? =

There are many reasons that can lead to such an issue. Please follow these steps that can help you with solving the problem.

* Open plugin settings page and find **Minimal width** and **Minimal height** options. Make sure that they are bigger enought overwise sharing will not work for some of your site images.
* Check **Buttons position** option and if it is set to **On image ( always show )** then change it to **On image ( show on mouse enter )**.
* Check the **Display rules** option and make sure that you have proper display conditions for the sharing button.
If your problem is still there - please ask your question on support forum.

= Sharing buttons not visible for my gallery/grid? What to do? =

Problem with the plugin like image galleries or grids is that they load its content dynamically and use JavaScript code for this. The Share This Image plugin works great with many of such types of plugins but sometimes the issues can still appear. In this case please try following steps:

* Open the plugin settings page and check **Buttons position** option. If it is set to **On image ( always show )** then change it to **On image ( show on mouse enter )**.

* This steps requires some coding skills. The trick is to recall sharing buttons display method after the gallery/grid images are fully loaded.
Than just use following JavaScript code:

`$("img").sti();`

The trick here is to call that method only after the gallery/grid images are loaded. Usually such plugins have some JS triggers that are activated after the full load. So you need to know it and then the final code will looks like that:

`$(document).on("custom-event-name", function(){
    window.clearTimeout(timeoutID);
        timeoutID = window.setTimeout( function() {
        $("img").sti();
    }, 1000 );
});`

= Sharing buttons shares default page content and not the one that I selected. How to fix it? =

There are several reasons for this. Please walk through the steps below.
Note: after each step you need to clear your browser cache and the cache of any active caching plugin on your website.

* Open the plugin settings page and find **Use intermediate page** option. Change its value and check if something was changed.

* Make sure that your website is open for indexing: open **Settings** -> **Reading** page and find **Search engine visibility** option.

* Use tools like [Facebook debugger](https://developers.facebook.com/tools/debug/) or [Twitter card validator](https://cards-dev.twitter.com/validator) to check what actual sharing data social networks sees. This information can help you figure out what goes wrong.

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/share-this-image)

== Screenshots ==

1. Plugin settings page
2. Feature to share selected image
3. Content customization
4. Filtering feature
5. Sharing buttons positions
6. Sharing buttons styles
7. Sharing content customization
8. Individual buttons content

== Changelog ==

= 2.08 ( 27.01.2025 ) =
* Update - Freemius sdk

= 2.07 ( 16.12.2024 ) =
* Update - Freemius sdk

= 2.06 ( 28.10.2024 ) =
* Update - Freemius sdk

= 2.05 ( 14.10.2024 ) =
* Update - Freemius sdk

= 2.04 ( 16.09.2024 ) =
* Update - Freemius sdk
* Fix - Check host before making redirect for shortlinks

= 2.03 ( 04.09.2024 ) =
* Fix - Encode parameters for shortcodes

= 2.02 ( 30.08.2024 ) =
* Fix - Encode URL parameters for plugin settings page
* Fix - Encode parameters for sharing buttons blocks

= 2.01 ( 12.08.2024 ) =
* Update - Freemius sdk

= 2.00 ( 15.07.2024 ) =
* Update - Freemius sdk

= 1.99 ( 20.05.2024 ) =
* Update - Compare host names before redirecting user via sharing link

= 1.98 ( 06.05.2024 ) =
* Update - Freemius sdk

= 1.97 ( 22.04.2024 ) =
* Update - Freemius sdk

= 1.96 ( 29.03.2024 ) =
* Fix - Bug with short links feature

= 1.95 ( 16.03.2024 ) =
* Fix - With with redirects when sharing custom page url

= 1.94 ( 04.03.2024 ) =
* Add - Viber sharing button

= 1.93 ( 22.02.2024 ) =
* Add - New plugin settings page layout

= 1.92 ( 22.01.2024 ) =
* Update - Support for Envira Gallery plugin
* Fix - Pinterest sharing

= 1.91 ( 08.01.2024 ) =
* Update - Freemius sdk

= 1.90 ( 25.12.2023 ) =
* Update - Integration with GA4
* Dev - Add stiAnalytics js event

= 1.89 ( 04.12.2023 ) =
* Add - Notices about plugin integrations
* Update - Admin page styles
* Update - Freemius sdk

= 1.88 ( 22.11.2023 ) =
* Add - Notices about plugin integrations

= 1.87 ( 06.11.2023 ) =
* Dev - Update function for display rules options
* Update - Freemius sdk

= 1.86 ( 16.10.2023 ) =
* Update - Support for Avada theme
* Update - Support for Metaslider plugin
* Dev - Add sti_generated_group_selector filter

= 1.85 ( 30.09.2023 ) =
* Add - Support for OceanWP theme blocks
* Update - Support for Avada theme
* Update - Google Analytics 4 integration

= 1.84 ( 20.09.2023 ) =
* Add - New plugin option to control z index style
* Update - Freemius sdk

= 1.83 ( 04.09.2023 ) =
* Add - Option to change Twitter icon to X
* Add - Support for Spectra plugin image galleries block
* Update - Support for Elementor image galleries
* Fix - Admin options check after plugin initialization
* Dev - Store buttons svg icons codes only in the php code

= 1.82 ( 21.08.2023 ) =
* Add - Support for Elementor image gallery

= 1.81 ( 10.07.2023 ) =
* Update - Freemius sdk
* Fix - Bug with block editor search module

= 1.80 ( 26.06.2023 ) =
* Add - Admin page error notices for incorrect display rules
* Update - Freemius sdk

= 1.79 ( 12.06.2023 ) =
* Update - Support for NextGEN Gallery plugin
* Update - Admin page notices
* Dev - Add sti_media js filter

= 1.78 ( 01.05.2023 ) =
* Update - Freemius sdk

= 1.77 ( 17.04.2023 ) =
* Update - Support for Avada theme
* Update - Support for Flatsome theme
* Update - Support for Porto theme
* Fix - Styles for sharing buttons with active WP admin bar

= 1.76 ( 08.03.2023 ) =
* Update - Freemius sdk
* Fix - Conflict with SEOPress plugin

= 1.75 ( 09.02.2023 ) =
* Update - Admin page text

= 1.74 ( 12.12.2022 ) =
* Update - Freemius sdk

= 1.73 ( 31.10.2022 ) =
* Dev - Add sti_default_selector filter
* Dev - Add sti_generated_selectors filter

= 1.72 ( 23.08.2022 ) =
* Update - Freemius sdk

= 1.71 ( 08.08.2022 ) =
* Update - Minify assets

= 1.70 ( 06.06.2022 ) =
* Fix - Several pages loading during page redirections

= 1.69 ( 30.05.2022 ) =
* Fix - Bug with short links feature when using plain permalinks structure

= 1.68 ( 18.04.2022 ) =
* Add - Support for WooThumbs for WooCommerce by Iconic plugin
* Dev - Add sti_sharing_box_layout js filters

= 1.67 ( 07.03.2022 ) =
* Update - Security fixes

= 1.66 ( 07.02.2022 ) =
* Update - Styles for admin page welcome message
* Fix - Bug with admin page scripts loader

= 1.65 ( 13.12.2021 ) =
* Add - Support for Envira Gallery plugin

= 1.64 ( 09.11.2021 ) =
* Add - Support for Simple Lightbox plugin

= 1.63 ( 25.10.2021 ) =
* Update - Styles for sharing buttons

= 1.62 ( 29.09.2021 ) =
* Add - Support for  SimpLy Gallery Block & Lightbox plugin

= 1.61 ( 09.09.2021 ) =
* Update - Odnoklassniki sharing link
* Update - Pinterest sharing link
* Update - Remove Digg and Delicious sharing buttons
* Update - Admin settings fields
* Fix - Error with not valid scripts loaded for new widgets block editor
* Fix - Change styles for mobile sharing icon

= 1.60 ( 18.08.2021 ) =
* Add - Sharing buttons display rules feature. Simple way to choose what images on what pages to share
* Update - Admin page Get Started message
* Update - PRO features description
* Fix - Error during database creation that was caused by wrong COLLATION value

= 1.59 ( 02.08.2021 ) =
* Add - Short link option. Adds feature to create and share short links. Very useful for sharing on Twitter, WhatsApp, etc.
* Update - Sharing buttons styling
* Fix - Sharing buttons shortcode. Share default image if no image specified

= 1.58 ( 19.07.2021 ) =
* Fix - Gutenberg blocks display issue
* Dev - Use new block_categories_all filter for blocks

= 1.57 ( 21.06.2021 ) =
* Add - Support for Photo Gallery plugin
* Dev - Add stiLoaded js event

= 1.56
