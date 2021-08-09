<?php
// ------------------------------------------------------------------------- //
//                            myblocksadmin.php                              //
//                - XOOPS block admin for each modules -                     //
//                          GIJOE <http://www.peak.ne.jp>                   //
// ------------------------------------------------------------------------- //

use XoopsModules\Blocksadmin\Helper;

/** @var Helper $helper */
/** @var \XoopsModuleHandler $moduleHandler */

require_once __DIR__ . '/admin_header.php';

$moduleDirName      = \basename(\dirname(__DIR__));
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);

$helper = Helper::getInstance();
$helper->loadLanguage('admin', 'system');
$helper->loadLanguage('common');
$helper->loadLanguage('blocksadmin');

//if( substr( XOOPS_VERSION , 6 , 3 ) > 2.0 ) {
//  require __DIR__ . '/myblocksadmin2.php' ;
//  exit ;
//}

//if (mb_substr(XOOPS_VERSION, 6, 3) > 2.0 && mb_substr(XOOPS_VERSION, 6, 3) < 2.3) {
//    require __DIR__ . '/myblocksadmin2.php';
//    exit;
//}

require __DIR__ . '/mygrouppermform.php';
require XOOPS_ROOT_PATH . '/kernel/block.php';

$xoops_system_path = XOOPS_ROOT_PATH . '/modules/system';

// language files
$language = $xoopsConfig['language'];
if (!file_exists("$xoops_system_path/language/$language/admin/blocksadmin.php")) {
    $language = 'english';
}

// to prevent from notice that constants already defined
$error_reporting_level = error_reporting(0);
require_once "$xoops_system_path/constants.php";
require_once "$xoops_system_path/language/$language/admin.php";
require_once "$xoops_system_path/language/$language/admin/blocksadmin.php";
error_reporting($error_reporting_level);

$group_defs = file("$xoops_system_path/language/$language/admin/groups.php");
foreach ($group_defs as $def) {
    if (mb_strstr($def, '_AM_SYSTEM_GROUPS_ACCESSRIGHTS') || mb_strstr($def, '_AM_SYSTEM_GROUPS_ACTIVERIGHTS')) {
        eval($def);
    }
}

// check $xoopsModule
if (!is_object($xoopsModule)) {
    redirect_header(XOOPS_URL . '/user.php', 1, _NOPERM);
}

// set target_module if specified by $_GET['dirname']
$moduleHandler = xoops_getHandler('module');
if (!empty($_GET['dirname'])) {
    $target_module = $moduleHandler->getByDirname($_GET['dirname']);
}
/* else if( ! empty( $_GET['mid'] ) ) {
    $target_module = $moduleHandler->get( intval( $_GET['mid'] ) );
}*/

if (!empty($target_module) && is_object($target_module)) {
    // specified by dirname
    $target_mid     = $target_module->getVar('mid');
    $target_mname   = $target_module->getVar('name') . '&nbsp;' . sprintf('(%2.2f)', $target_module->getVar('version') / 100.0);
    $query4redirect = '?dirname=' . urlencode(strip_tags($_GET['dirname']));
} elseif ((isset($_GET['mid']) && 0 == $_GET['mid']) || 'blocksadmin' === $xoopsModule->getVar('dirname')) {
    $target_mid     = 0;
    $target_mname   = '';
    $query4redirect = '?mid=0';
} else {
    $target_mid     = $xoopsModule->getVar('mid');
    $target_mname   = $xoopsModule->getVar('name');
    $query4redirect = '';
}

// check access right (needs system_admin of BLOCK)
/** @var \XoopsGroupPermHandler $grouppermHandler */
$grouppermHandler = xoops_getHandler('groupperm');
if (!$grouppermHandler->checkRight('system_admin', XOOPS_SYSTEM_BLOCK, $xoopsUser->getGroups())) {
    redirect_header(XOOPS_URL . '/user.php', 1, _NOPERM);
}

