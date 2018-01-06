<div class="content_bg">
<div class="title_cont inner_title">
    <h1 class="page_title"><?=$this->lang->line('shopping_cart')?></h1>
    <div class="back">
        <a href="<?=$continue?>"><?=$this->lang->line('back_to_search')?></a>
    </div>
    <div class="clear"></div>
</div>

<div class="typography content_padding">
<?if($cart) {?>

<form name="fcart" action="<?=$lang?>/cart/checkout.html" method="post">

    <div id="cart_content">
        <table>
            <tr>
                <th>&nbsp;</th>
                <th align="left"><?=$this->lang->line('allowed_use');?></th>
                <th align="left" class="cart_item_start"><?=$this->lang->line('mark_in_position');?></th>
                <th align="left" class="cart_item_end"><?=$this->lang->line('mark_out_position');?></th>
                <th align="left" class="cart_item_end"><?=$this->lang->line('price');?></th>
                <th class="cart_item_actions">&nbsp;</th>
            </tr>
            <?
            $types = array(1=>'Image(s)', 2=>'Clip(s)', 3=>'Collection(s)', 4 => 'Timeline(s)');
            foreach($results as $type=>$result) {

                foreach($result['items'] as $item) {
                    if($type == 4){
                        $item['title'] = $this->lang->line('timeline') . ' ' .  $item['id'];
                    }
                    ?>
                    <tr>
                        <td>
                            <p><?=$item['title']?></p>
                            <?if($item['url']){?>
                                <a href="<?=$item['url']?>">
                                    <img alt="<?=$item['title']?>" title="<?=$item['title']?>" src="<?=$item['thumb']?>">
                                </a>
                            <?}?>
                        </td>
                        <td class="cart_item_usage">
                            <?if($item['usage']){?>
                                <p><?=$item['usage']?></p>
                            <?}?>
                        </td>
                        <td class="paddedtd">
                            <?if($item['start_time']){?>
                                <?=$item['start_time']?>
                            <?}?>
                        </td>
                        <td class="paddedtd">
                            <?if($item['end_time']){?>
                                <?=$item['end_time']?>
                            <?}?>
                        </td>
                        <td class="paddedtd">
                            <?if($item['price'] != 0.00){?>
                            <?=$item['price']?>
                            <?}?>
                        </td>
                        <td class="paddedtd">
                            <?if($item['url']){?>
                                <a title="<?=$this->lang->line('information');?>" href="<?=$item['url']?>"><img src="/data/img/info.png" alt="Information"></a>
                            <?}?>
                            <a title="<?=$this->lang->line('remove');?>" href="<?=$lang?>/cart/delete/<?=$item['cart_item_id']?>"><img src="/data/img/remove.png" alt="Remove"></a>
                        </td>
                    </tr>
                    <?
                }
            }
            ?>
        </table>
    </div>

    <div id="cart_details">
        <!--<?if (!empty($bin_items)) {?>
        <div id="addFromBin">
            <h2>Add Footage from Bin</h2>
                <?foreach ($bin_items as $item) {?>

                        <a class="binItemThumb" href="<?=$item['url']?>"><img src="<?=$item['thumb']?>" alt=""></a>
                        <br>
                        <?=$item['title']?>

                        <a href="/cart/add/<?=$item['type']?>/<?=$item['id']?>/refresh" title="Add to basket"
                           target="cart"><img src="/data/img/cart.gif" alt="Add to basket"></a>

                <?}?>
            <a class="action" href="/cart/addbin">Add all clips from this bin</a>
        </div>
        <?}?>-->

        <br>

        <h2><?=$this->lang->line('cart_details')?></h2>

        <table cellspacing="0" id="cartInfo">
            <tr>
                <th><?=$this->lang->line('items')?>:</th>
                <td><?=$cart['count']?></td>
            </tr>
            <?if($delivery_methods){?>
            <tr>
                <th><?=$this->lang->line('delivery_method')?>:</th>
                <td>
                    <select name="delivery" id="delivery" onchange="change_action(document.fcart);">
                        <?foreach($delivery_methods as $method){?>
                        <option value="<?=$method['id']?>"<?if ($method['id']==$delivery) echo ' selected'?>>
                            <?=str_replace(' ', '&nbsp;', str_pad($method['name'], 16, ' ')) . ' ' . ($method['cost'] > 0.00 ?
                            number_format($method['cost'], 2, '.', '') . ' ' . $currency : 'free')?>
                        </option>
                        <?}?>
                    </select>
                </td>
            </tr>
            <?}?>
        </table>




        <div class="clear"></div>

        <a id="continueShopping" href="<?=$continue?>" class="action"><?=$this->lang->line('continue_shopping')?></a>

        <!--<button type="sbmit" onclick="return chkout('<?=$this->lang->line('accept_terms')?>')" class="action">Checkout</button>-->
        <button type="submit" class="action"><?=$this->lang->line('cart_checkout')?></button>

    </div>
</form>

<div id="dialog" style="display: none">
    <iframe id="dlg_frame" style="border: 0px;" width="99%" height = "99%"></iframe>
</div>

<br class="clear">

<?}else{?>
    <p><?=$this->lang->line('empty_cart');?></p>
<?}?>
</div>
</div>
