<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
    <?if($id && $this->permissions['browsepages-edit']){?>
    <a href="<?=$lang?>/browsepages/edit<?='/'.$id?>" class="btn">
        Edit page
    </a>
    <? } ?>
</div>

<br class="clr">

<form action="<?=$lang?>/browsepages/lists/<?=$id?>" method="post" class="form well">
    <fieldset>
        <legend>
            EDIT LISTS
        </legend>

        <?if($lists) {?>

        <table class="lists">

            <?
            foreach($lists as $val) { $i++;
                ?>

                <tr>
                    <td>
                        <input type="checkbox" <?if($val['checked']) echo "checked"?> name="id[]"
                               value="<?=$val['id']?>" id="list<?=$val['id']?>">
                    </td>
                    <td>
                        <label for="list<?=$val['id']?>"><?=$val['title']?></label>
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
