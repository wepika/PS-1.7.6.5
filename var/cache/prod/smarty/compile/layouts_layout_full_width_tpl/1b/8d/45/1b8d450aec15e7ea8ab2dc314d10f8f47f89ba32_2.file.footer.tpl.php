<?php
/* Smarty version 3.1.33, created on 2020-07-17 11:52:09
  from 'C:\wamp64\www\sandbox\1.7.6.5\themes\classic\templates\checkout\_partials\footer.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.33',
  'unifunc' => 'content_5f1174c9a70483_19605144',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    '1b8d450aec15e7ea8ab2dc314d10f8f47f89ba32' => 
    array (
      0 => 'C:\\wamp64\\www\\sandbox\\1.7.6.5\\themes\\classic\\templates\\checkout\\_partials\\footer.tpl',
      1 => 1593179646,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
  ),
),false)) {
function content_5f1174c9a70483_19605144 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_loadInheritance();
$_smarty_tpl->inheritance->init($_smarty_tpl, false);
$_smarty_tpl->inheritance->instanceBlock($_smarty_tpl, 'Block_4326929145f1174c9a66206_68348054', 'footer');
?>

<?php }
/* {block 'footer'} */
class Block_4326929145f1174c9a66206_68348054 extends Smarty_Internal_Block
{
public $subBlocks = array (
  'footer' => 
  array (
    0 => 'Block_4326929145f1174c9a66206_68348054',
  ),
);
public function callBlock(Smarty_Internal_Template $_smarty_tpl) {
?>

<div class="text-sm-center">
  <?php echo call_user_func_array( $_smarty_tpl->smarty->registered_plugins[Smarty::PLUGIN_FUNCTION]['l'][0], array( array('s'=>'%copyright% %year% - Ecommerce software by %prestashop%','sprintf'=>array('%prestashop%'=>'PrestaShop™','%year%'=>date('Y'),'%copyright%'=>'©'),'d'=>'Shop.Theme.Global'),$_smarty_tpl ) );?>

</div>
<?php
}
}
/* {/block 'footer'} */
}
