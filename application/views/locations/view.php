<script type="text/javascript">
  function selectLocation(src) {
    var id = src.value;
    if (!id)
      return;
    window.location.href = "/<?=$lang?>/locations/view/" + id;
  }

  function setId(locationType) {
    ctrl = document.getElementById(locationType);
    if (!ctrl) return false;
    id = ctrl.value;
    if (!id) {
      alert("Please select the " + locationType + " to edit.");
      return false;
    }
    document.forms.frmLocations.action = "/<?=$lang?>/locations/edit/" + id;
    return true;
  }

  function checkParent(locationType) {
    ctrl = document.getElementById(locationType);
    if (!ctrl) return false;
    id = ctrl.value;
    if (!id) {
      alert("Please select a " + locationType + ".");
      return false;
    }
    return true;
  }
  
  function deleteLocation(locationType) {
    ctrl = document.getElementById(locationType);
    if (!ctrl) return;
    id = ctrl.value;
    if (!id) {
      alert("Please select the " + locationType + " to delete.");
      return;
    }
    if (confirm("The location and its sublocations will be deleted.")) {
      location.href = "/<?=$lang?>/locations/delete/" + id;
    }
  }
</script>

<form method="post" action="/<?=$lang?>/locations/edit" name="frmLocations" class="form-horizontal well">
  <fieldset>
    <legend>
      Locations
    </legend>

    <div class="control-group">
      <label class="control-label" for="country">
        Country:
      </label>
      <div class="controls">
        <select name="country" id="country" onchange="selectLocation(this);">
          <option value="">--</option>
          <?foreach ($countries as $country) {?>
          <option value="<?=$country['id']?>"<?if ($country['id'] == $selected[0]) {?> selected="selected"<?}?>>
              <?=$country['name']?>
          </option>
          <?}?>
        </select>
      <?if ($this->permissions['locations-edit']) {?>
        <input type="submit" class="btn" name="edit_country" value="Edit" onclick="return setId('country');">
        <input type="submit" class="btn" name="add_country" value="Add">
        <input type="button" class="btn" value="Delete"
          onclick="deleteLocation('country');">
      <?}?>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="province">
        Province:
      </label>
      <div class="controls">
        <select name="province" id="province" onchange="selectLocation(this);">
          <option value="">--</option>
          <?foreach ($provinces as $province) {?>
          <option value="<?=$province['id']?>"<?if ($province['id'] == $selected[1]) {?> selected="selected"<?}?>>
              <?=$province['name']?>
          </option>
          <?}?>
        </select>
      <?if ($this->permissions['locations-edit']) {?>
        <input type="submit" class="btn" name="edit_province" value="Edit" onclick="return setId('province');">
        <input type="submit" class="btn" name="add_province" value="Add" onclick="return checkParent('country');">
        <input type="button" class="btn" value="Delete"
          onclick="deleteLocation('province');">
      <?}?>
      </div>
    </div>

    <div class="control-group">
      <label class="control-label" for="location">
        Location:
      </label>
      <div class="controls">
        <select name="location" id="location">
          <option value="">--</option>
          <?foreach ($cities as $location) {?>
          <option value="<?=$location['id']?>"><?=$location['name']?></option>
          <?}?>
        </select>
      <?if ($this->permissions['locations-edit']) {?>
        <input type="submit" class="btn" name="edit_location" value="Edit" onclick="return setId('location');">
        <input type="submit" class="btn" name="add_location" value="Add" onclick="return checkParent('province');">
        <input type="button" class="btn" value="Delete"
          onclick="deleteLocation('location');">
      <?}?>
      </div>
    </div>
  </fieldset>
</form>