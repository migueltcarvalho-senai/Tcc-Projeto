/**
 * js/acessibilidade.js — Controle da Aba de Acessibilidade & Modo Escuro
 * Sketchbook Educacional | Ensino Fundamental 1
 */

(function() {
    'use strict';

    // Cria e insere o HTML da aba se ainda não existir no DOM
    function injetarAbaAcessibilidade() {
        if (document.getElementById('drawer-acessibilidade')) return;

        const drawer = document.createElement('aside');
        drawer.id = 'drawer-acessibilidade';
        drawer.setAttribute('aria-label', 'Controles de Acessibilidade');

        drawer.innerHTML = `
            <div id="painel-acessibilidade" class="visivel" role="dialog" aria-modal="false" aria-label="Painel de Acessibilidade">
                <div class="acessibilidade-titulo">
                    <span>AJUSTES</span>
                    <button type="button" id="btn-fechar-drawer" class="btn-fechar-drawer" aria-label="Fechar painel">✕</button>
                </div>

                <!-- SELEÇÃO DE TAMANHO DE FONTE -->
                <div class="acessibilidade-secao">
                    <span class="acessibilidade-label">TAMANHO DA FONTE:</span>
                    <div class="botoes-tamanho-fonte" role="group" aria-label="Tamanhos de letra">
                        <button type="button" class="btn-fonte" data-tamanho="pequena" title="Letra Pequena">
                            A-
                        </button>
                        <button type="button" class="btn-fonte ativo" data-tamanho="padrao" title="Letra Padrão">
                            A
                        </button>
                        <button type="button" class="btn-fonte" data-tamanho="grande" title="Letra Grande">
                            A+
                        </button>
                    </div>
                </div>

                <!-- BOTÃO MODO CLARO / ESCURO -->
                <div class="acessibilidade-secao" style="margin-bottom: 0;">
                    <span class="acessibilidade-label">TEMA VISUAL:</span>
                    <button type="button" id="btn-toggle-tema" class="btn-tema">
                        <span id="label-tema-texto">MODO ESCURO</span>
                    </button>
                </div>
            </div>

            <!-- BOTÃO FLUTUANTE DE ABRIR/FECHAR -->
            <button type="button" id="btn-toggle-acessibilidade" aria-expanded="true" title="Abrir/Fechar Ajustes de Acessibilidade">
                <span>AJUSTES</span>
            </button>
        `;

        document.body.appendChild(drawer);
        inicializarEventos();
    }

    function inicializarEventos() {
        const painel = document.getElementById('painel-acessibilidade');
        const btnToggle = document.getElementById('btn-toggle-acessibilidade');
        const btnFechar = document.getElementById('btn-fechar-drawer');
        const btnTema = document.getElementById('btn-toggle-tema');
        const labelTema = document.getElementById('label-tema-texto');
        const botoesFonte = document.querySelectorAll('.btn-fonte');

        // Carregar preferências salvas
        const tamanhoSalvo = localStorage.getItem('sketchbook_font_size') || 'padrao';
        const temaSalvo = localStorage.getItem('sketchbook_theme') || 'claro';

        aplicarTamanhoFonte(tamanhoSalvo);
        aplicarTema(temaSalvo);

        // Abrir/Fechar painel
        if (btnToggle && painel) {
            btnToggle.addEventListener('click', () => {
                const estaVisivel = painel.classList.contains('visivel');
                if (estaVisivel) {
                    painel.classList.remove('visivel');
                    painel.classList.add('escondido');
                    btnToggle.setAttribute('aria-expanded', 'false');
                } else {
                    painel.classList.remove('escondido');
                    painel.classList.add('visivel');
                    btnToggle.setAttribute('aria-expanded', 'true');
                }
            });
        }

        if (btnFechar && painel && btnToggle) {
            btnFechar.addEventListener('click', () => {
                painel.classList.remove('visivel');
                painel.classList.add('escondido');
                btnToggle.setAttribute('aria-expanded', 'false');
            });
        }

        // Seleção de tamanho de fonte
        botoesFonte.forEach(btn => {
            btn.addEventListener('click', () => {
                const tamanho = btn.getAttribute('data-tamanho');
                aplicarTamanhoFonte(tamanho);
                localStorage.setItem('sketchbook_font_size', tamanho);
            });
        });

        // Alternância de tema
        if (btnTema) {
            btnTema.addEventListener('click', () => {
                const temaAtual = document.body.classList.contains('modo-escuro') ? 'escuro' : 'claro';
                const novoTema = temaAtual === 'escuro' ? 'claro' : 'escuro';
                aplicarTema(novoTema);
                localStorage.setItem('sketchbook_theme', novoTema);
            });
        }

        function aplicarTamanhoFonte(tamanho) {
            const html = document.documentElement;
            html.classList.remove('fonte-pequena', 'fonte-padrao', 'fonte-grande');

            if (tamanho === 'pequena') {
                html.classList.add('fonte-pequena');
            } else if (tamanho === 'grande') {
                html.classList.add('fonte-grande');
            } else {
                html.classList.add('fonte-padrao');
            }

            botoesFonte.forEach(btn => {
                if (btn.getAttribute('data-tamanho') === tamanho) {
                    btn.classList.add('ativo');
                } else {
                    btn.classList.remove('ativo');
                }
            });
        }

        function aplicarTema(tema) {
            if (tema === 'escuro') {
                document.body.classList.add('modo-escuro');
                if (labelTema) labelTema.textContent = 'MODO CLARO';
            } else {
                document.body.classList.remove('modo-escuro');
                if (labelTema) labelTema.textContent = 'MODO ESCURO';
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injetarAbaAcessibilidade);
    } else {
        injetarAbaAcessibilidade();
    }
})();
