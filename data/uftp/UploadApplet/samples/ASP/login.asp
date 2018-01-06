<%
Response.Expires = -1000 'Makes the browser not cache this page
Response.Buffer = True 'Buffers the content so our Response.Redirect will work

%>
<b>NOTE: You must move the asp files into the same directory as the JAR file for the applet.</b><br>
<br>
<b>You also must edit upload.asp and fill in the server and port information</b><br>
<form name=form1 action=upload.asp method=post>
User Name : <input type=text name=username><br>
Password : <input type=password name=userpwd><br>
<input type=submit value="Login">
</form>
