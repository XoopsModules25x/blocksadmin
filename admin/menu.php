<?php

use XoopsModules\Blocksadmin;

if (!defined('XOOPS_ROOT_PATH')) {
    exit;
}

include dirname(__DIR__) . '/preloads/autoloader.php';

$moduleDirName      = basename(dirname(__DIR__));
$moduleDirNameUpper = strtoupper($moduleDirName);

/** @var \XoopsModules\Blocksadmin\Helper $helper */
$helper = \XoopsModules\Blocksadmin\Helper::getInstance();
$helper->loadLanguage('common');

$pathIcon32 = \Xmf\Module\Admin::menuIconPath('');
if (is_object($helper->getModule())) {
    $pathModIcon32 = $helper->getModule()->getInfo('modicons32');
}

$adminmenu[0]['title'] = _BLOCKS_ADMIN_CUSTOM;
$adminmenu[0]['link']  = 'admin/myblocksadmin.php?mid=0';

$moduleHandler4menu = xoops_getHandler('module');
$criteria4menu      = new CriteriaCompo(new Criteria('isactive', 1));
//$criteria4menu->add(new Criteria('hasmain', 1));
$criteria4menu->add(new Criteria('mid', '1', '>'));
$modules4menu = $moduleHandler4menu->getObjects($criteria4menu, true);
array_unshift($modules4menu, $moduleHandler4menu->get(1));

foreach ($modules4menu as $m4menu) {
    $adminmenu[] = [
        'title' => $m4menu->getVar('name'),
        'link'  => 'admin/myblocksadmin.php?dirname=' . $m4menu->getVar('dirname')
    ];
}

$adminmenu[] = [
    'title' => _AM_MODULEADMIN_ABOUT,
    'link'  => 'admin/about.php',
    'icon'  => $pathIcon32 . '/about.png'
];

