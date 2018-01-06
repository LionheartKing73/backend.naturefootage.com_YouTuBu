// Use these Javascript variables to control the applet parameters
//
// WARNING: use the \ escape character for quotes (single or double)

var width = "220";  //Integer value in pixels
var height = "50";  //Integer value in pixels


var parameters = {
    server			    :"",
    port			    :"",
    user			    :"",
    pass			    :"",
    mode			    :"",   // UDP, FTP, HTTP (to use with servlet) or AUTO
    enableSSL		    :"",

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

    files                       : "", // semi-colon (Windows) or colon (Linux, OS X, Solaris) delimited list of filenames (must be known to exist in specified remotedir)
    filesRename                 : "",
    localdir                    : "",
    createlocaldir              : "", // This directory will be created under the path the user selects for download
    remotedir                   : "", // this must be a relative path, no leading slash
    autodownload                : "",


    // Transfer Features

    incremental                 : "",
    incrementalMode             : "",
    verifyIntegrity             : "", // true or false (compares using MD5 hash sum)
    verifyMode                  : "0",// verify after = 0 (default), verify on-the-fly = 1, verify concurrent = 2
    progressive                 : "",
    autoresume                  : "",
    compression                 : "", // true or false (default false) -- in UDP mode, compression is on the fly
    compMethod                  : "", // Use Zip Deflater (0) or LMZA (1) for on-the-fly compression
    compLevel                   : "", // value between 0 and 9 (0 is none, 1 is fastest, 9 is highest compression ratio)
    autoZip                     : "", // true or false (default false) -- zip files into a single archive before sending
    zipFileSizeLimit            : "", // breaks the files into several smaller zips
    useTempName                 : "",
    preservePathStructure       : "",
    keepFileAttributes          : "",
    deletePartial               : "",
    confirmOverwrite            : "",

    // Values that effect the color of the client
    // Red,Green,Blue (RGB) colors in 0-255 decimal numeration (not HEX)
    // examples:  "0,0,0" is black, "255,0,0" is red, "0,0,255" is blue, "255,255,255" is white

    background                  : "",
    buttonTextColor             : "",
    buttonbackground            : "",
    buttonTextColorOnMouseOver  : "",
    buttonColorOnMouseOver      : "",
    headerTextColor             : "",
    showDialogs                 : "",  // set this to true to supress progress dialog, and other message dialogs.
    ProgBarGraphic              : "",
    useImageButtonForDownload   : "", // set value to "true" if you want the download button to be an icon button.  Default is false.
    downloadIcon                : "", // This is a URL to the download button image (example: ?/images/mybutton.png?). Blank value defaults to a Classic Windows button image
    downloadIconOnMouseover     : "", // This is a URL to the download button image when the mouse hovers over the button.  If you do not want an effect, use the same image for both.  Blank value defaults to a Classic Windows button image
    separatePauseCancel         : "",
    lookAndFeel                 : "", //Sets the Look and Feel of the Applet. select from: Basic, Metal or Nimbus

    // PostURL and Browser Redirection Options

    delimiter                   : "", // used to specify a delimiter other than the default, can be a multi-character delimiter.
    sendLogsToURL               : "",
    autoRedirect                : "",
    autoReveal          : "",
    callurlaftertransfer        : "", // redirect to this URL after successful upload
    callurlaftertransfertarget  : "",
    transfererrorurl            : "", // redirect to this URL after failed upload
    transfererrorurltarget      : "",
    transfercancelurl           : "", // redirect to this URL after cancelled upload
    transfercancelurltarget     : "",
    transferpauseurl            : "",
    transferpauseurltarget      : "",
    othererrorurl               : "",
    othererrorurltarget         : "",
    callurlonload               : "", // Notify when the applet has loaded.  Common example: javascript:appletLoaded()
    callurlonloadtarget         : "",

    allParamsLoaded             : "true",

    // Use this to add additional Java arguments to increase Memory, etc...

    java_arguments		    	:"-Xmx512m -Djava.net.preferIPv4Stack:true",

    //used for debug finctionality

    debug			    :"false",   // increase logging on the Java console.  Can be used for troubleshooting.
    debugRate                   :"false"
};



var attributes = {
    name: "FileCatalyst",
    code: "unlimited.fc.client.FileCatalystDownloadApplet.class",
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