// get blocks owned by the module (Imported from xoopsblock.php then modified)
//$block_arr =& XoopsBlock::getByModule( $target_mid ) ;
$db        = \XoopsDatabaseFactory::getDatabaseConnection();
$sql       = 'SELECT * FROM ' . $db->prefix('newblocks') . " WHERE mid='$target_mid' ORDER BY visible DESC,side,weight";
$result    = $db->query($sql);
$block_arr = [];
while (false !== ($myrow = $db->fetchArray($result))) {
    $block_arr[] = new XoopsBlock($myrow);
}

function list_blocks()
{
    global $query4redirect, $block_arr;
    $moduleDirName      = \basename(\dirname(__DIR__));
    $moduleDirNameUpper = \mb_strtoupper($moduleDirName);

    // cachetime options
    $cachetimes = ['0' => _NOCACHE, '30' => sprintf(_SECONDS, 30), '60' => _MINUTE, '300' => sprintf(_MINUTES, 5), '1800' => sprintf(_MINUTES, 30), '3600' => _HOUR, '18000' => sprintf(_HOURS, 5), '86400' => _DAY, '259200' => sprintf(_DAYS, 3), '604800' => _WEEK, '2592000' => _MONTH];

    // displaying TH
    echo "
    <form action='admin.php' name='blockadmin' method='post'>
        <table width='95%' class='outer' cellpadding='4' cellspacing='1'>" . $GLOBALS['xoopsSecurity']->getTokenHTML() . "

        <tr valign='middle'>
            <th>" . _AM_SYSTEM_BLOCKS_TITLE . "</th>
            <th align='center' nowrap='nowrap'>" . constant('CO_' . $moduleDirNameUpper . '_' . 'SIDE') . "</th>
            <th align='center'>" . constant('CO_' . $moduleDirNameUpper . '_' . 'WEIGHT') . "</th>
             <th align='center'>" . constant('CO_' . $moduleDirNameUpper . '_' . 'VISIBLE') . "</th>            
            <th align='center'>" . constant('CO_' . $moduleDirNameUpper . '_' . 'VISIBLEIN') . "</th>            
            <th align='center'>" . constant('CO_' . $moduleDirNameUpper . '_' . 'AGDS') . "</th>            
            <th align='center'>" . constant('CO_' . $moduleDirNameUpper . '_' . 'BCACHETIME') . "</th>
            <th align='right'>" . constant('CO_' . $moduleDirNameUpper . '_' . 'ACTION') . "</th>
        </tr>\n";

    // blocks displaying loop
    $class         = 'even';
    $block_configs = get_block_configs();
    foreach (array_keys($block_arr) as $i) {
        $sseln = $sseln0 = $sseln1 = $ssel0 = $ssel1 = $ssel2 = $ssel3 = $ssel4 = $ssel5 = $ssel6 = $ssel7 = $ssel8 = $ssel9 = $sse20 = '';
        $scoln = $scoln0 = $scoln1 = $scol0 = $scol1 = $scol2 = $scol3 = $scol4 = $scol5 = $scol6 = $scol7 = $scol8 = $scol9 = $sco20 = '#FFFFFF';

        $weight     = $block_arr[$i]->getVar('weight');
        $title      = $block_arr[$i]->getVar('title');
        $name       = $block_arr[$i]->getVar('name');
        $bcachetime = $block_arr[$i]->getVar('bcachetime');
        $bid        = $block_arr[$i]->getVar('bid');

        // visible and side
        if (1 != $block_arr[$i]->getVar('visible')) {
            $sseln  = ' checked';
            $scoln  = '#FF0000';
            $sseln0 = ' checked';
            $scoln0 = '#00FF00';
        } else {
            $sseln1 = ' checked';
            $scoln1 = '#00FF00';
            switch ($block_arr[$i]->getVar('side')) {
                default:
                case XOOPS_SIDEBLOCK_LEFT:
                    $ssel0 = ' checked';
                    $scol0 = '#00FF00';
                    break;
                case XOOPS_SIDEBLOCK_RIGHT:
                    $ssel1 = ' checked';
                    $scol1 = '#00FF00';
                    break;
                case XOOPS_CENTERBLOCK_LEFT:
                    $ssel2 = ' checked';
                    $scol2 = '#00FF00';
                    break;
                case XOOPS_CENTERBLOCK_RIGHT:
                    $ssel4 = ' checked';
                    $scol4 = '#00FF00';
                    break;
                case XOOPS_CENTERBLOCK_CENTER:
                    $ssel3 = ' checked';
                    $scol3 = '#00FF00';
                    break;
                case XOOPS_CENTERBLOCK_BOTTOMLEFT:
                    $ssel5 = ' checked';
                    $scol5 = '#00FF00';
                    break;
                case XOOPS_CENTERBLOCK_BOTTOMRIGHT:
                    $ssel6 = ' checked';
                    $scol6 = '#00FF00';
                    break;
                case XOOPS_CENTERBLOCK_BOTTOM:
                    $ssel7 = ' checked';
                    $scol7 = '#00FF00';
                    break;
                //footer

                case XOOPS_FOOTERBLOCK_LEFT:
                    $ssel8 = ' checked';
                    $scol8 = '#00FF00';
                    break;
                case XOOPS_FOOTERBLOCK_CENTER:
                    $ssel9 = ' checked';
                    $scol9 = '#00FF00';
                    break;
                case XOOPS_FOOTERBLOCK_RIGHT:
                    $sse20 = ' checked';
                    $sco20 = '#00FF00';
                    break;
            }
        }

        // bcachetime
        $cachetime_options = '';
        foreach ($cachetimes as $cachetime => $cachetime_name) {
            if ($bcachetime == $cachetime) {
                $cachetime_options .= "<option value='$cachetime' selected='selected'>$cachetime_name</option>\n";
            } else {
                $cachetime_options .= "<option value='$cachetime'>$cachetime_name</option>\n";
            }
        }

        // target modules
        $db            = \XoopsDatabaseFactory::getDatabaseConnection();
        $result        = $db->query('SELECT module_id FROM ' . $db->prefix('block_module_link') . " WHERE block_id='$bid'");
        $selected_mids = [];
        while (list($selected_mid) = $db->fetchRow($result)) {
            $selected_mids[] = (int)$selected_mid;
        }
        $moduleHandler = xoops_getHandler('module');
        $criteria      = new CriteriaCompo(new Criteria('hasmain', 1));
        $criteria->add(new Criteria('isactive', 1));
        $module_list     = $moduleHandler->getList($criteria);
        $module_list[-1] = _AM_SYSTEM_BLOCKS_TOPPAGE;
        $module_list[0]  = _AM_SYSTEM_BLOCKS_ALLPAGES;
        ksort($module_list);
        $module_options = '';
        foreach ($module_list as $mid => $mname) {
            if (in_array($mid, $selected_mids)) {
                $module_options .= "<option value='$mid' selected='selected'>$mname</option>\n";
            } else {
                $module_options .= "<option value='$mid'>$mname</option>\n";
            }
        }

        $visibleInGroup = '';
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');
        /** @var \XoopsGroupPermHandler $grouppermHandler */
        $grouppermHandler = xoops_getHandler('groupperm');
        $groups           = $memberHandler->getGroups();
        $groups_perms     = $grouppermHandler->getGroupIds('block_read', $block_arr[$i]->getVar('bid'));

        //        echo "<td class='$class' align='center'><select size='5' name='groups[" . $block_arr[$i]->getVar('bid') . "][]' id='groups[" . $block_arr[$i]->getVar('bid') . "][]' multiple='multiple'>";
        foreach ($groups as $grp) {
            $visibleInGroup .= "<option value='" . $grp->getVar('groupid') . "' " . (in_array($grp->getVar('groupid'), $groups_perms) ? " selected='selected'" : '') . '>' . $grp->getVar('name') . '</option>';
        }
        //        echo '</select></td>';

        // delete link if it is cloned block
        if ('D' === $block_arr[$i]->getVar('block_type') || 'C' === $block_arr[$i]->getVar('block_type')) {
            $delete_link = "<a href='admin.php?fct=blocksadmin&amp;op=delete&amp;bid=$bid'><img src=" . $pathIcon16 . '/delete.png' . " alt='" . _DELETE . "' title='" . _DELETE . "'> </a>";// . _DELETE . '</a>';
        } else {
            $delete_link = '';
        }

        global $pathIcon16;

        // clone link if it is marked as cloneable block
        // $modversion['blocks'][n]['can_clone']
        if ('D' === $block_arr[$i]->getVar('block_type') || 'C' === $block_arr[$i]->getVar('block_type')) {
            $can_clone = true;
        } else {
            $can_clone = false;
            foreach ($block_configs as $bconf) {
                if ($block_arr[$i]->getVar('show_func') == $bconf['show_func'] && $block_arr[$i]->getVar('func_file') == $bconf['file'] && (empty($bconf['template']) || $block_arr[$i]->getVar('template') == $bconf['template'])) {
                    if (!empty($bconf['can_clone'])) {
                        $can_clone = true;
                    }
                }
            }
        }
        if ($can_clone) {
            $clone_link = "<a href='admin.php?fct=blocksadmin&amp;op=clone&amp;bid=$bid'> <img src=" . $pathIcon16 . '/editcopy.png' . " alt='" . _CLONE . "' title='" . _CLONE . "'> </a>";
        } else {
            $clone_link = '';
        }

        // displaying part
        echo "
        <tr valign='middle'>
            <td class='$class'>
                $name
                <br>
                <input type='text' name='title[$bid]' value='$title' size='20'>
            </td>
            <td class='$class' align='center' nowrap='nowrap' width='125px'>
<div align='center' >
    <input style='background-color:$scol2;' type='radio' name='side[$bid]' value='" . XOOPS_CENTERBLOCK_LEFT . "'$ssel2>
    <input style='background-color:$scol3;'type='radio' name='side[$bid]' value='" . XOOPS_CENTERBLOCK_CENTER . "'$ssel3>
    <input style='background-color:$scol4;'type='radio' name='side[$bid]' value='" . XOOPS_CENTERBLOCK_RIGHT . "'$ssel4>
</div>
<div>
    <span style='float:right'>
    <input style='background-color:$scol1;' type='radio' name='side[$bid]' value='" . XOOPS_SIDEBLOCK_RIGHT . "'$ssel1>
    </span>
    <div align='left'>
    <input style='background-color:$scol0;' type='radio' name='side[$bid]' value='" . XOOPS_SIDEBLOCK_LEFT . "'$ssel0>
    </div>
</div>
<div align='center'>
    <input style='background-color:$scol5;' type='radio' name='side[$bid]' value='" . XOOPS_CENTERBLOCK_BOTTOMLEFT . "'$ssel5>
    <input style='background-color:$scol7;' type='radio' name='side[$bid]' value='" . XOOPS_CENTERBLOCK_BOTTOM . "'$ssel7>
    <input style='background-color:$scol6;' type='radio' name='side[$bid]' value='" . XOOPS_CENTERBLOCK_BOTTOMRIGHT . "'$ssel6>
</div>

<div align='center'>
    <input style='background-color:$scol8;' type='radio' name='side[$bid]' value='" . XOOPS_FOOTERBLOCK_LEFT . "'$ssel8>
    <input style='background-color:$scol9;' type='radio' name='side[$bid]' value='" . XOOPS_FOOTERBLOCK_CENTER . "'$ssel9>
    <input style='background-color:$sco20;' type='radio' name='side[$bid]' value='" . XOOPS_FOOTERBLOCK_RIGHT . "'$sse20>
</div>

                <br>
                <br>
                <div style='float:left;width:40px;'>&nbsp;</div>
                <div style='float:left;background-color:$scoln;'>
                    <input type='radio' name='side[$bid]' value='-1' style='background-color:$scoln;' $sseln>
                </div>
                <div style='float:left;'>" . _NONE . "</div>
            </td>
            <td class='$class' align='center'>
                <input type='text' name=weight[$bid] value='$weight' size='3' maxlength='5' style='text-align:right;'>
            </td>
            
              <td class='$class' align='center' nowrap>
                       <input type='radio' name='visible[$bid]' value='1'  style='background-color:$scoln1;' {$sseln1}>" . _YES . " &nbsp;<input type='radio' name='visible[$bid]' value='-1'{$sseln0}>" . _NO . "
                   </td>
        
        
        
            <td class='$class' align='center'>
                <select name='bmodule[$bid][]' size='5' multiple='multiple'>
                    $module_options
                </select>
            </td>
            

            
            <td class='$class' align='center'><select size='5' name='groups[$bid][]' id='groups[$bid][]' multiple='multiple'>
            $visibleInGroup
            </select></td>
            
            
            
            
            
            <td class='$class' align='center'>
                <select name='bcachetime[$bid]' size='1'>
                    $cachetime_options
                </select>
            </td>
            <td class='$class' align='center'>
                <a href='admin.php?fct=blocksadmin&amp;op=edit&amp;bid=$bid'><img src=" . $pathIcon16 . '/edit.png' . " alt='" . _EDIT . "' title='" . _EDIT . "'> </a>{$delete_link}{$clone_link}
                <input type='hidden' name='bid[$bid]' value='$bid'>
            </td>
            
          
            
            
            
            
            
        </tr>\n";

        $class = ('even' === $class) ? 'odd' : 'even';
    }

    echo "
        <tr><td class='foot' align='center' colspan='6'>
                <input type='hidden' name='query4redirect' value='$query4redirect'>
                <input type='hidden' name='fct' value='blocksadmin'>
                <input type='hidden' name='op' value='order'>
                " . $GLOBALS['xoopsSecurity']->getTokenHTML() . "
                <input type='submit' name='submit' value='" . _SUBMIT . "'>
            </td></tr></table>
    </form>\n";
}

