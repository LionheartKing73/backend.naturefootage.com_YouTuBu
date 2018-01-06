<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['bins-edit']) : ?>
    <a class="btn" href="<?=$lang?>/bins/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['bins-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.bins, 'id[]')) change_action(document.bins,'<?=$lang?>/bins/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="bins" action="<?=$lang?>/bins/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.bins)">
            </th>
            <th>Title</th>
            <th>Action</th>
        </tr>

        <?if($bins):?>

        <?foreach($bins as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['title']?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['bins-edit'], 'url' => $lang.'/bins/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['bins-delete'], 'url' => $lang.'/bins/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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