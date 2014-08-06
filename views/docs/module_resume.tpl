{if !array_key_exists("hide", $module)}
<div class="module">
<h2><a href="/{$module_name}.info">{$module.name[0]}</a></h2>
<p class="lead">{$module.description[0]}</p>
<hr/>
{if !empty($module.actions)}
	<table class="table">
		<thead>
		<tr>
			<th class="col-md-4">Recurso</th><th class="col-md-6">Descripci&oacute;n</th>
		</tr>
		</thead>
		<tbody>
		{foreach from=$module.actions item=action key=a}
			{include file='docs/action_resume.tpl' action=$action path="/{$module_name}" action_name=$a}
		{/foreach}
		</tbody>
	</table>
{/if}
{if is_array($module.submodules)}
	{foreach from=$module.submodules key=m item=submodule}
		{include file='docs/submodule_resume.tpl' module_name=$m module=$submodule path="/{$module_name}"}
	{/foreach}
{/if}
</div>
{/if}
