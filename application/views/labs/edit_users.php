<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="labs" action="<?=$lang?>/labs/edit_users/<?=$id?>" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.labs)">
            </th>
            <th>Username</th>
            <th>Email</th>
        </tr>

        <?if($users):?>

            <?foreach($users as $user):?>
                <tr>
                    <td>
                        <input type="hidden" name="ids[]" value="<?=$user['id']?>">
                        <input
                            type="checkbox"
                            name="selected_ids[]"
                            value="<?=$user['id']?>"
                            <? if(in_array($user['id'], $selected_user_ids)){ ?>checked="checked"<? } ?>
                            >
                    </td>
                    <td><?=$user['fname'].' '.$user['lname']?></td>
                    <td>
                        <?=$user['email']?>
                    </td>
                </tr>
            <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="3" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
   <input type="submit" class="btn" value="Save" name="save">
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>