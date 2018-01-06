<table class="table" style="float: left; margin-right: 40px; width: 320px">
  <tr><th><?=$this->lang->line('fname')?>: </th><td><?=$user['fname']?></td></tr>
  <tr><th><?=$this->lang->line('lname')?>: </th><td><?=$user['lname']?></td></tr>
  <tr><th><?=$this->lang->line('company')?>: </th><td><?=$user['company']?></td></tr>
  <tr><th><?=$this->lang->line('address')?>: </th><td><?=$user['address']?></td></tr>
  <tr><th><?=$this->lang->line('position')?>: </th><td><?=$user['position']?></td></tr>
  <tr><th><?=$this->lang->line('postcode')?>: </th><td><?=$user['postcode']?></td></tr>
  <tr><th><?=$this->lang->line('city')?>: </th><td><?=$user['city']?></td></tr>
  <tr><th><?=$this->lang->line('country')?>: </th><td><?=$user['country']?></td></tr>
  <tr><th><?=$this->lang->line('email')?>: </th><td><?=$user['email']?></td></tr>
  <tr><th><?=$this->lang->line('phone')?>: </th><td><?=$user['phone']?></td></tr>
  <tr><th><?=$this->lang->line('login')?>: </th><td><?=$user['login']?></td></tr>
  <tr><th><?=$this->lang->line('password')?>: </th><td><?=$user['password']?></td></tr>
</table>

<form action="<?=$lang?>/editors/details/<?=$id?>" method="post"
  class="form-horizontal well" style="float: left; width: 320px">
  <fieldset>
    <legend>
      <?=$this->lang->line('commision')?>:
    </legend>

    <div class="control-group">
      <label class="control-label" for="meta_desc">
        <?=$this->lang->line('commision')?>, %:
      </label>
      <div class="controls">
        <input type="text" value="<?=$user['commision']?>" name="commision" style="width: 60px">
      </div>
    </div>

    <div class="form-actions">
      <input type="submit" class="btn btn-primary" value="<?=$this->lang->line('save')?>" name="save">
    </div>
  </fieldset>
</form>

<br class="clr">