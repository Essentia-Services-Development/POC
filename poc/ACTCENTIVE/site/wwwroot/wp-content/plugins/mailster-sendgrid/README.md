# Mailster SendGrid Integration

Contributors: everpress, xaverb  
Tags: mailster, delivery, newsletter, email, mailsteresp  
Requires at least: 3.8  
Tested up to: 6.0  
Stable tag: 2.1  
License: GPLv2 or later

## Description

Uses SendGrid to deliver emails for the [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=SendGrid).

> This Plugin requires [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=SendGrid)

## Installation

1. Upload the entire `mailster-sendgrid` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings => Newsletter => Delivery and select the `SendGrid` tab
4. Enter your credentials
5. Send a testmail

## Changelog

### 2.1

-   fixed: issue with invalid data type with WP 5.5

### 2.0.2

-   fixed: issue with Mailster 2.3.16+ and reply_to headers

### 2.0.1

-   fixed: undefined reply_to email address

### 2.0

-   switch to SendGrid API v3 requires now an API Key
-   embedded images works now with the WEB API
-   several improvements

### 1.0.1

-   fixed: issue with slashes in content when using the WEB API

### 1.0

-   initial release

## Additional Info

This Plugin requires [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=SendGrid)
