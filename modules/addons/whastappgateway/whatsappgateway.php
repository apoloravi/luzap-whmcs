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


require_once __DIR__ . '/lib/WhatsAppGateway.php';
use WHMCS\Database\Capsule;
use WHMCS\User\Client;

if (!defined('WHMCS'))
{
	die('Denied access');
}

function whatsappgateway_config()
{
	return
	[
		'name'        => 'LuZap API Diapsros',
		'description' => 'Módulo de notificações via WHMCS integrado ao WhatsApp API LuZap.',
		'version'     => '1.2',
		'language'    => 'portuguese-br',
		'author'      => 'HiperSend.App'
	];
}

function whatsappgateway_activate()
{
	try
	{
		Capsule::schema()->dropIfExists('mod_wg');
		Capsule::schema()->dropIfExists('mod_wg_templates');
		Capsule::schema()->dropIfExists('mod_wg_logs');
		Capsule::schema()->create('mod_wg', function ($table)
		{
			$table->increments('id');
			$table->longText('destiny');
			$table->longText('api');
			$table->longText('secret');
			$table->enum('status', ['active', 'deactive'])->default('deactive');
			$table->integer('clientpermissionfieldid')->default(0);
			$table->integer('alternativephonefieldid')->default(0);
			$table->text('created_at');
			$table->text('updated_at');
		});
		Capsule::schema()->create('mod_wg_templates', function ($table)
		{
			$table->increments('id');
			$table->text('type');
			$table->binary('message');
			$table->enum('status', ['active', 'deactive']);
			$table->text('created_at');
			$table->text('updated_at');
		});
		Capsule::schema()->create('mod_wg_logs', function ($table)
		{
			$table->increments('id');
			$table->text('message');
			$table->integer('clientid');
			$table->text('created_at');
			$table->text('updated_at');
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'createdinvoice',
				'message'    => 'Olá %name%! Informamos que a fatura N° %invoiceid%, com vencimento em %duedate%, tem o valor total de R$ %value%, já encontra-se disponível em sua área do cliente para pagamento. Evite transtornos e efetue o pagamento até a data de vencimento.',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'    => 'invoicereminder',
				'message' => 'Olá %name%! Informamos que sua fatura N° %invoiceid% está prestes vencer no dia %duedate%, com o valor total de R$ %value%. Evite transtornos e efetue o pagamento até a data de vencimento.',
				'status'  => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'invoicepaid',
				'message'    => 'Olá %name%! Informamos que o pagamento de sua fatura N° %invoiceid%, foi confirmado com sucesso, agradecemos a sua confiança.',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'ticketcreated',
				'message'    => 'Olá %name%! Recebemos o seu ticket %title% - de N° %ticket% em %date% as %hour% em nosso sistema. Aguarde, em breve um de nossos atendentes irá lhe atender.',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'ticketreplied',
				'message'    => 'Olá %name%! O seu ticket %title% - de N° %ticket% foi respondido por nossa equipe neste exato momento (%date% - %hour%). Visite sua área de cliente para conferir maiores informações.',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'servicecreated',
				'message'    => 'Olá %name%! O seu produto %product% foi ativado! Para sua segurança os dados de acesso foram encaminhados ao seu e-mail neste momento. Caso não encontre entre em contato conosco.',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'servicesuspended',
				'message'    => 'Olá %name%! Informamos que o produto %product% foi suspenso, para mais informações acesse a área do cliente.',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'servicereactived',
				'message'    => 'Olá %name%! Informamos que o produto %product% foi reativado, para mais informações acesse a área do cliente.',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'servicecancelled',
				'message'    => 'Olá %name%! Informamos que o produto %product% foi cancelado, para mais informações acesse a área do cliente.',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'welcome',
				'message'    => 'Olá %name%! Seja bem-vindo a %company%! Agradecemos a preferência em nossa plataforma, ficamos contentes com a sua chegada.',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'clientlogin',
				'message'    => 'Olá %name%! Identificamos um acesso à sua conta originado do endereço de IP %ipaddr%. Caso desconheça este IP recomendamos alterar sua senha imediatamente',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'failedaccess',
				'message'    => 'Olá %name%! Identificamos uma falha de login originada deste IP %ipaddr% em %date% as %hour% sua conta: %email%. Caso você desconheça este IP ou tenha certeza que não foi você recomendamos alterar sua senha imediatamente',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'invoicecancelled',
				'message'    => 'Olá %name%! Informamos que a fatura N° %invoiceid%, com vencimento em %duedate% e o valor total de R$ %value% foi cancelada em sua conta. Para mais detalhes entre em contato com o nosso suporte via ticket em sua área do cliente',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'clientchangepwd',
				'message'    => 'Olá %name%! Informamos que a senha da sua conta (%email%) foi alterada neste momento e por isso achamos prudente te notificar. IP: %ipaddr%, Data da alteração: %date%, Hora: %hour%. Se você desconhece essa ação recomendamos recuperar sua senha neste momento e em seguida entre em contato com nosso suporte via ticket em sua área do cliente',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'       => 'serviceready',
				'message'    => 'Olá %name%! Informamos o serviço %product% encontra-se pronto para uso a partir de agora. Veja alguns detalhes sobre: Domínio: %domain%, IP: %ip%, Valor: R$ %value%, Vencimento: %duedate%. Outros detalhes como: nome de usuário e senha do serviço foram encaminhados ao seu e-mail por uma questão de segurança. Caso não tenha recebido entre em contato com nosso suporte via ticket em sua área do cliente',
				'status'     => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'    => 'invoicefirstoverdue',
				'message' => '*1° aviso de vencimento!* Olá %name%! Informamos que sua fatura N° %invoiceid% está prestes vencer no dia %duedate%, com o valor total de R$ %value%. Evite transtornos e efetue o pagamento até a data de vencimento.',
				'status'  => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'    => 'invoicesecondoverdue',
				'message' => '*2° aviso de vencimento!* Olá %name%! Informamos que sua fatura N° %invoiceid% está prestes vencer no dia %duedate%, com o valor total de R$ %value%. Evite transtornos e efetue o pagamento até a data de vencimento.',
				'status'  => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		Capsule::connection()->transaction(function ($handler)
		{
			$handler->table('mod_wg_templates')->insert(
			[
				'type'    => 'invoicethirdoverdue',
				'message' => '*3° aviso de vencimento!* Olá %name%! Informamos que sua fatura N° %invoiceid% está prestes vencer no dia %duedate%, com o valor total de R$ %value%. Evite transtornos e efetue o pagamento até a data de vencimento.',
				'status'  => 'active',
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s')
			]);
		});
		return ['status' => 'success', 'description' => 'Módulo ativado com sucesso.'];
	}
	catch (\Exception $e)
	{
		return ['status' => 'error', 'description' => 'Erro ao ativar o modulo, debug: ' . $e->getMessage()];
	}
}

function whatsappgateway_deactivate()
{
	try
	{
		Capsule::schema()->dropIfExists('mod_wg');
		Capsule::schema()->dropIfExists('mod_wg_templates');
		Capsule::schema()->dropIfExists('mod_wg_logs');
		return ['status' => 'success', 'description' => 'Módulo desativado com sucesso.'];
	}
	catch (\Exception $e)
	{
		return ['status' => 'error', 'description' => 'Ocorreu um erro ao desativar o módulo, debug: ' . $e->getMessage()];
	}
}

function whatsappgateway_output($vars)
{
	$core = new WhatsAppGateway;
	if (isset($_SERVER['REQUEST_METHOD']))
	{
		$core->SetParameters($_GET, $_POST);
		if ($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			$validate = $core->PerformAction();
			echo alert($validate['message'], $validate['result'] === true ? 'success' : 'danger');
		}
		if ($_SERVER['REQUEST_METHOD'] === 'GET')
		{
			if (isset($_GET['outputaction']))
			{
				$validate = $core->OutputAction();
				echo alert($validate['message'], $validate['result'] === true ? 'success' : 'danger');
			}
		}
	}
	$wg           = Capsule::table('mod_wg')->first();
	$templates    = Capsule::table('mod_wg_templates');
	$logs         = Capsule::table('mod_wg_logs');
	$customfields = Capsule::table('tblcustomfields')->where('type', 'client');
?>
	<?php if ($wg->status === 'deactive') : ?>
		<div class="alert alert-danger text-center"><i class="fa fa-exclamation-circle" aria-hidden="true"></i>
			<b>Sistema Desativado!</b> O sistema não está em funcionamento porque o módulo encontra-se desativado.
		</div>
	<?php endif; ?>
	<ul class="nav nav-tabs admin-tabs" role="tablist">
		<li <?= (!isset($_GET['action']) || $_GET['action'] === 'configure' || $_GET['action'] === 'manualmessage') ? 'class="active"' : '' ?>><a class="tab-top" href="#tab1" role="tab" data-toggle="tab" id="tabLink1" data-tab-id="1">Definições da API</a></li>
		<li <?= isset($_GET['action']) && $_GET['action'] === 'updatetemplate' ? 'class="active"' : '' ?>><a class="tab-top" href="#tab2" role="tab" data-toggle="tab" id="tabLink2" data-tab-id="2">Configurar Mensagens</a></li>
		<li><a class="tab-top" href="#tab3" role="tab" data-toggle="tab" id="tabLink3" data-tab-id="3">Gerenciar API</a></li>
	</ul>
	<div class="tab-content admin-tabs">
		<div class="tab-pane <?= !isset($_GET['action']) || $_GET['action'] === 'configure' || $_GET['action'] === 'manualmessage' ? 'active' : '' ?>" id="tab1">
			<div class="auth-container" style="margin: 20px auto;">
				<form action="addonmodules.php?module=whatsappgateway&action=configure" method="post">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="inputConfirmPassword">Padrão</label>
								<input type="text" name="destiny" class="form-control" value="https://" disabled>
							</div>
						</div>
						<div class="col-md-8">
							<div class="form-group">
								<label for="inputConfirmPassword">Destino da Requisição </label>
								<input type="text" name="destiny" class="form-control" value="<?= isset($wg->destiny) ? $wg->destiny : '' ?>" required>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="inputConfirmPassword">API</label>
						<input type="text" name="api" class="form-control" value="<?= isset($wg->api) ? $wg->api : '' ?>" placeholder="Exemplo: api-12345678912345" required>
					</div>
					<div class="form-group">
						<label for="inputConfirmPassword">Chave Secreta</label>
						<input type="password" name="secret" class="form-control" value="" placeholder="Por questões de segurança a senha não é exibida" required>
					</div>
					<div class="form-group">
						<label for="inputConfirmPassword">Status da API</label>
						<select class="form-control" name="status">
							<option value="active" <?= isset($wg->status) && $wg->status === 'active' ? 'selected' : '' ?>>Ativado</option>
							<option value="deactive" <?= isset($wg->status) && $wg->status === 'deactive' ? 'selected' : '' ?>>Desativado</option>
						</select>
					</div>
					<div class="form-group">
						<div class="form-check">
							<input id="my-input" class="form-check-input" type="checkbox" name="clearlogs" <?= $logs->count() > 0 ? '' : 'disabled' ?>>
							<label class="form-check-label">Limpar Logs?</label>
						</div>
						<small class="text-danger">* Marque para limpar os logs da aba "Logs"</small>
					</div>
					<div class="form-group">
						<label for="inputConfirmPassword">Seletor de Telefone (WhatsApp)</label>
						<select class="form-control" name="alternativephonefieldid">
							<option value="0" <?= isset($wg->alternativephonefieldid) && $wg->alternativephonefieldid == 0 ? 'selected' : '' ?>>- Padrão do WHMCS (Telefone)</option>
							<?php foreach ($customfields->get() as $customfield) : ?>
								<option value="<?= $customfield->id ?>" <?= isset($wg->alternativephonefieldid) && $wg->alternativephonefieldid == $customfield->id ? 'selected' : '' ?>>Id: <?= $customfield->id ?> - Nome: <?= $customfield->fieldname ?></option>
							<?php endforeach ?>
						</select>
						Dúvida? Entenda <i class="fas fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="top" data-html="true" title="Permite usar um campo customizado de Telefone diferente do padrão do WHMCS. <b>É importante salientar que os envios irão exigir DDI + DDD + Telefone.</b> Caso não seja identificado o DDI no campo customizado o sistema irá obter o DDI do campo de Telefone padrão do WHMCS<br /><br />Formato de uso ideal:<br />(11) 9 9999-9999"></i>
					</div>
					<div class="form-group">
						<label for="inputConfirmPassword">Aceitação de Mensagem</label>
						<select class="form-control" name="clientpermissionfieldid">
							<option value="0" <?= isset($wg->clientpermissionfieldid) && $wg->clientpermissionfieldid == 0 ? 'selected' : '' ?>>- Nenhum</option>
							<?php foreach ($customfields->get() as $customfield) : ?>
								<option value="<?= $customfield->id ?>" <?= isset($wg->clientpermissionfieldid) && $wg->clientpermissionfieldid == $customfield->id ? 'selected' : '' ?>>Id: <?= $customfield->id ?> - Nome: <?= $customfield->fieldname ?></option>
							<?php endforeach ?>
						</select>
						Dúvida? Entenda <i class="fas fa-question-circle" aria-hidden="true" data-toggle="tooltip" data-placement="top" data-html="true" title="Crie um campo customizado <b>para clientes</b> com qualquer nome, mas <b>com os valores: Sim e Não.</b> Você deve dar ao cliente a opção de escolher se quer ou não ser notificado por whatsapp. Após cria-lo defina nesta configuração, selecionando o campo pelo seu nome dentre a lista.<br />Ao optar por usar 'Nenhum' irá ignorar este campo enviando a mensagem mesmo que o cliente não queira receber."></i>
					</div>
					<button type="submit" class="btn btn-success btn-block">Autenticar & Salvar</button>
				</form>
			</div>
		</div>
		<div class="tab-pane <?= isset($_GET['action']) && $_GET['action'] === 'updatetemplate' ? 'active' : '' ?>" id="tab2">
			<div class="alert alert-info text-center">
				Os templates de mensagens são os modelos à serem utilizados em cada ação efetuada pelo WHMCS, e cada um possui suas váriaveis de uso (veja em editar).</div>
			<table id="tabletemplates" class="table table-striped table-responsive" style="width: 100% !important">
				<thead>
					<tr>
						<td>#</td>
						<td>Tipo da Mensagem</td>
						<td>Mensagem</td>
						<td>Status</td>
						<td></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($templates->get() as $template) : ?>
						<tr>
							<th><?= $template->id ?></th>
							<th><?= messagetype($template->type) ?></th>
							<th><?= $template->message ?></th>
							<th><label class="label label-<?= $template->status === 'active' ? 'success' : 'danger' ?>"><span><?= $template->status === 'active' ? 'Ativado' : 'Desativado' ?></span></label></th>
							<th><button class="btn btn-info btn-sm" data-toggle="modal" data-target="#editmodal-<?= $template->id ?>">EDITAR</button></th>
						</tr>
						<div class="modal fade" id="editmodal-<?= $template->id ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="exampleModalLabel">Mensagem: #<?= $template->id ?> - <?= messagetype($template->type) ?></h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<form action="addonmodules.php?module=whatsappgateway&action=updatetemplate" method="post">
										<input type="hidden" name="messageid" value="<?= $template->id ?>">
										<div class="modal-body">
											<div class="form-group">
												<label>Mensagem</label>
												<textarea class="form-control" name="message" rows="10" style="resize: none;" required><?= $template->message ?></textarea>
											</div>
											<hr>
											<p>Variáveis Disponíveis</p>
											<?= variables($template->id) ?>
											<hr>
											<div class="form-group">
												<label>Status</label>
												<select class="form-control" name="status">
													<option value="active" <?= $template->status === 'active' ? 'selected' : '' ?>>Ativado</option>
													<option value="deactive" <?= $template->status === 'deactive' ? 'selected' : '' ?>>Desativado</option>
												</select>
												<small class="text-danger">* Se desativado, esta mensagem não será enviada</small>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
											<button type="submit" class="btn btn-primary">Salvar</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
		<!--<div class="tab-pane" id="tab3">
			<div class="alert alert-info text-center">Os logs registram todas as mensagens enviadas pelo módulo</div>
			<table id="tablelog" class="table table-striped table-responsive" style="width: 100% !important">
				<thead>
					<tr>
						<td>#</td>
						<td>Cliente</td>
						<td>Mensagem</td>
						<td>Data do Envio</td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($logs->get() as $log):
						$client = Client::find($log->clientid); ?>
						<tr>
							<th><?= $log->id ?></th>
							<th><a href="clientssummary.php?userid=<?= (int)$log->clientid ?>" target=_blank><?= $client->firstname . ' ' . $client->lastname . ' (Id: ' . $log->clientid . ')' ?></a></th>
							<th><?= $log->message ?></th>
							<th><?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?></th>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>!-->
		<div class="tab-pane" id="tab3">
			<iframe src="https://panel.myhs.app/?api=<?= isset($wg->api) ? $wg->api : '' ?>&secret=<?= isset($wg->secret) ? $wg->secret : '' ?>" width="100%" height="800" style="border:none;"></iframe>
		</div>
	</div>
	<script>
		$(document).ready(function()
		{
			$("#tabletemplates").DataTable(
			{
				"pageLength": 25
			});
			$("#tablelog").DataTable(
			{
				"order":
				[
					[0, "desc"]
				]
			});
		});
	</script>
<?php } ?>
