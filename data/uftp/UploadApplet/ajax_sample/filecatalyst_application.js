/* Create a new object-based namespace for the application */
var fcapp = {};
fcapp.initialized = false;

/* NOTES: Tracking forward/backwards calls by the browser could also be trapped here if
          expanding this example into a more complete solution.
*/


/**** FileCatalyst JavaScript API functionality. Only partially namespaced for fcapp ****/
/****************************************************************************************/


/* Status variables */
var NOTATTEMPTED = 0;
var SUCCESS = 1;
var CANCELLED = 2;
var ERROR = 3;
var TRANSFERRING = 4;
var VERIFYING = 5;
var COMPRESSING = 6;
var INITIALIZING = 7;
var DOINGMDTM = 8;
var DOINGCHMOD = 9;
var FINISHED = 10;
var DONEFILE = 11;
var CHECKING = 12;
var SIGNATURE = 13;
var DELTA = 14;
var REBUILD = 15;
var CREATINGDMG = 16;
var MOVINGFILES = 17;
var RECONNECTING = 18;

var hasTransferred = false;

/* Constantly check for upload changes. Production sites should include methods
 * to stop the checking with clearInterval() for improved efficiency */
fcapp.startProgress = function() {
  setInterval  ( 'fcapp.updateProgress()', 1000 );
}

/***** Alias the FileCatalyst API functions to fcapp namespace, and expand functionality where appropriate *****/

fcapp.browseAndAdd = function() {
  browsePath = document.FileCatalyst.browseLive(true);
}

fcapp.upload = function() {
  document.FileCatalyst.uploadLive();
}

fcapp.getQueueItemsRecursive = function() {
  var items = document.FileCatalyst.getQueueItemsRecursive();
  var splitItems = items.split(";");
  fcapp.toDisplay = "";
  for(i = 0; i < splitItems.length; i++){
    fcapp.toDisplay += splitItems[i] + "\n";
  }
  alert(fcapp.toDisplay);
}

fcapp.cancel = function() {
  document.FileCatalyst.setCancelled();
}

/* This function is called only when the upload page is loaded, binding the
 * fcapp transfer functions to a set of buttons */
fcapp.bindUploadClicks = function() {
  $('input#browseButton').click(function() {
    fcapp.browseAndAdd();
  });
  $('input#uploadButton').click(function() {
    fcapp.upload();
  });
  $('input#cancelButton').click(function() {
    fcapp.cancel();
  });
  $('input#queueButton').click(function() {
    fcapp.getQueueItemsRecursive();
  });
}

/* At given interval (set in startProgress()) retrieve progress update information
 * using the FileCatalyst API and publish this information to the applet area */
fcapp.updateProgress = function() {

  // Execute the following if any sort of Status Code is showing
  if(document.FileCatalyst.getStatusCode){
    // reset the progress area if there are no transfers
    var status = "There are no current transfers.";
    fcapp.fillBar.width(0);
    fcapp.barText.text("");
    fcapp.emptyText.text("");
    fcapp.filenameDiv.text("-");
    fcapp.timeLeft.text("");
    fcapp.rate.text("");
    fcapp.sent.text("");

    if (!document.FileCatalyst.isTransferError() && !document.FileCatalyst.isTransferCancelled() && !document.FileCatalyst.isTransferComplete()) {
      if (document.FileCatalyst.getStatusCode() == TRANSFERRING) {

        hasTransferred = true;

        /* use API to grab information from the applet */
        var filename = document.FileCatalyst.getCurrentFilename();
        var percent = document.FileCatalyst.getPercent();
        var fileNo = document.FileCatalyst.getFilesSoFar();
        var numFiles = document.FileCatalyst.getTotalFiles();
        var queueNum = filename + " ("+fileNo+" of "+numFiles+")<br>";
        var rate = document.FileCatalyst.getRateInKBperSecond();
        var eta = document.FileCatalyst.getTimeRemaining();
        var sofarKB = document.FileCatalyst.getBytesSoFarAllFiles()/1024;
        var totalKB = document.FileCatalyst.getSizeAllFiles()/1024;

        /* Build Status update string (var progress) based on information retrieved */
        if (document.FileCatalyst.isTransferWarning()) {
          status = document.FileCatalyst.getTransferWarningMessage();
        } else {
          status = document.FileCatalyst.getStatusMessage();
        }

        fcapp.filenameDiv.html(filename + " ("+fileNo+" of "+numFiles+")");
        
        /* update progress bar */
        fcapp.fillBar.width(percent+"%");
        if (percent >= 50) {
          fcapp.barText.text(percent + "%");
          fcapp.emptyText.text("");
        } else {
          fcapp.emptyText.text(percent + "%");
          fcapp.barText.text("");
        }

        fcapp.sent.html(sofarKB.toFixed(2)+" KB of "+totalKB.toFixed(2)+" KB");
        fcapp.timeLeft.html(eta);

        $('#rate').text(rate+" KB/s");
        
      } else if (document.FileCatalyst.getStatusCode() == NOTATTEMPTED){
        status =     "There are no current transfers.";
      } else {
        if (document.FileCatalyst.isTransferWarning()) {
          status = document.FileCatalyst.getTransferWarningMessage()+"<br>";
        } else {
          status = document.FileCatalyst.getStatusMessage()+"<br>";
        }
      }
    }

    if (hasTransferred) {
      /* Currently has no visible side-effects. In a modified scenario, you might
       * pass the status to a different field that is not overwritten by the
       * updateProgress function during its next cycle */
      if (document.FileCatalyst.isTransferError()) {
        status = "Transfer Error: "+ document.FileCatalyst.getErrorMessage();
      } else if (document.FileCatalyst.isTransferCancelled()) {
        status = "Transfer Cancelled.";
      } else if (document.FileCatalyst.isTransferComplete()) {
        status = "Transfer Complete.";
      }
      hasTransferred = false;
    }

    $('#status').html(status);
  }
}
/**** end JavaScript API section ****/

