<? if($private_cat){?>
<script type="text/javascript">
    $(document).ready(function () {
        loginPopup();
    });
</script>
<?}?>
<!--<?php if ($price) { ?>
<div id="collectionPrice">
    Price: <?php echo $price ?>
    <form method="post" action="<?=$lang?>/cart/add/3/<?=$subcat_id?>">
        <button <?if ($col_in_cart){?>onclick="return showShortMsg('This item is already in your cart.');"<?}else{?>type="submit"<?}?> title="Add the item to your shopping cart">
            <span>Add to Cart</span>
            <img src="/data/img/cart_red.jpg" alt="cart" width="30" height="23" title="" align="absmiddle">
        </button>
    </form>
</div>
<?php } ?>-->


<? if ($subcat_id || $cat_id) { ?>

<?if(!$private_cat && $cat_description){?>
    <div class="result_description typography">
        <?=$cat_description?>
    </div>
<?}?>

<? } ?>

<div class="title_cont">
    <h1 class="page_title">
        <? if ($private_cat) { ?>
        <?= $this->lang->line('access_forbidden') ?>
        <script type="text/javascript">
            $(document).ready(function () {
                loginPopup();
            });
        </script>
        <? } else { ?>

        <? if ($cats) { ?>
            <? if ($cat_type == 'collection') { ?>
                Collection <span><?= $cats ?></span> includes <?= intval($all) ?> clips
                <? } else { ?>
                Your search for <span><?= $cats ?></span> returned <?= intval($all) ?> results
                <? } ?>
            <? } elseif ($associated) { ?>
            Associated with <span><?= $associated ?></span> returned <?= intval($all) ?> results
            <? } elseif ($phrase) { ?>
            Your search for <span><?= $phrase ?></span> returned <?= intval($all) ?> results
            <? } ?>

        <? }?>
    </h1>

    <? if ($subcat_id || $cat_id) { ?>

        <div class="back">
            <?if ($subcat_id) { ?>
            <a href="<?=$lang?>/<?=$cat_module?>/<?=$cat_uri?>.html"><?=$this->lang->line('back_to')?> <?=$cat_title?> <?=$back_type?> <?=$this->lang->line('page')?></a>
            <? } elseif ($cat_id) { ?>
            <a href="<?=$lang?>/<?=$cat_module?>.html"><?=$this->lang->line('back_categories_page')?></a>
            <? }?>
        </div>

    <? } ?>

    <div class="clear"></div>

</div>

<?if($results) {?>

<div class="results_pagination">
    <div class="count">
        <?=$this->lang->line('displaying')?> <?=$perpage?> <?=$this->lang->line('from')?> <?=intval($all)?>
    </div>
    <div class="pages">
        <?=$page_navigation?>
    </div>
    <div class="perpage">
        <form action="<?=$uri?>" method="post" name="perpage_form">
            <?=$this->lang->line('results_per_page')?>:
            <select name="perpage" onchange="perpage_form.submit();">
                <option value="4" <?if($perpage==4) echo "selected"?>>4</option>
                <option value="12" <?if($perpage==12) echo "selected"?>>12</option>
                <option value="20" <?if($perpage==20) echo "selected"?>>20</option>
            </select>
        </form>
    </div>
    <div class="clear"></div>
</div>

<div class="results">
    <?=$results?>
    <div id="addToCartForm" title="Add to cart" class="popup">
        <p class="message" style="display: none;"><?=$this->lang->line('required_fields_err')?>.</p>
        <form class="to_cart_form">
        </form>
    </div>
</div>

<div class="clear"></div>

<div class="results_pagination bottom_pagination">
    <div class="count">
        <?=$this->lang->line('displaying')?> <?=$perpage?> <?=$this->lang->line('from')?> <?=intval($all)?>
    </div>
    <div class="pages">
        <?=$page_navigation?>
    </div>
    <div class="perpage">
        <form action="<?=$uri?>" method="post" name="perpage_form">
            <?=$this->lang->line('results_per_page')?>:
            <select name="perpage" onchange="perpage_form.submit();">
                <option value="4" <?if($perpage==4) echo "selected"?>>4</option>
                <option value="12" <?if($perpage==12) echo "selected"?>>12</option>
                <option value="20" <?if($perpage==20) echo "selected"?>>20</option>
            </select>
        </form>
    </div>
    <div class="clear"></div>
</div>


<?} else {?>
    <div class="clear"></div>
    <div class="content_padding typography"><p><?=$this->lang->line('no_results')?>.</p></div>
<?}?>

<div class="clear"></div>
