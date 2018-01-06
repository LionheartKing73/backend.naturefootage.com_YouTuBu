<div class="content_padding content_bg">
<table cellspacing="0" cellpadding="1" border="0">
    <tr><td>
        <a href="<?=$lang?>/register/profile" class="action"><?=$this->lang->line('profile');?></a>
        <a href="<?=$lang?>/invoices/show" class="action"><?=$this->lang->line('invoices');?></a>
        <a href="<?=$lang?>/download" class="action"><?=$this->lang->line('downloads');?></a>
        <br><br>
    </td></tr>
    <tr><td class="typography">


        <table cellpadding="0" cellspacing="0" border="0" width="100%">

            <tr><td vlaign="top">

                <table cellpadding="4" cellspacing="1" border="0" class="results" width="100%">
                    <tr>
                        <th width="20">#</th>
                        <th width="120" align="left"><?=$this->lang->line('invoice_ref');?></th>
                        <th width="120" align="left"><?=$this->lang->line('amount');?></th>
                        <th><?=$this->lang->line('payment_status');?></th>
                        <th><?=$this->lang->line('approve_status');?></th>
                        <th width="150"><?=$this->lang->line('date');?></th>
                        <th width="120"><?=$this->lang->line('action');?></th>
                    </tr>

                    <?php if($invoices): ?>
                    <?php foreach ($invoices as $key => $invoice): ?>
                        <tr>
                            <td valign="top" align="center"><?=$key+1?></td>
                            <td valign="top"><?=$invoice['ref']?></td>
                            <td valign="top"><? if($invoice['approve'] && $invoice['total']) echo $invoice['currency'].' '.$invoice['total']?></td>
                            <td valign="top" align="center"><?=$invoice['status_text']?></td>
                            <td valign="top" align="center"><?=$invoice['approve_status']?></td>
                            <td valign="top" align="center"><?=$invoice['ctime']?></td>
                            <td valign="top" align="center">
                                <?php
                                get_frontend_actions(array(
                                    array('display' => $this->permissions['invoices-more'], 'url' => $lang.'/invoices/more/'.$invoice['id'], 'name' => $this->lang->line('details')),
                                    array('display' => $this->permissions['payments-show'], 'url' => $lang.'/payments/show/'.$invoice['id'], 'name' => $this->lang->line('payments'))
                                ));
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" align="center"><?=$this->lang->line('empty_list');?></td>
                    </tr>
                    <?php endif; ?>


                </table>
                <br>
            </td></tr>

        </table>

    </td></tr>
</table>
</div>