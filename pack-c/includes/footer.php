<?php
/**
 * Package C — page footer: bottom navigation, shared dialogs, toast area and scripts.
 */
declare(strict_types=1);

$isApp = $page['layout'] === 'app' && $user;
$clientConfig = [
    'debug'   => MYKID_DEBUG,
    'csrf'    => csrf_token(),
    'apiUrl'  => url('api.php'),
    'role'    => $user['role'] ?? null,
    'page'    => $page['id'],
    'version' => $isApp ? ($ctx['version'] ?? null) : null,
    'poll'    => $isApp && $user['role'] === 'parent',
    'toast'   => flash('toast'),
];
?>
</main>

<?php if ($isApp): ?>
    <?php render_bottom_nav($user['role'], $page['nav']); ?>
    <?php render_confirm_sheet(); ?>
<?php endif; ?>

<div class="toast-stack" aria-live="polite" aria-atomic="true" data-toast-stack></div>

<script>window.MYKID = <?= json_encode($clientConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>;</script>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
