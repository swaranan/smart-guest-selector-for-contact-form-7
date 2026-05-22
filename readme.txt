=== CF7 Guests Field ===
Contributors: swaranan
Tags: contact form 7, cf7, guests, booking, adults, children, child ages
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a smart Guests field for Contact Form 7 with Adults, Children, and optional Children Age selection.

== Description ==

CF7 Guests Field extends Contact Form 7 by adding an advanced Guests selector suitable for hotel bookings, tours, reservations, events, and travel forms.

Instead of a simple number field, users can:

- Select total guests
- Choose Adults and Children separately
- Optionally enable age fields for each child
- Automatically validate totals
- Send structured guest data through Contact Form 7 emails

The plugin integrates directly into the Contact Form 7 form editor with its own tag generator button.

= Features =

- Adds a custom Guests field type to Contact Form 7
- Adults & Children selector UI
- Optional child age fields
- Configurable minimum and maximum guests
- Required field support
- Mobile-friendly UI
- Validation for total guest count
- Mail tags support
- Easy shortcode/tag generator inside CF7 editor

= Example Form Tag =

[cf7_guests* guests min:1 max:10 child_ages]

= Available Mail Tags =

[guests]
[guests_adults]
[guests_children]
[cf7-guests-summary]

= Example Output =

Guests: 4
Adults: 2
Children: 2
Child Ages: 5, 8

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/cf7-guests-field` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Make sure Contact Form 7 is installed and activated.
4. Edit a Contact Form 7 form.
5. Click the new "Guests" button in the form editor toolbar.
6. Configure your field options and insert the tag.

== Frequently Asked Questions ==

= Does this plugin require Contact Form 7? =

Yes. Contact Form 7 must be installed and activated.

= Can I disable child age fields? =

Yes. Simply omit the `child_ages` option from the form tag.

= Does it support required fields? =

Yes. Use the required version of the tag:

[cf7_guests* guests]

= Can I limit the number of guests? =

Yes. Use `min:` and `max:` options.

Example:

[cf7_guests guests min:1 max:8]

== Screenshots ==

1. Guests button inside Contact Form 7 editor
2. Frontend guest selector
3. Adults & Children dropdown UI
4. Child age fields example

== Changelog ==

= 1.1.0 =
* Added Contact Form 7 tag generator button
* Added Guests field settings popup
* Improved frontend UI
* Added optional child age fields
* Added validation improvements
* Added mail tag support

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
Includes Contact Form 7 editor integration and enhanced guest selector UI.

== License ==

This plugin is licensed under the GPLv2 or later.