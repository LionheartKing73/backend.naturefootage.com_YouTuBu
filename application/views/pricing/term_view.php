<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['pricingterm-edit']) : ?>
    <a class="btn" href="<?=$lang?>/pricingterm/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <? if ($this->permissions['pricingterm-ord']) : ?>
    <a class="btn" href="javascript: change_action(document.pricingterm,'<?=$lang?>/pricingterm/ord')">
        <?=$this->lang->line('save_ord')?></a>
    <?endif?>
    <?if ($this->permissions['pricingterm-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.pricingterm, 'id[]')) change_action(document.pricingterm,'<?=$lang?>/pricingterm/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="pricingterm" action="<?=$lang?>/pricingterm/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.pricingterm)">
            </th>
            <th>Term category</th>
            <th>Territory</th>
            <th>Term</th>
            <th>Factor</th>
            <th>Sort</th>
            <th>Action</th>
        </tr>

        <?if($terms):?>

        <?foreach($terms as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['term_cat']?></td>
                <td><?=$item['territory']?></td>
                <td><?=$item['term']?></td>
                <td><?=$item['factor']?></td>
                <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['sort']?>"></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['pricingterm-edit'], 'url' => $lang.'/pricingterm/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['pricingterm-delete'], 'url' => $lang.'/pricingterm/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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