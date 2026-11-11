# Hollow-Map

Um site completo com diversas utilidades para jogadores de **Hollow Knight** e **Hollow Knight: Silksong**, apontando a localização precisa de amuletos, itens, upgrades, bancos de descanso, estações de besouro e chefes em mapas interativos!

---

## 📋 Atendimento aos Requisitos (`requisitos.md`)

| Requisito | Status | Implementação |
| :--- | :---: | :--- |
| **Backend: PHP estruturado com sessões nativas** | ✅ 100% | Sessões nativas seguras (`session_start()`, CSRF, cookies HttpOnly). |
| **Banco de dados: MySQL com PDO** | ✅ 100% | Conexão isolada em `config/bd.php` com `PDO::ATTR_EMULATE_PREPARES => false` e prepared statements em todas as queries. |
| **Frontend: HTML5, PHP, CSS, Tailwind CSS** | ✅ 100% | HTML5 semântico com Tailwind CSS 3.x CDN e paleta temática Hallownest/Pharloom. |
| **Regra CORE: Senhas com Hash BCrypt** | ✅ 100% | Utilização estrita de `password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12])` e `password_verify()`. |
| **Regra CORE: Histórico e Auditoria Contínua** | ✅ 100% | Página dedicada `historico.php` com rastreamento permanente de criações, edições, exclusões e logins com IP e snapshots de mudanças. |
| **Regras Globais: Separação Lógica de Arquivos** | ✅ 100% | Pastas isoladas (`config/`, `actions/`, `includes/`, `database/`, `assets/`), evitando sistema monolítico. |
| **Regras Globais: Mobile-First responsivo** | ✅ 100% | Layout com drawer de menu mobile, cards responsivos, pan/zoom tátil para telas touch e botões otimizados. |
| **Regras Globais: Mensagens de Erro em TOAST** | ✅ 100% | Sistema dinâmico de Toasts flutuantes integrados às flash messages de sessão e eventos JS. |
| **Regras Globais: Sem Caixas Nativas (MODAL)** | ✅ 100% | Todos os alerts e confirms nativos do navegador foram substituídos por modais Tailwind (`#global-app-modal`). |

---

## 🏛️ Estrutura do Projeto

```text
Hollow-Map/
├── actions/                   # Scripts de backend isolados
│   ├── api_marcadores.php     # Endpoint JSON com filtros
│   ├── login.php              # Processa login com BCrypt e auditoria
│   ├── logout.php             # Encerra sessão e registra auditoria
│   ├── marcador_excluir.php   # Exclui ponto com auditoria
│   ├── marcador_salvar.php    # Cria ou edita ponto com auditoria
│   └── register.php           # Cadastro com BCrypt e auditoria
├── assets/
│   ├── css/
│   │   └── custom.css         # Estilização visual (Glow Soul, Silk, fontes)
│   ├── img/
│   │   ├── map_hallownest.svg # Mapa vetorial de Hallownest (Hollow Knight)
│   │   └── map_pharloom.svg   # Mapa vetorial de Pharloom (Silksong)
│   └── js/
│       ├── app.js             # Gerenciamento de Toasts e Modais personalizados
│       └── map.js             # Zoom, Pan, Drag, renderização e clique no mapa
├── config/
│   └── bd.php                 # Conexão segura MySQL via PDO (Singleton)
├── database/
│   └── schema.sql             # Estrutura do banco, tabelas relacionais e seeders
├── includes/
│   ├── auth.php               # Helpers de autenticação e proteção de rotas
│   ├── footer.php             # Rodapé com injeção de modais e scripts
│   ├── header.php             # Cabeçalho HTML5 com Tailwind CSS
│   ├── helpers.php            # Flash messages (Toast), CSRF e sanitização
│   ├── logs.php               # Sistema central de auditoria e busca
│   ├── modal.php              # Modais customizados Tailwind
│   ├── navbar.php             # Barra de navegação responsiva Mobile-First
│   └── toast.php              # Renderizador do container de toasts
├── index.php                  # Mapa interativo com filtros e alternância de jogos
├── marcadores.php             # Listagem em cards/tabela de todos os marcadores
├── historico.php              # Painel de auditoria e logs (Regra CORE)
├── login.php                  # Tela de autenticação estilizada
├── cadastro.php               # Tela de cadastro de novos usuários
└── requisitos.md              # Requisitos originais do sistema
```

---

## 🚀 Como Executar o Projeto

### 1. Requisitos
- Servidor PHP 8.0 ou superior (com extensões `pdo_mysql`, `session` ativas)
- Servidor MySQL 5.7+ ou MariaDB 10.3+ (ex: XAMPP, Laragon, Docker ou MySQL Server nativo)

### 2. Configurar o Banco de Dados MySQL
Importe o arquivo `database/schema.sql`:
```bash
mysql -u root -p < database/schema.sql
```
O script criará o banco `hollow_map`, todas as tabelas com chaves estrangeiras e os dados iniciais de exemplo.

### 3. Configurar Credenciais do Banco
Caso o seu MySQL utilize senha ou porta diferente do padrão (`127.0.0.1:3306`, usuário `root`, senha vazia), você pode definir variáveis de ambiente ou ajustar diretamente em `config/bd.php`:
- `DB_HOST`: Host do MySQL (padrão: `127.0.0.1`)
- `DB_PORT`: Porta (padrão: `3306`)
- `DB_NAME`: Nome da base (padrão: `hollow_map`)
- `DB_USER`: Usuário do banco (padrão: `root`)
- `DB_PASS`: Senha do banco (padrão: vazia)

### 4. Iniciar o Servidor Embutido do PHP
Na pasta raiz do projeto, execute:
```bash
php -S localhost:8000
```
Acesse no seu navegador: `http://localhost:8000`

---

## 🔑 Credenciais de Demonstração (Seeders Iniciais)

| Usuário | E-mail | Senha | Nível |
| :--- | :--- | :--- | :--- |
| **Mestre das Sombras** | `admin@hollowmap.com` | `admin123` | Administrador |
| **O Cavaleiro** | `cavaleiro@hollowmap.com` | `knight123` | Explorador (Editor) |

*Todas as senhas são criptografadas com `PASSWORD_BCRYPT` com custo 12.*
