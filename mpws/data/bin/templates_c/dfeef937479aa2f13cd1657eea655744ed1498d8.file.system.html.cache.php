<?php /* Smarty version Smarty-3.1.11, created on 2012-10-18 09:37:05
         compiled from "/var/www/mpws/web/default/v1.0/template/page/system.html" */ ?>
<?php /*%%SmartyHeaderCode:1415811830507ba5e2a704a2-82820236%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'dfeef937479aa2f13cd1657eea655744ed1498d8' => 
    array (
      0 => '/var/www/mpws/web/default/v1.0/template/page/system.html',
      1 => 1350542219,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1415811830507ba5e2a704a2-82820236',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_507ba5e2b612a2_46541161',
  'variables' => 
  array (
    'CURRENT' => 0,
    'wgt_sysUsrInfo' => 0,
    'msg_common' => 0,
    'INFO' => 0,
    'wgt_tools' => 0,
    'wgt_menuList' => 0,
    '_header' => 0,
    '_headers' => 0,
    '_content' => 0,
    '_contents' => 0,
    '_footer' => 0,
    '_footers' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_507ba5e2b612a2_46541161')) {function content_507ba5e2b612a2_46541161($_smarty_tpl) {?>

<?php $_smarty_tpl->tpl_vars["wgt_sysUsrInfo"] = new Smarty_variable($_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_widget_systemUserInfo, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array(), 0));?>

<?php $_smarty_tpl->tpl_vars["msg_common"] = new Smarty_variable($_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_messagesList, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_realm'=>'COMMON'), 0));?>


<?php $_smarty_tpl->createLocalArrayVariable('_headers', null, 0);
$_smarty_tpl->tpl_vars['_headers']->value[] = $_smarty_tpl->tpl_vars['wgt_sysUsrInfo']->value;?>
<?php $_smarty_tpl->createLocalArrayVariable('_contents', null, 0);
$_smarty_tpl->tpl_vars['_contents']->value[] = $_smarty_tpl->tpl_vars['msg_common']->value;?>
<?php $_smarty_tpl->tpl_vars['_footers'] = new Smarty_variable(Array(), null, 0);?>

<?php if ($_smarty_tpl->tpl_vars['INFO']->value['GET']['PAGE']==$_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectConfiguration_object_menuKeyToShowToolsList){?>
    <?php if (empty($_smarty_tpl->tpl_vars['INFO']->value['GET']['PLUGIN'])){?>
        <?php $_smarty_tpl->tpl_vars["wgt_tools"] = new Smarty_variable($_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_widget_systemPluginList, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array(), 0));?>

        <?php $_smarty_tpl->createLocalArrayVariable('_contents', null, 0);
$_smarty_tpl->tpl_vars['_contents']->value[] = $_smarty_tpl->tpl_vars['wgt_tools']->value;?>
    <?php }else{ ?>
        
        <?php if (empty($_smarty_tpl->tpl_vars['INFO']->value['GET']['DISPLAY'])){?>
            <?php $_smarty_tpl->tpl_vars["wgt_menuList"] = new Smarty_variable($_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_widget_systemPluginMenu, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array(), 0));?>

            <?php $_smarty_tpl->createLocalArrayVariable('_contents', null, 0);
$_smarty_tpl->tpl_vars['_contents']->value[] = $_smarty_tpl->tpl_vars['wgt_menuList']->value;?>
        <?php }?>
    <?php }?>
<?php }?>


<?php if (isset($_smarty_tpl->tpl_vars['_header']->value)){?>
    <?php $_smarty_tpl->tpl_vars['_headers'] = new Smarty_variable(array_merge($_smarty_tpl->tpl_vars['_headers']->value,$_smarty_tpl->tpl_vars['_header']->value), null, 0);?>
<?php }?>
<?php if (isset($_smarty_tpl->tpl_vars['_content']->value)){?>
    <?php $_smarty_tpl->tpl_vars['_contents'] = new Smarty_variable(array_merge($_smarty_tpl->tpl_vars['_contents']->value,$_smarty_tpl->tpl_vars['_content']->value), null, 0);?>
<?php }?>
<?php if (isset($_smarty_tpl->tpl_vars['_footer']->value)){?>
    <?php $_smarty_tpl->tpl_vars['_footers'] = new Smarty_variable(array_merge($_smarty_tpl->tpl_vars['_footers']->value,$_smarty_tpl->tpl_vars['_footer']->value), null, 0);?>
<?php }?>


<?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_page_standartSystemPageStyle1, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_header'=>$_smarty_tpl->tpl_vars['_headers']->value,'_content'=>$_smarty_tpl->tpl_vars['_contents']->value,'_footer'=>$_smarty_tpl->tpl_vars['_footers']->value), 0);?>
<?php }} ?>