=== Scalability Pro ===
Contributors: dhilditch
Donate link: https://www.wpintense.com/
Tags: speed, performance
Requires at least: 4.7
Tested up to: 5.8.2
Stable tag: 5.09
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Speeds up slow SQL queries and includes various other performance tweaks to help your site scale to any size.

== Description ==

Speeds up slow SQL queries and includes various other performance tweaks to help your site scale to any size.

== Frequently Asked Questions ==

= Is this PHP 8 compatible? =

Yes, and it's MySQL 8 and MariaDB compatible too.

== Screenshots ==

1. scalability-pro-settings.png

== Changelog ==

= 5.42 (21st November 2023) = 
* Updated plugin updater to look at superspeedyplugins.com

= 5.41 (21st November 2023) = 
* Fixed bug in check_license code - one path was not caching the result properly
* Improved speed, reduced use of 2x 'query' filter code so it only ever runs on get_posts, not on all get_results queries
* Improved speed of Fix Woo Onboarding slightly by checking for pattern in more efficient way

= 5.40 (15th November 2023) = 
* Optimised use of $e->getTraceAsString() so it gets called far less often
 - using this tracestring is one reliable technique to discover the originating function for bad SQL
 - I optimised this by first checking for the SQL pattern at the start of the SQL string

= 5.39 (15th November 2023) = 
* Added new SPRO_PREVENT_WPAI_DUP_CHECK definition 
 - setting this to true can help speed up WP All Import if you have large imports
 - this seems to be dependent on your config for your 'unique key' which is normally automated
* Fixed bug with fixing Woo Onboarding code 
 - the has_products function was fixed previously, now the is_new_install is properly fixed
 - previously the fix had only been added to some admin pages, this fix now stops that slow code running on WP All Import batches too
* Altered wp-admin > Optimise Woo products list to instead Remove Woo Product Category dropdown
 - the code previously altered the code to only list the first level of categories, but was no longer working
 - now this code just removes that dropdown completely
 - with the recent update to Super Speedy Search you can search for categories instead

* Improved Slow Query Log so it captures info more reliably
* Fixed bug that prevented slow query log running if no db.php existed

= 5.38 (14th November 2023) = 
* Added Empty Log Table button
* Improved Slow Query Log so it captures info more reliably
* Fixed bug that prevented slow query log running if no db.php existed

= 5.37 (20th October 2023) = 
* Added 2 hour transient cache for reading changelog files from our server
 - this will speed up wp-admin slightly and prevent the 403 forbidden error messages some have been getting when trying to run plugin updates

= 5.36 (19th October 2023) = 
* Removed action that was running after WP All Import completed
 - this was recounting ALL terms which was causing issues with some customers
 - underlying recount function has been further optimised to reduce RAM usage but will complete as expected nightly, not after each import

= 5.35 (19th October 2023) = 
* Updated &spro_show_import_queries=1 option to help debug slow WP All Import jobs
 - this will now output to screen whatever was supposed to be sent through json
 - KB article created to guide advanced users through this https://www.wpintense.com/knowledgebase/how-to-use-scalability-pro-to-debug-slow-wp-all-import-speeds/
* Discovered new index required for sku lookups with WP All Import, otherwise table scan against their lookup table happens

= 5.34 (13th October 2023) = 
* Option to cache post counts for admin pages created
 - this has been separated out from defer term counts as a separate option, but it's still in the import tab because it helps most with admins running imports a lot
 - option has also been updated to exclude shop_order post types from the cache since order statuses change frequently and admins need to see what items remain to be done

= 5.33 (11th October 2023) = 
* Hotfix for recent optimisation which could create duplicates in archives

= 5.32 (9th October 2023) = 
* Improved the remove DISTINCT/GROUP BY code further
 - more performance if querying against only wp_posts
 - especially more performance if the query can now use an index for sorting, e.g. order by date desc
 
= 5.31 (4th October 2023) = 
* Added new define option SPRO_ALWAYS_DO_TERM_RECOUNTS
 - defaults to array('nav_menu', 'link_category', 'post_format', 'wp_theme', 'wp_template_part_area', 'elementor_library_type', 'elementor_library_category')
 - this means even when 'defer term counts' is enabled, these taxonomies will always be recounted on-the-fly rather than waiting until overnight
 - if you want to use defer term counts but there are some taxonomies you wish to have up to date at all times, you can copy the definition from our defines.php to your wp-config and add extra taxonomy names to it

= 5.30 (26th September 2023) = 
* Fixed warning that was occurring on FSEs 

