<?php

$addonname = 'yrewrite';

$REX['ADDON']['install']['yrewrite'] = 1;
// ERRMSG IN CASE: $REX['ADDON']['installmsg']['yrewrite'] = "Leider konnte nichts installiert werden da.";

$sql = rex_sql::factory();
$sql->setQuery('ALTER TABLE `rex_article` ADD `yrewrite_url` VARCHAR( 255 ) NOT NULL ;');

$sql->setQuery('CREATE TABLE IF NOT EXISTS `rex_yrewrite_domain` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `domain` varchar(255) NOT NULL,
    `mount_id` int(11) NOT NULL,
    `start_id` int(11) NOT NULL,
    `notfound_id` int(11) NOT NULL,
    `alias_domain` varchar(255) NOT NULL,
    `clang` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');


$I18N->appendFile($REX['INCLUDE_PATH'] . '/addons/yrewrite/lang');

if ($REX['VERSION'] != '4' || $REX['SUBVERSION'] < '5') {
    $REX['ADDON']['install']['yrewrite'] = 0;
    $REX['ADDON']['installmsg']['yrewrite'] = $I18N->msg('yrewrite_install_redaxo_version_problem', $REX['VERSION'] . '.' . $REX['SUBVERSION'], '4.5');

} elseif (OOAddon::isAvailable('xform') != 1 || version_compare(OOAddon::getVersion('xform'), '4.5', '<')) {
    $REX['ADDON']['install']['yrewrite'] = 0;
    $REX['ADDON']['installmsg']['yrewrite'] = $I18N->msg('yrewrite_install_xform_version_problem', '4.5');

} elseif (version_compare(PHP_VERSION, '5.3.0', '<')) {
    $REX['ADDON']['install']['yrewrite'] = 0;
    $REX['ADDON']['installmsg']['yrewrite'] = $I18N->msg('yrewrite_install_php_version_problem', '5.3.0', PHP_VERSION);

}
