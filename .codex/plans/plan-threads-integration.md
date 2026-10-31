# Planejamento da integração com o Threads

## Objetivo

Integrar a API oficial do Threads ao fluxo de publicações sociais da Rede Akiba, reutilizando a infraestrutura existente de `MetaSocial`, agendamentos, filas, retentativas e registro de resultados.

O primeiro objetivo será publicar automaticamente no Threads os mesmos conteúdos editoriais e avisos de programas ao vivo que já podem ser enviados ao Instagram e Facebook.

## Premissas técnicas

- A integração utilizará a API oficial do Threads.
- O Threads será adicionado como um novo `PublicationChannel`.
- As publicações continuarão armazenadas em `social_publication_schedules`.
- O processamento continuará sendo feito pelo job `PublishSocialPublicationSchedule`.
- O canal ficará desabilitado por padrão e será ativado por variável de ambiente.
- A primeira versão trabalhará com texto e uma imagem.
- O token será configurado no ambiente durante o MVP.
- O host da API do Threads será `https://graph.threads.net`.
- A publicação seguirá o fluxo de criação e posterior publicação de um container.

---

## Capítulo 1 — Definir o escopo inicial

### Etapa 1.1 — Definir o MVP

Implementar inicialmente:

- Publicação de texto com uma imagem.
- Divulgação de posts publicados no site.
- Divulgação de programas ao vivo.
- Execução pela fila já existente.
- Registro do ID retornado pelo Threads.
- Retentativas para falhas temporárias.

### Etapa 1.2 — Separar recursos avançados

Deixar para versões posteriores:

- Vídeos.
- Carrosséis.
- Enquetes.
- Respostas automáticas.
- Métricas e insights.
- Moderação de respostas.
- Publicações encadeadas.

### Etapa 1.3 — Definir o formato editorial

- Limitar a publicação a 500 caracteres.
- Preservar o título ou a chamada principal.
- Preservar o link do conteúdo.
- Remover primeiro textos complementares quando o limite for excedido.
- Limitar hashtags para não comprometer o texto principal.
- Nunca cortar uma URL no meio.

### Critério de conclusão

Existir um exemplo aprovado de como um post editorial e um aviso de programa ao vivo serão exibidos no Threads.

---

## Capítulo 2 — Preparar o aplicativo na Meta

### Etapa 2.1 — Configurar o aplicativo

No Meta for Developers:

- Criar ou selecionar o aplicativo da Rede Akiba.
- Adicionar o caso de uso da API do Threads.
- Informar os domínios da aplicação.
- Cadastrar a URL de redirecionamento OAuth.
- Configurar política de privacidade e exclusão de dados, quando exigidas.

### Etapa 2.2 — Solicitar permissões

Preparar as permissões necessárias para publicação:

- `threads_basic`.
- `threads_content_publish`.

### Etapa 2.3 — Autorizar a conta da Rede Akiba

- Adicionar a conta como usuária de teste durante o desenvolvimento.
- Executar o consentimento OAuth.
- Obter o token inicial.
- Trocar o token inicial por um token de longa duração.
- Consultar e confirmar o ID da conta.
- Consultar e confirmar o nome de usuário.

### Etapa 2.4 — Planejar a renovação do token

No MVP:

- Armazenar o token somente no ambiente.
- Documentar a data de expiração.
- Criar um procedimento manual de renovação.

Depois do MVP:

- Automatizar a renovação.
- Alertar quando a renovação falhar.
- Evitar registrar tokens em logs.
- Se tokens forem armazenados no banco, utilizar criptografia.

### Critério de conclusão

Uma chamada manual à API consegue identificar a conta autorizada da Rede Akiba.

---

## Capítulo 3 — Adicionar as configurações do Threads

### Etapa 3.1 — Criar variáveis de ambiente

Adicionar ao arquivo de exemplo do ambiente:

```dotenv
META_THREADS_ENABLED=false
META_THREADS_FEED_ENABLED=true
META_THREADS_USER_ID=
META_THREADS_ACCESS_TOKEN=
META_THREADS_GRAPH_VERSION=v1.0
```

Manter uma versão separada para o Threads, pois sua API possui ciclo e host próprios.

### Etapa 3.2 — Registrar as configurações

Adicionar em `config/services.php`:

- `threads_enabled`.
- `threads_feed_enabled`.
- `threads_user_id`.
- `threads_access_token`.
- `threads_graph_version`.

### Etapa 3.3 — Validar a configuração

Alterar `PublicationConfiguration` para:

- Reconhecer o canal Threads.
- Exigir token, user ID e versão.
- Disponibilizar inicialmente apenas o placement `feed`.
- Manter o canal desabilitado por padrão.
- Informar claramente quais configurações estão ausentes.

### Critério de conclusão

O sistema identifica corretamente configurações completas, ausentes ou desabilitadas do Threads.

---

## Capítulo 4 — Expandir o domínio de publicação

### Etapa 4.1 — Adicionar o canal

Adicionar ao enum `PublicationChannel`:

```php
case Threads = 'threads';
```

### Etapa 4.2 — Reutilizar o placement existente

