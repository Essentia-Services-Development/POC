var re_ajax_cache = {
    data: {},
    remove: function (cache_id) {
        delete re_ajax_cache.data[cache_id];
    },
    exist: function (cache_id) {
        if(jQuery('.custom_search_box').length){
            return false;
        }
        return re_ajax_cache.data.hasOwnProperty(cache_id) && re_ajax_cache.data[cache_id] !== null;
    },
    get: function (cache_id) {
        return re_ajax_cache.data[cache_id];
    },
    set: function (cache_id, cachedData) {
        re_ajax_cache.remove(cache_id);
        re_ajax_cache.data[cache_id] = cachedData;
    }
};

var re_ajax_search = {

    // Some variables
    _current_selection_index:0,
    _last_request_results_count:0,
    _first_down_up:true,

    init: function init() {

        // keydown on the text box
        jQuery('.re-ajax-search').on("keydown", jQuery.debounce(250, function(event) {
            var ajaxsearchitem = jQuery(this);
            if (
                (event.which && event.which == 39)
                || (event.keyCode && event.keyCode == 39)
                || (event.which && event.which == 37)
                || (event.keyCode && event.keyCode == 37))
            {
                //do nothing on left and right arrows
                re_ajax_search.re_ajax_set_focus(ajaxsearchitem);
                return;
            }

            if ((event.which && event.which == 13) || (event.keyCode && event.keyCode == 13)) {
                // on enter
                var re_ajax_search_cur = jQuery(this).parent().parent().find('.re-sch-cur-element');
                if (re_ajax_search_cur.length > 0) {
                    var re_searchopen_url = re_ajax_search_cur.find('.re-search-result-title a').attr('href');
                    window.location = re_searchopen_url;
                } else {
                    jQuery(this).parent().submit();
                }
                return false; //redirect for search on enter
            } else {

                if ((event.which && event.which == 40) || (event.keyCode && event.keyCode == 40)) {
                    // down
                    re_ajax_search.re_aj_search_move_key_down(ajaxsearchitem);
                    return false; //disable the envent

                } else if((event.which && event.which == 38) || (event.keyCode && event.keyCode == 38)) {
                    //up
                    re_ajax_search.re_aj_search_move_key_up(ajaxsearchitem);
                    return false; //disable the envent
                } else {

                    //for backspace we have to check if the search query is empty and if so, clear the list
                    if ((event.which && event.which == 8) || (event.keyCode && event.keyCode == 8)) {
                        //if we have just one character left, that means it will be deleted now and we also have to clear the search results list
                        var search_query = jQuery(this).val();
                        if (search_query.length == 1) {
                            jQuery(this).parent().parent().find('.re-aj-search-wrap').removeClass( 're-aj-search-open' ).empty();
                        }

                    }

                    //various keys
                    re_ajax_search.re_ajax_set_focus(ajaxsearchitem);
                    //jQuery('.re-aj-search-wrap').empty();
                    setTimeout(function(){
                        re_ajax_search.do_ajax_call(ajaxsearchitem);
                    }, 100);
                }
                return true;
            }
        }));

    },

    /**
     * moves the select up
     */
    re_aj_search_move_key_up: function re_aj_search_move_key_up(elem) {
        if (re_ajax_search._first_down_up === true) {
            re_ajax_search._first_down_up = false;
            if (re_ajax_search._current_selection_index === 0) {
                re_ajax_search._current_selection_index = re_ajax_search._last_request_results_count - 1;
            } else {
                re_ajax_search._current_selection_index--;
            }
        } else {
            if (re_ajax_search._current_selection_index === 0) {
                re_ajax_search._current_selection_index = re_ajax_search._last_request_results_count;
            } else {
                re_ajax_search._current_selection_index--;
            }
        }
        elem.parent().parent().find('.re-search-result-div').removeClass('re-sch-cur-element');
        if (re_ajax_search._current_selection_index  > re_ajax_search._last_request_results_count -1) {
            //the input is selected
            elem.closest('form').fadeTo(100, 1);
        } else {
            re_ajax_search.re_search_input_remove_focus(elem);
            elem.parent().parent().find('.re-search-result-div').eq(re_ajax_search._current_selection_index).addClass('re-sch-cur-element');
        }
    },

    /**
     * moves the select prompt down
     */
    re_aj_search_move_key_down: function re_aj_search_move_key_down(elem) {
        if (re_ajax_search._first_down_up === true) {
            re_ajax_search._first_down_up = false;
        } else {
            if (re_ajax_search._current_selection_index === re_ajax_search._last_request_results_count) {
                re_ajax_search._current_selection_index = 0;
            } else {
                re_ajax_search._current_selection_index++;
            }
        }
        elem.parent().parent().find('.re-search-result-div').removeClass('re-sch-cur-element');
        if (re_ajax_search._current_selection_index > re_ajax_search._last_request_results_count - 1 ) {
            //the input is selected
            elem.closest('form').fadeTo(100, 1);
        } else {
            re_ajax_search.re_search_input_remove_focus(elem);
            elem.parent().parent().find('.re-search-result-div').eq(re_ajax_search._current_selection_index).addClass('re-sch-cur-element');
        }
    },

    /**
     * puts the focus on the input box
     */
    re_ajax_set_focus: function re_ajax_set_focus(elem) {
        re_ajax_search._current_selection_index = 0;
        re_ajax_search._first_down_up = true;
        elem.closest('form').fadeTo(100, 1);
        elem.parent().parent().find('.re-search-result-div').removeClass('re-sch-cur-element');
    },

    /**
     * removes the focus from the input box
     */
    re_search_input_remove_focus: function re_search_input_remove_focus(elem) {
        if (re_ajax_search._last_request_results_count !== 0) {
            elem.closest('form').css('opacity', 0.5);
        }
    },

    /**
     * AJAX: process the response from the server
     */
    process_ajax_response: function (data, callelem) {
        var current_query = callelem.val().trim();


        //the search is empty - drop results
        if (current_query == '') {
            callelem.parent().parent().find('.re-aj-search-wrap').empty();
            return;
        }

        var td_data_object = JSON.parse(data); //get the data object
        //drop the result - it's from a old query
        if (td_data_object.re_search_query !== current_query) {
            return;
        }

        //reset the current selection and total posts
        re_ajax_search._current_selection_index = 0;
        re_ajax_search._last_request_results_count = td_data_object.re_total_inlist;
        re_ajax_search._first_down_up = true;


        //update the query
        callelem.parent().parent().find('.re-aj-search-wrap').addClass( 're-aj-search-open' ).html(td_data_object.re_data);
        var iconsearch = callelem.parent().find('.rhi-sync'); 
        iconsearch.removeClass('rhi-sync fa-spin').addClass('rhi-search');
        callelem.removeClass('searching-now');

    },

    /**
     * AJAX: do the ajax request
     */
    do_ajax_call: function do_ajax_call(elem) {
        var posttypes = elem.data('posttype');
        var enable_compare = elem.data('enable_compare');
        var aff = elem.data('aff');
        if(elem.prevObject == undefined){
            var catid = elem.data('catid');
        }else{
            var catid = elem.attr('data-catid');
        }
        var callelem = elem;
        if (elem.val() == '') {
            re_ajax_search.re_ajax_set_focus(callelem);
            return;
        }

        var search_query = elem.val();

        //do we have a cache hit
        if (re_ajax_cache.exist(search_query)) {
            re_ajax_search.process_ajax_response(re_ajax_cache.get(search_query), callelem);
            return;
        }

        var iconsearch =  elem.parent().find('.rhi-search');     
        iconsearch.removeClass('rhi-search').addClass('rhi-sync fa-spin');
        elem.addClass('searching-now');

        jQuery.ajax({
            type: 'POST',
            url: rhscriptvars.ajax_url,
            data: {
                action: 'rehub_ajax_search',
                re_string: search_query,
                posttypesearch: posttypes,
                enable_compare : enable_compare,
                aff_link: aff,
                catid : catid,
                security : rhscriptvars.searchnonce,
            }
        }).done(function(data, textStatus, XMLHttpRequest){
            re_ajax_cache.set(search_query, data);
            re_ajax_search.process_ajax_response(data, callelem);
        });
    }
};

jQuery(document).ready(function($) {
   'use strict';    
   re_ajax_search.init();
});