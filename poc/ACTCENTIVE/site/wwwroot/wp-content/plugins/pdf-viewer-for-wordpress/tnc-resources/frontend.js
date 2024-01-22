(function($){
  "use strict";
jQuery(document).ready(function($){
    let bookFlipMenuId = document.getElementById('bookFlip');
    let spreadOddMenuId = document.getElementById('spreadOdd');
    let spreadEvenMenuId = document.getElementById('spreadEven');
  
    bookFlipMenuId.addEventListener('click', removeShaking );
    spreadOddMenuId.addEventListener('click', removeShaking );
    spreadEvenMenuId.addEventListener('click', removeShaking );
    
    function removeShaking() {
      setTimeout(function(){
        let bookFlipMenuClassName = bookFlipMenuId.className;
        let spreadOddMenuClassName = spreadOddMenuId.className;
        let spreadEvenMenuClassName = spreadEvenMenuId.className;
        if (bookFlipMenuClassName.includes('toggled')) {
          if( spreadOddMenuClassName.includes('toggled') || spreadEvenMenuClassName.includes('toggled') ) {
            let tncPvfwScaleSelectValue = $('#scaleSelect').val();
            let tncPvfwScaleAllValue = ['auto', 'page-actual', 'page-fit', 'page-width', '0.5', '0.75', '1'];
  
            if ($.inArray(tncPvfwScaleSelectValue, tncPvfwScaleAllValue) !== -1) {
              $('#viewer').css({'overflow' : 'hidden'})
            } else {
              $('#viewer').css({'overflow' : 'inherit'})
            }
          }
        }
      }, 1000);
    }     
 });
})(jQuery);