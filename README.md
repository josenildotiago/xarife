# Xarife

> Gerador de módulos de órgão para o sistema de almoxarifado da Prefeitura de Mossoró.

[![Latest Version](https://img.shields.io/badge/version-v1.0.3-blue)](https://github.com/josenildotiago/xarife/releases)
[![PHP](https://img.shields.io/badge/PHP-%5E8.3-777BB4?logo=php)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-%5E12.0-FF2D20?logo=laravel)](https://laravel.com)

## O que é

O **Xarife** é um pacote Laravel que automatiza a criação de toda a estrutura necessária para integrar um novo órgão (secretaria, autarquia etc.) ao sistema de almoxarifado. Com um único comando, ele gera e registra automaticamente:

- Controller do órgão
- `ProductService` e `ItemService` específicos
- Arquivo de rotas do órgão
- Entrada no `web.php`
- Entradas nas factories de serviços (`ItemServiceFactory` e `ProductServiceFactory`)
- Navegação lateral em `navigation.ts`

## Requisitos

| Dependência | Versão  |
| ----------- | ------- |
| PHP         | `^8.3`  |
| Laravel     | `^12.0` |

## Instalação

```bash
composer require pmm/xarife
```

O pacote é descoberto automaticamente pelo Laravel via _package auto-discovery_.

## Uso

```bash
php artisan make:orgao {sigla}
```

**Exemplo:**

```bash
php artisan make:orgao segov
```

### O que é gerado

| Arquivo        | Caminho                                          |
| -------------- | ------------------------------------------------ |
| Controller     | `app/Http/Controllers/Segov/SegovController.php` |
| ProductService | `app/Services/Product/SegovProductService.php`   |
| ItemService    | `app/Services/Item/SegovItemService.php`         |
| Rotas          | `routes/segov.php`                               |

Além disso, os seguintes arquivos existentes são **atualizados automaticamente**:

- `routes/web.php` — adiciona `require __DIR__.'/segov.php';`
- `app/Services/Item/ItemServiceFactory.php` — registra `'segov' => SegovItemService::class`
- `app/Services/Product/ProductServiceFactory.php` — registra `'segov' => SegovProductService::class`
- `resources/js/hooks/navigation.ts` — adiciona rotas e itens de navegação do órgão

### Próximos passos após executar o comando

1. `npm run build`
2. `php artisan optimize:clear`
3. `php artisan wayfinder:generate`
4. Revise `routes/{sigla}.php` com as rotas específicas do órgão
5. Implemente os métodos faltantes em `{Classe}Controller` — `lote`, `reports`, `listUser`, `editUser`, `updateUser`, `destroyUser`

## Publicando os stubs

Para personalizar os templates gerados pelo pacote, publique os stubs no projeto:

```bash
php artisan vendor:publish --tag=xarife-stubs
```

Os arquivos serão copiados para `stubs/xarife/`. O comando `make:orgao` dará preferência a esses stubs locais, permitindo que você ajuste os templates sem alterar o pacote.

### Stubs disponíveis

| Stub                   | Descrição                       |
| ---------------------- | ------------------------------- |
| `controller.stub`      | Template do controller do órgão |
| `product-service.stub` | Template do ProductService      |
| `item-service.stub`    | Template do ItemService         |
| `routes.stub`          | Template do arquivo de rotas    |

Os placeholders `{CLASSE}`, `{SIGLA}` e `{UPPER}` são substituídos automaticamente durante a geração.

## Estrutura do pacote

```
xarife/
├── src/
│   ├── Commands/
│   │   └── MakeOrgao.php          # Comando Artisan principal
│   └── XarifeServiceProvider.php  # Service provider do pacote
├── stubs/
│   ├── controller.stub
│   ├── item-service.stub
│   ├── product-service.stub
│   └── routes.stub
└── composer.json
```

## Licença

MIT © Prefeitura Municipal de Mossoró
