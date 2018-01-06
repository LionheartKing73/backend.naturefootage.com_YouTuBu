<style>
    #order_form .table table th, #order_form .table table td {
        padding: 3px;
    }
    #order_form .table table th {
        vertical-align: middle;
    }
	.admin-title {display:none;}
	.modify_page_title {
    color: #464646;
    font-family: "HelveticaNeue-Light","Helvetica Neue Light","Helvetica Neue",sans-serif;
    font-size: 20px;
    line-height: 29px;
    padding: 10px 15px 10px 0;
    font-weight: normal;
    margin: 0;
    text-shadow: 0 1px 0 #FFFFFF;
}
	.breadcrumb{display:none;}
</style>

<script type="text/javascript" src="/data/js/modify_order.js<?php echo '?' . date( 'dmYh' ); ?>"></script>

<h1 class="modify_page_title">Modify Order: <?php echo $invoice['id']; ?></h1>

<div class="btn-group toolbar-item">
    <? if ($this->permissions['invoices-paymentstatus']) : ?>
        <a class="btn" href="<?=$lang?>/invoices/order_pdf/<? echo $invoice['id']; ?>" target="_blank">View invoice</a>
    <? endif; ?>

    <? if ($this->permissions['invoices-details']) : ?>
        <a class="btn" href="<?=$lang?>/invoices/orderstatus/<?php echo $invoice['id']; ?>">Order Status</a>
    <? endif; ?>

    <? if ($this->permissions['invoices-paymentstatus']) : ?>
        <a class="btn" href="<?=$lang?>/#">Overwrite cart</a>
    <? endif; ?>
    	<a href="<?=$lang?>/invoices/view.html" class="btn">Order List </a>
</div>

<br class="clr">

<form id="order_form" action="<?php echo $lang; ?>/invoices/update/<?php echo $invoice['id']; ?>" method="post">
<input type="hidden" name="id" value="<?php echo $invoice['id']; ?>">
<input type="submit" class="btn btn-primary" value="Save" name="save">
<label for="client_name">
    <h4>User:</h4> 
    
</label>
<input type="text" name="client_name" id="client_name" value="<? echo $invoice['user']['fname'] . ' ' . $invoice['user']['lname'] . ' (' . $invoice['user']['login'] . ')'; ?>">
<input type="hidden" name="client_id" id="client_id" value="<? echo $invoice['client_id']; ?>">

<?php if ($invoice['with_rm']) { ?>
    <h3>Rights Managed License Terms:</h3><br>
    <label for="production_title">
        <h4>Production Title:</h4>
    </label>
    <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][production_title]" id="production_title" value="<? echo $invoice['license']['production_title']; ?>">
    <label for="production_description">
        <h4>Production Description:</h4>
    </label>
    <textarea style="width: 90%; height: 100px;"  name="license[<?php echo $invoice['license']['id']; ?>][production_description]" id="production_description" ><? echo $invoice['license']['production_description']; ?></textarea>
    <label for="allowed_use">
        <h4>Allowed Use(s):</h4>
    </label>
    <textarea style="width: 90%; height: 100px;" type="text" name="allowed_use" id="allowed_use"><? echo $invoice['allowed_use']; ?></textarea>
    <label for="restrictions">
        <h4>Restrictions:</h4>
    </label>
    <textarea style="width: 90%; height: 100px;" type="text" name="restrictions" id="restrictions"><? echo $invoice['restrictions']; ?></textarea>
    <?php if ($invoice['license']['production_territory']) { ?>
        <label for="production_territory">
            <h4>Production Territory:</h4>
        </label>
        <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][production_territory]" id="production_territory" value="<? echo $invoice['license']['production_territory']; ?>">
    <?php } ?>
    <label for="additional_notes">
        <h4>Additional Notes:</h4>
    </label>
    <textarea style="width: 90%; height: 100px;" name="license[<?php echo $invoice['license']['id']; ?>][additional_notes]" id="additional_notes"><? echo $invoice['license']['additional_notes']; ?></textarea>
    <br><br>
<?php } ?>

    <?php if ($invoice['with_nf']) { ?>
        <h3>Rights Managed NatureFlix License Terms:</h3><br>
        <label for="nf_allowed_use">
            <h4>Allowed Use(s):</h4>
        </label>
        <textarea style="width: 90%; height: 100px;" type="text" name="nf_allowed_use" id="nf_allowed_use"><? echo $invoice['nf_allowed_use']; ?></textarea>
        <label for="nf_restrictions">
            <h4>Restrictions:</h4>
        </label>
        <textarea style="width: 90%; height: 100px;" type="text" name="nf_restrictions" id="nf_restrictions" ><? echo $invoice['nf_restrictions']; ?></textarea>
        <br>
        <br>
    <?php } ?>

