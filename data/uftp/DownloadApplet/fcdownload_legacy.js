// FileCatalyst download applet script file.  Simply include this file as a script in your page.
// <script language="javascript" src="fcdownload.js"></script> is the only code you will need
// to add to your page.  Just make sure that fcart.js is in the same directory as your page.

// Use these Javascript variables to control the applet parameters


// Connection related settings
var server                      = ""; // IP address of FileCatalyst Server
var port                        = "";
var user                        = "";
var pass                        = "";
var encrypt                     = "";
var ek                          = "";
var clientConnectKey            = "";// In order to conenct to non-FileCatalyst servers, this key must be present
var enableSSL                   = "";
var waitRetry                   = "";
var maxRetries                  = "";
var localPort                   = ""; // incoming port for UDP traffic.  Set to 0 or leave blank to use any open port.


// Transport Tuning settings
var bandwidth                   = ""; // target rate, FC will attempt to transfer at this rate (defaults to 100000)
var mode                        = ""; // UDP, FTP, HTTP (to use with servlet) or AUTO
var numFTPStreams               = ""; // number of concurrent TCP streams to use in FTP mode
var servletLocation             = ""; // Used in conjuction with the FileCatalyst Servlet to allow HTTP transfers.
var servletUploadMultiplier     = "";
var blocksize                   = ""; // increasing this uses more memory to buffer but may increase performance
var unitsize                    = ""; // packet size to use for sending data
                                      // For best performance, set to maximum size of network MTU - 28 byte (headers)
                                      // Ex:  for 9000 MTU, set to 8972
var numSenderThreads            = ""; // number of threads used to send data up concurrently (numEncoders).
var congestionControl           = ""; // true|false
var startRate                   = ""; // this is the rate at which the transfer will start, should be lower than the target rate. default 1000 Kbps
var congestionControlAggression = ""; // determines how agressive to respond to network changes.  Default value of 5.
var congestionControlStrategy   = ""; // 0 for RTT based, 1 for Packet loss based.  default 1
var numSenderSockets            = ""; // numUDPStreams.  Should increase for every ~2gbps on link.  Default value 1 (upload only)
var numReceiveSockets           = ""; // Should increase for every ~5gbps on link.  Default value 1 (download only)
var numPacketProcessors         = ""; // Number of threads responsible for processing packets on receiving side (download only).
                                      // Recommended increasing for every 500mbps when using AES encryption to allow multi-CPU decryption
var packetQueueDepth            = ""; // Number of packets stored in memory by application prior to processing (download only).
var numBlockWriters             = ""; // Number of threads writing to disk per transfer.  Default value 1 (download only)
var writeBufferSizeKB           = ""; // Size of write operation when saving file to disk.
var writeFileMode               = "rw"; // File write mode ("rw", "rws", "rwd").  Blank default value is determined by OS.
var numBlockReaders             = ""; // Number of threads reading from disk per transfer.  Default value 1 (upload only)
var readBufferSizeKB            = ""; // Size of read operation when loading file from disk.
var forceTCPmodeACKs            = ""; // Force Acks to flow on the TCP channel (if UDP ACKs" are blocked by firewall)

// Transfer Content settings
var files                       = ""; // semi-colon (Windows) or colon (Linux, OS X, Solaris) delimited list of filenames (must be known to exist in specified remotedir)
var filesRename                 = "";
var localdir                    = "";
var createlocaldir              = ""; // This directory will be created under the path the user selects for download
var remotedir                   = ""; // this must be a relative path, no leading slash
var autodownload                = "";


// Transfer Features
var incremental                 = "";
var incrementalMode             = "";
var verifyIntegrity             = ""; // true or false (compares using MD5 hash sum)
var verifyMode                  = "";
var progressive                 = "";
var autoresume                  = "";
var compression                 = "";        // true or false (default false) -- in UDP mode, compression is on the fly
var compMethod                  = "";        // Use Zip Deflater (0) or LMZA (1) for on-the-fly compression
var compLevel                   = "";        // value between 0 and 9 (0 is none, 1 is fastest, 9 is highest compression ratio)
var autoZip                     = "";        // true or false (default false) -- zip files into a single archive before sending
var zipFileSizeLimit            = "";	     // breaks the files into several smaller zips
var useTempName                 = "";
var preservePathStructure       = "";
var keepFileAttributes          = "";
var deletePartial               = "";
var confirmOverwrite            = "";


