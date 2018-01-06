<div class="admin-nav-cont">
    <ul class="admin-nav">
        <?php
        //           echo '<pre>';
        //         print_r($tree);
        //           echo '</pre>';
        ?>

        <?php
        foreach ($tree as $k => $v) {
            $i++;
            $childs = array();
            if (!$use_clips && $v['module'] == 'clips')
                continue;
            if (!$use_images && $v['module'] == 'images')
                continue;

            foreach ((array)$v['child'] as $val) {

                $module = str_replace('/', '-', $val['module']);
                $module = str_replace('-view', '', $module);
                if (strpos($module, '-') != strrpos($module, '-')) {
                    $module = substr($module, 0, strrpos($module, '-'));
                }

                //  echo $module.'<br>';
                if (key_exists($module, $this->permissions) && ($this->permissions[$module] == 1)) {
                    $childs[] = $val;
                }
            }

            if (count($childs)) {
                ?>
                <li>
                    <img src="data/img/admin/menu/<?= $v['icon'] ?>" width="16" height="16" alt="">
                    <?= $v['name'] ?>

                    <?php if ($v['child']) { ?>
                        <ul>
                            <?php
                            foreach ($childs as $val) {
                                $link = $lang . '/' . $val['module'] . '.html';
                                ?>
                                <li<?php if (trim($this->input->server('REQUEST_URI'), '/') == $link) { ?> class="active"<?php } ?>>
                                    <a href="<?= $link ?>"><?= $val['name'] ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </li>
                <?php
            }
        }
        ?>
        <li>
            <img src="data/img/admin/menu/users.gif" width="16" height="16" alt="">
            Account
            <ul>
                <li>
                    <!--                    <a href="http://www.nfstage.com/" target="_blank">-->
                    <!--                        Main Site-->
                    <!--                    </a>-->
                    <a href="http://www.nfstage.com/" target="_blank"> Main Site </a>
                </li>
                <li>
                    <a href="<?= $lang ?>/login/index/logout">
                        <?= $this->lang->line('logout') ?>
                    </a>
                </li>

            </ul>

        </li>


    </ul>
    <div class="clearfix"></div>
</div>