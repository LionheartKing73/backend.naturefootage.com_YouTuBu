<html>
<head>
    <style>
        body, table {font-family:tahoma,verdana; font-size: 8pt;}
    </style>
</head>

<body>
Dear <?=$invoice['customer']?>.
<br><br>
Thank you for your order.
<br><br>

<table cellpadding="4" cellspacing="1" border="1" width="100%">
    <tr>
        <th width="20">#</th>
        <th><?=$this->lang->line('code');?></th>
        <th><?=$this->lang->line('start_time');?></th>
        <th><?=$this->lang->line('end_time');?></th>
        <th><?=$this->lang->line('invoice_ref');?></th>
        <th><?=$this->lang->line('item_price');?></th>
    </tr>

    <?php if($invoice['items']) {?>
    <?php foreach ($invoice['items'] as $k => $item) {?>
        <tr>
            <td valign="top" align="center"><?=$k+1?></td>
            <td valign="top"><?=$item['code']?></td>
            <td valign="top"><?=$item['start_time']?></td>
            <td valign="top"><?=$item['end_time']?></td>
            <td valign="top"><?=$invoice['ref']?></td>
            <td valign="top" align="right">
                <? if($item['price'] != 0.00){?>
                <?$invoice['currency']?> <?=$item['price']?>
                <?}else{?>
                &nbsp;
                <?}?>
            </td>
        </tr>
        <?php }} ?>
</table>

<? if(!$rm_order){ ?>
<table cellpadding="4" cellspacing="1" border="1" align="right">
    <tr>
        <th align="left">Items total</th>
        <td><?=$invoice['currency']?> <?=$invoice['sum']?></td>
    </tr>
    <tr>
        <th align="left"><?=$this->lang->line('discount')?></th>
        <td>
            <?=$invoice['discount']?>% (<? echo $invoice['currency'], ' ', $invoice['discount_abs']?>)
        </td>
    </tr>
    <tr>
        <th align="left">Delivery method</th>
        <td><?=$invoice['delivery']?></td>
    </tr>
    <tr>
        <th align="left">Delivery cost</th>
        <td><?=$invoice['currency']?> <?=$invoice['delivery_cost']?></td>
    </tr>
    <tr>
        <th align="left">Nett amount</th>
        <td>
            <? echo $invoice['currency'], ' ', $invoice['nett'] ?>
        </td>
    </tr>
    <tr>
        <th align="left"><?=$this->lang->line('vat');?></th>
        <td>
            <?=$invoice['vat']?>% (<? echo $invoice['currency'], ' ', $invoice['vat_abs']?>)
        </td>
    </tr>
    <tr>
        <th align="left">Gross amount</th>
        <td><?=$invoice['currency']?> <?=$invoice['total']?></td>
    </tr>
</table>
<?}?>

<br style="clear: both">
<br>

Kind regards,<br>
<?=$this->config->item('title')?> Library team<br>
<a href="http://<?=$host?>">http://<?=$host?></a>
</body>
</html>