<?php

$templatesDir = __DIR__ . '/templates';
$srcDir = __DIR__ . '/src';

$moves = [
    'Animal/animal' => 'animal',
    'Animal/animal_nourriture' => 'animal_nourriture',
    'Animal/nourriture' => 'nourriture',
    'Marketplace/cart' => 'cart',
    'Marketplace/product' => 'product',
    'Parcelle/culture' => 'culture',
    'Parcelle' => 'parcelle',
    'Equipement' => 'equipement',
    'User' => 'user',
];

// Sort to process deeper directories first so we don't move Parcelle before Parcelle/culture
uksort($moves, function($a, $b) {
    return strlen($b) - strlen($a);
});

echo "Moving directories...\n";
foreach ($moves as $oldPath => $newPath) {
    $oldDir = $templatesDir . '/' . $oldPath;
    $newDir = $templatesDir . '/' . $newPath;

    if (is_dir($oldDir)) {
        if (!is_dir($newDir)) {
            mkdir($newDir, 0777, true);
        }
        $files = array_diff(scandir($oldDir), ['.', '..']);
        foreach ($files as $file) {
            $source = $oldDir . '/' . $file;
            $dest = $newDir . '/' . $file;
            if (is_dir($source)) {
                // If it's a directory that isn't handled by the specific inner moves, move it inside
                rename($source, $dest);
            } else {
                rename($source, $dest);
            }
        }
        rmdir($oldDir);
        echo "Moved $oldPath to $newPath\n";
    }
}

// Clean up now-empty parent directories
$parents = ['Animal', 'Marketplace', 'Parcelle', 'User', 'Equipement'];
foreach ($parents as $parent) {
    $dir = $templatesDir . '/' . $parent;
    if (is_dir($dir)) {
        // Only if empty
        $files = array_diff(scandir($dir), ['.', '..']);
        if (empty($files) || count($files) === 1 && in_array('.gitkeep', $files)) {
            if (file_exists($dir.'/.gitkeep')) {
                unlink($dir.'/.gitkeep');
            }
            if (count(array_diff(scandir($dir), ['.', '..'])) === 0) {
                rmdir($dir);
                echo "Removed empty dir $parent\n";
            }
        }
    }
}

// Replace references in Controllers (src/Controller) and Twig files (templates)
$replacements = [];
foreach ($moves as $old => $new) {
    $replacements[$old . '/'] = $new . '/';
}

function updateReferences($dir, $extensions, $replacements) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), $extensions)) {
            $content = file_get_contents($file->getPathname());
            $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
            if ($newContent !== $content) {
                file_put_contents($file->getPathname(), $newContent);
                echo "Updated references in: " . $file->getFilename() . "\n";
            }
        }
    }
}

echo "Updating PHP Controller references...\n";
if (is_dir($srcDir . '/Controller')) {
    updateReferences($srcDir . '/Controller', ['php'], $replacements);
}

echo "Updating Twig template references...\n";
updateReferences($templatesDir, ['twig'], $replacements);

echo "Done.\n";

