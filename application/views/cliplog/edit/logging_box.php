<?php
/**
 * @var array $cliplog_template
 * @var array $cliplog_templates
 */


$templateUsed = !!( $cliplog_template );

$templateId = ( $cliplog_template ) ? $cliplog_template['id'] : NULL;
$templateName = ( $cliplog_template ) ? $cliplog_template['name'] : NULL;
$templateList = ( $cliplog_templates ) ? $cliplog_templates : array();
?>

<div class="cliplog-sidebar-box sidebar-padding">

    <div class="cliplog_sidebar_header">

        <h1>Logging template:</h1>

        <div class="header-template-name" data-template-id="<?php echo $templateId; ?>">
            <span class="cliplog_template_header"><?php
                if ($templateUsed) {
                    echo $templateName;
                } else {
                    ?>
                    Default Template
                <?php } ?></span>
            <input type="button" class="action save_logging_template" name="save_logging_template"  value="Save" style="display: none;" />
        </div>

    </div>

    <br />

    <div class="control-group" data-type="logging">

        <select name="applied_template_id" id="applied_template_id" class="cliplog_templates_list">
            <option disabled selected>- Select Template -</option>
            <option value="0">&nbsp;&nbsp;Default Template</option>
            <?php foreach ($templateList as $template) { ?>
                <option value="<?php echo $template['id']; ?>">&nbsp;&nbsp;<?php echo $template['name']; ?></option>
            <?php } ?>
        </select>

        <input type="hidden" id="apply_template" name="apply_template">
        <input type="button" class="action apply_logging_template" name="apply_logging_template" value="Apply" style="width: 40px;" />
        <input type="button" class="action delete_logging_template cliplog_delete_template" name="delete_logging_template" value="x" style="width: 18px; display: none;" />
    </div>

    <div class="control-group">
        <label class="control-label" for="tempalte_name">
            Save Layout as New Logging Template:
        </label>
        <input type="text" name="tempalte_name" id="tempalte_name" class="cliplog_template_name">
        <input type="submit" class="action cliplog_save_template" value="Create" name="save_template">
    </div>

</div>