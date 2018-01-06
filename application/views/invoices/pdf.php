<!DOCTYPE html>
<html>
<head>
    <title></title>
    <style>
        body {
            font-family: Verdana;
            font-size: 12px;
            line-height: 1.5;
        }

        table {
            border-collapse:collapse;
            border-spacing:0;
        }

        p{
            margin: 0 0 10px 0;
        }

        th, td{
            text-align: left;
            vertical-align: top;
            padding: 3px 10px 3px 0;
        }

        ul{

        }

        h2, h3 {
            font-size: 14px;
            margin: 10px 0;
        }

        table.license td, table.license th {
            border: 1px solid #000000;
            padding: 3px 5px;
        }

        table.license table td, table.license table th {
            border: none;
            padding: 0;
            vertical-align: bottom;
            padding-top: 10px;
        }

        table.license table td {
            border-bottom: 1px solid #000000;
        }

        table.item td, table.item th {
            padding: 3px 5px;
        }

    </style>
</head>
<body>
<table>
    <tr>
        <th>Date Issued:</th>
        <td><?php echo $date; ?></td>
    </tr>
</table>
<table width="100%">
    <tr>
        <th width="50%">Licensee:</th>
        <th>Bill To:</th>
    </tr>
    <tr>
        <td>
            <?php echo $license['name']; ?><br>
            <?php echo $license['company']; ?><br>
            <?php echo $license['street1'] ? $license['street1'] : ''; ?>
            <?php echo $license['street2'] ? ' ' . $license['street2'] : ''; ?><br>
            <?php
                echo $license['city'] ? $license['city'] : '';
                echo $license['state'] ? ', ' . $license['state'] : '';
                echo $license['zip'] ? ' ' .$license['zip'] : '';
            ?><br>
            <?php echo $license['country'] ? $license['country'] : ''; ?>
        </td>
        <td>
            <?php echo $billing['name']; ?><br>
            <?php echo $billing['company']; ?><br>
            <?php echo $billing['street1'] ? $billing['street1'] : ''; ?>
            <?php echo $billing['street2'] ? ' ' . $billing['street2'] : ''; ?><br>
            <?php
                echo $billing['city'] ? $billing['city'] : '';
                echo $billing['state'] ? ', ' . $billing['state'] : '';
                echo $billing['zip'] ? ' ' .$billing['zip'] : '';
            ?><br>
            <?php echo $billing['country'] ? $billing['country'] : ''; ?>
        </td>
    </tr>
</table>

<?php if($allowed_use) { ?>
<h2>Rights Managed License Terms:</h2>
<table>
    <tr>
        <th>Production Title:</th>
        <td><?php echo $license['production_title']; ?></td>
    </tr>
    <tr>
        <th>Description:</th>
        <td><?php echo $license['production_description']; ?></td>
    </tr>
    <tr>
        <th>Allowed Use(s):</th>
        <td>
            <?php echo $allowed_use; ?>
            <?php if($license['production_territory']) { ?>
                (<?php echo $license['production_territory']; ?>)
            <?php } ?>
        </td>
    </tr>
    <?php if($restrictions) { ?>
    <tr>
        <th>Restrictions:</th>
        <td><?php echo $restrictions; ?></td>
    </tr>
    <?php } ?>
</table>
<?php } ?>

