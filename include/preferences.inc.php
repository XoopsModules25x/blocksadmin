<?php

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright     {@link https://xoops.org/ XOOPS Project}
 * @license       {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author        Kazumi Ono (AKA onokazu) http://www.myweb.ne.jp/, https://xoops.org/, http://jp.xoops.org/
 * @author        XOOPS Development Team
 */

use XoopsModules\Blocksadmin\Helper;
/** @var Helper $helper */
/** @var \XoopsConfigHandler $configHandler */

$configHandler = xoops_getHandler('config');

if (!is_object($xoopsUser) || !is_object($xoopsModule) || !$xoopsUser->isAdmin($xoopsModule->mid())) {
    exit('Access Denied');
}
$op = 'list';
if (!empty($_POST['op'])) {
    $op = $_POST['op'];
}
if (isset($_GET['op'])) {
    $op = trim($_GET['op']);
}
if (isset($_GET['confcat_id'])) {
    $confcat_id = (int)$_GET['confcat_id'];
}
/*  if ($op == 'list') {
    $confcatHandler = xoops_getHandler('configcategory');
    $confcats =& $confcatHandler->getObjects();
    $catcount = count($confcats);
    xoops_cp_header();
    echo '<h4 style="text-align:left">'._MD_AM_SITEPREF.'</h4><ul>';
    for ($i = 0; $i < $catcount; ++$i) {
      echo '<li>'.constant($confcats[$i]->getVar('confcat_name')).' [<a href="admin.php?fct=preferences&amp;op=show&amp;confcat_id='.$confcats[$i]->getVar('confcat_id').'">'._EDIT.'</a>]</li>';
    }
    echo '</ul>';
    xoops_cp_footer();
    exit();
  } */

/*   if ($op == 'show') {
    if (empty($confcat_id)) {
      $confcat_id = 1;
    }
    $confcatHandler = xoops_getHandler('configcategory');
    $confcat =& $confcatHandler->get($confcat_id);
    if (!is_object($confcat)) {
      redirect_header('admin.php?fct=preferences', 1);
    }
    require_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
    require_once XOOPS_ROOT_PATH.'/class/xoopslists.php';
    $form = new XoopsThemeForm(constant($confcat->getVar('confcat_name')), 'pref_form', 'admin.php?fct=preferences');

$configHandler = xoops_getHandler('config');
$criteria      = new CriteriaCompo();
$criteria->add(new Criteria('conf_modid', 0));
$criteria->add(new Criteria('conf_catid', $confcat_id));
$config    =& $configHandler->getConfigs($criteria);
$confcount = count($config);
for ($i = 0; $i < $confcount; ++$i) {
    $title = (!defined($config[$i]->getVar('conf_desc')) || constant($config[$i]->getVar('conf_desc')) == '') ? constant($config[$i]->getVar('conf_title')) : constant($config[$i]->getVar('conf_title'))
                                                                                                                                                              . '<br><br><span style="font-weight:normal;">'
                                                                                                                                                              . constant($config[$i]->getVar('conf_desc'))
                                                                                                                                                              . '</span>';
    switch ($config[$i]->getVar('conf_formtype')) {
        case 'textarea':
            $myts = MyTextSanitizer::getInstance();
            if ($config[$i]->getVar('conf_valuetype') == 'array') {
                // this is exceptional.. only when value type is arrayneed a smarter way for this
                $ele = ($config[$i]->getVar('conf_value') != '') ? new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), htmlspecialchars(implode('|', $config[$i]->getConfValueForOutput())), 5, 50) : new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), '', 5, 50);
            } else {
                $ele = new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), htmlspecialchars($config[$i]->getConfValueForOutput()), 5, 50);
            }
            break;
        case 'select':
            $ele     = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
            $options =& $configHandler->getConfigOptions(new Criteria('conf_id', $config[$i]->getVar('conf_id')));
            $opcount = count($options);
            for ($j = 0; $j < $opcount; $j++) {
                $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');
                $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');
                $ele->addOption($optval, $optkey);
            }
            break;
        case 'select_multi':
            $ele     = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), 5, true);
            $options =& $configHandler->getConfigOptions(new Criteria('conf_id', $config[$i]->getVar('conf_id')));
            $opcount = count($options);
            for ($j = 0; $j < $opcount; $j++) {
                $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');
                $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');
                $ele->addOption($optval, $optkey);
            }
            break;
        case 'yesno':
            $ele = new XoopsFormRadioYN($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), _YES, _NO);
            break;
        case 'theme':
        case 'theme_multi':
            $ele     = ($config[$i]->getVar('conf_formtype') != 'theme_multi') ? new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput()) : new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), 5, true);
            $handle  = opendir(XOOPS_THEME_PATH . '/');
            $dirlist = [];
            while (false !== ($file = readdir($handle))) {
                if (is_dir(XOOPS_THEME_PATH . '/' . $file) && !preg_match('/^[.]{1,2}$/', $file) && strtolower($file) != 'cvs') {
                    $dirlist[$file] = $file;
                }
            }
            closedir($handle);
            if (!empty($dirlist)) {
                asort($dirlist);
                $ele->addOptionArray($dirlist);
            }
            //$themesetHandler = xoops_getHandler('themeset');
            //$themesetlist =& $themesetHandler->getList();
            //asort($themesetlist);
            //foreach ($themesetlist as $key => $name) {
            //  $ele->addOption($key, $name.' ('._MD_AM_THEMESET.')');
            //}
            // old theme value is used to determine whether to update cache or not. kind of dirty way
            $form->addElement(new XoopsFormHidden('_old_theme', $config[$i]->getConfValueForOutput()));
            break;
        case 'tplset':
            $ele           = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
            $tplsetHandler = xoops_getHandler('tplset');
            $tplsetlist    =& $tplsetHandler->getList();
            asort($tplsetlist);
            foreach ($tplsetlist as $key => $name) {
                $ele->addOption($key, $name);
            }
            // old theme value is used to determine whether to update cache or not. kind of dirty way
            $form->addElement(new XoopsFormHidden('_old_theme', $config[$i]->getConfValueForOutput()));
            break;
        case 'timezone':
            $ele = new XoopsFormSelectTimezone($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
            break;
        case 'language':
            $ele = new XoopsFormSelectLang($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
            break;
        case 'startpage':
            $ele = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());

            $moduleHandler = xoops_getHandler('module');
            $criteria      = new CriteriaCompo(new Criteria('hasmain', 1));
            $criteria->add(new Criteria('isactive', 1));
            $moduleslist       = $moduleHandler->getList($criteria, true);
            $moduleslist['--'] = _MD_AM_NONE;
            $ele->addOptionArray($moduleslist);
            break;
        case 'group':
            $ele = new XoopsFormSelectGroup($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
            break;
        case 'group_multi':
            $ele = new XoopsFormSelectGroup($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
            break;
        // RMV-NOTIFY - added 'user' and 'user_multi'
        case 'user':
            $ele = new XoopsFormSelectUser($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
            break;
        case 'user_multi':
            $ele = new XoopsFormSelectUser($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
            break;
        case 'module_cache':
            $moduleHandler = xoops_getHandler('module');
            $modules       = $moduleHandler->getObjects(new Criteria('hasmain', 1), true);
            $currrent_val  = $config[$i]->getConfValueForOutput();
            $cache_options = ['0' => _NOCACHE, '30' => sprintf(_SECONDS, 30), '60' => _MINUTE, '300' => sprintf(_MINUTES, 5), '1800' => sprintf(_MINUTES, 30), '3600' => _HOUR, '18000' => sprintf(_HOURS, 5), '86400' => _DAY, '259200' => sprintf(_DAYS, 3), '604800' => _WEEK];
            if (count($modules) > 0) {
                $ele = new XoopsFormElementTray($title, '<br>');
                foreach (array_keys($modules) as $mid) {
                    $c_val   = isset($currrent_val[$mid]) ? intval($currrent_val[$mid]) : null;
                    $selform = new XoopsFormSelect($modules[$mid]->getVar('name'), $config[$i]->getVar('conf_name') . "[$mid]", $c_val);
                    $selform->addOptionArray($cache_options);
                    $ele->addElement($selform);
                    unset($selform);
                }
            } else {
                $ele = new XoopsFormLabel($title, _MD_AM_NOMODULE);
            }
            break;
        case 'site_cache':
            $ele = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
            $ele->addOptionArray(['0' => _NOCACHE, '30' => sprintf(_SECONDS, 30), '60' => _MINUTE, '300' => sprintf(_MINUTES, 5), '1800' => sprintf(_MINUTES, 30), '3600' => _HOUR, '18000' => sprintf(_HOURS, 5), '86400' => _DAY, '259200' => sprintf(_DAYS, 3), '604800' => _WEEK]);
            break;
        case 'password':
            $myts = MyTextSanitizer::getInstance();
            $ele  = new XoopsFormPassword($title, $config[$i]->getVar('conf_name'), 50, 255, htmlspecialchars($config[$i]->getConfValueForOutput()));
            break;
        case 'textbox':
        default:
            $myts = MyTextSanitizer::getInstance();
            $ele  = new XoopsFormText($title, $config[$i]->getVar('conf_name'), 50, 255, htmlspecialchars($config[$i]->getConfValueForOutput()));
            break;
    }
    $hidden = new XoopsFormHidden('conf_ids[]', $config[$i]->getVar('conf_id'));
    $form->addElement($ele);
    $form->addElement($hidden);
    unset($ele);
    unset($hidden);
}
$form->addElement(new XoopsFormHidden('op', 'save'));
$xoopsGTicket->addTicketXoopsFormElement($form, __LINE__);
$form->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
xoops_cp_header();
echo '<a href="admin.php?fct=preferences">' . _MD_AM_PREFMAIN . '</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;' . constant($confcat->getVar('confcat_name')) . '<br><br>';
$form->display();
xoops_cp_footer();
exit();
} */

if ('showmod' === $op) {
    $mod = isset($_GET['mod']) ? (int)$_GET['mod'] : 0;
    if (empty($mod)) {
        header('Location: admin.php?fct=preferences');
        exit();
    }
    $config = &$configHandler->getConfigs(new Criteria('conf_modid', $mod));
    $count  = count($config);
    if ($count < 1) {
        redirect_header('admin.php?fct=preferences', 1);
    }
    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
    $form = new XoopsThemeForm(_MD_AM_MODCONFIG, 'pref_form', 'admin.php?fct=preferences');
    $moduleHandler = xoops_getHandler('module');
    $module        = $moduleHandler->get($mod);
    //    if (file_exists(XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/modinfo.php')) {
    //        require_once XOOPS_ROOT_PATH . '/modules/' . $module->getVar('dirname') . '/language/' . $xoopsConfig['language'] . '/modinfo.php';
    //    }

    $helper = Helper::getInstance();
    $helper->loadLanguage('modinfo');

    // if has comments feature, need comment lang file
    if (1 == $module->getVar('hascomments')) {
        require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/comment.php';
    }
    // RMV-NOTIFY
    // if has notification feature, need notification lang file
    if (1 == $module->getVar('hasnotification')) {
        require_once XOOPS_ROOT_PATH . '/language/' . $xoopsConfig['language'] . '/notification.php';
    }

    $modname     = $module->getVar('name');
    $button_tray = new XoopsFormElementTray('');
    if ($module->getInfo('adminindex')) {
        //      $form->addElement(new XoopsFormHidden('redirect', XOOPS_URL.'/modules/'.$module->getVar('dirname').'/'.$module->getInfo('adminindex')));
        $button_tray->addElement(new XoopsFormHidden('redirect', XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/admin/admin.php?fct=preferences&op=showmod&mod=' . $module->getVar('mid'))); // GIJ Patch
    }
    for ($i = 0; $i < $count; ++$i) {
        $title4tray = (!defined($config[$i]->getVar('conf_desc')) || '' == constant($config[$i]->getVar('conf_desc'))) ? constant($config[$i]->getVar('conf_title')) : constant($config[$i]->getVar('conf_title')) . '<br><br><span style="font-weight:normal;">' . constant(
                $config[$i]->getVar('conf_desc')
            ) . '</span>'; // GIJ
        $title      = ''; // GIJ
        switch ($config[$i]->getVar('conf_formtype')) {
            case 'textarea':
                $myts = MyTextSanitizer::getInstance();
                if ('array' === $config[$i]->getVar('conf_valuetype')) {
                    // this is exceptional.. only when value type is arrayneed a smarter way for this
                    $ele = ('' != $config[$i]->getVar('conf_value')) ? new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), htmlspecialchars(implode('|', $config[$i]->getConfValueForOutput()), ENT_QUOTES | ENT_HTML5), 5, 50) : new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), '', 5, 50);
                } else {
                    $ele = new XoopsFormTextArea($title, $config[$i]->getVar('conf_name'), htmlspecialchars($config[$i]->getConfValueForOutput(), ENT_QUOTES | ENT_HTML5), 5, 50);
                }
                break;
            case 'select':
                $ele     = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
                $options = &$configHandler->getConfigOptions(new Criteria('conf_id', $config[$i]->getVar('conf_id')));
                $opcount = count($options);
                for ($j = 0; $j < $opcount; $j++) {
                    $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');
                    $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');
                    $ele->addOption($optval, $optkey);
                }
                break;
            case 'select_multi':
                $ele     = new XoopsFormSelect($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), 5, true);
                $options = &$configHandler->getConfigOptions(new Criteria('conf_id', $config[$i]->getVar('conf_id')));
                $opcount = count($options);
                for ($j = 0; $j < $opcount; $j++) {
                    $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');
                    $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');
                    $ele->addOption($optval, $optkey);
                }
                break;
            case 'yesno':
                $ele = new XoopsFormRadioYN($title, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), _YES, _NO);
                break;
            case 'group':
                require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                $ele = new XoopsFormSelectGroup($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
                break;
            case 'group_multi':
                require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                $ele = new XoopsFormSelectGroup($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
                break;
            // RMV-NOTIFY: added 'user' and 'user_multi'
            case 'user':
                require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                $ele = new XoopsFormSelectUser($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
                break;
            case 'user_multi':
                require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
                $ele = new XoopsFormSelectUser($title, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
                break;
            case 'password':
                $myts = MyTextSanitizer::getInstance();
                $ele  = new XoopsFormPassword($title, $config[$i]->getVar('conf_name'), 50, 255, htmlspecialchars($config[$i]->getConfValueForOutput(), ENT_QUOTES | ENT_HTML5));
                break;
            case 'textbox':
            default:
                $myts = MyTextSanitizer::getInstance();
                $ele  = new XoopsFormText($title, $config[$i]->getVar('conf_name'), 50, 255, htmlspecialchars($config[$i]->getConfValueForOutput(), ENT_QUOTES | ENT_HTML5));
                break;
        }
        $hidden   = new XoopsFormHidden('conf_ids[]', $config[$i]->getVar('conf_id'));
        $ele_tray = new XoopsFormElementTray($title4tray, '');
        $ele_tray->addElement($ele);
        $ele_tray->addElement($hidden);
        $form->addElement($ele_tray);
        unset($ele_tray);
        unset($ele);
        unset($hidden);
    }
    $button_tray->addElement(new XoopsFormHidden('op', 'save'));
    $GLOBALS['xoopsSecurity']->getTokenHTML();
    $button_tray->addElement(new XoopsFormButton('', 'button', _GO, 'submit'));
    $form->addElement($button_tray);
    xoops_cp_header();
    // GIJ patch start
    require __DIR__ . '/mymenu.php';
    echo "<h3 style='text-align:left;'>" . $module->getVar('name') . ' &nbsp; ' . _PREFERENCES . "</h3>\n";
    // GIJ patch end
    $form->display();
    xoops_cp_footer();
    exit();
}

if ('save' === $op) {
    //if ( !admin_refcheck("/modules/$admin_mydirname/admin/") ) {
    //  exit('Invalid referer');
    //}
    if (!$GLOBALS['xoopsSecurity']->check(true, 'mymenu')) {
        redirect_header(XOOPS_URL . '/', 3, $GLOBALS['xoopsSecurity']->getErrors());
    }
    require_once XOOPS_ROOT_PATH . '/class/template.php';
    $xoopsTpl = new XoopsTpl();
    $xoopsTpl->clear_all_cache();
    // regenerate admin menu file
    xoops_module_write_admin_menu(xoops_module_get_admin_menu());
    if (!empty($_POST['conf_ids'])) {
        $conf_ids = $_POST['conf_ids'];
    }
    $count            = count($conf_ids);
    $tpl_updated      = false;
    $theme_updated    = false;
    $startmod_updated = false;
    $lang_updated     = false;
    if ($count > 0) {
        for ($i = 0; $i < $count; ++$i) {
            $config    = &$configHandler->getConfig($conf_ids[$i]);
            $new_value = &$_POST[$config->getVar('conf_name')];
            if (is_array($new_value) || $new_value != $config->getVar('conf_value')) {
                // if language has been changed
                if (!$lang_updated && XOOPS_CONF == $config->getVar('conf_catid') && 'language' === $config->getVar('conf_name')) {
                    // regenerate admin menu file
                    $xoopsConfig['language'] = $_POST[$config->getVar('conf_name')];
                    xoops_module_write_admin_menu(xoops_module_get_admin_menu());
                    $lang_updated = true;
                }

                // if default theme has been changed
                if (!$theme_updated && XOOPS_CONF == $config->getVar('conf_catid') && 'theme_set' === $config->getVar('conf_name')) {
                    /** @var \XoopsMemberHandler $memberHandler */
                    $memberHandler = xoops_getHandler('member');
                    $memberHandler->updateUsersByField('theme', $_POST[$config->getVar('conf_name')]);
                    $theme_updated = true;
                }

                // if default template set has been changed
                if (!$tpl_updated && XOOPS_CONF == $config->getVar('conf_catid') && 'template_set' === $config->getVar('conf_name')) {
                    // clear cached/compiled files and regenerate them if default theme has been changed
                    if ($xoopsConfig['template_set'] != $_POST[$config->getVar('conf_name')]) {
                        $newtplset = $_POST[$config->getVar('conf_name')];

                        // clear all compiled and cachedfiles
                        $xoopsTpl->clear_compiled_tpl();

                        // generate compiled files for the new theme
                        // block files only for now..
                        /** @var \XoopsTplfileHandler $tplfileHandler */
                        $tplfileHandler = xoops_getHandler('tplfile');
                        $dtemplates     = $tplfileHandler->find('default', 'block');
                        $dcount         = count($dtemplates);

                        // need to do this to pass to xoops_template_touch function
                        $GLOBALS['xoopsConfig']['template_set'] = $newtplset;

                        for ($i = 0; $i < $dcount; ++$i) {
                            $found = $tplfileHandler->find($newtplset, 'block', $dtemplates[$i]->getVar('tpl_refid'), null);
                            if (count($found) > 0) {
                                // template for the new theme found, compile it
                                xoops_template_touch($found[0]->getVar('tpl_id'));
                            } else {
                                // not found, so compile 'default' template file
                                xoops_template_touch($dtemplates[$i]->getVar('tpl_id'));
                            }
                        }

                        // generate image cache files from image binary data, save them under cache/
                        $imageHandler = xoops_getHandler('imagesetimg');
                        $imagefiles   = &$imageHandler->getObjects(new Criteria('tplset_name', $newtplset), true);
                        foreach (array_keys($imagefiles) as $i) {
                            if (!$fp = fopen(XOOPS_CACHE_PATH . '/' . $newtplset . '_' . $imagefiles[$i]->getVar('imgsetimg_file'), 'wb')) {
                            } else {
                                fwrite($fp, $imagefiles[$i]->getVar('imgsetimg_body'));
                                fclose($fp);
                            }
                        }
                    }
                    $tpl_updated = true;
                }

                // add read permission for the start module to all groups
                if (!$startmod_updated && '--' != $new_value && XOOPS_CONF == $config->getVar('conf_catid') && 'startpage' === $config->getVar('conf_name')) {
                    /** @var \XoopsMemberHandler $memberHandler */
                    $memberHandler = xoops_getHandler('member');
                    $groups        = $memberHandler->getGroupList();
                    /** @var \XoopsGroupPermHandler $grouppermHandler */
                    $grouppermHandler = xoops_getHandler('groupperm');
                    /** @var \XoopsModuleHandler $moduleHandler */
                    $moduleHandler = xoops_getHandler('module');
                    $module        = $moduleHandler->getByDirname($new_value);
                    foreach ($groups as $groupid => $groupname) {
                        if (!$grouppermHandler->checkRight('module_read', $module->getVar('mid'), $groupid)) {
                            $grouppermHandler->addRight('module_read', $module->getVar('mid'), $groupid);
                        }
                    }
                    $startmod_updated = true;
                }

                $config->setConfValueForInput($new_value);
                $configHandler->insertConfig($config);
            }
            unset($new_value);
        }
    }
    /* if (!empty($_POST['use_mysession']) && $xoopsConfig['use_mysession'] == 0 && $_POST['session_name'] != '') {
        setcookie($_POST['session_name'], session_id(), time()+(60*intval($_POST['session_expire'])), '/',  '', 0);
    } */
    if (!empty($_POST['redirect'])) {
        redirect_header($_POST['redirect'], 2, _AM_SYSTEM_DBUPDATED);
    } else {
        redirect_header('admin.php?fct=preferences', 2, _AM_SYSTEM_DBUPDATED);
    }
}
