<?php
$carTitle = $args['carTitle'];
get_header();
?>
<main class="car-loan-form">
    <h1>Loan <?= esc_html($carTitle); ?></h1>
    <?php if (is_user_logged_in()): ?>
        <form method="POST">
            <p>
                <label for="loanPhone">Phone Number:</label><br>
                <input type="tel" name="loanPhone" id="loanPhone" required>
            </p>
            <p><label>Start Date:<br><input type="date" name="loanStart" required></label></p>
            <p><label>End Date:<br><input type="date" name="loanEnd" required></label></p>
            <p><label>Notes:<br><textarea name="loanNotes" rows="4" style="width:100%;"></textarea></label></p>
            <p><button type="submit" name="loanSubmit">Submit Loan Request</button></p>
        </form>
    <?php else: ?>
        <p>You must be logged in to loan a car.</p>
    <?php endif; ?>
</main>
<?php
get_footer();
