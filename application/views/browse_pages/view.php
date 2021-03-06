<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <? if ($this->permissions['browsepages-edit']) { ?>
        <a class="btn" href="<?=$lang?>/browsepages/edit"><?=$this->lang->line('add')?></a>
    <? } ?>
    <? if ($this->permissions['browsepages-ord']) { ?>
        <a class="btn" href="javascript: change_action(document.browsepages,'<?=$lang?>/browsepages/ord')">
            <?=$this->lang->line('save_ord')?></a>
    <? } ?>
    <? if ($this->permissions['browsepages-visible']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.browsepages, 'id[]')) change_action(document.browsepages,'<?= $lang ?>/browsepages/visible');">
            <?= $this->lang->line('visible'); ?>
        </a>
    <? } ?>
    <? if ($this->permissions['browsepages-delete']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.browsepages, 'id[]')) change_action(document.browsepages,'<?=$lang?>/browsepages/delete')">
            <?=$this->lang->line('delete')?>
        </a>
    <? } ?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="browsepages" action="<?=$lang?>/browsepages/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.browsepages)">
            </th>
            <th>Title</th>
            <th>URL</th>
            <th>Sort</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?if($browse_pages):?>

        <?foreach($browse_pages as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['title']?></td>
                <td><?=$item['url']?></td>
                <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['sort']?>"></td>
                <td><? if ($item['status'] == 1) echo 'Active'; else echo 'Inactive'; ?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['browsepages-edit'], 'url' => $lang.'/browsepages/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['browsepages-lists'], 'url' => $lang.'/browsepages/lists/'.$item['id'], 'name' => 'Lists'),
                        array('display' => $this->permissions['browsepages-delete'], 'url' => $lang.'/browsepages/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
        <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="6" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>