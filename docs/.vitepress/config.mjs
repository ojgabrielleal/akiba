import { defineConfig } from 'vitepress'
import { join, relative, sep } from 'node:path'

const docsRoot = join(process.cwd(), 'docs')
const planejamentoRoot = join(docsRoot, 'planejamento')
const projetoRoot = join(docsRoot, 'projeto')

function titleFromSlug(slug) {
    return slug
        .replace(/-/g, ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase())
}

function linkFromFile(path) {
    return `/${relative(docsRoot, path).replaceAll(sep, '/').replace(/\.md$/, '')}`
}

function makeItems(paths) {
    return paths.map((path) => ({
        text: titleFromSlug(path.split('/').at(-1)),
        link: linkFromFile(join(planejamentoRoot, `${path}.md`)),
    }))
}

function makeProjectItems(paths) {
    return paths.map((path) => ({
        text: titleFromSlug(path.split('/').at(-1)),
        link: linkFromFile(join(projetoRoot, `${path}.md`)),
    }))
}

export default defineConfig({
    title: 'Akiba Docs',
    description: 'Documentacao tecnica e planejamento do projeto Akiba.',
    cleanUrls: true,
    lastUpdated: true,
    themeConfig: {
        logo: 'https://i.imgur.com/WbKAm6A.png',
        nav: [
            { text: 'Inicio', link: '/' },
            { text: 'Guia do Projeto', link: '/projeto/' },
            { text: 'Planejamento', link: '/planejamento/' },
        ],
        sidebar: {
            '/projeto/': [
                {
                    text: 'Comece Aqui',
                    items: makeProjectItems([
                        'index',
                        'estrutura-geral',
                        'fluxos/feature',
                        'configuracoes',
                    ]),
                },
                {
                    text: 'Autenticacao',
                    items: [
                        { text: 'Painel administrativo', link: '/projeto/autenticacao-interna' },
                        { text: 'OAuth para site publico', link: '/projeto/oauth' },
                    ],
                },
                {
                    text: 'Backend',
                    items: makeProjectItems([
                        'backend/rotas',
                        'backend/controllers',
                        'backend/requests',
                        'backend/actions',
                        'backend/models',
                        'backend/filters',
                        'backend/resources',
                        'backend/policies',
                        'backend/services',
                        'backend/middlewares',
                        'backend/commands',
                    ]),
                },
                {
                    text: 'Interface',
                    items: makeProjectItems([
                        'frontend/interface',
                        'frontend/pages',
                        'frontend/layouts',
                        'frontend/componentes',
                        'frontend/forms',
                        'frontend/stores',
                        'frontend/utils',
                        'frontend/constants',
                    ]),
                },
                {
                    text: 'Banco',
                    items: makeProjectItems([
                        'database/banco',
                        'database/factories',
                        'database/seeders',
                    ]),
                },
                {
                    text: 'Qualidade',
                    items: makeProjectItems([
                        'testes',
                    ]),
                },
            ],
            '/planejamento/': [
                {
                    text: 'Planejamento',
                    items: makeItems([
                        'index',
                        'roadmap',
                        'arquitetura',
                        'decisoes',
                        'mapa-de-modulos',
                        'planejamento-operacional',
                        'historico',
                    ]),
                },
                {
                    text: 'Modulos',
                    items: makeItems([
                        'modulos/dashboard',
                        'modulos/postagens',
                        'modulos/locucao',
                        'modulos/radio',
                        'modulos/podcasts',
                        'modulos/marketing',
                        'modulos/midias',
                        'modulos/administracao',
                        'modulos/relatorios',
                        'modulos/itens-desativados',
                    ]),
                },
                {
                    text: 'Operacao',
                    items: makeItems([
                        'operacao/index',
                        'operacao/area-publica',
                        'operacao/painel-privado',
                        'operacao/conteudo-editorial',
                        'operacao/radio-e-locucao',
                        'operacao/midias-e-interacao',
                        'operacao/administracao-operacional',
                    ]),
                },
                {
                    text: 'Governanca e qualidade',
                    items: makeItems([
                        'governanca/index',
                        'governanca/manutencao',
                        'governanca/criterios-de-decisao',
                        'governanca/checklist-de-revisao',
                        'governanca/templates',
                        'qualidade/index',
                        'qualidade/inventario',
                        'qualidade/metricas-de-saude',
                        'qualidade/revisao-periodica',
                        'qualidade/auditoria-de-links',
                        'qualidade/riscos-de-obsolescencia',
                    ]),
                },
                {
                    text: 'Adocao e publicacao',
                    items: makeItems([
                        'adocao/index',
                        'adocao/onboarding',
                        'adocao/mapa-de-leitura',
                        'adocao/glossario',
                        'adocao/vitepress',
                        'publicacao/index',
                        'publicacao/formas-de-compartilhamento',
                        'publicacao/navegacao-no-site',
                        'publicacao/pacote-estatico',
                        'publicacao/checklist-pre-publicacao',
                        'publicacao/informacao-interna',
                    ]),
                },
                {
                    text: 'Automacao e desenvolvimento',
                    items: makeItems([
                        'automacao/index',
                        'automacao/auditoria-local',
                        'automacao/escopo-da-automacao',
                        'desenvolvimento/index',
                        'desenvolvimento/padroes-de-arquivos',
                        'desenvolvimento/checklist-de-implementacao',
                        'desenvolvimento/documentacao-em-pr',
                        'desenvolvimento/mudancas-que-exigem-doc',
                        'desenvolvimento/vinculo-com-tarefas',
                    ]),
                },
                {
                    text: 'Consolidacao',
                    items: makeItems([
                        'consolidacao/index',
                        'consolidacao/resumo-executivo',
                        'consolidacao/matriz-de-entregas',
                        'consolidacao/pendencias-transversais',
                        'consolidacao/proximos-marcos',
                        'consolidacao/criterios-de-encerramento',
                        'ciclos/2026-08',
                        'tarefas/backlog',
                    ]),
                },
            ],
        },
        socialLinks: [
            { icon: 'github', link: 'https://github.com' },
        ],
        search: {
            provider: 'local',
        },
    },
})
