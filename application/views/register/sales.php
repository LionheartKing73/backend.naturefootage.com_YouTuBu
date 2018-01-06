<div style="width: 100%">
    <div style="">
     <?if($menu):?>
       <?=$menu?>
     <?endif;?>
    </div>
    
</div>


<div style="width: 600px; margin: 10px 0">
    <div style="float: left;">
        <h3 style="display: inline;">Sales Statistics</h3>
    </div>
    <div id="period">
        <span style="color: red"><?=$period_error?></span>
        <?=$filter_menu?>
        <div id="period-bar">
            <?= form_open($lang.'/register/sales/period', array('name'=>'period')) ?>
            <b><?=$this->lang->line('from');?>:</b>
            <input type="text" name="datefrom" value="<?=$this->validation->datefrom;?>" class="field" size="7">&nbsp;<a href="javascript:void(0)"><img src="/data/img/admin/calendar.gif" width=16 height=16 border="0" onClick="displayCalendar(document.period.datefrom,'dd.mm.yyyy',this);return false;" align="absmiddle"></a>
            <b><?=$this->lang->line('to');?>:</b>
            <input type="text" name="dateto" value="<?=$this->validation->dateto;?>" class="field" size="7">&nbsp;<a href="javascript:void(0)"><img src="/data/img/admin/calendar.gif" width=16 height=16 border="0" onClick="displayCalendar(document.period.dateto,'dd.mm.yyyy',this);return false;" align="absmiddle"></a>
            <a href="javascript: document.period.submit()" style="margin-left: 5px">Show</a>
            </form>
        </div>
    </div>
</div>
<div style="float: left; padding-top: 10px">
<?if($items){?>
<table border="0" cellpadding="3" cellspacing="0" width="600">
  <tr>
    <th align="left">Thumbnail</th>
    <th align="left">Code</th>
    <th align="left">Price</th>
    <th align="left">Your %</th>
    <th align="left">Your share</th>
    <th align="left">Date</th>
  </tr>
  <?foreach($items as $item) {?>
  <tr>
    <td>
      <?if ($item['item_type']==2) {?>
      <a href="<?=$lang?>/clips/<?=$item['item_id']?>.html">
      <?}?>
      <img src="<?=$item['thumb']?>" alt="<?=$item['code']?>" width="100">
      <?if ($item['item_type']==2) {?>
      </a>
      <?}?>
    </td>
    <td>
    <?if ($item['item_type']==2) {?>
      <a href="<?=$lang?>/clips/<?=$item['item_id']?>.html">
    <?}?>
      <?=$item['code']?>
    <?if ($item['item_type']==2) {?>
      </a>
    <?}?>
    </td>
    <td align="left"><?=$item['currency'] . ' ' . number_format($item['price'], 2, '.', '')?></td>
    <td align="left"><?=number_format(floatval($item['percent']), 2, '.', '')?></td>
    <?
        $share = number_format($item['price']*($item['percent']/100), 2, '.', '');
        $total_share += $share;
    ?>
    <td align="left"><?= $share ?></td>
    <td align="left"><?=date('d.m.Y', strtotime($item['ctime']))?></td>
  </tr>  
  <?}?>
  <tr>
    <td></td>
    <td></td>
    <td></td>
    <td>Total share:</td>
    <td><?= $total_share ?></td>
    <td></td>
  </tr>
</table>
<div>
<?
  } else {
?>
No items sold.
<div>
<?}?>
