<style type="text/css">
    @import url(/data/css/clipbin_manager.css);
</style>

<script type="text/javascript" src="/data/js/clipbin_manager.js"></script>
<form class="form-inline">
    <div class="controls controls-row">
        <div class="span6">
            <div class="control-group">
                Date Cteated:&nbsp;
                <div data-date-format="dd-mm-yyyy" data-date="<?php echo $filters['ctime_from']['value']; ?>" class="input-append date filterdatepicker">
                    <input class="span2" size="16" type="text" name="ctime_from" value="<?php echo $filters['ctime_from']['value']; ?>" readonly><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
                to
                <div data-date-format="dd-mm-yyyy" data-date="<?php echo $filters['ctime_to']['value']; ?>" class="input-append date filterdatepicker">
                    <input class="span2" size="16" type="text" name="ctime_to" value="<?php echo $filters['ctime_to']['value']; ?>" readonly><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div>
        <div class="span2">
            <div class="control-group">
                <input type="text" class="span2" name="clipbin_words" value="<?php echo $filters['clipbin_words']['value']; ?>" placeholder="<?php echo $filters['clipbin_words']['label']; ?>">
            </div>
        </div>
        <div class="span3">
            <div class="control-group">
                <select class="span2" name="category">
                    <option value=""><?php echo $filters['category']['label']; ?></option>
                    <option value="Client" <?php echo ($filters['category']['value'] == 'Client') ? 'selected' : ''; ?>>Client</option>
                    <option value="Internal" <?php echo ($filters['category']['value'] == 'Internal') ? 'selected' : ''; ?>>Internal</option>
                    <option value="Master" <?php echo ($filters['category']['value'] == 'Master') ? 'selected' : ''; ?>>Master</option>
                </select>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="controls controls-row">
        <div class="span6">
            <div class="control-group">
                Date Modified:
                <div data-date-format="dd-mm-yyyy" data-date="<?php echo $filters['mtime_from']['value']; ?>" class="input-append date filterdatepicker">
                    <input class="span2" size="16" type="text" name="mtime_from" value="<?php echo $filters['mtime_from']['value']; ?>" readonly><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
                to
                <div data-date-format="dd-mm-yyyy" data-date="<?php echo $filters['mtime_to']['value']; ?>" class="input-append date filterdatepicker">
                    <input class="span2" size="16" type="text" name="mtime_to" value="<?php echo $filters['mtime_to']['value']; ?>" readonly><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div>
        <div class="span2">
            <div class="control-group">
                <input type="text" class="span2" name="clip_words" value="<?php echo $filters['clip_words']['value']; ?>" placeholder="<?php echo $filters['clip_words']['label']; ?>">
            </div>
        </div>
        <div class="span3">
            <div class="control-group">
                <input type="submit" value="Find items" class="btn" name="apply_filters">
            </div>
        </div>
    </div>

    <?php
    foreach($filters as $param => $filter) {
        if($filter['in_sidebar']){ ?>
            <?php if($filter['options']) { ?>
                <input type="hidden" name="<?php echo $param; ?>" value="<?php echo $filter['value_str']; ?>">
            <?php } ?>
    <?php }} ?>

    <div class="clearfix"></div>
</form>
<br>

<div class="clipbin-manager-filter">
    <ul>
        <?php
        $count = 0;
        foreach($filters as $filter) {
            if($filter['in_sidebar']){
                $count++;
            ?>
            <li<?php if($count == 1) echo ' class="first"'; ?>>
                <span class="cliplog-tree-section"><?php echo $filter['label']; ?></span>
                <ul>
                    <?php if($filter['options']) {
                        foreach($filter['options'] as $option) {?>
                            <li<?php if($option['selected']) echo ' class="selected"'; ?>><a href="<?php echo $option['link']; ?>"><input type="checkbox"<?php if($option['selected']) echo ' checked'; ?>> <?php echo $option['label']; ?></a></li>
                    <?php }} ?>
                </ul>
            </li>
        <?php }} ?>
    </ul>
</div>

<div class="clipbin-manager-list">

    <strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
        <?= $this->lang->line('action') ?>:
    </strong>

    <input type="hidden" name="filter" value="1">

    <div class="btn-group toolbar-item">
        <?if ($this->permissions['clipbins-edit']) : ?>
        <a class="btn" href="<?=$lang?>/clipbins/edit"><?=$this->lang->line('add')?></a>
        <?endif?>
        <?if ($this->permissions['clipbins-delete']) : ?>
        <a class="btn" href="javascript: if (check_selected(document.clipbins, 'id[]')) change_action(document.clipbins,'<?=$lang?>/clipbins/delete')">
            <?=$this->lang->line('delete')?>
        </a>
        <?endif?>
    </div>

    <? if ($paging) { ?>
        <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
    <? } ?>

    <!--<br class="clearfix">-->
    <div style="clear: right;"></div>

    <form name="clipbins" action="<?=$lang?>/clipbins/view" method="post">
        <table class="table table-striped">
            <tr>
                <th width="30" align="center">
                    <input type="checkbox" name="sample" onclick="javascript:select_all(document.clipbins)">
                </th>
                <th>Date</th>
                <th>User</th>
                <th>Category</th>
                <th>Clipbin name</th>
                <th>Action</th>
            </tr>

            <?if($clipbins):?>

            <?foreach($clipbins as $item):?>
                <tr>
                    <td>
                        <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                    </td>
                    <td><?=$item['date']?></td>
                    <td><?=$item['fname'] . ' ' . $item['lname']; ?></td>
                    <td><?=$item['category']?></td>
                    <td><?=$item['title']?></td>
                    <td>
                        <?
                        get_actions(array(
                            array('display' => $this->permissions['clipbins-edit'], 'url' => $lang.'/clipbins/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                            array('display' => $this->permissions['clipbins-delete'], 'url' => $lang.'/clipbins/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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

</div>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
<script type="text/javascript">
    $('.filterdatepicker').datepicker();
</script>