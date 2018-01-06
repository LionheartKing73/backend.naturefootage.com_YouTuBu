<!DOCTYPE html>
<html>
  <head>
    <title></title>
    <style>
      body { font: 10pt Arial; width: 17cm }
      h1 { background: url("file://<?=$_SERVER['DOCUMENT_ROOT']?>/data/img/logo.gif") left bottom no-repeat;
           font: bold 18pt Arial; margin: 0 0 5px 0; padding: 20px 0 35px 0; text-align: center}
      h2 { font: bold 12pt Arial; margin: 2px 0 5px 0; padding: 0; text-align: left }
      table, td, th, #billTo, #address { border: solid 1px #000; border-collapse: collapse }
      td, th { padding: 4px }
      th, #billTo, #address { background: #ccc }
      #info { float: left; width: 7cm }
      #info th, #total th { text-align: left }
      #billTo, #address { padding: 2px 0 2px 5px; width: 5cm }
      #billTo { float: right }
      #items { margin: 10px 0 }
      #items td { vertical-align: top }
      #total { float: left; width: 8cm }
      #address { float: right }
      #watermark { position: absolute; left: 5cm; top: 4cm }
    </style>
  </head>
  <body>

    <h1>INVOICE</h1>

    <div id="info">
    <table cellspacing="0">
      <tr>
        <th>Invoice No.</th>
        <td><?=$invoice['id']?></td>
      </tr>
      <tr>
        <th>Customer No.</th>
        <td><?=$invoice['vat_no']?></td>
      </tr>
      <tr>
        <th>Invoice Date</th>
        <td><?=strftime('%d-%b-%Y', strtotime($invoice['ctime']))?></td>
      </tr>
      <tr>
        <th>Invoice Ref</th>
        <td><?=$invoice['ref']?></td>
      </tr>
      <tr>
        <th>Sales Order Date</th>
        <td><?=strftime('%d-%b-%Y', strtotime($invoice['ctime']))?></td>
      </tr>
      <tr>
        <th>Ordered By</th>
        <td><?=$invoice['customer']?></td>
      </tr>
    </table>
    </div>

    <div id="billTo">
      <h2>Bill To</h2>
      <?=$invoice['customer']?><br>
      <?=nl2br($invoice['customer_address'])?>
    </div>

    <br style="clear: both">

    <table border="1" cellpadding="4" cellspacing="0" id="items" width="100%">
      <tr>
        <th width="30" align="right">#</th>
        <th>Clip Reference</th>
        <th>Description</th>
        <th>Format</th>
        <th>Licensing</th>
        <th>Price per Item</th>
      </tr>

      <?foreach ($invoice['items'] as $k=>$item) {?>
      <tr>
        <td align="right"><?=$k+1?></td>
        <td>
          <?=$item['code']?>
        <? if($item['img']) {?>
          <br>
          <img src="file://<?=$_SERVER['DOCUMENT_ROOT']?>/<?=$item['img']?>" alt="" vspace="2" width="100">
        <?}?>
        </td>
        <td><?=$item['caption']?></td>
        <td><?=$item['df']?></td>
        <td><?=$item['usage']?></td>
        <td><?=$invoice['currency']?> <?=$item['price']?></td>
      </tr>
      <?}?>
    </table>

    <div id="total">
    <table cellspacing="0">
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
    </div>

    <div id="address">
      <h2>Registered Address</h2>
      <?=$this->config->item('vendor_name')?><br>
      <?=nl2br($this->config->item('vendor_address'))?>
      <br /><br />
      <h2>UK or EEC VAT number</h2>
      <?=$this->config->item('vendor_vat_no')?><br>
    </div>

    <div id="watermark">
      <img src="file://<?=$_SERVER['DOCUMENT_ROOT']?>/data/img/paid.png"
        alt="" width="390" height="380">
    </div>

  </body>
</html>