<script>
    $( document ).ready(function ($) {
        $('.pagination').first().css({"margin-bottom": "-31px"});
        $('.table.invoices-table').stickyTableHeaders();
        $('form[name="invoices"]').on('click', '.order-star.ajax', function () {
            $(this).toggleClass('star');
            var val = $(this).hasClass('star') ? 1 : 0;
            $.post($(this).attr('href'),
                {
                    star: true,
                    star_val: "" + val
                },
                function (data) {

                }
            );
            return false;
        });
    });
</script>

<form name="invoices" class="invoices" action="<?= $lang ?>/invoices/view" method="post">
<input type="hidden" name="filter" value="1">

<div class="toolbar-item mini">
    <div class="controls-group">
        <label for="order_id">Order<br>ID:</label>
        <input type="text" name="order_id" id="order_id" value="<?= $filter['order_id'] ?>" style="width: 80px">
    </div>

    <div class="controls-group">
        <label for="user_name">Username:</label>
        <input type="text" name="username" id="username" value="<?= $filter['username'] ?>" style="width: 80px">
    </div>

    <div class="controls-group">
        <label for="sales_rep">Sales<br>Rep.:</label>
        <select class="input-small" name="sales_rep">
            <option value="">--select--</option>
            <? foreach ($sales_reps as $rep) { ?>
                <option value="<? echo $rep['id'] ?>" <? if ($filter['sales_rep'] == $rep['id']) {
                    echo 'selected="selected"';} ?> ><? echo $rep['fname'] . ' ' . $rep['lname'] ?></option>
            <? } ?>
        </select>
    </div>

    <div class="controls-group">
        <label for="video_assets">Video<br>assets.:</label>
        <input type="text" name="clip_id" id="clip_id" value="<?= $filter['clip_id'] ?>" style="width: 80px">
    </div>

    <div class="controls-group">
        <label for="reviewed_orders">Reviewed<br>orders:</label>
        <select name="review_status" id="review_status" style="width: auto;">
            <option></option>
            <option value="Review" <? if ($filter['review_status'] == 'Review') { ?>selected="selected" <? } ?>>Review
            </option>
            <option value="Hold" <? if ($filter['review_status'] == 'Hold') { ?>selected="selected" <? } ?>>Hold
            </option>
            <option value="Completed" <? if ($filter['review_status'] == 'Completed') { ?>selected="selected" <? } ?>>
                Completed
            </option>
        </select>
    </div>

    <div class="controls-group">
        <!--<label><?=$this->lang->line('filter')?>:</label>-->
        <label for="status">Payment<br>status:</label>
        <select name="status" id="status" style="width: auto">
            <option value="0" <? if (!$filter['status']) echo 'selected' ?>>All</option>
            <option value="1" <? if ($filter['status'] == 1) echo 'selected' ?>>Not paid</option>
            <option value="2" <? if ($filter['status'] == 2) echo 'selected' ?>>Failed</option>
            <option value="3" <? if ($filter['status'] == 3) echo 'selected' ?>>Paid</option>
        </select>
    </div>

    <div class="controls-group">
        <input type="submit" value="<?= $this->lang->line('find') ?>" class="btn find">
    </div>
</div>

<br class="clr">

<div class="invoices-table-container">
<table class="table invoices-table">
<thead>
<tr>
    <? //TODO: add translations ?>
    <th class="star"><a href="<?= $uri ?>/sort/star" class="title order-star star filter-asc"></a></th>
    <th><a href="<?= $uri ?>/sort/id" class="title zero-padding-left-right">Invoice</a></th>
    <th><a href="<?= $uri ?>/sort/ctime" class="title zero-padding-left-right"><?= $this->lang->line('date') ?></a></th>
    <th><a href="<?= $uri ?>/sort/client_id" class="title zero-padding-left-right"><?= $this->lang->line('customer') ?></a></th>
    <th><a href="<?= $uri ?>/sort/sales_rep" class="title">Sales&nbsp;Rep.</a></th>
    <th><a href="<?= $uri ?>/sort/notes" class="title">Notes</a></th>
    <th><a href="<?= $uri ?>/sort/production_title" class="title">Title</a></th>
    <th><a href="<?= $uri ?>/sort/status" class="title">Payment</a></th>
    <th><a href="<?= $uri ?>/sort/imported_status" class="title">Zoho</a></th>
    <th><a href="<?= $uri ?>/sort/admin_status" class="title">Status</a></th>
    <th><span>Delivery</span></th>
    <th><a href="<?= $uri ?>/sort/review_status" class="title">Review</a></th>
