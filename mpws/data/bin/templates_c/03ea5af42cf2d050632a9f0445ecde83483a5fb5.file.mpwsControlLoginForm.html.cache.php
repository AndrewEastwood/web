<?php /* Smarty version Smarty-3.1.11, created on 2012-10-18 08:43:50
         compiled from "/var/www/mpws/web/default/v1.0/template/component/mpwsControlLoginForm.html" */ ?>
<?php /*%%SmartyHeaderCode:1621658757507f9716e157b2-82410007%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '03ea5af42cf2d050632a9f0445ecde83483a5fb5' => 
    array (
      0 => '/var/www/mpws/web/default/v1.0/template/component/mpwsControlLoginForm.html',
      1 => 1350538859,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1621658757507f9716e157b2-82410007',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODEL' => 0,
    '_action' => 0,
    '_formAction' => 0,
    'CURRENT' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_507f9716e4ce66_90736710',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_507f9716e4ce66_90736710')) {function content_507f9716e4ce66_90736710($_smarty_tpl) {?><div class="MPWSControl MPWSControlComplex MPWSControlComplexLoginForm">

<?php $_smarty_tpl->tpl_vars['_formAction'] = new Smarty_variable($_smarty_tpl->tpl_vars['MODEL']->value['CUSTOM']['LOGIN_URL'], null, 0);?>
<?php if (isset($_smarty_tpl->tpl_vars['_action']->value)){?>
    <?php $_smarty_tpl->tpl_vars['_formAction'] = new Smarty_variable($_smarty_tpl->tpl_vars['_action']->value, null, 0);?>
<?php }?>

<form action="?<?php echo $_smarty_tpl->tpl_vars['_formAction']->value;?>
" method="POST">
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_controlFieldSwitcher, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_type'=>'textbox','_name'=>'Login','_ownerName'=>"authBox"), 0);?>

    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_controlFieldSwitcher, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_type'=>'password','_name'=>'Password','_value'=>'','_ownerName'=>"authBox"), 0);?>

    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_simpleFormButtons, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_buttons'=>array('SignIn'),'_page'=>'Login'), 0);?>

</form>
    
</div><?php }} ?>