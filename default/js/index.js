$(function () {
    var box_action_button_obj = $('#box-buttons'); // Caixa dos bot�es de a��o
    var action_buttons = "#logout, #home";         // Id dos bot�es de a��o
    
    // Definindo a��es dos bot�es de a��o
    $(action_buttons).on('click', function () {
        var id_action_button = $(this)[0].id;
        if (id_action_button == 'logout') {
            loading('show', 'Deslogando');
            setTimeout(function () {
                mobile_logout();
            }, 1000);
        } else {
            loading('show', 'Carregando');
            setTimeout(function () {
                window.location.href = window.location;
            }, 600);
        }
        toggle_pop_buttons();
    });

    // Overlay preto, transparente que fica atr�s dos bot�es de a��o
    $('.button-plus, #overlay').click(function () {
        toggle_pop_buttons();                                   // D� um pop nos bot�es ou os recolhe, escondendo ou mostrando o overlay
    });

    // Script para esconder os bot�es de a��o da tela
    $('iframe#conteudo').on('load', function () {               // Espera o Iframe carregar tudo (carregar imagens e etc)

        var html_iframe = $($(this).contents()).find('html');
        auto_hide_action_button = function () {                 // Espera 4 segundos e esconde os bot�es da tela
            timeOut_hide_button = setTimeout(function () {
                if (state_action_buttons == 1)
                    return false;

                box_action_button_obj.animate({
                    'right': '-65px'
                }, 'fast');
            }, 4000);
        }

        auto_hide_action_button();                              // Esconde ao carregar a tela

        html_iframe.on("swipeleft", function () {               // Deslizar p/ a direita mostra o bot�o
            box_action_button_obj.animate({
                'right': '0px'
            }, 'fast');
            clearTimeout(timeOut_hide_button);
            auto_hide_action_button();                          // Se deslizado e ficar parado, esconde novamente
        }).on("swiperight", function () {                       // Deslizar p/ esquerda esconde o bot�o
            box_action_button_obj.animate({
                'right': '-65px'
            }, 'fast');
            clearTimeout(timeOut_hide_button);                  /* Deslizar p/ direita ir� esconder o bot�o, ent�o limpa o timeout que
             * ir� chamar a fun��o para esconder.
             * Evita tamb�m que, ao se deslizar para direita e, logo em seguida, p/
             * a esquerda (menos de 4 segundos) que a chamada para esconder programada
             * n�o seja executada.*/
        });

    });
});