= 5.29 (18th September 2023) = 
* Hardened symlink checker for slow query logger so it doesn't raise warnings

= 5.28 (17th September 2023) = 
* Fixed bug in switching off slow sql profiling

= 5.27 (8th September 2023) = 
* Fixed removal of symlink to clear the opcache if in use, this means when QM's symlink is put in place it then properly uses Query Monitors symlink
* Added new URL param spro_show_import_queries=1 which can be used with the wp-all-import processing URLs to view the queries with Query Monitor for a batch of rows imported
 - e.g. call  http://localhost/wp-load.php?import_key=OxdAHmoD&import_id=19&action=trigger and then:
 - call  http://localhost/wp-admin/?import_key=OxdAHmoD&import_id=19&action=processing&spro_show_import_queries=1
 - note the alteration of the URL to not use wp-load and instead load your dashboard, and then with spro_show_import_queries=1
 - this clones the wp all import processing function but with send_json removed 
 - this means you can see all the DB activity performed by WP All Import through Query Monitor!!

= 5.26 (6th September 2023) =
* Fixed bug in new symlink feature for slow query profiling which affected my dev multisite install
 - apparently $wpdb->prefix can be empty when get_site_ids() is called
* Fixed major bug in new db.php $wpdb class override
 - to save the profile, i was using wpdb
 - this meant subsequent actions like $wpdb->last_results would return the last result of my insert statement, i.e an empty resultset causing product archives to be empty!
 - this only happened on dev, 5.25 was not released to the public
* Added clear action scheduler logs button to the Imports tab by customer request
 - this probably will not help improve performance much unless something somewhere is reading from these logs
* Fixed missing array keys warning for upgraded scalability pro 

= 5.25 (5th September 2023) =
* Added new symlink feature for Slow Query Log tab
 - this symlinks db.php to point to our db.php which will allow us to capture queries which run before the 'init' hook
 - this also updates wp-config.php to contain our $SPRO_GLOBALS variable for the same purpose
* Improved layout of Slow Query Log output
* Added Query Pattern field to Slow Query Log tab
 - you can place regex in this field and it will add matching queries to the Slow Queries table so you can see the stack trace and find the source of problematic sql

= 5.24 (22nd August 2023) =
* Added new detection of unnecessary GROUP BY when LIMIT 0,1 is being used 
 - this seems to happen in imports and causes a table scan of wp_posts, so this should result in a good boost for imports on large sites 
* Added further improvement for 'defer term counting'
 - when a new item is published, that post types cache is wiped
 - this leads to slower imports since an entire recount can happen after every import
 - The change has modified this query to fetch data from our own cache table which stores the counts for 24 hours if you enable the defer term counting option 
* Added Optimize Product Attributes Lookup functionality to the Imports tab
 - WooCommerce maintains the product attributes table EVEN IF you deselect to use it
 - One outstanding issue here on larger sites is the SQL where they check WHERE product_id = 984353 OR product_or_parent_id = 984353; 
 - only the product_or_parent_id needs to be checked in this case and then an index gets used
 - This functionality alters this SQL query if you enable the option on the Imports tab
 
= 5.23 (22nd August 2023) =
* Added individual index control 
 - unused Notes column will be upgraded in future to provide links to articles about what this index can do
 - notes column will also be used for customers who have the ability to enable the performance schema - this will allow you to discover which indexes do nothing for your install

= 5.22 (17th August 2023) =
* Slow Query Log tab reintroduced

= 5.21 (15th August 2023) =
* Fixed bug in new update system where license key was not being passed properly to secure download link

= 5.20 (11th August 2023) =
* Fixed many warnings for brand-new users if they are displaying warnings on screen - these were just warnings about missing settings which went away if you saved the settings but anyway, they are now fixed

= 5.19 (11th August 2023) =
* Fixed some warning messages on the import tab & fixed another warning in the update checker

= 5.18 (10th August 2023) =
* Hotfix for new updates system - if your server was configured to disable remote URL requests, a critical error was happening upon upgrade. This has been fixed with a warning message that you cannot check for updates.

= 5.17 (4th August 2023) =
* Upgraded main settings page to show changes since last update
 - added update count to menu to help users notice
* Added links to all plugin docs and support areas in the settings page

= 5.16 (26th July 2023) =
* Upgraded updates sytem so it uses JSON and gets zip file from license-key-checked URL

= 5.15 (6th July 2023) =
* Fixed warning for new slow query log code

