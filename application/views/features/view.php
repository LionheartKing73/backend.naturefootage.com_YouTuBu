<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['features-edit']):?>
        <a class="btn" href="<?=$lang?>/features/edit<?=$visual?>.html"><?=$this->lang->line('add');?></a>
    <?endif;?>
    <?if ($this->permissions['features-visible']):?>
        <a class="btn" href="javascript: if (check_selected(document.features, 'id[]')) change_action(document.features,'<?=$lang?>/features/visible<?=$visual?>');"><?=$this->lang->line('visible');?></a>
    <?endif;?>
    <?if ($this->permissions['features-ord']):?>
        <a class="btn" href="javascript: change_action(document.features,'<?=$lang?>/features/ord<?=$visual?>');"><?=$this->lang->line('save_ord');?></a>
    <?endif;?>
    <?if ($this->permissions['features-delete']):?>
        <a class="btn" href="javascript: if (check_selected(document.features, 'id[]')) change_action(document.features,'<?=$lang?>/features/delete<?=$visual?>');"><?=$this->lang->line('delete');?></a>
    <?endif;?>
</div>

<br class="clr">

<form name="features" action="<?=$lang?>/features/view<?=$visual?>" method="post">

    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.features);">
            </th>
            <th><?=$this->lang->line('title');?></th>
            <th><?=$this->lang->line('thumbnail');?></th>
            <th>Link</th>
            <th width="40"><?=$this->lang->line('order');?></th>
            <th width="80"><?=$this->lang->line('status');?></th>
            <th width="120"><?=$this->lang->line('date');?></th>
            <th width="80" style="text-align: center"><?=$this->lang->line('action');?></th>
        </tr>

        <?foreach($features as $feature){?>
        <tr>
            <td>
                <input type="checkbox" name="id[]" value="<?=$feature['id']?>">
            </td>
            <td><a href="<?=$lang?>/features/edit/<?=$feature['id'].$visual?>.html"><?=$feature['name']?></a></td>
            <td>
                <?if ($feature['image']) {?>
                <img src="<?=$feature['image']?>" alt="" width="150">
                <?} else {?>
                --
                <?}?>
            </td>
            <td>
                <?if ($feature['link']) {?>
                <a href="<?=$feature['link']?>" target="blank"><?=$feature['link']?></a>
                <?} else {?>
                --
                <?}?>
            </td>
            <td>
                <input type="text" name="ord[<?=$feature['id']?>]" style="width:30px" value="<?=$feature['ord']?>">
            </td>
            <td><?=$feature['active'] ? $this->lang->line('nothidden') : $this->lang->line('hidden')?></td>
            <td><?=$feature['ctime']?></td>
            <td>
                <?
                get_actions(array(
                    array('display' => $this->permissions['features-edit'], 'url' => $lang.'/features/edit/'.$feature['id'], 'name' => $this->lang->line('edit')),
                    array('display' => $this->permissions['features-delete'], 'url' => $lang.'/features/delete/'.$feature['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                ))
                ?>
            </td>
        </tr>
        <?}?>
    </table>

</form>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>