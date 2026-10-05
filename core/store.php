<?php
/**
 * Core store — demo "database" for a package (no real database).
 *
 * All tables live in pack-x/storage/demo-state.json, seeded by the package's pkg_seed().
 * Falls back to the PHP session when the folder is not writable. Re-seeds every new day.
 * Callers must authorise first (permissions.php); the store only reads/writes rows.
 */
declare(strict_types=1);

final class DemoValidationException extends InvalidArgumentException
{
}

final class PermissionDeniedException extends RuntimeException
{
}

const MK_STORE_SCHEMA = 4; // bump when the seed shape changes (forces a reseed)

function store_file(): string
{
    return MK_PKG_ROOT . '/storage/demo-state.json';
}

function store_seed(): array
{
    mk_log('Store', 'store_seed START');
    $seed = pkg_seed();
    $state = [
        'schema'    => MK_STORE_SCHEMA,
        'seedDate'  => today(),
        'version'   => 1,
        'updatedAt' => date('c'),
        'nextId'    => $seed['nextId'],
        'tables'    => $seed['tables'],
    ];
    mk_log('Store', 'store_seed END', array_map('count', $seed['tables']));
    return $state;
}

function store_is_valid(mixed $state): bool
{
    return is_array($state)
        && ($state['schema'] ?? 0) === MK_STORE_SCHEMA
        && ($state['seedDate'] ?? '') === today()
        && isset($state['tables'], $state['version']);
}

function store_uses_file(): bool
{
    $file = store_file();
    return is_file($file) ? is_writable($file) : is_writable(dirname($file));
}

/** Current state (cached per request; refreshed after every mutation). */
function store_state(?array $replace = null): array
{
    static $state = null;
    if ($replace !== null) {
        return $state = $replace;
    }
    if ($state !== null) {
        return $state;
    }

    if (store_uses_file()) {
        $raw = is_file(store_file()) ? file_get_contents(store_file()) : false;
        $loaded = $raw ? json_decode($raw, true) : null;
    } else {
        $loaded = $_SESSION['demo_state'] ?? null;
    }

    if (!store_is_valid($loaded)) {
        mk_log('Store', 'state missing or stale, reseeding');
        return store_mutate(fn (array &$s) => null)['state'];
    }
    return $state = $loaded;
}

/** Raw (UNSCOPED) rows of a table — use scoped() from data-filter.php for anything shown to users. */
function store_rows(string $table): array
{
    return store_state()['tables'][$table] ?? [];
}

/** Atomically load → modify → save. Returns ['state' => ..., 'result' => mutator result]. */
function store_mutate(callable $mutator): array
{
    mk_log('Store', 'store_mutate START');

    if (!store_uses_file()) {
        $state = $_SESSION['demo_state'] ?? null;
        if (!store_is_valid($state)) {
            $state = store_seed();
        }
        $result = $mutator($state);
        $state['version']++;
        $state['updatedAt'] = date('c');
        $_SESSION['demo_state'] = $state;
        store_state($state);
        return ['state' => $state, 'result' => $result];
    }

    $handle = fopen(store_file(), 'c+');
    if ($handle === false) {
        throw new RuntimeException('Cannot open demo storage file');
    }
    try {
        flock($handle, LOCK_EX);
        $raw = stream_get_contents($handle);
        $state = $raw ? json_decode($raw, true) : null;
        if (!store_is_valid($state)) {
            $state = store_seed();
        }
        $result = $mutator($state);
        $state['version']++;
        $state['updatedAt'] = date('c');
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($state, JSON_UNESCAPED_UNICODE));
        fflush($handle);
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    store_state($state);
    mk_log('Store', 'store_mutate END', ['version' => $state['version']]);
    return ['state' => $state, 'result' => $result];
}

function store_find(array $state, string $table, int $id): ?int
{
    foreach ($state['tables'][$table] ?? [] as $index => $row) {
        if (($row['id'] ?? null) === $id) {
            return $index;
        }
    }
    return null;
}

function store_insert(string $table, array $row): int
{
    mk_log('Store', 'store_insert', ['table' => $table, 'row' => $row]);
    return store_mutate(function (array &$state) use ($table, $row) {
        $row = ['id' => $state['nextId']++] + $row;
        $state['tables'][$table][] = $row;
        return $row['id'];
    })['result'];
}

/** Update a row; $guard(oldRow) must return true (scope check against the stored row). */
function store_update(string $table, int $id, array $changes, callable $guard): void
{
    mk_log('Store', 'store_update', ['table' => $table, 'id' => $id, 'changes' => $changes]);
    store_mutate(function (array &$state) use ($table, $id, $changes, $guard) {
        $index = store_find($state, $table, $id);
        if ($index === null) {
            throw new DemoValidationException('ไม่พบข้อมูลนี้ อาจถูกลบไปแล้ว');
        }
        $guard($state['tables'][$table][$index]);
        $state['tables'][$table][$index] = array_merge($state['tables'][$table][$index], $changes);
    });
}

function store_delete(string $table, int $id, callable $guard): void
{
    mk_log('Store', 'store_delete', ['table' => $table, 'id' => $id]);
    store_mutate(function (array &$state) use ($table, $id, $guard) {
        $index = store_find($state, $table, $id);
        if ($index === null) {
            throw new DemoValidationException('ไม่พบข้อมูลนี้ อาจถูกลบไปแล้ว');
        }
        $guard($state['tables'][$table][$index]);
        array_splice($state['tables'][$table], $index, 1);
    });
}

/** Restore seed rows for which $inScope(table, row) is true (reset only the user's own scope). */
function store_reset_scope(callable $inScope): void
{
    mk_log('Store', 'store_reset_scope START');
    store_mutate(function (array &$state) use ($inScope) {
        $seed = store_seed();
        foreach ($seed['tables'] as $table => $rows) {
            $keep = array_filter($state['tables'][$table] ?? [], fn ($row) => !$inScope($table, $row));
            $fresh = array_filter($rows, fn ($row) => $inScope($table, $row));
            $state['tables'][$table] = array_values(array_merge($fresh, $keep));
        }
        $state['nextId'] = max($state['nextId'], $seed['nextId']);
    });
}
