<? if (isset($success)): ?>
    <?= MessageBox::success($success) ?>
<? elseif (isset($error)): ?>
    <?= MessageBox::error($error) ?>
<? endif ?>
