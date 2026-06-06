# Controllers

## Imports

Nos controllers PHP, organizar os imports em blocos separados por uma linha em branco.

Ordem dos blocos:

1. Imports base do controller e framework
   - Exceptions
   - `App\Http\Controllers\Controller`
   - Services
   - `Illuminate\...`
   - `Inertia\...`

2. Concerns
   - `App\Http\Controllers\Concerns\...`

3. Models
   - `App\Models\...`

4. Resources
   - `App\Http\Resources\...`

5. Actions
   - `App\Actions\...`

6. Requests
   - `App\Http\Requests\...`

Manter cada bloco em ordem alfabética quando possível.
