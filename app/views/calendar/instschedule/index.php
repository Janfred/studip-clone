<h1>
    <?= htmlReady(Context::getHeaderLine()) ?>  <?= _("im") ?>
    <?= htmlReady($current_semester['name']) ?>
</h1>
<?= $calendar_view->render() ?>
