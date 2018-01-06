<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['pricingrfdiscount-edit']) : ?>
    <a class="btn" href="<?=$lang?>/pricingrfdiscount/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['pricingrfdiscount-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.pricingrfdiscount, 'id[]')) change_action(document.pricingrfdiscount,'<?=$lang?>/pricingrfdiscount/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="pricingrfdiscount" action="<?=$lang?>/pricingrfdiscount/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.pricingrfdiscount)">
            </th>
            <th>Count</th>
            <th>Discount, %</th>
            <th>Action</th>
        </tr>

        <?if($discounts):?>

        <?foreach($discounts as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['count']?></td>
                <td><?=$item['discount']?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['pricingrfdiscount-edit'], 'url' => $lang.'/pricingrfdiscount/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['pricingrfdiscount-delete'], 'url' => $lang.'/pricingrfdiscount/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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