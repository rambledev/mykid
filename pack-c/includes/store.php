<?php
/**
 * Package C — runtime demo data (no database).
 *
 * Data that teachers change (classroom status, activities, food menu) is kept in a JSON file
 * (storage/demo-state.json), seeded from mock-data.php, so a parent on another device sees
 * the teacher's updates. Falls back to the PHP session when the folder is not writable.
 * The state re-seeds automatically each new day, or via "reset demo".
 *
 * Every table is a flat list of rows bound to a classroom, like database tables:
 *   statuses   [ {id, classroom_id, code, note, at, by} ]  newest first
 *   activities [ {id, classroom_id, time, icon, title, detail} ]
 *   foodMenus  [ {id, classroom_id, breakfast[], lunch[], snack[], milk[]} ]
 *
 * Mutations receive a classroom id that the caller has already authorised (see auth.php)
 * and additionally refuse to touch rows that belong to another classroom.
 */
declare(strict_types=1);

final class DemoValidationException extends InvalidArgumentException
{
}

final class PermissionDeniedException extends RuntimeException
{
}

const STORE_SCHEMA = 2;
const STORE_HISTORY_LIMIT = 15;

function store_seed(): array
{
    mk_log('Store', 'store_seed START');
    $data = mock_data();
    $today = date('Y-m-d');

    $statuses = [];
    foreach ($data['initialStatuses'] as $i => $s) {
        $statuses[] = [
            'id'           => $i + 1,
            'classroom_id' => $s['classroom_id'],
            'code'         => $s['code'],
            'note'         => $s['note'],
            'at'           => "$today {$s['time']}:00",
            'by'           => $s['by'],
        ];
    }

    $state = [
        'schema'     => STORE_SCHEMA,
        'seedDate'   => $today,
        'version'    => 1,
        'updatedAt'  => date('c'),
        'nextId'     => 1000,
        'statuses'   => array_reverse($statuses),
        'activities' => $data['activities'],
        'foodMenus'  => $data['foodMenus'],
    ];
    mk_log('Store', 'store_seed END', ['statuses' => count($statuses), 'activities' => count($data['activities'])]);
    return $state;
}

function store_is_valid(mixed $state): bool
{
    return is_array($state)
        && ($state['schema'] ?? 0) === STORE_SCHEMA
        && ($state['seedDate'] ?? '') === date('Y-m-d')
        && isset($state['statuses'], $state['activities'], $state['foodMenus'], $state['version']);
}

function store_uses_file(): bool
{
    return is_file(PACKC_STORAGE_FILE) ? is_writable(PACKC_STORAGE_FILE) : is_writable(dirname(PACKC_STORAGE_FILE));
}

function store_load(): array
{
    if (store_uses_file()) {
        $raw = is_file(PACKC_STORAGE_FILE) ? file_get_contents(PACKC_STORAGE_FILE) : false;
        $state = $raw ? json_decode($raw, true) : null;
    } else {
        $state = $_SESSION['demo_state'] ?? null;
    }

    if (!store_is_valid($state)) {
        mk_log('Store', 'store_load state missing or stale, reseeding');
        $state = store_mutate(fn (array &$s) => null)['state'];
    }
    return $state;
}

/**
 * Atomically load → modify → save the state. The mutator receives the state by reference.
 * Returns ['state' => new state, 'result' => mutator return value].
 */
