
<div style="margin-bottom: 10px; font-size: 14px;">
    <h1 class="order-status-title">Order status: <? echo $order['id']; ?></h1>
    <!--<span>
        <a href="#" class="scroll-link" data-target="order-ownership-anchor">Order ownership</a>
         | <a href="#" class="scroll-link" data-target="order-status-anchor">Order status</a>
         | <a href="#" class="scroll-link" data-target="file-status-anchor">File status</a>
         | <a href="#" class="scroll-link" data-target="ftp-access-anchor">FTP Access</a>
    <span>-->
</div>

<? //TODO: PERMISSIONS!!!! ?>
<div class="btn-group toolbar-item">
    <? if ($this->permissions['invoices-paymentstatus']) : ?>
        <a class="btn" href="<?=$lang?>/invoices/order_pdf/<? echo $order['id']; ?>" target="_blank">View invoice</a>
    <? endif; ?>

    <? if ($this->permissions['invoices-details']) : ?>
        <a class="btn" href="<?=$lang?>/invoices/details/<? echo $order['id']; ?>">Modify order</a>
    <? endif; ?>

    <? if ($this->permissions['invoices-paymentstatus']) : ?>
        <a class="btn" href="<?=$lang?>/#">Overwrite cart</a>
    <? endif; ?>
    	<a href="<?=$lang?>/invoices/view.html" class="btn">Order List </a>
</div>

<br class="clr">

<div>
    <h2 class="order-status-title"><a href="<? echo $lang; ?>/users/sales_representatives.html" target="_blank">Sales Representative:</a></h2>
    <div class="sales-rep" style="background-color: <? echo $order['selected_sales_rep']['color']?>;"><? echo $order['selected_sales_rep']['fname'] . ' ' . $order['selected_sales_rep']['lname']; ?></div>
    <select class="ajaxify-data" id="sales_rep_id" name="sales_rep_id" data-data='{"ajax":"true"}'>
        <option value="">select</option>
        <? foreach($sales_reps as $rep){ ?>}
            <option value="<? echo $rep['id']; ?>" <? if($rep['id']==$order['sales_rep']){?><?echo 'selected="selected"';}?>><? echo $rep['fname'] . ' ' . $rep['lname']; ?></option>
        <? } ?>
    </select>
</div>

<div class="order-ownership-container">
    <h2 class="order-status-title" id="order-ownership-anchor">Order Ownership:</h2><a href="<? echo $lang; ?>/users/edit/<? echo $order['client_id']; ?>"><? echo $order['user']['fname'] . ' ' . $order['user']['lname']; ?></a>
    <form name="order_ownership">
        <table>
            <thead></thead>
            <tfoot></tfoot>
            <tbody>
            <tr>
                <td>Username:</td>
                <td><input type="text" value="<? echo $order['user']['login']; ?>"></td>
                <td></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="text" value="<? echo $ftp_access['userid']?>" disabled></td>
                <td>Not Modifiable</td>
            </tr>
            <tr>
                <td>Order ID:</td>
                <td><input type="text" value="<? echo $order['id']; ?>" disabled></td>
                <td>Not Modifiable</td>
            </tr>
            <tr>
                <td>Email address:</td>
                <td><input type="text" value="<? echo $order['user']['email']; ?> " disabled></td>
                <td>Not Modifiable</td>
            </tr>
            <tr>
                <td>CC address:</td>
                <td><input type="text" value="[cc]"></td>
                <td>Separate addresses with Comma</td>
            </tr>
            </tbody>
        </table>
        <div class="order-ownership-info">
            <div>
                <strong>License information</strong>
                <p><? echo $order['license']['name']; ?></p>
                <p><? echo $order['license']['street1']; ?></p>
                <p><? echo $order['license']['street2']; ?></p>
                <p><? echo $order['license']['city']; ?></p>
                <p><? echo $order['license']['state']; ?></p>
                <p><? echo $order['license']['zip']; ?></p>
                <p><? echo $order['license']['country']; ?></p>
                <p><? echo $order['license']['phone']; ?></p>
            </div>
            <div>
                <strong>Billing information</strong>
                <p><? echo $order['billing']['name']; ?></p>
                <p><? echo $order['billing']['street1']; ?></p>
                <p><? echo $order['billing']['street2']; ?></p>
                <p><? echo $order['billing']['city']; ?></p>
                <p><? echo $order['billing']['state']; ?></p>
                <p><? echo $order['billing']['zip']; ?></p>
                <p><? echo $order['billing']['country']; ?></p>
                <p><? echo $order['billing']['phone']; ?></p>
            </div>
        </div>
        <a class="btn btn-primary" href="javascript: change_action(document.order_ownership,'');">Apply order Ownership</a>
    </form>
