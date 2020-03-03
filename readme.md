# Trabalho de Conclusão de curso

## Sistema de controle de acesso utilizando autenticação por RFID e gerenciamento por meio de software WEB

[Link para download do arquivo PDF de todo o projeto](https://www.monografias.ufop.br/handle/35400000/2222).

# Passos de configuração

## Utilizar a branch master

## Rodar o composer

Abrir o terminal e executar `composer install`

## Configurar o arquivo .env

Abrir o terminal e executar `cp .env.example .env`, e popular as variaveis com os dados de conexão de seu banco de dados

## Criar as migrations

Abrir o terminal e executar `php artisan migrate`

## Rodar o seeder para criar usuário de teste

Abrir o terminal e executar `php artisan db:seed`, Usuário: `adminuser@admin.com` Senha `!Admin123`

## Criar a chave de autenticação JWT

Abrir o terminal e executar `php artisan jwt:secret`

## Criar chave padrão

Abrir o terminal e executar `php artisan key:generate`

# Front-End do projeto

Todo o front-end da aplicação foi feito utilizando o framework **Angular**. [Repositório do Front](https://github.com/viniciusrufop/controle_acesso_angular.git)

# Firmware do projeto

O firmware foi feito utilizando a linguagem Arduino, que tem por base a linguagem C++. Algumas funções foram feitas utilizando FreeRTOS, que é um sistema operacional de tempo real. A placa de desenvolvimento utilizada no projeto foi a **ESP32-DevKitC**. [Repositório do Firmware](https://github.com/viniciusrufop/controle_acesso_firmware.git)