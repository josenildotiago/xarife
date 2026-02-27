<?php

namespace Pmm\Xarife\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeOrgao extends Command
{
    protected $signature = 'make:orgao {name : Sigla do órgão (ex: segov)}';

    protected $description = 'Gera toda a estrutura de módulo para um novo órgão (controller, services, rotas, factories e navegação)';

    public function handle(): int
    {
        $name = $this->argument('name');
        $sigla = strtolower($name);
        $classe = Str::studly($name);

        $this->newLine();
        $this->line("  Gerando módulo para o órgão: <comment>{$classe}</comment> (<comment>{$sigla}</comment>)");
        $this->newLine();

        $this->generateController($sigla, $classe);
        $this->generateProductService($sigla, $classe);
        $this->generateItemService($sigla, $classe);
        $this->generateRoutesFile($sigla, $classe);
        $this->updateWebRoutes($sigla);
        $this->updateItemServiceFactory($sigla, $classe);
        $this->updateProductServiceFactory($sigla, $classe);
        $this->updateNavigation($sigla, $classe);

        $this->newLine();
        $this->info("Módulo {$classe} gerado com sucesso!");
        $this->newLine();
        $this->comment('Próximos passos:');
        $this->line('  1. Execute: <info>php artisan wayfinder:generate</info>');
        $this->line("  2. Revise <info>routes/{$sigla}.php</info> com as rotas específicas do órgão");
        $this->line("  3. Implemente os métodos faltantes em <info>{$classe}Controller</info> (lote, reports, listUser, editUser, updateUser, destroyUser)");
        $this->newLine();

        return Command::SUCCESS;
    }

    // =========================================================================
    // FILE GENERATORS
    // =========================================================================

    private function generateController(string $sigla, string $classe): void
    {
        $dir = app_path("Http/Controllers/{$classe}");
        $file = "{$dir}/{$classe}Controller.php";

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($file)) {
            $this->line("  <comment>[SKIP]</comment> Controller já existe: app/Http/Controllers/{$classe}/{$classe}Controller.php");

            return;
        }

