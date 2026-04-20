<?php
namespace Piplup\ImageX\Console;

use Piplup\ImageX\Contracts\ImageManagerInterface;

class GenerateCommand
{
    public function run(array $args): int
    {
        $parsed = $this->parseArgs($args);

        $mgrClass = getenv('IMAGE_MANAGER_CLASS') ?: null;
        if ($mgrClass && class_exists($mgrClass) && is_subclass_of($mgrClass, ImageManagerInterface::class)) {
            $mgr = new $mgrClass();
            $attrs = $mgr->getAttributes($parsed['path'] ?? '', $parsed['options'] ?? []);
            echo json_encode(['command' => 'generate', 'attributes' => $attrs]) . PHP_EOL;
            return 0;
        }

        echo json_encode(['command' => 'generate', 'path' => $parsed['path'] ?? null, 'options' => $parsed['options']]) . PHP_EOL;
        return 0;
    }

    private function parseArgs(array $args): array
    {
        $out = ['path' => null, 'options' => []];
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                $kv = substr($arg, 2);
                if (str_contains($kv, '=')) {
                    [$k, $v] = explode('=', $kv, 2);
                    $out['options'][$k] = $v;
                } else {
                    $out['options'][$kv] = true;
                }
            } else if ($out['path'] === null) {
                $out['path'] = $arg;
            } else {
                $out['options'][] = $arg;
            }
        }
        return $out;
    }
}
