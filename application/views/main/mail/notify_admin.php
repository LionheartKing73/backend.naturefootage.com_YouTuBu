<html>
<head>
    <style>
        body, table {font-family:tahoma,verdana; font-size: 8pt;}
    </style>
</head>

<body>
<b>Confirmation letter</b><br><br>

<b>Customer:</b> <?=$invoice['customer']?><br>
<b>Status:</b> <?=$invoice['status_text']?><br>
<b>Invoice number: </b><?=$invoice['ref']?><br>
<b>Invoice date: </b><?=$invoice['ctime']?><br>
<br>

<table cellpadding="4" cellspacing="1" border="1" width="100%">
    <tr>
        <th width="20">#</th>
        <th>Code</th>
        <th>Start time</th>
        <th>End time</th>
        <th>Price per item</th>
    </tr>

    <?if ($invoice['items']) { ?>
    <? foreach ($invoice['items'] as $k => $item) { ?>
        <tr>
            <td valign="top" align="center"><?=$k + 1?></td>
            <td valign="top"><?=$item['code']?></td>
            <td valign="top"><?=$item['start_time']?></td>
            <td valign="top"><?=$item['end_time']?></td>
            <td valign="top" align="right">
                <? if($item['price'] != 0.00){?>
                <?$invoice['currency']?> <?=$item['price']?>
                <?}else{?>
                &nbsp;
                <?}?>
            </td>
        </tr>
        <? }
}?>
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

</body>
</html>