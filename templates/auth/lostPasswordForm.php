<form method="post">
    <?php wp_nonce_field('portalLostPassword', 'portalLostPasswordNonce'); ?>
    <p>
        <label for="user_login">Username or Email</label><br>
        <input type="text" name="user_login" id="user_login" required />
    </p>
    <p>
        <input type="submit" name="portalLostPasswordSubmit" value="Reset Password" />
    </p>
</form>
