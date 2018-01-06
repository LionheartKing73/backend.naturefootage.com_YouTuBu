<?php if ($import_errors) { ?>
    <div class="alert alert-error">
        <?= $import_errors ?>
    </div>
<? } ?>
<?php if ($task) { ?>
    <p class="status-message">
        <?php if ($task['status'] == 0) { ?>
            The file is waiting to be processed
        <?php } elseif ($task['status'] == 2) { ?>
            The file has been processed
        <?php } ?>
    </p>
    <div class="progress progress-striped active"<?php if ($task['status'] !== 1) { ?> style="display: none;"<?php } ?>>
        <div class="bar" style="width: 0%;"></div>
    </div>
<?php } else { ?>
<form method="post" enctype="multipart/form-data">
    <div class="control-group">
        <label class="control-label" for="file">File</label>
        <div class="controls">
            <input type="file" name="file" id="file">
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <input type="submit" name="upload" value="Import" class="btn">
        </div>
    </div>
</form>
<?php } ?>
<?php if ($task && $task['status'] != 2) { ?>
<script>
    (function($) {
        var intervalID;
        var progress = $('.progress');
        var progressBar = progress.find('.bar');
        var statusMessage = $('.status-message');
        function getTaskStatus(id) {
            $.ajax({
                url: '/en/import/master/' + id,
                dataType: 'json'
            })
                .done(function(data) {
                    if (data.task) {
                        if (data.task.status == 2) {
                            clearInterval(intervalID);
                            statusMessage.text('The file has been processed');
                            progress.hide();
                            progressBar.width('0%');
                        } else if (data.task.status == 1) {
                            statusMessage.text('');
                            progress.show();
                            progressBar.width(data.task.progress + '%');
                        }
                    }
                });
        }
        $(document).ready(function() {
            intervalID = setInterval(function() { getTaskStatus(<?= $task['id'] ?>) }, 1000);
        });
    })(jQuery);
</script>
<?php } ?>