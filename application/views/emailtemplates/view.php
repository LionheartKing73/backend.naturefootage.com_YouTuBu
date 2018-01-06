<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['emailtemplates-edit']) : ?>
    <a class="btn" href="<?=$lang?>/emailtemplates/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <?if ($this->permissions['emailtemplates-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.emailtemplates, 'id[]')) change_action(document.emailtemplates,'<?=$lang?>/emailtemplates/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
    <div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="emailtemplates" action="<?=$lang?>/emailtemplates/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.emailtemplates)">
            </th>
            <th>Name</th>
	        <th>Format</th>
            <th>Subject</th>
            <th>Description</th>
            <th>Action</th>
        </tr>

        <?if($templates):?>

        <?foreach($templates as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['name']?></td>
                <td><?= ( $item[ 'is_html' ] == 1 ) ? 'html' : 'text'; ?></td>
                <td><?=$item['subject']?></td>
	            <td>
		            <i style="font-size: 12px; color: #aaa;"><?=$item['description']?></i>
	            </td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['emailtemplates-edit'], 'url' => $lang.'/emailtemplates/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['emailtemplates-delete'], 'url' => $lang.'/emailtemplates/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
                    ))
                    ?>
                </td>
            </tr>
        <?endforeach?>
            </td></tr>
        <?else:?>
            <tr><td colspan="3" style="text-align: center"><?=$this->lang->line('empty_list')?></td></tr>
        <?endif?>
    </table>
</form>

<? if ($paging) { ?>
    <div class="pagination"><?= $paging ?></div>
<? } ?>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>