// GUI Behaviours and Presentation
var width                       = "150";
var height                      = "50";
// Values that effect the color of the client
// Red,Green,Blue (RGB) colors in 0-255 decimal numeration (not HEX)
// examples:  "0,0,0" is black, "255,0,0" is red, "0,0,255" is blue, "255,255,255" is white
var background                  = "";
var buttonTextColor             = "";
var buttonbackground            = "";
var buttonTextColorOnMouseOver  = "";
var buttonColorOnMouseOver      = "";
var headerTextColor             = "";
var showDialogs                 = "";  // set this to true to supress progress dialog, and other message dialogs.
var ProgBarGraphic              = "";
var useImageButtonForDownload   = ""; // set value to "true" if you want the download button to be an icon button.  Default is false.
var downloadIcon                = ""; // This is a URL to the download button image (example: ?/images/mybutton.png?). Blank value defaults to a Classic Windows button image
var downloadIconOnMouseover     = ""; // This is a URL to the download button image when the mouse hovers over the button.  If you do not want an effect, use the same image for both.  Blank value defaults to a Classic Windows button image
var separatePauseCancel         = "";
var lookAndFeel                 = ""; //Sets the Look and Feel of the Applet. select from: Basic, Metal or Nimbus

// PostURL and Browser Redirection Options
var delimiter                   = ""; // used to specify a delimiter other than the default, can be a multi-character delimiter.
var sendLogsToURL               = "";
var autoRedirect                = "";
var autoReveal          = "";
var callurlaftertransfer        = ""; // redirect to this URL after successful upload
var callurlaftertransfertarget  = "";
var transfererrorurl            = ""; // redirect to this URL after failed upload
var transfererrorurltarget      = "";
var transfercancelurl           = ""; // redirect to this URL after cancelled upload
var transfercancelurltarget     = "";
var transferpauseurl            = "";
var transferpauseurltarget      = "";
var othererrorurl               = "";
var othererrorurltarget         = "";
var callurlonload               = "";       // Notify when the applet has loaded.  Common example: javascript:appletLoaded()
var callurlonloadtarget         = "";



var debug                       = "false";
var allParamsLoaded             = "true";

//*************************************************************************************** //
// ********** DO NOT EDIT BELOW THIS POINT UNLESS YOU KNOW WHAT YOU ARE DOING!  ********* //
//*************************************************************************************** //
var n;

var agt=navigator.userAgent.toLowerCase();

// detect browser version
// Note: On IE5, these return 4, so use is_ie5up to detect IE5.
var is_major = parseInt(navigator.appVersion);
var is_minor = parseFloat(navigator.appVersion);

// *** BROWSER TYPE ***
var is_nav  = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1)
            && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1)
            && (agt.indexOf('webtv')==-1));

var is_opera = (agt.indexOf('opera')!=-1);
var is_safari = (agt.indexOf('safari')!=-1);
var is_konqueror = (agt.indexOf('konqueror')!=-1);
var is_opera6up = (is_opera && (is_major >= 6));
var is_nav4up = (is_nav && (is_major >= 4));
var is_nav6up = (is_nav && (is_major >= 6));
var is_ie   = (agt.indexOf("msie") != -1);
var is_ie3  = (is_ie && (is_major < 4));
var is_ie4  = (is_ie && (is_major == 4) && (agt.indexOf("msie 5.0")==-1) );
var is_ie5  = (is_ie && (is_major == 4) && (agt.indexOf("msie 5.0")!=-1) );
var is_ie5up  = (is_ie  && !is_ie3 && !is_ie4);
var is_firefox =  (agt.indexOf("firefox") != -1);

