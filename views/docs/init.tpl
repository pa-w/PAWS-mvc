<h2>API de la Ciudad de M&eacute;xico</h2>
{if is_array($_Modules)}
{foreach from=$_Modules key=k item=module}
	{include file='docs/module_resume.tpl' module_name=$k module=$module path=""}
{/foreach}
{/if}
