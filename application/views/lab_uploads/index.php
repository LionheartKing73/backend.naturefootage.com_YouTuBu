<?
/**
 * @var array $config
 * @var string $home_path
 */
?>

<table class="table table-striped">
    <thead></thead>
    <tbody>
    <tr>
        <th>Order ID</th>
        <th>Order Items</th>
        <th>Upload link</th>
    </tr>
    <? foreach($orders as $order_id=>$order){ ?>
        <tr>
            <td><?=$order_id?></td>
            <td>
                <? foreach($order['items'] as $item){ ?>
                    ID:<?=$item['id']?>, Description:<?=$item['df_description']?><br>
                <? } ?>
            </td>
            <td>
                <? if($order['is_token_active']){ ?>
                    <a href="<?='//'.$order['host_name'].'/orders?action=uploads&token=' . $order['upload_token']?>" target="_blank">Upload</a></td>
                <? }else{ ?>
                    Token inactive
                <?}?>
        </tr>
    <? } ?>
    </tbody>
</table>