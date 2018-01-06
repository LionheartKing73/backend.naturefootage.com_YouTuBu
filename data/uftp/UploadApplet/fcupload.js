
// Use these Javascript variables to control the applet parameters
//
// WARNING: use the \ escape character for quotes (single or double)

var width = "500";  //Integer value in pixels
var height = "350";  //Integer value in pixels


var parameters = {
    server			     :"",
    port			     :"",
    user			     :"",
    pass			     :"",
    mode			     :"",   // UDP, FTP, HTTP (to use with servlet) or AUTO
    enableSSL		     :"",

    // Connection related settings

    encrypt                     : "",
    ek                          : "",
    clientConnectKey            : "",        // In order to conenct to non-FileCatalyst servers, this key must be present
    maxRetries                  : "",
    waitRetry                   : "",

    // Transport Tuning settings

    bandwidth                   : "", // target rate, FC will attempt to transfer at this rate (defaults to 100000)
    numFTPStreams               : "", // number of concurrent TCP streams to use in FTP mode
    servletLocation             : "", // Used in conjuction with the FileCatalyst Servlet to allow HTTP transfers.
    servletUploadMultiplier     : "",
    blocksize                   : "", // increasing this uses more memory to buffer but may increase performance
    unitsize                    : "", // packet size to use for sending data
    // For best performance, set to maximum size of network MTU - 28 byte (headers)
    // Ex:  for 9000 MTU, set to 8972
    numSenderThreads            : "", // number of threads used to send data up concurrently (numEncoders).
    congestionControl           : "", // true|false
    startRate                   : "", // this is the rate at which the transfer will start, should be lower than the target rate. default 1000 Kbps
    congestionControlAggression : "", // determines how agressive to respond to network changes.  Default value of 5.
    congestionControlStrategy   : "", // 0 for RTT based, 1 for Packet loss based.  default 1
    numSenderSockets            : "", // numUDPStreams.  Should increase for every ~2gbps on link.  Default value 1 (upload only)
    numReceiveSockets           : "", // Should increase for every ~5gbps on link.  Default value 1 (download only)
    numPacketProcessors         : "", // Number of threads responsible for processing packets on receiving side (download only).
    // Recommended increasing for every 500mbps (up to # of processors) when using
    // AES encryption to allow multi-core decryption
    packetQueueDepth            : "", // Number of packets stored in memory by application prior to processing (download only).
    numBlockWriters             : "", // Number of threads writing to disk per transfer.  Default value 1 (download only)
    writeBufferSizeKB           : "", // Size of write operation when saving file to disk.
    writeFileMode               : "rw", // File write mode ("rw", "rws", "rwd").  Blank default value is determined by OS.
    numBlockReaders             : "", // Number of threads reading from disk per transfer.  Default value 1 (upload only)
    readBufferSizeKB            : "", // Size of read operation when loading file from disk.
    forceTCPmodeACKs            : "", // Force Acks to flow on the TCP channel (if UDP ACKs" are blocked by firewall)

    downgradeModeOnReConnect    : "", // If auto detect is detecting UDP, then transfers fail, set this option to true

    // Transfer Content settings

    files                       : "",        // semi-colon (Windows) or colon (Linux, OS X, Solaris) delimited list of filenames
    localdir                    : "",
    remotedir                   : "",
    autoUpload                  : "",
    maxfiles                    : "",
    maxsize                     : "",
    minsize                     : "",
    maxtotalsize                : "",        //total size of all files in the cart cannot exceed the value set here. Default: (9223372036854775807, or max long)

    // Regular expression filter -- cannot upload a file that does not match this regular expression.
    // Uses JAVA regex function.  Leave blank ("") to disable the filter.
    // Note:  You must double up backspace characters (because javascript will interpret them), and
    //          :> convert any "+" signs as "%2B", as they will be removed by javascript (into spaces)
    //          :> convert any "%" into "%25", as the java script may not start up
    // Thus, the regular expression: ^[a-z,%]{4}\_[C|M][0-9]{1,2}[a-z]?\_S[0-9]{1,2}\_K[0-9]{1,2}\_[D,V,T,X][0-9]{1,2}\_[0-9]{8}\_[0-9]+\_?[0-9]*\.log$
    //       becomes:                ^[a-z,%25]{4}\\_[C|M][0-9]{1,2}[a-z]?\\_S[0-9]{1,2}\\_K[0-9]{1,2}\\_[D,V,T,X][0-9]{1,2}\\_[0-9]{8}\\_[0-9]%2B\\_?[0-9]*\\.log$

    regex                       : "",
    limitUploadToFiles          : "",        //true or false (default false). Disallow the uploading of directories to the queue.  Only files may be added.

    // Transfer Features

    incremental                 : "",        // true or false (default false) -- transfer only files that have changed
    incrementalMode             : "",        // 0 or 1 (default 0) -- 0 transfers whole file if changed, 1 transfers only file deltas
    verifyIntegrity             : "",
    verifyMode                  : "0",       // verify after = 0 (default), verify on-the-fly = 1, verify concurrent = 2
    progressive                 : "",
    autoresume                  : "",
    compression                 : "",        // true or false (default false) -- in UDP mode, compression is on the fly
    compMethod                  : "",        // Use Zip Deflater (0) or LMZA (1) for on-the-fly compression
    compLevel                   : "",        // value between 0 and 9 (0 is none, 1 is fastest, 9 is highest compression ratio)
    autoZip                     : "",        // true or false (default false) -- zip files into a single archive before sending
    zipFileSizeLimit            : "",	     // breaks the files into several smaller zips
    autoUnzip                   : "",
    zipFilename                 : "",
    useTempName                 : "",
    preservePathStructure       : "",
    keepFileAttributes          : "",
    deletePartial               : "",
    confirmOverwrite            : "",
    autoDMG                     : "",        // true or false (default false) -- on Mac OSX, create a DMG maintaining resource forks
    dmgFileName                 : "",        // specify a filename to be used when autoDMG is true


    // Values that effect the color of the client
    // Red,Green,Blue (RGB) colors in 0-255 decimal numeration (not HEX)
    // examples:  "0,0,0" is black, "255,0,0" is red, "0,0,255" is blue, "255,255,255" is white

    background                  : "",
    buttonTextColor             : "",
    buttonbackground            : "",
    buttonTextColorOnMouseOver  : "",
    buttonColorOnMouseOver      : "",
    headerTextColor             : "",
    showDialogs                 : "",  // used to supress progress dialogs, and other status messages
    embedProgress               : "",
    showpreview                 : "",
    hideLocal                   : "",
    showBrowseSwitchButton      : "",
    ProgBarGraphic              : "",
    showHelpButton              : "",
    showUploadPauseButton       : "",
    showStopCancelButton        : "",
    showRemoveFromQueueButton   : "",
    showAddFileToQueueButton       : "true", // hides the > button to add a file to the queue (true|false)
    showAddAllFilesToQueueButton   : "true", // hides the >> button to add all files to the queue (true|false)
    showRemFileFromQueueButton     : "true", // hides the < button to remove a file from the queue (true|false)
    showRemAllFilesFromQueueButton : "true", // hides the << button to remove all files from the queue (true|false)
    allowExternalDragAndDrop    : "",
    labelPlay                   : "",
    labelPause                  : "",
    labelSwitchToDDView         : "",
    labelSwitchToBrowseView     : "",
    labelRemoveFromQueue        : "",
    separatePauseCancel         : "",
    lookAndFeel                 : "", //Sets the Look and Feel of the Applet. select from: Basic, Metal, or Nimbus
    rememberBrowseLocation      : "", //true/false (default false).  Subsequent browseLive() calls remember where you last browsed to (localDir gets updated)

    // PostURL and Browser Redirection Options

    delimiter                   : "",        // used to specify a delimiter other than the default, can be a multi-character delimiter.
    sendLogsToURL       : "",
    postURL                     : "",
    filesParam                  : "",
    addSkippedFilesToPost       : "",
    autoRedirect                : "",
    callurlaftertransfer        : "",
    callurlaftertransfertarget  : "",
    transfererrorurl            : "",
    transfererrorurltarget      : "",
    transfercancelurl           : "",
    transfercancelurltarget     : "",
    transferpauseurl            : "",
    transferpauseurltarget      : "",
    othererrorurl               : "",
    othererrorurltarget         : "",
    callurlonload               : "",       // Notify when the applet has loaded.  Common example: javascript:appletLoaded()
    callurlonloadtarget         : "",
    allParamsLoaded             : "true",

    // Use this to add additional Java arguments to increase Memory, etc...
    java_arguments		    :"-Xmx512m -Djava.net.preferIPv4Stack=true",

    //used for debug finctionality
    debug			    :"false",   // increase logging on the Java console.  Can be used for troubleshooting.
    debugRate                   :"false"
};


var attributes = {
    name: "FileCatalyst",
    code: "unlimited.fc.client.FileCatalystCart.class",
    archive: "FileCatalystApplets.jar",
    width:width,
    height:height
};


var version = '1.5';

if (typeof(deployJava) == 'undefined'){
    document.write("<h2>deployJava.js is missing, make sure that you also include deployJava.js in your html</h2>");
}else{
    deployJava.runApplet(attributes, parameters, version);
}