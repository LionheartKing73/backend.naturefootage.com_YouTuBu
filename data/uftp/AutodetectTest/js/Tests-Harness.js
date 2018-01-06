
/*
 * Copyright FileCatalyst 2012
 * Autodetect test applet, used to help customers with debugging connection issues.
 * 
 * Description: Functions to run diagnostic tests
 * Will simply test if a connection to the specified FileCatalyst server is possible,
 * cascading through the modes until a valid mode is identified.
 *
 * Written in conjunction with Kunaal Malhotra.
 */

// Flags when both upload and download test have been executed.
var allTestRun = false;

// Array used as DTO to capture tests results
var resultsArray = [];

// Called on user interaction
// Params:
//  server - FC server host IP or FQDN
function RunTransferTests(server) {
    RunTestsToServer(server, document.FileCatalystUpload)
    RunTestsToServer(server, document.FileCatalystDownload)
}

// Params:
//  server: FC server host IP or FQDN
//  fileCatalyst - file catalyst object to run tests against
function RunTestsToServer(server, fileCatalyst) {
    ChangeServer(server, fileCatalyst);
    RunConnectivityTests(fileCatalyst);
    FetchResults(server, fileCatalyst);
    DisplayResults(fileCatalyst);
}

// Changes server to connect to.
// Params:
//  server: FC server host IP or FQDN
//  fileCatalyst - file catalyst object to run tests against
function ChangeServer(server, fileCatalyst) {
    fileCatalyst.setServerHost(server);
}

// Executes FC auto-detect
// Params:
//  fileCatalyst - file catalyst object to run tests against
function RunConnectivityTests(fileCatalyst) {
    fileCatalyst.runAutoDetect();
}

// Wrapper captures results of various tests
// Params:
//  server: FC server host IP or FQDN
//  fileCatalyst - file catalyst object to run tests against
function FetchResults(server, fileCatalyst) {

    //Were these upload or download tests
    NoteResultSummary(server, fileCatalyst);

    //Check if applet permissions are adequate
    CheckPermissions(fileCatalyst);

    //Port 21 connectivity
    FetchTCPControlChannelResult(fileCatalyst);

    //Outgoing UDP connectivity
    FetchUDPOutgoingResult(fileCatalyst);

    //Incoming UDP
    FetchUDPIncomingResult(fileCatalyst);

    //Multistream FTP
    FetchTCPDataChannelMultiStreamResult(fileCatalyst);

    //Single stream FTP
    FetchTCPDataChannelSingleStreamResult(fileCatalyst);

    //HTTP control and data channels
    FetchHTTPControlAndDataResult(fileCatalyst);
}

// Notes the final outcome of all test - whether transfers will succeed or not.
// Params:
//  server: FC server host IP or FQDN
//  fileCatalyst - file catalyst object to run tests against
function NoteResultSummary(server, fileCatalyst) {
    var summaryString = "";
    if (fileCatalyst == document.FileCatalystUpload) {
        if (fileCatalyst.willTransferWork())
            summaryString = "Test Summary: Uploads to server "+server+" will succeed. Test results are as follows:"
        else
            summaryString = "Test Summary: Uploads to server " + server + " will not succeed. Test results are as follows:"
    }
    else {
        if (fileCatalyst.willTransferWork())
            summaryString = "Test Summary: Downloads from server " + server + " will succeed. Test results are as follows:"
        else
            summaryString = "Test Summary: Downloads from server " + server + " will not succeed. Test results are as follows:"
        allTestRun = true;

    }
    resultsArray.push(summaryString);
}

// Checks if applet permissions are adequate
// Params:
//  fileCatalyst - file catalyst object to run tests against
function CheckPermissions(fileCatalyst) {

    var testSucceeded = "No";
    if (fileCatalyst.checkPermissions() == true)
        testSucceeded = "Yes";

    resultsArray.push(new Array("Applet Permissions", "Yes", testSucceeded, ""));
}

// Fetch results of control channel tests.
//  Case 1: test run and suceeded
//  Case 2: test run and failed
//  Case 3: test not run
// Params:
//  fileCatalyst - file catalyst object to run tests against
function FetchTCPControlChannelResult(fileCatalyst) {
    switch (fileCatalyst.checkTCPControlChannel()) {
        case 1:
            resultsArray.push(new Array("TCP Control Channel", "Yes", "Yes", ""));
            break;
        case 2:
            resultsArray.push(new Array("TCP Control Channel", "Yes", "No", fileCatalyst.getTCPControlChannelError()));
            break;
        default:
            resultsArray.push(new Array("TCP Control Channel", "Not required", "", ""));
            break;
    }
}