Usar inicialmente:

```php
PublicationPlacement::Feed
```

Não criar placements específicos até existir uma necessidade real.

### Etapa 4.3 — Criar uma legenda específica

No serviço de agendamento:

- Criar uma formatação própria para o Threads.
- Receber o texto editorial, link e hashtags.
- Garantir o limite final de 500 caracteres.
- Normalizar espaços e quebras de linha.
- Remover hashtags excedentes.
- Preservar o link completo.
- Tratar emojis e caracteres multibyte corretamente.

### Etapa 4.4 — Definir a arte

Para o MVP:

- Reutilizar uma arte quadrada ou vertical existente.
- Criar o template `threads-feed`.
- Garantir que a imagem tenha uma URL pública acessível pela Meta.
- Não depender de autenticação ou cookies para baixar a imagem.

### Critério de conclusão

O `SocialPublicationScheduleService` consegue gerar um agendamento com `channel=threads` e payload válido.

---

## Capítulo 5 — Implementar o cliente da API

### Etapa 5.1 — Isolar o host do Threads

Adicionar ao cliente o host:

```text
https://graph.threads.net
```

Não enviar requisições do Threads para os hosts do Instagram ou Facebook.

### Etapa 5.2 — Criar o container

Fazer a requisição:

```http
POST /{threads-user-id}/threads
```

Enviar:

```text
media_type=IMAGE
image_url={url pública}
text={legenda}
```

Validar e armazenar o ID do container retornado.

### Etapa 5.3 — Consultar o processamento

- Consultar o status do container.
- Aguardar enquanto ele estiver processando.
- Usar intervalo e quantidade máxima de tentativas configuráveis.
- Não manter o worker bloqueado indefinidamente.
- Classificar estados finais de erro como falha de validação.

### Etapa 5.4 — Publicar o container

Fazer a requisição:

```http
POST /{threads-user-id}/threads_publish
```

Enviar:

```text
creation_id={container-id}
```

Validar e armazenar o ID da publicação final.

### Etapa 5.5 — Retornar o resultado padronizado

Converter a resposta para `PublicationResult`:

- Canal: Threads.
- ID externo: ID da publicação.
- Data: momento da confirmação.

### Etapa 5.6 — Proteger credenciais

Atualizar a sanitização de erros para remover:

- Token do Instagram.
- Token do Facebook.
- Token do Threads.

### Critério de conclusão

Um teste com HTTP simulado cobre a criação, o processamento e a publicação de um container.

---

## Capítulo 6 — Integrar com agendamento e fila

### Etapa 6.1 — Criar o agendamento automaticamente

Quando o Threads estiver habilitado:

- Incluir Threads nos canais disponíveis.
- Gerar uma linha própria em `social_publication_schedules`.
- Gerar uma chave de idempotência contendo `threads`.
- Armazenar legenda, link e informações necessárias no payload.

### Etapa 6.2 — Reutilizar o job existente

O `PublishSocialPublicationSchedule` deverá:

- Converter `threads` para o enum.
- Montar `PublicationData`.
- Chamar o publisher.
- Salvar o `external_id`.
- Marcar o agendamento como publicado ou falho.

### Etapa 6.3 — Classificar os erros

Tratar separadamente:

- Token inválido ou expirado: falha permanente ou de configuração.
- Conteúdo inválido: falha permanente.
- Imagem inacessível: falha de validação.
- Container ainda processando: falha temporária.
- Rate limit: falha temporária com backoff.
- Erro HTTP 5xx: falha temporária.
- Erro de conexão: falha temporária.

### Etapa 6.4 — Evitar publicação duplicada

- Persistir o ID do container antes da publicação final.
- Reaproveitar o container nas retentativas.
- Não criar um novo container em toda tentativa do job.
- Consultar o estado do container antes de repetir a publicação, quando possível.
- Considerar uma coluna própria ou uma estrutura padronizada dentro do payload para o ID do container.

### Critério de conclusão

Reexecutar o job não cria duas publicações no Threads.

---

## Capítulo 7 — Ajustar a interface administrativa

### Etapa 7.1 — Adicionar a seleção do canal

Adicionar um controle próprio para:

```text
Publicar no Threads
```

O controle não deve ser obrigatório.

### Etapa 7.2 — Expandir o payload

Adicionar:

```text
social.channels.threads
```

### Etapa 7.3 — Atualizar a validação

- Aceitar um valor booleano.
- Não exigir Threads para publicar o conteúdo.
- Usar `false` como padrão para conteúdos novos, até a ativação oficial.
- Manter posts antigos compatíveis.

### Etapa 7.4 — Atualizar os formulários Svelte

- Exibir o controle do Threads.
- Usar `orange-citric` por ser um elemento clicável.
- Aplicar o padrão de animação pública ou privada correspondente.
- Exibir um aviso sobre o limite de texto.
- Mostrar uma prévia da legenda final, se viável.

### Etapa 7.5 — Respeitar a seleção no agendador

Combinar as duas condições:

```text
canal habilitado no ambiente
+
canal selecionado no conteúdo
```

