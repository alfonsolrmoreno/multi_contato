var conexao_ativa = true;
var verificacao_rapida = false;
var tempo_verificacao = 30000;  // a cada 30 segundos verifica a conexao
var state_action_buttons = 0;   // Estado dos botões de ação
var state_overlay = 0;          // Estado do overlay dos botões de ação

var app_multi = 'contato_';           // isso ser para separar os storage
//var app_multi = 'crm';
var version_system = '2016.01';
//versao do mobile para mostrar no footer
var vs_mobile = 'v.1.0.0';
var debug_mode = false;
var debug_js_errors = false;
var StatusMobiscroll = false;
var Objeto_real = localStorage[app_multi + 'mobile_login'];
var num_tentativas = 0;

// Variáveis usadas na função @detectar_userAgent()
// @user_agent Traz as informações do agent do usuário (navegador de acesso/sistema)
var agents = new Array("Android", "iPhone", "Windows");
var user_agent = navigator.userAgent;
var agent = new String("");

if (typeof Objeto_real != 'undefined' && Objeto_real != '' && Objeto_real) {
    var Objeto_json = JSON.parse(Objeto_real)
    var COMMON_URL_MOBILE = Objeto_json.url + '/mobile_contato/';
    var COMMON_URL = Objeto_json.url;

    //definindo pagina padrao Home
    if (Objeto_json.page_home && Objeto_json.page_home != 'undefined') {
        var pageHome = Objeto_json.page_home + '.html';
    } else {
        var pageHome = "index.html";
    }

    //Andre Renovato - 26-02-2016
    //Mantendo dados no formulario de login
    $(function () {
        $("#url").val(COMMON_URL);
        $("#usuario").val(Objeto_json.user_bd);
        if (typeof Objeto_json.senha != 'undefined') {
            $("#senha").val(Objeto_json.senha);
        }
    })
} else {
    if (typeof getUrlVal() != 'undefined') {
        var COMMON_URL_MOBILE = getUrlVal() + '/mobile_contato/';
        var COMMON_URL = getUrlVal() + '/';
    } else {
        var COMMON_URL_MOBILE = '';
        var COMMON_URL = '';
        var Objeto_json = {};
    }
}

function getUrlVal() {
    var url = $("#url").val();

    if (typeof url == 'string' && url.toLowerCase() == 'ultra')
        url = 'http://h2.multidadosti.com.br/ultra';

    return url;
}

function loading(showOrHide, texto) {

    if (typeof texto == 'undefined')
        texto = 'Carregando...';

    if (typeof $.mobile != 'undefined' && typeof $.mobile.loading != 'undefined') {
        $.mobile.loading(showOrHide, {
            text: texto,
            textVisible: true,
            theme: 'b'
        });
    }
}

