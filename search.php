<?php

require_once "../config.php";
require_once COMMON_PATH . "includes/funcs/common_funcs.php";

$_POST = $_GET;
$palavra = $_GET['q'];
$tipo = $_GET['tipo'];
$idcliente = $_GET['idcliente'];
$idcliente = $_GET['idcliente'];
$idprojeto = $_GET['idprojeto'];
$idsenha = $_GET['idsenha'];
$idempresa = $_GET['idempresa'];
$idtarefa_principal = $_GET['idtarefa_principal'];
$pesquisa_ajax = $_GET['mode'] == 'ajax' ? true : false;

$busca = new FormBusca();
$timesheet = new Timesheet();
$formulario = new formulario();
$projetos = new projetos();

$busca->idempresa = $idempresa;
$busca->idsenha = $idsenha;
$busca->NO_EXTRA_FIELDS = 'T';
$busca->FILTRO_STATUS = 'T';
write_log($_POST);
if ($tipo == 'c') {
    //$palavra = ""; //temporário até ter busca

    $busca->FILTRO_NOME_CLIENTE = $palavra;
    $fim = is_numeric($_POST['offset']) ? $_POST['offset'] : die();
    if ($fim > $_POST['number']) {
        $ini = $fim - $_POST['number'] + 1;
    } else {
        $fim = $_POST['number'];
        $ini = 1;
    }
    if ($ini == 2) {
        $ini = 1;
    }

    // inacio 26/02/2015
    // o scrollPagination ta maluco, mandando o valor OFFSET sem ordem nenhuma
    // correcao rapida estou fazendo por sessao.
    // offset = 31 indica que ele começou a pesquisa novamente
    if (!$pesquisa_ajax) {
        if ((!isset($_SESSION['MOBILE_MULTIDADOS']['SEARCH']['INI_CLIENTE']) && !isset($_SESSION['MOBILE_MULTIDADOS']['SEARCH']['FIM_CLIENTE'])) || $_POST['offset'] == 31) {
            $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['INI_CLIENTE'] = 1;
            $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['FIM_CLIENTE'] = 30;
        } else {
            $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['INI_CLIENTE'] = $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['FIM_CLIENTE'] + 1;
            $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['FIM_CLIENTE'] += 30;
        }
        $ini = $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['INI_CLIENTE'];
        $fim = $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['FIM_CLIENTE'];

        $busca->ini_page = $ini;
        $busca->fim_page = $fim;
    }

    $sql = $busca->ret_lista_clientes();
    $dados = db_GetAll($sql);

    foreach ($dados as $id) {
        $return.= "<li id='idcliente_" . $id['idcliente'] . "' style='font-weight: bold'>" . $id['nome'] . "</li>";
    }

    echo $_GET['callback'] . "(" . json_utf8_encode($return) . ");";
    exit;
}
// tipo p lista projeto
elseif ($tipo == 'p') {
    $projetos = new projetos();

    $fim = is_numeric($_POST['offset']) ? $_POST['offset'] : die();

    if ($fim > $_POST['number']) {
        $ini = $fim - $_POST['number'] + 1;
    } else {
        $fim = $_POST['number'];
        $ini = 1;
    }
    if ($ini == 2) {
        $ini = 1;
    }

    // inacio 26/02/2015
    // o scrollPagination ta maluco, mandando o valor OFFSET sem ordem nenhuma
    // correcao rapida estou fazendo por sessao.
    // offset = 31 indica que ele começou a pesquisa novamente
    if (!$pesquisa_ajax) {
        if ((!isset($_SESSION['MOBILE_MULTIDADOS']['SEARCH']['INI_PROJETO']) && !isset($_SESSION['MOBILE_MULTIDADOS']['SEARCH']['FIM_PROJETO'])) ||
                $_POST['offset'] == 31) {
            $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['INI_PROJETO'] = 1;
            $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['FIM_PROJETO'] = 30;
        } else {
            $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['INI_PROJETO'] = $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['FIM_PROJETO'] + 1;
            $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['FIM_PROJETO'] += 30;
        }

        $ini = $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['INI_PROJETO'];
        $fim = $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['FIM_PROJETO'];

        $filtros['rows_ini'] = $ini;
        $filtros['rows_fim'] = $fim;
    }

    /* if ($idcliente) {
      $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['IDCLIENTE'] = $idcliente;
      } elseif ($_SESSION['MOBILE_MULTIDADOS']['SEARCH']['IDCLIENTE']) {
      $idcliente = $_SESSION['MOBILE_MULTIDADOS']['SEARCH']['IDCLIENTE'];
      } */

    //$palavra = ""; //temporário até ter busca

    $filtros['idcliente'] = $idcliente;
    $filtros['idsenha'] = $idsenha;
    $filtros['idempresa'] = $idempresa;
    $filtros['nome_projeto'] = $palavra;

    $filtros['orderby'] = 'nome_projeto';
    $filtros['orderby_dir'] = 'ASC';

    $fields = array(
        'idclienteprojeto',
        'idtabpreco',
        'nome_projeto',
        'codprojeto',
        'idcliente',
        'nome_cliente',
        'nome',
        'codcliente',
        'lawps_utbms_project');
    $dados = $projetos->ret_lista_projetos_tc(false, $filtros, $fields);

    $return = '';
    foreach ($dados as $id) {
        foreach ($id as $valor) {
            $return.= "<li id='idclienteprojeto_" . $valor['idclienteprojeto'] . "' data-idcliente='{$valor['idcliente']}' data-nomecliente='{$valor['nome_cliente']}' "
                    . "data-utbms-project='{$valor['lawps_utbms_project']}'  style='font-weight: bold'>" . $valor['nome_projeto'] . "</li>";
        }
        //$return[1] .= "$('#idclienteprojeto_".$id['idclienteprojeto']."').data('idcliente',{$id['idcliente']});";
        //$return[1] .= "$('#idclienteprojeto_".$id['idclienteprojeto']."').data('nome','{$id['nome']}');";
    }

    echo $_GET['callback'] . "(" . json_utf8_encode($return) . ");";
    exit;
}
// tipo t lista fase
elseif ($tipo == 't') {
    $idcliente = ($idcliente == '') ? $idcliente = 0 : $idcliente;
    $idprojeto = ($idprojeto == '') ? $idprojeto = 0 : $idprojeto;

    $sql = "SELECT IDUTBMS, UTBMS_NOME
    	           FROM RET_LISTA_TASK_ACTIVITY($idcliente,$idprojeto,0,0,'T',$idsenha)
    	           ORDER BY UTBMS_NOME";
    $dados = db_GetAll($sql);

    echo $_GET['callback'] . "(" . json_utf8_encode($dados) . ");";
    exit;
}
// tipo atividade lista atividade
elseif ($tipo == 'atividade') {


    if ($_GET['idtarefa'] != '') {
        $idtarefa = $_GET['idtarefa'];
    } else {
        $idtarefa = 0;
    }

    $timesheet->idcliente = $idcliente;
    $timesheet->idclienteprojeto = $idprojeto;
    $timesheet->idtarefa_utbms = $idtarefa;
    $timesheet->idvendedor = $idsenha;

    $sql = "SELECT lawps_utbms_project
                    FROM CLIENTE_PROJETO
                        WHERE idclienteprojeto = $idprojeto";
    $lawps_utbms_project = db_GetOne($sql);

    //Se for project
    if ($lawps_utbms_project == 'P') {
        $atividadeProject = $timesheet->getAtividadesProject();
        $dados = $atividadeProject['dadosAtividadesProject'];
    } else {
        $sql = "SELECT IDUTBMS, UTBMS_NOME
						FROM RET_LISTA_TASK_ACTIVITY($idcliente,$idprojeto,$idtarefa,0,'A',$idsenha)
					   ORDER BY UTBMS_NOME ";
        $dados = db_GetAll($sql);
    }

    echo $_GET['callback'] . "(" . json_utf8_encode($dados) . ");";
} elseif ($tipo == 'task_parent') {
    $filtros['idcliente'] = $idcliente;
    $filtros['idclienteprojeto'] = $idprojeto;
    $filtros['idsenha'] = $idsenha;
    $filtros['idempresa'] = $idempresa;

    $dadosProjeto = $projetos->ret_lista_projetos_tc(false, $filtros);
    $dadosProjeto = $dadosProjeto['data'][0];


    $idtarefa_utbms = $dadosProjeto['prj_idtarefa_padrao'];

    $timesheet->ididiomas_principal = $dadosProjeto['ididiomas_principal'];
    $timesheet->idclasse_utbms = $dadosProjeto['prj_idclasse_padrao'];

    $timesheet->idcliente = $idcliente;
    $timesheet->idclienteprojeto = $idprojeto;
    $timesheet->idtarefa_utbms = $idtarefa;
    $timesheet->idvendedor = $idsenha;

    $atividadeProject = $timesheet->getAtividadesProject();

    $idtask = $_GET['idtask'];
    $idtask_parent = false;
    if (is_array($atividadeProject['dadosAtividadesProject']) && count($atividadeProject['dadosAtividadesProject']) > 0) {
        $iniTot = array();
        foreach ($atividadeProject['dadosAtividadesProject'] as $k => $dadosAtividades) {
            if ($dadosAtividades['task_id'] == $idtask)
                $idtask_parent = $dadosAtividades['task_parent'];

            $arrDadosAtividades[$dadosAtividades['task_parent']][$dadosAtividades['task_id']] = $dadosAtividades['task_name'];

            //if($dadosAtividades['task_id'] != $dadosAtividades['task_parent']) $arrTotalizadoras[$dadosAtividades['task_parent']] = $dadosAtividades['task_parent'];
            $arrTotalizadoras[$dadosAtividades['task_parent']] = $dadosAtividades['task_parent'];

            $iniTot[] = $dadosAtividades['task_parent'];
        }
    }

    if (!empty($idprojeto)) {
        $queryTasksProj = "SELECT DISTINCT PRJ_TASKS.TASK_ID, PRJ_TASKS.TASK_NAME
                                                   FROM PRJ_TASKS
                                                   WHERE PRJ_TASKS.TASK_DYNAMIC = 1
                                                     AND PRJ_TASKS.TASK_PROJECT = $idprojeto ";

        $resTasksProj = db_GetAssoc($queryTasksProj);
    }

    if ($idtask_parent === false)
        $idtask_parent = $iniTot[0];

    $tarefasTotalizadoras = array();
    if (is_array($arrTotalizadoras) && count($arrTotalizadoras) > 0) {
        foreach ($arrTotalizadoras as $k => $idTotalizadoras) {
            $tarefasTotalizadoras[$idTotalizadoras] = $resTasksProj[$idTotalizadoras];
        }
    }

    /*
     * inacio lombardo : Thu Nov 22 19:57:34 BRST 2012 - OC:
     * comentario : As atividades sem pai deve ter um pai para poder seleciona-las na lista
     *
     */
    if (is_array($tarefasTotalizadoras) && count($tarefasTotalizadoras) > 0) {
        $tarefasTotalizadoras_new = array();
        foreach ($tarefasTotalizadoras as $idtask_totalizadora => $nome_tarefa_totalizadora) {
            $tarefasTotalizadoras[$idtask_totalizadora] = empty($nome_tarefa_totalizadora) ? __('Sem Tarefa Totalizadora') : $nome_tarefa_totalizadora;
        }
    }


    /*
     * inacio lombardo : Thu Oct 11 07:51:05 BRT 2012 - OC: Walar
     * comentario : Listando as fases das atividades Project
     */
    if (count($tarefasTotalizadoras) > 0) {
        $select_tarefas = $tarefasTotalizadoras;
        $select_tarefas_hidden = $dadosProjeto['prj_idtarefa_padrao'];
    }


    $return['select_tarefas'] = $select_tarefas;
    $return['select_tarefas_hidden'] = $select_tarefas_hidden;
    $return['selecionado'] = $idtask_parent;

    echo $_GET['callback'] . "(" . json_utf8_encode($return) . ");";
} elseif ($tipo == 'task') {

    $filtros['idcliente'] = $idcliente;
    $filtros['idclienteprojeto'] = $idprojeto;
    $filtros['idsenha'] = $idsenha;
    $filtros['idempresa'] = $idempresa;

    $dadosProjeto = $projetos->ret_lista_projetos_tc(false, $filtros);
    $dadosProjeto = $dadosProjeto['data'][0];


    $idtatividade_utbms = $dadosProjeto['prj_idatividade_padrao'];

    $timesheet->ididiomas_principal = $dadosProjeto['ididiomas_principal'];
    $timesheet->idclasse_utbms = $dadosProjeto['prj_idclasse_padrao'];

    $timesheet->idcliente = $idcliente;
    $timesheet->idclienteprojeto = $idprojeto;
    $timesheet->idvendedor = $idsenha;

    $atividadeProject = $timesheet->getAtividadesProject();

    foreach ($atividadeProject['dadosAtividadesProject'] as $k => $dadosAtividades) {
        $arrDadosAtividades[$dadosAtividades['task_parent']][$dadosAtividades['task_id']] = $dadosAtividades['task_name'];
    }

    $select_atividades = $arrDadosAtividades[$idtarefa_principal];

    $select_atividades_hidden = $idtatividade_utbms;

    $return['select_atividades'] = $select_atividades;
    $return['select_atividades_hidden'] = $select_atividades_hidden;

    echo $_GET['callback'] . "(" . json_utf8_encode($return) . ");";
} elseif ($tipo == 'percent_conclusao') {

    /*
     * Andre Renovato - 24/06/2014 - oc:
     * recupera range para mostrar porcentagem concluida
     */
    $step = getLAWPS_ADM_SISTEMA('STEP_PORCENTAGEM');

    //  default 5 em 5... senão... pega valor configurado pelo usuário.
    $step = empty($step) ? 5 : $step;

    $return = array_combine(range(0, 100, $step), range(0, 100, $step));

    echo $_GET['callback'] . "(" . json_utf8_encode($return) . ");";
}
?>
