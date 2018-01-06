<%@page contentType="text/html"%>
<%@page pageEncoding="UTF-8"%>
<%@ page language="java" import= "java.io.*,java.lang.*,java.util.*,unlimited.fc.client.api.*,java.net.*"%>



<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="styles.css" />
        <title>FileCatalyst Download Applet - Display List</title>
        
        <script>
	function buildFileList(){
	
	var fileList="";
	var formFiles = document.forms[0];
	//add the contact to recipients, make sure that you add a comma if there is already an entry in the field, also don't add suplicate entries
		for(i=0; i<formFiles.elements.length; i++){
			if(formFiles.elements[i].type=="checkbox"){
				if (formFiles.elements[i].checked){
					fileList+=formFiles.elements[i].value+";";
					}
				}
			}
	//alert(fileList);	
	formFiles.fileList.value = fileList;
	
	}
	
</script>
    </head>
    <body>
<center>
<img src="images/fileCatalystLogo.jpg" width="301" height="155">
<h1>
FileCatalyst Download Applet demo page.
</h1>
    <h3>List of files on FileCatalyst Server for user <%=request.getParameter("username")%></h3>
    <form action="showApplet.jsp" method="POST" onSubmit="buildFileList()">
<%

String server = request.getParameter("server");
String user = request.getParameter("username");
String passwd = request.getParameter("password");
int port = Integer.parseInt(request.getParameter("port"));

FCClient fc = new FCClient(server,port);
fc.initialize();
fc.connect();
fc.login(user, passwd);

FileListData fld = fc.list() ;

out.write("<table>");
for(int k = 0; k<fld.size(); k++){
 FileListDataItem data = fld.getItem(k);
	if(data.isDirectory()){
		out.write("<tr><td align='left'><input class='defaultText' type=\"checkbox\" name='chkbx1' value='"+data.getName()+"/' >/"+data.getName()+"</input></td></tr>");
    }
	else{
		out.write("<tr><td align='left'><input class='defaultText' type=\"checkbox\" name='chkbx1' value='"+data.getName()+"' >"+data.getName()+"</input></td></tr>");
	}

}

fc.disconnect();

%>
<tr><td><input type="hidden" name="fileList"/>
<input type="hidden" name="server" value="<%=server%>"/>
<input type="hidden" name="port" value="<%=port%>"/>
<input type="hidden" name="username" value="<%=user%>"/>
<input type="hidden" name="password" value="<%=passwd%>"/></td></tr>
<tr><td><input type="submit" value="Submit"/></td></tr>
</table>
</form>
</center> 
</body>
</html>