// *** PLATFORM ***
var is_win   = ( (agt.indexOf("win")!=-1) || (agt.indexOf("16bit")!=-1) );
var is_mac   = (agt.indexOf("mac")!=-1);
var is_sun   = (agt.indexOf("sunos")!=-1);
var is_irix  = (agt.indexOf("irix") !=-1);
var is_hpux  = (agt.indexOf("hp-ux")!=-1);
var is_aix   = (agt.indexOf("aix") !=-1);
var is_linux = (agt.indexOf("inux")!=-1);
var is_sco   = (agt.indexOf("sco")!=-1) || (agt.indexOf("unix_sv")!=-1);
var is_unixware = (agt.indexOf("unix_system_v")!=-1);
var is_mpras    = (agt.indexOf("ncr")!=-1);
var is_reliant  = (agt.indexOf("reliantunix")!=-1);
var is_dec   = ((agt.indexOf("dec")!=-1) || (agt.indexOf("osf1")!=-1) ||
               (agt.indexOf("dec_alpha")!=-1) || (agt.indexOf("alphaserver")!=-1) ||
               (agt.indexOf("ultrix")!=-1) || (agt.indexOf("alphastation")!=-1));
var is_sinix = (agt.indexOf("sinix")!=-1);
var is_freebsd = (agt.indexOf("freebsd")!=-1);
var is_bsd = (agt.indexOf("bsd")!=-1);
var is_unix  = ((agt.indexOf("x11")!=-1) || is_sun || is_irix || is_hpux ||
                 is_sco ||is_unixware || is_mpras || is_reliant ||
                 is_dec || is_sinix || is_aix || is_linux || is_bsd || is_freebsd);


function isMacX() {
    if (isMacJaguar()) return true;
    if (agt.indexOf("omniweb") != -1) return true;
    for (var i = 0; i < navigator.plugins.length; i++) {
        if (navigator.plugins[i].name.indexOf("OJI") > -1) return true;
        if (navigator.plugins[i].name.indexOf("Default Plugin Carbon.cfm") > -1) return true;
    }
    return false;
}

function isMacJaguar() {
    return (is_mac && javaPlugin()); // we know java plugin means 10.2 or higher
}




function javaPlugin() {
    if (is_safari || is_opera6up || is_konqueror || is_firefox ) return true;   // we know safari and opera use java plugin
    for (var i = 0; i < navigator.plugins.length; i++) {
        if (navigator.plugins[i].name.indexOf("Java Plug-in") > -1) return true;
        if (navigator.plugins[i].name.indexOf("Java Embedding Plug") > -1) return true;
    }
    return false;
}

