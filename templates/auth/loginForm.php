<form method="post" class="portal-login-form">
    <p>
        <label for="portalUsername">Username or Email</label><br>
        <input type="text" name="portalUsername" required />
    </p>
    <p>
        <label for="portalPassword">Password</label><br>
        <input type="password" name="portalPassword" required />
    </p>
    <?php wp_nonce_field('portalFrontLogin', 'portalLoginNonce'); ?>
    <p><input type="submit" name="portalLoginSubmit" value="Login" /></p>
</form>
