<%
Response.Expires = -1000 'Makes the browser not cache this page
Response.Buffer = True 'Buffers the content so our Response.Redirect will work


Dim username,password
username = Request.Form("username")
password = Request.Form("userpwd")

%>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Sample ASP Code for FileCatalyst Upload Applet</title>
<link href="styles.css" rel="stylesheet" type="text/css">
</head>
<body >


<script>
var numkeys 			= 1; // The number of keys you have
var keys= new Array(numkeys);

// Enter you keys here in the following form, starting with key[0], key[1], key[2], etc...

keys[0]				= "";
//keys[1]				= "";


// Connection related values
var server		= "<YOUR_SERVER_IP/DOMAIN HERE>";
var port		= "<FC SERVER PORT>";
var user		= "<%=username%>";
var pass 		= "<%=password%>";
var autoconnect		= "true";
var autoreconnect		= "true";
var passive 		= "";
var encrypt		= "";
var ek			="";

var mode                        = "";        // UDP, FTP, HTTP (to use with servlet) or AUTO
var numFTPStreams               = "";        // number of concurrent TCP streams to use in FTP mode
var blocksize                   = "";
var unitsize                    = "";
var numEncoders                 = "";
var readbuffer                  = "";
var bandwidth                   = "";
var localdir                    = "";
var remotedir                   = "";
var verifyIntegrity             = "";
var verifyMode 			= "";
var enableSSL                   = "";

var maxRetries                  = "";
var waitRetry                   = "";

var keepFileAttributes          = "";
var slowStart                   = "";
var slowStartRate               = "";        // this is the rate at which the transfer will start, should be lower than the target rate


var autoresume                  = "";
var progressive                 = "";
var useTempName                 = "";

// Parameter relating to HTTP POST of file info

var postURL                     = "";
var filesParam                  = "";
var delimiter                   = "";

var compression                 = "";
var compLevel                   = "";
var compStrategy                = "";

var autoZip                     = "";

var autoDMG                     = "";
var dmgFileName                 = "";

var incremental                 = "";
var incrementalMode             = "";

var preservePathStructure	= "";

var maxfiles                    = "";
var maxsize                     = "";
var showpreview                 = "";

var embedProgress               = "";
var hideLocal                   = "";
var autoUpload                  = "";
var autoRedirect		= "";

var callurlaftertransfer        = "";
var callurlaftertransfertarget  = "";
var transfererrorurl            = "";
var transfererrorurltarget      = "";
var transfercancelurl           = "";
var transfercancelurltarget     = "";
var othererrorurl               = "";
var othererrorurltarget         = "";


// Values that effect the color of the client
// Red,Green,Blue (RGB) colors in 0-255 decimal numeration (not HEX)
// examples:  "0,0,0" is black, "255,0,0" is red, "0,0,255" is blue, "255,255,255" is white
var background                  = "";
var headerTextColor             = "";