/* Set a flag to when the applet is ready */
fcapp.initializer = function() {
  fcapp.initialized = true;
}

/* Utility function for loading page content */
fcapp.loader = function() {
  fcapp.currentHTML = fcapp.current.find('a').attr('href');
  fcapp.contentArea.load(fcapp.currentHTML, function(){
    // Bind button actions only if you are on "uploads.html" page as defined in the demo.
    // Change appropriately if building on this example.
    if(fcapp.currentHTML.indexOf('uploads.html') != 0 )  {
      fcapp.bindUploadClicks()
    }
    
  });
}

/* Called on DOM ready, allowing the show/hide functionality for applet area */
fcapp.uploadRevealer = function() {
  $('#open').click( function() {
    $('#progress').slideDown();
  });
  $('#close').click( function() {
    $('#progress').slideUp();
  });

  $("#toggleButton a").click(function () {
    $("#toggleButton a").toggle();
  });
}

/* Functions to execute on DOM ready: identify the current page, load its
 * content, delegate navigation clicks, create   ****/
jQuery(function($) {

  /* cache DOM elements that will be re-used */

  fcapp.barText = $('#barText'); // numerical progress readout on progress bar
  fcapp.emptyText = $('#emptyText'); // numerical progress when on bar's empty space
  fcapp.filenameDiv = $('#filename');
  fcapp.fillBar = $('#fillBar'); // progress bar fill area (expands during transfer)
  fcapp.timeLeft = $('#timeLeft');
  fcapp.rate = $('#rate');
  fcapp.sent = $('#sent');

  fcapp.contentArea = $('#content'); // the DOM object which contains the page content
  fcapp.current = $('li.current'); // find the currently loaded page
  fcapp.loader(); // load initial page content

  /* We use true anchor tags for the Ajax fetch; this binding prevents loading new page on click */
  $('#navigation ul li a, #toggleButton a').click(function(e) {
    e.preventDefault();
  })

  /* Set the navigation UL to listen for clicks on the LI elements */
  $('#navigation ul').delegate('li', 'click', function(e) {
    placeholder = $(this);
    if ( !(placeholder.hasClass('current')) ) {
      /* update the navigation classes to show new page highlighted */
      fcapp.current.toggleClass('current');
      placeholder.toggleClass('current');
      fcapp.current = placeholder;

      /* load new page content using our Ajax fetching function*/
      fcapp.loader();
    }
  })

  /* Global on unload notification */
  warning = true; // set to false if you do not wish to warn users
  if(warning) {
    $(window).bind("unload", function() {
      if (document.FileCatalyst.getStatusCode() == TRANSFERRING) {
        // More in-depth handling is possible; the example uses an alert
        alert("A transfer in progress has been interrupted by refresh or close.");
      }
    })
  }

  fcapp.startProgress(); // start checking for changes to upload status
  fcapp.uploadRevealer(); // bind events for the transfer progress area
});