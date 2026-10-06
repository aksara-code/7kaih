<?php
function canonicalClassName(string $className): ?string
{
    $cleanName = trim((string) $className);

    if ($cleanName === '' || preg_match('/^(?:<<<<<<<\s+HEAD|=======|>>>>>>>.*)$/i', $cleanName)) {
        return null;
    }

    $cleanName = preg_replace('/^(?:<<<<<<<\s+HEAD\s*|=======\s*|>>>>>>>.*?\s*)/i', '', $cleanName);
    $cleanName = trim((string) $cleanName);

    if ($cleanName === '') {
        return null;
    }

    if (!preg_match('/^(XII|XI|X)\s*[- ]?\s*([1-8])$/i', $cleanName, $matches)) {
        return null;
    }

    return strtoupper($matches[1]) . '-' . $matches[2];
}

function ensureStandardClasses(PDO $pdo): array
{
    $classMap = [];
    $classQuery = $pdo->query('SELECT id, nama_kelas FROM kelas ORDER BY id ASC');

    foreach ($classQuery->fetchAll() as $class) {
        $canonicalName = canonicalClassName($class['nama_kelas']);
        if ($canonicalName !== null && !isset($classMap[$canonicalName])) {
            $classMap[$canonicalName] = (int) $class['id'];
        }
    }

    return $classMap;
}