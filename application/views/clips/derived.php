<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
    <?if($id && $this->permissions['clips-edit']){?>
    <a href="<?=$lang?>/clips/edit<?='/'.$id?>" class="btn">
        <?=$this->lang->line('edit')?>
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-cats']){?>
    <a href="<?=$lang?>/clips/cats<?='/'.$id?>" class="btn">
        Categories
    </a>
    <? } ?>
    <!--
    <?if($id && $this->permissions['clips-sequences']){?>
    <a href="<?=$lang?>/clips/sequences<?='/'.$id?>" class="btn">
        Sequences
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-bins']){?>
    <a href="<?=$lang?>/clips/bins<?='/'.$id?>" class="btn">
        Bins
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-galleries']){?>
    <a href="<?=$lang?>/clips/galleries<?='/'.$id?>" class="btn">
        Galleries
    </a>
    <? } ?>-->
    <?if($id && $this->permissions['clips-clipbins']){?>
    <a href="<?=$lang?>/clips/clipbins<?='/'.$id?>" class="btn">
        Clipbins
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-resources']){?>
    <a href="<?=$lang?>/clips/resources<?='/'.$id?>" class="btn">
        Resources
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-attachments']){?>
    <a href="<?=$lang?>/clips/attachments<?='/'.$id?>" class="btn">
        Attachments
    </a>
    <? } ?>
    <?if($id && $this->permissions['clips-statistics']){?>
    <a href="<?=$lang?>/clips/statistics<?='/'.$id?>" class="btn">
        Access statistics
    </a>
    <? } ?>
</div>

<br class="clr">

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
        <? } ?>

    <script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>

<?} else { ?>
    <tr><td colspan="6" class="empty-list"><?= $this->lang->line('empty_list') ?></td></tr>
    <? } ?>

</table>