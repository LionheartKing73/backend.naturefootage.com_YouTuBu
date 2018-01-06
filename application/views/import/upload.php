<?php if ($menu) echo $menu ?>
<?php if (!empty($reader)) {
    $i = 0; ?>
    <!--<h3>Import preview</h3>-->
    <br>
    <!--<form method="post">
            <input type="hidden" name="file_name" value="<?php $file_name ?>">
            <input type="submit" name="import" value="Import" class="btn">
            <input type="button" value="Cancel" class="btn" onclick="location = location">
    </form>-->
    <table class="table table-striped">
        <h3>File Imported Successfully</h3>
        <?php /* while (($row = $reader->get_row()) != NULL) {?>

          <?php if ($i == 0) {?>
          <tr>
          <th>#</th>
          <?php foreach ($row as $key=>$value) {?>
          <?php if (in_array($key, $useful_cols)) {?>
          <th><?php $cols_map[$key]?></th>
          <?php }?>
          <?php }?>
          </tr>
          <?php } ++$i;?>
          <tr>
          <td><?php $i?></td>
          <?php foreach ($row as $key=>$value) {?>
          <?php if (in_array($key, $useful_cols)) {?>
          <td><?php $value?></td>
          <?php }?>
          <?php }?>
          </tr>
          <?php } */ ?>
    </table>
    <!--<form method="post">
            <input type="hidden" name="file_name" value="<?php $file_name ?>">
            <input type="submit" name="import" value="Import" class="btn">
            <input type="button" value="Cancel" class="btn" onclick="location = location">
    </form>-->
    <?} else {?>

    <form method="post" enctype="multipart/form-data">
        <label>
            Excel workbook:
            <input type="file" name="xl">
        </label>
        <input type="checkbox" name="overwrite_keywords">Overwrite keywords<br>
        <input type="submit" name="upload" value="Upload" class="btn">
        &nbsp;&nbsp;
        <a href="data/example/import.xls?201205241100">Example of Excel file</a>
    </form>

    <h3>Columns for import</h3>
    <table class="table" style="width: 400px">
        <tr>
            <th>Column header</th>
            <th>Clip attribute</th>
        </tr>
        <tr>
            <td>Code</td>
            <td>Code or Filename</td>
        </tr>

        <tr>
            <td>Notes</td>
            <td>Notes</td>
        </tr>
        <tr>
            <td>License restrictions</td>
            <td>License restrictions</td>
        </tr>
        <tr>
            <td>Video/Audio</td>
            <td>Video only, Audio only, etc</td>
        </tr>
        <tr>
            <td>Collection</td>
            <td>Land, Ocean,etc</td>
        </tr>
        <tr>
            <td>Month filmed</td>
            <td>Month of filmed</td>
        </tr>
        <tr>
            <td>Year filmed</td>
            <td>Year of filmed</td>
        </tr>
        <tr>
            <td>Master format</td>
            <td>HDCam, SDCam etc</td>
        </tr>
        <tr>
            <td>Master frame size</td>
            <td>HD (1920x1080), etc</td>
        </tr>
        <tr>
            <td>Master frame rate</td>
            <td>29.97 FPS Interlaced</td>
        </tr>
        <tr>
            <td>Lab</td>
            <td>Deluxe Media, etc </td>
        </tr>
        <tr>
            <td>License type</td>
            <td>Type of license</td>
        </tr>
        <tr>
            <td>Price level</td>
            <td>Standard, Gold, etc</td>
        </tr>
        <tr>
            <td>Releases</td>
            <td>Releases</td>
        </tr>
        <tr>
            <td>Description</td>
            <td>Description</td>
        </tr>
        <tr>
            <td>Camera model</td>
            <td>e.g 1100D</td>
        </tr>
        <tr>
            <td>Camera sensor</td>
            <td>1/4"</td>
        </tr>
        <tr>
            <td>Source bit depth</td>
            <td>8-bit</td>
        </tr>
        <tr>
            <td>Source chroma subsampling</td>
            <td>3:01:01</td>
        </tr>
        <tr>
            <td>Source format</td>
            <td>HDV</td>
        </tr>
        <tr>
            <td>Source codec</td>
            <td>AVC HD</td>
        </tr>
        <tr>
            <td>Source frame size</td>
            <td>SD 4:3 (720x486)</td>
        </tr>
        <tr>
            <td>Source frame rate</td>
            <td>23.98 FPS Progressive</td>
        </tr>
        <tr>
            <td>Submission codec</td>
            <td>DV (Cross Platform)</td>
        </tr>
        <tr>
            <td>Submission data rate</td>
            <td>Submission data value</td>
        </tr>
        <tr>
            <td>Submission frame size</td>
            <td>SD 4:3 (720x486)</td>
        </tr>
        <tr>
            <td>Submission frame rate</td>
            <td>23.98 FPS Progressive</td>
        </tr>

        <tr>
            <td>Shot type</td>
            <td>Comma separated keyword values</td>
        </tr>
        <tr>
            <td>Subject category</td>
            <td>Comma separated keyword values</td>
        </tr>
        <tr>
            <td>Primary subject</td>
            <td>Comma separated keyword values</td>
        </tr>
        <tr>
            <td>Other subject</td>
            <td>Comma separated keyword values</td>
        </tr>
        <tr>
            <td>Appearance</td>
            <td>Comma separated keyword values</td>
        </tr>
        <tr>
            <td>Actions</td>
            <td>Comma separated keyword values</td>
        </tr>
        <tr>
            <td>Time</td>
            <td>Comma separated keyword values</td>
        </tr>
        <tr>
            <td>Habitat</td>
            <td>Comma separated keyword values</td>
        </tr>
        <tr>
            <td>Concept</td>
            <td>Comma separated keyword values</td>
        </tr>
        <tr>
            <td>Location</td>
            <td>Comma separated keyword values</td>
        </tr>
        <tr>
            <td>Country</td>
            <td>Name of country</td>
        </tr>
    </table>

    <?}?>

    <?if($import_results){?>
    <h3>Import results:</h3>
    Total: <?= $import_results['total'] ?><br>
    Imported: <?= $import_results['imported'] ?><br>
    <?}?>

    <?if($import_errors){?>
    <span class="mand">
    <?= nl2br($import_errors) ?>
</span>
<?}?>