<?if($tree):?>
    <nav id="bottom_nav">
        <ul>
        <?foreach ($tree as $key=>$val):?>
            <li <? if(!isset($tree[$key+1])){echo 'class="last"';}?>><a href="<?=$lang.'/'.$val['link']?>"><?=$val['title']?></a></li>
        <?endforeach;?>
        </ul>
    </nav>
<?endif;?>
