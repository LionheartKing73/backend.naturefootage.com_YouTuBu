<h2><?=$this->lang->line('popular_searches');?></h2>
<?foreach ((array)$tags as $tag):?>
  <a href="<?=$tag['link']?>" style="font-size:<?=$tag['size']?>px"><?=$tag['phrase']?></a>
<?endforeach;?>