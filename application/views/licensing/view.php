<table class="table table-striped">
    <tr>
        <th>#</th>
        <th>Code</th>
        <th>Name</th>
        <th>Description</th>
        <th>Actions</th>
    </tr>

    <?foreach ($licenses as $license) {?>
    <tr>
        <td><?=$license['id']?></td>
        <td><?=$license['code']?></td>
        <td><?=$license['name']?></td>
        <td><?=$license['description'] ? substr(strip_tags($license['description']), 0, 45) . '...' : ' '?></td>
        <td align="center">
            <?
            get_actions(array(
                array('display' => $this->permissions['licensing-edit'], 'url' => $lang.'/licensing/edit/'.$license['id'], 'name' => 'Description')
            ));
            ?>

            <!--<? if ($license['code'] == 'RM') { ?>
            |
            <a class="action" href="<?=$lang?>/rm/view">View prices</a>
            |
            <a class="action" href="<?=$lang?>/rm/edit">Edit prices</a>
            <? } ?>-->

        </td>
    </tr>
    <?}?>
</table>

<script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>