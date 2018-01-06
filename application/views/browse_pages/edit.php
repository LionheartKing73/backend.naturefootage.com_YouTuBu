<strong class="toolbar-item">
    <?=$this->lang->line('action')?>:
</strong>

<div class="btn-group toolbar-item">
    <?if($id && $this->permissions['browsepages-lists']){?>
        <a href="<?=$lang?>/browsepages/lists<?='/'.$id?>" class="btn">
            Lists
        </a>
    <? } ?>
</div>

<br class="clr">

<form action="<?= $lang ?>/browsepages/edit/<?=$id?>" method="post" class="form-horizontal well">
	<fieldset>
		<legend>
			<? if($id) echo 'EDIT PAGE'; else echo 'ADD PAGE'; ?>
		</legend>

        <div class="control-group">
            <label class="control-label" for="title">
                Title: <span class="mand">*</span>
            </label>
            <div class="controls">
                <input type="text" name="title" id="title" value="<?=$title?>">
                <input type="hidden" name="id" value="<?=$id?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="url">
                URL:
            </label>
            <div class="controls">
                <input type="text" name="url" id="url" value="<?=$url?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="meta_title">
                Meta title:
            </label>
            <div class="controls">
                <input type="text" name="meta_title" id="meta_title" value="<?=$meta_title?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="meta_keywords">
                Meta keywords:
            </label>
            <div class="controls">
                <input type="text" name="meta_keywords" id="meta_keywords" value="<?=$meta_keywords?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="meta_description">
                Meta description:
            </label>
            <div class="controls">
                <textarea name="meta_description" id="meta_description"><?=$meta_description?></textarea>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="body">
                Content:
            </label>
            <div class="controls">
                <textarea name="body" id="body"><?=$body?></textarea>
                <?=fck(750)?>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="thumbnail_url">
                Thumbnail URL:
            </label>
            <div class="controls">
                <input type="text" name="thumbnail_url" id="thumbnail_url" value="<?=$thumbnail_url?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="layout">
                Body Layout Options:
            </label>
            <div class="controls">

                <label class="radio inline">
                    <input type="radio" name="layout" value="text_left_movie_right"<?php if($layout == 'text_left_movie_right' || !$layout) echo ' checked';?>>Text Left, Movie Right
                </label>
                <label class="radio inline">
                    <input type="radio" name="layout" value="movie_top_text_bottom"<?php if($layout == 'movie_top_text_bottom') echo ' checked';?>>Movie Top / Text Bottom
                </label>
                <label class="radio inline">
                    <input type="radio" name="layout" value="no_movie"<?php if($layout == 'no_movie') echo ' checked';?>> No Movie at all
                </label>

            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="category_title">
                Page Category Title:
            </label>
            <div class="controls">
                <input type="text" name="category_title" id="category_title" value="<?=$category_title?>"> <img src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon" title="Shown at the top of the body area">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="sort">
                Sort:
            </label>
            <div class="controls">
                <input type="text" name="sort" id="sort" value="<?=$sort?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="status">
                Status
            </label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="status" value="1"<?if($status == 1 || !isset($status)) echo ' checked';?>>Active
                </label>
                <label class="radio inline">
                    <input type="radio" name="status" value="0"<?if(isset($status) && $status == 0) echo ' checked';?>>Inactive
                </label>
            </div>
        </div>

        <? if($is_admin && $providers) { ?>
            <div class="control-group">
                <label class="control-label" for="provider_id">
                    Provider
                </label>
                <div class="controls">
                    <select name="provider_id" id="provider_id">
                        <option value="0"></option>
                        <? foreach ($providers as $item) { ?>
                            <option value="<?=$item['id']?>"<?if ($item['id'] == $provider_id) echo ' selected'?>>
                                <?= $item['fname'] . ' ' . $item['lname']; ?>
                            </option>
                        <?}?>
                    </select>
                </div>
            </div>
        <? } ?>

        <legend>Video</legend>

        <div class="control-group">
            <label class="control-label" for="video_url">
                Video URL:
            </label>
            <div class="controls">
                <input type="text" name="video_url" id="video_url" value="<?=$video_url?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="video_width">
                Width:
            </label>
            <div class="controls">
                <input type="text" name="video_width" id="video_width" value="<?=$video_width?>">
                <img src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon" title="A normal video is set to 432 X 240">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="video_height">
                Height:
            </label>
            <div class="controls">
                <input type="text" name="video_height" id="video_height" value="<?=$video_height?>">
                <img src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon" title="A normal video is set to 432 X 240">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="video_autoplay">
                Autoplay:
            </label>
            <div class="controls">
                <input type="checkbox" id="video_autoplay" name="video_autoplay" <?if($video_autoplay){?>checked="checked"<?}?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="video_looping">
                Loop Movie
            </label>
            <div class="controls">
                <input type="checkbox" id="video_looping" name="video_looping" <?if($video_looping){?>checked="checked"<?}?>>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="video_sound">
                Audio
            </label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="video_sound" value="1"<?if($video_sound == 1 || !isset($video_sound)) echo ' checked';?>>Sound On
                </label>
                <label class="radio inline">
                    <input type="radio" name="video_sound" value="0"<?if(isset($video_sound) && $video_sound == 0) echo ' checked';?>>Mute
                </label>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="overlay_text">
                Overlay Text:
            </label>
            <div class="controls">
                <input type="text" name="overlay_text" id="overlay_text" value="<?=$overlay_text?>">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="text_under_video">
                Area under Movie <img src="/data/img/admin/cliplog/info_icon.jpg" class="info_icon" title="For adding links below movie, additional text, etc..."> :
            </label>
            <div class="controls">
                <textarea name="text_under_video" id="text_under_video"><?=$text_under_video?></textarea>
                <?=fck(750, 200, 'text_under_video')?>
            </div>
        </div>

		<div class="form-actions">
			<input type="submit" class="btn btn-primary" value="<?= $this->lang->line('save') ?>" name="save">
		</div>

	</fieldset>
</form>
