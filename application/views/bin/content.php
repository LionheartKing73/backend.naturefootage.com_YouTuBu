<div class="content_bg">
<div class="title_cont">
    <h1 class="page_title"><?=$this->lang->line('clip_bin')?></h1>
    <div class="back">
        <a href="<?=$continue?>"><?=$this->lang->line('back_to_search')?></a>
    </div>
    <div class="clear"></div>
</div>

<form name="lb" action="<?=$lang?>/bin/" method="post">

    <div id="bin_items">

        <?if($items):?>
        <div class="results"><?=$items?></div>
        <?else:?>
        <p><?=$this->lang->line('empty_bin')?></p>
        <?endif;?>

    </div>

    <div id="bin_manage">

        <h2><?=$this->lang->line('bin_manage')?></h2>

        <div id="bin_actions">
            <ul>
                <?if($bins):?>
                <li>
                    <?=$this->lang->line('bin_exist')?>:
                    <select name="bin" onchange="change_action(document.lb)">
                        <?foreach($bins as $id=>$name):?>
                        <option value="<?=$id?>" <?if($id==$current_bin) echo "selected";?>><?=$name?></option>
                        <?endforeach;?>
                    </select>
                </li>
                <?endif;?>
                <li>
                    <?=$this->lang->line('items')?>:
                    <?=$bin_count?><br>
                </li>

                <?if($client) {?>
                <li>
                    <a href="<?=$lang?>/bin/edit/<?=$current_bin?>" class="action"><?=$this->lang->line('bin_edit_link')?></a>
                </li>
                <li>
                    <a href="<?=$lang?>/bin/email/<?=$current_bin?>" class="action"><?=$this->lang->line('bin_send')?></a>
                </li>
                <li>
                    <button onclick="change_action(document.lb,'<?=$lang?>/bin/delete');" class="action">
                        <?=$this->lang->line('bin_delete')?>
                    </button>
                </li>
                <?}?>
                <li>
                    <button onclick="return select_all(document.lb, 'id[]');" class="action">
                        <?=$this->lang->line('select_all')?>
                    </button>
                </li>
                <li>
                    <button onclick="change_action(document.lb, '<?=$lang?>/bin/remove');" class="action">
                        <?=$this->lang->line('bin_remove')?>
                    </button>
                </li>
                <li>
                    <button onclick="binToCart();return false;" class="action">
                        <?=$this->lang->line('bin_tocart')?>
                    </button>
                </li>
                <li>
                    <a href="<?=$continue?>" class="action"><?=$this->lang->line('continue_shopping')?></a>
                </li>
                <?if(count($bins)>1 && $client):?>
                <li>
                    <?=$this->lang->line('bin_move')?>:
                    <select name="to" onchange="change_action(document.lb, '<?=$lang?>/bin/move')">
                        <?foreach($bins as $id=>$name):?>
                        <option value="<?=$id?>"<?if($id==$current_bin) echo " selected";?>>
                            <? echo $id==$current_bin ? '' : $name; ?></option>
                        <?endforeach;?>
                    </select>
                </li>
                <?endif;?>

                <?if($client):?>
                <li>
                    <a href="<?=$lang?>/bin/edit.html" class="action"><?=$this->lang->line('bin_add')?></a>
                </li>
                <?endif;?>
            </ul>
        </div>

    </div>

    <div class="clear"></div>

</form>
</div>

<div id="addToCartForm" title="Add to cart" class="popup">
    <p class="message" style="display: none;"><?=$this->lang->line('bin_add')?>.</p>
    <form class="to_cart_form">
    </form>
</div>