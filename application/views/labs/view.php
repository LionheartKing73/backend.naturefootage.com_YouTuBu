<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['labs-edit']) : ?>
    <a class="btn" href="<?=$lang?>/labs/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['labs-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.labs, 'id[]')) change_action(document.labs,'<?=$lang?>/labs/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
<div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="labs" action="<?=$lang?>/labs/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.labs)">
            </th>
            <th>Lab</th>
            <th>Action</th>
        </tr>

        <?if($labs):?>

        <?foreach($labs as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['name']?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['labs-edit'], 'url' => $lang.'/labs/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['labs-users'], 'url' => $lang.'/labs/edit_users/'.$item['id'], 'name' => $this->lang->line('edit_users')),
                        array('display' => $this->permissions['labs-delete'], 'url' => $lang.'/labs/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
            <?endforeach?>
        </td></tr>
        <?else:?>
        <tr><td colspan="3" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
<div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>