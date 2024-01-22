function copy_url_clipboard_target(event, clicked_id, result_id) {
    event.preventDefault();
    // Get the text field
    var copyText = document.getElementById(clicked_id);

    // Select the text field
    copyText.select();
    copyText.setSelectionRange(0, 99999); // For mobile devices
    
    // Copy the text inside the text field
    navigator.clipboard.writeText(copyText.value);
    
    // Alert the copied text
    document.getElementById( result_id ).innerHTML = "URL copied to clipboard";
    setTimeout(function() {
        document.getElementById( result_id ).innerHTML = "";
    },5000);
}

function copy_url_text_formate_clipboard_target(event, clicked_id) {
    event.preventDefault();

    var copyText = document.getElementById(clicked_id).value;
    navigator.clipboard.writeText(copyText);
    document.getElementById('pvfw-url-copied-message').innerHTML = 'URL copied to clipboard';
    setTimeout(function() {
        document.getElementById("pvfw-url-copied-message").innerHTML = "";
    },5000);
}




  


