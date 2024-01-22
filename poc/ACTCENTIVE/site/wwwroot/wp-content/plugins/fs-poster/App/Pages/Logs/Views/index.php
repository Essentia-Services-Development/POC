<?php

namespace FSPoster\App\Pages\Logs\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Request;

defined( 'ABSPATH' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Logs', 'css/fsp-logs.css' ); ?>">

<div id="fspInsightsDropdown" class="fsp-dropdown">
    <div class="fsp-dropdown-item" data-type="public"><?php echo fsp__( 'Hits' ); ?>: <span id="fsp_insights_hits"></span></div>
    <div class="fsp-dropdown-item"><?php echo fsp__( 'Comments' ); ?>: <span id="fsp_insights_comments"></span></div>
    <div class="fsp-dropdown-item" data-type="public"><?php echo fsp__( 'Shares' ); ?>: <span id="fsp_insights_shares"></span></div>
    <div class="fsp-dropdown-item" data-type="public"><?php echo fsp__( 'Likes' ); ?>: <span id="fsp_insights_likes"></span></div>
</div>

<div class="fsp-row">
	<input id="fspLogsScheduleID" type="hidden" value="<?php echo $fsp_params[ 'scheudleId' ]; ?>">
	<input id="fspRowsSelector" type="hidden" value="<?php echo Helper::getOption( 'logs_rows_count_' . get_current_user_id(), '4' ); ?>">
	<input id="fspShowLogsOf" type="hidden" value="<?php echo Helper::getOption('show_logs_of', 'own'); ?>">
	<input id="fspFilterSelector" type="hidden" value="<?php echo Request::get('filter_by', 'all'); ?>">
	<input id="fspSnSelector" type="hidden" value="<?php echo Request::get('sn_filter', 'all'); ?>">
	<div class="fsp-col-12 fsp-title fsp-logs-title">
		<div class="fsp-title-text">
			<?php echo fsp__( 'Logs' ); ?>
			<span id="fspLogsCount" class="fsp-title-count"></span>
		</div>
		<div class="fsp-title-button">
            <div class="fsp-title-selector">
                <label><?php echo fsp__( 'Delete logs' ); ?></label>
                <select id="fspDeleteLogs" class="fsp-form-select">
                    <option id="fspDeleteLogsDefault" value=""><?php echo fsp__( 'Select an option' ); ?></option>
                    <option value="all"><?php echo fsp__( 'All' ); ?></option>
                    <option value="only_errors"><?php echo fsp__( 'Only errors' ); ?></option>
                    <option value="only_selected_logs"><?php echo fsp__( 'Only selected logs' ); ?></option>
                    <option value="only_successful_logs"><?php echo fsp__( 'Only successful logs' ); ?></option>
                </select>
            </div>
            <button id="fspLogsFilter" class="fsp-button fsp-is-gray">
                <i class="fas fa-filter"></i>
                <span class="fsp-show"><?php echo fsp__( 'FILTER LOGS' ); ?></span>
            </button>
			<button id="fspExportLogs" class="fsp-button fsp-is-gray">
				<i class="fas fa-upload"></i>
				<span class="fsp-show"><?php echo fsp__( 'EXPORT TO CSV' ); ?></span>
			</button>
		</div>
	</div>
    <div class="fsp-col-12 fsp-check-all-row fsp-hide">
        <input type="checkbox" id="fspCheckAllLogs" class="fsp-form-checkbox">
    </div>
	<div id="fspLogs" class="fsp-col-12">
		<div id="fspLogs"></div>
	</div>
	<div id="fspLogsPages" class="fsp-col-12 fsp-logs-pagination"></div>
</div>
<script>
	FSPObject.page = <?php echo $fsp_params[ 'logs_page' ]; ?>;
    FSPObject.webhook_feed_id = <?php echo Request::get('webhook_feed_id', 0, 'int') ?>;

	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Logs', 'js/fsp-logs.js' ); ?>' );
	} );
</script>
