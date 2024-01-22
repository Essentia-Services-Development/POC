"use strict";
var rhloadedtdel = false;
    
const onRHInteraction = () => {
  if (rhloadedtdel === true) {
    return;
  }
  rhloadedtdel = true;

  const modelViewerScript = document.createElement("script"); 
  modelViewerScript.type = "module";
  modelViewerScript.src = modelvars.url + "/js/model-viewer.min.js"; 
  document.body.appendChild(modelViewerScript);

  const focusVisible = document.createElement("script");
  focusVisible.src = modelvars.url + "/js/focus-visible.js"; 
  document.body.appendChild(focusVisible);
};

const onRHProgress = (event) => {
    
    const progressBar = event.target.querySelector(".progress-bar");
    const updatingBar = event.target.querySelector(".update-bar");
    updatingBar.style.width = `${event.detail.totalProgress*100}%`;
    if (event.detail.totalProgress == 1) {
      progressBar.classList.add("hide");
    }
};

document.body.addEventListener("mouseover", onRHInteraction, {once:true});
document.body.addEventListener("touchmove", onRHInteraction, {once:true});
document.body.addEventListener("scroll", onRHInteraction, {once:true});
document.body.addEventListener("keydown", onRHInteraction, {once:true});
var requestIdleCallback = window.requestIdleCallback || function(cb) {
    const start = Date.now();
    return setTimeout(function() {
        cb({
            didTimeout: false,
            timeRemaining: function() {
                return Math.max(0, 50 - (Date.now() - start));
            },
        });
    }, 1);
};