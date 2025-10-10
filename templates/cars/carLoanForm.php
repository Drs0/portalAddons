<?php 
$carTitle = $args['carTitle'];
get_header();
?>
<main class="car-loan-form">
    <h1>Loan <?= esc_html($carTitle); ?></h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="notice success">
            ✅ Your loan request has been submitted and awaits admin approval.
        </div>
    <?php endif; ?>

    <?php if (is_user_logged_in()): ?>
        <form method="POST">
            <p><label>Start Date:<br><input type="date" name="loan_start" required></label></p>
            <p><label>End Date:<br><input type="date" name="loan_end" required></label></p>
            <p><label>Notes:<br><textarea name="loan_notes" rows="4" style="width:100%;"></textarea></label></p>
            <p><button type="submit" name="loan_submit">Submit Loan Request</button></p>
        </form>
    <?php else: ?>
        <p>You must be logged in to loan a car.</p>
    <?php endif; ?>
</main>
<?php
get_footer();
