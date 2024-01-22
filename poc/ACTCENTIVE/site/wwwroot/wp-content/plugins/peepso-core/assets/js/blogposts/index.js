import $ from 'jquery';
import peepso, { observer } from 'peepso';
import { blogposts as data } from 'peepsodata';
import PsPageBlogpost from './page-blogposts';

peepso.blogposts = {
	deletePost( id ) {
		activity.action_delete( id, {}, data.delete_post_warning );
		return false;
	}
};

// Handle markdown in authorbox.
$( () => {
	let $items = $( '.ps-blogposts__authorbox .ps-blogposts__authorbox-desc' );
	if ( $items.length ) {
		$items.each( function() {
			let $item = $( this );
			if ( $item.find( '.peepso-markdown' ).length ) {
				let html = observer.applyFilters( 'peepso_parse_content', $item.html() );
				$item.html( html );
			}
		} );
	}
} );
