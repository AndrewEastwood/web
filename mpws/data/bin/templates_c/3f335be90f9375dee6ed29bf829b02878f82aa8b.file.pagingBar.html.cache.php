<?php /* Smarty version Smarty-3.1.11, created on 2012-10-09 22:13:46
         compiled from "/var/www/mpws/rc_1.0/web/default/v1.0/template/component/pagingBar.html" */ ?>
<?php /*%%SmartyHeaderCode:14873537655073417fb71ec0-16858364%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3f335be90f9375dee6ed29bf829b02878f82aa8b' => 
    array (
      0 => '/var/www/mpws/rc_1.0/web/default/v1.0/template/component/pagingBar.html',
      1 => 1349809909,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '14873537655073417fb71ec0-16858364',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.11',
  'unifunc' => 'content_5073417fba3853_79236632',
  'variables' => 
  array (
    'CURRENT' => 0,
    '_data' => 0,
    'link' => 0,
    'edgeName' => 0,
    'pageIndex' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5073417fba3853_79236632')) {function content_5073417fba3853_79236632($_smarty_tpl) {?><div id="MPWSComponenPagingBarID" class="MPWSComponent MPWSComponenPagingBar">
    
    <div class="MPWSBlock MPWSBlockSummary">
        <div class="MPWSDataRow">
            <label class="MPWSLabel"><?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectProperty_component_pagingBarSummaryFilteredRecords;?>
</label>
            <span class="MPWSValue"><?php echo $_smarty_tpl->tpl_vars['_data']->value['AVAILABLE'];?>
</span>
        </div>
        <div class="MPWSDataRow">
            <label class="MPWSLabel"><?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectProperty_component_pagingBarSummaryTotalRecords;?>
</label>
            <span class="MPWSValue"><?php echo $_smarty_tpl->tpl_vars['_data']->value['TOTAL'];?>
</span>
        </div>
        <div class="MPWSDataRow">
            <label class="MPWSLabel"><?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectProperty_component_pagingBarSummaryPageSize;?>
</label>
            <span class="MPWSValue"><?php echo $_smarty_tpl->tpl_vars['_data']->value['LIMIT'];?>
</span>
        </div>
        <div class="MPWSDataRow">
            <label class="MPWSLabel"><?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectProperty_component_pagingBarSummaryPageCount;?>
</label>
            <span class="MPWSValue"><?php echo $_smarty_tpl->tpl_vars['_data']->value['PAGES'];?>
</span>
        </div>
        <div class="MPWSDataRow">
            <label class="MPWSLabel"><?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->objectProperty_component_pagingBarSummaryCurrentPage;?>
</label>
            <span class="MPWSValue"><?php echo $_smarty_tpl->tpl_vars['_data']->value['CURRENT'];?>
</span>
        </div>
    </div>
    
    <div class="MPWSBlock MPWSBlockEdgeLinks">
    <?php  $_smarty_tpl->tpl_vars['link'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['link']->_loop = false;
 $_smarty_tpl->tpl_vars['edgeName'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['_data']->value['EDGES']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['link']->key => $_smarty_tpl->tpl_vars['link']->value){
$_smarty_tpl->tpl_vars['link']->_loop = true;
 $_smarty_tpl->tpl_vars['edgeName']->value = $_smarty_tpl->tpl_vars['link']->key;
?>
    <a href="?<?php echo $_smarty_tpl->tpl_vars['link']->value;?>
" class="MPWSLink MPWSLinkPaging"><?php echo $_smarty_tpl->tpl_vars['CURRENT']->value['OBJECT']->{"objectProperty_component_pagingBarEdgeLink".((string)$_smarty_tpl->tpl_vars['edgeName']->value)};?>
</a>
    <?php } ?>
    </div>
    
    <div class="MPWSBlock MPWSBlockPageLinks">
    <?php  $_smarty_tpl->tpl_vars['link'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['link']->_loop = false;
 $_smarty_tpl->tpl_vars['pageIndex'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['_data']->value['LINKS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['link']->key => $_smarty_tpl->tpl_vars['link']->value){
$_smarty_tpl->tpl_vars['link']->_loop = true;
 $_smarty_tpl->tpl_vars['pageIndex']->value = $_smarty_tpl->tpl_vars['link']->key;
?>
    <a href="?<?php echo $_smarty_tpl->tpl_vars['link']->value;?>
" class="MPWSLink MPWSLinkPaging"><?php echo $_smarty_tpl->tpl_vars['pageIndex']->value;?>
</a>
    <?php } ?>
    </div>
    
</div><?php }} ?>