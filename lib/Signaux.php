<?php

namespace Garradin\Plugin\Htmailer;

use Garradin\Config;
use Garradin\Security;
use Garradin\Template;
use Garradin\Plugin;

use KD2\SMTP;

class Signaux
{
	
    static public function send(array $params)
    {
        $plugin = new Plugin('htmailer');
        $config = Config::getInstance();
        
        $content = $params['content'];
        $pgp_key = $params['pgp_key'];
        $subject = str_replace("[".$config->get('nom_asso')."] ", "", $params['subject']);
        $to = $params['recipient'];
        $context = $params['context'];

        $headers = [];
        
        $tpl = \Garradin\Template::getInstance()->fromString(base64_decode($plugin->getConfig('template')));
        $tpl->setCompiledDir(\Garradin\SHARED_CACHE_ROOT);
        
        $tpl->assign('content', $content);
        $tpl->assign('subject', $subject);
        
        $html = $tpl->fetch();
        
        if ($pgp_key)
        {
            $content = Security::encryptWithPublicKey($pgp_key, $content);
        }
 
        $headers['From'] = sprintf('"%s" <%s>', sprintf('=?UTF-8?B?%s?=', base64_encode($config->get('nom_asso'))), $config->get('email_asso'));
        $headers['Return-Path'] = $config->get('email_asso');
 
        $headers['MIME-Version'] = '1.0';
        $headers['Content-type'] = 'text/html; charset=UTF-8';
 
        if ($context == \Garradin\Utils::EMAIL_CONTEXT_BULK)
        {
            $headers['Precedence'] = 'bulk';
        }
 
        $hash = sha1(uniqid() . var_export([$headers, $to, $subject, $html], true));
        $headers['Message-ID'] = sprintf('%s@%s', $hash, isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : gethostname());
 
        if (\Garradin\SMTP_HOST)
        {
            $const = '\KD2\SMTP::' . strtoupper(\Garradin\SMTP_SECURITY);
 
            if (!defined($const))
            {
                throw new \LogicException('Configuration: SMTP_SECURITY n\'a pas une valeur reconnue. Valeurs acceptées: STARTTLS, TLS, SSL, NONE.');
            }
 
            $secure = constant($const);
 
            $smtp = new KD2\SMTP(\Garradin\SMTP_HOST, \Garradin\SMTP_PORT, \Garradin\SMTP_USER, \Garradin\SMTP_PASSWORD, $secure);
            return $smtp->send($to, $subject, $html, $headers);
        }
        else
        {
            // Encodage du sujet
            $subject = sprintf('=?UTF-8?B?%s?=', base64_encode($subject));
            $raw_headers = '';
 
            // Sérialisation des entêtes
            foreach ($headers as $name=>$value)
            {
                $raw_headers .= sprintf("%s: %s\r\n", $name, $value);
            }
 
            return \mail($to, $subject, $html, $raw_headers);
        }
        
        
    }
}