</div>


<div class="notes-container">
    <form class="ajaxify" method="POST" action="<?=$lang;?>/invoices/change_notes/<? echo $order['id']; ?>">
        <h2 class="admin-title">Notes:</h2>
        <textarea class="notes" name="notes" style="width: 40%; height: 100px;"><? echo $order['notes']?></textarea>
        <input type="submit" class="btn btn-primary" value="Add Notes">
    </form>
</div>
<div class="clearfix"></div>
<div>
    <h3>Log information:</h3>
    Customer's IP: <b><?php echo $order['ip'] ?></b><br>
    Order timestamp: <b><?php echo date('Y-m-d H:i:s', strtotime('-8 hours', strtotime($order['mtime'])))?></b>
</div>

<h2 class="order-status-title" id="order-status-anchor">Order Status</h2>
<a href="#">view definition</a>

<table class="order-status-table">
    <thead></thead>
    <tfoot></tfoot>
    <tbody>
    <tr>
        <td class="option-title">Imported to Zoho</td>
        <td class="option-value" style="<? echo 'background-color:'.$colors['imported_status'][$order['imported_status']].';'; ?>"><? echo $order['imported_status']?></td>
        <td class="option-select">
            <?php if ($this->permissions['invoices-imported_status']) { ?>
                <select class="input-small ajaxify" name="imported_status" id="imported_status-<?=$order['id']?>">
                    <option value="Imported"<?php if($order['imported_status'] == 'Imported') echo ' selected'; ?>>Imported</option>
                    <option value="Not imported"<?php if($order['imported_status'] == 'Not imported') echo ' selected'; ?>>Not imported</option>
                </select>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td class="option-title">Order Status:</td>
        <td class="option-value" style="<? echo 'background-color:'.$colors['admin_status'][$order['admin_status']].';'; ?>"><? echo $order['admin_status']; ?></td>
        <td class="option-select">
            <?php if ($this->permissions['invoices-admin_status']) { ?>
                <select class="input-small ajaxify" name="admin_status" id="admin_status-<?=$order['id']?>">
                    <option value="Fillout"<?php if($order['admin_status'] == 'Fillout') echo ' selected'; ?>>Fillout</option>
                    <option value="Reassigned"<?php if($order['admin_status'] == 'Reassigned') echo ' selected'; ?>>Reassigned</option>
                    <option value="Accepted online"<?php if($order['admin_status'] == 'Accepted online') echo ' selected'; ?>>Accepted online</option>
                    <option value="Accepted offline"<?php if($order['admin_status'] == 'Accepted offline') echo ' selected'; ?>>Accepted offline</option>
                </select>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td class="option-title">Payment Status</td>
        <td class="option-value"  style="<? echo 'background-color:'.$colors['status'][$order['status']].';'; ?>">
            <?=$statuses_map['status'][$order['status']]?>
        </td>
        <td class="option-select">
            <?php if ($this->permissions['invoices-paymentstatus']) { ?>
                <select class="input-small ajaxify" name="status" id="status-<?=$order['id']?>">
                    <option value="1"<?php if($order['status'] == '1') echo ' selected'; ?>><?=$statuses_map['status']['1']?></option>
                    <option value="3"<?php if($order['status'] == '3') echo ' selected'; ?>><?=$statuses_map['status']['3']?></option>
                    <option value="2"<?php if($order['status'] == '2') echo ' selected'; ?>><?=$statuses_map['status']['2']?></option>
                </select>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td class="option-title">Release Status:</td>
        <td class="option-value" style="<? echo 'background-color:'.$colors['release_status'][$order['release_status']].';'; ?>"><? echo $order['release_status']; ?></td>
        <td class="option-select">
            <?php if ($this->permissions['invoices-release_status']) { ?>
                <select class="ajaxify" name="release_status" id="release_status-<?=$order['id']?>">
                    <option value="Not approved"<?php if($order['release_status'] == 'Not approved') echo ' selected'; ?>>Not approved</option>
                    <option value="Approved"<?php if($order['release_status'] == 'Approved') echo ' selected'; ?>>Approved</option>
                    <!--option value="Preapproved"<?php if($order['release_status'] == 'Preapproved') echo ' selected'; ?>>Preapproved</option-->
                    <option value="Preapproved no payment"<?php if($order['release_status'] == 'Preapproved no payment') echo ' selected'; ?>>Preapproved no payment</option>
                </select>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td class="option-title">Order Confirmation:</td>
        <td class="option-value" style="<? echo 'background-color:'.$colors['invoice_email_status'][$order['invoice_email_status']].';'; ?>"><? echo $order['invoice_email_status']; ?></td>
        <td class="option-select">
            <a class="btn ajaxify template-change"
               href="<?= $lang ?>/invoices/invoice_email/<?= $order['id'] ?>"
                >Order Confirmation</a>
        </td>
    </tr>
    <tr>
        <td class="option-title">Resume Order Email:</td>
        <td class="option-value" style="<? echo 'background-color:'.$colors['resume_order_email_status'][$order['resume_order_email_status']].';'; ?>"><? echo $order['resume_order_email_status']; ?></td>
        <td class="option-select">
            <? if ($this->permissions['invoices-resume_order_email_status']) : ?>
                <a class="btn ajaxify template-change"
                   href="<?= $lang ?>/invoices/resume_order_email/<?= $order['id'] ?>"
                    >Resume Order Email</a>
            <? endif; ?>
        </td>
    </tr>
    <tr>
        <td class="option-title">FTP Instruction:</td>
        <td class="option-value" style="<? echo 'background-color:'.$colors['download_email_status'][$order['download_email_status']].';'; ?>">
            <?=$order['download_email_status']?>
        </td>
        <td class="option-select">
            <? if ($this->permissions['invoices-download_email_status']) : ?>
                <a class="btn ajaxify template-change"
                   href="<?= $lang ?>/invoices/download_email/<?= $order['id'] ?>"
                    >Send Download Email</a>
            <? endif; ?>
        </td>
    </tr>
    <!--<tr>
        <td class="option-title">View Email Log:</td>
        <td class="option-value" style="<? echo 'background-color:'.$colors['not_implemented'][$order['0']].';'; ?>"><a href="#">View All Sent Emails</a></td>
        <td class="option-select">
        </td>
    </tr>-->
    <tr>
        <td class="option-title">Order Review Status:</td>
        <td class="option-value" style="<? echo 'background-color:'.$colors['not_implemented'][$order['0']].';'; ?>"><?=$order['review_status']?></td>
        <td class="option-select">
            <select class="ajaxify" name="review_status" id="review_status-<?=$order['id']?>">
                <option value="Review" <? if($order['review_status'] == 'Review'){ echo ' Review';}?>>Review</option>
                <option value="Hold" <? if($order['review_status'] == 'Hold'){ echo ' selected';}?>>Hold</option>
                <option value="Completed" <? if($order['review_status'] == 'Completed'){ echo ' selected';}?>>Completed</option>
            </select>
        </td>
    </tr>
    <tr>
        <td class="option-title">Rate Quote Status:</td>
        <td class="option-value" style="<? echo 'background-color:'.$colors['rate_quote'][$order['rate_quote']].';'; ?>"><?=$order['rate_quote']?></td>
        <td class="option-select">
            <select class="ajaxify" name="rate_quote" id="rate_quote-<?=$order['id']?>">
                <option value="reset" <? if($order['rate_quote'] == '0'){ echo ' selected';}?>>Off</option>
                <option value="1" <? if($order['rate_quote'] == '1'){ echo ' selected';}?>>On</option>
            </select>
        </td>
    </tr>
    </tbody>
</table>

<h2 class="order-status-title" id="file-status-anchor">File Status</h2>
<a href="#">view definition</a>

<div class="clearfix"></div>

<? if(false){ ?>
    <? //TODO: PERMISSIONS!!!! ?>
    <div class="btn-group toolbar-item">
        <? if ($this->permissions['invoices-paymentstatus']) : ?>
            <a class="btn" href="<?=$lang?>/#">Order File Delivery Monitor</a>
        <? endif; ?>

        <? if ($this->permissions['invoices-paymentstatus']) : ?>
            <a class="btn" href="<?=$lang?>/#">Order Fullfilment Compressor Status</a>
        <? endif; ?>

        <? if ($this->permissions['invoices-paymentstatus']) : ?>
            <a class="btn" href="<?=$lang?>/#">Show Downloads Order</a>
        <? endif; ?>
    </div>
<? } ?>
<div class="clearfix"></div>

<table class="file-status-table">
    <thead>
    <tr>
        <th>Product</th>
        <th>Upload Status</th>
        <th>Download Status</th>
        <th>Method</th>
        <th>Lab Fulfillment</th>
        <th>Delivery Format</th>
        <th>Source Filename</th>
    </tr>
    </thead>
    <tfoot></tfoot>
    <tbody>

    <? foreach ($order['items'] as $item){ ?>
        <tr>
            <td><? echo $item['code']; ?></td>
            <td>
                <?php if ($this->permissions['invoices-upload_status']) { ?>
                    <select class="input-small ajaxify" name="upload_status" id="upload_status-<?php echo $item['id']?>" style="width: auto;">
                        <option value=""></option>
                        <?php foreach ($upload_statuses_map as $status => $description) { ?>
                            <option value="<?php echo $status; ?>"<?php if($item['upload_status'] == $status) echo ' selected'; ?>><?php echo $description; ?></option>
                        <?php } ?>
                    </select>
                <?php } else { ?>
                    <?php echo $item['upload_status']; ?>
                <?php } ?>
                <?php if($item['upload_status'] == 'Cancelled'){ ?>
                    <div class="uploaderror" data-title="<?php echo $item['upload_notes']?>">?</div>
                <?php } ?>
            </td>
            <td>
                <p><?=$statuses_map['downloaded'][$item['downloaded']]; ?></p>
                <a href="<? echo $lang . '/invoices/orderstatus/' . $order['id'] . '.html';?>"
                   data-data='{"download_status":"reset","order_item_id":"<? echo $item['id']; ?>"}'
                   class="btn ajaxify-data">Reset Download</a>
            </td>
            <td>
                <p><?php echo $item['delivery_method']; ?></p>
            </td>
            <td>
                <?php echo $item['master_lab']; ?>
            </td>
            <td><? echo $item['df_description']; ?></td>
            <td><? echo $item['res']; ?></td>
        </tr>
    <? } ?>

    <? if(false){ ?>
        <tr>
            <td colspan="9" class="summary">
                <p><strong>[Date]</strong><span>[Event]</span></p>
                <p><strong>[Date]</strong><span>[Event]</span></p>
                <a href="#" class="btn">Submit to Compressor</a>
                <a href="#" class="btn">Delete from Compressor</a>
                <a href="#" class="btn">Check Status - Refresh Screen</a>
            </td>
        </tr>
    <? } ?>
    </tbody>
</table>

<? if ($this->permissions['invoices-download_link']) : ?>
    <!--<a class="btn delete_download_link" href="<?=$lang?>/invoices/delete_download_link/<?=$order['id']?>">Delete download link</a>
    <a class="btn generate_link" href="<?=$lang?>/invoices/download_link/<?=$order['id']?>">Generate download link</a>
    <a class="btn ajaxify template-change" href="<?=$lang?>/invoices/email_download_link/<?=$order['id']?>">Email download link</a>-->
    <?php
    if($order['dir_exists']){
    ?>
        <!--<a class="btn" href="<?=$lang?>/invoices/make_token/<?=$order['id']?>" >Generate Upload Token</a>-->
    <?php }else{ ?>
        <!--<span class="btn disabled" >Generate Upload Token</span>-->
    <?php
    }
    ?>
    <span class="s3sync-wrapper">
        <?php if($order['s3_dir']){ ?>
            <span class="btn btn-success" >Transfer to S3 ON</span>
        <?php }else{ ?>
            <a class="btn s3sync btn-danger" href="<?=$lang?>/invoices/s3sync/<?=$order['id']?>" >Transfer to S3 OFF</a>
        <?php } ?>
    </span>
    <br>
<?php /*?>    <span class="link_container">Download link: <? if($order['download_link']) { echo '<a href="' . $order['download_link'] . '">' . $order['download_link'] . '</a>'; } ?></span><br>
<span>Upload link:
        <?php
            if($order['dir_exists']){
                if($token){
                   echo "<a href='//naturefootage.com/orders?action=uploads&token=".$token."'>http://naturefootage.com/orders?action=uploads&token=".$token."</a>";
                }
            }else{
                echo "Directory order is not created.";
            }
        ?>
    </span><?php */?>
    <br>
    <script type="text/javascript">
        $(document).ready(function () {
            $('a.generate_link').click(function(e){
                e.preventDefault();
                $.get($(this).attr('href'), function(data){
                    if(data.message)
                        alert(data.message);
                    if(data.link)
                        $('.link_container').html('Download link: <a href="' + data.link + '">' + data.link + '</a>');
                }, 'json')
                    .fail(function() { alert('error'); });
                return false;
            });

            $('a.delete_download_link').click(function(e){
                e.preventDefault();
                $.get($(this).attr('href'), function(data){
                    if(data.message)
                        alert(data.message);
                    if(data.success){
                        $('span.link_container').html('');
                    }
                }, 'json')
                    .fail(function() { alert('error'); });
                return false;
            });

            $('a.s3sync').click(function(e){
                e.preventDefault();
                $.get($(this).attr('href'), function(data){
                    if(data.message)
                        alert(data.message);
                    if(data.success){
                        $('span.s3sync-wrapper').html('<span class="btn btn-success" >Transfer to S3 ON</span>');
                    }
                }, 'json')
                    .fail(function() { alert('error'); });
                return false;
            });
        });
    </script>
<? endif; ?>
<!--
<h2 class="order-status-title" id="ftp-access-anchor">FTP Access</h2>

<table class="ftp-access-table">
    <thead></thead>
    <tfoot></tfoot>
    <tbody>
    <tr>
        <td colspan="3" class="summary">
            <div>
                <p>FTP Server: <? echo $ftp_access['ftp_server']; ?></p>
                <p>FTP Username: <? echo $ftp_access['userid']?></p>
                <p></p>
                <p>FTP Password: <? echo $ftp_access['passwd']?></p>
                <p>FTP Access Expired: <span class="expiration-date"><?=$order['access_expired']?></span></p>
                <p>FTP Files To Be Archived on: [date]</p>
            </div>
            <a class="btn ajaxify-data" href="<?=$lang?>/invoices/prolongate_access/<?=$order['id'];?>.html?ajax=true">Add 2 weeks of expiration dates</a>
            <a class="btn ajaxify-data" href="<?=$lang?>/invoices/expire_access/<?=$order['id'];?>.html?ajax=true">Expire User's Access(Expires now)</a></br>
        </td>
    </tr>
    
    <tr>
        <td>
            admin
        </td>
        <td>
            Access will expire [date]
        </td>
        <td>
            <a href="#">Remove User's Access Entirely</a></br>
            <a href="#">Expire User's Access(Expires now)</a></br>
            <p>They get back access when order Approved or 2 weeks added here</p>
            <a href="#">Grant 2 additional weeks access</a>
        </td>
    </tr>
    <tr>
        <td>
            someuser
        </td>
        <td>
            Access will expire [date]
        </td>
        <td>
            <a href="#">Remove User's Access Entirely</a></br>
            <a href="#">Expire User's Access(Expires now)</a></br>
            <p>They get back access when order Approved or 2 weeks added here</p>
            <a href="#">Grant 2 additional weeks access</a>
        </td>
    </tr>
    <tr>
        <td>
            orangemuse
        </td>
        <td>
            Access will expire [date]
        </td>
        <td>
            <a href="#">Remove User's Access Entirely</a></br>
            <a href="#">Expire User's Access(Expires now)</a></br>
            <p>They get back access when order Approved or 2 weeks added here</p>
            <a href="#">Grant 2 additional weeks access</a>
        </td>
    </tr>
  
    </tbody>
</table>
  -->
<!--<p><strong>Add Username to access this order via their download page:</strong></p>
<form action="" method="get" class="form-inline">
    <input type="text" name="username" placeholder="Username" style="width: 160px;" value="">
    <input type="submit" value="Add Users Access" class="btn" name="add_user_access">
</form>
-->
<br class="clr">

<script type="text/javascript" src="/vendors/fck/fckeditor.js">
    var count = 0;
</script>
<style type="text/css">
.file-status-table tr th {
    padding: 10px;
}
.file-status-table tbody td {
    padding: 10px 5px;
}

</style>
