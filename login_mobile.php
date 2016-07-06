<?php

include '../config.php';

$_POST = $_GET;

$user = $_POST['usuario'];
$user_bd = $_POST['usuario'];
$pass = $_POST['senha'];
$pass_md5 = md5($pass);
$cod_db = 0;

//Andre Renovato - 26/08/2015 - OC:
//RECUPERA O BANCO DE DADOS PARA LOGIN
if (strpos($user, '@')) { //verifica se existe @ no campo usuario
    $c = explode('@', $user); //separa banco de dados e nome de user para login
    if (is_array($c) && count($c) > 0) {
        $user = $c[1];
        $cod_db = $c[0];
    }
}

$_POST['DB'] = $cod_db;

define('ENTRY_POINT', true);
include_once dirname(__FILE__) . '/../includes/funcs/system_funcs.php';
require_once(dirname(__FILE__) . '/../config.php');

if ($_POST['logout'] == 1) {
    $login = new Usuario();
    $callback = $login->logout('', '', true);
} else {

    $login = new Usuario();
    $return = $login->login($user, $pass_md5, $cod_db, 'pt_BR');

    if ($return['error'] == 1) {
        $callback['erro'] = $return['msg'];
    } else {
        $acesso_mobile = getCONFIGURACAO('HABILITA_ACESSO_MOBILE', true);

        if ($acesso_mobile != 'T') {
            $callback['erro'] = __('Versão Mobile não está habilitada. Entre em contato com o suporte.');
        } else {

            if (getUserIDCONTATO() != 0) {
                //CALLBACK MOBILE
                $callback['db'] = get_current_db_prop('DB_NAME');
                $callback['nome_senha'] = substr(getCampoSENHAS('nome'), 0, 20);
                $callback['idsenha'] = getUserIDSENHA();
                $callback['user_bd'] = $user_bd;
                $callback['usuario'] = $user;
                $callback['senha'] = $pass;
                $callback['url'] = $_POST['url'];
                $callback['cnpj'] = db_GetOne("SELECT CNPJ FROM empresas WHERE idempresa=" . getUserIDEMPRESA());
                $callback['idempresa_vendedor'] = getUserIDEMPRESA();
                $callback['codigo_auxiliar'] = getCampoVENDEDORES('codigo');
                $callback['url_foto_user'] = $_POST['url'] . "/timesheet/multidados/module/scheduler/include/foto.php?idfoto=" . getUserIDVENDEDOR();
                $callback['url_logo_cliente'] = getSrcLogoEmpresa();
                $callback['perms_menu'] = Usuario::getPerfilAcesso_Mobile();
                $callback['page_home'] = 'index'; 
                //$callback['count_oco_pendentes'] = UsuarioPaineis::getOcorrenciasPendentes(true);
                //recupera o dashboard padrao da home crm (APENAS PARA O APP CRM)
                $callback['dashboard_default'] = getCONFIGURACAO('MOBILE_DASH_DEFAULT');

                $version = db_GetOne("SELECT CONTEUDO FROM ADM_SISTEMA WHERE COLUNA = 'VERSION'");
                $callback['version'] = $version ? $version : false;
                $callback['session_id'] = session_id();
                $callback['codigo_db'] = get_current_db_prop('CODIGO_DB');

                $_SESSION['DB'] = $_SESSION['_DATABASE'][get_current_db_prop('CODIGO_DB')];
                $_COOKIE['MultidadosLoginCodigoDb'] = get_current_db_prop('CODIGO_DB');
                //SESSION PARA IDENTIFICAR QUE NAO DEVE EXIBIR MENU NAS TELAS DO PORTAL
                $_SESSION['NOMENUAPP'] = 1;
            } else {
                $callback['erro'] = __("Login utilizado não é login de contato.");
            }
        }
    }
}
echo $_GET['callback'] . "(" . json_utf8_encode($callback) . ");";
?>