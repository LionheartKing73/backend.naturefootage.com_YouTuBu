<%@page contentType="text/html"%>
<%@page pageEncoding="UTF-8"%>
<%@ page language="java" import= "java.io.*,java.lang.*,java.util.*,unlimited.ftp.*,java.net.*"%>

<%

String fileList = request.getParameter("fileList");
String server = request.getParameter("server");
String port = request.getParameter("port");
String user = request.getParameter("username");
String pass = request.getParameter("password");

//out.write(chk+ server);
%>
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
   <meta name="GENERATOR" content="Mozilla/4.61 [en] (WinNT; I) [Netscape]">
   <meta name="Author" content="Elton Carneiro">
<link rel="stylesheet" href="styles.css" />
   <title>FileCatalyst Download Example</title>

</head>
<body bgcolor="#FFFFFF">
<center>
<img src="images/fileCatalystLogo.jpg" width="301" height="155">
<h1>
FileCatalyst Download Applet demo page.
</h1>
<h3>This page demonstrates how a client can use a server side scripting language to customize the FileCatalyst Download applet dynamically. </h3>

<script>
var server                      = "<%=server%>"; // IP address of FileCatalyst Server
var port                        = "<%=port%>";
var user                        = "<%=user%>";
var pass                        = "<%=pass%>";

var enableSSL                   = "";
var waitRetry                   = "";
var maxRetries                  = "";

var mode                        = ""; // UDP or FTP
var numFTPStreams               = ""; // number of concurrent TCP streams to use in FTP mode
var blocksize                   = ""; // increasing this uses more memory to buffer but may increase performance
var numEncoders                 = "";
var unitsize                    = "";
var bandwidth                   = ""; // target rate, FC will attempt to transfer at this rate (defaults to 1500)
var slowstart                   = ""; // if set to true, FC will slowly increase speed until it hits congestion, or the target rate (default true)
var slowStartRate               = ""; // this is the rate at which the transfer will start, should be lower than the target rate
var localdir                    = "C:\\";
var remotedir                   = ""; // this must be a relative path, no leading slash
var files                       = "<%=fileList%>"; // semi-colon or colon delimited list of filenames (must be known to exist in specified remotedir)
var verifyIntegrity             = ""; // true or false (compares using MD5 hash sum)
var verifyMode 			= "";
var autoresume                  = "";
var progressive                 = "";
var preservePathStructure       = "";

var autodownload                = "";
var sendLogsToURL               = "";


var compression                 = "";
var compLevel                   = "";
var compStrategy                = "";

var autoZip                     = "";

var localPort                   = "";

var incremental                 = "true";
var incrementalMode             = "1";

var callurlaftertransfer        = ""; // redirect to this URL after successful upload
var callurlaftertransfertarget  = "";
var transfererrorurl            = ""; // redirect to this URL after failed upload
var transfererrorurltarget      = "";
var transfercancelurl           = ""; // redirect to this URL after cancelled upload
var transfercancelurltarget     = "";
var othererrorurl               = "";
var othererrorurltarget         = "";

// Values that effect the color of the client
// Red,Green,Blue (RGB) colors in 0-255 decimal numeration (not HEX)
// examples:  "0,0,0" is black, "255,0,0" is red, "0,0,255" is blue, "255,255,255" is white
var background                  = "";
var buttonTextColor             = "";
var buttonbackground            = "";
var buttonTextColorOnMouseOver  = "";
var buttonColorOnMouseOver      = "";
var headerTextColor             = "";

var useImageButtonForDownload   = ""; // set value to "true" if you want the download button to be an icon button.  Default is false.
var downloadIcon                = ""; // This is a URL to the download button image (example: “/images/mybutton.png”). Blank value defaults to a Classic Windows button image
var downloadIconOnMouseover     = ""; // This is a URL to the download button image when the mouse hovers over the button.  If you do not want an effect, use the same image for both.  Blank value defaults to a Classic Windows button image

var width                       = "150";
var height                      = "50";


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
    if (is_safari || is_opera6up || is_konqueror) return true;   // we know safari and opera use java plugin
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


