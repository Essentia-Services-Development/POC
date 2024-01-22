<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>

<?php if ( ! $fsp_params[ 'accountInf' ] ) { ?>
	<script>
		jQuery( 'document' ).ready( function ( $ ) {
			FSPoster.modalHide( $( '.fsp-modal' ) );

			FSPoster.toast( fsp__( 'You have not a permission for adding subreddit in this account.' ), 'warning' );
		} );
	</script>
<?php } ?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fab fa-reddit-alien"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Add a subreddit' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<div class="fsp-modal-step">
		<input type="hidden" id="fspAccountID" value="<?php echo $fsp_params[ 'accountId' ]; ?>">
		<div class="fsp-form-group">
			<label><?php echo fsp__( 'Select a subreddit' ); ?></label>
			<select class="fsp-form-select" id="fspSubredditSelector"></select>
		</div>
	</div>
	<div class="fsp-modal-step">
		<div id="fspFlairSelectorContainer" class="fsp-form-group fsp-hide">
			<label class="fsp-is-jb">
				<?php echo fsp__( 'Select flair' ); ?>
				<a class="fsp-tooltip" data-title="<?php echo fsp__( 'Selecting a Flair for some subreddits is a must. If you select the "No flair" option, it depends entirely on the rules of the subreddit whether your posts will be deleted or not by moderators later.' ); ?>">
					<i class="far fa-question-circle"></i>
				</a>
			</label>
			<select class="fsp-form-select" id="fspFlairSelector"></select>
		</div>
	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
	<button id="fspModalAddSubredditButton" class="fsp-button"><?php echo fsp__( 'GET ACCESS' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-subreddit.js' ); ?>' );
	} );
</script>
