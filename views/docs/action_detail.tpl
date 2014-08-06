{assign var="me" value=$action[$method][$params]}
<div class="col-lg-12">
	<div class="row">
		<h1>{$method} /{$_Module}{if !empty($_Submodule)}/{$_Submodule}{/if}{if $_Action != "init"}/{$_Action}{/if}{foreach from=$me.rest_parameters item=rest}/:{$rest.name}{/foreach} </h1>
		<div class="col-lg-9">
			<p class="lead">{$me.description}</p>
			<hr/>
			{include file="docs/action_client.tpl"}
		</div>
		<div class="col-lg-3">
			<h4>Informaci&oacute;n</h4>
			<table class="table table-striped">
				<tr>
					<td>Autenticaci&oacute;n</td>
					<th>
						{if !empty($me.auth_required)}
							<a href="/tutoriales/auth/{$me.auth_required}">{$me.auth_required}</a>
						{else}
						No
						{/if}
					</th>
				</tr>
				<tr>
					<td>Limitado</td>
					<th>
						{if !empty($me.rate_limit)}
							<a href="/tutoriales/auth/limit">{$me.rate_limit} cada 15 minutos</a>
						{else}
						No
						{/if}
					</th>
				</tr>
				<tr>
					<td>Categor&iacute;a</td>
					<th>{$me.category}</th>
				</tr>
				<tr>
					<td>Versi&oacute;n</td>
					<th>{$me.version}</th>
				</tr>
			</table>
		</div>
	</div>
	<div class="row">
		{if is_array($me.parameters)}
			<h3>Par&aacute;metros</h3>
			{foreach from=$me.parameters item=parameter key=parameter_name}
				{include file='docs/parameter_detail.tpl' parameter_name=$parameter_name config=$parameter}
			{/foreach}
		{/if}
		{if !empty($me.returns) || !empty($me.return_value)}
			<h3>Respuesta</h3>
			{if !empty($me.returns)}<h4>{$me.returns}</h4>{/if}
			{if !empty($me.return_value)}
				<table class="table">
					<thead>
						<tr><th class="col-lg-1">Campo</th><th class="col-lg-5">Descripción</th><th class="col-lg-4">Notas</th></tr>
					</thead>
					{foreach from=$me.return_value key=return_key item=return_value}
						{include file='docs/action_return_resume.tpl' return_key=$return_key return_value=$return_value parent=0 notes=$me.return_value_notes[$return_key]}
					{/foreach}	
				</table>
				{if !empty($me.return_value_json)}
					<h4>Ejemplo de respuesta (JSON)</h4>
					<div class="json">
						{$me.return_value_json}
					</div>
				{/if}
				{if !empty($me.return_value_xml)}
					<h4>Ejemplo de respuesta (XML)</h4>
					<div class="xml">
						{$me.return_value_xml}
					</div>
				{/if}
			{/if}
		{/if}
	</div>
</div>
