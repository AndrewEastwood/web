<?php /* Smarty version Smarty-3.1.11, created on 2012-10-09 00:11:27
         compiled from "/var/www/mpws/rc_1.0/web/default/v1.0/template/widget/systemPluginLinkMenu.html" */ ?>
<?php /*%%SmartyHeaderCode:11792605845073417fda0150-95119348%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '04f084c1373abb6a23ce948bf8979bfc88e76392' => 
    array (
      0 => '/var/www/mpws/rc_1.0/web/default/v1.0/template/widget/systemPluginLinkMenu.html',
      1 => 1349287120,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11792605845073417fda0150-95119348',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'INFO' => 0,
    'OBJECT' => 0,
    'active_plugin_menu' => 0,
    'CURRENT' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5073417fdc0437_38086515',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5073417fdc0437_38086515')) {function content_5073417fdc0437_38086515($_smarty_tpl) {?><div id="MPWSWidgetSystemPluginLinkMenuID" class="MPWSWidget MPWSWidgetSystemPluginLinkMenu">
<?php $_smarty_tpl->tpl_vars["active_plugin_menu"] = new Smarty_variable($_smarty_tpl->tpl_vars['OBJECT']->value['WOB'][makeKey($_smarty_tpl->tpl_vars['INFO']->value['GET']['PLUGIN'])]->{"objectConfiguration_display_menuPlugin"}, null, 0);?>
<?php if (isset($_smarty_tpl->tpl_vars['active_plugin_menu']->value)){?>
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_menu, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_items'=>$_smarty_tpl->tpl_vars['active_plugin_menu']->value,'_OBJ'=>$_smarty_tpl->tpl_vars['OBJECT']->value['WOB'][makeKey($_smarty_tpl->tpl_vars['INFO']->value['GET']['PLUGIN'])],'_showDescription'=>true), 0);?>

<?php }?>
</div><?php }} ?>