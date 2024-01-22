jQuery(document).ready(function ($) {
    'use strict';


    function GCEL_ajax_load(ev){
        if (ev.target.classList.contains('loaded')) return;
        var post_id = ev.target.getAttribute('class').match(/load-block-([0-9]+)/)[1];
        var post_id = parseInt(post_id);
        var blockforload = document.querySelector(".gc-ajax-load-block-" + post_id);
        blockforload.classList.add("loading", "re_loadingafter", "padd20","font200","lightgreycolor");

        const request = new XMLHttpRequest();
        request.open('POST', gcreusablevars.ajax_url, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.responseType = 'json';
        request.onload = function () {
            if (this.status >= 200 && this.status < 400) {
                let responseobj = this.response.data;
                blockforload.classList.remove("loading", "re_loadingafter", "padd20","font200","lightgreycolor");
                ev.target.classList.add("loaded");
                blockforload.insertAdjacentHTML('beforeend',responseobj);
    
            } else {
                // Response error
            }
        };
        request.onerror = function() {
            // Connection error
        };
        request.send('action=rh_el_reusable_load&security=' + gcreusablevars.reusablenonce + '&post_id=' + post_id);
    }

    let gcreusablemouse = document.getElementsByClassName('gc-el-onhover');
    for (let i = 0; i < gcreusablemouse.length; i++) {
        let Node = gcreusablemouse[i];
        Node.addEventListener('mouseenter', function (ev) {
            GCEL_ajax_load(ev);
        });
    };

    let gcreusableclick = document.getElementsByClassName('gc-el-onclick');
    for (let i = 0; i < gcreusableclick.length; i++) {
        let Node = gcreusableclick[i];
        Node.addEventListener('click', function (ev) {
            GCEL_ajax_load(ev);
        });
    };

    const gcELLoadonviewObserves = document.getElementsByClassName('gc-el-onview');

    let gcelobserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                let ev = entry;
                GCEL_ajax_load(ev);
                gcelobserver.disconnect();
            }
        });
    });
    
    for (let itemobserve of gcELLoadonviewObserves) {
        gcelobserver.observe(itemobserve);
    }

});