<!--Nature Flix block-->
<?php if($nf_allowed_use) { ?>
    <h2>Rights Managed NatureFlix License Terms:</h2>
    <table>
        <tr>
            <th>Allowed Use(s):</th>
            <td>
                <?php echo $nf_allowed_use; ?>
                <?php if($license['production_territory']) { ?>
                    (<?php echo $license['production_territory']; ?>)
                <?php } ?>
            </td>
        </tr>
        <?php if($nf_restrictions) { ?>
            <tr>
                <th>Restrictions:</th>
                <td><?php echo $nf_restrictions; ?></td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<?php if($rf_allowed_use) { ?>
<h2>Royalty Free License Terms:</h2>
<table>
    <tr>
        <th>Allowed Use:</th>
        <td><?php echo $rf_allowed_use; ?></td>
    </tr>
</table>
<?php } ?>
<br>
<h2>Additional Notes:</h2>
<?php if($license['additional_notes']) { ?>
    <?php echo '<p>'.$license['additional_notes'].'</p>'; ?>
<?php } else { ?>
<p>
    Rights Managed clips may be used for a single edit in a single Production. If additional seconds are used then NatureFootage
    must be notified of, and give approval for, the final declaration prior to airing or other use of the additional seconds.
</p>
<?php } ?>
<br>
<table class="license" width="100%">
    <tr>
        <td rowspan="7" width="60%" style="vertical-align:top;">
            BY CLICKING ON THE "I ACCEPT" BUTTON ONLINE YOU AGREED TO
BE BOUND BY THE TERMS AND CONDITIONS OF THE NATUREFOOTAGE 
LICENSE AGREEMENT AND THAT YOU HAVE THE AUTHORITY TO DO SO. 
THIS INVOICE IN CONJUNCTION WITH THE NATUREFOOTAGE LICENSE 
AGREEMENT CONSTITUTES YOUR LICENSE.
        </td>
        <th width="30%" style="text-align: right; border-right: 0;">License Subtotal</th>
        <td style="border-left: 0;">$<?php echo $sum; ?></td>
    </tr>
    <tr>
        <th style="text-align: right; border-right: 0;">Model Release Fee</th>
        <td style="border-left: 0;">$0.00</td>
    </tr>
    <tr>
        <th style="text-align: right; border-right: 0;">Lab Fee</th>
        <td style="border-left: 0;">$<?php echo $delivery_cost; ?></td>
    </tr>
    <tr>
        <th style="text-align: right; border-right: 0;">Shipping Fee</th>
        <td style="border-left: 0;">&nbsp;</td>
    </tr>
    <tr>

        <?php ?>
        <th style="text-align: right; border-right: 0;">Order Total</th>
        <td style="border-left: 0;">$<?php echo $total; ?></td>
    </tr>
    <tr>
        <th style="text-align: right; border-right: 0;">Paid</th>
        <td style="border-left: 0;">$<?php echo ($status == 3) ? $total : '0.00'; ?></td>
    </tr>
    <tr>
        <th style="text-align: right; border-right: 0;">Balance Due (USD)</th>
        <td style="border-left: 0;">$<?php echo ($status == 3 ) ? '0.00' : $total; ?></td>
    </tr>
    <!--
    <tr>
        <td style="padding: 10px 20px 20px 10px;">
            <h3>Licensee:</h3>
            <table width="100%">
                <tr>
                    <th width="100">Printed Name:</th>
                    <td width="330">&nbsp;</td>
                </tr>
                <tr>
                    <th>Title:</th>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <th>Company:</th>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <th>Federal Tax ID#</th>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <th>Date:</th>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </td>
        <td colspan="2" style="padding: 10px 20px 20px 10px;">
            <h3>Licensor:</h3>
            <table width="100%">
                <tr>
                    <th width="100">Printed Name:</th>
                    <td width="170"><?php echo $licensor['name']; ?></td>
                </tr>
                <tr>
                    <th>Title:</th>
                    <td><?php echo $licensor['position']; ?></td>
                </tr>
                <tr>
                    <th>Company:</th>
                    <td><?php echo $licensor['company']; ?></td>
                </tr>
                <tr>
                    <th>Federal Tax ID#</th>
                    <td><?php echo $licensor['federal_tax_id']; ?></td>
                </tr>
                <tr>
                    <th>Date:</th>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>-->
</table>

<table width="100%" style="margin-top: 5px;">
    <tr>
        <td style="border: 1px solid #000000; text-align: center;">
            PAYMENT TERM: <?php echo ($status == 3 ) ? 'PAID IN FULL' : 'DUE UPON RECEIPT'; ?>
        </td>
    </tr>
</table>

<pagebreak />

<?php if($items) { ?>
<table width="100%">
    <?php foreach($items as $item) { ?>

    <tr>
        <td style="border-bottom: 1px solid #000044; padding: 10px 0;">
            <table class="item">
                <tr>
                    <td rowspan="9" width="220">
                        <?php if($item['thumb']) { ?>
                            <img src="<?php echo $item['thumb']; ?>" width="270" height="150">
                        <?php } ?>
                    </td>
                    <th width="100">Clip ID:</th>
                    <td><?php echo $item['code']; ?></td>
                </tr>
                <tr>
                    <th>Description:</th>
                    <td><?php echo $item['description']; ?></td>
                </tr>
                <?php if($item['license_restrictions']) { ?>
                    <tr>
                        <th>License Restrictions :</th>
                        <td><?php echo $item['license_restrictions']; ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <th>License Type:</th>
                    <td><?php echo $item['license_text']; ?> (See License Terms Above)
                    </td>
                </tr>
                <tr>
                    <th>Credit Line:</th>
                    <td>
                        <?php echo $item['provider_credits'] ? $item['provider_credits'] . ' / NatureFootage' : $item['company_name'] . ' / NatureFootage';  ?></td>
                </tr>
                <tr>
                    <th>Duration:</th>
                    <td><?php echo $item['duration'] ? $item['duration'] : floor($item['clip_duration']); ?> Seconds
                    </td>
                </tr>
                <tr>
                    <th>License Fee:</th>
                    <td>$<?php echo number_format($item['total_price'], 2); ?>
                        <?php if ($item['discount'] > 0) { ?>
                            ($<?php echo number_format($item['base_price'], 2) ?> with <?php echo number_format($item['discount'], 0); ?>% Discount)
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th>Release Fee:</th>
                    <td>No Model or Property Release Provided
                    </td>
                </tr>
                <tr>
                    <th>Delivery:</th>
                    <td><?php echo $item['df_description']; ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <?php } ?>
</table>
<?php } ?>
</body>
</html>
