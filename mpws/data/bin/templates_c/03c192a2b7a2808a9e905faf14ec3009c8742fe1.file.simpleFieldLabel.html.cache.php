<?php /* Smarty version Smarty-3.1.11, created on 2012-10-18 20:46:03
         compiled from "/var/www/mpws/rc_1.0/web/default/v1.0/template/component/simpleFieldLabel.html" */ ?>
<?php /*%%SmartyHeaderCode:16360540985080405b4a5fd4-44055952%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '03c192a2b7a2808a9e905faf14ec3009c8742fe1' => 
    array (
      0 => '/var/www/mpws/rc_1.0/web/default/v1.0/template/component/simpleFieldLabel.html',
      1 => 1350327225,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '16360540985080405b4a5fd4-44055952',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    '_labelKey' => 0,
    '_controlType' => 0,
    '_controlName' => 0,
    '_resource' => 0,
    'CURRENT' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5080405b4bb385_10772821',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5080405b4bb385_10772821')) {function content_5080405b4bb385_10772821($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_capitalize')) include '/media/sda3/Develop/github/web/mpws/engine/system/extension/Smarty-3.1.11/libs/plugins/modifier.capitalize.php';
?><div class="MPWSFieldLabel MPWSFieldLabel<?php echo smarty_modifier_capitalize($_smarty_tpl->tpl_vars['_labelKey']->value);?>
">
    <label for="MPWSControl<?php echo $_smarty_tpl->tpl_vars['_controlType']->value;?>
<?php echo $_smarty_tpl->tpl_vars['_controlName']->value;?>
ID">
        <span class="MPWSText"><?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->{"objectProperty_".((string)$_smarty_tpl->tpl_vars['_resource']->value)."_".((string)$_smarty_tpl->tpl_vars['_labelKey']->value)};?>
</span>
    </label>
</div><?php }} ?>