<?php /* Smarty version Smarty-3.1.11, created on 2012-09-30 23:47:58
         compiled from "/var/www/mpws/rc_1.0/web/plugin/toolbox/template/widget/tableActiveUsers.html" */ ?>
<?php /*%%SmartyHeaderCode:11758149695068affe53ba46-50830862%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f943691f40dd4677999b3f43de60cb5823f45db0' => 
    array (
      0 => '/var/www/mpws/rc_1.0/web/plugin/toolbox/template/widget/tableActiveUsers.html',
      1 => 1348952762,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '11758149695068affe53ba46-50830862',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SELF' => 0,
    'rowData' => 0,
    'cellData' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5068affe581c53_75778143',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5068affe581c53_75778143')) {function content_5068affe581c53_75778143($_smarty_tpl) {?><b>ACTIVE USERS</b>
<div class="MPWSDivTable">
<?php  $_smarty_tpl->tpl_vars['rowData'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['rowData']->_loop = false;
 $_smarty_tpl->tpl_vars['rowIndex'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['SELF']->value['DATA']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['rowData']->key => $_smarty_tpl->tpl_vars['rowData']->value){
$_smarty_tpl->tpl_vars['rowData']->_loop = true;
 $_smarty_tpl->tpl_vars['rowIndex']->value = $_smarty_tpl->tpl_vars['rowData']->key;
?>
    <div class="MPWSDivTableRow">
    <?php  $_smarty_tpl->tpl_vars['cellData'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['cellData']->_loop = false;
 $_smarty_tpl->tpl_vars['cellIndex'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['rowData']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['cellData']->key => $_smarty_tpl->tpl_vars['cellData']->value){
$_smarty_tpl->tpl_vars['cellData']->_loop = true;
 $_smarty_tpl->tpl_vars['cellIndex']->value = $_smarty_tpl->tpl_vars['cellData']->key;
?>
        <div class="MPWSDivTableCell"><?php echo $_smarty_tpl->tpl_vars['cellData']->value;?>
</div>
    <?php } ?>
    </div>
<?php } ?>
</div>
<?php }} ?>