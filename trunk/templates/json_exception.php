<? if (Studip\ENV === 'development'): ?>
<?= json_encode(studip_utf8encode([
    'status'  => (int) $status,
    'message' => $exception->getMessage(),
    'file'    => $exception->getFile(),
    'line'    => $exception->getLine(),
    'trace'   => $exception->getTraceAsString(),
])) ?>
<? else: ?>
<?= json_encode(studip_utf8encode([
    'status'  => (int) $status,
    'message' => $exception->getMessage(),
    ])) ?>
<? endif; ?>