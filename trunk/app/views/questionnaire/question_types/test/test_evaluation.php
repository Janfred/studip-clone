<?
$data = $vote['questiondata']->getArrayCopy();
$results = array();
$results_users = array();
$users = array();
foreach ($data['options'] as $option) {
    $results[] = 0;
    $results_users[] = array();
}
if (count($data['options']) > 0) {
    foreach ($answers as $answer) {
        if ($data['multiplechoice']) {
            foreach ($answer['answerdata']['answers'] as $a) {
                $results[(int)$a - 1]++;
                $results_users[(int)$a - 1][] = $answer['user_id'];
                $users[] = $answer['user_id'];
            }
        } else {
            $results[(int)$answer['answerdata']['answers'] - 1]++;
            $results_users[(int)$answer['answerdata']['answers'] - 1][] = $answer['user_id'];
            $users[] = $answer['user_id'];
        }
    }
}
$users = array_unique($users);
?>
<h3>
    <?= Icon::create('test', 'info')->asImg(20, ['class' => 'text-bottom']) ?>
    <?= formatReady($vote['questiondata']['question']) ?>
</h3>
<? if (count($vote->answers) > 0 && count($data['options']) > 0) : ?>
    <div style="max-height: none; opacity: 1;" id="questionnaire_<?= $vote->getId() ?>_chart" class="ct-chart"></div>
    <script>
    <?= Request::isAjax() ? 'jQuery(document).one("dialog-open", function () {' : 'jQuery(function () {' ?>

        var data = {
            labels: <?= json_encode(studip_utf8encode($data['options'])) ?>,
            series: [<?= json_encode(studip_utf8encode($results)) ?>]
        };
        <? if ($vote['questiondata']['multiplechoice']) : ?>
            new Chartist.Bar('#questionnaire_<?= $vote->getId() ?>_chart', data, { onlyInteger: true, axisY: { onlyInteger: true } });
        <? else : ?>
            data.series = data.series[0];
            new Chartist.Pie('#questionnaire_<?= $vote->getId() ?>_chart', data, { labelPosition: 'outside' });
        <? endif ?>
    });
    </script>
<? endif ?>
<? if (is_array($users) && in_array($GLOBALS['user']->id, $users)) : ?>
    <div style="max-height: none; opacity: 1; font-size: 1.4em; text-align: center;">
        <? if ($vote->correctAnswered()) : ?>
            <?= Icon::create('accept', 'accept')->asImg(25, ['class' => 'text-bottom']) ?>
            <?= _("Richtig beantwortet!") ?>
        <? else : ?>
            <?= Icon::create('decline', 'attention')->asImg(25, ['class' => 'text-bottom']) ?>
            <?= _("Falsch beantwortet!") ?>
        <? endif ?>
    </div>
<? endif ?>

<table class="default nohover">
    <tbody>
    <? $countAnswers = $vote->questionnaire->countAnswers() ?>
    <? foreach ($vote['questiondata']['options'] as $key => $option) : ?>
        <tr class="<?= $data['correctanswer'] ? "correct" : "incorrect" ?>">
            <? $percentage = $countAnswers ? round((int) $results[$key] / $countAnswers * 100) : 0 ?>
            <td style="text-align: right; background-size: <?= $percentage ?>% 100%; background-position: right center; background-image: url('<?= Assets::image_path("vote_lightgrey.png") ?>'); background-repeat: no-repeat;" width="50%">
                <strong><?= formatReady($option) ?></strong>
                <? if (in_array($key + 1, $data['correctanswer'])) : ?>
                    <?= Icon::create('accept', 'accept', ['title' =>  _("Diese Antwort ist richtig")])->asImg( ['class' => 'text-bottom']) ?>
                <? else : ?>
                    <?= Icon::create('decline', 'attention', ['title' =>  _("Eine falsche Antwort")])->asImg( ['class' => 'text-bottom']) ?>
                <? endif ?>
            </td>
            <td style="white-space: nowrap;">
                (<?= $percentage ?>%
                | <?= (int) $results[$key] ?>/<?= $countAnswers ?>)
            </td>
            <td width="50%">
                <? if (!$vote->questionnaire['anonymous'] && $results[$key]) : ?>
                    <? $users = SimpleCollection::createFromArray(User::findMany($results_users[$key])); ?>
                    <? foreach ((array) $results_users[$key] as $index => $user_id) : ?>
                        <? $user = $users->findOneBy('user_id', $user_id); ?>
                        <? if ($user) : ?>
                            <a href="<?= URLHelper::getLink("dispatch.php/profile", array('username' => $user->username)) ?>">
                                <?= Avatar::getAvatar($user_id,  $user->username)->getImageTag(Avatar::SMALL, array('title' => htmlReady( $user->getFullname('no_title')))) ?>
                                <? if (count($results_users[$key]) < 4) : ?>
                                    <?= htmlReady($user->getFullname('no_title')) ?>
                                <? endif ?>
                            </a>
                        <? endif ?>
                    <? endforeach ?>
                <? endif ?>
            </td>
        </tr>
    <? endforeach ?>
    </tbody>
</table>