function store_mutate(callable $mutator): array
{
    mk_log('Store', 'store_mutate START', ['file' => store_uses_file()]);

    if (!store_uses_file()) {
        $state = $_SESSION['demo_state'] ?? null;
        if (!store_is_valid($state)) {
            $state = store_seed();
        }
        $result = $mutator($state);
        $state['version']++;
        $state['updatedAt'] = date('c');
        $_SESSION['demo_state'] = $state;
        return ['state' => $state, 'result' => $result];
    }

    $handle = fopen(PACKC_STORAGE_FILE, 'c+');
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
        fwrite($handle, json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        fflush($handle);
    } finally {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    mk_log('Store', 'store_mutate END', ['version' => $state['version']]);
    return ['state' => $state, 'result' => $result];
}

/** Reset one classroom's rows back to the seed (a teacher may only reset their own room). */
function store_reset_classroom(int $classroomId): array
{
    mk_log('Store', 'store_reset_classroom START', compact('classroomId'));
    return store_mutate(function (array &$state) use ($classroomId) {
        $seed = store_seed();
        foreach (['statuses', 'activities', 'foodMenus'] as $table) {
            $others = array_filter($state[$table], fn ($row) => $row['classroom_id'] !== $classroomId);
            $state[$table] = array_values(array_merge(where_classroom($seed[$table], $classroomId), $others));
        }
    });
}

/* ----------------------------------------------------------------------------
 * Classroom-level readers (UNSCOPED — call through data-filter.php)
 * ------------------------------------------------------------------------- */

function classroom_status_history(array $state, int $classroomId): array
{
    $rows = array_filter(
        where_classroom($state['statuses'], $classroomId),
        fn ($s) => is_today($s['at'])
    );
    return array_map('status_entry_view', array_values($rows));
}

function classroom_current_status(array $state, int $classroomId): ?array
{
    $row = where_classroom($state['statuses'], $classroomId)[0] ?? null;
    return $row ? status_entry_view($row) : null;
}

function status_entry_view(array $entry): array
{
    $def = get_status_def($entry['code']) ?? ['label' => $entry['code'], 'emoji' => '⭐', 'color' => '#F5F5F5'];
    $teacher = get_teacher((int) ($entry['by'] ?? 0));
    return $entry + [
        'label'  => $def['label'],
        'emoji'  => $def['emoji'],
        'color'  => $def['color'],
        'text'   => $def['label'] . $entry['note'], // Thai joins without a space: ทำกิจกรรม + ศิลปะ
        'byName' => $teacher['nickname'] ?? 'คุณครู',
    ];
}

function classroom_activities(array $state, int $classroomId): array
{
    $rows = where_classroom($state['activities'], $classroomId);
    usort($rows, fn ($a, $b) => strcmp($a['time'], $b['time']));
    return $rows;
}

/** Meal types merged with the classroom's menu items. */
function classroom_food(array $state, int $classroomId): array
{
    $menu = where_classroom($state['foodMenus'], $classroomId)[0] ?? [];
    return array_map(function ($meal) use ($menu) {
        $meal['items'] = $menu[$meal['meal']] ?? [];
        return $meal;
    }, get_meal_types());
}

/* ----------------------------------------------------------------------------
 * Mutations — $classroomId must already be authorised by the caller.
 * ------------------------------------------------------------------------- */

/** Row-level guard: the row must exist and belong to the authorised classroom. */
function find_owned_row_index(array $rows, int $id, int $classroomId): int
{
    foreach ($rows as $index => $row) {
        if ($row['id'] !== $id) {
            continue;
        }
        if ($row['classroom_id'] !== $classroomId) {
            mk_log('Store', 'PERMISSION DENIED row belongs to another classroom', compact('id', 'classroomId') + ['owner' => $row['classroom_id']]);
            throw new PermissionDeniedException('ไม่มีสิทธิ์แก้ไขข้อมูลของห้องอื่น');
        }
        return $index;
    }
    throw new DemoValidationException('ไม่พบกิจกรรมนี้ อาจถูกลบไปแล้ว');
}

function store_set_status(int $classroomId, string $code, string $note, int $teacherId): array
{
    mk_log('Store', 'store_set_status START', compact('classroomId', 'code', 'note', 'teacherId'));
    if (!get_status_def($code)) {
        throw new DemoValidationException('กรุณาเลือกสถานะจากรายการ');
    }
    $note = mb_substr(trim($note), 0, 40);

    return store_mutate(function (array &$state) use ($classroomId, $code, $note, $teacherId) {
        array_unshift($state['statuses'], [
            'id'           => $state['nextId']++,
            'classroom_id' => $classroomId,
            'code'         => $code,
            'note'         => $note,
            'at'           => date('Y-m-d H:i:s'),
            'by'           => $teacherId,
        ]);

        // Keep the newest N rows per classroom.
        $seen = [];
        $state['statuses'] = array_values(array_filter($state['statuses'], function ($row) use (&$seen) {
            $seen[$row['classroom_id']] = ($seen[$row['classroom_id']] ?? 0) + 1;
            return $seen[$row['classroom_id']] <= STORE_HISTORY_LIMIT;
        }));
    });
}

function validate_activity_input(array $input): array
{
    $time = trim((string) ($input['time'] ?? ''));
    $title = trim((string) ($input['title'] ?? ''));
    $detail = trim((string) ($input['detail'] ?? ''));
    $icon = (string) ($input['icon'] ?? '⭐');

    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) {
        throw new DemoValidationException('กรุณาระบุเวลาให้ถูกต้อง');
    }
    if ($title === '') {
        throw new DemoValidationException('กรุณากรอกชื่อกิจกรรม');
    }
    if (!in_array($icon, get_activity_icons(), true)) {
        $icon = '⭐';
    }

    return [
        'time'   => $time,
        'title'  => mb_substr($title, 0, 80),
        'detail' => mb_substr($detail, 0, 200),
        'icon'   => $icon,
    ];
}

