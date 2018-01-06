<form name="invoices" action="<?=$lang?>/invoices/view/<?=$invoice['id']?>" method="post">
    <table class="table table-striped">
        <tr>
            <td>Imported to Filemaker</td>
            <td>
                <?php if ($this->permissions['invoices-imported_status']) { ?>
                    <select class="ajaxify" name="imported_status" id="imported_status-<?=$invoice['id']?>">
                        <option value="Imported"<?php if($invoice['imported_status'] == 'Imported') echo ' selected'; ?>>Imported</option>
                        <option value="Not imported"<?php if($invoice['imported_status'] == 'Not imported') echo ' selected'; ?>>Not imported</option>
                    </select>
                <?php } else { ?>
                    <?=$invoice['imported_status']?>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td>Order status</td>
            <td>
                <?php if ($this->permissions['invoices-admin_status']) { ?>
                    <select class="ajaxify" name="admin_status" id="admin_status-<?=$invoice['id']?>">
                        <option value=""></option>
                        <option value="Fillout"<?php if($invoice['admin_status'] == 'Fillout') echo ' selected'; ?>>Fillout</option>
                        <option value="Reassigned"<?php if($invoice['admin_status'] == 'Reassigned') echo ' selected'; ?>>Reassigned</option>
                        <option value="Accepted online"<?php if($invoice['admin_status'] == 'Accepted online') echo ' selected'; ?>>Accepted online</option>
                        <option value="Accepted offline"<?php if($invoice['admin_status'] == 'Accepted offline') echo ' selected'; ?>>Accepted offline</option>
                    </select>
                <?php } else { ?>
                    <?=$invoice['admin_status']?>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td>Payment status</td>
            <td>
                <?php if ($this->permissions['invoices-paymentstatus']) { ?>
                    <select class="ajaxify" name="status" id="status-<?=$invoice['id']?>">
                        <option value="1"<?php if($invoice['status'] == '1') echo ' selected'; ?>>Not paid</option>
                        <option value="3"<?php if($invoice['status'] == '3') echo ' selected'; ?>>Paid</option>
                        <option value="2"<?php if($invoice['status'] == '2') echo ' selected'; ?>>Failed</option>
                    </select>
                <?php } else { ?>
                    <?=$invoice['status_text']?>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td>Release status</td>
            <td>
                <?php if ($this->permissions['invoices-release_status']) { ?>
                    <select class="ajaxify" name="release_status" id="release_status-<?=$invoice['id']?>">
                        <option value=""></option>
                        <option value="Not approved"<?php if($invoice['release_status'] == 'Not approved') echo ' selected'; ?>>Not approved</option>
                        <option value="Approved"<?php if($invoice['release_status'] == 'Approved') echo ' selected'; ?>>Approved</option>
                        <option value="Preapproved"<?php if($invoice['release_status'] == 'Preapproved') echo ' selected'; ?>>Preapproved</option>
                        <option value="Preapproved no payment"<?php if($invoice['release_status'] == 'Preapproved no payment') echo ' selected'; ?>>Preapproved no payment</option>
                    </select>
                <?php } else { ?>
                    <?=$invoice['release_status']?>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td>Client status</td>
            <td>
                <?php if ($this->permissions['invoices-client_status']) { ?>
                    <select class="ajaxify" name="client_status" id="client_status-<?=$invoice['id']?>">
                        <option value="Review"<?php if($invoice['client_status'] == 'Review') echo ' selected'; ?>>Review</option>
                        <option value="Footage Uploading"<?php if($invoice['client_status'] == 'Footage Uploading') echo ' selected'; ?>>Footage Uploading</option>
                        <option value="Hold"<?php if($invoice['client_status'] == 'Hold') echo ' selected'; ?>>Hold</option>
                    </select>
                <?php } else { ?>
                    <?=$invoice['client_status']?>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <td>Resume order email sent</td>
            <td>
                <?php if ($this->permissions['invoices-resume_order_email_status']) { ?>
                    <select class="ajaxify" name="resume_order_email_status" id="resume_order_email_status-<?=$invoice['id']?>">
                        <option value="Sent"<?php if($invoice['resume_order_email_status'] == 'Sent') echo ' selected'; ?>>Sent</option>
                        <option value="Not sent"<?php if($invoice['resume_order_email_status'] == 'Not sent') echo ' selected'; ?>>Not sent</option>
                    </select>
                <?php } else { ?>
                    <?=$invoice['resume_order_email_status']?>
                <?php } ?>
                <? if ($this->permissions['invoices-resume_order_email']) : ?>
                    <br><a class="btn ajaxify" href="<?=$lang?>/invoices/resume_order_email/<?=$invoice['id']?>">Send</a>
                <? endif; ?>
            </td>
        </tr>
        <tr>
            <td>Download instructions sent</td>
            <td>
                <?php if ($this->permissions['invoices-download_email_status']) { ?>
                    <select class="ajaxify" name="download_email_status" id="download_email_status-<?=$invoice['id']?>">
                        <option value="Sent"<?php if($invoice['download_email_status'] == 'Sent') echo ' selected'; ?>>Sent</option>
                        <option value="Not sent"<?php if($invoice['download_email_status'] == 'Not sent') echo ' selected'; ?>>Not sent</option>
                    </select>
                <?php } else { ?>
                    <?=$invoice['download_email_status']?>
                <?php } ?>
                <br>
                <? if ($this->permissions['invoices-download_email']) : ?>
                    <a class="btn ajaxify" href="<?=$lang?>/invoices/download_email/<?=$invoice['id']?>">Send</a>
                <? endif; ?>
                <? if ($this->permissions['invoices-download_link']) : ?>
                    <a class="btn generate_link" href="<?=$lang?>/invoices/download_link/<?=$invoice['id']?>">Generate download link</a>
                    <br><br>
                    <span class="link_container"></span>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('a.generate_link').click(function(e){
                                e.preventDefault();
                                $.get($(this).attr('href'), function(data){
                                    if(data.message)
                                        alert(data.message);
                                    if(data.link)
                                        $('.link_container').html('<a href="' + data.link + '">' + data.link + '</a>');
                                }, 'json')
                                    .fail(function() { alert('error'); });
                                return false;
                            });
                        });
                    </script>
                <? endif; ?>
            </td>
        </tr>
    </table>
</form>