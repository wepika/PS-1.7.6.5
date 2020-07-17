<?php
/* Smarty version 3.1.33, created on 2020-07-09 20:56:31
  from 'C:\wamp64\www\sandbox\1.7.6.5\admin571bnis8f\themes\default\template\content.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_5f07685f384ca6_66395130',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'd8addfee908b46dfdb0c906a5351dce7e9caa613' => 
    array (
      0 => 'C:\\wamp64\\www\\sandbox\\1.7.6.5\\admin571bnis8f\\themes\\default\\template\\content.tpl',
      1 => 1593179649,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5f07685f384ca6_66395130 (Smarty_Internal_Template $_smarty_tpl) {
?><div id="ajax_confirmation" class="alert alert-success hide"></div>
<div id="ajaxBox" style="display:none"></div>


<div class="row">
	<div class="col-lg-12">
		<?php if (isset($_smarty_tpl->tpl_vars['content']->value)) {?>
			<?php echo $_smarty_tpl->tpl_vars['content']->value;?>

		<?php }?>
	</div>
</div>
<?php }
}
