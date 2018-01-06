<div id="feedbackPage">
<div id="leftPart"<?if ($errors) {?> style="width: 550px"<?}?>>
<?if ($submited) {?>
<p>Thank you. Your inquiry is being dealt with and we will respond very soon.</p>
<?} else {?>
<form method="post" id="feedbackForm">
  <p>Have a query?  Need help in finding the footage you need?  Send us your request and we will respond as quickly as possible.</p>
  
  <label for="name">Your name:<span class="mand">*</span></label>
  <input type="text" name="name" id="name" value="<?=$name?>" class="field">
  <?if ($errors['name']) {?><span class="mand"><?=$errors['name']?></span><?}?>

  <br class="clear"><br>
  
  <label for="email">Your email address:<span class="mand">*</span></label>
  <input type="text" name="email" id="email" value="<?=$email?>" class="field">
  <?if ($errors['email']) {?><span class="mand"><?=$errors['email']?></span><?}?>
  
  <br class="clear"><br>

  <label for="email">Your company:</label>
  <input type="text" name="company" id="email" value="<?=$company?>" class="field">
  <?if ($errors['company']) {?><span class="mand"><?=$errors['company']?></span><?}?>

  <br class="clear"><br>
  
  <label for="phone">Phone number:</label>
  <input type="text" name="phone" id="phone" value="<?=$phone?>" class="field">

  <br class="clear"><br>
  
  <label for="message">Your message:<span class="mand">*</span></label>
  <textarea name="message" id="message" cols="25" rows="5" class="field"><?=$message?></textarea>
  <?if ($errors['message']) {?><span class="mand" style="vertical-align: top"><?=$errors['message']?></span><?}?> 

  <br class="clear"><br>
  
  <label>&nbsp;</label>
  <button type="submit">Submit</button>
</form>
<?}?>
</div>

<div id="rightPart"<?if ($errors) {?> style="width: 380px"<?}?>>
<?=nl2br($this->config->item('vendor_address'))?>
<br>
<br>
<table border="0">
  <tr>
    <th>Email:</th><td><?=$this->config->item('vendor_email')?></td>
  </tr>
  <tr>
    <th>Phone:</th><td><?=$this->config->item('vendor_phone')?></td>
  </tr>
</table>
<br>
<?$this->config->item('vendor_name')?>
</div>
<br class="clear">
</div>