<?php

namespace FSPoster\App\Pages\Base\Views;

use FSPoster\App\Providers\Helper;
use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Request;

defined( 'ABSPATH' ) or exit;
?>


<div class="fsp-row">
	<div class="fsp-col-12 fsp-title">
		<div class="fsp-title-text">
			<?php echo fsp__( 'Schedules' ); ?>
			<span id="fspSchedulesCount" class="fsp-title-count"></span>
		</div>
		<div class="fsp-title-button">

			<button id="fspRemoveSelected" class="fsp-button fsp-is-red fsp-hide">
				<i class="far fa-trash-alt"></i>
				<span><?php echo fsp__( 'DELETE' ); ?></span>
				<span id="fspSelectedCount" class="fsp-schedule-selected-count">(<span></span>)</span>
			</button>

            <div class="fsp-form-input-has-icon fsp-schedule-search">
                <i class="fas fa-search"></i>
                <input id="fsp-schedule-search-input" autocomplete="off" class="fsp-form-input" value="<?php echo htmlspecialchars( Request::get( "search", "" ) ) ?>" placeholder="<?php echo fsp__( 'Search' ) ?>">
            </div>
            <div class="fsp-schedule-count-selector">
                <select id="fspScheduleCountSelector" class="fsp-form-select">
                    <option value="" disabled selected><?php echo fsp__( 'Count of rows' ); ?></option>
                    <?php
                        $schedules_rows_count = Helper::getOption( 'schedules_rows_count_' . get_current_user_id(), '4' );
                    ?>
                    <option value="4" <?php if($schedules_rows_count == '4') echo 'selected'?>>4</option>
                    <option value="8" <?php if($schedules_rows_count == '8') echo 'selected'?>>8</option>
                    <option value="15" <?php if($schedules_rows_count == '15') echo 'selected'?>>15</option>
                </select>
            </div>
			<a href="?page=fs-poster-schedules&view=calendar" class="fsp-button fsp-is-info">
				<i class="far fa-calendar-alt"></i>
				<span><?php echo fsp__( 'CALENDAR' ); ?></span>
			</a>
			<button class="fsp-button fsp-is-danger" data-load-modal="add_schedule" id="createNewScheduleBtn">
				<i class="fas fa-plus"></i>
				<span><?php echo fsp__( 'SCHEDULE' ); ?></span>
			</button>
			<a href="https://www.fs-poster.com/documentation/how-to-set-up-a-cron-job-on-wordpress-fs-poster-wp-plugin" target="_blank" class="fsp-button fsp-is-red fsp-tooltip" data-title="<?php echo fsp__( 'If you want the Schedule module to work on time, please configure Cron Job on your website. Click the button to learn more.' ); ?>">
				<i class="fas fa-question"></i>
				<span><?php echo fsp__( 'HAVE ISSUES?' ); ?></span>
			</a>
		</div>
	</div>
	<div class="fsp-col-12 fsp-schedules" id="fspSchedules">

		<div class="fsp-card fsp-emptiness fsp-hide">
			<div class="fsp-emptiness-image">
				<img src="<?php echo Pages::asset( 'Base', 'img/empty.svg' ); ?>">
			</div>
			<div class="fsp-emptiness-text">
				<?php echo fsp__( 'There haven\'t been created any schedules yet.' ); ?>
			</div>
		</div>

	</div>

    <div id="fspSchedulesPages" class="fsp-col-12 fsp-schedules-pagination"></div>

</div>
<?php /** @var $fsp_params array */?>
<script>
    FSPObject.page = "<?php echo $fsp_params[ 'schedule_page' ];?>";
</script>