O serviço não deve agendar uma publicação para um canal desmarcado.

### Critério de conclusão

Desmarcar Threads impede somente o agendamento do Threads, sem interferir nos outros canais.

---

## Capítulo 8 — Criar os testes

### Etapa 8.1 — Testes de configuração

Cobrir:

- Threads desabilitado.
- Token ausente.
- User ID ausente.
- Versão ausente.
- Configuração completa.

### Etapa 8.2 — Testes do cliente

Cobrir:

- Criação do container.
- Publicação bem-sucedida.
- Resposta sem ID.
- Container em processamento.
- Container com erro.
- Token expirado.
- Rate limit.
- Erro temporário.
- Remoção do token das mensagens de erro.

### Etapa 8.3 — Testes da legenda

Cobrir:

- Texto curto.
- Texto exatamente no limite.
- Texto acima do limite.
- Link preservado.
- Hashtags reduzidas.
- Caracteres multibyte.
- Emojis.

### Etapa 8.4 — Testes de orquestração

Cobrir:

- Criação do agendamento do Threads.
- Threads desmarcado no formulário.
- Threads desabilitado globalmente.
- Post em rascunho.
- Post publicado.
- Programa ao vivo.
- Chave de idempotência.
- Persistência do ID externo.
- Reexecução sem duplicação.

### Etapa 8.5 — Teste manual controlado

- Usar uma conta de teste.
- Publicar um conteúdo sem divulgação pública relevante.
- Conferir texto, imagem e link.
- Executar novamente o job.
- Confirmar que não houve duplicação.

### Critério de conclusão

Os testes automatizados passam e a publicação de homologação aparece corretamente na conta de teste.

---

## Capítulo 9 — Observabilidade e operação

### Etapa 9.1 — Melhorar os registros

Registrar somente:

- Canal.
- ID do agendamento.
- ID do container.
- ID externo.
- Código de erro.
- Tipo da falha.
- Número da tentativa.

Nunca registrar token ou dados completos de autenticação.

### Etapa 9.2 — Acompanhar falhas

Preparar consultas ou painel para:

- Publicações pendentes.
- Publicações falhas.
- Falhas por canal.
- Última publicação bem-sucedida.
- Erros relacionados ao token.

### Etapa 9.3 — Monitorar o limite de publicação

- Consultar o limite de publicação quando necessário.
- Não insistir quando a cota estiver esgotada.
- Reagendar para depois da janela do limite.
- Gerar um alerta administrativo.

### Etapa 9.4 — Criar um runbook

Documentar:

- Como gerar um token.
- Como renovar o token.
- Como validar o user ID.
- Como reexecutar um agendamento.
- Como desabilitar somente o Threads.
- Como investigar uma publicação duplicada.

### Critério de conclusão

Uma pessoa da equipe consegue recuperar a integração sem precisar alterar o código.

---

## Capítulo 10 — Fazer a liberação gradual

### Etapa 10.1 — Desenvolvimento

Manter:

```dotenv
META_THREADS_ENABLED=false
```

Implementar e testar utilizando respostas HTTP simuladas.

### Etapa 10.2 — Homologação

- Habilitar para uma conta de teste.
- Publicar poucos conteúdos.
- Validar texto, imagens e retentativas.
- Confirmar o comportamento da idempotência.

### Etapa 10.3 — Produção assistida

- Habilitar inicialmente apenas para posts editoriais.
- Acompanhar manualmente as primeiras publicações.
- Manter programas ao vivo desabilitados no início.

### Etapa 10.4 — Produção completa

- Habilitar posts e programas ao vivo.
- Ativar alertas.
- Revisar falhas e limites após uma semana.

### Etapa 10.5 — Planejar a próxima evolução

Depois da estabilização:

- Renovação automática do token.
- Vídeos.
- Carrosséis.
- Threads encadeadas.
- Métricas e insights.
- Respostas e moderação.
- Prévia específica no painel administrativo.

---

## Ordem recomendada de implementação

1. Configuração do aplicativo Meta.
2. Variáveis e validação de configuração.
3. Enum e contrato do novo canal.
4. Formatação da legenda.
5. Cliente da API.
6. Persistência do container e idempotência.
7. Agendamento e job.
8. Testes automatizados.
9. Interface administrativa.
10. Homologação e ativação gradual.

## Estimativa inicial

O MVP deve exigir aproximadamente quatro a seis dias de desenvolvimento, sem contar o tempo de análise e aprovação do aplicativo pela Meta.

Os principais riscos são:

- Renovação e expiração do token.
- Publicações duplicadas durante retentativas.
- Compatibilidade da legenda atual com o limite do Threads.
- Tempo de processamento das imagens pela Meta.
- Aprovação das permissões necessárias para produção.

## Referências

- [Documentação oficial da API do Threads](https://developers.facebook.com/docs/threads/)
- [Coleção oficial do Threads no Postman](https://www.postman.com/meta/threads/documentation/dht3nzz/threads-api)
- [Requisição oficial de publicação de container](https://www.postman.com/meta/threads/request/34203612-c940a17f-e719-4b5b-9d28-7390bc658fb7)
