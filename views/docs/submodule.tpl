<div class="module submodule">
{assign var="module" value=$_Modules[$_Module].submodules[$_Submodule]}
{include file='docs/module_detail.tpl' module=$module path="/{$_Module}/{$_Submodule}"}
</div>
