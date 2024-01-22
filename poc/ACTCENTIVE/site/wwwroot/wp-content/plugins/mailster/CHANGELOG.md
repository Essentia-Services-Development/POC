### Version 3.3.11 (2023-11-21)

- fixed: deprecated method
- fixed: issue when replacing links introduced in 3.3.9
- fixed: BF sales dates

### Version 3.3.10 (2023-11-15)

- fixed: warning on PHP 8.2 if subscriber count is 0
- fixed: issue with the admin bar
- fixed: smaller issue

### Version 3.3.9 (2023-11-06)

- PHP 8.2 compatibility 🎉
- updated: WP Coding Standards 3.0
- updated: Freemius SDK to 2.6.0
- fixed: inline style attribute got removed in some edge cases
- improved: placeholder image tags algorithmus
- fixed: smaller issue

### Version 3.3.8 (2023-10-05)

- improved: honeypot mechanism to prevent false positives on heavy cached sites.
- improved: wording on support
- updated: Freemius SDK to 2.5.12

### Version 3.3.7 (2023-07-07)

- fixed: missing action on migration process
- improved: handling of trials
- updated: Freemius SDK to 2.5.10

### Version 3.3.6 (2023-06-29)

- fixed: lists are not confirmed if user choice is enabled.

### Version 3.3.5 (2023-06-14)

- updated: Freemius SDK to 2.5.9
- fixed: small CSS issues on RTL

### Version 3.3.4 (2023-06-01)

- fixed: MySQL error on Autoresponder which uses "has received but not opened" or "has received and not clicked" condition.
- fixed: RTL CSS issue on Autoresponder options sidebar
- fixed: RTL CSS issue in admin bar and settings

### Version 3.3.3 (2023-05-04)

- added: 'mailster_admin_header' hook
- added: missing added argument
- added: if verification of subscribers fails the WP_Error object contains now the initial data
- improved: better handling of suppression of WPDB in lists class
- improved: check for missing functions in conditions
- fixed: conditions script must require mailster-script
- update: enabled beacon on the upgrade page
- fixed: check for existence of block editor in admin header
- fixed: default option on custom fields were not displayed correctly
- fixed: id was not set for custom fields on first save
- improved: lists assignments for new subscribers
- improved: refractor of lists class
- improved: the way tags are saved and loaded
- returned ids of subscriber queries are now integers

### Version 3.3.2 (2023-03-23)

- fixed: Division by zero on campaign edit screen
- fixed: Header already sent error on account page
- fixed: dashboard redirects to a blank page if in activation mode
- fixed: deprecated notices in PHP 8.2
- fixed: missing index in placeholder.class.php
- improved: check if `set_time_limit` is disabled

### Version 3.3.1 (2023-03-20)

- updated: Freemius SDK to 2.5.5
- fixed: Undefined variable $output_time in queue.class.php
- fixed: CSS issue on Precheck
- improved: connect screen for Envato licenses

### Version 3.3.0 (2023-03-15)

- new: [Action Required] license provider - please follow the guide to migrate your license
- new: Email logs. You can now enable logging for all outgoing mails sent by Mailster
- new: option to use OpenStreetMap as an alternative for Google Maps
- new: emoji picker in campaign editor

### Version 3.2.6 (2023-03-13)

- added: option to bulk add and remove from every list
- fixed: condition on the subscribers over view
- fixed: PHP throws error if `str_replace` with a value below 0
- fixed: RSS feed missing modified date in some cases change: RSS feed extract images from content first
- fixed: issue when bulk confirm/add/delete subscribers from list
- fixed: missing slug on add plugins page
- improved: better sql query for growth calculation.
- improved: database cleanup mechanism
- improved: prevent caching on cron page
- improved: speed of delivery is now split into PHP processing and mail sending on the cron page
- improved: updated queue SQL to handle campaigns when split campaigns is enabled.

### Version 3.2.5 (2023-03-02)

- new: Admin header bar with new support integration.
- new: Help buttons located in the plugin to provide context-specific assistance
- change: Mailster related notices only show up on Mailster related pages.
- fixed: campaign related conditions for "Any Campaign" now work as expected.
- fixed: restoring of deleted subscribers working again.
- fixed: issue where some subscribers are not able to get deleted/exported
- fixed: missing object error in mailster-script
- fixed: searching with quotes on the subscribers page working again
- fixed: broken RSS feed URL can cause timeouts
- fixed: force array when duplicating a campaign for `wp_insert_post`
- change: do not use the `$wp_filesystem` global when require filesystem
- added: `mailster_get_user_setting` and `mailster_set_user_setting`

### Version 3.2.4 (2023-01-31)

- Do not show form occurrences from auto draft posts
- allow selection private post in the static editbar
- implemented feed item date checks
- improved: handling of images in RSS feeds
- added: new status upgrade status code
- remove any tinyMCE attributes from the content on campaign save
- use vanilla methods to change target on frontpage

### Version 3.2.3 (2022-12-07)

- fixed: E_ERROR on Geo location class in PHP 8.1
- added: enhancement issue template
- check if option from queue has template element
- fixed: footer branding
- fixed: Uncaught TypeError: in notifications.class.php
- standardize outgoing URLs
- tested up to 6.1

### Version 3.2.2 (2022-11-01)

- added: native Advanced Custom Fields support.
- fixed: import of WordPress roles wasn't working in some cases.
- fixed: force hard reload on cron page if opened in browser.
- improved: handling of thickbox modal if other plugins interfere.
- added: `X-Redirect-By` header on all Mailster related redirects.
- added: set the global post inside the template editor.

### Version 3.2.1 (2022-09-22)