/**
 * @return array
 */
function get_block_configs()
{
    $error_reporting_level = error_reporting(0);
    if (preg_match('/^[.0-9a-zA-Z_-]+$/', @$_GET['dirname'])) {
        xoops_loadLanguage('modinfo', $_GET['dirname']);
        require \dirname(__DIR__, 2) . '/' . $_GET['dirname'] . '/xoops_version.php';
    } else {
        require \dirname(__DIR__) . '/xoops_version.php';
    }
    error_reporting($error_reporting_level);
    if (empty($modversion['blocks'])) {
        return [];
    }

    return $modversion['blocks'];
}

function list_groups()
{
    global $target_mid, $target_mname, $block_arr;
    $moduleDirName      = \basename(\dirname(__DIR__));
    $moduleDirNameUpper = \mb_strtoupper($moduleDirName);

    $item_list = [];
    foreach (array_keys($block_arr) as $i) {
        $item_list[$block_arr[$i]->getVar('bid')] = $block_arr[$i]->getVar('title');
    }

    $form = new MyXoopsGroupPermForm(_AM_SYSTEM_ADGS, 1, 'block_read', '');
    if ($target_mid > 1) {
        $form->addAppendix('module_admin', $target_mid, $target_mname . ' ' . constant('CO_' . $moduleDirNameUpper . '_' . 'ACTIVERIGHTS'));
        $form->addAppendix('module_read', $target_mid, $target_mname . ' ' . constant('CO_' . $moduleDirNameUpper . '_' . 'ACCESSRIGHTS'));
    }
    foreach ($item_list as $item_id => $item_name) {
        $form->addItem($item_id, $item_name);
    }
    echo $form->render();
}

if (!empty($_POST['submit'])) {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header(XOOPS_URL . '/', 3, $GLOBALS['xoopsSecurity']->getErrors());
    }

    require __DIR__ . '/mygroupperm.php';
    redirect_header(XOOPS_URL . '/modules/' . $xoopsModule->dirname() . "/admin/myblocksadmin.php$query4redirect", 1, _AM_SYSTEM_DBUPDATED);
}

xoops_cp_header();
if (file_exists('./mymenu.php')) {
    require __DIR__ . '/mymenu.php';
}

echo "<h3 style='text-align:left;'>$target_mname</h3>\n";

if (!empty($block_arr)) {
    echo "<h4 style='text-align:left;'>" . constant('CO_' . $moduleDirNameUpper . '_' . 'BADMIN') . "</h4>\n";
    list_blocks();
}

list_groups();
xoops_cp_footer();

?>