function mobile_login(obj) {
    loading('show');

    var dados = new Object();

    //Retorno do object no valida login
    if (obj) {
        var dadosArray = JSON.parse(obj);
        dados['USUARIO'] = dadosArray.user_bd;
        dados['SENHA'] = dadosArray.senha;
        dados['URL'] = dadosArray.url;
    } else {
        dados['USUARIO'] = $("#usuario").val();
        dados['SENHA'] = $("#senha").val();
        dados['URL'] = getUrlVal();

        //valida se todos os campos de login estao preechidos
        if (!notNull(getUrlVal()) || !notNull($("#usuario").val()) || !notNull($("#senha").val())) {
            loading('hide');
            $().toastmessage('showErrorToast', 'Para acessar o sistema Multidados entre com todas as informa&ccedil;&odblac;es');
            return false;
        }
    }

    if (dados['URL'] != "") {

        loading('show', 'Verificando a URL');

        var ajax_file_url = 'verifica_url.php';
        //Trata URL sem http://
        if ((dados['URL'].substr(0, 7)) != 'http://') {
            //AQUI VALIDAMOS A URL PELA SEGUNDA (/) PARA RECUPERAR O ENDERECO CORRETO
            var b1 = dados['URL'].search('/'); //localiza a posicao da primeira (/)
            var url_new = dados['URL'].slice(0, b1 + 1); //recupera apenas o localhost sem (/)
            var dados2 = dados['URL'].substr(b1 + 1); //recupera o que vem depois do localhost(/) para recuperar o resto depois da proxima (/)
            var b2 = dados2.search('/'); //recupera a posicao da segunda (/)
            if (b2 > 0) {
                url_new += dados2.slice(0, b2); //recupera apenas o conteundo antes do (/)
            } else {
                url_new += dados2; //nao tem barra no final entao junta a segunda parte da url
            }
            dados['URL'] = 'http://' + url_new;
        } else {
            //ENDERECO COM HTTP://
            //Remove http:// da URL
            var url_old = dados['URL'].slice(7);

            //AQUI VALIDAMOS A URL PELA SEGUNDA (/) PARA RECUPERAR O ENDERECO CORRETO
            var b1 = url_old.search('/'); //localiza a posicao da primeira (/)
            var url_new = url_old.slice(0, b1 + 1); //recupera apenas o localhost sem (/)
            var dados2 = url_old.substr(b1 + 1); //recupera o que vem depois do localhost(/) para recuperar o resto depois da proxima (/)
            var b2 = dados2.search('/'); //recupera a posicao da segunda (/)

            //caso nao encontre a segunda (/) o valor é -1 neste caso false, entao nao podemos realizar o slice
            if (b2 > 0) {
                url_new += dados2.slice(0, b2); //recupera apenas o conteundo antes do (/)
            } else {
                url_new += dados2;
            }

            dados['URL'] = 'http://' + url_new;
        }

        //VERIFICA SE EXISTE (/) NO FIM DA URL E REMOVE CASO EXISTA
        if ((dados['URL'].substr(dados['URL'].length - 1, 1)) == '/') {
            dados['URL'] = dados['URL'].substr(0, dados['URL'].length - 1);
        }

        //Remove "/login.php" caso enviado no campo URL
        if ((dados['URL'].substr(-10)) == '/login.php') {
            var count_url = dados['URL'].length;
            count_url = count_url - 10;
            dados['URL'] = dados['URL'].substr(0, count_url);
        }

        if (debug_mode)
            alert('efetuar login');

        var ajax_file = dados['URL'] + '/mobile_contato/login_mobile.php';
        COMMON_URL_MOBILE = dados['URL'] + '/mobile_contato';

        if (debug_mode) {
            alert('COMMON_URL_MOBILE: ' + COMMON_URL_MOBILE);
            alert(dados['URL'] + '/mobile_contato/' + ajax_file_url);
        }

        $.ajax({
            type: 'POST',
            url: dados['URL'] + '/mobile_contato/' + ajax_file_url,
            dataType: "jsonp",
            timeout: 15000,
            crossDomain: true,
            data: {
                url: COMMON_URL_MOBILE
            },
            error: function () {

                if (debug_mode) {
                    alert('ERROR MOBILE');
                }

                loading('hide');
                $().toastmessage('showErrorToast', 'Falha de comunica&ccedil;&atilde;o com o servidor. Verifique sua conex&atilde;o e se a URL est&aacute; correta');
            },
            success: function (data) {

                if (debug_mode) {
                    alert('SUCCESS');
                }

                loading('show', 'Autenticando o Usuário');

                $.ajax({
                    type: 'POST',
                    url: ajax_file,
                    dataType: "jsonp",
                    timeout: 15000, // aguarda 15s
                    crossDomain: true,
                    data: {
                        usuario: dados['USUARIO'],
                        senha: dados['SENHA'],
                        url: dados['URL']
                    },
                    error: function (jqXHR, statusText, error) {
                        loading('hide');
                        //Andre Renovato - 03/03/2016
                        //Tentando identificar pq as vezes nao faz login na primeira tentativa
                        //$().toastmessage('showErrorToast', 'URL incorreta ou vers&atilde;o incompat&iacute;vel' + statusText + ' - ' + error);
                        $().toastmessage('showErrorToast', '15 segundos se passaram sem resposta, verifique a URL informada');

                        console.log('mobile_login error : ');
                        console.log('statusText = ');
                        console.dir(statusText);
                        console.log('error = ');
                        console.dir(error);
                        console.log('ajax_file = ');
                        console.dir(ajax_file);
                        console.log('jqXHR = ');
                        console.dir(jqXHR);

                        window.location.href = 'pages.html#page_login';
                    },
                    success: function (data) {
                        if (data['erro']) {
                            loading('hide');
                            $().toastmessage('showErrorToast', data['erro']);
                            window.location.href = 'pages.html#page_login';
                        } else if (version_system != data['version']) {
                            loading('hide');
                            $().toastmessage('showErrorToast', 'Aplica&ccedil;&atilde;o web incompat&iacute;vel com o Aplicativo. Entre em contato com o suporte! ' + version_system + ' -> ' + data['version']);
                            window.location.href = 'pages.html#page_login';
                        } else {
                            var Objeto = {
                                'db': data['db'],
                                'nome_senha': data['nome_senha'],
                                'user_bd': data['user_bd'],
                                'usuario_id': data['idsenha'],
                                'usuario_nome': data['usuario'],
                                'senha': data['senha'],
                                'url': data['url'],
                                'idempresa_vendedor': data ['idempresa_vendedor'],
                                'codigo_auxiliar': data['codigo_auxiliar'],
                                'url_foto_user': data['url_foto_user'],
                                'url_logo_cliente': data['url_logo_cliente'],
                                'cnpj': data['cnpj'],
                                'perms_menu': data['perms_menu'],
                                'session_id': data['session_id'],
                                'version': data['version'],
                                'codigo_db': data['codigo_db'],
                                'page_home': data['page_home']};
                            //'count_oco_pendentes': data['count_oco_pendentes']};
                            localStorage.setItem(app_multi + 'mobile_login', JSON.stringify(Objeto));

                            var Objeto_real = localStorage.getItem(app_multi + 'mobile_login');
                            var Objeto_json = JSON.parse(Objeto_real);

                            loading('hide');

                            if (obj) {
                                $().toastmessage('showSuccessToast', 'Login realizado com sucesso');
                                setDadosIniciais();
                            } else {
                                window.location.href = 'index.html';
                            }
                        }
                    }
                });
            }
        });
    }
}

