<?php defined('\ABSPATH') || exit; ?>
<?php
_wp_admin_html_begin();
wp_print_styles('colors');
wp_print_scripts('jquery');
?>

<?php
$table->prepare_items();
?>

<title><?php _e('Storefronts', 'affegg'); ?></title>
<style type="text/css">
    /* Account for .wp-toolbar */
    html {
        padding-top: 0 !important;
    }
    body {
        margin: 0 0 15px 15px;
    }

    /* Fix search field positioning */
    #affegg-page .search-box {
        position: relative;
        height: auto;
        width: auto;
        float: right;
        clear: none;
        margin: 0;
    }
    #affegg-page .subtitle {
        float: left;
        padding: 10px 0 0;
    }
    #affegg-page .search-box input[name="s"] {
        float: left;
        width: auto;
    }

    /* Fix pagination layout */
    #affegg-page .tablenav-pages {
        text-align: left;
    }
    #affegg-page .tablenav .tablenav-pages a {
        padding: 5px 12px;
        font-size: 16px;
    }
    #affegg-page .tablenav-pages .pagination-links .paging-input {
        font-size: 16px;
    }

    #affegg-page .tablenav-pages .pagination-links .current-page {
        padding: 4px;
        font-size: 16px;
    }

    /* Width and font weight for the columns */
    .affegg-all-tables thead .column-id {
        width: 50px;
    }
    .affegg-all-tables tbody .column-id,
    .affegg-all-tables tbody .column-name {
        font-weight: bold;
    }
    .affegg-all-tables thead .column-create_date {
        width: 150px;
    }
    .affegg-all-tables thead .column-affegg_action {
        width: 155px;
    }
    .affegg-all-tables tbody .column-affegg_action {
        padding: 4px 7px 1px;
        vertical-align: middle;
    }

    /* Shortcode input field */
    #affegg-page .table-shortcode-inline {
        background: transparent;
        border: none;
        color: #333333;
        width: 110px;
        margin: 0;
        padding: 0;
        font-weight: bold;
        font-size: 14px;
        -webkit-box-shadow: none;
        box-shadow: none;
        text-align: center;
        vertical-align: top;
    }

    #affegg-page .table-shortcode {
        cursor: text;
    }
    <?php if (is_rtl()) : ?>
        /* RTL CSS */
        body.rtl {
            margin: 0 15px 15px 0;
        }
        .rtl #affegg-page .search-box {
            float: left;
        }
        .rtl #affegg-page .subtitle {
            float: right;
        }
        .rtl #affegg-page .search-box input[name="s"] {
            float: right;
        }
        .rtl #affegg-page .table-shortcode-inline {
            width: 125px;
            font-size: 13px;
            vertical-align: baseline;
        }
    <?php endif; ?>
</style>
</head>
<body class="wp-admin wp-core-ui js iframe<?php echo is_rtl() ? ' rtl' : ''; ?>">
    <div id="affegg-page" class="wrap mceActionPanel">
        <h2>
            <?php _e('Storefronts', 'affegg'); ?>

        </h2>
        <form method="get" action="">
            <input type="hidden" name="action" value="<?php echo $_REQUEST['action']; ?>" />
            <?php $table->search_box(__('Search of storefronts', 'affegg'), 'affegg_search'); ?>
        </form>	

        <form id="eggs-table" method="GET">
            <input type="hidden" name="action" value="<?php echo $_REQUEST['action'] ?>"/>
            <?php $table->display() ?>
        </form>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.affegg-all-tables').on('click', '.insert-shortcode', function () {
                var win = window.dialogArguments || opener || parent || top;
                win.send_to_editor($(this).attr('title'));
            });
        });
    </script>
</body>
</html>