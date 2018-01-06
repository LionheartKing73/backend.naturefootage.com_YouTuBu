


// Connection related values

var server                      = "";
var port                        = "";
var user                        = "";
var pass                        = "";
var autoconnect                 = "";
var autoreconnect               = "";
var passive                     = "";
var encrypt                     = "";
var ek                          = "";
var connecttimeout              = "";
var sotimeout                   = "";
var waitRetry                   = "";
var maxRetries                  = "";
var servletLocation             = "";

var clientConnectKey            = ""; // In order to conenct to non-FileCatalyst servers, this key must be present

// FileCatalyst parameters
var mode                        = "UDP"; //UDP, TCP or HTTP (HTTP will only work if servletLocation is specified)
var blocksize                   = "";
var unitsize                    = "";
var numEncoders                 = "";

var bandwidth                   = "";  //default 100000

var verifyIntegrity             = "";  // true or false
var verifyIntegrityMode         = "";  // 0 for after transfer, 1 for on the fly

var enableSSL                   = "";

var keepFileAttributes          = "";
var congestionControl           = ""; // true or false
var startRate                   = ""; // this is the rate at which the transfer will start, should be lower than the target rate. default 1000 Kbps
var congestionControlAggression = ""; // default 5
var congestionControlStrategy   = ""; // 0 for RTT based, 1 for Packet loss based.  default 1

var numFTPStreams               = ""; // number of concurrent TCP streams to use in FTP mode (default 5)

var incremental                 = ""; // true or false
var incrementalMode             = ""; // 0 for transfer entire file, 1 for deltas

var useTempName                 = ""; // true or false
var useTempNameMode             = ""; // 0 for prefix , 1 for suffix

var compression                 = "";
var compLevel                   = "";

// Proxy related settings for IE only

var autodetectproxy             = "";
var socksproxy                  = "";
var socksProxyHost              = "";
var socksProxyPort              = "";
var ftpproxy                    = "";
var ftpProxyHost                = "";
var ftpProxyPort                = "";

// Functionallity related values

var ascbin                      = "";
var showascbin                  = "";
var asciiextensions             = "";
var extensions                  = "";
var exclude                     = "";
var invertExclude               = "";
var lockinitialdir              = "";
var remotedir                   = "";
var localdir                    = "";
var deleteoncancel              = "";
var enableCookies               = "";
var doubleClickTransfer         = "";
var enablerightclick            = "";
var enablekeyboardshortcuts     = "";
var confirmoverwrite            = "";
var syncpriority                = "";

var selectalllocal              = "";
var selectallremote             = "";
var autoupload                  = "";
var autodownload                = "";
var autoallo                    = "";
var hostsAllowed                = "";
var createdirectoryonconnect    = "";
var confirmTransfer             = "";
var totalProgress               = "";
var enableResume                = "";
var customFileOptions           = "";
var customDirOptions            = "";
var sendLogsToURL               = "";
var helplocation                = "documentation.html";

// Values that effect the color of the client

var background                  = "";
var buttonTextColorOnMouseOver  = "";
var buttonTextColor             = "";
var buttonColorOnMouseOver      = "";
var buttonbackground            = "";
var headerTextColor             = "";
var headerBackgroundColor       = "";
var drivesForegroundColor       = "";
var drivesBackgroundColor       = "";
var ascBinTextColor             = "";

// values that effect the interface layout of the client

var language                    = "";
var showsizeanddate             = "";
var LocalOptions                = "";
var RemoteOptions               = "";
var strechButtons               = "";
var display                     = "";
var showhelpbutton              = "";
var showputbutton               = "";
var showgetbutton               = "";
var showsyncbutton              = "";
var showaboutbutton             = "";
var showconnectbutton           = "";
var showdisconnectbutton        = "";
var showlocallist               = "";
var showremotelist              = "";
var showSizeInKB                = "";
var showlocaladdressbar         = "";
var showremoteaddressbar        = "";
var showFileInfoBar             = "";
var showStatusBar               = "";
var useBottomToolbar            = "";
var remoteheader                = "";
var width                       = "640";
var height                      = "400";

