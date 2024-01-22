import $ from 'jquery';
import NotificationPopoverGeneral from './popover';

// Initialize general notification popover icons.
$( $ => {
    $( '.ps-js-notifications' ).each(function() {
        new NotificationPopoverGeneral( this );
    });
});
