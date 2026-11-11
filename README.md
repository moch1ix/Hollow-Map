# Hollow-Map (reconstrução)

Guia independente com mapas personalizados, filtros, marcadores, favoritos e progresso por usuário.

## Requisitos
- PHP 8.2+ com PDO MySQL
- MySQL 8+ ou MariaDB compatível
- XAMPP ou Laragon

## Instalação
1. Copie a pasta `Hollow-Map` para `htdocs` (XAMPP) ou `www` (Laragon).
2. Importe `database/schema.sql` pelo phpMyAdmin.
3. Importe `database/seed.sql`.
4. Configure credenciais em `config/config.php` ou variáveis `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
5. Abra `http://localhost/Hollow-Map/`.

## Primeiro administrador
Cadastre uma conta normalmente. Depois execute no banco, substituindo o e-mail:
```sql
UPDATE users SET role='admin' WHERE email='seu-email@exemplo.com';
```
Não há senha padrão insegura.

## Mapas e coordenadas
Adicione apenas imagens próprias, licenciadas ou cujo uso esteja autorizado em `assets/maps/hollow-knight/map.png` e `assets/maps/silksong/map.png`. O banco já contém caminhos sugeridos. Atualize `maps.image_width` e `maps.image_height` com dimensões reais. O frontend usa CRS.Simple, detecta as dimensões reais da imagem e converte percentuais em coordenadas da imagem. O cadastro de marcadores exige percentuais; registre como `verified` apenas quando houver fonte confiável. Não há coordenadas oficiais pré-carregadas.

## Dados
`seed.sql` cria jogos e categorias iniciais, sem fingir que há marcadores oficiais completos. O sistema não extrai nem copia mapas de sites de terceiros.

## Testes manuais
- Importar os SQLs e abrir a página inicial.
- Criar conta, entrar e sair.
- Tornar a conta administradora via SQL e acessar `/admin/`.
- Cadastrar marcador com posição e fonte.
- Conferir filtros, busca, favoritos e progresso.
- Repetir com ambos os jogos para conferir a separação.

## Segurança / observações
PDO prepared statements, `password_hash`, sessões HttpOnly/SameSite, token CSRF nos formulários POST, escaping HTML e controle de acesso básico. Antes de publicar na internet, configure HTTPS, política de upload restrita, rate limiting, logs e revisão de segurança.
