## Objetivo 
Esta tarefa tem como objetivo organizar o projeto no Laravel

#### Etapa 1: Organização dos Controllers: 

1. Os controllers devem seguir exatamente esta organização na pasta.
    - `/Pages`: Controllers que devem ser usados somente para renderizar uma página via inertia.
    - `/Invokes`: Controllers que não se encaixam como operação de CRUD padrão. Por exemplo: troca de status, desativação...

    Na raiz deve ficar os controllers de módulo que irão carregar operações de crud: `STORE`, `UPDATE`, `DELETE`.

2. Operações `STORE`, `UPDATE`, `DELETE` devem ser importadas das `Actions`.<br/>
    Todas as operações devem ser feitas em arquivos únicos na pasta `app/Actions` e carregadas como parâmetros nos métodos dentro dos controllers<br/>
    para evitar carregamento dentro do método e ocupação de espaço do método.

3. Validações de formulário devem ter um arquivo `REQUEST`.<br/>
    Operações que demandarem validação de dados como em `STORE` e `UPDATE` devem ter um arquivo na pasta `app\Http\Requests` e esses<br/>
    devem ser carregadas nos parâmetros no método para evitar carregamento dentro do método e ocupação de espaço do método.

4. Os parâmetros dos métodos devem seguir uma organização clara.<br/>
    Vamos manter uma sequência de parâmetros nos métodos padrão para o projeto inteiro, siga a sequência abaixo.<br/>
    - Request 
    - Action 
    - Model ( Route Bindind )

5. Organização dos controllers de renderização de página.<br/>
    Os controllers na pasta `/Pages` devem ter métodos de render. Cada conteúdo deve ter seu método privado dentro do mesmo controller como por exemplo: `indexPosts`, `indexPrograms`.<br/> e depois esses métodos devem ser chamados dentro do método render.<br/>

    Cada método de `index` deve usar um arquivo de filtragem dentro da pasta `app/Filters`, os filtes devem instanciados em um construtor dentro do controller. 

6. Uso de módulos e arquivos externos tem que usar o `use...`.<br/>
    Em vez de fazer o import por exemplo de classes dentro da lógica da classe deve se importar tudo o que for necessário antes da abertura classe usando o<br/>
    atributo `use`.<br/>

    Por exemplo: `use App\Models\Activity`.

7. Organização das importações devem seguir um padrão.<br/>
    Segue abaixo a lista de como deve ser organizado.<br/>
    - `Métodos padrão do laravel`: Requests, Inertia, Facades...
    - `Exceptions`
    - `Models`
    - `Requests`
    - `Resources`
    - `Actions`
    - `Services`

8. Cada método deve ter uma chamada de `authorize`.<br/>
    Somente na pasta `private`, cada método deve ter sua chamada de `authorize` das props, caso o método tenha uma validação de dados com `request` personalizada<br/>
    esse `authorize` deve ir dentro do arquivo de `request` correspondente no método `authorize`.

9. Métodos `show` devem retornar um `InertiaRender`.<br/>
    Para evitar que tudo desencadeie método `render`. Cada método `show` deverá retornar uma resposta `InertiaRender` carregando os dados com uma props correspondente.<br/>

    Para evitar perca de dados na UI devem ser tragos também para o método `show` as props do controller de renderização.

#### Etapa 2: Organização das Actions

1. Actions devem ser organizadas por pastas de escopo.<br/>
    Todas as actions devem ser organizadas na pasta `Actions` pelo escopo. Por exemplo actions que cuidam de usuários devem ficar na pasta `users`, actions</br>
    que cuidam de podcats devem ficar na pasta `podcasts` e etc.<br/>

    Caso de dúvidas basta olhar como está a organização atual.

2. Cada actions deve seguir um padrão de nome.<br/>
    Cada action deve seguir uma nomeclatura de nome que indica o que ela faz e o seu módulo.<br/>
    Por exemplo: `StorePodcast`, `UpdateUser` 

3. Actions devem usar `DB::transaction`.<br/>
    Algumas actions fazem várias ações em cadeia. Para evitar os famosos "dados fantasmas" use `DB::transaction`.

4. Dados devem ser passados via parâmetros.<br/>
    Os dados devem ser passados via parâmetro para as actions, quando se tratar de dados únicos pertencentes a algum modelo como por exemplo: `Users` o modelo</br>
    deve ser carregado via parâmetro para que seja evitada uma consulta desnecessária ao banco.

5. Ações extras devem virar métodos privados.<br/>
    Caso exista actions que façam além de cadastrar os dados na tabela como por exemplo: busca em uma outra tabela para completar um cadastro. Essas etapas extras</br>
    devem ficar em métodos privados na action. 

6. Organização das importações devem seguir um padrão.<br/>
    Segue abaixo a lista de como deve ser organizado.<br/>
    - `Métodos padrão do laravel`: Facades, exceptions e etc...
    - `Exceptions`
    - `Models`
    - `Services`

#### Etapa 3: Organização dos Filters

1. Filters devem ficar na raiz.<br/>
    Os filters devem ficar na raiz da pasta `app/Filters`. Cada filter faz uma busca no banco em seu modelo correspondente aceitando diversas formas<br/>
    de entregar os dados . 

2. Padrão no nome dos arquivos.<br/>
    Cada filter deve seguir o padrão "nome do model" + "filter". Por exemplo: `UserFilter`, `CalendarFilter`.

3. Cada filter deve ter um parâmetro `filters`.<br/>
    Cada arquivo de filter deve esperar receber um parâmetro do tipo array chamado `filters`. Nesse parâmetro será passado todas as filtragens necessárias<br/>
    para a consulta.

4. A filtragem deve utilizar o método `when` do eloquent.<br/>
    Usando abstrações do eloquent, os filtros devem utilizar o método `when` para as buscas seguindo do retorno com a páginação ou não.<br/>
    Por exemplo:<br/>
    ```
        $query = Calendar::query()
            ->when(
                $filters['upcoming'] ?? false,
                fn (Builder $query) => $query->upcoming()
            )
            ->when(
                $filters['with'] ?? null,
                fn (Builder $query, array|string $relations) => $query->with($relations)
            )
            ->orderBy(
                $filters['order_by'] ?? 'id',
                $filters['order_direction'] ?? 'desc'
            );

        return $query->when(
            $filters['paginate'] ?? null,
            fn (Builder $query, int $perPage) => $query->paginate($perPage),
            fn (Builder $query) => $query->get()
        );
    ```




