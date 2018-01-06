<div class="content_padding content_bg">
    <a href="<?=$lang?>/register/profile" class="action"><?=$this->lang->line('profile');?></a>
    <a href="<?=$lang?>/invoices/show" class="action"><?=$this->lang->line('invoices');?></a>
    <a href="<?=$lang?>/download" class="action"><?=$this->lang->line('downloads');?></a>
    <br><br>

    <div class="typography">
        <table cellpadding="4" cellspacing="1" border="0">
            <tr>
                <th align="left">#</th>
                <th align="left"><?=$this->lang->line('name');?></th>
                <th><?=$this->lang->line('start_time');?></th>
                <th><?=$this->lang->line('end_time');?></th>
                <th><?=$this->lang->line('invoice_ref');?></th>
                <th><?=$this->lang->line('date');?></th>
                <th><?=$this->lang->line('action');?></th>
            </tr>

            <?php if($downloads) { ?>
            <?php foreach ($downloads as $download) { ?>
                <tr>
                    <td><?=$download['num']?></td>
                    <td><?=$download['title']?></td>
                    <td align="center"><?=$download['start_time']?></td>
                    <td align="center"><?=$download['end_time']?></td>
                    <td align="center"><a href="<?=$lang?>/invoices/more/<?=$download['order_id'];?>"><?=$download['order_code']?></a></td>
                    <td align="center"><?=strftime('%d.%m.%Y %H:%M', strtotime($download['ctime']))?></td>
                    <td align="center">
                        <?php
                        if ($download['active']) {
                            if ($download['link']) {
                                get_frontend_actions(array(
                                    array(
                                        'display' => $this->permissions['download-items'],
                                        'url' => $download['link'],
                                        'name' => $this->lang->line('download'))
                                ));
                            } else {
                                ?>
                                File being prepared
                                <?php
                            }
                        }
                        ?>
                    </td>
                </tr>
                <?php }
        } else { ?>
            <tr>
                <td colspan="6" align="center"><?=$this->lang->line('empty_list');?></td>
            </tr>
            <?php } ?>

        </table>
    </div>
</div>