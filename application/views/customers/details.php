<form name="invoices" action="<?=$lang?>/customers/details/<?=$id?>" method="post">

  <strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
  </strong>

  <input type="hidden" name="filter" value="1">

  <div class="btn-group toolbar-item">
<?if ($this->permissions['customers-invoices']):?>
    <a class="btn" href="<?=$lang?>/invoices/view/customer/<?=$id?>"><?=$this->lang->line('invoices')?></a>
<?endif?>
  </div>
  
  <br class="clr">

  <table class="table" style="float: left; margin-right: 40px; width: 320px">
    <tr>
      <th><?=$this->lang->line('fname')?>: </th>
      <td><?=$user['fname']?></td></tr>
    <tr>
      <th><?=$this->lang->line('lname')?>: </th>
      <td><?=$user['lname']?></td>
    </tr>
    <tr>
      <th><?=$this->lang->line('company')?>: </th>
      <td><?=$user['company']?></td></tr>
    <tr>
      <th><?=$this->lang->line('address')?>: </th>
      <td><?=$user['address']?></td>
    </tr>
    <tr>
      <th><?=$this->lang->line('position')?>: </th>
      <td><?=$user['position']?></td>
    </tr>
    <tr>
      <th><?=$this->lang->line('postcode')?>: </th>
      <td><?=$user['postcode']?></td>
    </tr>
    <tr>
      <th><?=$this->lang->line('city')?>: </th>
      <td><?=$user['city']?></td>
    </tr>
    <tr>
      <th><?=$this->lang->line('country')?>: </th>
      <td><?=$user['country']?></td>
    </tr>
    <tr>
      <th><?=$this->lang->line('email')?>: </th>
      <td><?=$user['email']?></td>
    </tr>
    <tr>
      <th><?=$this->lang->line('phone')?>: </th>
      <td><?=$user['phone']?></td>
    </tr>
    <tr>
      <th><?=$this->lang->line('login')?>: </th>
      <td><?=$user['login']?></td>
    </tr>
    <tr>
      <th><?=$this->lang->line('password')?>: </th>
      <td><?=$user['password']?></td>
    </tr>
  </table>

  <form action="<?=$lang?>/customers/details/<?=$id?>" method="post"
    style="float: left; width: auto">
    <table class="table" style="width: 220px">
      <caption><?=$this->lang->line('corporate_account')?>:</caption>
     <tr>
       <th><?=$this->lang->line('active')?>: </th>
       <td><input type="checkbox" name="corporate_active" value="1" <?if($user['corporate_active']) echo "checked"?>></td>
     </tr>
     <tr>
       <th><?=$this->lang->line('discount')?>, %: </th>
       <td><input type="text" value="<?=$user['corporate_discount']?>" name="corporate_discount" style="width: 60px"></td>
     </tr>
     <tr>
       <th><?=$this->lang->line('balance')?>, <?=$user['currency']?>: </th>
       <td><input type="text" value="<?=$user['corporate_balance']?>" name="corporate_balance" style="width: 60px"></td>
     </tr>
    <tr>
      <td> </td>
      <td>
        <input type="submit" value="<?=$this->lang->line('save')?>" name="save" class="btn btn-primary">
      </td>
    </tr>
    </table>
  </form>
</form>
<br class="clr">