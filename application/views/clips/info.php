<table id="clip_info">
    <tr>
        <th><?=$this->lang->line('clip_title')?>:</th>
        <td><?=$clip['title']?></td>
    </tr>
    <?if($clip['duration']){?>
    <tr>
        <th><?=$this->lang->line('clip_duration')?>:</th>
        <td><?=$clip['duration']?></td>
    </tr>
    <?}?>
    <tr>
        <th><?=$this->lang->line('clip_resolution')?>:</th>
        <td><?=$clip['width'] . 'x' . $clip['height']?></td>
    </tr>
</table>