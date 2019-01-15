var handler = null;
var page = 1;
var isLoading = false;
var apiURL = 'http://vic.app.alltosun.net/201312ent/index.php?anu=movie/ajax/get_role_pic';

// Prepare layout options.
var options = {
  autoResize: true, // This will auto-update the layout when the browser window is resized.
  container: $('#tiles'), // Optional, used for some extra CSS styling
  offset: 2, // Optional, the distance between grid items
  itemWidth: 210 // Optional, the width of a grid item
};

/**
 * When scrolled all the way to the bottom, add more tiles.
 */
function onScroll(event) {
  // Only check when we're not still waiting for data.
  if(!isLoading) {
    // Check if we're within 100 pixels of the bottom edge of the broser window.
    var closeToBottom = ($(window).scrollTop() + $(window).height() > $(document).height() - 100);
    if(closeToBottom) {
      loadData();
    }
  }
};

/**
 * Refreshes the layout.
 */
function applyLayout() {
  // Clear our previous layout handler.
  if(handler) handler.wookmarkClear();
  
  // Create a new layout handler.
  handler = $('#tiles li');
  handler.wookmark(options);
};

/**
 * Loads data from the API.
 */
function loadData() {
  isLoading = true;

  $.ajax({
    url: apiURL,
    dataType: 'json',
    data: {page: page}, // Page parameter to make sure we load new data
    success: onLoadData
  });
};

/**
 * Receives data from the API, creates HTML for images and updates the layout
 */
function onLoadData(data) {
  isLoading = false;

  // Increment page index for future calls.
  page++;
  console.log(data);
  // Create HTML for the images.
  var html = '';
  var i=0, length=data.data.length, image;
  for(; i<length; i++) {
    image = data.data[i];
    html += '<li>';

	html += '<img src=http://vic.app.alltosun.net/201312ent/static/upload/"'+image.cover+'" width="200" height="'+Math.round(image.height/image.width*200)+'">';
	
	html += '</li>';
	  }

  $('#tiles').append(html);
  
      applyLayout();
    };
  
    $(document).ready(new function() {
      // Capture scroll event.
  $(document).bind('scroll', onScroll);
  
  // Load first data from the API.
  loadData();
});