<?if($visual_mode && $this->permissions['publication-edit']):?>
<div class="boxout" onmouseover="show_bar(<?=$pageid?>,'page','<?=$lang?>')" onmouseout="hide_bar(<?=$pageid?>,'page','<?=$lang?>')" id="page<?=$pageid?>">
    <div class="typography">
        <?=$body?>
    </div>
</div>
<?else:?>
    <div class="typography">
        <?=$body?>
    </div>
<?endif;?>