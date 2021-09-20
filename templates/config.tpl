{include file="admin/_head.tpl" title="Configuration — %s"|args:$plugin.nom current="plugin_%s"|args:$plugin.id}

{form_errors}

<form method="post" action="{$self_url}">

    <fieldset>
        <legend>Configuration</legend>
        <dl>
            {input type="textarea" name="template" value="" default=$template label="Corps du message HTML"}
        </dl>
    </fieldset>

	{literal}<p>Les balises {$subject} et {$content} seront remplacées par le titre et le corps du message.</p>{/literal}
    
    <p class="submit">
        {csrf_field key="config_plugin_%s"|args:$plugin.id}
        {button type="submit" class="main" name="save" label="Enregistrer" shape="right"}
    </p>
</form>

{include file="admin/_foot.tpl"}
