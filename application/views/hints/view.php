<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['hints-edit']) : ?>
        <a class="btn" href="<?=$lang?>/hints/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['hints-delete']) : ?>
        <a class="btn" href="javascript: if (check_selected(document.hints, 'id[]')) change_action(document.hints,'<?=$lang?>/hints/delete')">
            <?=$this->lang->line('delete')?>
        </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="hints" action="<?=$lang?>/hints/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.hints)">
            </th>
            <th>Field</th>
            <th>Action</th>
        </tr>

        <?if($hints):?>

        <?foreach($hints as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$fields[$item['field']]?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['hints-edit'], 'url' => $lang.'/hints/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['hints-delete'], 'url' => $lang.'/hints/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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