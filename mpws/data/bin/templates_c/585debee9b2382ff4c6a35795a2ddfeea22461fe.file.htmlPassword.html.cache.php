<?php /* Smarty version Smarty-3.1.11, created on 2012-10-27 00:04:45
         compiled from "/var/www/mpws/rc_1.0/web/default/v1.0/template/control/htmlPassword.html" */ ?>
<?php /*%%SmartyHeaderCode:161256854550817f102dc203-18927529%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '585debee9b2382ff4c6a35795a2ddfeea22461fe' => 
    array (
      0 => '/var/www/mpws/rc_1.0/web/default/v1.0/template/control/htmlPassword.html',
      1 => 1351275592,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '161256854550817f102dc203-18927529',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50817f1037bb48_09290280',
  'variables' => 
  array (
    '_name' => 0,
    '_controlCssName' => 0,
    '_value' => 0,
    '_size' => 0,
    '_renderMode' => 0,
    '_limit' => 0,
    '_controlCssNameCustom' => 0,
    '_controlRenderMode' => 0,
    '_controlName' => 0,
    '_controlValue' => 0,
    '_controlSize' => 0,
    '_controlLimit' => 0,
    'CURRENT' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50817f1037bb48_09290280')) {function content_50817f1037bb48_09290280($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_capitalize')) include '/media/sda3/Develop/github/web/mpws/engine/system/extension/Smarty-3.1.11/libs/plugins/modifier.capitalize.php';
?>
<?php $_smarty_tpl->tpl_vars['_controlName'] = new Smarty_variable("mpws_field_".((string)mb_strtolower($_smarty_tpl->tpl_vars['_name']->value, 'UTF-8')), null, 0);?>
<?php $_smarty_tpl->tpl_vars['_controlCssName'] = new Smarty_variable('Password', null, 0);?>
<?php $_smarty_tpl->tpl_vars['_controlCssNameCustom'] = new Smarty_variable(((string)$_smarty_tpl->tpl_vars['_controlCssName']->value).((string)$_smarty_tpl->tpl_vars['_name']->value), null, 0);?>
<?php $_smarty_tpl->tpl_vars['_controlValue'] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['_value']->value)===null||$tmp==='' ? libraryRequest::getPostFormField(mb_strtolower($_smarty_tpl->tpl_vars['_name']->value, 'UTF-8')) : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars['_controlSize'] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['_size']->value)===null||$tmp==='' ? 25 : $tmp), null, 0);?>
<?php $_smarty_tpl->tpl_vars['_controlLimit'] = new Smarty_variable('', null, 0);?>
<?php $_smarty_tpl->tpl_vars['_controlRenderMode'] = new Smarty_variable((($tmp = @$_smarty_tpl->tpl_vars['_renderMode']->value)===null||$tmp==='' ? 'normal' : $tmp), null, 0);?>


<?php if (isset($_smarty_tpl->tpl_vars['_limit']->value)){?>
    <?php $_smarty_tpl->tpl_vars['_controlLimit'] = new Smarty_variable(" maxlength=\"".((string)$_smarty_tpl->tpl_vars['_limit']->value)."\"", null, 0);?>
<?php }?>



<div class="MPWSControlField MPWSControlField<?php echo $_smarty_tpl->tpl_vars['_controlCssName']->value;?>
 MPWSControlField<?php echo $_smarty_tpl->tpl_vars['_controlCssNameCustom']->value;?>
 MPWSControlRenderMode<?php echo smarty_modifier_capitalize($_smarty_tpl->tpl_vars['_controlRenderMode']->value);?>
">
    
<?php if ($_smarty_tpl->tpl_vars['_controlRenderMode']->value=='normal'||$_smarty_tpl->tpl_vars['_controlRenderMode']->value=='error'){?>
    
    
    <input id="MPWSControl<?php echo $_smarty_tpl->tpl_vars['_controlCssNameCustom']->value;?>
ID" type="password" name="<?php echo $_smarty_tpl->tpl_vars['_controlName']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['_controlValue']->value;?>
" size="<?php echo $_smarty_tpl->tpl_vars['_controlSize']->value;?>
"<?php echo $_smarty_tpl->tpl_vars['_controlLimit']->value;?>
 class="MPWSControl MPWSControl<?php echo $_smarty_tpl->tpl_vars['_controlCssName']->value;?>
 MPWSControl<?php echo $_smarty_tpl->tpl_vars['_controlCssNameCustom']->value;?>
">
    <?php if ($_smarty_tpl->tpl_vars['_controlRenderMode']->value=='error'){?>
        <span class="MPWSText MPWSTextAsterix">*</span>
    <?php }?>

<?php }elseif($_smarty_tpl->tpl_vars['_controlRenderMode']->value=='hidden'){?>
    
    
    <span class="MPWSControlReadOnlyValue"><?php echo $_smarty_tpl->tpl_vars['_controlValue']->value;?>
</span>
    <input type="hidden" name="<?php echo $_smarty_tpl->tpl_vars['_controlName']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['_controlValue']->value;?>
"/>
         
<?php }else{ ?>
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_exception, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_message'=>"Wrong control render mode",'_tpl'=>basename($_smarty_tpl->source->filepath)), 0);?>

<?php }?>
    
</div><?php }} ?>