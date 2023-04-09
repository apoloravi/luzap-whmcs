<?php


/**
 *
 * HiperSend - Notificações Inteligentes via WhatsApp
 * Copyright (c) 2021-2022
 * GARANTIMOS A FUNCIONALIDADE DESTE ARQUIVO CASO O MESMO NÃO SOFRA ALTERAÇÕES.
 *
 * @package   HiperSend
 * @author    HiperSend
 * @copyright 2021-2022
 * @link      https://HiperSend.com.br
 * @since     Version 1.2 (27-04-2022)
 *
 */


require_once __DIR__ . '/Utils.php';

use WHMCS\Database\Capsule;
use WHMCS\User\Client;

if (!defined('WHMCS'))
{
    die('Denied access');
}

class WhatsAppGateway
{
	private $post;
	private $get;
	private $data     = [];
	public $templates = [];
	public $config;	

	/**
	 * __construct
	 * Realizamos um truque: obtemos todos os templates e os armazenamos
	 * em um array para facil utilização externa ou interna através do seu id
	 */
	public function __construct() //OK
	{
		$this->config = Capsule::table('mod_wg')->first();
		foreach(Capsule::table('mod_wg_templates')->get() as $template)
		{
			$this->templates[$template->id] = $template;
		}
	}

	/**
	 * SetParameters
	 * Definimos parametros recebidos do $_GET ou $_POST
	 */
	public function SetParameters($get, $post) //OK
	{
		$this->get  = $get;
		$this->post = $post;
	}

	/**
	 * PerformAction
	 * Um swithc estático para realizar funções internas baseado no tipo de requisição efetuado no módulo
	 */
	public function PerformAction() //OK
	{
		if (isset($this->get['action']) && !empty($this->get['action']))
		{
			switch($this->get['action'])
			{
				case 'configure':
						return $this->__ConfigureModule();
					break;
				case 'updatetemplate':
						return $this->__EditTemplate();
					break;
				case 'manualmessage':
						return $this->__ManualMessage();
					break;
			}
		}
	}

	/**
	 * __ConfigureModule
	 * Função interna para salvar configurações do módulo
	 */
	private function __ConfigureModule() //OK
	{
		Capsule::table('mod_wg')->truncate();
		
		Capsule::table('mod_wg')->insert(
		[
			'destiny'                 => $this->post['destiny'],
			'api'                     => $this->post['api'],
			'secret'                  => $this->post['secret'],
			'status'                  => $this->post['status'],
			'clientpermissionfieldid' => $this->post['clientpermissionfieldid'],
			'alternativephonefieldid' => $this->post['alternativephonefieldid'],
			'created_at'              => date('Y-m-d H:i:s'),
			'updated_at'              => date('Y-m-d H:i:s')
		]);

		if (isset($this->post['clearlogs']))
		{
			Capsule::table('mod_wg_logs')->truncate();
		}

		return ['result' => true, 'message' => '<i class="fa fa-check-circle" aria-hidden="true"></i> Tudo certo! <b>Dados do módulo atualizados com sucesso.</b>'];
	}	
	/**
	 * __EditTemplate
	 * Função interna para salvar edições de templates
	 */
	private function __EditTemplate() //OK
	{
		if (isset($this->post['messageid']))
		{
			if (Capsule::table('mod_wg_templates')->where('id', $this->post['messageid'])->exists())
			{
				Capsule::table('mod_wg_templates')->where('id', $this->post['messageid'])->update(
				[
					'message'    => $this->post['message'],
					'status'     => $this->post['status'],
					'updated_at' => date('Y-m-d H:i:s')
				]);

				return ['result' => true, 'message' => '<i class="fa fa-check-circle" aria-hidden="true"></i> Tudo certo! <b>Template #'. $this->post['messageid'] .' editado com sucesso</b>'];
			}

			return ['result' => false, 'message' => '<b>Erro!</b> O template solicitado para edição não existe no banco de dados.'];
		}
	}	
	/**
	 * __ManualMessage
	 * Função interna para envio de mensagem manual vindo da página de usuários (modal)
	 */
	private function __ManualMessage() //OK
	{
		if (isset($this->post['userid']))
		{
			if ($this->config->destiny === null || $this->config->api === null || $this->config->secret === null || $this->config->status === 'deactive')
			{
				return false;
			}

			$client = Client::find($this->post['userid']);

			if ($this->VerifyClientPermission($client) === false)
			{
				logActivity('[WhatsApp API - HiperSend] Envio de mensagem rejeitada. O cliente '. $client->firstname .' '. $client->lastname .' ('.$client->email.') não deseja ser notificado');
				return ['result' => false, 'message' => '<b>Erro!</b> Houve algum erro ao realizar o processo (debug: o cliente não deseja ser notificado)'];
			}

			$this->post['message'] = str_replace('%name%', $client->firstname, $this->post['message']);
			$this->post['message'] = str_replace('%completename%', $client->firstname. ' '. $client->lastname, $this->post['message']);
			$this->post['message'] = str_replace('%company%', $client->companyname, $this->post['message']);
			$this->post['message'] = str_replace('%email%', $client->email, $this->post['message']);
			$this->post['message'] = str_replace('%phone%', $client->phonenumber, $this->post['message']);
			$this->data =
			[
				'api'     => $this->config->api,
				'secret'  => $this->config->secret,
				'method'  => 'sendmessage',
				'phone'   => $this->AdjustNumber($client),
				'message' => $this->post['message'],
			];

			$return = $this->cURL();
			unset($this->data);

			if ($return['result'] === 'success' && $return['status'] == 200)
			{
				Capsule::table('mod_wg_logs')->insert(
				[
					'message'    => $this->post['message'],
					'clientid'   => $this->post['userid'],
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s')
				]);
				
				return ['result' => true, 'message' => '<i class="fa fa-check-circle" aria-hidden="true"></i> Tudo certo! <b>Mensagem manual enviada com sucesso!</b>'];
			}
			else
			{
				logActivity('Erro no processo de envio de mensagem, debug: ' . $return['message']);
				return ['result' => false, 'message' => '<b>Erro!</b> Houve algum erro ao realizar o processo (debug: '.$return['message'].')'];
			}
		}
	}	

