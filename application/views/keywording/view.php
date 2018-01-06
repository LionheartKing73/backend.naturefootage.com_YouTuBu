<table class="table table-striped" style="width: 750px;">
    <tr>
        <th><?= $this->lang->line('thumbnail') ?></th>
        <th><?= $this->lang->line('title') ?></th>
        <th><?= $this->lang->line('code') ?></th>
        <th><?= $this->lang->line('status') ?></th>
        <th><?= $this->lang->line('date') ?></th>
        <th class="col-action"><?= $this->lang->line('action') ?></th>
    </tr>

    <? if ($clips) {
        foreach ($clips as $clip) { ?>
        <tr class="tdata1">
            <td>
                <? if ($this->permissions['clips-edit']) { ?>
                <a href="<?= $lang ?>/clips/edit/<?= $clip['id'] ?>">
                <? } ?>
                <img src="<?= ($clip['thumb']) ? $clip['thumb'] : $default_img ?>" width="100">
                <? if ($this->permissions['clips-edit']) { ?>
                </a>
                <? } ?>
            </td>
            <td><?= esc($clip['title']) ?></td>
            <td><?= esc($clip['code']) ?></td>
            <td><? if ($clip['active'] == 1) echo 'published'; else echo 'unpublished'; ?></td>
            <td><?= $clip['ctime'] ?></td>
            <td>

        <?
        get_actions(array(
            array(
                'display' => $this->permissions['clips-edit'],
                'url' => $lang . '/clips/edit/' . $clip['id'],
                'name' => $this->lang->line('edit')
            ),
            array(
                'display' => $this->permissions['clips-delete'],
                'url' => $lang . '/clips/delete/' . $clip['id'],
                'name' => $this->lang->line('delete'),
                'confirm' => $this->lang->line('delete_confirm'))
        ))
        ?>
                </td>
            </tr>
        <? }

    } else { ?>
            <tr><td colspan="6" class="empty-list"><?= $this->lang->line('empty_list') ?></td></tr>
    <? } ?>

</table>

<? if ($paging) { ?>
		<div class="pagination"><?= $paging ?></div>
<? } ?>