function store_add_activity(int $classroomId, array $input): array
{
    mk_log('Store', 'store_add_activity START', compact('classroomId', 'input'));
    $clean = validate_activity_input($input);

    return store_mutate(function (array &$state) use ($classroomId, $clean) {
        $id = $state['nextId']++;
        $state['activities'][] = ['id' => $id, 'classroom_id' => $classroomId] + $clean;
        return $id;
    });
}

function store_update_activity(int $classroomId, int $id, array $input): array
{
    mk_log('Store', 'store_update_activity START', compact('classroomId', 'id', 'input'));
    $clean = validate_activity_input($input);

    return store_mutate(function (array &$state) use ($classroomId, $id, $clean) {
        $index = find_owned_row_index($state['activities'], $id, $classroomId);
        $state['activities'][$index] = ['id' => $id, 'classroom_id' => $classroomId] + $clean;
        return $id;
    });
}

function store_delete_activity(int $classroomId, int $id): array
{
    mk_log('Store', 'store_delete_activity START', compact('classroomId', 'id'));

    return store_mutate(function (array &$state) use ($classroomId, $id) {
        $index = find_owned_row_index($state['activities'], $id, $classroomId);
        array_splice($state['activities'], $index, 1);
    });
}

function store_save_meal(int $classroomId, string $meal, array $items): array
{
    mk_log('Store', 'store_save_meal START', compact('classroomId', 'meal', 'items'));
    if (!in_array($meal, array_column(get_meal_types(), 'meal'), true)) {
        throw new DemoValidationException('ไม่พบมื้ออาหารนี้');
    }

    $clean = array_values(array_filter(
        array_map(fn ($i) => mb_substr(trim((string) $i), 0, 60), $items),
        fn ($i) => $i !== ''
    ));
    if (!$clean) {
        throw new DemoValidationException('กรุณากรอกรายการอาหารอย่างน้อย 1 รายการ');
    }

    return store_mutate(function (array &$state) use ($classroomId, $meal, $clean) {
        foreach ($state['foodMenus'] as &$menu) {
            if ($menu['classroom_id'] === $classroomId) {
                $menu[$meal] = array_slice($clean, 0, 6);
                return;
            }
        }
        unset($menu);
        $state['foodMenus'][] = ['id' => $state['nextId']++, 'classroom_id' => $classroomId, $meal => array_slice($clean, 0, 6)];
    });
}
