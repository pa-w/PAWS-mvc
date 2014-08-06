{if !empty($return_key) && !empty($return_value)}
{if is_array($return_value) && !array_key_exists("value", $return_value)}
<tr><th colspan="3">{$return_key}</th></tr>
	{foreach from=$return_value key=rkey item=rval}
		{include file='docs/action_return_resume.tpl' return_key=$rkey return_value=$rval parent=$parent+1 notes=$notes[$rkey]}
	{/foreach}
{else}
<tr><td>{$return_key}</td><td>{$return_value}</td><td>{$notes}</td></tr>
{/if}
{/if}
