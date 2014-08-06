<table class="table parameter">
<tbody>
	<tr>
		<td class="col-md-3">
		<strong>{$parameter_name}</strong><br/><br/>
		{if $config.isrest}
			<span class="label label-default">REST</span>
		{/if}
		{if $config.required or array_key_exists("request_required_param", $config)}
			<span class="label label-danger">requerido</span>
		{/if}
		{if !$config.required and !array_key_Exists("request_required_param", $config)}
			<span class="label label-info">opcional</span>
		{/if}
		</td>
		<td>
		{if !empty($config.param_desc)}
			<p class="lead">{$config.param_desc[0]}</p>
			<hr/>
		{/if}
		{if !empty($config.param_options)}
			{assign var=opts value=" "|explode:$config.param_options[0]}
			<div class="alert alert-warning">
				Valores permitidos:<br>
				<ul>
				{foreach from=$opts item=opt}
					<li>{$opt}</li>
				{/foreach}
				</ul>
			</div>
		{/if}
		{if !empty($config.param_example)}
			<table class="table">
			<thead>
				<tr><th class="col-md-1">Valor ejemplo</th><th class="col-md-4">Descripción</th></tr>
			</thead>
			<tbody>
			{foreach from=$config.param_example item=example}
			{assign var=ex value="|"|explode:$example}
			<tr><td>{$ex[0]}</td><td>{$ex[1]}</td></tr>
			{/foreach}
			</tbody>
			</table>
		{/if}

		{if !empty($config.param_note)}
			<p class="alert alert-info">{$config.param_note[0]}</p>
		{/if}
		{if !empty($config.param_dependency)}
			<div class="alert alert-info">
				Si se especifica, también se debe de especificar:
				<ul>
				{foreach from=$config.param_dependency item=dep}
					<li>{$dep}</li>
				{/foreach}
				</ul>
			</div>
		{/if}
		</td>
	</tr>
</tbody>
</table>
