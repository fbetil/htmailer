<?php

namespace Garradin;

$session->requireAccess($session::SECTION_CONFIG, $session::ACCESS_ADMIN);

if (f('save'))
{
    $form->check('config_plugin_' . $plugin->id(), [
        'template' => 'string',
    ]);

    if (!$form->hasErrors())
    {
        try {
            $plugin->setConfig('template', (string) base64_encode(f('template')));
        }
        catch (UserException $e)
        {
            $form->addError($e->getMessage());
        }
    }
}

$tpl->assign('template', base64_decode($plugin->getConfig('template')));

$tpl->display(PLUGIN_ROOT . '/templates/config.tpl');
