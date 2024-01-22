//Check responsive nav pills
jQuery(function() {
   if(jQuery('.responsive-nav-greedy').length > 0){

      var $btnnavgreedy = jQuery('.responsive-nav-greedy .togglegreedybtn');
      var $vlinksgreedy = jQuery('.responsive-nav-greedy .rhgreedylinks');
      var $hlinksgreedy = jQuery('.responsive-nav-greedy .hidden-links');

      var numOfItemsGreedy = 0;
      var totalSpaceGreedy = 0;
      var breakWidthsGreedy = [];

      var greedytimer = function() {
            $hlinksgreedy.addClass('rhhidden');
         };      

      // Get initial state
      $vlinksgreedy.children().width(function(i, w) {
         totalSpaceGreedy += w;
         numOfItemsGreedy += 1;
         breakWidthsGreedy.push(totalSpaceGreedy);
      });

      var availableSpace, numOfVisibleItems, requiredSpace;

      var rh_responsive_pills_check = function() {

      // Get instant state
      availableSpace = $vlinksgreedy.width() - 10;

      numOfVisibleItems = $vlinksgreedy.children().length;
      requiredSpace = breakWidthsGreedy[numOfVisibleItems - 1];

      // There is not enought space
      if (requiredSpace > availableSpace) {
         $vlinksgreedy.children().last().prependTo($hlinksgreedy);
         numOfVisibleItems -= 1;
         rh_responsive_pills_check();
      // There is more than enough space
      } else if (availableSpace > breakWidthsGreedy[numOfVisibleItems]) {
         $hlinksgreedy.children().first().appendTo($vlinksgreedy);
         numOfVisibleItems += 1;
      }
      // Update the button accordingly
      $btnnavgreedy.attr("count", numOfItemsGreedy - numOfVisibleItems);
      if (numOfVisibleItems === numOfItemsGreedy) {
         $btnnavgreedy.addClass('rhhidden');
      } else $btnnavgreedy.removeClass('rhhidden');
      }

      // Window listeners
      jQuery(window).on("resize", function() {
         rh_responsive_pills_check();
      });

      $btnnavgreedy.on('click', function() {
         $hlinksgreedy.toggleClass('rhhidden');
         clearTimeout(greedytimer);
      });

      $hlinksgreedy.on('mouseleave', function() {
         setTimeout(greedytimer, 2000);
      }).on('mouseenter', function() {
         clearTimeout(greedytimer);
      });      

      rh_responsive_pills_check();
   }
});