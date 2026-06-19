<input type="<?= $field ?>"
    <?php if ($attr && is_array($attr) && !empty($attr)): ?>
        <?php foreach ($attr as $key => $value): ?>
            <?php if (is_bool($value) && $value): ?>
                <?= $key ?>="<?= $key ?>"
            <?php elseif (!is_bool($value)): ?>
                <?= $key ?>="<?= $value ?>"
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
/>