<?php

use Xmf\Module\Admin;
use XoopsModules\Blocksadmin\{
    Helper
};
/** @var Helper $helper */


if (!defined('XOOPS_ROOT_PATH')) {
    exit;
}

require \dirname(__DIR__) . '/preloads/autoloader.php';

$moduleDirName      = \basename(\dirname(__DIR__));
$moduleDirNameUpper = mb_strtoupper($moduleDirName);

$helper = Helper::getInstance();
$helper->loadLanguage('common');
$helper->loadLanguage('feedback');

$pathIcon32 = Admin::menuIconPath('');
$pathModIcon32 = XOOPS_URL .   '/modules/' . $moduleDirName . '/assets/images/icons/32/';
if (is_object($helper->getModule()) && false !== $helper->getModule()->getInfo('modicons32')) {
    $pathModIcon32 = $helper->url($helper->getModule()->getInfo('modicons32'));
}

$adminmenu[] = [
    'title' => _MI_BLOCKSADMIN_MENU_HOME,
    'link'  => 'admin/index.php',
    'icon'  => $pathIcon32 . '/home.png',
];

$adminmenu[] = [
    'title' => _MI_BLOCKS_ADMIN_BLOCKS,
    'link'  => 'admin/myblocksadmin.php',
    'icon'  => $pathIcon32 . '/manage.png',
];

/** @var \XoopsModuleHandler $moduleHandler */
$moduleHandler = xoops_getHandler('module');
$criteria4menu = new CriteriaCompo(new Criteria('isactive', 1));
//$criteria4menu->add(new Criteria('hasmain', 1));
$criteria4menu->add(new Criteria('mid', '1', '>'));
$modules4menu = $moduleHandler->getObjects($criteria4menu, true);
array_unshift($modules4menu, $moduleHandler->get(1));

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