- fixed: issue where taxonomies in campaigns are not stored correctly.
- improved: `{unsub}` and `{profile}` tags can now be used in confirmation messages.
- added: message for block form plugin
- support for [Local Google Fonts](https://wordpress.org/plugins/local-google-fonts/)
- new: filter: `mailster_do_placeholder` which filters the replaced content
- fixed: Jetpack no longer includes sharing button in content or excerpt
- fixed: some error notices on PHP 8.1

### Version 3.2.0 (2022-08-17)

- fixed: querying subscribers do no longer return subscribers with status deleted.
- fixed: adding an already deleted subscriber working as expected.
- fixed: wrong timestamp on signups if subscriber exists.
- improved: support for multiple campaigns triggered by action hooks.
- improved: removed skeleton loader on foreign columns in overview.
- improved: action hook campaigns support now multiple hooks, separated with a comma.
- improved: database updates now run in the background (optional).
- improved: taxonomies dropdown now uses select2 library to better handle large taxonomy entries.
- new: defaults strings form confirmation message.
- confirmation page on newsletter homepage now wrapped with `wpautop`.
- new: filters: `mailster_editor_tags` and `mailster_notification_content`.

### Version 3.1.6 (2022-07-25)

- added: option to change tracking of campaigns once the campaign has been finished
- improved: list counts are loaded now asynchronously to improve page load time
- improved: embedded images are now found outside the upload folder
- settings with "token" in the key are now hidden in the test email

### Version 3.1.5 (2022-06-16)

- new: growth rates on campaign overview
- opens, clicks, unsubscribes and bounces are now sortable
- minimum required PHP version is now 7.2.5
- reduced size of vendor folder

### Version 3.1.4.1 (2022-05-18)

- fixed: issue with WooCommerce 6.5.1 and third party library

### Version 3.1.4 (2022-05-02)

- new: forms on frontend no longer requires jQuery
- fixed: using single quotes in tags causes problems
- fixed: PHP warning on PHP 8.1
- improved: better handling of translations on plugin activation

### Version 3.1.3 (2022-03-01)

- fixed: default placeholder tags where not replaced on system mails
- fixed: security vulnerability where a logged in user can discover the profile URL from a different user. (discovered by D.Jong from patchstack.com)
- improved: ajax operations are now checked against capabilities
- improved: updated "Preheader text hack" from Litmus

### Version 3.1.2 (2022-02-09)

- fixed: CSS for WP 5.9
- fixed: small typo in variable
- fixed: compatibility for PHP 5.6+

### Version 3.1.1 (2022-01-19)

- fixed: time specific auto responders sent only on Sundays causes sending the following to be way in the future
- fixed: type in test bounce message
- fixed: typo in subscriber query causes database error
- fixed: PHP warning of undefined variable in option bar
- added: unsubscribe link to in mail app unsubscribe message
- added: filter `mailster_campaign_meta_defaults` to filter default meta values
- added: defined `wp_mail` filters are now applied if used with Mailster

### Version 3.1 (2022-01-12)

- new: Remove inactive Subscribers automatically
- new: Relative conditions for date fields
- new: filters in the subscriber overview
- updated: Manage Subscribers page
- fixed: add trailing space to preheaders to prevent unintentional line breaks in previews.

### Version 3.0.4 (2021-11-08)

- fixed: saving template from editor messed up template header
- fixed: bulk deletion with actions working again
- fixed: auto responder no longer triggered if post is published in the past
- fixed: shortcodes are now handled properly on the web version
- added: text strings for error messages defined by the security settings page
- improved: ajax handler

### Version 3.0.3 (2021-10-19)

- fixed: timeframe settings spanning over midnight
- fixed: layout issue on form/lists overview on smaller screens
- fixed: missing dbstructure method on queue process
- added: option to block and allow people from certain countries to signup
- update: using `get_user_local()` instead of `get_locale()` when applicable.

### Version 3.0.2 (2021-10-05)

- fixed: bulk options causes a subscriber query error.
- fixed: duplicating forms throw an error.
- fixed: some notifications missed template defined settings.
- change: optional warmup has been extended to 60 days.
- improved: database errors during cron tried to get fixed automatically.
- added: reminder to enable auto updates after a Mailster update.

### Version 3.0.1 (2021-09-20)

- added: editing templates on the templates page is back
- fixed: mergetags now work correctly in image URL field if fallback is present
- fixed: draft campaigns can now get duplicated
- fixed: install plugins on addon page is working
- fixed: problem if PHPMailer is loaded in another plugin
- fixed: installed templates were not access able when no required Mailster version was set
- improved: upgrade process from 2.4.x
- smaller bug fixes

### Version 3.0 (2021-09-01)

- new: Test the Email Quality with the built in Pre-check Feature.
- new: Tag you subscriber with Tags fro better segmentation.
- new: Improved security with a dedicate security settings page.
- new: Automatic batch size settings to calculate your optimal sending rate.
- new: Option to create new campaigns on action hook based auto responders.
- new: Updated add ons page to browse and install even more integrations.
- new: Updated templates page now lists over 400 free and premium templates.
- new: UI update with new icons based on SVG.
- new: Auto click prevention to prevent bots auto clicking and messing up your stats.
- new: Sending warmup if you send from a new IP address or domain.
- improved: db handling by splitting the actions table into five separate ones.
- improved: added primary keys to these tables: queue, subscriber_meta, subscriber_fields.
- improved: calculation of user rating has been offloaded as it's often server intense.
- improved: changes to tracking for the Apple Privacy Protection plans.
- improved: change on the random handler for random posts.
- added: new dynamic tag for button labels `{post_button:-1}`.
- added: indexes to campaigns to distinguish if multiple ones are sent (like birthday greetings).
- removed: auto update option to prefer native solution.
- removed: deprecated `mymail` hooks and filters.

### Version 2.4.19 (2021-06-14)

- fixed: Option bar is fixed after a certain scroll threshold again.
- fixed: decimals in the height or width field of the editbar for the selected image in the editor prevents submitting the form on Firefox.
- fixed: timeframe now respects sites timezone.
- fixed: export into Excel file may strip leading 0
- improved: test mails are now sent to the current user
- added: $org_content argument to `mailster_handle_shortcodes` filter
- added: `mailster_form_list_order` to handle list order in forms

### Version 2.4.18 (2021-04-14)

- fixed: comparing empty date values on MySQL 8
- fixed: issue while calculating time frame over midnight
- fixed: issue with merge tags when rss and regular tag is used
- fixed: use of hard-coded database prefix
- improved: removed/replaced deprecated jQuery methods
- improved: media editor link in settings
- improved: support for PHP 8

### Version 2.4.17 (2021-02-24)

- fixed: issue with subscriber button on some versions of Firefox
- fixed: problem downloading templates with special characters in filepath
- fixed: issue with manual sync button in Firefox
- fixed: post taxonomies are not respected for some autoresponder campaigns if the initial post status is publish
- fixed: issue creating images if content folder is outside of WordPress root
- fixed: empty values in form submission were not stored
- improved: URL hijacking mechanics to handle subdomains

### Version 2.4.16 (2020-12-21)

- fixed: warning on dashboard
- fixed: JS error in Firefox on WP 5.6
- improved: order by status now respects timestamps of campaigns.
- fixed: prevent URL hijacking by only allowing links from either the same domain or explicitly in the campaign.
- enabled: honeypot mechanism as the bug in Chrome has been fixed

### Version 2.4.15 (2020-11-25)

- added: option to remove subscribers with all assigned actions.
- improved: handling of folder names during template upload
- fixed: saving queued campaigns cause sending them immediately
- fixed: campaigns with no web version show in archive
- fixed: small JS issues

### Version 2.4.14 (2020-09-10)

- improved: internal handling for sending limits
- fixed: Display width specification for integer data types was deprecated in MySQL 8.0.17 which causes an error in the self test
- fixed: unwanted 'a11y-speak-intro-text' element in email body
- fixed: toggle behavior of meta boxes in WP 5.5
- fixed: missing content on custom dynamic post types without post ID

### Version 2.4.13 (2020-08-19)

- added: classes to settings rows
- added: option to handle short codes from the advanced settings tab
- added: support for `{attachment_image:XX}` to display images
- improved: handling of one click post requests for unsubscribes according to RFC8058
- fixed: auto update feature in WP 5.5
- fixed: do not localize variables
- fixed: error in PHPMailer (#2107) where exception is thrown when background attribute is empty
- fixed: multi site no longer share user meta data in conditions data between sub sites.

### Version 2.4.12 (2020-08-04)

- added: `mailster_inline_css` filter hook to disable auto inline css
- fixed: problem with third party shortcodes in excerpt
- fixed: several small bugfixes
- fixed: wrong counting on dashboard widget
- fixed: calculation of aggregated campaigns not accurate
- fixed: relative path in modules
- fixed: High DPI images on dynamic posts tags got wrong eight in some cases.
- improved: consistent behavior on handling shortcodes
- improved: Only the preheader text is shown in the email preview of email clients.
- improved: `mailster_preview_text_fix` filter to disable preview text fix.
- improved: automatically remove support accounts after one month after an update
- improved: you can now click on form fields to add them to the form
- improved: added missing aria labels

### Version 2.4.11 (2020-06-24)

- change: changing the email address on the profile sets status to pending and sends confirmation message if double opt in is enabled.
- change: the most recent notification is now displayed at the top.
- added: `mailster_register_dynamic_post_type` action hook to add custom dynamic post types.
- added: option to disable in-app-unsubscribe option
- fixed: issue where editor is not loading with some themes activated
- fixed: JS error if inline editor is not used
- fixed: people got unsubscribed if bounce address is the same as sign up notifications email.
- improved: User Agent handling.
- improved: pasting text from external source.
- improved: better support fro data-uris in style declarations.
- improved: better handling of sending HTML message with third party plugins
- removed: Gmail delivery option (fallback to SMTP)
- deprecated: use of Gmail via LSA as announced via Google

### Version 2.4.10 (2020-05-25)

- new: option to pick emoji for subject, preheader and from name
- improved: refactoring of JavaScript
- improved: refactoring of action based auto responders
- improved: campaigns in conditions are now ordered alphabetically.
- improved: content is now pasted as plain text instead of rich type
- fixed: creating campaigns with feeds which timeout causes empty autoresponders.
- fixed: redirection error on confirmation
- fixed: result of empty lists is no longer null
- fixed: links in iframe forms open now in parent window.
- fixed: issue where resuming a campaign on stats page is not possible.
- added: mailster_register_form_signup_field filter hook to modify signup checkbox on registration screen.
- added: more bulk options to campaign overview.

### Version 2.4.9 (2020-03-24)

- change: moved text strings for GDPR to text tab for better localization.
- change: sanitize_content method no longer handles custom Mailster styles (changes to the methods arguments)
- added: `mailster_add_tag` action hook to add custom tags.
- added: `mailster_add_style` action hook to add custom styles.
- improved: block tags removed in final output
- improved: RSS feed method for more flexible feed support.
- improved: tags now can return WP_Error object which prevents the campaign from sending.
- added: `mailster_gdpr_label` filter to change the content.
- fixed: empty strings on action hook based campaigns
- fixed: unchecked required checkbox prevents form fields page from saving.
- fixed: wrong less memory warning
- fixed: problem with CodeEditor on Avada
- fixed: missing inline styles on html elements from tags.

### Version 2.4.8 (2020-02-03)

- fixed: escaped content on edit screen.
- fixed: escaped several strings.
- improved: United Kingdom is no longer part of the European Union.

### Version 2.4.7 (2019-12-14)

- improved: CSS for WordPress 5.3
- improved: action type is now returned in form submission
- improved: form submission on errors
- fixed: content type selection not respected on dynamic insertion mode
- fixed: ERR_CONNECTION_RESET issue on some Apache installations

### Version 2.4.6 (2019-11-04)

- tested with WordPress 5.3
- fixed: potential XSS vulnerable on the subscribers detail page identified by Compass Security
- fixed: single quote in subject now correctly encoded
- fixed: confirm redirection issues on some installations
- change: Redirect after submit and Redirect after confirmation must be a URL

### Version 2.4.5 (2019-10-02)

- fixed: issue saving "send campaign only once" option
- fixed: duplication of finished campaign no longer breaks module selection
- improved: some fields are now stored urlencoded for better emoji support in databases with collation other than utf8mb4
- change: action hook auto responders no longer sent to all subscribers if subscriber id is set to `false` (use `null` instead)

### Version 2.4.4 (2019-09-09)

- improved: you can now encode tags output with an exclamation mark `{!mytag}`.
- improved: drag n drop images from your desktop now respects cropped image setting.
- improved: import screen.
- improved: test mails now fallback to the current users email if not defined.
- improved: subscriber query now search for ID as well.
- fixed: notifications to multiple addresses.
- fixed: modules without content sometimes preserve in the campaign.
- fixed: issue with RSS campaigns on time based autoresponders.
- fixed: wrong subscriber count if status "pending" in subscriber query.
- fixed: cumulative count calculations.
- fixed: redirecting issue with spaces in URLs.
- updated: to latest coding standards
- code refactoring

### Version 2.4.3 (2019-07-31)

- fixed: post_category tag now shows categories names again
- fixed: permalink issue with WPML add on
- improved: links from deleted campaigns end up in a 404
- improved: better visualization during module reordering in editor
- improved: menu icon is now a svg
- improved: loading posts in editbar
- improved: lang attribute now added for accessibility
- improved: accessibility in editor
- improved: plain text rendering

### Version 2.4.2 (2019-07-01)

- change: dummy image service domain
- improved: option to exclude taxonomies in dynamic tags
- improved: forms no longer use native validation
- fixed: remember usage tracking opt in setting
- fixed: correctly redirect after campaign duplication
- fixed: images from dynamic random post type in autoresponders
- fixed: subscriberID is now correctly populated in confirmation messages
- fixed: deprecated embed option

### Version 2.4.1 (2019-06-12)

- fixed: duplication of forms working again
- fixed: Subscribers are correctly connected if WordPress User is added later
- fixed: picpicker only showed 8 recent files
- fixed: unsubscribe auto responder works with list based subscriptions
- fixed: small JavaScript issues in the editor
- improved: better support for third party email with content type text/html
- improved: better checks for content related autoresponders
- improved: fixing broken settings automatically
- improved: database update checks

### Version 2.4 (2019-05-22)

- new: Use over 900K photos from Unsplash
- new: RSS to Email Campaigns.
- new: Random Post Tags
- new: Campaign-Subscriber related tags
- new: Custom Dynamic Post Types
- new: additional Form shortcode attributes
- new: Fresh UI
- new: Translation Dashboard Info
- improved: Preserved stats from deleted subscribers
- improved: Mailster now stores your email address when you send a test
- improved: Import Export

### Version 2.3.18 (2019-04-16)

- updated: included template
- added: option to use TLS on bounce servers
- fixed: missing module buttons after code edit.
- fixed: priority order in queue.
- fixed: single quote in subject now correctly encoded
- improved: handling of link mapping for multi byte characters.
- improved: handling if notification.html file is missing
- improved: better file sanitation on template uploads

### Version 2.3.17 (2019-03-19)

- added: `mailster_add_embeded_style` method to add custom embeded styles.
- added: option to enable sending usage statistics.
- fixed: issue with double quotes in background-image property
- fixed: correct saving of form option
- fixed: delivery issue with some third party apps
- improved: editor behavior when adding content in multiple areas.
- improved: styles added via `mailster_add(_embeded)_style` are now visible in the editor.
- improved: inline styles can now be skipped with an optional `data-embed` attribute
- improved: block comments now removed if present in the email
- improved: better action handling for mails opened on Yahoo
- improved: editor behavior
- improved: handling of options
- improved: removed some variables from the global space

### Version 2.3.16 (2019-02-12)

- new: option to choose original image in editbar (for animated gifs)
- fixed: wrong subscriber count in dashboard widget if assigned to multiple lists
- fixed: auto expanding of chart on dashboard
- fixed: title with quotes now escaped correctly
- fixed: calculation of images if height is set to “auto”
- fixes: template file selection on system mails are now respected correctly
- improved: wp_mail wrapper now supports to address in format name
- improved: wp_mail handling of reply-to, BCC and CC fields
- improved: DNS checks on settings page now asynchronous
- improved: subscribers now get removed from the queue if a bounce happens
- improved: better warning on import for pending subscribers
- improved: auto responder data now stored if campaign is saved as draft
- improved: handling of script tags during sanitation
- added: `mailster_allowed_script_domains` and `mailster_allowed_script_types` filter hooks
- added: condition “is in list” for better segmentation
- added: option to re test a test

### Version 2.3.15 (2018-12-05)

- fully tested on WordPress 5.0
- fixed: missing icons in WordPress 5.0
- fixed: prevent tracking on test mails
- fixed: relative width attributes now preserved in the editor
- fixed: issues with wp_mail if reply_to is an array
- improved: better checks if `wp_mail` is defined by another plugin

### Version 2.3.14 (2018-11-05)

- fixed: missing fallback on custom fields
- fixed: undefined bodyElement in editor.
- added: `sub_query_limit` to process subscriber query in chunks for very large subscriber base
- added: display count of selected subscribers on delete page
- added: test for wp_mail
- added: more date form options on export
- improved: Gravatar as source is no longer shown if the source is not Gravatar
- improved: wp_mail handling for third party plugins
- improved: raw header parser for wp_mail
- improved: external forms now embedded via dedicate URL
- improved: query on subscriber overview page
- improved: translated roles in conditions view
- improved: searching subscribers now highlights search term.

### Version 2.3.13 (2018-10-10)

- fixed: Max execution time error message pops up randomly on some servers.
- fixed: issue with certain post types and multiple underscores.
- fixed: PHP error on form duplication.
- fixed: issue with nested embed styles.
- fixed: smaller issues.
- improved: query for location based segmentation.
- added: `mailster_get_post_list_args` filter for static posts.
- added: `mailster_autoresponder_grace_period` filter.

### Version 2.3.12 (2018-09-19)

- fixed: height attribute of image tags were not always respected.
- improved: tag replacement handling
- improved: list order in overview
- improved: queue handling of time based auto responders
- improved: query for dashboard widget
- improved: sql query

### Version 2.3.11 (2018-08-28)

- fixed: added “source” tag in allowed tags
- fixed: sql query issue on “(didn’t) clicked link” condition
- fixed: smaller issues
- fixed: unsubscribe issue on single opt out if user is logged in
- fixed: subscriber export on sites with CloudFlare
- improved: custom tags are now replaced in the final campaign and no longer when created
- improved: privacy policy link gets updated if the address changes
- improved: subscriber query now has the campaign id as second argument.
- improved: nonce form handle
- added: `wp_include` and `wp_exclude` for subscriber query to handle WP user ID’s
- added: condition “(didn’t) clicked link” now allows to choose a certain campaign
- added: additional aggregated campaigns

### Version 2.3.10 (2018-07-25)

- new: you can now use `[newsletter_profile]` and `[newsletter_unsubscribe]` everywhere where short codes are accepted
- fixed: array_map warning in wp_mail wrapper
- fixed: honeypot was pre-filled on Google Chrome with autofill
- fixed: Some tags where not displayed on notifications
- fixed: Gravatar changes on third party apps were not respected
- fixed: error if location database is missing
- fixed: tags in links causes a protocol removal
- fixed: smaller issues
- improved: better support for mailster_subscriber of third party apps with wrong data type
- improved: show stats on campaign overview if heartbeat API is disabled (no live reload)
- improved: better handling of inline styles for subscriber buttons
- disabled: honeypot mechanism to prevent Chrome browsers to fill out the honeypot field

### Version 2.3.9 (2018-07-04)

- fixed: manage subscribers with no list assigned included users within a list
- fixed: some JS issues on IE 11
- fixed: IP addressed not stored on form submission
- fixed: not able to remove attachments
- fixed: wp_mail not working if receivers is not an array
- fixed: webversion tag was not displayed if campaign hasn’t been saved yet
- fixed: redirection issue if baseurl contains query arguments
- fixed: button is no longer available on the unsubscribe form with single opt out
- added: `get_last_post` now includes subscriber and campaign id
- added: option to enable custom tags on web version

### Version 2.3.8 (2018-06-05)

- fixed: caching issue on tags in subject line
- fixed: subscriber based autoresponder if “lists do not matter”
- new: Condition: GDPR Consent given
- added: meta data can now get exported
- added: `mailster_subscriber_rating` filter
- change: ratings now updated via cron to reduce server load on large databases

### Version 2.3.7 (2018-05-24)

- new: option to add GDPR compliance forms on the privacy settings page.
- added: search field for modules
- added: `mailster_profile_form` and `mailster_unsubscribe_form`filter
- added: information to privacy policy text in WordPress 4.9.6
- added: added Mailster data to Export Personal Data option in WordPress 4.9.6
- added: added Mailster data to Erase Personal Data option in WordPress 4.9.6
- fixes: various small bugs

### Version 2.3.6 (2018-05-14)

- new: Location based Segmentations
- new: filter: `mailster_form_field_label_[field_id]` to alter the label of form fields
- improved: simplified location based tracking with auto update
- improved: Export page now offers conditional export and saves defined settings.
- improved: Delete page now offers conditional deletion.
- change: active campaigns are now included in aggregated items in conditions
- fixed: odd offset issue on hover in editor
- fixed: importing emails with single quotes
- fixed: JS error when switching back from codeview with no head section
- fixed: do not redirect after unsubscribe
- fixed: removing a user from a blog on a multi site now correctly removes subscriber

### Version 2.3.5 (2018-05-01)

- fixed: list assignments for some third party add ons
- fixed: small bug fixes
- fixed: changes were not saved if only modules were rearranged
- fixed: ajax requests not working in some browser environments
- fixed: improved display of subscribers overview page with many custom fields
- fixes: export of subscribers not working on some servers
- added: more tests
- change: display Self Test menu entry if `WP_DEBUG` is enabled

### Version 2.3.4 (2018-04-20)

- fixed: prevent style blocks moved to body tag
- fixed: buttons no longer get removed after click on cancel
- fixed: Outlook conditional tags were removed
- fixed: body attributes added via codeview are now preserved
- fixed: small bug fixes
- improved: better error handling on export
- improved: more info for list confirmations
- added: bulk option to confirm subscriptions
- added: `{lists}` tag is now working in confirmation messages

### Version 2.3.3 (2018-04-11)

- fixed: pages were not editable
- fixed: error if `wp_get_attachment_metadata` returns false
- fixed: autoresponder query issue
- fixed: small bug fixes

### Version 2.3.2 (2018-04-10)

- fixed: pagination on subscribers overview page
- fixed: profile for logged in users working again
- fixed: confirmation message was sent on single opt in
- fixed: subscribers detail page sometimes empty
- fixed: missing images on some third party templates

### Version 2.3.1 (2018-04-05)

- fixed: error: Can’t use function return value in write context
- improved: display info if module has no label

### Version 2.3 (2018-04-04)

- new: option to hide the Webversion Bar
- new: option to disable tracking on campaign based basis
- new: option to disable user avatars
- new: time frame based delivery for campaigns
- new: Mailster test suite to test compatibility
- new: option to crop images in the picpicker
- new: elements can now expect fields in templates with``
- new: option to disable Webversion bar
- new: option for list based subscription
- new: subscriber query class for better list segmentation
- new: cron command page
- new: `{lists}` tag to display campaign related lists
- new: `mailster_option` and `mailster_option_[option]` filter
- new: Export format: xls
- new: Option to duplicate forms
- new: Option to disable Webversion
- new: privacy settings page
- change: `mailster_replace_link` now targets the output link
- improved: list segmentation
- improved: campaign editor for faster campaign creation with inline editing
- improved: modules with tags where the post not exists will get removed
- improved: image procession to support more third party plugins
- improved: info message on form submission now placed on after the form depending on scroll position.
- improved: background images behavior in editor
- improved: faster editor behavior
- improved: batch action on subscribers
- improved: multiple cron processes
- improved: image creation process to better support third party plugins
- improved: cron mechanism
- improved: export column selection
- improved: handling of placeholder images on td, th and v:fill
- added: copy-to-clipboard functionality
- added: subscriber crows indicator on dashboard widget
- added: Additional mail headers
- added: option to release cron lock
- added: option to reset cron last hit
- updated: PHPMailer to version 5.2.26
- deprecated MyMail methods

### Version 2.2.18 (2018-02-02)

- fixed: problem with slashes in head section during test campaign
- fixed: problem on template save
- fixed: display issue on color pickers in editor

### Version 2.2.17 (2018-01-19)

- added: optional caching parameter for forms
- change: sanitize_content no longer uses stripslashes

### Version 2.2.16 (2017-12-22)

- fixed: Double id on settings page
- fixed: conflict with some third party delivery plugins
- fixed: editor did not expand to correct height on some third party templates
- improved: Excerpt and Content of posts look the same no matter which type of embedding were used

### Version 2.2.15 (2017-12-11)

- fixed: visual issue with color picker on WP 4.9
- fixed: lists were always deleted from the manage subscribers tab
- fixed: issue with external image URLs not saving
- fixed: height not updated if image url is used in editor
- fixed: issue with Gravatar URLs
- improved: scrolling speed in editor
- improved: list query
- improved: Caching is now disabled on any page with a form
- improved: newsletter homepage selector on setting with sites over 100 pages
- improved: translation fetching

### Version 2.2.14 (2017-11-15)

- fixed: ratings with ‘%’ in segmentation causes not expected behavior in WordPress 4.8.3+
- fixed: missing escaping in segmentation rules where LIKE is used
- fixed: get next date on weekly autoresponder if weekday is not the current one
- fixed: missing form field on autoresponder overview
- fixed: updating third party templates via templates page
- updated: included template to 6.1

### Version 2.2.13 (2017-10-24)

- fixed: issue with stripped tags on template save
- fixed: resending of transactional mails
- fixed: registration not stored if username was missing
- fixed: bounce test failed on some servers
- added: additional security steps on form submission
- added: option to recheck for updates on the dashboard

### Version 2.2.12 (2017-10-05)

- fixed: editbar position issue on Chrome 61
- fixed: background image not editable if its the first of the template
- improved: placeholder image handling
- improved: loading of user meta values
- removed: deprecated jQuery methods

### Version 2.2.11 (2017-09-18)

- improved: translation checks and loading
- improved: loading of translations
- fixed: assigned unchecked lists on form submission if user choice is enabled
- fixed: PHP warning on bounce handler
- added: `remove_custom_value` method to remove meta values of subscribers
- added: Mailgun delivery option to setup

### Version 2.2.10 (2017-09-01)

- fixed: Editor button now only available in the backend
- fixed: Excerpts were missing when view mode is Excerpt View
- fixed: missing `wp_get_raw_referer` on WP < 4.5
- fixed: choosing default values for dropdowns and radio custom fields
- fixed: multiple attached attachments
- fixed: plain text option wasn’t respected during test campaigns
- improved: lists assigned to a form are now respected if form id is set explicitly on subscriber submission
- added: `mailster_campaign_content` filter to alter the content of campaigns
- added: `mailster_using_permalinks` filter

### Version 2.2.9 (2017-08-08)

- added: support for the SparkPost add on
- fixed: some tags with alternative content were not replaced when sending a test campaign
- fixed: module screenshots returned error if more than 30 modules in template
- improved: unsubscribe action can now contain a status for more info
- change: send method now returns internal message ID
- change: test mails to unknown email addresses are no longer assigned to the current user to prevent false mailbox actions

### Version 2.2.8 (2017-07-31)

- fixed: radio and dropdown values weren’t populated on profile in some cases
- fixed: wp_mail now supports coma separated emails if used by Mailster
- fixed: PHP notice with autoresponders on PHP 7.1
- fixed: link for buttons were pre filled with the URL from the previous selected button
- fixed: PHP notices on Cron lock
- fixed: issue with defined constants if GEO library is loaded in a third party plugin
- fixed: display issue of emojis in tinymce of multi elements
- fixed: link of images wasn’t populated correctly
- fixed: reading filesize on missing file during export
- improved: ever re-signup will respect the forms double-opt-in setting
- improved: using SQL_CALC_FOUND_ROWS on subscribers overview to speed up queries
- improved: form profile compatibility with certain themes
- improved: get referer on form signup
- improved: pre cache queries on autoresponder overview
- added: option for legacy POP3 method on bounce settings
- added: `mailster_update_option_*` filter to alter option on save
- added: `mailster_get_signups_sql`, `mailster_queue_campaign_subscriber_data` filters
- added: `mailster_cookie_time` filter to adjust Mailster cookie expiration time
- added: `mailster_get_current_user` and `mailster_get_current_user_id` methods
- change: `mailster_unsubscribe_link` hook position and added campaign_id to arguments

### Version 2.2.7 (2017-06-14)

- fully tested on WordPress 4.8
- fixed: exporting subscribers
- fixed: strip slashes on list descriptions
- fixed: SQL issue when unassign lists from subscribers
- fixed: encoding issue while saving campaigns on some servers
- improved: display Mailster username on Dashboard
- improved: removed usage of `create_function` for PHP 7.2
- improved: better sanitation and checks on date fields
- added: `mailster_keep_tags` filter to keep tags
- change: some default values on plugin activation

### Version 2.2.6 (2017-05-29)

- fixed: checkboxes were always checked by default
- fixed: status info on user time based auto responder
- fixed: save settings button not enabled in some cases
- fixed: duplicating of other campaigns without capabilities
- fixed: spelling mistakes
- improved: html tags in custom field names
- improved: compatibility with caching plugins
- improved: excerpts are now generated if not defined via more tag or explicit
- improved: loading fall back if notification.html is missing
- improved: removed redundant white spaces in plain text versions
- improved: links in plain text version are now grouped together below the content
- improved: compatibility with third party templates
- improved: CSS rules for RTL languages
- added: `mailster_get_last_post_args` filter to alter post arguments
- added: month to user time based autoresponder time frames
- updated: templates page

### Version 2.2.5 (2017-04-28)

- change: Signup date checkbox on Mange Subscriber Page now checked by default
- fixed: Thickbox dimensions on form detail page
- fixed: selecting static posts working again
- fixed: issue on recipients detail page since last update
- fixed: Newsletter sign up widget didn’t store empty title
- fixed: wrong excerpt on web version with dynamic tags in some cases
- updated: PHPMailer to version 5.2.23
- improved: Cron tab commands
- improved: Cron now supports secret via a header
- improved: URL rewrite support option on settings
- overall improvements

### Version 2.2.4 (2017-04-18)

- fixed: adding attachments not possible on Firefox
- fixed: Subscriber ID was cached on custom dynamic tags in some cases
- fixed: converting links on single elements no longer pre filled if link is not set
- fixed: smaller bugs
- updated: order by “Clicks” is now “Click Date” in recipients details view
- improved: all widgets are now wrapped by a div with class “mailster-widget” for better targeting
- new: option to get subscribers by md5 hash
- new: “mailster_subscriber_hash” filter to change subscriber hash
- improved: added various filters to list and subscribers view
- improved: support of arrays in auto post tag filter
- improved: allowing anonymous functions in `mailster_add_tag`
- improved: allowing anonymous functions in `mailster_add_style`
- improved: subscriber caching
- improved: loading of widgets

### Version 2.2.3 (2017-03-24)

- fixed: issue with custom editor buttons are not displayed
- fixed: assign subscribers to lists now correctly removes old assignments
- fixed: unescaped apostrophe on test mails
- fixed: small bugs
- improved: access to form.php and cron.php if location not default
- improved: third party templates support on PHP 7.1
- improved: cron mechanism
- improved: cron job settings page

### Version 2.2.2 (2017-03-15)

- added: option to add link to your logo
- fixed: issue in segmentation if WP user meta field matches a reserved Mailster fields
- fixed: changing order of WP dashboard widgets wasn’t stored
- improved: checks for path if plugin directory is not at it’s default locations
- improved: dismiss option on dashboard notifications
- improved: update mechanism

### Version 2.2.1 (2017-03-06)

- fixed: bounces weren’t handled correctly in some cases
- fixed: styles were not applied correctly while sending tests
- fixed: Subscriber import with coma separated lists causes that new created lists were not assigned correctly
- fixed: wrong DKIM record on some installations
- fixed: error output on wrong formatted HTML in excerpts
- added: option to export ratings
- improved: URLs for some social services
- improved: backwards compatibility
- improved: datepicker is no longer triggered on datefields if operator is a regular expression

### Version 2.2 (2017-02-23)

- new: Mailster Dashboard
- new: Setup Wizard
- new: Editor Button to quickly add common tags to your campaigns
- new: Editor Button to add forms to your post and pages
- new: Templates Settings allow you to define default logo and social media links
- new: add attachments to your campaigns
- new: Bounce servers now supports IMAP servers
- new: Bulk Actions for subscribers can now process all subscribers
- new: a screenshot.jpg file can now be used for the screenshot in templates folder
- new: Manage Settings to export and import your settings
- new: receivers email address now contains full name of subscribers
- new: tag: `author`. usage `{post_author:-1}`
- new: Subscriber Button Widget
- new: share service VK.com, Telegram, Whatsapp
- new: localization hub: translate.mailster.co
- new: `mailster_excerpt_length` let you define the length of the excerpt used in your campaigns
- improved: sending queue
- improved: loading of language files
- improved: better ordering of lists
- updated: The included template has been updated
- change: background images are now located in the uploads directory
- change: settings are now in the plugins menu

### Version 2.1.33 (2017-02-09)

- change: Segmentation now works on NULL field values if condition value is an empty string
- fixed: unexpected error messages causes by wrong notices formats
- fixed: module screenshots were not displayed caused by missing quotes

### Version 2.1.32 (2017-01-16)

- security: updated included PHPMailer version to 5.2.22.
- improved: cron lock handler with external cron services
- fixed: problem uploaded zipped template files in WP 4.7.1
- fixed: missing quote on fsockopen warning notification

### Version 2.1.31 (2016-12-29)

- security: updated included PHPMailer version to 5.2.21. Older versions have been removed.

### Version 2.1.30 (2016-12-27)

- security: updated included PHPMailer version to 5.2.19. Older versions are now deprecated.
- fixed: issue where pages are not available for the newsletter homepage

### Version 2.1.29 (2016-12-19)

- tested with WordPress 4.7
- updated: DNS settings string for DKIM
- fixed: un-assigning all lists from a subscriber works again
- fixed: receivers box showed missing WP meta fields
- fixed: merge on import didn’t work on some servers

### Version 2.1.28 (2016-11-21)

- added: “mymail_sanitize_content_body” filter to filter the the body of the html
- change: default DKIM bitsize from 512 to 1024
- fixed: database error when using regular expressions on user meta fields
- fixed: PHP notices on list update if no form exists
- fixed: issue on import with missing lists
- fixed: db query issue on cron lock mechanism
- fixed: issue storing private hash key on some sites

### Version 2.1.27 (2016-11-07)

- added: option to save campaigns as draft
- added: option to choose lists in columns on import
- added: option to toggle list selection on export and deletion page
- added: option to delete template file from the preview pane
- added: option to automatically repair broken options
- added: List-Unsubscribe header to test mails
- change: “Send now” and “Resume” on the edit page now save changes
- update: better abbreviation for million subscribers in subscriber button
- fixed: issue while creating template screenshots on some installations
- fixed: issue with updating and downloading templates

### Version 2.1.26 (2016-10-10)

- change: excerpt now handled globally for a constant appearance
- added: excerpts and can get filtered via `mymail_get_excerpt` and `mymail_pre_get_excerpt`
- update: Update class
- added: option to define authentication method for SMTP delivery option
- added: option to allow self signed certificates for SMTP delivery
- improved: error handling on export page
- removed: PHPMailer version 5.2.7 (auto transfer)
- fixed: encoding issue on subscriber upload with special characters

### Version 2.1.25 (2016-10-03)

- added: compatibility checks on plugin activation
- fixed: import with special characters on non UTF8 database charsets
- fixed: issue with non latin character languages on weekdays
- fixed: compatibilities issues on PHP 7
- fixed: problem with missing mb_string methods

### Version 2.1.24 (2016-09-25)

- added: option to add DKIM keys manually
- added: option to add Google Maps API Key
- added: latest PHP Mailer version
- fixed: WP users to Subscriber sync failed in some cases (email only)
- fixed: bulk deletion of subscribers didn’t delete WordPress Users if selected
- fixed: paragraphs were not recognized in newsletter short codes
- fixed: segmentation on user roles sometimes not include all subscribers
- fixed: WordPress User import on multi site only imports users from current site
- fixed: User Avatar were not displayed correctly on some installations
- updated: third party libraries

### Version 2.1.23 (2016-09-08)

- improved: test mails and bounce tests no longer require saving settings
- improved: better error reporting on bounce server tests
- improved: handling of DKIM keys creation
- added: option to select separator on subscriber export
- updated: loading image
- updated: strings
- fixed: test failed on some bounce servers
- fixed: headers sent error on form overview page
- fixed: issue with iframes on newsletter archive page

### Version 2.1.22 (2016-08-17)

- fully tested with WP 4.6
- change: prefer `wp_get_referer()` over ` $_SERVER[``HTTP_REFERER``] `
- fixed: new template file were created in default template path
- fixed: issue with html encoded characters in from name subject and preheader
- drop active support for WordPress 3.5

### Version 2.1.21 (2016-08-09)

- improved: cron lock mechanism
- improved: settings form submission
- improved: update error information
- added: option to change author of campaigns
- fixed: PHP warnings on queue
- fixed: wrong encoding on Gravatar urls
- fixed: DNS query issue on some hosts
- change: `mymail-` prefix for asset handles for forms
- change: `mymail_` prefix some GET variables
- removed: edit link on front page for forms

### Version 2.1.20 (2016-07-25)

- fixed: editor not responding if content is empty
- fixed: thumbnails are not generated for some third party templates
- fixed: issue were editor is not displayed on PHP <= 5.5
- improved: some sql queries
- improved: caching of lists
- improved: DNS checks
- removed: redundant fields on system info page

### Version 2.1.19 (2016-07-19)

- improved: cron lock mechanism
- improved: wording on cron lock issues.
- improved: module thumbnail generation. better quality and works now locally
- fixed: deleting templates now removes screenshots and thumbnails
- fixed: issue were settings of add ons are not saved

### Version 2.1.18 (2016-07-14)

- fixed: duplicate emails were sent on certain conditions
- fixed: thickbox dimensions on campaign edit page
- fixed: wrong system info page line

### Version 2.1.17 (2016-07-11)

- applied WordPress Coding Standards to all files. Change class names from underscore_case to CamelCase
- moved settings tabs into dedicated files for better maintainability
- new: filters: for getting static posts in the editbar: `mymail_auto_post`
- new: filters: for getting static rss in the editbar: `mymail_auto_rss`
- fixed: JS verification for emails on subscriber details page for “new” emails
- fixed: removing fields on form page was broken on FireFox

### Version 2.1.16 (2016-06-22)

- new: feature: post thumbnails for campaigns
- new: feature: auto generated screenshots for campaigns
- new: feature: webversion now offers oembed support
- new: feature: open graph meta data on webversion
- new: share service: Buffer
- fixed: password input field
- updated: templates previews
- improved: loading of GEO database files
- improved: share services popup on webversion
- fixed: issue with autosave
- fixed: html tags were stripped out on `{post_excerpt}` tag
- small bug fixes

### Version 2.1.15 (2016-06-18)

- new: modal window for saving template files
- fixed: issue on system info page
- change: referer for new subscribers is now `wp_get_referer()` by default

### Version 2.1.14 (2016-06-13)

- new: New Subscriber Notification Options
- change: unsubscribe form now has the custom styles from the form
- removed: using array association for `wp_remote*` results
- prevent warnings on invalid html templates

### Version 2.1.13 (2016-06-04)

- improved: support for RTL on all admin pages and subscriber button
- added: added `mymail_frontpage_logo_link` to alter the link to the homepage on the front page
- fixed: wrong order of names in detail view of subscribers
- fixed: links were not mapped correctly when used in wp_mail in some cases

### Version 2.1.12 (2016-06-03)

- added: `[newsletter_subscribers]` now supports `lists` attribute to limit count to certain lists
- added: option to define the name order for countries/languages where last name is before first name
- improved: better fall back in update class on unsupported SSL
- improved: better placement for “Use it!” option on forms

### Version 2.1.11 (2016-04-18)

- fully tested with WP 4.5
- improved: links in the editor of finished campaigns are opened in a new tab
- improved: mapping of links when used with `wp_mail`
- fixed: some themes overwrite icon files
- fixed: invalid SQL query on some notifications
- prevent PHP Warnings

### Version 2.1.10 (2016-03-03)

- remove languages from core plugin [read more](https://kb.mailster.co/translations-in-mailster/)
- improved: text handling
- improved: handling of referrers
- fixed: server error on test mails if sending fails
- fixed: meta data with % caused invalid SQL statement

### Version 2.1.9 (2016-02-29)

- improved: better sanitation of template html to prevent unsupported tags like ``are saved.
- improved: template coping on activation
- fixed: editor included multiple modules if switched back from codeview
- fixed: missing assets on lists detail page
- fixed: PHP warnings on templates page
- fixed: issue during list merge when subscriber is assigned to both lists

### Version 2.1.8 (2016-02-22)

- improved: template editing on the templates page
- change: test emails on campaign overview are now sent independently if more emails where added.
- fixed: unsubscribe button missing if single sign out is enabled
- fixed: wrong links to assets
- fixed: issue with background images not changed probably in some browsers

### Version 2.1.7 (2016-02-17)

- fixed: SMTP connect() issue on some servers caused by`got_url_rewrite` method
- fixed: PHP warning on settings page on first save and on frontpage
- removed: unused PHPMailer library
- added: Thai and Japanese languages

### Version 2.1.6 (2016-02-16)

- improved: use of `template_redirect` hook to prevent issues with third party plugins
- improved: activation processes
- fixed: check for wp_error in create_image method to prevent error output
- fixed: broken styles in codeview

### Version 2.1.5 (2016-02-12)

- added: edit form link on frontpage to quickly access form settings
- improved: cleanup after custom field gets removed
- improved: handling of newsletter homepage
- improved: handling of redirection
- improved: unzipping third party templates
- change: web version no longer applies `the_excerpt` filters to prevent output of style information with some themes
- fixed: sanitize archive slug

### Version 2.1.4 (2016-02-11)

- fixed: issue where some people were not able to unsubscribe

### Version 2.1.3 (2016-02-05)

- improved: handling external (embedded) forms on the site where MyMail is installed.
- improved: template files selection from the editor allows now scrolling when many files are present
- change: forwarding via frontpage now uses the email defined in the settings to prevent conflict with third party ESPs.
- fixed: issue with the7 theme by DreamTheme
- fixed: missing content on unsubscribe page
- fixed: issue on RTL sites with forms
- fixed: comment in PHPMailer class causes security concerns
- fixed: issue when creating custom fields

### Version 2.1.2 (2016-02-04)

- added: option to select different phpMailer version
- added: better info for archive page in the settings
- added: option to define archive slug
- added: option to disable thumbnails for modules
- improved: they way module thumbnails are displayed
- improved: handling of archive page on some themes
- improved: templates page and handling for template activation

### Version 2.1.1 (2016-02-01)

- added: option to change custom field ids
- fixed: issue when getting templates on some servers
- fixed: labels of custom fields are now respected on forms
- fixed: issue where content is used instead of excerpt
- fixed: issue when unsubscribe is custom and newsletter homepage is the frontpage

### Version 2.1 (2016-01-26)

- new: update background images in campaigns like regular ones
- new: subscribe button
- new: time based auto responders can now get triggered when a certain amount of posts have been published
- new: improved dashboard widget
- new: single-sign-out
- new: do-not-track feature
- new: subscriber rating
- new: add “mymail_ignore” post value to bypass post in campaigns
- new: option to get notification on unsubscribes
- new: frontend social icons
- new: shortcode for subscribers count
- new: premium template activation and update right from the templates page
- new: new custom field type: textarea
- new: added honeypot to forms
- new: receivers segmentation ratings, forms and referer
- new: receivers segmentation by regular expressions
- new: Distraction-free-writing similar to WP natives feature
- new: bounce handling of transactional mails like notifications
- new: allow users to update their data via any form
- new: option to delete WordPress User if Subscriber gets removed
- new: tags `{post_category[term]:-1}` displays a list of terms
- new: webversions now respect “Search Engine Visibility” settings
- improved: campaign editor
- improved: view on mobile devices
- improved: handling of object caches
- change: forms have now their matching field name in name attribute (backward compatibility given)
- change: tabindex on forms removed
- change: remove option “embed_form_css”
- change: forms now always ajax based if JavaScript is enabled
- change: only post_status of `finished` and `Active` on the archive page
- change: required class changed from “required” to “mymail-required” on forms to prevent conflicts with other plugins

### Version 2.0.34 (2016-01-14)

- fixed: unsubscribe page didn’t work in some cases cause of the fix in 2.0.33

### Version 2.0.33 (2016-01-08)

- hotfix: [#35031](https://core.trac.wordpress.org/ticket/35031) cause problems when newsletter homepage is frontpage

### Version 2.0.32 (2016-01-08)

- fixed: wrong redirections on welcome page with ceratin caching plugins
- fixed: issue with ACF plugin and others where the editor was empty

### Version 2.0.31 (2015-12-22)

- update: update class
- added: info for MyMail 2.1 beta

### Version 2.0.30 (2015-11-20)

- fixed: typo when get default timezone
- fixed: headings in WP 4.4
- fixed: Strict Standards warning
- fixed: error handling on form submit
- fixed: warnings on missing fsock_open
- added: new tag `{post_shortlink:X}` which uses `wp_get_shortlink()`

### Version 2.0.29 (2015-11-10)

- fixed: PHP notice
- fixed: wrong local issue
- fixed: updating wp-cron interval now actually updates the current cron interval
- fixed: force wp-cron if not triggered for over an hour
- fixed: status were set to “pending” if imported with merged option and ignore status
- fixed: issue with certain user time based autoresponders in different timezones
- fixed: wrong calculation of next schedule if current is in the past for more than 48 hours
- fixed: caching issue with some object cache plugins
- fixed: issue with undefined signup date on import
- fixed: error in queue when custom field count is &gt; 55
- update: using {post_image:123} == {attachment_image:123} if 123 is an image ID

### Version 2.0.28 (2015-09-19)

- fixed: plaintext view was visible on active camapings in WP 4.3
- added: notice for users with PHP version < 5.3

### Version 2.0.27 (2015-08-31)

- fixed: XSS vulnerability on backend – Thanks to swte!
- fixed: Missing CSS on welcome page
- updated: database structure

### Version 2.0.26 (2015-07-04)

- fixed: Missing image variable on addons page
- fixed: optimize tables run on mymail_cron
- fixed: profile uses correct form on update
- fixed: custom field data gets deleted on user profile update (#3)
- fixed: exporting status was missing
- fixed: sync user meta via `user_meta_update` works now correctly
- fixed: dashboard widget shows CTR while it should show ACTR (Adjusted Click Through Rate)
- fixed: curly brackets for tags where urlencoded in used templates which prevents tags in urls from working
- fixed: issue with some WordPress User meta data in receivers segmentation
- fixed: empty form array
- fixed: some PHP notices
- fixed: list of campaigns in follow up autoresponder now ordered by status
- added: option to export status code
- added: new language: Hebrew

### Version 2.0.25 (2015-06-10)

- improved: pasted urls get trimmed to prevent trailing or leading whitespace in the editbar
- update: bounce classes to get rid of some deprecated methods
- update: update class to represents WordPress like behavior
- fixed: missing loading spinners in campaign edit screen
- fixed: php notice in queue class
- fixed: update didn’t show supported versions and other meta data
- fixed: finishing paused campaigns is now possible as well
- fixed: missing subscriber meta field names on receivers meta box on finished campaigns
- fixed: mail headers should always be strings
- change: aspect ratio is now calculated from the original image
- change: relative tags (eg `{post_title:-1}`) in finished campaigns get now converted to absolute like in autoresponders

### Version 2.0.24 (2015-05-24)

- improved: visual clarity in receivers condition
- updated: welcome page
- fixed: ajax update of plugin in list view (WP 4.2+)
- fixed: invisible plain text textarea
- fixed: welcome mail not empty anymore
- fixed: {issue} tag now works in placeholder and from name
- fixed: autoresponder with post tags get populated even if no post tags were applied
- fixed: issue with custom hook autoresponders only get sent once
- fixed: wrong redirection after bulk action in subscribers and list view
- fixed: autoresponder are triggered more than once in rare cases
- fixed: database tables were not created on plugin activation
- added: debug info for test mails via SMTP

### Version 2.0.23 (2015-04-15)

- improved: handling of timezones with names of locations instead of offset
- fixed: issue with WP 4.2.2
- fixed: images of RSS feeds now display correctly again
- fixed: profile update with non selected lists works
- fixed: removed unwanted classes from images in the editor causing breaking the responsiveness in some third party templates
- fixed: issue in editor with third party templates

### Version 2.0.22 (2015-01-15)

- fixed: security issue on forms
- improved: response time on editbar
- improved: correct schema on forms

### Version 2.0.21 (2014-12-24)

- added: option to import WordPress users without a role
- added: option to finish active and paused campaigns manually
- added: check if permalinks not working correctly
- improved: removed loading of some unnecessary graphics in WP &gt;3.8
- improved: queue handling now takes less memory and time
- improved: better handling of html tags in form field labels
- change: time based autoresponder now gets queued 1h before when user based timezone is unchecked)
- change: forms are now disabled on submit and cleared on success
- fixed: issue with language
- fixed: issue with tracking links when permalinks are default
- fixed: confirmation now send for unsubscribed subscribers which sign up again
- fixed: issue with latest post if category is different
- fixed: creating segments with some characters failed

### Version 2.0.20 (2014-12-05)

- added: aria-\* attributes in form elements for better accessibility
- added: option to define the form for profiles on the form settings page
- added: `mymail_post_submit` filter for third party plugins
- change: list selection is no longer required on signup forms
- change: logged in users can now update their subscription on the profile page without clicking a link in a campaign
- fixed: remove excerpt from the overviews page
- fixed: forward by mail is working again correctly
- fixed: WP users to subscribers issue fixed
- fixed: return to Add Ons Page link after plugin installation removed
- fixed: issue with conditions on date fields other than custom fields

### Version 2.0.19 (2014-11-24)

- change: links in test mails are now mapped like in real campaigns
- change: user time autoresponder now get inactive if date field gets removed
- change: user time autoresponder now requires a date field
- improved: better handling of ALT attributes in editor
- improved: editor performance
- improved: better handling of custom dynamic tags
- added: date field have now a “datepicker” class on the frontend
- added: `mymail_verify_subscriber` filter for third party plugins
- added: `mymail_verify_list` filter for third party plugins
- added: `mymail_update_subscriber` action hook
- fixed: user time autoresponder now correctly handle dates with different daylight saving times (DST)
- fixed: timezone based sending related to DST
- fixed: British time format is now supported correctly (date format settings must be d/m/Y)
- fixed: issue when visual editor is disabled causing a page reload

### Version 2.0.18 (2014-11-14)

- added: option to delete subscriber if it’s WordPress user gets deleted
- fixed: new subscriber information were sent twice in some cases
- fixed: displayed checkbox on registration page without option
- fixed: removed main menu when no submenu or users have no capabilities
- improved: better routine to determine if it’s registration or a third party addition
- fixed: issue with tags resolved

### Version 2.0.17 (2014-11-10)

- change: totals now doesn’t include bounces by default
- added: overview displays now click-through-rate (CTA) and adjusted click-through-rate (ACTR)
- added: overview displays now unsubscribe-rate and adjusted unsubscribe-rate (ACTR)
- added: option to remove template files on the templates page
- improved: template file savings
- fixed: notifications for new subscribers are now sent when double opt in is disabled
- fixed: bug when changing background image link causing an injection of all modules
- update: export uses now WP_Filesystem class
- fixed: replyTo address are now reseted correctly

### Version 2.0.16 (2014-11-04)

- updated: bounce handler class to 2.7.4
- added: subscribers-&gt;add method has now a merge option
- added: mu-mymail-cron.php for the mu-plugins folder to get a real corn working with path problems
- fixed: path issue in newsletter-script.js
- fixed: some division by zero warnings
- fixed: some third party plugins causes problems with used WP_Query
- fixed: “&amp;” were converted to “&amp;” on some servers
- fixed: public class was private in mail class
- fixed: wrong notification text in news subscribers notification
- improved: wp cron trigger

### Version 2.0.15 (2014-11-04)

- added: create list based on opens, clicks and unopens in finished campaigns working again
- added: option to select specific user roles when WordPress Users should get subscribers
- added: additional info to system info page
- improved: better handling of db collation on plugin activation
- change: MyMail templates used with third party plugins have now in lined styles
- fixed: click badges now shows correct percentages
- fixed: external page broke if used internal with ajax enabled
- fixed: error while exporting subscribers with no lists assigned on some servers
- fixed: whitespace in placeholder class caused some “headers already sent” warnings
- compress image files to reduce filesize

### Version 2.0.14 (2014-10-25)

- added: referrer of user now included in the “new subscriber” message
- added: double opt in can now get defined individually for comment subscriptions and WP registrations
- updated: notice system now allows to dismiss all messages at once
- updated: option to only use MyMail templates but using the default wp_mail method (fixes some third party plugin issues)
- improved: total count for posts in the editbar
- change: subscriber via the WordPress User page doesn’t require a list anymore
- fixed: malicious link to delicious service
- fixed: issue where bounce message didn’t get out in some cases
- fixed: subscribers count with conditions where custom fields have a dash in the name\*
- fixed: missing form CSS on linked stylesheet

### Version 2.0.13 (2014-10-20)

- added: option to define signup date on import
- added: bulk option to set status to pending
- fixed: wrong bounce rate percentage
- fixed: autosave striped some tags when users with wrong capabilities create campaigns
- fixed: a problem with nice urls on some server
- fixed: some links in emails end up on wrong destinations in some cases
- fixed: profile page wasn’t accessible with old URL structure
- fixed: unsubscribe form showed up twice on some pages
- fixed: bug with Jetpacks Photon extension on dynamic images
- fixed: wrong queue size cause of localized microtime settings
- fixed: subscribe via comment works now when only approved comments are set
- fixed: new subscriber notification works again when adding subscribers via backend

### Version 2.0.12 (2014-10-14)

- fixed: issue with tags in href attributes on saved templates
- fixed: issue with third party plugins which uses wp_mail
- fixed: issue with some ISS servers and rewrite rules
- fixed: import progress no longer breaks on duplicate subscribers in some cases
- fixed: confirmation uses the base template in rare cases
- fixed: all images are now served via https if page is on a secure connection
- improved: update progress

### Version 2.0.11 (2014-10-11)

- fixed: bug with third party plugins which uses `template_redirect`hook
- change: testmails include now custom fields if available

### Version 2.0.10 (2014-10-10)

- added: option to define custom newsletter homepage slugs
- added: you can now use {linkaddress} in the confirmation message to get only the confirmation link
- fixed: dynamic images now respects the aspect ratio of the original image
- fixed: problem with page slugs when used by a third party plugin (resave permalinks!)
- fixed: issue with choosing WP user role in conditional fields causes 0 receivers

### Version 2.0.9 (2014-10-07)

- fixed: problem with updating on multisite. If you can’t update from version <= 2.0.8 update via FTP and a copy from CodeCanyon (<http://codecanyon.net/downloads>)
- update: improved update progress

### Version 2.0.8 (2014-10-06)

- fixed: better error handling of not sendable subscribers
- fixed: headers applied to wp_mail are now respected (including from address)
- fixed: error messages of subscribers are now shown in the activities
- fixed: a bug in editor with some third party templates
- fixed: subscriber based and follow up auto responders respect the selected lists
- fixed: some campaigns no longer finish with 0 receivers
- fixed: creating and editing subscribers from the user profile page requires now certain capabilities
- change: retry interval for subscribers after error increased to 3
- change: better display of email address in subscribers overview if name is unknown
- added: option to pause campaign on delivery error in the delivery settings

### Version 2.0.7 (2014-10-03)

- fixed: a bug in editor with some third party templates
- fixed: bounces are now not included in totals of finished campaigns
- fixed: issue with time based auto responders where local time has an offset to UTC more than 6 hours
- fixed: redirection issue where some environments doesn`t respect`template_include’ hook
- fixed: missing unsubscribe form when used as short codes
- fixed: changing template caused redirection to login if on https site
- change: switched back to PHPMailer 5.2.7 which causes less issues with some ESPs

### Version 2.0.6 (2014-10-01)

- fixed: wrong sent number when soft-bounces have been resent
- fixed: pages didn’t work with certain slugs
- fixed: redirection didn’t work if no post was defined
- change: search queries are kept after bulk action on subscribers page and lists page
- added: option to bulk add/remove subscribers to lists
- fixed: timestamp didn’t saved on signup autoresponders

### Version 2.0.5 (2014-09-30)

- fixed: issues with bulk actions for lists and subscribers

### Version 2.0.4 (2014-09-327)

- fixed: issues with bulk actions for lists and subscribers
- fixed: some PHP Notices and Strict warnings
- fixed: geo issue in PHP 5.6+
- fixed: time based auto responders now respects the “user times based” option
- added: option to resend confirmation to pending subscribers

### Version 2.0.3 (2014-09-27)

- fixed: newsletter homepage can now be either site homepage or a subpage (resave permalinks!)
- fixed: redirection after submit and confirmation working again
- fixed: archive pages shows newsletters in iframe again
- translation update

### Version 2.0.2 (2014-09-27)

- fixed: last name field wasn’t clickable in some browsers
- fixed: error with time based autoresponder
- change: system info page is now only accessible for admins
- change: maximum of custom fields limited to 58
- added: Author information on the add ons page
- updated: small UI update on subscriber details pages
- fixed: hidden list in subscribers detail view
- fixed: hidden list in subscribers detail view

### Version 2.0.1 (2014-09-26)

- fixed: hidden list in subscribers detail view
- fixed: some warnings in campaing.class.php
- fixed: subscribers from third party plugins are imported now imported as subscribed if no status is set
- change: tinyMCE editor has now more buttons for the content
- change: signup and confirm date is current timestamp if not defined explicitly

### Version 2.0 (2014-09-24)

- new: database structure
- action hook based autoresponder
- user date based autoresponder (birthday greetings)
- follow up autoresponder
- environment of your subscribers
- WordPress User synchronization
- Improved editor let you faster create your campaigns
- Drag ‘n drop images from your desktop right into your newsletter
- auto updating statistics
- custom date fields
- timezone based sending
- profile editing for subscribers
- lot of other stuff

### Version 1.6.6 (2014-04-07)

- you can now use post meta values in tags with {post_meta[meta_key]:XX}
- added: fix for html mails if used with MyMail
- fixed a bug with double labels on checkboxes
- added: mymail-list class to ul tags in forms
- ajax forms have now mymail-ajax-form class
- fixed a bug where form redirects to wrong location after confirmation
- fixed a bug where the settings page doesn’t get loaded caused by a corrupt geo db
- fixed a bug while importing with meta values
- send date for autoresponders fixed
- notices are now removed with an ajax request
- changes clickbadge look
- changed internal button class to mymail-btn
- moved System info page
- bug fixes

### Version 1.6.5.3

- fixed a bug were certain comments causes layout breaks

### Version 1.6.5.2

- added: bulk deletion of pending subscribers
- added: better handling for background images
- HTML conditional comments are now preserved (required for Outlook hacks)
- improved: update class
- fixed title shows {subject} in the web version
- fixed some flickering on the edit screen with certain templates
- fixed some minor bugs

### Version 1.6.5.1

- new: redirect user to a dedicate “thank you” page after confirmation (double-opt-in only)
- fixed: bug where emails are empty sometimes on third party plugins
- other small fixes

### Version 1.6.5 (2014-01-24)

- double-opt-in options are now form related
- new: subscribers get form and page they subscribed from
- new: language: Persian
- fixed bugs with wrong content urls or urls located outside of the WordPress directory
- fixed some bugs with rtl languages
- fixed some minor bugs

### Version 1.6.4.2

- fixed problem with some characters in images
- fixed problem with google plus URLs in autoresponders
- added: video and audio tags in whitelist
- better geo db handling
- improved: cron for more server types
- option to select all capabilities at once per role
- fixed some spelling mistakes
- small bug fixes

### Version 1.6.4.1

- open to choose meta values for WordPress users import
- fixed a bug for embedded hight DPI images on Apple Mail 7+
- fixed wrong character encoding in subject and from field
- fixed multipart mails in mails with linked images
- small bug fixes

### Version 1.6.4 (2013-12-11)

- fully tested with WordPress 3.8 RC2
- new: icons for WordPress 3.8
- new: edit icons
- new: archive function – display newsletters like post in an archive
- reset button in settings
- switched geo db location
- suppress some warnings in invalid html templates
- importing WP users now respects defined roles
- placholder images now work with missing GD library
- added: area an map tags in the whitelist
- manually uploads of geo db now works correctly
- fixed: user registration now works on custom registration pages
- fixed: bug when user subscribes and merge lists was active
- lot of small bug fixes

### Version 1.6.3.2

- fix a with some third party plugins

### Version 1.6.3.1

- fix a bug for PHP < 5.3
- passed headers get now progressed if wp_mail is used

### Version 1.6.3 (2013-11-09)

- improved: bounce handling
- support for soft bounces
- redesigned settings page
- MyMail now respects `WP_CONTENT_DIR` and `WP_CONTENT_URL` if they are defined in the wp-config.php
- bug fix on frontpage with forwarding newsletters via email autosave fixes in WP 3.7+
- able to use WP native local storage backup (WP 3.7+)
- bugfix on dynamic images with wrong height calculation
- updated: inline style class
- all external data get now served from bitbucket (was dropbox)
- fix bug were some “Â” show up in some clients in certain emails
- fixed some Strict Standards bugs (WP 3.7+)
- json responses returns now correct header
- embedded images are of by default now
- fixed bug in update class with multiple plugins
- fixed bug with custom background iamges in the editor
- updated: language files
- works now with the [MyMail Mandrill Integration](http://wordpress.org/plugins/mymail-mandrill-integration/) Plugin
- lot of small bug fixes

### Version 1.6.2.2

- better scrolling behavior
- better placement of the editbar
- fixed: wrong calculation of height in external images
- fixed: error thrown in some third party plugins which use the wp_mail function

### Version 1.6.2.1

- fixed: clickmap doesn’t show percentage
- fixed: first module lost module wrap after saving

### Version 1.6.2 (2013-10-11)

- you can now drag modules within the editor to rearrange them
- updated: phpMailer to 5.2.7 ([changelog](https://github.com/PHPMailer/PHPMailer/blob/master/changelog.md))
- added: port check for SMTP connections
- custom tags now get campaign ID and subscriber ID if available
- custom tags no longer get replaced by their content on finished campaigns
- user values get only overwritten if defined
- user meta values can now get assigned to custom field during import
- removed deprecative jQuery methods
- better error infos for JS error
- bug fixes

### Version 1.6.1 (2013-09-18)

- head part of templates are now respected
- option to change the campaign slug from “newsletter” to a custom value
- better wrapping of URL to prevent 403 errors
- issues with large amount of images solved
- bug fixes

### Version 1.6.0 (2013-09-12)

- new: template language
- included template updated to 3.0 with new template language
- new: included foreign RSS feed in your campaign
- new: text buttons
- new: syntax highlighting in code view
- new: code view for modules
- custom templates now include all modules by default
- custom templates can now have custom modules
- modules can now get renamed (to save custom modules)
- option to wrap single line elements with links
- user images are now based on WordPress avatars in the first place and Gravatars if the do not exists
- editor now offers paragraphs and headings
- editor now offers color picker for text
- newsletter homepage dropdown on settings page now shows published, private and drafted pages

### Version 1.5.8.1

- fixed subscribe button label which was “1” in some cases
- small bug fixes

### Version 1.5.8 (2013-08-09)

- new: segmentation based on user values
- new: shortcode: `[newsletter_list]` (display the latest newsletters in as a list)
- change: {post_date}, {post_modified} now displays only the date. use {post_time}, {post_modified_time} to display only the time
- added: new option for creating list after campaign (1.5.7): “who has not received”
- added: option to label submit button for each form
- subscribers notification now contains a google map if available
- added: option to filter receivers in finished campaigns
- updated: minicolors plugin to version 2.0
- renamed metabox “Lists” to “Receivers”
- tags now work correctly within links
- retina ready avatars for subscribers
- fixed wrong redirections
- updated: easy pie charts
- fixed bug in Polish translation (was Spanish)

### Version 1.5.7.1

- fixed: error in geo ip class

### Version 1.5.7 (2013-07-25)

- create new lists on finished (or active) campaigns based on:
  - all recipients
  - who has opened
  - who has opened but not clicked
  - who has opened and clicked
  - who has not opened
- search for subscribers now includes custom field values
- optimized cronjob to use less memory
- updated: languages
- added: user information to subscriber mail
- new: avatars for unknown subscribers
- updated: easy piechart plugin
- option to bulk convert subscribers with status “error” back to “subscribed”
- fixed bug where categories doesn’t get saved on autoresponders
- fixed google plus share link
- bug fixes

### Version 1.5.6 (2013-07-08)

- now works with the [Google Analytics](http://wordpress.org/plugins/google-analytics-for-mymail/) and the [Piwik](http://wordpress.org/plugins/piwik-analytics-for-mymail/) add-on
- don’t convert emails to mailto links anymore ([why](http://www.campaignmonitor.com/blog/post/4003/tip-avoid-using-mailto-links-in-html-email))
- fixed a bug with hash tags in links

### Version 1.5.5.2

- fixed bugs with third party shortcodes
- fixed bug with geo db

### Version 1.5.5.1

- fixed bugs for confirmation messages
- updated: update class

### Version 1.5.5 (2013-06-27)

- new: option to check Spam score (beta)
- option to get notified about new subscribers
- new: language: Portuguese (Brazil)
- added: `{post_author_name}`, `{post_author_email}`,`{post_author_url}`, `{post_author_nicename}` dynamic tags
- `{post_excerpt}` now uses [wp_trim_words()](http://codex.wordpress.org/Function_Reference/wp_trim_words) on the content if no excerpt is set
- changed pie charts to [easy-piecharts](https://github.com/rendro/easy-pie-chart)
- removed isNotSpam option
- added: option to reset limits
- added: option to change language of texts if available
- fixed a bug on network activation
- fixed error on to many fallback images on the settings page

### Version 1.5.4.1

- bug fixes on delivery meta box

### Version 1.5.4 (2013-06-06)

- new: time base autoresponder
- option to change charset and encoding
- optimized translations
- added: forms now get a “loading” classes if the form is progressing
- added: option to define the label of each field of every form
- option to hide the asterisk of required fields in forms
- fixed: form throws error if custom function doesn’t return a value
- fixed: issue with the stats on the dashboard widget

### Version 1.5.3.2

- new: function “mymail_get_subscriber”
- added: option to embed form css
- fixed: some not latin characters in url prevent redirections
- fixed: alt text of buttons didn’t show up in the editbar

### Version 1.5.3.1

- important fix for missing background colors

### Version 1.5.3 (2013-05-17)

- auto responder now have full statistics
- added: convert single line texts to images with a click
- improved: update class for better performance
- updated: some geo location files
- fixed: spelling issue in text after widget
- small bug fixes

### Version 1.5.2 (2013-05-06)

- prepared for an upcoming plugin – stay tuned!
- new: Twitter integration for Twitter API 1.1 – requires access credentials
- bug fixes

### Version 1.5.1.2

- performance improvements
- bug fixes

### Version 1.5.1.1

- better behavior of the edit bar
- option to keep status of existing subscribers on import
- editor now insert paragraphs correctly
- cleaned up editor buttons
- DKIM issue fixed – re-save settings if you had issues
- several bug fixes

### Version 1.5.1 (2013-04-12)

- fixes some Call-time pass-by-reference errors

### Version 1.5.0 (2013-04-12)

- works now on network sites
- better import for WordPress users
- added: option to merge imported contacts with existing ones
- added: bounce server test
- added: import WordPress users via Manage subscribers page
- removed: auto import of WordPress User on plugin activation
- option to define notification template for forms
- option to update geo database
- option to upload custom geo database
- lot of small bug fixes

### Version 1.4.1 (2013-03-12)

- added: allow users to sign up on new comment
- added: allow users to sign up on register
- added: subscribers avatar to subscribers list
- updated: PHPMailer to verison 5.2.4
- option to add vCard to confirmation mails
- send test to multiple receivers with comma separated list
- new: text “Newslettersignup”
- bug fixes

### Version 1.4.0 (2013-03-01)

- **Please finish all campaigns before update or you may have wrong stats in running campaigns!**
- new: dynamic post tags
- new: automatically send your latest post, pages or custom post types to your subscribers with auto responders
- updated: templates
- new: improved stats on the campaign detail page for each subscriber with opens and clicks
- added: better mobile preview
- added: insert image from URL
- added: HTML as output form on the export tab
- added: option to duplicate modules
- improved: sending queue – now uses up to 60% less resources
- improved: cron window with more info
- fixed: problems when importing and exporting with some special characters
- fixed: campaign stopped if subscriber caused the error
- fixed: some CSS issues in gecko browsers
- if you have [premium templates](http://rxa.li/mymailtemplates) check them for updates!

### Version 1.3.6 (2013-02-04)

- updated: included template to version 2.0:
- responsive
- added: more social icons
- updated: section for editbar: buttons
- added: welcome page
- removed: my first campaign
- added: option for merge lists via settings

### Version 1.3.5 (2013-01-24)

- added: HTML form embedding
- added: redirect after submit to any URL
- added: checkboxes for custom tags
- updated: language. now available in:
  - German
  - English
  - French
  - Croatian
  - Slovak
  - Italian

### Version 1.3.4 (2013-01-10)

- added: pending tab in manage subscribers for unconfirmed users
- templates are now located in the upload directory
- added: option to uses a custom country/city database
- added: option to resend confirmation notice after a defined time
- fixed: confirmation mails doesn’t effect limits

### Version 1.3.3 (2012-12-31)

- Completely rewritten subscriber management with improved upload, export and bulk deletion
- removed: old import/export section
- added: new capability ‘manage subscribers’ to give access to the new page
- added: option to pre-fill known user data in forms if user is logged in
- added: forms in widgets now have a ‘mymail-in-widget’ class
- added: optional text before and after the form in widgets
- performance improvements
- small bug fixes
- fixed: inline labels are not visible in IE&gt;

### Version 1.3.2.4

- Bug fixed for sending problems

### Version 1.3.2.3

- small fix

### Version 1.3.2.2

- Quick fix for sending problems

### Version 1.3.2.1

- small bug fixes

### Version 1.3.2 (2012-12-19)

- **Please finish all campaigns before update!**
- added: support for the new Media uploader in WP 3.5
- improved: Editbar:
  - better preview of posts, images and link
  - links includes now all other pages too
  - double click on the element you like to edit
  - double click on an image in the editbar to insert it instantly
- campaigns now get paused if an error occurs during sending
- fixed: subscribers falsely get marked as “error”
- fixed: missing HTML tab in the editbar
- fixed: geoip.inc conflicts
- fixed: autoresponder not triggered if duplicated
- a lot of bug fixes and performance improvements

### Version 1.3.1.3

- fixed: autoresponder get sent twice in some cases

### Version 1.3.1.2

- fixed: send problems in some cases

### Version 1.3.1.1

- fixed a small bug
- added: mymail_subscribe and mymail_unusbscribe functions

### Version 1.3.1 (2012-12-12)

- Better DKIM setup
- Better SPF help
- Fully tested in WordPress 3.5
- optimized delivery method page
- added: option to enable pagination on frontpage
- added: bulk delete of subscribers
- added: track subscribers IP and signup time (optional)
- added: SSL support for bounce mail server (POP3)
- added: “List-Unsubscribe” header with link to unsubscribe page
- added: Gmail delivery method
- added: optional delay between mails in campaigns
- fixed: empty Form CSS now prevents enqueuing form CSS
- fixed: required asterix always show up for names
- fixed: links didn’t work in some cases in Outlook 2007
- many bug fixes

### Version 1.3.0 (2012-11-27)

- Track vistors cities
- added: {forwad} tag to allow forwarding your newsletter
- added: inline label for forms (optional)
- images from custom templates in the template directory are now saved with relative path
- added: better feedback for saving templates on the templates page
- fixed: invalid emails are getting imported
- fixed: images are always embedded in notification mails

### Version 1.2.2.1

- fixed: headers are not set in some cases

### Version 1.2.2 (2012-11-15)

- MyMail Template updated to 1.3
- added: embed your form on another site
- added: new capability “manage capabilities”
- added: campaigns now sortable by status
- fixed: Auto responder not sent if limit reached
- fixed: more loadHTML issues
- small bug fixes and performance improvements

### Version 1.2.1.4

- fixed: loadHTML error in some cases
- fixed: HTML editor not available in some cases
- fixed: HTML doesn’t get changed on Firefox in some cases

### Version 1.2.1.3

- fixed: small bug in Javascript

### Version 1.2.1.2

- added: auto responders now get send on bulk import too
- updated: form CSS to better match twenty eleven and twenty twelve theme
- fixed: editor doesn’t close if tinymce is disabled

### Version 1.2.1.1

- fixed: auto responders get send again after update

### Version 1.2.1 (2012-10-18)

- added: better support for custom templates
- added: new option: email limit for a certain period
- better forms now works with JS disabled
- prefix for import and template page
- improved: post and image list
- localized number formatting
- fixed: some “Call time passed by reference errors”
- fixed: bug in wp_mail
- fixed: bulk import with “wrong” line breaks
- fixed: auto responders can get activated without permission
- small bug fixes

### Version 1.2.0 (2012-10-15)

- new: auto responders
- webversion link now working in test mails
- Dashboard widget settings removed – now only through capabilities
- List descriptions are now included in the form
- WordPress system mails now uses notification template (optional)
- loading graphic updated for retina support

### Version 1.1.1.1 (2012-10-09)

- fixed: problems with form CSS

### Version 1.1.1 (2012-10-09)

- custom color schemas can now get deleted
- better custom color handling for templates
- active campaigns are not editable anymore (must be paused)
- campaign statistics for active campaigns
- added: texts tab, better text management in settings page
- fixed: Bulk import breaks in some cases
- fixed: wrong click count if cron was running

### Version 1.1.0 (2012-10-04)

- new: capabilities
- new: Bulk Import for large subscriber lists
- performance improvements
- lists in forms now optional drop downs
- change value of “First Name”, “Last Name” via settings panel
- improved: custom fields with support for textfields, drop downs or radio buttons
- fixed: scroll down to the bottom on frontpage not possible

### Version 1.0.1 (2012-09-24)

- Different ajax action for exporting subscribers (was not so common)
- small fixes

### Version 1.0 (2012-09-24)

- Initial Release
