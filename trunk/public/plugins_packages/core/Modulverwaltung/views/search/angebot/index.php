<h1><?= $name ?></h1>
<h4><strong><?= implode(' | ',$chars)?> </strong></h4>
<? foreach ($faecher as $char => $abschluesse): ?>
<h3 style="background-color: #e7ebf1; padding: 5px;"><a name="<?= $char ?>"><?= ucfirst($char)?></a></h3>
    <ul style="list-style-type: none;">
    <? foreach ($abschluesse as $fach): ?>
        <li>
            <a href="<?= $controller->url_for($url, $fach['fach_id'], $fach['abschluss_id']) ?>"> <?= htmlReady($fach['name']) ?></a>
        </li>
    <? endforeach; ?>
    </ul>
<? endforeach; ?>
