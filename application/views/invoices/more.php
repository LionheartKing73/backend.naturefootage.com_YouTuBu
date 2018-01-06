<div class="content_padding content_bg">
<a href="<?=$lang?>/register/profile" class="action"><?=$this->lang->line('profile');?></a>
<a href="<?=$lang?>/invoices/show" class="action"><?=$this->lang->line('invoices');?></a>
<a href="<?=$lang?>/download" class="action"><?=$this->lang->line('downloads');?></a>
<br><br>
<div class="typography">
    <table cellpadding="4" cellspacing="1" border="0" width="100%">
        <tr>
            <th width="20">#</th>
            <th align="left"><?=$this->lang->line('code');?></th>
            <th><?=$this->lang->line('start_time');?></th>
            <th><?=$this->lang->line('end_time');?></th>
            <th><?=$this->lang->line('invoice_ref');?></th>
            <th align="right"><?=$this->lang->line('item_price');?></th>
        </tr>

        <?php if($invoice['items']) {?>
        <?php foreach ($invoice['items'] as $k => $item) {?>
            <tr>
                <td valign="top" align="center"><?=$k+1?></td>
                <td valign="top">
                    <?if($item['type'] == 'timeline'){
                        echo $this->lang->line('timeline') . ' ' .  $item['item_id'];
                    }else{?>
                        <?=$item['code']?>
                    <?}?>
                </td>
                <td valign="top" align="center"><?=$item['start_time']?></td>
                <td valign="top" align="center"><?=$item['end_time']?></td>
                <td valign="top" align="center"><?=$invoice['ref']?></td>
                <td valign="top" align="right"><?=$invoice['currency']?> <?=$item['price']?></td>
            </tr>
            <?php }} else { ?>
        <tr>
            <td colspan="8" align="center"><?=$this->lang->line('empty_list');?></td>
        </tr>
        <?php } ?>

    </table>

    <br>

    <table cellpadding="4" cellspacing="1" border="0" class="results"  align="right">
        <tr>
            <th align="left"><?=$this->lang->line('net_total');?></th>
            <td><?=$invoice['currency']?> <?=$invoice['sum']?></td>
        </tr>
        <tr>
            <th align="left"><?=$this->lang->line('discount');?></th>
            <td><?=$invoice['currency']?> <?=number_format($invoice['discount'], 2, '.', '')?></td>
        </tr>
        <?/*
  <tr>
    <th align="left"><?=$this->lang->line('vat');?> @ <?=$vat?></th>
    <td><?=$invoice['vat']?> <?=$invoice['currency']?></td>
  </tr>
*/?>
        <tr>
            <th align="left"><?=$this->lang->line('delivery_method');?></th>
            <td><?=$invoice['delivery']?></td>
        </tr>
        <tr>
            <th align="left"><?=$this->lang->line('delivery_cost');?></th>
            <td><?=$invoice['currency']?> <?=$invoice['delivery_cost']?></td>
        </tr>
        <tr>
            <th align="left"><?=$this->lang->line('total');?></th>
            <td><?=$invoice['currency']?> <?=$invoice['total']?></td>
        </tr>
    </table>
</div>
<div class="clear"></div>
</div>