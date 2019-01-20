<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit;
}

require __DIR__ . '/admin_header.php';
//require __DIR__ . '/mygrouppermform.php';
//require_once XOOPS_ROOT_PATH . '/class/xoopsblock.php';

if (!defined('XOOPS_ORETEKI')) {
    // Skip for ORETEKI XOOPS

    if (!isset($module) || !is_object($module)) {
        $module = $xoopsModule;
    } elseif (!is_object($xoopsModule)) {
        die('$xoopsModule is not set');
    }

    if (file_exists(__DIR__ . '/../language/' . $xoopsConfig['language'] . '/modinfo.php')) {
        require_once dirname(__DIR__) . '/language/' . $xoopsConfig['language'] . '/modinfo.php';
    } else {
        require_once dirname(__DIR__) . '/language/english/modinfo.php';
    }

    require __DIR__ . '/menu.php'; //can NOT be require_once

    //  array_push( $adminmenu , array( 'title' => _PREFERENCES , 'link' => '../system/admin.php?fct=preferences&op=showmod&mod=' . $module->getvar('mid') ) ) ;
    $menuitem_dirname = $module->getvar('dirname');
    if ($module->getvar('hasconfig')) {
        $adminmenu[] = ['title' => _PREFERENCES, 'link' => 'admin/admin.php?fct=preferences&op=showmod&mod=' . $module->getvar('mid')];
    }

    $menuitem_count = 0;
    $mymenu_uri     = empty($mymenu_fake_uri) ? $_SERVER['REQUEST_URI'] : $mymenu_fake_uri;
    $mymenu_link    = mb_substr(mb_strstr($mymenu_uri, '/admin/'), 1);

    // hilight
    foreach (array_keys($adminmenu) as $i) {
        if ($mymenu_link == $adminmenu[$i]['link']) {
            $adminmenu[$i]['color'] = '#FFCCCC';
            $adminmenu_hilighted    = true;
        } else {
            $adminmenu[$i]['color'] = '#DDDDDD';
        }
    }
    if (empty($adminmenu_hilighted)) {
        foreach (array_keys($adminmenu) as $i) {
            if (mb_stristr($mymenu_uri, $adminmenu[$i]['link'])) {
                $adminmenu[$i]['color'] = '#FFCCCC';
            }
        }
    }

    /*  // display
        foreach( $adminmenu as $menuitem ) {
            echo "<a href='".XOOPS_URL."/modules/$menuitem_dirname/{$menuitem['link']}' style='background-color:{$menuitem['color']};font:normal normal bold 9pt/12pt;'>{$menuitem['title']}</a> &nbsp; \n" ;

            if( ++ $menuitem_count >= 4 ) {
                echo "</div>\n<div width='95%' align='center'>\n" ;
                $menuitem_count = 0 ;
            }
        }
        echo "</div>\n" ;
    */
    // display

    echo "<div style='text-align:left;width:98%;'>";
    foreach ($adminmenu as $menuitem) {
//        echo "<div style='float:left;height:1.5em;'><nobr><a href='" . XOOPS_URL . "/modules/$menuitem_dirname/{$menuitem['link']}' style='background-color:{$menuitem['color']};font:normal normal bold 9pt/12pt;'>{$menuitem['title']}</a> | </nobr></div>\n";
    }



    // Initialize module handler
    /* @var \XoopsModuleHandler $module_handler */
    $module_handler = xoops_getHandler('module');
    $modules        = $module_handler->getObjects(null, true);
    $criteria       = new \CriteriaCompo(new \Criteria('hasmain', 1));

    $criteria->add(new \Criteria('isactive', 1));
    // Modules for blocks to be visible in
    $display_list = $module_handler->getList($criteria);
    unset($criteria);

    // Initialize blocks handler
    /* @var SystemBlockHandler $block_handler */
//    $block_handler5 = xoops_getHandler('Block');

//    $block_handler = xoops_getModuleHandler('block');


//    include_once __DIR__ . '/../../system/class/block.php';

//    $block_handler = new \XoopsBlockHandler();



//    global $block_handler;
    // Initialize module handler
    /* @var XoopsModuleHandler $module_handler */
    $module_handler = xoops_getHandler('module');
    $modules        = $module_handler->getObjects(null, true);

    $filterform = new \XoopsThemeForm('', 'filterform', 'myblocksadmin.php', 'get');
//    $filterform->addElement(new \XoopsFormHidden('fct', 'blocksadmin'));
//    $filterform->addElement(new \XoopsFormHidden('op', 'list'));
//    $filterform->addElement(new \XoopsFormHidden('filter', 1));
    $selgen = null;
//    $sel_gen = new XoopsFormSelect(_AM_SYSTEM_BLOCKS_GENERATOR, 'selgen', $selgen);
    $sel_gen = new XoopsFormSelect(_AM_SYSTEM_BLOCKS_GENERATOR, 'dirname', $selgen);
    $sel_gen->setExtra("onchange='submit()'");
//    $sel_gen->addOption(-1, _AM_SYSTEM_BLOCKS_TYPES);
//    $sel_gen->addOption(0, _AM_SYSTEM_BLOCKS_CUSTOM);
    foreach ($modules as $list) {
        $sel_gen->addOption($list->getVar('dirname'), $list->getVar('name'));
    }
    $filterform->addElement($sel_gen);

   /*

    $selmod = null;
    $sel_mod = new XoopsFormSelect(_AM_SYSTEM_BLOCKS_SVISIBLEIN, 'selmod', $selmod);
    $sel_mod->setExtra("onchange='submit()'");
    ksort($display_list);
    $display_list_spec[0]  = _AM_SYSTEM_BLOCKS_ALLPAGES;
    $display_list_spec[-1] = _AM_SYSTEM_BLOCKS_TOPPAGE;
    $display_list_spec[-2] = _AM_SYSTEM_BLOCKS_TYPES;
    $display_list          = $display_list_spec + $display_list;
    foreach ($display_list as $k => $v) {
        $sel_mod->addOption($k, $v);
    }
    $filterform->addElement($sel_mod);




    // For selection of group access
    $selgrp = null;
    $sel_grp = new XoopsFormSelect(_AM_SYSTEM_BLOCKS_GROUP, 'selgrp', $selgrp);
    $sel_grp->setExtra("onchange='submit()'");
//    /* @var XoopsMemberHandler $member_handler */

/*
    $member_handler = xoops_getHandler('member');
    $group_list     = $member_handler->getGroupList();
    $sel_grp->addOption(-1, _AM_SYSTEM_BLOCKS_TYPES);
    $sel_grp->addOption(0, _AM_SYSTEM_BLOCKS_UNASSIGNED);
    foreach ($group_list as $k => $v) {
        $sel_grp->addOption($k, $v);
    }
    $filterform->addElement($sel_grp);
    $selvis = null;
    $sel_vis = new XoopsFormSelect(_AM_SYSTEM_BLOCKS_VISIBLE, 'selvis', $selvis);
    $sel_vis->setExtra("onchange='submit()'");
    $sel_vis->addOption(-1, _AM_SYSTEM_BLOCKS_TYPES);
    $sel_vis->addOption(0, _NO);
    $sel_vis->addOption(1, _YES);

    $filterform->addElement($sel_vis);

    $filterform->assign($xoopsTpl);

//     Get blocks
    $selvis      = (-1 == $selvis) ? null : $selvis;
    $selmod      = (-2 == $selmod) ? null : $selmod;
    $order_block = (isset($selvis) ? '' : 'b.visible DESC, ') . 'b.side,b.weight,b.bid';



//    global $block_handler;

    if (0 == $selgrp) {
        // get blocks that are not assigned to any groups
//        $blocks_arr = $block_handler->getNonGroupedBlocks($selmod, $toponlyblock = false, $selvis, $order_block);
    } else {
        $blocks_arr = $block_handler->getAllByGroupModule($selgrp, $selmod, $toponlyblock = false, $selvis, $order_block);
    }

    if ($selgen >= 0) {
        foreach (array_keys($blocks_arr) as $bid) {
            if ($blocks_arr[$bid]->getVar('mid') != $selgen) {
                unset($blocks_arr[$bid]);
            }
        }
    }

    $arr = [];
    foreach (array_keys($blocks_arr) as $i) {
        $arr[$i] = $blocks_arr[$i]->toArray();
        $xoopsTpl->append_by_ref('blocks', $arr[$i]);
    }
//    $block     = $block_handler->create();
//    $blockform = $block->getForm();
    $xoopsTpl->assign('blockform', $filterform->render());
*/
    echo $filterform->render();



    echo "</div>\n<hr style='clear:left;display:block;'>\n";
}