= 5.14 (6th July 2023) =
* Fixed warning against missing 'sizes' key in $data passed to wp_get_attachment_metadata filter - not sure why this would ever be missing but apparently it can be

= 5.13 (6th July 2023) =
* Minor visual fix for slow query log query container (max height then scrollbar)

= 5.12 (6th July 2023) =
* Added Slow Query Log tab
 - this uses a lightweight technique to capture slow queries
 - slow query log defaults to off, query duration is defaulted to 0.5 seconds, but you can adjust this in the slow query tab
 - this slow query log will not yet capture timedout queries since after a timeout the code does not continue to run - current solution is to increase your server page timeouts so you can capture these (e.g. in your dev/staging environment)
 - slow query log captures the query, the stack trace and the URL from whence the slow query originated
 - a future version will allow users to submit their slow query log to WP Intense to help us with figuring out what to optimise next
 - new definition can be overridden in your wp-config.php, defaults to: define('SPRO_MAX_TRACE_CHARS', 10000); - this affects captured query length and captured stack trace length in characters

= 5.11 (22nd June 2023) =
* Fixed bug in reducing storage of action scheduler items 
* Added new Beta SPRO_CACHE_PMXE_META_KEYS option 
 - to enable, add define(SPRO_CACHE_PMXE_META_KEYS, true); to your wp-config.php 
 - when exports start with WP All Export, it counts the unique meta keys in your table 
 - on large sites, this can be a pain

= 5.10 (8th June 2023) =
* Added action scheduler optimization option to the Imports tab
 - WooCommerce uses this heavily when updating products and when the _actions table gets heavily populated I've seen an extra 1 second per product for imports which is incredibly slow
 - Enabling this optimization option will cause the action scheduler to clear out completed items daily instead of every 30 days
* Added a button to manually clear the action scheduler table 
 - this safely deletes all actions with status of 'cancelled', 'completed' and 'failed'

= 5.09 (7th April 2023) =
* Improved speed of 'Remove check for private items' option - moved it from the 'query' filter to the pre_get_posts filter - this reduces CPU usage on sites with high query counts
* Fixed 'Remove Sort Order' and 'Remove SQL CALC ROWS' options so that they operate on taxonomy langing pages as well as filtered archives
* Added new Ajax search capability for when editing a products attributes - helps if you have a lot of terms
 - We had a client with 100,000+ terms in an attribute (artists of a song)
 - WooCommerce loads all 100,000+ of these artists into the HTML select box on the Edit Product page
 - this makes the initial page load very slow AND it makes the page react really slowly because there is so much HTML for the browser to process
 - the new option here transforms that dropdown into a box which shows the first 10 then you can search to find others
* Added fix for WooCommerce onboarding - this speeds up wp-admin on our foundthru site from 10s to 1s in most cases, and from 70s to 1s in some cases!
 - Woo Onboarding uses the has_products function which for some stupid reason performs a table scan
 - this check IS cached, but if you edit any products and/or are performing imports then this cache would be wiped and you'd be immediately back to slow performance 
 - we have added a separate 24 hour cache so that even if you are running imports this simple check for has_products() will be intercepted and return true
* Added new Import Tab option to reduce the number of image sizes generated
 - by default, WordPress creates images in all registered sizes
 - this could mean you have 20+ images being generated in different sizes on your server when you upload a single image - this uses excessive disk space and CPU
 - this new option adjusts WordPress so only the images you choose get created
* Added new index for use by Rankmath on wp_rank_math_analytics_objects table on object_id - speeds up rankmath analytics
* Added new index on posts on post_modified_gmt


= 5.08 (24th January 2023) =
* Fixed JS bug affecting the new 'Ajaxify term search' option. The search was working, but the wp-admin JS was replacing the option list with the original list. Now searching for terms in huge term lists is very fast.

= 5.07 (6th November 2022) =
* Fixed bug with defer term counting which was preventing new menu items being added to brand new menus

= 5.06 (5th November 2022) =
* Added option to track creation of wp_scalability_pro_cache table rather than checking information_schema.tables every page load

= 5.05 (4th November 2022) =
* Previous fix confirmed - updated settings page to instruct users to add define('SPRO_FIX_WOO_ONBOARDING', true); to wp-config.php if they wish to test this option 

= 5.04 (4th November 2022) =
* Replaced new onboarding option temporarily with define('SPRO_FIX_WOO_ONBOARDING', false); 
 - change this to true in your wp-config.php if you wish to try it, we're seeing memory issues in multisite for some reason

= 5.03 (4th November 2022) =
* Added manual garbage collection to clear stack trace variable used to check query origin

