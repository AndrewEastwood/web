<?php /* Smarty version Smarty-3.1.11, created on 2012-10-18 12:20:50
         compiled from "/var/www/mpws/web/default/v1.0/template/widget/systemPluginMenu.html" */ ?>
<?php /*%%SmartyHeaderCode:1857403714507fc9f21f42b9-32927593%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '302414ead005912a6ce664d1fd746dfcadae916d' => 
    array (
      0 => '/var/www/mpws/web/default/v1.0/template/widget/systemPluginMenu.html',
      1 => 1350540232,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1857403714507fc9f21f42b9-32927593',
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
  'unifunc' => 'content_507fc9f22c1865_35952798',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_507fc9f22c1865_35952798')) {function content_507fc9f22c1865_35952798($_smarty_tpl) {?><div id="MPWSWidgetSystemPluginMenuID" class="MPWSWidget MPWSWidgetSystemPluginMenu">
<?php $_smarty_tpl->tpl_vars["active_plugin_menu"] = new Smarty_variable($_smarty_tpl->tpl_vars['OBJECT']->value['WOB'][makeKey($_smarty_tpl->tpl_vars['INFO']->value['GET']['PLUGIN'])]->{"objectConfiguration_display_menuPlugin"}, null, 0);?>
<?php if (isset($_smarty_tpl->tpl_vars['active_plugin_menu']->value)){?>
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_menu, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_items'=>$_smarty_tpl->tpl_vars['active_plugin_menu']->value,'_OBJ'=>$_smarty_tpl->tpl_vars['OBJECT']->value['WOB'][makeKey($_smarty_tpl->tpl_vars['INFO']->value['GET']['PLUGIN'])],'_showDescription'=>true), 0);?>

<?php }?>
</div><?php }} ?>