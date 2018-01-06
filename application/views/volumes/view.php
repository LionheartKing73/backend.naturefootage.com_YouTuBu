<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="volumes" action="<?=$lang?>/volumes/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.volumes)">
            </th>
            <th>Volume</th>
            <th>Size</th>
            <th>Used</th>
            <th>Capacity indicator</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?if($volumes):?>

        <?foreach($volumes as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['name']?></td>
                <td><?=$item['size']?></td>
                <td><?=$item['used']?></td>
                <td><?=$item['is_full'] ? 'Full' : ''?></td>
                <td><?=$item['active'] ? 'Mounted' : 'Offline'?></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['volumes-full'], 'url' => $lang.'/volumes/full/'.$item['id'], 'name' => $item['is_full'] ? 'Unmark full status' : 'Mark as full'),
                        array('display' => $this->permissions['volumes-delete'], 'url' => $lang.'/volumes/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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