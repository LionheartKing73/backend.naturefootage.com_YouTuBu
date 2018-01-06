<?if($visual_mode) {?>
<div id="showreel">
    <div id="banner0" class="boxout" onmouseover="show_bar(0, 'banner', '')" onmouseout="hide_bar(0, 'banner', '')">
        <img src="/data/img/banner.gif" alt="Banner" width="1008" height="378">
    </div>
</div>
<?}elseif ($this->config->item('use_qt')){?>
<div id="showreel">
    <?
    if (!empty($banners)) {
        ?>
        <embed type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/"
               src="<?=$this->config->item('banner_path').$banners[0]['resource']?>" width="1008" height="378"
               bgcolor="black" autoplay="true" controller="false" loop="false"
        <?
        if (count($banners) > 1) {
            for ($i = 1; $i < count($banners); ++$i) {
                ?>
               qtnext<?=$i?>="<<?=$banner_path.$banners[$i]['resource']?>> T<myself>"
                <?
            }
            ?>
               qtnext<?=$i?>="GOTO0"
            <?
        }
    }
    ?>
    wmode="transparent">
</div>
<?}else{?>
<div id="showreel">
    <a href="http://www.macromedia.com/go/getflashplayer" class="highlight" target="_blank">
        Get the Flash Player
    </a>
    to see this content.
</div>
<script type="text/javascript">
    var so = new SWFObject("/data/swf/player.swf", "flvplayer", "1008", "378", "10");
    so.addParam("allowfullscreen","false");
    so.addParam("bgcolor","#000000");
    so.addParam("wmode","opaque");
    so.addVariable("controlbar", "none");
    so.addVariable("file", "/playlist.html");
    so.addVariable("type", "application/xml");
    so.addVariable("autostart", "true");
    so.addVariable("shuffle","false");
    so.addVariable("repeat","always");
    so.addVariable("mute", "true");
    so.write("showreel");
</script>
<?}?>