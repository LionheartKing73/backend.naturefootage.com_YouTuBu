<?if ($visual_mode) {?>
<div class="boxout" onmouseover="show_bar(<?=$id?>,'block','<?=$lang?>')"
    onmouseout="hide_bar(<?=$id?>,'block','<?=$lang?>')" id="block<?=$id?>" style="margin: 5px 0">
<?}?>
<?=$content?>
<?if ($visual_mode) {?>
</div>
<?}?>