<?foreach ($featured as $feature):?>
<div class="boxgrid caption">
    <a href="<?=$lang?>/<?=$feature['link']?>"><img src="<?=$feature['image']?>" width="337" /></a>
    <div class="cover boxcaption">
        <h3><a href="<?=$lang?>/<?=$feature['link']?>"><?=$feature['name']?></a></h3>
        <p><?=$feature['description']?></p>
    </div>
</div>
<?endforeach;?>