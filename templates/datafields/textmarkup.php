<textarea name="<?= $name ?>[<?= $model->id ?>]"
          id="<?= $name ?>_<?= $model->id ?>"
          class="add_toolbar wysiwyg"
          <? if ($model->is_required) echo 'required'; ?>
><?= wysiwygReady($value) ?></textarea>
