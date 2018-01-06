<form name="banners" action="<?=$lang?>/banners/view<?=$visual?>" method="post">

    <strong class="toolbar-item">
        <?=$this->lang->line('action')?>:
    </strong>

    <div class="btn-group toolbar-item">
        <?if ($this->permissions['banners-edit']):?>
            <a class="btn" href="<?=$lang?>/banners/edit"><?=$this->lang->line('add');?></a>
        <?endif;?>
        <?if ($this->permissions['banners-visible']):?>
            <a class="btn" href="javascript: if (check_selected(document.banners, 'id[]')) change_action(document.banners,'<?=$lang?>/banners/visible');"><?=$this->lang->line('visible');?></a>
        <?endif;?>
        <?if ($this->permissions['banners-ord']):?>
            <a class="btn" href="javascript: change_action(document.banners,'<?=$lang?>/banners/ord');"><?=$this->lang->line('save_ord');?></a>
        <?endif;?>
        <?if ($this->permissions['banners-delete']):?>
            <a class="btn" href="javascript: if (check_selected(document.banners, 'id[]')) change_action(document.banners,'<?=$lang?>/banners/delete');"><?=$this->lang->line('delete');?></a>
        <?endif;?>
    </div>

    <br class="clr">

    <table class="table table-striped">
        <tr>
            <th>
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.banners);">
            </th>
            <th><?=$this->lang->line('title');?></th>
            <th><?=$this->lang->line('order');?></th>
            <th><?=$this->lang->line('status');?></th>
            <th><?=$this->lang->line('date');?></th>
            <th class="col-action"><?=$this->lang->line('action');?></th>
        </tr>

        <?foreach ($banners as $banner) { ?>
        <tr>
            <td>
                <input type="checkbox" name="id[]" value="<?=$banner['id']?>">
            </td>
            <td><a href="<?=$lang?>/banners/edit/<?=$banner['id'] . $visual?>.html"><?=$banner['name']?></a></td>
            <td>
                <input type="text" class="field" name="ord[<?=$banner['id']?>]" style="text-align:right;width:30px"
                       value="<?=$banner['ord']?>">
            </td>
            <td><?=$banner['active'] ? $this->lang->line('nothidden') : $this->lang->line('hidden')?></td>
            <td><?=$banner['ctime']?></td>
            <td>
                <?
                get_actions(array(
                    array('display' => $this->permissions['banners-edit'], 'url' => $lang.'/banners/edit/'.$banner['id'], 'name' => $this->lang->line('edit')),
                    array('display' => $this->permissions['banners-delete'], 'url' => $lang.'/banners/delete/'.$banner['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                ));
                ?>
            </td>
        </tr>
        <? }?>
    </table>

</form>


<form action="<?=$lang?>/banners/sort" method="post" lass="form-inline">
        <?=$this->lang->line('banners_sort');?>:
        <label class="radio inline">
            <input type="radio" name="banners_sort" value="random"<?if ($banners_sort == 'random') { ?>
                   checked="checked"<? }?>>
            <?=$this->lang->line('random');?>
        </label>
        <label class="radio inline">
        <input type="radio" name="banners_sort" value="number"<?if ($banners_sort == 'number') { ?>
                   checked="checked"<? }?>>
        <?=$this->lang->line('by_order');?>
        </label>
    <input type="submit" value="<?=$this->lang->line('save');?>" class="btn btn-primary">
</form>

<?if($banners){?>
    <script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?}?>