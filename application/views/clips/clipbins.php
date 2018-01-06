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
    <?if($id && $this->permissions['clips-galleries']){?>
    <a href="<?=$lang?>/clips/galleries<?='/'.$id?>" class="btn">
        Galleries
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-bins']){?>
    <a href="<?=$lang?>/clips/bins<?='/'.$id?>" class="btn">
        Bins
    </a>
    <? } ?>-->
    <?if($id && $this->permissions['clips-resources']){?>
    <a href="<?=$lang?>/clips/resources<?='/'.$id?>" class="btn">
        Resources
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-attachments']){?>
    <a href="<?=$lang?>/clips/attachments<?='/'.$id?>" class="btn">
        Attachments
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

<form action="<?=$lang?>/clips/clipbins/<?=$id?>" method="post" class="form well">
    <fieldset>
        <legend>
            EDIT CLIP CLIPBINS
        </legend>

        <?if($clipbins) {?>

        <table class="clipbins">

            <?
            foreach($clipbins as $val) { $i++;
                ?>

                <tr>
                    <td>
                        <input type="checkbox" <?if($val['checked']) echo "checked"?> name="id[]"
                               value="<?=$val['id']?>" id="clipbin<?=$val['id']?>">
                    </td>
                    <td>
                        <label for="clipbin<?=$val['id']?>"><?=$val['title']?></label>
                    </td>
                </tr>

                <?}?>
        </table>

        <?}?>
        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
        </div>
    </fieldset>
</form>
