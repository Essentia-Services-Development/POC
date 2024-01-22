jQuery(document).ready(function($) {

   'use strict';

    //SVG blobs
    if($('.rh-svgblob-wrapper').length){
        $('.rh-svgblob-wrapper').each(function(el){    
            
            var id_scope = $(this).attr('data-id');

            //console.log(elementSettings);

            var numPoints = parseInt($(this).data('numpoints'));
            var minRadius = parseInt($(this).data('minradius'));
            var maxRadius = parseInt($(this).data('maxradius'));
            var minDuration = parseInt($(this).data('minduration'));
            var maxDuration = parseInt($(this).data('maxduration'));
            var tensionPoints = parseInt($(this).data('tensionpoints'));

            var blob1 = createBlob({
                element: document.querySelector("#rhblobpath-"+id_scope),
                numPoints: numPoints, //5,
                centerX: 300,
                centerY: 300,
                minRadius: minRadius, //200,
                maxRadius: maxRadius, //225,
                minDuration: minDuration,
                maxDuration: maxDuration,
                tensionPoints: tensionPoints,
            });
            

        });

        function createBlob(options) {
           
            var points = [];  
            var path = options.element;
            var slice = (Math.PI * 2) / options.numPoints;
            var startAngle = random(Math.PI * 2);
          
            var tl = gsap.timeline({
                onUpdate: update
            });  
          
            for (var i = 0; i < options.numPoints; i++) {
                var angle = startAngle + i * slice;
                var duration = random(options.minDuration, options.maxDuration);
                
                var point = {
                    x: options.centerX + Math.cos(angle) * options.minRadius,
                    y: options.centerY + Math.sin(angle) * options.minRadius
                };   
                
                var tween = gsap.to(point, {
                    duration: duration,
                    x: options.centerX + Math.cos(angle) * options.maxRadius,
                    y: options.centerY + Math.sin(angle) * options.maxRadius,
                    repeat: -1,
                    yoyo: true,
                    ease: Sine.easeInOut
                });
                
                tl.add(tween, -random(duration));
                points.push(point);
            }
          
            options.tl = tl;
            options.points = points;
          
            function update() {
                path.setAttribute("d", cardinal(points, true, options.tensionPoints));
            }
            return options;
        }

        // Cardinal spline - a uniform Catmull-Rom spline with a tension option
        function cardinal(data, closed, tension) {
          
          if (data.length < 1) return "M0 0";
          if (tension == null) tension = 1;
          
          var size = data.length - (closed ? 0 : 1);
          var path = "M" + data[0].x + " " + data[0].y + " C";
          
          for (var i = 0; i < size; i++) {
            
            var p0, p1, p2, p3;
            
            if (closed) {
              p0 = data[(i - 1 + size) % size];
              p1 = data[i];
              p2 = data[(i + 1) % size];
              p3 = data[(i + 2) % size];
              
            } else {
              p0 = i == 0 ? data[0] : data[i - 1];
              p1 = data[i];
              p2 = data[i + 1];
              p3 = i == size - 1 ? p2 : data[i + 2];
            }
                
            var x1 = p1.x + (p2.x - p0.x) / 6 * tension;
            var y1 = p1.y + (p2.y - p0.y) / 6 * tension;

            var x2 = p2.x - (p3.x - p1.x) / 6 * tension;
            var y2 = p2.y - (p3.y - p1.y) / 6 * tension;
            
            path += " " + x1 + " " + y1 + " " + x2 + " " + y2 + " " + p2.x + " " + p2.y;
          }
          
          return closed ? path + "z" : path;
        }

        function random(min, max) {
            if (max == null) { max = min; min = 0; }
            if (min > max) { var tmp = min; min = max; max = tmp; }
            return min + (max - min) * Math.random();
        }
    }

});