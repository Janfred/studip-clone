<dl>
    <dt><?= _('Name') ?></dt>
    <dd><?= htmlReady($plugin['name']) ?></dd>

    <dt><?= _('Klasse') ?></dt>
    <dd><?= htmlReady($plugin['class']) ?></dd>

    <dt><?= _('Typ') ?></dt>
    <dd><?= join(', ', $plugin['type']) ?></dd>

    <dt><?= _('Origin') ?></dt>
    <dd><?= htmlReady($plugin['origin']) ?></dd>

    <dt><?= _('Version') ?></dt>
    <dd><?= htmlReady($plugin['version']) ?></dd>

    <dt><?= _('Beschreibung') ?></dt>
    <dd><?= htmlReady($plugin['description']) ?></dd>
</dl>
