<script type="text/javascript">
    $( document ).ready(function() {
        $(".still").on( "click", function() {
            $('.still').attr('style', '');
            $.ajax({
                type: "POST",
                url: "/en/cliplog/index/changethumb",
                data: {cid: <?php echo $cid ?>, path: $(this).attr("src")}
            }).done(function() {

            });
            $('.still').attr('style', 'padding: 25px');
            $(this).attr('style', 'border:5px red solid; padding: 25px');
        });

    });
</script>

<?php if ($clips){ ?>


            <?php

            $addr = parse_url($path);

            for ($i = (($page - 1)*$per_page); $i < ($page)*$per_page; $i++){


                $clip_addr = parse_url($clips[$i]);

                if($clip_addr['host']){
                    $thumb_addr = ltrim($addr['path'], "/");
                }else{
                    $thumb_addr = $clips[$i];
                }


                if(end(explode('.', $clips[$i])) == "jpg"){


                    if ($thumb_addr == ltrim($addr['path'], "/")){

                        echo '<img src="http://s3.footagesearch.com/'. $thumb_addr.'" id="active" style="border:5px red solid; padding: 25px" class="still">';
                    }else{
                        echo '<img src="http://s3.footagesearch.com/'. $thumb_addr.'" style="padding: 25px" class="still">';
                    }

                }
            }



            ?>


<?php }else{ ?>
    Thumbnails not found
<?php } ?>