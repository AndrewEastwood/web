<?php /* Smarty version Smarty-3.1.11, created on 2012-10-09 00:11:27
         compiled from "/var/www/mpws/rc_1.0/web/default/v1.0/template/page/standartSystemPageStyle1.html" */ ?>
<?php /*%%SmartyHeaderCode:9003581735073417fdc97d3-56634365%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fee379ac26415e5d616f64f7c05de0bc03ae5909' => 
    array (
      0 => '/var/www/mpws/rc_1.0/web/default/v1.0/template/page/standartSystemPageStyle1.html',
      1 => 1349287675,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '9003581735073417fdc97d3-56634365',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'CURRENT' => 0,
    'wgt_pluginMenu' => 0,
    '_header' => 0,
    '_content' => 0,
    '_footer' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5073417fe04917_92172832',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5073417fe04917_92172832')) {function content_5073417fe04917_92172832($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["wgt_pluginMenu"] = new Smarty_variable($_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_menu, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_items'=>$_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectConfiguration_display_menuSystem), 0));?>


<?php $_smarty_tpl->createLocalArrayVariable('_header', null, 0);
$_smarty_tpl->tpl_vars['_header']->value[] = $_smarty_tpl->tpl_vars['wgt_pluginMenu']->value;?>
 

<?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_page_standartPublicPageStyle1, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_header'=>$_smarty_tpl->tpl_vars['_header']->value,'_content'=>$_smarty_tpl->tpl_vars['_content']->value,'_footer'=>$_smarty_tpl->tpl_vars['_footer']->value), 0);?>
<?php }} ?>