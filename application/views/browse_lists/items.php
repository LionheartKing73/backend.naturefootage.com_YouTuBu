<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">

    <?if($id && $this->permissions['browselists-edit']){?>
        <a href="<?=$lang?>/browselists/edit<?='/'.$id?>" class="btn">
            Edit list
        </a>
    <? } ?>
    <? if ($this->permissions['browselistitems-edit']) { ?>
        <a class="btn" href="<?=$lang?>/browselistitems/edit<?='/'.$id?>">Add item</a>
    <? } ?>
    <? if ($this->permissions['browselistitems-ord']) { ?>
        <a class="btn" href="javascript: change_action(document.browselistitems,'<?=$lang?>/browselistitems/ord<?='/'.$id?>')">
            <?=$this->lang->line('save_ord')?></a>
    <? } ?>
    <? if ($this->permissions['browselistitems-visible']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.browselistitems, 'id[]')) change_action(document.browselistitems,'<?= $lang ?>/browselistitems/visible<?='/'.$id?>');">
            <?= $this->lang->line('visible'); ?>
        </a>
    <? } ?>
    <? if ($this->permissions['browselistitems-delete']) { ?>
        <a class="btn" href="javascript: if (check_selected(document.browselistitems, 'id[]')) change_action(document.browselistitems,'<?=$lang?>/browselistitems/delete<?='/'.$id?>')">
            <?=$this->lang->line('delete')?>
        </a>
    <? } ?>
</div>

<br class="clr">

<form name="browselistitems" action="<?=$lang?>/browselistitems/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.browselistitems)">
            </th>
            <th>Title</th>
            <th>Sort</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?if($items):?>

            <?foreach($items as $item):?>
                <tr>
                    <td>
                        <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                    </td>
                    <td><?=$item['title']?></td>
                    <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['sort']?>"></td>
                    <td><? if ($item['status'] == 1) echo 'Active'; else echo 'Inactive'; ?></td>
                    <td>
                        <?
                        get_actions(array(
                            array('display' => $this->permissions['browselistitems-edit'], 'url' => $lang.'/browselistitems/edit/'.$id.'/'.$item['id'], 'name' => $this->lang->line('edit')),
                            array('display' => $this->permissions['browselistitems-delete'], 'url' => $lang.'/browselistitems/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                        ))
                        ?>
                    </td>
                </tr>
            <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="5" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
