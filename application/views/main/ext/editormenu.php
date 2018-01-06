<table cellspacing="0" cellpadding="1" border="0" width="100%">
    <tr><td align="left">

        <?php
        $lib_settings = $this->api->settings();

        get_frontend_actions(array(
            array(
                'display' => $this->permissions['register-profile'],
                'url' => $lang.'/register/profile',
                'name' => $this->lang->line('profile')
            ),
            array(
                'display' => $this->permissions['invoices-show'],
                'url' => $lang.'/invoices/show',
                'name' => $this->lang->line('invoices')
            ),
            array(
                'display' => $this->permissions['download-items'],
                'url' => $lang.'/download',
                'name' => $this->lang->line('downloads')
            ),
            array(
                'display' => ($this->permissions['images'] && $lib_settings['use_imagelib']),
                'url' => $lang.'/images/view',
                'name' => $this->lang->line('images')
            ),
            array(
                'display' => ($this->permissions['clips'] && $lib_settings['use_cliplib']),
                'url' => $lang.'/clips/view',
                'name' => $this->lang->line('clips')
            ),
            array(
                'display' => ($this->permissions['images'] || $this->permissions['clips']),
                'url' => $lang.'/register/sales',
                'name' => 'Sales'
            )
        ));
        ?>

    </td>
        <!--<td align="right">

            <?php
            get_actions(array(
                array(
                    'display' => ($this->permissions['upload-loaded'] && $lib_settings['use_imagelib']),
                    'url' => $lang.'/upload/loaded/images',
                    'name' => $this->lang->line('upload_images')),
                array(
                    'display' => ($this->permissions['upload-loaded'] && $lib_settings['use_cliplib']),
                    'url' => $lang.'/upload/loaded/clips',
                    'name' => $this->lang->line('upload_clips'))
            ));
            ?>

        </td>-->
    </tr>
</table>
<br>