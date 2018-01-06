<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['sequences-edit']) : ?>
    <a class="btn" href="<?=$lang?>/sequences/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['sequences-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.sequences, 'id[]')) change_action(document.sequences,'<?=$lang?>/sequences/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="sequences" action="<?=$lang?>/sequences/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.sequences)">
            </th>
            <th>Title</th>
            <th>Action</th>
        </tr>

        <?if($sequences):?>

        <?foreach($sequences as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['title']?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['sequences-edit'], 'url' => $lang.'/sequences/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['sequences-delete'], 'url' => $lang.'/sequences/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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