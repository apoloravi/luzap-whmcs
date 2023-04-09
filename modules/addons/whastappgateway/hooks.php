<?php


/**
 *
 * HiperSend - Notificações Inteligentes via WhatsApp
 * Copyright (c) 2021-2022
 * GARANTIMOS A FUNCIONALIDADE DESTE ARQUIVO CASO O MESMO NÃO SOFRA ALTERAÇÕES.
 *
 * @package   HiperSend
 * @author    HiperSend | Modificado por Apolo Ravi
 * @copyright 2021-2022
 * @link      https://HiperSend.com.br
 * @since     Version 1.2 (27-04-2022)
 *
 */


require_once __DIR__ . '/lib/WhatsAppGateway.php';
use WHMCS\Database\Capsule;
use WHMCS\User\Client;

if (!defined('WHMCS'))
{
    die('Denied access');
}

function AdjustValue($value)
{
	return number_format($value, 2, ',', '.');
}

function AdjustDate($date)
{
	return date('d/m/Y', strtotime($date));
}

function AdjustHour($hour)
{
	return date('H:i', strtotime($hour));
}

add_hook('InvoiceCreated', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	$invoice = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->first();
	if ($invoice->total !== '0.00')
	{
		if ($wg->config->status === 'active' && $wg->templates[1]->status === 'active')
		{
			//Integração Envio de PDF - Juno Edvan.
			if($invoice->paymentmethod=="boletofacil"){
				//Buscando XML
				$boletofacil 	= Capsule::table('faturas_boletofacil')->where('fatura', $invoice->id)->first();
				//Lendo XML
				$ArquivoXML 	= simplexml_load_string($boletofacil->xml);
				//Verificando se encontrou dados no XML
				if($ArquivoXML->data->charges->charge->link!=""){
					$URLBoleto 	= $ArquivoXML->data->charges->charge->link;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Integração Envio de PDF - PagHiper Oficial.
			elseif($invoice->paymentmethod=="PagHiper Boleto"){
				//Buscando Dados do PDF
				$paghiper 		= Capsule::table('mod_paghiper')->where('order_id', $invoice->id)->first();
				//Verificando se o resultado não é nulo
				if($paghiper->url_slip_pdf!=""){
					$URLBoleto 	= $paghiper->url_slip_pdf;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Integração Envio de PDF - ASAAS - cobrancaasaasmpay.
			elseif($invoice->paymentmethod=="cobrancaasaasmpay"){
				//Buscando Dados do PDF
				$cobrancaasaasmpay 		= Capsule::table('mod_cobrancaasaasmpay')->where('fatura_id', $invoice->id)->first();
				//Verificando se o resultado não é nulo
				if($cobrancaasaasmpay->url_boleto!=""){
					$URLBoleto 	= $cobrancaasaasmpay->url_boleto;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Caso não for nenhum dos casos acima.
			else{
				$URLBoleto 		= "";
			}
			$client = Client::find($invoice->userid);
			$ampersand = "&";
			$wg->templates[1]->message = str_replace('%name%', $client->firstname, $wg->templates[1]->message);
			$wg->templates[1]->message = str_replace('%email%', $client->email, $wg->templates[1]->message);
			$wg->templates[1]->message = str_replace('%invoiceid%', $vars['invoiceid'], $wg->templates[1]->message);
			$wg->templates[1]->message = str_replace('%duedate%', AdjustDate($invoice->duedate), $wg->templates[1]->message);
			$wg->templates[1]->message = str_replace('%value%', AdjustValue($invoice->total), $wg->templates[1]->message);
			$wg->templates[1]->message = str_replace('%linkboleto%', $URLBoleto, $wg->templates[1]->message);
			$wg->templates[1]->message = str_replace('%ampersand%', $ampersand, $twg->templates[1]->message);
			$wg->SendMessage($client, $wg->templates[1]->message);
		}
	}
});

add_hook('InvoiceCancelled', 1, function($vars)
{
	$wg      = new WhatsAppGateway;
	$invoice = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->first();
	if ($invoice->total !== '0.00')
	{
		//Integração Envio de PDF - Juno Edvan.
		if($invoice->paymentmethod=="boletofacil"){
			//Buscando XML
			$boletofacil 	= Capsule::table('faturas_boletofacil')->where('fatura', $invoice->id)->first();
			//Lendo XML
			$ArquivoXML 	= simplexml_load_string($boletofacil->xml);
			//Verificando se encontrou dados no XML
			if($ArquivoXML->data->charges->charge->link!=""){
				$URLBoleto 	= $ArquivoXML->data->charges->charge->link;
			}
			//Caso não tiver achado
			else{
				$URLBoleto 	= "";
			}
		}
		//Integração Envio de PDF - PagHiper Oficial.
		elseif($invoice->paymentmethod=="PagHiper Boleto"){
			//Buscando Dados do PDF
			$paghiper 		= Capsule::table('mod_paghiper')->where('order_id', $invoice->id)->first();
			//Verificando se o resultado não é nulo
			if($paghiper->url_slip_pdf!=""){
				$URLBoleto 	= $paghiper->url_slip_pdf;
			}
			//Caso não tiver achado
			else{
				$URLBoleto 	= "";
			}
		}
		//Integração Envio de PDF - ASAAS - cobrancaasaasmpay.
		elseif($invoice->paymentmethod=="cobrancaasaasmpay"){
			//Buscando Dados do PDF
			$cobrancaasaasmpay 		= Capsule::table('mod_cobrancaasaasmpay')->where('fatura_id', $invoice->id)->first();
			//Verificando se o resultado não é nulo
			if($cobrancaasaasmpay->url_boleto!=""){
				$URLBoleto 	= $cobrancaasaasmpay->url_boleto;
			}
			//Caso não tiver achado
			else{
				$URLBoleto 	= "";
			}
		}
		//Caso não for nenhum dos casos acima.
		else{
			$URLBoleto 		= "";
		}
		if ($wg->config->status === 'active' && $wg->templates[13]->status === 'active')
		{
			$client = Client::find($invoice->userid);
			$ampersand = "&";
			$wg->templates[13]->message = str_replace('%name%', $client->firstname, $wg->templates[13]->message);
			$wg->templates[13]->message = str_replace('%email%', $client->email, $wg->templates[13]->message);
			$wg->templates[13]->message = str_replace('%invoiceid%', $vars['invoiceid'], $wg->templates[13]->message);
			$wg->templates[13]->message = str_replace('%duedate%', AdjustDate($invoice->duedate), $wg->templates[13]->message);
			$wg->templates[13]->message = str_replace('%value%', AdjustValue($invoice->total), $wg->templates[13]->message);
			$wg->templates[13]->message = str_replace('%linkboleto%', $URLBoleto, $wg->templates[13]->message);
			$wg->SendMessage($client, $wg->templates[13]->message);
		}
	}
});

add_hook('InvoicePaymentReminder', 1, function($vars)
{
	$wg      = new WhatsAppGateway;
	$invoice = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->first();
	if ($invoice->total !== '0.00')
	{
		if ($wg->config->status === 'active' && $wg->templates[2]->status === 'active')
		{
			//Integração Envio de PDF - Juno Edvan.
			if($invoice->paymentmethod=="boletofacil"){
				//Buscando XML
				$boletofacil 	= Capsule::table('faturas_boletofacil')->where('fatura', $invoice->id)->first();
				//Lendo XML
				$ArquivoXML 	= simplexml_load_string($boletofacil->xml);
				//Verificando se encontrou dados no XML
				if($ArquivoXML->data->charges->charge->link!=""){
					$URLBoleto 	= $ArquivoXML->data->charges->charge->link;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Integração Envio de PDF - PagHiper Oficial.
			elseif($invoice->paymentmethod=="PagHiper Boleto"){
				//Buscando Dados do PDF
				$paghiper 		= Capsule::table('mod_paghiper')->where('order_id', $invoice->id)->first();
				//Verificando se o resultado não é nulo
				if($paghiper->url_slip_pdf!=""){
					$URLBoleto 	= $paghiper->url_slip_pdf;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Integração Envio de PDF - ASAAS - cobrancaasaasmpay.
			elseif($invoice->paymentmethod=="cobrancaasaasmpay"){
				//Buscando Dados do PDF
				$cobrancaasaasmpay 		= Capsule::table('mod_cobrancaasaasmpay')->where('fatura_id', $invoice->id)->first();
				//Verificando se o resultado não é nulo
				if($cobrancaasaasmpay->url_boleto!=""){
					$URLBoleto 	= $cobrancaasaasmpay->url_boleto;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Caso não for nenhum dos casos acima.
			else{
				$URLBoleto 		= "";
			}
			$client = Client::find($invoice->userid);
			$wg->templates[2]->message = str_replace('%name%', $client->firstname, $wg->templates[2]->message);
			$wg->templates[2]->message = str_replace('%email%', $client->email, $wg->templates[2]->message);
			$wg->templates[2]->message = str_replace('%invoiceid%', $vars['invoiceid'], $wg->templates[2]->message);
			$wg->templates[2]->message = str_replace('%duedate%', AdjustDate($invoice->duedate), $wg->templates[2]->message);
			$wg->templates[2]->message = str_replace('%value%', AdjustValue($invoice->total), $wg->templates[2]->message);
			$wg->templates[2]->message = str_replace('%linkboleto%', $URLBoleto, $wg->templates[2]->message);
			$wg->SendMessage($client, $wg->templates[2]->message);
		}
	}
});

add_hook('InvoicePaid', 1, function($vars)
{
	$wg      = new WhatsAppGateway;
	$invoice = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->first();
	if ($invoice->total !== '0.00')
	{
		if ($wg->config->status === 'active' && $wg->templates[3]->status === 'active')
		{
			//Integração Envio de PDF - Juno Edvan.
			if($invoice->paymentmethod=="boletofacil"){
				//Buscando XML
				$boletofacil 	= Capsule::table('faturas_boletofacil')->where('fatura', $invoice->id)->first();
				//Lendo XML
				$ArquivoXML 	= simplexml_load_string($boletofacil->xml);
				//Verificando se encontrou dados no XML
				if($ArquivoXML->data->charges->charge->link!=""){
					$URLBoleto 	= $ArquivoXML->data->charges->charge->link;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Integração Envio de PDF - PagHiper Oficial.
			elseif($invoice->paymentmethod=="PagHiper Boleto"){
				//Buscando Dados do PDF
				$paghiper 		= Capsule::table('mod_paghiper')->where('order_id', $invoice->id)->first();
				//Verificando se o resultado não é nulo
				if($paghiper->url_slip_pdf!=""){
					$URLBoleto 	= $paghiper->url_slip_pdf;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Integração Envio de PDF - ASAAS - cobrancaasaasmpay.
			elseif($invoice->paymentmethod=="cobrancaasaasmpay"){
				//Buscando Dados do PDF
				$cobrancaasaasmpay 		= Capsule::table('mod_cobrancaasaasmpay')->where('fatura_id', $invoice->id)->first();
				//Verificando se o resultado não é nulo
				if($cobrancaasaasmpay->url_boleto!=""){
					$URLBoleto 	= $cobrancaasaasmpay->url_boleto;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Caso não for nenhum dos casos acima.
			else{
				$URLBoleto 		= "";
			}
			$client = Client::find($invoice->userid);
			$wg->templates[3]->message = str_replace('%name%', $client->firstname, $wg->templates[3]->message);
			$wg->templates[3]->message = str_replace('%email%', $client->email, $wg->templates[3]->message);
			$wg->templates[3]->message = str_replace('%invoiceid%', $vars['invoiceid'], $wg->templates[3]->message);
			$wg->templates[3]->message = str_replace('%duedate%', AdjustDate($invoice->duedate), $wg->templates[3]->message);
			$wg->templates[3]->message = str_replace('%value%', AdjustValue($invoice->total), $wg->templates[3]->message);
			$wg->templates[3]->message = str_replace('%linkboleto%', $URLBoleto, $wg->templates[3]->message);
			$wg->SendMessage($client, $wg->templates[3]->message);
		}
	}
});

add_hook('TicketOpen', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active' && $wg->templates[4]->status === 'active')
	{
		$client	= Client::find($vars['userid']);
		if (isset($client->id))
		{
			$ticket = Capsule::table('tbltickets')->where('id', $vars['ticketid'])->first();
			$wg->templates[4]->message = str_replace('%name%', $client->firstname, $wg->templates[4]->message);
			$wg->templates[4]->message = str_replace('%email%', $client->email, $wg->templates[4]->message);
			$wg->templates[4]->message = str_replace('%ticket%', $ticket->tid, $wg->templates[4]->message);
			$wg->templates[4]->message = str_replace('%title%', $ticket->title, $wg->templates[4]->message);
			$wg->templates[4]->message = str_replace('%date%', AdjustDate($ticket->date), $wg->templates[4]->message);
			$wg->templates[4]->message = str_replace('%hour%', date('H:i', strtotime($ticket->date)), $wg->templates[4]->message);
			$wg->SendMessage($client, $wg->templates[4]->message);
		}
	}
});

add_hook('TicketAdminReply', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active' && $wg->templates[5]->status === 'active')
	{
		$ticket = Capsule::table('tbltickets')->where('id', $vars['ticketid'])->first();
		$client	= Client::find($ticket->userid);
		if (isset($client->id))
		{
			$wg->templates[5]->message = str_replace('%name%', $client->firstname, $wg->templates[5]->message);
			$wg->templates[5]->message = str_replace('%email%', $client->email, $wg->templates[5]->message);
			$wg->templates[5]->message = str_replace('%ticket%', $ticket->tid, $wg->templates[5]->message);
			$wg->templates[5]->message = str_replace('%title%', $ticket->title, $wg->templates[5]->message);
			$wg->templates[5]->message = str_replace('%date%', AdjustDate($ticket->lastreply), $wg->templates[5]->message);
			$wg->templates[5]->message = str_replace('%hour%', date('H:i', strtotime($ticket->lastreply)), $wg->templates[5]->message);
			$wg->SendMessage($client, $wg->templates[5]->message);
		}
	}
});

add_hook('AfterModuleCreate', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active' && $wg->templates[6]->status === 'active')
	{
		$service = Capsule::table('tblhosting')->where('id', $vars['params']['serviceid'])->first();	
		$product = Capsule::table('tblproducts')->where('id', $vars['params']['pid'])->first();
		$client	 = Client::find($service->userid);
		$wg->templates[6]->message = str_replace('%name%', $client->firstname, $wg->templates[6]->message);
		$wg->templates[6]->message = str_replace('%email%', $client->email, $wg->templates[6]->message);
		$wg->templates[6]->message = str_replace('%product%', $product->name, $wg->templates[6]->message);
		$wg->templates[6]->message = str_replace('%id%', $service->id, $wg->templates[6]->message);
		$wg->templates[6]->message = str_replace('%duedate%', AdjustDate($service->nextduedate), $wg->templates[6]->message);
		$wg->templates[6]->message = str_replace('%value%', AdjustValue($service->amount), $wg->templates[6]->message);
		$wg->templates[6]->message = str_replace('%ip%', $service->dedicatedip, $wg->templates[6]->message);
		$wg->templates[6]->message = str_replace('%domain%', $service->domain, $wg->templates[6]->message);
		$wg->templates[6]->message = str_replace('%user%', $service->username, $wg->templates[6]->message);
		$wg->templates[6]->message = str_replace('%password%', decrypt($service->password), $wg->templates[6]->message);
		$wg->SendMessage($client, $wg->templates[6]->message);
	}
});

add_hook('AfterModuleSuspend', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active' && $wg->templates[7]->status === 'active')
	{
		$service = Capsule::table('tblhosting')->where('id', $vars['params']['serviceid'])->first();	
		$product = Capsule::table('tblproducts')->where('id', $vars['params']['pid'])->first();
		$client	 = Client::find($service->userid);
		$wg->templates[7]->message = str_replace('%name%', $client->firstname, $wg->templates[7]->message);
		$wg->templates[7]->message = str_replace('%email%', $client->email, $wg->templates[7]->message);
		$wg->templates[7]->message = str_replace('%product%', $product->name, $wg->templates[7]->message);
		$wg->templates[7]->message = str_replace('%id%', $service->id, $wg->templates[7]->message);
		$wg->templates[7]->message = str_replace('%duedate%', AdjustDate($service->nextduedate), $wg->templates[7]->message);
		$wg->templates[7]->message = str_replace('%value%', AdjustValue($service->amount), $wg->templates[7]->message);
		$wg->SendMessage($client, $wg->templates[7]->message);
	}
});

add_hook('AfterModuleUnsuspend', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active' && $wg->templates[8]->status === 'active')
	{
		$service = Capsule::table('tblhosting')->where('id', $vars['params']['serviceid'])->first();	
		$product = Capsule::table('tblproducts')->where('id', $vars['params']['pid'])->first();
		$client	 = Client::find($service->userid);
		$wg->templates[8]->message = str_replace('%name%', $client->firstname, $wg->templates[8]->message);
		$wg->templates[8]->message = str_replace('%email%', $client->email, $wg->templates[8]->message);
		$wg->templates[8]->message = str_replace('%product%', $product->name, $wg->templates[8]->message);
		$wg->templates[8]->message = str_replace('%id%', $service->id, $wg->templates[8]->message);
		$wg->templates[8]->message = str_replace('%duedate%', AdjustDate($service->nextduedate), $wg->templates[8]->message);
		$wg->templates[8]->message = str_replace('%value%', AdjustValue($service->amount), $wg->templates[8]->message);
		$wg->SendMessage($client, $wg->templates[8]->message);
	}
});

add_hook('AfterModuleTerminate', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active' && $wg->templates[9]->status === 'active')
	{
		$service = Capsule::table('tblhosting')->where('id', $vars['params']['serviceid'])->first();	
		$product = Capsule::table('tblproducts')->where('id', $vars['params']['pid'])->first();
		$client	 = Client::find($service->userid);
		$wg->templates[9]->message = str_replace('%name%', $client->firstname, $wg->templates[9]->message);
		$wg->templates[9]->message = str_replace('%email%', $client->email, $wg->templates[9]->message);
		$wg->templates[9]->message = str_replace('%product%', $product->name, $wg->templates[9]->message);
		$wg->SendMessage($client, $wg->templates[9]->message);
	}
});

add_hook('ClientAdd', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active' && $wg->templates[10]->status === 'active')
	{
		$client    = Client::find($vars['userid']);
		$settings  = Capsule::table('tblconfiguration')->where('setting', 'Domain')->first();
		$domain    = $settings->value;
		$settings  = Capsule::table('tblconfiguration')->where('setting', 'SystemURL')->first();
		$systemurl = $settings->value;
		$settings  = Capsule::table('tblconfiguration')->where('setting', 'CompanyName')->first();
		$company   = $settings->value;
		$wg->templates[10]->message = str_replace('%name%', $client->firstname, $wg->templates[10]->message);
		$wg->templates[10]->message = str_replace('%email%', $client->email, $wg->templates[10]->message);
		$wg->templates[10]->message = str_replace('%website%', $domain, $wg->templates[10]->message);
		$wg->templates[10]->message = str_replace('%whmcs%', $systemurl, $wg->templates[10]->message);
		$wg->templates[10]->message = str_replace('%company%', $company, $wg->templates[10]->message);
		$wg->SendMessage($client, $wg->templates[10]->message);
	}
});

add_hook('ClientLogin', 1, function($vars)
{
	/**
	 * Verifica se o login efetuado não foi através de sessão administrativa
	 */
	if (!isset($_SESSION['adminid']))
	{
		$wg = new WhatsAppGateway;
		if ($wg->config->status === 'active' && $wg->templates[11]->status === 'active')
		{
			$ipaddr = getenv("REMOTE_ADDR");
			/**
			 * Inibe o envio se o IP não for um IPv4
			 */
			if (!filter_var($ipaddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
			{
				return FALSE;
			}
			$client = Client::find($vars['userid']);
			$wg->templates[11]->message = str_replace('%name%', $client->firstname, $wg->templates[11]->message);
			$wg->templates[11]->message = str_replace('%email%', $client->email, $wg->templates[11]->message);
			$wg->templates[11]->message = str_replace('%ipaddr%', $ipaddr, $wg->templates[11]->message);
			$wg->templates[11]->message = str_replace('%date%', date('d/m/Y'), $wg->templates[11]->message);
			$wg->templates[11]->message = str_replace('%hour%', date('H:i'), $wg->templates[11]->message);
			$wg->SendMessage($client, $wg->templates[11]->message);
		}
	}
});

add_hook("ClientAreaPageLogin", 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active' && $wg->templates[12]->status === 'active')
	{
		$log = Capsule::table('tblactivitylog')->orderBy('date', 'desc')->first();
		if ($log->user === 'System')
		{
			$text = explode(' ', $log->description);
			if ($text[0] === 'Failed' && $text[1] === 'Login' && $text[2] === 'Attempt')
			{
				$logdate = date('Y-m-d', strtotime($log->date));
				$loghour = date('H:i', strtotime($log->date));
				if ($logdate === date('Y-m-d') && $loghour === date('H:i'))
				{
					$client = Client::find($log->userid);
					$wg->templates[12]->message = str_replace('%name%', $client->firstname, $wg->templates[12]->message);
					$wg->templates[12]->message = str_replace('%email%', $client->email, $wg->templates[12]->message);
					$wg->templates[12]->message = str_replace('%ipaddr%', $log->ipaddr, $wg->templates[12]->message);
					$wg->templates[12]->message = str_replace('%date%', AdjustDate($log->date), $wg->templates[12]->message);
					$wg->templates[12]->message = str_replace('%hour%', AdjustHour($log->date), $wg->templates[12]->message);
					$wg->SendMessage($client, $wg->templates[12]->message);
				}
			}
		}
	}
});

add_hook('ClientChangePassword', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active' && $wg->templates[14]->status === 'active')
	{
		$client = Client::find($vars['userid']);
		$ipaddr = getenv("REMOTE_ADDR");
		$wg->templates[14]->message = str_replace('%name%', $client->firstname, $wg->templates[14]->message);
		$wg->templates[14]->message = str_replace('%email%', $client->email, $wg->templates[14]->message);
		$wg->templates[14]->message = str_replace('%ipaddr%', $ipaddr, $wg->templates[14]->message);
		$wg->templates[14]->message = str_replace('%date%', date('d/m/Y'), $wg->templates[14]->message);
		$wg->templates[14]->message = str_replace('%hour%', date('H:i'), $wg->templates[14]->message);
		$wg->SendMessage($client, $wg->templates[14]->message);
	}
});

add_hook('AdminClientServicesTabFields', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active')
	{
		$service = Capsule::table('tblhosting')->where('id', $vars['id'])->first();
		if ($service->domainstatus === 'Active')
		{
			$div = '';
				$div .= '<div class="row">';
					$div .= '<div class="col-md-1" style="margin-bottom: 10px !important;">';
						$div .= '<a href="addonmodules.php?module=whatsappgateway&outputaction=service_ready&serviceid='.$vars['id'].'" class="btn btn-warning" data-toggle="tooltip" data-placement="top" title="Utiliza o template \'Serviço Pronto\' (#15) para emitir uma notificação ao cliente em questão">[WhatsApp Gateway] Serviço Pronto</a>';
					$div .= '</div>';
				$div .= '</div>';
			echo $div;
		}
	}
});

add_hook('AdminInvoicesControlsOutput', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active')
	{
		$invoice = Capsule::table('tblinvoices')->where('id', $vars['invoiceid'])->first();
		if ($invoice->status === 'Unpaid')
		{
			return '<br /><br /><a href="addonmodules.php?module=whatsappgateway&outputaction=invoice_reminder&invoiceid='.$vars['invoiceid'].'" target="_blank" class="btn btn-warning" data-toggle="tooltip" data-placement="top" title="Utiliza o template \'Lembrete de Fatura\' (#2) para emitir uma notificação ao cliente em questão">[WhatsApp Gateway] Fatura em Aberto</a>';
		}
	}
});

add_hook('AdminAreaClientSummaryPage', 1, function($vars)
{
	$wg = new WhatsAppGateway;
	if ($wg->config->status === 'active')
	{
		$html = '';
		$html .= '<a href="#" data-toggle="modal" data-target="#whatsappgatewaymessage" target="_blank" class="btn btn-warning" data-toggle="tooltip" data-placement="top" title="Permite enviar uma mensagem à este cliente em específico">[WhatsApp Gateway] Enviar Mensagem</a>';
		$html .= 
		'
		<div class="modal fade" id="whatsappgatewaymessage">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
						<h5 class="modal-title">[WhatsApp Gateway] Envio de Mensagem Manual</h4>
					</div>
					<form action="addonmodules.php?module=whatsappgateway&action=manualmessage" class="form-horizontal" method="post">
					<input type="hidden" name="userid" value="'. $vars['userid'] .'" />
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12">
								<label>Mensagem</label>
								<textarea name="message" rows="10" class="form-control" style="resize: none;" required></textarea>
							</div>
						</div>
						<p style="margin-top: 10px !important;">Variáveis Disponíveis</p>
						<div class="alert alert-info text-center">
							%name% - Preenche o nome do cliente<br />
							%completename% - Preenche o nome completo do cliente<br />
							%company% - Preenche o nome da companhia do cliente<br />
							%email% - Preenche o e-mail do cliente<br />
							%phone% - Preenche o telefone do cliente
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success">Enviar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					</div>
					</form>
				</div>
			</div>
		</div>
		';
		return $html;
	}
});

add_hook('EmailPreSend', 1, function($vars)
{
	$templates = 
	[
		'First Invoice Overdue Notice',
		'Second Invoice Overdue Notice',
		'Third Invoice Overdue Notice'
	];

	$template  = $vars['messagename'];

	$invoiceid = $vars['relid'];

	$index     = 0;

	if (in_array($template, $templates))
	{
		$type    = explode(' ', $template);
		$type[0] = strtolower($type[0]);

		if ($index != 1)
		{
			switch ($type[0])
			{
				case 'first':
						$index = 16;
					break;
		
				case 'second':
						$index = 17;
					break;
		
				case 'third':
						$index = 18;
					break;
			}
		}
	}

	if ($index != 0)
	{
		$wg      = new WhatsAppGateway;
		$invoice = Capsule::table('tblinvoices')->where('id', $invoiceid)->first();
		
		if (isset($wg->templates[$index]) && $invoice->total !== '0.00' && $invoice->status === 'Unpaid')
		{
			//Integração Envio de PDF - Juno Boletos - Edvan.
			if($invoice->paymentmethod=="boletofacil"){
				//Buscando XML
				$boletofacil 	= Capsule::table('faturas_boletofacil')->where('fatura', $invoice->id)->first();
				//Lendo XML
				$ArquivoXML 	= simplexml_load_string($boletofacil->xml);
				//Verificando se encontrou dados no XML
				if($ArquivoXML->data->charges->charge->link!=""){
					$URLBoleto 	= $ArquivoXML->data->charges->charge->link;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Integração Envio de PDF - PagHiper Oficial.
			elseif($invoice->paymentmethod=="PagHiper Boleto"){
				//Buscando Dados do PDF
				$paghiper 		= Capsule::table('mod_paghiper')->where('order_id', $invoice->id)->first();
				//Verificando se o resultado não é nulo
				if($paghiper->url_slip_pdf!=""){
					$URLBoleto 	= $paghiper->url_slip_pdf;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Integração Envio de PDF - ASAAS - cobrancaasaasmpay.
			elseif($invoice->paymentmethod=="cobrancaasaasmpay"){
				//Buscando Dados do PDF
				$cobrancaasaasmpay 		= Capsule::table('mod_cobrancaasaasmpay')->where('fatura_id', $invoice->id)->first();
				//Verificando se o resultado não é nulo
				if($cobrancaasaasmpay->url_boleto!=""){
					$URLBoleto 	= $cobrancaasaasmpay->url_boleto;
				}
				//Caso não tiver achado
				else{
					$URLBoleto 	= "";
				}
			}
			//Caso não for nenhum dos casos acima.
			else{
				$URLBoleto 		= "";
			}
			$client = Client::find($invoice->userid);

			$wg->templates[$index]->message = str_replace('%name%', $client->firstname, $wg->templates[$index]->message);
			$wg->templates[$index]->message = str_replace('%email%', $client->email, $wg->templates[$index]->message);
			$wg->templates[$index]->message = str_replace('%invoiceid%', $invoiceid, $wg->templates[$index]->message);
			$wg->templates[$index]->message = str_replace('%duedate%', AdjustDate($invoice->duedate), $wg->templates[$index]->message);
			$wg->templates[$index]->message = str_replace('%value%', AdjustValue($invoice->total), $wg->templates[$index]->message);
			$wg->templates[$index]->message = str_replace('%linkboleto%', $URLBoleto, $wg->templates[$index]->message);			
			$document       = [];
			$configurations = unserialize($wg->templates[$index]->configurations);

			$wg->SendMessage($client, $wg->templates[$index]->message, $document);
		}
	}
});
