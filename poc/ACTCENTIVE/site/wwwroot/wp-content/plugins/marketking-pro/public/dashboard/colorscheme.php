<?php

/*

* @version 1.0.0

This template file can be edited and overwritten with your own custom template. To do this, simply copy this file under your theme (or child theme) folder, in a folder named 'marketking', and then edit it there. 

For example, if your theme is storefront, you can copy this file under wp-content/themes/storefront/marketking/ and then edit it with your own custom content and changes.

*/


?><?php
// Load colors
$color = get_option( 'marketking_main_dashboard_color_setting', '#854fff' );
$colorhover = get_option( 'marketking_main_dashboard_hover_color_setting', '#6a29ff' );

?>
<style type="text/css">
    .user-avatar, [class^="user-avatar"]:not([class*="-group"]),.datepicker table tr td.today:hover, .datepicker table tr td.today:hover:hover, .datepicker table tr td.today.disabled:hover, .datepicker table tr td.today.disabled:hover:hover, .datepicker table tr td.today:active, .datepicker table tr td.today:hover:active, .datepicker table tr td.today.disabled:active, .datepicker table tr td.today.disabled:hover:active, .datepicker table tr td.today.active, .datepicker table tr td.today:hover.active, .datepicker table tr td.today.disabled.active, .datepicker table tr td.today.disabled:hover.active, .datepicker table tr td.today.disabled, .datepicker table tr td.today:hover.disabled, .datepicker table tr td.today.disabled.disabled, .datepicker table tr td.today.disabled:hover.disabled, .datepicker table tr td.today[disabled], .datepicker table tr td.today:hover[disabled], .datepicker table tr td.today.disabled[disabled], .datepicker table tr td.today.disabled:hover[disabled],.datepicker table tr td.range.today:hover, .datepicker table tr td.range.today:hover:hover, .datepicker table tr td.range.today.disabled:hover, .datepicker table tr td.range.today.disabled:hover:hover, .datepicker table tr td.range.today:active, .datepicker table tr td.range.today:hover:active, .datepicker table tr td.range.today.disabled:active, .datepicker table tr td.range.today.disabled:hover:active, .datepicker table tr td.range.today.active, .datepicker table tr td.range.today:hover.active, .datepicker table tr td.range.today.disabled.active, .datepicker table tr td.range.today.disabled:hover.active, .datepicker table tr td.range.today.disabled, .datepicker table tr td.range.today:hover.disabled, .datepicker table tr td.range.today.disabled.disabled, .datepicker table tr td.range.today.disabled:hover.disabled, .datepicker table tr td.range.today[disabled], .datepicker table tr td.range.today:hover[disabled], .datepicker table tr td.range.today.disabled[disabled], .datepicker table tr td.range.today.disabled:hover[disabled],.datepicker table tr td.active, .datepicker table tr td.active:hover, .datepicker table tr td.active.disabled, .datepicker table tr td.active.disabled:hover,.datepicker table tr td span.active, .datepicker table tr td span.active:hover, .datepicker table tr td span.active.disabled, .datepicker table tr td span.active.disabled:hover, .user-avatar, [class^="user-avatar"]:not([class*="-group"]), .btn-primary, .nav-tabs .nav-link:after, .custom-control-input:checked ~ .custom-control-label::before, .custom-control-input:not(:disabled):active ~ .custom-control-label::before, .salesking_available_payout_card, .badge-primary, .nk-msg-menu-item a:after, .user-avatar, [class^="user-avatar"]:not([class*="-group"]), .page-item.active .page-link, .btn-outline-primary:hover, .marketking-icon-main, .woocommerce-exporter-button.button-primary, .woocommerce-progress-form-wrapper .button-primary, progress::-webkit-progress-value, progress::-moz-progress-bar{
        background: <?php echo esc_html( $color ); ?> ;
    }

    .card.is-dark{
        background: <?php echo esc_html( $color ); ?>;
        filter: grayscale(0.4);
    }

    .btn-primary, .form-control:focus, .dual-listbox .dual-listbox__search:focus, .custom-control-input:checked ~ .custom-control-label::before, .custom-control-input:not(:disabled):active ~ .custom-control-label::before, .badge-primary, .badge-primary, .page-item.active .page-link, .custom-control-input:focus:not(:checked) ~ .custom-control-label::before, .btn-outline-primary, .btn-outline-primary:hover, .btn-outline-primary:focus, .btn-outline-primary:not(:disabled):not(.disabled):active, .btn-outline-primary:not(:disabled):not(.disabled).active, .show > .btn-outline-primary.dropdown-toggle{
        border-color: <?php echo esc_html( $color ); ?>;
    }

    a, .link-list a:hover, .is-light .nk-menu-link:hover, .is-light .active > .nk-menu-link, .nk-menu-link:hover .nk-menu-icon, .nk-menu-item.active > .nk-menu-link .nk-menu-icon, .nk-menu-item.current-menu > .nk-menu-link .nk-menu-icon, .user-balance, .link-list-menu li.active > a, .link-list-menu a.active, .link-list-menu a:hover, .link-list-menu li.active > a .icon, .link-list-menu a.active .icon, .link-list-menu a:hover .icon, .link-list-menu li.active > a:after, .link-list-menu a.active:after, .link-list-menu a:hover:after, .nav-tabs .nav-link.active, .nk-msg-menu-item.active a, .nk-msg-menu-item a:hover, .nk-menu-badge, .icon-avatar, .user-avatar[class*="-purple-dim"], .page-link:hover,.link-list-opt a:hover, .nav-tabs .nav-link:focus, .btn-outline-primary{ 
        color: <?php echo esc_html( $color ); ?>;
    }

    .bg-primary, .page-item.active .page-link{
        background: <?php echo esc_html( $color ); ?>!important;
    }

    #salesking_dashboard_customers_table .bg-primary, .btn-outline-primary:not(:disabled):not(.disabled):active, .btn-outline-primary:not(:disabled):not(.disabled).active, .show > .btn-outline-primary.dropdown-toggle {
        background-color: <?php echo esc_html( $color ); ?>!important;
    }

    .link-primary, .text-primary{
        color: <?php echo esc_html( $color ); ?>!important;
    }

    a:hover {
        color: <?php echo esc_html( $colorhover ); ?>;
    }

    .icon-avatar, .nk-menu-badge, .user-avatar[class*="-purple-dim"]{
        background: #ebebeb;
    }

    .btn-primary:hover, .btn-primary:not(:disabled):not(.disabled):active, .btn-primary:not(:disabled):not(.disabled).active, .show > .btn-primary.dropdown-toggle, .btn-primary:focus, .btn-primary.focus, .salesking_available_payout_header, .woocommerce-exporter-button.button-primary:hover, .woocommerce-progress-form-wrapper .button-primary {
        background-color: <?php echo esc_html( $colorhover ); ?>;
    }

    .btn-primary:hover, .btn-primary:not(:disabled):not(.disabled):active, .btn-primary:not(:disabled):not(.disabled).active, .show > .btn-primary.dropdown-toggle, .btn-primary:focus, .btn-primary.focus{
        border-color: <?php echo esc_html( $colorhover ); ?>;
    }

    .btn-primary:not(:disabled):not(.disabled):active:focus, .btn-primary:not(:disabled):not(.disabled).active:focus, .show > .btn-primary.dropdown-toggle:focus, .btn-primary:focus, .btn-primary.focus{
        box-shadow: 0 0 0 0.2rem <?php echo esc_html($colorhover); ?> ;
    }

    .nk-reply-item .user-card .user-avatar{
        background-color: <?php echo esc_html( $color ); ?>!important;
        filter: hue-rotate(-25deg);
    }

    .alert-primary.alert-icon{
        background-color: #fff;
        border-color: #d6d6d6;
        color: <?php echo esc_html( $color ); ?>;
    }

</style>