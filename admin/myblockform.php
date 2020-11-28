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
 * @author        XOOPS Development Team
 */

use XoopsModules\Blocksadmin\{
    Helper
};
/** @var Helper $helper */

if (!defined('XOOPS_ROOT_PATH')) {
    exit;
}
require __DIR__ . '/admin_header.php';
$moduleDirName      = basename(dirname(__DIR__));
$moduleDirNameUpper = mb_strtoupper($moduleDirName); //$capsDirName

$helper = Helper::getInstance();
$helper->loadLanguage('admin', 'system');
$helper->loadLanguage('common');

$usespaw = empty($_GET['usespaw']) ? 0 : 1;

require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
//$form = new XoopsThemeForm($block['form_title'], 'blockform', XOOPS_URL."/modules/blocksadmin/admin/admin.php" ) ;
$form = new XoopsThemeForm($block['form_title'], 'blockform', 'admin.php', 'post', true);
if (isset($block['name'])) {
    $form->addElement(new XoopsFormLabel(_AM_SYSTEM_BLOCKS_NAME, $block['name']));
}
$side_select = new XoopsFormSelect(_AM_SYSTEM_BLOCKS_TYPE, 'bside', $block['side']);
$side_select->addOptionArray([0 => _AM_SYSTEM_BLOCKS_SBLEFT, 1 => _AM_SYSTEM_BLOCKS_SBRIGHT, 3 => _AM_SYSTEM_BLOCKS_CBLEFT, 4 => _AM_SYSTEM_BLOCKS_CBRIGHT, 5 => _AM_SYSTEM_BLOCKS_CBCENTER, 7 => _AM_SYSTEM_BLOCKS_CBBOTTOMLEFT, 8 => _AM_SYSTEM_BLOCKS_CBBOTTOMRIGHT, 9 => _AM_SYSTEM_BLOCKS_CBBOTTOM]);
$form->addElement($side_select);
$form->addElement(new XoopsFormText(constant('CO_' . $moduleDirNameUpper . '_' . 'WEIGHT'), 'bweight', 2, 5, $block['weight']));
$form->addElement(new XoopsFormRadioYN(constant('CO_' . $moduleDirNameUpper . '_' . 'VISIBLE'), 'bvisible', $block['visible']));
$mod_select = new XoopsFormSelect(constant('CO_' . $moduleDirNameUpper . '_' . 'VISIBLEIN'), 'bmodule', $block['modules'], 5, true);
/** @var \XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
$criteria      = new CriteriaCompo(new Criteria('hasmain', 1));
$criteria->add(new Criteria('isactive', 1));
$module_list     = $moduleHandler->getList($criteria);
$module_list[-1] = _AM_SYSTEM_BLOCKS_TOPPAGE;
$module_list[0]  = _AM_SYSTEM_BLOCKS_ALLPAGES;
ksort($module_list);
$mod_select->addOptionArray($module_list);
$form->addElement($mod_select);
$form->addElement(new XoopsFormText(constant('CO_' . $moduleDirNameUpper . '_' . 'TITLE'), 'btitle', 50, 255, $block['title']), false);

if ($block['is_custom']) {
    // Custom Block's textarea
    $notice_for_tags = '<span style="font-size:x-small;font-weight:bold;">' . _AM_SYSTEM_BLOCKS_USEFULTAGS . '</span><br><span style="font-size:x-small;font-weight:normal;">' . sprintf(_AM_BLOCKTAG1, '{X_SITEURL}', XOOPS_URL . '/') . '</span>';
    $current_op      = 'clone' === @$_GET['op'] ? 'clone' : 'edit';
    $uri_to_myself   = XOOPS_URL . "/modules/blocksadmin/admin/admin.php?fct=blocksadmin&amp;op=$current_op&amp;bid={$block['bid']}";
    // $can_use_spaw = check_browser_can_use_spaw() ;
    $myts     = MyTextSanitizer::getInstance();
    $textarea = new XoopsFormDhtmlTextArea(_AM_SYSTEM_BLOCKS_CONTENT, 'bcontent', htmlspecialchars($block['content'], ENT_QUOTES | ENT_HTML5), 15, 70);
    $textarea->setDescription($notice_for_tags);

    $form->addElement($textarea, true);

    $ctype_select = new XoopsFormSelect(_AM_SYSTEM_BLOCKS_CTYPE, 'bctype', $block['ctype']);
    $ctype_select->addOptionArray(['H' => _AM_SYSTEM_BLOCKS_HTML, 'P' => _AM_SYSTEM_BLOCKS_PHP, 'S' => _AM_SYSTEM_BLOCKS_AFWSMILE, 'T' => _AM_SYSTEM_BLOCKS_AFNOSMILE]);
    $form->addElement($ctype_select);
} else {
    if ('' != $block['template'] && !defined('XOOPS_ORETEKI')) {
        /** @var \XoopsTplfileHandler $tplfileHandler */
        $tplfileHandler = xoops_getHandler('tplfile');
        $btemplate      = $tplfileHandler->find($GLOBALS['xoopsConfig']['template_set'], 'block', $block['bid']);
        if (count($btemplate) > 0) {
            $form->addElement(new XoopsFormLabel(_AM_SYSTEM_BLOCKS_CONTENT, '<a href="' . XOOPS_URL . '/modules/system/admin.php?fct=tplsets&op=edittpl&id=' . $btemplate[0]->getVar('tpl_id') . '">' . _AM_SYSTEM_BLOCKS_EDITTPL . '</a>'));
        } else {
            $btemplate2 = $tplfileHandler->find('default', 'block', $block['bid']);
            if (count($btemplate2) > 0) {
                $form->addElement(new XoopsFormLabel(_AM_SYSTEM_BLOCKS_CONTENT, '<a href="' . XOOPS_URL . '/modules/system/admin.php?fct=tplsets&op=edittpl&id=' . $btemplate2[0]->getVar('tpl_id') . '" target="_blank">' . _AM_SYSTEM_BLOCKS_EDITTPL . '</a>'));
            }
        }
    }
    if (false !== $block['edit_form']) {
        $form->addElement(new XoopsFormLabel(_AM_SYSTEM_BLOCKS_OPTIONS, $block['edit_form']));
    }
}
$cache_select = new XoopsFormSelect(_AM_SYSTEM_BLOCKS_BCACHETIME, 'bcachetime', $block['cachetime']);
$cache_select->addOptionArray(['0' => _NOCACHE, '30' => sprintf(_SECONDS, 30), '60' => _MINUTE, '300' => sprintf(_MINUTES, 5), '1800' => sprintf(_MINUTES, 30), '3600' => _HOUR, '18000' => sprintf(_HOURS, 5), '86400' => _DAY, '259200' => sprintf(_DAYS, 3), '604800' => _WEEK, '2592000' => _MONTH]);
$form->addElement($cache_select);
if (isset($block['bid'])) {
    $form->addElement(new XoopsFormHidden('bid', $block['bid']));
}
// $form->addElement(new XoopsFormHidden('options', $block['options']));
$form->addElement(new XoopsFormHidden('op', $block['op']));
$form->addElement(new XoopsFormHidden('fct', 'blocksadmin'));
$button_tray = new XoopsFormElementTray('', '&nbsp;');
if ($block['is_custom']) {
    $button_tray->addElement(new XoopsFormButton('', 'previewblock', _PREVIEW, 'submit'));
}
$button_tray->addElement(new XoopsFormButton('', 'submitblock', $block['submit_button'], 'submit'));
$form->addElement($button_tray);

// checks browser compatibility with the control
/**
 * @return bool
 */
function check_browser_can_use_spaw()
{
    $browser = $_SERVER['HTTP_USER_AGENT'];
    // check if msie
    if (preg_match('/MSIE[^;]*/i', $browser, $msie)) {
        // get version
        if (preg_match('/\d+\.\d+/i', $msie[0], $version)) {
            // check version
            if ((float)$version[0] >= 5.5) {
                // finally check if it's not opera impersonating ie
                if (false !== mb_strpos($browser, 'opera')) {
                    return true;
                }
            }
        }
    }

    return false;
}
