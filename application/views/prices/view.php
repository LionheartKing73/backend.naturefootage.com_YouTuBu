<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <? if ($this->permissions['prices-edit']) : ?>
    <a class="btn" href="<?=$lang?>/prices/edit"><?=$this->lang->line('add');?></a>
    <? endif; ?>
    <? if ($this->permissions['prices-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.prices, 'id[]')) change_action(document.prices,'<?=$lang?>/prices/delete');"><?=$this->lang->line('delete');?></a>
    <? endif; ?>
</div>

<br class="clr">

<form name="prices" action="<?=$lang?>/prices/view" method="post">

    <table class="table table-striped">
        <tr>
            <th width="30" align="center"><input type="checkbox" name="sample" onclick="javascript:select_all(document.prices);"></th>
            <th><?=$this->lang->line('code');?></th>
            <th><?=$this->lang->line('price');?></th>
            <th><?=$this->lang->line('action');?></th>
        </tr>

        <?if($prices): foreach($prices as $k=>$item):?>
        <tr>
            <td><input type="checkbox" name="id[]" value="<?=$item['id']?>"></td>
            <td><?=$item['code']?></td>
            <td><?=$item['price']?></td>
            <td>
                <?
                get_actions(array(
                    array('display' => $this->permissions['prices-edit'], 'url' => $lang.'/prices/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                    array('display' => $this->permissions['prices-delete'], 'url' => $lang.'/prices/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                ));
                ?>
            </td>
        </tr>
        <?endforeach; else:?>
        <tr>
            <td colspan="4" align="center"><?=$this->lang->line('empty_list');?></td>
        </tr>
        <?endif;?>
    </table>

</form>
<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>