function notNull(valor) {
    if (valor != "" && !(valor.match(/^\s+$/))) {
        return true;
    } else {
        return false;
    }
}

function Storagelogout() {
    if (localStorage.getItem(app_multi + 'mobile_login')) {
        var Objeto_json = JSON.parse(localStorage.getItem(app_multi + 'mobile_login'));

        // inacio 04-05-2016
        // limpa todos caches e só deixa a URL e o nome usuario
        url_cache = Objeto_json.url;
        user_bd_cache = Objeto_json.user_bd;

        localStorage.removeItem(app_multi + 'mobile_login');

        var Objeto = {
            'url': url_cache,
            'user_bd': user_bd_cache};

        localStorage.setItem(app_multi + 'mobile_login', JSON.stringify(Objeto));
    }
}

function mobile_logout() {
    if (debug_mode)
        alert('mobile_logout');

    var dados = new Object();
    var ajax_file = COMMON_URL_MOBILE + '/login_mobile.php?logout=1';

    $.ajax({
        type: 'POST',
        url: ajax_file,
        dataType: "jsonp",
        timeout: 5000,
        crossDomain: true,
        data: {
            usuario: dados['USUARIO'],
            senha: dados['SENHA'],
            url: dados['URL']
        },
        error: function () {
            loading('hide');
            //caso servidor nao esteja disponivel vamos apenas limpar os dados de conexao e redirecionar para pagina de login
            //localStorage.clear();
            Storagelogout();
            window.location.href = 'pages.html#page_login';
        },
        success: function (data) {
            if (data) {
                //localStorage.clear();
                Storagelogout();
                window.location.href = 'pages.html#page_login';
            }
        }
    });
}

