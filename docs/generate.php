<?php
/**
 * Generate the documentation for the available commands
 */

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$bin = dirname(__DIR__) . '/joomla';
$out = __DIR__ . '/commands';

$commands = getInfo($bin);

$twig = new Environment(
    new FilesystemLoader(__DIR__ . '/.templates'),
    [
        'debug'            => true,
        'strict_variables' => false,
        'autoescape'       => false,
    ]
);

shell_exec("rm -fr $out");

$template = $twig->load('index.twig');
if (!file_exists($out)) {
    mkdir($out, 0777, true);
}
file_put_contents($out . '/index.md', $template->render([
    'data' => $commands,
]));

$shorts = $commands['commands'][''];

foreach ($commands['commands'] as $group => $details) {
    if ($group === '') {
        continue;
    }

    $template = $twig->load('group_index.twig');

    if (!file_exists($out . '/' . $group)) {
        mkdir($out . '/' . $group, 0777, true);
    }

    if (isset($shorts[$group])) {
        $shorts[$group]['group'] = $group;
        $shorts[$group]['path']  = $group . '/' . $shorts[$group]['path'];
        $details                 = [$group => $shorts[$group]] + $details;
    }

    file_put_contents($out . '/' . $group . '/index.md', $template->render([
        'group' => $group,
        'data'  => $details,
    ]));

    foreach ($details as $detail) {
        $template = $twig->load('command.twig');

        file_put_contents($out . '/' . $detail['path'] . '.md', $template->render([
            'data' => $detail,
        ]));
    }
}
#print_r($commands);

/**
 * @param  string  $bin
 *
 * @return array
 */
function getInfo(string $bin): array
{
    $list             = getCommandList($bin);
    $list['commands'] = getCommandDetails($bin, $list['available commands']);

    unset($list['available commands']);

    return $list;
}

/**
 * @param  string  $bin
 *
 * @return array
 */
function getCommandList(string $bin): array
{
    $raw      = shell_exec("$bin list");
    $sections = explode("\n\n", $raw);
    [$name, $version] = explode(' version ', array_shift($sections));

    $list = [
        'name'    => $name,
        'version' => $version,
    ];

    $list += parseSections($sections);

    return $list;
}

/**
 * @param $bin
 * @param $availableCommands
 *
 * @return array
 */
function getCommandDetails($bin, $availableCommands): array
{
    $group    = '';
    $commands = [];
    foreach ($availableCommands as $command) {
        if (preg_match('~^\s+([\w:]+)\s+(.*?)$~', $command, $m)) {
            $commands[$group][$m[1]] = [
                'group'       => $group,
                'command'     => $m[1],
                'path'        => str_replace(':', '/', $m[1]),
                'part'        => preg_replace('~^.*?:~', '', $m[1]),
                'description' => $m[2],
            ];

            $raw                     = shell_exec("{$bin} {$m[1]} --help");
            $sections                = explode("\n\n", $raw);
            $commands[$group][$m[1]] += parseSections($sections);
            continue;
        }

        if (preg_match('~^\s*(\w+)\s*$~', $command, $m)) {
            $group = $m[1];
            continue;
        }
    }

    return $commands;
}

/**
 * @param  array  $sections
 *
 * @return array
 */
function parseSections(array $sections): array
{
    $list = [];
    do {
        $section    = array_shift($sections);
        $lines      = explode("\n", $section);
        $key        = strtolower(rtrim(array_shift($lines), ':'));
        $list[$key] = $lines;
    } while (!empty($sections));

    return $list;
}
