<form method="POST" action="<? echo $lang; ?>/users/sales_representatives">

    <div class="toolbar-item">
        <label for="rep_id">Sales Representative</label>
        <select name="rep_id">
            <option></option>
            <? foreach($admin_users as $admin_user){ ?>
                <option value="<? echo $admin_user['id']; ?>"><? echo $admin_user['fname']; ?></option>
            <? } ?>
        </select>
    </div>

    <div class="toolbar-item">
        <label for="fname">First name</label>
        <input type="text" name="fname" value="">
    </div>

    <div class="toolbar-item">
        <label for="lname">Last name</label>
        <input type="text" name="lname" value="">
    </div>

    <div class="toolbar-item">
        <input type="hidden" name="color" id="salesRepresentativesColor" value="">
        <div style="display:inline-block;">
            <strong>Select color</strong>
            <div class="color-box" data-target-id="salesRepresentativesColor"></div>
        </div>
    </div>
    <div class="clearfix"></div>
    <input type="submit" class="btn btn-primary" value="Add Sales Representative">
</form>

<table class="table table-striped">
    <thead>

    </thead>
    <tfoot></tfoot>
    <tbody>
    <tr>
        <th>
            First name
        </th>
        <th>
            Last Name
        </th>
        <th>
            Color
        </th>
        <th>Username</th>
        <th></th>
    </tr>
    <? foreach($sales_representatives as $rep){ ?>
        <tr>
            <td>
                <? echo $rep['fname']?>
            </td>
            <td>
                <? echo $rep['lname']?>
            </td>
            <td>
                <div class="color-box" style="background-color: <? echo $rep['color'];?>"></div>
            </td>
            <td>
                <? echo $rep['username']?>
            </td>
            <td>
                <a href="<? echo $lang;?>/users/delete_rep/<? echo $rep['id'];?>" >Delete</a>
            </td>
        </tr>
    <? } ?>
    </tbody>
</table>