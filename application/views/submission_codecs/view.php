<strong class="toolbar-item"<? if ($paging) { ?> style="margin-top: 10px;"<? } ?>>
    <?= $this->lang->line('action') ?>:
</strong>

<input type="hidden" name="filter" value="1">

<div class="btn-group toolbar-item">
    <?if ($this->permissions['submission_codecs-edit']) : ?>
    <a class="btn" href="<?=$lang?>/submission_codecs/edit"><?=$this->lang->line('add')?></a>
    <?endif?>
    <? if ($this->permissions['submission_codecs-ord']) : ?>
    <a class="btn" href="javascript: change_action(document.submission_codecs,'<?=$lang?>/submission_codecs/ord')">
        <?=$this->lang->line('save_ord')?></a>
    <?endif?>
    <?if ($this->permissions['submission_codecs-delete']) : ?>
    <a class="btn" href="javascript: if (check_selected(document.submission_codecs, 'id[]')) change_action(document.submission_codecs,'<?=$lang?>/submission_codecs/delete')">
        <?=$this->lang->line('delete')?>
    </a>
    <?endif?>
</div>

<? if ($paging) { ?>
<div class="pagination" style="float: right; width: auto"><?= $paging ?></div>
<? } ?>

<br class="clr">

<form name="submission_codecs" action="<?=$lang?>/submission_codecs/view" method="post">
    <table class="table table-striped">
        <tr>
            <th width="30" align="center">
                <input type="checkbox" name="sample" onclick="javascript:select_all(document.submission_codecs)">
            </th>
            <th>Codec</th>
            <th>Sort</th>
            <th>Action</th>
        </tr>

        <?if($submission_codecs):?>

        <?foreach($submission_codecs as $item):?>
            <tr>
                <td>
                    <input type="checkbox" name="id[]" value="<?=$item['id']?>">
                </td>
                <td><?=$item['name']?></td>
                <td><input type="text" name="ord[<?=$item['id']?>]" style="width:30px" value="<?=$item['sort']?>"></td>
                <td>
                    <?
                    get_actions(array(
                        array('display' => $this->permissions['submission_codecs-edit'], 'url' => $lang.'/submission_codecs/edit/'.$item['id'], 'name' => $this->lang->line('edit')),
                        array('display' => $this->permissions['submission_codecs-delete'], 'url' => $lang.'/submission_codecs/delete/'.$item['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm'))
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