<?if($visual_mode) {?>

<div id="production0" class="boxout" onmouseover="show_bar(0, 'production', '<?=$lang?>')"
  onmouseout="hide_bar(0, 'production', '<?=$lang?>')">
<?}?> 

<table cellspacing="0" cellpadding="0" border="0" style="width: 100%">
    <tr>
        <td width="530">
              <div id="player1">
                <a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this content.
              </div>
              <script type="text/javascript">
                var so = new SWFObject("data/swf/player.swf", "single", "512", "310", "9");
                so.addParam("bgcolor","#1a1a1a");
                so.addParam("allowfullscreen", "true");
                so.addParam("wmode", "opaque");
                so.addVariable("file",
                  "<?=$this->config->item('production_path') . $production['filename']?>");
                so.addVariable("autostart", "true");
                so.addVariable("repeat","always");
                so.write("player1");
              </script>
        </td>
        <td rowspan="2">
            <?=$production['content']?>    
        </td>
    </tr>
    <tr>
        <td><?=$production['bottom_content']?></td>
    </tr>
</table>      
<?if($visual_mode) {?>
</div>
<?}?>
<br class="clear">