<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_gerautog
 * @category    string
 * @copyright   2020 Nasnuv <tecnologia@nasnuv.com.br>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['missingidandcmid'] = 'Sem dados do curso!';

// Form Sections.
$string['pluginname'] = 'Gerador de Autógrafos';
$string['modulename'] = 'Gerador de Autógrafos';
$string['modulenameplural'] = 'Geradores de Autógrafos';
$string['gerautogname'] = 'Nome';
$string['gerautogname_help'] = 'Nome da atividade';
$string['setting_fileupload'] = 'Selecione um arquivo pdf';
$string['setting_fileupload_help'] = "Podes trocar o arquivo selecionado até clicar o botão Salvar.";

$string['pluginadministration'] = 'Administração do Gerador de Autógrafos';

$string['issueoptions'] = 'Opções de Emissão';
$string['emailauthors'] = 'Enviar email para o autore';
$string['emailothers'] = 'Enviar email para outros';
$string['emailfrom'] = 'Nome alternativo do remetente do email';
$string['delivery'] = 'Envio';

$string['emailauthors_help'] = 'Quando habilitado, os professores recebem os emails toda vez que um aluno emitir um certificado.';
$string['emailothers_help'] = 'Digite os endereços de emails que vão receber o alerta de emissão de certificado.';
$string['emailfrom_help'] = 'Nome a ser usado como remetente dos email enviados';
$string['delivery_help'] = 'Escolha como o certificado deve ser entregue aos alunos:<br>
<ul>
<li>Visualizar em uma nova janela: Abre uma nova janela no navegador do aluno contendo o certificado.</li>
<li>Forçar o download: Abre uma janela de download de arquivo para o aluno salvar em seu computador.</li>
<li>por Email: Envia o certificado para o email do aluno, e abre o certificado em uma nova janela do navegador.</li>
</ul><p>
Depois que estudante emite seu certificado, se ele clicar na atividade certificado aparecerá a data de emissão do certificado e ele poderá revisar ocertificado emitido</p>';

// Delivery options.
$string['openbrowser'] = 'Visualizar em uma nova janela';
$string['download'] = 'Baixar';
$string['emailbook'] = 'Enviar por Email';
$string['nodelivering'] = 'Sem envio, o usuário vai receber o livro por outros meios';
$string['emailoncompletion'] = 'Enviar na conclusão do curso';

$string['filenotfound'] = 'Arquivo não encontrado.';

$string['openwindow'] = 'Clique nos botões abaixo para ver o livro autografado!';
$string['generatebook'] = 'Gerar livro autografado';
$string['getbook'] = 'Ver livro';

// Email form
$string['tit_config'] = "Gerador de Autógrafos - Configuração";
$string['desc_config'] = "Coloque o email, mensagem para o leitor a ser colocada no livro e a imagem do autógrafo. O email e a mensagem são opcionais. Sugere-se não colocar o email, caso o objetivo seja apenas visualizar o resultado. Ao visualizar o livro, sempre é possível fazer também o download.";
$string['emailto'] = "Email do leitor";
$string['emailto_help'] = "Email do leitor para enviar o livro.";
$string['message_book'] = 'Messagem ao leitor.';
$string['message_book_help'] = 'Messagem ao leitor para colocar no livro.';
$string['autog_book'] = "Imagem do autógrafo";
$string['autog_book_help'] = "Imagem do autógrafo a colocar no livro.";
$string['send'] = 'Enviar';
$string['emailsubject'] = 'Nasnuv: seu livro autografado!';
$string['emailtext'] = 'Olá! Está em anexo seu livro autografado!';
$string['pngerror'] = "Arquivo PNG deve estar em modo 'não-entrelaçado'.";
$string['emailsent'] = 'Email enviado!';
$string['emailnotsent'] = 'Não conseguimos enviar o email!';
$string['emailnotfound'] = 'Email não cadastrado no evento.';

$string['back'] = "Voltar";
