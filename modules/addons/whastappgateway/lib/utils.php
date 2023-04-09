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


/**
 * alert
 * Usado para desenhar facilmente alertas dentro do módulo
 */
function alert($message, $type = 'success')
{
	return '<div class="alert alert-'. $type .' text-center">' . $message . '</div>';
}

/**
 * messagetype
 * Usado para converter o nome interno de um template em um nome legível
 */
function messagetype($type)
{
	switch ($type)
	{
		case 'createdinvoice':
			$msgtype = 'Fatura Criada';
		break;

	case 'invoicereminder':
			$msgtype = 'Lembrete de Fatura';
		break;

	case 'invoicepaid':
			$msgtype = 'Fatura Paga';
		break;

	case 'ticketcreated':
			$msgtype = 'Ticket Criado';
		break;
	case 'ticketreplied':
			$msgtype = 'Ticket Respondido';
		break;

	case 'servicecreated':
			$msgtype = 'Serviço Criado';
		break;

	case 'servicesuspended':
			$msgtype = 'Serviço Suspenso';
		break;

	case 'servicereactived':
			$msgtype = 'Serviço Reativado';
		break;

	case 'servicecancelled':
			$msgtype = 'Serviço Cancelado';
		break;

	case 'welcome':
			$msgtype = 'Bem-vindo';
		break;

	case 'clientlogin':
			$msgtype = 'Login de Cliente';
		break;

	case 'failedaccess':
			$msgtype = 'Falha de Login';
		break;

	case 'invoicecancelled':
			$msgtype = 'Fatura Cancelada';
		break;

	case 'clientchangepwd':
			$msgtype = 'Troca de Senha do Cliente';
		break;

	case 'serviceready':
			$msgtype = 'Serviço Pronto <label class="label label-info"><span>Uso Manual</span></label>';
		break;
		
	case 'invoicefirstoverdue':
			$msgtype = 'Primeira notificação de atraso';
		break;
		
	case 'invoicesecondoverdue':
			$msgtype = 'Segunda notificação de atraso';
		break;
		
	case 'invoicethirdoverdue':
			$msgtype = 'Terceira notificação de atraso';
		break;
	}
	return $msgtype;
}

/**
 * variables
 * Usado para facilmente desenhar variáveis dentro dos templates baseado em seu id
 */
function variables($id)
{
	$alert  = '<div class="alert alert-info text-center">';
	$alert .= '%name% - Nome do cliente<br />';
	$alert .= '%email% - E-mail do cliente<br />';

	switch ($id)
	{
		case 1: case 2: case 13: case 16: case 17: case 18:
		        $alert .= '%ampersand% - Caracter em Fatura<br />';
				$alert .= '%invoiceid% - Id da fatura<br />';
				$alert .= '%duedate% - Vencimento da fatura (d/m/Y)<br />';
				$alert .= '%value% - Valor total (sem R$)<br />';
				$alert .= '%id% - Montar ID do User <br />';
				$alert .= '%linkboleto% - Link do Boleto Compatível com: <br /><strong>(Juno ADYA HOST, PagHiper Oficial & Asaas ModulesPay)</strong><br />';
			break; 

		case 3:
				$alert .= '%invoiceid% - Id da fatura<br />';
				$alert .= '%duedate% - Vencimento da fatura (d/m/Y)<br />';
				$alert .= '%value% - Valor total (sem R$)<br />';
			break;

		case 4: case 5:
				$alert .= '%ticket% - Id do Ticket<br />';
				$alert .= '%title% - Título do Ticket<br />';
				$alert .= '%date% - Data da abertura / resposta<br />';
				$alert .= '%hour% - Hora da abertura / resposta<br />';
			break;

		case 7: case 8:
				$alert .= '%product% - Nome do serviço<br />';
				$alert .= '%id% - Id do serviço<br />';
				$alert .= '%duedate% - Data de vencimento (d/m/Y)<br />';
				$alert .= '%value% - Valor total (sem R$)<br />';
			break;

		case 9:
				$alert .= '%product% - Nome do serviço<br />';
			break;

		case 10:
				$alert .= '%website% - Link do site (salvo nas configs. do WHMCS)<br />';
				$alert .= '%whmcs% - Link do WHMCS (salvo nas configs. do WHMCS)<br />';
				$alert .= '%company% - Nome da Empresa (salvo nas configs. do WHMCS)<br />';
			break;

		case 11: case 12: case 14:
				$alert .= '%ipaddr% - IP de acesso<br />';
				$alert .= '%date% - Data do acesso<br />';
				$alert .= '%hour% - Hora do acesso<br />';
			break;

		case 14:
				$alert .= '%ipaddr% - IP de acesso<br />';
				$alert .= '%date% - Data do processo<br />';
				$alert .= '%hour% - Hora do processo<br />';
			break;

		case 6: case 15:
				$alert .= '%product% - Nome do serviço<br />';
				$alert .= '%id% - Id do serviço<br />';
				$alert .= '%duedate% - Data de vencimento (d/m/Y)<br />';
				$alert .= '%value% - Valor total (sem R$)<br />';
				$alert .= '%ip% - IP do serviço (vinculado ao campo IP Dedicado)<br />';
				$alert .= '%domain% - Domínio do serviço (vinculado ao campo Domínio)<br />';
				$alert .= '%user% - Usuário do serviço (vinculado ao campo Nome de Usuário)<br />';
				$alert .= '%password% - Senha do serviço (vinculado ao campo Senha)';
			break;
	}

	$alert .=
	'</div>
	<p class="text-danger"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Escreva o texto como se de fato estivesse escrevendo no WhatsApp</p>';

	return $alert;
}
