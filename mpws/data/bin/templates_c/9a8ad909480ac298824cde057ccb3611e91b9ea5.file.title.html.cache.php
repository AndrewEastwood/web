<?php /* Smarty version Smarty-3.1.11, created on 2012-10-23 23:36:41
         compiled from "/var/www/mpws/rc_1.0/web/default/v1.0/template/component/title.html" */ ?>
<?php /*%%SmartyHeaderCode:24962103250817f2349b477-10667200%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9a8ad909480ac298824cde057ccb3611e91b9ea5' => 
    array (
      0 => '/var/www/mpws/rc_1.0/web/default/v1.0/template/component/title.html',
      1 => 1351024593,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '24962103250817f2349b477-10667200',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50817f234c0990_56444753',
  'variables' => 
  array (
    'CURRENT' => 0,
    'OBJECT' => 0,
    '_resourceOwner' => 0,
    '__prop__' => 0,
    'DISPLAY_OBJECT' => 0,
    '_customText' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50817f234c0990_56444753')) {function content_50817f234c0990_56444753($_smarty_tpl) {?>


<?php $_smarty_tpl->tpl_vars["DISPLAY_OBJECT"] = new Smarty_variable(glGetFirstNonEmptyValue($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT'],$_smarty_tpl->tpl_vars['OBJECT']->value['SITE']), null, 0);?>


<?php $_smarty_tpl->tpl_vars["__prop__"] = new Smarty_variable("objectProperty_".((string)(($tmp = @$_smarty_tpl->tpl_vars['_resourceOwner']->value)===null||$tmp==='' ? 'component' : $tmp))."_", null, 0);?>

<div class="MPWSComponent MPWSComponentTitle">
    <span class="MPWSText MPWSTextTitle"><?php echo $_smarty_tpl->tpl_vars['DISPLAY_OBJECT']->value->{((string)$_smarty_tpl->tpl_vars['__prop__']->value)."title"};?>
</span>
    <span class="MPWSText MPWSTextDetails"><?php echo $_smarty_tpl->tpl_vars['DISPLAY_OBJECT']->value->{((string)$_smarty_tpl->tpl_vars['__prop__']->value)."description"};?>
</span>
    <?php if (isset($_smarty_tpl->tpl_vars['_customText']->value)){?>
    <span class="MPWSText MPWSTextCustom"><?php echo $_smarty_tpl->tpl_vars['_customText']->value;?>
</span>
    <?php }?>
</div><?php }} ?>