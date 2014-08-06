{assign var="request" value="/"|explode:$smarty.server.REQUEST_URI}
{assign var="count" value=count($request)-1}
{if !empty($_Submodule)}
	{assign var="action" value=$_Modules[$_Module].submodules[$_Submodule].actions[$_Action]} 
	{assign var="rest_params" value=3}
{else}
	{assign var="action" value=$_Modules[$_Module].actions[$_Action]}
	{assign var="rest_params" value=2}
{/if}
{include file='docs/action_detail.tpl' action=$action method=$smarty.get.method params=$count-$rest_params}
