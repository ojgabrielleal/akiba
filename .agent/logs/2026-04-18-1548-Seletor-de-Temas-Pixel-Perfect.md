# Implementação do Seletor de Temas Pixel-Perfect

Seguindo a referência visual fornecida, foi implementado um seletor de temas (Claro, Auto, Escuro) com design de alta fidelidade na Navbar pública.

## Detalhes da Implementação

### 1. Design do Componente
- **Container**: Estilo "pílula" (pill-shaped) com fundo azul `--color-blue-skywave`.
- **Destaque Ativo**: Círculos de seleção com cores dinâmicas:
    - **Modo Claro**: Fundo `--color-suspense-honeycream` com ícone laranja.
    - **Modo Auto/Escuro**: Fundo `--color-blue-ocean` com ícone laranja ou branco.

### 2. Ícones Customizados (SVG Inline)
- **Sun**: Ícone de sol com detalhes de raios.
- **Auto ("A")**: Ícone estilizado representado por uma ponta de seta/A angular.
- **Moon**: Crescente com duas estrelas pequenas sobrepostas para maior profundidade visual.

### 3. Lógica de Interface
- Adicionada uma variável reativa `activeTheme` no `Navbar.svelte` para gerenciar a troca visual imediata ao clicar.
- Incluídas transições suaves (`transition-all duration-300`) para a mudança dos círculos de seleção e cores dos ícones.

---
**Data:** 18 de Abril de 2026
**Responsável:** Antigravity (IA)
