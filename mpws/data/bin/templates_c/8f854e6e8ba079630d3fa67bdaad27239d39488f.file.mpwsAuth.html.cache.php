<?php /* Smarty version Smarty-3.1.11, created on 2012-10-24 11:56:54
         compiled from "/var/www/mpws/web/default/v1.0/template/control/mpwsAuth.html" */ ?>
<?php /*%%SmartyHeaderCode:207016554350814ffb9c1c03-33896720%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8f854e6e8ba079630d3fa67bdaad27239d39488f' => 
    array (
      0 => '/var/www/mpws/web/default/v1.0/template/control/mpwsAuth.html',
      1 => 1351067541,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '207016554350814ffb9c1c03-33896720',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50814ffba167f7_85547147',
  'variables' => 
  array (
    '_action' => 0,
    'MODEL' => 0,
    '_formAction' => 0,
    'CURRENT' => 0,
    '_controlOwner' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50814ffba167f7_85547147')) {function content_50814ffba167f7_85547147($_smarty_tpl) {?><?php $_smarty_tpl->tpl_vars['_controlOwner'] = new Smarty_variable("MpwsAuthBox", null, 0);?>
<div class="MPWSControl MPWSControlMpws MPWSControlMpwsAuth">
<?php $_smarty_tpl->tpl_vars['_formAction'] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['_action']->value)===null||$tmp==='' ? $_smarty_tpl->tpl_vars['MODEL']->value['CUSTOM']['LOGIN_URL'] : $tmp), null, 0);?>
<form action="?<?php echo $_smarty_tpl->tpl_vars['_formAction']->value;?>
" method="POST" class="MPWSForm">
    <div class="MPWSFormHeader">
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_simple_header, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_key'=>$_smarty_tpl->tpl_vars['_controlOwner']->value), 0);?>

    </div>
    <div class="MPWSFormBody">
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_trigger_control, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_type'=>'textbox','_name'=>'Login','_controlOwner'=>$_smarty_tpl->tpl_vars['_controlOwner']->value), 0);?>

    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_trigger_control, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_type'=>'password','_name'=>'Password','_value'=>false,'_controlOwner'=>$_smarty_tpl->tpl_vars['_controlOwner']->value), 0);?>

    </div>
    <div class="MPWSFormFooter">
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_control_mpwsFormButtons, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_buttons'=>array('SignIn'),'_controlOwner'=>$_smarty_tpl->tpl_vars['_controlOwner']->value), 0);?>

    </div>
</form>
</div><?php }} ?>