<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">
  <channel>
<?foreach($banners as $banner):?>
    <item>
      <media:content url="<?=$banner_path.$banner['resource']?>" type="<?=$banner['mime_type']?>" />
    </item>
<?endforeach;?>
  </channel>
</rss>