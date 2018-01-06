<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['presets-edit']) : ?>
        <a class="btn" href="<?=$lang?>/presets/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['presets-delete']) : ?>
        <a class="btn" href="javascript: if (check_selected(document.presets, 'id[]')) change_action(document.presets,'<?=$lang?>/presets/delete')">
            <?=$this->lang->line('delete')?>
        </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="presets" action="<?=$lang?>/presets/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.presets)">
            </th>
            <th>Name</th>
            <th>Action</th>
        </tr>

        <?if($presets):?>

            <?foreach($presets as $item):?>
                <tr>
                    <td>
                        <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                    </td>
                    <td><?=$item['name']?></td>
                    <td>
                        <?
                        get_actions(array(
                            array('display' => $this->permissions['presets-edit'], 'url' => $lang.'/presets/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                            array('display' => $this->permissions['presets-delete'], 'url' => $lang.'/presets/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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