<?php /* Smarty version Smarty-3.1.11, created on 2012-10-18 08:59:57
         compiled from "/var/www/mpws/web/default/v1.0/template/component/messagesList.html" */ ?>
<?php /*%%SmartyHeaderCode:879152190507f9a57057041-29476527%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c51fd3eb68bcff0806f6c105bbe249bc3158e1a8' => 
    array (
      0 => '/var/www/mpws/web/default/v1.0/template/component/messagesList.html',
      1 => 1350539994,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '879152190507f9a57057041-29476527',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_507f9a570f28f2_54509084',
  'variables' => 
  array (
    '_realm' => 0,
    'CURRENT' => 0,
    'MODEL' => 0,
    'itemvar' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_507f9a570f28f2_54509084')) {function content_507f9a570f28f2_54509084($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_capitalize')) include '/mydata/GitHub/web/mpws/engine/system/extension/Smarty-3.1.11/libs/plugins/modifier.capitalize.php';
?><?php if (isset($_smarty_tpl->tpl_vars['_realm']->value)){?>
    <div id="MPWSComponentMessagesList<?php echo smarty_modifier_capitalize($_smarty_tpl->tpl_vars['_realm']->value,0,1);?>
ID" class="MPWSComponent MPWSComponentMessagesList MPWSComponentMessagesList<?php echo smarty_modifier_capitalize($_smarty_tpl->tpl_vars['_realm']->value,0,1);?>
">
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_simpleHeader, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_title'=>$_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectProperty_component_messagesListHeader), 0);?>

    <?php if (isset($_smarty_tpl->tpl_vars['MODEL']->value['MESSAGE'][$_smarty_tpl->tpl_vars['_realm']->value])){?>
        <ul class="MPWSList">
        <?php  $_smarty_tpl->tpl_vars['itemvar'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['itemvar']->_loop = false;
 $_smarty_tpl->tpl_vars['keyvar'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['MODEL']->value['MESSAGE'][$_smarty_tpl->tpl_vars['_realm']->value]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['itemvar']->key => $_smarty_tpl->tpl_vars['itemvar']->value){
$_smarty_tpl->tpl_vars['itemvar']->_loop = true;
 $_smarty_tpl->tpl_vars['keyvar']->value = $_smarty_tpl->tpl_vars['itemvar']->key;
?>
            <li class="MPWSListItem"><?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->{"objectProperty_message_".((string)$_smarty_tpl->tpl_vars['itemvar']->value)};?>
</li>
        <?php } ?>
        </ul>
    <?php }else{ ?>
        <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_exception, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_message'=>"Wrong realm type",'_tpl'=>basename($_smarty_tpl->source->filepath)), 0);?>

    <?php }?>
    </div>
<?php }?><?php }} ?>