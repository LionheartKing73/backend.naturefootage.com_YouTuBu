// Use these Javascript variables to control the applet parameters
//
// WARNING: use the \ escape character for quotes (single or double)

var width = "640";  //Integer value in pixels
var height = "480";  //Integer value in pixels


var parameters = {

    // Connection related parameters

    server                      :"",
    port                        :"",
    user                        :"",
    pass                        :"",
    autoconnect                 :"",
    autoreconnect               :"",
    passive                     :"",
    encrypt                     :"",
    ek                          :"",
    connecttimeout              :"",
    sotimeout                   :"",
    waitRetry                   :"",
    maxRetries                  :"",
    servletLocation             :"",


    clientConnectKey            :"",  // In order to conenct to non-FileCatalyst FTP or FTPS servers, this key must be present

    // FileCatalyst parameters

    mode                        :"UDP", //AUTO, UDP, TCP or HTTP (HTTP will only work if servletLocation is specified)
    blocksize                   :"",
    unitsize                    :"",
    numEncoders                 :"",

    bandwidth                   :"",  // defaults to 100000

    verifyIntegrity             :"",  //true or false
    verifyIntegrityMode         :"",  //0 for after transfer, 1 for on the fly

    enableSSL                   :"",

    keepFileAttributes          :"",
    congestionControl           :"",  //true or false default is true
    startRate                   :"",  // this is the rate at which the transfer will start, should be lower than the target rate.  Default is 1000
    congestionControlAggression :"", // 1 - 10, default is 5
    congestionControlStrategy   :"", // 0 for RTT based, 1 for Loss based.  default 1

    numFTPStreams               :"", // number of concurrent TCP streams to use in FTP mode (default 5)

    incremental                 :"", // true or false
    incrementalMode             :"", // 0 for transfer entire file, 1 for deltas

    useTempName                 :"", // true or false
    useTempNameMode             :"", // 0 for prefix , 1 for suffix

    compression                 :"",
    compLevel                   :"",

    // Proxy related settings for IE only

    autodetectproxy             :"",
    socksproxy                  :"",
    socksProxyHost              :"",
    socksProxyPort              :"",
    ftpproxy                    :"",
    ftpProxyHost                :"",
    ftpProxyPort                :"",

    // Functionallity related values

    ascbin                      :"",
    showascbin                  :"",
    asciiextensions             :"",
    extensions                  :"",
    exclude                     :"",
    invertExclude               :"",
    lockinitialdir              :"",
    remotedir                   :"",
    localdir                    :"",
    deleteoncancel              :"",
    enableCookies               :"",
    doubleClickTransfer         :"",
    enablerightclick            :"",
    enablekeyboardshortcuts     :"",
    confirmoverwrite            :"",
    syncpriority                :"",

    selectalllocal              :"",
    selectallremote             :"",
    autoupload                  :"",
    autodownload                :"",
    autoallo                    :"",
    hostsAllowed                :"",
    createdirectoryonconnect    :"",
    confirmTransfer             :"",
    totalProgress               :"",
    enableResume                :"",
    customFileOptions           :"",
    customDirOptions            :"",
    sendLogsToURL               :"",
    helplocation                :"documentation.html",

    // Values that effect the color of the client

    background                  :"",
    buttonTextColorOnMouseOver  :"",
    buttonTextColor             :"",
    buttonColorOnMouseOver      :"",
    buttonbackground            :"",
    headerTextColor             :"",
    headerBackgroundColor       :"",
    drivesForegroundColor       :"",
    drivesBackgroundColor       :"",
    ascBinTextColor             :"",

    // values that effect the interface layout of the client

    language                    :"",
    showsizeanddate             :"",
    LocalOptions                :"",
    RemoteOptions               :"",
    strechButtons               :"",
    display                     :"",
    showhelpbutton              :"",
    showputbutton               :"",
    showgetbutton               :"",
    showsyncbutton              :"",
    showaboutbutton             :"",
    showconnectbutton           :"",
    showdisconnectbutton        :"",
    showlocallist               :"",
    showremotelist              :"",
    showSizeInKB                :"",
    showlocaladdressbar         :"",
    showremoteaddressbar        :"",
    showFileInfoBar             :"",
    showStatusBar               :"",
    useBottomToolbar            :"",
    remoteheader                :"",

    showAdvancedTab             :"",
    showSitename                :"",
    showHostname                :"",
    showUsername                :"",
    showPassword                :"",
    showAnonymous               :"",
    showSaveConnection          :"",

    // some customizable error pages

    rejectPermissionURL         :"rejectPerms.html",
    errNavWin                   :"errNavWin.html",
    errIEWin                    :"errIEWin.html",
    errIEWinVM                  :"errIEWinVM.html",
    errNavUnix                  :"errNavUnix.html",
    errIEMac                    :"errIEMac.html",
    errNavMac                   :"errNavMac.html",
    errOperaWin                 :"errOperaWin.html",


    // Use this to add additional Java arguments to increase Memory, etc...
    java_arguments		    	:"-Xmx512m -Djava.net.preferIPv4Stack:true",

    //used for debug finctionality
    debug			    :"false",   // increase logging on the Java console.  Can be used for troubleshooting.
    debugRate                   :"false"
};


	
var attributes = {
    name: "FileCatalyst",
    code: "unlimited.ftp.FileCatalystTransferApplet.class",
    archive: "fctransferapplet.jar",
    width:width,
    height:height
};

var version = '1.5';
	
if (typeof(deployJava) == 'undefined'){
    document.write("<h2>deployJava.js is missing, make sure that you also include deployJava.js in your html</h2>");
}else{
    deployJava.runApplet(attributes, parameters, version);
}