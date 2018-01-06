<?php
$addr = parse_url($active_thumbnail);
?>
    <script type="text/javascript">

        function changeAutomove() {
            if ($('#changeAutomove').is(":checked")) {
                var status = 1;
            } else {

                var status = '';
            }
            $.ajax({
                type: "POST",
                url: "/en/cliplog/index/getSessionAutoNextPageThumbgallery",
                data: {status: status}
            }).done(function () {
               // location.reload();
            });
        }

        $(document).ready(function () {
            $(".still").on("click", function () {
                $('.still').attr('style', '');
                $.ajax({
                    type: "POST",
                    url: "/en/cliplog/index/changethumb",
                    data: {cid: <?php echo $cid ?>, path: $(this).attr("src")}
                }).done(function () {
                    if ($('#changeAutomove').is(":checked")) {
                        var hrefLink = $('#nextClipid').attr('href')
                        if (typeof hrefLink !== 'undefined') {
                            location.href = hrefLink;
                        }
                    }
                });
                $('.still').attr('style', 'padding: 25px');
                $(this).attr('style', 'border:5px red solid; padding: 25px ');
            });
        });
    </script>
    <h1>
        Select Still for: <?php echo $clip_code; ?>
    </h1>
    <br>
<?php
if (isset($_SESSION['backPageThumb'])) {
    $returnUrl = "http://" . $_SERVER['SERVER_NAME'] . $_SESSION['backPageThumb'];
} else {
    $returnUrl = 'en/cliplog/view.html';
}
?>
    <b><a href="<?php echo $returnUrl ?>">Return to ClipLog Search Results</a></b><br>

<?php if ($came_from != '/en/cliplog/view.html') { ?>
    <b><a href="en/cliplog/edit/<?php echo $cid; ?>">Return to Clip Editing </a></b>
<? } ?>
    <br>
    <br>
<?php if ($previos_clip[0]['code']) { ?>
    <a href="/en/cliplog/index/thumbgallery/<?php echo $previos_clip[0]['id'] ?>">< Previous Clip
        (<?php echo $previos_clip[0]['code'] ?>)</a>
<?php } ?>
    ||
<?php if ($nextclip[0]['code']) { ?>
    <a href="/en/cliplog/index/thumbgallery/<?php echo $nextclip[0]['id'] ?>" id="nextClipid">Next Clip
        (<?php echo $nextclip[0]['code'] ?>) ></a>
<?php } ?>
    <br><br>

    <input type="checkbox" id="changeAutomove" onclick="changeAutomove()"
        <?php echo isset($_SESSION['autoMoveCheck']) ? 'checked="checked"' : '' ?> /> Proceed to Next Clip After Still Selection

<?php if ($clips) { ?>

    <div id="thumbs_list">


        <?php
        //var_dump($clips);

        for ($i = 0; $i <= count($clips); $i++) {

            $clip_addr = parse_url($clips[$i]);

            if ($clip_addr['host']) {
                $thumb_addr = ltrim($addr['path'], "/");
            } else {
                $thumb_addr = $clips[$i];
            }


            if (end(explode('.', $clips[$i])) == "jpg") {


                if ($thumb_addr == ltrim($addr['path'], "/")) {

                    echo '<img src="//video.naturefootage.com/' . $thumb_addr . '" id="active" style="border:5px red solid; padding: 25px" class="still">';
                } else {
                    echo '<img src="//video.naturefootage.com/' . $thumb_addr . '" style="padding: 25px" class="still">';
                }

            }
        }


        ?>

    </div>


<?php } else { ?>
    Thumbnails not found
<?php } ?>