<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['deliverypricefactors-edit']) : ?>
        <a class="btn" href="<?=$lang?>/deliverypricefactors/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['deliverypricefactors-delete']) : ?>
        <a class="btn" href="javascript: if (check_selected(document.deliverypricefactors, 'id[]')) change_action(document.deliverypricefactors,'<?=$lang?>/deliverypricefactors/delete')">
            <?=$this->lang->line('delete')?>
        </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="deliverypricefactors" action="<?=$lang?>/deliverypricefactors/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.deliverypricefactors)">
            </th>
            <th>Format</th>
            <th>Factor</th>
            <th>Action</th>
        </tr>

        <?if($delivery_price_factors):?>

        <?foreach($delivery_price_factors as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['format']?></td>
                <td><?=$item['factor']?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['deliverypricefactors-edit'], 'url' => $lang.'/deliverypricefactors/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['deliverypricefactors-delete'], 'url' => $lang.'/deliverypricefactors/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
        <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="4" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>