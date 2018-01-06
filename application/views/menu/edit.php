<form action="<?=$lang?>/menu/edit/<?=$id?>" method="post"
  enctype="multipart/form-data" class="form-horizontal well">

  <fieldset>
    <legend>
      <?=$this->lang->line('menu_edit')?> (<?=$this->lang->line('required_fields')?> <span class="mand">*</span>):
    </legend>

    <div class="control-group">
      <label class="control-label" for="title">
        <?=$this->lang->line('title')?>: <span class="mand">*</span>
      </label>
      <div class="controls">
        <input type="text" name="title" id="title" maxlength="255" size="70" value="<?=$title?>">
        <input type="hidden" name="id" value="<?=$id?>">
      </div>
    </div>

      <div class="control-group">
          <label class="control-label" for="cats">
              <?=$this->lang->line('resources');?>
          </label>
          <div class="controls">
              <select id="cats" onchange="update_selects();set_link();">
              </select>
          </div>
      </div>

      <div class="control-group">
          <label class="control-label" for="subcats">
              <?=$this->lang->line('urls');?>
          </label>
          <div class="controls">
              <select id="subcats" onchange="set_link();">
              </select>
          </div>
      </div>

    <div class="control-group">
      <label class="control-label" for="urllink">
        <?=$this->lang->line('link')?>: <span class="mand">*</span>
      </label>
      <div class="controls">
        <input type="text" name="link" maxlength="255" size="50" value="<?=$link?>" id="urllink">
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="ord">
        <?=$this->lang->line('order')?>:
      </label>
      <div class="controls">
        <input type="text" name="ord" id="ord" maxlength="255" size="50" value="<?=$ord?>">
      </div>
    </div>
    
    <?if($parents){?>
    <div class="control-group">
      <label class="control-label" for="parent_id">
        <?=$this->lang->line('parent')?>:
      </label>
      <div class="controls">
        <select name="parent_id" id="parent_id">
          <option value="0">
      <?foreach($parents as $parent){?>
          <option value="<?=$parent['id']?>"<?if($parent['id']==$parent_id) echo ' selected'?>> <?=$parent['title']?><br>
      <?}?>
        </select>
      </div>
    </div>
    <?}?>
    
    <div class="control-group">
      <label class="control-label" for="target">
        <?=$this->lang->line('target')?>:
      </label>
      <div class="controls">
        <select name="target" id="target">
          <option value="0" <?if($target==0) echo 'selected'?>>_self
          <option value="1" <?if($target==1) echo 'selected'?>>_blank
        </select>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="mimg">
        <?=$this->lang->line('picture')?>:
      </label>
      <div class="controls">
        <input type="file" name="mimg" id="mimg" size="32">
      </div>
    </div>

    <div class="form-actions">
      <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
    </div>


<?if($picture){?>

<table>
  <tr><td align="center"><img src="<?=$picture?>" border="0" style="border:solid 1px #efefef"></td></tr>
  <tr><td align="center">
    <input type="hidden" name="sid" value="<?=$sid?>">
    <input type="submit" value="<?=$this->lang->line('delete')?>" class="sub" name="delete">
  </td></tr>
</table>

<?}?>

  </fieldset>
</form>

<script>
    var subcats = new Array();
    var cats = new Array(<?=$cats?>);

    <?foreach($subs as $k=>$v):?>
    subcats[<?=$k?>] = new Array(<?=$v?>);
        <?endforeach;?>

    update_selects(0, 0);
</script>