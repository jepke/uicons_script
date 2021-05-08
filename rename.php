<?php

include('config.php');
/* Current mega icons */
$megaPokemon = [3, 6, 9, 15, 18, 65, 94, 115, 127, 130, 142, 150, 181, 229, 302, 303, 310, 334, 354, 384, 428, 460];

/* Start processing pokemon icons */
$iconsInFolder = dirtree($oldIconPath);
foreach ($iconsInFolder as $k => $icon) {
    if (!is_array($icon)) {
        $oldIcon = $oldIconPath . $icon;
        if (str_starts_with($icon, 'pokemon_icon_')) {
            $iconInfo = explode('_', (basename(str_replace('pokemon_icon_', '', $icon), '.png')));
            $pokemonId = ($iconInfo[0] === '000') ? '0' : ltrim($iconInfo[0], '0');
            $formId = ($iconInfo[1] === '00') ? '' : '_f' . ltrim($iconInfo[1], '0');
            $evolutionId = '';
            $genderId = '';
            if (in_array($pokemonId, $megaPokemon)) {
                $evolutionId = array_key_exists(2, $iconInfo) ? '_e' . ltrim($iconInfo[2], '0') : '';
                $costumeId = array_key_exists(3, $iconInfo) ? '_c' . ltrim($iconInfo[3], '0') : '';
            } else {
                $costumeId = array_key_exists(2, $iconInfo) ? '_c' . ltrim($iconInfo[2], '0') : '';
            }
            if (!is_dir($newIconPath . 'pokemon')) {
                mkdir($newIconPath . 'pokemon', 0755);
                echo 'New pokemon folder created.' . PHP_EOL;
            }
            $newIcon = $newIconPath . 'pokemon/' . $pokemonId . $evolutionId . $formId . $costumeId . $genderId . '.png';
            print_r('Old icon name: ' . $icon . "     \t New icon name: " . $newIcon . PHP_EOL);
            copy($oldIcon, $newIcon);
        }
    /* Start processing reward icons*/
    } else if ($k == 'rewards') {
        $rewardsInFolder = dirtree($oldIconPath . 'rewards');
        foreach ($rewardsInFolder as $k => $reward) {
            break;
            $oldRewardIcon = $oldIconPath . 'rewards/' . $reward;
            if (str_starts_with($reward, 'reward_') && ! str_starts_with($reward, 'reward_mega') && ! str_starts_with($reward, 'reward_stardust')) {
                $rewardInfo = explode('_', (basename(str_replace('reward_', '', $reward), '.png')));
                $itemId = $rewardInfo[0];
                $itemAmount = array_key_exists(1, $rewardInfo) ? (($rewardInfo[1] > 0) ? '_a' . $rewardInfo[1] : '') : '';

                if (!is_dir($newIconPath . 'reward')) {
                    mkdir($newIconPath . 'reward', 0755);
                    echo 'New reward folder created.' . PHP_EOL;
                }
                if (!is_dir($newIconPath . 'reward/item')) {
                    mkdir($newIconPath . 'reward/item', 0755);
                    echo 'New item folder created.' . PHP_EOL;
                }
                $newRewardIcon = $newIconPath . 'reward/item/' . $itemId . $itemAmount . '.png';
                print_r('Old icon name: ' . $reward . "     \t New icon name: " . $newRewardIcon . PHP_EOL);
                copy($oldRewardIcon, $newRewardIcon);
            }
            if (str_starts_with($reward, 'reward_mega_energy_')) {
                $megaInfo = explode('_', (basename(str_replace('reward_mega_energy_', '', $reward), '.png')));
                $megaId = is_array($megaInfo) ? $megaInfo[0] : '0';

                if (!is_dir($newIconPath . 'reward')) {
                    mkdir($newIconPath . 'reward', 0755);
                    echo 'New reward folder created.' . PHP_EOL;
                }
                if (!is_dir($newIconPath . 'reward/mega_resource')) {
                    mkdir($newIconPath . 'reward/mega_resource', 0755);
                    echo 'New mega_resource folder created.' . PHP_EOL;
                }
                $newMegaIcon = $newIconPath . 'reward/mega_resource/' . $megaId . '.png';
                print_r('Old icon name: ' . $reward . "     \t New icon name: " . $newMegaIcon . PHP_EOL);
                copy($oldRewardIcon, $newMegaIcon);
            }
            if (str_starts_with($reward, 'reward_stardust_')) {
                $dustInfo = explode('_', (basename(str_replace('reward_stardust_', '', $reward), '.png')));
                $dustAmount = is_array($dustInfo) ? $dustInfo[0] : '0';

                if (!is_dir($newIconPath . 'reward')) {
                    mkdir($newIconPath . 'reward', 0755);
                    echo 'New reward folder created.' . PHP_EOL;
                }
                if (!is_dir($newIconPath . 'reward/stardust')) {
                    mkdir($newIconPath . 'reward/stardust', 0755);
                    echo 'New stardust folder created.' . PHP_EOL;
                }
                $newDustIcon = $newIconPath . 'reward/stardust/' . $dustAmount . '.png';
                print_r('Old icon name: ' . $reward . "     \t New icon name: " . $newDustIcon . PHP_EOL);
                copy($oldRewardIcon, $newDustIcon);
            }
        }
    }
}
/* Create master json file */
file_put_contents($newIconPath . 'index.json', json_encode(dirtree($newIconPath)));
/* Create subfolder json files */
foreach (dirtree($newIconPath) as $k => $dir) {
    if (is_dir($newIconPath . $k)) {
        $jsonFile = $newIconPath . $k . DIRECTORY_SEPARATOR . 'index.json';
        $directory = dirtree($newIconPath . $k);
        file_put_contents($jsonFile, json_encode($directory));
    }
    if ($k === 'reward') {
        foreach ($dir as $ks => $subdir) {
            if (is_dir($newIconPath . $k . DIRECTORY_SEPARATOR . $ks)) {
                $jsonFile = $newIconPath . $k . DIRECTORY_SEPARATOR . $ks . DIRECTORY_SEPARATOR . 'index.json';
                $directory = dirtree($newIconPath . $k . DIRECTORY_SEPARATOR . $ks);
                file_put_contents($jsonFile, json_encode($directory));
            }
        }
    }
}

function dirtree($dir, $ignoreEmpty=false) {
    if (!$dir instanceof DirectoryIterator) {
        $dir = new DirectoryIterator((string)$dir);
    }
    $dirs  = array();
    $files = array();
    foreach ($dir as $node) {
        if ($node->isDir() && !$node->isDot()) {
            $tree = dirtree($node->getPathname(), $ignoreEmpty);
            if (!$ignoreEmpty || count($tree)) {
                $dirs[$node->getFilename()] = $tree;
            }
        } elseif ($node->isFile()) {
            $name = $node->getFilename();
            if (!str_ends_with($name, '.json')) {
                $files[] = $name;
            }
        }
    }
    asort($dirs);
    sort($files);

    return array_merge($dirs, $files);
}
