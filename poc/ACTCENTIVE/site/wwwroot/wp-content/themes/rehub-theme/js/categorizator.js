jQuery(document).ready(function($) {
'use strict';	
    //category pager
   $('.cat-pagination').on('click', 'a:not(.active) ', function(){
      var multi_cat = $(this).closest('.multi_cat');
      var multi_cat_wrap = multi_cat.find('.multi_cat_wrap');
      var page = $(this).data('paginated');
      var data = {
         'action': 'multi_cat',
         'page': page,
         'tax': multi_cat.data('tax'),
         'term': multi_cat.data('term'),
         'nonce' : rhscriptvars.nonce,
      };

      multi_cat_wrap.addClass('loading');

      $.post(rhscriptvars.ajax_url, data, function(response) {
         if (response !== 'fail') {
            multi_cat_wrap.html(response);
            multi_cat.find('.cat-pagination a').removeClass('active');
            multi_cat.find('.cat-pagination a[data-paginated="' + page + '"]').addClass('active');           
         }
         multi_cat_wrap.removeClass('loading');
      });
   }); 
});