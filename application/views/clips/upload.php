<?php if($is_provider) {?>

<style type="text/css">
.download{
    background: #353d4a none repeat scroll 0 0;
    border-radius: 5px;
    color: #fff;
    font-size: 15px;
    margin-right: 10px;
	padding:8px;
    text-decoration: none;
}
.download:hover { background:#008BA7; color:#FFF;}
.embed-container {
position: relative;
padding-bottom: 56.25%; /* 16/9 ratio */
padding-top: 30px; /* IE6 workaround*/
height: 0;
overflow: hidden;
}
.embed-container iframe,
.embed-container object,
.embed-container embed {
position: absolute;
top: 0;
left: 0;
width: 100%;
height: 100%;
}
.admin-title{ padding:10px 15px 10px 25px !important;}
</style>
<div style="padding: 15px; font-size: 14px;">
<h4>Video submissions to NatureFootage are handled via <a target="_blank" href="http://catapultsoft.com/">Catapult</a>, a file transfer application.</h4>
<br/>
<p style="font-size: 14px;"><a target="_blank" href="http://catapultsoft.com/Download" class="download">Download Catapult</a></p>
<br/>
<p style="font-size: 14px;">Upon installation of Catapult, simply click the + icon in the top right corner and enter these credentials:</p>
<ul style="margin-top:20px; margin-bottom:20px;">
<li><b>Server:</b> upload.naturefootage.com</li>
<li><b>Connection:</b> UDP</li>
<li><b>*</b> The Username and Password are the same as your website login credentials for NatureFootage.com. If you forgot your username or password, <a target="_blank" href="http://www.naturefootage.com/login?action=lostpassword">click here</a>.</li>
</ul>
<h3 style="font-size:1.1rem; margin-bottom:10px;">Catapult Help Video</h3>
<div class="embed-container"><iframe width="100%" height="500" frameborder="0" allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" webkitallowfullscreen="webkitallowfullscreen" src="https://player.vimeo.com/video/156039566?title=0&amp;byline=0&amp;portrait=0"></iframe></div>
</div>

<?php } ?>