= 5.02 (4th November 2022) =
* Added BETA option SPRO_REDUCE_IMAGE_SIZES
 - this reduces how many image sizes you import
 - to try it, please add the following to your wp-config.php file:
   define('SPRO_REDUCE_IMAGE_SIZES', true);

= 5.01 (4th November 2022) =
* Fix for slow WooCommerce onboarding code 
 - this eliminates slow code that relies on wp_cache. 
 - wp_cache gets wiped whenever products get edited, added, deleted. 
 - That means, this should massively speed up imports.
 - Related article: https://www.wpintense.com/2022/11/04/speeding-up-woocommerce-7/

= 5.00 (20th October 2022) =
* Further upgrade for Remove Sort Order - it now defaults to only removing sort order from the Product Query since typically you want the latest posts to arrive
 - to override this, if you wish, please copy the define('SPRO_REMOVE_SORT_ORDER_POST_TYPES', array('product', array('product', 'product_variation'))); from the defines.php file to your wp-config.php file and add extra post types to the array as you wish
* Fixed broken index count - index was being created, but was not being counted because of UNIQUE constraint on wp_options (option_name)

= 4.99 (19th October 2022) =
* Added new feature from @ifrountas request to ajaxify the attributes/taxonomies on post.php and post-new.php 
 - this means if you have 30,000 terms in your taxonomies (e.g. authors, artists) then edit your posts will no longer be slowed down by this unwieldy select box 
 
