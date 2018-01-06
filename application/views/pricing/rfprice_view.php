<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['pricingrfprice-edit'] && $is_admin) : ?>
    <a class="btn" href="<?=$lang?>/pricingrfprice/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['pricingrfprice-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.pricingrfprice, 'id[]')) change_action(document.pricingrfprice,'<?=$lang?>/pricingrfprice/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="pricingrfprice" action="<?=$lang?>/pricingrfprice/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.pricingrfprice)">
            </th>
            <th>License</th>
            <th>Budget</th>
            <th>Standard</th>
            <th>Premium</th>
            <th>Exclusive</th>
            <th>Action</th>
        </tr>

        <?if($rfprices):?>

        <?foreach($rfprices as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['license']?></td>
                <td><?=$item['budgete_rate']?></td>
                <td><?=$item['standard_rate']?></td>
                <td><?=$item['premium_rate']?></td>
                <td><?=isset($item['provider_exclusive_rate']) ? $item['provider_exclusive_rate'] : $item['exclusive_rate']?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['pricingrfprice-edit'], 'url' => $lang.'/pricingrfprice/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['pricingrfprice-delete'], 'url' => $lang.'/pricingrfprice/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
        <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="7" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>