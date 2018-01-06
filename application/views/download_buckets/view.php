<form action="" method="post" class="form-horizontal well">
    <fieldset>
        <table class="table table-striped">
            <tr>
                <th>
                    &nbsp;
                    <!--<input type="checkbox" name="sample" onclick="javascript:select_all(document.delivery);">-->
                </th>
                <th>Name</td> </th>
                <th>Size</td> </th>
            </tr>
            <?php
            foreach ($bucket_List as $data) {
                if ($data['Size'] > 0) {
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="values[]" value="<?= $data['Key'] ?>">
                        </td>
                        <td><?php
                            $var = explode('/', $data['Key']);
                            echo $var[2];
                            ?>
                        </td>
                        <td>
                            <?php
                            $var1 = $data['Size'] / 1024;
                            $varFinal = $var1 / 1024;
                            echo number_format($varFinal, 2, '.', '') . ' Mb';
                            ?>
                        </td>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
        <div class="form-actions">
            <input type="submit" class="btn btn-primary" value="Download Selected" name="downloadFiles">
        </div>
    </fieldset>
</form>

<script type="text/javascript">

    var downloadFiles = function() {
        for(var i=0; i<arguments.length; i++) {
            var iframe = $('<iframe style="display: none;"></iframe>');
            $('body').append(iframe);
            var content = iframe[0].contentDocument;
            var form = '<form action="' + arguments[i] + '" method="GET"></form>';
            content.write(form);
            $('form', content).submit();
            setTimeout((function(iframe) {
                return function() {
                    iframe.remove();
                }
            })(iframe), 2000);
        }
    }

</script>