var showAdvancedTab             = "";
var showSitename                = "";
var showHostname                = "";
var showUsername                = "";
var showPassword                = "";
var showAnonymous               = "";
var showSaveConnection          = "";

// some customizable error pages


var rejectPermissionURL         = "rejectPerms.html";
var errNavWin                   = "errNavWin.html";
var errIEWin                    = "errIEWin.html";
var errIEWinVM                  = "errIEWinVM.html";
var errNavUnix                  = "errNavUnix.html";
var errIEMac                    = "errIEMac.html";
var errNavMac                   = "errNavMac.html";
var errOperaWin                 = "errOperaWin.html";

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
            document.write("<OBJECT name='FileCatalyst' classid='clsid:CAFEEFAC-0016-0000-0018-ABCDEFFEDCBA' codebase=�http://java.sun.com/update/1.6.0/jinstall-6-windows-i586.cab#Version=1,6,0,18' height="+height+" width="+width+"'>");
            // old Java 1.4
            //document.write("<OBJECT name='FileCatalyst Transfer Applet' classid='clsid:8AD9C840-044E-11D1-B3E9-00805F499D93' codebase='http://java.sun.com/products/plugin/autodl/jinstall-1_4-windows-i586.cab#Version=1,4,2,0' height="+height+" width="+width+"'>");
        } else {
            document.write("<APPLET name='FileCatalyst Transfer Applet' code='unlimited.ftp.FileCatalystTransferApplet.class' height="+height+" width="+width+" archive='fctransferapplet.jar' VIEWASTEXT>");
        }
        document.write("<param name='archive' value='fctransferapplet.jar'>");
        document.write("<param name='code' value='unlimited.ftp.FileCatalystTransferApplet.class'>");
        document.write("<PARAM NAME='server' VALUE='"+server+"'>");
        document.write("<PARAM NAME='port' VALUE='"+port+"'>");
        document.write("<PARAM NAME='pass' VALUE='"+pass+"'>");
        document.write("<PARAM NAME='user' VALUE='"+user+"'>");
        document.write("<PARAM NAME='autoconnect' VALUE='"+autoconnect+"'>");
        document.write("<PARAM NAME='autoreconnect' VALUE='"+autoreconnect+"'>");
        document.write("<PARAM NAME='ascbin' VALUE='"+ascbin+"'>");
        document.write("<PARAM NAME='showascbin' VALUE='"+showascbin+"'>");
        document.write("<PARAM NAME='asciiextensions' VALUE='"+asciiextensions+"'>");
        document.write("<PARAM NAME='passive' VALUE='"+passive+"'>");
        document.write("<PARAM NAME='extensions' VALUE='"+extensions+"'>");
        document.write("<PARAM NAME='exclude' VALUE='"+exclude+"'>");
        document.write("<PARAM NAME='lockinitialdir' VALUE='"+lockinitialdir+"'>");
        document.write("<PARAM NAME='remotedir' VALUE='"+remotedir+"'>");
        document.write("<PARAM NAME='localdir' VALUE='"+localdir+"'>");
        document.write("<PARAM NAME='enableCookies' VALUE='"+enableCookies+"'>");
        document.write("<PARAM NAME='doubleClickTransfer' VALUE='"+doubleClickTransfer+"'>");
        document.write("<PARAM NAME='syncpriority' VALUE='"+syncpriority+"'>");
        document.write("<PARAM NAME='background' VALUE='"+background+"'>");
        document.write("<PARAM NAME='buttonTextColorOnMouseOver' VALUE='"+buttonTextColorOnMouseOver+"'>");
        document.write("<PARAM NAME='buttonTextColor' VALUE='"+buttonTextColor+"'>");
        document.write("<PARAM NAME='buttonColorOnMouseOver' VALUE='"+buttonColorOnMouseOver+"'>");
        document.write("<PARAM NAME='headerTextColor' VALUE='"+headerTextColor+"'>");
        document.write("<PARAM NAME='headerBackgroundColor' VALUE='"+headerBackgroundColor+"'>");
        document.write("<PARAM NAME='drivesForegroundColor' VALUE='"+drivesForegroundColor+"'>");
        document.write("<PARAM NAME='drivesBackgroundColor' VALUE='"+drivesBackgroundColor+"'>");
        document.write("<PARAM NAME='ascBinTextColor' VALUE='"+ascBinTextColor+"'>");
        document.write("<PARAM NAME='buttonbackground' VALUE='"+buttonbackground+"'>");
        document.write("<PARAM NAME='language' VALUE='"+language+"'>");
        document.write("<PARAM NAME='helplocation' VALUE='"+helplocation+"'>");
        document.write("<PARAM NAME='LocalOptions' VALUE='"+LocalOptions+"'>");
        document.write("<PARAM NAME='RemoteOptions' VALUE='"+RemoteOptions+"'>");
        document.write("<PARAM NAME='strechButtons' VALUE='"+strechButtons+"'>");
        document.write("<PARAM NAME='display' VALUE='"+display+"'>");
        document.write("<PARAM NAME='showhelpbutton' VALUE='"+showhelpbutton+"'>");
        document.write("<PARAM NAME='showputbutton' VALUE='"+showputbutton+"'>");
        document.write("<PARAM NAME='showgetbutton' VALUE='"+showgetbutton+"'>");
        document.write("<PARAM NAME='showsyncbutton' VALUE='"+showsyncbutton+"'>");
        document.write("<PARAM NAME='showaboutbutton' VALUE='"+showaboutbutton+"'>");
        document.write("<PARAM NAME='showlocallist' VALUE='"+showlocallist+"'>");
        document.write("<PARAM NAME='showremotelist' VALUE='"+showremotelist+"'>");
        document.write("<PARAM NAME='errIEWinVM' VALUE='"+errIEWinVM+"'>");
        document.write("<PARAM NAME='selectalllocal' VALUE='"+selectalllocal+"'>");
        document.write("<PARAM NAME='selectallremote' VALUE='"+selectallremote+"'>");
        document.write("<PARAM NAME='autoupload' VALUE='"+autoupload+"'>");
        document.write("<PARAM NAME='autodownload' VALUE='"+autodownload+"'>");
        document.write("<PARAM NAME='autoallo' VALUE='"+autoallo+"'>");
        document.write("<PARAM NAME='rejectPermissionURL' VALUE='"+rejectPermissionURL+"'>");
        document.write("<PARAM NAME='encrypt' VALUE='"+encrypt+"'>");
        document.write("<PARAM NAME='ek' VALUE='"+ek+"'>");
        document.write("<PARAM NAME='createdirectoryonconnect' VALUE='"+createdirectoryonconnect+"'>");

        document.write("<PARAM NAME='sotimeout' VALUE='"+sotimeout+"'>");
        document.write("<PARAM NAME='connecttimeout' VALUE='"+connecttimeout+"'>");
        document.write("<PARAM NAME='enablerightclick' VALUE='"+enablerightclick+"'>");
        document.write("<PARAM NAME='hostsAllowed' VALUE='"+hostsAllowed+"'>");
        document.write("<PARAM NAME='remoteheader' VALUE='"+remoteheader+"'>");

        document.write("<PARAM NAME='enablekeyboardshortcuts' VALUE='"+enablekeyboardshortcuts+"'>");
        document.write("<PARAM NAME='confirmoverwrite' VALUE='"+confirmoverwrite+"'>");
        document.write("<PARAM NAME='showsizeanddate' VALUE='"+showsizeanddate+"'>");
        document.write("<PARAM NAME='showSizeInKB' VALUE='"+showSizeInKB+"'>");
        document.write("<PARAM NAME='deleteoncancel' VALUE='"+deleteoncancel+"'>");
        document.write("<PARAM NAME='showlocaladdressbar' VALUE='"+showlocaladdressbar+"'>");
        document.write("<PARAM NAME='showremoteaddressbar' VALUE='"+showremoteaddressbar+"'>");
        document.write("<PARAM NAME='waitRetry' VALUE='"+waitRetry+"'>");
        document.write("<PARAM NAME='maxRetries' VALUE='"+maxRetries+"'>");
        document.write("<PARAM NAME='useBottomToolbar' VALUE='"+useBottomToolbar+"'>");


        document.write("<PARAM NAME='invertExclude' VALUE='"+invertExclude+"'>");
        document.write("<PARAM NAME='confirmTransfer' VALUE='"+confirmTransfer+"'>");
        document.write("<PARAM NAME='totalProgress' VALUE='"+totalProgress+"'>");
        document.write("<PARAM NAME='enableResume' VALUE='"+enableResume+"'>");
        document.write("<PARAM NAME='customFileOptions' VALUE='"+customFileOptions+"'>");
        document.write("<PARAM NAME='customDirOptions' VALUE='"+customDirOptions+"'>");
        document.write("<PARAM NAME='sendLogsToURL' VALUE='"+sendLogsToURL+"'>");
        document.write("<PARAM NAME='showconnectbutton' VALUE='"+showconnectbutton+"'>");
        document.write("<PARAM NAME='showdisconnectbutton' VALUE='"+showdisconnectbutton+"'>");
        document.write("<PARAM NAME='showFileInfoBar' VALUE='"+showFileInfoBar+"'>");
        document.write("<PARAM NAME='showStatusBar' VALUE='"+showStatusBar+"'>");

        document.writeln('<PARAM NAME = "mode" VALUE = "'+mode+'">');
        document.write("<PARAM NAME='numFTPStreams' VALUE='"+numFTPStreams+"'>");
        document.writeln('<PARAM NAME = "startRate" VALUE = "'+startRate+'">');
        document.writeln('<PARAM NAME = "blockSize" VALUE = "'+blocksize+'"> ');
        document.writeln('<PARAM NAME = "unitSize" VALUE = "'+unitsize+'"> ');
        document.writeln('<PARAM NAME = "bandwidth" VALUE = "'+bandwidth+'">');
        document.writeln('<PARAM NAME = "keepFileAttributes" VALUE = "'+keepFileAttributes+'">');
        document.writeln('<PARAM NAME = "numEncoders" VALUE = "'+numEncoders+'">');
        document.writeln('<PARAM NAME = "congestionControl" VALUE = "'+congestionControl+'">');
        document.writeln('<PARAM NAME = "verifyIntegrity" VALUE = "'+verifyIntegrity+'">');
        document.writeln('<PARAM NAME = "verifyIntegrityMode" VALUE = "'+verifyIntegrityMode+'">');
        document.writeln('<PARAM NAME = "enableSSL" VALUE = "'+enableSSL+'">');
        document.writeln('<PARAM NAME = "incremental" VALUE = "'+incremental+'">');
        document.writeln('<PARAM NAME = "incrementalMode" VALUE = "'+incrementalMode+'">');
        document.writeln('<PARAM NAME = "useTempName" VALUE = "'+useTempName+'">');
        document.writeln('<PARAM NAME = "useTempNameMode" VALUE = "'+useTempNameMode+'">');

        document.writeln('<PARAM NAME = "showAdvancedTab" VALUE = "'+showAdvancedTab+'">');
        document.writeln('<PARAM NAME = "showSitename" VALUE = "'+showSitename+'">');
        document.writeln('<PARAM NAME = "showHostname" VALUE = "'+showHostname+'">');
        document.writeln('<PARAM NAME = "showUsername" VALUE = "'+showUsername+'">');
        document.writeln('<PARAM NAME = "showPassword" VALUE = "'+showPassword+'">');
        document.writeln('<PARAM NAME = "showAnonymous" VALUE = "'+showAnonymous+'">');
        document.writeln('<PARAM NAME = "showSaveConnection" VALUE = "'+showSaveConnection+'">');

        document.writeln('<PARAM NAME = "compression" VALUE = "'+compression+'">');
        document.writeln('<PARAM NAME = "compLevel" VALUE = "'+compLevel+'">');
        document.write("<PARAM NAME='clientConnectKey' VALUE='"+clientConnectKey+"'>");
        document.write("<PARAM NAME='congestionControlAggression' VALUE='"+congestionControlAggression+"'>");
        document.write("<PARAM NAME='congestionControlStrategy' VALUE='"+congestionControlStrategy+"'>");
        document.write("<PARAM NAME='servletLocation' VALUE='"+servletLocation+"'>");
        //end of error message
        if (is_ie && !isMacJaguar()) {
            document.write("</OBJECT>");
        } else {
            document.write("</APPLET>");
        }

    } else if (is_nav) {
        document.writeln("<EMBED name='FileCatalyst Transfer Applet' TYPE = 'application/x-java-applet;version=1.4' PLUGINSPAGE = 'http://www.java.com' BORDER='0' java_CODE='unlimited.ftp.FileCatalystTransferApplet.class' height='"+height+"' width='"+width+"' java_CODEBASE = . java_ARCHIVE='fctransferapplet.jar'");
        document.writeln("server='"+server+"'");
        document.writeln("port='"+port+"'");
        document.writeln("pass='"+pass+"'");
        document.writeln("user='"+user+"'");
        document.writeln("autoconnect='"+autoconnect+"'");
        document.writeln("autoreconnect='"+autoreconnect+"'");
        document.writeln("ascbin='"+ascbin+"'");
        document.writeln("showascbin='"+showascbin+"'");
        document.writeln("asciiextensions='"+asciiextensions+"'");
        document.writeln("passive='"+passive+"'");
        document.writeln("extensions='"+extensions+"'");
        document.writeln("exclude='"+exclude+"'");
        document.writeln("lockinitialdir='"+lockinitialdir+"'");
        document.writeln("remotedir='"+remotedir+"'");
        document.writeln("localdir='"+localdir+"'");
        document.writeln("enableCookies='"+enableCookies+"'");
        document.writeln("doubleClickTransfer='"+doubleClickTransfer+"'");
        document.writeln("syncpriority='"+syncpriority+"'");
        document.writeln("background='"+background+"'");
        document.writeln("buttonTextColorOnMouseOver='"+buttonTextColorOnMouseOver+"'");
        document.writeln("buttonTextColor='"+buttonTextColor+"'");
        document.writeln("buttonColorOnMouseOver='"+buttonColorOnMouseOver+"'");
        document.writeln("headerTextColor='"+headerTextColor+"'");
        document.writeln("headerBackgroundColor='"+headerBackgroundColor+"'");
        document.writeln("drivesForegroundColor='"+drivesForegroundColor+"'");
        document.writeln("drivesBackgroundColor='"+drivesBackgroundColor+"'");
        document.writeln("ascBinTextColor='"+ascBinTextColor+"'");
        document.writeln("buttonbackground='"+buttonbackground+"'");
        document.writeln("language='"+language+"'");
        document.writeln("helplocation='"+helplocation+"'");
        document.writeln("LocalOptions='"+LocalOptions+"'");
        document.writeln("RemoteOptions='"+RemoteOptions+"'");
        document.writeln("strechButtons='"+strechButtons+"'");
        document.writeln("display='"+display+"'");
        document.writeln("showhelpbutton='"+showhelpbutton+"'");
        document.writeln("showputbutton='"+showputbutton+"'");
        document.writeln("showgetbutton='"+showgetbutton+"'");
        document.writeln("showsyncbutton='"+showsyncbutton+"'");
        document.writeln("showaboutbutton='"+showaboutbutton+"'");
        document.writeln("showlocallist='"+showlocallist+"'");
        document.writeln("showremotelist='"+showremotelist+"'");
        document.writeln("errIEWinVM='"+errIEWinVM+"'");
        document.writeln("selectalllocal='"+selectalllocal+"'");
        document.writeln("selectallremote='"+selectallremote+"'");
        document.writeln("autoupload='"+autoupload+"'");
        document.writeln("autodownload='"+autodownload+"'");
        document.writeln("autoallo='"+autoallo+"'");
        document.writeln("rejectPermissionURL='"+rejectPermissionURL+"'");
        document.writeln("encrypt='"+encrypt+"'");
        document.writeln("ek='"+ek+"'");
        document.writeln("createdirectoryonconnect='"+createdirectoryonconnect+"'");

        document.writeln("sotimeout='"+sotimeout+"'");
        document.writeln("connecttimeout='"+connecttimeout+"'");
        document.writeln("enablerightclick='"+enablerightclick+"'");
        document.writeln("remoteheader='"+remoteheader+"'");

        document.writeln("hostsAllowed='"+hostsAllowed+"'");
        document.writeln("showsizeanddate='"+showsizeanddate+"'");
        document.writeln("showSizeInKB='"+showSizeInKB+"'");
        document.writeln("deleteoncancel='"+deleteoncancel+"'");
        document.writeln("enablekeyboardshortcuts='"+enablekeyboardshortcuts+"'");
        document.writeln("confirmoverwrite='"+confirmoverwrite+"'");
        document.writeln("showremoteaddressbar='"+showremoteaddressbar+"'");
        document.writeln("showlocaladdressbar='"+showlocaladdressbar+"'");
        document.writeln("waitRetry='"+waitRetry+"'");
        document.writeln("maxRetries='"+maxRetries+"'");
        document.writeln("useBottomToolbar='"+useBottomToolbar+"'");
        

        document.writeln("invertExclude='"+invertExclude+"'");
        document.writeln("confirmTransfer='"+confirmTransfer+"'");
        document.writeln("totalProgress='"+totalProgress+"'");
        document.writeln("enableResume='"+enableResume+"'");
        document.writeln("customFileOptions='"+customFileOptions+"'");
        document.writeln("customDirOptions='"+customDirOptions+"'");
        document.writeln("sendLogsToURL='"+sendLogsToURL+"'");
        document.writeln("showconnectbutton='"+showconnectbutton+"'");
        document.writeln("showdisconnectbutton='"+showdisconnectbutton+"'");
        document.writeln("showFileInfoBar='"+showFileInfoBar+"'");
        document.writeln("showStatusBar='"+showStatusBar+"'");
        document.writeln("numFTPStreams='"+numFTPStreams+"'");
        document.writeln("verifyIntegrity='"+verifyIntegrity+"'");
        document.writeln("verifyIntegrityMode='"+verifyIntegrityMode+"'");
        document.writeln("blocksize='"+blocksize+"'");
        document.writeln("unitsize='"+unitsize+"'");
        document.writeln("bandwidth='"+bandwidth+"'");
        document.writeln("keepFileAttributes='"+keepFileAttributes+"'");
        document.writeln("numEncoders='"+numEncoders+"'");
        document.writeln("congestionControl='"+congestionControl+"'");
        document.writeln("startRate='"+startRate+"'");
        document.writeln("mode='"+mode+"'");
        document.writeln("enableSSL='"+enableSSL+"'");
        document.writeln("incremental='"+incremental+"'");
        document.writeln("incrementalMode='"+incrementalMode+"'");
        document.writeln("useTempName='"+useTempName+"'");
        document.writeln("useTempNameMode='"+useTempNameMode+"'");
        document.writeln("compression='"+compression+"'");
        document.writeln("compLevel='"+compLevel+"'");

        document.writeln("showAdvancedTab='"+showAdvancedTab+"'");
        document.writeln("showSitename='"+showSitename+"'");
        document.writeln("showHostname='"+showHostname+"'");
        document.writeln("showUsername='"+showUsername+"'");
        document.writeln("showPassword='"+showPassword+"'");
        document.writeln("showAnonymous='"+showAnonymous+"'");
        document.writeln("showSaveConnection='"+showSaveConnection+"'");

        document.writeln("clientConnectKey='"+clientConnectKey+"'");
        document.writeln("congestionControlAggression='"+congestionControlAggression+"'");
        document.writeln("congestionControlStrategy='"+congestionControlStrategy+"'");
        document.writeln("servletLocation='"+servletLocation+"'");
        document.writeln(">");
    }

