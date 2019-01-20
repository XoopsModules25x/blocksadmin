<?php

if (!defined('XOOPS_ROOT_PATH')) {
    exit;
}

include dirname(__DIR__) . '/preloads/autoloader.php';

$moduleDirName      = basename(dirname(__DIR__));
$moduleDirNameUpper = mb_strtoupper($moduleDirName);

/** @var \XoopsModules\Blocksadmin\Helper $helper */
$helper = \XoopsModules\Blocksadmin\Helper::getInstance();
$helper->loadLanguage('common');

$pathIcon32 = \Xmf\Module\Admin::menuIconPath('');
if (is_object($helper->getModule())) {
    $pathModIcon32 = $helper->getModule()->getInfo('modicons32');
}


$adminmenu[] = [
    'title' => _MI_BLOCKSADMIN_MENU_HOME,
    'link' => 'admin/index.php',
    'icon' => $pathIcon32 . '/home.png',
];

$adminmenu[] = [
    'title' => _MI_BLOCKS_ADMIN_BLOCKS,
    'link'  => 'admin/myblocksadmin.php?dirname=system',
    'icon'  => $pathIcon32 . '/manage.png',
];

$moduleHandler4menu = xoops_getHandler('module');
$criteria4menu      = new CriteriaCompo(new Criteria('isactive', 1));
//$criteria4menu->add(new Criteria('hasmain', 1));
$criteria4menu->add(new Criteria('mid', '1', '>'));
$modules4menu = $moduleHandler4menu->getObjects($criteria4menu, true);
array_unshift($modules4menu, $moduleHandler4menu->get(1));

//foreach ($modules4menu as $m4menu) {
//    $adminmenu[] = [
//        'title' => $m4menu->getVar('name'),
//        'link'  => 'admin/myblocksadmin.php?dirname=' . $m4menu->getVar('dirname'),
//    ];
//}

$adminmenu[] = [
    'title' => _MI_BLOCKSADMIN_MENU_ABOUT,
    'link'  => 'admin/about.php',
    'icon'  => $pathIcon32 . '/about.png',
];

