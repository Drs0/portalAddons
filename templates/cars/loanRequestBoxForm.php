<p><strong>User:</strong> <?= esc_html(get_userdata($userId)->display_name ?? 'Unknown'); ?></p>
<p><strong>Car:</strong> <?= esc_html(get_the_title($carId)); ?></p>
<p><strong>Phone:</strong> <?php echo esc_html($phone); ?></p>
<p><strong>Start:</strong> <?= esc_html($start); ?></p>
<p><strong>End:</strong> <?= esc_html($end); ?></p>
<p><strong>Notes:</strong> <?= nl2br(esc_html($notes)); ?></p>
<hr>
<p><label>Status:
    <select name="loanStatus">
        <?php foreach ($statuses as $key => $label): ?>
            <option value="<?= esc_attr($key); ?>" <?= selected($status, $key, false); ?>><?= esc_html($label); ?></option>
        <?php endforeach; ?>
    </select>
</label></p>