/*
 * Verifica conexão do dispositivo de 30 em 30 segundos. Caso não haja conexão
 * irá verificar de 1 em 1 segundo, até a conexão estiver ativa novamente.
 */
function checkNetConnection() {
    var d = new Date();

    var scriptElem = document.createElement('script');
    scriptElem.type = 'text/javascript';

    scriptElem.onerror = function () {

        if (conexao_ativa) {
            loading('show', "Sem conexão com a Internet. Favor verificar o sinal.");
        }

        conexao_ativa = false;
        if (verificacao_rapida == false) {
            verificacao_rapida = setInterval(checkNetConnection, 1000);
        }
    };

    scriptElem.src = COMMON_URL_MOBILE + '/checkConexaoCliente.php';
    document.getElementsByTagName("body")[0].appendChild(scriptElem);

    if (conexao_ativa && verificacao_rapida) {
        console.log('Limpar: ' + d.toLocaleString() + '(' + verificacao_rapida + ')');

        clearInterval(verificacao_rapida);
        verificacao_rapida = false;
    }

    conexao_ativa = true;
    loading('hide');
}

function checkSession(primeira) {
    if (primeira) {
        loading('show', "Verificando conexão com o servidor...");
    }

    $.ajax({
        type: 'POST',
        url: COMMON_URL_MOBILE + '/checkSession.php',
        dataType: "jsonp",
        timeout: 10000,
        crossDomain: true,
        data: Objeto_json,
        success: function (data) {
            loading('hide');
            console.log('Sessao = ' + data.session_ativa);
            if (data.session_ativa == false) {
                console.log('Sessao false, logando novamente');
                mobile_login(localStorage.getItem(app_multi + 'mobile_login'));
            } else if (primeira) {
                loading('show', 'Carregando');
                setDadosIniciais();
            }
        },
        error: function () {
            if (num_tentativas < 3) {
                loading('show', "O tempo de resposta do servidor está lento.");
                num_tentativas++;
            } else {
                alert('Por favor verifique sua conexão e faça um novo login');
                window.location.href = 'pages.html#page_login';
            }
            return false;
        }
    });
}

function setDadosIniciais() {
    // Source do Iframe. Chamando o PORTAL DE CLIENTES.
    var src_iframe = COMMON_URL + "?display=portal&m=usuarios&a=portalcliente";
    $("#conteudo").css('display', 'none')
            .attr("src", src_iframe)
            .load(function () {
                loading('hide');
                $(this).fadeIn('slow');
            });
}

/*
 * Identifica a origem do acesso do usuário, se foi feito através de um sistema iOS, Android ou
 * Windows. Qualquer outro, deverá ser inserido no array agents, declarado no topo.
 * O retorno é um string com o nome do agent do usuário.
 */
function detectar_userAgent() {
    user_agent = user_agent.toUpperCase(); // Evitando problemas com maiúsculas e minúsculas
    var agent = new String("");

    $(agents).each(function (k, v) {
        agent_info = user_agent.indexOf(v.toUpperCase()); // Evitando problemas com maiúsculas e minúsculas

        if (agent_info != '-1') {
            agent = v.toUpperCase();
            return false;
        }
    });

    if (agent != '-1') {
        return agent;
    } else {
        console.log('Não foi possível identificar o User Agent da origem do acesso.');
        console.log(user_agent);
        console.log('Considere adicionar no array "agents" do arquivo default.js');

        return false;
    }
}