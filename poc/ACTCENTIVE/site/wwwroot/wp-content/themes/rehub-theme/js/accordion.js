/* accordition */
var accordionWrap = document.getElementsByClassName('wpsm-accordion');
if(accordionWrap.length > 0){
    var accHD = document.getElementsByClassName('wpsm-accordion-trigger');
    for (let i = 0; i < accHD.length; i++) {
        accHD[i].addEventListener('click', rhtoggleItem, false);
    }
    function rhtoggleItem() {
        var grandparent = this.parentNode.parentNode;
        var toggledata = grandparent.dataset.accordion;
        var itemClass = this.parentNode.className;
        if(toggledata == "yes"){
            var currItem = grandparent.getElementsByClassName('wpsm-accordion-item');
            for (let i = 0; i < currItem.length; i++) {
                currItem[i].className = 'wpsm-accordion-item close';
            }
        }else{
            if (itemClass == 'wpsm-accordion-item open') {
                this.parentNode.className = 'wpsm-accordion-item close';
            }
        }
        if (itemClass == 'wpsm-accordion-item close') {
            this.parentNode.className = 'wpsm-accordion-item open';
            this.nextSibling.classList.add('stuckMoveDownOpacity');
        }
    }
}