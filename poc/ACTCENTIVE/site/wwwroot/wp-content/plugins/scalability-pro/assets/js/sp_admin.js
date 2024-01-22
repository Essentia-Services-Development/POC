
function spro_click_tab(tabindex) {
	console.log('scalability pro settings loaded & tab clicked: ' + tabindex);

    jQuery('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
	jQuery('.settings_page_scalabilitypro .spro-tab').css('display', 'none');
    jQuery('.nav-tab-wrapper .nav-tab').eq(tabindex).addClass('nav-tab-active');
    jQuery('.nav-tab-wrapper .nav-tab').eq(tabindex).focus();
	jQuery('.settings_page_scalabilitypro .spro-tab').eq(tabindex).css('display', 'block');
	jQuery('.settings_page_scalabilitypro .spro-tab').eq(tabindex).css('display', 'block');
	jQuery('#spro_tab').val(tabindex);
}
jQuery(document).ready(function() {

	if (location.hash !== '') {
        spro_click_tab(location.hash.replace('#', ''));
    } else {
		if (jQuery('#spro_tab').val() > 0) {
			location.hash = '#'+jQuery('#spro_tab').val();
		}
        spro_click_tab(jQuery('#spro_tab').val());
    }

});
jQuery(window).bind("popstate", function(e) {
    var state = e.originalEvent.state;
    if ( state === null ) { 
        console.log("step one");
    } else { 
        console.log(state.foo);
    }

    if (location.hash !== '') {
        spro_click_tab(location.hash.replace('#', ''));
    } else {
        spro_click_tab(0);
    }
});

jQuery(document).on('click', '.settings_page_scalabilitypro .nav-tab-wrapper .nav-tab', function(e) {
    window.history.pushState(null, null, "#" + jQuery(this).index());

    spro_click_tab(jQuery(this).index());
	e.preventDefault();
	e.stopPropagation();
});



function wpisp_saveprofile() {
	//console.log(response);
	qm = jQuery('#sp_monitoring iframe').contents();
	qm = jQuery('#qm', qm).wrap('p').html();

	profile = jQuery('#sp_monitoring iframe').data('profile');
	tindex = jQuery('#sp_monitoring iframe').data('index');
	url = jQuery('#sp_monitoring iframe').attr('src');
	if (jQuery('#qm-overview table tr td:first-child', qm).length > 0) {
		profiletime = jQuery('#qm-overview table tr td:first-child', qm).html().split('<br>')[0];
		jQuery('#p' + tindex + ' .' + profile).html(parseFloat(profiletime).toFixed(2) + 's');
		profileresults[profile, url] = qm;
	} else {
//		profileresults[profile, url] = -1; // could not grab profile results for some reason (timeout?)
	}
	
	
	jQuery.post(sproVars.ajaxUrl, 
		{
			action: 'wpisp_saveperfresults',
			profileid: profile,
			url: url,
			profileresults: qm
		},
		function(response) {
			wpiindex = jQuery('#sp_monitoring iframe').data('index');
			wpiindex++;
			jQuery('#sp_monitoring iframe').remove();
			if (wpiindex < wpisiteurls.length) {
				url = window.wpisiteurls[wpiindex];
				if (profile == 'before') {
					jQuery('#sp_results').append('<div class="perfrow" id="p' + wpiindex + '"><div class="url">' + url + '</div><div class="before"></div></div>');					
					jQuery('#sp_monitoring').append('<iframe data-index="' + wpiindex + '" data-profile="' + profile + '" src="' + url + '" onload="wpisp_saveprofile();"></iframe>');
				} else {
					jQuery.get(url, function() {
						jQuery('#sp_monitoring').append('<iframe data-index="' + wpiindex + '" data-profile="' + profile + '" src="' + url + '" onload="wpisp_saveprofile();"></iframe>');						
					});					
				}
			} else {
				scalabilityprostage++;
				if (scalabilityprostage >= 1) {
					jQuery('#sp_progress').append('\nProfiling complete.');
					jQuery('#startoptimisation').remove();
					jQuery('#sp_submitreport').append('<p>Now click Submit Report to WPI to submit your performance report and plugin list to us to help us optimise Scalability Pro further:</p>');
					jQuery('#sp_submitreport').append('<p><a class="button" href="javascript:void(0);" id="submitreport">Submit Report to WPI</a></p>');
				}
				
			}

			
		}
	);
}
var scalabilityprostage = 1;
jQuery(document).on('click', '#startoptimisation', function() {
	jQuery('#sp_progress').append('<br>Started profiling - please wait...');
	jQuery('#startoptimisation').html('Processing...');
	jQuery('#startoptimisation').attr('disabled', true);
	var wpiindex = 0; 
	var wpiprofile = 'before'; //todo: add profile name here
	
	url = wpisiteurls[wpiindex];			
	jQuery('#sp_results').append('<div class="perfrow"><div class="url">URL</div><div class="before">Speed</div></div>');
	jQuery('#sp_results').append('<div class="perfrow" id="p' + wpiindex + '"><div class="url">' + url + '</div><div class="before"></div></div>');
	jQuery('#sp_monitoring').append('<iframe data-index="' + wpiindex + '" data-profile="' + wpiprofile + '" src="' + url + '" onload="wpisp_saveprofile();"></iframe>');
});

jQuery("#wpicreateindexes").click(function() {
	scalability_pro_update_indexes();
});

function scalability_pro_update_indexes() {
    let selectedIndexes = jQuery("input[name='indexes[]']:checked").map(function() {
        return jQuery(this).val();
    }).get();

    jQuery.ajax({
        type: "POST",
        url: sproVars.ajaxUrl,
        data: {
            action: "wpi_createindexes",
            indexes: selectedIndexes
        },
        success: function(response) {
			jQuery('#sp_progress').append('<br>B-tree indexes updated');
			jQuery('#wpicreateindexes').text('Complete');
        },
		error: function(x, t, m) {
			if(t==="timeout") {
				jQuery('#sp_progress').append('<br>Working on creating b-tree indexes - please wait...');
				window.setTimeout(function() {scalability_pro_update_indexes();}, 25000);
			} else {
				jQuery('#sp_progress').append('<4>Error</h4>');
				jQuery('#sp_progress').append('<pre>' + t + '</pre>');
			}
		}
    });
}


jQuery(document).on('click', '#wpidropindexes', function () {
	jQuery('#wpidropindexes').text('Working...');
	jQuery.post(
			sproVars.ajaxUrl,
			{action: 'wpi_dropindexes'},
			function (response) {
				alert('Indexes dropped');
				jQuery('#wpidropindexes').text('Complete');
				console.log(response);
			}
	);

});
jQuery(document).on('click', '#wpicreateindexes', function () {
	jQuery('#wpicreateindexes').text('Working...');
	scalability_pro_update_indexes();

});
jQuery(document).on('click', '#submitreport', function() {
	//todo: submit profile to WPI
	console.log (profileresults.length);
	console.log (profileresults.size);
	console.log (profileresults);
	jQuery.ajax({
		type: 'POST',
		url: 'https://profiling.wpintense.com/wp-admin/admin-ajax.php',
		crossDomain: true,
		data: {
				"action": 'wpi_submitreport',
				"websiteurl": window.location.href,
				"profile": JSON.stringify(profileresults)
		},
		dataType: 'jsonp',
		success: function(responseData, textStatus, jqXHR) {
			alert(responseData);
		},
		error: function (responseData, textStatus, errorThrown) {
			alert('Submit failed.');
		}
	});
	
});
jQuery(document).on('change', '#sponoffstatus', function() {
	console.log ('changing status');
	jQuery.post(sproVars.ajaxUrl, 
		{
			action: 'wpisp_switchonoff',
			newstatus: jQuery('#sponoffswitch').val()
		},
		function(response) {
			console.log(response);
		}
	);	
});

jQuery(document).on('click', '#closereport', function() {
	jQuery('#wpisp-first-run').css('display', 'none');
	wpisp_showprofile();
});
function wpisp_showprofile() {
	jQuery('#wpisp_perf_profile').css('display', 'block');
	jQuery.each(profileresults , function( index, obj ) {
		var perfout = '';
		perfout += '<div class="perfhrow"><div class="perfurl"></div>';
		jQuery.each(obj, function( key, value ) {
			perfout += '<div class="perfurlresult">' + key + '</div>';
			return false;
		});
		perfout += '</div>';
		jQuery('#perfprofile').append(perfout);		
		return false;
	});
	jQuery.each(profileresults , function( index, obj ) {
		var perfout = '';
		perfout += '<div class="perfhrow"><div class="perfurl">' + index + '</div>';
		jQuery.each(obj, function( key, value ) {
			perfout += '<div class="perfurlresult">' + value + '</div>';
		});
		perfout += '</div>';
		jQuery('#perfprofile').append(perfout);		
	});
}
jQuery(document).on('click', '.wpiperf_remove_post_type', function () {
	
	post_type_name = jQuery(this).closest('.wpiperf_image_sizes').find('.post_type_name').val();
	console.log('remove clicked ' + post_type_name);
	jQuery(this).closest('.wpiperf_image_sizes').remove();
	//find option with post_type_name and enable it
	jQuery('#spro_remove_images_select_container').show();

});
jQuery(document).on('click', '#wpiperf_add_post_type', function () {
	postTypeName = jQuery('#wpiperf_post_type_select option:selected').val();
	postTypeLabel = jQuery('#wpiperf_post_type_select option:selected').text();

	if (jQuery('#wpiperf_post_type_select option[value="' + postTypeName + '"]').prop('disabled')) {
		return;
	}

	console.log(postTypeName);
	console.log(postTypeLabel);

	const postTypeOptionsHtml = postTypeOptionsTemplate.innerHTML.replace(/\{\{post_type\}\}/g, postTypeName).replace('{{post_type_label}}', postTypeLabel);
	const postTypeOptionsElement = document.createElement('div');
	postTypeOptionsElement.innerHTML = postTypeOptionsHtml;
	
	document.getElementById('wpiperf_post_types').appendChild(postTypeOptionsElement);
	jQuery('#spro_remove_images_select_container').hide();
});

jQuery(document).ready(function($) {

	//make the slow queries column span 2 columns
	var row = $('#slow-queries-table').closest('tr');
    var title = row.find('> th');
    var content = row.find('> td');

    // Move title to be above content
    title.insertBefore(row).attr('colspan', '2');
    content.attr('colspan', '2');

    // Remove the now-empty row
//    row.remove();


    var offset = jQuery('.slow_query_id').last().text();

    // When the user scrolls in the slow queries table...
	var fetching = false;
    $('.spro_slow_queries').scroll(function() {
        if ($(this).scrollTop() + $(this).innerHeight() >= ($(this)[0].scrollHeight - 20)) {
			if (!fetching) {
				fetching = true;
				$.post(
					sproVars.ajaxUrl, 
					{
						'action': 'spro_fetch_slow_queries',
						'offset': offset
					}, 
					function(response){
						// Append the response at the end of the table
						$('#slow-queries-table tbody').append(response);
						offset = jQuery('.slow_query_id').last().text();
						fetching = false;
					}
				);
			}
        }
    });
});
jQuery(document).ready(function($) {
    $('#selectAll').click(function() {
        if ($(this).prop('checked')) {
            $('.wpi-index-table tbody input[type="checkbox"]').prop('checked', true);
        } else {
            $('.wpi-index-table tbody input[type="checkbox"]').prop('checked', false);
        }
    });

    // If any checkbox is unchecked, the "Select All" checkbox should also be unchecked
    $('.wpi-index-table tbody input[type="checkbox"]').click(function() {
        if (!$(this).prop('checked')) {
            $('#selectAll').prop('checked', false);
        }
    });
});

jQuery(document).ready(function($) {
    $('#create_symlink').click(function(event) {
		console.log('create symlink clicked');
		event.preventDefault();  // Prevent default action
        $.post(sproVars.ajaxUrl, { action: 'spro_create_symlink' }, function(response) {
            location.reload();
        }).fail(function() {
            alert('Failed to create symlink.');
        });
    });

    $('#delete_symlink').click(function(event) {
		console.log('delete symlink clicked');
		event.preventDefault();  // Prevent default action
        $.post(sproVars.ajaxUrl, { action: 'spro_delete_symlink' }, function(response) {
            location.reload();
        }).fail(function() {
            alert('Failed to delete symlink.');
        });
    });
});
jQuery(document).ready(function($) {
    $('#clear_post_count_cache').click(function(e) {
		console.log('clearing post count cache');
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: sproVars.ajaxUrl,
            data: {
                action: 'spro_clear_postcount_cache',
                nonce: sproVars.nonce
            },
            success: function(response) {
                alert(response);
            }
        });
    });
});

jQuery(document).ready(function($) {
    $('#truncate-table-button').click(function() {
        $.ajax({
            url: sproVars.ajaxUrl,
            type: 'post',
			dataType: 'json',
            data: {
                action: 'truncate_spro_table'
            },
            success: function(response) {
				console.log(response);
                alert('Table emptied.');
            }
        });
    });
});
