<?php

/**
 * YREWRITE Addon
 * @author jan.kristinus@yakamara.de
 * @package redaxo4.5
 */

$showlist = true;
$data_id = rex_request('data_id', 'int', 0);
$func = rex_request('func', 'string');

if ($func != '') {

    $xform = new rex_xform;
    // $xform->setDebug(TRUE);
    $xform->setHiddenField('page', 'yrewrite');
    $xform->setHiddenField('subpage', '');
    $xform->setHiddenField('func', $func);
    $xform->setHiddenField('save', '1');

    $xform->setObjectparams('main_table', 'rex_yrewrite_domain');

    $xform->setValueField('text', array('domain', $I18N->msg('yrewrite_domain_info')));
    $xform->setValueField('text', array('server_name', 'Server Name:'));
    $xform->setValueField('be_link', array('mount_id', $I18N->msg('yrewrite_mount_id')));
    $xform->setValueField('be_link', array('start_id', $I18N->msg('yrewrite_start_id')));
    $xform->setValueField('be_link', array('notfound_id', $I18N->msg('yrewrite_notfound_id')));
    // $xform->setValueField("text",array("clang","clang"));

    $xform->setValidateField('unique', array('domain', $I18N->msg('yrewrite_domain_already_defined')));
    $xform->setValidateField('empty', array('domain', $I18N->msg('yrewrite_no_domain_defined')));
    // $xform->setValidateField('empty', array('mount_id', $I18N->msg('yrewrite_no_mount_id_defined')));
    $xform->setValidateField('empty', array('start_id', $I18N->msg('yrewrite_no_start_id_defined')));
    $xform->setValidateField('empty', array('notfound_id', $I18N->msg('yrewrite_no_not_found_id_defined')));

    // unique: mount_id,alias_domain
    // unique auf 2 Feldern funktioniert erst ab xform version 4.5.1
    // deswegen noch als customfunction
    $xform->setValidateField('customfunction', array('mount_id', 'rex_yrewrite_domaincheck', $xform, $I18N->msg('yrewrite_mount_id_already_defined')));

    function rex_yrewrite_domaincheck ($field, $value, $xform) {
        $sql = 'select '.$field.' from '.$xform->objparams["main_table"].' where '.$field.'="'.mysql_real_escape_string($value).'" and alias_domain="" AND !('.$xform->objparams["main_where"].')';
        $a = rex_sql::factory();
        $result = $a->getArray($sql);
        if(count($result)>0) return true;
        return false;
    }

    $xform->setValueField('fieldset', array('seo',$I18N->msg('yrewrite_rewriter_seo')));

    $xform->setValueField('text', array('title_scheme', $I18N->msg('yrewrite_domain_title_scheme'),rex_yrewrite_seo::$title_scheme_default));
    $xform->setValueField('textarea', array('description', $I18N->msg('yrewrite_domain_description'),'','','short'));
    $xform->setValueField('textarea', array('robots', $I18N->msg('yrewrite_domain_robots'),rex_yrewrite_seo::$robots_default,'','short'));
    $xform->setValueField('textarea', array('analytics_code', 'Analytics-Code','','','short'));

?>
<script>
  jQuery(document).ready(function () {
      jQuery("#xform-formular-title_scheme").append('<span style="display:block; margin-left:230px; font-size:10px"><?php echo $I18N->msg('yrewrite_domain_title_scheme_info'); ?></span>');
      jQuery("#xform-formular-description").append('<span style="display:block; margin-left:230px; font-size:10px;"></span>');
      jQuery("#xform-formular-description textarea").bind ("change input keyup keydown keypress mouseup mousedown cut copy paste",function (e) {
          var v = jQuery(this).val().replace(/(\r\n|\n|\r)/gm, "").length;
          jQuery("#xform-formular-description").find('span').html( v + ' <?php echo $I18N->msg('yrewrite_domain_description_info'); ?>');
      return true;
      }).trigger("keydown");
  });
</script><?php

    if ($func == 'delete') {

        $d = rex_sql::factory();
        $d->setQuery('delete from rex_yrewrite_domain where id=' . $data_id);
        echo rex_info($I18N->msg('yrewrite_domain_deleted'));
        rex_yrewrite::deleteCache();

    } elseif ($func == 'edit') {

        $xform->setHiddenField('data_id', $data_id);
        $xform->setActionField('db', array('rex_yrewrite_domain', 'id=' . $data_id));
        $xform->setObjectparams('main_id', $data_id);
        $xform->setObjectparams('main_where', "id=$data_id");
        $xform->setObjectparams('getdata', true);
        $xform->setObjectparams('submit_btn_label', $I18N->msg('save'));
        $form = $xform->getForm();

        if ($xform->objparams['actions_executed']) {
            echo rex_info($I18N->msg('yrewrite_domain_updated'));
            rex_yrewrite::deleteCache();

        } else {
            $showlist = false;
            echo '<div class="rex-area">
                            <h3 class="rex-hl2">' . $I18N->msg('yrewrite_edit_domain') . '</h3>
                            <div class="rex-area-content">';
            echo $form;
            echo '</div></div>';
        }

    } elseif ($func == 'add') {

        $xform->setActionField('db', array('rex_yrewrite_domain'));
        $xform->setObjectparams('submit_btn_label', $I18N->msg('add'));
        $form = $xform->getForm();

        if ($xform->objparams['actions_executed']) {
            echo rex_info($I18N->msg('yrewrite_domain_added'));
            rex_yrewrite::deleteCache();

        } else {
            $showlist = false;
            echo '<div class="rex-area">
                            <h3 class="rex-hl2">' . $I18N->msg('yrewrite_add_domain') . '</h3>
                            <div class="rex-area-content">';
            echo $form;
            echo '</div></div>';
        }

    }

}

