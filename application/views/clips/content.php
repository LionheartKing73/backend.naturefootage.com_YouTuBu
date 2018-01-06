<div class="content_bg">
<div class="title_cont inner_title">
    <h1 class="page_title">
        <?=$clip['title'] ? $clip['title'] : ''?>
    </h1>
    <div class="back">
        <a href="<?=$continue?>"><?=$this->lang->line('back_to_search')?></a>
    </div>
    <div class="clear"></div>
</div>

<?if ($clip) {?>

<div id="left_part">

<?
  if($clip['res']){
?>
      <script type="text/javascript">
          editor.onSelectionChanged = function (isEnabled, selectedPart) {
              if (isEnabled && selectedPart) {
                  function roundValue(value){
                      var rounded = value * 100;
                      rounded = Math.round(rounded);
                      rounded = rounded/100;
                      return rounded;
                  }
                  document.getElementById("startTimeOfTheSelection_inner").value = roundValue(selectedPart.startOffset);
                  document.getElementById("endTimeOfTheSelection_inner").value = roundValue(selectedPart.stopOffset);
              }
          };
          editor.onLoaded = function () {
              editor.addExternalDropTarget(document.getElementById("cart_info"), function (dropTarget, timelineElement) {
                  addDropedElement(timelineElement);
                  /*window.alert(dropTarget);
                  window.alert(timelineElement.preview + " " +
                      timelineElement.id + " " +
                      timelineElement.filename + " " +
                      timelineElement.length_ + " " +
                      timelineElement.muted + " " +
                      timelineElement.startOffset + " " +
                      timelineElement.stopOffset
                  );*/
              });
              window.clipInfo = <?=json_encode($clip)?>;
              editor.putAsset('testId', "<?=$clip['res']?>", 0, <?=$clip['duration']?>, <?=$clip['duration']?>, false,
                  "<?=$clip['frames']['path']?>/", 0);
          };
      </script>

<?}else{?>
      <?=$this->lang->line('no_preview')?>.
<?}?>
</div>

<div id="right_part">
    <div class="scrollPane">
    <table class="clip_details">
        <tr>
            <th width="155"><?=$this->lang->line('clip_title')?>:</th>
            <td><?=$clip['title']?></td>
        </tr>
        <?if($clip['creator']){?>
        <tr>
            <th><?=$this->lang->line('clip_creator')?>:</th>
            <td><?=$clip['creator']?></td>
        </tr>
        <?}?>
        <?if($clip['rights']){?>
        <tr>
            <th><?=$this->lang->line('clip_rights')?>:</th>
            <td><?=$clip['rights']?></td>
        </tr>
        <?}?>
        <?if($clip['subject']){?>
        <tr>
            <th><?=$this->lang->line('clip_subject')?>:</th>
            <td><?=$clip['subject']?></td>
        </tr>
        <?}?>
        <?if($clip['description']){?>
        <tr>
            <th><?=$this->lang->line('clip_description')?>:</th>
            <td><?=$clip['description']?></td>
        </tr>
        <?}?>
        <?if($clip['duration']){?>
        <tr>
            <th><?=$this->lang->line('clip_duration')?>:</th>
            <td><?=$clip['duration']?></td>
        </tr>
        <?}?>
        <tr>
            <th><?=$this->lang->line('clip_date')?>:</th>
            <td><?=$clip['creation_date']?></td>
        </tr>
        <tr>
            <th><?=$this->lang->line('clip_width')?>:</th>
            <td><?=$clip['width']?></td>
        </tr>
        <tr>
            <th><?=$this->lang->line('clip_height')?>:</th>
            <td><?=$clip['height']?></td>
        </tr>
        <tr>
            <th><?=$this->lang->line('clip_aspect')?>:</th>
            <td><?=$clip['aspect']?></td>
        </tr>
        <tr>
            <th><?=$this->lang->line('clip_frame_rate')?>:</th>
            <td><?=$clip['frame_rate']?></td>
        </tr>
        <tr>
            <th><?=$this->lang->line('clip_bit_rate')?>:</th>
            <td><?=($clip['bit_rate']/1000)?> kbit/s</td>
        </tr>
        <tr>
            <th><?=$this->lang->line('clip_codec')?>:</th>
            <td><?=$clip['codec']?></td>
        </tr>
        <tr>
            <th><?=$this->lang->line('clip_format')?>:</th>
            <td><?=$clip['format']?></td>
        </tr>
        <tr>
            <th><?=$this->lang->line('clip_category')?>:</th>
            <td><?=$clip['category']?></td>
        </tr>
        <?if($clip['keywords']){?>
        <tr>
            <th><?=$this->lang->line('clip_keywords')?>:</th>
            <td><?=$clip['keywords']?></td>
        </tr>
        <?}?>
        <?if($clip['author']){?>
        <tr>
            <th><?=$this->lang->line('clip_author')?>:</th>
            <td><?=$clip['author']?></td>
        </tr>
        <?}?>
        <?if($clip['director']){?>
        <tr>
            <th><?=$this->lang->line('clip_director')?>:</th>
            <td><?=$clip['director']?></td>
        </tr>
        <?}?>
        <?if($clip['actors']){?>
        <tr>
            <th><?=$this->lang->line('clip_actors')?>:</th>
            <td><?=$clip['actors']?></td>
        </tr>
        <?}?>
        <?if($clip['series_episode']){?>
        <tr>
            <th><?=$this->lang->line('clip_series')?>:</th>
            <td><?=$clip['series_episode']?></td>
        </tr>
        <?}?>
        <?if($clip['complex']){?>
        <tr>
            <th><?=$this->lang->line('clip_complex')?>:</th>
            <td><?=$clip['complex']?></td>
        </tr>
        <?}?>
        <?if($clip['musical_instruments']){?>
        <tr>
            <th><?=$this->lang->line('clip_musical_instruments')?>:</th>
            <td><?=$clip['musical_instruments']?></td>
        </tr>
        <?}?>
    </table>
    </div>

   <div>
    <table class="clip_details">
        <tr>
            <th width="155">
                <?if ($clip['in_bin']) { ?>
                &nbsp;
                <? } else { ?>
                <a href="<?=$lang?>/bin/add/2/<?=$clip['id']?>" class="to_bin_btn toBin"><?=$this->lang->line('add_to_bin')?></a>
                <? }?>
            </th>
            <td>
                <a href="<?=$lang?>/cart/add/2/<?=$clip['id']?>" class="to_basket_btn toCart">
                    <span><?=$this->lang->line('add_to_basket')?></span>
                </a>
                <input type="hidden" name="item-<?=$clip['id']?>" value="<?=htmlspecialchars('{"id":"'.$clip['id'].'","price":"'.$clip['price'].'","title":"'.$clip['title'].'"}')?>">
                <input type="hidden" name="startTime-<?=$clip['id']?>" id="startTimeOfTheSelection_inner">
                <input type="hidden" name="endTime-<?=$clip['id']?>" id="endTimeOfTheSelection_inner">
            </td>
        </tr>
    </table>
   </div>

    <div id="addToCartForm" title="Add to cart" class="popup">
        <p class="message" style="display: none;"><?=$this->lang->line('required_fields_err')?>.</p>
        <form class="to_cart_form">
        </form>
    </div>

</div>

<div class="clear"></div>

<div id="timeline"></div>

<?}?>

<div class="clear"></div>
</div>