        file_put_contents($file, $this->buildStub('controller', $sigla, $classe));
        $this->line("  <info>[OK]</info>   Controller criado:       app/Http/Controllers/{$classe}/{$classe}Controller.php");
    }

    private function generateProductService(string $sigla, string $classe): void
    {
        $file = app_path("Services/Product/{$classe}ProductService.php");

        if (file_exists($file)) {
            $this->line("  <comment>[SKIP]</comment> ProductService já existe: app/Services/Product/{$classe}ProductService.php");

            return;
        }

        file_put_contents($file, $this->buildStub('product-service', $sigla, $classe));
        $this->line("  <info>[OK]</info>   ProductService criado:   app/Services/Product/{$classe}ProductService.php");
    }

    private function generateItemService(string $sigla, string $classe): void
    {
        $file = app_path("Services/Item/{$classe}ItemService.php");

        if (file_exists($file)) {
            $this->line("  <comment>[SKIP]</comment> ItemService já existe:    app/Services/Item/{$classe}ItemService.php");

            return;
        }

        file_put_contents($file, $this->buildStub('item-service', $sigla, $classe));
        $this->line("  <info>[OK]</info>   ItemService criado:      app/Services/Item/{$classe}ItemService.php");
    }

    private function generateRoutesFile(string $sigla, string $classe): void
    {
        $file = base_path("routes/{$sigla}.php");

        if (file_exists($file)) {
            $this->line("  <comment>[SKIP]</comment> Arquivo de rotas já existe: routes/{$sigla}.php");

            return;
        }

        file_put_contents($file, $this->buildStub('routes', $sigla, $classe));
        $this->line("  <info>[OK]</info>   Rotas criadas:           routes/{$sigla}.php");
    }

    // =========================================================================
    // FILE UPDATERS
    // =========================================================================

    private function updateWebRoutes(string $sigla): void
    {
        $file = base_path('routes/web.php');
        $content = file_get_contents($file);
        $requireLine = "require __DIR__.'/{$sigla}.php';";

        if (str_contains($content, $requireLine)) {
            $this->line("  <comment>[SKIP]</comment> web.php já possui require para {$sigla}.php");

            return;
        }

        file_put_contents($file, rtrim($content)."\n".$requireLine."\n");
        $this->line("  <info>[OK]</info>   web.php:                 require {$sigla}.php adicionado");
    }

    private function updateItemServiceFactory(string $sigla, string $classe): void
    {
        $file = app_path('Services/Item/ItemServiceFactory.php');
        $content = file_get_contents($file);

        if (str_contains($content, "'{$sigla}'")) {
            $this->line("  <comment>[SKIP]</comment> ItemServiceFactory já possui entrada para '{$sigla}'");

            return;
        }

        $entry = "            '{$sigla}' => {$classe}ItemService::class,\n";
        $content = str_replace(
            '            default => throw',
            $entry.'            default => throw',
            $content
        );

        file_put_contents($file, $content);
        $this->line("  <info>[OK]</info>   ItemServiceFactory:      '{$sigla}' => {$classe}ItemService adicionado");
    }

    private function updateProductServiceFactory(string $sigla, string $classe): void
    {
        $file = app_path('Services/Product/ProductServiceFactory.php');
        $content = file_get_contents($file);

        if (str_contains($content, "'{$sigla}'")) {
            $this->line("  <comment>[SKIP]</comment> ProductServiceFactory já possui entrada para '{$sigla}'");

            return;
        }

        $entry = "            '{$sigla}' => {$classe}ProductService::class,\n";
        $content = str_replace(
            '            default => throw',
            $entry.'            default => throw',
            $content
        );

        file_put_contents($file, $content);
        $this->line("  <info>[OK]</info>   ProductServiceFactory:   '{$sigla}' => {$classe}ProductService adicionado");
    }

    private function updateNavigation(string $sigla, string $classe): void
    {
        $file = base_path('resources/js/hooks/navigation.ts');

        if (! file_exists($file)) {
            $this->warn("  [WARN]  navigation.ts não encontrado em resources/js/hooks/navigation.ts — atualize manualmente.");

            return;
        }

        $content = file_get_contents($file);
        $upper = strtoupper($sigla);

        if (str_contains($content, "{$sigla}SpecificItems")) {
            $this->line("  <comment>[SKIP]</comment> navigation.ts já possui entrada para {$sigla}");

            return;
        }

        // 1. Add import after last import line
        $import = "import { index as {$sigla}OrgaoIndex, createUser as {$sigla}CreateUser } from '@/actions/App/Http/Controllers/{$classe}/{$classe}Controller';";
        $lines = explode("\n", $content);
        $lastImportIndex = -1;

        foreach ($lines as $i => $line) {
            if (str_starts_with(trim($line), 'import ')) {
                $lastImportIndex = $i;
            }
        }

        if ($lastImportIndex !== -1) {
            array_splice($lines, $lastImportIndex + 1, 0, [$import]);
            $content = implode("\n", $lines);
        }

        // 2. Add specific items block before "// Navegação por órgão"
        $specificItems = "// Itens específicos {$upper}\n"
            ."const {$sigla}SpecificItems: NavItem[] = [\n"
            ."    {\n"
            ."        title: 'Sobre o Órgão',\n"
            ."        href: {$sigla}OrgaoIndex().url,\n"
            ."        icon: Archive,\n"
            ."        permission: ['ver orgao'],\n"
            ."    },\n"
            ."    {\n"
            ."        title: 'Gerenciar Usuários',\n"
            ."        href: {$sigla}CreateUser().url,\n"
            ."        icon: Users,\n"
            ."        permission: ['criar permissões'],\n"
            ."    },\n"
            ."];\n\n";

        $content = str_replace(
            '// Navegação por órgão',
            $specificItems.'// Navegação por órgão',
            $content
        );

        // 3. Add navItems const before "// Mapeamento"
        $navItem = "const {$sigla}NavItems: NavItem[] = [...defaultNavItems, ...productNavItems, ...itemNavItems, ...{$sigla}SpecificItems];\n\n";
        $content = str_replace(
            '// Mapeamento entre o identificador',
            $navItem.'// Mapeamento entre o identificador',
            $content
        );

        // 4. Add entry to navigationMap before "    default:"
        $mapEntry = "    {$sigla}: {$sigla}NavItems,\n";
        $content = str_replace(
            '    default: defaultNavItems,',
            $mapEntry.'    default: defaultNavItems,',
            $content
        );

        file_put_contents($file, $content);
        $this->line("  <info>[OK]</info>   navigation.ts:           {$sigla}NavItems adicionado ao navigationMap");
    }

    // =========================================================================
    // STUB BUILDER
    // =========================================================================

    /**
     * Loads a stub file (from published stubs or package defaults) and replaces
     * the {CLASSE}, {SIGLA} and {UPPER} placeholders.
     */
    private function buildStub(string $name, string $sigla, string $classe): string
    {
        $published = base_path("stubs/xarife/{$name}.stub");
        $default = __DIR__."/../../stubs/{$name}.stub";

        $path = file_exists($published) ? $published : $default;
        $stub = file_get_contents($path);

        return str_replace(
            ['{CLASSE}', '{SIGLA}', '{UPPER}'],
            [$classe, $sigla, strtoupper($sigla)],
            $stub
        );
    }
}
