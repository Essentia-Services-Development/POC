(function () {
    "use strict";
    function equalize(table){
        var groupName = Array.prototype.slice.call( table.querySelectorAll('[data-match-height]')),
            groupHeights = {};
    
        for (var item of groupName) {
            var data = item.getAttribute('data-match-height');
            item.style.minHeight = 'auto';
            item.style.height = 'auto';
    
            if (groupHeights.hasOwnProperty(data)) {
            Object.defineProperty(groupHeights, data, {
                value: Math.max(groupHeights[data], item.offsetHeight),
                configurable: true,
                writable: true,
                enumerable: true
            });
            } else {
                groupHeights[data] = item.offsetHeight;
            }
        }
    
        var groupHeightsMax = groupHeights;

        Object.getOwnPropertyNames(groupHeightsMax).forEach(function(value) {
            var elementsToChange = table.querySelectorAll(
                "[data-match-height='" + value + "']"
            ),
            elementsLength = elementsToChange.length;
    
            for (var i = 0; i < elementsLength; i++) {
            elementsToChange[i].style.height =
                Object.getOwnPropertyDescriptor(groupHeightsMax, value).value +
                'px';
            }
        });
    }

    var tables = document.querySelectorAll('.comparison-table');
    var swiper = {destroyed: true}; 

    
    document.addEventListener('DOMContentLoaded', function() {
        
        for (let i = 0; i < tables.length; i++) {
            let table = tables[i];
            equalize(table);
            let tableType = table.getAttribute('data-table-type');
            if(window.innerWidth < 768 && 'slide' === tableType){
                swiper = new Swiper( '.swiper-container', {
                    navigation: {
                      nextEl: '.comparison-control-next',
                      prevEl: '.comparison-control-prev',
                    }
                });
            }    
        }
    });
    window.addEventListener('resize', function() { 
        
        for (let i = 0; i < tables.length; i++) {
            let table = tables[i];
            equalize(table);
            let tableType = table.getAttribute('data-table-type');
            if( 'slide' === tableType ){
                if(window.innerWidth >= 768 && !swiper.destroyed ){
                    swiper.destroy( true, true );
                }
                if(window.innerWidth < 768 && swiper.destroyed ){
                    swiper = new Swiper( '.swiper-container', {
                        navigation: {
                        nextEl: '.comparison-control-next',
                        prevEl: '.comparison-control-prev',
                        }
                    });
                }
            }  
        }
    } );
})();