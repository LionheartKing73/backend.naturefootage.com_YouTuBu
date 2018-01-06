<?php if($is_provider) {?>
    <iframe width="100%" height="1000" id="fsIframe"></iframe>
    <script>
        var isSafari = (/Safari/.test(navigator.userAgent));
        if(isSafari){
            window.open("http://<?php echo $provider_login . ':' . $provider_password . '@' . $aspera_connect_server; ?>", '_blank');
            var el = document.getElementById('fsIframe');
            el.parentNode.removeChild(el);
        }
        else{
            document.getElementById('fsIframe').src = "http://<?php echo $provider_login . ':' . $provider_password . '@' . $aspera_connect_server; ?>";
        }
    </script>

<?php } ?>