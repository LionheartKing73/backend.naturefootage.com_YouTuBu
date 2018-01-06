<div class="content_padding typography content_bg">
<?
if ($client) {
    if($rm_order){
    ?>
        <p>Thank you for your order.</p>
        <p>You can pay it after approving.</p>
    <?}
    elseif ($corporate) {
        if ($order_thx) {
            ?>
        <p>Thank you for your order.</p>
        <p>Please find link for download in <a href="<?=$lang?>/download.html" class="mand">Downloads section</a></p>
        <?
        } else {
            ?>
        <p>Sorry, your balance is not sufficient for payment of the order.</p><br>
        <p>
            Your balance: <? echo $currency, ' ', $balance ?><br>
            Cart amount: <? echo $currency, ' ', $total ?>
        <p>
        <?
        }
    } else {
        ?>
        <p><?=$this->lang->line('order_saved')?></p>
        <br />
        <?=$gateway?>
    <?
    }
} else {
    ?>
    <p><?=$this->lang->line('order_login');?> <a href="<?=$lang?>/register.html"> <?=$this->lang->line('order_register')?></a>.</p>
<?
}
?>
<div>