<?php if ($invoice['with_rf']) { ?>
    <h3>Royalty Free License Terms:</h3><br>
    <label for="rf_allowed_use">
        <h4>Allowed Use:</h4>
    </label>
    <textarea style="width: 90%; height: 100px;" type="text" name="rf_allowed_use" id="rf_allowed_use" ><? echo $invoice['rf_allowed_use']; ?></textarea>
    <br><br>
<?php } ?>

<div style="width: 30%; float: left;">
    <h3>Licensee Information:</h3><br>
    <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][name]" value="<? echo $invoice['license']['name']; ?>" style="width: 302px;" class="lic_name"><br>
    <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][company]" value="<? echo $invoice['license']['company']; ?>" style="width: 302px;" class="lic_company"><br>
    <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][street1]" value="<? echo $invoice['license']['street1']; ?>" style="width: 302px;" class="lic_street1"><br>
    <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][street2]" value="<? echo $invoice['license']['street2']; ?>" style="width: 302px;" class="lic_street2"><br>
    <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][city]" value="<? echo $invoice['license']['city']; ?>" class="input-small lic_city">,
    <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][state]" value="<? echo $invoice['license']['state']; ?>" class="input-small lic_state">
    <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][zip]" value="<? echo $invoice['license']['zip']; ?>" class="input-small lic_zip"><br>
    <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][country]" value="<? echo $invoice['license']['country']; ?>" style="width: 302px;" class="lic_country"><br>
    <input type="text" name="license[<?php echo $invoice['license']['id']; ?>][phone]" value="<? echo $invoice['license']['phone']; ?>" style="width: 302px;" class="lic_phone">
</div>
<div style="width: 30%; float: left;">
    <h3>Billing Information:</h3><br>
    <input type="text" name="billing[<?php echo $invoice['billing']['id']; ?>][name]" value="<? echo $invoice['billing']['name']; ?>" style="width: 302px;" class="bill_name"><br>
    <input type="text" name="billing[<?php echo $invoice['billing']['id']; ?>][company]" value="<? echo $invoice['billing']['company']; ?>" style="width: 302px;" class="bill_company"><br>
    <input type="text" name="billing[<?php echo $invoice['billing']['id']; ?>][street1]" value="<? echo $invoice['billing']['street1']; ?>" style="width: 302px;" class="bill_street1"><br>
    <input type="text" name="billing[<?php echo $invoice['billing']['id']; ?>][street2]" value="<? echo $invoice['billing']['street2']; ?>" style="width: 302px;" class="bill_street2"><br>
    <input type="text" name="billing[<?php echo $invoice['billing']['id']; ?>][city]" value="<? echo $invoice['billing']['city']; ?>" class="input-small bill_city">,
    <input type="text" name="billing[<?php echo $invoice['billing']['id']; ?>][state]" value="<? echo $invoice['billing']['state']; ?>" class="input-small bill_state">
    <input type="text" name="billing[<?php echo $invoice['billing']['id']; ?>][zip]" value="<? echo $invoice['billing']['zip']; ?>" class="input-small bill_zip"><br>
    <input type="text" name="billing[<?php echo $invoice['billing']['id']; ?>][country]" value="<? echo $invoice['billing']['country']; ?>" style="width: 302px;" class="bill_country"><br>
    <input type="text" name="billing[<?php echo $invoice['billing']['id']; ?>][phone]" value="<? echo $invoice['billing']['phone']; ?>" style="width: 302px;" class="bill_phone">
</div>
<div class="clr"></div>
<br>


<div class="row-fluid">
    <div class="span3">
        <h3>Footage Selected:</h3>
    </div>
    <div class="span9">
        <div class="sorting right-text">
            <div class="filter">
                <label>Sort by: </label>
                <select class="code" name="code" onchange="location = this.options[this.selectedIndex].value;">
                    <option value="<?='http://'.$_SERVER['HTTP_HOST'].preg_replace('/.html/i','',$_SERVER['REQUEST_URI']); ?>/code/asc">Clip ID (A-Z)</option>
                    <option value="<?='http://'.$_SERVER['HTTP_HOST'].preg_replace('/.html/i','',$_SERVER['REQUEST_URI']); ?>/code/desc" <?=($by=='desc')?'selected':''; ?>>Clip ID (Z-A)</option>
                </select>
            </div>
        </div>
    </div>
</div>


