Sistema de Cadastro e Login Seguro (Vesper Aurora)

## O que foi desenvolvido

Sistema completo de **Cadastro e Login** em PHP com design premium exclusivo (**Vesper Aurora**), seguindo boas práticas de segurança para aplicações web.

### Quem desenvolveu

Desenvolvido por: Italo Trancoso Lopes e Gustavo Apolonio da Silva Reis

---

## 📁 Arquivos Entregues

| Arquivo | Descrição |
|---|---|
| [database.sql](file:///c:/Users/user/Desktop/ItaloEGustavo/AtividadeSeguran-a_Cad_e_Login/database.sql) | Script SQL — cria banco e tabela `usuarios` |
| [config.php](file:///c:/Users/user/Desktop/ItaloEGustavo/AtividadeSeguran-a_Cad_e_Login/config.php) | Configuração central: PDO, sessão, helpers CSRF/XSS |
| [style.css](file:///c:/Users/user/Desktop/ItaloEGustavo/AtividadeSeguran-a_Cad_e_Login/style.css) | Design system completo Vesper Aurora |
| [register.php](file:///c:/Users/user/Desktop/ItaloEGustavo/AtividadeSeguran-a_Cad_e_Login/register.php) | Tela de Cadastro |
| [login.php](file:///c:/Users/user/Desktop/ItaloEGustavo/AtividadeSeguran-a_Cad_e_Login/login.php) | Tela de Login |
| [home.php](file:///c:/Users/user/Desktop/ItaloEGustavo/AtividadeSeguran-a_Cad_e_Login/home.php) | Dashboard pós-login (acesso restrito) |
| [logout.php](file:///c:/Users/user/Desktop/ItaloEGustavo/AtividadeSeguran-a_Cad_e_Login/logout.php) | Encerramento seguro de sessão |

---

## 🔒 Recursos de Segurança Implementados

| Ameaça | Proteção Aplicada |
|---|---|
| **SQL Injection** | PDO + Prepared Statements em todas as queries |
| **Senhas expostas** | `password_hash(PASSWORD_DEFAULT)` + `password_verify()` |
| **CSRF** | Token rotativo por sessão + `hash_equals()` na validação |
| **XSS** | Função `escape()` com `htmlspecialchars()` em todos os outputs |
| **Session Fixation** | `session_regenerate_id(true)` após login bem-sucedido |
| **Enumeração de contas** | Mensagem de erro genérica no login + timing attack prevention |
| **Acesso não autorizado** | Verificação de `$_SESSION['user_id']` na home + redirecionamento |
| **Cookie hijacking** | `httponly`, `samesite=Strict`, uso exclusivo de cookies |

---

## 🚀 Como Usar (Passo a Passo XAMPP)

### 1. Preparar o banco de dados
1. Abra o **XAMPP Control Panel** e inicie o **Apache** e o **MySQL**
2. Acesse **phpMyAdmin**: `http://localhost/phpmyadmin`
3. Clique em **"Importar"** → selecione o arquivo [database.sql](file:///c:/Users/user/Desktop/ItaloEGustavo/AtividadeSeguran-a_Cad_e_Login/database.sql) → clique em **"Executar"**

### 2. Copiar os arquivos
Copie a pasta `AtividadeSeguran-a_Cad_e_Login` para dentro de `C:\xampp\htdocs\`

### 3. Acessar o sistema
Abra o navegador e acesse:
```
http://localhost/AtividadeSeguran-a_Cad_e_Login/register.php
```

### 4. Fluxo de uso
```
register.php  →  (cadastro válido)  →  login.php
login.php     →  (credenciais ok)   →  home.php
home.php      →  (botão "Sair")     →  logout.php  →  login.php
```

---

## 🎨 Design System Vesper Aurora

- **Tema**: Dark Mode com fundo Obsidian (`#0B0F19`)
- **Glassmorphism**: Cartões com `backdrop-filter: blur(20px)` e bordas translúcidas
- **Gradiente**: Violeta Neon `#8B5CF6` → Ciano `#06B6D4`
- **Tipografia**: *Poppins* (títulos) + *Inter* (corpo)
- **Animações**: Orbes de luz no fundo, `slideUp` na entrada dos cards, `iconPulse` no logo
- **Responsivo**: Funciona em mobile, tablet e desktop

---

## ✅ Checklist de Validação

> [!TIP]
> Execute estes testes para verificar que tudo funciona corretamente:

- [ ] Cadastro com todos os campos preenchidos corretamente → deve redirecionar para login com mensagem de sucesso
- [ ] Cadastro com senhas diferentes → deve exibir erro sem salvar nada
- [ ] Cadastro com e-mail já existente → deve exibir aviso de duplicidade
- [ ] Cadastro com e-mail inválido → deve exibir erro de validação
- [ ] Login com credenciais corretas → deve redirecionar para `home.php`
- [ ] Login com senha errada → deve exibir mensagem genérica (sem revelar se e-mail existe)
- [ ] Acessar `home.php` diretamente sem login → deve redirecionar para `login.php`
- [ ] Clicar em "Sair" na home → deve limpar sessão e retornar ao login com mensagem
