<?php /* Smarty version Smarty-3.1.11, created on 2012-10-10 01:14:38
         compiled from "/var/www/mpws/rc_1.0/web/default/v1.0/template/widget/dataTableView.html" */ ?>
<?php /*%%SmartyHeaderCode:10284384755073417f827212-83124874%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b43d79ad5db39a467ed90062c78be6d51ad27400' => 
    array (
      0 => '/var/www/mpws/rc_1.0/web/default/v1.0/template/widget/dataTableView.html',
      1 => 1349820870,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '10284384755073417f827212-83124874',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5073417f8bf488_77023854',
  'variables' => 
  array (
    'CURRENT' => 0,
    'DTV_CFG' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5073417f8bf488_77023854')) {function content_5073417f8bf488_77023854($_smarty_tpl) {?>



<?php $_smarty_tpl->tpl_vars["DTV_CFG"] = new Smarty_variable($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->{"objectConfiguration_widget_dataTableView".((string)$_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['NAME'])}, null, 0);?>

<div id="MPWSWidget<?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['NAME'];?>
ID" class="MPWSWidget MPWSWidget<?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['NAME'];?>
">
    
<?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_objectSummary, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_ownerName'=>$_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['NAME']), 0);?>


<?php if (!empty($_smarty_tpl->tpl_vars['DTV_CFG']->value['searchbox'])&&$_smarty_tpl->tpl_vars['DTV_CFG']->value['searchbox']['enabled']){?>
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_searchBox, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_confing'=>$_smarty_tpl->tpl_vars['DTV_CFG']->value,'_data'=>$_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['DATA']['SEARCH'],'_ownerName'=>$_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['NAME']), 0);?>

<?php }?>

<?php if (!empty($_smarty_tpl->tpl_vars['DTV_CFG']->value['filtering'])&&$_smarty_tpl->tpl_vars['DTV_CFG']->value['filtering']['enabled']){?>
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_quickFiltering, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_confing'=>$_smarty_tpl->tpl_vars['DTV_CFG']->value,'_ownerName'=>$_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['NAME']), 0);?>

<?php }?>

<?php if (!empty($_smarty_tpl->tpl_vars['DTV_CFG']->value['datatable'])){?>
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_dataTableTopActions, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_confing'=>$_smarty_tpl->tpl_vars['DTV_CFG']->value,'_data'=>$_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['DATA']['SOURCE']['RECORDS'],'_ownerName'=>$_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['NAME']), 0);?>

    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_dataTable, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_confing'=>$_smarty_tpl->tpl_vars['DTV_CFG']->value,'_data'=>$_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['DATA']['SOURCE']['RECORDS'],'_ownerName'=>$_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['NAME']), 0);?>

<?php }?>

<?php if (!empty($_smarty_tpl->tpl_vars['DTV_CFG']->value['pagination'])&&$_smarty_tpl->tpl_vars['DTV_CFG']->value['pagination']['enabled']){?>
    <?php echo $_smarty_tpl->getSubTemplate ($_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectTemplatePath_component_pagingBar, $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 9999, null, array('_confing'=>$_smarty_tpl->tpl_vars['DTV_CFG']->value,'_data'=>$_smarty_tpl->tpl_vars['CURRENT']->value['SOURCE']['DATA']['PAGING']), 0);?>

<?php }?>

</div><?php }} ?>