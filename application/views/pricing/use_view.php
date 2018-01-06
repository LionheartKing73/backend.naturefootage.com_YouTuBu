<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['pricinguse-edit'] && $is_admin) : ?>
    <a class="btn" href="<?=$lang?>/pricinguse/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['pricinguse-visible']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.pricinguse, 'id[]')) change_action(document.pricinguse,'<?=$lang?>/pricinguse/visible')">
        <?=$this->lang->line('visible')?>
    </a>
    <?endif?>
    <? if ($this->permissions['pricinguse-ord']) : ?>
    <a class="btn" href="javascript: change_action(document.pricinguse,'<?=$lang?>/pricinguse/ord')">
        <?=$this->lang->line('save_ord')?></a>
    <?endif?>
    <?if ($this->permissions['pricinguse-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.pricinguse, 'id[]')) change_action(document.pricinguse,'<?=$lang?>/pricinguse/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="pricinguse" action="<?=$lang?>/pricinguse/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.pricinguse)">
            </th>
            <th>Category</th>
            <th>Use</th>
            <th>Budget</th>
            <th>Standard</th>
            <th>Premium</th>
            <th>Exclusive</th>
            <th>Terms category</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?if($uses):?>

        <?foreach($uses as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['category']?></td>
                <td><?=$item['use']?></td>
                <td><?=$item['budgete_rate']?></td>
                <td><?=$item['standard_rate']?></td>
                <td><?=$item['premium_rate']?></td>
                <td><?=isset($item['provider_exclusive_rate']) ? $item['provider_exclusive_rate'] : $item['exclusive_rate']?></td>
                <td><?=$item['terms_cat']?></td>
                <!--<td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['ord']?>"></td>-->
                <td><?if($item['display']) echo 'active'; else echo 'inactive'?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['pricinguse-edit'], 'url' => $lang.'/pricinguse/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['pricinguse-delete'], 'url' => $lang.'/pricinguse/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
        <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="10" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>