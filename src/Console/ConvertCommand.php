<?php
namespace Piplup\ImageX\Console;

class ConvertCommand
{
    public function run(array $args): int
    {
        $parsed = $this->parseArgs($args);
        echo json_encode(['command' => 'convert', 'path' => $parsed['path'] ?? null, 'options' => $parsed['options']]) . PHP_EOL;
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
