{foreach from=$action key=method item=item}
	{foreach from=$item item=rest key=param_count name=endpoints}
		<tr>
			<td>
			<a href="{$path}/{$action_name}{if is_array($rest.rest_parameters)}{foreach from=$rest.rest_parameters key=param_name item=param_info}/{$param_info.name}{/foreach}{/if}.info?method={$method}">
				{$method} {$path}{if $action_name != "init"}/{$action_name}{/if}{if is_array($rest.rest_parameters)}{foreach from=$rest.rest_parameters key=param_name item=param_info}/:{$param_info.name}{/foreach}{/if}
			</a>
			</td>
			<td>
			{$rest.description}
			</td>
		</tr>
	{/foreach}
{/foreach}
