<?
# Lifter010: TODO
?>
<html>
<head>
    <title><?= htmlReady(PageLayout::getTitle())?></title>
</head>
<body>

<? if (sizeof($dates)) : ?>
    <table cellspacing="0" cellpadding="0" border="1" width="100%">

        <tr>
            <th colspan="4">
                <h2><?= htmlReady(PageLayout::getTitle())?></h2>
            </th>
        </tr>

        <?
        $semester = new SemesterData();
        $all_semester = $semester->getAllSemesterData();

        foreach ($dates as $date) :
            if ( ($grenze == 0) || ($grenze < $date['start']) ) {
                foreach ($all_semester as $zwsem) {
                    if ( ($zwsem['beginn'] < $date['start']) && ($zwsem['ende'] > $date['start']) ) {
                        $grenze = $zwsem['ende'];
                        ?>
                        <tr>
                            <td colspan="4">
                                <h3><?= htmlReady($zwsem['name']) ?></h3>
                            </td>
                        </tr>
                    <?
                    }
                }
            }
            ?>
            <tr>
                <td width="25%"><?= htmlReady($date['date'])  ?></td>
                <td width="25%"><?= htmlReady($date['title']) ?></td>
                <td width="25%">
                    <? if (count($date['related_persons']) != $lecturer_count) : ?>
                        <? foreach ($date['related_persons'] as $key => $user_id) {
                            echo ($key > 0 ? ", " : "").htmlReady(get_fullname($user_id));
                        } ?>
                    <? endif ?>
                </td>
                <td width="25%"><?= htmlReady($date['room']) ?></td>
            </tr>
            <? if ($date['description']) : ?>
            <tr>
                <td>&nbsp;</td>
                <td colspan="3"><?= formatReady($date['description'])?></td>
            </tr>
        <? endif ?>
        <? endforeach ?>

    </table>
<? endif ?>
</body>
</html>
