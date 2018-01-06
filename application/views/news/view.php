<form name="news" action="<?=$lang?>/news/view" method="post">

    <strong class="toolbar-item">
        <?=$this->lang->line('action')?>:
    </strong>

    <input type="hidden" name="filter" value="1">

    <div class="btn-group toolbar-item">
        <? if ($this->permissions['news-edit']) : ?>
        <a class="btn" href="<?=$lang?>/news/edit"><?=$this->lang->line('add');?></a>
        <? endif; ?>
        <? if ($this->permissions['news-visible']) : ?>
        <a class="btn" href="javascript: if (check_selected(document.news, 'id[]')) change_action(document.news,'<?=$lang?>/news/visible');"><?=$this->lang->line('visible');?></a>
        <? endif; ?>
        <? if ($this->permissions['news-delete']) : ?>
        <a class="btn" href="javascript: if (check_selected(document.news, 'id[]')) change_action(document.news,'<?=$lang?>/news/delete');"><?=$this->lang->line('delete');?></a>
        <? endif; ?>
    </div>

    <div class="toolbar-item">
        <div class="controls-group">
            <label for="words"><?= $this->lang->line('search') ?>:</label>
            <input type="text" name="words" id="words" style="width: 120px" value="<?=$filter['words']?>">
        </div>
        <input type="submit" value="<?=$this->lang->line('find')?>" class="btn find">
    </div>

    <div class="toolbar-item">
        <label for="active"><?=$this->lang->line('filter')?>:</label>
        <select name="active" id="active" onchange="change_action(document.news,'')" style="width: auto">
            <option value="0" <? if(!$filter['active']) echo 'selected'?>><?=$this->lang->line('all')?>
            <option value="1" <? if($filter['active']==1) echo 'selected'?>><?=$this->lang->line('nothidden')?>
            <option value="2" <? if($filter['active']==2) echo 'selected'?>><?=$this->lang->line('hidden')?>
        </select>
    </div>

    <br class="clr">

    <table class="table table-striped">
        <tr>
            <th><input type="checkbox" name="sample" onclick="javascript:select_all(document.news);"></th>
            <th><a href="<?=$uri?>/sort/title" class="title"><?=$this->lang->line('title');?></a></th>
            <th><?=$this->lang->line('thumbnail')?></th>
            <th><a href="<?=$uri?>/sort/active" class="title"><?=$this->lang->line('status');?></a></th>
            <th><a href="<?=$uri?>/sort/ctime" class="title"><?=$this->lang->line('date');?></a></th>
            <th class="col-action"><?=$this->lang->line('action');?></th>
        </tr>

        <?php if($news): foreach($news as $page):?>
        <tr class="tdata1">
            <td><input type="checkbox" name="id[]" value="<?=$page['id']?>"></td>
            <td><?=$page['title']?></td>
            <td>
                <? if($page['thumb']){?>
                    <img src="<?=$page['thumb']?>?date=<?=strftime('%Y%m%d%H%M%S', strtotime($page['mtime']))?>" width="100">
                <?}else{?>
                    &nbsp;
                <?}?>
            </td>
            <td><? if($page['active']) echo $this->lang->line('nothidden'); else echo $this->lang->line('hidden');?></td>
            <td><?=$page['ctime']?></td>

            <td>
                <?
                get_actions(array(
                    array('display' => $this->permissions['news-edit'], 'url' => $lang.'/news/edit/'.$page['id'], 'name' => $this->lang->line('edit')),
                    array('display' => $this->permissions['news-delete'], 'url' => $lang.'/news/delete/'.$page['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                ));
                ?>
            </td>
        </tr>
        <?php endforeach; else:?>
            <tr><td colspan="5" class="empty-list"><?=$this->lang->line('empty_list');?></td></tr>
        <?php endif;?>

    </table>

</form>

<?if($news){?>
    <script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<?}?>