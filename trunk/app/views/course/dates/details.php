<form name="edit_termin" action="<?= $controller->url_for("course/dates/save_details",array()) ?>" method="POST" >
    <table style="width: 100%" class="default nohover" data-termin_id="<?= htmlReady($date->getId()) ?>">
        <tbody>
            <tr>
                <td><strong><?= _("Thema") ?></strong></td>
                <td>
                    <ul class="themen_list">
                        <? foreach ($date->topics as $topic) : ?>
                            <?= $this->render_partial("course/dates/_topic_li", compact("topic")) ?>
                        <? endforeach ?>
                    </ul>
                    <? if ($GLOBALS['perm']->have_studip_perm("tutor", $date['range_id'])) : ?>
                    <div>
                        <form onSubmit="STUDIP.Dates.addTopic(); return false;">
                            <input type="text" name="new_topic" id="new_topic" placeholder="<?= _("Thema hinzufügen") ?>">
                            <a href="#" onClick="STUDIP.Dates.addTopic(); return false;"><?= Icon::create('add', 'clickable')->asImg(['class' => "text-bottom"]) ?></a>
                        </form>
                        <script>
                            jQuery(function () {
                                jQuery("#new_topic").autocomplete({
                                    'source': <?= json_encode(studip_utf8encode(Course::findCurrent()->topics->pluck('title'))) ?>
                                });
                            });
                        </script>
                    </div>
                    <? endif ?>
                </td>
            </tr>
            <tr>
                <td><strong><?= _("Art des Termins") ?></strong></td>                
                <td>                
                	<select name="dateType">
                    <? foreach ($GLOBALS['TERMIN_TYP'] as $key => $val) : ?>
                            <option value="<?= $key ?>" <?= $date['date_typ'] == $key ? ' selected' : '' ?> > <?= $val['name'] ?> </option>
                    <? endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><strong><?= _("Durchführende Dozenten") ?></strong></td>
                <td>
                    <? $dozenten = $date->dozenten ?>
                    <? $course_dozenten = array_map(function ($m) { return $m->user; }, (Course::findCurrent()->getMembersWithStatus("dozent"))) ?>
                    <?	 
                        $related_doz = array();
                        foreach ($dozenten as $dozent) {
                        	if (in_array($dozent, $course_dozenten) !== false) {
                        		$dozent_id = $dozent['user_id'];
                        		$related_doz[$dozent_id] = true;
                        	}
                        } 
                    ?>
                    
                    <ul class="termin_related teachers clean">
                    <? foreach ($course_dozenten as $dozent) : ?>
                        <? $dozent_id = $dozent['user_id']; ?>
                        <li data-lecturerid="<?= $dozent['user_id'] ?>" <?= $related_doz[$dozent_id] ? '' : 'style="display: none"'?>>
                            <a href="<?= $controller->link_for("profile", array('username' => $dozent['username'])) ?>"><?= Avatar::getAvatar($dozent['user_id'])->getImageTag(Avatar::SMALL)." ".htmlReady($dozent->getFullName()) ?></a>
                       
                      		<a href="javascript:" onClick="STUDIP.Raumzeit.removeLecturer('<?= $dozent['user_id'] ?>')">
                               <?= Icon::create('trash', 'clickable') ?>
                            </a>
                        </li>
                    <? endforeach ?>
                    </ul>
                    
                    <input type="hidden" name="related_teachers" value="<?= implode(",",array_keys($related_doz)) ?>">
                    <select name="teachers" style="width: 300px">
                        <option value="none"><?= _('-- Dozent/in auswählen --') ?></option>
                    <? foreach ($course_dozenten as $dozent) : ?>
                        <? $dozent_id = $dozent['user_id']; ?>
                        <option value="<?= $dozent['user_id'] ?>" <?= $related_doz[$dozent_id] ? 'style="display: none"' : '' ?>>
                            <?= htmlReady($dozent->getFullName()) ?>
                        </option>
                    <? endforeach ?>
                    </select>
                    
                    <a href="javascript:" onclick="STUDIP.Raumzeit.addLecturer()" title="<?= _("Dozent/in hinzufügen") ?>">
                        <?= Icon::create('arr_2up', 'sort')  ?>
                    </a>
                    
                </td>
            </tr>
            <tr>
                <td><strong><?= _("Beteiligte Gruppen") ?></strong></td>
                <td>
                    <? $groups = $date->statusgruppen ?>                    
                    <? $course_groups = Statusgruppen::findBySeminar_id(Course::findCurrent()->getId()) ?>
					<?	 
    	                $related_groups = array();
    	                foreach ($groups as $group) {
                    	   if (in_array($group, $course_groups) !== false) {
    	                		$group_id = $group['id'];
    	                		$related_groups[$group_id] = true;
    	                   }
    	                } 
                    ?>
                    
                    <ul class="termin_related groups clean" style="width: 319px">
                    <? foreach ($course_groups as $group) : ?>
                    	<? $group_id = $group['id']; ?>                    
                        <li data-groupid="<?= htmlReady($group['id']) ?>" <?= $related_groups[$group_id] ? '' : 'style="display: none"'?>>
    	                    <?= htmlReady($group['name']) ?>
    	                    <a style="float: right;" href="javascript:" onClick="STUDIP.Raumzeit.removeGroup('<?= htmlReady($group['id']) ?>'),refreshUl()">
                               <?= Icon::create('trash', 'clickable')  ?>
                            </a>
                        </li>
                    <? endforeach ?>
                        <li id="all_groups" data-groupid="" <?= count($groups) ? 'style="display: none"' : ''?>>
    	                    <?= _("alle Teilnehmer") ?>
                        </li>
                    </ul>
                    <input id="related_statusgruppen" type="hidden" name="related_statusgruppen" value="<?= implode(",",array_keys($related_groups)) ?>">
                    
      				<select name="groups" style="width: 300px">
                        <option value="none"><?= _('-- Gruppen auswählen --') ?></option>
                    <? foreach ($course_groups as $group) : ?>
                        <? $group_id = $group['id']; ?>
                        <option value="<?= htmlReady($group['id']) ?>" <?= $related_groups[$group_id] ? 'style="display: none"' : '' ?>>
                            <?= htmlReady($group['name']) ?>
                        </option>
                    <? endforeach ?>
                    </select> 
                    <a href="javascript:" onclick="STUDIP.Raumzeit.addGroup(),refreshUl()" title="<?= _('Gruppe hinzufügen') ?>">
                        <?= Icon::create('arr_2up', 'sort')  ?>
                    </a>
                    <script>
                        function refreshUl() {
                            if ($("#related_statusgruppen").val() == "") {
                            	$("#all_groups").show();
                            } else {
                            	$("#all_groups").hide();
                            }
                        };
                    </script>
                                        
                </td>
            </tr>
        </tbody>
    </table>
    
    <input type="hidden" name="singleDateID" value="<?= htmlReady($date->getId()) ?>">
    <? if (!empty($date->metadate_id)) : ?>
    <input type="hidden" name="cycle_id" value="<?= $date->metadate_id ?>">
    <? endif; ?>    
    <input type="hidden" name="startDate" value="<?= htmlReady(date("d.m.Y",$date->date)) ?>">
    <input type="hidden" name="start_stunde" value="<?= htmlReady(date("H",$date->date)) ?>">
	<input type="hidden" name="start_minute" value="<?= htmlReady(date("i",$date->date)) ?>">
	<input type="hidden" name="end_stunde" value="<?= htmlReady(date("H",$date->end_time)) ?>">
	<input type="hidden" name="end_minute" value="<?= htmlReady(date("i",$date->end_time)) ?>">
    
    <div style="text-align: center;" data-dialog-button>
        <div class="button-group">
        	<?= \Studip\Button::create(_('Speichern'), "editSingleDate_button" ); ?>
            <? if (!$dates_locked && $GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) : ?>
            	<?= \Studip\LinkButton::create(_("Termin bearbeiten"), URLHelper::getURL("raumzeit.php#".$date->getId(), array('raumzeitFilter' => "all", 'cycle_id' => $date['metadate_id'], 'singleDateID' => $date->getId()))) ?>
            <? endif ?>
            <? if (!$cancelled_dates_locked && $GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar'])) : ?>
                <?= \Studip\LinkButton::create(_("Ausfallen lassen"), $controller->url_for("course/cancel_dates", array('termin_id' => $date->getId())), array('data-dialog' => '')) ?>
            <? endif ?>
        </div>
    </div>
</form>