if (!is_nav || is_safari) {
    if (is_ie && !is_mac) {
        document.write("<OBJECT name='FileCatalyst' classid='clsid:8AD9C840-044E-11D1-B3E9-00805F499D93' codebase='http://java.sun.com/products/plugin/autodl/jinstall-1_4-windows-i586.cab#Version=1,4,2,0' height="+height+" width="+width+"'>");
    } else {
        document.write("<APPLET name='FileCatalyst' code='unlimited.fc.client.FileCatalystDownloadApplet.class' height="+height+" width="+width+" archive='FileCatalystApplets.jar' VIEWASTEXT>");
    }
    document.writeln("<param name='archive' value='FileCatalystApplets.jar'>");
    document.writeln("<param name='code' value='unlimited.fc.client.FileCatalystDownloadApplet.class'>");
    document.writeln("<PARAM NAME='scriptable' VALUE='true'>");
    document.writeln("<PARAM NAME='MAYSCRIPT' VALUE='true'>");
    document.writeln("<PARAM NAME = 'id' VALUE='uupload'>");
    document.writeln("<PARAM NAME='debug' VALUE='false'>");
    document.writeln('<PARAM NAME = "server" VALUE = "'+server+'"> ');
    document.writeln('<PARAM NAME = "port" VALUE = "'+port+'"> ');
    document.writeln('<PARAM NAME = "blockSize" VALUE = "'+blocksize+'"> ');
    document.writeln('<PARAM NAME = "unitSize" VALUE = "'+unitsize+'"> ');
    document.writeln('<PARAM NAME = "bandwidth" VALUE = "'+bandwidth+'">');
    document.writeln('<PARAM NAME = "localdir" VALUE = "'+localdir+'">');
    document.writeln('<PARAM NAME = "numEncoders" VALUE = "'+numEncoders+'">');
    document.writeln('<PARAM NAME = "remotedir" VALUE = "'+remotedir+'">');
    document.writeln('<PARAM NAME = "files" VALUE = "'+files+'">');
    document.writeln('<PARAM NAME = "callurlaftertransfer" VALUE = "'+callurlaftertransfer+'">');
    document.writeln('<PARAM NAME = "callurlaftertransfertarget" VALUE = "'+callurlaftertransfertarget+'">');
    document.writeln("<PARAM NAME='transfererrorurl' VALUE='"+transfererrorurl+"'>");
    document.writeln("<PARAM NAME='transfercancelurl' VALUE='"+transfercancelurl+"'>");
    document.writeln("<PARAM NAME='transfererrorurltarget' VALUE='"+transfererrorurltarget+"'>");
    document.writeln("<PARAM NAME='transfercancelurltarget' VALUE='"+transfercancelurltarget+"'>");
    document.writeln("<PARAM NAME='othererrorurl' VALUE='"+othererrorurl+"'>");
    document.writeln("<PARAM NAME='othererrorurltarget' VALUE='"+othererrorurltarget+"'>");
    document.writeln('<PARAM NAME = "verifyIntegrity" VALUE = "'+verifyIntegrity+'">');
    document.writeln('<PARAM NAME = "verifyMode" VALUE = "'+verifyMode+'">');
    document.writeln('<PARAM NAME = "enableSSL" VALUE = "'+enableSSL+'">');
    document.writeln('<PARAM NAME = "pass" VALUE = "'+pass+'">');
    document.writeln('<PARAM NAME = "user" VALUE = "'+user+'">');
    document.writeln('<PARAM NAME = "slowstart" VALUE = "'+slowstart+'">');
    document.writeln('<PARAM NAME = "waitRetry" VALUE = "'+waitRetry+'">');
    document.writeln('<PARAM NAME = "maxRetries" VALUE = "'+maxRetries+'">');
    document.writeln('<PARAM NAME = "slowStartRate" VALUE = "'+slowStartRate+'">');
    document.writeln('<PARAM NAME = "autoresume" VALUE = "'+autoresume+'">');
    document.writeln('<PARAM NAME = "progressive" VALUE = "'+progressive+'">');
    document.writeln('<PARAM NAME = "mode" VALUE = "'+mode+'">');
    document.writeln('<PARAM NAME = "numFTPStreams" VALUE = "'+numFTPStreams+'">');
    document.writeln('<PARAM NAME = "autodownload" VALUE = "'+autodownload+'">');
    document.writeln('<PARAM NAME = "sendLogsToURL" VALUE = "'+sendLogsToURL+'">');
    document.writeln('<PARAM NAME = "preservePathStructure" VALUE = "'+preservePathStructure+'">');
    document.writeln('<PARAM NAME = "compression" VALUE = "'+compression+'">');
    document.writeln('<PARAM NAME = "compLevel" VALUE = "'+compLevel+'">');
    document.writeln('<PARAM NAME = "compStrategy" VALUE = "'+compStrategy+'">');
    document.writeln('<PARAM NAME = "autoZip" VALUE = "'+autoZip+'">');
    document.writeln('<PARAM NAME = "incremental" VALUE = "'+incremental+'">');
    document.writeln('<PARAM NAME = "incrementalMode" VALUE = "'+incrementalMode+'">');
    document.writeln('<PARAM NAME = "localPort" VALUE = "'+localPort+'">');
    document.write("<PARAM NAME='background' VALUE='"+background+"'>");
    document.write("<PARAM NAME='buttonbackground' VALUE='"+buttonbackground+"'>");
    document.write("<PARAM NAME='buttonTextColor' VALUE='"+buttonTextColor+"'>");
    document.write("<PARAM NAME='buttonColorOnMouseOver' VALUE='"+buttonColorOnMouseOver+"'>");
    document.write("<PARAM NAME='buttonTextColorOnMouseOver' VALUE='"+buttonTextColorOnMouseOver+"'>");
    document.write("<PARAM NAME='headerTextColor' VALUE='"+headerTextColor+"'>");

    document.write("<PARAM NAME='useImageButtonForDownload' VALUE='"+useImageButtonForDownload+"'>");
    document.write("<PARAM NAME='downloadIcon' VALUE='"+downloadIcon+"'>");
    document.write("<PARAM NAME='downloadIconOnMouseover' VALUE='"+downloadIconOnMouseover+"'>");

    //end of error message
    if (is_ie && !isMacJaguar()) {
        document.write("</OBJECT>");
    } else {
        document.write("</APPLET>");
    }
} else if (is_nav) {
    document.writeln("<EMBED name='FileCatalyst' TYPE = 'application/x-java-applet;version=1.4' PLUGINSPAGE = 'http://www.java.com' BORDER='0' java_CODE='unlimited.fc.client.FileCatalystDownloadApplet.class' height='"+height+"' width='"+width+"' java_CODEBASE = . java_ARCHIVE='FileCatalystApplets.jar'");
    document.writeln("server='"+server+"'");
    document.writeln("port='"+port+"'");
    document.writeln("blocksize='"+blocksize+"'");
    document.writeln("unitsize='"+unitsize+"'");
    document.writeln("numEncoders='"+numEncoders+"'");
    document.writeln("bandwidth='"+bandwidth+"'");
    document.writeln("localdir='"+localdir+"'");
    document.writeln("remotedir='"+remotedir+"'");
    document.writeln("files='"+files+"'");
    document.writeln("callurlaftertransfer='"+callurlaftertransfer+"'");
    document.writeln("callurlaftertransfertarget='"+callurlaftertransfertarget+"'");
    document.writeln("transfererrorurl='"+transfererrorurl+"'");
    document.writeln("transfercancelurl='"+transfercancelurl+"'");
    document.writeln("transfererrorurltarget='"+transfererrorurltarget+"'");
    document.writeln("transfercancelurltarget='"+transfercancelurltarget+"'");
    document.writeln("othererrorurl='"+othererrorurl+"'");
    document.writeln("othererrorurltarget='"+othererrorurltarget+"'");
    document.writeln("verifyIntegrity='"+verifyIntegrity+"'");
    document.writeln("verifyMode='"+verifyMode+"'");
    document.writeln("enableSSL='"+enableSSL+"'");
    document.writeln("pass='"+pass+"'");
    document.writeln("user='"+user+"'");
    document.writeln("slowstart='"+slowstart+"'");
    document.writeln("waitRetry='"+waitRetry+"'");
    document.writeln("maxRetries='"+maxRetries+"'");
    document.writeln("slowStartRate='"+slowStartRate+"'");
    document.writeln("autoresume='"+autoresume+"'");
    document.writeln("progressive='"+progressive+"'");
    document.writeln("mode='"+mode+"'");
    document.writeln("numFTPStreams='"+numFTPStreams+"'");
    document.writeln("autodownload='"+autodownload+"'");
    document.writeln("sendLogsToURL='"+sendLogsToURL+"'");
    document.writeln("preservePathStructure='"+preservePathStructure+"'");
    document.writeln("compression='"+compression+"'");
    document.writeln("compLevel='"+compLevel+"'");
    document.writeln("compStrategy='"+compStrategy+"'");
    document.writeln("autoZip='"+autoZip+"'");
    document.writeln("incremental='"+incremental+"'");
    document.writeln("incrementalMode='"+incrementalMode+"'");
    document.writeln("localPort='"+localPort+"'");
    document.writeln("background='"+background+"'");
    document.writeln("buttonbackground='"+buttonbackground+"'");
    document.writeln("buttonTextColor='"+buttonTextColor+"'");
    document.writeln("buttonColorOnMouseOver='"+buttonColorOnMouseOver+"'");
    document.writeln("buttonTextColorOnMouseOver='"+buttonTextColorOnMouseOver+"'");
    document.writeln("headerTextColor='"+headerTextColor+"'");

    document.writeln("useImageButtonForDownload='"+useImageButtonForDownload+"'");
    document.writeln("downloadIcon='"+downloadIcon+"'");
    document.writeln("downloadIconOnMouseover='"+downloadIconOnMouseover+"'");

    document.writeln(">");
}
</script>
</center>
</body>
</html>