var width                       = "500";
var height                      = "300";


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
    if (is_ie && !is_mac)  {
        document.write("<OBJECT name='FileCatalyst' classid='clsid:8AD9C840-044E-11D1-B3E9-00805F499D93' codebase='http://java.sun.com/products/plugin/autodl/jinstall-1_4-windows-i586.cab#Version=1,4,2,0' height="+height+" width="+width+"'>");
    } else {
        document.write("<APPLET name='FileCatalyst' code='unlimited.fc.client.FileCatalystCart.class' height="+height+" width="+width+" archive='FileCatalystApplets.jar' VIEWASTEXT>");
    }
    document.writeln("<param name='archive' value='FileCatalystApplets.jar'>");
    document.writeln("<param name='code' value='unlimited.fc.client.FileCatalystCart.class'>");
    document.writeln("<PARAM NAME='scriptable' VALUE='true'>");
    document.writeln("<PARAM NAME='MAYSCRIPT' VALUE='true'>");
    document.writeln("<PARAM NAME = 'id' VALUE='uupload'>");
    document.writeln("<PARAM NAME='debug' VALUE='false'>");
    document.writeln('<PARAM NAME = "server" VALUE = "'+server+'"> ');
    document.writeln('<PARAM NAME = "port" VALUE = "'+port+'"> ');
    document.writeln('<PARAM NAME = "blockSize" VALUE = "'+blocksize+'"> ');
    document.writeln('<PARAM NAME = "unitSize" VALUE = "'+unitsize+'"> ');
    document.writeln('<PARAM NAME = "readBuffer" VALUE = "'+readbuffer+'"> ');
    document.writeln('<PARAM NAME = "bandwidth" VALUE = "'+bandwidth+'">');
    document.writeln('<PARAM NAME = "localdir" VALUE = "'+localdir+'">');
    document.writeln('<PARAM NAME = "remotedir" VALUE = "'+remotedir+'">');
    document.writeln('<PARAM NAME = "maxRetries" VALUE = "'+maxRetries+'">');
    document.writeln('<PARAM NAME = "waitRetry" VALUE = "'+waitRetry+'">');
    document.writeln('<PARAM NAME = "keepFileAttributes" VALUE = "'+keepFileAttributes+'">');
    document.writeln('<PARAM NAME = "numEncoders" VALUE = "'+numEncoders+'">');
    document.writeln('<PARAM NAME = "slowStart" VALUE = "'+slowStart+'">');
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
    document.writeln('<PARAM NAME = "postURL" VALUE = "'+postURL+'">');
    document.writeln('<PARAM NAME = "filesParam" VALUE = "'+filesParam+'">');
    document.writeln('<PARAM NAME = "delimiter" VALUE = "'+delimiter+'">');
    document.writeln('<PARAM NAME = "enableSSL" VALUE = "'+enableSSL+'">');
    document.writeln('<PARAM NAME = "user" VALUE = "'+user+'">');
    document.writeln('<PARAM NAME = "pass" VALUE = "'+pass+'">');
    document.writeln('<PARAM NAME = "slowStartRate" VALUE = "'+slowStartRate+'">');
    document.writeln('<PARAM NAME = "autoresume" VALUE = "'+autoresume+'">');
    document.writeln('<PARAM NAME = "progressive" VALUE = "'+progressive+'">');
    document.writeln('<PARAM NAME = "mode" VALUE = "'+mode+'">');
    document.writeln('<PARAM NAME = "numFTPStreams" VALUE = "'+numFTPStreams+'">');
    document.writeln('<PARAM NAME = "useTempName" VALUE = "'+useTempName+'">');
    document.writeln('<PARAM NAME = "compression" VALUE = "'+compression+'">');
    document.writeln('<PARAM NAME = "compLevel" VALUE = "'+compLevel+'">');
    document.writeln('<PARAM NAME = "compStrategy" VALUE = "'+compStrategy+'">');
    document.writeln('<PARAM NAME = "autoZip" VALUE = "'+autoZip+'">');
    document.writeln('<PARAM NAME = "incremental" VALUE = "'+incremental+'">');
    document.writeln('<PARAM NAME = "incrementalMode" VALUE = "'+incrementalMode+'">');
    document.write("<PARAM NAME='encrypt' VALUE='"+encrypt+"'>");
    document.write("<PARAM NAME='ek' VALUE='"+ek+"'>");
    document.writeln('<PARAM NAME = "autoDMG" VALUE = "'+autoDMG+'">');
    document.writeln('<PARAM NAME = "dmgFileName" VALUE = "'+dmgFileName+'">');
    document.write("<PARAM NAME='maxfiles' VALUE='"+maxfiles+"'>");
    document.write("<PARAM NAME='maxsize' VALUE='"+maxsize+"'>");
    document.write("<PARAM NAME='showpreview' VALUE='"+showpreview+"'>");
    document.write("<PARAM NAME='embedProgress' VALUE='"+embedProgress+"'>");
    document.write("<PARAM NAME='hideLocal' VALUE='"+hideLocal+"'>");
    document.write("<PARAM NAME='autoUpload' VALUE='"+autoUpload+"'>");
    document.write("<PARAM NAME='background' VALUE='"+background+"'>");
    document.write("<PARAM NAME='headerTextColor' VALUE='"+headerTextColor+"'>");
    document.write("<PARAM NAME='preservePathStructure' VALUE='"+preservePathStructure+"'>");
    document.write("<PARAM NAME='autoRedirect' VALUE='"+autoRedirect+"'>");

    //end of error message
    if (is_ie && !isMacJaguar()) {
        document.write("</OBJECT>");
    } else {
        document.write("</APPLET>");
    }
} else if (is_nav) {
    document.writeln("<EMBED name='FileCatalyst' TYPE = 'application/x-java-applet;version=1.4' PLUGINSPAGE = 'http://www.java.com' BORDER='0' java_CODE='unlimited.fc.client.FileCatalystCart.class' height='"+height+"' width='"+width+"' java_CODEBASE = . java_ARCHIVE='FileCatalystApplets.jar'");
    document.writeln("server='"+server+"'");
    document.writeln("port='"+port+"'");
    document.writeln("blocksize='"+blocksize+"'");
    document.writeln("unitsize='"+unitsize+"'");
    document.writeln("readbuffer='"+readbuffer+"'");
    document.writeln("bandwidth='"+bandwidth+"'");
    document.writeln("localdir='"+localdir+"'");
    document.writeln("remotedir='"+remotedir+"'");
    document.writeln("maxRetries='"+maxRetries+"'");
    document.writeln("waitRetry='"+waitRetry+"'");
    document.writeln("keepFileAttributes='"+keepFileAttributes+"'");
    document.writeln("numEncoders='"+numEncoders+"'");
    document.writeln("slowStart='"+slowStart+"'");
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
    document.writeln("user='"+user+"'");
    document.writeln("pass='"+pass+"'");
    document.writeln("postURL='"+postURL+"'");
    document.writeln("filesParam='"+filesParam+"'");
    document.writeln("delimiter='"+delimiter+"'");
    document.writeln("slowStartRate='"+slowStartRate+"'");
    document.writeln("autoresume='"+autoresume+"'");
    document.writeln("progressive='"+progressive+"'");
    document.writeln("mode='"+mode+"'");
    document.writeln("numFTPStreams='"+numFTPStreams+"'");
    document.writeln("useTempName='"+useTempName+"'");
    document.writeln("compression='"+compression+"'");
    document.writeln("compLevel='"+compLevel+"'");
    document.writeln("compStrategy='"+compStrategy+"'");
    document.writeln("autoZip='"+autoZip+"'");
    document.writeln("incremental='"+incremental+"'");
    document.writeln("incrementalMode='"+incrementalMode+"'");
    document.writeln("encrypt='"+encrypt+"'");
    document.writeln("ek='"+ek+"'");
    document.writeln("autoDMG='"+autoDMG+"'");
    document.writeln("dmgFileName='"+dmgFileName+"'");
    document.writeln("maxfiles='"+maxfiles+"'");
    document.writeln("maxsize='"+maxsize+"'");
    document.writeln("showpreview='"+showpreview+"'");
    document.writeln("embedProgress='"+embedProgress+"'");
    document.writeln("hideLocal='"+hideLocal+"'");
    document.writeln("autoUpload='"+autoUpload+"'");
    document.writeln("background='"+background+"'");
    document.writeln("headerTextColor='"+headerTextColor+"'");

    document.writeln("preservePathStructure='"+preservePathStructure+"'");
    document.writeln("autoRedirect='"+autoRedirect+"'");

    document.writeln(">");
}

</script>



</body>
</html>