if ($showlist) {

    function rex_yrewrite_show_article($params) {
        global $I18N;
        $id = $params['list']->getValue($params['field']);
        if($id == 0) {
            return $I18N->msg('yrewrite_root');
        } else {
            if(($article = OOArticle::getArticleById($id))) {
                if($article->isStartArticle()) {
                    $link = 'index.php?page=structure&category_id='.$id.'&clang=0';
                } else {
                    $link ='index.php?page=content&article_id='.$id.'&mode=edit&clang=0';
                }
                return $article->getName().' [<a href="'.$link.'">'.$id.'</a>]';
            }
        }
      return '['.$id.']';
    }


    $sql = 'SELECT * FROM rex_yrewrite_domain where alias_domain = ""';

    $list = rex_list::factory($sql, 100);
    $list->setColumnFormat('id', 'Id');
    $list->addParam('page', 'yrewrite');
    $list->addParam('subpage', '');

    $header = '<a class="rex-i-element rex-i-generic-add" href="' . $list->getUrl(array('func' => 'add')) . '"><span class="rex-i-element-text">' . $I18N->msg('yrewrite_add_domain') . '</span></a>';
    $list->addColumn($header, '###id###', 0, array('<th class="rex-icon">###VALUE###</th>', '<td class="rex-small">###VALUE###</td>'));

    $list->setColumnParams('id', array('data_id' => '###id###', 'func' => 'edit' ));
    $list->setColumnSortable('id');

    $list->removeColumn('id');
    $list->removeColumn('analytics_code');
    # $list->removeColumn('server_name');

    $list->setColumnLabel('domain', $I18N->msg('yrewrite_domain'));
    $list->setColumnLabel('server_name', 'Server-Name');
    $list->setColumnLabel('mount_id', $I18N->msg('yrewrite_mount_id'));
    $list->setColumnLabel('start_id', $I18N->msg('yrewrite_start_id'));
    $list->setColumnLabel('notfound_id', $I18N->msg('yrewrite_notfound_id'));

    $list->addColumn($I18N->msg('delete'), $I18N->msg('delete'));
    $list->setColumnParams($I18N->msg('delete'), array('data_id' => '###id###', 'func' => 'delete'));
    $list->addLinkAttribute($I18N->msg('delete'), 'onclick', 'return confirm(\' id=###id### ' . $I18N->msg('delete') . ' ?\')');

    $list->addColumn($I18N->msg('edit'), $I18N->msg('edit'));
    $list->setColumnParams($I18N->msg('edit'), array('data_id' => '###id###', 'func' => 'edit', 'start' => rex_request('start', 'string')));

    $list->setColumnFormat('mount_id', 'custom', 'rex_yrewrite_show_article', array());
    $list->setColumnFormat('start_id', 'custom', 'rex_yrewrite_show_article', array());
    $list->setColumnFormat('notfound_id', 'custom', 'rex_yrewrite_show_article', array());

    $list->removeColumn('clang');
    $list->removeColumn('alias_domain', 'alias_domain');
    $list->removeColumn('robots', 'robots');
    $list->removeColumn('title_scheme', 'title_scheme');
    $list->removeColumn('description', 'description');

    echo $list->get();

}
