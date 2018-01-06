<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
    <?if($id && $this->permissions['clips-edit']){?>
    <a href="<?=$lang?>/clips/edit<?='/'.$id?>" class="btn">
        <?=$this->lang->line('edit')?>
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-cats']){?>
    <a href="<?=$lang?>/clips/cats<?='/'.$id?>" class="btn">
        Categories
    </a>
    <? } ?>
    <!--
    <?if($id && $this->permissions['clips-sequences']){?>
    <a href="<?=$lang?>/clips/sequences<?='/'.$id?>" class="btn">
        Sequences
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-bins']){?>
    <a href="<?=$lang?>/clips/bins<?='/'.$id?>" class="btn">
        Bins
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-galleries']){?>
    <a href="<?=$lang?>/clips/galleries<?='/'.$id?>" class="btn">
        Galleries
    </a>
    <? } ?>-->
    <?if($id && $this->permissions['clips-resources']){?>
    <a href="<?=$lang?>/clips/resources<?='/'.$id?>" class="btn">
        Resources
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-statistics']){?>
    <a href="<?=$lang?>/clips/statistics<?='/'.$id?>" class="btn">
        Access statistics
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-derived']){?>
    <a href="<?=$lang?>/clips/derived<?='/'.$id?>" class="btn">
        Derived
    </a>
    <? } ?>
</div>

<br class="clr">

<form action="<?=$lang?>/clips/attachments<?='/'.$id?>" method="post" enctype="multipart/form-data" class="form well">
    <fieldset>
        <legend>
            Attachments
            <? if ($code) { ?>
            <p>Clip code: <?=$code?></p>
            <? } ?>
        </legend>

        <table class="table table-striped" style="width: auto;">

            <? if ($attachments) { ?>
                <tr>
                    <th>File type</th>
                    <th>&nbsp;</th>
                    <th>Actions</th>
                </tr>

                <? foreach ($attachments as $attachment) { ?>

                <tr>
                    <td><?=$attachment['filetype']?></td>
                    <td>
                        <? if($attachment['is_image']) { ?>
                            <img src="<?=$attachment['filepath']?>" width="100">
                        <?}
                        else {?>
                            <a href="<?=$attachment['filepath']?>"><?=$attachment['file']?></a>
                        <?}?>
                    </td>
                    <td>
                        <input type="submit" name="delete[<?=$attachment['id']?>]" value="Delete" class="btn btn-danger"
                               onclick="return confirm('The item will be deleted.');">
                    </td>
                </tr>
                <? }
            } else { ?>
                <tr><td class="empty-list"><?= $this->lang->line('empty_list') ?></td></tr>
            <? } ?>
        </table>

        <div class="control-group">
            <label class="control-label" for="attachment">
                Attachment:
            </label>
            <div class="controls">
                <input type="file" name="attachment" id="attachment" class="input-file">
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="Upload" name="upload">
        </div>

    </fieldset>
</form>