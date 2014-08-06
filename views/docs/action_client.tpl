<script type="text/javascript" src="/js/apiclient.js"></script>
<h4>Prueba este m&eacute;todo</h4>
<form role="form" class="form-inline" id="apiclient">
<div class="row alert alert-info">
	<strong>
		{$method} /{$_Module}{if !empty($_Submodule)}/{$_Submodule}{/if}{if $_Action != "init"}/{$_Action}{/if}
		{foreach from=$me.rest_parameters item=rest}/
			<input type="text" name="{$rest.name}" placeholder="{$rest.name}" size="{strlen($rest.name)}" class="input-mini rest-param">
		{/foreach}.
	</strong>
	<select name="format" class="input-mini">
		<option value="json">JSON</option>
		<option value="xml">XML</option>
	</select>
</div>
<div class="row">
	<div class="col-lg-12">
			{foreach from=$me.parameters item=parameter key=parameter_name}
				{if $parameter.isrest != "1"}
					<div class="form-group col-lg-3 {if array_key_exists("request_required_param", $parameter)}has-error{/if}">
						<label for="{$parameter_name}" class="control-label">
							{$parameter_name}
						</label>
						<input type="text" name="{$parameter_name}" id="{$parameter_name}" class="form-control col-lg-1">
					</div>
				{/if}
			{/foreach}
	</div>
</div>
<br/>
<input type="hidden" name="method" value="{$method}">
<div id="loading_gif"><img src="/img/ajax-loader.gif"/></div>
<button id="boton_submit_client" type="submit" class="btn btn-primary">Ingresar</button>
<hr/>
<div class="row">
	<div class="col-lg-12">
		<pre id="api_resultado">
			Resultado
		</pre>
	</div>
</div>
</form>
