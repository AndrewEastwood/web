<?php /* Smarty version Smarty-3.1.11, created on 2012-10-11 12:44:23
         compiled from "/var/www/mpws/web/default/v1.0/template/component/menu.html" */ ?>
<?php /*%%SmartyHeaderCode:1274395105507694f7559da2-35960942%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'cd33b4b0e2534893b619a4efbe9e33a6eea00cf6' => 
    array (
      0 => '/var/www/mpws/web/default/v1.0/template/component/menu.html',
      1 => 1349945264,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1274395105507694f7559da2-35960942',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    '_OBJ' => 0,
    'CURRENT' => 0,
    'OBJECT' => 0,
    'DOs' => 0,
    '_showDescription' => 0,
    '_items' => 0,
    'keyvar' => 0,
    'DISPLAY_OBJECT' => 0,
    'itemvar' => 0,
    '_linkText' => 0,
    'showDescription' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_507694f75da7d6_64459072',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_507694f75da7d6_64459072')) {function content_507694f75da7d6_64459072($_smarty_tpl) {?><?php if (!is_callable('smarty_modifier_capitalize')) include '/mydata/GitHub/web/mpws/engine/system/extension/Smarty-3.1.11/libs/plugins/modifier.capitalize.php';
?><?php $_smarty_tpl->tpl_vars["DOs"] = new Smarty_variable(array(), null, 0);?>

<?php if ((isset($_smarty_tpl->tpl_vars['_OBJ']->value))){?>
    <?php $_smarty_tpl->tpl_vars["DOs"] = new Smarty_variable(array($_smarty_tpl->tpl_vars['_OBJ']->value), null, 0);?>
<?php }?>

<?php $_smarty_tpl->createLocalArrayVariable('DOs', null, 0);
$_smarty_tpl->tpl_vars['DOs']->value[] = $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT'];?>
<?php $_smarty_tpl->createLocalArrayVariable('DOs', null, 0);
$_smarty_tpl->tpl_vars['DOs']->value[] = $_smarty_tpl->tpl_vars['OBJECT']->value['SITE'];?>

<?php $_smarty_tpl->tpl_vars["DISPLAY_OBJECT"] = new Smarty_variable(glGetFirstNonEmptyValue($_smarty_tpl->tpl_vars['DOs']->value), null, 0);?>

<?php $_smarty_tpl->tpl_vars['showDescription'] = new Smarty_variable(false, null, 0);?>
<?php if (isset($_smarty_tpl->tpl_vars['_showDescription']->value)){?>
    <?php $_smarty_tpl->tpl_vars['showDescription'] = new Smarty_variable($_smarty_tpl->tpl_vars['_showDescription']->value, null, 0);?>
<?php }?>

<div class="MPWSComponent MPWSComponenMenu">
    <?php if (isset($_smarty_tpl->tpl_vars['_items']->value)){?>
    <ul class="MPWSList MPWSListMenu">
    <?php  $_smarty_tpl->tpl_vars['itemvar'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['itemvar']->_loop = false;
 $_smarty_tpl->tpl_vars['keyvar'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['_items']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['itemvar']->key => $_smarty_tpl->tpl_vars['itemvar']->value){
$_smarty_tpl->tpl_vars['itemvar']->_loop = true;
 $_smarty_tpl->tpl_vars['keyvar']->value = $_smarty_tpl->tpl_vars['itemvar']->key;
?>
        <li class="MPWSListItem MPWSListItemMenu">
            <?php $_smarty_tpl->tpl_vars['_linkText'] = new Smarty_variable($_smarty_tpl->tpl_vars['DISPLAY_OBJECT']->value->{"objectProperty_display_menuText".((string)smarty_modifier_capitalize($_smarty_tpl->tpl_vars['keyvar']->value,0,1))}, null, 0);?>
            <a href="<?php echo $_smarty_tpl->tpl_vars['itemvar']->value['link'];?>
" target="<?php echo $_smarty_tpl->tpl_vars['itemvar']->value['target'];?>
" class="MPWSLink" title="<?php echo $_smarty_tpl->tpl_vars['_linkText']->value;?>
">
                <span class="MPWSText MPWSTextTitle"><?php echo $_smarty_tpl->tpl_vars['_linkText']->value;?>
</span>
                <?php if ($_smarty_tpl->tpl_vars['showDescription']->value){?>
                <span class="MPWSText MPWSTextDescription"><?php echo $_smarty_tpl->tpl_vars['DISPLAY_OBJECT']->value->{"objectProperty_display_menuTextDescription".((string)smarty_modifier_capitalize($_smarty_tpl->tpl_vars['keyvar']->value,0,1))};?>
</span>
                <?php }?>
            </a>
            <?php if (isset($_smarty_tpl->tpl_vars['itemvar']->value['contains'])&&($_smarty_tpl->tpl_vars['itemvar']->value['contains']=='__PLUGINS__')){?>
                <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['DISPLAY_OBJECT']->value->objectTemplatePath_component_menuPlugins, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array(), 0);?>

            <?php }?>
        </li>
    <?php } ?>
    </ul>
    <?php }?>
</div><?php }} ?>