// Fetch results of UDP outgoing data channel tests.
//  Case 1: test run and suceeded
//  Case 2: test run and failed
//  Case 3: test not run
// Params:
//  fileCatalyst - file catalyst object to run tests against
function FetchUDPOutgoingResult(fileCatalyst) {
    switch (fileCatalyst.checkUDPOutgoing()) {
        case 1:
            resultsArray.push(new Array("UDP Data Outgoing", "Yes", "Yes", ""));
            break;
        case 2:
            resultsArray.push(new Array("UDP Data Outgoing", "Yes", "No", fileCatalyst.getUDPOutgoingError()));
            break;
        default:
            resultsArray.push(new Array("UDP Data Outgoing", "Not required", "", ""));
            break;
    }
}

// Fetch results of UDP Incoming data channel tests.
//  Case 1: test run and suceeded
//  Case 2: test run and failed
//  Case 3: test not run
// Params:
//  fileCatalyst - file catalyst object to run tests against
function FetchUDPIncomingResult(fileCatalyst) {

    switch (fileCatalyst.checkUDPIncoming()) {
        case 1:
            resultsArray.push(new Array("UDP Data Incoming", "Yes", "Yes", ""));
            break;
        case 2:
            resultsArray.push(new Array("UDP Data Incoming", "Yes", "No", fileCatalyst.getUDPIncomingError()));
            break;
        default:
            //resultsArray.push(new Array("UDP Data Incoming", "Not required", "", ""));
            resultsArray.push(new Array("UDP Data Incoming", "Not required", "", fileCatalyst.getUDPIncomingError()));
            break;
    }
}

// Fetch results of TCP multi-stream data channel tests.
//  Case 1: test run and suceeded
//  Case 2: test run and failed
//  Case 3: test not run
// Params:
//  fileCatalyst - file catalyst object to run tests against
function FetchTCPDataChannelMultiStreamResult(fileCatalyst) {

    switch (fileCatalyst.checkTCPDataChannelMultiStream()) {
        case 1:
            resultsArray.push(new Array("TCP Data Multi-stream", "Yes", "Yes", ""));
            break;
        case 2:
            resultsArray.push(new Array("TCP Data Multi-stream", "Yes", "No", fileCatalyst.getTCPDataChannelMultiStreamError()));
            break;
        default:
            resultsArray.push(new Array("TCP Data Multi-stream", "Not required", "", ""));
            break;
    }
}

// Fetch results of TCP single-stream data channel tests.
//  Case 1: test run and suceeded
//  Case 2: test run and failed
//  Case 3: test not run
// Params:
//  fileCatalyst - file catalyst object to run tests against
function FetchTCPDataChannelSingleStreamResult(fileCatalyst) {

    switch (fileCatalyst.checkTCPDataChannelSingleStream()) {
        case 1:
            resultsArray.push(new Array("TCP Data Single-stream", "Yes", "Yes", ""));
            break;
        case 2:
            resultsArray.push(new Array("TCP Data Single-stream", "Yes", "No", fileCatalyst.getTCPDataChannelSingleStreamError()));
            break;
        default:
            resultsArray.push(new Array("TCP Data Single-stream", "Not required", "", ""));
            break;
    }
}

// Fetch results of HTTP Data and Control channel tests
//  Case 1: test run and suceeded
//  Case 2: test run and failed
//  Case 3: test not run
// Params:
//  fileCatalyst - file catalyst object to run tests against
function FetchHTTPControlAndDataResult(fileCatalyst) {

    switch (fileCatalyst.checkHTTPControlAndData()) {
        case 1:
            resultsArray.push(new Array("HTTP Control and Data", "Yes", "Yes", ""));
            break;
        case 2:
            resultsArray.push(new Array("HTTP Control and Data", "Yes", "No", fileCatalyst.getHTTPControlAndDataError()));
            break;
        default:
            resultsArray.push(new Array("HTTP Control and Data", "Not required", "", ""));
            break;
    }
}
