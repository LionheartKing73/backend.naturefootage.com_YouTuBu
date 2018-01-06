<?
if (count($res_to_create)) {
  $coma = '';
?>
<script type="text/javascript">
  var resToCreate = new Array(
  <? foreach ($res_to_create as $res) {?>
    <?=$coma?>{ id: <?=$res['id']?>, res_type: "<?=$res['res_type']?>" }
<?
       $coma = ', ';
     }
?>
  );
  var imgCount = <?=$img_count?>;
  var thumbCount = <?=$thumb_count?>;
  var previewCount = <?=$preview_count?>;
  
  var idx = 0;
  var resType = "";
  var d = new Date();

  
  function processResponce(msg) {
    if (msg == "OK") {
      switch (resType) {
        case "img":
          ++imgCount;
          $("#img").html(imgCount);
          break;
        case "thumb":
          ++thumbCount;
          $("#thumb").html(thumbCount);
          break;
        case "preview":
          ++previewCount;
          $("#preview").html(previewCount);
          break;
      }
    }
    
    ++idx;
    if (idx >= resToCreate.length) {
      location = window.location.href;
      return;
    }
    
    window.setTimeout("createResource()", 3000);
  }
  
  function createResource() {
    if (idx >= resToCreate.length) {
      location = window.location.href;
      return;
    }
    
    var id = resToCreate[idx].id;
    resType = resToCreate[idx].res_type;
    
    $.ajax({
      type: "GET",
      url: "/<?=$lang?>/clips/createfile/" + id + "/" + resType + "/" + d.getTime(),
      success: function(msg) { processResponce(msg); }
    });
  }
  
  function startCreating() {
    $("#loader").show();
    $("#buttonCreate").attr("disabled", "disabled");
    createResource();    
  }
  
  $(document).ready(function() {
    $("#buttonCreate").click(startCreating);
  });
</script>
<?}?>

<b>Total clips:</b> <?=$clips_count?>

<h3>Registered files</h3>
<table cellspacing="1" border="0" class="listview">

  <tr>
    <th>Resource type</th>
    <th>Total registered</th>
  </tr>
  
  <tr>
    <td>Delivery files</td>
    <td align="right"><?=$hd_count?></td>
  </tr>

  <tr>
    <td>Image thumbnails</td>
    <td align="right"><span id="img"><?=$img_count?></span></td>
  </tr>

  <tr>
    <td>Motion thumbnails</td>
    <td align="right"><span id="thumb"><?=$thumb_count?></span></td>
  </tr>

  <tr>
    <td>Video previews</td>
    <td align="right"><span id="preview"><?=$preview_count?></span></td>
  </tr>

</table>

<? if (count($res_to_create)) { ?>
<br>
<input id="buttonCreate" type="button" value="Create missed files" class="sub">
<img id="loader" src="data/img/loading.gif" alt="working..." align="top" hspace="5" style="display: none">
<? } ?>