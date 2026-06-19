	$(document).ready(function(){
  
  //If iframe exists on page
  if ( $('iframe').length >= 1 ) {
    
    // Inject YouTube API script
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/player_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    // On click of element with class .open-yt-lightbox
        //Firefox needs you to manually hold ( event ), Chrome does not
    $('.open-yt-lightbox').click(function( event ){
      // Prevent link from opening youtube
        // You don't need to link to YT since it's getting the element, not the link
        // But if the user has JS disable, the link will still open the video on YT
      event.preventDefault();
      // Run the openbox function
      openbox();
    });

    // On click of element with class .close
      // In this case, this is the X button when the lightbox is open
    $('.close').click(function(){
      // Run the closebox function
      closebox();
    });

    // On click of element with class .lightbox-backdrop
      // In this case, the element wrapping the lightbox and iframe elements
    $('.lightbox-backdrop').click(function(){
      // Run the closebox function
      closebox();
    });

  }
  // Global variable for Youtube API (Yes, this could be outside the document.ready function)
  var player;
});

// Youtube API calls this function when it's ready to go
  // The name of this function CAN NOT be changed
function onYouTubePlayerAPIReady() {
  // Set the player variable to a new player (YT API), which is your iframe
    // In this case, the iframe has the ID #yt-iframe to make it easy to grab
    // You can also add your API params here, like events
  player = new YT.Player('yt-iframe');
}

// Function to open the lightbox
function openbox(){
  // Make the lightbox elements visible
  $('.lightbox-backdrop, .box').css('display', 'inline-block');
  $('.lightbox-backdrop, .box').animate({'opacity':'1'}, 300, 'linear');
  // Start playing the video
  player.playVideo();
}

// Function to close the lightbox
function closebox() {
  // Hide the lightbox elements
  $('.lightbox-backdrop, .box').animate({'opacity':'0'}, 300, 'linear', function(){
    $('.lightbox-backdrop, .box').css('display', 'none');
    //Pause the video
    player.pauseVideo();
  });
}
