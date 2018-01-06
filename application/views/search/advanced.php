<div class="title_cont">
    <h1 class="page_title">Advanced Search</h1>
    <div class="clear"></div>
</div>
<div class="typography">
<form method="post" onsubmit="return checkPhrase(true);" action="<?=$_SERVER['REQUEST_URI']?>">
  <div id="adv_search">
    <label for="words">Keyword(s):</label>
    <input type="text" name="phrase" value="<?=$phrase?>" id="words" class="field">

    <label for="exact">Exact phrase:</label>
    <input type="checkbox" name="exact" value="1" id="exact"<?if ($exact) echo ' checked="checked"'?>>

    <label for="hd_sd">Definition:</label>
    <select name="hd_sd" id="hd_sd">
      <option value="0">-- All --</option>
      <option value="1"<?if ($hd_sd == 1) echo ' selected="selected"'?>>HD</option>
      <option value="2"<?if ($hd_sd == 2) echo ' selected="selected"'?>>SD</option>
    </select>
    
    <label for="hd_sd">Aspect ratio:</label>
    <select name="ratio" id="ratio">
      <option value="0">-- All --</option>
      <option value="1"<?if ($ratio == 1) echo ' selected="selected"'?>>16:9</option>
      <option value="2"<?if ($ratio == 2) echo ' selected="selected"'?>>4:3</option>
    </select>

    <label for="model_release">Model release only:</label>
    <input type="checkbox" name="model_release" id="model_release" value="1"<?if ($model_release) echo ' checked="checked"'?>>

    <label for="property_release">Property release only:</label>
    <input type="checkbox" name="property_release" id="property_release" value="1"<?if ($property_release) echo ' checked="checked"'?>>

    <label>&nbsp;</label>
    <input type="submit" value="Find" class="action">
    <input type="reset" value="Reset" class="action">
  </div>
</form>
</div>