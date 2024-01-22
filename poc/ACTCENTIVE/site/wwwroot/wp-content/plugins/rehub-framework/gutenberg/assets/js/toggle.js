/* SLIDE UP */
let gcslideUp = (target, duration=500) => {

    target.style.transitionProperty = 'height, margin, padding';
    target.style.transitionDuration = duration + 'ms';
    target.style.boxSizing = 'border-box';
    target.style.height = target.offsetHeight + 'px';
    target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    window.setTimeout( () => {
          target.style.display = 'none';
          target.style.removeProperty('height');
          target.style.removeProperty('padding-top');
          target.style.removeProperty('padding-bottom');
          target.style.removeProperty('margin-top');
          target.style.removeProperty('margin-bottom');
          target.style.removeProperty('overflow');
          target.style.removeProperty('transition-duration');
          target.style.removeProperty('transition-property');
          //alert("!");
    }, duration);
}

/* SLIDE DOWN */
let gcslideDown = (target, duration=500) => {

    target.style.removeProperty('display');
    let display = window.getComputedStyle(target).display;
    if (display === 'none') display = 'block';
    target.style.display = display;
    let height = target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    target.offsetHeight;
    target.style.boxSizing = 'border-box';
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = duration + 'ms';
    target.style.height = height + 'px';
    target.style.removeProperty('padding-top');
    target.style.removeProperty('padding-bottom');
    target.style.removeProperty('margin-top');
    target.style.removeProperty('margin-bottom');
    const y = target.getBoundingClientRect().top + window.scrollY - 50;
    window.scroll({
      top: y,
      behavior: 'smooth'
    });
    window.setTimeout( () => {
      target.style.removeProperty('height');
      target.style.removeProperty('overflow');
      target.style.removeProperty('transition-duration');
      target.style.removeProperty('transition-property');
    }, duration);
}

/* TOOGLE */
let gcslideToggle = (target, duration = 500) => {
    if (window.getComputedStyle(target).display === 'none') {
      return gcslideDown(target, duration);
    } else {
      return gcslideUp(target, duration);
    }
}

var gctoggle = document.getElementsByClassName('gc-expandable-wrapper');
for (let i = 0; i < gctoggle.length; i++) {
    let toggleNode = gctoggle[i];
    toggleNode.addEventListener('click', function (ev) {
      if (!ev.target.matches('.gc-expandable-trigger')) return;
      else{
        let el = ev.target.closest('.gc-expandable-wrapper').querySelector('.gc-expandable-content');
        gcslideToggle(el);
      }		
    }, false);
}