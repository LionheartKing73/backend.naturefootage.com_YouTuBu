<div id="headline">Inquiry</div>

<div id="back">
  <img src="data/img/back.gif" alt="&gt;" title="" width="14" height="18" align="absmiddle">
  <a href="<?=$continue?>">Back to search results</a>
</div>

<br class="clear">

<hr>

<?if ($clip_error) {?>

<div class="err"><?=$clip_error?></div>

<?} elseif($sent) {?>

Thank you for your inquiry; we&rsquo;ll answer you very soon.
<br><br>
World Stock Footage Customer Service Team

<?} else {?>

<form method="post" id="enquiry_form">

  <?if ($data_error) {?>
  <span class="err"><?=$data_error?><br>Please correct and try again.</span>
  <br class="clear"><br>
  <?}?>

  <label>Clip reference no.</label>
  <b><?=$clip['code']?></b>
  
  <br class="clear"><br>
  
  <label>Clip title</label>
  <b><?=$clip['title']?></b>
  
  <br class="clear"><br>
  
  <label for="name">Name<span class="mand">*</span></label>
  <input type="text" name="name" id="name" maxlength="100" value="<?=$posted['name']?>">
  
  <br class="clear"><br>
  
  <label for="phone">Phone number</label>
  <input type="text" name="phone" id="phone" maxlength="100" value="<?=$posted['phone']?>">
  
  <br class="clear"><br>
  
  <label for="email">Email address<span class="mand">*</span></label>
  <input type="text" name="email" id="email" maxlength="200" value="<?=$posted['email']?>">
  
  <br class="clear"><br>
  
  <label for="comment">Comment</label>
  <textarea name="comment" id="comment" rows="4" cols="25"><?=$posted['comment']?></textarea>

  <br class="clear"><br>
  
  <label>&nbsp;</label>
  <button type="submit" name="submit">Submit Inquiry</button>
  
  <br class="clear"><br>
  
  <span class="mand">*</span> required fields
</form>

<?}?>