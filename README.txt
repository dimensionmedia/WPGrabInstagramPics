=== WPGrabInstagramPics ===
Contributors: dimensionmedia
Donate link: http://davidbisset.com/
Tags: instagram
Requires at least: 3.6
Tested up to: 3.6.1

This plugin will search through recent Instagram posts (containing a certain hashtag or keyword), and import those photos along with some metadata into a custom post type.

== Installation ==

1. Upload the WPGrabInstagramPics folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Update a key term in the options, click the "grab" button in the grab area and if anything was found it would be put into the "Instagram Posts" section in the backend. My test hashtag was #confrz.

== What Will I Need? ==

You'll need a Instagram "Key" from their developer site.


== Changelog ==

= 0.3 =
* Significant changes to how plugin now works: instead of importing photos directly to the media gallery, we are using custom post types that store the metadata.
* Better process for detecting new Instagram posts via the API

= 0.2 =
* Code cleanup.
* Instagram Client ID now a setting.

= 0.1 =
* Basic bare-bones plugin.