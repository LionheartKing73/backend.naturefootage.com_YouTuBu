<script src="/data/js/jquery-impromptu.3.0.min.js" type="text/javascript"></script>
<script src="/data/js/urlEncode.js" type="text/javascript"></script>
<link href="/data/css/popup.css" type="text/css" rel="stylesheet">
<script type="text/javascript">

    $(document).ready(function(){

        var unregItems = <?=$has_unreg_bin_items?>;

        if(unregItems) {

            var action = 0;
            var bin_name = '';

            $.prompt(
                'There are already clips in your default bin. Please answer Yes to add new clips to existing one or NO to create new clipbin for new clips.',
                {
                    buttons: { Yes: 'yes', No: 'no' },
                    prefix: 'extended',
                    callback: function(answer, m, form){

                        if(answer == 'yes') {
                            action = '1';
                        } else if(answer == 'no') {

                            $.prompt('Please specify clip bin name: <input type="text" id="bin_name" name="bin_name" size=20 />', {
                                prefix: 'extended',
                                buttons: { Create : true },
                                submit: function(answer, m, form) {
                                    if(form.bin_name == '') {
                                        m.children('#bin_name').css("border","solid #ff0000 1px");
                                        return false;
                                    }
                                    return true;

                                },
                                callback: function(answer, m, form) {
                                    if(answer && form.bin_name) {
                                        bin_name = form.bin_name;
                                        window.location = '/bin/unreg/2/'+ $.URLEncode(bin_name);
                                    }
                                }
                            });

                        }

                        if(action > 0)
                            window.location = '/bin/unreg/'+action;
                    }
                });
        }
    });

</script>

<div class="content_padding content_bg">
<table cellspacing="0" cellpadding="1" border="0" width="100%">
    <tr>
        <td>
            <?if ($menu) echo $menu;?>
        </td>
    </tr>
    <tr>
        <td>
            <div class="typography">
                <p>
                    <b><?=$this->lang->line('profile');?></b> - <?=$this->lang->line('profile_info');?><br>
                    <b><?=$this->lang->line('invoices');?></b> - <?=$this->lang->line('invoices_info');?><br>
                </p>
            </div>
        </td>
    </tr>
</table>
</div>