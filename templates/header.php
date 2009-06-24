<!-- Dynamische Links mit Icons -->
<div id='header'>
	<!--<div id='barTopLogo'>
		<img src="<?=$GLOBALS['ASSETS_URL']?>images/logoneu.jpg" alt="Logo Uni Göttingen">
	</div>
	 -->
	<div id="barTopFont">
	<?=$GLOBALS['UNI_NAME']?>
	</div>
	<div id="barTopMenu">
		<ul>
		<?
		$accesskey = 0;
		foreach (array($home,$courses,$messages,$chat,$online,$homepage,$planner,$admin) as $item)
		{
			 if(!is_null($item)){
				 if($item['accesskey']){
					 $accesskey = ++$accesskey % 10;
				 }
				 ?>
				<li>
				<div style="font-size:12px; z-index:2; line-height:90%; padding-bottom:3px">
				<a href="<?=$item['link']?>" <?=($item['accesskey'] ? 'accesskey="' . $accesskey . '"' : '')?>>
				<img <?=tooltip($item['info'] . ($item['accesskey'] ? "  [ALT] + $accesskey" : "") )?> src="<?=$GLOBALS['ASSETS_URL'] . 'images/header_' . $item['image'] . '.gif'?>" border="0">
				<br>
				<?=htmlReady($item['text'])?>
				</a></div>
				</li>
				<?
			}
		}
		if(is_array($plugins))
		{
			foreach ($plugins as $plugin_item)
			{
				?>
				<li>
				<div style="font-size:12px; z-index:2; line-height:90%; padding-bottom:3px">
				<a href="<?=$plugin_item['link']?>">
				<img <?=tooltip($plugin_item['info'])?> src="<?=$plugin_item['image']?>" border="0">
				<br>
				<?=htmlReady($plugin_item['text'])?>
				</a></div>
				</li>
				<?
			}
		}
		?>
		</ul>
	</div>
</div>
<!--Statische Text Links -->
<div id="barTopTools">
	<ul>
		<li>
			<a href="http://blog.studip.de" target="_blank">
			<?=_("Stud.IP Blog")?>
			</a>
		</li>
	</ul>
</div>
<!-- Stud.IP Logo -->
<div id="barTopStudip">
	<a href="http://www.studip.de/" title="Studip Homepage">
		<img src="<?=$GLOBALS['ASSETS_URL']?>images/studipmirror.jpg" alt="Stud.IP Homepage">
	</a>
</div>
<div style="position: relative; margin-top: -34px; margin-right: 42px; float: right; z-index: 99;" align="right">
  <img src="<?=$GLOBALS['ASSETS_URL']?>images/studipdot.gif" alt="Stud.IP Homepage">
</div>
<!-- Leiste unten -->
<div id="barBottomLeft">
	<?=($current_page != "" ? _("Aktuelle Seite:") : "")?>
</div>
<div id="barBottommiddle">&nbsp;
	<?=($current_page != "" ? htmlReady($current_page) : "")?>
	&nbsp;
</div>
<!-- Dynamische Links ohne Icons -->
<div id="barBottomright">
	<ul>
		<? if ($quicksearch) : ?>
		<li>
		<form id="quicksearch" action="<?= URLHelper::getLink('sem_portal.php', array('send' => 'yes', 'group_by' => '0')) ?>" method="post">
		  <input type="hidden" name="search_sem_qs_choose" value="all">
		  <input type="hidden" name="search_sem_sem" value="all">
		  <input type="hidden" name="search_sem_1508068a50572e5faff81c27f7b3a72f" value="1">
		  <input class="quicksearchbox" type="text" name="search_sem_quick_search" value="Veranstaltungen" title="<?= _('Suche nach Veranstaltungen, Dozenten oder Nummern starten') ?>">
		  <input class="quicksearchbutton" type="submit" name="search_sem" value="OK" title="<?= _('Suche nach Veranstaltungen, Dozenten oder Nummern starten') ?>">
		</form>
		</li>
		<? endif ?>
		<? foreach (array($search, $imprint, $help, $caslogin, $shiblogin, $loginlogout) as $item) :
			 if(isset($item)){
 				 if($item['accesskey']){
					 $accesskey = ++$accesskey % 10;
				 }
				 ?>
				 <li>
				 <a <?=tooltip($item['info'] . ($item['accesskey'] ? "  [ALT] + $accesskey" : ""), false)?>
				 	href="<?=$item['link']?>" <?=($item['target'] ? "target=\"{$item['target']}\"" : "")?>
					 <?=($item['accesskey'] ? 'accesskey="' . $accesskey . '"' : '')?>>
				 <?=htmlReady($item['text'])?>
				 </a>
				 </li>
				 <?
			 }
		endforeach ?>
	</ul>
</div>
<br>
<!-- Ende Header -->
