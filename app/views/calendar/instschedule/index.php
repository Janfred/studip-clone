<h1>
    <?= htmlReady($GLOBALS['SessSemName']['header_line']) ?>  <?= _("im") ?>
    <?= htmlReady($current_semester['name']) ?>
</h1>
<?= $calendar_view->render() ?>
