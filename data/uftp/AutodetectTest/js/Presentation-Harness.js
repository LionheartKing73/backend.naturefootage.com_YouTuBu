/*
 * Copyright FileCatalyst 2012
 * Autodetect test applet, used to help customers with debugging connection issues.
 * 
 * Description: Contains code to present diagnostic information
 * Written in conjunction with Kunaal Malhotra.
 */


// When window load completes, show page as being ready
window.onload = function () {
  ShowPageLoaded();
};

// Show page as processing
function ShowPageLoading() {
  document.getElementById('TestBtn').disabled = true;
  document.getElementById('LoadingImg').style.visibility = 'visible';
}

// Show page as being ready, having finished processing
function ShowPageLoaded() {
  document.getElementById('TestBtn').disabled = false;
  document.getElementById('LoadingImg').style.visibility = 'hidden';
}

// Clear out any test results displayed
function ClearResults() {
  document.getElementById('ResultDiv').innerHTML = ''
}

// Called when 'Run Tests' button is clicked
function TestBtn_onclick() {

  // Show the page as processing
  ShowPageLoading();

  // Reset flag
  allTestRun = false;

  // Vacate (Reset) results array
  resultsArray = [];

  // Run tests
  RunTransferTests(setParameters());
}


// Presents results in tabular fashion
// Params:
//  fileCatalyst - FC Server host IP or FQDN
function DisplayResults(fileCatalyst) {

  // Have all tests run, or do I wait?
  if (allTestRun) {

    // Show the page as having finished processing
    ShowPageLoaded();

    // Format the results and publish
    document.getElementById('ResultDiv').innerHTML = FormatOutput(resultsArray);
  }
}

// Updates the applet parameters with those set by the user, and then returns
// only the FC Server host IP or FQDN (only the server hostname is passed into tests)
function setParameters() {
  var upApplet = document.FileCatalystUpload;
  var downApplet = document.FileCatalystDownload;

  var hostname = document.getElementById('hostname').value;
  var port = document.getElementById('port').value;
  var username = document.getElementById('username').value;
  var password = document.getElementById('password').value;
  var servlet = document.getElementById('servlet').value;
 
  var useSSL = document.getElementById('chbxUseSSL').checked;
    
  upApplet.setServerHost(hostname);
  downApplet.setServerHost(hostname);

  upApplet.setServerPort(port);
  downApplet.setServerPort(port);

  upApplet.setUserName(username);
  downApplet.setUserName(username);

  upApplet.setUserPassword(password);
  downApplet.setUserPassword(password);

  upApplet.setServletLoc(servlet);
  downApplet.setServletLoc(servlet);
  
  upApplet.setSSL(useSSL);
  downApplet.setSSL(useSSL);

  return hostname;
}

// Formats the test results into HTML
// Params:
//  resultsArray - array containing test results for upload and download tests
// Return Value: HTML containing tabular display of results
function FormatOutput(resultsArray) {
  var outputHtml = "<table border = 0 cellpadding=0 cellspacing=0>";
  outputHtml +=       "<thead>"
  outputHtml +=           "<tr>";
  outputHtml +=               "<th>Test</th><th>Run</th><th>Succeeded</th><th>Error Message</th>";
  outputHtml +=           "</tr>";
  outputHtml +=       "</thead>";
  outputHtml +=       "<tr class='resultSummary'><td colspan=4>"+ resultsArray[0].toString() +"</td></tr>";

  for (var i = 1; i < resultsArray.length; i++) {
    if (i == 8) {
      outputHtml += "<tr class='resultSummary'><td colspan=4>" + resultsArray[i].toString() + "</td></tr>";
      continue;
    }

    outputHtml += "<tr>";
    outputHtml += "<td>" + resultsArray[i][0].toString() + "</td>";
    outputHtml += "<td>" + resultsArray[i][1].toString() + "</td>";
    outputHtml += "<td>" + resultsArray[i][2].toString() + "</td>";
    outputHtml += "<td>" + resultsArray[i][3].toString() + "</td>";
    outputHtml += "</tr>";

  }

  outputHtml += "</table>";
  return outputHtml;
}


// Helper function to attach a JS file from this file
// Params:
//  jsFile - relative path to the JS file to be included
function IncludeJavaScript(jsFile)
{
  document.write('<script type="text/javascript" src="'
    + jsFile + '"></scr' + 'ipt>');
}


