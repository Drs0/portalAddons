<label for="price">Price:</label>
<input type="number" id="price" name="price" value="<?= esc_attr($price) ?>">

<label for="year">Year: </label>
<input type="number" id="year" name="year" value="<?= esc_attr($year) ?>">

<label for="mileage">Mileage: </label>
<input type="number" id="mileage" name="mileage" value="<?= esc_attr($mileage) ?>">

<label for="engine">Engine: </label>
<input type="text" id="engine" name="engine" value="<?= esc_attr($engine) ?>">

<label for="loanAmount">Loan Amount: </label>
<input type="number" id="loanAmount" name="loanAmount" value="<?= esc_attr($loanAmount) ?>">

<label><input type="checkbox" name="isLoaned" value="1" <?= checked($isLoaned, true, false) ?>> Loaned</label>
