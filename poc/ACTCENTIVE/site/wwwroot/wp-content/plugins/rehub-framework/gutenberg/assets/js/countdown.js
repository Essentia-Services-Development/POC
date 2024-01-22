"use strict";
function getTimeRemaining(endtime){
    const total = Date.parse(endtime) - Date.parse(new Date());
    const seconds = Math.floor( (total/1000) % 60 );
    const minutes = Math.floor( (total/1000/60) % 60 );
    const hours = Math.floor( (total/(1000*60*60)) % 24 );
    const days = Math.floor( total/(1000*60*60*24) );

    return {
    total,
    days,
    hours,
    minutes,
    seconds
    };
}

var gccountdown = document.getElementsByClassName('rh-countdown');
for (let i = 0; i < gccountdown.length; i++) {
    let clock= gccountdown[i];
    let endtime = clock.dataset.endtime;
    let daysSpan = clock.querySelector('.days');
    let hoursSpan = clock.querySelector('.hours');
    let minutesSpan = clock.querySelector('.minutes');
    let secondsSpan = clock.querySelector('.seconds');
    function updateClock(){
        let t = getTimeRemaining(endtime);
        daysSpan.innerHTML = ('0' + t.days).slice(-2);
        hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
        minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
        secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);
        if (t.total <= 0) {
            //clearInterval(timeinterval);
            daysSpan.innerHTML = hoursSpan.innerHTML = minutesSpan.innerHTML = secondsSpan.innerHTML = '00';
        }
    }
    updateClock();
    var timeinterval = setInterval(updateClock,1000);
}