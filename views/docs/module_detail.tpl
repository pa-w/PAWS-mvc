<h1>{$module.name[0]}</h1>
<p class="lead">{$module.description[0]}</p>
{if !empty($module.actions)}
<table class="table">
<tr>
	<thead>
		<th class="col-md-4">Recurso</th>
		<th class="col-md-6">Descripci&oacute;n</th>
	</thead>
</tr>
{foreach from=$module.actions item=methods key=action_name}
	{foreach from=$methods item=rest key=method}
		{foreach from=$rest item=action key=rest_params}
		<tr>
			<td><a href="{$path}/{$action_name}{if !empty($action.rest_parameters)}{foreach from=$action.rest_parameters item=parameter}/{$parameter.name}{/foreach}{/if}.info?method={$method}">{$method} {$path}{if $action_name != "init"}/{$action_name}{/if}{if !empty($action.rest_parameters)}{foreach from=$action.rest_parameters item=parameter}/:{$parameter.name}{/foreach}{/if}</a></td>
			<td>{$action.short_desc}</td>
		</tr>
		{/foreach}
	{/foreach}
{/foreach}
</table>
{/if}
{foreach from=$module.submodules item=submodule key=submodule_name}
	{assign var="module" value=$submodule}
	{include file='docs/module_detail.tpl' module=$module path="{$path}/{$submodule_name}"}
{/foreach}
