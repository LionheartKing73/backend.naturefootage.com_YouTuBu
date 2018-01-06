<b>Customer:</b> <?=$invoice['customer']?><br>
<b>Payment status:</b> <?=$invoice['status_text']?><br>
<b>Invoice ref:</b> <?=$invoice['ref']?><br>
<b>Invoice date:</b> <?=$invoice['ctime']?>
<br><br>

<div id="playPreview"></div>

<script type="text/javascript">
    lang = "<?=$lang?>";
    function playPreview(preview) {
        createPlayer(preview, 512, 288, "playPreview", false, true);
        $("#playPreview").dialog({
            title: "Clip Preview",
            width: "auto",
            height: "auto",
            position: ["center", "center"],
            modal: true,
            resizable: false,
            buttons: { "Close": function() { $(this).dialog("close"); } },
            beforeClose: function() {
                removePlayer("playPreview");
            }
        });
    }
</script>

<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
    <? if ($this->permissions['invoices-paymentstatus']) : ?>
        <a class="btn" href="<?=$lang?>/invoices/paymentstatus/<?=$invoice['id']?>"><?=$invoice['status'] == 1 ? 'Paid' : 'Unpaid'?></a>
    <? endif; ?>
</div>

<br class="clr">

<form name="invoices" action="<?=$lang?>/invoices/update/<?=$invoice['id']?>" method="post">

    <table class="table">
        <tr>
            <th>#</th>
            <th>Thumbnail</th>
            <th>Code</th>
            <th>Price</th>
            <th>Duration</th>
            <? if(false): ?>
            <th>Type</th>
            <th>Licence</th>
            <th>Caption</th>
            <th>Delivery</th>
            <th>Delivery price</th>
            <!--<th>Licence</th>-->
            <th>Licence usage</th>
            <!--<th>Uploaded</th>-->
            <th>Upload status</th>
            <th>Downloaded</th>
            <th>Delivery Process</th>
            <th>Lab</th>
            <th style="width: 160px">Price per second</th>

            <? endif; ?>
        </tr>

        <?if($invoice['items']):?>
        <?foreach ($invoice['items'] as $k=>$item):?>
            <tr>
                <td><?=$k+1?></td>
                <td>
                    <? if($item['thumb']){ ?>
                        <? if ($item['preview']) { ?>
                            <a href="javascript:playPreview('<?=$item['preview']?>')">
                        <? } ?>
                        <img src="<?= $item['thumb']; ?>" width="100">
                        <? if ($item['preview']) { ?>
                            </a>
                        <? } ?>
                    <?}?>
                </td>
                <td>
                    <? if ($this->permissions['clips-edit']) { ?>
                    <a href="<?= $lang ?>/clips/edit/<?= $item['item_id'] ?>">
                        <? } ?>
                        <?=$item['code']?>
                        <? if ($this->permissions['clips-edit']) { ?>
                    </a>
                <? } ?>
                </td>
                <td><?=number_format($item['total_price'], 2); if($item['discount'] && $item['old_total_price']) echo ' ($' . number_format($item['old_total_price'], 2) . ' with ' . $item['discount'] . '% discount)'; ?></td>
                <td>
                    <? if($this->permissions['clips-edit']){?>
                        <select>
                        <? foreach($item['accepted_durations'] as $dur){ ?>
                            <option value="<?=$dur?>"><?=$dur?> seconds</option>
                        <? } ?>
                        </select>
                    <?} else{ ?>
                        <?=$item['duration'];?>
                    <?}?>
                </td>

                <? if(false): ?>
                <td><?=$item['type']?></td>
                <td><?=$item['rights']?></td>
                <td><?=$item['caption']?></td>
                <td><?=$item['df_description']?></td>
                <td><?=$item['d_price']?></td>
                <!--<td><?=$item['usage']?></td>-->
                <td><?=$item['allowed_use']?></td>
                <!--<td><?=$invoice['currency']?> <?=$item['price']?></td>-->

                <!--<td><?php echo $item['uploaded'] ? 'Yes' : 'No'; ?></td>-->
                <td>
                    <?php if ($this->permissions['invoices-upload_status']) { ?>
                        <select class="input-small ajaxify" name="upload_status" id="upload_status-<?=$item['id']?>">
                            <option value=""></option>
                            <option value="Lab"<?php if($item['upload_status'] == 'Lab') echo ' selected'; ?>>Lab</option>
                            <option value="Created"<?php if($item['upload_status'] == 'Created') echo ' selected'; ?>>Created</option>
                            <option value="Submitted"<?php if($item['upload_status'] == 'Submitted') echo ' selected'; ?>>Submitted</option>
                            <option value="Uploading"<?php if($item['upload_status'] == 'Uploading') echo ' selected'; ?>>Uploading</option>
                            <option value="Uploaded"<?php if($item['upload_status'] == 'Uploaded') echo ' selected'; ?>>Uploaded</option>
                        </select>
                    <?php } else { ?>
                        <?=$item['upload_status']?>
                    <?php } ?>
                </td>
                <td><?php echo $item['downloaded'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <?php if ($this->permissions['invoices-delivery_process']) { ?>
                    <select class="input-small ajaxify" name="delivery_process" id="delivery_process-<?=$item['id']?>">
                        <option value="Manual"<?php if($item['delivery_process'] == 'Manual') echo ' selected'; ?>>Manual</option>
                        <option value="Automated"<?php if($item['delivery_process'] == 'Automated') echo ' selected'; ?>>Automated</option>
                    </select>
                    <?php } else { ?>
                    <?=$item['delivery_process']?>
                    <?php } ?>
                </td>
                <td><input type="text" name="labs[<?=$invoice['id']?>][<?=$item['id']?>]" value="<?=$item['lab']?>" class="input-small"></td>

                <td>
                    <div class="input-prepend">
                        <span class="add-on"><?=$invoice['currency']?></span><input type="text" class="input-mini" name="prices[<?=$invoice['id']?>][<?=$item['id']?>]" value="<?=$item['price']?>" style="margin-bottom: 0;">
                    </div>
                </td>

            <? endif; ?>
            </tr>
            <?endforeach;?>
        <?else:?>
        <tr><td colspan="8" class="empty-list">There are no data.</td></tr>
        <?endif;?>
    </table>

    <? if ($this->permissions['invoices-update'] && $invoice['items']) : ?>
    <div class="form-actions" style="text-align: right; padding-right: 65px;">
        <a class="btn btn-primary" href="javascript: change_action(document.invoices,'<?=$lang?>/invoices/update/<?=$invoice['id']?>');">Update order</a>
    </div>
    <? endif; ?>

</form>

<table class="table" style="float: right; width: 320px">
    <tr>
        <th>Net total</th>
        <td style="width: 160px"><?=$invoice['currency']?> <?=number_format($invoice['sum'], 2)?></td>
    </tr>
    <tr>
        <th>Discount</th>
        <td><?=$invoice['discount']?>%</td>
    </tr>
    <?/*
  <tr>
    <th>Vat @ <?=$vat?>%</th>
    <td><?=$invoice['vat']?> <?=$invoice['currency']?></td>
  </tr>
*/?>
    <!--<tr>
        <th>Delivery method</th>
        <td align="right"><?=$invoice['delivery']?></td>
    </tr>-->
    <tr>
        <th>Delivery cost</th>
        <td><?=$invoice['currency']?> <?=number_format($invoice['delivery_cost'], 2)?></td>
    </tr>
    <tr>
        <th>Total</th>
        <td><?=$invoice['currency']?> <?=number_format($invoice['total'], 2)?></td>
    </tr>
</table>
<br class="clr">