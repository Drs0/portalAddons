<?php get_header(); ?>
<form method="post" class="portal-register-form" id="signup-form">
    <div>
        <label for="portalRegUsername">Username</label>
        <input id="portalRegUsername" type="text" name="portalRegUsername" required />
    </div>
    <div>
        <label for="portalRegEmail">Email</label>
        <input id="portalRegEmail" type="email" name="portalRegEmail" required />
    </div>
    <div>
        <label for="portalRegPassword">Password</label>
        <input id="portalRegPassword" type="password" name="portalRegPassword" required />
    </div>
    <div>
        <label for="confirm-password">Confirm Password:</label>
        <input type="password" id="confirm-password" name="portalConfirmRegPassword" required />
        <span id="confirm-error" style="color:red; display:none;">Passwords do not match</span>
    </div>
    <?php wp_nonce_field('portalFrontRegister', 'portalRegisterNonce'); ?>
    <div><input type="submit" name="portalRegisterSubmit" value="Register" /></div>
</form>
<?php get_footer(); ?>
