<? if (sizeof($buddies)) : ?>

    <?
    require_once 'lib/classes/Avatar.class.php';
    $limit = $show_all ? PHP_INT_MAX : 12;
    ?>

    <style>
    #buddycontainer {
        width: 200px;
    }

    #buddycontainer ul.buddies {
        clear: both;
        list-style: none;
        margin: 0pt;
        overflow: hidden;
        padding: 0pt;
    }

    #buddycontainer ul.buddies li {
        float: left;
        padding-left: 20px;
        padding-bottom: 5px;
    }

    #buddycontainer ul.buddies li.morebuddies {
        font-size: 25px;
        font-weight: bold;
    }

    #buddycontainer .header {
        background-color: #4A5681;
        background-image: url(<?= Assets::image_path('fill1.gif') ?>);
        color: white;
        width: 200px;
        display: table;
        margin-top: 1em;
        margin-bottom: 5px;
    }

    #buddycontainer .header .text {
        float: left;
        width: 145px;
        padding: 2px 0px 2px 5px;
        font-weight: bold;
    }

    #buddycontainer .header .toggles {
        float: right;
        width: 48px;
        text-align: right;
        padding: 2px 2px 2px 0px;
        margin-top: 2px;
    }

    </style>
<?
$user_id = $GLOBALS['auth']->auth['uid'];
?>

    <div id="buddycontainer">
        <div class="header">
            <div class="text">Buddies</div>

            <? if ($GLOBALS['auth']->auth['uname'] === $username) : ?>
                <div class="toggles">
                    <? if (MayPublishBuddies($user_id)) : ?>
                        <a href="dispatch.php/buddies/toggle_publish" title="<?= _("Deine Buddies werden öffentlich anzeigen.") ?>">
                            <?= Assets::img('world.png', array('alt' => _("Deine Buddies werden öffentlich anzeigen."))) ?>
                        </a>
                    <? else : ?>
                        <a href="dispatch.php/buddies/toggle_publish" title="<?= _("Deine Buddies werden nicht öffentlich anzeigen.") ?>">
                            <?= Assets::img('world2.png', array('alt' => _("Deine Buddies werden nicht öffentlich anzeigen."))) ?>
                        </a>
                    <? endif ?>
                </div>
            <? endif ?>

        </div>

        <?
        $buddies_to_show = array_splice($buddies, 0, $limit);
        ?>
        <ul class="buddies" id="buddies-head">
            <? foreach ($buddies_to_show as $id) : ?>
                <li>
                    <a href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>about.php?username=<?= get_username($id) ?>" title="<?= get_fullname($id) ?>">
                        <?= Avatar::getAvatar($id)->getImageTag(Avatar::SMALL) ?>
                    </a>
                </li>
          <? endforeach ?>
          <? if ($num = sizeof($buddies)) : ?>
              <li class="morebuddies">
                  <a href="<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>about.php?username=<?= $username ?>&amp;show_all_buddies">
                  + <?= $num ?>
              </li>
          <? endif ?>
        </ul>
    </div>
<? endif ?>