</tr>
</thead>
<tfoot>
<tr>
    <td></td>
</tr>
</tfoot>
<tbody>
<? if ($invoices): foreach ($invoices as $invoice): ?>
    <tr>
        <td class="star"><a href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html"
                            class="order-star ajax <? if ($invoice['star'] == "1") {
                                echo 'star';
                            } ?>"></a>
        </td>
        <td>
            <p><? echo $invoice['id']?></p>
            <a href="<? echo $lang; ?>/invoices/order_pdf/<? echo $invoice['id']; ?>" onclick='window.open("<? echo $lang; ?>/invoices/order_pdf/<? echo $invoice['id']; ?>");return false;'>View</a><br>
            <a href="<? echo $lang; ?>/invoices/orderstatus/<? echo $invoice['id'] ?>.html">Status</a><br>
            <a href="<? echo $lang . '/invoices/details/' . $invoice['id'] . '.html'; ?>">Modify</a></td>
        <td class="zero-padding-left-right"><?= $invoice['ctime'] ?></td>
        <td class="zero-padding-left-right"><?= '<a href="' . $lang . '/users/edit/' . $invoice['client_id'] . '">' . $invoice['customer'] . '</a>' ?></td>
        <td>
            <a class="sales-rep" style="background-color:<? echo $invoice['sales_rep']['color']; ?>" target="_blank"
               href="<? echo $lang . '/users/sales_representatives.html'; ?>"><? if ($invoice['sales_rep']['fname']) {
                    echo $invoice['sales_rep']['fname'] . '&nbsp;' . $invoice['sales_rep']['lname'];
                } ?></a><br>
            <select style="width:auto;" class="ajaxify-data" id="sales_rep_id" name="sales_rep_id"
                    data-data='{"order_id":"<? echo $invoice['id']; ?>"}'
                    data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html"
                >
                <option value="false">--select--</option>
                <? foreach ($sales_reps as $rep) { ?>
                    <option value="<? echo $rep['id'] ?>" <? if ($invoice['sales_rep_id'] == $rep['id']) {
                        echo 'selected="selected"';
                    } ?>><? echo $rep['fname'] . ' ' . $rep['lname'] ?></option>
                <? } ?>
            </select>
        </td>
        <td><textarea class="notes ajaxify"
                      data-action="<? echo $lang . '/invoices/change_notes/' . $invoice['id'] . '.html'; ?>" data-
                      style="width: 100px;"><? echo $invoice['notes']; ?></textarea></td>
        <td><? echo $invoice['production_title'] ?></td>
        <td class="talign-center" style="background-color:<?=$colors['status'][$invoice['status']]?>">
            <?php if ($this->permissions['invoices-status']) { ?>
                <select class="input-small ajaxify-data" id="status-<?= $invoice['id'] ?>"
                        data-data='{"name":"status", "id":"<? echo $invoice['id']; ?>"}'
                        data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html">
                    <option value="false">--select--</option>
                    <option value="1"<?php if (($invoice['status'] == '1') OR ($invoice['status'] == '5')) echo ' selected'; ?>>Not Paid</option>
                    <option value="3"<?php if ($invoice['status'] == '3') echo ' selected'; ?>>Paid</option>
                    <option value="2"<?php if ($invoice['status'] == '2') echo ' selected'; ?>>CC failed</option>
                </select><br>
            <?php } else { ?>
                <?= $invoice['status_text'] ?>
            <?php } ?>
            <p><?= $invoice['status_text'] ?> $<?= $invoice['total'] ?></p>
            <a class="btn ajaxify template-change" href="en/invoices/invoice_email/<? echo $invoice['id']; ?>">Send Invoice</a>
        </td>
        <td style="background-color:<?=$colors['imported_status'][$invoice['imported_status']]?>">
            <?php if ($this->permissions['invoices-imported_status']) { ?>
                <select class="input-small ajaxify_status" name="imported_status"
                        id="imported_status-<?= $invoice['id'] ?>"
                        data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html"
                    >
                    <option value="Imported"<?php if ($invoice['imported_status'] == 'Imported') echo ' selected'; ?>>
                        Imported
                    </option>
                    <option
                        value="Not imported"<?php if ($invoice['imported_status'] == 'Not imported') echo ' selected'; ?>>
                        Not imported
                    </option>
                </select>
            <?php } ?>
        </td>
        <td style="background-color:<?=$colors['admin_status'][$invoice['admin_status']]?>">
            <?php if ($this->permissions['invoices-admin_status']) { ?>
                <select style="width: 107px;" class="input-small ajaxify_status" name="admin_status" id="admin_status-<?= $invoice['id'] ?>"
                        data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html"
                    >
                    <option value="false"></option>
                    <option value="Fillout"<?php if ($invoice['admin_status'] == 'Fillout') echo ' selected'; ?>>
                        Fillout
                    </option>
                    <option value="Reassigned"<?php if ($invoice['admin_status'] == 'Reassigned') echo ' selected'; ?>>
                        Reassigned
                    </option>
                    <option
                        value="Accepted online"<?php if ($invoice['admin_status'] == 'Accepted online') echo ' selected'; ?>>
                        Accepted online
                    </option>
                    <option
                        value="Accepted offline"<?php if ($invoice['admin_status'] == 'Accepted offline') echo ' selected'; ?>>
                        Accepted offline
                    </option>
                </select><br>
                <a class="btn ajaxify template-change" href="en/invoices/resume_order_email/<? echo $invoice['id'] ?>">Resume
                    Order</a>
            <?php } ?>
        </td>
        <td class="ftp-wrapper">
            <div>
                <div>
                    <span class="nowrap">Upload [<? echo $invoice['uploaded_text'];?>]</span>

                </div>
                <div style="margin-bottom: 10px;">
                    <span class="nowrap">Download [<? echo $invoice['downloaded_text'];?>]</span>
                </div>
                <div>
                    <select class="ajaxify_status input-small" name="release_status"
                            id="release_status-<?= $invoice['id'] ?>"
                            data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html"
                        >
                        <?php if(!empty($statuses_map['release_status'])): ?>
                            <?php foreach($statuses_map['release_status'] as $status): ?>
                                <option value="<?=$status?>"<?php if ($invoice['release_status'] == $status) echo ' selected'; ?>>
                                    <?=$status?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <span><? echo $invoice['download_email_status']; ?></span>
                    <? if ($this->permissions['invoices-download_email']) : ?>
                        <a class="btn ajaxify template-change"
                           href="<?= $lang ?>/invoices/download_email/<?= $invoice['id'] ?>"
                            >Send</a>
                    <? endif; ?>
                </div>
            </div>
        </td>
        <td class="review-status">
            <span><?=$invoice['review_status'];?></span>
            <?
            get_actions(array(
                array('display' => $this->permissions['invoices-details'], 'url' => $lang . '/invoices/reviewstatus/' . $invoice['id'] . '.html?review_status=Review', 'name' => 'Review'),
                array('display' => $this->permissions['invoices-statuses'], 'url' => $lang . '/invoices/reviewstatus/' . $invoice['id'] . '.html?review_status=Hold', 'name' => 'Hold'),
                array('display' => $this->permissions['invoices-delete'], 'url' => $lang . '/invoices/reviewstatus/' . $invoice['id'] . '.html?review_status=Completed', 'name' => 'Completed'),
            ));
            ?>
        </td>

    </tr>

    <? if (false): ?>
        <tr>
            <td><input type="checkbox" name="id[]" value="<?= $invoice['id'] ?>"></td>
            <td><?= $invoice['ref'] ?></td>
            <td><?= $invoice['customer'] ?></td>
            <td><?= $invoice['currency'] . ' ' . $invoice['total'] ?></td>
            <td>
                <?php if ($this->permissions['invoices-paymentstatus']) { ?>
                    <select class="input-small ajaxify" name="status" id="status-<?= $invoice['id'] ?>">
                        <option value="1"<?php if ($invoice['status'] == '1') echo ' selected'; ?>>Not paid</option>
                        <option value="3"<?php if ($invoice['status'] == '3') echo ' selected'; ?>>Paid</option>
                        <option value="2"<?php if ($invoice['status'] == '2') echo ' selected'; ?>>CC failed</option>
                    </select>
                <?php } else { ?>
                    <?= $invoice['status_text'] ?>
                <?php } ?>
            </td>
            <td>
                <?php if ($this->permissions['invoices-admin_status']) { ?>
                    <select class="input-small ajaxify" name="admin_status" id="admin_status-<?= $invoice['id'] ?>">
                        <option value="0"></option>
                        <option value="1"<?php if ($invoice['admin_status'] == 'Imported') echo ' selected'; ?>>
                            Imported
                        </option>
                        <option value="2"<?php if ($invoice['admin_status'] == 'Accepted Online') echo ' selected'; ?>>
                            Accepted Online
                        </option>
                        <option value="3"<?php if ($invoice['admin_status'] == 'Payment') echo ' selected'; ?>>Payment
                        </option>
                        <option value="4"<?php if ($invoice['admin_status'] == 'FTP Status') echo ' selected'; ?>>FTP
                            Status
                        </option>
                        <option value="5"<?php if ($invoice['admin_status'] == 'Emails') echo ' selected'; ?>>Emails
                        </option>
                    </select>
                <?php } else { ?>
                    <?= $invoice['admin_status'] ?>
                <?php } ?>
            </td>
            <td>
                <?php if ($this->permissions['invoices-client_status']) { ?>
                    <select class="input-small ajaxify" name="client_status" id="client_status-<?= $invoice['id'] ?>">
                        <option value="1"<?php if ($invoice['client_status'] == 'Review') echo ' selected'; ?>>Review
                        </option>
                        <option
                            value="2"<?php if ($invoice['client_status'] == 'Footage Uploading') echo ' selected'; ?>>
                            Footage Uploading
                        </option>
                    </select>
                <?php } else { ?>
                    <?= $invoice['client_status'] ?>
                <?php } ?>
            </td>

            <td><?= $invoice['ctime'] ?></td>

            <td align="center" width="100">
                <?
                $status_name = $invoice['status'] == 1 ? 'Paid' : 'Unpaid';
                $approve_status_name = $invoice['approve'] == 1 ? 'Unapprove' : 'Approve';
                get_actions(array(
                    array('display' => $this->permissions['invoices-details'], 'url' => $lang . '/invoices/details/' . $invoice['id'], 'name' => $this->lang->line('details')),
                    array('display' => $this->permissions['invoices-statuses'], 'url' => $lang . '/invoices/statuses/' . $invoice['id'], 'name' => 'Statuses'),
                    array('display' => $this->permissions['invoices-delete'], 'url' => $lang . '/invoices/delete/' . $invoice['id'], 'name' => $this->lang->line('delete'), 'confirm' => $this->lang->line('delete_confirm')),
                    //array('display' => $this->permissions['invoices-paymentstatus'], 'url' => $lang.'/invoices/paymentstatus/'.$invoice['id'], 'name' => $status_name)
                ));
                ?>
            </td>
        </tr>


        <tr>
            <td colspan="7">
                <table width="100%" class="invoice-statuses">
                    <tr>
                        <td><a href="#">Statuses</a></td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" class="selected-statuses">
                                <tr>
                                    <td>

                                        <div
                                            class="selected-status selected_imported_status"<?php if ($invoice['imported_status']) echo ' style="display:block;"' ?>>
                                            <span class="status-label">Imported to Zoho</span> <span
                                                class="status_value"><?php echo $invoice['imported_status']; ?></span>
                                        </div>
                                        <div
                                            class="selected-status selected_admin_status"<?php if ($invoice['admin_status']) echo ' style="display:block;"' ?>>
                                            <span class="status-label">Order status</span> <span
                                                class="status_value"><?php echo $invoice['admin_status']; ?></span>
                                        </div>
                                        <div
                                            class="selected-status selected_status"<?php if ($invoice['status']) echo ' style="display:block;"' ?>>
                                            <span class="status-label">Payment status</span> <span
                                                class="status_value"><?php echo $invoice['status_text']; ?></span>
                                        </div>
                                        <div
                                            class="selected-status selected_download_email_status"<?php if ($invoice['download_email_status']) echo ' style="display:block;"' ?>>
                                            <span class="status-label">Download instructions sent</span> <span
                                                class="status_value"><?php echo $invoice['download_email_status']; ?></span>
                                        </div>
                                        <div
                                            class="selected-status selected_release_status"<?php if ($invoice['release_status']) echo ' style="display:block;"' ?>>
                                            <span class="status-label">Release status</span> <span
                                                class="status_value"><?php echo $invoice['release_status']; ?></span>
                                        </div>
                                        <div
                                            class="selected-status selected_client_status"<?php if ($invoice['client_status']) echo ' style="display:block;"' ?>>
                                            <span class="status-label">Client status</span> <span
                                                class="status_value"><?php echo $invoice['client_status']; ?></span>
                                        </div>
                                        <div
                                            class="selected-status selected_resume_order_email_status"<?php if ($invoice['resume_order_email_status']) echo ' style="display:block;"' ?>>
                                            <span class="status-label">Resume order email sent</span> <span
                                                class="status_value"><?php echo $invoice['resume_order_email_status']; ?></span>
                                        </div>
                                    </td>
                                    <td width="82">
                                        <img src="data/img/admin/edit_statuses.jpg" class="edit_statuses">
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr class="statuses-select-cont">
                        <td>
                            <table width="100%" class="status-select">
                                <tr>
                                    <td>
                                        <table class="form-inline" width="100%">
                                            <tr>
                                                <th width="175">Imported to Zoho</th>
                                                <td>
                                                    <?php if ($this->permissions['invoices-imported_status']) { ?>
                                                        <select class="ajaxify_status" name="imported_status"
                                                                id="imported_status-<?= $invoice['id'] ?>"
                                                                data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html">
                                                            <option
                                                                value="Imported"<?php if ($invoice['imported_status'] == 'Imported') echo ' selected'; ?>>
                                                                Imported
                                                            </option>
                                                            <option
                                                                value="Not imported"<?php if ($invoice['imported_status'] == 'Not imported') echo ' selected'; ?>>
                                                                Not imported
                                                            </option>
                                                        </select>
                                                    <?php } ?>
                                                </td>
                                                <th width="175">Release status</th>
                                                <td>
                                                    <?php if ($this->permissions['invoices-release_status']) { ?>
                                                        <select class="ajaxify_status" name="release_status"
                                                                id="release_status-<?= $invoice['id'] ?>"
                                                                data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html">
                                                            <option value=""></option>
                                                            <option
                                                                value="Not approved"<?php if ($invoice['release_status'] == 'Not approved') echo ' selected'; ?>>
                                                                Not approved
                                                            </option>
                                                            <option
                                                                value="Approved"<?php if ($invoice['release_status'] == 'Approved') echo ' selected'; ?>>
                                                                Approved
                                                            </option>
                                                            <option
                                                                value="Preapproved"<?php if ($invoice['release_status'] == 'Preapproved') echo ' selected'; ?>>
                                                                Preapproved
                                                            </option>
                                                            <option
                                                                value="Preapproved no payment"<?php if ($invoice['release_status'] == 'Preapproved no payment') echo ' selected'; ?>>
                                                                Preapproved no payment
                                                            </option>
                                                        </select>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Order status</th>
                                                <td>
                                                    <?php if ($this->permissions['invoices-admin_status']) { ?>
                                                        <select class="ajaxify_status" name="admin_status"
                                                                id="admin_status-<?= $invoice['id'] ?>"
                                                                data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html">
                                                            <option value=""></option>
                                                            <option
                                                                value="Fillout"<?php if ($invoice['admin_status'] == 'Fillout') echo ' selected'; ?>>
                                                                Fillout
                                                            </option>
                                                            <option
                                                                value="Reassigned"<?php if ($invoice['admin_status'] == 'Reassigned') echo ' selected'; ?>>
                                                                Reassigned
                                                            </option>
                                                            <option
                                                                value="Accepted online"<?php if ($invoice['admin_status'] == 'Accepted online') echo ' selected'; ?>>
                                                                Accepted online
                                                            </option>
                                                            <option
                                                                value="Accepted offline"<?php if ($invoice['admin_status'] == 'Accepted offline') echo ' selected'; ?>>
                                                                Accepted offline
                                                            </option>
                                                        </select>
                                                    <?php } ?>
                                                </td>
                                                <th>Client status</th>
                                                <td>
                                                    <?php if ($this->permissions['invoices-client_status']) { ?>
                                                        <select class="ajaxify_status" name="client_status"
                                                                id="client_status-<?= $invoice['id'] ?>"
                                                                data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html">
                                                            <option
                                                                value="Review"<?php if ($invoice['client_status'] == 'Review') echo ' selected'; ?>>
                                                                Review
                                                            </option>
                                                            <option
                                                                value="Footage Uploading"<?php if ($invoice['client_status'] == 'Footage Uploading') echo ' selected'; ?>>
                                                                Footage Uploading
                                                            </option>
                                                            <option
                                                                value="Hold"<?php if ($invoice['client_status'] == 'Hold') echo ' selected'; ?>>
                                                                Hold
                                                            </option>
                                                        </select>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Payment status</th>
                                                <td>
                                                    <?php if ($this->permissions['invoices-paymentstatus']) { ?>
                                                        <select class="ajaxify_status" name="status"
                                                                id="status-<?= $invoice['id'] ?>"
                                                                data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html">
                                                            <option
                                                                value="1"<?php if ($invoice['status'] == '1') echo ' selected'; ?>>
                                                                Not paid
                                                            </option>
                                                            <option
                                                                value="3"<?php if ($invoice['status'] == '3') echo ' selected'; ?>>
                                                                Paid
                                                            </option>
                                                            <option
                                                                value="2"<?php if ($invoice['status'] == '2') echo ' selected'; ?>>
                                                                Cancelled
                                                            </option>
                                                        </select>
                                                    <?php } ?>
                                                </td>
                                                <th>Resume order email sent</th>
                                                <td>
                                                    <?php if ($this->permissions['invoices-resume_order_email_status']) { ?>
                                                        <select class="ajaxify_status" name="resume_order_email_status"
                                                                id="resume_order_email_status-<?= $invoice['id'] ?>"
                                                                data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html">
                                                            <option
                                                                value="Sent"<?php if ($invoice['resume_order_email_status'] == 'Sent') echo ' selected'; ?>>
                                                                Sent
                                                            </option>
                                                            <option
                                                                value="Not sent"<?php if ($invoice['resume_order_email_status'] == 'Not sent') echo ' selected'; ?>>
                                                                Not sent
                                                            </option>
                                                        </select>
                                                    <?php } ?>
                                                    <? if ($this->permissions['invoices-resume_order_email']) : ?>
                                                        <a class="btn ajaxify"
                                                           href="<?= $lang ?>/invoices/resume_order_email/<?= $invoice['id'] ?>">Send
                                                            Receipt</a>
                                                    <? endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Download instructions sent</th>
                                                <td colspan="3">
                                                    <?php if ($this->permissions['invoices-download_email_status']) { ?>
                                                        <select class="ajaxify_status" name="download_email_status"
                                                                id="download_email_status-<?= $invoice['id'] ?>"
                                                                data-href="<? echo $lang; ?>/invoices/view/<? echo $invoice['id']; ?>.html">
                                                            <option
                                                                value="Sent"<?php if ($invoice['download_email_status'] == 'Sent') echo ' selected'; ?>>
                                                                Sent
                                                            </option>
                                                            <option
                                                                value="Not sent"<?php if ($invoice['download_email_status'] == 'Not sent') echo ' selected'; ?>>
                                                                Not sent
                                                            </option>
                                                        </select>
                                                    <?php } ?>
                                                    <? if ($this->permissions['invoices-download_email']) : ?>
                                                        <a class="btn ajaxify"
                                                           href="<?= $lang ?>/invoices/download_email/<?= $invoice['id'] ?>">Send</a>
                                                    <? endif; ?>
                                                    <? if ($this->permissions['invoices-download_link']) : ?>
                                                        <a class="btn generate_link"
                                                           href="<?= $lang ?>/invoices/download_link/<?= $invoice['id'] ?>">Generate
                                                            download link</a>
                                                        <br><br>
                                                        <span class="link_container"></span>
                                                        <script type="text/javascript">
                                                            $(document).ready(function () {
                                                                $('a.generate_link').click(function (e) {
                                                                    e.preventDefault();
                                                                    $.get($(this).attr('href'), function (data) {
                                                                        if (data.message)
                                                                            alert(data.message);
                                                                        if (data.link)
                                                                            $('.link_container').html('<a href="' + data.link + '">' + data.link + '</a>');
                                                                    }, 'json')
                                                                        .fail(function () {
                                                                            alert('error');
                                                                        });
                                                                    return false;
                                                                });
                                                            });
                                                        </script>
                                                    <? endif; ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

    <? endif; ?>
<? endforeach; else: ?>
    <tr>
        <td colspan="7" class="empty-list"><?= $this->lang->line('empty_list') ?></td>
    </tr>
<?endif ?>
</tbody>
</table>
</div>

</form>

<? if ($invoices) { ?>
    <script type="text/javascript" src="data/js/bootstrap-dropdown.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            $('.edit_statuses').click(function () {
                var cont = $(this).parents('.invoice-statuses').find('.statuses-select-cont');
                if (cont.css('display') == 'none') {
                    cont.show();
                    $(this).attr('src', 'data/img/admin/edit_statuses_hide.jpg')
                }
                else {
                    cont.hide();
                    $(this).attr('src', 'data/img/admin/edit_statuses.jpg')
                }
            });

            $('select.ajaxify_status').change(function () {
                var data = {},
                    idArr = $(this).attr('id').split('-'),
                    that = this,
                    href = $(this).data('href') ? $(this).data('href') : window.location.href;
                data[$(this).attr('name')] = $(this).val();
                if (idArr[1] !== undefined)
                    data['id'] = idArr[1];
                $.post(
                    href,
                    data,
                    function (data) {
                        var selected_status;
                        if (data.message)
                            alert(data.message);
                        if (data.success) {
                            if ($(that).attr('name') == 'status') {
                                var statuses_map = ['Not paid', 'Cancelled', 'Paid'];
                                selected_status = statuses_map[$(that).val() - 1];
                            }
                            else {
                                selected_status = $(that).val();
                            }
                            $(that).parents('.invoice-statuses').find('.selected_' + $(that).attr('name')).show().find('.status_value').text(selected_status);
                        }
                        if(data.color){
                            $(that).parents('td').css({"background-color": data.color});
                        }
                        else{
                            $(that).parents('td').removeAttr('style');
                        }
                    },
                    'json'
                )
            });
        });
    </script>
<? } ?>

<script type="text/javascript" src="/vendors/fck/fckeditor.js">
    var count = 0;
</script>