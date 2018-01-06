<form action="<?=$lang?>/news/edit<?='/'.$id.(($visual)?'/visual':'')?>" method="post" enctype="multipart/form-data" class="form-horizontal well">

    <fieldset>
        <legend>
            <?=$this->lang->line('news_edit')?> (<?=$this->lang->line('required_fields')?> <span class="mand">*</span>):
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
            <label class="control-label" for="annotation">
                <?=$this->lang->line('annotation');?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <textarea name="annotation" id="annotation" cols="80" rows="4"><?=$annotation?></textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="body">
                <?=$this->lang->line('content')?>: <span class="mand">*</span>
            </label>
            <div class="controls">
                <textarea name="body" id="body"><?=$body?></textarea>
                <?=fck(750)?>
            </div>
        </div>

        <?if(!$visual){?>
        <div class="control-group">
            <label class="control-label" for="meta_title">
                <?=$this->lang->line('meta_title')?>:
            </label>
            <div class="controls">
                <input type="text" name="meta_title" id="meta_title" maxlength="255" size="80" value="<?=$meta_title?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="meta_desc">
                <?=$this->lang->line('meta_desc')?>:
            </label>
            <div class="controls">
                <textarea name="meta_desc" id="meta_desc" cols="80" rows="4"><?=$meta_desc?></textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="meta_keys">
                <?=$this->lang->line('meta_keys')?>:
            </label>
            <div class="controls">
                <textarea name="meta_keys" id="meta_keys" cols="80" rows="4"><?=$meta_keys?></textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="mimg">
                <?=$this->lang->line('picture')?>:
            </label>
            <div class="controls">
                <input type="file" name="mimg" id="mimg" class="input-file">

                <?if($picture){?>

                <table border="0" cellspacing="0" cellpadding="2">
                    <tr>
                        <td align="center">
                            <img src="<?=$picture?>?date=<?=$mtime?>" border="0" style="border:solid 1px #efefef; width: 200px">
                        </td>
                    </tr>
                    <?if (!$item_code) {?>
                    <tr>
                        <td align="center">
                            <input type="hidden" name="sid" value="<?=$id?>">
                            <input type="submit" value="<?=$this->lang->line('delete')?>" class="btn btn-danger" name="delete">
                        </td>
                    </tr>
                    <?}?>
                </table>

                <?}?>

            </div>
        </div>
        <?}?>

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
        </div>
    </fieldset>

</form>
