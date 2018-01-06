<!--p>To access it please add to your local host file these settings for transcoder:</p>

<h2 style="color:#cd2228">63.249.66.182 transcoder.com www.transcoder.com</h2>

<p>Then you will be able to see:</p>
<h3><a style="color:#0092FE" href="http://transcoder.com/">http://transcoder.com/</a></h3>
<h2 style="color:#cd2228">and login into admin panel: admin/admin</h2-->
<?php header('Access-Control-Allow-Origin: *'); ?>

<div id="iframeTranscoding" style=" width: 100%; height: 100%; "></div>

<script type="text/javascript">
    var transcodingSite='http://transcoding.footagesearch.com/';
    var login='admin';
    var pass='admin';

    function authTranscoding(){
        $.ajax({
            type: "POST",
            async:true,
            cache: false,
            xhrFields: {
                withCredentials: true
            },
            url: transcodingSite+"?c=login",
            crossDomain:true,
            data: {username:login,password:pass,login:"Submit"},
            success:function(data) {}
        });
    }

    function iframe(){
        $('#iframeTranscoding' ).html('<iframe src="'+transcodingSite+'" width="100%" height="' + window.innerHeight + '" align="center" style="border:0;">Ваш браузер не поддерживает плавающие фреймы! </iframe>');
    }

    $(document).ready(function() {
        authTranscoding();
        iframe();
    });
</script>

