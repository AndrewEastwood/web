<?php /* Smarty version Smarty-3.1.11, created on 2012-10-11 12:44:40
         compiled from "/var/www/mpws/web/default/v1.0/template/component/quickFiltering.html" */ ?>
<?php /*%%SmartyHeaderCode:36925396650769508ed1fb2-21461377%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1a0890e2fef5427fab806f0d3f45ac8d91d053dd' => 
    array (
      0 => '/var/www/mpws/web/default/v1.0/template/component/quickFiltering.html',
      1 => 1349945264,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '36925396650769508ed1fb2-21461377',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    '_ownerName' => 0,
    'CURRENT' => 0,
    '_confing' => 0,
    '__prop__' => 0,
    'qfEntry' => 0,
    '_requestKey' => 0,
    '_keyAsc' => 0,
    '_keyDesc' => 0,
    '_filterAction' => 0,
    '_linkTextKey' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_50769508f3d9c3_13467993',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_50769508f3d9c3_13467993')) {function content_50769508f3d9c3_13467993($_smarty_tpl) {?>

<div id="MPWSComponenQuickFilteringID" class="MPWSComponent MPWSComponenQuickFiltering">
    <?php $_smarty_tpl->tpl_vars["__prop__"] = new Smarty_variable("objectProperty_custom_".((string)$_smarty_tpl->tpl_vars['_ownerName']->value), null, 0);?>
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_simpleHeader, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_title'=>$_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectProperty_component_quickFilteringHeader), 0);?>

    <?php  $_smarty_tpl->tpl_vars['qfEntry'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['qfEntry']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['_confing']->value['filtering']['fields']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['qfEntry']->key => $_smarty_tpl->tpl_vars['qfEntry']->value){
$_smarty_tpl->tpl_vars['qfEntry']->_loop = true;
?>
    <div class="MPWSBlock">
        <div class="MPWSDataRow">
            <label class="MPWSLabel">
                <?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectProperty_component_quickFilteringFieldPrefix;?>

                <?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->{((string)$_smarty_tpl->tpl_vars['__prop__']->value)."QuickFilteringField".((string)$_smarty_tpl->tpl_vars['qfEntry']->value)};?>

                <?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectProperty_component_quickFilteringFieldSuffix;?>

            </label>
            <span class="MPWSValue">
                <?php $_smarty_tpl->tpl_vars['_keyAsc'] = new Smarty_variable(((string)$_smarty_tpl->tpl_vars['qfEntry']->value).".asc", null, 0);?>
                <?php $_smarty_tpl->tpl_vars['_keyDesc'] = new Smarty_variable(((string)$_smarty_tpl->tpl_vars['qfEntry']->value).".desc", null, 0);?>
                <?php $_smarty_tpl->tpl_vars['_requestKey'] = new Smarty_variable(libraryRequest::getValue($_smarty_tpl->tpl_vars['_confing']->value['filtering']['sortKey']), null, 0);?>
                
                <?php $_smarty_tpl->tpl_vars['_filterAction'] = new Smarty_variable(libraryRequest::getNewUrl('sort',libraryUtils::valueSelect($_smarty_tpl->tpl_vars['_requestKey']->value,$_smarty_tpl->tpl_vars['_keyAsc']->value,$_smarty_tpl->tpl_vars['_keyDesc']->value,$_smarty_tpl->tpl_vars['_keyAsc']->value)), null, 0);?>
                <?php $_smarty_tpl->tpl_vars['_linkTextKey'] = new Smarty_variable(libraryUtils::valueSelect($_smarty_tpl->tpl_vars['_requestKey']->value,$_smarty_tpl->tpl_vars['_keyAsc']->value,"DESC","ASC"), null, 0);?>
                <a href="?<?php echo $_smarty_tpl->tpl_vars['_filterAction']->value;?>
#MPWSComponenQuickFilteringID">
                    <?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->{"objectProperty_component_quickFilteringAction".((string)$_smarty_tpl->tpl_vars['_linkTextKey']->value)};?>

                </a>
            </span>
        </div>
    </div>
    <?php } ?>
</div><?php }} ?>