= 4.98 (19th October 2022) =
* Added update for new creations of options index to make it unique on options_name (SSS will also fix this as it's the plugin that requires it)

= 4.97 (18th October 2022) =
* Fix for Remove Sort Order so it doesn't affect wp-admin stuff, orders, products etc 

= 4.96 (13th October 2022) =
* Added new semi-permanent cache for months and other cache from VIP code - ugosprint found when running imports, the regular WP cache is constantly being flushed so updating orders was taking 30 seconds as a result

= 4.95 (13th October 2022) =
* Fixed override for removing pagination on back-end edit.php - the options object was not checked properly
* Added permanent override to remove unneeded DISTINCT which was causing table scans on posts_clauses - if LIMIT = 0, 1 or LIMIT = 1 then remove DISTINCT since it's not required and will cause a table scan - this is applied across all $wpdb queries
* 2 new indexes on WP All Import.pmxi_posts which speeds up large imports quite a bit 
* Fixed the wp_posts.post_title index - this was why index counts were sometimes wrong on some setups and it kept telling you there was one more index to create 
* New index on wp_actionscheduler_actions - I've been seeing a LOT of slow activity on this newish table once stores get larger. Speeds up CRON in particular and reduces background load on the server.

= 4.94 (12th October 2022) =
* Upgrade and fix for defer term counts - speeds up imports for all post types, not just products, product import speed also boosted with this upgraded technique

= 4.93 (5th October 2022) =
* Added override for remove pagination on front-end if post_type empty or post_type = 'course' for compatibility with LifterLMS dashboard which expects the rowcounts and bails infinitely if the rowcounts are not there.

= 4.92 (3rd October 2022) =
* Finally found the real issue - there was an uncommitted change in SSS to overrule Spro sort order and there was an incorrect rule to only remove sort order for products, and the check for main query had been removed. All fixed.

= 4.91 (3rd October 2022) =
* Final hotfix update to fix the 'remove sort order' option. Was removing relevancy and also was not respecting the actual option setting.

= 4.89 (3rd October 2022) =
* Further update to remove redundant removal of sort order, now just rely on clauses filter

= 4.88 (3rd October 2022) =
* Minor update for priority of code to remove sort order to move it slightly earlier to ensure the SSS RELEVANCE order runs after it

= 4.87 (29th September 2022) =
* Fixed Remove Sort Order option - it was not correctly removing the default sort order from the products archive. It should only remove the sort order if there is no URL orderby parameter to override the default.

= 4.86 (14th September 2022) =
* Fixed error on settings page related to profiling table (profiling table removed, there will be an integration with query monitor in near future)
* Added new option for SQL_CALC_ROWS on edit.php which can massively help speed up those pages for users with 10000s of posts/products/pages/cpts

= 4.85 (11th August 2022) =
* Fixed warnings that were appearing relating to new options to cache best-selling and other 2 shortcodes

= 4.84 (12th July 2022) =
* Added new index on wp_posts (post_title) - discovered this is required for Smart Coupons to stay scalable on large websites. Covered in this article: https://www.wpintense.com/2022/07/07/how-to-use-query-monitor-to-analyse-wordpress-performance-problems/
* Fixed bug which was constantly showing that 1 more index is required to be created if you don't have WP All Import installed (we add 5 indexes for WPAI)

= 4.83 (7th June 2022) =
* Added optimisation for sites with lots of media files - two expensive queries that occur on every page load of wp-admin are now cached for 1 day. These queries just fetch the month/year for available media files. On client site with 300,000 products and related media, this eliminates 10s from wp-admin.
* Added optimisation for WooCommerce onboarding where they continuously check for existence of something in the database, but for some reason they sort the results. I removed the sort operation to make this return instantly, no need for caching. On foundthru, this eliminates 70s from wp-admin.
* Added optimisation for bulk editing items - now defer term counting is switched off for bulk editing - may potentially add further optimisations here - e.g. to queue up actions added to save_post such as third-party server based stuff like CRM, email, updating stock on Amazon etc

= 4.82 (23rd May 2022) =
* Improved Optimise Group BY option so that it applies far more often and changes the query to SELECT DISTINCT instead of select X, ...., GROUP BY posts.ID

= 4.81 (4th March 2022) =
* Removed error logging from 4.80
* Improved code surrounding detecting custom sort order to bypass this rule - this is to allow users to choose a sort order in the shop archive (e.g. sort by price) and it will work always for all themes

= 4.80 (4th March 2022) =
* Remove sort order now fully restricted to 'products' to avoid showing articles in the wrong order - this depends on your database config whether this would have been an issue. If a sort is added to the URL, sorting will operate as normal.

= 4.79 (23rd February 2022) =
* Further fix for 'remove sort order' option to ensure it doesn't affect admin pages

= 4.78 (11th February 2022) =
* Fixed warning that was appearing on super speedy search results for scalability pro

= 4.77 (28th January 2022) =
* Fixed the 'remove sort order' option - it was previously overruling some pages that it shouldn't - it definitely only affects the main query on the front end now
* Sort option also disabled if ?orderby parameter in the URL
* Sort option confirmed compatible with super speedy search RELEVANCE scoring
* Fixed multisite compatibility for creating indexes

= 4.76 (17th December 2021) =
* Altered the update checker to use Bitbucket instead of my own server

= 4.75 (17th December 2021) =
* Upgraded the update checker - checked the two errors that I fixed earlier are fixed in the update. 

= 4.74 (17th December 2021) =
* Hardened plugin update checker - an error was occurring if the wpintense json file was not available

= 4.73 (15th December 2021) =
* Added 3 new indexes for the new way WP All Import performs image lookups

= 4.72 (10th November 2021) =
* Merged old items into core

= 4.71 (4th November 2021) =
* Added scalability fix for order admin issue caused by the Order Delivery Date plugin

= 4.70 (2nd November 2021) =
* Added index to wp_options on name - found in perf analysis that this is actually a very frequent SQL pattern and when > 2000 options exist it starts to get noticable
* Added index to wp_comments to assist with admin area where comment counts are counted by comment type
* Added count users cache for wp-admin order admin speed boost - it's only used to display a dropdown in the list orders screen (and possibly other screens)
* Refactored how indexes are created - paves the way for selective indexing in future

= 4.69 (22nd October 2021) =
* Hardened code to avoid fatal warning at end of overnight cron job if WooCommerce not installed - checks for existence of wc_recount_all_terms - was not affecting anything other than creating an error in debug.log

= 4.68 (9th September 2021) =
* Added optimisation for WCFM Multivendor marketplace - the query they have to generate the STORE filter is not scalable, made scalable with this update
* Added 2 indexes to support improved query for WCLOVERS WCFM Multivendor Marketplace plugin - wp_usermeta.wpi_wclovers_wcfm_marketplace_usermeta and wp_users.wpi_wclovers_wcfm_marketplace_users - even just 6000 entries in wp_usermeta (10 users) is enough to add 0.5 seconds to page load, but with this index + query alteration the same query is 0.003 seconds

= 4.67 (3rd September 2021) =
* Added settings control over the option to remove OR post_status = 'private' to front-end queries. Doing this can speed up WP_Query in some cases. Using this option will stop private items displaying on your front-end.

= 4.66 (31st August 2021) =
* Altered code that removes check for private posts to never run on wp-admin pages

= 4.65 (30th July 2021) =
* Fixed bug that occured with 'CHANGE TAX TO EXISTS' when no tax exists

= 4.64 (12th May 2021) =
* Minor PHP notice warning removal

= 4.63 (12th May 2021) =
* Import speed boosted significantly - https://www.wpintense.com/2021/05/12/speeding-up-wp-all-import-imports-using-scalability-pro/
* Improved Defer Term Count code to speed up imports 
* Added wc_lookup_sku index for product lookups against the sku (used by more than just imports, so speed boosts elsewhere too)
* Added wpallimport_sku to speed up imports when checking to see if item already inserted
* Added wpallimport_guid to speed up imports

= 4.62 (30th September 2020) =
* Added new WPI Settings page where users can enter their license key to enable plugin updates

= 4.61 (22nd June 2020) =
* Added multisite compatibility

= 4.60 Beta (1st May 2020) =
* Prevent query alterations for cron and ajax where the order of results may actually matter for functionality
* Improved removal of GROUP BY when group by not needed - gives speed boost in some cases

= 4.59 Beta (16th April 2020) =
* Changed mechanism for the 'WHERE NOT EXISTS' option to make it work with all taxonomies and multiple taxonomies (previously was disabled and when enabled it only handled 1 taxonomy) - this optimisation helps in many cases but not all - you'll need to test it to check with your data config

= 4.58 (31st March 2020) =
* Added new index on wp_posts on just the guid column. Some themes grab posts directly using SQL against wp_posts and just use the guid. Typically this is when they're grabbing images. They shouldn't do it that way but some do.

= 4.57 (27th August 2019) =
* Fixed bug related to 'removing woocommerce ajax variations count' which improves performance of pages with many variations
* - bug was related to items which didn't have all variations created. Fix was to return at least 1 of the variations which seems to fix the Woo JS code so that it will correctly use Ajax to fetch prices.

= 4.56 (16th May 2019) =
* Updated code to prevent 'sort order suppression' and 'pagination suppression' from running on admin pages

= 4.55 (16th May 2019) =
* Re-added experimental EXISTS option for those users who have already set it and are successfully using it. Full fix coming soon to re-add it to options page.

= 4.54 (8th May 2019) =
* Further hotfix for sitemap index

= 4.53 (8th May 2019) =
* Fixed sitemap index in cases where somehow the keys were > 1000 characters

= 4.52 (8th May 2019) =
* Removed the experimental EXISTS feature as it was causing errors in many cases

= 4.51 (6th May 2019) =
* Altered the plugin update checker to use the latest version and avoid warning message.

= 4.50 (12th April 2019) =
* WHERE EXISTS experimental option now fixed - if all 3 wp_query options set, dogs category on foundthru is 0.0009s

= 4.49 (1st April 2019) =
* Swapped out use of dbDelta to use generic CREATE statement instead since it seems there is a bug inside WordPress core - https://wordpress.stackexchange.com/questions/141971/why-does-dbdelta-not-catch-mysqlerrors/141984
* Removed the .js file from front-end as it's not yet needed

= 4.48 (28th March 2019) =
* Bumped version number to help customer

= 4.47 (28th March 2019) =
* Removed row-level caching from WooCommerce->Orders (needs bugs fixed)
* Added caching table for better performance than using transients

= 4.46 (4th March 2019) =
* Added caching table for use by wp-admin views
* Added row-level caching to WooCommerce -> Orders
* Added row-level caching to wp-admin -> Products
* Removed warnings & notices

= 4.45 (11th February 2019) =
* V minor speed boost when checking for queries related to group BY (mostly around sitemaps)

= 4.44 (4th February 2019) =
* Added new index for XML sitemaps - optimises BWP Google XML Sitemaps and Yoast SEO XML sitemap functionality
* Added new filter to optimise paged query for BWP Google XML Sitemaps
* Added new filter to optimise main query for Yoast SEO XML sitemap functionality

= 4.43 (10th January 2019) =
* Removed warning caused by 'beta' check for updates for those who were not on beta program

= 4.42 (2nd December 2018) =
* Fixed issue with spotting indexes that were created using MySQL 8

= 4.41 (17th September 2018) =
* Fixed product's disappearing on some themes when 'no pagination' option set

= 4.37 (11th January 2018) =
* Improved product listing page speed

= 4.36 (11th January 2018) =
* Fixed 2 errors when object is not defined - was preventing correct operation of 'duplicate product' link (for example)

= 4.35 =
* Improved experimental left join
* Improved admin pages and documentation

= 4.34 =
* Fixed broken 'sort order' option
* Made experimental LEFT JOIN optimisation (to WHERE EXISTS) far more stable