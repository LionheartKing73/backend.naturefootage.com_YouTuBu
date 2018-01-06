<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['submissions-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.submissions, 'id[]')) change_action(document.submissions,'<?=$lang?>/submissions/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="submissions" action="<?=$lang?>/submissions/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.submissions)">
            </th>
            <th>Code</th>
            <th>Date</th>
            <th>Action</th>
        </tr>

        <?if($submissions):?>

        <?foreach($submissions as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['code']?></td>
                <td><?=$item['date']?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['submissions-edit'], 'url' => $lang.'/submissions/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['submissions-delete'], 'url' => $lang.'/submissions/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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