<?php if($invoice['items']) { ?>
    <table width="100%" class="table table-striped">
        <tr>
            <th>Delete</th>
            <th width="220">Clip</th>
            <th>Clip Details</th>
        </tr>
        <?php foreach($invoice['items'] as $item) { ?>
            <tr id="item-<?php echo $item['id']; ?>" class="item-license-<?php echo $item['license']; ?>">
                <td><span class="delete-icon"></span></td>
                <td style="text-align: center;">
                    <?php if($item['thumb']) { ?>
                        <?php echo $item['code']; ?>
                        (<?php echo floor($item['clip_duration']); ?> sec)
                        <img src="<?php echo $item['thumb']; ?>">
                    <?php } ?>
                </td>
                <td>
                    <table class="table table-striped">
                        <tr>
                            <th width="100">Description:</th>
                            <td><?php echo $item['description']; ?></td>
                        </tr>
                        <tr>
                            <th>License Type:</th>
                            <td><?php echo $item['license_text']; ?> (See License Terms Above)
                            </td>
                        </tr>
                        <tr>
                            <th>Credit Line:</th>
                            <td><?php echo $item['provider_credits'] ? $item['provider_credits'] . ' / Footage Search' : ''; ?></td>
                        </tr>
                        <tr>
                            <th>Duration:</th>
                            <td>
                                <div class="input-append">
                                    <input type="text" name="items[<?php echo $item['id']; ?>][duration]" value="<?php echo $item['duration'] ? $item['duration'] : floor($item['clip_duration']); ?>" class="input-mini" style="margin-bottom: 0;"><span class="add-on">s</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Base Fee:</th>
                            <td>
                                <div class="input-prepend">
                                    <span class="add-on">$</span><input type="text" name="items[<?php echo $item['id']; ?>][base_price]" value="<?php echo $item['base_price']; ?>" class="input-mini js-editable" style="margin-bottom: 0;">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td>
                                <div class="input-append">
                                    <input type="text" name="items[<?php echo $item['id']; ?>][discount]" value="<?php echo $item['discount']; ?>" class="input-mini js-editable" style="margin-bottom: 0;"><span class="add-on">%</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>License Fee:</th>
                            <td>
                                <div class="input-prepend">
                                    <span class="add-on">$</span><input type="text" name="items[<?php echo $item['id']; ?>][total_price]" value="<?php echo $item['total_price']; ?>" class="input-mini" disabled style="margin-bottom: 0;">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Delivery:</th>
                            <td>
                                <input type="text" name="items[<?php echo $item['id']; ?>][delivery_format]" value="<?php echo $item['df_description']; ?>">
                            </td>
                        </tr>
                        <tr>
                            <th>Delivery Fee:</th>
                            <td>
                                <div class="input-prepend">
                                    <span class="add-on">$</span><input type="text" name="items[<?php echo $item['id']; ?>][delivery_price]" value="<?php echo $item['d_price']; ?>" class="input-mini js-editable" style="margin-bottom: 0;">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Total:</th>
                            <td>
                                <div class="input-prepend">
                                    <span class="add-on">$</span><input type="text" name="items[<?php echo $item['id']; ?>][total]" value="<?php echo $item['total']; ?>" class="input-mini" disabled style="margin-bottom: 0;">
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php } ?>
    </table>

    <table  class="table table-striped">
        <tr>
            <th>RM Discount</th>
            <td>
                <div class="input-append">
                    <input type="text" name="rm_discount" class="input-mini" style="margin-bottom: 0;"><span class="add-on">%</span>
                    <button class="btn btn-primary js-apply-rm">Apply to RM Clips</button>
                </div>
            </td>
        </tr>
        <tr>
            <th>RF Discount</th>
            <td>
                <div class="input-append">
                    <input type="text" name="rf_discount" class="input-mini" style="margin-bottom: 0;"><span class="add-on">%</span>
                    <button class="btn btn-primary js-apply-rf">Apply to RF Clips</button>
                </div>
            </td>
        </tr>
        <tr>
            <th>License Fee Subtotal</th>
            <td>
                <div class="input-prepend">
                    <span class="add-on">$</span><input type="text" name="sum" value="<?php echo $invoice['sum']; ?>" style="margin-bottom: 0;">
                </div>
            </td>
        </tr>
        <tr>
            <th>Delivery Fee Subtotal</th>
            <td>
                <div class="input-prepend">
                    <span class="add-on">$</span><input type="text" name="delivery_cost" value="<?php echo $invoice['delivery_cost']; ?>" style="margin-bottom: 0;">
                </div>
            </td>
        </tr>
        <tr>
            <th>Grand Total</th>
            <td>
                <div class="input-prepend">
                    <span class="add-on">$</span><input type="text" name="total" value="<?php echo $invoice['total']; ?>" style="margin-bottom: 0;">
                </div>
            </td>
        </tr>
    </table>
    <div class="form-actions">
        <input type="submit" class="btn btn-primary" value="Save" name="save">
        <span class="btn btn-danger hide" id="del-clips" data-order-id="<?php echo $invoice['id']; ?>">Confirm Delete Clips</span>
    </div>
<?php } ?>
</form>
<input type="hidden" id="del-clips-ids" name="del-clips-ids">
<script type="text/javascript" src="/data/js/invoices.js"></script>