function mrj()  {
    if (isMacX()) return true;
    for (var i = 0; i < navigator.plugins.length; i++) {
        if (navigator.plugins[i].name.indexOf("MRJ") > -1) return true;
    }
    return false;
}


 if (!is_nav || is_safari || is_firefox ) {
    if (is_ie && !is_mac) {
        // JAVA 1.6
        document.write("<OBJECT name='FileCatalyst' classid='clsid:CAFEEFAC-0016-0000-0018-ABCDEFFEDCBA' codebase=?http://java.sun.com/update/1.6.0/jinstall-6-windows-i586.cab#Version=1,6,0,18' height="+height+" width="+width+"'>");
        // old Java 1.4
        //document.write("<OBJECT name='FileCatalyst' classid='clsid:8AD9C840-044E-11D1-B3E9-00805F499D93' codebase='http://java.sun.com/products/plugin/autodl/jinstall-1_4-windows-i586.cab#Version=1,4,2,0' height="+height+" width="+width+"'>");
    } else {
        document.write("<APPLET name='FileCatalyst' code='unlimited.fc.client.FileCatalystDownloadApplet.class' height="+height+" width="+width+" archive='FileCatalystApplets.jar' VIEWASTEXT>");
    }
    document.writeln("<param name='archive' value='FileCatalystApplets.jar'>");
    document.writeln("<param name='code' value='unlimited.fc.client.FileCatalystDownloadApplet.class'>");
    document.writeln("<PARAM NAME='scriptable' VALUE='true'>");
    document.writeln("<PARAM NAME='MAYSCRIPT' VALUE='true'>");
    document.writeln("<PARAM NAME = 'id' VALUE='FileCatalyst'>");
    document.writeln('<PARAM NAME = "debug"                         VALUE = "'+debug+'"> ');
    document.writeln('<PARAM NAME = "server"                        VALUE = "'+server+'"> ');
    document.writeln('<PARAM NAME = "port"                          VALUE = "'+port+'"> ');
    document.writeln('<PARAM NAME = "user"                          VALUE = "'+user+'">');
    document.writeln('<PARAM NAME = "pass"                          VALUE = "'+pass+'">');
    document.writeln("<PARAM NAME = 'encrypt'                       VALUE = '"+encrypt+"'>");
    document.writeln("<PARAM NAME = 'ek'                            VALUE = '"+ek+"'>");
    document.writeln("<PARAM NAME = 'clientConnectKey'              VALUE = '"+clientConnectKey+"'>");
    document.writeln('<PARAM NAME = "enableSSL"                     VALUE = "'+enableSSL+'">');
    document.writeln('<PARAM NAME = "waitRetry"                     VALUE = "'+waitRetry+'">');
    document.writeln('<PARAM NAME = "maxRetries"                    VALUE = "'+maxRetries+'">');
    document.writeln('<PARAM NAME = "localPort"                     VALUE = "'+localPort+'">');
    document.writeln('<PARAM NAME = "mode"                          VALUE = "'+mode+'">');
    document.writeln('<PARAM NAME = "servletLocation"               VALUE = "'+servletLocation+'">');
    document.writeln('<PARAM NAME = "servletUploadMultiplier"       VALUE = "'+servletUploadMultiplier+'">');
    document.writeln('<PARAM NAME = "numFTPStreams"                 VALUE = "'+numFTPStreams+'">');
    document.writeln('<PARAM NAME = "bandwidth"                     VALUE = "'+bandwidth+'">');
    document.writeln('<PARAM NAME = "blockSize"                     VALUE = "'+blocksize+'"> ');
    document.writeln('<PARAM NAME = "unitSize"                      VALUE = "'+unitsize+'"> ');
    document.writeln('<PARAM NAME = "numSenderThreads"              VALUE = "'+numSenderThreads+'">');
    document.writeln('<PARAM NAME = "congestionControl"             VALUE = "'+congestionControl+'">');
    document.writeln('<PARAM NAME = "startRate"                     VALUE = "'+startRate+'">');
    document.writeln("<PARAM NAME = 'congestionControlAggression'   VALUE = '"+congestionControlAggression+"'>");
    document.writeln("<PARAM NAME = 'congestionControlStrategy'     VALUE = '"+congestionControlStrategy+"'>");
    document.writeln('<PARAM NAME = "numSenderSockets"              VALUE = "'+numSenderSockets+'">');
    document.writeln('<PARAM NAME = "numReceiveSockets"             VALUE = "'+numReceiveSockets+'">');
    document.writeln('<PARAM NAME = "numPacketProcessors"           VALUE = "'+numPacketProcessors+'">');
    document.writeln('<PARAM NAME = "packetQueueDepth"              VALUE = "'+packetQueueDepth+'">');
    document.writeln('<PARAM NAME = "numBlockWriters"               VALUE = "'+numBlockWriters+'">');
    document.writeln('<PARAM NAME = "writeBufferSizeKB"             VALUE = "'+writeBufferSizeKB+'">');
    document.writeln('<PARAM NAME = "writeFileMode"                 VALUE = "'+writeFileMode+'">');
    document.writeln('<PARAM NAME = "numBlockReaders"               VALUE = "'+numBlockReaders+'">');
    document.writeln('<PARAM NAME = "readBufferSizeKB"              VALUE = "'+readBufferSizeKB+'">');
    document.writeln('<PARAM NAME = "forceTCPmodeACKs"              VALUE = "'+forceTCPmodeACKs+'">');
    document.writeln('<PARAM NAME = "files"                         VALUE = "'+files+'">');
    document.writeln("<PARAM NAME = 'filesRename'                   VALUE = '"+filesRename+"'>");
    document.writeln('<PARAM NAME = "localdir"                      VALUE = "'+localdir+'">');
    document.writeln('<PARAM NAME = "createlocaldir"                VALUE = "'+createlocaldir+'">');
    document.writeln('<PARAM NAME = "remotedir"                     VALUE = "'+remotedir+'">');
    document.writeln('<PARAM NAME = "autodownload"                  VALUE = "'+autodownload+'">');
    document.writeln('<PARAM NAME = "incremental"                   VALUE = "'+incremental+'">');
    document.writeln('<PARAM NAME = "incrementalMode"               VALUE = "'+incrementalMode+'">');
    document.writeln('<PARAM NAME = "verifyIntegrity"               VALUE = "'+verifyIntegrity+'">');
    document.writeln('<PARAM NAME = "verifyMode"                    VALUE = "'+verifyMode+'">');
    document.writeln('<PARAM NAME = "progressive"                   VALUE = "'+progressive+'">');
    document.writeln('<PARAM NAME = "autoresume"                    VALUE = "'+autoresume+'">');
    document.writeln('<PARAM NAME = "compression"                   VALUE = "'+compression+'">');
    document.writeln('<PARAM NAME = "compMethod"                    VALUE = "'+compMethod+'">');
    document.writeln('<PARAM NAME = "compLevel"                     VALUE = "'+compLevel+'">');
    document.writeln('<PARAM NAME = "autoZip"                       VALUE = "'+autoZip+'">');
    document.writeln('<PARAM NAME = "zipFileSizeLimit"              VALUE = "'+zipFileSizeLimit+'">');    
    document.writeln('<PARAM NAME = "useTempName"                   VALUE = "'+useTempName+'">');
    document.writeln('<PARAM NAME = "preservePathStructure"         VALUE = "'+preservePathStructure+'">');
    document.writeln('<PARAM NAME = "keepFileAttributes"            VALUE = "'+keepFileAttributes+'">');
    document.writeln('<PARAM NAME = "deletePartial"                 VALUE = "'+deletePartial+'">');
    document.writeln('<PARAM NAME = "confirmOverwrite"              VALUE = "'+confirmOverwrite+'">');
    document.writeln("<PARAM NAME = 'background'                    VALUE = '"+background+"'>");
    document.writeln("<PARAM NAME = 'buttonbackground'              VALUE = '"+buttonbackground+"'>");
    document.writeln("<PARAM NAME = 'buttonTextColor'               VALUE = '"+buttonTextColor+"'>");
    document.writeln("<PARAM NAME = 'buttonColorOnMouseOver'        VALUE = '"+buttonColorOnMouseOver+"'>");
    document.writeln("<PARAM NAME = 'buttonTextColorOnMouseOver'    VALUE = '"+buttonTextColorOnMouseOver+"'>");
    document.writeln("<PARAM NAME = 'headerTextColor'               VALUE = '"+headerTextColor+"'>");
    document.writeln("<PARAM NAME = 'showDialogs'                   VALUE = '"+showDialogs+"'>");
    document.writeln("<PARAM NAME = 'ProgBarGraphic'                VALUE = '"+ProgBarGraphic+"'>");
    document.writeln("<PARAM NAME = 'useImageButtonForDownload'     VALUE = '"+useImageButtonForDownload+"'>");
    document.writeln("<PARAM NAME = 'downloadIcon'                  VALUE = '"+downloadIcon+"'>");
    document.writeln("<PARAM NAME = 'downloadIconOnMouseover'       VALUE = '"+downloadIconOnMouseover+"'>");
    document.writeln("<PARAM NAME = 'separatePauseCancel'           VALUE = '"+separatePauseCancel+"'>");
    document.writeln("<PARAM NAME = 'lookAndFeel'                   VALUE = '"+delimiter+"'>");
    document.writeln("<PARAM NAME = 'delimiter'                     VALUE = '"+delimiter+"'>");
    document.writeln('<PARAM NAME = "sendLogsToURL"                 VALUE = "'+sendLogsToURL+'">');
    document.writeln('<PARAM NAME = "autoRedirect"                  VALUE = "'+autoRedirect+'">');
    document.writeln('<PARAM NAME = "autoReveal"                    VALUE = "'+autoReveal+'">');
    document.writeln('<PARAM NAME = "callurlaftertransfer"          VALUE = "'+callurlaftertransfer+'">');
    document.writeln('<PARAM NAME = "callurlaftertransfertarget"    VALUE = "'+callurlaftertransfertarget+'">');
    document.writeln("<PARAM NAME = 'transfererrorurl'              VALUE = '"+transfererrorurl+"'>");
    document.writeln("<PARAM NAME = 'transfererrorurltarget'        VALUE = '"+transfererrorurltarget+"'>");
    document.writeln("<PARAM NAME = 'transfercancelurl'             VALUE = '"+transfercancelurl+"'>");
    document.writeln("<PARAM NAME = 'transfercancelurltarget'       VALUE = '"+transfercancelurltarget+"'>");
    document.writeln("<PARAM NAME = 'transferpauseurl'              VALUE = '"+transferpauseurl+"'>");
    document.writeln("<PARAM NAME = 'transferpauseurltarget'        VALUE = '"+transferpauseurltarget+"'>");
    document.writeln("<PARAM NAME = 'othererrorurl'                 VALUE = '"+othererrorurl+"'>");
    document.writeln("<PARAM NAME = 'othererrorurltarget'           VALUE = '"+othererrorurltarget+"'>");
    document.writeln("<PARAM NAME = 'callurlonload'                 VALUE = '"+callurlonload+"'>");
    document.writeln("<PARAM NAME = 'callurlonloadtarget'           VALUE = '"+callurlonloadtarget+"'>");
    document.writeln("<PARAM NAME = 'allParamsLoaded'               VALUE = '"+allParamsLoaded+"'>");
    //end of error message
    if (is_ie && !isMacJaguar()) {
        document.write("</OBJECT>");
    } else {
        document.write("</APPLET>");
    }
} else if (is_nav) {
    document.writeln("<EMBED name='FileCatalyst' TYPE = 'application/x-java-applet;version=1.4' PLUGINSPAGE = 'http://www.java.com' BORDER='0' java_CODE='unlimited.fc.client.FileCatalystDownloadApplet.class' height='"+height+"' width='"+width+"' java_CODEBASE = . java_ARCHIVE='FileCatalystApplets.jar'");
    document.writeln("debug='"+debug+"'");
    document.writeln("server='"+server+"'");
    document.writeln("port='"+port+"'");
    document.writeln("user='"+user+"'");
    document.writeln("pass='"+pass+"'");
    document.writeln("encrypt='"+encrypt+"'");
    document.writeln("ek='"+ek+"'");
    document.writeln("clientConnectKey='"+clientConnectKey+"'");
    document.writeln("enableSSL='"+enableSSL+"'");
    document.writeln("waitRetry='"+waitRetry+"'");
    document.writeln("maxRetries='"+maxRetries+"'");
    document.writeln("localPort='"+localPort+"'");
    document.writeln("mode='"+mode+"'");
    document.writeln("servletLocation='"+servletLocation+"'");
    document.writeln("servletUploadMultiplier='"+servletUploadMultiplier+"'");
    document.writeln("blocksize='"+blocksize+"'");
    document.writeln("numFTPStreams='"+numFTPStreams+"'");
    document.writeln("unitsize='"+unitsize+"'");
    document.writeln("bandwidth='"+bandwidth+"'");
    document.writeln("numSenderThreads='"+numSenderThreads+"'");
    document.writeln("congestionControl='"+congestionControl+"'");
    document.writeln("startRate='"+startRate+"'");
    document.writeln("congestionControlAggression='"+congestionControlAggression+"'");
    document.writeln("congestionControlStrategy='"+congestionControlStrategy+"'");
    document.writeln("numSenderSockets='"+numSenderSockets+"'");
    document.writeln("numReceiveSockets='"+numReceiveSockets+"'");
    document.writeln("numPacketProcessors='"+numPacketProcessors+"'");
    document.writeln("packetQueueDepth='"+packetQueueDepth+"'");
    document.writeln("numBlockWriters='"+numBlockWriters+"'");
    document.writeln("writeBufferSizeKB='"+writeBufferSizeKB+"'");
    document.writeln("writeFileMode='"+writeFileMode+"'");
    document.writeln("numBlockReaders='"+numBlockReaders+"'");
    document.writeln("readBufferSizeKB='"+readBufferSizeKB+"'");
    document.writeln("forceTCPmodeACKs='"+forceTCPmodeACKs+"'");
    document.writeln("files='"+files+"'");
    document.writeln("filesRename='"+filesRename+"'");
    document.writeln("localdir='"+localdir+"'");
    document.writeln("createlocaldir='"+createlocaldir+"'");
    document.writeln("remotedir='"+remotedir+"'");
    document.writeln("autodownload='"+autodownload+"'");
    document.writeln("incremental='"+incremental+"'");
    document.writeln("incrementalMode='"+incrementalMode+"'");
    document.writeln("verifyIntegrity='"+verifyIntegrity+"'");
    document.writeln("verifyMode='"+verifyMode+"'");
    document.writeln("progressive='"+progressive+"'");
    document.writeln("autoresume='"+autoresume+"'");
    document.writeln("compression='"+compression+"'");
    document.writeln("compMethod='"+compMethod+"'");
    document.writeln("compLevel='"+compLevel+"'");
    document.writeln("autoZip='"+autoZip+"'");
    document.writeln("zipFileSizeLimit='"+zipFileSizeLimit+"'");    
    document.writeln("useTempName='"+useTempName+"'");
    document.writeln("preservePathStructure='"+preservePathStructure+"'");
    document.writeln("keepFileAttributes='"+keepFileAttributes+"'");
    document.writeln("deletePartial='"+deletePartial+"'");
    document.writeln("confirmOverwrite='"+confirmOverwrite+"'");
    document.writeln("background='"+background+"'");
    document.writeln("buttonbackground='"+buttonbackground+"'");
    document.writeln("buttonTextColor='"+buttonTextColor+"'");
    document.writeln("buttonColorOnMouseOver='"+buttonColorOnMouseOver+"'");
    document.writeln("buttonTextColorOnMouseOver='"+buttonTextColorOnMouseOver+"'");
    document.writeln("headerTextColor='"+headerTextColor+"'");
    document.writeln("showDialogs='"+showDialogs+"'");
    document.writeln("ProgBarGraphic='"+ProgBarGraphic+"'");
    document.writeln("useImageButtonForDownload='"+useImageButtonForDownload+"'");
    document.writeln("downloadIcon='"+downloadIcon+"'");
    document.writeln("downloadIconOnMouseover='"+downloadIconOnMouseover+"'");
    document.writeln("separatePauseCancel='"+separatePauseCancel+"'");
    document.writeln("lookAndFeel='"+lookAndFeel+"'");
    document.writeln("delimiter='"+delimiter+"'");
    document.writeln("sendLogsToURL='"+sendLogsToURL+"'");
    document.writeln("autoRedirect='"+autoRedirect+"'");
    document.writeln("callurlaftertransfer='"+callurlaftertransfer+"'");
    document.writeln("callurlaftertransfertarget='"+callurlaftertransfertarget+"'");
    document.writeln("transfererrorurl='"+transfererrorurl+"'");
    document.writeln("transfererrorurltarget='"+transfererrorurltarget+"'");
    document.writeln("transfercancelurl='"+transfercancelurl+"'");
    document.writeln("transfercancelurltarget='"+transfercancelurltarget+"'");
    document.writeln("transferpauseurl='"+transferpauseurl+"'");
    document.writeln("transferpauseurltarget='"+transferpauseurltarget+"'");
    document.writeln("othererrorurl='"+othererrorurl+"'");
    document.writeln("othererrorurltarget='"+othererrorurltarget+"'");
    document.writeln("callurlonload='"+callurlonload+"'");
    document.writeln("callurlonloadtarget='"+callurlonloadtarget+"'");
    document.writeln("allParamsLoaded='"+allParamsLoaded+"'");
    document.writeln("autoReveal='"+autoReveal+"'");
    document.writeln(">");
}