	/**
	 * AdjustNumber
	 * Formatação compelta de telefone
	 */
	private function AdjustNumber($client) //OK
	{
		$phonefieldid = (int)$this->config->alternativephonefieldid;

		if ($phonefieldid != 0)
		{
			$field  = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $phonefieldid)->where('relid', $client->id)->first();
			$number = str_replace(['(', ')', ' ', '-', '.', '+'], '', $field->value);
			$number = trim($number);

			/**
			 * Se o WHMCS não utilizar INTL (versões antigas)
			 * Definimos o DDI por padrão como 55 (Brasil)
			 */
			$exist = strpos($client->phonenumber, '+');
			if ($exist === false)
			{
				$number = '55' . $number;
			}
			else
			{
				$phone  = explode('.', $client->phonenumber);
				$ddi    = str_replace(['+', ' '], '', $phone[0]);
				$number = $ddi . $number;
			}
		}
		else
		{
			$number = str_replace(['(', ')', ' ', '-', '.', '+'], '', $client->phonenumber);
			$exist  = strpos($client->phonenumber, '+');

			if ($exist === false)
			{
				$number = '55' . $number;
			}
		}

		return $number;
	}

	/**
	 * AdjustValue
	 * Método fácil de ajuste de valores baseados em R$ (R$ 1.234,56)
	 */
	private function AdjustValue($value) //OK
	{
		return number_format($value, 2, ',', '.');
	}

	/**
	 * AdjustDate
	 * Método fácil de formatação de datas (01/01/2020)
	 */
	private function AdjustDate($date) //OK
	{
		return date('d/m/Y', strtotime($date));
	}

	/**
	 * VerifyClientPermission
	 *
	 * Usamos este método para verificar a permissão de recebimento de mensagem do cliente
	 * @param object $client
	 * @return bool
	 */
	private function VerifyClientPermission($client)
	{
		$this->config->clientpermissionfieldid = (int)$this->config->clientpermissionfieldid;

		if ($this->config->clientpermissionfieldid != 0)
		{
			$field = Capsule::table('tblcustomfieldsvalues')->where('fieldid', $this->config->clientpermissionfieldid)->where('relid', $client->id)->first();

			if (!empty($field->value))
			{
				$field->value = trim($field->value);

				if ($field->value === 'NÃO' || $field->value === 'Não' || $field->value === 'Nao' || $field->value === 'N' || $field->value === 'n')
				{
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * OutputAction
	 * Método público para realizar ações de fora do template: `invoice reminder page`(template 2) ou `service ready` (template 15)
	 */
	public function OutputAction() //OK
	{
		if ($this->get['outputaction'] === 'invoice_reminder' && isset($this->get['invoiceid']) && $this->templates[2]->status === 'active')
		{
			$invoice = Capsule::table('tblinvoices')->where('id', $this->get['invoiceid'])->first();

			if ($invoice->total !== '0.00' && $invoice->status === 'Unpaid')
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
				elseif($invoice->paymentmethod=="paghiper"){
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
                $this->templates[2]->message = str_replace('%name%', $client->firstname, $this->templates[2]->message);
                $this->templates[2]->message = str_replace('%email%', $client->email, $this->templates[2]->message);
                $this->templates[2]->message = str_replace('%invoiceid%', $this->get['invoiceid'], $this->templates[2]->message);
                $this->templates[2]->message = str_replace('%id%', $client->id, $this->templates[2]->message);
                $this->templates[2]->message = str_replace('%duedate%', $this->AdjustDate($invoice->duedate), $this->templates[2]->message);
                $this->templates[2]->message = str_replace('%value%', $this->AdjustValue($invoice->total), $this->templates[2]->message);
                $this->templates[2]->message = str_replace('%linkboleto%', $URLBoleto, $this->templates[2]->message);
                $this->templates[2]->message = str_replace('%ampersand%', $ampersand, $this->templates[2]->message);


				$this->SendMessage($client, $this->templates[2]->message);

				return ['result' => true, 'message' => '<i class="fa fa-check-circle" aria-hidden="true"></i> Tudo certo! <b>Lembrete de fatura enviado com sucesso!</b>'];
			}
		}

		if ($this->get['outputaction'] === 'service_ready' && isset($this->get['serviceid']) && $this->templates[15]->status === 'active')
		{
			$service = Capsule::table('tblhosting')->where('id', $this->get['serviceid'])->first();
			$product = Capsule::table('tblproducts')->where('id', $service->packageid)->first();

			if ($service->domainstatus === 'Active')
			{
				$client = Client::find($service->userid);
				$this->templates[15]->message = str_replace('%name%', $client->firstname, $this->templates[15]->message);
				$this->templates[15]->message = str_replace('%email%', $client->email, $this->templates[15]->message);
				$this->templates[15]->message = str_replace('%product%', $product->name, $this->templates[15]->message);
				$this->templates[15]->message = str_replace('%id%', $service->id, $this->templates[15]->message);
				$this->templates[15]->message = str_replace('%duedate%', AdjustDate($service->nextduedate), $this->templates[15]->message);
				$this->templates[15]->message = str_replace('%value%', AdjustValue($service->amount), $this->templates[15]->message);
				$this->templates[15]->message = str_replace('%ip%', $service->dedicatedip, $this->templates[15]->message);
				$this->templates[15]->message = str_replace('%domain%', $service->domain, $this->templates[15]->message);
				$this->templates[15]->message = str_replace('%user%', $service->username, $this->templates[15]->message);
				$this->templates[15]->message = str_replace('%password%', decrypt($service->password), $this->templates[15]->message);				

				$this->SendMessage($client, $this->templates[15]->message);

				return ['result' => true, 'message' => '<i class="fa fa-check-circle" aria-hidden="true"></i> Tudo certo! <b>Notificação de Serviço Pronto enviado com sucesso!</b>'];
			}
			return ['result' => false, 'message' => '<b>Erro!</b> O serviço não está com o status "Ativo"'];
		}
		return ['result' => false, 'message' => '<b>Erro!</b> Houve algum erro ao realizar o processo'];
	}

	/**
	 * SendMessage
	 * O processo principal que emite o envio da mensagem através dos hooks ou internamente na classe
	 */
	public function SendMessage($client, $message) //OK
	{
		if ($this->config->destiny === null || $this->config->api === null || $this->config->secret === null || $this->config->status === 'deactive')
		{
			return false;
		}

		if ($this->VerifyClientPermission($client) === false)
		{
			logActivity('[WhatsApp API - HiperSend] Envio de mensagem rejeitada. O cliente '. $client->firstname .' '. $client->lastname .' ('.$client->email.') não deseja ser notificado');
		}

		$this->data =
		[
			'app'	  => 'whmcs',
			'api'     => $this->config->api,
			'secret'  => $this->config->secret,
			'method'  => 'sendmessage',
			'phone'   => $this->AdjustNumber($client),
			'message' => $message,
		];

		$return = $this->cURL();

		if (isset($return['result']) && $return['result'] === 'success' && $return['status'] == 200)
		{
			Capsule::table('mod_wg_logs')->insert(
			[
				'message'    => $message,
				'clientid'   => $client->id,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		}
		else
		{
			logActivity('[WhatsApp API - HiperSend] Erro no processo de envio de mensagem, debug: ' . $return['message']);
		}
		unset($this->data);
	}

	/**
	 * cURL
	 * O que faz a requisição até a API
	 * ********** FIQUE LONGE DAQUI (NÃO MEXA!) **********
	 */
	private function cURL() //OK
	{
		if (!isset($this->config->destiny) || $this->config->destiny === null || empty($this->data))
		{
			throw new \Exception('[WhatsApp API - HiperSend] Erro interno. Parametros necessários não definidos ou nulos.');
		}

		$curl = curl_init('https://' . $this->config->destiny. '?' .http_build_query($this->data));

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		$result = curl_exec($curl);
		curl_close($curl);
		$json = json_decode($result, true);
		
		return $json;
	}
}
